<?php
namespace PizzaLayer\Admin;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * PizzaLayer – Layer Image Maker Meta Box
 *
 * Adds a "Layer Image Maker" meta box to the edit/new screens for every
 * image-bearing CPT (all types except 'sizes'). The box is collapsed by default
 * and shows only a prompt until the user opens it.
 *
 * When opened it embeds a compact version of the image-maker tool (same Canvas/
 * filter pipeline as the standalone page) with a "Set as Layer Image" button that
 * writes the result directly to the post's {type}_layer_image meta field – no
 * download step required.
 *
 * Field write strategy
 * --------------------
 * SCF/ACF stores image fields as either:
 *   (a) an integer attachment ID  (when "Return Format" = ID or Object)
 *   (b) a URL string              (when "Return Format" = URL)
 *
 * Because we don't know which format the user chose, we:
 *  1. Upload the PNG to the media library (via media_handle_sideload) → get an ID.
 *  2. Write the ID to  post_meta("{type}_layer_image").
 *  3. Also write the URL to post_meta("{type}_layer_image") — SCF will use
 *     whichever it finds.  In practice, writing both the ID and the URL to the
 *     same key would conflict, so we write the *attachment ID* (integer) which
 *     SCF/ACF handles regardless of return format.
 *
 * If SCF/ACF is not active we fall back to storing the URL directly.
 */
class LayerImageMetaBox {

	/** CPT slugs that have layer images (sizes has none). */
	private const IMAGE_TYPES = [ 'toppings', 'crusts', 'sauces', 'cheeses', 'drizzles', 'cuts' ];

	// ── Registration ─────────────────────────────────────────────────────────

	public function register_hooks(): void {
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
		add_action( 'wp_ajax_pizzalayer_metabox_set_layer_image', [ $this, 'ajax_set_layer_image' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	public function add_meta_boxes(): void {
		foreach ( self::IMAGE_TYPES as $slug ) {
			$post_type = 'pizzalayer_' . $slug;
			add_meta_box(
				'pizzalayer_layer_image_maker',
				'<span class="dashicons dashicons-format-image" style="color:#ff6b35;font-size:14px;width:14px;height:14px;vertical-align:middle;margin-right:5px;"></span> ' . esc_html__( 'Layer Image Maker', 'pizzalayer' ),
				[ $this, 'render_meta_box' ],
				$post_type,
				'side',
				'default',
				[ 'slug' => $slug ]
			);
		}
	}

	public function enqueue_assets( string $hook ): void {
		// Only on post edit/new screens for our CPTs
		if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) { return; }
		$screen = get_current_screen();
		if ( ! $screen ) { return; }
		$cpt = $screen->post_type ?? '';
		if ( strpos( $cpt, 'pizzalayer_' ) !== 0 ) { return; }
		$slug = str_replace( 'pizzalayer_', '', $cpt );
		if ( ! in_array( $slug, self::IMAGE_TYPES, true ) ) { return; }

		wp_enqueue_media();
	}

	// ── Meta box HTML ─────────────────────────────────────────────────────────

	public function render_meta_box( \WP_Post $post, array $box ): void {
		$slug       = $box['args']['slug'] ?? '';
		$type       = rtrim( $slug, 's' ); // toppings→topping etc.
		$field_key  = $type . '_layer_image';
		$post_id    = $post->ID;

		// Current value — try SCF/ACF get_field first, fall back to raw meta
		$current_url = '';
		$current_id  = 0;
		if ( function_exists( 'get_field' ) ) {
			$val = get_field( $field_key, $post_id );
			if ( is_array( $val ) && ! empty( $val['url'] ) ) {
				$current_url = $val['url'];
				$current_id  = (int) ( $val['ID'] ?? 0 );
			} elseif ( is_string( $val ) && $val ) {
				$current_url = $val;
			} elseif ( is_int( $val ) && $val ) {
				$current_id  = $val;
				$current_url = (string) wp_get_attachment_url( $val );
			}
		}
		if ( ! $current_url ) {
			$meta = get_post_meta( $post_id, $field_key, true );
			if ( is_array( $meta ) && ! empty( $meta['url'] ) ) {
				$current_url = $meta['url'];
				$current_id  = (int) ( $meta['ID'] ?? 0 );
			} elseif ( is_string( $meta ) && $meta ) {
				$current_url = $meta;
			} elseif ( is_numeric( $meta ) && $meta ) {
				$current_id  = (int) $meta;
				$current_url = (string) wp_get_attachment_url( $current_id );
			}
		}

		$aspect_raw = get_option( 'pizzalayer_setting_pizza_aspect', '4 / 3' );
		$aspect_js  = preg_replace( '/\s+/', '', $aspect_raw );
		$singular   = ucfirst( $type );
		$nonce      = wp_create_nonce( 'pizzalayer_layer_image_maker' );
		$meta_nonce = wp_create_nonce( 'pzl_metabox_set_layer_image_' . $post_id );

		?>
		<div class="pzlmb-wrap" id="pzlmb-wrap-<?php echo esc_attr( $slug ); ?>"
		     data-post-id="<?php echo esc_attr( $post_id ); ?>"
		     data-field-key="<?php echo esc_attr( $field_key ); ?>"
		     data-type="<?php echo esc_attr( $type ); ?>"
		     data-ajax="<?php echo esc_url( admin_url('admin-ajax.php') ); ?>"
		     data-nonce="<?php echo esc_attr( $nonce ); ?>"
		     data-meta-nonce="<?php echo esc_attr( $meta_nonce ); ?>"
		     data-aspect="<?php echo esc_attr( $aspect_js ); ?>">

			<!-- ── Collapsed prompt ──────────────────────────────────────── -->
			<div class="pzlmb-prompt" id="pzlmb-prompt-<?php echo esc_attr( $slug ); ?>">
				<?php if ( $current_url ) : ?>
				<div class="pzlmb-current">
					<img src="<?php echo esc_url( $current_url ); ?>" alt="Current layer image"
					     class="pzlmb-current-img" id="pzlmb-current-img-<?php echo esc_attr( $slug ); ?>">
					<span class="pzlmb-current-label"><?php esc_html_e( 'Current layer image', 'pizzalayer' ); ?></span>
				</div>
				<?php else : ?>
				<div class="pzlmb-empty">
					<span class="dashicons dashicons-format-image pzlmb-empty-icon"></span>
					<span class="pzlmb-empty-label"><?php esc_html_e( 'No layer image set', 'pizzalayer' ); ?></span>
				</div>
				<?php endif; ?>

				<button type="button" class="button pzlmb-open-btn"
				        id="pzlmb-open-btn-<?php echo esc_attr( $slug ); ?>">
					<span class="dashicons dashicons-edit"></span>
					<?php echo $current_url ? esc_html__( 'Edit / Replace Layer Image', 'pizzalayer' ) : esc_html__( 'Create Layer Image', 'pizzalayer' ); ?>
				</button>
			</div>

			<!-- ── Expanded editor (hidden until button click) ───────────── -->
			<div class="pzlmb-editor" id="pzlmb-editor-<?php echo esc_attr( $slug ); ?>" style="display:none;">

				<!-- Source buttons -->
				<div class="pzlmb-source-row">
					<div class="pzlmb-drop-zone" id="pzlmb-drop-<?php echo esc_attr( $slug ); ?>"
					     tabindex="0" role="button" aria-label="<?php esc_attr_e( 'Upload image', 'pizzalayer' ); ?>">
						<span class="dashicons dashicons-upload"></span>
						<span><?php esc_html_e( 'Drop / click to upload', 'pizzalayer' ); ?></span>
						<input type="file" class="pzlmb-file-input" accept="image/*" style="display:none;">
					</div>
					<button type="button" class="button pzlmb-media-btn">
						<span class="dashicons dashicons-admin-media"></span> <?php esc_html_e( 'Media Library', 'pizzalayer' ); ?>
					</button>
				</div>

				<!-- Canvas stage -->
				<div class="pzlmb-stage" id="pzlmb-stage-<?php echo esc_attr( $slug ); ?>">
					<div class="pzlmb-canvas-empty" id="pzlmb-canvas-empty-<?php echo esc_attr( $slug ); ?>">
						<span class="dashicons dashicons-format-image"></span>
						<p><?php esc_html_e( 'Upload an image to begin', 'pizzalayer' ); ?></p>
					</div>
					<canvas class="pzlmb-canvas" style="display:none;max-width:100%;"></canvas>
					<svg class="pzlmb-guide-svg" style="display:none;position:absolute;top:0;left:0;pointer-events:none;width:100%;height:100%;"></svg>
				</div>

				<!-- Mini toolbar -->
				<div class="pzlmb-toolbar">
					<button type="button" class="pzlmb-tbtn pzlmb-tbtn--rotate-ccw" title="<?php esc_attr_e( 'Rotate left', 'pizzalayer' ); ?>">↺</button>
					<button type="button" class="pzlmb-tbtn pzlmb-tbtn--rotate-cw"  title="<?php esc_attr_e( 'Rotate right', 'pizzalayer' ); ?>">↻</button>
					<button type="button" class="pzlmb-tbtn pzlmb-tbtn--flip-h"     title="<?php esc_attr_e( 'Flip H', 'pizzalayer' ); ?>">⇄</button>
					<button type="button" class="pzlmb-tbtn pzlmb-tbtn--flip-v"     title="<?php esc_attr_e( 'Flip V', 'pizzalayer' ); ?>">⇅</button>
					<span class="pzlmb-toolbar-sep"></span>
					<label class="pzlmb-tbtn-label" title="Show guide">
						<input type="checkbox" class="pzlmb-show-guide" checked> <?php esc_html_e( 'Guide', 'pizzalayer' ); ?>
					</label>
				</div>

				<!-- Adjustments (compact) -->
				<div class="pzlmb-adj">
					<?php
					$mb_sliders = [
						[ 'key' => 'brightness', 'label' => __( 'Bright', 'pizzalayer' ), 'min' => -100, 'max' => 100, 'def' => 0   ],
						[ 'key' => 'contrast',   'label' => __( 'Contrast', 'pizzalayer' ),'min' => -100, 'max' => 100, 'def' => 0   ],
						[ 'key' => 'saturation', 'label' => __( 'Sat', 'pizzalayer' ),    'min' => -100, 'max' => 100, 'def' => 0   ],
						[ 'key' => 'opacity',    'label' => __( 'Opacity', 'pizzalayer' ), 'min' => 0,   'max' => 100, 'def' => 100 ],
					];
					foreach ( $mb_sliders as $s ) : ?>
					<div class="pzlmb-adj-row">
						<span class="pzlmb-adj-label"><?php echo esc_html( $s['label'] ); ?></span>
						<input type="range" class="pzlmb-slider pzlmb-slider--<?php echo esc_attr( $s['key'] ); ?>"
						       min="<?php echo (int) $s['min']; ?>" max="<?php echo (int) $s['max']; ?>"
						       value="<?php echo (int) $s['def']; ?>" step="1">
						<span class="pzlmb-adj-val"><?php echo (int) $s['def']; ?></span>
					</div>
					<?php endforeach; ?>
					<button type="button" class="button-link pzlmb-reset-adj">↺ <?php esc_html_e( 'Reset', 'pizzalayer' ); ?></button>
				</div>

				<!-- Action row -->
				<div class="pzlmb-actions">
					<button type="button" class="button button-primary pzlmb-set-btn" disabled>
						<span class="dashicons dashicons-yes"></span>
						<?php printf( esc_html__( 'Set as %s Layer Image', 'pizzalayer' ), esc_html( $singular ) ); ?>
					</button>
					<button type="button" class="button pzlmb-cancel-btn"><?php esc_html_e( 'Cancel', 'pizzalayer' ); ?></button>
				</div>
				<p class="pzlmb-status" id="pzlmb-status-<?php echo esc_attr( $slug ); ?>"></p>
			</div>

		</div><!-- /.pzlmb-wrap -->

		<?php $this->render_meta_box_assets( $slug ); ?>
		<?php
	}

	// ── AJAX: upload PNG and set post meta ────────────────────────────────────

	public function ajax_set_layer_image(): void {
		$post_id   = (int) ( $_POST['post_id']   ?? 0 );
		$filename  = sanitize_file_name( $_POST['filename']  ?? 'layer-image.png' );
		$data      = $_POST['data'] ?? ''; // phpcs:ignore

		check_ajax_referer( 'pzl_metabox_set_layer_image_' . $post_id, 'meta_nonce' );

		// Require both edit and upload rights.
		if ( ! current_user_can( 'edit_post', $post_id ) || ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( 'Forbidden' );
		}
		if ( ! $post_id ) {
			wp_send_json_error( 'Missing parameters' );
		}

		// Allowlist the writable meta keys to prevent arbitrary meta writes.
		$raw_key = sanitize_key( $_POST['field_key'] ?? '' );
		$allowed_keys = array_map(
			static function( string $slug ): string {
				return rtrim( $slug, 's' ) . '_layer_image';
			},
			self::IMAGE_TYPES
		);
		if ( ! in_array( $raw_key, $allowed_keys, true ) ) {
			wp_send_json_error( 'Invalid field key' );
		}
		$field_key = $raw_key;

		// Strip data-URI header
		if ( strpos( $data, 'base64,' ) !== false ) {
			[ , $data ] = explode( 'base64,', $data );
		}
		$raw = base64_decode( $data ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
		if ( ! $raw ) { wp_send_json_error( 'Bad image data' ); }

		// Validate decoded bytes are a real image before touching the filesystem.
		$finfo     = new \finfo( FILEINFO_MIME_TYPE );
		$real_mime = $finfo->buffer( $raw );
		if ( ! in_array( $real_mime, [ 'image/png', 'image/jpeg', 'image/gif', 'image/webp' ], true ) ) {
			wp_send_json_error( 'Invalid image data' );
		}

		// Derive a safe extension from the real MIME type (never trust the client name).
		$ext_map  = [ 'image/png' => '.png', 'image/jpeg' => '.jpg', 'image/gif' => '.gif', 'image/webp' => '.webp' ];
		$safe_ext = $ext_map[ $real_mime ];
		$filename = pathinfo( $filename, PATHINFO_FILENAME ) . $safe_ext;

		$tmp = wp_tempnam( $filename );
		file_put_contents( $tmp, $raw ); // phpcs:ignore WordPress.WP.AlternativeFunctions

		$att_id = media_handle_sideload(
			[
				'name'     => $filename,
				'type'     => $real_mime,
				'tmp_name' => $tmp,
				'error'    => 0,
				'size'     => strlen( $raw ),
			],
			$post_id,
			'',
			[ 'post_title' => pathinfo( $filename, PATHINFO_FILENAME ) ]
		);

		@unlink( $tmp ); // phpcs:ignore

		if ( is_wp_error( $att_id ) ) {
			wp_send_json_error( $att_id->get_error_message() );
		}

		$url = (string) wp_get_attachment_url( $att_id );

		// Write to post meta — store attachment ID (integer) so SCF/ACF handles it
		// regardless of return format. Also update_field if SCF/ACF is active.
		update_post_meta( $post_id, $field_key, $att_id );
		if ( function_exists( 'update_field' ) ) {
			update_field( $field_key, $att_id, $post_id );
		}

		wp_send_json_success( [ 'id' => $att_id, 'url' => $url ] );
	}

	// ── Styles + script (output once per meta box instance) ──────────────────

	private static bool $assets_printed = false;

	private function render_meta_box_assets( string $slug ): void {
		// Styles printed once, script printed per instance (unique IDs per slug)
		if ( ! self::$assets_printed ) {
			self::$assets_printed = true;
			$this->render_mb_styles();
		}
		$this->render_mb_script( $slug );
	}

	private function render_mb_styles(): void { ?>
		<style>
		/* ── Meta box wrapper ──────────────────────────────────────── */
		.pzlmb-wrap { font-size: 13px; }

		/* ── Prompt state ─────────────────────────────────────────── */
		.pzlmb-current { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
		.pzlmb-current-img {
			width: 56px; height: 56px; object-fit: cover;
			border-radius: 6px; border: 1px solid #e0e3e7;
			background: repeating-conic-gradient(#ccc 0% 25%, #fff 0% 50%) 0 0 / 10px 10px;
			flex-shrink: 0;
		}
		.pzlmb-current-label { font-size: 12px; color: #646970; }
		.pzlmb-empty { display: flex; align-items: center; gap: 8px; margin-bottom: 10px; color: #aaa; }
		.pzlmb-empty-icon { font-size: 22px !important; width: 22px !important; height: 22px !important; }
		.pzlmb-empty-label { font-size: 12px; }
		.pzlmb-open-btn {
			width: 100%;
			display: flex !important; align-items: center; justify-content: center; gap: 5px;
			font-size: 12px !important;
		}
		.pzlmb-open-btn .dashicons { font-size: 13px !important; width: 13px !important; height: 13px !important; }

		/* ── Editor state ─────────────────────────────────────────── */
		.pzlmb-source-row { display: flex; flex-direction: column; gap: 6px; margin-bottom: 8px; }
		.pzlmb-drop-zone {
			border: 2px dashed #c3c4c7; border-radius: 6px;
			padding: 10px; text-align: center; cursor: pointer;
			font-size: 12px; color: #646970;
			display: flex; align-items: center; justify-content: center; gap: 6px;
			transition: border-color .15s, background .15s, color .15s;
		}
		.pzlmb-drop-zone:hover,
		.pzlmb-drop-zone--over { border-color: #ff6b35; background: #fff5f0; color: #ff6b35; }
		.pzlmb-drop-zone .dashicons { font-size: 14px !important; width: 14px !important; height: 14px !important; }
		.pzlmb-media-btn { width: 100%; display: flex !important; align-items: center; justify-content: center; gap: 5px; font-size: 12px !important; }
		.pzlmb-media-btn .dashicons { font-size: 13px !important; width: 13px !important; height: 13px !important; }

		/* ── Canvas stage ─────────────────────────────────────────── */
		.pzlmb-stage {
			position: relative; background: #1a1a2e; border-radius: 8px;
			min-height: 130px; margin-bottom: 8px; overflow: hidden;
			display: flex; align-items: center; justify-content: center;
		}
		.pzlmb-canvas-empty { text-align: center; color: #555; font-size: 12px; padding: 16px; }
		.pzlmb-canvas-empty .dashicons { font-size: 28px !important; width: 28px !important; height: 28px !important; display: block; margin: 0 auto 6px; }
		.pzlmb-canvas-empty p { margin: 4px 0 0; }
		.pzlmb-canvas { display: block; max-width: 100%; max-height: 180px; }

		/* ── Toolbar ──────────────────────────────────────────────── */
		.pzlmb-toolbar {
			display: flex; align-items: center; gap: 4px;
			margin-bottom: 8px; flex-wrap: wrap;
		}
		.pzlmb-tbtn {
			background: #f6f7f7; border: 1px solid #c3c4c7; border-radius: 4px;
			padding: 3px 7px; font-size: 13px; cursor: pointer; line-height: 1.4;
			transition: background .12s, border-color .12s;
		}
		.pzlmb-tbtn:hover { background: #fff5f0; border-color: #ff6b35; color: #ff6b35; }
		.pzlmb-toolbar-sep { flex: 1; }
		.pzlmb-tbtn-label { font-size: 11px; color: #646970; display: flex; align-items: center; gap: 3px; cursor: pointer; }

		/* ── Adjustments ──────────────────────────────────────────── */
		.pzlmb-adj { margin-bottom: 10px; }
		.pzlmb-adj-row {
			display: grid; grid-template-columns: 46px 1fr 28px;
			align-items: center; gap: 5px; margin-bottom: 4px;
		}
		.pzlmb-adj-label { font-size: 11px; color: #646970; white-space: nowrap; }
		.pzlmb-slider { width: 100%; accent-color: #ff6b35; cursor: pointer; }
		.pzlmb-adj-val { font-size: 11px; color: #1d2023; font-weight: 600; text-align: right; }
		.pzlmb-reset-adj { font-size: 11px; color: #646970; padding: 0; cursor: pointer; }
		.pzlmb-reset-adj:hover { color: #ff6b35; }

		/* ── Actions ──────────────────────────────────────────────── */
		.pzlmb-actions { display: flex; flex-direction: column; gap: 5px; }
		.pzlmb-set-btn {
			width: 100%; display: flex !important; align-items: center;
			justify-content: center; gap: 5px; font-size: 12px !important;
		}
		.pzlmb-set-btn .dashicons { font-size: 13px !important; width: 13px !important; height: 13px !important; }
		.pzlmb-cancel-btn { width: 100%; font-size: 12px !important; }
		.pzlmb-status { font-size: 11px; margin: 5px 0 0; min-height: 16px; }
		.pzlmb-status--ok  { color: #00a32a; }
		.pzlmb-status--err { color: #d63638; }
		</style>
	<?php }

	private function render_mb_script( string $slug ): void {
		$wrap_id   = 'pzlmb-wrap-'        . esc_js( $slug );
		$prompt_id = 'pzlmb-prompt-'      . esc_js( $slug );
		$editor_id = 'pzlmb-editor-'      . esc_js( $slug );
		$open_id   = 'pzlmb-open-btn-'    . esc_js( $slug );
		$stage_id  = 'pzlmb-stage-'       . esc_js( $slug );
		$empty_id  = 'pzlmb-canvas-empty-'. esc_js( $slug );
		$status_id = 'pzlmb-status-'      . esc_js( $slug );
		$curr_img  = 'pzlmb-current-img-' . esc_js( $slug );
		?>
		<?php
		// JS enqueued via wp_enqueue_script( 'pizzalayer-layer-image-metabox' )
		// $wrap element supplies all IDs/nonces via data-* attributes
		?>
		<?php
	}
}
