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

		// Shortcodes (registered on init)
		$this->loader->add_action( 'init', $this, 'register_shortcodes' );

		// Gutenberg blocks (registered on init, requires WP 5.8+)
		$blocks = new Blocks\BlockRegistrar();
		$this->loader->add_action( 'init', $blocks, 'register' );

		// REST API (public — available on front and admin)
		$rest_api = new Api\PizzaRestApi();
		$this->loader->add_action( 'rest_api_init', $rest_api, 'register_routes' );

		// Admin
		if ( is_admin() ) {
			$admin_menu = new Admin\AdminMenu();
			$this->loader->add_action( 'admin_menu', $admin_menu, 'register' );

			$admin_bar = new Admin\AdminBar();
			$this->loader->add_action( 'admin_bar_menu', $admin_bar, 'register', 100 );

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
}
