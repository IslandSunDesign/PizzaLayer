<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
do_action( 'pizzalayer_file_pztp-template-custom_start' );

/**
 * Command Center template — shared PHP helpers + settings-driven CSS injection.
 *
 * This file runs once on wp_enqueue_scripts (via TemplateLoader::load_template_custom).
 * It reads all commandcenter_setting_* options and emits a <style> block that
 * overrides CSS custom properties on .cc-root, ensuring every setting
 * propagates to the front-end without touching template.css.
 */

/* ── Helpers ─────────────────────────────────────────────────────── */

if ( ! function_exists( 'cc_hex2rgba' ) ) {
	/**
	 * Convert a hex colour + alpha to an rgba() string.
	 */
	function cc_hex2rgba( string $color, float $alpha ): string {
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
	function hex2rgba( $color, $alpha ) { return cc_hex2rgba( (string) $color, (float) $alpha ); }
}

if ( ! function_exists( 'pzt_commandcenter_get_font_stack' ) ) :
function pzt_commandcenter_get_font_stack( string $key ): string {
	$map = [
		'system'     => "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif",
		'inter'      => "'Inter', system-ui, sans-serif",
		'poppins'    => "'Poppins', system-ui, sans-serif",
		'montserrat' => "'Montserrat', system-ui, sans-serif",
		'roboto'     => "'Roboto', system-ui, sans-serif",
	];
	return $map[ $key ] ?? $map['system'];
}
endif;

if ( ! function_exists( 'pzt_commandcenter_inject_css' ) ) :
function pzt_commandcenter_inject_css(): void {
	$g = function( string $k, string $d = '' ) { return (string) get_option( $k, $d ); };

	// ── Read all settings with safe defaults matching template.css ──
	$accent          = sanitize_hex_color( $g( 'commandcenter_setting_accent_color',         '#e94560' ) ) ?: '#e94560';
	$accent_hover    = sanitize_hex_color( $g( 'commandcenter_setting_accent_hover_color',   '#ff5572' ) ) ?: '#ff5572';
	$step_done       = sanitize_hex_color( $g( 'commandcenter_setting_step_done_color',      '#3dd68c' ) ) ?: '#3dd68c';
	$bg              = sanitize_hex_color( $g( 'commandcenter_setting_bg_color',             '#0b1120' ) ) ?: '#0b1120';
	$surface         = sanitize_hex_color( $g( 'commandcenter_setting_surface_color',        '#16213e' ) ) ?: '#16213e';
	$surface_2       = sanitize_hex_color( $g( 'commandcenter_setting_surface_2_color',      '#1e2d4f' ) ) ?: '#1e2d4f';
	$text            = sanitize_hex_color( $g( 'commandcenter_setting_text_color',           '#e8eaf6' ) ) ?: '#e8eaf6';
	$text_muted      = sanitize_hex_color( $g( 'commandcenter_setting_text_muted_color',     '#8892b0' ) ) ?: '#8892b0';

	$font_key        = sanitize_key( $g( 'commandcenter_setting_font_family',                'system'  ) );
	$font_stack      = pzt_commandcenter_get_font_stack( $font_key );

	$base_size       = max( 12, min( 20, (int) $g( 'commandcenter_setting_base_font_size',   '15' ) ) );
	$radius          = max(  0, min( 24, (int) $g( 'commandcenter_setting_corner_radius',    '12' ) ) );

	$show_step_nums  = $g( 'commandcenter_setting_show_step_numbers',    'yes' ) === 'yes';
	$show_sidebar    = $g( 'commandcenter_setting_show_summary_sidebar', 'yes' ) === 'yes';
	$accent_glow     = $g( 'commandcenter_setting_accent_glow',          'yes' ) === 'yes';

	// ── Derive dependent rgba/glow values from accent ───────────────
	$accent_dim  = cc_hex2rgba( $accent, 0.18 );
	$accent_glow_shadow = $accent_glow
		? '0 0 20px ' . cc_hex2rgba( $accent, 0.35 )
		: 'none';
	$step_done_dim = cc_hex2rgba( $step_done, 0.15 );

	// Derive smaller radius proportionally (template uses 12 / 8 / 16 ratio).
	$radius_sm = max( 0, (int) round( $radius * 0.66 ) );
	$radius_lg = (int) round( $radius * 1.33 );

	// ── Build CSS ───────────────────────────────────────────────────
	$css  = ".cc-root {";
	$css .= "--cc-accent:" .        esc_attr( $accent )       . ";";
	$css .= "--cc-accent-hover:" .  esc_attr( $accent_hover ) . ";";
	$css .= "--cc-accent-dim:" .    esc_attr( $accent_dim )   . ";";
	$css .= "--cc-accent-glow:" .   esc_attr( $accent_glow_shadow ) . ";";
	$css .= "--cc-step-done:" .     esc_attr( $step_done )      . ";";
	$css .= "--cc-step-done-dim:" . esc_attr( $step_done_dim )  . ";";
	$css .= "--cc-bg:" .            esc_attr( $bg )           . ";";
	$css .= "--cc-surface:" .       esc_attr( $surface )      . ";";
	$css .= "--cc-surface-2:" .     esc_attr( $surface_2 )    . ";";
	$css .= "--cc-text:" .          esc_attr( $text )         . ";";
	$css .= "--cc-text-muted:" .    esc_attr( $text_muted )   . ";";
	$css .= "--cc-radius:" .        $radius    . "px;";
	$css .= "--cc-radius-sm:" .     $radius_sm . "px;";
	$css .= "--cc-radius-lg:" .     $radius_lg . "px;";
	$css .= "font-family:" .        esc_attr( $font_stack )   . ";";
	$css .= "font-size:" .          $base_size . "px;";
	$css .= "}";

	// Optional behavioural toggles.
	if ( ! $show_step_nums ) {
		$css .= ".cc-root .cc-step__num { display: none !important; }";
	}
	if ( ! $show_sidebar ) {
		$css .= ".cc-root .cc-sidebar { display: none !important; }";
		$css .= ".cc-root .cc-main-col { width: 100%; }";
	}

	wp_add_inline_style( 'pizzalayer-template-commandcenter', $css ); // phpcs:ignore — dynamic CSS vars
}
endif;

add_action( 'wp_enqueue_scripts', 'pzt_commandcenter_inject_css', 99 );

do_action( 'pizzalayer_file_pztp-template-custom_end' );
