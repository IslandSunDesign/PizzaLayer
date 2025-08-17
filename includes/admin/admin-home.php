<?php
/* +======================================================+
 |  PizzaLayer Admin Home (Enhanced Sliders + New Intro) |
 +======================================================+ */

/* +===  Render PizzaLayer Dashboard Tab Panel  ===+ */
function pizzalayer_dashboard_home_tab_panel( $pz_panel_slug, $pz_panel_title, $pz_panel_content, $pz_panel_is_active ) {
	if ( $pz_panel_is_active === 'yes' ) {
		$pz_panel_active_status_css = ' active';
	} else {
		$pz_panel_active_status_css = '';
	}
	return '<!-- +===  Tab Content Area : ' . esc_html( $pz_panel_title ) . '  ===+ -->
		<div id="pizzalayer-tab-' . esc_attr( $pz_panel_slug ) . '" class="pizzalayer-tab-content' . esc_attr( $pz_panel_active_status_css ) . '">
			<h2>' . esc_html( $pz_panel_title ) . '</h2>
			<div class="pizzalayer-tab-inner">' . wp_kses_post( $pz_panel_content ) . '</div>
		</div>';
}

/* +===  Box Section (no forced <p>, allows sliders/HTML)  ===+ */
function pizzalayer_dashboard_home_box_section( $pz_home_box_title, $pz_home_box_content, $pz_home_box_icon ) {
	return '<div class="pizzalayer-section">
				<h3><span class="dashicons ' . esc_attr( $pz_home_box_icon ) . '"></span> ' . esc_html( $pz_home_box_title ) . '</h3>
				<div class="pizzalayer-section-content">' . wp_kses_post( $pz_home_box_content ) . '</div>
			</div>';
}

/* +===  Render PizzaLayer Dashboard Homepage  ===+ */
function pizzalayer_render_dashboard_home_page() {

	/* +=== Tab content copy ===+ */
	$pz_panel_crusts_content_description   = '<p>The foundation of every great pizza starts with the perfect crust. From thin and crispy to thick and fluffy, build your base your way.</p>';
	$pz_panel_sauces_content_description   = '<p>Splash on the flavor with savory sauces that set the tone. Classic tomato, creamy white, or spicy surprises—your pizza’s story starts here.</p>';
	$pz_panel_cheeses_content_description  = '<p>Get gooey with it! Whether you melt, stretch, or crumble, cheese brings the magic to every bite.</p>';
	$pz_panel_toppings_content_description = '<p>This is where the fun begins—load up your pie with everything from pepperoni to pineapple. Every topping is a personality!</p>';
	$pz_panel_drizzles_content_description = '<p>Finish strong with a final flourish! Sweet, spicy, or zesty, a drizzle adds that chef’s-kiss moment.</p>';
	$pz_panel_cuts_content_description     = '<p>Shape your masterpiece with precision. Whether squares or slices, how you cut is how you conquer.</p>';

	/* +=== Getting Started slider (7s) ===+ */
	$getting_started_slides = array(
		'Install & activate WooCommerce, then create your first Pizza product.',
		'Add your layers: Crusts, Sauces, Cheeses, Toppings, Drizzles, and Cuts (as CPT items).',
		'Open <strong>My Template</strong> to pick a visual template that matches your brand.',
		'Configure dynamic pricing in the Pizza product type (size, halves, and fractions).',
		'Use the shortcode or block to embed the builder on any page.',
		'Preview your pizza builder, place a test order, and verify the order meta.',
	);

	$getting_started_html  = '<div class="pizzalayer-rotator" data-interval="7000">';
	foreach ( $getting_started_slides as $i => $text ) {
		$active              = $i === 0 ? ' is-active' : '';
		$getting_started_html .= '<div class="pz-rotator-slide' . esc_attr( $active ) . '">' . wp_kses_post( $text ) . '</div>';
	}
	$getting_started_html .= '</div>';

	/* +=== Tips & Tricks slider (6s) ===+ */
	$tips_slides = array(
		'Keep layer images lean: use WebP/PNG and consistent canvas sizes for crisp stacking.',
		'Name CPT slugs cleanly (e.g., "pepperoni")—they flow into CSS classes and data keys.',
		'Offer halves/quarters for toppings to boost AOV without decision overload.',
		'Use a child theme template for complete control while staying update-safe.',
		'Cache thumbnails and preload the first visible set for snappier first paint.',
		'Document your price rules in the product notes so future you says thanks.',
	);

	$tips_html  = '<div class="pizzalayer-rotator" data-interval="6000">';
	foreach ( $tips_slides as $i => $text ) {
		$active     = $i === 0 ? ' is-active' : '';
		$tips_html .= '<div class="pz-rotator-slide' . esc_attr( $active ) . '">' . wp_kses_post( $text ) . '</div>';
	}
	$tips_html .= '</div>';

	/* +=== Extend PizzaLayer (3-sentence starter + button) ===+ */
	$extend_html  = '<p>Want full visual control? Create a <strong>child theme</strong> and add a directory at <code>/pzttemplates/your-template-slug/</code>. '
	              . 'Copy a base template from the plugin’s <code>/templates/</code> folder, then tweak layout, partials, and CSS without touching the plugin. '
	              . 'Switch templates anytime from <em>PizzaLayer → My Template</em> and keep your customizations update-safe.</p>';
	$extend_html .= '<p><a href="#" class="button button-secondary">Template Developer Guide (coming soon)</a></p>';

	/* +=== Info boxes (editable) ===+ */
	$boxes = array(
		'active_template' => array(
			'title'   => 'Active Template',
			'content' => 'You are currently using the default Glassy template. Switch templates using the My Template menu.',
		),
		'basic_stats' => array(
			'title'   => 'Basic Stats',
			'content' => 'PizzaLayer is active on 3 products and 18 custom layers.',
		),
		'setup_checklist' => array(
			'title'   => 'Setup Checklist',
			'content' => 'Complete the setup guide to enable all pizza customization features.',
		),
	);

	?>
	<div class="wrap pizzalayer-admin-wrap">

		<!-- +=== Hero / Intro ===+ -->
		<div class="pizzalayer-hero">
			<div class="pizzalayer-hero-left">
				<h1 class="pizzalayer-hero-title">
					<span class="dashicons dashicons-pizza" aria-hidden="true"></span>
					Welcome to PizzaLayer
				</h1>
				<p class="pizzalayer-hero-desc">
					Your all-in-one toolkit for building beautiful, customizable pizza experiences with WordPress + WooCommerce.
					Start by adding layers, pick a template, and drop the builder into any page—easy as pie.
				</p>
				<div class="pizzalayer-hero-cta">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=pizzalayer_my_template' ) ); ?>" class="button button-primary">Choose a Template</a>
					<a href="#" class="button">Setup Guide</a>
					<a href="#" class="button">Docs (soon)</a>
				</div>
			</div>
			<div class="pizzalayer-hero-right">
				<div class="pizzalayer-hero-card">
					<h3><span class="dashicons dashicons-admin-generic"></span> Quick Links</h3>
					<ul class="pizzalayer-quick-links">
						<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=pizzalayer_crusts' ) ); ?>">Manage Crusts</a></li>
						<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=pizzalayer_sauces' ) ); ?>">Manage Sauces</a></li>
						<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=pizzalayer_cheeses' ) ); ?>">Manage Cheeses</a></li>
						<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=pizzalayer_toppings' ) ); ?>">Manage Toppings</a></li>
						<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=pizzalayer_drizzles' ) ); ?>">Manage Drizzles</a></li>
						<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=pizzalayer_cuts' ) ); ?>">Manage Cuts</a></li>
					</ul>
				</div>
			</div>
		</div>

		<!-- +===  Tab Menu  ===+ -->
		<h2 class="nav-tab-wrapper pizzalayer-tabs">
			<a href="#pizzalayer-tab-crusts" class="nav-tab nav-tab-active">Crusts</a>
			<a href="#pizzalayer-tab-sauces" class="nav-tab">Sauces</a>
			<a href="#pizzalayer-tab-cheeses" class="nav-tab">Cheeses</a>
			<a href="#pizzalayer-tab-toppings" class="nav-tab">Toppings</a>
			<a href="#pizzalayer-tab-drizzles" class="nav-tab">Drizzles</a>
			<a href="#pizzalayer-tab-cuts" class="nav-tab">Cuts</a>
			<a href="#pizzalayer-tab-settings" class="nav-tab pizzalayer-open-customizer">Settings <span class="dashicons dashicons-external"></span></a>
		</h2>

		<!-- +===  Tab Content Areas  ===+ -->
		<?php
		echo pizzalayer_dashboard_home_tab_panel( 'crusts', 'Crusts', $pz_panel_crusts_content_description, 'yes' );
		echo pizzalayer_dashboard_home_tab_panel( 'sauces', 'Sauces', $pz_panel_sauces_content_description, 'no' );
		echo pizzalayer_dashboard_home_tab_panel( 'cheeses', 'Cheeses', $pz_panel_cheeses_content_description, 'no' );
		echo pizzalayer_dashboard_home_tab_panel( 'toppings', 'Toppings', $pz_panel_toppings_content_description, 'no' );
		echo pizzalayer_dashboard_home_tab_panel( 'drizzles', 'Drizzles', $pz_panel_drizzles_content_description, 'no' );
		echo pizzalayer_dashboard_home_tab_panel( 'cuts', 'Cuts', $pz_panel_cuts_content_description, 'no' );
		?>

		<!-- +===  Three Feature Sections  ===+ -->
		<hr>
		<div class="pizzalayer-layout-sections">
			<?php
			echo pizzalayer_dashboard_home_box_section( 'Getting Started', $getting_started_html, 'dashicons-info' );
			echo pizzalayer_dashboard_home_box_section( 'Tips &amp; Tricks', $tips_html, 'dashicons-admin-tools' );
			echo pizzalayer_dashboard_home_box_section( 'Extend PizzaLayer', $extend_html, 'dashicons-admin-plugins' );
			?>
		</div>

		<!-- +=== Responsive Info Boxes Row (3 Columns on Desktop) ===+ -->
		<div class="pizzalayer-info-boxes">
			<?php foreach ( $boxes as $id => $data ) : ?>
				<div class="pizzalayer-info-box">
					<h2><?php echo esc_html( $data['title'] ); ?></h2>
					<p><?php echo esc_html( $data['content'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- +=== Two-Column Panel with Video ===+ -->
		<div class="pizzalayer-two-col">
			<div class="pizzalayer-panel">
				<h2>Getting Started</h2>
				<p>This video will walk you through the basics of using PizzaLayer to build dynamic pizza options.</p>
			</div>
			<div class="pizzalayer-video">
				<iframe width="100%" height="380" src="https://www.youtube.com/embed/VIDEO_ID" frameborder="0" allowfullscreen></iframe>
			</div>
		</div>

		<!-- +=== Full-Width Button Row ===+ -->
		<div class="pizzalayer-btn-row">
			<a href="#" class="button button-primary">Help</a>
			<a href="#" class="button">Setup Guide</a>
			<a href="#" class="button">Get Embed Code</a>
			<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=pizzalayer_toppings' ) ); ?>" class="button">Edit Pizza Layers</a>
		</div>

		<!-- +=== Credits Section ===+ -->
		<div class="pizzalayer-credits">
			<h2>Credits</h2>
			<p>PizzaLayer is proudly developed by Ryan Bishop, a WordPress plugin author dedicated to building powerful tools for creative sites.</p>
			<p>For custom plugin work, visit <a href="https://islandsundesign.com" target="_blank" rel="noopener">IslandSunDesign.com</a>.</p>
		</div>

	</div><!-- /.wrap -->

	<!-- +===  Tabbed Navigation Script  ===+ -->
	<script>
	document.addEventListener("DOMContentLoaded", function () {
		var tabs = document.querySelectorAll(".pizzalayer-tabs a");
		var contents = document.querySelectorAll(".pizzalayer-tab-content");

		tabs.forEach(function(tab){
			tab.addEventListener("click", function (e) {
				e.preventDefault();

				if (this.classList.contains("pizzalayer-open-customizer")) {
					window.open("<?php echo esc_js( admin_url( 'customize.php' ) ); ?>", "_blank");
					return;
				}

				tabs.forEach(function(t){ t.classList.remove("nav-tab-active"); });
				contents.forEach(function(c){ c.classList.remove("active"); });

				this.classList.add("nav-tab-active");
				var target = document.querySelector(this.getAttribute("href"));
				if (target) { target.classList.add("active"); }
			});
		});
	});

	/* +=== jQuery Rotators (fade every N ms) ===+ */
	jQuery(document).ready(function(){
		jQuery(".pizzalayer-rotator").each(function(){
			var $rotator   = jQuery(this);
			var intervalMs = parseInt($rotator.attr("data-interval"), 10) || 6000;
			var $slides    = $rotator.find(".pz-rotator-slide");
			var idx        = 0;
			var isAnimating = false;

			// Show only the active slide initially
			$slides.removeClass("is-active").hide().attr("aria-hidden", "true");
			$slides.first().addClass("is-active").show().attr("aria-hidden", "false");

			function advanceSlide(){
				if (isAnimating) { return; }
				isAnimating = true;

				var $current = $slides.eq(idx);
				var nextIdx  = (idx + 1) % $slides.length;
				var $next    = $slides.eq(nextIdx);

				$current.stop(true, true).fadeOut(400, function(){
					$current.removeClass("is-active").attr("aria-hidden", "true");
					$next.stop(true, true).fadeIn(400, function(){
						$next.addClass("is-active").attr("aria-hidden", "false");
						idx = nextIdx;
						isAnimating = false;
					});
				});
			}

			setInterval(advanceSlide, intervalMs);
		});
	});
	</script>

	<!-- +===  Basic Styling for Layout  ===+ -->
	<style>
		/* Hero */
		.pizzalayer-admin-wrap .pizzalayer-hero {
			display: flex; gap: 20px; flex-wrap: wrap; align-items: stretch; margin-bottom: 20px;
		}
		.pizzalayer-hero-left { flex: 1 1 420px; background: #fff; border: 1px solid #ccd0d4; padding: 20px; }
		.pizzalayer-hero-right { width: 360px; max-width: 100%; }
		.pizzalayer-hero-title { margin: 0 0 10px; display: flex; align-items: center; gap: 8px; }
		.pizzalayer-hero-title .dashicons { font-size: 26px; width: 26px; height: 26px; }
		.pizzalayer-hero-desc { margin: 0 0 12px; }
		.pizzalayer-hero-cta .button { margin-right: 8px; margin-bottom: 8px; }

		.pizzalayer-hero-card { background: #fff; border: 1px solid #ccd0d4; padding: 16px; }
		.pizzalayer-quick-links { margin: 8px 0 0; padding-left: 18px; }

		/* Tabs */
		.pizzalayer-tab-content {
			display: none;
			background: #fff;
			border: 1px solid #ccd0d4;
			padding: 20px;
			margin-top: -1px;
		}
		.pizzalayer-tab-content.active { display: block; }
		.pizzalayer-tab-inner p:last-child { margin-bottom: 0; }

		/* Three feature sections */
		.pizzalayer-layout-sections {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
			gap: 20px;
			margin-top: 20px;
		}
		.pizzalayer-section {
			background: #f8f9fa;
			border: 1px solid #e1e4e8;
			padding: 20px;
			border-radius: 6px;
		}
		.pizzalayer-section h3 { margin-top: 0; }

		/* Rotators */
		.pizzalayer-rotator { position: relative; min-height: 64px; }
		.pz-rotator-slide { display: none; }
		.pz-rotator-slide.is-active { display: block; }

		/* Info boxes row */
		.pizzalayer-info-boxes {
			margin-top: 20px;
			display: flex;
			flex-wrap: wrap;
			gap: 20px;
		}
		.pizzalayer-info-box {
			flex: 1 1 calc(33.333% - 20px);
			min-width: 280px;
			background: #fff;
			padding: 20px;
			border: 1px solid #ccd0d4;
			box-sizing: border-box;
		}
		.pizzalayer-info-box h2 { margin-top: 0; }

		/* Two-column panel with video */
		.pizzalayer-two-col { display: flex; gap: 20px; margin-top: 30px; flex-wrap: wrap; }
		.pizzalayer-panel {
			flex: 1 1 280px;
			background: #fff; padding: 20px; border: 1px solid #ccd0d4;
		}
		.pizzalayer-video {
			width: 550px; max-width: 100%;
			background: #fff; padding: 10px; border: 1px solid #ccd0d4;
		}

		/* Button row */
		.pizzalayer-btn-row {
			margin-top: 30px; padding: 20px 0; border-top: 1px solid #ccd0d4;
			display: flex; gap: 10px; flex-wrap: wrap;
		}

		/* Credits */
		.pizzalayer-credits { margin-top: 40px; padding: 20px; background: transparent; }
	</style>
	<?php
}
