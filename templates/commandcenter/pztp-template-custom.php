<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
do_action( 'pizzalayer_file_pztp-template-custom_start' );

/*
 * Command Center template – shared PHP helpers.
 * Loaded once per page by AssetManager::load_template_custom().
 */

/* hex2rgba helper */
if ( ! function_exists( 'hex2rgba' ) ) {
    function hex2rgba( $color, $alpha ) {
        if ( $color[0] === '#' ) { $color = substr( $color, 1 ); }
        list( $r, $g, $b ) = array_map( 'hexdec', str_split( $color, strlen( $color ) / 3 ) );
        return 'rgba(' . $r . ',' . $g . ',' . $b . ',' . $alpha . ')';
    }
}

do_action( 'pizzalayer_file_pztp-template-custom_end' );
