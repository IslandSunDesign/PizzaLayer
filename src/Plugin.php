<?php
namespace PizzaLayer;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Main plugin singleton. Wires all subsystems via the Loader.
 */
final class Plugin {

	/** @var self|null */
	private static $instance = null;

	/** @var Core\Loader */
	private $loader;

	private function __construct() {
		$this->loader = new Core\Loader();
		$this->register_services();
	}

	/**
	 * Boot the plugin (called on plugins_loaded).
	 */
	public static function init(): void {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->loader->run();
		}
	}

	/**
	 * Register all plugin services with the loader.
	 */
	private function register_services(): void {

		// Load text domain
		$this->loader->add_action( 'init', $this, 'load_textdomain' );

		// CPTs
		$cpt = new PostTypes\PostTypeRegistrar();
		$this->loader->add_action( 'init', $cpt, 'register', 0 );

		// Assets
		$assets = new Assets\AssetManager();
		$this->loader->add_action( 'wp_enqueue_scripts',          $assets, 'enqueue_frontend' );
		$this->loader->add_action( 'enqueue_block_editor_assets', $assets, 'enqueue_block_editor' );
		$this->loader->add_action( 'admin_enqueue_scripts',       $assets, 'enqueue_admin' );

		// Frontend Settings — apply all Settings page options to the front end.
		// Priority 20 ensures handles registered by enqueue_frontend already exist.
		$frontend_settings = new Frontend\FrontendSettings();
		$this->loader->add_action( 'wp_enqueue_scripts', $frontend_settings, 'inject_inline_styles',    20 );
		$this->loader->add_action( 'wp_enqueue_scripts', $frontend_settings, 'apply_performance',       20 );
		$this->loader->add_action( 'wp_enqueue_scripts', $frontend_settings, 'localise_js_data',        25 );
		$this->loader->add_action( 'wp_enqueue_scripts', $frontend_settings, 'inject_a11y_css',         20 );
		$this->loader->add_action( 'wp_head',            $frontend_settings, 'inject_custom_code',      99 );
		$this->loader->add_action( 'wp_footer',          $frontend_settings, 'inject_custom_footer_js', 99 );
		$this->loader->add_filter( 'pizzalayer_query_args_toppings', $frontend_settings, 'apply_sort_filter', 10, 2 );
		$this->loader->add_filter( 'pizzalayer_tab_order',           $frontend_settings, 'apply_tab_order',   10, 2 );

		// Shortcodes (registered on init)
		$this->loader->add_action( 'init', $this, 'register_shortcodes' );

		// Gutenberg blocks (registered on init, requires WP 5.8+)
		$blocks = new Blocks\BlockRegistrar();
		$this->loader->add_action( 'init', $blocks, 'register' );

		// REST API (public — available on front and admin)
		$rest_api = new Api\PizzaRestApi();
		$this->loader->add_action( 'rest_api_init', $rest_api, 'register_routes' );

		// ── Live template preview override (front-end + admin) ───────────
		// Must run outside is_admin() — the iframe loads on the front-end.
		// A signed ?pzl_preview=slug&pzl_nonce=HASH request temporarily
		// swaps the active template for that page-load only (no DB write).
		$this->loader->add_action( 'init', $this, 'handle_preview_override', 1 );

		// Admin
		if ( is_admin() ) {
			$admin_menu = new Admin\AdminMenu();
			$this->loader->add_action( 'admin_menu', $admin_menu, 'register' );

			$admin_bar = new Admin\AdminBar();
			$this->loader->add_action( 'admin_bar_menu', $admin_bar, 'register', 100 );

			// Settings export — must run before any HTML output
			$settings = new Admin\Settings();
			$this->loader->add_action( 'admin_post_pizzalayer_export_settings', $settings, 'handle_export' );

			// AJAX: template switcher
			$this->loader->add_action( 'wp_ajax_pizzalayer_set_template', $this, 'ajax_set_template' );

			// Content Hub AJAX panel switcher
			$content_hub = new Admin\ContentHub();
			$this->loader->add_action( 'wp_ajax_pizzalayer_content_panel', $content_hub, 'ajax_panel' );
		}

		// Debug logging — only when WP_DEBUG is on
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$this->loader->add_action( 'init', $this, 'register_debug_helpers' );
		}
	}

	/** Load plugin text domain. */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			'pizzalayer',
			false,
			dirname( plugin_basename( PIZZALAYER_PLUGIN_FILE ) ) . '/languages'
		);
	}

	/** Register all four shortcodes. */
	public function register_shortcodes(): void {
		add_shortcode( 'pizza_builder',    [ new Shortcodes\BuilderShortcode(),    'render' ] );
		add_shortcode( 'pizza_static',     [ new Shortcodes\StaticShortcode(),     'render' ] );
		add_shortcode( 'pizza_layer',      [ new Shortcodes\LayerImageShortcode(), 'render' ] );
		add_shortcode( 'pizza_layer_info', [ new Shortcodes\LayerInfoShortcode(),  'render' ] );

		// Deprecated aliases — keep for one major version
		add_shortcode( 'pizzalayer-menu',   [ new Shortcodes\BuilderShortcode(), 'render' ] );
		add_shortcode( 'pizzalayer-static', [ new Shortcodes\StaticShortcode(),  'render' ] );
	}

	/** Register write_log() helper when WP_DEBUG is active. */
	public function register_debug_helpers(): void {
		if ( ! function_exists( 'pizzalayer_log' ) ) {
			function pizzalayer_log( $data ): void { // phpcs:ignore
				$entry = is_array( $data ) || is_object( $data ) ? print_r( $data, true ) : (string) $data;
				error_log( '[PizzaLayer] ' . $entry ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
			}
		}
	}

	/** AJAX: switch active template. */
	public function ajax_set_template(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Unauthorized' ], 403 );
		}
		check_ajax_referer( 'pizzalayer_set_template', 'nonce' );

		$slug = isset( $_POST['slug'] ) ? sanitize_key( wp_unslash( $_POST['slug'] ) ) : '';
		if ( ! $slug ) {
			wp_send_json_error( [ 'message' => 'Missing slug' ], 400 );
		}

		$loader    = new Template\TemplateLoader();
		$templates = $loader->get_available_templates();
		if ( ! isset( $templates[ $slug ] ) ) {
			wp_send_json_error( [ 'message' => 'Invalid template' ], 400 );
		}

		update_option( 'pizzalayer_setting_global_template', $slug );
		wp_send_json_success( [ 'slug' => $slug ] );
	}

	/**
	 * If a valid ?pzl_preview=slug&pzl_nonce=hash is present, swap the active
	 * template option for this request only (no DB write).
	 * Only works for logged-in users with manage_options capability.
	 */
	public function handle_preview_override(): void {
		if ( empty( $_GET['pzl_preview'] ) || empty( $_GET['pzl_nonce'] ) ) {
			return;
		}
		$slug  = sanitize_key( wp_unslash( $_GET['pzl_preview'] ) );
		$nonce = sanitize_text_field( wp_unslash( $_GET['pzl_nonce'] ) );

		// Nonce is action-specific. wp_verify_nonce returns 1 or 2 on success.
		if ( ! wp_verify_nonce( $nonce, 'pizzalayer_preview_' . $slug ) ) {
			return;
		}

		// Validate slug exists
		$loader    = new Template\TemplateLoader();
		$templates = $loader->get_available_templates();
		if ( ! isset( $templates[ $slug ] ) ) {
			return;
		}

		// Override the option in-memory for this request only (no DB write)
		add_filter( 'option_pizzalayer_setting_global_template', function() use ( $slug ) {
			return $slug;
		} );

		// Remove X-Frame-Options so the admin iframe can embed the page.
		// WordPress sets SAMEORIGIN by default; security plugins may set DENY.
		// For same-origin admin preview, we need to clear this header.
		add_filter( 'x_frame_options', '__return_false' );
		add_filter( 'wp_headers', function( $headers ) {
			unset( $headers['X-Frame-Options'] );
			if ( isset( $headers['Content-Security-Policy'] ) ) {
				$headers['Content-Security-Policy'] = preg_replace(
					'/frame-ancestors[^;]*;?/',
					'frame-ancestors \'self\';',
					$headers['Content-Security-Policy']
				);
			}
			return $headers;
		} );

		// Body class signals we are in preview mode
		add_filter( 'body_class', function( $classes ) use ( $slug ) {
			$classes[] = 'pizzalayer-preview-mode';
			$classes[] = 'pizzalayer-preview-' . $slug;
			return $classes;
		} );

		// Hide admin bar for a cleaner preview
		add_filter( 'show_admin_bar', '__return_false' );
	}
}
