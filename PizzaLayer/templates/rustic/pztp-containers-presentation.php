<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
do_action( 'pizzalayer_file_pztp-containers-presentation_start' );

$pizzalayer_template_images_directory = plugin_dir_url( __FILE__ ) . 'images/';

/*
 * Fornaia registers the [pizzalayer-visualizer] shortcode.
 * The builder UI is rendered by pztp-containers-menu.php.
 */

function pizzalayer_toppings_visualizer_func_rustic( $atts = array() ) {
    $atts = shortcode_atts( array(
        'id'       => 'pizzalayer-pizza',
        'crust'    => '',
        'sauce'    => '',
        'cheese'   => '',
        'toppings' => '',
        'drizzle'  => '',
        'cut'      => '',
    ), $atts, 'pizzalayer-visualizer' );

    if ( function_exists( 'pizzalayer_toppings_menu_func' ) ) {
        return pizzalayer_toppings_menu_func();
    }

    return '<!-- Fornaia: pizzalayer_toppings_menu_func not found -->';
}

add_shortcode( 'pizzalayer-visualizer', 'pizzalayer_toppings_visualizer_func_rustic' );

do_action( 'pizzalayer_file_pztp-containers-presentation_end' );
