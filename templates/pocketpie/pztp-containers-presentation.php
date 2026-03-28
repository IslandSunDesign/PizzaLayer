<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
do_action( 'pizzalayer_file_pztp-containers-presentation_start' );

/**
 * PocketPie registers [pizzalayer-visualizer] shortcode.
 * All rendering is handled in pztp-containers-menu.php.
 */
function pzt_pocketpie_visualizer_func( $atts = array() ) {
    $atts = shortcode_atts( array(
        'id'      => 'pizzalayer-pizza',
        'crust'   => '',
        'sauce'   => '',
        'cheese'  => '',
        'layout'  => 'corner-quad', // corner-quad | layer-deck | slide-drawer | stack-panel
    ), $atts, 'pizzalayer-visualizer' );

    if ( function_exists( 'pzt_pocketpie_menu_func' ) ) {
        return pzt_pocketpie_menu_func( $atts );
    }
    return '<!-- PocketPie: pzt_pocketpie_menu_func not found -->';
}

add_shortcode( 'pizzalayer-visualizer', 'pzt_pocketpie_visualizer_func' );

do_action( 'pizzalayer_file_pztp-containers-presentation_end' );
