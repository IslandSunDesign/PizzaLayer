<?php
/**
 * PocketPie — registers [pizzalayer-menu] shortcode.
 * Delegates to pzt_pocketpie_menu_func() defined in pztp-containers-menu.php.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
do_action( 'pizzalayer_file_pztp-template-custom_start' );

/**
 * pzt_pocketpie_menu_func
 *
 * Called by both [pizzalayer-menu] and [pizzalayer-visualizer] shortcodes.
 * Accepts the same $atts array as the NightPie equivalent.
 *
 * @param  array $atts  Shortcode / direct-call attributes.
 * @return string        Buffered HTML output.
 */
if ( ! function_exists( 'pzt_pocketpie_menu_func' ) ) :
function pzt_pocketpie_menu_func( $atts = [] ) {

    $atts = shortcode_atts( [
        'id'             => 'pizzabuilder-1',
        'layout'         => 'corner-quad',   // corner-quad | layer-deck | slide-drawer | stack-panel
        'max_toppings'   => '',
        'pizza_shape'    => '',
        'pizza_aspect'   => '',
        'pizza_radius'   => '',
        'hide_tabs'      => '',
        'show_tabs'      => '',
        'default_crust'  => '',
        'default_sauce'  => '',
        'default_cheese' => '',
    ], $atts, 'pizzalayer-menu' );

    // Ensure unique instance IDs when multiple shortcodes are on one page.
    static $pzt_pp_instance_counter = 0;
    $pzt_pp_instance_counter++;

    $instance_id    = sanitize_html_class( $atts['id'] ?: ( 'pizzabuilder-' . $pzt_pp_instance_counter ) );
    $template_slug  = 'pocketpie';
    $function_prefix = 'pzt_pocketpie';

    ob_start();
    include __DIR__ . '/pztp-containers-menu.php';
    return ob_get_clean();
}
endif;

add_shortcode( 'pizzalayer-menu', 'pzt_pocketpie_menu_func' );

do_action( 'pizzalayer_file_pztp-template-custom_end' );
