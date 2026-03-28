<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* +======================================================+
 |  PizzaLayer — Setup Guide                            |
 +======================================================+ */

function pizzalayer_render_dashboard_intro_page() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }

	/* ── Layer tabs: each has a brief description + setup steps ── */
	$layer_tabs = array(
		'crusts' => array(
			'label'   => 'Crusts',
			'icon'    => 'dashicons-tag',
			'intro'   => 'Crusts are the foundation of every pizza in the builder. Add at least one crust before testing the visualizer.',
			'steps'   => array(
				'Go to <strong>PizzaLayer → Crusts</strong> and click <strong>Add New</strong>.',
				'Enter a title, e.g. <code>Thin Crust</code> or <code>Stuffed Crust</code>.',
				'Upload a <strong>layer image</strong> in the <em>Crust Layer Image</em> field — this is the image that renders on the pizza.',
				'Optionally add a menu thumbnail in the <em>Crust Image</em> field (shown in the builder card).',
				'Fill in the <strong>Price Grid</strong> box with size and pricing rows.',
				'Click <strong>Publish</strong> when done.',
			),
			'tip'     => 'Use transparent PNGs on a consistent square canvas (e.g. 800×800px) for the cleanest layer stacking.',
			'cpt'     => 'crusts',
		),
		'sauces' => array(
			'label'   => 'Sauces',
			'icon'    => 'dashicons-admin-generic',
			'intro'   => 'Sauces render as a layer directly on top of the crust. Add at least one to enable sauce selection in the builder.',
			'steps'   => array(
				'Go to <strong>PizzaLayer → Sauces</strong> and click <strong>Add New</strong>.',
				'Enter a title, e.g. <code>Classic Tomato</code> or <code>Garlic White</code>.',
				'Upload a <strong>Sauce Layer Image</strong> — this is the visual overlay on the pizza circle.',
				'Optionally add a <strong>Sauce Image</strong> for the selection card thumbnail.',
				'Set pricing in the <strong>Price Grid</strong> if sauces have an upcharge.',
				'Click <strong>Publish</strong>.',
			),
			'tip'     => 'Semi-transparent layer images with soft edges look most natural when layered on top of a crust.',
			'cpt'     => 'sauces',
		),
		'cheeses' => array(
			'label'   => 'Cheeses',
			'icon'    => 'dashicons-category',
			'intro'   => 'Cheeses are a separate layer type that sits between the sauce and toppings — great for offering Mozzarella, Vegan, Provolone, and more.',
			'steps'   => array(
				'Go to <strong>PizzaLayer → Cheeses</strong> and click <strong>Add New</strong>.',
				'Give it a clear name, e.g. <code>Mozzarella</code>.',
				'Upload a <strong>Cheese Layer Image</strong> (the visual overlay).',
				'Optionally add a card thumbnail and price grid rows.',
				'Click <strong>Publish</strong>.',
			),
			'tip'     => 'A subtle melt pattern with a golden edge makes cheese images look convincingly realistic.',
			'cpt'     => 'cheeses',
		),
		'toppings' => array(
			'label'   => 'Toppings',
			'icon'    => 'dashicons-star-filled',
			'intro'   => 'Toppings are the heart of the builder. Each one gets its own layer image, price data, and supports whole / half / quarter coverage placement.',
			'steps'   => array(
				'Go to <strong>PizzaLayer → Toppings</strong> and click <strong>Add New</strong>.',
				'Enter a name, e.g. <code>Pepperoni</code>.',
				'Upload a <strong>Topping Layer Image</strong> — this stacks on the pizza in real time when selected.',
				'Optionally add a <strong>Topping Image</strong> for the card thumbnail.',
				'Set the <strong>Price Grid</strong> pricing per size and fraction.',
				'Set a <strong>Max Toppings</strong> limit in <em>PizzaLayer → Settings</em> if needed.',
				'Click <strong>Publish</strong>.',
			),
			'tip'     => 'Use consistent 500×500px transparent PNGs for all toppings — this keeps layers perfectly aligned across templates.',
			'cpt'     => 'toppings',
		),
		'drizzles' => array(
			'label'   => 'Drizzles',
			'icon'    => 'dashicons-admin-customizer',
			'intro'   => 'Drizzles are optional finishing layers that appear on top of everything — balsamic glaze, hot honey, ranch swirl, etc.',
			'steps'   => array(
				'Go to <strong>PizzaLayer → Drizzles</strong> and click <strong>Add New</strong>.',
				'Enter a name, e.g. <code>Hot Honey</code>.',
				'Upload a <strong>Drizzle Layer Image</strong>.',
				'Add a card thumbnail and optional price grid rows.',
				'Click <strong>Publish</strong>.',
			),
			'tip'     => 'Asymmetric, flowing drizzle patterns look more handcrafted and appetizing than perfectly symmetrical ones.',
			'cpt'     => 'drizzles',
		),
		'cuts' => array(
			'label'   => 'Cuts',
			'icon'    => 'dashicons-editor-table',
			'intro'   => 'Cut styles render as an overlay on the final pizza — triangle slices, square cuts, party-style, or whole.',
			'steps'   => array(
				'Go to <strong>PizzaLayer → Cuts</strong> and click <strong>Add New</strong>.',
				'Enter a name, e.g. <code>8 Slices</code> or <code>Square Cut</code>.',
				'Upload a <strong>Cut Layer Image</strong> — typically a thin line graphic on a transparent background.',
				'Click <strong>Publish</strong>.',
			),
			'tip'     => 'Keep cut line images subtle — a low-opacity thin line lets the toppings beneath remain the star.',
			'cpt'     => 'cuts',
		),
		'woocommerce' => array(
			'label'   => 'WooCommerce',
			'icon'    => 'dashicons-cart',
			'intro'   => 'PizzaLayer pairs with WooCommerce to handle ordering. Create a Pizza product type and link it to your builder.',
			'steps'   => array(
				'Make sure <strong>WooCommerce is installed and active</strong>.',
				'Go to <strong>Products → Add New</strong> in the WordPress admin.',
				'In the Product Data box, set the product type to <strong>Pizza</strong>.',
				'Fill in the <strong>Pizza Details</strong> tab — set sizes, base price, and any per-topping pricing rules.',
				'Publish the product.',
				'Embed the builder on any page using the shortcode from <em>PizzaLayer → Shortcode Generator</em>.',
			),
			'tip'     => 'Use a test product first and walk through a complete order before going live — verify the order meta, pricing, and confirmation email.',
			'cpt'     => null,
		),
		'shortcode' => array(
			'label'   => 'Shortcode',
			'icon'    => 'dashicons-editor-code',
			'intro'   => 'Once your layers and product are set up, embed the pizza builder onto any page with a shortcode.',
			'steps'   => array(
				'Go to <strong>PizzaLayer → Shortcode Generator</strong>.',
				'Select the product and template you want to use.',
				'Copy the generated shortcode, e.g. <code>[pizzalayer-visualizer]</code>.',
				'Paste it into any Page, Post, or widget area using the Gutenberg block or Classic editor.',
				'Preview the page — the builder should appear with your layers visible.',
			),
			'tip'     => 'You can embed the builder on a dedicated "Build Your Pizza" page and link to it directly from your WooCommerce product.',
			'cpt'     => null,
		),
		'settings' => array(
			'label'   => 'Settings',
			'icon'    => 'dashicons-admin-settings',
			'intro'   => 'Fine-tune PizzaLayer\'s behavior using the WordPress Customizer and plugin settings.',
			'steps'   => array(
				'Open the <strong>WordPress Customizer</strong> via <em>PizzaLayer → Settings</em> or Appearance → Customize.',
				'Find the <strong>PizzaLayer</strong> section and adjust defaults — default crust, default sauce, max toppings, etc.',
				'Set a <strong>Default Template</strong> from <em>PizzaLayer → My Template</em>.',
				'Save and preview your storefront to confirm everything looks correct.',
			),
			'tip'     => 'Setting sensible defaults (pre-selected crust and sauce) reduces friction and helps customers start building faster.',
			'cpt'     => null,
		),
	);

	/* ── Checklist items ── */
	$checklist = array(
		array( 'label' => 'Install &amp; activate PizzaLayer',                   'link' => null ),
		array( 'label' => 'Install &amp; activate WooCommerce',                  'link' => null ),
		array( 'label' => 'Add at least one Crust',                              'link' => admin_url( 'post-new.php?post_type=pizzalayer_crusts' ) ),
		array( 'label' => 'Add at least one Sauce',                              'link' => admin_url( 'post-new.php?post_type=pizzalayer_sauces' ) ),
		array( 'label' => 'Add at least one Cheese',                             'link' => admin_url( 'post-new.php?post_type=pizzalayer_cheeses' ) ),
		array( 'label' => 'Add your Toppings',                                   'link' => admin_url( 'post-new.php?post_type=pizzalayer_toppings' ) ),
		array( 'label' => 'Add Drizzles (optional)',                             'link' => admin_url( 'post-new.php?post_type=pizzalayer_drizzles' ) ),
		array( 'label' => 'Add Cut styles (optional)',                           'link' => admin_url( 'post-new.php?post_type=pizzalayer_cuts' ) ),
		array( 'label' => 'Create a WooCommerce Pizza product',                  'link' => admin_url( 'post-new.php?post_type=product' ) ),
		array( 'label' => 'Choose a template in PizzaLayer → My Template',      'link' => admin_url( 'admin.php?page=pizzalayer_my_template' ) ),
		array( 'label' => 'Embed the builder shortcode on a page',               'link' => admin_url( 'admin.php?page=pizzalayer_shortcode_generator' ) ),
		array( 'label' => 'Place a test order end-to-end',                       'link' => null ),
	);

	?>
	<div class="wrap psg-wrap">

		<!-- +=== Page header ===+ -->
		<div class="psg-page-header">
			<span class="dashicons dashicons-welcome-learn-more psg-page-header__icon"></span>
			<div>
				<h1 class="psg-page-header__title">Setup Guide</h1>
				<p class="psg-page-header__sub">Everything you need to get PizzaLayer up and running — in the right order.</p>
			</div>
		</div>

		<!-- +=== Checklist card ===+ -->
		<div class="psg-card psg-card--checklist">
			<div class="psg-card__head">
				<h2><span class="dashicons dashicons-yes-alt"></span> Setup Checklist</h2>
				<p>Work through these steps in order. Click any item that has a link to jump straight to the right screen.</p>
			</div>
			<ol class="psg-checklist">
				<?php foreach ( $checklist as $idx => $item ) : ?>
					<li class="psg-checklist__item">
						<span class="psg-checklist__num"><?php echo esc_html( $idx + 1 ); ?></span>
						<span class="psg-checklist__label">
							<?php if ( $item['link'] ) : ?>
								<a href="<?php echo esc_url( $item['link'] ); ?>"><?php echo wp_kses_post( $item['label'] ); ?></a>
							<?php else : ?>
								<?php echo wp_kses_post( $item['label'] ); ?>
							<?php endif; ?>
						</span>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>

		<!-- +=== Layer guide tabs ===+ -->
		<div class="psg-card psg-card--tabs">
			<div class="psg-card__head">
				<h2><span class="dashicons dashicons-category"></span> Layer-by-Layer Setup Guide</h2>
				<p>Select a section to see step-by-step instructions for setting it up.</p>
			</div>

			<nav class="psg-tabnav" id="psg-tabs" role="tablist">
				<?php
				$first = true;
				foreach ( $layer_tabs as $slug => $tab ) :
					?>
					<button class="psg-tab<?php echo $first ? ' psg-tab--active' : ''; ?>"
					        data-tab="<?php echo esc_attr( $slug ); ?>"
					        role="tab"
					        aria-selected="<?php echo $first ? 'true' : 'false'; ?>"
					        aria-controls="psg-panel-<?php echo esc_attr( $slug ); ?>">
						<span class="dashicons <?php echo esc_attr( $tab['icon'] ); ?>"></span>
						<?php echo esc_html( $tab['label'] ); ?>
					</button>
					<?php $first = false; endforeach; ?>
			</nav>

			<div class="psg-panels">
				<?php
				$first = true;
				foreach ( $layer_tabs as $slug => $tab ) :
					?>
					<div class="psg-panel<?php echo $first ? ' psg-panel--active' : ''; ?>"
					     id="psg-panel-<?php echo esc_attr( $slug ); ?>"
					     role="tabpanel">

						<p class="psg-panel__intro"><?php echo esc_html( $tab['intro'] ); ?></p>

						<ol class="psg-steps">
							<?php foreach ( $tab['steps'] as $step ) : ?>
								<li class="psg-steps__item"><?php echo wp_kses_post( $step ); ?></li>
							<?php endforeach; ?>
						</ol>

						<div class="psg-panel__tip">
							<span class="dashicons dashicons-lightbulb"></span>
							<?php echo esc_html( $tab['tip'] ); ?>
						</div>

						<?php if ( $tab['cpt'] ) : ?>
							<div class="psg-panel__actions">
								<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=pizzalayer_' . $tab['cpt'] ) ); ?>" class="button">
									<span class="dashicons dashicons-list-view"></span>
									View All <?php echo esc_html( $tab['label'] ); ?>
								</a>
								<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=pizzalayer_' . $tab['cpt'] ) ); ?>" class="button button-primary">
									<span class="dashicons dashicons-plus-alt2"></span>
									Add New <?php echo esc_html( rtrim( $tab['label'], 's' ) ); ?>
								</a>
							</div>
						<?php elseif ( $slug === 'woocommerce' ) : ?>
							<div class="psg-panel__actions">
								<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=product' ) ); ?>" class="button button-primary">
									<span class="dashicons dashicons-cart"></span> Create Pizza Product
								</a>
							</div>
						<?php elseif ( $slug === 'shortcode' ) : ?>
							<div class="psg-panel__actions">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=pizzalayer_shortcode_generator' ) ); ?>" class="button button-primary">
									<span class="dashicons dashicons-editor-code"></span> Open Shortcode Generator
								</a>
							</div>
						<?php elseif ( $slug === 'settings' ) : ?>
							<div class="psg-panel__actions">
								<a href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>" class="button button-primary" target="_blank" rel="noopener">
									<span class="dashicons dashicons-admin-generic"></span> Open Customizer
								</a>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=pizzalayer_my_template' ) ); ?>" class="button">
									<span class="dashicons dashicons-admin-appearance"></span> Choose Template
								</a>
							</div>
						<?php endif; ?>

					</div>
					<?php $first = false; endforeach; ?>
			</div>
		</div>

		<!-- +=== Need help card ===+ -->
		<div class="psg-card psg-card--help">
			<span class="dashicons dashicons-sos psg-card--help__icon"></span>
			<div>
				<h3>Need help?</h3>
				<p>Check the documentation or reach out through <a href="https://islandsundesign.com" target="_blank" rel="noopener">IslandSunDesign.com</a>.</p>
			</div>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=pizzalayer_main_menu' ) ); ?>" class="button">
				← Back to Dashboard
			</a>
		</div>

	</div><!-- /.psg-wrap -->

	<!-- +=== Tab Script ===+ -->
	<script>
	document.addEventListener( 'DOMContentLoaded', function () {
		var tabs   = document.querySelectorAll( '.psg-tab' );
		var panels = document.querySelectorAll( '.psg-panel' );

		tabs.forEach( function ( tab ) {
			tab.addEventListener( 'click', function () {
				tabs.forEach( function(t){ t.classList.remove('psg-tab--active'); t.setAttribute('aria-selected','false'); });
				panels.forEach( function(p){ p.classList.remove('psg-panel--active'); });
				tab.classList.add( 'psg-tab--active' );
				tab.setAttribute( 'aria-selected', 'true' );
				var panel = document.getElementById( 'psg-panel-' + tab.dataset.tab );
				if ( panel ) { panel.classList.add( 'psg-panel--active' ); }
			} );
		} );
	} );
	</script>

	<!-- +=== Styles ===+ -->
	<style>
		/* Wrap */
		.psg-wrap { max-width: 900px; }

		/* Page header */
		.psg-page-header {
			display: flex; align-items: center; gap: 16px;
			background: #1d2023; color: #fff;
			border-radius: 10px; padding: 22px 28px; margin-bottom: 20px;
		}
		.psg-page-header__icon { font-size: 36px !important; width: 36px !important; height: 36px !important; color: #ff6b35; flex-shrink: 0; }
		.psg-page-header__title { margin: 0; font-size: 22px; font-weight: 700; color: #fff; }
		.psg-page-header__sub { margin: 3px 0 0; color: #a0a8b0; font-size: 13px; }

		/* Cards */
		.psg-card {
			background: #fff; border: 1px solid #e0e3e7;
			border-radius: 10px; margin-bottom: 20px; overflow: hidden;
		}
		.psg-card__head { padding: 20px 24px 14px; border-bottom: 1px solid #f0f0f0; }
		.psg-card__head h2 {
			margin: 0 0 4px; font-size: 15px;
			display: flex; align-items: center; gap: 8px;
		}
		.psg-card__head h2 .dashicons { font-size: 18px !important; width: 18px !important; height: 18px !important; color: #646970; }
		.psg-card__head p { margin: 0; color: #646970; font-size: 13px; }

		/* Checklist */
		.psg-checklist {
			margin: 0; padding: 16px 24px 20px;
			list-style: none; counter-reset: none;
			display: grid; grid-template-columns: repeat( auto-fit, minmax( 300px, 1fr ) ); gap: 6px 24px;
		}
		.psg-checklist__item {
			display: flex; align-items: center; gap: 10px;
			padding: 9px 12px; border-radius: 6px; background: #f8f9fa;
			border: 1px solid #e0e3e7; font-size: 13px;
		}
		.psg-checklist__num {
			width: 22px; height: 22px; border-radius: 50%;
			background: #2271b1; color: #fff;
			font-size: 11px; font-weight: 700;
			display: flex; align-items: center; justify-content: center;
			flex-shrink: 0;
		}
		.psg-checklist a { color: #2271b1; text-decoration: none; }
		.psg-checklist a:hover { text-decoration: underline; }

		/* Tab nav */
		.psg-tabnav {
			display: flex; flex-wrap: wrap; gap: 0;
			border-bottom: 2px solid #e0e3e7;
			padding: 0 16px; background: #f8f9fa;
		}
		.psg-tab {
			display: flex; align-items: center; gap: 6px;
			padding: 10px 14px; border: none; border-bottom: 2px solid transparent;
			background: transparent; cursor: pointer; font-size: 13px; font-weight: 500;
			color: #646970; white-space: nowrap; margin-bottom: -2px;
			transition: color .15s, border-color .15s;
		}
		.psg-tab:hover { color: #1d2023; }
		.psg-tab--active { color: #2271b1; border-bottom-color: #2271b1; font-weight: 600; }
		.psg-tab .dashicons { font-size: 14px !important; width: 14px !important; height: 14px !important; }

		/* Panels */
		.psg-panels { padding: 0; }
		.psg-panel { display: none; padding: 22px 24px 24px; }
		.psg-panel--active { display: block; }
		.psg-panel__intro {
			margin: 0 0 16px; font-size: 14px; color: #3c434a;
			padding: 12px 16px; background: #f8f9fa; border-left: 4px solid #2271b1;
			border-radius: 0 6px 6px 0;
		}

		/* Steps */
		.psg-steps {
			margin: 0 0 18px; padding-left: 0; list-style: none;
			counter-reset: psg-step;
		}
		.psg-steps__item {
			display: flex; align-items: flex-start; gap: 12px;
			padding: 10px 0; border-bottom: 1px solid #f0f0f0;
			font-size: 13px; counter-increment: psg-step;
		}
		.psg-steps__item:last-child { border-bottom: none; }
		.psg-steps__item::before {
			content: counter(psg-step);
			display: flex; align-items: center; justify-content: center;
			width: 24px; height: 24px; border-radius: 50%;
			background: #dce8f7; color: #2271b1;
			font-size: 11px; font-weight: 700; flex-shrink: 0; margin-top: 1px;
		}
		.psg-steps__item code { background: #f0f0f1; padding: 1px 5px; border-radius: 3px; font-size: 12px; }

		/* Tip */
		.psg-panel__tip {
			display: flex; align-items: flex-start; gap: 10px;
			background: #fffbf0; border: 1px solid #f0b849; border-radius: 6px;
			padding: 12px 14px; font-size: 13px; color: #3c434a; margin-bottom: 18px;
		}
		.psg-panel__tip .dashicons { color: #f0b849; flex-shrink: 0; margin-top: 1px; font-size: 16px !important; width: 16px !important; height: 16px !important; }

		/* Actions */
		.psg-panel__actions { display: flex; gap: 8px; flex-wrap: wrap; }
		.psg-panel__actions .button { display: flex; align-items: center; gap: 6px; }
		.psg-panel__actions .dashicons { font-size: 14px !important; width: 14px !important; height: 14px !important; }

		/* Help card */
		.psg-card--help {
			display: flex; align-items: center; gap: 20px; flex-wrap: wrap;
			padding: 18px 24px; background: #f6f7f7;
		}
		.psg-card--help__icon { font-size: 28px !important; width: 28px !important; height: 28px !important; color: #646970; flex-shrink: 0; }
		.psg-card--help h3 { margin: 0 0 3px; font-size: 14px; }
		.psg-card--help p { margin: 0; font-size: 13px; color: #646970; }
		.psg-card--help > div { flex: 1; }
	</style>
	<?php
}
