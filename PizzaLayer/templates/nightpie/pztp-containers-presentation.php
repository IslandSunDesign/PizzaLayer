<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
do_action( 'pizzalayer_file_pztp-containers-presentation_start' );

$pizzalayer_template_images_directory = plugin_dir_url( __FILE__ ) . 'images/';

/*
 * NightPie registers the [pizzalayer-visualizer] shortcode.
 * This is the shortcode that pages actually use (e.g. [pizzalayer-visualizer id="glass-demo-ui"]).
 * It outputs the full NightPie UI: sticky split-screen pizza + tabbed builder.
 * The heavy lifting is in pztp-containers-menu.php (pizzalayer_toppings_menu_func).
 */

function pizzalayer_toppings_visualizer_func( $atts = array() ) {
    // Merge shortcode attributes with defaults
    $atts = shortcode_atts( array(
        'id'       => 'pizzalayer-pizza',
        'crust'    => '',
        'sauce'    => '',
        'cheese'   => '',
        'toppings' => '',
        'drizzle'  => '',
        'cut'      => '',
    ), $atts, 'pizzalayer-visualizer' );

    // pizzalayer_toppings_menu_func is defined in pztp-containers-menu.php
    // and already registered as the [pizzalayer-menu] shortcode handler.
    // We call it directly here so [pizzalayer-visualizer] also works.
    if ( function_exists( 'pizzalayer_toppings_menu_func' ) ) {
        return pizzalayer_toppings_menu_func();
    }

    return '<!-- NightPie: pizzalayer_toppings_menu_func not found -->';
}

add_shortcode( 'pizzalayer-visualizer', 'pizzalayer_toppings_visualizer_func' );

do_action( 'pizzalayer_file_pztp-containers-presentation_end' );
