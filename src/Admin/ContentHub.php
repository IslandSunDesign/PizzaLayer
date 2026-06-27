<?php
namespace PizzaLayer\Admin;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * PizzaLayer Content Hub
 *
 * Single admin page that provides access to all 8 CPTs without cluttering
 * the WordPress sidebar. Uses a vertical left-rail tab design with an
 * embedded view for each CPT — no page navigation needed.
 *
 * Two view modes:
 *  - "list" — the native WP_Posts_List_Table (sortable, searchable, paginated)
 *  - "grid" — image-thumbnail cards, one per layer, for a visual overview
 *
 * Custom columns:
 *  - Each CPT exposes its real custom fields (calories, spice level, dietary
 *    flags, diameter, thickness, sort order, slug, description, ID) as optional
 *    columns. Most are hidden by default; the user toggles them via the
 *    "Columns" dropdown and the choice is stored per-CPT in user meta.
 *
 * Bulk actions (trash / restore / delete) are handled on `admin_init`
 * (before any output) so the post-action redirect can fire cleanly.
 */
class ContentHub {

	/** User-meta key: selected view mode ('list' | 'grid'). */
	private const META_VIEW = 'pizzalayer_hub_view';

	/** User-meta key: per-CPT enabled custom columns (JSON map). */
	private const META_COLS = 'pizzalayer_hub_cols';

	/** All managed CPT slugs in display order. */
	private const CPTS = [
		'toppings' => [
			'label'    => 'Toppings',
			'singular' => 'Topping',
			'icon'     => 'dashicons-carrot',
			'color'    => '#f0b849',
			'desc'     => 'Layer images placed on top of cheese. Supports whole, half, and quarter coverage.',
		],
		'crusts' => [
			'label'    => 'Crusts',
			'singular' => 'Crust',
			'icon'     => 'dashicons-admin-generic',
			'color'    => '#c8956c',
			'desc'     => 'The base canvas. Each crust gets a layer image that anchors the pizza stack.',
		],
		'sauces' => [
			'label'    => 'Sauces',
			'singular' => 'Sauce',
			'icon'     => 'dashicons-food',
			'color'    => '#d63638',
			'desc'     => 'Applied on top of the crust. Semi-transparent edges create a natural blend.',
		],
		'cheeses' => [
			'label'    => 'Cheeses',
			'singular' => 'Cheese',
			'icon'     => 'dashicons-category',
			'color'    => '#dba633',
			'desc'     => 'Sits between sauce and toppings in the visual stack.',
		],
		'drizzles' => [
			'label'    => 'Drizzles',
			'singular' => 'Drizzle',
			'icon'     => 'dashicons-admin-customizer',
			'color'    => '#00a32a',
			'desc'     => 'Finishing touches — balsamic, hot honey, ranch. Layer above toppings.',
		],
		'cuts' => [
			'label'    => 'Cuts',
			'singular' => 'Cut',
			'icon'     => 'dashicons-editor-table',
			'color'    => '#2271b1',
			'desc'     => 'Slicing overlays: triangle cuts, square cuts, party-style, etc.',
		],
		'sizes' => [
			'label'    => 'Sizes',
			'singular' => 'Size',
			'icon'     => 'dashicons-image-rotate',
			'color'    => '#8c5af8',
			'desc'     => 'Dimension options (small / medium / large) with area and pricing metadata.',
		],

		'presets' => [
			'label'    => 'Presets',
			'singular' => 'Preset',
			'icon'     => 'dashicons-food',
			'color'    => '#e8692a',
			'desc'     => 'Pre-configured pizza combinations (crust + sauce + cheese + toppings) customers can start from.',
		],

	];

	/**
	 * Custom column registry.
	 *
	 * key => [
	 *   label    : column header,
	 *   types    : which CPT slugs the column applies to ([] = all),
	 *   default  : shown by default?  (keep MOST false — "hidden unless important"),
	 * ]
	 *
	 * Rendering for each key is handled in render_custom_cell().
	 */
	private function column_registry(): array {
		return [
			'sort_order'      => [ 'label' => 'Order',       'types' => [], 'default' => true  ],
			'dietary'         => [ 'label' => 'Dietary',     'types' => [ 'toppings', 'crusts', 'sauces', 'cheeses', 'drizzles' ], 'default' => true  ],
			'diameter_inches' => [ 'label' => 'Diameter',    'types' => [ 'sizes' ],     'default' => true  ],
			'calories'        => [ 'label' => 'Calories',    'types' => [ 'toppings' ],  'default' => false ],
			'spice_level'     => [ 'label' => 'Spice',       'types' => [ 'sauces', 'drizzles' ], 'default' => false ],
			'thickness'       => [ 'label' => 'Thickness',   'types' => [ 'crusts' ],    'default' => false ],
			'ingredients'     => [ 'label' => 'Ingredients', 'types' => [ 'toppings', 'crusts', 'sauces', 'cheeses', 'drizzles' ], 'default' => false ],
			'slug'            => [ 'label' => 'Slug',        'types' => [], 'default' => false ],
			'description'     => [ 'label' => 'Description', 'types' => [], 'default' => false ],
			'id'              => [ 'label' => 'ID',          'types' => [], 'default' => false ],
		];
	}

	/** Columns available for a given CPT, in registry order. */
	private function columns_for( string $slug ): array {
		$out = [];
		foreach ( $this->column_registry() as $key => $def ) {
			if ( empty( $def['types'] ) || in_array( $slug, $def['types'], true ) ) {
				$out[ $key ] = $def;
			}
		}
		return $out;
	}

	/** Default-enabled column keys for a CPT. */
	private function default_columns( string $slug ): array {
		$keys = [];
		foreach ( $this->columns_for( $slug ) as $key => $def ) {
			if ( ! empty( $def['default'] ) ) { $keys[] = $key; }
		}
		return $keys;
	}

	/** Resolve the current user's enabled columns for a CPT (falls back to defaults). */
	private function enabled_columns( string $slug ): array {
		$all   = $this->columns_for( $slug );
		$store = get_user_meta( get_current_user_id(), self::META_COLS, true );
		$store = is_array( $store ) ? $store : [];

		if ( ! array_key_exists( $slug, $store ) ) {
			return $this->default_columns( $slug );
		}
		// Keep only keys that are still valid for this CPT, preserve registry order.
		$saved = is_array( $store[ $slug ] ) ? $store[ $slug ] : [];
		$out   = [];
		foreach ( array_keys( $all ) as $key ) {
			if ( in_array( $key, $saved, true ) ) { $out[] = $key; }
		}
		return $out;
	}

	/** Resolve the current user's view mode ('list' | 'grid'). */
	private function current_view(): string {
		$v = (string) get_user_meta( get_current_user_id(), self::META_VIEW, true );
		return ( $v === 'grid' ) ? 'grid' : 'list';
	}

	/** Register AJAX handlers (called from Plugin.php). */
	public function register_ajax(): void {
		add_action( 'wp_ajax_pizzalayer_content_panel', [ $this, 'ajax_panel' ] );
	}

	/* ─────────────────────────────────────────────────────────────────────
	 * Bulk action handling — runs on admin_init (before any output) so the
	 * redirect after delete/trash/restore fires cleanly. Replaces the old
	 * (broken) call to WP_Posts_List_Table::process_bulk_action(), which is
	 * not a real method and fataled on every bulk submit.
	 * ──────────────────────────────────────────────────────────────────── */
	public function maybe_handle_bulk(): void {
		// Only on the Content Hub screen.
		$page = isset( $_REQUEST['page'] ) ? sanitize_key( wp_unslash( $_REQUEST['page'] ) ) : '';
		if ( $page !== 'pizzalayer-content' ) { return; }

		// Resolve the requested bulk action from either dropdown.
		$action = '';
		if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] !== '-1' ) {
			$action = sanitize_key( wp_unslash( $_REQUEST['action'] ) );
		}
		if ( ( $action === '' || $action === '-1' ) && isset( $_REQUEST['action2'] ) && $_REQUEST['action2'] !== '-1' ) {
			$action = sanitize_key( wp_unslash( $_REQUEST['action2'] ) );
		}

		// We only handle delete/trash/restore here; everything else falls through.
		$known = [ 'trash', 'untrash', 'restore', 'delete' ];
		if ( ! in_array( $action, $known, true ) ) { return; }

		if ( ! current_user_can( 'manage_options' ) ) { return; }
		check_admin_referer( 'bulk-posts' );

		$slug = isset( $_REQUEST['pl_cpt'] ) ? sanitize_key( wp_unslash( $_REQUEST['pl_cpt'] ) ) : 'toppings';
		if ( ! array_key_exists( $slug, self::CPTS ) ) { $slug = 'toppings'; }
		$cpt = 'pizzalayer_' . $slug;

		$ids = [];
		if ( isset( $_REQUEST['post'] ) ) {
			$ids = array_map( 'intval', (array) wp_unslash( $_REQUEST['post'] ) );
		}
		$ids = array_filter( $ids );

		$done = 0;
		foreach ( $ids as $id ) {
			if ( get_post_type( $id ) !== $cpt ) { continue; }
			switch ( $action ) {
				case 'trash':
					if ( current_user_can( 'delete_post', $id ) && wp_trash_post( $id ) ) { $done++; }
					break;
				case 'untrash':
				case 'restore':
					if ( current_user_can( 'delete_post', $id ) && wp_untrash_post( $id ) ) { $done++; }
					break;
				case 'delete':
					if ( current_user_can( 'delete_post', $id ) && wp_delete_post( $id, true ) ) { $done++; }
					break;
			}
		}

		$args = [
			'page'         => 'pizzalayer-content',
			'pl_cpt'       => $slug,
			'plch_done'    => $done,
			'plch_action'  => $action,
		];
		// Preserve search / paging / status / sort context across the redirect.
		foreach ( [ 's', 'paged', 'post_status', 'orderby', 'order' ] as $k ) {
			if ( isset( $_REQUEST[ $k ] ) && $_REQUEST[ $k ] !== '' ) {
				$args[ $k ] = sanitize_text_field( wp_unslash( $_REQUEST[ $k ] ) );
			}
		}

		$redirect = add_query_arg( $args, admin_url( 'admin.php' ) );
		wp_safe_redirect( $redirect );
		exit;
	}

	/** Admin notice after a bulk action completes. */
	private function maybe_render_bulk_notice(): void {
		if ( ! isset( $_GET['plch_done'], $_GET['plch_action'] ) ) { return; } // phpcs:ignore WordPress.Security.NonceVerification
		$done   = (int) $_GET['plch_done']; // phpcs:ignore WordPress.Security.NonceVerification
		$action = sanitize_key( wp_unslash( $_GET['plch_action'] ) ); // phpcs:ignore WordPress.Security.NonceVerification

		$verb = [
			'trash'   => __( 'moved to Trash', 'pizzalayer' ),
			'untrash' => __( 'restored', 'pizzalayer' ),
			'restore' => __( 'restored', 'pizzalayer' ),
			'delete'  => __( 'permanently deleted', 'pizzalayer' ),
		];
		$msg_verb = $verb[ $action ] ?? __( 'updated', 'pizzalayer' );

		if ( $done > 0 ) {
			$text = sprintf(
				/* translators: 1: number of items, 2: action verb. */
				_n( '%1$d item %2$s.', '%1$d items %2$s.', $done, 'pizzalayer' ),
				$done,
				$msg_verb
			);
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $text ) . '</p></div>';
		} else {
			echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html__( 'No items were changed.', 'pizzalayer' ) . '</p></div>';
		}
	}

	/* ─────────────────────────────────────────────────────────────────────
	 * AJAX: return the panel HTML for a given CPT (and optionally persist the
	 * view mode / column selection the user just changed).
	 * ──────────────────────────────────────────────────────────────────── */
	public function ajax_panel(): void {
		check_ajax_referer( 'pizzalayer_content_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) { wp_die( -1 ); }

		$slug = isset( $_POST['cpt'] ) ? sanitize_key( wp_unslash( $_POST['cpt'] ) ) : 'toppings';
		if ( ! array_key_exists( $slug, self::CPTS ) ) { $slug = 'toppings'; }

		$uid = get_current_user_id();

		// Persist view mode if supplied.
		if ( isset( $_POST['view'] ) ) {
			$view = ( sanitize_key( wp_unslash( $_POST['view'] ) ) === 'grid' ) ? 'grid' : 'list';
			update_user_meta( $uid, self::META_VIEW, $view );
		}

		// Persist column selection for this CPT if supplied.
		if ( isset( $_POST['cols'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- JSON payload; each decoded key is sanitize_key()'d and validated against an allowlist below.
			$raw = json_decode( wp_unslash( $_POST['cols'] ), true );
			$valid_keys = array_keys( $this->columns_for( $slug ) );
			$clean = [];
			if ( is_array( $raw ) ) {
				foreach ( $raw as $k ) {
					$k = sanitize_key( $k );
					if ( in_array( $k, $valid_keys, true ) ) { $clean[] = $k; }
				}
			}
			$store = get_user_meta( $uid, self::META_COLS, true );
			$store = is_array( $store ) ? $store : [];
			$store[ $slug ] = array_values( array_unique( $clean ) );
			update_user_meta( $uid, self::META_COLS, $store );
		}

		// Pass through search/orderby/order for the list table / grid query.
		$_GET['post_type']  = 'pizzalayer_' . $slug;
		$_POST['post_type'] = 'pizzalayer_' . $slug;

		ob_start();
		$this->render_panel_inner( $slug );
		$html = ob_get_clean();

		wp_send_json_success( [
			'html' => $html,
			'slug' => $slug,
			'view' => $this->current_view(),
			'cols' => $this->enabled_columns( $slug ),
		] );
	}

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) { return; }

		// Active CPT slug from query param — default to 'toppings'
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only CPT tab selector; sanitized and validated against self::CPTS below, no state change.
		$active_slug = isset( $_GET['pl_cpt'] ) ? sanitize_key( wp_unslash( $_GET['pl_cpt'] ) ) : 'toppings';

		if ( ! array_key_exists( $active_slug, self::CPTS ) ) {
			$active_slug = 'toppings';
		}

		$active_cpt  = 'pizzalayer_' . $active_slug;
		$active_meta = self::CPTS[ $active_slug ];

		// Count all CPTs for badges
		$counts = [];
		foreach ( array_keys( self::CPTS ) as $slug ) {
			$c = wp_count_posts( 'pizzalayer_' . $slug );
			$counts[ $slug ] = (int) ( $c->publish ?? 0 );
		}

		// Prepare initial list table for the active CPT (list mode only)
		$_GET['post_type']  = $active_cpt;
		$_POST['post_type'] = $active_cpt;

		$list_table = null;
		if ( $this->current_view() === 'list' ) {
			$list_table = $this->get_list_table( $active_cpt );
			$list_table->prepare_items();
		}

		$hub_url = admin_url( 'admin.php?page=pizzalayer-content' );

		?>
		<div class="wrap plch-wrap">
		<?php $this->render_styles(); ?>
		<?php $this->maybe_render_bulk_notice(); ?>

		<!-- ══ Header ═══════════════════════════════════════════════════ -->
		<div class="plch-header" id="plch-header">
			<span class="dashicons <?php echo esc_attr( $active_meta['icon'] ); ?> plch-header__icon" id="plch-header-icon"
			      style="color:<?php echo esc_attr( $active_meta['color'] ); ?>;font-size:32px;width:32px;height:32px;flex-shrink:0;"></span>
			<div class="plch-header__left">
				<h1 class="plch-header__title" id="plch-header-title">
					<span id="plch-header-label"><?php echo esc_html( $active_meta['label'] ); ?></span>
				</h1>
				<p class="plch-header__desc" id="plch-header-desc"><?php echo esc_html( $active_meta['desc'] ); ?></p>
			</div>
			<div class="plch-header__actions">
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=' . $active_cpt ) ); ?>"
				   class="button plch-wp-list-btn" id="plch-wp-list-btn" title="<?php esc_attr_e( 'Open WordPress list', 'pizzalayer' ); ?>"
				   style="background:rgba(255,255,255,.15);border-color:rgba(255,255,255,.3);color:#fff;">
					<span class="dashicons dashicons-list-view"></span>
					<span id="plch-wp-list-label"><?php esc_html_e( 'WP List', 'pizzalayer' ); ?></span>
				</a>
				<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . $active_cpt ) ); ?>"
				   class="button button-primary plch-add-btn" id="plch-add-btn">
					<span class="dashicons dashicons-plus-alt2"></span>
					<?php esc_html_e( 'Add New', 'pizzalayer' ); ?> <span id="plch-add-singular"><?php echo esc_html( $active_meta['singular'] ); ?></span>
				</a>
			</div>
		</div>

		<!-- ══ Layout: left rail + main ════════════════════════════════ -->
		<div class="plch-layout">

			<!-- Left vertical tab rail — instant JS switching -->
			<nav class="plch-rail" aria-label="Layer Types">
				<?php foreach ( self::CPTS as $slug => $meta ) :
					$is_active  = ( $slug === $active_slug );
					$count      = $counts[ $slug ];
					$zero_class = $count === 0 ? ' plch-rail__count--zero' : '';
					$cpt_data   = wp_json_encode( [
						'slug'     => $slug,
						'label'    => $meta['label'],
						'singular' => $meta['singular'],
						'icon'     => $meta['icon'],
						'color'    => $meta['color'],
						'desc'     => $meta['desc'],
						'addUrl'    => admin_url( 'post-new.php?post_type=pizzalayer_' . $slug ),
					'wpListUrl' => admin_url( 'edit.php?post_type=pizzalayer_' . $slug ),
					] );
				?>
				<a href="<?php echo esc_url( add_query_arg( 'pl_cpt', $slug, $hub_url ) ); ?>"
				   class="plch-rail__item<?php echo $is_active ? ' plch-rail__item--active' : ''; ?>"
				   data-slug="<?php echo esc_attr( $slug ); ?>"
				   data-cpt='<?php echo esc_attr( $cpt_data ); ?>'
				   aria-current="<?php echo $is_active ? 'page' : 'false'; ?>">

					<span class="plch-rail__icon"
					      style="<?php echo $is_active ? 'background:' . esc_attr( $meta['color'] ) . '20;color:' . esc_attr( $meta['color'] ) . ';' : ''; ?>">
						<span class="dashicons <?php echo esc_attr( $meta['icon'] ); ?>"></span>
					</span>

					<span class="plch-rail__label"><?php echo esc_html( $meta['label'] ); ?></span>

					<span class="plch-rail__count<?php echo esc_attr( $zero_class ); ?>" data-count="<?php echo esc_attr( $slug ); ?>">
						<?php echo esc_html( $count ); ?>
					</span>

					<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=pizzalayer_' . $slug ) ); ?>"
					   class="plch-rail__add"
					   title="Add New <?php echo esc_attr( $meta['singular'] ); ?>"
					   onclick="event.stopPropagation();">
						<span class="dashicons dashicons-plus"></span>
					</a>
				</a>
				<?php endforeach; ?>
			</nav>

			<!-- Main content: panel area, content swaps via AJAX -->
			<main class="plch-main" id="plch-main">

				<!-- Loading indicator -->
				<div class="plch-loading" id="plch-loading" style="display:none;">
					<div class="plch-spinner"></div>
					<span>Loading…</span>
				</div>

				<!-- Panel content (initially server-rendered, then swapped by JS) -->
				<div id="plch-panel-content">
					<?php $this->render_panel_inner( $active_slug, $list_table ); ?>
				</div>

			</main>
		</div><!-- /.plch-layout -->
		</div><!-- /.plch-wrap -->

		<?php
	}

	/**
	 * Render the inner panel content: toolbar + (list table | grid).
	 * Can receive a pre-built list_table to avoid re-querying.
	 */
	private function render_panel_inner( string $active_slug, ?\WP_Posts_List_Table $list_table = null ): void {
		$active_cpt  = 'pizzalayer_' . $active_slug;
		$active_meta = self::CPTS[ $active_slug ];
		$hub_url     = admin_url( 'admin.php?page=pizzalayer-content' );
		$view        = $this->current_view();
		$enabled     = $this->enabled_columns( $active_slug );

		// ── Toolbar: view toggle + columns dropdown ──────────────────
		$this->render_toolbar( $active_slug, $view, $enabled );

		if ( $view === 'grid' ) {
			$this->render_grid( $active_slug, $enabled );
			return;
		}

		// ── LIST MODE ────────────────────────────────────────────────
		if ( $list_table === null ) {
			$_GET['post_type']  = $active_cpt;
			$_POST['post_type'] = $active_cpt;
			$list_table = $this->get_list_table( $active_cpt );
			$list_table->prepare_items();
		}

		// Register custom + thumbnail columns for this CPT before display().
		$this->register_list_columns( $active_slug, $enabled );

		?>
		<!-- Search form -->
		<form id="plch-search-form" method="get" action="<?php echo esc_url( $hub_url ); ?>">
			<input type="hidden" name="page"   value="pizzalayer-content">
			<input type="hidden" name="pl_cpt" value="<?php echo esc_attr( $active_slug ); ?>">
			<?php $list_table->search_box( 'Search ' . $active_meta['label'], 'pizzalayer-content-search' ); ?>
		</form>

		<!-- Bulk actions form -->
		<form id="plch-bulk-form" method="post" action="<?php echo esc_url( $hub_url ); ?>">
			<input type="hidden" name="page"      value="pizzalayer-content">
			<input type="hidden" name="pl_cpt"    value="<?php echo esc_attr( $active_slug ); ?>">
			<input type="hidden" name="post_type" value="<?php echo esc_attr( $active_cpt ); ?>">
			<?php wp_nonce_field( 'bulk-posts' ); ?>
			<?php $list_table->display(); ?>
		</form>
		<?php
	}

	/* ─────────────────────────────────────────────────────────────────────
	 * Toolbar (view toggle + columns chooser)
	 * ──────────────────────────────────────────────────────────────────── */
	private function render_toolbar( string $slug, string $view, array $enabled ): void {
		$cols = $this->columns_for( $slug );
		?>
		<div class="plch-toolbar" data-slug="<?php echo esc_attr( $slug ); ?>">
			<div class="plch-viewtoggle" role="group" aria-label="<?php esc_attr_e( 'View mode', 'pizzalayer' ); ?>">
				<button type="button" class="plch-vbtn<?php echo $view === 'list' ? ' is-active' : ''; ?>" data-view="list" aria-pressed="<?php echo $view === 'list' ? 'true' : 'false'; ?>">
					<span class="dashicons dashicons-list-view"></span> <?php esc_html_e( 'List', 'pizzalayer' ); ?>
				</button>
				<button type="button" class="plch-vbtn<?php echo $view === 'grid' ? ' is-active' : ''; ?>" data-view="grid" aria-pressed="<?php echo $view === 'grid' ? 'true' : 'false'; ?>">
					<span class="dashicons dashicons-grid-view"></span> <?php esc_html_e( 'Grid', 'pizzalayer' ); ?>
				</button>
			</div>

			<?php if ( ! empty( $cols ) ) : ?>
			<div class="plch-colmenu">
				<button type="button" class="button plch-colmenu__btn" aria-expanded="false">
					<span class="dashicons dashicons-columns"></span>
					<?php esc_html_e( 'Columns', 'pizzalayer' ); ?>
					<span class="dashicons dashicons-arrow-down-alt2"></span>
				</button>
				<div class="plch-colmenu__pop" hidden>
					<p class="plch-colmenu__hint"><?php esc_html_e( 'Show extra columns', 'pizzalayer' ); ?></p>
					<?php foreach ( $cols as $key => $def ) :
						$checked = in_array( $key, $enabled, true ); ?>
						<label class="plch-colmenu__row">
							<input type="checkbox" class="plch-col-cb" data-col="<?php echo esc_attr( $key ); ?>" <?php checked( $checked ); ?>>
							<span><?php echo esc_html( $def['label'] ); ?></span>
						</label>
					<?php endforeach; ?>
				</div>
			</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/* ─────────────────────────────────────────────────────────────────────
	 * GRID MODE — image-thumbnail cards
	 * ──────────────────────────────────────────────────────────────────── */
	private function render_grid( string $slug, array $enabled ): void {
		$cpt         = 'pizzalayer_' . $slug;
		$active_meta = self::CPTS[ $slug ];
		$hub_url     = admin_url( 'admin.php?page=pizzalayer-content' );

		$paged   = isset( $_REQUEST['paged'] ) ? max( 1, (int) $_REQUEST['paged'] ) : 1; // phpcs:ignore WordPress.Security.NonceVerification
		$search  = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$status  = isset( $_REQUEST['post_status'] ) ? sanitize_key( wp_unslash( $_REQUEST['post_status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$per_page = 24;

		$args = [
			'post_type'      => $cpt,
			'posts_per_page' => $per_page,
			'paged'          => $paged,
			'post_status'    => $status ? $status : [ 'publish', 'draft', 'pending', 'private' ],
			'orderby'        => 'title',
			'order'          => 'ASC',
			's'              => $search,
		];
		$q = new \WP_Query( $args );

		?>
		<!-- Search form (grid) -->
		<form id="plch-search-form" method="get" action="<?php echo esc_url( $hub_url ); ?>" class="plch-grid-search">
			<input type="hidden" name="page"   value="pizzalayer-content">
			<input type="hidden" name="pl_cpt" value="<?php echo esc_attr( $slug ); ?>">
			<p class="search-box">
				<label class="screen-reader-text" for="plch-grid-search-input"><?php echo esc_html( 'Search ' . $active_meta['label'] ); ?></label>
				<input type="search" id="plch-grid-search-input" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php echo esc_attr( 'Search ' . $active_meta['label'] ); ?>">
				<button type="submit" class="button"><?php esc_html_e( 'Search', 'pizzalayer' ); ?></button>
			</p>
		</form>

		<form id="plch-bulk-form" method="post" action="<?php echo esc_url( $hub_url ); ?>">
			<input type="hidden" name="page"      value="pizzalayer-content">
			<input type="hidden" name="pl_cpt"    value="<?php echo esc_attr( $slug ); ?>">
			<input type="hidden" name="post_type" value="<?php echo esc_attr( $cpt ); ?>">
			<?php if ( $search ) : ?><input type="hidden" name="s" value="<?php echo esc_attr( $search ); ?>"><?php endif; ?>
			<?php wp_nonce_field( 'bulk-posts' ); ?>

			<!-- Grid bulk toolbar -->
			<div class="plch-grid-bulkbar">
				<label class="plch-grid-selectall">
					<input type="checkbox" id="plch-grid-checkall"> <?php esc_html_e( 'Select all', 'pizzalayer' ); ?>
				</label>
				<select name="action" aria-label="<?php esc_attr_e( 'Bulk action', 'pizzalayer' ); ?>">
					<option value="-1"><?php esc_html_e( 'Bulk actions', 'pizzalayer' ); ?></option>
					<?php if ( $status === 'trash' ) : ?>
						<option value="untrash"><?php esc_html_e( 'Restore', 'pizzalayer' ); ?></option>
						<option value="delete"><?php esc_html_e( 'Delete Permanently', 'pizzalayer' ); ?></option>
					<?php else : ?>
						<option value="trash"><?php esc_html_e( 'Move to Trash', 'pizzalayer' ); ?></option>
					<?php endif; ?>
				</select>
				<button type="submit" class="button plch-grid-apply"><?php esc_html_e( 'Apply', 'pizzalayer' ); ?></button>
				<span class="plch-grid-count"><?php echo esc_html( sprintf( /* translators: %d = number of items. */ _n( '%d item', '%d items', (int) $q->found_posts, 'pizzalayer' ), (int) $q->found_posts ) ); ?></span>
			</div>

			<?php if ( $q->have_posts() ) : ?>
			<div class="plch-grid">
				<?php while ( $q->have_posts() ) : $q->the_post();
					$pid       = get_the_ID();
					$thumb     = $this->get_thumb_url( $slug, $pid );
					$edit_link = get_edit_post_link( $pid );
					$trash_link = get_delete_post_link( $pid ); // trash (or delete if trash disabled)
					?>
					<div class="plch-card">
						<label class="plch-card__check">
							<input type="checkbox" name="post[]" value="<?php echo esc_attr( $pid ); ?>">
						</label>

						<a class="plch-card__thumb" href="<?php echo esc_url( $edit_link ); ?>">
							<?php if ( $thumb ) : ?>
								<img src="<?php echo esc_url( $thumb ); ?>" alt="" loading="lazy">
							<?php else : ?>
								<span class="plch-card__placeholder" style="color:<?php echo esc_attr( $active_meta['color'] ); ?>;">
									<span class="dashicons <?php echo esc_attr( $active_meta['icon'] ); ?>"></span>
								</span>
							<?php endif; ?>
						</a>

						<div class="plch-card__body">
							<a class="plch-card__title" href="<?php echo esc_url( $edit_link ); ?>">
								<?php echo esc_html( get_the_title() ? get_the_title() : __( '(no title)', 'pizzalayer' ) ); ?>
							</a>

							<?php
							$meta_bits = $this->card_meta_bits( $slug, $pid, $enabled );
							if ( $meta_bits ) :
								?>
								<div class="plch-card__meta"><?php echo wp_kses_post( implode( '', $meta_bits ) ); ?></div>
							<?php endif; ?>

							<div class="plch-card__actions">
								<a href="<?php echo esc_url( $edit_link ); ?>"><?php esc_html_e( 'Edit', 'pizzalayer' ); ?></a>
								<?php if ( $trash_link ) : ?>
									<span aria-hidden="true">·</span>
									<a class="plch-card__trash" href="<?php echo esc_url( $trash_link ); ?>"><?php esc_html_e( 'Trash', 'pizzalayer' ); ?></a>
								<?php endif; ?>
							</div>
						</div>
					</div>
				<?php endwhile; ?>
			</div>

			<?php
			// ── Pager ──
			$total_pages = (int) $q->max_num_pages;
			if ( $total_pages > 1 ) :
				$base = add_query_arg( [ 'page' => 'pizzalayer-content', 'pl_cpt' => $slug ], $hub_url );
				if ( $search ) { $base = add_query_arg( 's', $search, $base ); }
				?>
				<div class="plch-grid-pager tablenav-pages">
					<span class="displaying-num"><?php echo esc_html( sprintf( /* translators: %d = number of items. */ _n( '%d item', '%d items', (int) $q->found_posts, 'pizzalayer' ), (int) $q->found_posts ) ); ?></span>
					<span class="pagination-links">
						<?php
						for ( $i = 1; $i <= $total_pages; $i++ ) {
							$url = esc_url( add_query_arg( 'paged', $i, $base ) );
							if ( $i === $paged ) {
								echo '<span class="tablenav-pages-navspan button disabled" aria-current="page">' . esc_html( $i ) . '</span> ';
							} else {
								echo '<a class="button" href="' . esc_url( $url ) . '">' . esc_html( $i ) . '</a> ';
							}
						}
						?>
					</span>
				</div>
			<?php endif; ?>

			<?php else : ?>
				<div class="plch-grid-empty">
					<span class="dashicons <?php echo esc_attr( $active_meta['icon'] ); ?>" style="color:<?php echo esc_attr( $active_meta['color'] ); ?>;"></span>
					<p>
						<?php
						echo $search
							? esc_html( sprintf( /* translators: %s = content type label. */ __( 'No %s match your search.', 'pizzalayer' ), strtolower( $active_meta['label'] ) ) )
							: esc_html( sprintf( /* translators: %s = content type label. */ __( 'No %s yet.', 'pizzalayer' ), strtolower( $active_meta['label'] ) ) );
						?>
					</p>
					<a class="button button-primary" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . $cpt ) ); ?>">
						<?php echo esc_html( sprintf( /* translators: %s = content type name. */ __( 'Add %s', 'pizzalayer' ), $active_meta['singular'] ) ); ?>
					</a>
				</div>
			<?php endif; ?>
		</form>
		<?php
		wp_reset_postdata();
	}

	/** Build the small meta chips shown on a grid card, honoring enabled columns. */
	private function card_meta_bits( string $slug, int $pid, array $enabled ): array {
		$bits = [];
		foreach ( $enabled as $key ) {
			$html = $this->render_custom_value( $key, $slug, $pid, true );
			if ( $html === '' ) { continue; }
			$label = $this->column_registry()[ $key ]['label'] ?? $key;
			if ( $key === 'dietary' ) {
				$bits[] = '<span class="plch-chip plch-chip--diet">' . $html . '</span>';
			} else {
				$bits[] = '<span class="plch-chip"><em>' . esc_html( $label ) . ':</em> ' . $html . '</span>';
			}
		}
		return $bits;
	}

	/* ─────────────────────────────────────────────────────────────────────
	 * LIST MODE — column registration + rendering
	 * ──────────────────────────────────────────────────────────────────── */
	private function register_list_columns( string $active_slug, array $enabled ): void {
		$active_cpt = 'pizzalayer_' . $active_slug;
		$has_images = ! in_array( $active_slug, [ 'sizes', 'presets' ], true );

		$col_filter = 'manage_' . $active_cpt . '_posts_columns';
		$val_action = 'manage_' . $active_cpt . '_posts_custom_column';

		add_filter( $col_filter, function ( $cols ) use ( $has_images, $enabled, $active_slug ) {
			$registry = $this->column_registry();

			// Rebuild: cb, thumb, title, [enabled customs], then native date.
			$new = [ 'cb' => $cols['cb'] ?? '<input type="checkbox">' ];
			if ( $has_images ) {
				$new['pzl_thumb'] = '<span title="Thumbnail">🖼</span>';
			}
			if ( isset( $cols['title'] ) ) {
				$new['title'] = $cols['title'];
			}
			foreach ( $enabled as $key ) {
				if ( $key === 'description' ) {
					$new['pzl_description'] = esc_html( $registry[ $key ]['label'] );
				} else {
					$new[ 'pzl_' . $key ] = esc_html( $registry[ $key ]['label'] ?? $key );
				}
			}
			// Preserve a date column at the end if present.
			if ( isset( $cols['date'] ) ) {
				$new['date'] = $cols['date'];
			}
			return $new;
		} );

		add_action( $val_action, function ( $col_name, $post_id ) use ( $has_images, $active_slug ) {
			if ( $col_name === 'pzl_thumb' && $has_images ) {
				$this->render_thumb_cell( $active_slug, (int) $post_id );
				return;
			}
			if ( strpos( $col_name, 'pzl_' ) === 0 ) {
				$key = substr( $col_name, 4 );
				echo wp_kses_post( $this->render_custom_value( $key, $active_slug, (int) $post_id, false ) );
			}
		}, 10, 2 );

		// Column widths are enqueued via wp_add_inline_style() in
		// AssetManager::enqueue_admin() (Content Hub branch) so they go out
		// through the normal stylesheet pipeline instead of an inline <style>.
	}

	/** Render the thumbnail cell (list mode) or return nothing. */
	private function render_thumb_cell( string $slug, int $post_id ): void {
		$thumb_url = $this->get_thumb_url( $slug, $post_id );
		if ( $thumb_url ) {
			echo '<img src="' . esc_url( $thumb_url ) . '" alt="" style="width:40px;height:40px;object-fit:cover;border-radius:4px;border:1px solid #e0e3e7;display:block;">';
		} else {
			echo '<span style="display:inline-flex;align-items:center;justify-content:center;width:40px;height:40px;background:#f0f0f1;border-radius:4px;color:#ccc;font-size:18px;">🍕</span>';
		}
	}

	/**
	 * Render a single custom field's value as HTML.
	 *
	 * @param bool $compact When true (grid cards) values render tighter, with no em-dash fallback.
	 */
	private function render_custom_value( string $key, string $slug, int $post_id, bool $compact ): string {
		$dash = $compact ? '' : '<span style="color:#c3c4c7;">—</span>';

		switch ( $key ) {
			case 'sort_order':
				$v = $this->read_meta( $post_id, 'sort_order' );
				return ( $v === '' ) ? $dash : esc_html( (string) (int) $v );

			case 'calories':
				$v = $this->read_meta( $post_id, 'calories' );
				return ( $v === '' ) ? $dash : esc_html( number_format_i18n( (int) $v ) . ' kcal' );

			case 'diameter_inches':
				$v = $this->read_meta( $post_id, 'diameter_inches' );
				return ( $v === '' ) ? $dash : esc_html( rtrim( rtrim( (string) (float) $v, '0' ), '.' ) . '"' );

			case 'thickness':
				$v = $this->read_meta( $post_id, 'thickness' );
				return ( $v === '' ) ? $dash : esc_html( ucfirst( (string) $v ) );

			case 'spice_level':
				$v = strtolower( (string) $this->read_meta( $post_id, 'spice_level' ) );
				if ( $v === '' ) { return $dash; }
				$map = [
					'mild'      => '🌶️ Mild',
					'medium'    => '🌶️🌶️ Medium',
					'hot'       => '🌶️🌶️🌶️ Hot',
					'extra_hot' => '🌶️🌶️🌶️🌶️ Extra Hot',
				];
				return esc_html( $map[ $v ] ?? ucfirst( $v ) );

			case 'dietary':
				$flags = [];
				if ( $this->meta_truthy( $post_id, 'is_vegetarian' ) ) { $flags[] = [ 'V',  '#3a7d2c', __( 'Vegetarian', 'pizzalayer' ) ]; }
				if ( $this->meta_truthy( $post_id, 'is_vegan' ) )      { $flags[] = [ 'VG', '#1f6b2e', __( 'Vegan', 'pizzalayer' ) ]; }
				if ( $this->meta_truthy( $post_id, 'is_gluten_free' ) ){ $flags[] = [ 'GF', '#9a6b00', __( 'Gluten-free', 'pizzalayer' ) ]; }
				if ( $this->meta_truthy( $post_id, 'is_dairy_free' ) ) { $flags[] = [ 'DF', '#0a6b8a', __( 'Dairy-free', 'pizzalayer' ) ]; }
				if ( ! $flags ) { return $dash; }
				$out = '';
				foreach ( $flags as $f ) {
					$out .= '<span class="plch-diet-badge" title="' . esc_attr( $f[2] ) . '" style="background:' . esc_attr( $f[1] ) . ';">' . esc_html( $f[0] ) . '</span>';
				}
				return $out;

			case 'slug':
				$name = get_post_field( 'post_name', $post_id );
				return $name ? '<code>' . esc_html( $name ) . '</code>' : $dash;

			case 'ingredients':
				$raw  = $this->read_meta( $post_id, 'ingredients' );
				$list = array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $raw ) ) ) );
				if ( ! $list ) { return $dash; }
				$count = count( $list );
				if ( $compact ) {
					return esc_html( sprintf( /* translators: %d = number of ingredients. */ _n( '%d ingredient', '%d ingredients', $count, 'pizzalayer' ), $count ) );
				}
				$shown = array_slice( $list, 0, 4 );
				$text  = implode( ', ', $shown );
				if ( $count > 4 ) { $text .= ' +' . ( $count - 4 ); }
				return esc_html( $text );

			case 'description':
				$txt = get_post_field( 'post_excerpt', $post_id );
				if ( ! $txt ) { $txt = get_post_field( 'post_content', $post_id ); }
				$txt = wp_strip_all_tags( (string) $txt );
				if ( $txt === '' ) { return $dash; }
				return esc_html( wp_trim_words( $txt, $compact ? 12 : 18 ) );

			case 'id':
				return esc_html( (string) $post_id );
		}
		return $dash;
	}

	/* ─────────────────────────────────────────────────────────────────────
	 * Meta + image helpers
	 * ──────────────────────────────────────────────────────────────────── */

	/** Read a layer custom field, tolerant of the storage variants in use. */
	private function read_meta( int $post_id, string $key ) {
		// Primary: prefixed meta written by the Layer Builder Wizard.
		$v = get_post_meta( $post_id, '_pizzalayer_' . $key, true );
		if ( $v !== '' && $v !== null && $v !== false ) { return $v; }
		// Secondary: un-prefixed meta / ACF field name.
		$v = get_post_meta( $post_id, $key, true );
		if ( $v !== '' && $v !== null && $v !== false ) { return $v; }
		if ( function_exists( 'get_field' ) ) {
			$f = get_field( $key, $post_id );
			if ( $f !== null && $f !== '' && $f !== false ) { return $f; }
		}
		return '';
	}

	/** Truthy test for a boolean-ish flag field. */
	private function meta_truthy( int $post_id, string $key ): bool {
		$v = $this->read_meta( $post_id, $key );
		if ( is_bool( $v ) ) { return $v; }
		$v = strtolower( trim( (string) $v ) );
		return in_array( $v, [ '1', 'true', 'yes', 'on' ], true );
	}

	/** Resolve the best available thumbnail URL for a layer. */
	private function get_thumb_url( string $slug, int $post_id ): string {
		$type = rtrim( $slug, 's' ); // toppings→topping, cheeses→cheese, etc.

		$candidates = [];
		if ( function_exists( 'get_field' ) ) {
			$candidates[] = get_field( $type . '_image', $post_id );
			$candidates[] = get_field( $type . '_layer_image', $post_id );
		}
		foreach ( [ $type . '_image', $type . '_layer_image', 'pzl_layer_image' ] as $mk ) {
			$candidates[] = get_post_meta( $post_id, $mk, true );
		}
		foreach ( $candidates as $c ) {
			if ( is_array( $c ) && ! empty( $c['url'] ) ) { return (string) $c['url']; }
			if ( is_string( $c ) && $c !== '' && strpos( $c, 'http' ) === 0 ) { return $c; }
		}

		$att = (int) get_post_meta( $post_id, '_pizzalayer_layer_image_id', true );
		if ( $att ) {
			$url = wp_get_attachment_image_url( $att, 'thumbnail' );
			if ( $url ) { return (string) $url; }
		}
		return '';
	}

	private function get_list_table( string $post_type ): \WP_Posts_List_Table {
		$screen = \WP_Screen::get( 'edit-' . $post_type );
		if ( ! class_exists( 'WP_Posts_List_Table' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
			require_once ABSPATH . 'wp-admin/includes/class-wp-posts-list-table.php';
		}
		return new \WP_Posts_List_Table( [ 'screen' => $screen ] );
	}

	private function render_styles(): void { ?>
	<style>
	/* ── Wrap ──────────────────────────────────────────────────────── */
	.plch-wrap { max-width: 1400px; }

	/* ── Header ────────────────────────────────────────────────────── */
	.plch-header {
		display: flex; align-items: center; justify-content: space-between;
		gap: 16px; flex-wrap: wrap;
		background: linear-gradient(135deg, #1a1e23 0%, #2d3748 100%);
		color: #fff; border-radius: 10px;
		padding: 20px 24px; margin-bottom: 18px;
	}
	.plch-header__icon {
		font-size: 32px !important; width: 32px !important; height: 32px !important;
		flex-shrink: 0;
	}
	.plch-header__left { flex: 1; min-width: 0; }
	.plch-header__title {
		margin: 0 0 4px; font-size: 20px; font-weight: 700; color: #fff;
		display: flex; align-items: center; gap: 10px;
	}
	.plch-header__desc { margin: 0; font-size: 13px; color: #8d97a5; }
	.plch-add-btn {
		display: inline-flex !important; align-items: center; gap: 6px;
		white-space: nowrap; transition: background .2s, border-color .2s;
	}
	.plch-add-btn .dashicons { font-size: 15px !important; width: 15px !important; height: 15px !important; margin: 0; }
	.plch-wp-list-btn {
		display: inline-flex !important; align-items: center; gap: 5px;
		white-space: nowrap;
	}
	.plch-wp-list-btn .dashicons { font-size: 14px !important; width: 14px !important; height: 14px !important; margin: 0; }

	/* ── Layout ────────────────────────────────────────────────────── */
	.plch-layout {
		display: flex; gap: 0; align-items: flex-start;
		background: #fff; border: 1px solid #e0e3e7; border-radius: 10px;
		overflow: hidden; min-height: 400px;
	}

	/* ── Left rail ─────────────────────────────────────────────────── */
	.plch-rail {
		width: 195px; flex-shrink: 0;
		background: #f8f9fa; border-right: 1px solid #e0e3e7;
		padding: 8px 0; align-self: stretch;
	}
	.plch-rail__item {
		display: flex; align-items: center; gap: 8px;
		padding: 9px 10px 9px 14px;
		text-decoration: none; color: #3c434a; font-size: 13px;
		border-left: 3px solid transparent;
		transition: background .12s, color .12s, border-color .12s;
		position: relative; cursor: pointer;
	}
	.plch-rail__item:hover { background: #eef0f2; color: #1d2023; }
	.plch-rail__item:hover .plch-rail__add { opacity: 1; pointer-events: auto; }
	.plch-rail__item--active {
		background: #fff; border-left-color: #2271b1;
		color: #2271b1; font-weight: 600;
	}
	.plch-rail__item--loading { opacity: .6; pointer-events: none; }

	.plch-rail__icon {
		width: 28px; height: 28px; border-radius: 6px; flex-shrink: 0;
		display: flex; align-items: center; justify-content: center;
		background: #eef0f2; color: #646970;
		transition: background .15s, color .15s;
	}
	.plch-rail__item--active .plch-rail__icon { background: #dce8f720; color: #2271b1; }
	.plch-rail__icon .dashicons { font-size: 15px !important; width: 15px !important; height: 15px !important; }
	.plch-rail__label { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
	.plch-rail__count {
		background: #e0e3e7; color: #646970; border-radius: 999px;
		font-size: 10px; font-weight: 700; padding: 1px 6px; min-width: 18px;
		text-align: center; flex-shrink: 0; transition: background .15s, color .15s;
	}
	.plch-rail__count--zero { background: #fce8e8; color: #d63638; }
	.plch-rail__item--active .plch-rail__count { background: #dce8f7; color: #2271b1; }
	.plch-rail__add {
		opacity: 0; pointer-events: none; flex-shrink: 0;
		width: 22px; height: 22px; border-radius: 4px;
		display: flex; align-items: center; justify-content: center;
		background: #2271b120; color: #2271b1;
		text-decoration: none; transition: opacity .12s, background .12s;
	}
	.plch-rail__add:hover { background: #2271b1; color: #fff; }
	.plch-rail__add .dashicons { font-size: 14px !important; width: 14px !important; height: 14px !important; }

	/* ── Main panel ─────────────────────────────────────────────────── */
	.plch-main { flex: 1; min-width: 0; padding: 16px 20px 20px; position: relative; }

	/* ── Loading overlay ────────────────────────────────────────────── */
	.plch-loading {
		position: absolute; inset: 0; background: rgba(255,255,255,.82);
		display: flex; align-items: center; justify-content: center; gap: 10px;
		font-size: 13px; color: #646970; z-index: 10; border-radius: 0 10px 10px 0;
		backdrop-filter: blur(2px);
	}
	.plch-spinner {
		width: 20px; height: 20px; border: 2px solid #e0e3e7;
		border-top-color: #2271b1; border-radius: 50%;
		animation: plch-spin .6s linear infinite;
	}
	@keyframes plch-spin { to { transform: rotate(360deg); } }

	/* ── Panel content fade ─────────────────────────────────────────── */
	#plch-panel-content { transition: opacity .15s; }
	#plch-panel-content.plch-fading { opacity: 0; }

	/* ── Toolbar (view toggle + columns) ────────────────────────────── */
	.plch-toolbar {
		display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
		margin: 0 0 12px;
	}
	.plch-viewtoggle { display: inline-flex; border: 1px solid #dcdcde; border-radius: 6px; overflow: hidden; background: #fff; }
	.plch-vbtn {
		display: inline-flex; align-items: center; gap: 5px;
		border: 0; background: #fff; color: #50575e; cursor: pointer;
		padding: 6px 12px; font-size: 13px; line-height: 1.4;
		border-right: 1px solid #dcdcde;
	}
	.plch-vbtn:last-child { border-right: 0; }
	.plch-vbtn:hover { background: #f0f0f1; color: #1d2023; }
	.plch-vbtn.is-active { background: #2271b1; color: #fff; }
	.plch-vbtn .dashicons { font-size: 16px; width: 16px; height: 16px; }

	.plch-colmenu { position: relative; margin-left: auto; }
	.plch-colmenu__btn { display: inline-flex !important; align-items: center; gap: 4px; }
	.plch-colmenu__btn .dashicons { font-size: 15px; width: 15px; height: 15px; }
	.plch-colmenu__pop {
		position: absolute; right: 0; top: calc(100% + 4px); z-index: 30;
		background: #fff; border: 1px solid #c3c4c7; border-radius: 6px;
		box-shadow: 0 4px 16px rgba(0,0,0,.12); padding: 8px; min-width: 190px;
	}
	.plch-colmenu__hint { margin: 2px 4px 8px; font-size: 11px; text-transform: uppercase; letter-spacing: .04em; color: #787c82; font-weight: 600; }
	.plch-colmenu__row { display: flex; align-items: center; gap: 7px; padding: 5px 6px; border-radius: 4px; font-size: 13px; cursor: pointer; }
	.plch-colmenu__row:hover { background: #f0f6fc; }

	/* ── List-mode badges ───────────────────────────────────────────── */
	.plch-diet-badge {
		display: inline-block; color: #fff; font-size: 10px; font-weight: 700;
		line-height: 1; padding: 3px 5px; border-radius: 3px; margin-right: 3px; letter-spacing: .02em;
	}

	/* ── Grid mode ──────────────────────────────────────────────────── */
	.plch-grid-search { margin-bottom: 8px; }
	.plch-grid-bulkbar {
		display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
		padding: 8px 0; margin-bottom: 12px; border-bottom: 1px solid #f0f0f1;
	}
	.plch-grid-selectall { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; color: #50575e; }
	.plch-grid-count { color: #787c82; font-size: 12px; margin-left: auto; }

	.plch-grid {
		display: grid; gap: 14px;
		grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
	}
	.plch-card {
		position: relative; border: 1px solid #e0e3e7; border-radius: 10px;
		background: #fff; overflow: hidden; display: flex; flex-direction: column;
		transition: box-shadow .15s, border-color .15s, transform .12s;
	}
	.plch-card:hover { box-shadow: 0 4px 14px rgba(0,0,0,.08); border-color: #c3c4c7; transform: translateY(-1px); }
	.plch-card__check {
		position: absolute; top: 8px; left: 8px; z-index: 3;
		background: rgba(255,255,255,.92); border-radius: 4px; padding: 2px 3px 0;
		box-shadow: 0 1px 3px rgba(0,0,0,.18);
	}
	.plch-card__check input { margin: 0; }
	.plch-card__thumb {
		display: flex; align-items: center; justify-content: center;
		aspect-ratio: 1 / 1; width: 100%;
		background:
			linear-gradient(45deg, #f6f7f7 25%, transparent 25%),
			linear-gradient(-45deg, #f6f7f7 25%, transparent 25%),
			linear-gradient(45deg, transparent 75%, #f6f7f7 75%),
			linear-gradient(-45deg, transparent 75%, #f6f7f7 75%);
		background-size: 18px 18px; background-position: 0 0, 0 9px, 9px -9px, -9px 0;
		background-color: #fbfbfc;
	}
	.plch-card__thumb img { max-width: 100%; max-height: 100%; object-fit: contain; display: block; }
	.plch-card__placeholder .dashicons { font-size: 40px; width: 40px; height: 40px; opacity: .55; }
	.plch-card__body { padding: 10px 12px 12px; display: flex; flex-direction: column; gap: 6px; flex: 1; }
	.plch-card__title { font-weight: 600; font-size: 13px; color: #1d2023; text-decoration: none; line-height: 1.3; }
	.plch-card__title:hover { color: #2271b1; }
	.plch-card__meta { display: flex; flex-wrap: wrap; gap: 4px 8px; }
	.plch-chip { font-size: 11px; color: #50575e; }
	.plch-chip em { font-style: normal; color: #787c82; }
	.plch-chip--diet { display: inline-flex; gap: 2px; }
	.plch-card__actions { margin-top: auto; font-size: 12px; }
	.plch-card__actions a { text-decoration: none; }
	.plch-card__trash { color: #b32d2e; }

	.plch-grid-pager { margin-top: 16px; text-align: right; }
	.plch-grid-pager .button { margin-left: 3px; }

	.plch-grid-empty { text-align: center; padding: 40px 20px; color: #646970; }
	.plch-grid-empty .dashicons { font-size: 42px; width: 42px; height: 42px; opacity: .6; margin-bottom: 8px; }
	.plch-grid-empty p { font-size: 14px; margin: 0 0 14px; }

	/* ── WP list table tweaks ───────────────────────────────────────── */
	.plch-main .wp-list-table { border: 1px solid #e0e3e7; border-radius: 6px; overflow: hidden; }
	.plch-main .tablenav { margin: 6px 0; }
	.plch-main .search-box { margin-bottom: 8px; }
	.plch-main .wp-list-table th { background: #f8f9fa; }
	.plch-main .column-title { width: 35%; }
	.plch-main .wp-list-table th a { white-space: nowrap; }
	</style>
	<?php }

}
