<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
$css_url = PIZZALAYER_TEMPLATES_URL . 'commandcenter/template.css';
wp_enqueue_style( 'pizzalayer-template-commandcenter', $css_url, [ 'pizzalayer-css' ], '1.1.0' );
