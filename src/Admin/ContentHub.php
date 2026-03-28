<?php
namespace PizzaLayer\Admin;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * PizzaLayer Content Hub
 *
 * Single admin page that provides access to all 8 CPTs without cluttering
 * the WordPress sidebar. Uses a vertical left-rail tab design with an
 * embedded WP_List_Table view for each CPT — no page navigation needed.
 *
 * Architecture:
 *  - Vertical tab nav (left rail) with type, count badge, and Add New button
 *  - Main area switches content via AJAX (no page reload) OR query param fallback
 *  - Tab panels are lazy-loaded on first click, then cached in DOM
 *  - All native WP bulk actions, search, and pagination still work
 *  - Add New button goes to the native post-new.php screen
 */
class ContentHub {

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
		'pizzas' => [
			'label'    => 'Presets',
			'singular' => 'Preset',
			'icon'     => 'dashicons-pizza',
			'color'    => '#ff6b35',
			'desc'     => 'Pre-built pizza combinations. Use [pizza_static preset="slug"] to embed anywhere.',
		],
	];

	/** Register AJAX handlers (called from Plugin.php or AdminMenu.php). */
	public function register_ajax(): void {
		add_action( 'wp_ajax_pizzalayer_content_panel', [ $this, 'ajax_panel' ] );
	}

	/** AJAX handler: return the list-table HTML for a given CPT. */
	public function ajax_panel(): void {
		check_ajax_referer( 'pizzalayer_content_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) { wp_die( -1 ); }

		$slug = isset( $_POST['cpt'] ) ? sanitize_key( $_POST['cpt'] ) : 'toppings';
		if ( ! array_key_exists( $slug, self::CPTS ) ) { $slug = 'toppings'; }

		// Pass through search/orderby/order for the list table
		$_GET['post_type']  = 'pizzalayer_' . $slug;
		$_POST['post_type'] = 'pizzalayer_' . $slug;

		ob_start();
		$this->render_panel_inner( $slug );
		$html = ob_get_clean();

		wp_send_json_success( [ 'html' => $html, 'slug' => $slug ] );
	}

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) { return; }

		// Active CPT slug from query param — default to 'toppings'
		$active_slug = isset( $_GET['pl_cpt'] )
			? sanitize_key( $_GET['pl_cpt'] )
			: 'toppings';

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

		// Prepare initial list table for the active CPT
		$_GET['post_type']  = $active_cpt;
		$_POST['post_type'] = $active_cpt;
		$list_table = $this->get_list_table( $active_cpt );
		$list_table->process_bulk_action();
		$list_table->prepare_items();

		$hub_url = admin_url( 'admin.php?page=pizzalayer-content' );

		?>
		<div class="wrap plch-wrap">
		<?php $this->render_styles(); ?>

		<!-- ══ Header ═══════════════════════════════════════════════════ -->
		<div class="plch-header" id="plch-header">
			<div class="plch-header__left">
				<h1 class="plch-header__title" id="plch-header-title">
					<span class="dashicons <?php echo esc_attr( $active_meta['icon'] ); ?> plch-header-icon"
					      style="color:<?php echo esc_attr( $active_meta['color'] ); ?>"></span>
					<span id="plch-header-label"><?php echo esc_html( $active_meta['label'] ); ?></span>
				</h1>
				<p class="plch-header__desc" id="plch-header-desc"><?php echo esc_html( $active_meta['desc'] ); ?></p>
			</div>
			<div class="plch-header__actions">
				<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . $active_cpt ) ); ?>"
				   class="button button-primary plch-add-btn" id="plch-add-btn">
					<span class="dashicons dashicons-plus-alt2"></span>
					Add New <span id="plch-add-singular"><?php echo esc_html( $active_meta['singular'] ); ?></span>
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
					$cpt_data   = esc_attr( json_encode( [
						'slug'     => $slug,
						'label'    => $meta['label'],
						'singular' => $meta['singular'],
						'icon'     => $meta['icon'],
						'color'    => $meta['color'],
						'desc'     => $meta['desc'],
						'addUrl'   => admin_url( 'post-new.php?post_type=pizzalayer_' . $slug ),
					] ) );
				?>
				<a href="<?php echo esc_url( add_query_arg( 'pl_cpt', $slug, $hub_url ) ); ?>"
				   class="plch-rail__item<?php echo $is_active ? ' plch-rail__item--active' : ''; ?>"
				   data-slug="<?php echo esc_attr( $slug ); ?>"
				   data-cpt='<?php echo $cpt_data; ?>'
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
	 * Render the inner panel content (search + list table).
	 * Can receive a pre-built list_table to avoid re-querying.
	 */
	private function render_panel_inner( string $active_slug, ?\WP_Posts_List_Table $list_table = null ): void {
		$active_cpt  = 'pizzalayer_' . $active_slug;
		$active_meta = self::CPTS[ $active_slug ];
		$hub_url     = admin_url( 'admin.php?page=pizzalayer-content' );

		if ( $list_table === null ) {
			$_GET['post_type']  = $active_cpt;
			$_POST['post_type'] = $active_cpt;
			$list_table = $this->get_list_table( $active_cpt );
			$list_table->process_bulk_action();
			$list_table->prepare_items();
		}

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
		display: flex; align-items: flex-start; justify-content: space-between;
		gap: 16px; flex-wrap: wrap;
		background: #fff; border: 1px solid #e0e3e7; border-radius: 10px;
		padding: 20px 24px; margin-bottom: 18px;
		transition: border-color .2s;
	}
	.plch-header__left { flex: 1; min-width: 0; }
	.plch-header__title {
		margin: 0 0 4px; font-size: 20px; font-weight: 700;
		display: flex; align-items: center; gap: 10px;
		transition: color .2s;
	}
	.plch-header__title .dashicons {
		font-size: 22px !important; width: 22px !important; height: 22px !important;
		transition: color .2s;
	}
	.plch-header__desc { margin: 0; font-size: 13px; color: #646970; transition: opacity .2s; }
	.plch-add-btn {
		display: inline-flex !important; align-items: center; gap: 6px;
		white-space: nowrap; transition: background .2s, border-color .2s;
	}
	.plch-add-btn .dashicons { font-size: 15px !important; width: 15px !important; height: 15px !important; margin: 0; }

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
