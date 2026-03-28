<?php
namespace PizzaLayer\Admin;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Shortcode Generator — visual configurator for all PizzaLayer shortcodes.
 * Supports: [pizza_builder], [pizza_static], [pizza_layer], [pizza_layer_info]
 */
class ShortcodeGenerator {

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) { return; }

		// Fetch all CPT posts for dropdowns
		$q_args   = [ 'posts_per_page' => -1, 'post_status' => 'publish', 'orderby' => 'title', 'order' => 'ASC' ];
		$crusts   = get_posts( array_merge( $q_args, [ 'post_type' => 'pizzalayer_crusts'   ] ) );
		$sauces   = get_posts( array_merge( $q_args, [ 'post_type' => 'pizzalayer_sauces'   ] ) );
		$cheeses  = get_posts( array_merge( $q_args, [ 'post_type' => 'pizzalayer_cheeses'  ] ) );
		$toppings = get_posts( array_merge( $q_args, [ 'post_type' => 'pizzalayer_toppings' ] ) );
		$drizzles = get_posts( array_merge( $q_args, [ 'post_type' => 'pizzalayer_drizzles' ] ) );
		$cuts     = get_posts( array_merge( $q_args, [ 'post_type' => 'pizzalayer_cuts'     ] ) );
		$sizes    = get_posts( array_merge( $q_args, [ 'post_type' => 'pizzalayer_sizes'    ] ) );
		$presets  = get_posts( array_merge( $q_args, [ 'post_type' => 'pizzalayer_pizzas'   ] ) );

		// Template list
		$plugin_tpl_dir = PIZZALAYER_TEMPLATES_DIR;
		$theme_tpl_dir  = get_stylesheet_directory() . '/pizzalayer/';
		$templates      = [];
		foreach ( [ $plugin_tpl_dir, $theme_tpl_dir ] as $dir ) {
			if ( is_dir( $dir ) ) {
				foreach ( (array) scandir( $dir ) as $f ) {
					if ( $f !== '.' && $f !== '..' && is_dir( $dir . $f ) ) {
						$templates[] = $f;
					}
				}
			}
		}

		$all_tabs = [ 'crust', 'sauce', 'cheese', 'toppings', 'drizzle', 'slicing', 'yourpizza' ];
		$layer_types = [ 'topping', 'crust', 'sauce', 'cheese', 'drizzle', 'cut' ];

		?>
		<div class="wrap pscg-wrap">
		<?php $this->render_styles(); ?>

		<!-- ══ Header ════════════════════════════════════════════════ -->
		<div class="pscg-header">
			<span class="dashicons dashicons-editor-code pscg-header__icon"></span>
			<div>
				<h1 class="pscg-header__title">Shortcode Generator</h1>
				<p class="pscg-header__sub">Configure any PizzaLayer shortcode and copy it directly to your clipboard.</p>
			</div>
		</div>

		<!-- ══ Shortcode type selector ═══════════════════════════════ -->
		<div class="pscg-type-tabs">
			<button class="pscg-type-tab pscg-type-tab--active" data-type="builder">
				<span class="dashicons dashicons-hammer"></span>
				<span class="pscg-type-tab__label">[pizza_builder]</span>
				<span class="pscg-type-tab__desc">Interactive builder</span>
			</button>
			<button class="pscg-type-tab" data-type="static">
				<span class="dashicons dashicons-format-image"></span>
				<span class="pscg-type-tab__label">[pizza_static]</span>
				<span class="pscg-type-tab__desc">Static preset display</span>
			</button>
			<button class="pscg-type-tab" data-type="layer">
				<span class="dashicons dashicons-image-filter"></span>
				<span class="pscg-type-tab__label">[pizza_layer]</span>
				<span class="pscg-type-tab__desc">Single layer image</span>
			</button>
			<button class="pscg-type-tab" data-type="layerinfo">
				<span class="dashicons dashicons-info-outline"></span>
				<span class="pscg-type-tab__label">[pizza_layer_info]</span>
				<span class="pscg-type-tab__desc">Layer field value</span>
			</button>
		</div>

		<!-- ══ Builder configurator ══════════════════════════════════ -->
		<div class="pscg-form" id="pscg-form-builder">
			<div class="pscg-card">
				<div class="pscg-card__head"><h2>Interactive Builder — <code>[pizza_builder]</code></h2></div>
				<div class="pscg-card__body">
					<div class="pscg-grid">
						<div class="pscg-field">
							<label>Builder ID <span class="pscg-hint">Unique ID — required for multiple builders on one page</span></label>
							<input type="text" class="pscg-input" id="b-id" placeholder="pizza-1">
						</div>
						<div class="pscg-field">
							<label>Template <span class="pscg-hint">Leave blank to use active template</span></label>
							<select class="pscg-select" id="b-template">
								<option value="">— default active template —</option>
								<?php foreach ( $templates as $tpl ) : ?>
								<option value="<?php echo esc_attr( $tpl ); ?>"><?php echo esc_html( $tpl ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="pscg-field">
							<label>Max Toppings <span class="pscg-hint">0 = use plugin setting</span></label>
							<input type="number" class="pscg-input" id="b-max-toppings" min="0" placeholder="0">
						</div>
						<div class="pscg-field">
							<label>Default Crust</label>
							<select class="pscg-select" id="b-default-crust">
								<option value="">— use plugin default —</option>
								<?php foreach ( $crusts as $p ) : $s = sanitize_title( $p->post_title ); ?>
								<option value="<?php echo esc_attr( $s ); ?>"><?php echo esc_html( $p->post_title ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="pscg-field">
							<label>Default Sauce</label>
							<select class="pscg-select" id="b-default-sauce">
								<option value="">— use plugin default —</option>
								<?php foreach ( $sauces as $p ) : $s = sanitize_title( $p->post_title ); ?>
								<option value="<?php echo esc_attr( $s ); ?>"><?php echo esc_html( $p->post_title ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="pscg-field">
							<label>Default Cheese</label>
							<select class="pscg-select" id="b-default-cheese">
								<option value="">— use plugin default —</option>
								<?php foreach ( $cheeses as $p ) : $s = sanitize_title( $p->post_title ); ?>
								<option value="<?php echo esc_attr( $s ); ?>"><?php echo esc_html( $p->post_title ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
					<!-- Tabs visibility -->
					<div class="pscg-field pscg-field--full">
						<label>Visible Tabs <span class="pscg-hint">Uncheck to hide a tab from the builder. Leave all checked to show all.</span></label>
						<div class="pscg-checkboxes">
							<?php foreach ( $all_tabs as $tab ) : ?>
							<label class="pscg-cb-label">
								<input type="checkbox" class="pscg-cb-tab" value="<?php echo esc_attr( $tab ); ?>" checked>
								<?php echo esc_html( ucfirst( $tab ) ); ?>
							</label>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- ══ Static pizza configurator ═════════════════════════════ -->
		<div class="pscg-form" id="pscg-form-static" style="display:none;">
			<div class="pscg-card">
				<div class="pscg-card__head"><h2>Static Display — <code>[pizza_static]</code></h2></div>
				<div class="pscg-card__body">
					<p class="pscg-desc">Renders a non-interactive pizza image. Use a preset or specify layers individually.</p>
					<div class="pscg-grid">
						<div class="pscg-field">
							<label>Preset <span class="pscg-hint">Loads all layers from a saved pizza preset</span></label>
							<select class="pscg-select" id="s-preset">
								<option value="">— no preset (specify layers below) —</option>
								<?php foreach ( $presets as $p ) : $sl = sanitize_title( $p->post_title ); ?>
								<option value="<?php echo esc_attr( $sl ); ?>"><?php echo esc_html( $p->post_title ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="pscg-field">
							<label>Crust</label>
							<select class="pscg-select" id="s-crust">
								<option value="">— none —</option>
								<?php foreach ( $crusts as $p ) : $sl = sanitize_title( $p->post_title ); ?>
								<option value="<?php echo esc_attr( $sl ); ?>"><?php echo esc_html( $p->post_title ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="pscg-field">
							<label>Sauce</label>
							<select class="pscg-select" id="s-sauce">
								<option value="">— none —</option>
								<?php foreach ( $sauces as $p ) : $sl = sanitize_title( $p->post_title ); ?>
								<option value="<?php echo esc_attr( $sl ); ?>"><?php echo esc_html( $p->post_title ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="pscg-field">
							<label>Cheese</label>
							<select class="pscg-select" id="s-cheese">
								<option value="">— none —</option>
								<?php foreach ( $cheeses as $p ) : $sl = sanitize_title( $p->post_title ); ?>
								<option value="<?php echo esc_attr( $sl ); ?>"><?php echo esc_html( $p->post_title ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="pscg-field">
							<label>Drizzle</label>
							<select class="pscg-select" id="s-drizzle">
								<option value="">— none —</option>
								<?php foreach ( $drizzles as $p ) : $sl = sanitize_title( $p->post_title ); ?>
								<option value="<?php echo esc_attr( $sl ); ?>"><?php echo esc_html( $p->post_title ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="pscg-field">
							<label>Cut</label>
							<select class="pscg-select" id="s-cut">
								<option value="">— none —</option>
								<?php foreach ( $cuts as $p ) : $sl = sanitize_title( $p->post_title ); ?>
								<option value="<?php echo esc_attr( $sl ); ?>"><?php echo esc_html( $p->post_title ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
					<!-- Toppings multi-select -->
					<div class="pscg-field pscg-field--full">
						<label>Toppings <span class="pscg-hint">Hold Ctrl / Cmd to select multiple</span></label>
						<select class="pscg-select" id="s-toppings" multiple size="6">
							<?php foreach ( $toppings as $p ) : $sl = sanitize_title( $p->post_title ); ?>
							<option value="<?php echo esc_attr( $sl ); ?>"><?php echo esc_html( $p->post_title ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
			</div>
		</div>

		<!-- ══ Single layer image configurator ═══════════════════════ -->
		<div class="pscg-form" id="pscg-form-layer" style="display:none;">
			<div class="pscg-card">
				<div class="pscg-card__head"><h2>Single Layer Image — <code>[pizza_layer]</code></h2></div>
				<div class="pscg-card__body">
					<p class="pscg-desc">Outputs a single layer <code>&lt;img&gt;</code> tag. Useful for menu pages or product listings.</p>
					<div class="pscg-grid">
						<div class="pscg-field">
							<label>Layer Type</label>
							<select class="pscg-select" id="l-type">
								<?php foreach ( $layer_types as $lt ) : ?>
								<option value="<?php echo esc_attr( $lt ); ?>"><?php echo esc_html( ucfirst( $lt ) ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="pscg-field">
							<label>Slug <span class="pscg-hint">Lowercase, hyphenated post slug</span></label>
							<input type="text" class="pscg-input" id="l-slug" placeholder="pepperoni">
						</div>
						<div class="pscg-field">
							<label>Image Type</label>
							<select class="pscg-select" id="l-image">
								<option value="layer">Layer image (stack PNG)</option>
								<option value="list">List image (thumbnail)</option>
							</select>
						</div>
						<div class="pscg-field">
							<label>CSS Class <span class="pscg-hint">Optional extra CSS class</span></label>
							<input type="text" class="pscg-input" id="l-class" placeholder="my-custom-class">
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- ══ Layer info configurator ═══════════════════════════════ -->
		<div class="pscg-form" id="pscg-form-layerinfo" style="display:none;">
			<div class="pscg-card">
				<div class="pscg-card__head"><h2>Layer Field Value — <code>[pizza_layer_info]</code></h2></div>
				<div class="pscg-card__body">
					<p class="pscg-desc">Outputs a single SCF field value as escaped text. Good for displaying ingredient lists or descriptions.</p>
					<div class="pscg-grid">
						<div class="pscg-field">
							<label>Layer Type</label>
							<select class="pscg-select" id="li-type">
								<?php foreach ( $layer_types as $lt ) : ?>
								<option value="<?php echo esc_attr( $lt ); ?>"><?php echo esc_html( ucfirst( $lt ) ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="pscg-field">
							<label>Slug</label>
							<input type="text" class="pscg-input" id="li-slug" placeholder="pepperoni">
						</div>
						<div class="pscg-field">
							<label>Field name <span class="pscg-hint">SCF field key, e.g. <code>topping_ingredients</code></span></label>
							<input type="text" class="pscg-input" id="li-field" placeholder="topping_ingredients">
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- ══ Output ════════════════════════════════════════════════ -->
		<div class="pscg-output-card">
			<label class="pscg-output-label">Generated Shortcode</label>
			<div class="pscg-output-row">
				<code class="pscg-output" id="pscg-output">[pizza_builder]</code>
				<button class="button button-primary pscg-copy-btn" id="pscg-copy-btn">
					<span class="dashicons dashicons-clipboard"></span> Copy
				</button>
			</div>
			<div id="pscg-copy-notice" class="pscg-copy-notice" style="display:none;">✓ Copied to clipboard!</div>
		</div>

		</div><!-- /.wrap -->
		<?php
	}

	private function render_styles(): void { ?>
	<style>
	.pscg-wrap { max-width: 960px; }
	.pscg-header { display:flex; align-items:center; gap:16px; background:linear-gradient(135deg,#1a1e23,#2d3748); color:#fff; border-radius:10px; padding:22px 28px; margin-bottom:20px; }
	.pscg-header__icon { font-size:36px !important; width:36px !important; height:36px !important; color:#ff6b35; flex-shrink:0; }
	.pscg-header__title { margin:0; font-size:22px; font-weight:700; color:#fff; }
	.pscg-header__sub { margin:3px 0 0; color:#8d97a5; font-size:13px; }
	.pscg-type-tabs { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:20px; }
	.pscg-type-tab { display:flex; flex-direction:column; align-items:center; justify-content:center; gap:4px; flex:1 1 140px; padding:14px 10px; background:#fff; border:2px solid #e0e3e7; border-radius:10px; cursor:pointer; font-family:inherit; transition:border-color .15s,box-shadow .15s; }
	.pscg-type-tab .dashicons { font-size:24px !important; width:24px !important; height:24px !important; color:#646970; }
	.pscg-type-tab__label { font-size:12px; font-weight:700; font-family:monospace; color:#1d2023; }
	.pscg-type-tab__desc { font-size:11px; color:#787c82; }
	.pscg-type-tab--active { border-color:#2271b1; box-shadow:0 0 0 2px #dce8f7; }
	.pscg-type-tab--active .dashicons { color:#2271b1; }
	.pscg-card { background:#fff; border:1px solid #e0e3e7; border-radius:10px; margin-bottom:16px; overflow:hidden; }
	.pscg-card__head { padding:16px 24px 12px; border-bottom:1px solid #f0f0f0; }
	.pscg-card__head h2 { margin:0; font-size:15px; }
	.pscg-card__head h2 code { font-size:13px; background:#f0f0f1; padding:2px 6px; border-radius:3px; }
	.pscg-card__body { padding:20px 24px; }
	.pscg-desc { margin:0 0 16px; font-size:13px; color:#646970; }
	.pscg-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(240px,1fr)); gap:16px; margin-bottom:16px; }
	.pscg-field { display:flex; flex-direction:column; gap:5px; }
	.pscg-field--full { grid-column:1/-1; }
	.pscg-field label { font-size:12px; font-weight:600; color:#1d2023; }
	.pscg-hint { font-weight:400; color:#787c82; font-size:11px; }
	.pscg-input,.pscg-select { width:100%; padding:7px 10px; border:1px solid #8c8f94; border-radius:4px; font-size:13px; font-family:inherit; }
	.pscg-select[multiple] { padding:6px; }
	.pscg-checkboxes { display:flex; flex-wrap:wrap; gap:8px; margin-top:4px; }
	.pscg-cb-label { display:inline-flex; align-items:center; gap:5px; font-size:13px; font-weight:400; background:#f8f9fa; border:1px solid #e0e3e7; border-radius:4px; padding:5px 10px; cursor:pointer; }
	.pscg-cb-label input { margin:0; }
	.pscg-output-card { background:#1a1e23; border-radius:10px; padding:20px 24px; margin-bottom:20px; }
	.pscg-output-label { display:block; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#8d97a5; margin-bottom:10px; }
	.pscg-output-row { display:flex; gap:10px; align-items:center; }
	.pscg-output { flex:1; display:block; background:#2d3748; color:#a3d977; padding:12px 16px; border-radius:6px; font-size:14px; font-family:monospace; word-break:break-all; line-height:1.5; }
	.pscg-copy-btn { flex-shrink:0; display:inline-flex !important; align-items:center; gap:5px; }
	.pscg-copy-btn .dashicons { font-size:14px !important; width:14px !important; height:14px !important; }
	.pscg-copy-notice { margin-top:8px; color:#a3d977; font-size:13px; font-weight:600; }
	</style>
	<?php }

}
