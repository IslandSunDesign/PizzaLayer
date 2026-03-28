<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
/* +======================================================+
 |  PizzaLayer Admin Home                               |
 +======================================================+ */

/* +===  Render Dashboard Tab Panel  ===+ */
function pizzalayer_dashboard_home_tab_panel( $pz_panel_slug, $pz_panel_title, $pz_panel_content, $pz_panel_is_active ) {
	$active_class = ( $pz_panel_is_active === 'yes' ) ? ' active' : '';
	return '<!-- Tab: ' . esc_html( $pz_panel_title ) . ' -->
		<div id="pizzalayer-tab-' . esc_attr( $pz_panel_slug ) . '" class="pizzalayer-tab-content' . esc_attr( $active_class ) . '">
			<div class="pizzalayer-tab-inner">' . wp_kses_post( $pz_panel_content ) . '</div>
		</div>';
}

/* +===  Feature Box  ===+ */
function pizzalayer_dashboard_home_box_section( $pz_home_box_title, $pz_home_box_content, $pz_home_box_icon ) {
	return '<div class="plh-feature-box">
				<div class="plh-feature-box__icon"><span class="dashicons ' . esc_attr( $pz_home_box_icon ) . '"></span></div>
				<h3 class="plh-feature-box__title">' . esc_html( $pz_home_box_title ) . '</h3>
				<div class="plh-feature-box__content">' . wp_kses_post( $pz_home_box_content ) . '</div>
			</div>';
}

/* +===  Render PizzaLayer Dashboard Homepage  ===+ */
function pizzalayer_render_dashboard_home_page() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }

	/* +=== Live stats ===+ */
	$stat_crusts   = wp_count_posts( 'pizzalayer_crusts' )->publish   ?? 0;
	$stat_sauces   = wp_count_posts( 'pizzalayer_sauces' )->publish   ?? 0;
	$stat_cheeses  = wp_count_posts( 'pizzalayer_cheeses' )->publish  ?? 0;
	$stat_toppings = wp_count_posts( 'pizzalayer_toppings' )->publish ?? 0;
	$stat_drizzles = wp_count_posts( 'pizzalayer_drizzles' )->publish ?? 0;
	$stat_cuts     = wp_count_posts( 'pizzalayer_cuts' )->publish     ?? 0;
	$total_layers  = $stat_crusts + $stat_sauces + $stat_cheeses + $stat_toppings + $stat_drizzles + $stat_cuts;

	/* +=== Layer tab descriptions with action links ===+ */
	$layer_tabs = array(
		'crusts'   => array(
			'label'   => 'Crusts',
			'icon'    => 'dashicons-tag',
			'desc'    => 'Your crust is the canvas. Define every base your pizza can be built on — from thin & crispy to thick & pillowy. Each crust item gets its own layer image that stacks in the live visualizer.',
			'tip'     => '💡 Tip: Use a transparent PNG against a consistent circle canvas for the crispest stacking results.',
			'count'   => $stat_crusts,
			'cpt'     => 'crusts',
		),
		'sauces'   => array(
			'label'   => 'Sauces',
			'icon'    => 'dashicons-admin-generic',
			'desc'    => 'The sauce defines the flavor direction. Whether it\'s a bold marinara, a smoky BBQ, or a creamy garlic white — each sauce is a distinct layer image that sits on top of the crust in the visualizer.',
			'tip'     => '💡 Tip: Keep sauce layer images semi-transparent around the edges for a natural blending effect on the pizza.',
			'count'   => $stat_sauces,
			'cpt'     => 'sauces',
		),
		'cheeses'  => array(
			'label'   => 'Cheeses',
			'icon'    => 'dashicons-category',
			'desc'    => 'Cheese sits between the sauce and toppings in your stack. By keeping it a separate category, you can offer mozzarella, provolone, dairy-free alternatives, and more — each with its own visual layer.',
			'tip'     => '💡 Tip: A subtle melt texture with a slight golden edge makes cheese layers look mouth-wateringly real.',
			'count'   => $stat_cheeses,
			'cpt'     => 'cheeses',
		),
		'toppings' => array(
			'label'   => 'Toppings',
			'icon'    => 'dashicons-star-filled',
			'desc'    => 'Toppings are where the action is. Every topping has its own layer image, price data, and coverage options (whole, half, quarters). The visualizer renders each selected topping in real time as your customer builds.',
			'tip'     => '💡 Tip: Use consistent image dimensions for all topping PNGs — 500×500px transparent circles work great across templates.',
			'count'   => $stat_toppings,
			'cpt'     => 'toppings',
		),
		'drizzles' => array(
			'label'   => 'Drizzles',
			'icon'    => 'dashicons-admin-customizer',
			'desc'    => 'Drizzles are the finishing touch — balsamic glaze, hot honey, ranch swirl. They layer above toppings in the visualizer and give your menu a premium feel with minimal setup.',
			'tip'     => '💡 Tip: Drizzle images look best with a flowing, asymmetric pattern that feels handcrafted, not perfectly symmetrical.',
			'count'   => $stat_drizzles,
			'cpt'     => 'drizzles',
		),
		'cuts'     => array(
			'label'   => 'Cuts',
			'icon'    => 'dashicons-editor-table',
			'desc'    => 'Define how a finished pizza gets sliced. Square cuts, classic triangles, party-style, or left whole — each cut style gets its own overlay layer image that drops on top of the finished pizza.',
			'tip'     => '💡 Tip: Cut overlay PNGs should use a very thin line weight with a slight transparency so the toppings below still show through.',
			'count'   => $stat_cuts,
			'cpt'     => 'cuts',
		),
	);

	/* +=== Rotator: Tips & Tricks ===+ */
	$tips_slides = array(
		'Keep layer images lean — use WebP or transparent PNG at a consistent canvas size for crisp, predictable stacking.',
		'Name CPT slugs cleanly (e.g. <code>pepperoni</code>, <code>thin-crust</code>) — they feed directly into CSS classes and JS data keys.',
		'Offer half and quarter topping coverage to boost average order value without overwhelming the decision.',
		'Use a child theme template for full visual control while staying safely update-proof.',
		'Cache thumbnails and preload your first visible layer set for a snappier first paint on slower connections.',
		'Document your price grid rules in the product notes field — your future self will thank you.',
	);
	$tips_html = '<div class="pizzalayer-rotator" data-interval="6000">';
	foreach ( $tips_slides as $i => $text ) {
		$active     = $i === 0 ? ' is-active' : '';
		$tips_html .= '<div class="pz-rotator-slide' . esc_attr( $active ) . '">' . wp_kses_post( $text ) . '</div>';
	}
	$tips_html .= '</div>';

	/* +=== Extend PizzaLayer ===+ */
	$extend_html  = '<p>Want total visual control? Create a <strong>child theme</strong> and add a directory at <code>/pzttemplates/your-template-slug/</code>. Copy a base template from the plugin\'s <code>/templates/</code> folder, then freely edit the layout, partials, and CSS — your changes stay safe through plugin updates.</p>';
	$extend_html .= '<p>Switch between templates at any time from <em>PizzaLayer → My Template</em>.</p>';
	$extend_html .= '<p><a href="#" class="button button-secondary">Template Developer Guide <span style="font-size:11px;">(coming soon)</span></a></p>';

	/* +=== Active template ===+ */
	$active_template_slug = get_option( 'pizzalayer_active_template', 'default' );

	?>
	<div class="wrap plh-wrap">

		<!-- +=== Header bar ===+ -->
		<div class="plh-header">
			<div class="plh-header__brand">
				<span class="dashicons dashicons-pizza plh-header__icon" aria-hidden="true"></span>
				<div>
					<h1 class="plh-header__title">PizzaLayer</h1>
					<p class="plh-header__tagline">The WordPress pizza builder — powered by WooCommerce.</p>
				</div>
			</div>
			<div class="plh-header__actions">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=pizzalayer_my_template' ) ); ?>" class="button button-primary">
					<span class="dashicons dashicons-admin-appearance"></span> Choose Template
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=pizzalayer_setup_guide' ) ); ?>" class="button">
					<span class="dashicons dashicons-welcome-learn-more"></span> Setup Guide
				</a>
				<a href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>" class="button" target="_blank" rel="noopener">
					<span class="dashicons dashicons-admin-generic"></span> Customizer
				</a>
			</div>
		</div>

		<!-- +=== Stats row ===+ -->
		<div class="plh-stats-row">
			<div class="plh-stat">
				<span class="plh-stat__number"><?php echo esc_html( $total_layers ); ?></span>
				<span class="plh-stat__label">Total Layers</span>
			</div>
			<div class="plh-stat">
				<span class="plh-stat__number"><?php echo esc_html( $stat_toppings ); ?></span>
				<span class="plh-stat__label">Toppings</span>
			</div>
			<div class="plh-stat">
				<span class="plh-stat__number"><?php echo esc_html( $stat_crusts ); ?></span>
				<span class="plh-stat__label">Crusts</span>
			</div>
			<div class="plh-stat">
				<span class="plh-stat__number"><?php echo esc_html( $stat_sauces ); ?></span>
				<span class="plh-stat__label">Sauces</span>
			</div>
			<div class="plh-stat">
				<span class="plh-stat__number"><?php echo esc_html( $stat_cheeses + $stat_drizzles + $stat_cuts ); ?></span>
				<span class="plh-stat__label">Cheese / Drizzle / Cuts</span>
			</div>
			<div class="plh-stat plh-stat--template">
				<span class="plh-stat__number plh-stat__number--sm"><?php echo esc_html( ucwords( str_replace( '-', ' ', $active_template_slug ) ) ); ?></span>
				<span class="plh-stat__label">Active Template</span>
			</div>
		</div>

		<!-- +=== Layer Manager Tabs ===+ -->
		<div class="plh-card plh-card--tabs">
			<div class="plh-card__head">
				<h2 class="plh-card__title"><span class="dashicons dashicons-category"></span> Layer Manager</h2>
				<p class="plh-card__subtitle">Select a layer type below to learn about it and jump directly to its content.</p>
			</div>

			<nav class="plh-tabnav" id="plh-layer-tabs" role="tablist">
				<?php
				$first = true;
				foreach ( $layer_tabs as $slug => $tab ) :
					$active_class = $first ? ' plh-tab--active' : '';
					?>
					<button class="plh-tab<?php echo esc_attr( $active_class ); ?>"
					        data-tab="<?php echo esc_attr( $slug ); ?>"
					        role="tab"
					        aria-selected="<?php echo $first ? 'true' : 'false'; ?>"
					        aria-controls="plh-panel-<?php echo esc_attr( $slug ); ?>">
						<span class="dashicons <?php echo esc_attr( $tab['icon'] ); ?>"></span>
						<?php echo esc_html( $tab['label'] ); ?>
						<span class="plh-tab__count"><?php echo esc_html( $tab['count'] ); ?></span>
					</button>
					<?php $first = false; endforeach; ?>
			</nav>

			<div class="plh-panels">
				<?php
				$first = true;
				foreach ( $layer_tabs as $slug => $tab ) :
					$active_class = $first ? ' plh-panel--active' : '';
					?>
					<div class="plh-panel<?php echo esc_attr( $active_class ); ?>"
					     id="plh-panel-<?php echo esc_attr( $slug ); ?>"
					     role="tabpanel">
						<div class="plh-panel__body">
							<div class="plh-panel__text">
								<p><?php echo esc_html( $tab['desc'] ); ?></p>
								<p class="plh-panel__tip"><?php echo wp_kses_post( $tab['tip'] ); ?></p>
							</div>
							<div class="plh-panel__actions">
								<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=pizzalayer_' . $tab['cpt'] ) ); ?>"
								   class="button">
									<span class="dashicons dashicons-list-view"></span>
									View All <?php echo esc_html( $tab['label'] ); ?>
									<?php if ( $tab['count'] > 0 ) : ?>
										<span class="plh-count-badge"><?php echo esc_html( $tab['count'] ); ?></span>
									<?php endif; ?>
								</a>
								<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=pizzalayer_' . $tab['cpt'] ) ); ?>"
								   class="button button-primary">
									<span class="dashicons dashicons-plus-alt2"></span>
									Add New <?php echo esc_html( rtrim( $tab['label'], 's' ) ); ?>
								</a>
							</div>
						</div>
					</div>
					<?php $first = false; endforeach; ?>
			</div>
		</div>

		<!-- +=== Three-column feature cards ===+ -->
		<div class="plh-features-row">

			<!-- Tips & Tricks -->
			<div class="plh-card plh-card--feature">
				<div class="plh-card__icon-header">
					<span class="dashicons dashicons-admin-tools"></span>
					<h3>Tips & Tricks</h3>
				</div>
				<div class="plh-card__content">
					<div class="plh-rotator-wrap">
						<?php echo $tips_html; ?>
						<div class="plh-rotator-dots" id="plh-rotator-dots" aria-hidden="true">
							<?php for ( $i = 0; $i < count( $tips_slides ); $i++ ) : ?>
								<span class="plh-rotator-dot<?php echo $i === 0 ? ' is-active' : ''; ?>"></span>
							<?php endfor; ?>
						</div>
					</div>
				</div>
			</div>

			<!-- Extend PizzaLayer -->
			<div class="plh-card plh-card--feature">
				<div class="plh-card__icon-header">
					<span class="dashicons dashicons-admin-plugins"></span>
					<h3>Extend PizzaLayer</h3>
				</div>
				<div class="plh-card__content">
					<?php echo wp_kses_post( $extend_html ); ?>
				</div>
			</div>

			<!-- Quick Access -->
			<div class="plh-card plh-card--feature plh-card--quicklinks">
				<div class="plh-card__icon-header">
					<span class="dashicons dashicons-arrow-right-alt"></span>
					<h3>Quick Access</h3>
				</div>
				<ul class="plh-quicklinks">
					<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=pizzalayer_setup_guide' ) ); ?>"><span class="dashicons dashicons-welcome-learn-more"></span> Setup Guide</a></li>
					<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=pizzalayer_my_template' ) ); ?>"><span class="dashicons dashicons-admin-appearance"></span> Choose a Template</a></li>
					<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=pizzalayer_shortcode_generator' ) ); ?>"><span class="dashicons dashicons-editor-code"></span> Shortcode Generator</a></li>
					<li><a href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>" target="_blank" rel="noopener"><span class="dashicons dashicons-admin-generic"></span> WP Customizer</a></li>
					<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=pizzalayer_toppings' ) ); ?>"><span class="dashicons dashicons-star-filled"></span> Manage Toppings</a></li>
					<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=product' ) ); ?>"><span class="dashicons dashicons-cart"></span> WooCommerce Products</a></li>
				</ul>
			</div>

		</div>

		<!-- +=== Credits ===+ -->
		<div class="plh-credits">
			<p>PizzaLayer is crafted by <strong>Ryan Bishop</strong> — WordPress plugin developer at <a href="https://islandsundesign.com" target="_blank" rel="noopener">IslandSunDesign.com</a>.</p>
		</div>

	</div><!-- /.plh-wrap -->

	<!-- +=== Tab Script ===+ -->
	<script>
	document.addEventListener( 'DOMContentLoaded', function () {

		// ── Layer tabs ──
		var tabs     = document.querySelectorAll( '.plh-tab' );
		var panels   = document.querySelectorAll( '.plh-panel' );

		tabs.forEach( function ( tab ) {
			tab.addEventListener( 'click', function () {
				tabs.forEach( function(t){ t.classList.remove('plh-tab--active'); t.setAttribute('aria-selected','false'); });
				panels.forEach( function(p){ p.classList.remove('plh-panel--active'); });
				tab.classList.add( 'plh-tab--active' );
				tab.setAttribute( 'aria-selected', 'true' );
				var panel = document.getElementById( 'plh-panel-' + tab.dataset.tab );
				if ( panel ) { panel.classList.add( 'plh-panel--active' ); }
			} );
		} );

	} );

	// ── Tips rotator ──
	jQuery( document ).ready( function () {
		jQuery( '.pizzalayer-rotator' ).each( function () {
			var $rotator   = jQuery( this );
			var intervalMs = parseInt( $rotator.attr( 'data-interval' ), 10 ) || 6000;
			var $slides    = $rotator.find( '.pz-rotator-slide' );
			var $dots      = jQuery( '#plh-rotator-dots' ).find( '.plh-rotator-dot' );
			var idx        = 0;
			var isAnim     = false;

			$slides.hide().attr( 'aria-hidden', 'true' );
			$slides.first().show().addClass( 'is-active' ).attr( 'aria-hidden', 'false' );

			function advance() {
				if ( isAnim ) { return; }
				isAnim = true;
				var $cur  = $slides.eq( idx );
				var next  = ( idx + 1 ) % $slides.length;
				var $next = $slides.eq( next );
				$dots.eq( idx ).removeClass( 'is-active' );
				$cur.stop( true, true ).fadeOut( 350, function () {
					$cur.removeClass( 'is-active' ).attr( 'aria-hidden', 'true' );
					$next.stop( true, true ).fadeIn( 350, function () {
						$next.addClass( 'is-active' ).attr( 'aria-hidden', 'false' );
						$dots.eq( next ).addClass( 'is-active' );
						idx = next;
						isAnim = false;
					} );
				} );
			}
			setInterval( advance, intervalMs );
		} );
	} );
	</script>

	<!-- +=== Styles ===+ -->
	<style>
		/* ── Wrap ── */
		.plh-wrap { max-width: 1200px; }

		/* ── Header ── */
		.plh-header {
			display: flex; align-items: center; justify-content: space-between;
			flex-wrap: wrap; gap: 16px;
			background: #1d2023; color: #fff;
			border-radius: 10px; padding: 22px 28px; margin-bottom: 20px;
		}
		.plh-header__brand { display: flex; align-items: center; gap: 16px; }
		.plh-header__icon { font-size: 36px !important; width: 36px !important; height: 36px !important; color: #ff6b35; }
		.plh-header__title { margin: 0; font-size: 24px; font-weight: 700; color: #fff; }
		.plh-header__tagline { margin: 2px 0 0; color: #a0a8b0; font-size: 13px; }
		.plh-header__actions { display: flex; gap: 8px; flex-wrap: wrap; }
		.plh-header__actions .button { display: flex; align-items: center; gap: 5px; }
		.plh-header__actions .dashicons { font-size: 16px !important; width: 16px !important; height: 16px !important; margin: 0; }

		/* ── Stats row ── */
		.plh-stats-row {
			display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 20px;
		}
		.plh-stat {
			flex: 1 1 100px; background: #fff; border: 1px solid #e0e3e7;
			border-radius: 8px; padding: 14px 18px; text-align: center;
		}
		.plh-stat__number { display: block; font-size: 28px; font-weight: 700; color: #1d2023; line-height: 1.1; }
		.plh-stat__number--sm { font-size: 15px; padding-top: 6px; }
		.plh-stat__label { display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.06em; color: #787c82; margin-top: 4px; }
		.plh-stat--template { flex: 1 1 140px; }

		/* ── Generic card ── */
		.plh-card {
			background: #fff; border: 1px solid #e0e3e7; border-radius: 10px;
			margin-bottom: 20px; overflow: hidden;
		}
		.plh-card__head { padding: 20px 24px 0; }
		.plh-card__title { margin: 0 0 4px; font-size: 16px; display: flex; align-items: center; gap: 8px; }
		.plh-card__title .dashicons { color: #646970; font-size: 18px !important; width: 18px !important; height: 18px !important; }
		.plh-card__subtitle { margin: 0 0 16px; color: #646970; font-size: 13px; }

		/* ── Tab nav ── */
		.plh-tabnav {
			display: flex; gap: 0; overflow-x: auto;
			border-bottom: 2px solid #e0e3e7;
			padding: 0 16px; background: #f8f9fa;
		}
		.plh-tab {
			display: flex; align-items: center; gap: 6px;
			padding: 10px 16px; border: none; border-bottom: 2px solid transparent;
			background: transparent; cursor: pointer; font-size: 13px; font-weight: 500;
			color: #646970; white-space: nowrap; margin-bottom: -2px;
			transition: color .15s, border-color .15s;
		}
		.plh-tab:hover { color: #1d2023; }
		.plh-tab--active { color: #2271b1; border-bottom-color: #2271b1; font-weight: 600; }
		.plh-tab .dashicons { font-size: 14px !important; width: 14px !important; height: 14px !important; }
		.plh-tab__count {
			background: #e0e3e7; color: #646970; border-radius: 999px;
			font-size: 10px; font-weight: 700; padding: 1px 6px; line-height: 1.4;
		}
		.plh-tab--active .plh-tab__count { background: #dce8f7; color: #2271b1; }

		/* ── Panels ── */
		.plh-panels { padding: 0; }
		.plh-panel { display: none; }
		.plh-panel--active { display: block; }
		.plh-panel__body {
			display: flex; align-items: flex-start; justify-content: space-between;
			gap: 24px; flex-wrap: wrap;
			padding: 20px 24px;
		}
		.plh-panel__text { flex: 1 1 300px; }
		.plh-panel__text p { margin: 0 0 8px; }
		.plh-panel__tip { background: #f6f7f7; border-left: 3px solid #f0b849; padding: 10px 14px; border-radius: 0 6px 6px 0; font-size: 13px; color: #3c434a; }
		.plh-panel__actions { display: flex; flex-direction: column; gap: 8px; flex-shrink: 0; }
		.plh-panel__actions .button { display: flex; align-items: center; gap: 6px; white-space: nowrap; }
		.plh-panel__actions .dashicons { font-size: 14px !important; width: 14px !important; height: 14px !important; }
		.plh-count-badge {
			background: #fff; border: 1px solid #ccd0d4; border-radius: 999px;
			font-size: 11px; font-weight: 600; padding: 0 7px; line-height: 1.6;
		}

		/* ── Feature cards row ── */
		.plh-features-row {
			display: grid;
			grid-template-columns: repeat( auto-fit, minmax( 280px, 1fr ) );
			gap: 20px; margin-bottom: 20px;
		}
		.plh-card--feature { margin-bottom: 0; }
		.plh-card__icon-header {
			display: flex; align-items: center; gap: 10px;
			padding: 18px 20px 10px; border-bottom: 1px solid #f0f0f0;
		}
		.plh-card__icon-header .dashicons { font-size: 20px !important; width: 20px !important; height: 20px !important; color: #2271b1; }
		.plh-card__icon-header h3 { margin: 0; font-size: 14px; font-weight: 600; }
		.plh-card__content { padding: 16px 20px; font-size: 13px; color: #3c434a; }
		.plh-card__content p { margin: 0 0 10px; }
		.plh-card__content p:last-child { margin-bottom: 0; }
		.plh-card__content code { background: #f0f0f1; padding: 1px 5px; border-radius: 3px; font-size: 12px; }

		/* Rotator */
		.plh-rotator-wrap { position: relative; min-height: 80px; }
		.pizzalayer-rotator { position: relative; }
		.pz-rotator-slide { display: none; font-size: 13px; line-height: 1.6; }
		.pz-rotator-slide.is-active { display: block; }
		.pz-rotator-slide code { background: #f0f0f1; padding: 1px 5px; border-radius: 3px; font-size: 12px; }
		.plh-rotator-dots { display: flex; gap: 5px; margin-top: 14px; }
		.plh-rotator-dot { width: 6px; height: 6px; border-radius: 50%; background: #dcdcdc; transition: background .2s; }
		.plh-rotator-dot.is-active { background: #2271b1; }

		/* Quick links */
		.plh-quicklinks { margin: 0; padding: 0; list-style: none; }
		.plh-quicklinks li { border-bottom: 1px solid #f0f0f0; }
		.plh-quicklinks li:last-child { border-bottom: none; }
		.plh-quicklinks a {
			display: flex; align-items: center; gap: 8px; padding: 8px 4px;
			color: #2271b1; text-decoration: none; font-size: 13px;
			transition: color .15s;
		}
		.plh-quicklinks a:hover { color: #135e96; }
		.plh-quicklinks .dashicons { font-size: 15px !important; width: 15px !important; height: 15px !important; color: #646970; }

		/* ── Credits ── */
		.plh-credits { padding: 12px 0 20px; font-size: 12px; color: #787c82; }
		.plh-credits a { color: #787c82; }
	</style>
	<?php
}
