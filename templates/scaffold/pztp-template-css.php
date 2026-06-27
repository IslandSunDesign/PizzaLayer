<?php
/**
 * Scaffold Template — template.css loader.
 *
 * Intentionally minimal: provides reset + layout skeleton only.
 * All visual design is your responsibility.
 *
 * NOTE: On the front end the plugin's AssetManager already enqueues this
 * template's template.css under the canonical handle
 * 'pizzalayer-template-scaffold' (and the per-instance CSS custom properties
 * are attached to that handle). This file therefore only enqueues a fallback
 * copy when that canonical handle is NOT present, to avoid loading the same
 * stylesheet twice.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! wp_style_is( 'pizzalayer-template-scaffold', 'enqueued' )
	&& ! wp_style_is( 'pizzalayer-template-scaffold', 'registered' ) ) {
	$css_url = PIZZALAYER_TEMPLATES_URL . 'scaffold/template.css';
	wp_enqueue_style( 'pztp-scaffold', $css_url, [], PIZZALAYER_VERSION );
}
