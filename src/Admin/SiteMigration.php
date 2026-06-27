<?php
namespace PizzaLayer\Admin;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * PizzaLayer Site Migration
 *
 * Full-site backup / migration tool. Exports every piece of PizzaLayer
 * configuration on a site so it can be restored on another installation:
 *
 *   • All pizzalayer_setting_* options (via Settings::get_option_keys())
 *   • All eight CPT post types (toppings, crusts, sauces, cheeses,
 *     drizzles, cuts, sizes, presets) — title, slug, content, excerpt,
 *     status, menu_order, full post_meta, and assigned taxonomy terms
 *   • The pizzalayer_ingredient_group taxonomy tree (parent/child)
 *   • Image references — URL + filename + alt + caption — instead of
 *     the binary files. On import, images are sideloaded into the new
 *     site's media library by URL.
 *   • Anything PizzaLayerPro chooses to contribute via the
 *     pizzalayer_export_payload filter (and consume via
 *     pizzalayer_import_payload).
 *
 * Import policy: create-new-only by slug. If a post or term with the
 * same slug already exists, it's skipped. This is the safest default —
 * importing on top of an existing site will never destroy or overwrite
 * data; it will only fill in gaps.
 *
 * Schema:
 *
 *   {
 *     "schema":  "pizzalayer-site-export",
 *     "version": 1,
 *     "exported_at": "2026-05-04T12:34:56Z",
 *     "source_site": "https://example.com",
 *     "plugin_version": "1.4.0",
 *     "settings": { key: value, ... },
 *     "taxonomy_terms": [ { slug, name, description, parent_slug }, ... ],
 *     "posts": [ {
 *         post_type, slug, title, content, excerpt, status, menu_order,
 *         meta: { key: value, ... },
 *         terms: [ slug, ... ],
 *         layer_image: { url, filename, alt, caption } | null
 *     }, ... ],
 *     "pro": { ... whatever Pro contributes via filter ... } | null
 *   }
 */
class SiteMigration {

	public const SCHEMA_NAME    = 'pizzalayer-site-export';
	public const SCHEMA_VERSION = 1;

	/** All eight CPT post types this plugin owns. */
	private const POST_TYPES = [
		'pizzalayer_toppings',
		'pizzalayer_crusts',
		'pizzalayer_sauces',
		'pizzalayer_cheeses',
		'pizzalayer_drizzles',
		'pizzalayer_cuts',
		'pizzalayer_sizes',
		'pizzalayer_presets',
	];

	private const TAXONOMY = 'pizzalayer_ingredient_group';

	/** Cap on import file size — full sites with many CPTs can be a few MB. */
	private const MAX_IMPORT_BYTES = 25 * 1024 * 1024; // 25 MB

	/* ═══════════════════════════════════════════════════════════════════
	   ROUTING
	   ═══════════════════════════════════════════════════════════════════ */

	/**
	 * admin-post handler for the export download. Wired up in AdminMenu.
	 */
	public function handle_export(): void {
		if ( ! current_user_can( 'manage_options' ) ) { wp_die( -1 ); }
		check_admin_referer( 'pizzalayer_site_export' );
		$this->stream_export();
	}

	/**
	 * Page renderer. Handles the import POST inline before drawing the UI
	 * so the success/error notice can appear above the form on the same
	 * page load.
	 */
	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) { return; }

		$notice = '';

		if ( isset( $_POST['pizzalayer_site_import_submit'], $_POST['_wpnonce'] )
		     && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'pizzalayer_site_import' ) ) {
			$notice = $this->handle_import_upload();
		}

		$this->render_page( $notice );
	}

	/* ═══════════════════════════════════════════════════════════════════
	   EXPORT
	   ═══════════════════════════════════════════════════════════════════ */

	/**
	 * Build the full payload, JSON-encode it, and stream as a download.
	 */
	private function stream_export(): void {
		$payload = $this->build_payload();
		$json    = (string) wp_json_encode( $payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );

		$filename = 'pizzalayer-site-' . gmdate( 'Y-m-d-His' ) . '.json';

		// Discard any buffered output so download headers can be sent cleanly.
		while ( ob_get_level() > 0 ) {
			ob_end_clean();
		}

		// Mirror the headers-already-sent fallback used by Settings::export_settings.
		if ( headers_sent() ) {
			// Headers already committed before this admin_post handler ran, so
			// a file download can't be sent. Fall back to a no-JavaScript page
			// presenting the export JSON in a read-only textarea to copy/save.
			$back = esc_url( wp_get_referer() ?: admin_url( 'admin.php?page=pizzalayer-migration' ) );
			echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>' . esc_html__( 'PizzaLayer Site Export', 'pizzalayer' ) . '</title></head><body style="font-family:sans-serif;padding:24px;max-width:820px;margin:0 auto;">';
			echo '<h1 style="font-size:18px;">' . esc_html__( 'Site export', 'pizzalayer' ) . '</h1>';
			echo '<p>' . esc_html__( 'Automatic download was unavailable on this server. Copy the text below and save it with this file name:', 'pizzalayer' ) . ' <code>' . esc_html( $filename ) . '</code></p>';
			echo '<textarea readonly rows="20" style="width:100%;box-sizing:border-box;font-family:monospace;font-size:12px;">' . esc_textarea( $json ) . '</textarea>';
			echo '<p><a href="' . $back . '">' . esc_html__( 'Back', 'pizzalayer' ) . '</a></p>';
			echo '</body></html>';
			exit;
		}

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Content-Length: ' . strlen( $json ) );
		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput — raw JSON download
		exit;
	}

	/**
	 * Assemble the full export payload.
	 */
	private function build_payload(): array {
		$payload = [
			'schema'         => self::SCHEMA_NAME,
			'version'        => self::SCHEMA_VERSION,
			'exported_at'    => gmdate( 'c' ),
			'source_site'    => home_url( '/' ),
			'plugin_version' => defined( 'PIZZALAYER_VERSION' ) ? PIZZALAYER_VERSION : 'unknown',
			'settings'       => $this->collect_settings(),
			'taxonomy_terms' => $this->collect_terms(),
			'posts'          => $this->collect_posts(),
			'pro'            => null,
		];

		/**
		 * Filter the full export payload before serialization.
		 *
		 * PizzaLayerPro hooks here to add its own settings, post meta,
		 * pricing grids, etc. under the 'pro' key. Other extensions can
		 * also add top-level keys, but should namespace them clearly
		 * (e.g. 'mycompany_pizza_addon').
		 *
		 * @param array $payload Full export payload.
		 */
		$payload = apply_filters( 'pizzalayer_export_payload', $payload );

		return is_array( $payload ) ? $payload : [];
	}

	/**
	 * Snapshot every plugin-managed option.
	 */
	private function collect_settings(): array {
		$out = [];
		foreach ( Settings::get_option_keys() as $key ) {
			$out[ $key ] = get_option( $key, null );
		}
		return $out;
	}

	/**
	 * Walk the ingredient-group taxonomy tree.
	 *
	 * Records each term's slug, name, description, and parent slug
	 * (resolved from parent ID) so the hierarchy can be reconstructed
	 * on import without depending on numeric IDs.
	 */
	private function collect_terms(): array {
		$terms = get_terms( [
			'taxonomy'   => self::TAXONOMY,
			'hide_empty' => false,
		] );
		if ( is_wp_error( $terms ) || ! is_array( $terms ) ) { return []; }

		// Build slug-by-id map for parent resolution.
		$id_to_slug = [];
		foreach ( $terms as $t ) { $id_to_slug[ (int) $t->term_id ] = (string) $t->slug; }

		$out = [];
		foreach ( $terms as $t ) {
			$out[] = [
				'slug'        => (string) $t->slug,
				'name'        => (string) $t->name,
				'description' => (string) $t->description,
				'parent_slug' => $t->parent ? ( $id_to_slug[ (int) $t->parent ] ?? '' ) : '',
			];
		}
		return $out;
	}

	/**
	 * Collect every PizzaLayer-owned post across all eight CPTs.
	 *
	 * Includes: full post body, all post meta, term assignments, and a
	 * resolved layer-image reference (URL + filename + alt + caption)
	 * suitable for sideload-by-URL on the destination site.
	 */
	private function collect_posts(): array {
		$out = [];

		foreach ( self::POST_TYPES as $post_type ) {
			$query = new \WP_Query( [
				'post_type'      => $post_type,
				'post_status'    => [ 'publish', 'draft', 'pending', 'private', 'future' ],
				'posts_per_page' => -1,
				'orderby'        => [ 'menu_order' => 'ASC', 'title' => 'ASC' ],
				'no_found_rows'  => true,
				'fields'         => 'all',
			] );

			foreach ( $query->posts as $post ) {
				$out[] = $this->serialize_post( $post );
			}

			wp_reset_postdata();
		}

		return $out;
	}

	/**
	 * Serialize a single post into a transport-safe array.
	 */
	private function serialize_post( \WP_Post $post ): array {
		$id = (int) $post->ID;

		// Strip WordPress-internal meta keys (revisions, edit lock, etc.)
		// and any obviously private underscored keys EXCEPT ones we know
		// about (the layer-image attachment ID, which we re-resolve to
		// URL below anyway). Capturing arbitrary user-added meta as-is
		// is the whole point.
		$raw_meta = get_post_meta( $id );
		$meta     = [];
		foreach ( $raw_meta as $key => $values ) {
			if ( $this->is_internal_meta_key( $key ) ) { continue; }
			// get_post_meta returns array of strings; collapse single-value
			// keys to a scalar to keep the payload readable, preserve arrays
			// for true multi-value keys.
			if ( count( $values ) === 1 ) {
				$meta[ $key ] = maybe_unserialize( $values[0] );
			} else {
				$meta[ $key ] = array_map( 'maybe_unserialize', $values );
			}
		}

		// Resolve term assignments by slug.
		$term_objs = wp_get_object_terms( $id, self::TAXONOMY );
		$term_slugs = [];
		if ( is_array( $term_objs ) ) {
			foreach ( $term_objs as $t ) { $term_slugs[] = (string) $t->slug; }
		}

		return [
			'post_type'   => (string) $post->post_type,
			'slug'        => (string) $post->post_name,
			'title'       => (string) $post->post_title,
			'content'     => (string) $post->post_content,
			'excerpt'     => (string) $post->post_excerpt,
			'status'      => (string) $post->post_status,
			'menu_order'  => (int)    $post->menu_order,
			'meta'        => $meta,
			'terms'       => $term_slugs,
			'layer_image' => $this->resolve_layer_image( $id ),
		];
	}

	/**
	 * Build a portable image reference for the post's layer image.
	 *
	 * Looks at the standard meta keys this plugin uses:
	 *   • _pizzalayer_layer_image_id   — attachment ID (preferred)
	 *   • pzl_layer_image              — URL string (fallback / legacy)
	 *
	 * Returns null if the post has no resolvable layer image.
	 */
	private function resolve_layer_image( int $post_id ) {
		$attachment_id = (int) get_post_meta( $post_id, '_pizzalayer_layer_image_id', true );
		$url           = '';
		$filename      = '';
		$alt           = '';
		$caption       = '';

		if ( $attachment_id > 0 ) {
			$url      = (string) wp_get_attachment_url( $attachment_id );
			$file     = (string) get_attached_file( $attachment_id );
			$filename = $file ? basename( $file ) : '';
			$alt      = (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
			$attached = get_post( $attachment_id );
			if ( $attached instanceof \WP_Post ) {
				$caption = (string) $attached->post_excerpt;
			}
		} else {
			$legacy_url = (string) get_post_meta( $post_id, 'pzl_layer_image', true );
			if ( $legacy_url ) {
				$url      = $legacy_url;
				$filename = basename( wp_parse_url( $legacy_url, PHP_URL_PATH ) ?: '' );
			}
		}

		if ( ! $url ) { return null; }

		return [
			'url'      => esc_url_raw( $url ),
			'filename' => sanitize_file_name( $filename ),
			'alt'      => sanitize_text_field( $alt ),
			'caption'  => wp_kses_post( $caption ),
		];
	}

	/**
	 * Filter out WordPress-managed meta keys we should never export.
	 *
	 * We intentionally KEEP arbitrary user-added meta (e.g. allergens,
	 * topping_ingredients, melt_factor). We strip:
	 *   • _pizzalayer_layer_image_id (re-derived from URL on import)
	 *   • Core WP meta (_edit_lock, _edit_last, _wp_old_*)
	 */
	private function is_internal_meta_key( string $key ): bool {
		if ( $key === '_pizzalayer_layer_image_id' ) { return true; }
		if ( strpos( $key, '_edit_' ) === 0 )         { return true; }
		if ( strpos( $key, '_wp_old' ) === 0 )        { return true; }
		if ( $key === '_wp_trash_meta_status' )       { return true; }
		if ( $key === '_wp_trash_meta_time' )         { return true; }
		return false;
	}

	/* ═══════════════════════════════════════════════════════════════════
	   IMPORT
	   ═══════════════════════════════════════════════════════════════════ */

	/**
	 * Receive the import upload, validate it, and run the import.
	 * Returns an HTML notice (success or error) for inline display.
	 */
	private function handle_import_upload(): string {
		if ( empty( $_FILES['pizzalayer_site_import_file']['tmp_name'] ) ) {
			return $this->error_notice( __( 'No file received.', 'pizzalayer' ) );
		}

		$file = $_FILES['pizzalayer_site_import_file']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

		if ( ! empty( $file['error'] ) && (int) $file['error'] !== UPLOAD_ERR_OK ) {
			return $this->error_notice( __( 'Upload error.', 'pizzalayer' ) );
		}

		$tmp = isset( $file['tmp_name'] ) ? (string) $file['tmp_name'] : '';
		if ( ! $tmp || ! is_uploaded_file( $tmp ) ) {
			return $this->error_notice( __( 'Invalid upload.', 'pizzalayer' ) );
		}

		$size = isset( $file['size'] ) ? (int) $file['size'] : 0;
		if ( $size > self::MAX_IMPORT_BYTES ) {
			return $this->error_notice( sprintf(
				/* translators: %s = max size with unit */
				__( 'File too large (max %s).', 'pizzalayer' ),
				size_format( self::MAX_IMPORT_BYTES )
			) );
		}

		$orig_name = isset( $file['name'] ) ? sanitize_file_name( (string) $file['name'] ) : '';
		if ( $orig_name === '' || strtolower( pathinfo( $orig_name, PATHINFO_EXTENSION ) ) !== 'json' ) {
			return $this->error_notice( __( 'Expected a .json file.', 'pizzalayer' ) );
		}

		$raw = file_get_contents( $tmp ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		if ( ! $raw ) {
			return $this->error_notice( __( 'Could not read file.', 'pizzalayer' ) );
		}

		$payload = json_decode( $raw, true );
		if ( ! is_array( $payload ) ) {
			return $this->error_notice( __( 'Invalid JSON.', 'pizzalayer' ) );
		}

		// Schema sanity check.
		$schema = isset( $payload['schema'] ) ? (string) $payload['schema'] : '';
		if ( $schema !== self::SCHEMA_NAME ) {
			return $this->error_notice( __( 'Not a PizzaLayer site export file.', 'pizzalayer' ) );
		}
		$version = isset( $payload['version'] ) ? (int) $payload['version'] : 0;
		if ( $version < 1 || $version > self::SCHEMA_VERSION ) {
			return $this->error_notice( sprintf(
				/* translators: %d = schema version found */
				__( 'Unsupported export schema version (%d).', 'pizzalayer' ),
				$version
			) );
		}

		// Determine which sections the user wants to import.
		$do_settings = ! empty( $_POST['pizzalayer_import_settings_section'] );
		$do_terms    = ! empty( $_POST['pizzalayer_import_terms_section'] );
		$do_posts    = ! empty( $_POST['pizzalayer_import_posts_section'] );
		$do_images   = ! empty( $_POST['pizzalayer_import_images'] );
		$do_pro      = ! empty( $_POST['pizzalayer_import_pro_section'] );

		$results = [
			'settings'      => 0,
			'terms_created' => 0,
			'terms_skipped' => 0,
			'posts_created' => 0,
			'posts_skipped' => 0,
			'images_loaded' => 0,
			'images_failed' => 0,
		];

		if ( $do_settings && ! empty( $payload['settings'] ) && is_array( $payload['settings'] ) ) {
			$results['settings'] = $this->import_settings( $payload['settings'] );
		}

		if ( $do_terms && ! empty( $payload['taxonomy_terms'] ) && is_array( $payload['taxonomy_terms'] ) ) {
			[ $created, $skipped ] = $this->import_terms( $payload['taxonomy_terms'] );
			$results['terms_created'] = $created;
			$results['terms_skipped'] = $skipped;
		}

		if ( $do_posts && ! empty( $payload['posts'] ) && is_array( $payload['posts'] ) ) {
			$post_results = $this->import_posts( $payload['posts'], $do_images );
			$results['posts_created']  = $post_results['created'];
			$results['posts_skipped']  = $post_results['skipped'];
			$results['images_loaded']  = $post_results['images_loaded'];
			$results['images_failed']  = $post_results['images_failed'];
		}

		// Hand the whole payload to Pro so it can consume its 'pro' section
		// (and anything else it expects). Free plugin does nothing here.
		if ( $do_pro ) {
			/**
			 * Fires after the free-plugin import sections have run.
			 *
			 * Pro hooks here to consume the 'pro' key and anything else
			 * it added during export. The implementation is responsible
			 * for its own create-only-by-slug semantics.
			 *
			 * @param array $payload Full import payload.
			 * @param array $results Counts populated by the free importer.
			 */
			do_action( 'pizzalayer_import_payload', $payload, $results );
		}

		return $this->success_notice( $results );
	}

	/**
	 * Import settings using the same allowlist & sanitization rules as
	 * Settings::import_settings, applied directly here so we don't have
	 * to expose that private method publicly.
	 *
	 * @return int Number of settings restored.
	 */
	private function import_settings( array $data ): int {
		$allowed = array_flip( Settings::get_option_keys() );
		$count   = 0;

		$array_options = [
			'pizzalayer_setting_topping_fractions',
		];
		$raw_options = [
			'pizzalayer_setting_adv_custom_css',
			'pizzalayer_setting_adv_custom_js',
		];
		$allowed_fractions = [
			'whole',
			'half-left', 'half-right',
			'quarter-top-left', 'quarter-top-right',
			'quarter-bottom-left', 'quarter-bottom-right',
		];

		foreach ( $data as $key => $value ) {
			if ( ! isset( $allowed[ $key ] ) ) { continue; }
			$key_safe = sanitize_key( $key );

			if ( in_array( $key, $array_options, true ) ) {
				$sanitised = is_array( $value )
					? array_values( array_intersect( array_map( 'sanitize_key', $value ), $allowed_fractions ) )
					: [];
				if ( ! in_array( 'whole', $sanitised, true ) ) {
					array_unshift( $sanitised, 'whole' );
				}
				update_option( $key_safe, $sanitised );
			} elseif ( in_array( $key, $raw_options, true ) ) {
				// Custom CSS / Custom JS are stored raw and emitted verbatim on
				// the front end, so importing them requires unfiltered_html
				// (matches Settings::save_settings). Skip silently for users
				// who lack the capability — e.g. multisite site admins.
				if ( ! current_user_can( 'unfiltered_html' ) ) { continue; }
				update_option( $key_safe, (string) $value );
			} else {
				update_option( $key_safe, wp_kses_post( (string) $value ) );
			}
			$count++;
		}

		return $count;
	}

	/**
	 * Import taxonomy terms with create-only-by-slug semantics.
	 *
	 * Two-pass: create terms in slug order first, then a second pass to
	 * wire up parent relationships once all terms exist.
	 *
	 * @return array{0:int,1:int} [created, skipped]
	 */
	private function import_terms( array $terms ): array {
		$created = 0;
		$skipped = 0;
		$slug_to_id = [];

		// Map existing terms first.
		$existing = get_terms( [ 'taxonomy' => self::TAXONOMY, 'hide_empty' => false ] );
		if ( is_array( $existing ) ) {
			foreach ( $existing as $t ) { $slug_to_id[ $t->slug ] = (int) $t->term_id; }
		}

		// Pass 1 — create.
		foreach ( $terms as $t ) {
			if ( ! is_array( $t ) ) { continue; }
			$slug = isset( $t['slug'] ) ? sanitize_title( (string) $t['slug'] ) : '';
			$name = isset( $t['name'] ) ? sanitize_text_field( (string) $t['name'] ) : '';
			if ( ! $slug || ! $name ) { continue; }

			if ( isset( $slug_to_id[ $slug ] ) ) {
				$skipped++;
				continue;
			}

			$result = wp_insert_term( $name, self::TAXONOMY, [
				'slug'        => $slug,
				'description' => isset( $t['description'] ) ? wp_kses_post( (string) $t['description'] ) : '',
			] );
			if ( is_wp_error( $result ) ) {
				$skipped++;
				continue;
			}
			$slug_to_id[ $slug ] = (int) $result['term_id'];
			$created++;
		}

		// Pass 2 — wire parent relationships for terms we just created.
		foreach ( $terms as $t ) {
			if ( ! is_array( $t ) ) { continue; }
			$slug        = isset( $t['slug'] ) ? sanitize_title( (string) $t['slug'] ) : '';
			$parent_slug = isset( $t['parent_slug'] ) ? sanitize_title( (string) $t['parent_slug'] ) : '';
			if ( ! $slug || ! $parent_slug )                  { continue; }
			if ( ! isset( $slug_to_id[ $slug ], $slug_to_id[ $parent_slug ] ) ) { continue; }
			wp_update_term( $slug_to_id[ $slug ], self::TAXONOMY, [
				'parent' => $slug_to_id[ $parent_slug ],
			] );
		}

		return [ $created, $skipped ];
	}

	/**
	 * Import posts with create-only-by-slug semantics.
	 *
	 * For each post: skip if a post with the same slug already exists on
	 * the same post_type; otherwise insert, copy meta, attach taxonomy
	 * terms, and (optionally) sideload the layer image by URL.
	 */
	private function import_posts( array $posts, bool $do_images ): array {
		$created = 0;
		$skipped = 0;
		$images_loaded = 0;
		$images_failed = 0;

		foreach ( $posts as $row ) {
			if ( ! is_array( $row ) ) { continue; }

			$post_type = isset( $row['post_type'] ) ? sanitize_key( (string) $row['post_type'] ) : '';
			$slug      = isset( $row['slug'] )      ? sanitize_title( (string) $row['slug'] )      : '';
			$title     = isset( $row['title'] )     ? sanitize_text_field( (string) $row['title'] ) : '';

			if ( ! $post_type || ! $slug || ! in_array( $post_type, self::POST_TYPES, true ) ) {
				$skipped++;
				continue;
			}

			// Create-only-by-slug: skip if an existing post on this type already uses the slug.
			$existing = get_page_by_path( $slug, OBJECT, $post_type );
			if ( $existing instanceof \WP_Post ) {
				$skipped++;
				continue;
			}

			$status_in = isset( $row['status'] ) ? sanitize_key( (string) $row['status'] ) : 'publish';
			$allowed_statuses = [ 'publish', 'draft', 'pending', 'private', 'future' ];
			if ( ! in_array( $status_in, $allowed_statuses, true ) ) { $status_in = 'publish'; }

			$post_id = wp_insert_post( [
				'post_type'    => $post_type,
				'post_name'    => $slug,
				'post_title'   => $title ?: $slug,
				'post_content' => isset( $row['content'] ) ? wp_kses_post( (string) $row['content'] ) : '',
				'post_excerpt' => isset( $row['excerpt'] ) ? wp_kses_post( (string) $row['excerpt'] ) : '',
				'post_status'  => $status_in,
				'menu_order'   => isset( $row['menu_order'] ) ? (int) $row['menu_order'] : 0,
			], true );

			if ( is_wp_error( $post_id ) || ! $post_id ) {
				$skipped++;
				continue;
			}

			// Meta — wp_insert_post returns a fresh ID, so copy meta now.
			if ( ! empty( $row['meta'] ) && is_array( $row['meta'] ) ) {
				foreach ( $row['meta'] as $mkey => $mval ) {
					$mkey_safe = sanitize_key( (string) $mkey );
					if ( ! $mkey_safe ) { continue; }
					if ( $this->is_internal_meta_key( $mkey_safe ) ) { continue; }

					if ( is_array( $mval ) ) {
						// Multi-value meta: clear, then add each value.
						delete_post_meta( $post_id, $mkey_safe );
						foreach ( $mval as $v ) {
							add_post_meta( $post_id, $mkey_safe, $this->sanitize_meta_value( $v ) );
						}
					} else {
						update_post_meta( $post_id, $mkey_safe, $this->sanitize_meta_value( $mval ) );
					}
				}
			}

			// Taxonomy terms — only those that already exist on the destination
			// (term import pass should have run first, but we don't force it).
			if ( ! empty( $row['terms'] ) && is_array( $row['terms'] ) ) {
				$term_ids = [];
				foreach ( $row['terms'] as $term_slug ) {
					$term_slug = sanitize_title( (string) $term_slug );
					if ( ! $term_slug ) { continue; }
					$existing_term = get_term_by( 'slug', $term_slug, self::TAXONOMY );
					if ( $existing_term && ! is_wp_error( $existing_term ) ) {
						$term_ids[] = (int) $existing_term->term_id;
					}
				}
				if ( $term_ids ) {
					wp_set_object_terms( $post_id, $term_ids, self::TAXONOMY, false );
				}
			}

			// Layer image — sideload from URL if requested.
			if ( $do_images && ! empty( $row['layer_image'] ) && is_array( $row['layer_image'] ) ) {
				$ok = $this->sideload_layer_image( $post_id, $row['layer_image'] );
				if ( $ok ) { $images_loaded++; } else { $images_failed++; }
			}

			$created++;
		}

		return [
			'created'       => $created,
			'skipped'       => $skipped,
			'images_loaded' => $images_loaded,
			'images_failed' => $images_failed,
		];
	}

	/**
	 * Best-effort sanitization for arbitrary meta values from import.
	 *
	 * Strings → kses_post. Numeric → preserved. Bool → preserved. Arrays
	 * → recursively sanitized. Anything else → coerced to string and kses'd.
	 */
	private function sanitize_meta_value( $value ) {
		if ( is_array( $value ) ) {
			return array_map( [ $this, 'sanitize_meta_value' ], $value );
		}
		if ( is_bool( $value ) || is_int( $value ) || is_float( $value ) ) {
			return $value;
		}
		return wp_kses_post( (string) $value );
	}

	/**
	 * Sideload a layer image from an external URL into the media library
	 * and attach it to the freshly-imported post via the standard meta keys.
	 *
	 * Mirrors the upload pattern used by LayerImageMaker / LayerImageMetaBox.
	 */
	private function sideload_layer_image( int $post_id, array $img ): bool {
		$url = isset( $img['url'] ) ? esc_url_raw( (string) $img['url'] ) : '';
		if ( ! $url ) { return false; }

		// SSRF hardening: only fetch http(s) URLs, and reject loopback/private/
		// reserved hosts. wp_http_validate_url() blocks those unless a site has
		// explicitly opted in via the http_request_host_is_external filter.
		$scheme = strtolower( (string) wp_parse_url( $url, PHP_URL_SCHEME ) );
		if ( 'http' !== $scheme && 'https' !== $scheme ) {
			return false;
		}
		if ( ! wp_http_validate_url( $url ) ) {
			return false;
		}

		// media_handle_sideload requires these helpers.
		if ( ! function_exists( 'media_handle_sideload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		$tmp = download_url( $url, 30 );
		if ( is_wp_error( $tmp ) ) { return false; }

		$filename = isset( $img['filename'] ) && $img['filename']
			? sanitize_file_name( (string) $img['filename'] )
			: sanitize_file_name( basename( wp_parse_url( $url, PHP_URL_PATH ) ?: 'layer-image.png' ) );

		$file_array = [
			'name'     => $filename,
			'tmp_name' => $tmp,
		];

		$caption  = isset( $img['caption'] ) ? wp_kses_post( (string) $img['caption'] ) : '';
		$alt      = isset( $img['alt'] )     ? sanitize_text_field( (string) $img['alt'] ) : '';

		$attachment_id = media_handle_sideload(
			$file_array,
			0, // not attaching to a parent (matches existing pattern)
			$caption ?: null
		);

		// download_url's tmp file is auto-cleaned by media_handle_sideload on
		// success, but we need to clean it ourselves on failure.
		if ( is_wp_error( $attachment_id ) ) {
			if ( file_exists( $tmp ) ) { @unlink( $tmp ); } // phpcs:ignore
			return false;
		}

		// Apply alt text.
		if ( $alt ) {
			update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt );
		}

		// Wire up to the post under both meta keys (URL for templates that
		// read the legacy key, ID for things that prefer the resolved attachment).
		$resolved_url = (string) wp_get_attachment_url( $attachment_id );
		update_post_meta( $post_id, '_pizzalayer_layer_image_id', (int) $attachment_id );
		update_post_meta( $post_id, 'pzl_layer_image', esc_url_raw( $resolved_url ) );

		return true;
	}

	/* ═══════════════════════════════════════════════════════════════════
	   UI
	   ═══════════════════════════════════════════════════════════════════ */

	private function render_page( string $notice ): void {
		$pro_active = class_exists( 'PizzaLayerPro\\Pro\\Plugin' );

		// Quick statistics for the export preview.
		$post_counts = [];
		$total_posts = 0;
		foreach ( self::POST_TYPES as $pt ) {
			$count = wp_count_posts( $pt );
			$n = isset( $count->publish ) ? (int) $count->publish : 0;
			$n += isset( $count->draft ) ? (int) $count->draft : 0;
			$n += isset( $count->private ) ? (int) $count->private : 0;
			$post_counts[ $pt ] = $n;
			$total_posts += $n;
		}
		$settings_count = count( Settings::get_option_keys() );
		$term_count     = (int) wp_count_terms( [ 'taxonomy' => self::TAXONOMY, 'hide_empty' => false ] );

		?>
		<div class="wrap psm-wrap">
		<?php $this->render_styles(); ?>

		<div class="psm-header">
			<span class="dashicons dashicons-migrate psm-header__icon"></span>
			<div style="flex:1;">
				<h1 class="psm-header__title"><?php esc_html_e( 'Site Migration', 'pizzalayer' ); ?></h1>
				<p class="psm-header__sub"><?php esc_html_e( 'Move an entire PizzaLayer setup — settings, ingredients, custom fields, taxonomy, and Pro data — to another WordPress installation.', 'pizzalayer' ); ?></p>
			</div>
		</div>

		<?php if ( $notice ) { echo $notice; /* phpcs:ignore WordPress.Security.EscapeOutput — pre-built, escaped notice */ } ?>

		<div class="psm-grid">

			<!-- ═════ EXPORT CARD ═════ -->
			<div class="psm-card">
				<div class="psm-card__head">
					<h2><span class="dashicons dashicons-download"></span> <?php esc_html_e( 'Export', 'pizzalayer' ); ?></h2>
					<p><?php esc_html_e( 'Build a complete JSON snapshot of this site\'s PizzaLayer setup. Layer images travel as URL references, not packaged files.', 'pizzalayer' ); ?></p>
				</div>
				<div class="psm-card__body">

					<h3 class="psm-section-h"><?php esc_html_e( 'What\'s included', 'pizzalayer' ); ?></h3>
					<ul class="psm-stats">
						<li><strong><?php echo (int) $settings_count; ?></strong> <?php esc_html_e( 'plugin settings', 'pizzalayer' ); ?></li>
						<li><strong><?php echo (int) $total_posts; ?></strong> <?php esc_html_e( 'total content items', 'pizzalayer' ); ?></li>
						<li><strong><?php echo (int) $term_count; ?></strong> <?php esc_html_e( 'ingredient groups', 'pizzalayer' ); ?></li>
						<?php if ( $pro_active ) : ?>
						<li><span class="psm-pill psm-pill--pro">Pro</span> <?php esc_html_e( 'PizzaLayerPro data (via filter)', 'pizzalayer' ); ?></li>
						<?php endif; ?>
					</ul>

					<details class="psm-details">
						<summary><?php esc_html_e( 'Per-type breakdown', 'pizzalayer' ); ?></summary>
						<table class="psm-table">
							<tbody>
							<?php foreach ( $post_counts as $pt => $n ) :
								$label = ucwords( str_replace( '_', ' ', str_replace( 'pizzalayer_', '', $pt ) ) );
							?>
								<tr>
									<td><?php echo esc_html( $label ); ?></td>
									<td><strong><?php echo (int) $n; ?></strong></td>
								</tr>
							<?php endforeach; ?>
							</tbody>
						</table>
					</details>

					<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=pizzalayer_site_export' ), 'pizzalayer_site_export' ) ); ?>"
					   class="button button-primary psm-export-btn">
						<span class="dashicons dashicons-download"></span>
						<?php esc_html_e( 'Download Full Export (JSON)', 'pizzalayer' ); ?>
					</a>

					<p class="psm-note">
						<?php esc_html_e( 'Image URLs in the export point to this site. The destination installation must be able to reach those URLs over HTTP at import time.', 'pizzalayer' ); ?>
					</p>
				</div>
			</div>

			<!-- ═════ IMPORT CARD ═════ -->
			<div class="psm-card">
				<div class="psm-card__head">
					<h2><span class="dashicons dashicons-upload"></span> <?php esc_html_e( 'Import', 'pizzalayer' ); ?></h2>
					<p><?php esc_html_e( 'Restore a JSON site export. Imports are create-only: any existing post or term with the same slug is skipped, never overwritten.', 'pizzalayer' ); ?></p>
				</div>
				<div class="psm-card__body">
					<form method="post" enctype="multipart/form-data" action="">
						<?php wp_nonce_field( 'pizzalayer_site_import' ); ?>

						<h3 class="psm-section-h"><?php esc_html_e( 'Sections to restore', 'pizzalayer' ); ?></h3>
						<div class="psm-checks">
							<label><input type="checkbox" name="pizzalayer_import_settings_section" value="1" checked> <?php esc_html_e( 'Settings (all PizzaLayer options)', 'pizzalayer' ); ?> <span class="psm-warn"><?php esc_html_e( '— overwrites current settings', 'pizzalayer' ); ?></span></label>
							<label><input type="checkbox" name="pizzalayer_import_terms_section" value="1" checked> <?php esc_html_e( 'Ingredient groups (taxonomy)', 'pizzalayer' ); ?></label>
							<label><input type="checkbox" name="pizzalayer_import_posts_section" value="1" checked> <?php esc_html_e( 'Content items (toppings, crusts, sauces, cheeses, drizzles, cuts, sizes, presets) and their custom fields', 'pizzalayer' ); ?></label>
							<label><input type="checkbox" name="pizzalayer_import_images" value="1" checked> <?php esc_html_e( 'Sideload layer images from their URLs into this site\'s media library', 'pizzalayer' ); ?></label>
							<label<?php echo $pro_active ? '' : ' style="opacity:.55;"'; ?>>
								<input type="checkbox" name="pizzalayer_import_pro_section" value="1" <?php checked( $pro_active ); ?> <?php disabled( ! $pro_active ); ?>>
								<?php esc_html_e( 'PizzaLayerPro data (only if Pro is installed and active here)', 'pizzalayer' ); ?>
								<?php if ( ! $pro_active ) : ?>
									<span class="psm-warn"><?php esc_html_e( '— Pro not detected', 'pizzalayer' ); ?></span>
								<?php endif; ?>
							</label>
						</div>

						<h3 class="psm-section-h"><?php esc_html_e( 'Export file', 'pizzalayer' ); ?></h3>
						<input type="file" name="pizzalayer_site_import_file" accept=".json,application/json" required class="psm-file">

						<div class="psm-warning-box">
							<span class="dashicons dashicons-warning"></span>
							<div>
								<strong><?php esc_html_e( 'Heads up:', 'pizzalayer' ); ?></strong>
								<?php esc_html_e( 'Importing settings WILL overwrite this site\'s current PizzaLayer settings. Posts, terms, and Pro data are create-only by slug — already-present items will be left untouched.', 'pizzalayer' ); ?>
							</div>
						</div>

						<button type="submit" name="pizzalayer_site_import_submit" value="1" class="button button-primary psm-import-btn"
						        onclick="return confirm('<?php echo esc_attr__( 'Run the import now? Settings will be overwritten; posts and terms will be created if missing.', 'pizzalayer' ); ?>');">
							<span class="dashicons dashicons-upload"></span>
							<?php esc_html_e( 'Run Import', 'pizzalayer' ); ?>
						</button>
					</form>
				</div>
			</div>

		</div><!-- /.psm-grid -->

		<!-- ═════ HOW IT WORKS ═════ -->
		<div class="psm-howto">
			<h2><?php esc_html_e( 'How site migration works', 'pizzalayer' ); ?></h2>
			<ol>
				<li><?php esc_html_e( 'On the source site: click Download Full Export. You get a single JSON file containing settings, all 8 content types with their custom fields, the ingredient-group taxonomy, and (if Pro is active) Pro data.', 'pizzalayer' ); ?></li>
				<li><?php esc_html_e( 'On the destination site: install PizzaLayer (and PizzaLayerPro if you used it on the source), upload the JSON, and click Run Import.', 'pizzalayer' ); ?></li>
				<li><?php esc_html_e( 'Layer images are sideloaded from their original URLs into the destination media library. The source site must be reachable over HTTP at import time.', 'pizzalayer' ); ?></li>
				<li><?php esc_html_e( 'Existing posts/terms with the same slug are skipped — re-running an import is safe and idempotent for everything except settings.', 'pizzalayer' ); ?></li>
			</ol>
		</div>

		</div><!-- /.wrap -->
		<?php
	}

	private function render_styles(): void {
		?>
		<style>
		.psm-wrap { max-width: 1200px; }
		.psm-header { background: linear-gradient(135deg, #1a1e23 0%, #2a3038 100%); color:#fff; padding:22px 26px; border-radius:10px; margin:14px 0 20px; display:flex; align-items:flex-start; gap:18px; }
		.psm-header__icon { font-size: 36px !important; width: 36px !important; height: 36px !important; color: #ff6b35; flex-shrink: 0; }
		.psm-header__title { margin:0 0 4px; font-size:22px; font-weight:700; color:#fff; }
		.psm-header__sub { margin:0; color:#c8ccd2; font-size:13px; line-height:1.55; max-width:760px; }

		.psm-grid { display:grid; grid-template-columns: 1fr 1fr; gap:20px; }
		@media (max-width: 960px) { .psm-grid { grid-template-columns: 1fr; } }

		.psm-card { background:#fff; border:1px solid #e0e3e7; border-radius:10px; overflow:hidden; }
		.psm-card__head { padding:18px 22px 14px; border-bottom:1px solid #f0f0f0; }
		.psm-card__head h2 { margin:0 0 4px; font-size:17px; display:flex; align-items:center; gap:8px; }
		.psm-card__head h2 .dashicons { color:#ff6b35; }
		.psm-card__head p { margin:0; font-size:13px; color:#646970; line-height:1.55; }
		.psm-card__body { padding:18px 22px 22px; }

		.psm-section-h { margin:14px 0 8px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#646970; }
		.psm-section-h:first-child { margin-top:0; }

		.psm-stats { list-style:none; margin:0 0 14px; padding:0; display:flex; flex-wrap:wrap; gap:8px 16px; }
		.psm-stats li { font-size:13px; color:#3c434a; }
		.psm-stats strong { color:#1a1e23; font-size:15px; }

		.psm-pill { display:inline-block; font-size:10px; font-weight:700; padding:2px 7px; border-radius:3px; background:#fef3c7; color:#92400e; vertical-align:middle; margin-right:4px; }
		.psm-pill--pro { background:#fde68a; color:#92400e; }

		.psm-details { margin:0 0 14px; }
		.psm-details summary { cursor:pointer; font-size:12px; color:#646970; padding:6px 0; }
		.psm-details summary:hover { color:#1a1e23; }
		.psm-table { width:100%; border-collapse:collapse; margin-top:6px; }
		.psm-table td { padding:6px 10px; font-size:13px; border-bottom:1px solid #f3f4f6; }
		.psm-table td:last-child { text-align:right; font-variant-numeric: tabular-nums; }
		.psm-table tr:last-child td { border-bottom:none; }

		.psm-export-btn, .psm-import-btn { display:inline-flex !important; align-items:center; gap:6px; height:auto !important; padding:8px 16px !important; font-size:14px !important; }
		.psm-export-btn .dashicons, .psm-import-btn .dashicons { font-size:16px !important; width:16px !important; height:16px !important; }

		.psm-note { font-size:12px; color:#646970; margin:10px 0 0; line-height:1.5; }

		.psm-checks { display:flex; flex-direction:column; gap:8px; margin-bottom:16px; }
		.psm-checks label { font-size:13px; color:#3c434a; line-height:1.5; }
		.psm-checks input[type="checkbox"] { margin-right:6px; }
		.psm-warn { color:#b32d2e; font-size:12px; }

		.psm-file { margin-bottom:16px; }

		.psm-warning-box { display:flex; gap:10px; align-items:flex-start; background:#fff8e6; border-left:3px solid #f0b849; padding:12px 14px; border-radius:0 6px 6px 0; margin-bottom:16px; }
		.psm-warning-box .dashicons { color:#f0b849; flex-shrink:0; margin-top:1px; }
		.psm-warning-box div { font-size:12.5px; color:#3c434a; line-height:1.5; }

		.psm-howto { margin-top:24px; background:#f6f7f7; border:1px solid #e0e3e7; border-radius:10px; padding:18px 22px; }
		.psm-howto h2 { margin:0 0 10px; font-size:14px; font-weight:700; }
		.psm-howto ol { margin:0; padding-left:22px; }
		.psm-howto ol li { font-size:13px; color:#3c434a; line-height:1.6; margin-bottom:6px; }
		.psm-howto ol li:last-child { margin-bottom:0; }

		body.pzl-admin-dark .psm-card { background:#252a31; border-color:#2d3748; }
		body.pzl-admin-dark .psm-card__head { border-bottom-color:#2d3748; }
		body.pzl-admin-dark .psm-card__head h2 { color:#e2e8f0; }
		body.pzl-admin-dark .psm-card__head p { color:#8d97a5; }
		body.pzl-admin-dark .psm-stats li,
		body.pzl-admin-dark .psm-checks label,
		body.pzl-admin-dark .psm-howto ol li { color:#cbd5e0; }
		body.pzl-admin-dark .psm-stats strong { color:#f0f6fc; }
		body.pzl-admin-dark .psm-howto { background:#1a1e23; border-color:#2d3748; }
		body.pzl-admin-dark .psm-howto h2 { color:#e2e8f0; }
		body.pzl-admin-dark .psm-table td { border-bottom-color:#2d3748; }
		</style>
		<?php
	}

	/* ═══════════════════════════════════════════════════════════════════
	   NOTICES
	   ═══════════════════════════════════════════════════════════════════ */

	private function error_notice( string $msg ): string {
		return '<div class="notice notice-error is-dismissible"><p><strong>'
			. esc_html__( 'Import failed:', 'pizzalayer' )
			. '</strong> ' . esc_html( $msg ) . '</p></div>';
	}

	private function success_notice( array $r ): string {
		$lines = [];
		if ( $r['settings'] > 0 ) {
			$lines[] = sprintf(
				/* translators: %d = setting count */
				_n( '%d setting restored', '%d settings restored', $r['settings'], 'pizzalayer' ),
				$r['settings']
			);
		}
		if ( $r['terms_created'] > 0 || $r['terms_skipped'] > 0 ) {
			$lines[] = sprintf(
				/* translators: 1: created count, 2: skipped count */
				__( 'Ingredient groups: %1$d created, %2$d skipped (already existed)', 'pizzalayer' ),
				$r['terms_created'],
				$r['terms_skipped']
			);
		}
		if ( $r['posts_created'] > 0 || $r['posts_skipped'] > 0 ) {
			$lines[] = sprintf(
				/* translators: 1: created, 2: skipped */
				__( 'Content items: %1$d created, %2$d skipped (slug already existed)', 'pizzalayer' ),
				$r['posts_created'],
				$r['posts_skipped']
			);
		}
		if ( $r['images_loaded'] > 0 || $r['images_failed'] > 0 ) {
			$lines[] = sprintf(
				/* translators: 1: loaded, 2: failed */
				__( 'Layer images: %1$d sideloaded, %2$d failed', 'pizzalayer' ),
				$r['images_loaded'],
				$r['images_failed']
			);
		}
		if ( ! $lines ) {
			$lines[] = __( 'Nothing to import (no sections selected or all entries skipped).', 'pizzalayer' );
		}

		$body  = '<strong>' . esc_html__( 'Import complete.', 'pizzalayer' ) . '</strong> ';
		$body .= '<ul style="margin:6px 0 0 18px;">';
		foreach ( $lines as $line ) {
			$body .= '<li>' . esc_html( $line ) . '</li>';
		}
		$body .= '</ul>';

		return '<div class="notice notice-success is-dismissible"><p>' . $body . '</p></div>';
	}
}
