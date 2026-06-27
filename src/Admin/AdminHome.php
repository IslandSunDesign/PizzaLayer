<?php
namespace PizzaLayer\Admin;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * PizzaLayer Dashboard — main admin home page.
 *
 * Includes:
 *  - Header bar with version + action buttons
 *  - Live layer stats strip (each box links to its CPT in the Content Hub)
 *  - Setup nag for missing/empty CPTs
 *  - Quick-access icon nav (Help surfaced as a featured item)
 *  - Hero intro
 *  - Shortcode reference + Extend / developer cards
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
			'presets'  => (int) ( wp_count_posts( 'pizzalayer_presets'  )->publish ?? 0 ),
		];
		$total = array_sum( array_values( $stats ) );
		$active_template = (string) get_option( 'pizzalayer_setting_global_template', 'nightpie' );

		// ── Setup nags: which essential CPTs are still empty ────────────
		$essential = [ 'crusts', 'sauces', 'cheeses', 'toppings' ];
		$missing   = array_filter( $essential, fn( $k ) => $stats[ $k ] === 0 );


		// ── Quick-access icon nav items ──────────────────────────────────
		$quick_nav = [
			[
				'icon'  => 'dashicons-welcome-learn-more',
				'label' => __( 'Setup Guide', 'pizzalayer' ),
				'href'  => admin_url( 'admin.php?page=pizzalayer-setup' ),
				'color' => '#2271b1',
			],
			[
				'icon'     => 'dashicons-sos',
				'label'    => __( 'Help', 'pizzalayer' ),
				'href'     => admin_url( 'admin.php?page=pizzalayer-help' ),
				'color'    => '#d63638',
				'featured' => true,
			],
			[
				'icon'  => 'dashicons-editor-code',
				'label' => __( 'Shortcode Generator', 'pizzalayer' ),
				'href'  => admin_url( 'admin.php?page=pizzalayer-shortcodes' ),
				'color' => '#00a32a',
			],
			[
				'icon'  => 'dashicons-admin-appearance',
				'label' => __( 'Template', 'pizzalayer' ),
				'href'  => admin_url( 'admin.php?page=pizzalayer-template' ),
				'color' => '#8c5af8',
			],
			[
				'icon'  => 'dashicons-admin-generic',
				'label' => __( 'Customizer', 'pizzalayer' ),
				'href'  => admin_url( 'admin.php?page=pizzalayer-settings' ),
				'color' => '#9b51e0',
			],
			[
				'icon'  => 'dashicons-star-filled',
				'label' => __( 'Toppings', 'pizzalayer' ),
				'href'  => admin_url( 'edit.php?post_type=pizzalayer_toppings' ),
				'color' => '#f0b849',
			],
			[
				'icon'  => 'dashicons-food',
				'label' => __( 'Presets', 'pizzalayer' ),
				'href'  => admin_url( 'edit.php?post_type=pizzalayer_presets' ),
				'color' => '#e8692a',
			],
			[
				'icon'  => 'dashicons-migrate',
				'label' => __( 'Site Migration', 'pizzalayer' ),
				'href'  => admin_url( 'admin.php?page=pizzalayer-migration' ),
				'color' => '#0073aa',
			],
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
						<p class="plh-header__tagline"><?php
							/* translators: %s = version number */
							printf( esc_html__( 'The WordPress pizza builder — v%s', 'pizzalayer' ), esc_html( PIZZALAYER_VERSION ) );
						?></p>
					</div>
				</div>
				<div class="plh-header__actions">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=pizzalayer-template' ) ); ?>" class="button button-primary">
						<span class="dashicons dashicons-admin-appearance"></span> <?php esc_html_e( 'Template', 'pizzalayer' ); ?>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=pizzalayer-setup' ) ); ?>" class="button">
						<span class="dashicons dashicons-welcome-learn-more"></span> <?php esc_html_e( 'Setup Guide', 'pizzalayer' ); ?>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=pizzalayer-shortcodes' ) ); ?>" class="button">
						<span class="dashicons dashicons-editor-code"></span> <?php esc_html_e( 'Shortcodes', 'pizzalayer' ); ?>
					</a>
				</div>
			</div>

			<!-- ══ Pro upsell CTA ════════════════════════════════════════ -->
			<?php if ( $show_pro_cta ) : ?>
			<div class="plh-pro-cta">
				<span class="plh-pro-cta__icon">🍕</span>
				<div class="plh-pro-cta__text">
					<strong><?php esc_html_e( 'Supercharge with PizzaLayerPro', 'pizzalayer' ); ?></strong> &mdash;
					<?php esc_html_e( 'Add WooCommerce cart integration, order pricing grids, and more.', 'pizzalayer' ); ?>
					<a href="https://pizzalayer.com/pro" target="_blank" rel="noopener"><?php esc_html_e( 'Learn more →', 'pizzalayer' ); ?></a>
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
								<?php printf( /* translators: %s = content type name. */ esc_html__( 'Add your first %s →', 'pizzalayer' ), esc_html( ucfirst( $k ) ) ); ?>
							</a>
						</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
			<?php endif; ?>

			<!-- ══ Stats strip ════════════════════════════════════════════ -->
			<?php
			$hub_disabled = ( get_option( 'pizzalayer_setting_disable_content_hub', 'no' ) === 'yes' );
			$hub_url      = admin_url( 'admin.php?page=pizzalayer-content' );
			$total_url    = $hub_disabled ? admin_url( 'edit.php?post_type=pizzalayer_toppings' ) : $hub_url;
			?>
			<div class="plh-stats-row">
				<a class="plh-stat plh-stat--total" href="<?php echo esc_url( $total_url ); ?>">
					<span class="plh-stat__number"><?php echo esc_html( $total ); ?></span>
					<span class="plh-stat__label"><?php esc_html_e( 'Total Layers', 'pizzalayer' ); ?></span>
				</a>
				<?php
				$stat_display = [
					'toppings' => __( 'Toppings', 'pizzalayer' ),
					'crusts'   => __( 'Crusts', 'pizzalayer' ),
					'sauces'   => __( 'Sauces', 'pizzalayer' ),
					'cheeses'  => __( 'Cheeses', 'pizzalayer' ),
					'drizzles' => __( 'Drizzles', 'pizzalayer' ),
					'cuts'     => __( 'Cuts', 'pizzalayer' ),
				];
				foreach ( $stat_display as $k => $label ) :
					$warn     = $stats[ $k ] === 0 && in_array( $k, $essential, true );
					$stat_url = $hub_disabled
						? admin_url( 'edit.php?post_type=pizzalayer_' . $k )
						: add_query_arg( 'pl_cpt', $k, $hub_url );
				?>
				<a class="plh-stat<?php echo $warn ? ' plh-stat--warn' : ''; ?>" href="<?php echo esc_url( $stat_url ); ?>">
					<span class="plh-stat__number"><?php echo esc_html( $stats[ $k ] ); ?></span>
					<span class="plh-stat__label"><?php echo esc_html( $label ); ?></span>
					<?php if ( $warn ) : ?>
					<span class="plh-stat__warn-badge"><?php esc_html_e( 'Needs content', 'pizzalayer' ); ?></span>
					<?php endif; ?>
				</a>
				<?php endforeach; ?>
				<a class="plh-stat plh-stat--template" href="<?php echo esc_url( admin_url( 'admin.php?page=pizzalayer-template' ) ); ?>">
					<span class="plh-stat__number plh-stat__number--sm"><?php echo esc_html( ucwords( str_replace( '-', ' ', $active_template ) ) ); ?></span>
					<span class="plh-stat__label"><?php esc_html_e( 'Active Template', 'pizzalayer' ); ?></span>
				</a>
			</div>

			<!-- ══ Quick-access icon nav ══════════════════════════════════ -->
			<div class="plh-quicknav">
				<?php foreach ( $quick_nav as $item ) :
					$is_featured = ! empty( $item['featured'] );
					$item_class  = 'plh-quicknav__item' . ( $is_featured ? ' plh-quicknav__item--featured' : '' );
				?>
				<a href="<?php echo esc_url( $item['href'] ); ?>" class="<?php echo esc_attr( $item_class ); ?>"<?php echo $is_featured ? ' style="--pzl-qn-accent:' . esc_attr( $item['color'] ) . '"' : ''; ?>>
					<span class="plh-quicknav__icon" style="background:<?php echo esc_attr( $item['color'] ); ?>20;color:<?php echo esc_attr( $item['color'] ); ?>">
						<span class="dashicons <?php echo esc_attr( $item['icon'] ); ?>"></span>
					</span>
					<span class="plh-quicknav__label"><?php echo esc_html( $item['label'] ); ?></span>
				</a>
				<?php endforeach; ?>
				<?php do_action( 'pizzalayer_admin_home_quicknav' ); ?>
			</div>

			<!-- ══ Hero intro ══════════════════════════════════════════════ -->
			<div class="plh-hero">
				<div class="plh-hero__inner">
					<div class="plh-hero__copy">
						<h2 class="plh-hero__heading"><?php esc_html_e( 'Build beautiful pizza builders — one layer at a time.', 'pizzalayer' ); ?></h2>
						<p class="plh-hero__text"><?php esc_html_e( 'PizzaLayer turns your WordPress site into an interactive pizza configurator. Add your ingredients as layer images, choose a template, drop in a shortcode, and your customers build their perfect pizza in real time.', 'pizzalayer' ); ?></p>
						<div class="plh-hero__steps">
							<div class="plh-hero__step">
								<span class="plh-hero__step-num">1</span>
								<span><strong><?php esc_html_e( 'Add content', 'pizzalayer' ); ?></strong> — <?php esc_html_e( 'upload crusts, sauces, cheeses &amp; toppings as layer images.', 'pizzalayer' ); ?></span>
							</div>
							<div class="plh-hero__step">
								<span class="plh-hero__step-num">2</span>
								<span><strong><?php esc_html_e( 'Choose a template', 'pizzalayer' ); ?></strong> — <?php esc_html_e( 'pick the visual style for your builder UI.', 'pizzalayer' ); ?></span>
							</div>
							<div class="plh-hero__step">
								<span class="plh-hero__step-num">3</span>
								<span><strong><?php esc_html_e( 'Embed &amp; go', 'pizzalayer' ); ?></strong> — <?php
									/* translators: [pizza_builder] is a shortcode, keep as-is */
									esc_html_e( 'paste [pizza_builder] on any page and you\'re live.', 'pizzalayer' );
								?></span>
							</div>
						</div>
						<div class="plh-hero__btns">
							<a href="<?php echo esc_url( admin_url('admin.php?page=pizzalayer-setup') ); ?>" class="button button-primary">
								<span class="dashicons dashicons-welcome-learn-more"></span> <?php esc_html_e( 'Setup Guide', 'pizzalayer' ); ?>
							</a>
							<a href="<?php echo esc_url( admin_url('admin.php?page=pizzalayer-template') ); ?>" class="button">
								<span class="dashicons dashicons-admin-appearance"></span> <?php esc_html_e( 'Choose Template', 'pizzalayer' ); ?>
							</a>
							<a href="<?php echo esc_url( admin_url('admin.php?page=pizzalayer-shortcodes') ); ?>" class="button">
								<span class="dashicons dashicons-editor-code"></span> <?php esc_html_e( 'Shortcodes', 'pizzalayer' ); ?>
							</a>
						</div>
					</div>
					<div class="plh-hero__stats-side">
						<div class="plh-hero__stat-pill">
							<span class="dashicons dashicons-admin-appearance plh-hero__pill-icon"></span>
							<div>
								<span class="plh-hero__pill-label"><?php esc_html_e( 'Active Template', 'pizzalayer' ); ?></span>
								<span class="plh-hero__pill-val"><?php echo esc_html( $active_template ? ucwords( str_replace('-',' ',$active_template) ) : __( 'Not set', 'pizzalayer' ) ); ?></span>
							</div>
						</div>
						<div class="plh-hero__stat-pill">
							<span class="dashicons dashicons-images-alt2 plh-hero__pill-icon"></span>
							<div>
								<span class="plh-hero__pill-label"><?php esc_html_e( 'Total Layers Published', 'pizzalayer' ); ?></span>
								<span class="plh-hero__pill-val"><?php echo esc_html( $total ); ?></span>
							</div>
						</div>
						<div class="plh-hero__stat-pill">
							<span class="dashicons dashicons-star-filled plh-hero__pill-icon"></span>
							<div>
								<span class="plh-hero__pill-label"><?php esc_html_e( 'Toppings', 'pizzalayer' ); ?></span>
								<span class="plh-hero__pill-val"><?php echo esc_html( $stats['toppings'] ); ?></span>
							</div>
						</div>
						<a href="<?php echo esc_url( home_url('/') ); ?>" target="_blank" rel="noopener" class="plh-hero__view-site">
							<span class="dashicons dashicons-external"></span> <?php esc_html_e( 'View Site', 'pizzalayer' ); ?>
						</a>
					</div>
				</div>
			</div>

			<!-- ══ Bottom feature cards ═════════════════════════════════ -->
			<div class="plh-features-row">

				<!-- Shortcode reference -->
				<div class="plh-card plh-card--feature">
					<div class="plh-card__icon-header">
						<span class="dashicons dashicons-editor-code"></span>
						<h3><?php esc_html_e( 'Shortcode Reference', 'pizzalayer' ); ?></h3>
					</div>
					<div class="plh-card__content">
						<p><code>[pizza_builder]</code><br><span class="plh-sc-desc"><?php esc_html_e( 'Interactive builder on any page.', 'pizzalayer' ); ?></span></p>
						<p><code>[pizza_builder id="pizza-1" max_toppings="5"]</code><br><span class="plh-sc-desc"><?php esc_html_e( 'Multiple builders, different settings.', 'pizzalayer' ); ?></span></p>
						<p><code>[pizza_static crust="thin-crust" sauce="tomato" toppings="pepperoni"]</code><br><span class="plh-sc-desc"><?php esc_html_e( 'Static pizza display anywhere.', 'pizzalayer' ); ?></span></p>
						<p><code>[pizza_layer type="topping" slug="pepperoni"]</code><br><span class="plh-sc-desc"><?php esc_html_e( 'Single layer image anywhere.', 'pizzalayer' ); ?></span></p>
						<p style="margin-top:12px;">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=pizzalayer-shortcodes' ) ); ?>" class="button button-secondary">
								<?php esc_html_e( 'Open Shortcode Generator', 'pizzalayer' ); ?>
							</a>
						</p>
					</div>
				</div>

				<!-- Extend / developer card -->
				<div class="plh-card plh-card--feature">
					<div class="plh-card__icon-header">
						<span class="dashicons dashicons-admin-plugins"></span>
						<h3><?php esc_html_e( 'Extend PizzaLayer', 'pizzalayer' ); ?></h3>
					</div>
					<div class="plh-card__content">
						<p><?php
							/* translators: /pzttemplates/your-slug/ and /templates/ are directory paths, keep as-is */
							echo wp_kses_post( __( 'Create a <strong>child theme template</strong> by adding a directory at <code>/pzttemplates/your-slug/</code>. Copy a base template from the plugin\'s <code>/templates/</code> folder, then freely edit layout, partials, and CSS.', 'pizzalayer' ) );
						?></p>
						<p><?php
							/* translators: pizzalayer_before_builder etc. are PHP hooks, keep as-is */
							echo wp_kses_post( __( 'Hook into any part of the builder with the full <strong>action &amp; filter API</strong> — <code>pizzalayer_before_builder</code>, <code>pizzalayer_layer_html</code>, <code>pizzalayer_tab_order</code>, and more.', 'pizzalayer' ) );
						?></p>
						<p>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=pizzalayer-help' ) ); ?>" class="button button-secondary">
								<?php esc_html_e( 'Developer Hooks Reference', 'pizzalayer' ); ?>
							</a>
						</p>
					</div>
				</div>

			</div><!-- /.plh-features-row -->

			<?php do_action( 'pizzalayer_admin_home_cards' ); ?>

			<!-- ══ Credits ════════════════════════════════════════════════ -->
			<div class="plh-credits">
				<?php
				printf(
					/* translators: 1: version number, 2: author name, 3: company link HTML */
					wp_kses_post( __( 'PizzaLayer v%1$s &mdash; crafted by <strong>%2$s</strong> / %3$s', 'pizzalayer' ) ),
					esc_html( PIZZALAYER_VERSION ),
					'Ryan Bishop',
					'<a href="https://islandsundesign.com" target="_blank" rel="noopener">Island Sun Design</a>'
				);
				?>
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
			background: linear-gradient(135deg, #7a2e00 0%, #5c1f00 100%);
			border: 1px solid #c84b00; border-radius: 8px;
			padding: 12px 16px; margin-bottom: 16px; font-size: 13px; color: #ffd9b8;
		}
		.plh-pro-cta__icon { font-size: 18px; }
		.plh-pro-cta__text { flex: 1; }
		.plh-pro-cta__text strong { color: #fff; }
		.plh-pro-cta__text a { font-weight: 600; color: #ffad73; }
		.plh-pro-cta__text a:hover { color: #ffd0a8; }
		.plh-pro-cta__dismiss {
			color: #ffad73; text-decoration: none; font-size: 14px;
			padding: 2px 6px; border-radius: 3px; transition: background .15s;
		}
		.plh-pro-cta__dismiss:hover { background: rgba(255,107,53,.25); color: #fff; }

		/* ── Setup nag ────────────────────────────────────────────────── */
		.plh-nag {
			display: flex; align-items: flex-start; gap: 10px;
			background: rgba(34,113,177,.18); border-left: 4px solid #4a9fd4; border-radius: 0 8px 8px 0;
			padding: 14px 18px; margin-bottom: 16px; font-size: 13px; color: #e2e8f0;
		}
		.plh-nag .dashicons { color: #4a9fd4; margin-top: 2px; flex-shrink: 0; }
		.plh-nag strong { display: block; margin-bottom: 6px; color: #fff; }
		.plh-nag__list { margin: 0; padding: 0 0 0 16px; }
		.plh-nag__list li { margin-bottom: 3px; }
		.plh-nag__list a { color: #7ec8e3; }
		.plh-nag__list a:hover { color: #fff; }

		/* ── Stats strip ──────────────────────────────────────────────── */
		.plh-stats-row {
			display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px;
		}
		.plh-stat {
			flex: 1 1 90px; background: #fff; border: 1px solid #e0e3e7;
			border-radius: 8px; padding: 14px 16px; text-align: center;
			position: relative; display: block; text-decoration: none; color: inherit;
			transition: border-color .15s, box-shadow .15s, transform .15s;
		}
		a.plh-stat:hover {
			border-color: #2271b1; box-shadow: 0 2px 8px rgba(0,0,0,.1);
			transform: translateY(-2px);
		}
		a.plh-stat:focus { outline: 2px solid #2271b1; outline-offset: 1px; }
		.plh-stat--total {
			background: #1a1e23; border-color: #1a1e23;
		}
		a.plh-stat--total:hover { border-color: #3a4452; box-shadow: 0 2px 10px rgba(0,0,0,.25); }
		a.plh-stat--warn:hover { border-color: #e0a020; }
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
		.plh-quicknav__item--featured {
			border-color: var(--pzl-qn-accent, #2271b1);
			background: #fff8f8;
			box-shadow: 0 0 0 1px var(--pzl-qn-accent, #2271b1) inset;
		}
		.plh-quicknav__item--featured .plh-quicknav__label {
			color: var(--pzl-qn-accent, #2271b1); font-weight: 700;
		}
		.plh-quicknav__item--featured:hover {
			border-color: var(--pzl-qn-accent, #2271b1);
			color: var(--pzl-qn-accent, #2271b1);
			box-shadow: 0 2px 10px rgba(214,54,56,.22), 0 0 0 1px var(--pzl-qn-accent, #2271b1) inset;
		}

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

		/* ── Credits ──────────────────────────────────────────────────── */
		.plh-credits { padding: 8px 0 24px; font-size: 12px; color: #aaa; }
		.plh-credits a { color: #aaa; text-decoration: none; }
		.plh-credits a:hover { color: #2271b1; }

		/* ── Hero intro ────────────────────────────────────────────────── */
		.plh-hero {
			background: linear-gradient(135deg,#1a1e23 0%,#2d3748 60%,#1e3a5f 100%);
			border-radius: 10px; margin-bottom: 20px; overflow: hidden;
		}
		.plh-hero__inner {
			display: flex; align-items: flex-start; gap: 28px;
			padding: 28px 28px 24px; flex-wrap: wrap;
		}
		.plh-hero__copy { flex: 1; min-width: 260px; }
		.plh-hero__heading { margin: 0 0 10px; font-size: 20px; font-weight: 700; color: #fff; line-height: 1.3; }
		.plh-hero__text { margin: 0 0 18px; font-size: 13px; color: #a0aec0; line-height: 1.65; }
		.plh-hero__steps { display: flex; flex-direction: column; gap: 8px; margin-bottom: 20px; }
		.plh-hero__step { display: flex; align-items: flex-start; gap: 10px; font-size: 13px; color: #cbd5e0; }
		.plh-hero__step-num {
			display: flex; align-items: center; justify-content: center;
			width: 22px; height: 22px; border-radius: 50%;
			background: #ff6b35; color: #fff; font-size: 11px; font-weight: 700; flex-shrink: 0; margin-top: 1px;
		}
		.plh-hero__step strong { color: #fff; }
		.plh-hero__step code { background: rgba(255,255,255,.12); padding: 1px 5px; border-radius: 3px; font-size: 11px; color: #a3d977; }
		.plh-hero__btns { display: flex; gap: 8px; flex-wrap: wrap; }
		.plh-hero__btns .button { display: inline-flex !important; align-items: center; gap: 5px; }
		.plh-hero__btns .dashicons { font-size: 14px !important; width: 14px !important; height: 14px !important; }
		.plh-hero__stats-side {
			display: flex; flex-direction: column; gap: 10px;
			min-width: 180px; padding-top: 4px;
		}
		.plh-hero__stat-pill {
			display: flex; align-items: center; gap: 10px;
			background: rgba(255,255,255,.07); border-radius: 8px; padding: 10px 14px;
		}
		.plh-hero__pill-icon { font-size: 18px !important; width: 18px !important; height: 18px !important; color: #ff6b35; flex-shrink: 0; }
		.plh-hero__pill-label { display: block; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: #718096; margin-bottom: 2px; }
		.plh-hero__pill-val { display: block; font-size: 15px; font-weight: 700; color: #fff; }
		.plh-hero__view-site {
			display: inline-flex; align-items: center; gap: 6px;
			font-size: 12px; color: #a0aec0; text-decoration: none;
			padding: 8px 14px; border: 1px solid rgba(255,255,255,.12);
			border-radius: 6px; transition: background .15s, color .15s; margin-top: 4px;
		}
		.plh-hero__view-site:hover { background: rgba(255,255,255,.08); color: #fff; }
		.plh-hero__view-site .dashicons { font-size: 13px !important; width: 13px !important; height: 13px !important; }
		</style>
		<?php
	}

	}
