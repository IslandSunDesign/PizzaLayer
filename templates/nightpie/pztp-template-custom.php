<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
do_action( 'pizzalayer_file_pztp-template-custom_start' );

/**
 * NightPie template — shared PHP helpers + settings-driven CSS injection.
 *
 * Runs once per page load via TemplateLoader::load_template_custom().
 * Reads all nightpie_setting_* options and emits an inline <style> block
 * that overrides CSS custom properties on .np-root.
 */

/* ── Helpers ─────────────────────────────────────────────────────── */

if ( ! function_exists( 'np_hex2rgba' ) ) {
	function np_hex2rgba( string $color, float $alpha ): string {
		$color = ltrim( $color, '#' );
		if ( strlen( $color ) === 3 ) {
			$color = $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
		}
		if ( strlen( $color ) !== 6 ) { return 'rgba(0,0,0,' . $alpha . ')'; }
		$r = hexdec( substr( $color, 0, 2 ) );
		$g = hexdec( substr( $color, 2, 2 ) );
		$b = hexdec( substr( $color, 4, 2 ) );
		return 'rgba(' . $r . ',' . $g . ',' . $b . ',' . $alpha . ')';
	}
}

// Back-compat alias used by older code in this file
if ( ! function_exists( 'hex2rgba' ) ) {
	function hex2rgba( $color, $alpha ) { return np_hex2rgba( (string) $color, (float) $alpha ); }
}

if ( ! function_exists( 'pzt_nightpie_get_font_stack' ) ) :
function pzt_nightpie_get_font_stack( string $key ): string {
	$map = [
		'system'     => "system-ui, -apple-system, 'Segoe UI', Roboto, Arial, sans-serif",
		'inter'      => "'Inter', system-ui, sans-serif",
		'poppins'    => "'Poppins', system-ui, sans-serif",
		'montserrat' => "'Montserrat', system-ui, sans-serif",
		'roboto'     => "'Roboto', system-ui, sans-serif",
	];
	return $map[ $key ] ?? $map['system'];
}
endif;

if ( ! function_exists( 'pzt_nightpie_inject_css' ) ) :
function pzt_nightpie_inject_css(): void {
	$g = function( string $k, string $d = '' ) { return (string) get_option( $k, $d ); };

	// ── Read all settings with safe defaults matching template.css ──
	$accent       = sanitize_hex_color( $g( 'nightpie_setting_accent_color',         '#ff5722' ) ) ?: '#ff5722';
	$bg           = sanitize_hex_color( $g( 'nightpie_setting_bg_color',             '#0e0e12' ) ) ?: '#0e0e12';
	$surface      = sanitize_hex_color( $g( 'nightpie_setting_surface_color',        '#18181f' ) ) ?: '#18181f';
	$surface_2    = sanitize_hex_color( $g( 'nightpie_setting_surface_2_color',      '#22222c' ) ) ?: '#22222c';
	$text         = sanitize_hex_color( $g( 'nightpie_setting_text_color',           '#f0f0f4' ) ) ?: '#f0f0f4';
	$text_muted   = sanitize_hex_color( $g( 'nightpie_setting_text_muted_color',     '#888898' ) ) ?: '#888898';

	$font_key     = sanitize_key( $g( 'nightpie_setting_font_family', 'system' ) );
	$font_stack   = pzt_nightpie_get_font_stack( $font_key );

	$base_size    = max( 12, min( 20, (int) $g( 'nightpie_setting_base_font_size', '15' ) ) );
	$radius       = max(  0, min( 28, (int) $g( 'nightpie_setting_corner_radius',  '16' ) ) );

	$sticky       = $g( 'nightpie_setting_sticky_preview', 'yes' ) === 'yes';
	$accent_glow  = $g( 'nightpie_setting_accent_glow',    'yes' ) === 'yes';

	// ── Derive dependent values ─────────────────────────────────────
	$accent_dim         = np_hex2rgba( $accent, 0.15 );
	$accent_glow_color  = np_hex2rgba( $accent, 0.35 );

	// Proportional small/large radius (template defaults: sm=10, base=16, lg=24).
	$radius_sm = max( 0, (int) round( $radius * 0.625 ) );
	$radius_lg = (int) round( $radius * 1.5 );

	// ── Build CSS ───────────────────────────────────────────────────
	$css  = ".np-root {";
	$css .= "--np-accent:" .         esc_attr( $accent )            . ";";
	$css .= "--np-accent-dim:" .     esc_attr( $accent_dim )        . ";";
	$css .= "--np-accent-glow:" .    esc_attr( $accent_glow_color ) . ";";
	$css .= "--np-bg:" .             esc_attr( $bg )                . ";";
	$css .= "--np-surface:" .        esc_attr( $surface )           . ";";
	$css .= "--np-surface-2:" .      esc_attr( $surface_2 )         . ";";
	$css .= "--np-text:" .           esc_attr( $text )              . ";";
	$css .= "--np-text-muted:" .     esc_attr( $text_muted )        . ";";
	$css .= "--np-radius-sm:" .      $radius_sm . "px;";
	$css .= "--np-radius:" .         $radius    . "px;";
	$css .= "--np-radius-lg:" .      $radius_lg . "px;";
	$css .= "--np-font:" .           esc_attr( $font_stack )        . ";";
	$css .= "font-size:" .           $base_size . "px;";
	$css .= "}";

	// Optional behavioural toggles.
	if ( ! $sticky ) {
		$css .= "@media (min-width:900px){.np-root .np-pizza-col{position:static !important;max-height:none !important;}}";
	}
	if ( ! $accent_glow ) {
		// Suppress glow shadows by zeroing the variable used in box-shadow declarations.
		$css .= ".np-root { --np-accent-glow: rgba(0,0,0,0); }";
	}

	wp_add_inline_style( 'pizzalayer-template-nightpie', $css ); // phpcs:ignore — dynamic CSS vars
}
endif;

add_action( 'wp_enqueue_scripts', 'pzt_nightpie_inject_css', 99 );

do_action( 'pizzalayer_file_pztp-template-custom_end' );
