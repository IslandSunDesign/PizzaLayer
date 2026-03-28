<?php
/**
 * Scaffold Template — enqueues template.css.
 * Intentionally minimal: provides reset + layout skeleton only.
 * All visual design is your responsibility.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
$css_url = PIZZALAYER_TEMPLATES_URL . 'scaffold/template.css';
wp_enqueue_style( 'pztp-scaffold', $css_url, [], '1.0.0' );
