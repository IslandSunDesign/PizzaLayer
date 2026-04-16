<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
do_action( 'pizzalayer_file_pztp-template-custom_start' );

/**
 * Metro template — shared PHP helpers + settings-driven CSS injection.
 *
 * This file runs once on wp_enqueue_scripts (via TemplateLoader::load_template_custom).
 * It reads all metro_setting_* options and injects a <style> block that
 * overrides CSS custom properties on .mt-root, ensuring every setting
 * propagates to the front-end without touching template.css.
 */

/* ── Helpers ─────────────────────────────────────────────────────── */

if ( ! function_exists( 'mt_hex2rgba' ) ) {
	/**
	 * Convert a hex colour + alpha to an rgba() string.
	 */
	function mt_hex2rgba( string $color, float $alpha ): string {
		$color = ltrim( $color, '#' );
		if ( strlen( $color ) === 3 ) {
			$color = $color[0].$color[0].$color[1].$color[1].$color[2].$color[2];
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
	function hex2rgba( $color, $alpha ) { return mt_hex2rgba( (string) $color, (float) $alpha ); }
}

/* ── Read all metro settings ─────────────────────────────────────── */

$mt_accent         = sanitize_hex_color( get_option( 'metro_setting_accent_color',           '#e63946' ) ) ?: '#e63946';
$mt_bg             = sanitize_hex_color( get_option( 'metro_setting_background_color',        '#f7f7f5' ) ) ?: '#f7f7f5';
$mt_card_bg        = sanitize_hex_color( get_option( 'metro_setting_card_bg_color',           '#f8f9fa' ) ) ?: '#f8f9fa';
$mt_heading_font   = sanitize_key(       get_option( 'metro_setting_heading_font',            'system'  ) );
$mt_font_size      = (int)               get_option( 'metro_setting_base_font_size',           14        );
$mt_layout         = sanitize_key(       get_option( 'metro_setting_layout_mode',             'centered') );
$mt_columns        = sanitize_key(       get_option( 'metro_setting_card_columns',            '3'       ) );
$mt_viz_size       = (int)               get_option( 'metro_setting_visualizer_size',          0         );
$mt_show_prices    =                     get_option( 'metro_setting_show_ingredient_prices',   'no'      ) === 'yes';
$mt_show_tray      =                     get_option( 'metro_setting_show_summary_bar',         'yes'     ) === 'yes';
$mt_sticky_viz     =                     get_option( 'metro_setting_sticky_visualizer',        'no'      ) === 'yes';
$mt_show_count     =                     get_option( 'metro_setting_show_ingredient_count',    'yes'     ) === 'yes';
$mt_section_gap    = (int)               get_option( 'metro_setting_section_gap',              24        );
$mt_card_radius    = (int)               get_option( 'metro_setting_card_border_radius',       14        );
$mt_card_style     = sanitize_key(       get_option( 'metro_setting_card_style',               'standard') );
$mt_tab_style      = sanitize_key(       get_option( 'metro_setting_tab_style',                'scrollbar') );
$mt_font_size      = max( 12, min( 20, $mt_font_size ) );
$mt_section_gap    = max(  8, min( 60, $mt_section_gap ) );
$mt_card_radius    = max(  0, min( 24, $mt_card_radius ) );

/* ── Font map ─────────────────────────────────────────────────────── */

$mt_font_stacks = [
	'system'     => "system-ui, -apple-system, 'Segoe UI', Roboto, Arial, sans-serif",
	'inter'      => "'Inter', system-ui, sans-serif",
	'poppins'    => "'Poppins', system-ui, sans-serif",
	'montserrat' => "'Montserrat', system-ui, sans-serif",
	'playfair'   => "'Playfair Display', Georgia, serif",
];
$mt_google_fonts = [
	'inter'      => 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
	'poppins'    => 'https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap',
	'montserrat' => 'https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap',
	'playfair'   => 'https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&display=swap',
];

$mt_font_stack = $mt_font_stacks[ $mt_heading_font ] ?? $mt_font_stacks['system'];

/* ── Enqueue Google Font if needed ───────────────────────────────── */

if ( isset( $mt_google_fonts[ $mt_heading_font ] ) ) {
	add_action( 'wp_enqueue_scripts', function() use ( $mt_heading_font, $mt_google_fonts ) {
		wp_enqueue_style(
			'mt-google-font-' . $mt_heading_font,
			$mt_google_fonts[ $mt_heading_font ],
			[],
			null
		);
	} );
}

/* ── Derive dependent colour values ─────────────────────────────── */

// Slightly darken accent for hover: shift each channel -18
$darken = function( string $hex, int $amount = 18 ): string {
	$hex = ltrim( $hex, '#' );
	if ( strlen( $hex ) === 3 ) {
		$hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
	}
	$r = max( 0, hexdec( substr( $hex, 0, 2 ) ) - $amount );
	$g = max( 0, hexdec( substr( $hex, 2, 2 ) ) - $amount );
	$b = max( 0, hexdec( substr( $hex, 4, 2 ) ) - $amount );
	return sprintf( '#%02x%02x%02x', $r, $g, $b );
};
$mt_accent_hover = $darken( $mt_accent, 18 );
$mt_accent_dim   = mt_hex2rgba( $mt_accent, 0.10 );

/* ── Column map: setting value → CSS minmax width ────────────────── */

$mt_col_widths = [
	'2'    => '220px',
	'3'    => '160px',
	'4'    => '130px',
	'auto' => '140px',
];
$mt_card_min_w = $mt_col_widths[ $mt_columns ] ?? '160px';

/* ── Visualizer size ─────────────────────────────────────────────── */

$mt_hero_size = $mt_viz_size > 0
	? 'min(' . $mt_viz_size . 'px, 90vw)'
	: 'min(340px, 80vw)';

/* ── Output scoped CSS variable overrides ────────────────────────── */

add_action( 'wp_head', function() use (
	$mt_accent, $mt_accent_hover, $mt_accent_dim,
	$mt_bg, $mt_card_bg,
	$mt_font_stack, $mt_font_size,
	$mt_hero_size, $mt_card_min_w, $mt_card_radius,
	$mt_section_gap,
	$mt_show_tray, $mt_sticky_viz, $mt_show_count, $mt_show_prices,
	$mt_layout, $mt_card_style, $mt_tab_style
) {
	// CSS variable overrides
	$accent_esc       = esc_attr( $mt_accent );
	$accent_hover_esc = esc_attr( $mt_accent_hover );
	$accent_dim_esc   = esc_attr( $mt_accent_dim );
	$bg_esc           = esc_attr( $mt_bg );
	$card_bg_esc      = esc_attr( $mt_card_bg );
	$font_esc         = esc_attr( $mt_font_stack );
	$font_size_esc    = (int) $mt_font_size;
	$hero_esc         = esc_attr( $mt_hero_size );
	$card_min_w_esc   = esc_attr( $mt_card_min_w );
	$card_r_esc       = (int) $mt_card_radius;
	$gap_esc          = (int) $mt_section_gap;

	$_mt_css = "";
	$_mt_css .= ".mt-root {\n";
	$_mt_css .= "  --mt-accent:         {$accent_esc};\n";
	$_mt_css .= "  --mt-accent-hover:   {$accent_hover_esc};\n";
	$_mt_css .= "  --mt-accent-dim:     {$accent_dim_esc};\n";
	$_mt_css .= "  --mt-bg:             {$bg_esc};\n";
	$_mt_css .= "  --mt-surface:        {$card_bg_esc};\n";
	$_mt_css .= "  --mt-font:           {$font_esc};\n";
	$_mt_css .= "  --mt-hero-pizza-size:{$hero_esc};\n";
	$_mt_css .= "  --mt-card-w:         {$card_min_w_esc};\n";
	$_mt_css .= "  --mt-radius:         {$card_r_esc}px;\n";
	$_mt_css .= "  font-size:           {$font_size_esc}px;\n";
	$_mt_css .= "}\n";

	// Section gap
	$_mt_css .= ".mt-root .mt-section { padding-top:{$gap_esc}px; padding-bottom:{$gap_esc}px; }\n";

	// Hide tray if disabled
	if ( ! $mt_show_tray ) {
		echo ".mt-root { padding-bottom: 0 !important; }\n";
		echo ".mt-root .mt-tray { display: none !important; }\n";
	}

	// Hide topping count badge if disabled
	if ( ! $mt_show_count ) {
		echo ".mt-root .mt-hero__meta { display: none !important; }\n";
		echo ".mt-root .mt-section__badge--toppings { display: none !important; }\n";
		echo ".mt-root .mt-orb__count { display: none !important; }\n";
	}

	// Sticky visualizer
	if ( $mt_sticky_viz ) {
		echo ".mt-root.mt-layout--centered .mt-hero__pizza-wrap,\n";
		echo ".mt-root.mt-layout--side-by-side .mt-sidebar__pizza-wrap {\n";
		echo "  position: sticky; top: 16px;\n";
		echo "}\n";
	}

	// Show ingredient prices (unhide the price span)
	if ( $mt_show_prices ) {
		echo ".mt-root .mt-card__price { display: block; }\n";
	} else {
		echo ".mt-root .mt-card__price { display: none; }\n";
	}

	// Layout mode classes
	if ( $mt_layout === 'side-by-side' ) {
		echo ".mt-root.mt-layout--side-by-side .mt-hero { display: none; }\n";
		echo ".mt-root.mt-layout--side-by-side .mt-builder-wrap {\n";
		echo "  display: flex; flex-direction: row; align-items: flex-start; gap: 0;\n";
		echo "}\n";
		echo ".mt-root.mt-layout--side-by-side .mt-sidebar {\n";
		echo "  display: flex; flex-direction: column; align-items: center;\n";
		echo "  width: 340px; flex-shrink: 0;\n";
		echo "  padding: 24px 20px;\n";
		echo "  background: var(--mt-surface);\n";
		echo "  border-right: 1px solid var(--mt-border);\n";
		echo "  position: sticky; top: 0; align-self: flex-start;\n";
		echo "  max-height: 100vh; overflow-y: auto;\n";
		echo "}\n";
		echo ".mt-root.mt-layout--side-by-side .mt-builder { flex: 1; min-width: 0; }\n";
	} elseif ( $mt_layout === 'fullwidth' ) {
		echo ".mt-root.mt-layout--fullwidth .mt-hero { position: sticky; top: 0; z-index: 100; }\n";
		echo ".mt-root.mt-layout--fullwidth .mt-hero__pizza-wrap { width: min(200px,40vw); height: min(200px,40vw); }\n";
		echo ".mt-root.mt-layout--fullwidth .mt-hero__inner { flex-direction: row; justify-content: center; max-width: 100%; }\n";
	}

	// Card style modifier class
	$card_style_esc = esc_attr( $mt_card_style );
	$tab_style_esc  = esc_attr( $mt_tab_style );
	$_mt_css .= ".mt-root { --mt-card-style: '{$card_style_esc}'; --mt-tab-style: '{$tab_style_esc}'; }\n";

	wp_add_inline_style( 'pizzalayer-template-metro', $_mt_css );
}, 20 );

do_action( 'pizzalayer_file_pztp-template-custom_end' );
