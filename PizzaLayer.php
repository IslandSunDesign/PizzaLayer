<?php
/**
 * Plugin Name: PizzaLayer
 * Plugin URI:  https://pizzalayer.com
 * Description: Pizza toppings customizer and visualizer.
 * Version:     1.13.3
 * Author:      Island Sun Design
 * Author URI:  https://islandsundesign.com
 * Requires at least: 6.2
 * Tested up to:      7.0
 * License:     GPLv2 or later
 * Text Domain: pizzalayer
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

// Autoloader (PSR-4: PizzaLayer\ → src/)
spl_autoload_register( function ( $class ) {
	$prefix   = 'PizzaLayer\\';
	$base_dir = __DIR__ . '/src/';
	$len      = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) { return; }
	$relative = substr( $class, $len );
	$file     = $base_dir . str_replace( '\\', '/', $relative ) . '.php';
	if ( file_exists( $file ) ) { require $file; }
} );

// Constants
define( 'PIZZALAYER_VERSION',       '1.13.3' );
define( 'PIZZALAYER_PLUGIN_FILE',   __FILE__ );
define( 'PIZZALAYER_PLUGIN_DIR',    plugin_dir_path( __FILE__ ) );
define( 'PIZZALAYER_PLUGIN_URL',    plugin_dir_url( __FILE__ ) );
define( 'PIZZALAYER_TEMPLATES_DIR', PIZZALAYER_PLUGIN_DIR . 'templates/' );
define( 'PIZZALAYER_TEMPLATES_URL', PIZZALAYER_PLUGIN_URL . 'templates/' );
define( 'PIZZALAYER_ASSETS_URL',    PIZZALAYER_PLUGIN_URL . 'assets/' );
define( 'PIZZALAYER_IMAGES_URL',    PIZZALAYER_PLUGIN_URL . 'assets/images/' );
define( 'PIZZALAYER_BLOCKS_DIR',    PIZZALAYER_PLUGIN_DIR . 'blocks/' );

/**
 * Returns the array of enabled topping coverage fractions from settings.
 * Always includes 'whole'. Handles legacy single-value migration.
 *
 * @return string[]
 */
if ( ! function_exists( 'pz_get_enabled_fractions' ) ) {
	function pz_get_enabled_fractions(): array { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Uses the plugin-owned pz_ prefix; function_exists()-guarded above.
		$saved = get_option( 'pizzalayer_setting_topping_fractions', [] );
		if ( ! is_array( $saved ) ) {
			// Migrate legacy string values
			$lv    = (string) $saved;
			$saved = [ 'whole' ];
			if ( $lv === 'halves' || $lv === 'quarters' ) {
				$saved[] = 'half-left';
				$saved[] = 'half-right';
			}
			if ( $lv === 'quarters' ) {
				$saved[] = 'quarter-top-left';
				$saved[] = 'quarter-top-right';
				$saved[] = 'quarter-bottom-left';
				$saved[] = 'quarter-bottom-right';
			}
		}
		if ( empty( $saved ) ) {
			return [ 'whole', 'half-left', 'half-right', 'quarter-top-left', 'quarter-top-right', 'quarter-bottom-left', 'quarter-bottom-right' ];
		}
		if ( ! in_array( 'whole', $saved, true ) ) {
			array_unshift( $saved, 'whole' );
		}
		return $saved;
	}
}

/**
 * Safe SCF/ACF field accessor with a raw post-meta fallback.
 *
 * When SCF/ACF is active, delegates to get_field() so the configured return
 * format is preserved. When neither is active, falls back to post meta so the
 * front-end templates and the [pizza_layer_info] shortcode degrade gracefully
 * instead of fataling on an undefined get_field(). For image field keys
 * (those ending in "_image") a stored attachment ID is resolved to its URL,
 * matching what the layer-image meta box writes.
 *
 * @param string $field   Field / meta key.
 * @param int    $post_id Post ID.
 * @return mixed          Field value (string|array|int) or '' when unset.
 */
if ( ! function_exists( 'pzl_get_field' ) ) {
	function pzl_get_field( $field, $post_id ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Uses the plugin-owned pzl_ prefix; this is the shared ACF/SCF safe accessor, function_exists()-guarded above.
		if ( function_exists( 'get_field' ) ) {
			return get_field( $field, $post_id );
		}

		$field = (string) $field;
		$value = get_post_meta( (int) $post_id, $field, true );
		if ( '' === $value || null === $value ) {
			return '';
		}

		// Image fields store an attachment ID (or an array) — resolve to a URL
		// so templates that expect a URL string keep working without SCF/ACF.
		if ( '_image' === substr( $field, -6 ) ) {
			if ( is_array( $value ) ) {
				return isset( $value['url'] ) ? (string) $value['url'] : '';
			}
			if ( is_numeric( $value ) ) {
				$url = wp_get_attachment_url( (int) $value );
				return $url ? $url : '';
			}
		}

		return $value;
	}
}

// Boot
add_action( 'plugins_loaded', [ 'PizzaLayer\\Plugin', 'init' ] );

register_activation_hook(   __FILE__, [ 'PizzaLayer\\Core\\Activator',   'activate'   ] );
register_deactivation_hook( __FILE__, [ 'PizzaLayer\\Core\\Deactivator', 'deactivate' ] );
