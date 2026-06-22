<?php
namespace PizzaLayer\Admin;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * PizzaLayer Settings page — replaces all WP Customizer entries
 * with a native admin UI. Reads/writes the same option keys the
 * customizer used so front-end output is unchanged.
 */
class Settings {

	/** All option keys managed by this page. */
	private const OPTIONS = [
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
		// WooCommerce cart options moved to PizzaLayerPro
		// 'pizzalayer_setting_price_show_cart_btn'  → pztpro_get_setting('show_cart_btn')
		// 'pizzalayer_setting_price_cart_btn_text'  → pztpro_get_setting('cart_btn_text')
		// 'pizzalayer_setting_price_require_crust'  → pztpro_get_setting('require_crust')
		// 'pizzalayer_setting_price_require_sauce'  → pztpro_get_setting('require_sauce')
		// 'pizzalayer_setting_price_min_order'      → pztpro_get_setting('min_order')
		// 'pizzalayer_setting_price_tax_display'    → pztpro_get_setting('tax_display')
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
		'pizzalayer_setting_adv_rest_api_enabled',
		'pizzalayer_setting_adv_rest_cache_ttl',
		'pizzalayer_setting_adv_log_level',
		// Plainlist template settings
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
		// Active template — stored separately from Settings page but exported/imported here
		'pizzalayer_setting_global_template',
	];

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) { return; }

		// Import: read uploaded JSON
		$import_msg = '';
		if ( isset( $_POST['pizzalayer_import_settings'], $_POST['_wpnonce'] )
		     && wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'pizzalayer_settings_save' ) ) {
			$import_msg = $this->import_settings();
		}

		// Save
		if ( isset( $_POST['pizzalayer_settings_save'], $_POST['_wpnonce'] )
		     && wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'pizzalayer_settings_save' ) ) {
			$this->save_settings();
			echo '<div class="notice notice-success is-dismissible"><p><strong>' . esc_html__( 'Settings saved.', 'pizzalayer' ) . '</strong></p></div>';
		}

		if ( $import_msg ) {
			echo $import_msg; // phpcs:ignore — sanitized in import_settings()
		}

		// Load CPT options for dropdowns
		$q = [ 'posts_per_page' => -1, 'post_status' => 'publish', 'orderby' => 'title', 'order' => 'ASC' ];
		$crusts   = get_posts( array_merge( $q, [ 'post_type' => 'pizzalayer_crusts'   ] ) );
		$sauces   = get_posts( array_merge( $q, [ 'post_type' => 'pizzalayer_sauces'   ] ) );
		$cheeses  = get_posts( array_merge( $q, [ 'post_type' => 'pizzalayer_cheeses'  ] ) );
		$drizzles = get_posts( array_merge( $q, [ 'post_type' => 'pizzalayer_drizzles' ] ) );
		$cuts     = get_posts( array_merge( $q, [ 'post_type' => 'pizzalayer_cuts'     ] ) );

		$g = fn( string $key, string $default = '' ) => (string) get_option( $key, $default );

		// Active template
		$active_template = (string) get_option( 'pizzalayer_setting_global_template', '' );

		// Load template settings if available
		$template_settings = [];
		if ( $active_template ) {
			$tpl_dirs = [
				get_stylesheet_directory() . '/pzttemplates/' . $active_template . '/',
				PIZZALAYER_TEMPLATES_DIR . $active_template . '/',
			];
			foreach ( $tpl_dirs as $dir ) {
				$options_file = $dir . 'pztp-template-options.php';
				if ( file_exists( $options_file ) ) {
					$template_settings = include $options_file;
					if ( ! is_array( $template_settings ) ) { $template_settings = []; }
					break;
				}
			}
		}

		?>
		<div class="wrap pset-wrap">
		<?php $this->render_styles(); ?>

		<div class="pset-header" style="display:flex;align-items:center;gap:16px;justify-content:space-between;flex-wrap:wrap;">
			<div style="display:flex;align-items:center;gap:16px;">
				<span class="dashicons dashicons-admin-settings pset-header__icon"></span>
				<div>
					<h1 class="pset-header__title"><?php esc_html_e( 'Settings', 'pizzalayer' ); ?></h1>
					<p class="pset-header__sub"><?php esc_html_e( 'All plugin settings in one place. New to PizzaLayer? Try the Settings Wizard for a plain-English guided walk-through.', 'pizzalayer' ); ?></p>
				</div>
			</div>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=pizzalayer-wizard' ) ); ?>" class="button" style="background:rgba(255,255,255,.15);border-color:rgba(255,255,255,.3);color:#fff;white-space:nowrap;">
				<span class="dashicons dashicons-welcome-learn-more" style="margin-top:3px;margin-right:4px;"></span>
				<?php esc_html_e( '✦ Settings Wizard', 'pizzalayer' ); ?>
			</a>
		</div>

		<!-- ── Quick-jump pill nav ─────────────────────────────────────── -->
		<nav class="pset-quickjump" aria-label="Jump to section">
			<?php
			$pset_sections = [
				'default-layers'      => [ 'dashicons-category',        __( 'Default Layers', 'pizzalayer' ) ],
				'toppings'            => [ 'dashicons-star-filled',      __( 'Toppings', 'pizzalayer' ) ],
				'pizza-display'       => [ 'dashicons-pizza',            __( 'Pizza Display', 'pizzalayer' ) ],
				'pizza-shape'         => [ 'dashicons-image-crop',       __( 'Pizza Shape', 'pizzalayer' ) ],
				'layer-animation'     => [ 'dashicons-controls-play',    __( 'Animations', 'pizzalayer' ) ],
				'crust-options'       => [ 'dashicons-tag',              __( 'Crust', 'pizzalayer' ) ],
				'sauce-cheese'        => [ 'dashicons-admin-generic',    __( 'Sauce & Cheese', 'pizzalayer' ) ],
				'ui-styles'           => [ 'dashicons-art',              __( 'UI Styles', 'pizzalayer' ) ],
				'branding'            => [ 'dashicons-admin-customizer', __( 'Branding', 'pizzalayer' ) ],
				'plugin-settings'     => [ 'dashicons-info-outline',     __( 'Plugin', 'pizzalayer' ) ],
				'builder-layout'      => [ 'dashicons-layout',           __( 'Layout', 'pizzalayer' ) ],
				'pricing-cart'        => [ 'dashicons-cart',             __( 'Pricing', 'pizzalayer' ) ],
				'typography'          => [ 'dashicons-editor-textcolor', __( 'Typography', 'pizzalayer' ) ],
				'colour-palette'      => [ 'dashicons-color-picker',     __( 'Colours', 'pizzalayer' ) ],
				'spacing-borders'     => [ 'dashicons-editor-expand',    __( 'Spacing', 'pizzalayer' ) ],
				'topping-display'     => [ 'dashicons-images-alt2',      __( 'Topping Display', 'pizzalayer' ) ],
				'accessibility-perf'  => [ 'dashicons-universal-access', __( 'A11y & Perf', 'pizzalayer' ) ],
				'customer-experience' => [ 'dashicons-smiley',           __( 'Customer UX', 'pizzalayer' ) ],
				'advanced-dev'        => [ 'dashicons-editor-code',      __( 'Advanced', 'pizzalayer' ) ],
				'data-backup'         => [ 'dashicons-database-import',   __( 'Import/Export', 'pizzalayer' ) ],
			];
			if ( $active_template ) {
				$pset_sections['template-settings'] = [ 'dashicons-admin-appearance', ucwords( str_replace( '-', ' ', $active_template ) ) . ' Template' ];
			}
			foreach ( $pset_sections as $pset_slug => [ $pset_icon, $pset_label ] ) :
			?>
			<a href="#pset-body-<?php echo esc_attr( $pset_slug ); ?>" class="pset-quickjump__pill" data-section="<?php echo esc_attr( $pset_slug ); ?>">
				<span class="dashicons <?php echo esc_attr( $pset_icon ); ?>"></span>
				<?php echo esc_html( $pset_label ); ?>
			</a>
			<?php endforeach; ?>
		</nav>

		<form method="post" action="" id="pset-form" enctype="multipart/form-data">
		<?php wp_nonce_field( 'pizzalayer_settings_save' ); ?>
		<input type="hidden" name="pizzalayer_settings_save" value="1">

		<div class="pset-layout">
		<div class="pset-main">

		<!-- ══ Section: Default Layers ═══════════════════════════════ -->
		<div class="pset-card">
			<div class="pset-card__head pset-card__head--collapsible" data-pset-toggle="default-layers">
				<div>
					<h2><span class="dashicons dashicons-category"></span> <?php esc_html_e( 'Default Layers', 'pizzalayer' ); ?></h2>
					<p><?php esc_html_e( 'These layers are pre-selected when the builder loads, unless overridden by the shortcode attribute.', 'pizzalayer' ); ?></p>
				</div>
				<button type="button" class="pset-collapse-btn" aria-expanded="true" aria-controls="pset-body-default-layers"><span class="dashicons dashicons-arrow-up-alt2"></span></button>
			</div>
			<div class="pset-card__body" id="pset-body-default-layers">
				<div class="pset-grid pset-grid--layers">
					<?php $this->render_layer_picker( __( 'Default Crust',   'pizzalayer' ), 'pizzalayer_setting_crust_defaultcrust',     $crusts,   $g('pizzalayer_setting_crust_defaultcrust') ); ?>
					<?php $this->render_layer_picker( __( 'Default Sauce',   'pizzalayer' ), 'pizzalayer_setting_sauce_defaultsauce',     $sauces,   $g('pizzalayer_setting_sauce_defaultsauce') ); ?>
					<?php $this->render_layer_picker( __( 'Default Cheese',  'pizzalayer' ), 'pizzalayer_setting_cheese_defaultcheese',   $cheeses,  $g('pizzalayer_setting_cheese_defaultcheese') ); ?>
					<?php $this->render_layer_picker( __( 'Default Drizzle', 'pizzalayer' ), 'pizzalayer_setting_drizzle_defaultdrizzle', $drizzles, $g('pizzalayer_setting_drizzle_defaultdrizzle') ); ?>
					<?php $this->render_layer_picker( __( 'Default Cut',     'pizzalayer' ), 'pizzalayer_setting_cut_defaultcut',         $cuts,     $g('pizzalayer_setting_cut_defaultcut') ); ?>
				</div>
			</div>
		</div>

		<!-- ══ Layer Picker Modal ═════════════════════════════════ -->
		<div id="pset-layer-modal" class="pset-modal" role="dialog" aria-modal="true" aria-label="Choose layer" style="display:none;">
			<div class="pset-modal__backdrop"></div>
			<div class="pset-modal__box">
				<div class="pset-modal__head">
					<h3 id="pset-modal-title" class="pset-modal__title"><?php esc_html_e( 'Choose a layer', 'pizzalayer' ); ?></h3>
					<button type="button" class="pset-modal__close" aria-label="<?php esc_attr_e( 'Close', 'pizzalayer' ); ?>">&times;</button>
				</div>
				<div class="pset-modal__search-wrap">
					<span class="dashicons dashicons-search pset-modal__search-icon"></span>
					<input type="text" id="pset-modal-search" class="pset-modal__search" placeholder="<?php esc_attr_e( 'Searchâ¦', 'pizzalayer' ); ?>" autocomplete="off">
				</div>
				<div id="pset-modal-grid" class="pset-modal__grid"></div>
				<div class="pset-modal__foot">
					<button type="button" class="pset-modal__clear button">
						<span class="dashicons dashicons-dismiss"></span> <?php esc_html_e( 'Clear selection', 'pizzalayer' ); ?>
					</button>
				</div>
			</div>
		</div>

		<!-- ══ Section: Toppings ═════════════════════════════════════ -->
		<div class="pset-card">
			<div class="pset-card__head pset-card__head--collapsible" data-pset-toggle="toppings">
				<div>
					<h2><span class="dashicons dashicons-star-filled"></span> <?php esc_html_e( 'Toppings', 'pizzalayer' ); ?></h2>
					<p>Controls how many toppings customers can add and what pizza fractions are available.</p>
				</div>
				<button type="button" class="pset-collapse-btn" aria-expanded="true" aria-controls="pset-body-toppings"><span class="dashicons dashicons-arrow-up-alt2"></span></button>
			</div>
			<div class="pset-card__body" id="pset-body-toppings">
				<div class="pset-grid">
					<div class="pset-field">
						<label><?php esc_html_e( 'Max Toppings', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Maximum number of toppings a customer can add. 0 = unlimited.', 'pizzalayer' ); ?></p>
						<input type="number" name="pizzalayer_setting_topping_maxtoppings" min="0"
						       value="<?php echo esc_attr( $g('pizzalayer_setting_topping_maxtoppings') ); ?>" class="pset-input">
					</div>
					<div class="pset-field pset-field--full">
						<label><?php esc_html_e( 'Topping Portions', 'pizzalayer' ); ?></label>
						<p class="pset-desc">Choose which coverage options are available when customers apply toppings. <strong>Whole</strong> is always shown first. Uncheck any portions you do not want to offer.</p>
						<?php
						$_saved_fractions = get_option( 'pizzalayer_setting_topping_fractions', [] );
						if ( ! is_array( $_saved_fractions ) ) {
							// Migrate legacy single-value string → array
							$_lv = (string) $_saved_fractions;
							$_saved_fractions = [ 'whole' ];
							if ( $_lv === 'halves' || $_lv === 'quarters' ) {
								$_saved_fractions[] = 'half-left';
								$_saved_fractions[] = 'half-right';
							}
							if ( $_lv === 'quarters' ) {
								$_saved_fractions[] = 'quarter-top-left';
								$_saved_fractions[] = 'quarter-top-right';
								$_saved_fractions[] = 'quarter-bottom-left';
								$_saved_fractions[] = 'quarter-bottom-right';
							}
						}
						if ( empty( $_saved_fractions ) ) {
							$_saved_fractions = [ 'whole', 'half-left', 'half-right', 'quarter-top-left', 'quarter-top-right', 'quarter-bottom-left', 'quarter-bottom-right' ];
						}
						$_fraction_opts = [
							'whole'                => [ 'Whole',    'dashicons-marker',           'Always available — the full pizza.' ],
							'half-left'            => [ 'Left ½',   'dashicons-arrow-left-alt2',  'Left half of the pizza.' ],
							'half-right'           => [ 'Right ½',  'dashicons-arrow-right-alt2', 'Right half of the pizza.' ],
							'quarter-top-left'     => [ 'Q1 ↖',     'dashicons-editor-ul',        'Top-left quarter.' ],
							'quarter-top-right'    => [ 'Q2 ↗',     'dashicons-editor-ul',        'Top-right quarter.' ],
							'quarter-bottom-left'  => [ 'Q3 ↙',     'dashicons-editor-ul',        'Bottom-left quarter.' ],
							'quarter-bottom-right' => [ 'Q4 ↘',     'dashicons-editor-ul',        'Bottom-right quarter.' ],
						];
						?>
						<div class="pset-portions-grid">
							<?php foreach ( $_fraction_opts as $_fv => [ $_fl, $_fi, $_fd ] ) : ?>
							<label class="pset-portion-box<?php echo in_array( $_fv, $_saved_fractions, true ) ? ' pset-portion-box--on' : ''; ?>"
							       title="<?php echo esc_attr( $_fd ); ?>">
								<input type="checkbox"
								       name="pizzalayer_setting_topping_fractions[]"
								       value="<?php echo esc_attr( $_fv ); ?>"
								       <?php checked( in_array( $_fv, $_saved_fractions, true ) ); ?>
								       <?php echo $_fv === 'whole' ? 'disabled checked' : ''; ?>>
								<span class="pset-portion-box__label"><?php echo esc_html( $_fl ); ?></span>
							</label>
							<?php endforeach; ?>
						</div>
						<p class="pset-desc" style="margin-top:6px;">
							<em>Whole is always enabled. Changes here affect all templates and the fraction picker shown to customers.</em>
						</p>
					</div>
				</div>
			</div>
		</div>

		<!-- ══ Section: Pizza Display ════════════════════════════════ -->
		<div class="pset-card">
			<div class="pset-card__head pset-card__head--collapsible" data-pset-toggle="pizza-display">
				<div>
					<h2><span class="dashicons dashicons-pizza"></span> <?php esc_html_e( 'Pizza Display', 'pizzalayer' ); ?></h2>
					<p>Control the size and appearance of the pizza visualizer circle.</p>
				</div>
				<button type="button" class="pset-collapse-btn" aria-expanded="true" aria-controls="pset-body-pizza-display"><span class="dashicons dashicons-arrow-up-alt2"></span></button>
			</div>
			<div class="pset-card__body" id="pset-body-pizza-display">
				<div class="pset-grid">
					<div class="pset-field">
						<label><?php esc_html_e( 'Max Size', 'pizzalayer' ); ?></label>
						<p class="pset-desc">Max size (px or %). Include unit — e.g. <code>500px</code> or <code>100%</code>.</p>
						<input type="text" name="pizzalayer_setting_pizza_size_max"
						       value="<?php echo esc_attr( $g('pizzalayer_setting_pizza_size_max') ); ?>" class="pset-input" placeholder="500px">
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Min Size', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Min size (px or %). Include unit.', 'pizzalayer' ); ?></p>
						<input type="text" name="pizzalayer_setting_pizza_size_min"
						       value="<?php echo esc_attr( $g('pizzalayer_setting_pizza_size_min') ); ?>" class="pset-input" placeholder="200px">
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Border Width', 'pizzalayer' ); ?></label>
						<p class="pset-desc">Any valid CSS width, e.g. <code>2px</code>.</p>
						<input type="text" name="pizzalayer_setting_pizza_border"
						       value="<?php echo esc_attr( $g('pizzalayer_setting_pizza_border') ); ?>" class="pset-input" placeholder="2px">
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Border Color', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Color for the pizza border.', 'pizzalayer' ); ?></p>
						<div class="pset-color-wrap">
							<input type="color" id="pset-color-pizza_border_color" name="pizzalayer_setting_pizza_border_color"
							       value="<?php echo esc_attr( $g('pizzalayer_setting_pizza_border_color', '#d4a04c') ); ?>" class="pset-color">
							<button type="button" class="pset-color-revert"
							        data-default="#d4a04c"
							        data-target="pset-color-pizza_border_color"
							        title="Revert to default (#d4a04c)">
								<span class="dashicons dashicons-image-rotate"></span>
							</button>
							<span class="pset-color-default-swatch" style="background:#d4a04c;" title="Default: #d4a04c"></span>
						</div>
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Accent Color', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Global accent color used in templates.', 'pizzalayer' ); ?></p>
						<div class="pset-color-wrap">
							<input type="color" id="pset-color-global_color" name="pizzalayer_setting_global_color"
							       value="<?php echo esc_attr( $g('pizzalayer_setting_global_color', '#ff6b35') ); ?>" class="pset-color">
							<button type="button" class="pset-color-revert"
							        data-default="#ff6b35"
							        data-target="pset-color-global_color"
							        title="Revert to default (#ff6b35)">
								<span class="dashicons dashicons-image-rotate"></span>
							</button>
							<span class="pset-color-default-swatch" style="background:#ff6b35;" title="Default: #ff6b35"></span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- ══ Section: Pizza Shape ══════════════════════════════════ -->
		<div class="pset-card">
			<div class="pset-card__head pset-card__head--collapsible" data-pset-toggle="pizza-shape">
				<div>
					<h2><span class="dashicons dashicons-image-crop"></span> <?php esc_html_e( 'Pizza Shape', 'pizzalayer' ); ?></h2>
					<p>Controls the shape of the pizza preview in the builder. Can be overridden per-shortcode with <code>pizza_shape="..."</code>.</p>
				</div>
				<button type="button" class="pset-collapse-btn" aria-expanded="true" aria-controls="pset-body-pizza-shape"><span class="dashicons dashicons-arrow-up-alt2"></span></button>
			</div>
			<div class="pset-card__body" id="pset-body-pizza-shape">
				<div class="pset-grid">
					<div class="pset-field">
						<label><?php esc_html_e( 'Shape Preset', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Choose a shape for the pizza visualizer.', 'pizzalayer' ); ?></p>
						<select name="pizzalayer_setting_pizza_shape" class="pset-select" id="pset-pizza-shape">
							<?php foreach ( [
								'round'     => '⬤ Round (circle)',
								'square'    => '■ Square (rounded corners)',
								'rectangle' => '▬ Rectangle / Oval',
								'custom'    => '✦ Custom (set aspect ratio & radius below)',
							] as $v => $l ) : ?>
							<option value="<?php echo esc_attr( $v ); ?>"<?php selected( $g('pizzalayer_setting_pizza_shape', 'round'), $v ); ?>><?php echo esc_html( $l ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="pset-field pset-shape-custom">
						<label>Aspect Ratio <span class="pset-hint">(rectangle &amp; custom)</span></label>
						<p class="pset-desc">CSS <code>aspect-ratio</code> value, e.g. <code>4 / 3</code>, <code>16 / 9</code>, <code>3 / 4</code>.</p>
						<input type="text" name="pizzalayer_setting_pizza_aspect"
						       value="<?php echo esc_attr( $g('pizzalayer_setting_pizza_aspect', '4 / 3') ); ?>" class="pset-input" placeholder="4 / 3">
					</div>
					<div class="pset-field pset-shape-custom">
						<label>Border Radius <span class="pset-hint">(custom shape only)</span></label>
						<p class="pset-desc">CSS <code>border-radius</code>, e.g. <code>8px</code>, <code>50%</code>, <code>12px 40px</code>.</p>
						<input type="text" name="pizzalayer_setting_pizza_radius"
						       value="<?php echo esc_attr( $g('pizzalayer_setting_pizza_radius', '8px') ); ?>" class="pset-input" placeholder="8px">
					</div>
				</div>
				<!-- Live preview of shape -->
				<div style="margin-top:16px;">
					<p class="pset-desc" style="margin-bottom:6px;">Shape preview:</p>
					<div id="pset-shape-preview" style="
						width:80px; height:80px; background:linear-gradient(135deg,#ff8c42,#ff5722);
						border-radius:50%; transition:all 0.35s cubic-bezier(0.34,1.2,0.64,1);
						display:inline-block; vertical-align:middle; box-shadow:0 4px 16px rgba(0,0,0,0.25);
					"></div>
				</div>
			</div>
		</div>

		<!-- ══ Section: Layer Animation ══════════════════════════════ -->
		<div class="pset-card">
			<div class="pset-card__head pset-card__head--collapsible" data-pset-toggle="layer-animation">
				<div>
					<h2><span class="dashicons dashicons-controls-play"></span> <?php esc_html_e( 'Layer Animation', 'pizzalayer' ); ?></h2>
					<p>Animation played when a layer is added to the pizza preview. Can be overridden per-shortcode with <code>layer_anim="..."</code>.</p>
				</div>
				<button type="button" class="pset-collapse-btn" aria-expanded="true" aria-controls="pset-body-layer-animation"><span class="dashicons dashicons-arrow-up-alt2"></span></button>
			</div>
			<div class="pset-card__body" id="pset-body-layer-animation">
				<div class="pset-grid">
					<div class="pset-field">
						<label><?php esc_html_e( 'Animation Style', 'pizzalayer' ); ?></label>
						<select name="pizzalayer_setting_layer_anim" class="pset-select" id="pset-layer-anim">
							<?php foreach ( [
								'fade'     => '✦ Fade In (default)',
								'scale-in' => '⊕ Scale In (bouncy pop)',
								'slide-up' => '↑ Slide Up',
								'flip-in'  => '↻ Flip In (3-D rotate)',
								'drop-in'  => '↓ Drop In (fall from above)',
								'instant'  => '⚡ Instant (no animation)',
							] as $v => $l ) : ?>
							<option value="<?php echo esc_attr( $v ); ?>"<?php selected( $g('pizzalayer_setting_layer_anim', 'fade'), $v ); ?>><?php echo esc_html( $l ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="pset-field" id="pset-anim-speed-field">
						<label>Animation Speed <span class="pset-hint" id="pset-anim-speed-label">(<?php echo esc_html( $g('pizzalayer_setting_layer_anim_speed', '320') ); ?>ms)</span></label>
						<p class="pset-desc"><?php esc_html_e( 'Duration of the layer animation. Ignored when style is Instant.', 'pizzalayer' ); ?></p>
						<div class="pset-range-wrap">
							<input type="range" name="pizzalayer_setting_layer_anim_speed" id="pset-anim-speed"
							       min="80" max="800" step="20"
							       value="<?php echo esc_attr( $g('pizzalayer_setting_layer_anim_speed', '320') ); ?>"
							       class="pset-range"
							       oninput="document.getElementById('pset-anim-speed-val').textContent=this.value+'ms';document.getElementById('pset-anim-speed-label').textContent='('+this.value+'ms)'">
							<span class="pset-range__val" id="pset-anim-speed-val"><?php echo esc_html( $g('pizzalayer_setting_layer_anim_speed', '320') ); ?>ms</span>
						</div>
					</div>
					<div class="pset-field pset-anim-demo-wrap" style="display:flex;align-items:center;gap:12px;">
						<div>
							<label><?php esc_html_e( 'Preview', 'pizzalayer' ); ?></label>
							<p class="pset-desc"><?php esc_html_e( 'Click the button to preview the selected animation.', 'pizzalayer' ); ?></p>
							<button type="button" class="button" id="pset-anim-preview-btn">▶ Preview animation</button>
						</div>
						<div id="pset-anim-demo" style="
							width:56px; height:56px; background:linear-gradient(135deg,#ff8c42,#ff5722);
							border-radius:50%; box-shadow:0 4px 16px rgba(0,0,0,0.25); flex-shrink:0;
							opacity:1; transform:none;
						"></div>
					</div>
				</div>
			</div>
		</div>

		<!-- ══ Section: Crust Options ════════════════════════════════ -->
		<div class="pset-card">
			<div class="pset-card__head pset-card__head--collapsible" data-pset-toggle="crust-options">
				<div>
					<h2><span class="dashicons dashicons-tag"></span> <?php esc_html_e( 'Crust Options', 'pizzalayer' ); ?></h2>
					<p>Fine-tune how the crust layer is sized and spaced in the visualizer.</p>
				</div>
				<button type="button" class="pset-collapse-btn" aria-expanded="true" aria-controls="pset-body-crust-options"><span class="dashicons dashicons-arrow-up-alt2"></span></button>
			</div>
			<div class="pset-card__body" id="pset-body-crust-options">
				<p class="pset-desc" style="padding:0 22px 4px;margin:0;color:#646970;font-size:12px;">
					<span class="dashicons dashicons-info-outline" style="font-size:13px;vertical-align:middle;"></span>
					Crust shape and aspect ratio are controlled globally in <a href="<?php echo esc_url(admin_url('admin.php?page=pizzalayer-settings#pset-body-pizza-display')); ?>">Pizza Display settings</a>.
				</p>
				<div class="pset-grid">
					<div class="pset-field">
						<label>Crust Padding
							<span class="pset-hint" id="pset-spc-crust_padding-lbl">(<?php echo esc_html( (string)(int)preg_replace('/[^0-9]/','', $g('pizzalayer_setting_crust_padding','0')) ); ?>px)</span>
						</label>
						<p class="pset-desc"><?php esc_html_e( 'Inset padding applied to the crust layer image.', 'pizzalayer' ); ?></p>
						<div class="pset-range__wrap">
							<input type="range" id="pset-spc-crust_padding-range" min="0" max="80" step="1"
							       value="<?php echo esc_attr( (string)(int)preg_replace('/[^0-9]/','', $g('pizzalayer_setting_crust_padding','0')) ); ?>"
							       class="pset-range__slider pset-spacing-range"
							       data-target="pset-spc-crust_padding-text" data-label="pset-spc-crust_padding-lbl">
							<input type="text" id="pset-spc-crust_padding-text" name="pizzalayer_setting_crust_padding"
							       value="<?php echo esc_attr( $g('pizzalayer_setting_crust_padding') ?: '0px' ); ?>"
							       class="pset-range__val pset-spacing-text"
							       data-range="pset-spc-crust_padding-range" data-label="pset-spc-crust_padding-lbl"
							       placeholder="0px" style="width:72px;">
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- ══ Section: Sauce / Cheese Options ═══════════════════════ -->
		<div class="pset-card">
			<div class="pset-card__head pset-card__head--collapsible" data-pset-toggle="sauce-cheese">
				<div>
					<h2><span class="dashicons dashicons-admin-generic"></span> <?php esc_html_e( 'Sauce &amp; Cheese Options', 'pizzalayer' ); ?></h2>
					<p>Adjust padding and inset distances for the sauce and cheese layers.</p>
				</div>
				<button type="button" class="pset-collapse-btn" aria-expanded="true" aria-controls="pset-body-sauce-cheese"><span class="dashicons dashicons-arrow-up-alt2"></span></button>
			</div>
			<div class="pset-card__body" id="pset-body-sauce-cheese">
				<div class="pset-grid">
					<div class="pset-field">
						<label>Sauce Padding
							<span class="pset-hint" id="pset-spc-sauce_padding-lbl">(<?php echo esc_html( (string)(int)preg_replace('/[^0-9]/','', $g('pizzalayer_setting_sauce_padding','0')) ); ?>px)</span>
						</label>
						<p class="pset-desc"><?php esc_html_e( 'Padding between sauce and crust edge.', 'pizzalayer' ); ?></p>
						<div class="pset-range__wrap">
							<input type="range" id="pset-spc-sauce_padding-range" min="0" max="80" step="1"
							       value="<?php echo esc_attr( (string)(int)preg_replace('/[^0-9]/','', $g('pizzalayer_setting_sauce_padding','0')) ); ?>"
							       class="pset-range__slider pset-spacing-range"
							       data-target="pset-spc-sauce_padding-text" data-label="pset-spc-sauce_padding-lbl">
							<input type="text" id="pset-spc-sauce_padding-text" name="pizzalayer_setting_sauce_padding"
							       value="<?php echo esc_attr( $g('pizzalayer_setting_sauce_padding') ?: '0px' ); ?>"
							       class="pset-range__val pset-spacing-text"
							       data-range="pset-spc-sauce_padding-range" data-label="pset-spc-sauce_padding-lbl"
							       placeholder="0px" style="width:72px;">
						</div>
					</div>
					<div class="pset-field">
						<label>Cheese Distance from Edge
							<span class="pset-hint" id="pset-spc-cheese_dist-lbl">(<?php echo esc_html( (string)(int)preg_replace('/[^0-9]/','', $g('pizzalayer_cheese_setting_cheesedistance','0')) ); ?>px)</span>
						</label>
						<p class="pset-desc"><?php esc_html_e( 'How far inset the cheese layer is.', 'pizzalayer' ); ?></p>
						<div class="pset-range__wrap">
							<input type="range" id="pset-spc-cheese_dist-range" min="0" max="80" step="1"
							       value="<?php echo esc_attr( (string)(int)preg_replace('/[^0-9]/','', $g('pizzalayer_cheese_setting_cheesedistance','0')) ); ?>"
							       class="pset-range__slider pset-spacing-range"
							       data-target="pset-spc-cheese_dist-text" data-label="pset-spc-cheese_dist-lbl">
							<input type="text" id="pset-spc-cheese_dist-text" name="pizzalayer_cheese_setting_cheesedistance"
							       value="<?php echo esc_attr( $g('pizzalayer_cheese_setting_cheesedistance') ?: '0px' ); ?>"
							       class="pset-range__val pset-spacing-text"
							       data-range="pset-spc-cheese_dist-range" data-label="pset-spc-cheese_dist-lbl"
							       placeholder="0px" style="width:72px;">
						</div>
					</div>
					<div class="pset-field">
						<label>Cheese Padding
							<span class="pset-hint" id="pset-spc-cheese_padding-lbl">(<?php echo esc_html( (string)(int)preg_replace('/[^0-9]/','', $g('pizzalayer_setting_cheese_padding','0')) ); ?>px)</span>
						</label>
						<p class="pset-desc"><?php esc_html_e( 'Padding between cheese and toppings.', 'pizzalayer' ); ?></p>
						<div class="pset-range__wrap">
							<input type="range" id="pset-spc-cheese_padding-range" min="0" max="80" step="1"
							       value="<?php echo esc_attr( (string)(int)preg_replace('/[^0-9]/','', $g('pizzalayer_setting_cheese_padding','0')) ); ?>"
							       class="pset-range__slider pset-spacing-range"
							       data-target="pset-spc-cheese_padding-text" data-label="pset-spc-cheese_padding-lbl">
							<input type="text" id="pset-spc-cheese_padding-text" name="pizzalayer_setting_cheese_padding"
							       value="<?php echo esc_attr( $g('pizzalayer_setting_cheese_padding') ?: '0px' ); ?>"
							       class="pset-range__val pset-spacing-text"
							       data-range="pset-spc-cheese_padding-range" data-label="pset-spc-cheese_padding-lbl"
							       placeholder="0px" style="width:72px;">
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- ══ Section: UI Element Styles ════════════════════════════ -->
		<div class="pset-card">
			<div class="pset-card__head pset-card__head--collapsible" data-pset-toggle="ui-styles">
				<div>
					<h2><span class="dashicons dashicons-art"></span> <?php esc_html_e( 'UI Element Styles', 'pizzalayer' ); ?></h2>
					<p>These control the visual style of selection cards in the builder.</p>
				</div>
				<button type="button" class="pset-collapse-btn" aria-expanded="true" aria-controls="pset-body-ui-styles"><span class="dashicons dashicons-arrow-up-alt2"></span></button>
			</div>
			<div class="pset-card__body" id="pset-body-ui-styles">
				<div class="pset-grid">
					<div class="pset-field">
						<label>Layer Choice Style <span class="pset-hint">(crust/sauce/cheese etc.)</span></label>
						<select name="pizzalayer_setting_element_style_layers" class="pset-select">
							<?php foreach ( [ 'default'=>'Default','thumblabel'=>'Thumb with Label','thumbcorner'=>'Thumb Corner','thumbcircle'=>'Thumb Circle','labeloverthumb'=>'Label over Thumb','thumbrow'=>'Thumb Row','textrow'=>'Text Row','icontext'=>'Icon and Text','text'=>'Text','appsidetrigger'=>'App Row with Side Triggers' ] as $v => $l ) : ?>
							<option value="<?php echo esc_attr( $v ); ?>"<?php selected( $g('pizzalayer_setting_element_style_layers'), $v ); ?>><?php echo esc_html( $l ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Toppings Style', 'pizzalayer' ); ?></label>
						<select name="pizzalayer_setting_element_style_toppings" class="pset-select">
							<?php foreach ( [ 'default'=>'Default','controlbox'=>'Control Box','thumbcorner'=>'Thumb Corner','bgtoggle'=>'Background Toggle','modern'=>'Modern Offset','cornertag'=>'Corner Tag','appadd'=>'App Add' ] as $v => $l ) : ?>
							<option value="<?php echo esc_attr( $v ); ?>"<?php selected( $g('pizzalayer_setting_element_style_toppings'), $v ); ?>><?php echo esc_html( $l ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Topping Choice Menu Style', 'pizzalayer' ); ?></label>
						<select name="pizzalayer_setting_element_style_topping_choice_menu" class="pset-select">
							<?php foreach ( [ 'default'=>'Default','minimal'=>'Minimal','iconwfraction'=>'Icon (with fraction)','iconnofraction'=>'Icon (no fraction)' ] as $v => $l ) : ?>
							<option value="<?php echo esc_attr( $v ); ?>"<?php selected( $g('pizzalayer_setting_element_style_topping_choice_menu'), $v ); ?>><?php echo esc_html( $l ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Show Thumbnails', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Show thumbnail images in menu UI.', 'pizzalayer' ); ?></p>
						<label class="pset-toggle">
							<input type="hidden" name="pizzalayer_setting_show_thumbnails" value="no">
							<input type="checkbox" name="pizzalayer_setting_show_thumbnails" value="yes"<?php checked( $g('pizzalayer_setting_show_thumbnails', 'yes'), 'yes' ); ?>>
							<span class="pset-toggle__track"><span class="pset-toggle__thumb"></span></span>
							<span class="pset-toggle__label">Show thumbnails in builder</span>
						</label>
					</div>
				</div>
			</div>
		</div>

		<!-- ══ Section: Branding ════════════════════════════════════ -->
		<div class="pset-card">
			<div class="pset-card__head pset-card__head--collapsible" data-pset-toggle="branding">
				<div>
					<h2><span class="dashicons dashicons-admin-customizer"></span> <?php esc_html_e( 'Branding', 'pizzalayer' ); ?></h2>
					<p>Logo, colours, tagline, and custom copy shown in the builder's branded areas.</p>
				</div>
				<button type="button" class="pset-collapse-btn" aria-expanded="true" aria-controls="pset-body-branding"><span class="dashicons dashicons-arrow-up-alt2"></span></button>
			</div>
			<div class="pset-card__body" id="pset-body-branding">
				<div class="pset-grid pset-grid--wide">

					<!-- Logo -->
					<div class="pset-field pset-field--full">
						<label>Logo Image <span class="pset-hint">For templates that show a header logo</span></label>
						<?php $logoVal = $g('pizzalayer_setting_branding_altlogo'); ?>
						<div class="pset-logo-picker" id="pset-logo-picker">
							<div class="pset-logo-picker__preview" id="pset-logo-preview">
								<?php if ( $logoVal ) : ?>
								<img src="<?php echo esc_url( $logoVal ); ?>" alt="Current logo" style="max-height:60px;max-width:200px;border-radius:4px;border:1px solid #e0e3e7;">
								<?php else : ?>
								<span class="pset-logo-picker__placeholder"><span class="dashicons dashicons-format-image"></span> No logo selected</span>
								<?php endif; ?>
							</div>
							<div class="pset-logo-picker__actions">
								<button type="button" class="button" id="pset-logo-select-btn">
									<span class="dashicons dashicons-upload"></span>
									<?php echo $logoVal ? esc_html__( 'Change Logo', 'pizzalayer' ) : esc_html__( 'Select / Upload Logo', 'pizzalayer' ); ?>
								</button>
								<?php if ( $logoVal ) : ?>
								<button type="button" class="button pset-logo-remove-btn" id="pset-logo-remove-btn" style="color:#b32d2e;border-color:#b32d2e;">
									<span class="dashicons dashicons-trash"></span> <?php esc_html_e( 'Remove', 'pizzalayer' ); ?>
								</button>
								<?php endif; ?>
							</div>
							<input type="hidden" name="pizzalayer_setting_branding_altlogo" id="pset-logo-url-input"
							       value="<?php echo esc_attr( $logoVal ); ?>">
							<?php if ( $logoVal ) : ?>
							<p class="pset-desc" style="margin-top:4px;word-break:break-all;">
								<a href="<?php echo esc_url( $logoVal ); ?>" target="_blank" rel="noopener"><?php echo esc_html( basename( $logoVal ) ); ?></a>
							</p>
							<?php endif; ?>
						</div>
					</div>

					<div class="pset-field">
						<label><?php esc_html_e( 'Logo Display Width', 'pizzalayer' ); ?></label>
						<p class="pset-desc">CSS width for the logo image, e.g. <code>120px</code> or <code>auto</code>.</p>
						<input type="text" name="pizzalayer_setting_branding_logo_width"
						       value="<?php echo esc_attr( $g('pizzalayer_setting_branding_logo_width') ); ?>" class="pset-input" placeholder="120px">
					</div>

					<div class="pset-field">
						<label><?php esc_html_e( 'Logo Display Height', 'pizzalayer' ); ?></label>
						<p class="pset-desc">CSS height for the logo image, e.g. <code>40px</code> or <code>auto</code>.</p>
						<input type="text" name="pizzalayer_setting_branding_logo_height"
						       value="<?php echo esc_attr( $g('pizzalayer_setting_branding_logo_height') ); ?>" class="pset-input" placeholder="40px">
					</div>

					<div class="pset-field">
						<label><?php esc_html_e( 'Logo Alt Text', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Accessibility alt text for the logo image.', 'pizzalayer' ); ?></p>
						<input type="text" name="pizzalayer_setting_branding_logo_alt"
						       value="<?php echo esc_attr( $g('pizzalayer_setting_branding_logo_alt') ); ?>" class="pset-input" placeholder="Your restaurant name">
					</div>

					<!-- Brand tagline -->
					<div class="pset-field pset-field--full">
						<label><?php esc_html_e( 'Brand Tagline', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Short slogan shown beneath the logo in templates that support it (e.g. "Build your perfect pizza").', 'pizzalayer' ); ?></p>
						<input type="text" name="pizzalayer_setting_branding_tagline"
						       value="<?php echo esc_attr( $g('pizzalayer_setting_branding_tagline') ); ?>" class="pset-input pset-input--wide" placeholder="Build your perfect pizza">
					</div>

					<!-- Brand colours -->
					<div class="pset-field">
						<label><?php esc_html_e( 'Brand Primary Colour', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Main brand colour — used for buttons, accents, and active states in supported templates.', 'pizzalayer' ); ?></p>
						<div class="pset-color-wrap">
							<input type="color" id="pset-color-branding_primary" name="pizzalayer_setting_branding_primary_color"
							       value="<?php echo esc_attr( $g('pizzalayer_setting_branding_primary_color', '#ff6b35') ); ?>" class="pset-color">
							<button type="button" class="pset-color-revert" data-default="#ff6b35" data-target="pset-color-branding_primary" title="Revert to default">
								<span class="dashicons dashicons-image-rotate"></span>
							</button>
							<span class="pset-color-default-swatch" style="background:#ff6b35;" title="Default: #ff6b35"></span>
						</div>
					</div>

					<div class="pset-field">
						<label><?php esc_html_e( 'Brand Secondary Colour', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Supporting brand colour — used for secondary buttons and highlights.', 'pizzalayer' ); ?></p>
						<div class="pset-color-wrap">
							<input type="color" id="pset-color-branding_secondary" name="pizzalayer_setting_branding_secondary_color"
							       value="<?php echo esc_attr( $g('pizzalayer_setting_branding_secondary_color', '#2d3748') ); ?>" class="pset-color">
							<button type="button" class="pset-color-revert" data-default="#2d3748" data-target="pset-color-branding_secondary" title="Revert to default">
								<span class="dashicons dashicons-image-rotate"></span>
							</button>
							<span class="pset-color-default-swatch" style="background:#2d3748;" title="Default: #2d3748"></span>
						</div>
					</div>

					<!-- Builder copy -->
					<div class="pset-field pset-field--full">
						<label><?php esc_html_e( 'Content Above Menu Icons', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Intro text or custom HTML shown above the builder tab icons. Use Visual mode for rich text, Text tab for raw HTML.', 'pizzalayer' ); ?></p>
						<?php wp_editor( $g('pizzalayer_setting_branding_menu_title'), 'pzl_editor_menu_title', [ 'textarea_name' => 'pizzalayer_setting_branding_menu_title', 'media_buttons' => false, 'teeny' => true, 'textarea_rows' => 4, 'tinymce' => [ 'toolbar1' => 'bold,italic,underline,link,unlink,removeformat,code' ], 'quicktags' => [ 'buttons' => 'strong,em,link,code,close' ] ] ); ?>
					</div>

					<div class="pset-field pset-field--full">
						<label><?php esc_html_e( 'Header Custom Content', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Custom HTML for the branding area in the builder header (logo area, above the tabs). Use Text tab for raw HTML with image tags etc.', 'pizzalayer' ); ?></p>
						<?php wp_editor( $g('pizzalayer_setting_branding_header_custom_content'), 'pzl_editor_header_content', [ 'textarea_name' => 'pizzalayer_setting_branding_header_custom_content', 'media_buttons' => true, 'teeny' => true, 'textarea_rows' => 4, 'tinymce' => [ 'toolbar1' => 'bold,italic,underline,link,unlink,image,removeformat,code' ], 'quicktags' => [ 'buttons' => 'strong,em,link,img,code,close' ] ] ); ?>
					</div>

					<div class="pset-field pset-field--full">
						<label><?php esc_html_e( 'Builder Footer Text', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Optional text or HTML shown in the footer of the builder (e.g. allergen notice, T&amp;Cs link).', 'pizzalayer' ); ?></p>
						<?php wp_editor( $g('pizzalayer_setting_branding_footer_text'), 'pzl_editor_footer_text', [ 'textarea_name' => 'pizzalayer_setting_branding_footer_text', 'media_buttons' => false, 'teeny' => true, 'textarea_rows' => 3, 'tinymce' => [ 'toolbar1' => 'bold,italic,underline,link,unlink,removeformat,code' ], 'quicktags' => [ 'buttons' => 'strong,em,link,code,close' ] ] ); ?>
					</div>

				</div>
			</div>
		</div>

		<!-- ══ Section: Plugin Settings ═════════════════════════════ -->
		<div class="pset-card">
			<div class="pset-card__head pset-card__head--collapsible" data-pset-toggle="plugin-settings">
				<div>
					<h2><span class="dashicons dashicons-info-outline"></span> <?php esc_html_e( 'Plugin Settings', 'pizzalayer' ); ?></h2>
					<p>Announcement bar text and builder help content shown to customers.</p>
				</div>
				<button type="button" class="pset-collapse-btn" aria-expanded="true" aria-controls="pset-body-plugin-settings"><span class="dashicons dashicons-arrow-up-alt2"></span></button>
			</div>
			<div class="pset-card__body" id="pset-body-plugin-settings">
				<div class="pset-grid pset-grid--wide">
					<div class="pset-field pset-field--full">
						<label><?php esc_html_e( 'Demo / Announcement Bar', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'If set, this message appears as an announcement bar above all pages using PizzaLayer. Leave empty to disable.', 'pizzalayer' ); ?></p>
						<textarea name="pizzalayer_setting_settings_demonotice" class="pset-textarea" rows="2" placeholder="e.g. Now open for online ordering! Order before 8pm for same-day delivery."><?php echo esc_textarea( $g('pizzalayer_setting_settings_demonotice') ); ?></textarea>
					</div>
					<div class="pset-field pset-field--full">
						<label><?php esc_html_e( 'Help Screen Content', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Content shown in the builder\'s help modal/tab when customers click the help icon.', 'pizzalayer' ); ?></p>
						<textarea name="pizzalayer_setting_global_help_content" class="pset-textarea" rows="4"><?php echo esc_textarea( $g('pizzalayer_setting_global_help_content') ); ?></textarea>
					</div>
				</div>
			</div>
		</div>


		<!-- ══ Section: Builder Layout & Behaviour ════════════════════════════ -->
		<div class="pset-card">
			<div class="pset-card__head pset-card__head--collapsible" data-pset-toggle="builder-layout">
				<div>
					<h2><span class="dashicons dashicons-layout"></span> <?php esc_html_e( 'Builder Layout &amp; Behaviour', 'pizzalayer' ); ?></h2>
					<p>Control how the pizza builder is laid out and how customers interact with it.</p>
				</div>
				<button type="button" class="pset-collapse-btn" aria-expanded="true" aria-controls="pset-body-builder-layout"><span class="dashicons dashicons-arrow-up-alt2"></span></button>
			</div>
			<div class="pset-card__body" id="pset-body-builder-layout">
				<div class="pset-grid">
					<div class="pset-field">
						<label><?php esc_html_e( 'Builder Layout Mode', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'How the builder panels are arranged on screen.', 'pizzalayer' ); ?></p>
						<?php $v = (string) get_option('pizzalayer_setting_layout_mode','stacked'); ?>
						<select name="pizzalayer_setting_layout_mode" class="pset-select">
							<?php foreach(['stacked'=>'Stacked (vertical)','split-ltr'=>'Side-by-side (pizza left, menu right)','split-rtl'=>'Side-by-side (menu left, pizza right)','floating'=>'Floating pizza overlay','fullscreen'=>'Full-screen immersive'] as $ov=>$ol):?>
							<option value="<?php echo esc_attr($ov);?>"<?php selected($v,$ov);?>><?php echo esc_html($ol);?></option>
							<?php endforeach;?>
						</select>
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Builder Width', 'pizzalayer' ); ?></label>
						<p class="pset-desc">Maximum width of the builder container, e.g. <code>900px</code> or <code>100%</code>.</p>
						<input type="text" name="pizzalayer_setting_layout_builder_width"
						       value="<?php echo esc_attr((string)get_option('pizzalayer_setting_layout_builder_width','')); ?>"
						       class="pset-input" placeholder="100%">
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Mobile Breakpoint (px)', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Screen width at which the mobile layout activates.', 'pizzalayer' ); ?></p>
						<input type="number" name="pizzalayer_setting_layout_mobile_bp"
						       value="<?php echo esc_attr((string)get_option('pizzalayer_setting_layout_mobile_bp','')); ?>"
						       class="pset-input" placeholder="768">
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Mobile Layout', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'How the builder stacks on small screens.', 'pizzalayer' ); ?></p>
						<?php $v = (string) get_option('pizzalayer_setting_layout_mobile','pizza-top'); ?>
						<select name="pizzalayer_setting_layout_mobile" class="pset-select">
							<?php foreach(['pizza-top'=>'Pizza on top, menu below','menu-top'=>'Menu on top, pizza below','menu-only'=>'Hidden pizza (menu only)','sticky-pizza'=>'Sticky pizza (fixed top)'] as $ov=>$ol):?>
							<option value="<?php echo esc_attr($ov);?>"<?php selected($v,$ov);?>><?php echo esc_html($ol);?></option>
							<?php endforeach;?>
						</select>
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Step-by-Step Mode', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Guide customers through one layer category at a time.', 'pizzalayer' ); ?></p>
						<label class="pset-toggle">
							<input type="hidden" name="pizzalayer_setting_layout_step_by_step" value="no">
							<input type="checkbox" name="pizzalayer_setting_layout_step_by_step" value="yes"<?php checked((string)get_option('pizzalayer_setting_layout_step_by_step','no'),'yes');?>>
							<span class="pset-toggle__track"><span class="pset-toggle__thumb"></span></span>
							<span class="pset-toggle__label">Enable guided step mode</span>
						</label>
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Auto-Advance Steps', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Automatically move to the next step after a selection.', 'pizzalayer' ); ?></p>
						<label class="pset-toggle">
							<input type="hidden" name="pizzalayer_setting_layout_auto_advance" value="no">
							<input type="checkbox" name="pizzalayer_setting_layout_auto_advance" value="yes"<?php checked((string)get_option('pizzalayer_setting_layout_auto_advance','no'),'yes');?>>
							<span class="pset-toggle__track"><span class="pset-toggle__thumb"></span></span>
							<span class="pset-toggle__label">Auto-advance on selection</span>
						</label>
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Tab Order', 'pizzalayer' ); ?></label>
						<p class="pset-desc">Comma-separated list of tabs in display order. E.g. <code>crust, sauce, cheese, toppings, drizzle, slicing</code>.</p>
						<input type="text" name="pizzalayer_setting_layout_tab_order"
						       value="<?php echo esc_attr((string)get_option('pizzalayer_setting_layout_tab_order','')); ?>"
						       class="pset-input" placeholder="crust, sauce, cheese, toppings, drizzle, slicing">
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Hide Empty Categories', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Don\'t show tabs for categories with no published items.', 'pizzalayer' ); ?></p>
						<label class="pset-toggle">
							<input type="hidden" name="pizzalayer_setting_layout_hide_empty" value="no">
							<input type="checkbox" name="pizzalayer_setting_layout_hide_empty" value="yes"<?php checked((string)get_option('pizzalayer_setting_layout_hide_empty','no'),'yes');?>>
							<span class="pset-toggle__track"><span class="pset-toggle__thumb"></span></span>
							<span class="pset-toggle__label">Hide tabs with no content</span>
						</label>
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Keyboard Navigation', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Allow customers to navigate the builder with arrow keys.', 'pizzalayer' ); ?></p>
						<label class="pset-toggle">
							<input type="hidden" name="pizzalayer_setting_layout_keyboard_nav" value="no">
							<input type="checkbox" name="pizzalayer_setting_layout_keyboard_nav" value="yes"<?php checked((string)get_option('pizzalayer_setting_layout_keyboard_nav','no'),'yes');?>>
							<span class="pset-toggle__track"><span class="pset-toggle__thumb"></span></span>
							<span class="pset-toggle__label">Enable keyboard shortcuts</span>
						</label>
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Sticky Builder Header', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Keep the pizza preview pinned while scrolling the menu.', 'pizzalayer' ); ?></p>
						<label class="pset-toggle">
							<input type="hidden" name="pizzalayer_setting_layout_sticky_header" value="no">
							<input type="checkbox" name="pizzalayer_setting_layout_sticky_header" value="yes"<?php checked((string)get_option('pizzalayer_setting_layout_sticky_header','no'),'yes');?>>
							<span class="pset-toggle__track"><span class="pset-toggle__thumb"></span></span>
							<span class="pset-toggle__label">Enable sticky header</span>
						</label>
					</div>
				</div>
			</div>
		</div>

		<!-- ══ Section: Pricing & Cart ════════════════════════════════════════ -->
		<div class="pset-card">
			<div class="pset-card__head pset-card__head--collapsible" data-pset-toggle="pricing-cart">
				<div>
					<h2><span class="dashicons dashicons-cart"></span> <?php esc_html_e( 'Pricing &amp; Cart', 'pizzalayer' ); ?></h2>
					<p>How prices are calculated, displayed, and passed to the cart.</p>
				</div>
				<button type="button" class="pset-collapse-btn" aria-expanded="true" aria-controls="pset-body-pricing-cart"><span class="dashicons dashicons-arrow-up-alt2"></span></button>
			</div>
			<div class="pset-card__body" id="pset-body-pricing-cart">
				<div class="pset-grid">
					<div class="pset-field">
						<label><?php esc_html_e( 'Price Display Mode', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'How prices are shown to customers in the builder UI.', 'pizzalayer' ); ?></p>
						<?php $v = (string) get_option('pizzalayer_setting_price_display_mode','total'); ?>
						<select name="pizzalayer_setting_price_display_mode" class="pset-select">
							<?php foreach(['total'=>'Show total only','per-item'=>'Show per-item prices','per-item-total'=>'Show per-item + running total','hidden'=>'Hide all prices'] as $ov=>$ol):?>
							<option value="<?php echo esc_attr($ov);?>"<?php selected($v,$ov);?>><?php echo esc_html($ol);?></option>
							<?php endforeach;?>
						</select>
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Base Price', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Default starting price before any layer selections.', 'pizzalayer' ); ?></p>
						<input type="text" name="pizzalayer_setting_price_base"
						       value="<?php echo esc_attr((string)get_option('pizzalayer_setting_price_base','')); ?>"
						       class="pset-input" placeholder="0.00">
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Currency Symbol Position', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Where the currency symbol appears relative to the amount.', 'pizzalayer' ); ?></p>
						<?php $v = (string) get_option('pizzalayer_setting_price_currency_pos','before'); ?>
						<select name="pizzalayer_setting_price_currency_pos" class="pset-select">
							<?php foreach(['before'=>'Before (e.g. $10.00)','after'=>'After (e.g. 10.00€)'] as $ov=>$ol):?>
							<option value="<?php echo esc_attr($ov);?>"<?php selected($v,$ov);?>><?php echo esc_html($ol);?></option>
							<?php endforeach;?>
						</select>
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Price Update Animation', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Visual effect when the running total changes.', 'pizzalayer' ); ?></p>
						<?php $v = (string) get_option('pizzalayer_setting_price_update_anim','fade'); ?>
						<select name="pizzalayer_setting_price_update_anim" class="pset-select">
							<?php foreach(['fade'=>'Fade','countup'=>'Count-up','flash'=>'Flash highlight','none'=>'None'] as $ov=>$ol):?>
							<option value="<?php echo esc_attr($ov);?>"<?php selected($v,$ov);?>><?php echo esc_html($ol);?></option>
							<?php endforeach;?>
						</select>
					</div>
					<?php if ( ! class_exists( 'PizzaLayerPro\\Pro\\Plugin' ) ) : ?>
					<div class="pset-field pset-field--full">
						<div class="pset-pro-notice">
							<span class="dashicons dashicons-cart"></span>
							<div>
								<strong><?php esc_html_e( 'WooCommerce Cart Settings', 'pizzalayer' ); ?></strong>
								<p><?php esc_html_e( 'Cart button visibility, button text, require crust/sauce, minimum order, and tax display are managed in PizzaLayerPro → Pro Settings.', 'pizzalayer' ); ?>
								<a href="https://pizzalayer.com/pro" target="_blank" rel="noopener"><?php esc_html_e( 'Learn more →', 'pizzalayer' ); ?></a></p>
							</div>
						</div>
					</div>
					<?php else : ?>
					<div class="pset-field pset-field--full">
						<div class="pset-pro-notice pset-pro-notice--active">
							<span class="dashicons dashicons-yes-alt"></span>
							<div>
								<strong><?php esc_html_e( 'WooCommerce Cart Settings', 'pizzalayer' ); ?></strong>
								<p><?php esc_html_e( 'Cart integration settings are managed in ', 'pizzalayer' ); ?>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=pizzalayerpro-settings' ) ); ?>"><?php esc_html_e( 'PizzaLayerPro → Pro Settings', 'pizzalayer' ); ?></a>.</p>
							</div>
						</div>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<!-- ══ Section: Typography ════════════════════════════════════════════ -->
		<div class="pset-card">
			<div class="pset-card__head pset-card__head--collapsible" data-pset-toggle="typography">
				<div>
					<h2><span class="dashicons dashicons-editor-textcolor"></span> <?php esc_html_e( 'Typography', 'pizzalayer' ); ?></h2>
					<p>Font families, sizes, and weights used throughout the builder UI.</p>
				</div>
				<button type="button" class="pset-collapse-btn" aria-expanded="true" aria-controls="pset-body-typography"><span class="dashicons dashicons-arrow-up-alt2"></span></button>
			</div>
			<div class="pset-card__body" id="pset-body-typography">
				<div class="pset-grid">
					<div class="pset-field">
						<label><?php esc_html_e( 'Primary Font Family', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Font used for headings and labels in the builder.', 'pizzalayer' ); ?></p>
						<?php $v = (string) get_option('pizzalayer_setting_typo_font_family','inherit'); ?>
						<select name="pizzalayer_setting_typo_font_family" class="pset-select">
							<?php foreach(['inherit'=>'Inherit from theme','system'=>'System UI (sans-serif)','georgia'=>'Georgia (serif)','courier'=>'Courier New (monospace)','google'=>'Custom Google Font (see below)'] as $ov=>$ol):?>
							<option value="<?php echo esc_attr($ov);?>"<?php selected($v,$ov);?>><?php echo esc_html($ol);?></option>
							<?php endforeach;?>
						</select>
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Custom Google Font Name', 'pizzalayer' ); ?></label>
						<p class="pset-desc">e.g. <code>Roboto</code>, <code>Lato</code>, <code>Playfair Display</code></p>
						<input type="text" name="pizzalayer_setting_typo_google_font"
						       value="<?php echo esc_attr((string)get_option('pizzalayer_setting_typo_google_font','')); ?>"
						       class="pset-input" placeholder="Roboto">
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Base Font Size', 'pizzalayer' ); ?></label>
						<p class="pset-desc">Root font size for the builder, e.g. <code>15px</code> or <code>1rem</code>.</p>
						<input type="text" name="pizzalayer_setting_typo_base_size"
						       value="<?php echo esc_attr((string)get_option('pizzalayer_setting_typo_base_size','')); ?>"
						       class="pset-input" placeholder="15px">
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Heading Font Weight', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Weight applied to section headings inside the builder.', 'pizzalayer' ); ?></p>
						<?php $v = (string) get_option('pizzalayer_setting_typo_heading_fw','700'); ?>
						<select name="pizzalayer_setting_typo_heading_fw" class="pset-select">
							<?php foreach(['400'=>'400 — Regular','500'=>'500 — Medium','600'=>'600 — Semi-Bold','700'=>'700 — Bold','800'=>'800 — Extra Bold','900'=>'900 — Black'] as $ov=>$ol):?>
							<option value="<?php echo esc_attr($ov);?>"<?php selected($v,$ov);?>><?php echo esc_html($ol);?></option>
							<?php endforeach;?>
						</select>
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Label Font Size', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Size of item name labels in the menu grid.', 'pizzalayer' ); ?></p>
						<input type="text" name="pizzalayer_setting_typo_label_size"
						       value="<?php echo esc_attr((string)get_option('pizzalayer_setting_typo_label_size','')); ?>"
						       class="pset-input" placeholder="13px">
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Price Font Size', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Size of price figures in the builder.', 'pizzalayer' ); ?></p>
						<input type="text" name="pizzalayer_setting_typo_price_size"
						       value="<?php echo esc_attr((string)get_option('pizzalayer_setting_typo_price_size','')); ?>"
						       class="pset-input" placeholder="14px">
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Button Font Weight', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Weight for text inside action buttons.', 'pizzalayer' ); ?></p>
						<?php $v = (string) get_option('pizzalayer_setting_typo_btn_fw','600'); ?>
						<select name="pizzalayer_setting_typo_btn_fw" class="pset-select">
							<?php foreach(['400'=>'400 — Regular','600'=>'600 — Semi-Bold','700'=>'700 — Bold'] as $ov=>$ol):?>
							<option value="<?php echo esc_attr($ov);?>"<?php selected($v,$ov);?>><?php echo esc_html($ol);?></option>
							<?php endforeach;?>
						</select>
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Letter Spacing (headings)', 'pizzalayer' ); ?></label>
						<p class="pset-desc">CSS letter-spacing for section headings, e.g. <code>0.05em</code>.</p>
						<input type="text" name="pizzalayer_setting_typo_letter_sp"
						       value="<?php echo esc_attr((string)get_option('pizzalayer_setting_typo_letter_sp','')); ?>"
						       class="pset-input" placeholder="0">
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Text Transform (labels)', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Case transformation for item name labels.', 'pizzalayer' ); ?></p>
						<?php $v = (string) get_option('pizzalayer_setting_typo_text_transform','none'); ?>
						<select name="pizzalayer_setting_typo_text_transform" class="pset-select">
							<?php foreach(['none'=>'None','uppercase'=>'Uppercase','lowercase'=>'Lowercase','capitalize'=>'Capitalize'] as $ov=>$ol):?>
							<option value="<?php echo esc_attr($ov);?>"<?php selected($v,$ov);?>><?php echo esc_html($ol);?></option>
							<?php endforeach;?>
						</select>
					</div>
				</div>
			</div>
		</div>

		<!-- ══ Section: Global Colour Palette ════════════════════════════════ -->
		<div class="pset-card">
			<div class="pset-card__head pset-card__head--collapsible" data-pset-toggle="colour-palette">
				<div>
					<h2><span class="dashicons dashicons-color-picker"></span> <?php esc_html_e( 'Global Colour Palette', 'pizzalayer' ); ?></h2>
					<p>Fine-grained color control for every UI surface in the builder.</p>
				</div>
				<button type="button" class="pset-collapse-btn" aria-expanded="true" aria-controls="pset-body-colour-palette"><span class="dashicons dashicons-arrow-up-alt2"></span></button>
			</div>
			<div class="pset-card__body" id="pset-body-colour-palette">

				<!-- Preset colour schemes -->
				<div class="pset-palette-presets">
					<span class="pset-palette-presets__label">Quick Presets:</span>
					<div class="pset-palette-preset-chips" id="pset-palette-chips">
						<?php foreach ( $this->get_palette_presets() as $preset ) :
							$safe = esc_attr( wp_json_encode( $preset['values'] ) );
						?>
						<button type="button" class="pset-palette-chip"
						        data-palette="<?php echo $safe; ?>"
						        data-name="<?php echo esc_attr( $preset['name'] ); ?>"
						        title="Apply: <?php echo esc_attr( $preset['name'] ); ?>">
							<span class="pset-palette-chip__swatches">
								<?php foreach ( array_slice( $preset['preview'], 0, 4 ) as $c ) : ?>
								<span class="pset-palette-chip__dot" style="background:<?php echo esc_attr($c); ?>;"></span>
								<?php endforeach; ?>
							</span>
							<span class="pset-palette-chip__name"><?php echo esc_html( $preset['name'] ); ?></span>
						</button>
						<?php endforeach; ?>
					</div>
				</div>

				<!-- Palette preset confirmation modal -->
				<div id="pset-palette-modal" class="pset-modal" role="dialog" aria-modal="true" aria-label="Apply colour preset" style="display:none;">
					<div class="pset-modal__backdrop"></div>
					<div class="pset-modal__box" style="max-width:400px;">
						<div class="pset-modal__head">
							<h3 class="pset-modal__title">Apply preset: <span id="pset-palette-modal-name"></span></h3>
							<button type="button" class="pset-modal__close" id="pset-palette-modal-cancel" aria-label="<?php esc_attr_e( 'Close', 'pizzalayer' ); ?>">&times;</button>
						</div>
						<div style="padding:16px 20px;">
							<p style="font-size:13px;color:#3c434a;margin:0 0 12px;"><?php esc_html_e( 'This will replace all 15 colour palette values below with the selected preset. Your current colours will be overwritten.', 'pizzalayer' ); ?></p>
							<div id="pset-palette-modal-swatches" style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:16px;"></div>
							<div style="display:flex;gap:8px;justify-content:flex-end;">
								<button type="button" class="button" id="pset-palette-modal-cancel2"><?php esc_html_e( 'Cancel', 'pizzalayer' ); ?></button>
								<button type="button" class="button button-primary" id="pset-palette-modal-apply"><?php esc_html_e( 'Apply Preset', 'pizzalayer' ); ?></button>
							</div>
						</div>
					</div>
				</div>

				<div class="pset-grid">
					<?php
					$color_palette_fields = [
						['pizzalayer_setting_color_bg',          __( 'Builder Background', 'pizzalayer' ),       '#f5f5f5'],
						['pizzalayer_setting_color_menu_bg',     __( 'Menu Panel Background', 'pizzalayer' ),    '#ffffff'],
						['pizzalayer_setting_color_card_bg',     __( 'Card Background', 'pizzalayer' ),          '#ffffff'],
						['pizzalayer_setting_color_card_border', __( 'Card Border Color', 'pizzalayer' ),        '#e0e0e0'],
						['pizzalayer_setting_color_selected',    __( 'Selected Card Highlight', 'pizzalayer' ),  '#ff6b35'],
						['pizzalayer_setting_color_tab_bg',      __( 'Tab Bar Background', 'pizzalayer' ),       '#ffffff'],
						['pizzalayer_setting_color_tab_active',  __( 'Active Tab Color', 'pizzalayer' ),         '#ff6b35'],
						['pizzalayer_setting_color_tab_text',    __( 'Tab Text Color', 'pizzalayer' ),           '#333333'],
						['pizzalayer_setting_color_btn_bg',      __( 'Primary Button Background', 'pizzalayer' ),'#ff6b35'],
						['pizzalayer_setting_color_btn_text',    __( 'Primary Button Text', 'pizzalayer' ),      '#ffffff'],
						['pizzalayer_setting_color_btn2_bg',     __( 'Secondary Button Background', 'pizzalayer' ),'#f0f0f0'],
						['pizzalayer_setting_color_body_text',   __( 'Body Text Color', 'pizzalayer' ),          '#222222'],
						['pizzalayer_setting_color_muted_text',  __( 'Muted / Helper Text', 'pizzalayer' ),      '#777777'],
						['pizzalayer_setting_color_error',       __( 'Error / Warning Color', 'pizzalayer' ),    '#d32f2f'],
						['pizzalayer_setting_color_success',     __( 'Success / Confirmed Color', 'pizzalayer' ),'#388e3c'],
					];
					foreach ( $color_palette_fields as [$key, $label, $default] ) :
						$val = (string) get_option( $key, '' );
					?>
					<div class="pset-field">
						<label><?php echo esc_html( $label ); ?></label>
						<div class="pset-color-wrap">
							<input type="color" id="pset-color-<?php echo esc_attr( $key ); ?>"
							       name="<?php echo esc_attr( $key ); ?>"
							       value="<?php echo esc_attr( $val ?: $default ); ?>"
							       class="pset-color pset-palette-color"
							       data-palette-key="<?php echo esc_attr( $key ); ?>">
							<button type="button" class="pset-color-revert"
							        data-default="<?php echo esc_attr( $default ); ?>"
							        data-target="pset-color-<?php echo esc_attr( $key ); ?>"
							        title="Revert to default (<?php echo esc_attr( $default ); ?>)">
								<span class="dashicons dashicons-image-rotate"></span>
							</button>
							<span class="pset-color-default-swatch"
							      style="background:<?php echo esc_attr( $default ); ?>;"
							      title="Default: <?php echo esc_attr( $default ); ?>"></span>
						</div>
					</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>

		<!-- ══ Section: Spacing & Borders ════════════════════════════════════ -->
		<div class="pset-card">
			<div class="pset-card__head pset-card__head--collapsible" data-pset-toggle="spacing-borders">
				<div>
					<h2><span class="dashicons dashicons-editor-expand"></span> <?php esc_html_e( 'Spacing &amp; Borders', 'pizzalayer' ); ?></h2>
					<p>Control padding, gaps, border radii, and dividers across the builder UI.</p>
				</div>
				<button type="button" class="pset-collapse-btn" aria-expanded="true" aria-controls="pset-body-spacing-borders"><span class="dashicons dashicons-arrow-up-alt2"></span></button>
			</div>
			<div class="pset-card__body" id="pset-body-spacing-borders">
				<?php
				// Helper: render a px-slider field with a live text box for exact override
				// $key = option key, $label = label, $desc = description, $default = px int, $max = slider max px
				$_render_spacing_slider = function( string $key, string $label, string $desc, int $default, int $max ) use ( $g ) {
					$raw = $g( $key );
					// Extract numeric value from stored string (e.g. "16px" → 16)
					$num = (int) preg_replace( '/[^0-9]/', '', $raw );
					if ( $num === 0 && $raw === '' ) { $num = $default; }
					$uid = 'pset-spc-' . str_replace( [ 'pizzalayer_setting_spacing_', 'pizzalayer_setting_' ], '', $key );
					?>
					<div class="pset-field">
						<label><?php echo esc_html( $label ); ?>
							<span class="pset-hint" id="<?php echo esc_attr( $uid ); ?>-lbl">(<?php echo esc_html( (string)$num ); ?>px)</span>
						</label>
						<p class="pset-desc"><?php echo wp_kses_post( $desc ); ?></p>
						<div class="pset-range__wrap">
							<input type="range" id="<?php echo esc_attr( $uid ); ?>-range"
							       min="0" max="<?php echo esc_attr( (string)$max ); ?>" step="1"
							       value="<?php echo esc_attr( (string)$num ); ?>"
							       class="pset-range__slider pset-spacing-range"
							       data-target="<?php echo esc_attr( $uid ); ?>-text"
							       data-label="<?php echo esc_attr( $uid ); ?>-lbl">
							<input type="text" id="<?php echo esc_attr( $uid ); ?>-text"
							       name="<?php echo esc_attr( $key ); ?>"
							       value="<?php echo esc_attr( $raw !== '' ? $raw : $num . 'px' ); ?>"
							       class="pset-range__val pset-spacing-text"
							       data-range="<?php echo esc_attr( $uid ); ?>-range"
							       data-label="<?php echo esc_attr( $uid ); ?>-lbl"
							       placeholder="<?php echo esc_attr( (string)$default ); ?>px"
							       style="width:72px;">
						</div>
					</div>
					<?php
				};
				?>
				<div class="pset-grid">
					<?php $_render_spacing_slider( 'pizzalayer_setting_spacing_outer_pad',   'Builder Outer Padding',  'Padding around the outermost builder container.',      16, 80 ); ?>
					<?php $_render_spacing_slider( 'pizzalayer_setting_spacing_grid_gap',    'Menu Grid Gap',          'Gap between item cards in the menu grid.',              12, 60 ); ?>
					<?php $_render_spacing_slider( 'pizzalayer_setting_spacing_card_pad',    'Card Inner Padding',     'Padding inside each item card.',                        10, 60 ); ?>
					<?php $_render_spacing_slider( 'pizzalayer_setting_spacing_card_radius', 'Card Border Radius',     'Corner rounding for item cards.',                        8, 40 ); ?>
					<?php $_render_spacing_slider( 'pizzalayer_setting_spacing_card_border', 'Card Border Width',      'Thickness of the border around item cards.',             1, 10 ); ?>
					<?php $_render_spacing_slider( 'pizzalayer_setting_spacing_btn_radius',  'Button Border Radius',   'Corner rounding for buttons in the builder.',            6, 40 ); ?>
					<?php $_render_spacing_slider( 'pizzalayer_setting_spacing_tab_height',  'Tab Bar Height',         'Height of the layer category tab bar.',                 48, 100 ); ?>
					<div class="pset-field">
						<label><?php esc_html_e( 'Card Box Shadow', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Shadow preset for item cards.', 'pizzalayer' ); ?></p>
						<?php $v = (string) get_option('pizzalayer_setting_spacing_shadow',''); ?>
						<select name="pizzalayer_setting_spacing_shadow" class="pset-select">
							<?php foreach([''=>'None','sm'=>'Subtle (sm)','md'=>'Medium (md)','lg'=>'Elevated (lg)','custom'=>'Custom CSS (see below)'] as $ov=>$ol):?>
							<option value="<?php echo esc_attr($ov);?>"<?php selected($v,$ov);?>><?php echo esc_html($ol);?></option>
							<?php endforeach;?>
						</select>
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Custom Box Shadow CSS', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Used when "Custom CSS" is selected above.', 'pizzalayer' ); ?></p>
						<input type="text" name="pizzalayer_setting_spacing_shadow_css"
						       value="<?php echo esc_attr((string)get_option('pizzalayer_setting_spacing_shadow_css','')); ?>"
						       class="pset-input" placeholder="0 4px 12px rgba(0,0,0,0.15)">
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Section Divider Style', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Visual divider style between builder sections.', 'pizzalayer' ); ?></p>
						<?php $v = (string) get_option('pizzalayer_setting_spacing_divider','solid'); ?>
						<select name="pizzalayer_setting_spacing_divider" class="pset-select">
							<?php foreach(['solid'=>'Solid line','dashed'=>'Dashed line','dotted'=>'Dotted line','none'=>'None'] as $ov=>$ol):?>
							<option value="<?php echo esc_attr($ov);?>"<?php selected($v,$ov);?>><?php echo esc_html($ol);?></option>
							<?php endforeach;?>
						</select>
					</div>
				</div>
			</div>
		</div>

		<!-- ══ Section: Topping Display ══════════════════════════════════════ -->
		<div class="pset-card">
			<div class="pset-card__head pset-card__head--collapsible" data-pset-toggle="topping-display">
				<div>
					<h2><span class="dashicons dashicons-images-alt2"></span> <?php esc_html_e( 'Topping Display', 'pizzalayer' ); ?></h2>
					<p>Fine-grained control over how toppings appear on the pizza visualizer and in the menu.</p>
				</div>
				<button type="button" class="pset-collapse-btn" aria-expanded="true" aria-controls="pset-body-topping-display"><span class="dashicons dashicons-arrow-up-alt2"></span></button>
			</div>
			<div class="pset-card__body" id="pset-body-topping-display">
				<div class="pset-grid">
					<div class="pset-field">
						<label><?php esc_html_e( 'Topping Thumbnail Size', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Size of the topping image in the menu grid.', 'pizzalayer' ); ?></p>
						<?php $v = (string) get_option('pizzalayer_setting_topping_thumb_size',''); ?>
						<select name="pizzalayer_setting_topping_thumb_size" class="pset-select" id="pset-topping-size-preset">
							<?php foreach([''=>'Default (theme)','48px'=>'Small (48px)','72px'=>'Medium (72px)','96px'=>'Large (96px)','custom'=>'Custom (see below)'] as $ov=>$ol):?>
							<option value="<?php echo esc_attr($ov);?>"<?php selected($v,$ov);?>><?php echo esc_html($ol);?></option>
							<?php endforeach;?>
						</select>
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Custom Thumbnail Size', 'pizzalayer' ); ?></label>
						<p class="pset-desc">Used when "Custom" is selected above, e.g. <code>80px</code>.</p>
						<input type="text" name="pizzalayer_setting_topping_thumb_custom"
						       value="<?php echo esc_attr((string)get_option('pizzalayer_setting_topping_thumb_custom','')); ?>"
						       class="pset-input" placeholder="72px">
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Topping Columns (desktop)', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Number of topping columns in the menu grid on large screens.', 'pizzalayer' ); ?></p>
						<input type="number" name="pizzalayer_setting_topping_cols_desktop"
						       value="<?php echo esc_attr((string)get_option('pizzalayer_setting_topping_cols_desktop','')); ?>"
						       class="pset-input" placeholder="4" min="1" max="8">
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Topping Columns (mobile)', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Number of topping columns on small screens.', 'pizzalayer' ); ?></p>
						<input type="number" name="pizzalayer_setting_topping_cols_mobile"
						       value="<?php echo esc_attr((string)get_option('pizzalayer_setting_topping_cols_mobile','')); ?>"
						       class="pset-input" placeholder="2" min="1" max="4">
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Topping Placement Mode', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'How toppings are positioned over the pizza visualizer.', 'pizzalayer' ); ?></p>
						<?php $v = (string) get_option('pizzalayer_setting_topping_placement','scattered'); ?>
						<select name="pizzalayer_setting_topping_placement" class="pset-select">
							<?php foreach(['scattered'=>'Scattered (random)','grid'=>'Grid pattern','rings'=>'Concentric rings','center'=>'Center cluster','edge'=>'Edge ring'] as $ov=>$ol):?>
							<option value="<?php echo esc_attr($ov);?>"<?php selected($v,$ov);?>><?php echo esc_html($ol);?></option>
							<?php endforeach;?>
						</select>
					</div>
					<div class="pset-field">
						<label>Topping Visualizer Size <span class="pset-hint" id="pset-topping-vis-label">(<?php echo esc_html((string)get_option('pizzalayer_setting_topping_vis_size','20')); ?>%)</span></label>
						<p class="pset-desc"><?php esc_html_e( 'Size of each topping image on the pizza preview (% of pizza width).', 'pizzalayer' ); ?></p>
						<div class="pset-range-wrap">
							<input type="range" name="pizzalayer_setting_topping_vis_size"
							       value="<?php echo esc_attr((string)get_option('pizzalayer_setting_topping_vis_size','20')); ?>"
							       min="5" max="50" step="1" class="pset-range"
							       oninput="document.getElementById('pset-topping-vis-val').textContent=this.value+'%';document.getElementById('pset-topping-vis-label').textContent='('+this.value+'%)'">
							<span class="pset-range__val" id="pset-topping-vis-val"><?php echo esc_html((string)get_option('pizzalayer_setting_topping_vis_size','20')); ?>%</span>
						</div>
					</div>
					<div class="pset-field">
						<label>Topping Opacity on Pizza <span class="pset-hint" id="pset-topping-op-label">(<?php echo esc_html((string)get_option('pizzalayer_setting_topping_vis_opacity','100')); ?>%)</span></label>
						<p class="pset-desc"><?php esc_html_e( 'Opacity of topping images in the visualizer.', 'pizzalayer' ); ?></p>
						<div class="pset-range-wrap">
							<input type="range" name="pizzalayer_setting_topping_vis_opacity"
							       value="<?php echo esc_attr((string)get_option('pizzalayer_setting_topping_vis_opacity','100')); ?>"
							       min="10" max="100" step="5" class="pset-range"
							       oninput="document.getElementById('pset-topping-op-val').textContent=this.value+'%';document.getElementById('pset-topping-op-label').textContent='('+this.value+'%)'">
							<span class="pset-range__val" id="pset-topping-op-val"><?php echo esc_html((string)get_option('pizzalayer_setting_topping_vis_opacity','100')); ?>%</span>
						</div>
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Show Topping Count Badge', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Display a quantity badge on topping cards when count &gt; 0.', 'pizzalayer' ); ?></p>
						<label class="pset-toggle">
							<input type="hidden" name="pizzalayer_setting_topping_show_badge" value="no">
							<input type="checkbox" name="pizzalayer_setting_topping_show_badge" value="yes"<?php checked((string)get_option('pizzalayer_setting_topping_show_badge','no'),'yes');?>>
							<span class="pset-toggle__track"><span class="pset-toggle__thumb"></span></span>
							<span class="pset-toggle__label">Show quantity badges</span>
						</label>
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Group Toppings by Category', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Separate toppings into category sub-groups in the menu.', 'pizzalayer' ); ?></p>
						<label class="pset-toggle">
							<input type="hidden" name="pizzalayer_setting_topping_group_cats" value="no">
							<input type="checkbox" name="pizzalayer_setting_topping_group_cats" value="yes"<?php checked((string)get_option('pizzalayer_setting_topping_group_cats','no'),'yes');?>>
							<span class="pset-toggle__track"><span class="pset-toggle__thumb"></span></span>
							<span class="pset-toggle__label">Enable category grouping</span>
						</label>
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Topping Sort Order', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'How toppings are sorted in the menu grid.', 'pizzalayer' ); ?></p>
						<?php $v = (string) get_option('pizzalayer_setting_topping_sort','menu'); ?>
						<select name="pizzalayer_setting_topping_sort" class="pset-select">
							<?php foreach(['menu'=>'Manual (WordPress menu order)','alpha_asc'=>'Alphabetical (A–Z)','alpha_desc'=>'Alphabetical (Z–A)','price_asc'=>'Price (low to high)','price_desc'=>'Price (high to low)'] as $ov=>$ol):?>
							<option value="<?php echo esc_attr($ov);?>"<?php selected($v,$ov);?>><?php echo esc_html($ol);?></option>
							<?php endforeach;?>
						</select>
					</div>
				</div>
			</div>
		</div>

		<!-- ══ Section: Accessibility & Performance ══════════════════════════ -->
		<div class="pset-card">
			<div class="pset-card__head pset-card__head--collapsible" data-pset-toggle="accessibility-perf">
				<div>
					<h2><span class="dashicons dashicons-universal-access"></span> <?php esc_html_e( 'Accessibility &amp; Performance', 'pizzalayer' ); ?></h2>
					<p>WCAG accessibility aids and front-end performance options.</p>
				</div>
				<button type="button" class="pset-collapse-btn" aria-expanded="true" aria-controls="pset-body-accessibility-perf"><span class="dashicons dashicons-arrow-up-alt2"></span></button>
			</div>
			<div class="pset-card__body" id="pset-body-accessibility-perf">
				<div class="pset-grid">
					<div class="pset-field">
						<label><?php esc_html_e( 'Reduce Motion', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Disable all animations for users who prefer reduced motion.', 'pizzalayer' ); ?></p>
						<label class="pset-toggle">
							<input type="hidden" name="pizzalayer_setting_a11y_reduce_motion" value="no">
							<input type="checkbox" name="pizzalayer_setting_a11y_reduce_motion" value="yes"<?php checked((string)get_option('pizzalayer_setting_a11y_reduce_motion','no'),'yes');?>>
							<span class="pset-toggle__track"><span class="pset-toggle__thumb"></span></span>
							<span class="pset-toggle__label">Honor prefers-reduced-motion</span>
						</label>
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'High Contrast Mode', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Force high-contrast colors for all builder UI elements.', 'pizzalayer' ); ?></p>
						<label class="pset-toggle">
							<input type="hidden" name="pizzalayer_setting_a11y_high_contrast" value="no">
							<input type="checkbox" name="pizzalayer_setting_a11y_high_contrast" value="yes"<?php checked((string)get_option('pizzalayer_setting_a11y_high_contrast','no'),'yes');?>>
							<span class="pset-toggle__track"><span class="pset-toggle__thumb"></span></span>
							<span class="pset-toggle__label">Enable high contrast</span>
						</label>
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Focus Ring Style', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Style of the keyboard-focus ring on interactive elements.', 'pizzalayer' ); ?></p>
						<?php $v = (string) get_option('pizzalayer_setting_a11y_focus_ring','default'); ?>
						<select name="pizzalayer_setting_a11y_focus_ring" class="pset-select">
							<?php foreach(['default'=>'Theme default','bold'=>'Bold outline (high visibility)','glow'=>'Glow ring','none'=>'None (not recommended)'] as $ov=>$ol):?>
							<option value="<?php echo esc_attr($ov);?>"<?php selected($v,$ov);?>><?php echo esc_html($ol);?></option>
							<?php endforeach;?>
						</select>
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'ARIA Labels Language', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Language used for auto-generated ARIA accessibility labels.', 'pizzalayer' ); ?></p>
						<?php $v = (string) get_option('pizzalayer_setting_a11y_aria_lang','inherit'); ?>
						<select name="pizzalayer_setting_a11y_aria_lang" class="pset-select">
							<?php foreach(['inherit'=>'Inherit from WordPress','en'=>'English','es'=>'Spanish','fr'=>'French','de'=>'German','it'=>'Italian','pt'=>'Portuguese'] as $ov=>$ol):?>
							<option value="<?php echo esc_attr($ov);?>"<?php selected($v,$ov);?>><?php echo esc_html($ol);?></option>
							<?php endforeach;?>
						</select>
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Lazy-Load Topping Images', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Only load topping images when they scroll into view.', 'pizzalayer' ); ?></p>
						<label class="pset-toggle">
							<input type="hidden" name="pizzalayer_setting_perf_lazy_load" value="no">
							<input type="checkbox" name="pizzalayer_setting_perf_lazy_load" value="yes"<?php checked((string)get_option('pizzalayer_setting_perf_lazy_load','yes'),'yes');?>>
							<span class="pset-toggle__track"><span class="pset-toggle__thumb"></span></span>
							<span class="pset-toggle__label">Enable lazy loading</span>
						</label>
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Preload Builder Assets', 'pizzalayer' ); ?></label>
						<p class="pset-desc">Add <code>&lt;link rel="preload"&gt;</code> hints for critical builder assets.</p>
						<label class="pset-toggle">
							<input type="hidden" name="pizzalayer_setting_perf_preload_assets" value="no">
							<input type="checkbox" name="pizzalayer_setting_perf_preload_assets" value="yes"<?php checked((string)get_option('pizzalayer_setting_perf_preload_assets','no'),'yes');?>>
							<span class="pset-toggle__track"><span class="pset-toggle__thumb"></span></span>
							<span class="pset-toggle__label">Preload critical assets</span>
						</label>
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Image Format Preference', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Preferred image format for layer images.', 'pizzalayer' ); ?></p>
						<?php $v = (string) get_option('pizzalayer_setting_perf_img_format','auto'); ?>
						<select name="pizzalayer_setting_perf_img_format" class="pset-select">
							<?php foreach(['auto'=>'Auto (browser-determined)','webp'=>'Prefer WebP','legacy'=>'Force JPEG/PNG only'] as $ov=>$ol):?>
							<option value="<?php echo esc_attr($ov);?>"<?php selected($v,$ov);?>><?php echo esc_html($ol);?></option>
							<?php endforeach;?>
						</select>
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Client-Side Caching', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Cache layer data in the browser for faster repeat visits.', 'pizzalayer' ); ?></p>
						<?php $v = (string) get_option('pizzalayer_setting_perf_cache','session'); ?>
						<select name="pizzalayer_setting_perf_cache" class="pset-select">
							<?php foreach(['session'=>'Session only','1d'=>'24 hours','7d'=>'7 days','off'=>'Disabled'] as $ov=>$ol):?>
							<option value="<?php echo esc_attr($ov);?>"<?php selected($v,$ov);?>><?php echo esc_html($ol);?></option>
							<?php endforeach;?>
						</select>
					</div>
				</div>
			</div>
		</div>

		<!-- ══ Section: Customer Experience ══════════════════════════════════ -->
		<div class="pset-card">
			<div class="pset-card__head pset-card__head--collapsible" data-pset-toggle="customer-experience">
				<div>
					<h2><span class="dashicons dashicons-smiley"></span> <?php esc_html_e( 'Customer Experience', 'pizzalayer' ); ?></h2>
					<p>Notifications, confirmations, and micro-copy shown to customers during the ordering flow.</p>
				</div>
				<button type="button" class="pset-collapse-btn" aria-expanded="true" aria-controls="pset-body-customer-experience"><span class="dashicons dashicons-arrow-up-alt2"></span></button>
			</div>
			<div class="pset-card__body" id="pset-body-customer-experience">
				<div class="pset-grid">
					<div class="pset-field">
						<label><?php esc_html_e( 'Show "Pizza Summary" Panel', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Display a running summary of selected layers alongside the visualizer.', 'pizzalayer' ); ?></p>
						<label class="pset-toggle">
							<input type="hidden" name="pizzalayer_setting_cx_show_summary" value="no">
							<input type="checkbox" name="pizzalayer_setting_cx_show_summary" value="yes"<?php checked((string)get_option('pizzalayer_setting_cx_show_summary','no'),'yes');?>>
							<span class="pset-toggle__track"><span class="pset-toggle__thumb"></span></span>
							<span class="pset-toggle__label">Show summary panel</span>
						</label>
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Toast Notification Style', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Style of the pop-up when a layer is added or removed.', 'pizzalayer' ); ?></p>
						<?php $v = (string) get_option('pizzalayer_setting_cx_toast_style','bottom-right'); ?>
						<select name="pizzalayer_setting_cx_toast_style" class="pset-select">
							<?php foreach(['bottom-right'=>'Slide-in (bottom-right)','top-center'=>'Slide-in (top-center)','inline'=>'Inline below visualizer','none'=>'None'] as $ov=>$ol):?>
							<option value="<?php echo esc_attr($ov);?>"<?php selected($v,$ov);?>><?php echo esc_html($ol);?></option>
							<?php endforeach;?>
						</select>
					</div>
					<div class="pset-field">
						<label>Toast Duration <span class="pset-hint" id="pset-cx-toast-label">(<?php echo esc_html((string)get_option('pizzalayer_setting_cx_toast_duration','2000')); ?>ms)</span></label>
						<p class="pset-desc"><?php esc_html_e( 'How long the toast notification stays visible.', 'pizzalayer' ); ?></p>
						<div class="pset-range-wrap">
							<input type="range" name="pizzalayer_setting_cx_toast_duration"
							       value="<?php echo esc_attr((string)get_option('pizzalayer_setting_cx_toast_duration','2000')); ?>"
							       min="500" max="5000" step="250" class="pset-range"
							       oninput="document.getElementById('pset-cx-toast-val').textContent=this.value+'ms';document.getElementById('pset-cx-toast-label').textContent='('+this.value+'ms)'">
							<span class="pset-range__val" id="pset-cx-toast-val"><?php echo esc_html((string)get_option('pizzalayer_setting_cx_toast_duration','2000')); ?>ms</span>
						</div>
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( '"Added" Confirmation Text', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Message shown when an item is added to the pizza.', 'pizzalayer' ); ?></p>
						<input type="text" name="pizzalayer_setting_cx_text_added"
						       value="<?php echo esc_attr((string)get_option('pizzalayer_setting_cx_text_added','Added to your pizza!')); ?>"
						       class="pset-input" placeholder="Added to your pizza!">
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( '"Removed" Confirmation Text', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Message shown when an item is removed.', 'pizzalayer' ); ?></p>
						<input type="text" name="pizzalayer_setting_cx_text_removed"
						       value="<?php echo esc_attr((string)get_option('pizzalayer_setting_cx_text_removed','Removed from your pizza.')); ?>"
						       class="pset-input" placeholder="Removed from your pizza.">
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Max Toppings Warning Text', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Shown when the customer tries to exceed the topping limit.', 'pizzalayer' ); ?></p>
						<input type="text" name="pizzalayer_setting_cx_text_max_toppings"
						       value="<?php echo esc_attr((string)get_option('pizzalayer_setting_cx_text_max_toppings','You\'ve reached the maximum number of toppings.')); ?>"
						       class="pset-input" placeholder="You've reached the maximum number of toppings.">
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Show "Start Over" Button', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Display a button that resets all selections to defaults.', 'pizzalayer' ); ?></p>
						<label class="pset-toggle">
							<input type="hidden" name="pizzalayer_setting_cx_show_start_over" value="no">
							<input type="checkbox" name="pizzalayer_setting_cx_show_start_over" value="yes"<?php checked((string)get_option('pizzalayer_setting_cx_show_start_over','yes'),'yes');?>>
							<span class="pset-toggle__track"><span class="pset-toggle__thumb"></span></span>
							<span class="pset-toggle__label">Show reset button</span>
						</label>
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( '"Start Over" Button Label', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Custom text for the reset button.', 'pizzalayer' ); ?></p>
						<input type="text" name="pizzalayer_setting_cx_start_over_label"
						       value="<?php echo esc_attr((string)get_option('pizzalayer_setting_cx_start_over_label','Start Over')); ?>"
						       class="pset-input" placeholder="Start Over">
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Show Special Instructions Field', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Allow customers to add free-text notes to their order.', 'pizzalayer' ); ?></p>
						<label class="pset-toggle">
							<input type="hidden" name="pizzalayer_setting_cx_special_instructions" value="no">
							<input type="checkbox" name="pizzalayer_setting_cx_special_instructions" value="yes"<?php checked((string)get_option('pizzalayer_setting_cx_special_instructions','no'),'yes');?>>
							<span class="pset-toggle__track"><span class="pset-toggle__thumb"></span></span>
							<span class="pset-toggle__label">Enable special instructions</span>
						</label>
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Special Instructions Placeholder', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Hint text inside the special instructions text box.', 'pizzalayer' ); ?></p>
						<input type="text" name="pizzalayer_setting_cx_special_instr_placeholder"
						       value="<?php echo esc_attr((string)get_option('pizzalayer_setting_cx_special_instr_placeholder','Any special requests? (optional)')); ?>"
						       class="pset-input" placeholder="Any special requests? (optional)">
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Special Instructions Max Length', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Maximum characters allowed in the instructions field.', 'pizzalayer' ); ?></p>
						<input type="number" name="pizzalayer_setting_cx_special_instr_max"
						       value="<?php echo esc_attr((string)get_option('pizzalayer_setting_cx_special_instr_max','300')); ?>"
						       class="pset-input" placeholder="300">
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Order Review Modal', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Show a "Review your order" confirmation dialog before adding to cart.', 'pizzalayer' ); ?></p>
						<label class="pset-toggle">
							<input type="hidden" name="pizzalayer_setting_cx_review_modal" value="no">
							<input type="checkbox" name="pizzalayer_setting_cx_review_modal" value="yes"<?php checked((string)get_option('pizzalayer_setting_cx_review_modal','no'),'yes');?>>
							<span class="pset-toggle__track"><span class="pset-toggle__thumb"></span></span>
							<span class="pset-toggle__label">Show review modal before cart</span>
						</label>
					</div>
				</div>
			</div>
		</div>

		<!-- ══ Section: Advanced & Developer ═════════════════════════════════ -->
		<div class="pset-card">
			<div class="pset-card__head pset-card__head--collapsible" data-pset-toggle="advanced-dev">
				<div>
					<h2><span class="dashicons dashicons-editor-code"></span> <?php esc_html_e( 'Advanced &amp; Developer', 'pizzalayer' ); ?></h2>
					<p>Custom CSS injection, debug tools, and low-level overrides for developers.</p>
				</div>
				<button type="button" class="pset-collapse-btn" aria-expanded="true" aria-controls="pset-body-advanced-dev"><span class="dashicons dashicons-arrow-up-alt2"></span></button>
			</div>
			<div class="pset-card__body" id="pset-body-advanced-dev">
				<div class="pset-grid pset-grid--wide">
					<div class="pset-field pset-field--full">
						<label><?php esc_html_e( 'Custom CSS (injected into builder pages)', 'pizzalayer' ); ?></label>
						<p class="pset-desc">CSS added inside a <code>&lt;style&gt;</code> tag on every page containing a PizzaLayer builder. Use with care.</p>
						<textarea name="pizzalayer_setting_adv_custom_css" class="pset-textarea pset-textarea--code" rows="6" placeholder="/* Your custom CSS here */"><?php echo esc_textarea((string)get_option('pizzalayer_setting_adv_custom_css','')); ?></textarea>
					</div>
					<div class="pset-field pset-field--full">
						<label><?php esc_html_e( 'Custom JS (runs after builder initialises)', 'pizzalayer' ); ?></label>
						<p class="pset-desc">JavaScript run after the builder initialises. Useful for custom tracking or integrations. Outputs in <code>&lt;wp_footer&gt;</code>.</p>
						<textarea name="pizzalayer_setting_adv_custom_js" class="pset-textarea pset-textarea--code" rows="6" placeholder="// Your custom JS here"><?php echo esc_textarea((string)get_option('pizzalayer_setting_adv_custom_js','')); ?></textarea>
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Debug Mode', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Log builder events and state changes to the browser console.', 'pizzalayer' ); ?></p>
						<label class="pset-toggle">
							<input type="hidden" name="pizzalayer_setting_adv_debug_mode" value="no">
							<input type="checkbox" name="pizzalayer_setting_adv_debug_mode" value="yes"<?php checked((string)get_option('pizzalayer_setting_adv_debug_mode','no'),'yes');?>>
							<span class="pset-toggle__track"><span class="pset-toggle__thumb"></span></span>
							<span class="pset-toggle__label">Enable console debug output</span>
						</label>
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Disable All Plugin CSS', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Prevent PizzaLayer from enqueueing any front-end stylesheets.', 'pizzalayer' ); ?></p>
						<label class="pset-toggle">
							<input type="hidden" name="pizzalayer_setting_adv_disable_css" value="no">
							<input type="checkbox" name="pizzalayer_setting_adv_disable_css" value="yes"<?php checked((string)get_option('pizzalayer_setting_adv_disable_css','no'),'yes');?>>
							<span class="pset-toggle__track"><span class="pset-toggle__thumb"></span></span>
							<span class="pset-toggle__label">Disable plugin front-end CSS</span>
						</label>
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Enable REST API', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Enables the PizzaLayer REST API endpoints (used by developers and headless setups). Not needed for normal shortcode/block usage — leave off unless you specifically need it.', 'pizzalayer' ); ?></p>
						<label class="pset-toggle">
							<input type="hidden" name="pizzalayer_setting_adv_rest_api_enabled" value="no">
							<input type="checkbox" name="pizzalayer_setting_adv_rest_api_enabled" value="yes"<?php checked((string)get_option('pizzalayer_setting_adv_rest_api_enabled','no'),'yes');?>>
							<span class="pset-toggle__track"><span class="pset-toggle__thumb"></span></span>
							<span class="pset-toggle__label"><?php esc_html_e( 'Enable REST API endpoints', 'pizzalayer' ); ?></span>
						</label>
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'REST API Cache TTL (seconds)', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'How long to cache REST API responses server-side. 0 = no cache. Only applies when REST API is enabled above.', 'pizzalayer' ); ?></p>
						<input type="number" name="pizzalayer_setting_adv_rest_cache_ttl"
						       value="<?php echo esc_attr((string)get_option('pizzalayer_setting_adv_rest_cache_ttl','300')); ?>"
						       class="pset-input" placeholder="300">
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Server Log Level', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Verbosity of server-side logging to the WordPress debug log.', 'pizzalayer' ); ?></p>
						<?php $v = (string) get_option('pizzalayer_setting_adv_log_level','off'); ?>
						<select name="pizzalayer_setting_adv_log_level" class="pset-select">
							<?php foreach(['off'=>'Off','errors'=>'Errors only','warnings'=>'Warnings + Errors','all'=>'All (verbose)'] as $ov=>$ol):?>
							<option value="<?php echo esc_attr($ov);?>"<?php selected($v,$ov);?>><?php echo esc_html($ol);?></option>
							<?php endforeach;?>
						</select>
					</div>
				</div>
			</div>
		</div>


		<!-- ══ Section: Import / Export ══════════════════════════════════ -->
		<div class="pset-card" id="pset-card-data-backup">
			<div class="pset-card__head">
				<div>
					<h2>
						<span class="dashicons dashicons-database-import"></span>
						<?php esc_html_e( 'Import / Export Settings', 'pizzalayer' ); ?>
					</h2>
					<p class="pset-desc"><?php esc_html_e( 'Back up your settings as a JSON file, or restore them on a new site.', 'pizzalayer' ); ?></p>
				</div>
				<button type="button" class="pset-collapse-btn" aria-expanded="true" aria-controls="pset-body-data-backup"><span class="dashicons dashicons-arrow-up-alt2"></span></button>
			</div>
			<div class="pset-card__body" id="pset-body-data-backup">
				<div class="pset-grid pset-grid--wide">
					<div class="pset-field">
						<label><?php esc_html_e( 'Export Settings', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Download all current PizzaLayer settings as a JSON file. Use this to back up your configuration or copy it to another site.', 'pizzalayer' ); ?></p>
						<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=pizzalayer_export_settings' ), 'pizzalayer_export_settings' ) ); ?>"
						   class="button button-secondary">
							<span class="dashicons dashicons-download" style="margin-top:3px;margin-right:4px;"></span>
							<?php esc_html_e( 'Download Settings JSON', 'pizzalayer' ); ?>
						</a>
					</div>
					<div class="pset-field">
						<label><?php esc_html_e( 'Import Settings', 'pizzalayer' ); ?></label>
						<p class="pset-desc"><?php esc_html_e( 'Restore settings from a previously exported JSON file. This will overwrite your current settings.', 'pizzalayer' ); ?></p>
						<div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:8px;">
							<input type="file" name="pizzalayer_import_file" accept=".json,application/json" style="max-width:280px;">
							<button type="submit" name="pizzalayer_import_settings" value="1" class="button button-secondary"
							        onclick="return confirm('<?php esc_attr_e( 'This will overwrite your current settings. Continue?', 'pizzalayer' ); ?>');">
								<span class="dashicons dashicons-upload" style="margin-top:3px;margin-right:4px;"></span>
								<?php esc_html_e( 'Import JSON', 'pizzalayer' ); ?>
							</button>
						</div>
						<p class="pset-desc" style="color:#b32d2e;"><?php esc_html_e( 'Importing will replace all current settings immediately. Export a backup first.', 'pizzalayer' ); ?></p>
					</div>
				</div>
			</div>
		</div>

				<!-- ══ Section: Template Settings (moved to Template page) ══ -->
		<?php if ( $active_template ) : ?>
		<div class="pset-card">
			<div class="pset-card__head">
				<div>
					<h2>
						<span class="dashicons dashicons-admin-appearance"></span>
						<?php echo esc_html( ucwords( str_replace( '-', ' ', $active_template ) ) ); ?> Template Settings
					</h2>
					<p class="pset-desc">Template-specific settings have moved to the <strong>Template</strong> page, below the template selector.</p>
				</div>
			</div>
			<div class="pset-card__body" style="padding:18px 24px;">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=pizzalayer-template#template-settings' ) ); ?>" class="button button-primary">
					<span class="dashicons dashicons-admin-appearance" style="margin-top:3px;"></span>
					<?php printf( esc_html__( 'Open %s Template Settings', 'pizzalayer' ), esc_html( ucwords( str_replace( '-', ' ', $active_template ) ) ) ); ?>
				</a>
				<p style="margin-top:12px;font-size:13px;color:#646970;">Settings for the active template are now configured directly alongside the template selector.</p>
			</div>
		</div>
		<?php endif; ?>

		</div><!-- /.pset-main -->
		</div><!-- /.pset-layout -->

		<!-- ══ Save Bar ══════════════════════════════════════════════ -->
		<div style="position:sticky;bottom:0;z-index:100;background:linear-gradient(to top,#1a1e23 80%,transparent);padding:14px 0 4px;margin-top:20px;text-align:right;">
			<button type="submit" class="button button-primary" style="display:inline-flex;align-items:center;gap:7px;font-size:14px;padding:8px 22px;height:auto;line-height:1.4;">
				<span class="dashicons dashicons-saved" style="font-size:16px;width:16px;height:16px;"></span>
				<?php esc_html_e( 'Save Settings', 'pizzalayer' ); ?>
			</button>
		</div>

		</form>
		</div><!-- /.wrap -->
		<?php $this->render_styles_sidebar(); ?>
		<?php
	}

	/** Called via admin_post_pizzalayer_export_settings — fires before any HTML output. */
	public function handle_export(): void {
		if ( ! current_user_can( 'manage_options' ) ) { wp_die( -1 ); }
		check_admin_referer( 'pizzalayer_export_settings' );
		$this->export_settings();
	}

	private function export_settings(): void {
		$data = [];
		foreach ( self::OPTIONS as $key ) {
			$data[ $key ] = get_option( $key, null );
		}
		// Note: pizzalayer_setting_global_template is now included in OPTIONS above.

		$json     = (string) wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
		$filename = 'pizzalayer-settings-' . gmdate( 'Y-m-d' ) . '.json';

		// Discard any buffered output so download headers can be sent cleanly
		while ( ob_get_level() > 0 ) {
			ob_end_clean();
		}

		if ( headers_sent() ) {
			// Headers already committed — use a JS Blob download as fallback
			echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Exporting&hellip;</title></head><body>';
			echo '<script>';
			printf( 'var d=%s;', $json ); // phpcs:ignore WordPress.Security.EscapeOutput
			echo 'var b=new Blob([JSON.stringify(d,null,2)],{type:"application/json"});';
			printf( 'var a=document.createElement("a");a.href=URL.createObjectURL(b);a.download=%s;', wp_json_encode( $filename ) );
			echo 'document.body.appendChild(a);a.click();';
			echo 'setTimeout(function(){history.back();},1200);';
			echo '</script>';
			echo '<p style="font-family:sans-serif;padding:20px;">Downloading <strong>' . esc_html( $filename ) . '</strong>… <a href="javascript:history.back()">Go back</a></p>';
			echo '</body></html>';
			exit;
		}

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Content-Length: ' . strlen( $json ) );
		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput — raw JSON download
		exit;
	}

	private function import_settings(): string {
		if ( empty( $_FILES['pizzalayer_import_file']['tmp_name'] ) ) {
			return '<div class="notice notice-error is-dismissible"><p><strong>' . esc_html__( 'Import failed:', 'pizzalayer' ) . '</strong> ' . esc_html__( 'no file received.', 'pizzalayer' ) . '</p></div>';
		}

		$tmp  = $_FILES['pizzalayer_import_file']['tmp_name']; // phpcs:ignore
		$raw  = file_get_contents( $tmp ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		if ( ! $raw ) {
			return '<div class="notice notice-error is-dismissible"><p><strong>' . esc_html__( 'Import failed:', 'pizzalayer' ) . '</strong> ' . esc_html__( 'could not read file.', 'pizzalayer' ) . '</p></div>';
		}

		$data = json_decode( $raw, true );
		if ( ! is_array( $data ) ) {
			return '<div class="notice notice-error is-dismissible"><p><strong>' . esc_html__( 'Import failed:', 'pizzalayer' ) . '</strong> ' . esc_html__( 'invalid JSON.', 'pizzalayer' ) . '</p></div>';
		}

		$allowed = array_flip( self::OPTIONS );
		// pizzalayer_setting_global_template is now part of OPTIONS — no manual addition needed.
		$count = 0;

		// Keys that are stored as arrays — must not be cast to string
		$array_options = [
			'pizzalayer_setting_topping_fractions',
		];

		foreach ( $data as $key => $value ) {
			if ( ! isset( $allowed[ $key ] ) ) { continue; }

			if ( in_array( $key, $array_options, true ) ) {
				// Sanitise as an array of keys
				$allowed_fractions = [ 'whole', 'half-left', 'half-right', 'quarter-top-left', 'quarter-top-right', 'quarter-bottom-left', 'quarter-bottom-right' ];
				$sanitised         = is_array( $value )
					? array_values( array_intersect( array_map( 'sanitize_key', $value ), $allowed_fractions ) )
					: [];
				if ( ! in_array( 'whole', $sanitised, true ) ) {
					array_unshift( $sanitised, 'whole' );
				}
				update_option( sanitize_key( $key ), $sanitised );
			} else {
				// All other options treated as sanitised text/HTML
				update_option( sanitize_key( $key ), wp_kses_post( (string) $value ) );
			}
			$count++;
		}

		$msg = sprintf(
			/* translators: %d = number of settings restored */
			'<strong>' . esc_html__( 'Import successful:', 'pizzalayer' ) . '</strong> ' . esc_html__( '%d settings restored.', 'pizzalayer' ),
			$count
		);
		return '<div class="notice notice-success is-dismissible"><p>' . $msg . '</p></div>';
	}

	private function save_settings(): void {
		$text_options = [
			// Existing
			'pizzalayer_setting_pizza_size_max',
			'pizzalayer_setting_pizza_size_min',
			'pizzalayer_setting_pizza_border',
			'pizzalayer_setting_crust_aspectratio',
			'pizzalayer_setting_crust_padding',
			'pizzalayer_setting_sauce_padding',
			'pizzalayer_cheese_setting_cheesedistance',
			'pizzalayer_setting_cheese_padding',
			'pizzalayer_setting_branding_altlogo',
			'pizzalayer_setting_branding_logo_width',
			'pizzalayer_setting_branding_logo_height',
			'pizzalayer_setting_branding_logo_alt',
			'pizzalayer_setting_branding_tagline',
			'pizzalayer_setting_pizza_aspect',
			'pizzalayer_setting_pizza_radius',
			// Builder Layout
			'pizzalayer_setting_layout_builder_width',
			'pizzalayer_setting_layout_tab_order',
			// Pricing (WC cart options moved to PizzaLayerPro)
			// Typography
			'pizzalayer_setting_typo_google_font',
			'pizzalayer_setting_typo_base_size',
			'pizzalayer_setting_typo_label_size',
			'pizzalayer_setting_typo_price_size',
			'pizzalayer_setting_typo_letter_sp',
			// Spacing
			'pizzalayer_setting_spacing_outer_pad',
			'pizzalayer_setting_spacing_grid_gap',
			'pizzalayer_setting_spacing_card_pad',
			'pizzalayer_setting_spacing_card_radius',
			'pizzalayer_setting_spacing_card_border',
			'pizzalayer_setting_spacing_btn_radius',
			'pizzalayer_setting_spacing_tab_height',
			'pizzalayer_setting_spacing_shadow_css',
			// Topping
			'pizzalayer_setting_topping_thumb_custom',
			// Customer Experience
			'pizzalayer_setting_cx_text_added',
			'pizzalayer_setting_cx_text_removed',
			'pizzalayer_setting_cx_text_max_toppings',
			'pizzalayer_setting_cx_start_over_label',
			'pizzalayer_setting_cx_special_instr_placeholder',
		];
		$select_options = [
			// Existing
			'pizzalayer_setting_crust_defaultcrust',
			'pizzalayer_setting_sauce_defaultsauce',
			'pizzalayer_setting_cheese_defaultcheese',
			'pizzalayer_setting_drizzle_defaultdrizzle',
			'pizzalayer_setting_cut_defaultcut',
			'pizzalayer_setting_show_thumbnails',
			'pizzalayer_setting_element_style_layers',
			'pizzalayer_setting_element_style_toppings',
			'pizzalayer_setting_element_style_topping_choice_menu',
			'pizzalayer_setting_pizza_shape',
			'pizzalayer_setting_layer_anim',
			// Builder Layout
			'pizzalayer_setting_layout_mode',
			'pizzalayer_setting_layout_mobile',
			// Pricing
			'pizzalayer_setting_price_display_mode',
			'pizzalayer_setting_price_currency_pos',
			'pizzalayer_setting_price_update_anim',
			// Typography
			'pizzalayer_setting_typo_font_family',
			'pizzalayer_setting_typo_heading_fw',
			'pizzalayer_setting_typo_btn_fw',
			'pizzalayer_setting_typo_text_transform',
			// Topping Display
			'pizzalayer_setting_topping_thumb_size',
			'pizzalayer_setting_topping_placement',
			'pizzalayer_setting_topping_sort',
			// Spacing
			'pizzalayer_setting_spacing_shadow',
			'pizzalayer_setting_spacing_divider',
			// Accessibility / Performance
			'pizzalayer_setting_a11y_focus_ring',
			'pizzalayer_setting_a11y_aria_lang',
			'pizzalayer_setting_perf_img_format',
			'pizzalayer_setting_perf_cache',
			// Customer Experience
			'pizzalayer_setting_cx_toast_style',
			// Advanced
			'pizzalayer_setting_adv_log_level',
		];
		$color_options = [
			// Existing
			'pizzalayer_setting_pizza_border_color',
			'pizzalayer_setting_global_color',
			// Branding
			'pizzalayer_setting_branding_primary_color',
			'pizzalayer_setting_branding_secondary_color',
			// Colour Palette
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
		];
		$number_options = [
			'pizzalayer_setting_topping_maxtoppings',
			'pizzalayer_setting_layer_anim_speed',
			// Builder Layout
			'pizzalayer_setting_layout_mobile_bp',
			// Topping display
			'pizzalayer_setting_topping_cols_desktop',
			'pizzalayer_setting_topping_cols_mobile',
			'pizzalayer_setting_topping_vis_size',
			'pizzalayer_setting_topping_vis_opacity',
			// Customer Experience
			'pizzalayer_setting_cx_toast_duration',
			'pizzalayer_setting_cx_special_instr_max',
			// Advanced
			'pizzalayer_setting_adv_rest_cache_ttl',
		];
		$toggle_options = [
			// Builder Layout
			'pizzalayer_setting_layout_step_by_step',
			'pizzalayer_setting_layout_auto_advance',
			'pizzalayer_setting_layout_hide_empty',
			'pizzalayer_setting_layout_keyboard_nav',
			'pizzalayer_setting_layout_sticky_header',
			// Pricing (WC cart toggles moved to PizzaLayerPro)
			// Topping Display
			'pizzalayer_setting_topping_show_badge',
			'pizzalayer_setting_topping_group_cats',
			// Accessibility / Performance
			'pizzalayer_setting_a11y_reduce_motion',
			'pizzalayer_setting_a11y_high_contrast',
			'pizzalayer_setting_perf_lazy_load',
			'pizzalayer_setting_perf_preload_assets',
			// Customer Experience
			'pizzalayer_setting_cx_show_summary',
			'pizzalayer_setting_cx_show_start_over',
			'pizzalayer_setting_cx_special_instructions',
			'pizzalayer_setting_cx_review_modal',
			// Advanced
			'pizzalayer_setting_adv_debug_mode',
			'pizzalayer_setting_adv_disable_css',
			'pizzalayer_setting_adv_rest_api_enabled',
		];
		$textarea_options = [
			// Global help content (plain text)
			'pizzalayer_setting_settings_demonotice',
			'pizzalayer_setting_global_help_content',
			// Advanced
			'pizzalayer_setting_adv_custom_css',
			'pizzalayer_setting_adv_custom_js',
		];
		// Branding HTML fields — use wp_kses_post to allow rich HTML from the editor
		$html_options = [
			'pizzalayer_setting_branding_menu_title',
			'pizzalayer_setting_branding_header_custom_content',
			'pizzalayer_setting_branding_footer_text',
		];
		// Price base is a decimal string
		$decimal_options = [
			'pizzalayer_setting_price_base',
		];

		foreach ( $text_options as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				update_option( $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
			}
		}
		foreach ( $select_options as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				update_option( $key, sanitize_key( $_POST[ $key ] ) );
			}
		}
		foreach ( $color_options as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				$color = sanitize_hex_color( wp_unslash( $_POST[ $key ] ) );
				if ( $color ) { update_option( $key, $color ); }
			}
		}
		foreach ( $number_options as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				update_option( $key, (int) $_POST[ $key ] );
			}
		}
		foreach ( $toggle_options as $key ) {
			// Hidden field sends 'no'; checkbox sends 'yes' when checked
			update_option( $key, ( isset( $_POST[ $key ] ) && sanitize_key( wp_unslash( $_POST[ $key ] ) ) === 'yes' ) ? 'yes' : 'no' );
		}
		// Topping fractions — multi-checkbox array
		$_allowed_fractions = [ 'whole', 'half-left', 'half-right', 'quarter-top-left', 'quarter-top-right', 'quarter-bottom-left', 'quarter-bottom-right' ];
		$_posted_fractions  = isset( $_POST['pizzalayer_setting_topping_fractions'] ) && is_array( $_POST['pizzalayer_setting_topping_fractions'] )
			? array_values( array_intersect( array_map( 'sanitize_key', wp_unslash( $_POST['pizzalayer_setting_topping_fractions'] ) ), $_allowed_fractions ) )
			: [];
		// Always include 'whole'
		if ( ! in_array( 'whole', $_posted_fractions, true ) ) {
			array_unshift( $_posted_fractions, 'whole' );
		}
		update_option( 'pizzalayer_setting_topping_fractions', $_posted_fractions );

		foreach ( $textarea_options as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				// Custom CSS/JS: use raw (capability-gated)
				if ( in_array( $key, [ 'pizzalayer_setting_adv_custom_css', 'pizzalayer_setting_adv_custom_js' ], true ) ) {
					update_option( $key, wp_unslash( $_POST[ $key ] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- intentional, validated by capability check above
				} else {
					update_option( $key, sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) );
				}
			}
		}
		// Branding HTML fields — allow rich markup from wp_editor
		foreach ( $html_options as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				update_option( $key, wp_kses_post( wp_unslash( $_POST[ $key ] ) ) );
			}
		}
		foreach ( $decimal_options as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				update_option( $key, (string) round( (float) sanitize_text_field( wp_unslash( $_POST[ $key ] ) ), 4 ) );
			}
		}

		// Save template-specific settings dynamically
		$active_template = (string) get_option( 'pizzalayer_setting_global_template', '' );
		if ( $active_template ) {
			$tpl_dirs = [
				get_stylesheet_directory() . '/pzttemplates/' . $active_template . '/',
				PIZZALAYER_TEMPLATES_DIR . $active_template . '/',
			];
			foreach ( $tpl_dirs as $dir ) {
				$options_file = $dir . 'pztp-template-options.php';
				if ( file_exists( $options_file ) ) {
					$tpl_settings = include $options_file;
					if ( is_array( $tpl_settings ) ) {
						foreach ( $tpl_settings as $field ) {
							if ( empty( $field['key'] ) || empty( $field['type'] ) ) { continue; }
							$key = $field['key'];
							if ( ! isset( $_POST[ $key ] ) && $field['type'] === 'toggle' ) {
								update_option( $key, 'no' );
								continue;
							}
							if ( ! isset( $_POST[ $key ] ) ) { continue; }
							switch ( $field['type'] ) {
								case 'color':
									$v = sanitize_hex_color( wp_unslash( $_POST[ $key ] ) );
									if ( $v ) { update_option( $key, $v ); }
									break;
								case 'number':
								case 'range':
									update_option( $key, (float) $_POST[ $key ] );
									break;
								case 'textarea':
									update_option( $key, sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) );
									break;
								case 'toggle':
									update_option( $key, sanitize_key( $_POST[ $key ] ) === 'yes' ? 'yes' : 'no' );
									break;
								default:
									update_option( $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
									break;
							}
						}
					}
					break;
				}
			}
		}
	}

	private function render_select( string $label, string $key, array $posts, string $current ): void {
		?>
		<div class="pset-field">
			<label><?php echo esc_html( $label ); ?></label>
			<select name="<?php echo esc_attr( $key ); ?>" class="pset-select">
				<option value=""><?php esc_html_e( __( '— None / Plugin default —', 'pizzalayer' ), 'pizzalayer' ); ?></option>
				<?php foreach ( $posts as $p ) :
					$slug = sanitize_title( $p->post_title );
				?>
				<option value="<?php echo esc_attr( $slug ); ?>"<?php selected( $current, $slug ); ?>><?php echo esc_html( $p->post_title ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<?php
	}

	private function render_layer_picker( string $label, string $key, array $posts, string $current ): void {
		// Build items array with thumbnail URLs
		$items = [];
		foreach ( $posts as $p ) {
			$slug  = sanitize_title( $p->post_title );
			$thumb = get_the_post_thumbnail_url( $p->ID, 'thumbnail' );
			$items[] = [
				'slug'  => $slug,
				'title' => $p->post_title,
				'thumb' => $thumb ?: '',
			];
		}
		// Find active item for display
		$active_title = '';
		$active_thumb = '';
		foreach ( $items as $item ) {
			if ( $item['slug'] === $current ) {
				$active_title = $item['title'];
				$active_thumb = $item['thumb'];
				break;
			}
		}
		$items_json = esc_attr( wp_json_encode( $items ) );
		?>
		<div class="pset-field pset-layer-picker-field"
		     data-picker-key="<?php echo esc_attr( $key ); ?>"
		     data-picker-label="<?php echo esc_attr( $label ); ?>"
		     data-picker-items="<?php echo $items_json; ?>">
			<label><?php echo esc_html( $label ); ?></label>
			<input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $current ); ?>">
			<button type="button" class="pset-layer-trigger <?php echo $current ? 'pset-layer-trigger--has-value' : ''; ?>">
				<?php if ( $current && $active_title ) : ?>
				<span class="pset-layer-trigger__thumb">
					<?php if ( $active_thumb ) : ?>
					<img src="<?php echo esc_url( $active_thumb ); ?>" alt="<?php echo esc_attr( $active_title ); ?>">
					<?php else : ?>
					<span class="pset-layer-trigger__placeholder dashicons dashicons-format-image"></span>
					<?php endif; ?>
				</span>
				<span class="pset-layer-trigger__name"><?php echo esc_html( $active_title ); ?></span>
				<?php else : ?>
				<span class="pset-layer-trigger__placeholder dashicons dashicons-plus-alt2"></span>
				<span class="pset-layer-trigger__name pset-hint"><?php esc_html_e( 'None selected', 'pizzalayer' ); ?></span>
				<?php endif; ?>
				<span class="pset-layer-trigger__edit dashicons dashicons-edit"></span>
			</button>
		</div>
		<?php
	}

	/** Global colour palette presets — each sets all 15 palette keys. */
	private function get_palette_presets(): array {
		return [
			[
				'name'    => 'Classic Orange',
				'preview' => [ '#f5f5f5', '#ff6b35', '#ff6b35', '#222222' ],
				'values'  => [
					'pizzalayer_setting_color_bg'          => '#f5f5f5',
					'pizzalayer_setting_color_menu_bg'     => '#ffffff',
					'pizzalayer_setting_color_card_bg'     => '#ffffff',
					'pizzalayer_setting_color_card_border' => '#e0e0e0',
					'pizzalayer_setting_color_selected'    => '#ff6b35',
					'pizzalayer_setting_color_tab_bg'      => '#ffffff',
					'pizzalayer_setting_color_tab_active'  => '#ff6b35',
					'pizzalayer_setting_color_tab_text'    => '#333333',
					'pizzalayer_setting_color_btn_bg'      => '#ff6b35',
					'pizzalayer_setting_color_btn_text'    => '#ffffff',
					'pizzalayer_setting_color_btn2_bg'     => '#f0f0f0',
					'pizzalayer_setting_color_body_text'   => '#222222',
					'pizzalayer_setting_color_muted_text'  => '#777777',
					'pizzalayer_setting_color_error'       => '#d32f2f',
					'pizzalayer_setting_color_success'     => '#388e3c',
				],
			],
			[
				'name'    => 'Night Mode',
				'preview' => [ '#1a1e23', '#252a31', '#ff6b35', '#e2e8f0' ],
				'values'  => [
					'pizzalayer_setting_color_bg'          => '#1a1e23',
					'pizzalayer_setting_color_menu_bg'     => '#252a31',
					'pizzalayer_setting_color_card_bg'     => '#2d3748',
					'pizzalayer_setting_color_card_border' => '#3a4558',
					'pizzalayer_setting_color_selected'    => '#ff6b35',
					'pizzalayer_setting_color_tab_bg'      => '#252a31',
					'pizzalayer_setting_color_tab_active'  => '#ff6b35',
					'pizzalayer_setting_color_tab_text'    => '#e2e8f0',
					'pizzalayer_setting_color_btn_bg'      => '#ff6b35',
					'pizzalayer_setting_color_btn_text'    => '#ffffff',
					'pizzalayer_setting_color_btn2_bg'     => '#3a4558',
					'pizzalayer_setting_color_body_text'   => '#e2e8f0',
					'pizzalayer_setting_color_muted_text'  => '#8d97a5',
					'pizzalayer_setting_color_error'       => '#ef5350',
					'pizzalayer_setting_color_success'     => '#66bb6a',
				],
			],
			[
				'name'    => 'Forest Green',
				'preview' => [ '#f4f1e8', '#2d6a4f', '#2d6a4f', '#1a2e1e' ],
				'values'  => [
					'pizzalayer_setting_color_bg'          => '#f4f1e8',
					'pizzalayer_setting_color_menu_bg'     => '#fffef9',
					'pizzalayer_setting_color_card_bg'     => '#fffef9',
					'pizzalayer_setting_color_card_border' => '#d4e8d8',
					'pizzalayer_setting_color_selected'    => '#2d6a4f',
					'pizzalayer_setting_color_tab_bg'      => '#fffef9',
					'pizzalayer_setting_color_tab_active'  => '#2d6a4f',
					'pizzalayer_setting_color_tab_text'    => '#1a2e1e',
					'pizzalayer_setting_color_btn_bg'      => '#2d6a4f',
					'pizzalayer_setting_color_btn_text'    => '#ffffff',
					'pizzalayer_setting_color_btn2_bg'     => '#e8f0e9',
					'pizzalayer_setting_color_body_text'   => '#1a2e1e',
					'pizzalayer_setting_color_muted_text'  => '#5a7a60',
					'pizzalayer_setting_color_error'       => '#c62828',
					'pizzalayer_setting_color_success'     => '#2d6a4f',
				],
			],
			[
				'name'    => 'Rustic Red',
				'preview' => [ '#fdf4ec', '#c2410c', '#c2410c', '#2d1a0e' ],
				'values'  => [
					'pizzalayer_setting_color_bg'          => '#fdf4ec',
					'pizzalayer_setting_color_menu_bg'     => '#ffffff',
					'pizzalayer_setting_color_card_bg'     => '#ffffff',
					'pizzalayer_setting_color_card_border' => '#f0d5c0',
					'pizzalayer_setting_color_selected'    => '#c2410c',
					'pizzalayer_setting_color_tab_bg'      => '#ffffff',
					'pizzalayer_setting_color_tab_active'  => '#c2410c',
					'pizzalayer_setting_color_tab_text'    => '#2d1a0e',
					'pizzalayer_setting_color_btn_bg'      => '#c2410c',
					'pizzalayer_setting_color_btn_text'    => '#ffffff',
					'pizzalayer_setting_color_btn2_bg'     => '#fce8d8',
					'pizzalayer_setting_color_body_text'   => '#2d1a0e',
					'pizzalayer_setting_color_muted_text'  => '#8c6a55',
					'pizzalayer_setting_color_error'       => '#b71c1c',
					'pizzalayer_setting_color_success'     => '#2e7d32',
				],
			],
			[
				'name'    => 'Midnight Blue',
				'preview' => [ '#0f1729', '#1e2d4a', '#2563eb', '#f0f4ff' ],
				'values'  => [
					'pizzalayer_setting_color_bg'          => '#0f1729',
					'pizzalayer_setting_color_menu_bg'     => '#1e2d4a',
					'pizzalayer_setting_color_card_bg'     => '#253557',
					'pizzalayer_setting_color_card_border' => '#2e4070',
					'pizzalayer_setting_color_selected'    => '#2563eb',
					'pizzalayer_setting_color_tab_bg'      => '#1e2d4a',
					'pizzalayer_setting_color_tab_active'  => '#2563eb',
					'pizzalayer_setting_color_tab_text'    => '#f0f4ff',
					'pizzalayer_setting_color_btn_bg'      => '#2563eb',
					'pizzalayer_setting_color_btn_text'    => '#ffffff',
					'pizzalayer_setting_color_btn2_bg'     => '#2e4070',
					'pizzalayer_setting_color_body_text'   => '#f0f4ff',
					'pizzalayer_setting_color_muted_text'  => '#8da4c8',
					'pizzalayer_setting_color_error'       => '#ef5350',
					'pizzalayer_setting_color_success'     => '#66bb6a',
				],
			],
			[
				'name'    => 'Monochrome',
				'preview' => [ '#f4f4f5', '#18181b', '#18181b', '#09090b' ],
				'values'  => [
					'pizzalayer_setting_color_bg'          => '#f4f4f5',
					'pizzalayer_setting_color_menu_bg'     => '#ffffff',
					'pizzalayer_setting_color_card_bg'     => '#ffffff',
					'pizzalayer_setting_color_card_border' => '#d4d4d8',
					'pizzalayer_setting_color_selected'    => '#18181b',
					'pizzalayer_setting_color_tab_bg'      => '#ffffff',
					'pizzalayer_setting_color_tab_active'  => '#18181b',
					'pizzalayer_setting_color_tab_text'    => '#09090b',
					'pizzalayer_setting_color_btn_bg'      => '#18181b',
					'pizzalayer_setting_color_btn_text'    => '#ffffff',
					'pizzalayer_setting_color_btn2_bg'     => '#e4e4e7',
					'pizzalayer_setting_color_body_text'   => '#09090b',
					'pizzalayer_setting_color_muted_text'  => '#71717a',
					'pizzalayer_setting_color_error'       => '#dc2626',
					'pizzalayer_setting_color_success'     => '#16a34a',
				],
			],
			[
				'name'    => 'Rose Bistro',
				'preview' => [ '#fff0f6', '#be185d', '#be185d', '#3b0a1f' ],
				'values'  => [
					'pizzalayer_setting_color_bg'          => '#fff0f6',
					'pizzalayer_setting_color_menu_bg'     => '#ffffff',
					'pizzalayer_setting_color_card_bg'     => '#ffffff',
					'pizzalayer_setting_color_card_border' => '#f9c8e0',
					'pizzalayer_setting_color_selected'    => '#be185d',
					'pizzalayer_setting_color_tab_bg'      => '#ffffff',
					'pizzalayer_setting_color_tab_active'  => '#be185d',
					'pizzalayer_setting_color_tab_text'    => '#3b0a1f',
					'pizzalayer_setting_color_btn_bg'      => '#be185d',
					'pizzalayer_setting_color_btn_text'    => '#ffffff',
					'pizzalayer_setting_color_btn2_bg'     => '#fce7f3',
					'pizzalayer_setting_color_body_text'   => '#3b0a1f',
					'pizzalayer_setting_color_muted_text'  => '#9d174d',
					'pizzalayer_setting_color_error'       => '#b91c1c',
					'pizzalayer_setting_color_success'     => '#15803d',
				],
			],
			[
				'name'    => 'Sea Breeze',
				'preview' => [ '#f0f9ff', '#0891b2', '#0891b2', '#0c3040' ],
				'values'  => [
					'pizzalayer_setting_color_bg'          => '#f0f9ff',
					'pizzalayer_setting_color_menu_bg'     => '#ffffff',
					'pizzalayer_setting_color_card_bg'     => '#ffffff',
					'pizzalayer_setting_color_card_border' => '#bae6fd',
					'pizzalayer_setting_color_selected'    => '#0891b2',
					'pizzalayer_setting_color_tab_bg'      => '#ffffff',
					'pizzalayer_setting_color_tab_active'  => '#0891b2',
					'pizzalayer_setting_color_tab_text'    => '#0c3040',
					'pizzalayer_setting_color_btn_bg'      => '#0891b2',
					'pizzalayer_setting_color_btn_text'    => '#ffffff',
					'pizzalayer_setting_color_btn2_bg'     => '#e0f2fe',
					'pizzalayer_setting_color_body_text'   => '#0c3040',
					'pizzalayer_setting_color_muted_text'  => '#0e7490',
					'pizzalayer_setting_color_error'       => '#dc2626',
					'pizzalayer_setting_color_success'     => '#059669',
				],
			],
		];
	}

	/** 10 Metro color scheme presets — each sets accent, page bg, card bg. */
	private function get_metro_color_schemes(): array {
		return [
			[
				'name'   => 'Classic Red',
				'colors' => [ '#e63946', '#f7f7f5', '#ffffff' ],
				'keys'   => [ 'metro_setting_accent_color' => '#e63946', 'metro_setting_background_color' => '#f7f7f5', 'metro_setting_card_bg_color' => '#ffffff' ],
			],
			[
				'name'   => 'Midnight Blue',
				'colors' => [ '#2563eb', '#0f1729', '#1e2d4a' ],
				'keys'   => [ 'metro_setting_accent_color' => '#2563eb', 'metro_setting_background_color' => '#0f1729', 'metro_setting_card_bg_color' => '#1e2d4a' ],
			],
			[
				'name'   => 'Forest & Cream',
				'colors' => [ '#2d6a4f', '#f4f1e8', '#fffef9' ],
				'keys'   => [ 'metro_setting_accent_color' => '#2d6a4f', 'metro_setting_background_color' => '#f4f1e8', 'metro_setting_card_bg_color' => '#fffef9' ],
			],
			[
				'name'   => 'Burnt Orange',
				'colors' => [ '#c2410c', '#fdf4ec', '#ffffff' ],
				'keys'   => [ 'metro_setting_accent_color' => '#c2410c', 'metro_setting_background_color' => '#fdf4ec', 'metro_setting_card_bg_color' => '#ffffff' ],
			],
			[
				'name'   => 'Slate & Steel',
				'colors' => [ '#475569', '#1e293b', '#293548' ],
				'keys'   => [ 'metro_setting_accent_color' => '#475569', 'metro_setting_background_color' => '#1e293b', 'metro_setting_card_bg_color' => '#293548' ],
			],
			[
				'name'   => 'Rose Bistro',
				'colors' => [ '#be185d', '#fff0f6', '#ffffff' ],
				'keys'   => [ 'metro_setting_accent_color' => '#be185d', 'metro_setting_background_color' => '#fff0f6', 'metro_setting_card_bg_color' => '#ffffff' ],
			],
			[
				'name'   => 'Golden Hour',
				'colors' => [ '#b45309', '#fffbeb', '#ffffff' ],
				'keys'   => [ 'metro_setting_accent_color' => '#b45309', 'metro_setting_background_color' => '#fffbeb', 'metro_setting_card_bg_color' => '#ffffff' ],
			],
			[
				'name'   => 'Violet Night',
				'colors' => [ '#7c3aed', '#1a0533', '#2a1045' ],
				'keys'   => [ 'metro_setting_accent_color' => '#7c3aed', 'metro_setting_background_color' => '#1a0533', 'metro_setting_card_bg_color' => '#2a1045' ],
			],
			[
				'name'   => 'Sea Breeze',
				'colors' => [ '#0891b2', '#f0f9ff', '#ffffff' ],
				'keys'   => [ 'metro_setting_accent_color' => '#0891b2', 'metro_setting_background_color' => '#f0f9ff', 'metro_setting_card_bg_color' => '#ffffff' ],
			],
			[
				'name'   => 'Monochrome',
				'colors' => [ '#18181b', '#f4f4f5', '#ffffff' ],
				'keys'   => [ 'metro_setting_accent_color' => '#18181b', 'metro_setting_background_color' => '#f4f4f5', 'metro_setting_card_bg_color' => '#ffffff' ],
			],
		];
	}

	private function get_plainlist_presets(): array {
		return [
			[
				'name'   => 'Classic Black',
				'colors' => [ '#1a1a1a', '#ffffff', '#111111' ],
				'keys'   => [ 'plainlist_setting_accent_color' => '#1a1a1a', 'plainlist_setting_bg_color' => '#ffffff', 'plainlist_setting_section_header_color' => '#111111' ],
			],
			[
				'name'   => 'Warm Paper',
				'colors' => [ '#7c3a00', '#fdf6ec', '#3d2000' ],
				'keys'   => [ 'plainlist_setting_accent_color' => '#7c3a00', 'plainlist_setting_bg_color' => '#fdf6ec', 'plainlist_setting_section_header_color' => '#3d2000' ],
			],
			[
				'name'   => 'Dark Mode',
				'colors' => [ '#f97316', '#18181b', '#ffffff' ],
				'keys'   => [ 'plainlist_setting_accent_color' => '#f97316', 'plainlist_setting_bg_color' => '#18181b', 'plainlist_setting_section_header_color' => '#ffffff' ],
			],
			[
				'name'   => 'Forest',
				'colors' => [ '#2d6a4f', '#f4f9f6', '#1b3d2d' ],
				'keys'   => [ 'plainlist_setting_accent_color' => '#2d6a4f', 'plainlist_setting_bg_color' => '#f4f9f6', 'plainlist_setting_section_header_color' => '#1b3d2d' ],
			],
			[
				'name'   => 'Navy Clean',
				'colors' => [ '#1e3a8a', '#f8faff', '#0f2060' ],
				'keys'   => [ 'plainlist_setting_accent_color' => '#1e3a8a', 'plainlist_setting_bg_color' => '#f8faff', 'plainlist_setting_section_header_color' => '#0f2060' ],
			],
			[
				'name'   => 'Rose',
				'colors' => [ '#be185d', '#fff0f6', '#7c103d' ],
				'keys'   => [ 'plainlist_setting_accent_color' => '#be185d', 'plainlist_setting_bg_color' => '#fff0f6', 'plainlist_setting_section_header_color' => '#7c103d' ],
			],
			[
				'name'   => 'Slate',
				'colors' => [ '#475569', '#f1f5f9', '#1e293b' ],
				'keys'   => [ 'plainlist_setting_accent_color' => '#475569', 'plainlist_setting_bg_color' => '#f1f5f9', 'plainlist_setting_section_header_color' => '#1e293b' ],
			],
			[
				'name'   => 'Newspaper',
				'colors' => [ '#222222', '#f7f4ee', '#000000' ],
				'keys'   => [ 'plainlist_setting_accent_color' => '#222222', 'plainlist_setting_bg_color' => '#f7f4ee', 'plainlist_setting_section_header_color' => '#000000', 'plainlist_setting_font_family' => 'georgia', 'plainlist_setting_check_style' => 'bullet' ],
			],
		];
	}

	private function render_styles(): void {
		// CSS is now enqueued via AssetManager: assets/css/settings-page.css
	}

	private function render_styles_sidebar(): void {
		// CSS is now enqueued via AssetManager: assets/css/settings-page.css
	}
}
