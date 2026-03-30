<?php
/**
 * Plugin Name: Pizza Layer
 * Plugin URI:  https://pizzalayer.com
 * Description: Pizza toppings customizer and visualizer.
 * Version:     1.0.2
 * Author:      Island Sun Design
 * Author URI:  https://pizzalayer.com
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
define( 'PIZZALAYER_VERSION',       '1.0.2' );
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
	function pz_get_enabled_fractions(): array {
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

// Boot
add_action( 'plugins_loaded', [ 'PizzaLayer\\Plugin', 'init' ] );

register_activation_hook(   __FILE__, [ 'PizzaLayer\\Core\\Activator',   'activate'   ] );
register_deactivation_hook( __FILE__, [ 'PizzaLayer\\Core\\Deactivator', 'deactivate' ] );
