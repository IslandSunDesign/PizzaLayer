<?php
/**
 * Scaffold Template — registers [pizzalayer-visualizer] shortcode.
 * Delegates to pzt_scaffold_menu_func() defined in pztp-template-custom.php.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
do_action( 'pizzalayer_file_pztp-containers-presentation_start' );

function pzt_scaffold_visualizer_func( $atts = [] ) {
    $atts = shortcode_atts( [
        'id'    => 'pizzalayer-pizza',
        'crust' => '',
        'sauce' => '',
        'cheese'=> '',
    ], $atts, 'pizzalayer-visualizer' );

    if ( function_exists( 'pzt_scaffold_menu_func' ) ) {
        return pzt_scaffold_menu_func( $atts );
    }
    return '<!-- Scaffold: pzt_scaffold_menu_func not found -->';
}

add_shortcode( 'pizzalayer-visualizer', 'pzt_scaffold_visualizer_func' );

do_action( 'pizzalayer_file_pztp-containers-presentation_end' );
