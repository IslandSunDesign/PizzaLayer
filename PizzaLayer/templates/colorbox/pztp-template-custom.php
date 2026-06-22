<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
do_action( 'pizzalayer_file_pztp-template-custom_start' );

/*
 * Colorbox template – shared PHP helpers
 * These are thin wrappers / re-exports used by pztp-containers-menu.php.
 * All heavy lifting lives in template.css + custom.js.
 */

/* hex2rgba helper (needed by some templates; included here for safety) */
if ( ! function_exists( 'hex2rgba' ) ) {
    function hex2rgba( $color, $alpha ) {
        if ( $color[0] === '#' ) { $color = substr( $color, 1 ); }
        list( $r, $g, $b ) = array_map( 'hexdec', str_split( $color, strlen( $color ) / 3 ) );
        return 'rgba(' . $r . ',' . $g . ',' . $b . ',' . $alpha . ')';
    }
}

do_action( 'pizzalayer_file_pztp-template-custom_end' );
