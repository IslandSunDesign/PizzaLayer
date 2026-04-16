<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) { exit; }

/**
 * PizzaLayer Uninstall
 *
 * Removes all plugin options, CPT posts (and their postmeta), and
 * transients from the database.
 */

// ── Core plugin options (mirrors Settings::OPTIONS) ────────────────────
$options = [
	// Template
	'pizzalayer_setting_global_template',
	// Pizza display
	'pizzalayer_setting_pizza_size_max',
	'pizzalayer_setting_pizza_size_min',
	'pizzalayer_setting_pizza_border',
	'pizzalayer_setting_pizza_border_color',
	'pizzalayer_setting_global_color',
	// Pizza shape
	'pizzalayer_setting_pizza_shape',
	'pizzalayer_setting_pizza_aspect',
	'pizzalayer_setting_pizza_radius',
	// Layer animation
	'pizzalayer_setting_layer_anim',
	'pizzalayer_setting_layer_anim_speed',
	// Layer defaults
	'pizzalayer_setting_crust_defaultcrust',
	'pizzalayer_setting_sauce_defaultsauce',
	'pizzalayer_setting_cheese_defaultcheese',
	'pizzalayer_setting_drizzle_defaultdrizzle',
	'pizzalayer_setting_cut_defaultcut',
	// Crust
	'pizzalayer_setting_crust_aspectratio',
	'pizzalayer_setting_crust_padding',
	// Sauce
	'pizzalayer_setting_sauce_padding',
	// Cheese
	'pizzalayer_cheese_setting_cheesedistance',
	'pizzalayer_setting_cheese_padding',
	// Toppings
	'pizzalayer_setting_topping_maxtoppings',
	'pizzalayer_setting_topping_fractions',
	// Display features
	'pizzalayer_setting_show_thumbnails',
	'pizzalayer_setting_element_style_layers',
	'pizzalayer_setting_element_style_toppings',
	'pizzalayer_setting_element_style_topping_choice_menu',
	// Branding
	'pizzalayer_setting_branding_altlogo',
	'pizzalayer_setting_branding_logo_width',
	'pizzalayer_setting_branding_logo_height',
	'pizzalayer_setting_branding_logo_alt',
	'pizzalayer_setting_branding_tagline',
	'pizzalayer_setting_branding_primary_color',
	'pizzalayer_setting_branding_secondary_color',
	'pizzalayer_setting_branding_footer_text',
	'pizzalayer_setting_branding_menu_title',
	'pizzalayer_setting_branding_header_custom_content',
	// Plugin settings
	'pizzalayer_setting_settings_demonotice',
	'pizzalayer_setting_global_help_content',
	// Builder Layout & Behaviour
	'pizzalayer_setting_layout_mode',
	'pizzalayer_setting_layout_builder_width',
	'pizzalayer_setting_layout_mobile_bp',
	'pizzalayer_setting_layout_mobile',
	'pizzalayer_setting_layout_step_by_step',
	'pizzalayer_setting_layout_auto_advance',
	'pizzalayer_setting_layout_tab_order',
	'pizzalayer_setting_layout_hide_empty',
	'pizzalayer_setting_layout_keyboard_nav',
	'pizzalayer_setting_layout_sticky_header',
	// Pricing & Cart
	'pizzalayer_setting_price_display_mode',
	'pizzalayer_setting_price_base',
	'pizzalayer_setting_price_currency_pos',
	'pizzalayer_setting_price_update_anim',
	// Typography
	'pizzalayer_setting_typo_font_family',
	'pizzalayer_setting_typo_google_font',
	'pizzalayer_setting_typo_base_size',
	'pizzalayer_setting_typo_heading_fw',
	'pizzalayer_setting_typo_label_size',
	'pizzalayer_setting_typo_price_size',
	'pizzalayer_setting_typo_btn_fw',
	'pizzalayer_setting_typo_letter_sp',
	'pizzalayer_setting_typo_text_transform',
	// Global Colour Palette
	'pizzalayer_setting_color_bg',
	'pizzalayer_setting_color_menu_bg',
	'pizzalayer_setting_color_card_bg',
	'pizzalayer_setting_color_card_border',
	'pizzalayer_setting_color_selected',
	'pizzalayer_setting_color_tab_bg',
	'pizzalayer_setting_color_tab_active',
	'pizzalayer_setting_color_tab_text',
	'pizzalayer_setting_color_btn_bg',
	'pizzalayer_setting_color_btn_text',
	'pizzalayer_setting_color_btn2_bg',
	'pizzalayer_setting_color_body_text',
	'pizzalayer_setting_color_muted_text',
	'pizzalayer_setting_color_error',
	'pizzalayer_setting_color_success',
	// Spacing & Borders
	'pizzalayer_setting_spacing_outer_pad',
	'pizzalayer_setting_spacing_grid_gap',
	'pizzalayer_setting_spacing_card_pad',
	'pizzalayer_setting_spacing_card_radius',
	'pizzalayer_setting_spacing_card_border',
	'pizzalayer_setting_spacing_btn_radius',
	'pizzalayer_setting_spacing_tab_height',
	'pizzalayer_setting_spacing_shadow',
	'pizzalayer_setting_spacing_shadow_css',
	'pizzalayer_setting_spacing_divider',
	// Topping Display
	'pizzalayer_setting_topping_thumb_size',
	'pizzalayer_setting_topping_thumb_custom',
	'pizzalayer_setting_topping_cols_desktop',
	'pizzalayer_setting_topping_cols_mobile',
	'pizzalayer_setting_topping_placement',
	'pizzalayer_setting_topping_vis_size',
	'pizzalayer_setting_topping_vis_opacity',
	'pizzalayer_setting_topping_show_badge',
	'pizzalayer_setting_topping_group_cats',
	'pizzalayer_setting_topping_sort',
	// Accessibility & Performance
	'pizzalayer_setting_a11y_reduce_motion',
	'pizzalayer_setting_a11y_high_contrast',
	'pizzalayer_setting_a11y_focus_ring',
	'pizzalayer_setting_a11y_aria_lang',
	'pizzalayer_setting_perf_lazy_load',
	'pizzalayer_setting_perf_preload_assets',
	'pizzalayer_setting_perf_img_format',
	'pizzalayer_setting_perf_cache',
	// Customer Experience
	'pizzalayer_setting_cx_show_summary',
	'pizzalayer_setting_cx_toast_style',
	'pizzalayer_setting_cx_toast_duration',
	'pizzalayer_setting_cx_text_added',
	'pizzalayer_setting_cx_text_removed',
	'pizzalayer_setting_cx_text_max_toppings',
	'pizzalayer_setting_cx_show_start_over',
	'pizzalayer_setting_cx_start_over_label',
	'pizzalayer_setting_cx_special_instructions',
	'pizzalayer_setting_cx_special_instr_placeholder',
	'pizzalayer_setting_cx_special_instr_max',
	'pizzalayer_setting_cx_review_modal',
	// Advanced & Developer
	'pizzalayer_setting_adv_custom_css',
	'pizzalayer_setting_adv_custom_js',
	'pizzalayer_setting_adv_debug_mode',
	'pizzalayer_setting_adv_disable_css',
	'pizzalayer_setting_adv_rest_cache_ttl',
	'pizzalayer_setting_adv_log_level',

	// ── Plainlist template settings ─────────────────────────────
	'plainlist_setting_layout_mode',
	'plainlist_setting_accent_color',
	'plainlist_setting_bg_color',
	'plainlist_setting_section_header_color',
	'plainlist_setting_item_text_color',
	'plainlist_setting_divider_color',
	'plainlist_setting_font_family',
	'plainlist_setting_base_font_size',
	'plainlist_setting_heading_size',
	'plainlist_setting_heading_weight',
	'plainlist_setting_text_transform',
	'plainlist_setting_check_style',
	'plainlist_setting_check_size',
	'plainlist_setting_max_width',
	'plainlist_setting_section_gap',
	'plainlist_setting_item_gap',
	'plainlist_setting_columns',
	'plainlist_setting_show_dividers',
	'plainlist_setting_show_section_icons',
	'plainlist_setting_show_prices',
	'plainlist_setting_show_item_count',
	'plainlist_setting_show_summary',
	'plainlist_setting_show_reset',
	'plainlist_setting_step_btn_label_next',
	'plainlist_setting_step_btn_label_prev',
	'plainlist_setting_step_show_progress',
	'plainlist_setting_step_require_selection',
	'plainlist_setting_intro_text',
	'plainlist_setting_footer_note',
	'plainlist_setting_summary_heading',
	'plainlist_setting_reset_label',

	// ── Rustic template settings ─────────────────────────────────
	'rustic_setting_bg_color',
	'rustic_setting_surface_color',
	'rustic_setting_card_bg_color',
	'rustic_setting_accent_color',
	'rustic_setting_gold_color',
	'rustic_setting_text_color',
	'rustic_setting_muted_text_color',
	'rustic_setting_font_serif',
	'rustic_setting_font_size',
	'rustic_setting_preview_col_width',
	'rustic_setting_pizza_canvas_size',
	'rustic_setting_card_radius',
	'rustic_setting_cards_per_row',
	'rustic_setting_show_step_labels',
	'rustic_setting_show_step_icons',
	'rustic_setting_stepnav_bg',
	'rustic_setting_stepnav_active_color',
	'rustic_setting_preview_bg',
	'rustic_setting_show_badge',
	'rustic_setting_badge_top_text',
	'rustic_setting_badge_main_text',
	'rustic_setting_badge_bottom_text',
	'rustic_setting_pizza_canvas_bg',
	'rustic_setting_show_grain_texture',
	'rustic_setting_show_wood_grain',
	'rustic_setting_show_lined_paper',
	'rustic_setting_show_corner_fold',
	'rustic_setting_uppercase_btns',
	'rustic_setting_btn_style',
	'rustic_setting_card_hover_lift',
	'rustic_setting_order_title',
	'rustic_setting_order_tagline',
	'rustic_setting_add_label',
	'rustic_setting_remove_label',
	'rustic_setting_choose_label',
	'rustic_setting_reset_label',

	// ── Metro template settings ───────────────────────────────────
	'metro_setting_accent_color',
	'metro_setting_background_color',
	'metro_setting_card_bg_color',
	'metro_setting_heading_font',
	'metro_setting_base_font_size',
	'metro_setting_card_border_radius',
	'metro_setting_card_columns',
	'metro_setting_card_style',
	'metro_setting_footer_note',
	'metro_setting_hero_tagline',
	'metro_setting_layout_mode',
	'metro_setting_section_gap',
	'metro_setting_show_ingredient_count',
	'metro_setting_show_ingredient_prices',
	'metro_setting_show_summary_bar',
	'metro_setting_sticky_visualizer',
	'metro_setting_tab_style',
	'metro_setting_visualizer_size',

	// ── PocketPie template settings ───────────────────────────────
	'pocketpie_setting_default_layout',
	'pocketpie_setting_widget_max_width',
	'pocketpie_setting_pizza_size_cq',
	'pocketpie_setting_pizza_size_ld',
	'pocketpie_setting_pizza_size_sd',
	'pocketpie_setting_pizza_size_sp',
	'pocketpie_setting_theme',
	'pocketpie_setting_color_accent',
	'pocketpie_setting_color_accent2',
	'pocketpie_setting_color_bg',
	'pocketpie_setting_color_bg2',
	'pocketpie_setting_color_bg3',
	'pocketpie_setting_color_border',
	'pocketpie_setting_color_muted',
	'pocketpie_setting_color_success',
	'pocketpie_setting_color_text',
	'pocketpie_setting_font_family',
	'pocketpie_setting_font_custom',
	'pocketpie_setting_font_base_size',
	'pocketpie_setting_chip_cols',
	'pocketpie_setting_chip_hover_anim',
	'pocketpie_setting_chip_radius',
	'pocketpie_setting_chip_show_name',
	'pocketpie_setting_chip_thumb_size',
	'pocketpie_setting_close_on_backdrop',
	'pocketpie_setting_corner_quad_aspect',
	'pocketpie_setting_coverage_reveal',
	'pocketpie_setting_coverage_style',
	'pocketpie_setting_cq_corner_bl',
	'pocketpie_setting_cq_corner_br',
	'pocketpie_setting_cq_corner_tl',
	'pocketpie_setting_cq_corner_tr',
	'pocketpie_setting_cq_panel_max_height',
	'pocketpie_setting_cq_panel_width',
	'pocketpie_setting_cq_trigger_size',
	'pocketpie_setting_custom_css',
	'pocketpie_setting_grain_overlay',
	'pocketpie_setting_label_transform',
	'pocketpie_setting_ld_deck_thumb_width',
	'pocketpie_setting_ld_preview_height',
	'pocketpie_setting_ld_show_sel_label',
	'pocketpie_setting_modal_anim',
	'pocketpie_setting_modal_backdrop',
	'pocketpie_setting_review_btn_label',
	'pocketpie_setting_sd_drawer_max_height',
	'pocketpie_setting_sd_pill_position',
	'pocketpie_setting_sd_pill_style',
	'pocketpie_setting_sd_swipe_close',
	'pocketpie_setting_show_reset',
	'pocketpie_setting_show_review_btn',
	'pocketpie_setting_show_summary_pizza',
	'pocketpie_setting_sp_sheet_max_height',
	'pocketpie_setting_sp_show_step_dots',
	'pocketpie_setting_sp_step_label',
	'pocketpie_setting_sp_swipe_close',
	'pocketpie_setting_summary_show_empty_rows',
	'pocketpie_setting_summary_title',
	'pocketpie_setting_toppings_cols',
	'pocketpie_setting_transition_speed',

	// ── Scaffold template settings ────────────────────────────────
	'scaffold_setting_accent_color',
	'scaffold_setting_anim_speed',
	'scaffold_setting_base_font_size',
	'scaffold_setting_bg_color',
	'scaffold_setting_border_color',
	'scaffold_setting_builder_width',
	'scaffold_setting_card_cols',
	'scaffold_setting_card_radius',
	'scaffold_setting_custom_css',
	'scaffold_setting_font_custom',
	'scaffold_setting_font_family',
	'scaffold_setting_show_labels',
	'scaffold_setting_summary_title',
	'scaffold_setting_tab_style',
	'scaffold_setting_text_color',
	'scaffold_setting_thumb_size',

	// ── Preview URL (Template page) ───────────────────────────────
	'pizzalayer_template_preview_url',

	// ── Plugin state / UI flags ────────────────────────────────────
	'pizzalayer_setup_done',
	'pizzalayer_setting_dark_mode',
	'pizzalayer_wizard_done',
];

foreach ( $options as $opt ) {
	delete_option( $opt );
}

// ── Delete all CPT posts and their postmeta ────────────────────────────
$cpt_slugs = [
	'pizzalayer_toppings',
	'pizzalayer_crusts',
	'pizzalayer_sauces',
	'pizzalayer_cheeses',
	'pizzalayer_drizzles',
	'pizzalayer_cuts',
	'pizzalayer_sizes',
];

foreach ( $cpt_slugs as $post_type ) {
	$posts = get_posts( [
		'post_type'      => $post_type,
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'fields'         => 'ids',
	] );

	foreach ( $posts as $post_id ) {
		wp_delete_post( (int) $post_id, true ); // true = force-delete, skip trash
	}
}

// ── Delete any plugin transients ──────────────────────────────────────
global $wpdb;
// phpcs:ignore WordPress.DB.DirectDatabaseQuery
$wpdb->query(
	"DELETE FROM {$wpdb->options}
	 WHERE option_name LIKE '_transient_pizzalayer_%'
	    OR option_name LIKE '_transient_timeout_pizzalayer_%'"
);
