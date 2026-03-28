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
			<div class="pset-card__head"><h2><span class="dashicons dashicons-category"></span> Default Layers</h2>
			<p>These layers are pre-selected when the builder loads, unless overridden by the shortcode attribute.</p></div>
			<div class="pset-card__body">
				<div class="pset-grid">
					<?php $this->render_select( 'Default Crust', 'pizzalayer_setting_crust_defaultcrust', $crusts, $g('pizzalayer_setting_crust_defaultcrust') ); ?>
					<?php $this->render_select( 'Default Sauce', 'pizzalayer_setting_sauce_defaultsauce', $sauces, $g('pizzalayer_setting_sauce_defaultsauce') ); ?>
					<?php $this->render_select( 'Default Cheese', 'pizzalayer_setting_cheese_defaultcheese', $cheeses, $g('pizzalayer_setting_cheese_defaultcheese') ); ?>
					<?php $this->render_select( 'Default Drizzle', 'pizzalayer_setting_drizzle_defaultdrizzle', $drizzles, $g('pizzalayer_setting_drizzle_defaultdrizzle') ); ?>
					<?php $this->render_select( 'Default Cut', 'pizzalayer_setting_cut_defaultcut', $cuts, $g('pizzalayer_setting_cut_defaultcut') ); ?>
				</div>
			</div>
		</div>

		<!-- ══ Section: Toppings ═════════════════════════════════════ -->
		<div class="pset-card">
			<div class="pset-card__head"><h2><span class="dashicons dashicons-star-filled"></span> Toppings</h2></div>
			<div class="pset-card__body">
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
			<div class="pset-card__head"><h2><span class="dashicons dashicons-pizza"></span> Pizza Display</h2>
			<p>Control the size and appearance of the pizza visualizer circle.</p></div>
			<div class="pset-card__body">
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
						<input type="color" name="pizzalayer_setting_pizza_border_color"
						       value="<?php echo esc_attr( $g('pizzalayer_setting_pizza_border_color', '#d4a04c') ); ?>" class="pset-color">
					</div>
					<div class="pset-field">
						<label>Accent Color</label>
						<p class="pset-desc">Global accent color used in templates.</p>
						<input type="color" name="pizzalayer_setting_global_color"
						       value="<?php echo esc_attr( $g('pizzalayer_setting_global_color', '#ff6b35') ); ?>" class="pset-color">
					</div>
				</div>
			</div>
		</div>

		<!-- ══ Section: Pizza Shape ══════════════════════════════════ -->
		<div class="pset-card">
			<div class="pset-card__head"><h2><span class="dashicons dashicons-image-crop"></span> Pizza Shape</h2>
			<p>Controls the shape of the pizza preview in the builder. Can be overridden per-shortcode with <code>pizza_shape="..."</code>.</p></div>
			<div class="pset-card__body">
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
				<script>
				(function(){
					function updateShapePreview(){
						var shape  = document.getElementById('pset-pizza-shape').value;
						var aspect = document.querySelector('[name="pizzalayer_setting_pizza_aspect"]').value || '4 / 3';
						var radius = document.querySelector('[name="pizzalayer_setting_pizza_radius"]').value || '8px';
						var el     = document.getElementById('pset-shape-preview');
						var w = 80, h = 80;
						if (shape === 'round')     { el.style.borderRadius='50%'; el.style.width=w+'px'; el.style.height=w+'px'; }
						if (shape === 'square')    { el.style.borderRadius='8px'; el.style.width=w+'px'; el.style.height=w+'px'; }
						if (shape === 'rectangle') {
							var parts = aspect.replace(/\s/g,'').split('/');
							var ar = parts.length===2 ? parseFloat(parts[0])/parseFloat(parts[1]) : 1.33;
							el.style.borderRadius='12px'; el.style.width=(h*ar)+'px'; el.style.height=h+'px';
						}
						if (shape === 'custom')    { el.style.borderRadius=radius; el.style.width=w+'px'; el.style.height=w+'px'; }
					}
					document.getElementById('pset-pizza-shape').addEventListener('change', updateShapePreview);
					document.querySelector('[name="pizzalayer_setting_pizza_aspect"]').addEventListener('input', updateShapePreview);
					document.querySelector('[name="pizzalayer_setting_pizza_radius"]').addEventListener('input', updateShapePreview);
					updateShapePreview();
				})();
				</script>
			</div>
		</div>

		<!-- ══ Section: Layer Animation ══════════════════════════════ -->
		<div class="pset-card">
			<div class="pset-card__head"><h2><span class="dashicons dashicons-controls-play"></span> Layer Animation</h2>
			<p>Animation played when a layer is added to the pizza preview. Can be overridden per-shortcode with <code>layer_anim="..."</code>.</p></div>
			<div class="pset-card__body">
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
				<script>
				(function(){
					var animations = {
						'fade':     function(el){ el.style.transition='none'; el.style.opacity=0; el.style.transform=''; rAF(function(){ rAF(function(){ el.style.transition='opacity 0.35s ease'; el.style.opacity=1; }); }); },
						'scale-in': function(el){ el.style.transition='none'; el.style.opacity=0; el.style.transform='scale(0.4)'; rAF(function(){ rAF(function(){ el.style.transition='opacity 0.4s ease,transform 0.4s cubic-bezier(0.34,1.56,0.64,1)'; el.style.opacity=1; el.style.transform='scale(1)'; }); }); },
						'slide-up': function(el){ el.style.transition='none'; el.style.opacity=0; el.style.transform='translateY(40%)'; rAF(function(){ rAF(function(){ el.style.transition='opacity 0.35s ease,transform 0.35s cubic-bezier(0.22,1,0.36,1)'; el.style.opacity=1; el.style.transform='translateY(0)'; }); }); },
						'flip-in':  function(el){ el.style.transition='none'; el.style.opacity=0; el.style.transform='rotateY(90deg) scale(0.8)'; rAF(function(){ rAF(function(){ el.style.transition='opacity 0.4s ease,transform 0.4s cubic-bezier(0.34,1.2,0.64,1)'; el.style.opacity=1; el.style.transform='rotateY(0) scale(1)'; }); }); },
						'drop-in':  function(el){ el.style.transition='none'; el.style.opacity=0; el.style.transform='translateY(-40%) scale(1.1)'; rAF(function(){ rAF(function(){ el.style.transition='opacity 0.35s ease,transform 0.35s cubic-bezier(0.22,1,0.36,1)'; el.style.opacity=1; el.style.transform='translateY(0) scale(1)'; }); }); },
						'instant':  function(el){ el.style.transition='none'; el.style.opacity=1; el.style.transform=''; },
					};
					function rAF(fn){ requestAnimationFrame(fn); }
					document.getElementById('pset-anim-preview-btn').addEventListener('click', function(){
						var mode = document.getElementById('pset-layer-anim').value;
						var el   = document.getElementById('pset-anim-demo');
						(animations[mode] || animations['fade'])(el);
					});
				})();
				</script>
			</div>
		</div>

		<!-- ══ Section: Crust Options ════════════════════════════════ -->
		<div class="pset-card">
			<div class="pset-card__head"><h2><span class="dashicons dashicons-tag"></span> Crust Options</h2></div>
			<div class="pset-card__body">
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
			<div class="pset-card__head"><h2><span class="dashicons dashicons-admin-generic"></span> Sauce &amp; Cheese Options</h2></div>
			<div class="pset-card__body">
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
			<div class="pset-card__head"><h2><span class="dashicons dashicons-art"></span> UI Element Styles</h2>
			<p>These control the visual style of selection cards in the builder.</p></div>
			<div class="pset-card__body">
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
						<select name="pizzalayer_setting_show_thumbnails" class="pset-select">
							<option value="yes"<?php selected( $g('pizzalayer_setting_show_thumbnails'), 'yes' ); ?>>Yes</option>
							<option value="no"<?php selected( $g('pizzalayer_setting_show_thumbnails'), 'no' ); ?>>No</option>
						</select>
					</div>
				</div>
			</div>
		</div>

		<!-- ══ Section: Branding ════════════════════════════════════ -->
		<div class="pset-card">
			<div class="pset-card__head"><h2><span class="dashicons dashicons-admin-customizer"></span> Branding</h2>
			<p>Content displayed in the builder's branded areas (header, sidebar, above menu icons).</p></div>
			<div class="pset-card__body">
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
			<div class="pset-card__head"><h2><span class="dashicons dashicons-info-outline"></span> Plugin Settings</h2></div>
			<div class="pset-card__body">
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
		$number_options = [ 'pizzalayer_setting_topping_maxtoppings' ];
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
	.pset-card__head { padding:16px 22px 10px; border-bottom:1px solid #f0f0f0; }
	.pset-card__head h2 { margin:0 0 3px; font-size:15px; display:flex; align-items:center; gap:8px; }
	.pset-card__head h2 .dashicons { font-size:17px !important; width:17px !important; height:17px !important; color:#646970; }
	.pset-card__head p { margin:0; font-size:12px; color:#646970; }
	.pset-card__body { padding:18px 22px; }
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
	.pset-textarea { width:100%; padding:8px 10px; border:1px solid #8c8f94; border-radius:4px; font-size:13px; font-family:inherit; resize:vertical; }
	</style>
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
