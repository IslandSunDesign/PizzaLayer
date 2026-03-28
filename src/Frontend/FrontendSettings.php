<?php
namespace PizzaLayer\Frontend;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * FrontendSettings — reads the new Settings page options and applies them
 * to the front-end: inline CSS variables, localised JS data, and wp_head hooks.
 *
 * Hooked in Plugin::register_services():
 *   wp_enqueue_scripts  → inject_inline_styles()   (inline <style> after scripts enqueued)
 *   wp_head             → inject_custom_code()      (custom CSS / JS in <head>)
 *   wp_enqueue_scripts  → apply_performance()       (lazy-load / preload / defer flags)
 *   wp_footer           → inject_custom_footer_js() (deferred custom JS)
 */
class FrontendSettings {

	// ─── Helpers ──────────────────────────────────────────────────────────────

	private static function g( string $key, string $default = '' ): string {
		return (string) get_option( $key, $default );
	}

	private static function gb( string $key, string $default = 'no' ): bool {
		return self::g( $key, $default ) === 'yes';
	}

	private static function gi( string $key, int $default = 0 ): int {
		return (int) get_option( $key, $default );
	}

	// ─── Main entry points ────────────────────────────────────────────────────

	/**
	 * Inject inline CSS into the pizzalayer stylesheet handle.
	 * Generates a <style> block carrying all CSS custom-property overrides
	 * driven by the settings page.  Everything from Typography → Colour Palette
	 * → Spacing & Borders → Builder-level vars lives here.
	 */
	public function inject_inline_styles(): void {

		// ── Typography ──────────────────────────────────────────────────
		$font_family  = self::g( 'pizzalayer_setting_typo_font_family',  'inherit' );
		$google_font  = self::g( 'pizzalayer_setting_typo_google_font',  '' );
		$base_size    = self::g( 'pizzalayer_setting_typo_base_size',    '15px' );
		$heading_fw   = self::g( 'pizzalayer_setting_typo_heading_fw',   '700' );
		$label_size   = self::g( 'pizzalayer_setting_typo_label_size',   '13px' );
		$price_size   = self::g( 'pizzalayer_setting_typo_price_size',   '14px' );
		$btn_fw       = self::g( 'pizzalayer_setting_typo_btn_fw',       '600' );
		$letter_sp    = self::g( 'pizzalayer_setting_typo_letter_sp',    '0' );
		$text_tx      = self::g( 'pizzalayer_setting_typo_text_transform','none' );

		// Resolve font-family value
		$resolved_font = 'inherit';
		switch ( $font_family ) {
			case 'system':   $resolved_font = 'system-ui, -apple-system, sans-serif'; break;
			case 'georgia':  $resolved_font = 'Georgia, "Times New Roman", serif'; break;
			case 'courier':  $resolved_font = '"Courier New", Courier, monospace'; break;
			case 'google':   $resolved_font = $google_font ? '"' . esc_attr( $google_font ) . '", sans-serif' : 'inherit'; break;
			default:         $resolved_font = 'inherit';
		}

		// If Google Font is selected, enqueue it
		if ( $font_family === 'google' && $google_font ) {
			$gf_url = 'https://fonts.googleapis.com/css2?family=' . urlencode( $google_font ) . ':wght@400;500;600;700;800&display=swap';
			wp_enqueue_style( 'pizzalayer-google-font', $gf_url, [], null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters
		}

		// ── Colour Palette ───────────────────────────────────────────────
		$c_bg          = self::g( 'pizzalayer_setting_color_bg',         '' );
		$c_menu_bg     = self::g( 'pizzalayer_setting_color_menu_bg',    '' );
		$c_card_bg     = self::g( 'pizzalayer_setting_color_card_bg',    '' );
		$c_card_border = self::g( 'pizzalayer_setting_color_card_border','' );
		$c_selected    = self::g( 'pizzalayer_setting_color_selected',   '' );
		$c_tab_bg      = self::g( 'pizzalayer_setting_color_tab_bg',     '' );
		$c_tab_active  = self::g( 'pizzalayer_setting_color_tab_active', '' );
		$c_tab_text    = self::g( 'pizzalayer_setting_color_tab_text',   '' );
		$c_btn_bg      = self::g( 'pizzalayer_setting_color_btn_bg',     '' );
		$c_btn_text    = self::g( 'pizzalayer_setting_color_btn_text',   '' );
		$c_btn2_bg     = self::g( 'pizzalayer_setting_color_btn2_bg',    '' );
		$c_body_text   = self::g( 'pizzalayer_setting_color_body_text',  '' );
		$c_muted_text  = self::g( 'pizzalayer_setting_color_muted_text', '' );
		$c_error       = self::g( 'pizzalayer_setting_color_error',      '' );
		$c_success     = self::g( 'pizzalayer_setting_color_success',    '' );

		// ── Spacing & Borders ────────────────────────────────────────────
		$sp_outer_pad    = self::g( 'pizzalayer_setting_spacing_outer_pad',     '' );
		$sp_grid_gap     = self::g( 'pizzalayer_setting_spacing_grid_gap',      '' );
		$sp_card_pad     = self::g( 'pizzalayer_setting_spacing_card_pad',      '' );
		$sp_card_radius  = self::g( 'pizzalayer_setting_spacing_card_radius',   '' );
		$sp_card_border  = self::g( 'pizzalayer_setting_spacing_card_border',   '' );
		$sp_btn_radius   = self::g( 'pizzalayer_setting_spacing_btn_radius',    '' );
		$sp_tab_height   = self::g( 'pizzalayer_setting_spacing_tab_height',    '' );
		$sp_shadow_preset= self::g( 'pizzalayer_setting_spacing_shadow',        '' );
		$sp_shadow_css   = self::g( 'pizzalayer_setting_spacing_shadow_css',    '' );
		$sp_divider      = self::g( 'pizzalayer_setting_spacing_divider',       '' );

		// Resolve box-shadow from preset
		$shadow_val = '';
		switch ( $sp_shadow_preset ) {
			case 'sm':      $shadow_val = '0 1px 4px rgba(0,0,0,0.10)'; break;
			case 'md':      $shadow_val = '0 4px 12px rgba(0,0,0,0.14)'; break;
			case 'lg':      $shadow_val = '0 8px 24px rgba(0,0,0,0.18)'; break;
			case 'custom':  $shadow_val = $sp_shadow_css; break;
			default:        $shadow_val = ''; break;
		}

		// ── Builder CSS vars ─────────────────────────────────────────────
		$vars = [];

		// Typography vars
		if ( $resolved_font !== 'inherit' )  { $vars['--pzl-font']          = $resolved_font; }
		if ( $base_size )                    { $vars['--pzl-font-size']      = esc_attr( $base_size ); }
		if ( $heading_fw )                   { $vars['--pzl-heading-fw']     = esc_attr( $heading_fw ); }
		if ( $label_size )                   { $vars['--pzl-label-size']     = esc_attr( $label_size ); }
		if ( $price_size )                   { $vars['--pzl-price-size']     = esc_attr( $price_size ); }
		if ( $btn_fw )                       { $vars['--pzl-btn-fw']         = esc_attr( $btn_fw ); }
		if ( $letter_sp && $letter_sp !== '0' ) { $vars['--pzl-letter-sp']  = esc_attr( $letter_sp ); }
		if ( $text_tx && $text_tx !== 'none' )  { $vars['--pzl-text-tx']    = esc_attr( $text_tx ); }

		// Colour palette vars — only emit if set (non-empty)
		$colour_map = [
			'--pzl-bg'          => $c_bg,
			'--pzl-menu-bg'     => $c_menu_bg,
			'--pzl-card-bg'     => $c_card_bg,
			'--pzl-card-border' => $c_card_border,
			'--pzl-selected'    => $c_selected,
			'--pzl-tab-bg'      => $c_tab_bg,
			'--pzl-tab-active'  => $c_tab_active,
			'--pzl-tab-text'    => $c_tab_text,
			'--pzl-btn-bg'      => $c_btn_bg,
			'--pzl-btn-text'    => $c_btn_text,
			'--pzl-btn2-bg'     => $c_btn2_bg,
			'--pzl-body-text'   => $c_body_text,
			'--pzl-muted-text'  => $c_muted_text,
			'--pzl-error'       => $c_error,
			'--pzl-success'     => $c_success,
		];
		foreach ( $colour_map as $prop => $val ) {
			if ( $val ) { $vars[ $prop ] = esc_attr( $val ); }
		}

		// Spacing vars
		$spacing_map = [
			'--pzl-outer-pad'   => $sp_outer_pad,
			'--pzl-grid-gap'    => $sp_grid_gap,
			'--pzl-card-pad'    => $sp_card_pad,
			'--pzl-card-radius' => $sp_card_radius,
			'--pzl-card-border' => $sp_card_border,
			'--pzl-btn-radius'  => $sp_btn_radius,
			'--pzl-tab-height'  => $sp_tab_height,
		];
		foreach ( $spacing_map as $prop => $val ) {
			if ( $val ) { $vars[ $prop ] = esc_attr( $val ); }
		}
		if ( $shadow_val )  { $vars['--pzl-card-shadow'] = esc_attr( $shadow_val ); }

		if ( empty( $vars ) ) { return; }

		// Build :root rule
		$rule = ':root{';
		foreach ( $vars as $prop => $val ) {
			$rule .= esc_attr( $prop ) . ':' . $val . ';';
		}
		$rule .= '}';

		// Map pzl vars → template-specific CB vars (Colorbox/Metro/NightPie all use --cb-*)
		$bridge = $this->build_template_bridge( $c_bg, $c_menu_bg, $c_card_bg, $c_card_border, $c_selected, $c_tab_bg, $c_tab_active, $c_tab_text, $c_btn_bg, $c_btn_text, $c_body_text, $c_muted_text, $sp_card_radius, $sp_card_border, $shadow_val, $sp_grid_gap, $sp_tab_height, $sp_outer_pad, $sp_btn_radius, $resolved_font );

		// Topping display — columns and grid gap
		$top_cols_d  = self::gi( 'pizzalayer_setting_topping_cols_desktop', 0 );
		$top_cols_m  = self::gi( 'pizzalayer_setting_topping_cols_mobile',  0 );
		$top_size    = self::g(  'pizzalayer_setting_topping_thumb_size',   '' );
		$top_opacity = self::gi( 'pizzalayer_setting_topping_vis_opacity',  100 );

		$topping_css = '';
		if ( $top_cols_d > 0 ) {
			$topping_css .= '.cb-card-grid--toppings{grid-template-columns:repeat(' . $top_cols_d . ',1fr)!important;}';
			$topping_css .= '.pztp-toppings-grid{grid-template-columns:repeat(' . $top_cols_d . ',1fr)!important;}';
		}
		if ( $top_cols_m > 0 ) {
			$topping_css .= '@media(max-width:600px){.cb-card-grid--toppings{grid-template-columns:repeat(' . $top_cols_m . ',1fr)!important;}.pztp-toppings-grid{grid-template-columns:repeat(' . $top_cols_m . ',1fr)!important;}}';
		}
		if ( $top_size ) {
			$topping_css .= '.cb-card__thumb{width:' . esc_attr( $top_size ) . '!important;height:' . esc_attr( $top_size ) . '!important;}';
		}
		if ( $top_opacity < 100 ) {
			$topping_css .= '.cb-pizza-stage [data-layer-id*="topping"] img{opacity:' . ( (float) $top_opacity / 100 ) . ';}';
			$topping_css .= '.np-pizza-stage [class*="topping"] img{opacity:' . ( (float) $top_opacity / 100 ) . ';}';
		}

		// Builder width from Builder Layout settings
		$builder_width  = self::g( 'pizzalayer_setting_layout_builder_width', '' );
		$mobile_bp      = self::gi( 'pizzalayer_setting_layout_mobile_bp', 0 );
		$tab_height_set = self::g( 'pizzalayer_setting_spacing_tab_height', '' );

		$layout_css = '';
		if ( $builder_width ) {
			$layout_css .= '.cb-root,.pztp-container,.np-root{max-width:' . esc_attr( $builder_width ) . '!important;}';
		}
		if ( $mobile_bp > 0 ) {
			$layout_css .= '.cb-layout__row{flex-direction:column;}';
			$layout_css .= '@media(min-width:' . $mobile_bp . 'px){.cb-layout__row{flex-direction:row;}}';
		}

		// Divider style
		$divider_css = '';
		if ( $sp_divider && $sp_divider !== 'solid' ) {
			$divider_css .= '.cb-panel,.cb-tabs-col .cb-builder{border-style:' . esc_attr( $sp_divider ) . ';}';
		}

		// Template-specific generated CSS (from pztp-template-css.php)
		$template_generated_css = '';
		$loader = new \PizzaLayer\Template\TemplateLoader();
		$active_slug = $loader->get_active_slug();
		$css_file = $loader->get_template_file( 'pztp-template-css.php', $active_slug );
		if ( file_exists( $css_file ) ) {
			include_once $css_file;
			$fn = 'pizzalayer_template_' . sanitize_key( $active_slug ) . '_generated_css';
			if ( function_exists( $fn ) ) {
				$template_generated_css = (string) $fn();
			}
		}

		$all_css = $rule . $bridge . $topping_css . $layout_css . $divider_css . $template_generated_css;

		// Apply CSS vars to specific template roots so they override the template defaults
		wp_add_inline_style( 'pizzalayer-css', $all_css );
	}

	/**
	 * Build a CSS bridge mapping pzl vars to cb/np/metro vars used by templates.
	 * This lets global colour/spacing settings affect all templates consistently.
	 */
	private function build_template_bridge(
		string $bg, string $menu_bg, string $card_bg, string $card_border, string $selected,
		string $tab_bg, string $tab_active, string $tab_text,
		string $btn_bg, string $btn_text,
		string $body_text, string $muted_text,
		string $card_radius, string $card_border_w, string $shadow,
		string $grid_gap, string $tab_height, string $outer_pad, string $btn_radius,
		string $font
	): string {
		$rules = [];

		// Colorbox (cb-root) and NightPie (np-root) use --cb-* / --np-* custom props
		// We override them directly via CSS var() cascades
		$cb_overrides = [];
		if ( $bg )           { $cb_overrides['--cb-bg']             = $bg;         }
		if ( $menu_bg )      { $cb_overrides['--cb-surface']        = $menu_bg;    }
		if ( $card_bg )      { $cb_overrides['--cb-surface-2']      = $card_bg;    }
		if ( $card_border )  { $cb_overrides['--cb-border']         = $card_border;}
		if ( $selected )     { $cb_overrides['--cb-accent']         = $selected;   }
		if ( $tab_active )   { $cb_overrides['--cb-accent']         = $tab_active; }
		if ( $body_text )    { $cb_overrides['--cb-text']           = $body_text;  }
		if ( $muted_text )   { $cb_overrides['--cb-text-muted']     = $muted_text; }
		if ( $card_radius )  { $cb_overrides['--cb-radius']         = $card_radius;}
		if ( $shadow )       { $cb_overrides['--cb-shadow']         = $shadow;     }
		if ( $font !== 'inherit' && $font ) { $cb_overrides['--cb-font'] = '"' . addslashes( $font ) . '"'; }

		if ( ! empty( $cb_overrides ) ) {
			$cb_rule = '.cb-root{';
			foreach ( $cb_overrides as $prop => $val ) {
				$cb_rule .= esc_attr( $prop ) . ':' . esc_attr( $val ) . ';';
			}
			$cb_rule .= '}';
			$rules[] = $cb_rule;
		}

		// Metro template uses --metro-* vars
		$metro_overrides = [];
		if ( $bg )         { $metro_overrides['--metro-bg']     = $bg;      }
		if ( $card_bg )    { $metro_overrides['--metro-card-bg']= $card_bg; }
		if ( $selected )   { $metro_overrides['--metro-accent'] = $selected;}
		if ( $body_text )  { $metro_overrides['--metro-text']   = $body_text;}

		if ( ! empty( $metro_overrides ) ) {
			$m_rule = '.metro-root,.np-root{';
			foreach ( $metro_overrides as $prop => $val ) {
				$m_rule .= esc_attr( $prop ) . ':' . esc_attr( $val ) . ';';
			}
			$m_rule .= '}';
			$rules[] = $m_rule;
		}

		// Fornaia / Rustic template (.rp-root) — maps global palette vars to --rp-* tokens
		$rp_overrides = [];
		if ( $bg )          { $rp_overrides['--rp-bg']           = $bg;         }
		if ( $menu_bg )     { $rp_overrides['--rp-surface']      = $menu_bg;    }
		if ( $card_bg )     { $rp_overrides['--rp-surface-2']    = $card_bg;    }
		if ( $card_border ) { $rp_overrides['--rp-border']       = $card_border;}
		if ( $selected )    { $rp_overrides['--rp-accent']       = $selected;   }
		if ( $tab_active )  { $rp_overrides['--rp-accent']       = $tab_active; }
		if ( $body_text )   { $rp_overrides['--rp-text']         = $body_text;  }
		if ( $muted_text )  { $rp_overrides['--rp-text-muted']   = $muted_text; }
		if ( $card_radius ) { $rp_overrides['--rp-radius']       = $card_radius;}
		if ( $shadow )      { $rp_overrides['--rp-shadow']       = $shadow;     }
		if ( $font !== 'inherit' && $font ) {
			$rp_overrides['--rp-font-body']  = '"' . addslashes( $font ) . '", system-ui, sans-serif';
		}

		if ( ! empty( $rp_overrides ) ) {
			$rp_rule = '.rp-root{';
			foreach ( $rp_overrides as $prop => $val ) {
				$rp_rule .= esc_attr( $prop ) . ':' . esc_attr( $val ) . ';';
			}
			$rp_rule .= '}';
			$rules[] = $rp_rule;
		}

		// Explicit overrides for buttons (can't always reach via CSS var)
		if ( $btn_bg || $btn_text || $btn_radius ) {
			$btn_rule = '.cb-btn--primary,.pztp-add-btn,.np-btn--primary,.rp-btn--add,.rp-btn--next{';
			if ( $btn_bg )     { $btn_rule .= 'background:' . esc_attr( $btn_bg )     . '!important;'; }
			if ( $btn_text )   { $btn_rule .= 'color:'      . esc_attr( $btn_text )   . '!important;'; }
			if ( $btn_radius ) { $btn_rule .= 'border-radius:' . esc_attr( $btn_radius ) . '!important;'; }
			$btn_rule .= '}';
			$rules[] = $btn_rule;
		}

		// Card grid gap
		if ( $grid_gap ) {
			$rules[] = '.cb-card-grid,.pztp-grid,.rp-cards-grid{gap:' . esc_attr( $grid_gap ) . '!important;}';
		}

		// Card border width
		if ( $card_border_w ) {
			$rules[] = '.cb-card,.pztp-item,.rp-card{border-width:' . esc_attr( $card_border_w ) . '!important;}';
		}

		// Tab / step height
		if ( $tab_height ) {
			$rules[] = '.cb-tab,.np-tab,.pztp-tab,.rp-step{height:' . esc_attr( $tab_height ) . '!important;min-height:' . esc_attr( $tab_height ) . '!important;}';
		}

		// Outer padding
		if ( $outer_pad ) {
			$rules[] = '.cb-root,.np-root,.metro-root,.rp-root,.pizzalayer-ui-container{padding:' . esc_attr( $outer_pad ) . '!important;}';
		}

		return implode( '', $rules );
	}

	/**
	 * Inject custom CSS and JS into wp_head.
	 * Only outputs content when the respective options are non-empty.
	 */
	public function inject_custom_code(): void {
		$custom_css = self::g( 'pizzalayer_setting_adv_custom_css', '' );
		$custom_js  = self::g( 'pizzalayer_setting_adv_custom_js',  '' );

		if ( $custom_css ) {
			echo '<style id="pizzalayer-custom-css">' . wp_strip_all_tags( $custom_css ) . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
		}

		// Inline JS is output in wp_footer so it runs after builder scripts
		// (stored here, echoed in inject_custom_footer_js)
	}

	/**
	 * Output custom JS in wp_footer, after all builder scripts have loaded.
	 */
	public function inject_custom_footer_js(): void {
		$custom_js = self::g( 'pizzalayer_setting_adv_custom_js', '' );
		if ( $custom_js ) {
			echo '<script id="pizzalayer-custom-js">' . "\n" . $custom_js . "\n" . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
		}
	}

	/**
	 * Performance-related enqueue hooks:
	 * - Disable all plugin CSS if requested
	 * - Add preload hints for builder CSS
	 * - Lazy-load flag is applied via CSS (native loading="lazy" is already on img tags)
	 */
	public function apply_performance(): void {
		// Disable all plugin CSS
		if ( self::gb( 'pizzalayer_setting_adv_disable_css' ) ) {
			wp_dequeue_style( 'pizzalayer-css' );
			wp_dequeue_style( 'pizzalayer-bootstrap-grid' );
		}

		// Preload hint for critical CSS
		if ( self::gb( 'pizzalayer_setting_perf_preload_assets' ) ) {
			add_action( 'wp_head', function () {
				$css_url = PIZZALAYER_ASSETS_URL . 'css/pizzalayer.css';
				echo '<link rel="preload" href="' . esc_url( $css_url ) . '" as="style">' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
			}, 1 );
		}

		// Lazy-load topping images — add a body class, JS/CSS handle the rest
		if ( self::gb( 'pizzalayer_setting_perf_lazy_load', 'yes' ) ) {
			add_filter( 'body_class', function ( $classes ) {
				$classes[] = 'pzl-lazy-load';
				return $classes;
			} );
		}

		// Reduce motion body class
		if ( self::gb( 'pizzalayer_setting_a11y_reduce_motion' ) ) {
			add_filter( 'body_class', function ( $classes ) {
				$classes[] = 'pzl-reduce-motion';
				return $classes;
			} );
		}

		// High contrast body class
		if ( self::gb( 'pizzalayer_setting_a11y_high_contrast' ) ) {
			add_filter( 'body_class', function ( $classes ) {
				$classes[] = 'pzl-high-contrast';
				return $classes;
			} );
		}

		// Debug mode body class
		if ( self::gb( 'pizzalayer_setting_adv_debug_mode' ) ) {
			add_filter( 'body_class', function ( $classes ) {
				$classes[] = 'pzl-debug-mode';
				return $classes;
			} );
		}
	}

	/**
	 * Localise JS data to the builder scripts on the frontend.
	 * Passes Customer Experience strings, toast settings, max-topping warning, etc.
	 * Attached to wp_enqueue_scripts (runs after enqueue_frontend).
	 */
	public function localise_js_data(): void {
		// Only localise if a pizzalayer JS handle is actually enqueued
		if ( ! wp_script_is( 'pizzalayer-js', 'enqueued' ) ) { return; }

		$data = [
			// Customer Experience strings
			'toastStyle'        => self::g( 'pizzalayer_setting_cx_toast_style',       'bottom-right' ),
			'toastDuration'     => self::gi( 'pizzalayer_setting_cx_toast_duration',   2000 ),
			'textAdded'         => self::g( 'pizzalayer_setting_cx_text_added',        'Added to your pizza!' ),
			'textRemoved'       => self::g( 'pizzalayer_setting_cx_text_removed',      'Removed from your pizza.' ),
			'textMaxToppings'   => self::g( 'pizzalayer_setting_cx_text_max_toppings', 'You\'ve reached the maximum number of toppings.' ),
			'showStartOver'     => self::gb( 'pizzalayer_setting_cx_show_start_over', 'yes' ) ? 'yes' : 'no',
			'startOverLabel'    => self::g( 'pizzalayer_setting_cx_start_over_label',  'Start Over' ),
			'showSummaryPanel'  => self::gb( 'pizzalayer_setting_cx_show_summary' ) ? 'yes' : 'no',
			'showReviewModal'   => self::gb( 'pizzalayer_setting_cx_review_modal' ) ? 'yes' : 'no',
			'showSpecialInstr'  => self::gb( 'pizzalayer_setting_cx_special_instructions' ) ? 'yes' : 'no',
			'specialInstrPlaceholder' => self::g( 'pizzalayer_setting_cx_special_instr_placeholder', 'Any special requests? (optional)' ),
			'specialInstrMaxLen'=> self::gi( 'pizzalayer_setting_cx_special_instr_max', 300 ),
			// Pricing (display)
			'priceDisplayMode'  => self::g( 'pizzalayer_setting_price_display_mode', 'total' ),
			// Cart/WooCommerce — defaults here, filterable so PizzaLayerPro can override.
			// Pro hooks into 'pizzalayer_js_cart_data' to supply live values.
			'addToCartLabel'    => (string) apply_filters( 'pizzalayer_cart_btn_text',       'Add to Cart' ),
			'showCartBtn'       => apply_filters( 'pizzalayer_show_cart_btn',        false ) ? 'yes' : 'no',
			'requireCrust'      => apply_filters( 'pizzalayer_require_crust',         false ) ? 'yes' : 'no',
			'requireSauce'      => apply_filters( 'pizzalayer_require_sauce',         false ) ? 'yes' : 'no',
			// Topping display
			'toppingPlacement'  => self::g( 'pizzalayer_setting_topping_placement', 'scattered' ),
			'toppingVisSize'    => self::gi( 'pizzalayer_setting_topping_vis_size', 20 ),
			'toppingShowBadge'  => self::gb( 'pizzalayer_setting_topping_show_badge' ) ? 'yes' : 'no',
			// Builder behaviour
			'stepByStep'        => self::gb( 'pizzalayer_setting_layout_step_by_step' ) ? 'yes' : 'no',
			'autoAdvance'       => self::gb( 'pizzalayer_setting_layout_auto_advance' ) ? 'yes' : 'no',
			// Advanced
			'debugMode'         => self::gb( 'pizzalayer_setting_adv_debug_mode' ) ? 'yes' : 'no',
			'logLevel'          => self::g( 'pizzalayer_setting_adv_log_level', 'off' ),
		];

		wp_localize_script( 'pizzalayer-js', 'pizzalayerSettings', $data );

		// Also try to attach to template scripts so they pick it up
		foreach ( [ 'pizzalayer-template-colorbox', 'pizzalayer-template-metro', 'pizzalayer-template-nightpie', 'pizzalayer-template-rustic' ] as $handle ) {
			if ( wp_script_is( $handle, 'enqueued' ) ) {
				wp_localize_script( $handle, 'pizzalayerSettings', $data );
			}
		}

		// Fornaia / rustic template — pass copy label overrides to JS
		if ( wp_script_is( 'pizzalayer-template-rustic', 'enqueued' ) ) {
			$rustic_labels = [
				'addLabel'     => (string) get_option( 'rustic_setting_add_label',    'Add' ),
				'removeLabel'  => (string) get_option( 'rustic_setting_remove_label', 'Remove' ),
				'chooseLabel'  => (string) get_option( 'rustic_setting_choose_label', 'Choose' ),
				'resetLabel'   => (string) get_option( 'rustic_setting_reset_label',  'Reset' ),
			];
			wp_localize_script( 'pizzalayer-template-rustic', 'pizzalayerRusticSettings', $rustic_labels );
		}
	}

	/**
	 * Apply custom tab order from settings to the pizzalayer_tab_order filter.
	 * Hooked in Plugin.php.
	 */
	public function apply_tab_order( array $tabs, string $instance_id ): array {
		$custom_order = self::g( 'pizzalayer_setting_layout_tab_order', '' );
		if ( ! $custom_order ) { return $tabs; }

		$ordered = array_map( 'trim', explode( ',', $custom_order ) );
		$ordered = array_filter( $ordered ); // remove empties

		// Reorder: start with items listed in settings, then append any unlisted ones
		$result = [];
		foreach ( $ordered as $t ) {
			if ( in_array( $t, $tabs, true ) ) {
				$result[] = $t;
			}
		}
		foreach ( $tabs as $t ) {
			if ( ! in_array( $t, $result, true ) ) {
				$result[] = $t;
			}
		}
		return $result;
	}

	/**
	 * Apply query-time settings when CPT posts are fetched for builder rendering.
	 * Hooked to pizzalayer_query_args_toppings filter.
	 *
	 * @param array  $args   WP_Query args
	 * @param string $type   CPT type slug
	 */
	public function apply_sort_filter( array $args, string $type ): array {
		if ( $type !== 'toppings' ) { return $args; }

		$sort_order = self::g( 'pizzalayer_setting_topping_sort', 'menu' );

		switch ( $sort_order ) {
			case 'alpha_asc':
				$args['orderby'] = 'title';
				$args['order']   = 'ASC';
				break;
			case 'alpha_desc':
				$args['orderby'] = 'title';
				$args['order']   = 'DESC';
				break;
			case 'price_asc':
				$args['meta_key'] = 'topping_price'; // phpcs:ignore WordPress.DB.SlowDBQuery
				$args['orderby']  = 'meta_value_num';
				$args['order']    = 'ASC';
				break;
			case 'price_desc':
				$args['meta_key'] = 'topping_price'; // phpcs:ignore WordPress.DB.SlowDBQuery
				$args['orderby']  = 'meta_value_num';
				$args['order']    = 'DESC';
				break;
			default: // 'menu' = WordPress menu_order
				$args['orderby'] = 'menu_order title';
				$args['order']   = 'ASC';
		}

		return $args;
	}

	/**
	 * Inject a11y + performance inline CSS rules (reduce-motion, high-contrast, etc).
	 * Uses body-class-triggered rules so they don't activate unless the setting is on.
	 */
	public function inject_a11y_css(): void {
		$css = '';

		// Reduce motion — disable all PizzaLayer animations
		$css .= '.pzl-reduce-motion .cb-layer-div img,'
			.  '.pzl-reduce-motion .cb-fly-clone,'
			.  '.pzl-reduce-motion .cb-toast{'
			.      'transition:none!important;animation:none!important;opacity:1!important;transform:none!important;'
			.  '}';

		// High contrast
		$css .= '.pzl-high-contrast .cb-root{'
			.      '--cb-border:rgba(0,0,0,0.5);--cb-text:#000000;--cb-bg:#ffffff;--cb-surface:#ffffff;'
			.  '}';
		$css .= '.pzl-high-contrast .cb-card{'
			.      'border-width:2px!important;border-color:#000!important;'
			.  '}';
		$css .= '.pzl-high-contrast .cb-card--selected{'
			.      'outline:3px solid #0000ff!important;'
			.  '}';

		// Debug mode — outline all builder elements
		$css .= '.pzl-debug-mode .cb-root *{'
			.      'outline:1px solid rgba(255,0,0,0.3);'
			.  '}';

		// Lazy load — native loading attr applied to all builder images
		$css .= '.pzl-lazy-load .cb-card__thumb img,'
			.  '.pzl-lazy-load .cb-layer-div img{'
			.      'content-visibility:auto;'
			.  '}';

		wp_add_inline_style( 'pizzalayer-css', $css );
	}
}
