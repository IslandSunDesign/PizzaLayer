<?php
namespace PizzaLayer\Assets;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class AssetManager {

	/**
	 * Template slugs requested by shortcode instances on this page load.
	 * Populated via require_template() before wp_enqueue_scripts fires.
	 *
	 * @var string[]
	 */
	private static array $required_templates = [];

	/**
	 * Register a template slug as required for this page.
	 * Called by BuilderShortcode during render so that enqueue_frontend()
	 * knows which template assets to load beyond the global default.
	 */
	public static function require_template( string $slug ): void {
		if ( $slug && ! in_array( $slug, self::$required_templates, true ) ) {
			self::$required_templates[] = $slug;
		}
	}

	public function enqueue_frontend(): void {
		$v = PIZZALAYER_VERSION;

		wp_enqueue_style( 'pizzalayer-css',            PIZZALAYER_ASSETS_URL . 'css/pizzalayer.css',            [], $v );
		wp_enqueue_style( 'pizzalayer-bootstrap-grid', PIZZALAYER_ASSETS_URL . 'css/bootstrap-grid-system.css', [], $v );
		wp_enqueue_script( 'pizzalayer-js',            PIZZALAYER_ASSETS_URL . 'js/pizzalayer-main.js',         [ 'jquery' ], $v, true );

		$loader = new \PizzaLayer\Template\TemplateLoader();

		// Always enqueue the globally active template.
		$active_slug = $loader->get_active_slug();
		$slugs_to_load = array_unique( array_merge( [ $active_slug ], self::$required_templates ) );

		foreach ( $slugs_to_load as $slug ) {
			// Load the template's custom PHP file (hooks, helpers) exactly once per page load.
			$loader->load_template_custom( $slug );

			if ( file_exists( $loader->get_template_file( 'template.css', $slug ) ) ) {
				wp_enqueue_style( 'pizzalayer-template-' . $slug, $loader->get_template_url( 'template.css', $slug ), [ 'pizzalayer-css' ], $v );
			}
			if ( file_exists( $loader->get_template_file( 'custom.js', $slug ) ) ) {
				wp_enqueue_script( 'pizzalayer-template-' . $slug, $loader->get_template_url( 'custom.js', $slug ), [ 'jquery', 'pizzalayer-js' ], $v, true );
			}
		}
	}

	/**
	 * Enqueue styles in the block editor so server-side-rendered previews
	 * look correct inside the editor iframe/canvas.
	 */
	public function enqueue_block_editor(): void {
		$v      = PIZZALAYER_VERSION;
		$loader = new \PizzaLayer\Template\TemplateLoader();
		$slug   = $loader->get_active_slug();

		wp_enqueue_style( 'pizzalayer-css', PIZZALAYER_ASSETS_URL . 'css/pizzalayer.css', [], $v );

		if ( file_exists( $loader->get_template_file( 'template.css', $slug ) ) ) {
			wp_enqueue_style(
				'pizzalayer-template-' . $slug,
				$loader->get_template_url( 'template.css', $slug ),
				[ 'pizzalayer-css' ],
				$v
			);
		}
	}

	/**
	 * Enqueue admin assets — shared tabs CSS/JS plus page-specific scripts.
	 *
	 * @param string $hook Current admin page hook suffix.
	 */
	public function enqueue_admin( string $hook ): void {
		if ( false === strpos( $hook, 'pizzalayer' ) ) { return; }

		$v    = PIZZALAYER_VERSION;
		$base = PIZZALAYER_ASSETS_URL . 'js/admin/';

		// Shared admin styles + tab widget
		wp_enqueue_style( 'pizzalayer-admin-tabs', PIZZALAYER_ASSETS_URL . 'css/admin-tabs.css', [], $v );
		wp_enqueue_script( 'pizzalayer-admin-js',  PIZZALAYER_ASSETS_URL . 'js/admin-tabs.js',   [ 'jquery' ], $v, true );

		// Dashboard
		if ( false !== strpos( $hook, 'pizzalayer_page_pizzalayer' ) || 'toplevel_page_pizzalayer' === $hook ) {
			wp_enqueue_script(
				'pizzalayer-admin-home',
				$base . 'admin-home.js',
				[ 'jquery' ],
				$v,
				true
			);
		}

		// Setup Guide
		if ( false !== strpos( $hook, 'pizzalayer-setup' ) ) {
			wp_enqueue_script(
				'pizzalayer-setup-guide',
				$base . 'setup-guide.js',
				[],
				$v,
				true
			);
		}

		// Content Hub
		if ( false !== strpos( $hook, 'pizzalayer-content' ) ) {
			wp_enqueue_script(
				'pizzalayer-content-hub',
				$base . 'content-hub.js',
				[],
				$v,
				true
			);

			// Build CPT data array for the JS
			$cpt_slugs = [ 'toppings', 'crusts', 'sauces', 'cheeses', 'drizzles', 'cuts', 'sizes' ];
			$cpt_meta  = [
				'toppings' => [ 'label' => 'Toppings', 'singular' => 'Topping',  'icon' => 'dashicons-carrot',          'color' => '#f0b849', 'desc' => 'Layer images placed on top of cheese.' ],
				'crusts'   => [ 'label' => 'Crusts',   'singular' => 'Crust',    'icon' => 'dashicons-admin-generic',    'color' => '#c8956c', 'desc' => 'The base canvas for the pizza stack.' ],
				'sauces'   => [ 'label' => 'Sauces',   'singular' => 'Sauce',    'icon' => 'dashicons-food',             'color' => '#d63638', 'desc' => 'Applied on top of the crust.' ],
				'cheeses'  => [ 'label' => 'Cheeses',  'singular' => 'Cheese',   'icon' => 'dashicons-category',         'color' => '#dba633', 'desc' => 'Sits between sauce and toppings.' ],
				'drizzles' => [ 'label' => 'Drizzles', 'singular' => 'Drizzle',  'icon' => 'dashicons-admin-customizer', 'color' => '#00a32a', 'desc' => 'Finishing touches above toppings.' ],
				'cuts'     => [ 'label' => 'Cuts',     'singular' => 'Cut',      'icon' => 'dashicons-editor-table',     'color' => '#2271b1', 'desc' => 'Slicing overlays.' ],
				'sizes'    => [ 'label' => 'Sizes',    'singular' => 'Size',     'icon' => 'dashicons-image-rotate',     'color' => '#8c5af8', 'desc' => 'Dimension options with pricing metadata.' ],
			];

			$js_cpt_data = [];
			foreach ( $cpt_slugs as $s ) {
				$m = $cpt_meta[ $s ];
				$js_cpt_data[ $s ] = [
					'label'    => $m['label'],
					'singular' => $m['singular'],
					'icon'     => $m['icon'],
					'color'    => $m['color'],
					'desc'     => $m['desc'],
					'addUrl'   => admin_url( 'post-new.php?post_type=pizzalayer_' . $s ),
				];
			}

			$active_slug = isset( $_GET['pl_cpt'] ) ? sanitize_key( $_GET['pl_cpt'] ) : 'toppings'; // phpcs:ignore WordPress.Security.NonceVerification
			if ( ! array_key_exists( $active_slug, $js_cpt_data ) ) {
				$active_slug = 'toppings';
			}

			wp_localize_script(
				'pizzalayer-content-hub',
				'pizzalayerContentHub',
				[
					'nonce'   => wp_create_nonce( 'pizzalayer_content_nonce' ),
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'cptData' => $js_cpt_data,
					'active'  => $active_slug,
				]
			);
		}

		// Shortcode Generator
		if ( false !== strpos( $hook, 'pizzalayer-shortcodes' ) ) {
			wp_enqueue_script(
				'pizzalayer-shortcode-generator',
				$base . 'shortcode-generator.js',
				[],
				$v,
				true
			);
			// Pass CPT items for the layer slug select
			$q = [ 'posts_per_page' => -1, 'post_status' => 'publish', 'orderby' => 'title', 'order' => 'ASC' ];
			$cpt_items = [];
			foreach ( [ 'topping', 'crust', 'sauce', 'cheese', 'drizzle', 'cut' ] as $type ) {
				$posts = get_posts( array_merge( $q, [ 'post_type' => 'pizzalayer_' . $type . 's' ] ) );
				$cpt_items[ $type ] = array_map( fn( $p ) => [
					'slug'  => sanitize_title( $p->post_title ),
					'title' => $p->post_title,
				], $posts );
			}
			wp_localize_script( 'pizzalayer-shortcode-generator', 'pizzalayerSCG', [ 'cptItems' => $cpt_items ] );
		}

		// Settings
		if ( false !== strpos( $hook, 'pizzalayer-settings' ) ) {
			wp_enqueue_media(); // Required for the logo image picker
			wp_enqueue_style(
				'pizzalayer-settings-page',
				PIZZALAYER_ASSETS_URL . 'css/settings-page.css',
				[ 'pizzalayer-admin-tabs' ],
				$v
			);
			wp_enqueue_script(
				'pizzalayer-settings',
				$base . 'settings.js',
				[],
				$v,
				true
			);
			wp_enqueue_script(
				'pizzalayer-settings-page',
				PIZZALAYER_ASSETS_URL . 'js/admin/settings-page.js',
				[ 'jquery', 'pizzalayer-settings' ],
				$v,
				true
			);
		}

		// Template Choice
		if ( false !== strpos( $hook, 'pizzalayer-template' ) ) {
			wp_enqueue_script(
				'pizzalayer-template-choice',
				$base . 'template-choice.js',
				[],
				$v,
				true
			);
		}

		// Settings Wizard
		if ( false !== strpos( $hook, 'pizzalayer-wizard' ) ) {
			wp_enqueue_script(
				'pizzalayer-settings-wizard',
				$base . 'settings-wizard.js',
				[],
				$v,
				true
			);
		}

		// Layer Builder Wizard
		if ( false !== strpos( $hook, 'pizzalayer-layer-wizard' ) ) {
			wp_enqueue_script(
				'pizzalayer-layer-builder-wizard',
				$base . 'layer-builder-wizard.js',
				[ 'jquery' ],
				$v,
				true
			);
			wp_localize_script(
				'pizzalayer-layer-builder-wizard',
				'pizzalayerLBW',
				[
					'nonce'      => wp_create_nonce( 'pizzalayer_layer_builder' ),
					'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
					'limUrl'     => admin_url( 'admin.php?page=pizzalayer-layer-maker' ),
					'layerTypes' => \PizzaLayer\Admin\LayerBuilderWizard::LAYER_TYPES,
				]
			);
		}

		// Layer Image Maker (full-page tool)
		if ( false !== strpos( $hook, 'pizzalayer-layer-maker' ) ) {
			wp_enqueue_script(
				'pizzalayer-layer-image-maker',
				$base . 'layer-image-maker.js',
				[],
				$v,
				true
			);
			wp_localize_script(
				'pizzalayer-layer-image-maker',
				'plimConfig',
				[
					'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
					'nonce'       => wp_create_nonce( 'pizzalayer_layer_image_maker' ),
					'aspectRatio' => preg_replace( '/\s+/', '', get_option( 'pizzalayer_setting_pizza_aspect', '4 / 3' ) ),
				]
			);
		}

		// Layer Image MetaBox — CPT post-edit screens
		if ( in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
			$screen = get_current_screen();
			if ( $screen && strpos( $screen->post_type ?? '', 'pizzalayer_' ) === 0 ) {
				wp_enqueue_script(
					'pizzalayer-layer-image-metabox',
					$base . 'layer-image-metabox.js',
					[],
					$v,
					true
				);
			}
		}
	}
}
