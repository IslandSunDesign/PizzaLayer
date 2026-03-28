<?php
namespace PizzaLayer\Admin;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * PizzaLayer Dashboard — main admin home page.
 *
 * Includes:
 *  - Header bar with version + action buttons
 *  - Live layer stats strip
 *  - Setup nag for missing/empty CPTs
 *  - Layer Manager tabbed section (description + tips + links per type)
 *  - Quick-access icon nav
 *  - Tips rotator
 *  - Extend / developer card
 *  - Pro upsell CTA (dismissable per-user, hidden when Pro active)
 */
class AdminHome {

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) { return; }

		// Handle Pro CTA dismissal
		if (
			isset( $_GET['pizzalayer_dismiss_pro_cta'] )
			&& check_admin_referer( 'pizzalayer_dismiss_pro_cta' )
		) {
			update_user_meta( get_current_user_id(), 'pizzalayer_pro_cta_dismissed', true );
		}

		$show_pro_cta = ! class_exists( 'PizzaLayerPro' )
		             && ! get_user_meta( get_current_user_id(), 'pizzalayer_pro_cta_dismissed', true );

		// ── Live stats ──────────────────────────────────────────────────
		$stats = [
			'toppings' => (int) ( wp_count_posts( 'pizzalayer_toppings' )->publish ?? 0 ),
			'crusts'   => (int) ( wp_count_posts( 'pizzalayer_crusts'   )->publish ?? 0 ),
			'sauces'   => (int) ( wp_count_posts( 'pizzalayer_sauces'   )->publish ?? 0 ),
			'cheeses'  => (int) ( wp_count_posts( 'pizzalayer_cheeses'  )->publish ?? 0 ),
			'drizzles' => (int) ( wp_count_posts( 'pizzalayer_drizzles' )->publish ?? 0 ),
			'cuts'     => (int) ( wp_count_posts( 'pizzalayer_cuts'     )->publish ?? 0 ),
			'sizes'    => (int) ( wp_count_posts( 'pizzalayer_sizes'    )->publish ?? 0 ),
			'pizzas'   => (int) ( wp_count_posts( 'pizzalayer_pizzas'   )->publish ?? 0 ),
		];
		$total = array_sum( array_values( $stats ) );
		$active_template = (string) get_option( 'pizzalayer_setting_global_template', 'nightpie' );

		// ── Setup nags: which essential CPTs are still empty ────────────
		$essential = [ 'crusts', 'sauces', 'cheeses', 'toppings' ];
		$missing   = array_filter( $essential, fn( $k ) => $stats[ $k ] === 0 );

		// ── Layer tab definitions ────────────────────────────────────────
		$layer_tabs = [
			'toppings' => [
				'label' => 'Toppings',
				'icon'  => 'dashicons-star-filled',
				'desc'  => 'Toppings are where the action is. Every topping has its own layer image, price data, and coverage options (whole, half, quarters). The visualizer renders each selected topping in real time as your customer builds.',
				'tip'   => '💡 Use consistent transparent PNGs — 500×500 px works great across all templates.',
				'cpt'   => 'toppings',
				'count' => $stats['toppings'],
			],
			'crusts' => [
				'label' => 'Crusts',
				'icon'  => 'dashicons-tag',
				'desc'  => 'Your crust is the canvas. Define every base your pizza can be built on — from thin & crispy to thick & pillowy. Each crust item gets its own layer image that stacks in the live visualizer.',
				'tip'   => '💡 Use a transparent PNG against a consistent circular canvas for the crispest stacking results.',
				'cpt'   => 'crusts',
				'count' => $stats['crusts'],
			],
			'sauces' => [
				'label' => 'Sauces',
				'icon'  => 'dashicons-admin-generic',
				'desc'  => 'The sauce defines the flavor direction. Whether it\'s a bold marinara, a smoky BBQ, or a creamy garlic white — each sauce is a distinct layer image that sits on top of the crust in the visualizer.',
				'tip'   => '💡 Keep sauce layer images semi-transparent around the edges for a natural blending effect.',
				'cpt'   => 'sauces',
				'count' => $stats['sauces'],
			],
			'cheeses' => [
				'label' => 'Cheeses',
				'icon'  => 'dashicons-category',
				'desc'  => 'Cheese sits between sauce and toppings in your stack. Offer mozzarella, provolone, dairy-free alternatives — each with its own visual layer image.',
				'tip'   => '💡 A subtle melt texture with a slight golden edge makes cheese layers look mouth-wateringly real.',
				'cpt'   => 'cheeses',
				'count' => $stats['cheeses'],
			],
			'drizzles' => [
				'label' => 'Drizzles',
				'icon'  => 'dashicons-admin-customizer',
				'desc'  => 'Drizzles are the finishing touch — balsamic glaze, hot honey, ranch swirl. They layer above toppings in the visualizer and give your menu a premium feel with minimal setup.',
				'tip'   => '💡 Drizzle images look best with a flowing, asymmetric pattern that feels handcrafted.',
				'cpt'   => 'drizzles',
				'count' => $stats['drizzles'],
			],
			'cuts' => [
				'label' => 'Cuts',
				'icon'  => 'dashicons-editor-table',
				'desc'  => 'Define how a finished pizza gets sliced. Square cuts, classic triangles, party-style, or left whole — each cut style gets its own overlay layer that drops on top of the finished pizza.',
				'tip'   => '💡 Cut overlay PNGs should use a thin line weight with slight transparency so toppings show through.',
				'cpt'   => 'cuts',
				'count' => $stats['cuts'],
			],
			'sizes' => [
				'label' => 'Sizes',
				'icon'  => 'dashicons-image-rotate',
				'desc'  => 'Size options define the available pizza dimensions — small, medium, large, party. Each size carries dimension metadata, weight, area, and base price for pricing integrations.',
				'tip'   => '💡 Set size_area_sqin for accurate topping price-per-area calculations in PizzaLayerPro.',
				'cpt'   => 'sizes',
				'count' => $stats['sizes'],
			],
			'pizzas' => [
				'label' => 'Presets',
				'icon'  => 'dashicons-pizza',
				'desc'  => 'Pizza presets are pre-configured combinations — a "Margherita" or "BBQ Chicken" ready to drop onto any page via <code>[pizza_static preset="margherita"]</code>. Great for showcasing your signature pies.',
				'tip'   => '💡 Use presets on your menu page alongside the interactive builder for a complete ordering experience.',
				'cpt'   => 'pizzas',
				'count' => $stats['pizzas'],
			],
		];

		// ── Quick-access icon nav items ──────────────────────────────────
		$quick_nav = [
			[
				'icon'  => 'dashicons-welcome-learn-more',
				'label' => 'Setup Guide',
				'href'  => admin_url( 'admin.php?page=pizzalayer-setup' ),
				'color' => '#2271b1',
			],
			[
				'icon'  => 'dashicons-editor-code',
				'label' => 'Shortcode Generator',
				'href'  => admin_url( 'admin.php?page=pizzalayer-shortcodes' ),
				'color' => '#00a32a',
			],
			[
				'icon'  => 'dashicons-admin-appearance',
				'label' => 'Template',
				'href'  => admin_url( 'admin.php?page=pizzalayer-template' ),
				'color' => '#8c5af8',
			],
			[
				'icon'  => 'dashicons-admin-generic',
				'label' => 'Customizer',
				'href'  => admin_url( 'admin.php?page=pizzalayer-settings' ),
				'color' => '#d63638',
			],
			[
				'icon'  => 'dashicons-star-filled',
				'label' => 'Toppings',
				'href'  => admin_url( 'edit.php?post_type=pizzalayer_toppings' ),
				'color' => '#f0b849',
			],
			[
				'icon'  => 'dashicons-media-document',
				'label' => 'Help',
				'href'  => admin_url( 'admin.php?page=pizzalayer-help' ),
				'color' => '#646970',
			],
		];

		// ── Tips rotator ─────────────────────────────────────────────────
		$tips = [
			'Keep layer images lean — use WebP or transparent PNG at a consistent canvas size for crisp, predictable stacking.',
			'Name CPT slugs cleanly (e.g. <code>pepperoni</code>, <code>thin-crust</code>) — they feed directly into CSS classes and JS data keys.',
			'Offer half and quarter topping coverage to boost average order value without overwhelming the decision.',
			'Use the <code>[pizza_layer]</code> shortcode to embed a single ingredient image anywhere on your menu pages.',
			'Cache thumbnails and preload your first visible layer set for a snappier first paint on slower connections.',
			'Document your price grid rules in the product notes field — your future self will thank you.',
		];

		?>
		<div class="wrap plh-wrap">

			<?php $this->render_styles(); ?>

			<!-- ══ Header ══════════════════════════════════════════════════ -->
			<div class="plh-header">
				<div class="plh-header__brand">
					<span class="dashicons dashicons-pizza plh-header__icon" aria-hidden="true"></span>
					<div>
						<h1 class="plh-header__title">PizzaLayer</h1>
						<p class="plh-header__tagline">The WordPress pizza builder &mdash; v<?php echo esc_html( PIZZALAYER_VERSION ); ?></p>
					</div>
				</div>
				<div class="plh-header__actions">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=pizzalayer-template' ) ); ?>" class="button button-primary">
						<span class="dashicons dashicons-admin-appearance"></span> Template
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=pizzalayer-setup' ) ); ?>" class="button">
						<span class="dashicons dashicons-welcome-learn-more"></span> Setup Guide
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=pizzalayer-shortcodes' ) ); ?>" class="button">
						<span class="dashicons dashicons-editor-code"></span> Shortcodes
					</a>
				</div>
			</div>

			<!-- ══ Pro upsell CTA ════════════════════════════════════════ -->
			<?php if ( $show_pro_cta ) : ?>
			<div class="plh-pro-cta">
				<span class="plh-pro-cta__icon">🍕</span>
				<div class="plh-pro-cta__text">
					<strong>Supercharge with PizzaLayerPro</strong> &mdash;
					Add WooCommerce cart integration, order pricing grids, and more.
					<a href="https://pizzalayer.com/pro" target="_blank" rel="noopener">Learn more →</a>
				</div>
				<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'pizzalayer_dismiss_pro_cta', '1' ), 'pizzalayer_dismiss_pro_cta' ) ); ?>"
				   class="plh-pro-cta__dismiss" title="<?php esc_attr_e( 'Dismiss', 'pizzalayer' ); ?>">✕</a>
			</div>
			<?php endif; ?>

			<!-- ══ Setup nag ═════════════════════════════════════════════ -->
			<?php if ( ! empty( $missing ) ) : ?>
			<div class="plh-nag">
				<span class="dashicons dashicons-info-outline"></span>
				<div>
					<strong><?php esc_html_e( 'A few things still need content before your builder works:', 'pizzalayer' ); ?></strong>
					<ul class="plh-nag__list">
						<?php foreach ( $missing as $k ) : ?>
						<li>
							<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=pizzalayer_' . $k ) ); ?>">
								<?php printf( esc_html__( 'Add your first %s →', 'pizzalayer' ), esc_html( ucfirst( $k ) ) ); ?>
							</a>
						</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
			<?php endif; ?>

			<!-- ══ Stats strip ════════════════════════════════════════════ -->
			<div class="plh-stats-row">
				<div class="plh-stat plh-stat--total">
					<span class="plh-stat__number"><?php echo esc_html( $total ); ?></span>
					<span class="plh-stat__label">Total Layers</span>
				</div>
				<?php
				$stat_display = [
					'toppings' => 'Toppings',
					'crusts'   => 'Crusts',
					'sauces'   => 'Sauces',
					'cheeses'  => 'Cheeses',
					'drizzles' => 'Drizzles',
					'cuts'     => 'Cuts',
				];
				foreach ( $stat_display as $k => $label ) :
					$warn = $stats[ $k ] === 0 && in_array( $k, $essential, true );
				?>
				<div class="plh-stat<?php echo $warn ? ' plh-stat--warn' : ''; ?>">
					<span class="plh-stat__number"><?php echo esc_html( $stats[ $k ] ); ?></span>
					<span class="plh-stat__label"><?php echo esc_html( $label ); ?></span>
					<?php if ( $warn ) : ?>
					<span class="plh-stat__warn-badge">Needs content</span>
					<?php endif; ?>
				</div>
				<?php endforeach; ?>
				<div class="plh-stat plh-stat--template">
					<span class="plh-stat__number plh-stat__number--sm"><?php echo esc_html( ucwords( str_replace( '-', ' ', $active_template ) ) ); ?></span>
					<span class="plh-stat__label">Active Template</span>
				</div>
			</div>

			<!-- ══ Quick-access icon nav ══════════════════════════════════ -->
			<div class="plh-quicknav">
				<?php foreach ( $quick_nav as $item ) : ?>
				<a href="<?php echo esc_url( $item['href'] ); ?>" class="plh-quicknav__item">
					<span class="plh-quicknav__icon" style="background:<?php echo esc_attr( $item['color'] ); ?>20;color:<?php echo esc_attr( $item['color'] ); ?>">
						<span class="dashicons <?php echo esc_attr( $item['icon'] ); ?>"></span>
					</span>
					<span class="plh-quicknav__label"><?php echo esc_html( $item['label'] ); ?></span>
				</a>
				<?php endforeach; ?>
				<?php do_action( 'pizzalayer_admin_home_quicknav' ); ?>
			</div>

			<!-- ══ Layer Manager ══════════════════════════════════════════ -->
			<div class="plh-card plh-card--tabs">
				<div class="plh-card__head">
					<h2 class="plh-card__title">
						<span class="dashicons dashicons-category"></span> Layer Manager
					</h2>
					<p class="plh-card__subtitle">Select a layer type to learn about it and jump directly to its content.</p>
				</div>

				<nav class="plh-tabnav" id="plh-layer-tabs" role="tablist">
					<?php $first = true; foreach ( $layer_tabs as $slug => $tab ) : ?>
					<button class="plh-tab<?php echo $first ? ' plh-tab--active' : ''; ?>"
					        data-tab="<?php echo esc_attr( $slug ); ?>"
					        role="tab"
					        aria-selected="<?php echo $first ? 'true' : 'false'; ?>"
					        aria-controls="plh-panel-<?php echo esc_attr( $slug ); ?>">
						<span class="dashicons <?php echo esc_attr( $tab['icon'] ); ?>"></span>
						<?php echo esc_html( $tab['label'] ); ?>
						<span class="plh-tab__count<?php echo $tab['count'] === 0 ? ' plh-tab__count--zero' : ''; ?>">
							<?php echo esc_html( $tab['count'] ); ?>
						</span>
					</button>
					<?php $first = false; endforeach; ?>
				</nav>

				<div class="plh-panels">
					<?php $first = true; foreach ( $layer_tabs as $slug => $tab ) : ?>
					<div class="plh-panel<?php echo $first ? ' plh-panel--active' : ''; ?>"
					     id="plh-panel-<?php echo esc_attr( $slug ); ?>"
					     role="tabpanel">
						<div class="plh-panel__body">
							<div class="plh-panel__text">
								<p><?php echo wp_kses_post( $tab['desc'] ); ?></p>
								<p class="plh-panel__tip"><?php echo wp_kses_post( $tab['tip'] ); ?></p>
							</div>
							<div class="plh-panel__actions">
								<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=pizzalayer_' . $tab['cpt'] ) ); ?>" class="button">
									<span class="dashicons dashicons-list-view"></span>
									View All <?php echo esc_html( $tab['label'] ); ?>
									<?php if ( $tab['count'] > 0 ) : ?>
									<span class="plh-count-badge"><?php echo esc_html( $tab['count'] ); ?></span>
									<?php endif; ?>
								</a>
								<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=pizzalayer_' . $tab['cpt'] ) ); ?>" class="button button-primary">
									<span class="dashicons dashicons-plus-alt2"></span>
									Add New <?php echo esc_html( rtrim( $tab['label'], 's' ) ); ?>
								</a>
							</div>
						</div>
					</div>
					<?php $first = false; endforeach; ?>
				</div>
			</div>

			<!-- ══ Bottom three-column cards ════════════════════════════ -->
			<div class="plh-features-row">

				<!-- Tips rotator -->
				<div class="plh-card plh-card--feature">
					<div class="plh-card__icon-header">
						<span class="dashicons dashicons-admin-tools"></span>
						<h3>Tips &amp; Tricks</h3>
					</div>
					<div class="plh-card__content">
						<div class="plh-rotator-wrap">
							<div class="pizzalayer-rotator" data-interval="6000">
								<?php foreach ( $tips as $i => $text ) : ?>
								<div class="pz-rotator-slide<?php echo $i === 0 ? ' is-active' : ''; ?>">
									<?php echo wp_kses_post( $text ); ?>
								</div>
								<?php endforeach; ?>
							</div>
							<div class="plh-rotator-dots" aria-hidden="true">
								<?php for ( $i = 0; $i < count( $tips ); $i++ ) : ?>
								<span class="plh-rotator-dot<?php echo $i === 0 ? ' is-active' : ''; ?>"></span>
								<?php endfor; ?>
							</div>
						</div>
					</div>
				</div>

				<!-- Shortcode reference -->
				<div class="plh-card plh-card--feature">
					<div class="plh-card__icon-header">
						<span class="dashicons dashicons-editor-code"></span>
						<h3>Shortcode Reference</h3>
					</div>
					<div class="plh-card__content">
						<p><code>[pizza_builder]</code><br><span class="plh-sc-desc">Interactive builder on any page.</span></p>
						<p><code>[pizza_builder id="pizza-1" max_toppings="5"]</code><br><span class="plh-sc-desc">Multiple builders, different settings.</span></p>
						<p><code>[pizza_static preset="hawaiian"]</code><br><span class="plh-sc-desc">Static preset display.</span></p>
						<p><code>[pizza_layer type="topping" slug="pepperoni"]</code><br><span class="plh-sc-desc">Single layer image anywhere.</span></p>
						<p style="margin-top:12px;">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=pizzalayer-shortcodes' ) ); ?>" class="button button-secondary">
								Open Shortcode Generator
							</a>
						</p>
					</div>
				</div>

				<!-- Extend / developer card -->
				<div class="plh-card plh-card--feature">
					<div class="plh-card__icon-header">
						<span class="dashicons dashicons-admin-plugins"></span>
						<h3>Extend PizzaLayer</h3>
					</div>
					<div class="plh-card__content">
						<p>Create a <strong>child theme template</strong> by adding a directory at <code>/pzttemplates/your-slug/</code>. Copy a base template from the plugin's <code>/templates/</code> folder, then freely edit layout, partials, and CSS.</p>
						<p>Hook into any part of the builder with the full <strong>action &amp; filter API</strong> — <code>pizzalayer_before_builder</code>, <code>pizzalayer_layer_html</code>, <code>pizzalayer_tab_order</code>, and more.</p>
						<p>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=pizzalayer-help' ) ); ?>" class="button button-secondary">
								Developer Hooks Reference
							</a>
						</p>
					</div>
				</div>

			</div><!-- /.plh-features-row -->

			<?php do_action( 'pizzalayer_admin_home_cards' ); ?>

			<!-- ══ Credits ════════════════════════════════════════════════ -->
			<div class="plh-credits">
				PizzaLayer v<?php echo esc_html( PIZZALAYER_VERSION ); ?> &mdash;
				crafted by <strong>Ryan Bishop</strong> /
				<a href="https://islandsundesign.com" target="_blank" rel="noopener">Island Sun Design</a>
			</div>

		</div><!-- /.plh-wrap -->

		<?php
	}

	private function render_styles(): void {
		?>
		<style>
		/* ── Wrap ─────────────────────────────────────────────────────── */
		.plh-wrap { max-width: 1200px; }

		/* ── Header ───────────────────────────────────────────────────── */
		.plh-header {
			display: flex; align-items: center; justify-content: space-between;
			flex-wrap: wrap; gap: 16px;
			background: linear-gradient(135deg, #1a1e23 0%, #2d3748 100%);
			color: #fff; border-radius: 10px;
			padding: 22px 28px; margin-bottom: 20px;
		}
		.plh-header__brand { display: flex; align-items: center; gap: 16px; }
		.plh-header__icon {
			font-size: 38px !important; width: 38px !important; height: 38px !important;
			color: #ff6b35;
		}
		.plh-header__title { margin: 0; font-size: 24px; font-weight: 700; color: #fff; }
		.plh-header__tagline { margin: 3px 0 0; color: #8d97a5; font-size: 13px; }
		.plh-header__actions { display: flex; gap: 8px; flex-wrap: wrap; }
		.plh-header__actions .button { display: inline-flex; align-items: center; gap: 5px; }
		.plh-header__actions .dashicons { font-size: 15px !important; width: 15px !important; height: 15px !important; margin: 0; }

		/* ── Pro CTA ──────────────────────────────────────────────────── */
		.plh-pro-cta {
			display: flex; align-items: center; gap: 12px;
			background: #fff8e6; border: 1px solid #f0b849; border-radius: 8px;
			padding: 12px 16px; margin-bottom: 16px; font-size: 13px;
		}
		.plh-pro-cta__icon { font-size: 18px; }
		.plh-pro-cta__text { flex: 1; }
		.plh-pro-cta__text a { font-weight: 600; }
		.plh-pro-cta__dismiss {
			color: #787c82; text-decoration: none; font-size: 14px;
			padding: 2px 6px; border-radius: 3px; transition: background .15s;
		}
		.plh-pro-cta__dismiss:hover { background: #f0e8cc; }

		/* ── Setup nag ────────────────────────────────────────────────── */
		.plh-nag {
			display: flex; align-items: flex-start; gap: 10px;
			background: #f0f6ff; border-left: 4px solid #2271b1; border-radius: 0 8px 8px 0;
			padding: 14px 18px; margin-bottom: 16px; font-size: 13px;
		}
		.plh-nag .dashicons { color: #2271b1; margin-top: 2px; flex-shrink: 0; }
		.plh-nag strong { display: block; margin-bottom: 6px; }
		.plh-nag__list { margin: 0; padding: 0 0 0 16px; }
		.plh-nag__list li { margin-bottom: 3px; }

		/* ── Stats strip ──────────────────────────────────────────────── */
		.plh-stats-row {
			display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px;
		}
		.plh-stat {
			flex: 1 1 90px; background: #fff; border: 1px solid #e0e3e7;
			border-radius: 8px; padding: 14px 16px; text-align: center;
			position: relative;
		}
		.plh-stat--total {
			background: #1a1e23; border-color: #1a1e23;
		}
		.plh-stat--total .plh-stat__number { color: #fff; }
		.plh-stat--total .plh-stat__label  { color: #8d97a5; }
		.plh-stat--warn { border-color: #f0b849; background: #fffdf0; }
		.plh-stat__number { display: block; font-size: 26px; font-weight: 700; color: #1d2023; line-height: 1.1; }
		.plh-stat__number--sm { font-size: 14px; padding-top: 5px; }
		.plh-stat__label { display: block; font-size: 11px; text-transform: uppercase; letter-spacing: .06em; color: #787c82; margin-top: 4px; }
		.plh-stat__warn-badge {
			display: block; margin-top: 5px; font-size: 10px; font-weight: 600;
			color: #996600; background: #fef3cd; border-radius: 3px; padding: 1px 5px;
		}
		.plh-stat--template { flex: 1 1 130px; }

		/* ── Quick-nav icon grid ──────────────────────────────────────── */
		.plh-quicknav {
			display: flex; flex-wrap: wrap; gap: 12px;
			margin-bottom: 20px;
		}
		.plh-quicknav__item {
			flex: 1 1 110px; display: flex; flex-direction: column;
			align-items: center; gap: 8px;
			background: #fff; border: 1px solid #e0e3e7; border-radius: 10px;
			padding: 16px 12px; text-decoration: none; color: #1d2023;
			font-size: 12px; font-weight: 600; text-align: center;
			transition: border-color .15s, box-shadow .15s, transform .15s;
		}
		.plh-quicknav__item:hover {
			border-color: #2271b1; box-shadow: 0 2px 8px rgba(0,0,0,.1);
			transform: translateY(-2px); color: #2271b1;
		}
		.plh-quicknav__icon {
			width: 44px; height: 44px; border-radius: 10px;
			display: flex; align-items: center; justify-content: center;
		}
		.plh-quicknav__icon .dashicons {
			font-size: 22px !important; width: 22px !important; height: 22px !important;
		}
		.plh-quicknav__label { line-height: 1.2; }

		/* ── Generic card ─────────────────────────────────────────────── */
		.plh-card {
			background: #fff; border: 1px solid #e0e3e7;
			border-radius: 10px; margin-bottom: 20px; overflow: hidden;
		}
		.plh-card__head { padding: 20px 24px 0; }
		.plh-card__title {
			margin: 0 0 4px; font-size: 16px;
			display: flex; align-items: center; gap: 8px;
		}
		.plh-card__title .dashicons { color: #646970; font-size: 18px !important; width: 18px !important; height: 18px !important; }
		.plh-card__subtitle { margin: 0 0 0; color: #646970; font-size: 13px; padding-bottom: 4px; }

		/* ── Tab nav ──────────────────────────────────────────────────── */
		.plh-tabnav {
			display: flex; overflow-x: auto; border-bottom: 2px solid #e0e3e7;
			padding: 0 16px; background: #f8f9fa; gap: 0;
		}
		.plh-tab {
			display: inline-flex; align-items: center; gap: 6px;
			padding: 8px 14px; border: none; border-bottom: 2px solid transparent;
			background: transparent; cursor: pointer; font-size: 13px; font-weight: 500;
			color: #646970; white-space: nowrap; margin-bottom: -2px; line-height: 1;
			transition: color .15s, border-color .15s;
		}
		.plh-tab:hover { color: #1d2023; }
		.plh-tab--active { color: #2271b1; border-bottom-color: #2271b1; font-weight: 600; }
		.plh-tab .dashicons {
			font-size: 14px !important; width: 14px !important; height: 14px !important;
			line-height: 1 !important; vertical-align: middle; flex-shrink: 0;
		}
		.plh-tab__count {
			background: #e0e3e7; color: #646970; border-radius: 999px;
			font-size: 10px; font-weight: 700; padding: 1px 6px; min-width: 16px; text-align: center;
		}
		.plh-tab--active .plh-tab__count { background: #dce8f7; color: #2271b1; }
		.plh-tab__count--zero { background: #fce8e8; color: #d63638; }

		/* ── Panels ───────────────────────────────────────────────────── */
		.plh-panels { padding: 0; }
		.plh-panel { display: none; }
		.plh-panel--active { display: block; }
		.plh-panel__body {
			display: flex; align-items: flex-start; justify-content: space-between;
			gap: 24px; flex-wrap: wrap; padding: 20px 24px;
		}
		.plh-panel__text { flex: 1 1 300px; font-size: 13px; }
		.plh-panel__text p { margin: 0 0 10px; }
		.plh-panel__tip {
			background: #f6f7f7; border-left: 3px solid #f0b849;
			padding: 10px 14px; border-radius: 0 6px 6px 0;
			font-size: 13px; color: #3c434a; margin: 0;
		}
		.plh-panel__actions { display: flex; flex-direction: column; gap: 8px; flex-shrink: 0; }
		.plh-panel__actions .button { display: inline-flex; align-items: center; gap: 6px; white-space: nowrap; }
		.plh-panel__actions .dashicons { font-size: 14px !important; width: 14px !important; height: 14px !important; }
		.plh-count-badge {
			background: #fff; border: 1px solid #ccd0d4; border-radius: 999px;
			font-size: 11px; font-weight: 600; padding: 0 7px;
		}

		/* ── Bottom feature cards ─────────────────────────────────────── */
		.plh-features-row {
			display: grid;
			grid-template-columns: repeat( auto-fit, minmax( 280px, 1fr ) );
			gap: 20px; margin-bottom: 20px;
		}
		.plh-card--feature { margin-bottom: 0; }
		.plh-card__icon-header {
			display: flex; align-items: center; gap: 10px;
			padding: 16px 20px 12px; border-bottom: 1px solid #f0f0f0;
		}
		.plh-card__icon-header .dashicons { font-size: 20px !important; width: 20px !important; height: 20px !important; color: #2271b1; }
		.plh-card__icon-header h3 { margin: 0; font-size: 14px; font-weight: 600; }
		.plh-card__content { padding: 16px 20px; font-size: 13px; color: #3c434a; }
		.plh-card__content p { margin: 0 0 10px; }
		.plh-card__content p:last-child { margin-bottom: 0; }
		.plh-card__content code { background: #f0f0f1; padding: 1px 5px; border-radius: 3px; font-size: 11.5px; }
		.plh-sc-desc { color: #787c82; font-size: 12px; }

		/* Rotator */
		.plh-rotator-wrap { position: relative; min-height: 72px; }
		.pizzalayer-rotator { position: relative; }
		.pz-rotator-slide { display: none; font-size: 13px; line-height: 1.65; }
		.pz-rotator-slide.is-active { display: block; }
		.pz-rotator-slide code { background: #f0f0f1; padding: 1px 5px; border-radius: 3px; font-size: 12px; }
		.plh-rotator-dots { display: flex; gap: 5px; margin-top: 14px; }
		.plh-rotator-dot { width: 6px; height: 6px; border-radius: 50%; background: #ddd; transition: background .2s; }
		.plh-rotator-dot.is-active { background: #2271b1; }

		/* ── Credits ──────────────────────────────────────────────────── */
		.plh-credits { padding: 8px 0 24px; font-size: 12px; color: #aaa; }
		.plh-credits a { color: #aaa; text-decoration: none; }
		.plh-credits a:hover { color: #2271b1; }
		</style>
		<?php
	}

	}
