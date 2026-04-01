<?php
namespace PizzaLayer\Admin;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * PizzaLayer Layer Builder Wizard
 *
 * A fully guided, step-by-step admin page that walks the user through:
 *   Step 1 — Choose layer type (topping, crust, sauce, cheese, drizzle, cut, size)
 *   Step 2 — Enter details (name, description, price, attributes specific to type)
 *   Step 3 — Upload / pick a layer image
 *   Step 4 — Review & save → creates the CPT post and returns the shortcode
 *
 * All steps run on a single page via JS state; no page reloads between steps.
 * On completion the post is created via an AJAX call to this class.
 */
class LayerBuilderWizard {

	/** Layer type definitions */
	private const LAYER_TYPES = [
		'toppings' => [
			'label'       => 'Topping',
			'plural'      => 'Toppings',
			'cpt'         => 'pizzalayer_toppings',
			'icon'        => 'dashicons-tag',
			'color'       => '#e74c3c',
			'emoji'       => '🍕',
			'description' => 'Ingredients placed on top of the cheese — pepperoni, mushrooms, peppers, etc.',
			'extra_fields'=> [ 'calories', 'is_vegetarian', 'is_vegan', 'is_gluten_free' ],
		],
		'crusts' => [
			'label'       => 'Crust',
			'plural'      => 'Crusts',
			'cpt'         => 'pizzalayer_crusts',
			'icon'        => 'dashicons-admin-page',
			'color'       => '#e67e22',
			'emoji'       => '🫓',
			'description' => 'The base of your pizza — thin, thick, stuffed, gluten-free, etc.',
			'extra_fields'=> [ 'thickness', 'is_gluten_free' ],
		],
		'sauces' => [
			'label'       => 'Sauce',
			'plural'      => 'Sauces',
			'cpt'         => 'pizzalayer_sauces',
			'icon'        => 'dashicons-portfolio',
			'color'       => '#c0392b',
			'emoji'       => '🥫',
			'description' => 'The sauce spread on the crust — marinara, white sauce, pesto, BBQ, etc.',
			'extra_fields'=> [ 'spice_level', 'is_vegan' ],
		],
		'cheeses' => [
			'label'       => 'Cheese',
			'plural'      => 'Cheeses',
			'cpt'         => 'pizzalayer_cheeses',
			'icon'        => 'dashicons-star-filled',
			'color'       => '#f39c12',
			'emoji'       => '🧀',
			'description' => 'Cheese layer options — mozzarella, parmesan, vegan cheese, etc.',
			'extra_fields'=> [ 'is_vegan', 'is_dairy_free' ],
		],
		'drizzles' => [
			'label'       => 'Drizzle',
			'plural'      => 'Drizzles',
			'cpt'         => 'pizzalayer_drizzles',
			'icon'        => 'dashicons-admin-customizer',
			'color'       => '#8e44ad',
			'emoji'       => '💧',
			'description' => 'Finishing drizzles applied after baking — olive oil, balsamic, hot honey, etc.',
			'extra_fields'=> [ 'spice_level' ],
		],
		'cuts' => [
			'label'       => 'Cut',
			'plural'      => 'Cuts',
			'cpt'         => 'pizzalayer_cuts',
			'icon'        => 'dashicons-image-crop',
			'color'       => '#2980b9',
			'emoji'       => '✂️',
			'description' => 'How the pizza is cut — traditional slices, square cut, uncut, etc.',
			'extra_fields'=> [],
		],
		'sizes' => [
			'label'       => 'Size',
			'plural'      => 'Sizes',
			'cpt'         => 'pizzalayer_sizes',
			'icon'        => 'dashicons-editor-expand',
			'color'       => '#27ae60',
			'emoji'       => '📏',
			'description' => 'Available pizza sizes — personal (6"), small (8"), medium (12"), large (16"), etc.',
			'extra_fields'=> [ 'diameter_inches' ],
		],
	];

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		wp_enqueue_media();
		$nonce = wp_create_nonce( 'pizzalayer_wizard_save' );
		?>
		<div class="wrap plbw-wrap">
		<?php $this->render_styles(); ?>

		<!-- Header -->
		<div class="plbw-header">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="plbw-header-icon" aria-hidden="true">
				<path d="M10 1C5.03 1 1 5.03 1 10s4.03 9 9 9 9-4.03 9-9-4.03-9-9-9zM10 2.6c3.37 0 6.27 2.08 7.52 5.06L10 10.1 2.48 7.66C3.73 4.68 6.63 2.6 10 2.6zM2.6 10c0-.38.03-.75.09-1.11L10 11.7l7.31-2.81c.06.36.09.73.09 1.11 0 4.08-3.32 7.4-7.4 7.4S2.6 14.08 2.6 10zM7.2 11.8a1.1 1.1 0 1 0 0 2.2 1.1 1.1 0 0 0 0-2.2zM12.4 12.6a1.1 1.1 0 1 0 0 2.2 1.1 1.1 0 0 0 0-2.2z"/>
			</svg>
			<div>
				<h1 class="plbw-header__title"><?php esc_html_e( 'Layer Builder Wizard', 'pizzalayer' ); ?></h1>
				<p class="plbw-header__sub"><?php esc_html_e( 'Build any pizza layer in minutes — choose a type, fill in the details, add an image, and get your shortcode.', 'pizzalayer' ); ?></p>
			</div>
		</div>

		<!-- Step progress bar -->
		<div class="plbw-progress" id="plbw-progress" role="progressbar" aria-valuenow="1" aria-valuemin="1" aria-valuemax="4">
			<?php
			$steps = [
				1 => 'Choose Type',
				2 => 'Details',
				3 => 'Image',
				4 => 'Review & Save',
			];
			foreach ( $steps as $n => $label ) :
			?>
			<div class="plbw-step <?php echo $n === 1 ? 'is-active' : ''; ?>" data-step="<?php echo $n; ?>">
				<div class="plbw-step-circle"><?php echo $n; ?></div>
				<span class="plbw-step-label"><?php echo esc_html( $label ); ?></span>
			</div>
			<?php if ( $n < 4 ) : ?>
			<div class="plbw-step-connector"></div>
			<?php endif; endforeach; ?>
		</div>

		<!-- ── STEP 1: Choose Layer Type ────────────────────────────── -->
		<div class="plbw-panel" id="plbw-panel-1">
			<h2 class="plbw-panel-title"><?php esc_html_e( 'What type of layer are you adding?', 'pizzalayer' ); ?></h2>
			<p class="plbw-panel-sub"><?php esc_html_e( 'Each layer type represents a different part of your pizza build.', 'pizzalayer' ); ?></p>

			<div class="plbw-type-grid" id="plbw-type-grid">
				<?php foreach ( self::LAYER_TYPES as $slug => $type ) : ?>
				<button type="button"
					class="plbw-type-card"
					data-type="<?php echo esc_attr( $slug ); ?>"
					data-label="<?php echo esc_attr( $type['label'] ); ?>"
					data-cpt="<?php echo esc_attr( $type['cpt'] ); ?>"
					data-color="<?php echo esc_attr( $type['color'] ); ?>"
					data-extra="<?php echo esc_attr( wp_json_encode( $type['extra_fields'] ) ); ?>"
					style="--plbw-accent:<?php echo esc_attr( $type['color'] ); ?>">
					<span class="plbw-type-emoji" aria-hidden="true"><?php echo $type['emoji']; ?></span>
					<span class="plbw-type-name"><?php echo esc_html( $type['label'] ); ?></span>
					<span class="plbw-type-desc"><?php echo esc_html( $type['description'] ); ?></span>
					<span class="plbw-type-check dashicons dashicons-yes-alt" aria-hidden="true"></span>
				</button>
				<?php endforeach; ?>
			</div>

			<div class="plbw-nav-row">
				<span></span>
				<button type="button" class="button button-primary plbw-next-btn" id="plbw-step1-next" disabled>
					<?php esc_html_e( 'Next: Enter Details', 'pizzalayer' ); ?> <span class="dashicons dashicons-arrow-right-alt2"></span>
				</button>
			</div>
		</div>

		<!-- ── STEP 2: Details ──────────────────────────────────────── -->
		<div class="plbw-panel" id="plbw-panel-2" style="display:none;">
			<h2 class="plbw-panel-title" id="plbw-step2-title"><?php esc_html_e( 'Layer Details', 'pizzalayer' ); ?></h2>

			<div class="plbw-fields">
				<!-- Core fields -->
				<div class="plbw-field-row">
					<label for="plbw-name" class="plbw-label">
						<?php esc_html_e( 'Name', 'pizzalayer' ); ?>
						<span class="plbw-required" aria-label="required">*</span>
					</label>
					<input type="text" id="plbw-name" class="regular-text plbw-input" placeholder="<?php esc_attr_e( 'e.g. Pepperoni, Thin Crust, Marinara…', 'pizzalayer' ); ?>" maxlength="100">
					<p class="plbw-help"><?php esc_html_e( 'The display name shown to customers.', 'pizzalayer' ); ?></p>
				</div>

				<div class="plbw-field-row">
					<label for="plbw-slug" class="plbw-label"><?php esc_html_e( 'Slug', 'pizzalayer' ); ?></label>
					<input type="text" id="plbw-slug" class="regular-text plbw-input" placeholder="<?php esc_attr_e( 'auto-generated from name', 'pizzalayer' ); ?>" maxlength="60" pattern="[a-z0-9\-]+">
					<p class="plbw-help"><?php esc_html_e( 'URL-friendly identifier. Used in shortcodes and presets. Auto-generated if left blank.', 'pizzalayer' ); ?></p>
				</div>

				<div class="plbw-field-row">
					<label for="plbw-description" class="plbw-label"><?php esc_html_e( 'Description', 'pizzalayer' ); ?></label>
					<textarea id="plbw-description" class="plbw-input plbw-textarea" rows="3" placeholder="<?php esc_attr_e( 'Optional short description shown in the builder…', 'pizzalayer' ); ?>" maxlength="500"></textarea>
				</div>

				<div class="plbw-field-row">
					<label for="plbw-price" class="plbw-label"><?php esc_html_e( 'Price Modifier', 'pizzalayer' ); ?></label>
					<input type="number" id="plbw-price" class="small-text plbw-input" step="0.01" min="0" value="0.00" placeholder="0.00">
					<p class="plbw-help"><?php esc_html_e( 'Additional cost for this layer (used by PizzaLayer Pro). Enter 0 for no extra charge.', 'pizzalayer' ); ?></p>
				</div>

				<!-- Dynamic extra fields (shown based on type) -->
				<div id="plbw-extra-fields">

					<!-- Calories (toppings) -->
					<div class="plbw-field-row plbw-extra" data-for="toppings" style="display:none;">
						<label for="plbw-calories" class="plbw-label"><?php esc_html_e( 'Calories (per serving)', 'pizzalayer' ); ?></label>
						<input type="number" id="plbw-calories" class="small-text plbw-input" min="0" max="9999" value="">
					</div>

					<!-- Thickness (crusts) -->
					<div class="plbw-field-row plbw-extra" data-for="crusts" style="display:none;">
						<label for="plbw-thickness" class="plbw-label"><?php esc_html_e( 'Thickness', 'pizzalayer' ); ?></label>
						<select id="plbw-thickness" class="plbw-input plbw-select">
							<option value=""><?php esc_html_e( '— Select —', 'pizzalayer' ); ?></option>
							<option value="thin"><?php esc_html_e( 'Thin', 'pizzalayer' ); ?></option>
							<option value="medium"><?php esc_html_e( 'Medium', 'pizzalayer' ); ?></option>
							<option value="thick"><?php esc_html_e( 'Thick', 'pizzalayer' ); ?></option>
							<option value="stuffed"><?php esc_html_e( 'Stuffed', 'pizzalayer' ); ?></option>
						</select>
					</div>

					<!-- Diameter (sizes) -->
					<div class="plbw-field-row plbw-extra" data-for="sizes" style="display:none;">
						<label for="plbw-diameter" class="plbw-label"><?php esc_html_e( 'Diameter (inches)', 'pizzalayer' ); ?></label>
						<input type="number" id="plbw-diameter" class="small-text plbw-input" min="1" max="48" step="0.5" value="">
					</div>

					<!-- Spice level (sauces, drizzles) -->
					<div class="plbw-field-row plbw-extra" data-for="sauces drizzles" style="display:none;">
						<label for="plbw-spice" class="plbw-label"><?php esc_html_e( 'Spice Level', 'pizzalayer' ); ?></label>
						<select id="plbw-spice" class="plbw-input plbw-select">
							<option value=""><?php esc_html_e( '— None —', 'pizzalayer' ); ?></option>
							<option value="mild"><?php esc_html_e( 'Mild', 'pizzalayer' ); ?></option>
							<option value="medium"><?php esc_html_e( 'Medium', 'pizzalayer' ); ?></option>
							<option value="hot"><?php esc_html_e( 'Hot 🌶️', 'pizzalayer' ); ?></option>
							<option value="extra_hot"><?php esc_html_e( 'Extra Hot 🌶️🌶️', 'pizzalayer' ); ?></option>
						</select>
					</div>

					<!-- Dietary toggles -->
					<div class="plbw-field-row plbw-extra plbw-checkgroup" data-for="toppings crusts sauces cheeses drizzles" style="display:none;">
						<span class="plbw-label"><?php esc_html_e( 'Dietary Flags', 'pizzalayer' ); ?></span>
						<div class="plbw-check-row">
							<label class="plbw-check-label plbw-extra" data-for="toppings sauces drizzles" style="display:none;">
								<input type="checkbox" id="plbw-is-vegetarian"> <?php esc_html_e( 'Vegetarian', 'pizzalayer' ); ?>
							</label>
							<label class="plbw-check-label plbw-extra" data-for="toppings sauces cheeses drizzles" style="display:none;">
								<input type="checkbox" id="plbw-is-vegan"> <?php esc_html_e( 'Vegan', 'pizzalayer' ); ?>
							</label>
							<label class="plbw-check-label plbw-extra" data-for="toppings crusts" style="display:none;">
								<input type="checkbox" id="plbw-is-gf"> <?php esc_html_e( 'Gluten-Free', 'pizzalayer' ); ?>
							</label>
							<label class="plbw-check-label plbw-extra" data-for="cheeses" style="display:none;">
								<input type="checkbox" id="plbw-is-dairyfree"> <?php esc_html_e( 'Dairy-Free', 'pizzalayer' ); ?>
							</label>
						</div>
					</div>

				</div><!-- #plbw-extra-fields -->

				<div class="plbw-field-row">
					<label for="plbw-sort-order" class="plbw-label"><?php esc_html_e( 'Sort Order', 'pizzalayer' ); ?></label>
					<input type="number" id="plbw-sort-order" class="small-text plbw-input" min="0" value="0">
					<p class="plbw-help"><?php esc_html_e( 'Lower numbers appear first in the builder (0 = default).', 'pizzalayer' ); ?></p>
				</div>

			</div><!-- .plbw-fields -->

			<div class="plbw-nav-row">
				<button type="button" class="button plbw-back-btn" data-target="1">
					<span class="dashicons dashicons-arrow-left-alt2"></span> <?php esc_html_e( 'Back', 'pizzalayer' ); ?>
				</button>
				<button type="button" class="button button-primary plbw-next-btn" id="plbw-step2-next">
					<?php esc_html_e( 'Next: Add Image', 'pizzalayer' ); ?> <span class="dashicons dashicons-arrow-right-alt2"></span>
				</button>
			</div>
		</div>

		<!-- ── STEP 3: Image ────────────────────────────────────────── -->
		<div class="plbw-panel" id="plbw-panel-3" style="display:none;">
			<h2 class="plbw-panel-title"><?php esc_html_e( 'Layer Image', 'pizzalayer' ); ?></h2>
			<p class="plbw-panel-sub"><?php esc_html_e( 'Upload or choose a transparent PNG image for this layer. You can skip this step and add an image later.', 'pizzalayer' ); ?></p>

			<div class="plbw-image-area" id="plbw-image-area">
				<div class="plbw-image-preview" id="plbw-image-preview">
					<span class="dashicons dashicons-format-image plbw-img-icon"></span>
					<p><?php esc_html_e( 'No image selected', 'pizzalayer' ); ?></p>
				</div>

				<div class="plbw-image-actions">
					<button type="button" class="button button-primary" id="plbw-choose-image">
						<span class="dashicons dashicons-admin-media"></span> <?php esc_html_e( 'Choose from Media Library', 'pizzalayer' ); ?>
					</button>
					<button type="button" class="button" id="plbw-upload-image">
						<span class="dashicons dashicons-upload"></span> <?php esc_html_e( 'Upload New Image', 'pizzalayer' ); ?>
					</button>
					<button type="button" class="button plbw-lim-btn" id="plbw-open-lim">
						<span class="dashicons dashicons-art"></span> <?php esc_html_e( 'Layer Image Maker', 'pizzalayer' ); ?>
					</button>
					<button type="button" class="button button-link-delete" id="plbw-remove-image" style="display:none;">
						<span class="dashicons dashicons-trash"></span> <?php esc_html_e( 'Remove Image', 'pizzalayer' ); ?>
					</button>
				</div>

				<input type="hidden" id="plbw-image-id" value="">
				<input type="hidden" id="plbw-image-url" value="">

				<div class="plbw-image-tip">
					<span class="dashicons dashicons-info-outline"></span>
					<?php esc_html_e( 'For best results, use a transparent PNG at 800×600px (4:3 ratio). Use the Layer Image Maker tool to prepare your image.', 'pizzalayer' ); ?>
				</div>
			</div>

			<div class="plbw-nav-row">
				<button type="button" class="button plbw-back-btn" data-target="2">
					<span class="dashicons dashicons-arrow-left-alt2"></span> <?php esc_html_e( 'Back', 'pizzalayer' ); ?>
				</button>
				<button type="button" class="button button-primary" id="plbw-step3-next">
					<?php esc_html_e( 'Next: Review', 'pizzalayer' ); ?> <span class="dashicons dashicons-arrow-right-alt2"></span>
				</button>
			</div>
		</div>

		<!-- ── STEP 4: Review & Save ────────────────────────────────── -->
		<div class="plbw-panel" id="plbw-panel-4" style="display:none;">
			<h2 class="plbw-panel-title"><?php esc_html_e( 'Review & Save', 'pizzalayer' ); ?></h2>
			<p class="plbw-panel-sub"><?php esc_html_e( 'Everything looks good? Hit Save Layer to create it.', 'pizzalayer' ); ?></p>

			<div class="plbw-review-card" id="plbw-review-card">
				<!-- Filled by JS -->
			</div>

			<div class="plbw-nav-row">
				<button type="button" class="button plbw-back-btn" data-target="3">
					<span class="dashicons dashicons-arrow-left-alt2"></span> <?php esc_html_e( 'Back', 'pizzalayer' ); ?>
				</button>
				<button type="button" class="button button-primary plbw-save-btn" id="plbw-save-btn">
					<span class="dashicons dashicons-saved"></span> <?php esc_html_e( 'Save Layer', 'pizzalayer' ); ?>
				</button>
			</div>

			<!-- Saving spinner -->
			<div class="plbw-saving-overlay" id="plbw-saving-overlay" style="display:none;" aria-live="polite">
				<span class="spinner is-active"></span>
				<p><?php esc_html_e( 'Saving your layer…', 'pizzalayer' ); ?></p>
			</div>
		</div>

		<!-- ── SUCCESS PANEL ─────────────────────────────────────────── -->
		<div class="plbw-panel plbw-success-panel" id="plbw-success-panel" style="display:none;">
			<div class="plbw-success-inner" id="plbw-success-inner">
				<!-- Filled by JS -->
			</div>
		</div>

		</div><!-- .plbw-wrap -->

		<script>
		(function($){
			'use strict';

			var nonce        = <?php echo wp_json_encode( $nonce ); ?>;
			var ajaxUrl      = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
			var limUrl       = <?php echo wp_json_encode( admin_url( 'admin.php?page=pizzalayer-layer-maker' ) ); ?>;
			var layerTypes   = <?php echo wp_json_encode( self::LAYER_TYPES ); ?>;

			/* ── State ─────────────────────────────────────────── */
			var state = {
				step     : 1,
				typeSlug : '',
				typeLabel: '',
				typeCpt  : '',
				typeColor: '',
				typeExtra: [],
				name     : '',
				slug     : '',
				desc     : '',
				price    : '0.00',
				imageId  : 0,
				imageUrl : '',
				meta     : {}
			};

			/* ── Step navigation ───────────────────────────────── */
			function goStep(n) {
				state.step = n;
				$('.plbw-panel').hide();
				$('#plbw-panel-' + n).show();
				$('.plbw-step').removeClass('is-active is-done');
				for (var i = 1; i < n; i++) {
					$('.plbw-step[data-step="' + i + '"]').addClass('is-done');
				}
				$('.plbw-step[data-step="' + n + '"]').addClass('is-active');
				$('#plbw-progress').attr('aria-valuenow', n);
				$('html,body').animate({ scrollTop: 0 }, 200);

				if (n === 4) { buildReview(); }
			}

			/* ── Step 1: type selection ────────────────────────── */
			$(document).on('click', '.plbw-type-card', function() {
				$('.plbw-type-card').removeClass('is-selected');
				$(this).addClass('is-selected');
				state.typeSlug  = $(this).data('type');
				state.typeLabel = $(this).data('label');
				state.typeCpt   = $(this).data('cpt');
				state.typeColor = $(this).data('color');
				state.typeExtra = $(this).data('extra') || [];
				$('#plbw-step1-next').prop('disabled', false);
			});

			$('#plbw-step1-next').on('click', function() {
				if (!state.typeSlug) { return; }
				// Update step 2 title
				$('#plbw-step2-title').text('<?php esc_html_e( 'Details for your', 'pizzalayer' ); ?> ' + state.typeLabel);
				showExtraFields(state.typeSlug);
				goStep(2);
			});

			function showExtraFields(typeSlug) {
				// Show/hide extra field rows based on type
				$('.plbw-extra').each(function(){
					var forTypes = ($(this).data('for') || '').split(' ');
					if (forTypes.indexOf(typeSlug) !== -1) {
						$(this).show();
					} else {
						$(this).hide();
					}
				});
			}

			/* ── Step 2: details ───────────────────────────────── */
			// Auto-generate slug from name
			$('#plbw-name').on('input', function(){
				state.name = $(this).val();
				if (!$('#plbw-slug').data('manual')) {
					var slug = state.name
						.toLowerCase()
						.replace(/[^a-z0-9\s\-]/g, '')
						.replace(/\s+/g, '-')
						.replace(/-+/g, '-')
						.substring(0, 60);
					$('#plbw-slug').val(slug);
					state.slug = slug;
				}
			});
			$('#plbw-slug').on('input', function(){
				$(this).data('manual', $(this).val() !== '');
				state.slug = $(this).val();
			});
			$('#plbw-description').on('input', function(){ state.desc  = $(this).val(); });
			$('#plbw-price').on('input',       function(){ state.price = $(this).val(); });

			$('#plbw-step2-next').on('click', function(){
				state.name  = $.trim($('#plbw-name').val());
				state.slug  = $.trim($('#plbw-slug').val());
				state.desc  = $.trim($('#plbw-description').val());
				state.price = $('#plbw-price').val();
				if (!state.name) {
					$('#plbw-name').focus().closest('.plbw-field-row').addClass('plbw-field-error');
					return;
				}
				$('#plbw-name').closest('.plbw-field-row').removeClass('plbw-field-error');
				// Collect meta
				state.meta = {};
				if ($('#plbw-calories').val())   { state.meta.calories        = $('#plbw-calories').val(); }
				if ($('#plbw-thickness').val())  { state.meta.thickness       = $('#plbw-thickness').val(); }
				if ($('#plbw-diameter').val())   { state.meta.diameter_inches = $('#plbw-diameter').val(); }
				if ($('#plbw-spice').val())      { state.meta.spice_level     = $('#plbw-spice').val(); }
				if ($('#plbw-is-vegetarian').is(':checked')) { state.meta.is_vegetarian  = '1'; }
				if ($('#plbw-is-vegan').is(':checked'))      { state.meta.is_vegan        = '1'; }
				if ($('#plbw-is-gf').is(':checked'))         { state.meta.is_gluten_free  = '1'; }
				if ($('#plbw-is-dairyfree').is(':checked'))  { state.meta.is_dairy_free   = '1'; }
				state.meta.sort_order = $('#plbw-sort-order').val() || '0';
				goStep(3);
			});

			/* ── Step 2: back buttons ──────────────────────────── */
			$(document).on('click', '.plbw-back-btn', function(){
				goStep( parseInt($(this).data('target'), 10) );
			});

			/* ── Step 3: image ─────────────────────────────────── */
			var mediaFrame = null;

			function openMedia(mode) {
				if (mediaFrame) { mediaFrame.off('select'); }
				mediaFrame = wp.media({
					title  : mode === 'upload' ? '<?php esc_html_e( 'Upload Layer Image', 'pizzalayer' ); ?>' : '<?php esc_html_e( 'Choose Layer Image', 'pizzalayer' ); ?>',
					button : { text: '<?php esc_html_e( 'Use this image', 'pizzalayer' ); ?>' },
					library: mode === 'upload' ? { type: 'image', uploadedTo: null } : { type: 'image' },
					multiple: false
				});
				if (mode === 'upload') { mediaFrame.on('open', function(){ mediaFrame.state().get('selection').reset(); }); }
				mediaFrame.on('select', function(){
					var att = mediaFrame.state().get('selection').first().toJSON();
					setImage(att.id, att.url);
				});
				mediaFrame.open();
			}

			function setImage(id, url) {
				state.imageId  = id;
				state.imageUrl = url;
				$('#plbw-image-id').val(id);
				$('#plbw-image-url').val(url);
				$('#plbw-image-preview').html('<img src="' + url + '" alt="" style="max-width:100%;max-height:200px;border-radius:4px;">');
				$('#plbw-remove-image').show();
			}

			$('#plbw-choose-image').on('click', function(){ openMedia('library'); });
			$('#plbw-upload-image').on('click', function(){ openMedia('upload'); });
			$('#plbw-remove-image').on('click', function(){
				state.imageId  = 0;
				state.imageUrl = '';
				$('#plbw-image-id').val('');
				$('#plbw-image-url').val('');
				$('#plbw-image-preview').html('<span class="dashicons dashicons-format-image plbw-img-icon"></span><p><?php esc_html_e( 'No image selected', 'pizzalayer' ); ?></p>');
				$(this).hide();
			});
			$('#plbw-open-lim').on('click', function(){
				window.open(limUrl, '_blank');
			});
			$('#plbw-step3-next').on('click', function(){ goStep(4); });

			/* ── Step 4: review ────────────────────────────────── */
			function buildReview() {
				var typeInfo = layerTypes[state.typeSlug] || {};
				var slugVal  = state.slug || slugify(state.name);
				var html = '';
				html += '<div class="plbw-review-type" style="--plbw-accent:' + state.typeColor + '">';
				html += '<span class="plbw-review-emoji">' + (typeInfo.emoji || '') + '</span>';
				html += '<span class="plbw-review-type-label">' + escHtml(state.typeLabel) + '</span>';
				html += '</div>';

				html += '<table class="plbw-review-table">';
				html += reviewRow('<?php esc_html_e( 'Name', 'pizzalayer' ); ?>',  escHtml(state.name));
				html += reviewRow('<?php esc_html_e( 'Slug', 'pizzalayer' ); ?>',  '<code>' + escHtml(slugVal) + '</code>');
				if (state.desc) {
					html += reviewRow('<?php esc_html_e( 'Description', 'pizzalayer' ); ?>', escHtml(state.desc));
				}
				if (parseFloat(state.price) > 0) {
					html += reviewRow('<?php esc_html_e( 'Price modifier', 'pizzalayer' ); ?>', escHtml(state.price));
				}
				// Meta
				if (state.meta.thickness)      { html += reviewRow('<?php esc_html_e( 'Thickness', 'pizzalayer' ); ?>', escHtml(state.meta.thickness)); }
				if (state.meta.calories)        { html += reviewRow('<?php esc_html_e( 'Calories', 'pizzalayer' ); ?>', escHtml(state.meta.calories)); }
				if (state.meta.diameter_inches) { html += reviewRow('<?php esc_html_e( 'Diameter', 'pizzalayer' ); ?>', escHtml(state.meta.diameter_inches) + '″'); }
				if (state.meta.spice_level)     { html += reviewRow('<?php esc_html_e( 'Spice level', 'pizzalayer' ); ?>', escHtml(state.meta.spice_level)); }

				var flags = [];
				if (state.meta.is_vegetarian) { flags.push('<?php esc_html_e( 'Vegetarian', 'pizzalayer' ); ?>'); }
				if (state.meta.is_vegan)      { flags.push('<?php esc_html_e( 'Vegan', 'pizzalayer' ); ?>'); }
				if (state.meta.is_gluten_free){ flags.push('<?php esc_html_e( 'Gluten-Free', 'pizzalayer' ); ?>'); }
				if (state.meta.is_dairy_free) { flags.push('<?php esc_html_e( 'Dairy-Free', 'pizzalayer' ); ?>'); }
				if (flags.length) {
					html += reviewRow('<?php esc_html_e( 'Dietary', 'pizzalayer' ); ?>', flags.join(', '));
				}

				if (state.imageUrl) {
					html += reviewRow('<?php esc_html_e( 'Image', 'pizzalayer' ); ?>', '<img src="' + state.imageUrl + '" style="max-height:80px;border-radius:4px;vertical-align:middle;">');
				} else {
					html += reviewRow('<?php esc_html_e( 'Image', 'pizzalayer' ); ?>', '<em><?php esc_html_e( 'None (can be added later)', 'pizzalayer' ); ?></em>');
				}
				html += '</table>';

				$('#plbw-review-card').html(html);
			}
			function reviewRow(label, value) {
				return '<tr><th>' + label + '</th><td>' + value + '</td></tr>';
			}

			/* ── Save ──────────────────────────────────────────── */
			$('#plbw-save-btn').on('click', function(){
				var $btn = $(this);
				$btn.prop('disabled', true);
				$('#plbw-saving-overlay').show();

				var slugVal = state.slug || slugify(state.name);

				$.post(ajaxUrl, {
					action  : 'pizzalayer_wizard_save_layer',
					nonce   : nonce,
					type    : state.typeSlug,
					cpt     : state.typeCpt,
					name    : state.name,
					slug    : slugVal,
					desc    : state.desc,
					price   : state.price,
					image_id: state.imageId,
					meta    : JSON.stringify(state.meta)
				}, function(resp){
					$('#plbw-saving-overlay').hide();
					$btn.prop('disabled', false);

					if (resp.success) {
						showSuccess(resp.data);
					} else {
						alert('<?php esc_html_e( 'Error saving layer:', 'pizzalayer' ); ?> ' + (resp.data && resp.data.message ? resp.data.message : '<?php esc_html_e( 'Unknown error.', 'pizzalayer' ); ?>'));
					}
				}).fail(function(){
					$('#plbw-saving-overlay').hide();
					$btn.prop('disabled', false);
					alert('<?php esc_html_e( 'Network error. Please try again.', 'pizzalayer' ); ?>');
				});
			});

			function showSuccess(data) {
				$('.plbw-panel').hide();
				var html = '';
				html += '<div class="plbw-success-check"><span class="dashicons dashicons-yes-alt"></span></div>';
				html += '<h2 class="plbw-success-title">' + escHtml(data.name) + ' <?php esc_html_e( 'was saved!', 'pizzalayer' ); ?></h2>';
				html += '<p><?php esc_html_e( 'Your new layer has been created. Use the shortcode below to include it on any page.', 'pizzalayer' ); ?></p>';

				html += '<div class="plbw-shortcode-box">';
				html += '<code id="plbw-shortcode-output">' + escHtml(data.shortcode) + '</code>';
				html += '<button type="button" class="button plbw-copy-btn" data-clipboard="' + escAttr(data.shortcode) + '">';
				html += '<span class="dashicons dashicons-clipboard"></span> <?php esc_html_e( 'Copy', 'pizzalayer' ); ?>';
				html += '</button>';
				html += '</div>';

				html += '<div class="plbw-success-actions">';
				html += '<a href="' + escAttr(data.edit_url) + '" class="button button-primary">';
				html += '<span class="dashicons dashicons-edit"></span> <?php esc_html_e( 'Edit Layer', 'pizzalayer' ); ?></a> ';
				html += '<a href="' + escAttr(data.list_url) + '" class="button">';
				html += '<span class="dashicons dashicons-list-view"></span> <?php esc_html_e( 'All', 'pizzalayer' ); ?> ' + escHtml(state.typeLabel + 's') + '</a> ';
				html += '<button type="button" class="button" id="plbw-build-another">';
				html += '<span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e( 'Build Another Layer', 'pizzalayer' ); ?></button>';
				html += '</div>';

				$('#plbw-success-inner').html(html);
				$('#plbw-success-panel').show();
				$('html,body').animate({ scrollTop: 0 }, 200);

				// Update progress to show all done
				$('.plbw-step').addClass('is-done').removeClass('is-active');
			}

			$(document).on('click', '#plbw-build-another', function(){
				// Reset state
				state = { step:1, typeSlug:'', typeLabel:'', typeCpt:'', typeColor:'', typeExtra:[], name:'', slug:'', desc:'', price:'0.00', imageId:0, imageUrl:'', meta:{} };
				$('.plbw-type-card').removeClass('is-selected');
				$('#plbw-name,#plbw-slug,#plbw-description').val('');
				$('#plbw-price').val('0.00');
				$('#plbw-sort-order').val('0');
				$('#plbw-image-id,#plbw-image-url').val('');
				$('#plbw-image-preview').html('<span class="dashicons dashicons-format-image plbw-img-icon"></span><p><?php esc_html_e( 'No image selected', 'pizzalayer' ); ?></p>');
				$('#plbw-remove-image').hide();
				$('#plbw-step1-next').prop('disabled', true);
				$('#plbw-slug').data('manual', false);
				$('.plbw-field-error').removeClass('plbw-field-error');
				$('#plbw-success-panel').hide();
				goStep(1);
			});

			$(document).on('click', '.plbw-copy-btn', function(){
				var text = $(this).data('clipboard');
				if (navigator.clipboard) {
					navigator.clipboard.writeText(text);
				} else {
					var ta = document.createElement('textarea');
					ta.value = text; document.body.appendChild(ta);
					ta.select(); document.execCommand('copy');
					document.body.removeChild(ta);
				}
				$(this).text('<?php esc_html_e( 'Copied!', 'pizzalayer' ); ?>').addClass('plbw-copied');
				var $btn = $(this);
				setTimeout(function(){ $btn.html('<span class="dashicons dashicons-clipboard"></span> <?php esc_html_e( 'Copy', 'pizzalayer' ); ?>').removeClass('plbw-copied'); }, 1800);
			});

			/* ── Helpers ───────────────────────────────────────── */
			function slugify(s) {
				return (s || '').toLowerCase().replace(/[^a-z0-9\s\-]/g,'').replace(/\s+/g,'-').replace(/-+/g,'-').substring(0,60);
			}
			function escHtml(s) {
				return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
			}
			function escAttr(s) { return escHtml(s); }

		})(jQuery);
		</script>
		<?php
	}

	/** AJAX: save layer post */
	public function ajax_save_layer(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Unauthorized' ], 403 );
		}
		check_ajax_referer( 'pizzalayer_wizard_save', 'nonce' );

		$type     = isset( $_POST['type'] )  ? sanitize_key( wp_unslash( $_POST['type'] ) )    : '';
		$cpt      = isset( $_POST['cpt'] )   ? sanitize_key( wp_unslash( $_POST['cpt'] ) )     : '';
		$name     = isset( $_POST['name'] )  ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$slug     = isset( $_POST['slug'] )  ? sanitize_title( wp_unslash( $_POST['slug'] ) )  : '';
		$desc     = isset( $_POST['desc'] )  ? sanitize_textarea_field( wp_unslash( $_POST['desc'] ) ) : '';
		$price    = isset( $_POST['price'] ) ? (float) $_POST['price']                         : 0.0;
		$image_id = isset( $_POST['image_id'] ) ? absint( $_POST['image_id'] )                 : 0;
		$meta_raw = isset( $_POST['meta'] )  ? sanitize_text_field( wp_unslash( $_POST['meta'] ) ) : '{}';

		if ( ! $name || ! $cpt ) {
			wp_send_json_error( [ 'message' => __( 'Name and layer type are required.', 'pizzalayer' ) ] );
		}

		// Validate CPT is one of ours
		$valid_cpts = [];
		foreach ( self::LAYER_TYPES as $cfg ) {
			$valid_cpts[] = $cfg['cpt'];
		}
		if ( ! in_array( $cpt, $valid_cpts, true ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid layer type.', 'pizzalayer' ) ] );
		}

		// Create the post
		$post_id = wp_insert_post( [
			'post_title'   => $name,
			'post_name'    => $slug ?: sanitize_title( $name ),
			'post_content' => $desc,
			'post_type'    => $cpt,
			'post_status'  => 'publish',
		], true );

		if ( is_wp_error( $post_id ) ) {
			wp_send_json_error( [ 'message' => $post_id->get_error_message() ] );
		}

		// Save price
		if ( $price > 0 ) {
			update_post_meta( $post_id, '_pizzalayer_price', $price );
		}

		// Save image
		if ( $image_id ) {
			update_post_meta( $post_id, '_pizzalayer_layer_image_id', $image_id );
			set_post_thumbnail( $post_id, $image_id );
		}

		// Save meta fields
		$meta = json_decode( $meta_raw, true );
		if ( is_array( $meta ) ) {
			$allowed_meta = [ 'calories', 'thickness', 'diameter_inches', 'spice_level',
				'is_vegetarian', 'is_vegan', 'is_gluten_free', 'is_dairy_free', 'sort_order' ];
			foreach ( $allowed_meta as $key ) {
				if ( isset( $meta[ $key ] ) ) {
					update_post_meta( $post_id, '_pizzalayer_' . $key, sanitize_text_field( $meta[ $key ] ) );
				}
			}
		}

		// Build shortcode
		$layer_type_map = [
			'toppings' => 'toppings',
			'crusts'   => 'crust',
			'sauces'   => 'sauce',
			'cheeses'  => 'cheese',
			'drizzles' => 'drizzle',
			'cuts'     => 'cut',
			'sizes'    => 'size',
		];
		$sc_type   = $layer_type_map[ $type ] ?? $type;
		$post_slug = get_post_field( 'post_name', $post_id );
		$shortcode = '[pizza_layer type="' . $sc_type . '" slug="' . $post_slug . '"]';

		wp_send_json_success( [
			'post_id'   => $post_id,
			'name'      => $name,
			'shortcode' => $shortcode,
			'edit_url'  => get_edit_post_link( $post_id, 'raw' ),
			'list_url'  => admin_url( 'admin.php?page=pizzalayer-content&pl_cpt=' . $type ),
		] );
	}

	private function render_styles(): void {
		?>
		<style>
		/* ══ Layer Builder Wizard ═══════════════════════════════════════ */
		.plbw-wrap { max-width: 820px; }

		/* Header */
		.plbw-header {
			display: flex;
			align-items: center;
			gap: 14px;
			margin: 0 0 24px;
			padding: 18px 20px;
			background: linear-gradient(135deg, #1a1a2e 0%, #2d1b0e 100%);
			border-radius: 8px;
			border-left: 4px solid #ff6b35;
		}
		.plbw-header-icon {
			flex-shrink: 0;
			width: 44px;
			height: 44px;
			fill: #ff6b35;
		}
		.plbw-header__title { color: #fff !important; margin: 0 0 4px !important; font-size: 20px !important; }
		.plbw-header__sub   { color: #aaa; margin: 0; font-size: 13px; }

		/* Progress bar */
		.plbw-progress {
			display: flex;
			align-items: center;
			margin-bottom: 28px;
			padding: 0 4px;
		}
		.plbw-step {
			display: flex;
			flex-direction: column;
			align-items: center;
			gap: 6px;
			flex-shrink: 0;
		}
		.plbw-step-circle {
			width: 32px;
			height: 32px;
			border-radius: 50%;
			background: #ddd;
			color: #888;
			font-weight: 700;
			font-size: 14px;
			display: flex;
			align-items: center;
			justify-content: center;
			transition: background .2s, color .2s;
		}
		.plbw-step.is-active .plbw-step-circle {
			background: #ff6b35;
			color: #fff;
			box-shadow: 0 0 0 3px rgba(255,107,53,.2);
		}
		.plbw-step.is-done .plbw-step-circle {
			background: #27ae60;
			color: #fff;
		}
		.plbw-step.is-done .plbw-step-circle::before {
			content: '✓';
			font-size: 16px;
		}
		.plbw-step.is-done .plbw-step-circle { font-size: 0; }
		.plbw-step-label {
			font-size: 11px;
			color: #888;
			white-space: nowrap;
		}
		.plbw-step.is-active .plbw-step-label { color: #ff6b35; font-weight: 600; }
		.plbw-step.is-done   .plbw-step-label { color: #27ae60; }
		.plbw-step-connector {
			flex: 1;
			height: 2px;
			background: #ddd;
			margin: 0 8px;
			margin-bottom: 18px;
		}

		/* Panels */
		.plbw-panel {
			background: #fff;
			border: 1px solid #ddd;
			border-radius: 8px;
			padding: 28px;
			position: relative;
		}
		.plbw-panel-title { margin-top: 0; font-size: 18px; }
		.plbw-panel-sub   { color: #666; margin-top: -8px; }

		/* Type grid */
		.plbw-type-grid {
			display: grid;
			grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
			gap: 12px;
			margin: 20px 0;
		}
		.plbw-type-card {
			border: 2px solid #e0e0e0;
			border-radius: 8px;
			padding: 16px 12px;
			background: #fafafa;
			cursor: pointer;
			text-align: center;
			transition: border-color .15s, box-shadow .15s, background .15s;
			position: relative;
			display: flex;
			flex-direction: column;
			align-items: center;
			gap: 6px;
		}
		.plbw-type-card:hover {
			border-color: var(--plbw-accent, #ff6b35);
			background: #fff;
			box-shadow: 0 2px 8px rgba(0,0,0,.08);
		}
		.plbw-type-card.is-selected {
			border-color: var(--plbw-accent, #ff6b35);
			background: #fff;
			box-shadow: 0 0 0 3px rgba(255,107,53,.15);
		}
		.plbw-type-emoji { font-size: 28px; line-height: 1; }
		.plbw-type-name  { font-weight: 700; font-size: 14px; }
		.plbw-type-desc  { font-size: 11px; color: #777; line-height: 1.4; }
		.plbw-type-check {
			position: absolute;
			top: 6px; right: 6px;
			font-size: 18px !important;
			color: var(--plbw-accent, #ff6b35);
			opacity: 0;
			transition: opacity .15s;
		}
		.plbw-type-card.is-selected .plbw-type-check { opacity: 1; }

		/* Fields */
		.plbw-fields { margin: 16px 0; }
		.plbw-field-row { margin-bottom: 18px; }
		.plbw-field-row.plbw-field-error .plbw-input { border-color: #c00 !important; box-shadow: 0 0 0 1px #c00 !important; }
		.plbw-label { display: block; font-weight: 600; margin-bottom: 5px; font-size: 13px; }
		.plbw-required { color: #c00; margin-left: 2px; }
		.plbw-input { width: 100%; max-width: 440px; }
		.plbw-textarea { width: 100%; max-width: 440px; resize: vertical; }
		.plbw-select  { max-width: 260px; }
		.plbw-help    { color: #666; font-size: 12px; margin-top: 4px; }
		.plbw-check-row { display: flex; gap: 16px; flex-wrap: wrap; margin-top: 6px; }
		.plbw-check-label { display: flex; align-items: center; gap: 6px; font-size: 13px; cursor: pointer; }

		/* Nav row */
		.plbw-nav-row {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-top: 24px;
			padding-top: 20px;
			border-top: 1px solid #eee;
		}
		.plbw-nav-row .button .dashicons { vertical-align: middle; margin: 0 2px; font-size: 14px; width: 14px; height: 14px; }

		/* Image area */
		.plbw-image-area { margin: 16px 0; }
		.plbw-image-preview {
			border: 2px dashed #ccc;
			border-radius: 8px;
			padding: 32px;
			text-align: center;
			margin-bottom: 14px;
			background: #fafafa;
			min-height: 120px;
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: center;
			gap: 8px;
		}
		.plbw-img-icon { font-size: 40px !important; width: 40px !important; height: 40px !important; color: #bbb; }
		.plbw-image-actions { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
		.plbw-lim-btn { border-color: #ff6b35 !important; color: #ff6b35 !important; }
		.plbw-lim-btn:hover { background: #ff6b35 !important; color: #fff !important; }
		.plbw-image-tip {
			background: #f0f6ff;
			border-left: 3px solid #2980b9;
			padding: 10px 12px;
			border-radius: 0 4px 4px 0;
			font-size: 12px;
			color: #555;
			display: flex;
			align-items: flex-start;
			gap: 8px;
		}
		.plbw-image-tip .dashicons { flex-shrink: 0; color: #2980b9; }

		/* Review */
		.plbw-review-card { border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; }
		.plbw-review-type {
			background: linear-gradient(135deg, #1a1a2e, #2d1b0e);
			padding: 16px 20px;
			display: flex;
			align-items: center;
			gap: 12px;
			border-left: 4px solid var(--plbw-accent, #ff6b35);
		}
		.plbw-review-emoji      { font-size: 28px; }
		.plbw-review-type-label { color: #fff; font-size: 16px; font-weight: 700; }
		.plbw-review-table { width: 100%; border-collapse: collapse; }
		.plbw-review-table th, .plbw-review-table td {
			padding: 10px 16px;
			text-align: left;
			border-bottom: 1px solid #f0f0f0;
			font-size: 13px;
			vertical-align: middle;
		}
		.plbw-review-table th { width: 160px; font-weight: 600; color: #555; background: #fafafa; }
		.plbw-review-table tr:last-child th,
		.plbw-review-table tr:last-child td { border-bottom: none; }
		.plbw-review-table code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-size: 12px; }

		/* Saving overlay */
		.plbw-saving-overlay {
			position: absolute; inset: 0;
			background: rgba(255,255,255,.8);
			display: flex; flex-direction: column;
			align-items: center; justify-content: center;
			gap: 12px;
			border-radius: 8px;
			font-size: 14px;
			color: #555;
		}
		.plbw-saving-overlay .spinner { float: none; margin: 0; }

		/* Success panel */
		.plbw-success-panel { text-align: center; }
		.plbw-success-check {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			width: 64px; height: 64px;
			border-radius: 50%;
			background: #27ae60;
			margin: 0 auto 16px;
		}
		.plbw-success-check .dashicons { color: #fff; font-size: 36px !important; width: 36px !important; height: 36px !important; }
		.plbw-success-title { font-size: 22px; margin: 0 0 10px; }
		.plbw-shortcode-box {
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 10px;
			background: #f4f4f4;
			border: 1px solid #ddd;
			border-radius: 6px;
			padding: 12px 16px;
			margin: 16px auto;
			max-width: 480px;
		}
		.plbw-shortcode-box code { font-size: 14px; color: #1a1a2e; }
		.plbw-copy-btn { flex-shrink: 0; }
		.plbw-copy-btn.plbw-copied { background: #27ae60 !important; color: #fff !important; border-color: #27ae60 !important; }
		.plbw-success-actions { display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; margin-top: 16px; }
		.plbw-success-actions .button .dashicons { vertical-align: middle; font-size: 14px; width: 14px; height: 14px; margin-right: 4px; }
		</style>
		<?php
	}
}
