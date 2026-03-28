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
		'pizzalayer_setting_branding_menu_title',
		'pizzalayer_setting_branding_header_custom_content',
		// Plugin settings
		'pizzalayer_setting_settings_demonotice',
		'pizzalayer_setting_global_help_content',
	];

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) { return; }

		// Save
		if ( isset( $_POST['pizzalayer_settings_save'], $_POST['_wpnonce'] )
		     && wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'pizzalayer_settings_save' ) ) {
			$this->save_settings();
			echo '<div class="notice notice-success is-dismissible"><p><strong>Settings saved.</strong></p></div>';
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

		<div class="pset-header">
			<span class="dashicons dashicons-admin-settings pset-header__icon"></span>
			<div>
				<h1 class="pset-header__title">Settings</h1>
				<p class="pset-header__sub">All plugin settings in one place. These replace the old WordPress Customizer panel.</p>
			</div>
		</div>

		<form method="post" action="" id="pset-form" enctype="multipart/form-data">
		<?php wp_nonce_field( 'pizzalayer_settings_save' ); ?>
		<input type="hidden" name="pizzalayer_settings_save" value="1">

		<div class="pset-layout">
		<div class="pset-main">

		<!-- ══ Section: Default Layers ═══════════════════════════════ -->
		<div class="pset-card">
			<div class="pset-card__head pset-card__head--collapsible" data-pset-toggle="default-layers">
				<div>
					<h2><span class="dashicons dashicons-category"></span> Default Layers</h2>
					<p>These layers are pre-selected when the builder loads, unless overridden by the shortcode attribute.</p>
				</div>
				<button type="button" class="pset-collapse-btn" aria-expanded="true" aria-controls="pset-body-default-layers"><span class="dashicons dashicons-arrow-up-alt2"></span></button>
			</div>
			<div class="pset-card__body" id="pset-body-default-layers">
				<div class="pset-grid pset-grid--layers">
					<?php $this->render_layer_picker( 'Default Crust',   'pizzalayer_setting_crust_defaultcrust',     $crusts,   $g('pizzalayer_setting_crust_defaultcrust') ); ?>
					<?php $this->render_layer_picker( 'Default Sauce',   'pizzalayer_setting_sauce_defaultsauce',     $sauces,   $g('pizzalayer_setting_sauce_defaultsauce') ); ?>
					<?php $this->render_layer_picker( 'Default Cheese',  'pizzalayer_setting_cheese_defaultcheese',   $cheeses,  $g('pizzalayer_setting_cheese_defaultcheese') ); ?>
					<?php $this->render_layer_picker( 'Default Drizzle', 'pizzalayer_setting_drizzle_defaultdrizzle', $drizzles, $g('pizzalayer_setting_drizzle_defaultdrizzle') ); ?>
					<?php $this->render_layer_picker( 'Default Cut',     'pizzalayer_setting_cut_defaultcut',         $cuts,     $g('pizzalayer_setting_cut_defaultcut') ); ?>
				</div>
			</div>
		</div>

		<!-- ══ Layer Picker Modal ═════════════════════════════════ -->
		<div id="pset-layer-modal" class="pset-modal" role="dialog" aria-modal="true" aria-label="Choose layer" style="display:none;">
			<div class="pset-modal__backdrop"></div>
			<div class="pset-modal__box">
				<div class="pset-modal__head">
					<h3 id="pset-modal-title" class="pset-modal__title">Choose a layer</h3>
					<button type="button" class="pset-modal__close" aria-label="Close">&times;</button>
				</div>
				<div class="pset-modal__search-wrap">
					<span class="dashicons dashicons-search pset-modal__search-icon"></span>
					<input type="text" id="pset-modal-search" class="pset-modal__search" placeholder="Search&#8230;" autocomplete="off">
				</div>
				<div id="pset-modal-grid" class="pset-modal__grid"></div>
				<div class="pset-modal__foot">
					<button type="button" class="pset-modal__clear button">
						<span class="dashicons dashicons-dismiss"></span> Clear selection
					</button>
				</div>
			</div>
		</div>

		<!-- ══ Section: Toppings ═════════════════════════════════════ -->
		<div class="pset-card">
			<div class="pset-card__head pset-card__head--collapsible" data-pset-toggle="toppings">
				<div>
					<h2><span class="dashicons dashicons-star-filled"></span> Toppings</h2>
					<p>Controls how many toppings customers can add and what pizza fractions are available.</p>
				</div>
				<button type="button" class="pset-collapse-btn" aria-expanded="true" aria-controls="pset-body-toppings"><span class="dashicons dashicons-arrow-up-alt2"></span></button>
			</div>
			<div class="pset-card__body" id="pset-body-toppings">
				<div class="pset-grid">
					<div class="pset-field">
						<label>Max Toppings</label>
						<p class="pset-desc">Maximum number of toppings a customer can add. 0 = unlimited.</p>
						<input type="number" name="pizzalayer_setting_topping_maxtoppings" min="0"
						       value="<?php echo esc_attr( $g('pizzalayer_setting_topping_maxtoppings') ); ?>" class="pset-input">
					</div>
					<div class="pset-field">
						<label>Topping Portions</label>
						<p class="pset-desc">The smallest pizza portion customers can apply toppings to.</p>
						<select name="pizzalayer_setting_topping_fractions" class="pset-select">
							<?php foreach ( [ 'whole' => 'Whole only', 'halves' => 'Halves', 'quarters' => 'Quarters' ] as $v => $l ) : ?>
							<option value="<?php echo esc_attr( $v ); ?>"<?php selected( $g('pizzalayer_setting_topping_fractions'), $v ); ?>><?php echo esc_html( $l ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
			</div>
		</div>

		<!-- ══ Section: Pizza Display ════════════════════════════════ -->
		<div class="pset-card">
			<div class="pset-card__head pset-card__head--collapsible" data-pset-toggle="pizza-display">
				<div>
					<h2><span class="dashicons dashicons-pizza"></span> Pizza Display</h2>
					<p>Control the size and appearance of the pizza visualizer circle.</p>
				</div>
				<button type="button" class="pset-collapse-btn" aria-expanded="true" aria-controls="pset-body-pizza-display"><span class="dashicons dashicons-arrow-up-alt2"></span></button>
			</div>
			<div class="pset-card__body" id="pset-body-pizza-display">
				<div class="pset-grid">
					<div class="pset-field">
						<label>Max Size</label>
						<p class="pset-desc">Max size (px or %). Include unit — e.g. <code>500px</code> or <code>100%</code>.</p>
						<input type="text" name="pizzalayer_setting_pizza_size_max"
						       value="<?php echo esc_attr( $g('pizzalayer_setting_pizza_size_max') ); ?>" class="pset-input" placeholder="500px">
					</div>
					<div class="pset-field">
						<label>Min Size</label>
						<p class="pset-desc">Min size (px or %). Include unit.</p>
						<input type="text" name="pizzalayer_setting_pizza_size_min"
						       value="<?php echo esc_attr( $g('pizzalayer_setting_pizza_size_min') ); ?>" class="pset-input" placeholder="200px">
					</div>
					<div class="pset-field">
						<label>Border Width</label>
						<p class="pset-desc">Any valid CSS width, e.g. <code>2px</code>.</p>
						<input type="text" name="pizzalayer_setting_pizza_border"
						       value="<?php echo esc_attr( $g('pizzalayer_setting_pizza_border') ); ?>" class="pset-input" placeholder="2px">
					</div>
					<div class="pset-field">
						<label>Border Color</label>
						<p class="pset-desc">Color for the pizza border.</p>
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
						<label>Accent Color</label>
						<p class="pset-desc">Global accent color used in templates.</p>
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
					<h2><span class="dashicons dashicons-image-crop"></span> Pizza Shape</h2>
					<p>Controls the shape of the pizza preview in the builder. Can be overridden per-shortcode with <code>pizza_shape="..."</code>.</p>
				</div>
				<button type="button" class="pset-collapse-btn" aria-expanded="true" aria-controls="pset-body-pizza-shape"><span class="dashicons dashicons-arrow-up-alt2"></span></button>
			</div>
			<div class="pset-card__body" id="pset-body-pizza-shape">
				<div class="pset-grid">
					<div class="pset-field">
						<label>Shape Preset</label>
						<p class="pset-desc">Choose a shape for the pizza visualizer.</p>
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
					<h2><span class="dashicons dashicons-controls-play"></span> Layer Animation</h2>
					<p>Animation played when a layer is added to the pizza preview. Can be overridden per-shortcode with <code>layer_anim="..."</code>.</p>
				</div>
				<button type="button" class="pset-collapse-btn" aria-expanded="true" aria-controls="pset-body-layer-animation"><span class="dashicons dashicons-arrow-up-alt2"></span></button>
			</div>
			<div class="pset-card__body" id="pset-body-layer-animation">
				<div class="pset-grid">
					<div class="pset-field">
						<label>Animation Style</label>
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
						<p class="pset-desc">Duration of the layer animation. Ignored when style is Instant.</p>
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
							<label>Preview</label>
							<p class="pset-desc">Click the button to preview the selected animation.</p>
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
					<h2><span class="dashicons dashicons-tag"></span> Crust Options</h2>
					<p>Fine-tune how the crust layer is sized and spaced in the visualizer.</p>
				</div>
				<button type="button" class="pset-collapse-btn" aria-expanded="true" aria-controls="pset-body-crust-options"><span class="dashicons dashicons-arrow-up-alt2"></span></button>
			</div>
			<div class="pset-card__body" id="pset-body-crust-options">
				<div class="pset-grid">
					<div class="pset-field">
						<label>Aspect Ratio / Shape</label>
						<p class="pset-desc">CSS aspect-ratio for the crust, e.g. <code>1</code> for round, <code>4/3</code> for rectangular.</p>
						<input type="text" name="pizzalayer_setting_crust_aspectratio"
						       value="<?php echo esc_attr( $g('pizzalayer_setting_crust_aspectratio', '1') ); ?>" class="pset-input" placeholder="1">
					</div>
					<div class="pset-field">
						<label>Crust Padding</label>
						<p class="pset-desc">CSS padding value, e.g. <code>10px</code>.</p>
						<input type="text" name="pizzalayer_setting_crust_padding"
						       value="<?php echo esc_attr( $g('pizzalayer_setting_crust_padding') ); ?>" class="pset-input" placeholder="0">
					</div>
				</div>
			</div>
		</div>

		<!-- ══ Section: Sauce / Cheese Options ═══════════════════════ -->
		<div class="pset-card">
			<div class="pset-card__head pset-card__head--collapsible" data-pset-toggle="sauce-cheese">
				<div>
					<h2><span class="dashicons dashicons-admin-generic"></span> Sauce &amp; Cheese Options</h2>
					<p>Adjust padding and inset distances for the sauce and cheese layers.</p>
				</div>
				<button type="button" class="pset-collapse-btn" aria-expanded="true" aria-controls="pset-body-sauce-cheese"><span class="dashicons dashicons-arrow-up-alt2"></span></button>
			</div>
			<div class="pset-card__body" id="pset-body-sauce-cheese">
				<div class="pset-grid">
					<div class="pset-field">
						<label>Sauce Padding</label>
						<p class="pset-desc">Padding between sauce and crust edge.</p>
						<input type="text" name="pizzalayer_setting_sauce_padding"
						       value="<?php echo esc_attr( $g('pizzalayer_setting_sauce_padding') ); ?>" class="pset-input" placeholder="0">
					</div>
					<div class="pset-field">
						<label>Cheese Distance from Edge</label>
						<p class="pset-desc">How far inset the cheese layer is.</p>
						<input type="text" name="pizzalayer_cheese_setting_cheesedistance"
						       value="<?php echo esc_attr( $g('pizzalayer_cheese_setting_cheesedistance') ); ?>" class="pset-input" placeholder="0">
					</div>
					<div class="pset-field">
						<label>Cheese Padding</label>
						<p class="pset-desc">Padding between cheese and toppings.</p>
						<input type="text" name="pizzalayer_setting_cheese_padding"
						       value="<?php echo esc_attr( $g('pizzalayer_setting_cheese_padding') ); ?>" class="pset-input" placeholder="0">
					</div>
				</div>
			</div>
		</div>

		<!-- ══ Section: UI Element Styles ════════════════════════════ -->
		<div class="pset-card">
			<div class="pset-card__head pset-card__head--collapsible" data-pset-toggle="ui-styles">
				<div>
					<h2><span class="dashicons dashicons-art"></span> UI Element Styles</h2>
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
						<label>Toppings Style</label>
						<select name="pizzalayer_setting_element_style_toppings" class="pset-select">
							<?php foreach ( [ 'default'=>'Default','controlbox'=>'Control Box','thumbcorner'=>'Thumb Corner','bgtoggle'=>'Background Toggle','modern'=>'Modern Offset','cornertag'=>'Corner Tag','appadd'=>'App Add' ] as $v => $l ) : ?>
							<option value="<?php echo esc_attr( $v ); ?>"<?php selected( $g('pizzalayer_setting_element_style_toppings'), $v ); ?>><?php echo esc_html( $l ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="pset-field">
						<label>Topping Choice Menu Style</label>
						<select name="pizzalayer_setting_element_style_topping_choice_menu" class="pset-select">
							<?php foreach ( [ 'default'=>'Default','minimal'=>'Minimal','iconwfraction'=>'Icon (with fraction)','iconnofraction'=>'Icon (no fraction)' ] as $v => $l ) : ?>
							<option value="<?php echo esc_attr( $v ); ?>"<?php selected( $g('pizzalayer_setting_element_style_topping_choice_menu'), $v ); ?>><?php echo esc_html( $l ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="pset-field">
						<label>Show Thumbnails</label>
						<p class="pset-desc">Show thumbnail images in menu UI.</p>
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
					<h2><span class="dashicons dashicons-admin-customizer"></span> Branding</h2>
					<p>Content displayed in the builder's branded areas (header, sidebar, above menu icons).</p>
				</div>
				<button type="button" class="pset-collapse-btn" aria-expanded="true" aria-controls="pset-body-branding"><span class="dashicons dashicons-arrow-up-alt2"></span></button>
			</div>
			<div class="pset-card__body" id="pset-body-branding">
				<div class="pset-grid pset-grid--wide">
					<div class="pset-field pset-field--full">
						<label>Logo URL <span class="pset-hint">For templates that show a header logo</span></label>
						<p class="pset-desc">Current: <?php $logoVal = $g('pizzalayer_setting_branding_altlogo'); echo $logoVal ? '<a href="'.esc_url($logoVal).'" target="_blank">'.esc_html(basename($logoVal)).'</a>' : '<em>none</em>'; ?></p>
						<input type="url" name="pizzalayer_setting_branding_altlogo"
						       value="<?php echo esc_attr( $logoVal ?? '' ); ?>" class="pset-input pset-input--wide" placeholder="https://...">
					</div>
					<div class="pset-field pset-field--full">
						<label>Content Above Menu Icons</label>
						<p class="pset-desc">Intro text or custom HTML shown above the builder tab icons.</p>
						<textarea name="pizzalayer_setting_branding_menu_title" class="pset-textarea" rows="3"><?php echo esc_textarea( $g('pizzalayer_setting_branding_menu_title') ); ?></textarea>
					</div>
					<div class="pset-field pset-field--full">
						<label>Header Custom Content</label>
						<p class="pset-desc">Custom HTML for the branding area in the builder header.</p>
						<textarea name="pizzalayer_setting_branding_header_custom_content" class="pset-textarea" rows="3"><?php echo esc_textarea( $g('pizzalayer_setting_branding_header_custom_content') ); ?></textarea>
					</div>
				</div>
			</div>
		</div>

		<!-- ══ Section: Plugin Settings ═════════════════════════════ -->
		<div class="pset-card">
			<div class="pset-card__head pset-card__head--collapsible" data-pset-toggle="plugin-settings">
				<div>
					<h2><span class="dashicons dashicons-info-outline"></span> Plugin Settings</h2>
					<p>Announcement bar text and builder help content shown to customers.</p>
				</div>
				<button type="button" class="pset-collapse-btn" aria-expanded="true" aria-controls="pset-body-plugin-settings"><span class="dashicons dashicons-arrow-up-alt2"></span></button>
			</div>
			<div class="pset-card__body" id="pset-body-plugin-settings">
				<div class="pset-grid pset-grid--wide">
					<div class="pset-field pset-field--full">
						<label>Demo / Announcement Bar</label>
						<p class="pset-desc">If set, this message appears as an announcement bar above all pages using PizzaLayer. Leave empty to disable.</p>
						<textarea name="pizzalayer_setting_settings_demonotice" class="pset-textarea" rows="2" placeholder="e.g. Now open for online ordering! Order before 8pm for same-day delivery."><?php echo esc_textarea( $g('pizzalayer_setting_settings_demonotice') ); ?></textarea>
					</div>
					<div class="pset-field pset-field--full">
						<label>Help Screen Content</label>
						<p class="pset-desc">Content shown in the builder's help modal/tab when customers click the help icon.</p>
						<textarea name="pizzalayer_setting_global_help_content" class="pset-textarea" rows="4"><?php echo esc_textarea( $g('pizzalayer_setting_global_help_content') ); ?></textarea>
					</div>
				</div>
			</div>
		</div>

		<!-- ══ Section: Template Settings ══════════════════════════ -->
		<?php if ( $active_template ) : ?>
		<div class="pset-card pset-card--template">
			<div class="pset-card__head pset-card__head--collapsible" data-pset-toggle="template-settings">
				<div>
					<h2>
						<span class="dashicons dashicons-admin-appearance"></span>
						<?php echo esc_html( ucwords( str_replace( '-', ' ', $active_template ) ) ); ?> Template Settings
						<span class="pset-card__badge">Active Template</span>
					</h2>
				</div>
				<button type="button" class="pset-collapse-btn" aria-expanded="true" aria-controls="pset-body-template-settings">
					<span class="dashicons dashicons-arrow-up-alt2"></span>
				</button>
			</div>
			<div class="pset-card__body" id="pset-body-template-settings">
				<?php if ( ! empty( $template_settings ) && is_array( $template_settings ) ) : ?>
					<p class="pset-desc" style="margin-bottom:16px;">These settings apply only to the <strong><?php echo esc_html( ucwords( str_replace( '-', ' ', $active_template ) ) ); ?></strong> template. Switching templates will show that template's settings instead.</p>
					<?php if ( $active_template === 'metro' ) : ?>
					<div class="pset-scheme-row">
						<span class="pset-scheme-label">Color Schemes:</span>
						<div class="pset-scheme-chips" id="pset-scheme-chips">
							<?php foreach ( $this->get_metro_color_schemes() as $scheme ) :
								$safe = esc_attr( wp_json_encode( $scheme['colors'] ) );
							?>
							<button type="button" class="pset-scheme-chip"
							        data-scheme="<?php echo $safe; ?>"
							        title="<?php echo esc_attr( $scheme['name'] ); ?>">
								<span class="pset-scheme-chip__swatches">
									<?php foreach ( $scheme['colors'] as $c ) : ?>
									<span class="pset-scheme-chip__dot" style="background:<?php echo esc_attr( $c ); ?>;"></span>
									<?php endforeach; ?>
								</span>
								<span class="pset-scheme-chip__name"><?php echo esc_html( $scheme['name'] ); ?></span>
							</button>
							<?php endforeach; ?>
						</div>
					</div>
					<?php endif; ?>
					<div class="pset-grid pset-grid--wide">
					<?php foreach ( $template_settings as $field ) :
						if ( empty( $field['key'] ) || empty( $field['type'] ) ) { continue; }
						$fkey = esc_attr( $field['key'] );
						$fval = (string) get_option( $field['key'], $field['default'] ?? '' );
						$flabel = $field['label'] ?? $field['key'];
						$fdesc  = $field['desc']  ?? '';
					?>
					<div class="pset-field<?php echo ( $field['type'] === 'textarea' || $field['type'] === 'text_wide' ) ? ' pset-field--full' : ''; ?>">
						<label><?php echo esc_html( $flabel ); ?></label>
						<?php if ( $fdesc ) : ?>
						<p class="pset-desc"><?php echo esc_html( $fdesc ); ?></p>
						<?php endif; ?>
						<?php if ( $field['type'] === 'text' || $field['type'] === 'text_wide' ) : ?>
							<input type="text" name="<?php echo $fkey; ?>" value="<?php echo esc_attr( $fval ); ?>" class="pset-input<?php echo $field['type'] === 'text_wide' ? ' pset-input--wide' : ''; ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>">
						<?php elseif ( $field['type'] === 'number' ) : ?>
							<input type="number" name="<?php echo $fkey; ?>" value="<?php echo esc_attr( $fval ); ?>" class="pset-input" min="<?php echo esc_attr( (string)( $field['min'] ?? '' ) ); ?>" max="<?php echo esc_attr( (string)( $field['max'] ?? '' ) ); ?>" step="<?php echo esc_attr( (string)( $field['step'] ?? '1' ) ); ?>">
						<?php elseif ( $field['type'] === 'color' ) : ?>
							<div class="pset-color-wrap">
								<input type="color" name="<?php echo $fkey; ?>" id="pset-color-<?php echo $fkey; ?>"
								       value="<?php echo esc_attr( $fval ?: ( $field['default'] ?? '#000000' ) ); ?>" class="pset-color">
								<?php if ( ! empty( $field['default'] ) ) : ?>
								<button type="button" class="pset-color-revert"
								        data-default="<?php echo esc_attr( $field['default'] ); ?>"
								        data-target="pset-color-<?php echo $fkey; ?>"
								        title="Revert to default (<?php echo esc_attr( $field['default'] ); ?>)">
									<span class="dashicons dashicons-image-rotate"></span>
								</button>
								<span class="pset-color-default-swatch"
								      style="background:<?php echo esc_attr( $field['default'] ); ?>;"
								      title="Default: <?php echo esc_attr( $field['default'] ); ?>"></span>
								<?php endif; ?>
							</div>
						<?php elseif ( $field['type'] === 'select' ) : ?>
							<select name="<?php echo $fkey; ?>" class="pset-select">
								<?php foreach ( $field['options'] ?? [] as $ov => $ol ) : ?>
								<option value="<?php echo esc_attr( $ov ); ?>"<?php selected( $fval, $ov ); ?>><?php echo esc_html( $ol ); ?></option>
								<?php endforeach; ?>
							</select>
						<?php elseif ( $field['type'] === 'toggle' ) : ?>
							<label class="pset-toggle">
								<input type="hidden" name="<?php echo $fkey; ?>" value="no">
								<input type="checkbox" name="<?php echo $fkey; ?>" value="yes"<?php checked( $fval, 'yes' ); ?>>
								<span class="pset-toggle__track"><span class="pset-toggle__thumb"></span></span>
								<span class="pset-toggle__label"><?php echo esc_html( $field['toggle_label'] ?? 'Enabled' ); ?></span>
							</label>
						<?php elseif ( $field['type'] === 'textarea' ) : ?>
							<textarea name="<?php echo $fkey; ?>" class="pset-textarea" rows="<?php echo esc_attr( (string)( $field['rows'] ?? 3 ) ); ?>"><?php echo esc_textarea( $fval ); ?></textarea>
						<?php elseif ( $field['type'] === 'radio' ) : ?>
							<div class="pset-radio-group">
								<?php foreach ( $field['options'] ?? [] as $ov => $ol ) : ?>
								<label class="pset-radio-label">
									<input type="radio" name="<?php echo $fkey; ?>" value="<?php echo esc_attr( $ov ); ?>"<?php checked( $fval, $ov ); ?>>
									<?php echo esc_html( $ol ); ?>
								</label>
								<?php endforeach; ?>
							</div>
						<?php elseif ( $field['type'] === 'range' ) : ?>
							<div class="pset-range-wrap">
								<input type="range" name="<?php echo $fkey; ?>" value="<?php echo esc_attr( $fval ); ?>" min="<?php echo esc_attr( (string)( $field['min'] ?? 0 ) ); ?>" max="<?php echo esc_attr( (string)( $field['max'] ?? 100 ) ); ?>" step="<?php echo esc_attr( (string)( $field['step'] ?? 1 ) ); ?>" class="pset-range" oninput="this.nextElementSibling.textContent=this.value">
								<span class="pset-range__val"><?php echo esc_html( $fval ); ?></span>
							</div>
						<?php endif; ?>
					</div>
					<?php endforeach; ?>
					</div>
				<?php else : ?>
					<div class="pset-tpl-empty">
						<span class="dashicons dashicons-admin-appearance"></span>
						<p>The <strong><?php echo esc_html( ucwords( str_replace( '-', ' ', $active_template ) ) ); ?></strong> template has no customizable settings defined yet.</p>
						<p class="pset-desc">Template authors can add settings by returning an array from <code>pztp-template-options.php</code>.</p>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php else : ?>
		<div class="pset-card">
			<div class="pset-card__head"><h2><span class="dashicons dashicons-admin-appearance"></span> Template Settings</h2></div>
			<div class="pset-card__body">
				<p class="pset-desc">No template selected. <a href="<?php echo esc_url( admin_url('admin.php?page=pizzalayer-template') ); ?>">Choose a template</a> to see its settings here.</p>
			</div>
		</div>
		<?php endif; ?>

		</div><!-- /.pset-main -->

		<!-- ══ Sidebar ═══════════════════════════════════════════════ -->
		<div class="pset-sidebar">
			<div class="pset-save-card">
				<button type="submit" class="button button-primary pset-save-btn">
					<span class="dashicons dashicons-saved"></span> Save Settings
				</button>
			</div>
			<div class="pset-info-card">
				<h3>Quick Links</h3>
				<ul>
					<li><a href="<?php echo esc_url( admin_url('admin.php?page=pizzalayer-template') ); ?>"><span class="dashicons dashicons-admin-appearance"></span> Template</a></li>
					<li><a href="<?php echo esc_url( admin_url('admin.php?page=pizzalayer-shortcodes') ); ?>"><span class="dashicons dashicons-editor-code"></span> Shortcode Generator</a></li>
					<li><a href="<?php echo esc_url( admin_url('admin.php?page=pizzalayer-setup') ); ?>"><span class="dashicons dashicons-welcome-learn-more"></span> Setup Guide</a></li>
				</ul>
			</div>
			<div class="pset-info-card pset-info-card--tip">
				<span class="dashicons dashicons-lightbulb"></span>
				<p>Setting a default crust and sauce ensures the builder shows something immediately when it loads, even before the customer makes selections.</p>
			</div>
		</div>

		</div><!-- /.pset-layout -->
		</form>
		</div><!-- /.wrap -->
		<?php $this->render_styles_sidebar(); ?>
		<?php
	}

	private function save_settings(): void {
		$text_options = [
			'pizzalayer_setting_pizza_size_max',
			'pizzalayer_setting_pizza_size_min',
			'pizzalayer_setting_pizza_border',
			'pizzalayer_setting_crust_aspectratio',
			'pizzalayer_setting_crust_padding',
			'pizzalayer_setting_sauce_padding',
			'pizzalayer_cheese_setting_cheesedistance',
			'pizzalayer_setting_cheese_padding',
			'pizzalayer_setting_branding_altlogo',
			'pizzalayer_setting_pizza_aspect',
			'pizzalayer_setting_pizza_radius',
		];
		$select_options = [
			'pizzalayer_setting_crust_defaultcrust',
			'pizzalayer_setting_sauce_defaultsauce',
			'pizzalayer_setting_cheese_defaultcheese',
			'pizzalayer_setting_drizzle_defaultdrizzle',
			'pizzalayer_setting_cut_defaultcut',
			'pizzalayer_setting_topping_fractions',
			'pizzalayer_setting_show_thumbnails',
			'pizzalayer_setting_element_style_layers',
			'pizzalayer_setting_element_style_toppings',
			'pizzalayer_setting_element_style_topping_choice_menu',
			'pizzalayer_setting_pizza_shape',
			'pizzalayer_setting_layer_anim',
		];
		$color_options = [
			'pizzalayer_setting_pizza_border_color',
			'pizzalayer_setting_global_color',
		];
		$number_options = [ 'pizzalayer_setting_topping_maxtoppings', 'pizzalayer_setting_layer_anim_speed' ];
		$textarea_options = [
			'pizzalayer_setting_branding_menu_title',
			'pizzalayer_setting_branding_header_custom_content',
			'pizzalayer_setting_settings_demonotice',
			'pizzalayer_setting_global_help_content',
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
		foreach ( $textarea_options as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				update_option( $key, sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) );
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
				<option value="">— None / Plugin default —</option>
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
				<span class="pset-layer-trigger__name pset-hint">None selected</span>
				<?php endif; ?>
				<span class="pset-layer-trigger__edit dashicons dashicons-edit"></span>
			</button>
		</div>
		<?php
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

	private function render_styles(): void { ?>
	<style>
	.pset-wrap { max-width: 1100px; }
	.pset-header { display:flex; align-items:center; gap:16px; background:linear-gradient(135deg,#1a1e23,#2d3748); color:#fff; border-radius:10px; padding:22px 28px; margin-bottom:20px; }
	.pset-header__icon { font-size:36px !important; width:36px !important; height:36px !important; color:#ff6b35; flex-shrink:0; }
	.pset-header__title { margin:0; font-size:22px; font-weight:700; color:#fff; }
	.pset-header__sub { margin:3px 0 0; color:#8d97a5; font-size:13px; }
	.pset-layout { display:flex; gap:20px; align-items:flex-start; }
	.pset-main { flex:1; min-width:0; }
	.pset-card { background:#fff; border:1px solid #e0e3e7; border-radius:10px; margin-bottom:20px; overflow:hidden; }
	.pset-card--template { border-color:#8c5af8; }
	.pset-card--template .pset-card__head { background:linear-gradient(135deg,#f5f0ff,#ede8ff); border-bottom-color:#d5c8f5; }
	.pset-card__head { padding:16px 22px 10px; border-bottom:1px solid #f0f0f0; }
	.pset-card__head--collapsible { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; padding:14px 18px; cursor:default; }
	.pset-card__head--collapsible > div { flex:1; min-width:0; }
	.pset-card__head h2 { margin:0 0 3px; font-size:15px; display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
	.pset-card__head h2 .dashicons { font-size:17px !important; width:17px !important; height:17px !important; color:#646970; }
	.pset-card--template .pset-card__head h2 .dashicons { color:#8c5af8; }
	.pset-card__head p { margin:0; font-size:12px; color:#646970; }
	.pset-card__badge { font-size:10px; font-weight:700; background:#8c5af820; color:#8c5af8; border:1px solid #8c5af840; border-radius:4px; padding:2px 7px; margin-left:4px; }
	.pset-card__body { padding:18px 22px; }
	.pset-card__body--collapsed { display:none; }
	.pset-collapse-btn { flex-shrink:0; background:none; border:1px solid #e0e3e7; border-radius:6px; cursor:pointer; padding:4px 6px; color:#787c82; transition:background .15s, color .15s, transform .2s; display:flex; align-items:center; }
	.pset-collapse-btn:hover { background:#f0f0f1; color:#1d2023; }
	.pset-collapse-btn[aria-expanded="false"] .dashicons { transform:rotate(180deg); }
	.pset-collapse-btn .dashicons { font-size:14px !important; width:14px !important; height:14px !important; transition:transform .2s; }
	.pset-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(210px,1fr)); gap:14px; }
	.pset-grid--wide { grid-template-columns:1fr; }
	.pset-field { display:flex; flex-direction:column; gap:4px; }
	.pset-field--full { grid-column:1/-1; }
	.pset-field label { font-size:12px; font-weight:600; color:#1d2023; }
	.pset-hint { font-weight:400; color:#787c82; }
	.pset-desc { margin:0 0 4px; font-size:11.5px; color:#787c82; }
	.pset-desc code { background:#f0f0f1; padding:1px 4px; border-radius:3px; font-size:11px; }
	.pset-input,.pset-select { padding:7px 10px; border:1px solid #8c8f94; border-radius:4px; font-size:13px; font-family:inherit; width:100%; }
	.pset-input--wide { max-width:100%; }
	.pset-color { height:36px; width:60px; padding:2px 4px; border:1px solid #8c8f94; border-radius:4px; cursor:pointer; }
	/* Color field with revert button */
	.pset-color-wrap { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
	.pset-color-revert { background:none; border:1px solid #e0e3e7; border-radius:4px; cursor:pointer; padding:4px 6px; color:#646970; display:flex; align-items:center; transition:background .15s,color .15s,border-color .15s; }
	.pset-color-revert:hover { background:#f0f0f1; color:#d63638; border-color:#d63638; }
	.pset-color-revert .dashicons { font-size:14px !important; width:14px !important; height:14px !important; }
	.pset-color-default-swatch { display:inline-block; width:20px; height:20px; border-radius:4px; border:1px solid #ccd0d4; flex-shrink:0; cursor:help; }
	.pset-textarea { width:100%; padding:8px 10px; border:1px solid #8c8f94; border-radius:4px; font-size:13px; font-family:inherit; resize:vertical; }
	/* Toggle switch */
	.pset-toggle { display:flex; align-items:center; gap:10px; cursor:pointer; user-select:none; }
	.pset-toggle input[type="checkbox"] { position:absolute; opacity:0; width:0; height:0; }
	.pset-toggle input[type="hidden"] { display:none; }
	.pset-toggle__track { width:40px; height:22px; background:#ccd0d4; border-radius:11px; position:relative; transition:background .2s; flex-shrink:0; }
	.pset-toggle input[type="checkbox"]:checked ~ .pset-toggle__track { background:#2271b1; }
	.pset-toggle__thumb { width:16px; height:16px; background:#fff; border-radius:50%; position:absolute; top:3px; left:3px; transition:transform .2s; box-shadow:0 1px 3px rgba(0,0,0,.3); }
	.pset-toggle input[type="checkbox"]:checked ~ .pset-toggle__track .pset-toggle__thumb { transform:translateX(18px); }
	.pset-toggle__label { font-size:12px; color:#3c434a; }
	/* Radio group */
	.pset-radio-group { display:flex; flex-wrap:wrap; gap:8px 16px; margin-top:4px; }
	.pset-radio-label { display:flex; align-items:center; gap:6px; font-size:13px; cursor:pointer; }
	/* Range */
	.pset-range-wrap { display:flex; align-items:center; gap:10px; }
	.pset-range { flex:1; }
	.pset-range__val { font-size:13px; font-weight:600; color:#1d2023; min-width:32px; text-align:center; }
	/* Template empty state */
	.pset-tpl-empty { text-align:center; padding:28px 20px; color:#646970; }
	.pset-tpl-empty .dashicons { font-size:36px !important; width:36px !important; height:36px !important; color:#c3c4c7; margin-bottom:10px; }
	.pset-tpl-empty p { margin:0 0 6px; font-size:13px; }

	/* ── Layer grid ──────────────────────────────────────────────── */
	.pset-grid--layers { grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap:12px; }

	/* ── Layer picker trigger button ─────────────────────────────── */
	.pset-layer-trigger {
		display:flex; flex-direction:column; align-items:center; justify-content:center;
		gap:6px; width:100%; aspect-ratio:1/1; min-height:110px;
		background:#f6f7f8; border:2px dashed #c3c4c7; border-radius:10px;
		cursor:pointer; padding:10px; transition:border-color .15s,background .15s;
		position:relative; overflow:hidden; text-align:center;
	}
	.pset-layer-trigger:hover { border-color:#2271b1; background:#f0f5fc; }
	.pset-layer-trigger--has-value { border-style:solid; border-color:#d0d5dd; background:#fff; }
	.pset-layer-trigger--has-value:hover { border-color:#2271b1; }
	.pset-layer-trigger__thumb { width:68px; height:68px; border-radius:6px; overflow:hidden; flex-shrink:0; }
	.pset-layer-trigger__thumb img { width:100%; height:100%; object-fit:cover; display:block; }
	.pset-layer-trigger__name { font-size:11px; font-weight:600; color:#1d2023; line-height:1.3; word-break:break-word; }
	.pset-layer-trigger .pset-hint { color:#787c82; font-weight:400; }
	.pset-layer-trigger__placeholder.dashicons { font-size:28px !important; width:28px !important; height:28px !important; color:#c3c4c7; }
	.pset-layer-trigger__edit {
		position:absolute; top:5px; right:5px;
		font-size:13px !important; width:13px !important; height:13px !important;
		color:#787c82; opacity:0; transition:opacity .15s;
	}
	.pset-layer-trigger:hover .pset-layer-trigger__edit,
	.pset-layer-trigger--has-value .pset-layer-trigger__edit { opacity:1; }

	/* ── Layer picker modal ───────────────────────────────────────── */
	.pset-modal {
		position:fixed; inset:0; z-index:999999;
		display:flex; align-items:center; justify-content:center;
	}
	.pset-modal__backdrop {
		position:absolute; inset:0; background:rgba(0,0,0,.55); backdrop-filter:blur(2px);
	}
	.pset-modal__box {
		position:relative; background:#fff; border-radius:14px;
		width:min(680px,92vw); max-height:82vh;
		display:flex; flex-direction:column; overflow:hidden;
		box-shadow:0 20px 60px rgba(0,0,0,.25);
		animation:pset-modal-in .18s cubic-bezier(0.34,1.3,0.64,1);
	}
	@keyframes pset-modal-in {
		from { opacity:0; transform:scale(.92) translateY(10px); }
		to   { opacity:1; transform:scale(1) translateY(0); }
	}
	.pset-modal__head {
		display:flex; align-items:center; justify-content:space-between;
		padding:16px 20px; border-bottom:1px solid #f0f0f0; flex-shrink:0;
	}
	.pset-modal__title { margin:0; font-size:15px; font-weight:700; color:#1d2023; }
	.pset-modal__close {
		background:none; border:none; cursor:pointer; font-size:22px; line-height:1;
		color:#646970; padding:2px 6px; border-radius:4px; transition:background .12s,color .12s;
	}
	.pset-modal__close:hover { background:#f0f0f1; color:#d63638; }
	.pset-modal__search-wrap {
		position:relative; padding:12px 16px 8px; flex-shrink:0;
	}
	.pset-modal__search-icon {
		position:absolute; left:24px; top:50%; transform:translateY(-50%);
		font-size:16px !important; width:16px !important; height:16px !important;
		color:#787c82; pointer-events:none; margin-top:2px;
	}
	.pset-modal__search {
		width:100%; padding:8px 10px 8px 34px;
		border:1px solid #8c8f94; border-radius:6px; font-size:13px;
	}
	.pset-modal__search:focus { border-color:#2271b1; outline:none; box-shadow:0 0 0 2px rgba(34,113,177,.2); }
	.pset-modal__grid {
		flex:1; overflow-y:auto; padding:12px 16px;
		display:grid; grid-template-columns:repeat(auto-fill,minmax(110px,1fr)); gap:10px;
	}
	.pset-modal__item {
		display:flex; flex-direction:column; align-items:center; gap:6px;
		padding:10px 8px; border:2px solid #e0e3e7; border-radius:10px;
		cursor:pointer; background:#fafafa; text-align:center;
		transition:border-color .12s, background .12s, transform .1s;
	}
	.pset-modal__item:hover { border-color:#2271b1; background:#f0f5fc; transform:translateY(-1px); }
	.pset-modal__item--active { border-color:#2271b1; background:#eef4fb; }
	.pset-modal__item img {
		width:72px; height:72px; object-fit:cover; border-radius:6px;
		border:1px solid #e0e3e7;
	}
	.pset-modal__item__no-img {
		width:72px; height:72px; border-radius:6px; background:#f0f0f1;
		display:flex; align-items:center; justify-content:center;
		border:1px solid #e0e3e7;
	}
	.pset-modal__item__no-img .dashicons { font-size:28px !important; width:28px !important; height:28px !important; color:#c3c4c7; }
	.pset-modal__item__name { font-size:11px; font-weight:600; color:#1d2023; line-height:1.3; word-break:break-word; }
	.pset-modal__foot {
		padding:10px 16px; border-top:1px solid #f0f0f0; flex-shrink:0;
		display:flex; align-items:center; gap:8px;
	}
	.pset-modal__clear { display:flex !important; align-items:center; gap:5px; color:#787c82 !important; }
	.pset-modal__clear .dashicons { font-size:13px !important; width:13px !important; height:13px !important; }
	.pset-modal__empty { grid-column:1/-1; text-align:center; color:#787c82; padding:20px; font-size:13px; }

	/* ── Color scheme row ─────────────────────────────────────────── */
	.pset-scheme-row {
		display:flex; align-items:center; gap:10px; flex-wrap:wrap;
		background:#f8f7ff; border:1px solid #e4deff; border-radius:8px;
		padding:10px 14px; margin-bottom:16px;
	}
	.pset-scheme-label { font-size:11.5px; font-weight:700; color:#5b3fd4; white-space:nowrap; flex-shrink:0; }
	.pset-scheme-chips { display:flex; flex-wrap:wrap; gap:6px; }
	.pset-scheme-chip {
		display:flex; align-items:center; gap:5px; padding:4px 8px 4px 5px;
		border:1.5px solid #d5c8f5; border-radius:20px; background:#fff;
		cursor:pointer; font-size:11px; font-weight:600; color:#3c2d8c;
		transition:border-color .12s, background .12s, transform .1s;
	}
	.pset-scheme-chip:hover { border-color:#8c5af8; background:#f2edff; transform:translateY(-1px); }
	.pset-scheme-chip--active { border-color:#8c5af8; background:#ede8ff; }
	.pset-scheme-chip__swatches { display:flex; gap:2px; }
	.pset-scheme-chip__dot { width:12px; height:12px; border-radius:50%; border:1px solid rgba(0,0,0,.12); flex-shrink:0; }
	.pset-scheme-chip__name { white-space:nowrap; }
	</style>
	<script>
	document.addEventListener('DOMContentLoaded', function() {
		var storageKey = 'pset_collapsed_sections';
		var collapsed = {};
		try { collapsed = JSON.parse(localStorage.getItem(storageKey) || '{}'); } catch(e) {}

		document.querySelectorAll('.pset-card__head--collapsible').forEach(function(head) {
			var slug = head.getAttribute('data-pset-toggle');
			var btn  = head.querySelector('.pset-collapse-btn');
			var body = document.getElementById('pset-body-' + slug);
			if (!btn || !body) return;

			if (collapsed[slug]) {
				body.classList.add('pset-card__body--collapsed');
				btn.setAttribute('aria-expanded', 'false');
			}

			btn.addEventListener('click', function() {
				var isOpen = btn.getAttribute('aria-expanded') === 'true';
				if (isOpen) {
					body.classList.add('pset-card__body--collapsed');
					btn.setAttribute('aria-expanded', 'false');
					collapsed[slug] = true;
				} else {
					body.classList.remove('pset-card__body--collapsed');
					btn.setAttribute('aria-expanded', 'true');
					delete collapsed[slug];
				}
				try { localStorage.setItem(storageKey, JSON.stringify(collapsed)); } catch(e) {}
			});
		});

		// Color revert buttons
		document.querySelectorAll('.pset-color-revert').forEach(function(btn) {
			btn.addEventListener('click', function() {
				var def    = btn.getAttribute('data-default');
				var target = btn.getAttribute('data-target');
				var input  = document.getElementById(target);
				if (input && def) {
					input.value = def;
					input.dispatchEvent(new Event('input'));
					input.dispatchEvent(new Event('change'));
				}
			});
		});

		// ── Layer picker modal ────────────────────────────────────────
		var modal       = document.getElementById('pset-layer-modal');
		var modalTitle  = document.getElementById('pset-modal-title');
		var modalGrid   = document.getElementById('pset-modal-grid');
		var modalSearch = document.getElementById('pset-modal-search');
		var modalClear  = modal ? modal.querySelector('.pset-modal__clear') : null;
		var modalClose  = modal ? modal.querySelector('.pset-modal__close') : null;
		var modalBack   = modal ? modal.querySelector('.pset-modal__backdrop') : null;
		var activeField = null;  // the .pset-layer-picker-field currently being edited

		function buildModalGrid(items, currentSlug, searchVal) {
			modalGrid.innerHTML = '';
			var q = (searchVal || '').toLowerCase().trim();
			var filtered = items.filter(function(it) { return !q || it.title.toLowerCase().indexOf(q) !== -1; });
			if (!filtered.length) {
				modalGrid.innerHTML = '<p class="pset-modal__empty">No items found.</p>';
				return;
			}
			filtered.forEach(function(item) {
				var div = document.createElement('div');
				div.className = 'pset-modal__item' + (item.slug === currentSlug ? ' pset-modal__item--active' : '');
				div.dataset.slug = item.slug;
				var imgHtml = item.thumb
					? '<img src="' + item.thumb + '" alt="' + item.title.replace(/"/g,'&quot;') + '" loading="lazy">'
					: '<span class="pset-modal__item__no-img"><span class="dashicons dashicons-format-image"></span></span>';
				div.innerHTML = imgHtml + '<span class="pset-modal__item__name">' + item.title + '</span>';
				div.addEventListener('click', function() {
					if (!activeField) return;
					applyLayerChoice(activeField, item);
					closeModal();
				});
				modalGrid.appendChild(div);
			});
		}

		function openModal(field) {
			if (!modal) return;
			activeField = field;
			var label   = field.getAttribute('data-picker-label') || 'Choose a layer';
			var items   = JSON.parse(field.getAttribute('data-picker-items') || '[]');
			var current = field.querySelector('input[type=hidden]').value;
			modalTitle.textContent = 'Choose: ' + label;
			modalSearch.value = '';
			buildModalGrid(items, current, '');
			modal.style.display = 'flex';
			document.body.style.overflow = 'hidden';
			setTimeout(function() { modalSearch.focus(); }, 50);
		}

		function closeModal() {
			if (!modal) return;
			modal.style.display = 'none';
			document.body.style.overflow = '';
			activeField = null;
		}

		function applyLayerChoice(field, item) {
			var hidden  = field.querySelector('input[type=hidden]');
			var trigger = field.querySelector('.pset-layer-trigger');
			if (!hidden || !trigger) return;
			hidden.value = item.slug;
			var thumbHtml = item.thumb
				? '<span class="pset-layer-trigger__thumb"><img src="' + item.thumb + '" alt="' + item.title.replace(/"/g,'&quot;') + '"></span>'
				: '<span class="pset-layer-trigger__placeholder dashicons dashicons-format-image"></span>';
			trigger.innerHTML = thumbHtml
				+ '<span class="pset-layer-trigger__name">' + item.title + '</span>'
				+ '<span class="pset-layer-trigger__edit dashicons dashicons-edit"></span>';
			trigger.classList.add('pset-layer-trigger--has-value');
		}

		function clearLayerChoice(field) {
			var hidden  = field.querySelector('input[type=hidden]');
			var trigger = field.querySelector('.pset-layer-trigger');
			if (!hidden || !trigger) return;
			hidden.value = '';
			trigger.innerHTML = '<span class="pset-layer-trigger__placeholder dashicons dashicons-plus-alt2"></span>'
				+ '<span class="pset-layer-trigger__name pset-hint">None selected</span>'
				+ '<span class="pset-layer-trigger__edit dashicons dashicons-edit"></span>';
			trigger.classList.remove('pset-layer-trigger--has-value');
		}

		// Wire up trigger buttons
		document.querySelectorAll('.pset-layer-picker-field').forEach(function(field) {
			field.querySelector('.pset-layer-trigger').addEventListener('click', function() {
				openModal(field);
			});
		});

		if (modalClose) modalClose.addEventListener('click', closeModal);
		if (modalBack)  modalBack.addEventListener('click', closeModal);

		if (modalSearch) {
			modalSearch.addEventListener('input', function() {
				if (!activeField) return;
				var items   = JSON.parse(activeField.getAttribute('data-picker-items') || '[]');
				var current = activeField.querySelector('input[type=hidden]').value;
				buildModalGrid(items, current, modalSearch.value);
			});
		}

		if (modalClear) {
			modalClear.addEventListener('click', function() {
				if (activeField) clearLayerChoice(activeField);
				closeModal();
			});
		}

		document.addEventListener('keydown', function(e) {
			if (e.key === 'Escape' && modal && modal.style.display !== 'none') closeModal();
		});

		// ── Color scheme presets ─────────────────────────────────────
		document.querySelectorAll('.pset-scheme-chip').forEach(function(chip) {
			chip.addEventListener('click', function() {
				var colors;
				try { colors = JSON.parse(chip.getAttribute('data-scheme')); } catch(e) { return; }
				// colors = [accent, bg, card_bg]
				var keys = [
					'metro_setting_accent_color',
					'metro_setting_background_color',
					'metro_setting_card_bg_color'
				];
				colors.forEach(function(hex, i) {
					var input = document.getElementById('pset-color-' + keys[i]);
					if (input) {
						input.value = hex;
						input.dispatchEvent(new Event('input'));
						input.dispatchEvent(new Event('change'));
					}
				});
				document.querySelectorAll('.pset-scheme-chip').forEach(function(c) {
					c.classList.remove('pset-scheme-chip--active');
				});
				chip.classList.add('pset-scheme-chip--active');
			});
		});
	});
	</script>
	<?php }

	private function render_styles_sidebar(): void { ?>
	<style>
	.pset-sidebar { width: 220px; flex-shrink:0; }
	.pset-save-card { background:#2271b1; border-radius:10px; padding:16px; margin-bottom:16px; }
	.pset-save-btn { width:100% !important; justify-content:center; display:flex !important; align-items:center; gap:6px; background:#fff !important; color:#2271b1 !important; border-color:#fff !important; font-weight:700 !important; }
	.pset-save-btn .dashicons { font-size:15px !important; width:15px !important; height:15px !important; }
	.pset-info-card { background:#fff; border:1px solid #e0e3e7; border-radius:10px; padding:14px 16px; margin-bottom:14px; font-size:13px; }
	.pset-info-card h3 { margin:0 0 10px; font-size:13px; font-weight:700; }
	.pset-info-card ul { margin:0; padding:0; list-style:none; }
	.pset-info-card li { border-bottom:1px solid #f0f0f0; }
	.pset-info-card li:last-child { border-bottom:none; }
	.pset-info-card a { display:flex; align-items:center; gap:7px; padding:7px 2px; color:#2271b1; text-decoration:none; font-size:12px; }
	.pset-info-card .dashicons { font-size:14px !important; width:14px !important; height:14px !important; color:#646970; }
	.pset-info-card--tip { display:flex; gap:10px; align-items:flex-start; background:#fffbf0; border-color:#f0b849; }
	.pset-info-card--tip .dashicons { color:#f0b849; font-size:18px !important; width:18px !important; height:18px !important; flex-shrink:0; }
	.pset-info-card--tip p { margin:0; font-size:12px; color:#3c434a; }
	</style>
	<?php }
}
