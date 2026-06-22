<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Metro template — customization settings.
 *
 * Each entry renders as a field in Settings → Template Settings.
 * Supported types: text, text_wide, number, color, select, toggle, textarea, radio, range
 *
 * For 'color' type, set 'default' to the hex you want the revert button to restore.
 */
return [

	// ── Colours ──────────────────────────────────────────────────────
	[
		'key'     => 'metro_setting_accent_color',
		'type'    => 'color',
		'label'   => 'Accent Color',
		'desc'    => 'Primary action color used for buttons, active states, and highlights.',
		'default' => '#e63946',
	],
	[
		'key'     => 'metro_setting_background_color',
		'type'    => 'color',
		'label'   => 'Page Background Color',
		'desc'    => 'Background of the builder outer container.',
		'default' => '#f7f7f5',
	],
	[
		'key'     => 'metro_setting_card_bg_color',
		'type'    => 'color',
		'label'   => 'Card Background Color',
		'desc'    => 'Background of ingredient selection cards.',
		'default' => '#ffffff',
	],

	// ── Typography ────────────────────────────────────────────────────
	[
		'key'     => 'metro_setting_heading_font',
		'type'    => 'select',
		'label'   => 'Heading Font',
		'desc'    => 'Font stack used for section headings in the builder.',
		'default' => 'system',
		'options' => [
			'system'     => 'System UI (default)',
			'inter'      => 'Inter',
			'poppins'    => 'Poppins',
			'montserrat' => 'Montserrat',
			'playfair'   => 'Playfair Display (serif)',
		],
	],
	[
		'key'     => 'metro_setting_base_font_size',
		'type'    => 'range',
		'label'   => 'Base Font Size (px)',
		'desc'    => 'Adjusts the base text size inside the builder.',
		'default' => '14',
		'min'     => 12,
		'max'     => 20,
		'step'    => 1,
	],

	// ── Layout ────────────────────────────────────────────────────────
	[
		'key'     => 'metro_setting_layout_mode',
		'type'    => 'radio',
		'label'   => 'Layout Mode',
		'desc'    => 'Controls how the pizza visualizer and ingredient panels are arranged.',
		'default' => 'centered',
		'options' => [
			'centered'     => 'Centered hero (pizza top, ingredients below)',
			'side-by-side' => 'Side by side (pizza left, panels right)',
			'fullwidth'    => 'Full-width panels, sticky visualizer',
		],
	],
	[
		'key'     => 'metro_setting_card_columns',
		'type'    => 'select',
		'label'   => 'Ingredient Card Columns',
		'desc'    => 'Number of columns in the ingredient grid.',
		'default' => '3',
		'options' => [
			'2'    => '2 columns',
			'3'    => '3 columns (default)',
			'4'    => '4 columns',
			'auto' => 'Auto (responsive)',
		],
	],
	[
		'key'     => 'metro_setting_visualizer_size',
		'type'    => 'number',
		'label'   => 'Visualizer Max Width (px)',
		'desc'    => 'Maximum pixel width of the pizza canvas. Leave 0 for template default.',
		'default' => '0',
		'min'     => 0,
		'max'     => 800,
		'step'    => 10,
	],

	// ── Card Style ────────────────────────────────────────────────────
	[
		'key'     => 'metro_setting_card_style',
		'type'    => 'select',
		'label'   => 'Ingredient Card Style',
		'desc'    => 'Visual presentation style for ingredient selection cards.',
		'default' => 'standard',
		'options' => [
			'standard'    => 'Standard — image top, name + button below',
			'compact'     => 'Compact — small image, horizontal layout',
			'minimal'     => 'Minimal — name only, no image shown',
			'large-image' => 'Large Image — taller photo, name overlaid',
			'pill'        => 'Pill — round thumb, horizontal chip layout',
		],
	],

	// ── Tab Bar Style ─────────────────────────────────────────────────
	[
		'key'     => 'metro_setting_tab_style',
		'type'    => 'select',
		'label'   => 'Section Tab Bar Style',
		'desc'    => 'Visual style of the builder\'s section navigation tabs.',
		'default' => 'scrollbar',
		'options' => [
			'scrollbar'  => 'Scroll Bar — icon + label, horizontal scroll',
			'icons-only' => 'Icons Only — compact icon strip',
			'pills'      => 'Pills — rounded pill buttons',
			'underline'  => 'Underline — minimal underline tabs',
			'sidebar'    => 'Sidebar — vertical left-side nav',
		],
	],

	// ── Features ─────────────────────────────────────────────────────
	[
		'key'          => 'metro_setting_show_ingredient_prices',
		'type'         => 'toggle',
		'label'        => 'Show Ingredient Prices',
		'desc'         => 'Display the price of each ingredient on its selection card.',
		'default'      => 'no',
		'toggle_label' => 'Show prices on cards',
	],
	[
		'key'          => 'metro_setting_show_summary_bar',
		'type'         => 'toggle',
		'label'        => 'Show Running Summary Bar',
		'desc'         => 'Sticky bar at the bottom showing selected ingredients and running total.',
		'default'      => 'yes',
		'toggle_label' => 'Show summary bar',
	],
	[
		'key'          => 'metro_setting_sticky_visualizer',
		'type'         => 'toggle',
		'label'        => 'Sticky Pizza Visualizer',
		'desc'         => 'Keep the pizza canvas fixed in view as the user scrolls through ingredients.',
		'default'      => 'no',
		'toggle_label' => 'Enable sticky canvas',
	],
	[
		'key'          => 'metro_setting_show_ingredient_count',
		'type'         => 'toggle',
		'label'        => 'Show Topping Count Badge',
		'desc'         => 'Display how many toppings have been selected vs. the maximum.',
		'default'      => 'yes',
		'toggle_label' => 'Show topping counter',
	],

	// ── Branding ─────────────────────────────────────────────────────
	[
		'key'         => 'metro_setting_hero_tagline',
		'type'        => 'text_wide',
		'label'       => 'Hero Tagline',
		'desc'        => 'Short tagline displayed above the pizza visualizer in centered layout.',
		'default'     => '',
		'placeholder' => 'e.g. Build your perfect pizza',
	],
	[
		'key'     => 'metro_setting_footer_note',
		'type'    => 'textarea',
		'label'   => 'Builder Footer Note',
		'desc'    => 'Optional note or allergy disclaimer shown below the builder. Supports basic HTML.',
		'default' => '',
		'rows'    => 2,
	],

	// ── Spacing ───────────────────────────────────────────────────────
	[
		'key'     => 'metro_setting_section_gap',
		'type'    => 'range',
		'label'   => 'Section Gap (px)',
		'desc'    => 'Vertical spacing between ingredient sections.',
		'default' => '24',
		'min'     => 8,
		'max'     => 60,
		'step'    => 4,
	],
	[
		'key'     => 'metro_setting_card_border_radius',
		'type'    => 'range',
		'label'   => 'Card Corner Radius (px)',
		'desc'    => 'Border radius for ingredient selection cards.',
		'default' => '14',
		'min'     => 0,
		'max'     => 24,
		'step'    => 1,
	],
];
