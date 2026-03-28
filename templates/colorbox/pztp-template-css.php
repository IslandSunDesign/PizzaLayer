<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
do_action( 'pizzalayer_file_pztp-template-css_start' );

function pizzalayer_template_colorbox_generated_css() {
    // No dynamic CSS needed – all styles are in template.css
    return '';
}

do_action( 'pizzalayer_file_pztp-template-css_end' );
