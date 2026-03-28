<?php
/**
 * NightPie Template — [pizza_builder] output.
 *
 * This file is included (not called as a function) by BuilderShortcode.
 * Variables available from the shortcode:
 *   $instance_id  — unique ID string (e.g. "pizza-1", "pizzabuilder-1")
 *   $atts         — shortcode attribute array
 *
 * Multi-instance support: every JS reference uses $np_var (the per-instance
 * namespace) instead of the global "NP", so multiple builders on one page
 * each maintain independent state.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Ensure we have all expected variables (guard for direct include)
if ( ! isset( $instance_id ) )    { $instance_id    = 'pizzabuilder-1'; }
if ( ! isset( $atts ) )           { $atts           = []; }
if ( ! isset( $template_slug ) )  { $template_slug  = 'nightpie'; }
if ( ! isset( $function_prefix ) ) { $function_prefix = 'pzt_nightpie'; }

// Per-instance JS namespace: NP_pizza1, NP_pizzabuilder2, etc.
$np_var = 'NP_' . preg_replace( '/[^a-zA-Z0-9_]/', '_', $instance_id );

// Resolve max toppings: shortcode attr → plugin option → default 99
$max_toppings = isset( $atts['max_toppings'] ) && (int) $atts['max_toppings'] > 0
	? (int) $atts['max_toppings']
	: intval( get_option( 'pizzalayer_setting_topping_maxtoppings', 0 ) );
if ( $max_toppings < 1 ) { $max_toppings = 99; }

// Apply developer filter
$max_toppings = (int) apply_filters( 'pizzalayer_max_toppings', $max_toppings, $instance_id );

// Resolve pizza shape: shortcode attr → plugin option → 'round'
$valid_shapes  = [ 'round', 'square', 'rectangle', 'custom' ];
$pizza_shape   = sanitize_key( $atts['pizza_shape'] ?? get_option( 'pizzalayer_setting_pizza_shape', 'round' ) );
if ( ! in_array( $pizza_shape, $valid_shapes, true ) ) { $pizza_shape = 'round'; }
$pizza_aspect  = sanitize_text_field( $atts['pizza_aspect']  ?? get_option( 'pizzalayer_setting_pizza_aspect',  '1 / 1' ) );
$pizza_radius  = sanitize_text_field( $atts['pizza_radius']  ?? get_option( 'pizzalayer_setting_pizza_radius',  '8px'   ) );

// Resolve layer animation: shortcode attr → plugin option → 'fade'
$valid_anims   = [ 'fade', 'scale-in', 'slide-up', 'flip-in', 'drop-in', 'instant' ];
$layer_anim    = sanitize_key( $atts['layer_anim'] ?? get_option( 'pizzalayer_setting_layer_anim', 'fade' ) );
if ( ! in_array( $layer_anim, $valid_anims, true ) ) { $layer_anim = 'fade'; }
$layer_anim_speed = max( 80, min( 800, (int) get_option( 'pizzalayer_setting_layer_anim_speed', 320 ) ) );

// Resolve hidden tabs
$hide_tabs_raw = $atts['hide_tabs'] ?? '';
$show_tabs_raw = $atts['show_tabs'] ?? '';
$all_tabs      = [ 'crust', 'sauce', 'cheese', 'toppings', 'drizzle', 'slicing', 'yourpizza' ];
$all_tabs      = apply_filters( 'pizzalayer_tab_order', $all_tabs, $instance_id );

if ( $show_tabs_raw ) {
	$visible_tabs = array_intersect( $all_tabs, array_map( 'trim', explode( ',', $show_tabs_raw ) ) );
} elseif ( $hide_tabs_raw ) {
	$hide_set     = array_map( 'trim', explode( ',', $hide_tabs_raw ) );
	$visible_tabs = array_diff( $all_tabs, $hide_set );
} else {
	$visible_tabs = $all_tabs;
}

// Query all CPTs
$query_base = [
	'posts_per_page' => -1,
	'post_status'    => 'publish',
	'orderby'        => 'menu_order title',
	'order'          => 'ASC',
];
$crusts   = apply_filters( 'pizzalayer_query_args_crusts',   get_posts( array_merge( $query_base, [ 'post_type' => 'pizzalayer_crusts'   ] ) ), 'crusts'   );
$sauces   = apply_filters( 'pizzalayer_query_args_sauces',   get_posts( array_merge( $query_base, [ 'post_type' => 'pizzalayer_sauces'   ] ) ), 'sauces'   );
$cheeses  = apply_filters( 'pizzalayer_query_args_cheeses',  get_posts( array_merge( $query_base, [ 'post_type' => 'pizzalayer_cheeses'  ] ) ), 'cheeses'  );
$drizzles = apply_filters( 'pizzalayer_query_args_drizzles', get_posts( array_merge( $query_base, [ 'post_type' => 'pizzalayer_drizzles' ] ) ), 'drizzles' );
$toppings = apply_filters( 'pizzalayer_query_args_toppings', get_posts( array_merge( $query_base, [ 'post_type' => 'pizzalayer_toppings' ] ) ), 'toppings' );
$cuts     = apply_filters( 'pizzalayer_query_args_cuts',     get_posts( array_merge( $query_base, [ 'post_type' => 'pizzalayer_cuts'     ] ) ), 'cuts'     );

/**
 * Build an exclusive-select card (crust / sauce / cheese / drizzle / cut).
 * Uses $np_var for JS calls instead of global NP.
 */
if ( ! function_exists( 'pzt_nightpie_exclusive_card' ) ) :
function pzt_nightpie_exclusive_card( $post, string $layer_type, string $np_var, int $zindex = 200 ): string {
	if ( ! ( $post instanceof \WP_Post ) ) { return ''; }
	$id        = $post->ID;
	$title     = get_the_title( $post );
	$slug      = sanitize_title( $title );
	$img_field = $layer_type . '_image';
	$lyr_field = $layer_type . '_layer_image';

	$thumb_url = get_field( $img_field, $id ) ?: get_field( $lyr_field, $id ) ?: (string) get_the_post_thumbnail_url( $id, 'medium' );
	$layer_url = get_field( $lyr_field, $id ) ?: $thumb_url;

	$js_title  = esc_js( $title );
	$js_layer  = esc_js( (string) $layer_url );
	$js_add    = "window['{$np_var}']&&window['{$np_var}'].swapBase('{$layer_type}','{$slug}','{$js_title}','{$js_layer}',this)";
	$js_remove = "window['{$np_var}']&&window['{$np_var}'].removeBase('{$layer_type}','{$slug}',this)";

	ob_start();
	do_action( 'pizzalayer_before_layer_card', $post, $layer_type );
	?>
	<div class="np-card np-card--exclusive"
	     data-layer="<?php echo esc_attr( $layer_type ); ?>"
	     data-slug="<?php echo esc_attr( $slug ); ?>"
	     data-title="<?php echo esc_attr( $title ); ?>"
	     data-thumb="<?php echo esc_attr( (string) $thumb_url ); ?>"
	     data-layer-img="<?php echo esc_attr( (string) $layer_url ); ?>">
		<div class="np-card__thumb-wrap">
			<?php if ( $thumb_url ) : ?>
				<img class="np-card__thumb" src="<?php echo esc_url( (string) $thumb_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy" />
			<?php else : ?>
				<div class="np-card__thumb np-card__thumb--placeholder"></div>
			<?php endif; ?>
			<div class="np-card__check"><i class="fa fa-check"></i></div>
		</div>
		<div class="np-card__body">
			<span class="np-card__name"><?php echo esc_html( $title ); ?></span>
		</div>
		<div class="np-card__actions">
			<button type="button" class="np-btn np-btn--add" onclick="<?php echo esc_attr( $js_add ); ?>">
				<i class="fa fa-plus"></i> <?php esc_html_e( 'Add', 'pizzalayer' ); ?>
			</button>
			<button type="button" class="np-btn np-btn--remove" style="display:none;" onclick="<?php echo esc_attr( $js_remove ); ?>">
				<i class="fa fa-times"></i> <?php esc_html_e( 'Remove', 'pizzalayer' ); ?>
			</button>
		</div>
	</div>
	<?php
	do_action( 'pizzalayer_after_layer_card', $post, $layer_type );
	return apply_filters( 'pizzalayer_card_html', ob_get_clean(), $post, $layer_type );
}
endif;

/**
 * Build a topping card (multi-select with coverage picker).
 */
if ( ! function_exists( 'pzt_nightpie_topping_card' ) ) :
function pzt_nightpie_topping_card( $post, string $np_var, int $zindex ): string {
	if ( ! ( $post instanceof \WP_Post ) ) { return ''; }
	$id        = $post->ID;
	$title     = get_the_title( $post );
	$slug      = sanitize_title( $title );
	$layer_id  = 'pizzalayer-topping-' . $slug;

	$thumb_url = get_field( 'topping_image', $id ) ?: get_field( 'topping_layer_image', $id ) ?: (string) get_the_post_thumbnail_url( $id, 'medium' );
	$layer_url = get_field( 'topping_layer_image', $id ) ?: $thumb_url;

	$js_title  = esc_js( $title );
	$js_slug   = esc_js( $slug );
	$js_layer  = esc_js( (string) $layer_url );

	$js_add    = "window['{$np_var}']&&window['{$np_var}'].addTopping({$zindex},'{$js_slug}','{$js_layer}','{$js_title}','{$layer_id}','{$layer_id}',this)";
	$js_remove = "window['{$np_var}']&&window['{$np_var}'].removeTopping('pizzalayer-topping-{$js_slug}','{$js_slug}',this)";

	ob_start();
	do_action( 'pizzalayer_before_layer_card', $post, 'toppings' );
	?>
	<div class="np-card np-card--topping"
	     data-layer="toppings"
	     data-slug="<?php echo esc_attr( $slug ); ?>"
	     data-title="<?php echo esc_attr( $title ); ?>"
	     data-thumb="<?php echo esc_attr( (string) $thumb_url ); ?>"
	     data-layer-img="<?php echo esc_attr( (string) $layer_url ); ?>"
	     data-zindex="<?php echo esc_attr( (string) $zindex ); ?>">
		<div class="np-card__thumb-wrap">
			<?php if ( $thumb_url ) : ?>
				<img class="np-card__thumb" src="<?php echo esc_url( (string) $thumb_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy" />
			<?php else : ?>
				<div class="np-card__thumb np-card__thumb--placeholder"></div>
			<?php endif; ?>
			<div class="np-card__check"><i class="fa fa-check"></i></div>
		</div>
		<div class="np-card__body">
			<span class="np-card__name"><?php echo esc_html( $title ); ?></span>
			<div class="np-coverage" style="display:none;">
				<span class="np-coverage__label"><?php esc_html_e( 'Coverage:', 'pizzalayer' ); ?></span>
				<div class="np-coverage__btns">
					<?php
					$coverages = [ 'whole' => 'Whole', 'half-left' => 'Left', 'half-right' => 'Right',
					               'quarter-top-left' => 'Q1', 'quarter-top-right' => 'Q2',
					               'quarter-bottom-left' => 'Q3', 'quarter-bottom-right' => 'Q4' ];
					foreach ( $coverages as $fraction => $label ) :
						$js_cov = "window['{$np_var}']&&window['{$np_var}'].setCoverage('" . esc_js( $slug ) . "','" . esc_js( $fraction ) . "',this)";
						$ico    = 'np-cov-ico--' . str_replace( [ 'half-', 'quarter-' ], [ '', '' ], $fraction );
					?>
					<button type="button" class="np-cov-btn" data-fraction="<?php echo esc_attr( $fraction ); ?>"
					        onclick="<?php echo esc_attr( $js_cov ); ?>">
						<span class="np-cov-ico <?php echo esc_attr( $ico ); ?>"></span>
						<?php echo esc_html( $label ); ?>
					</button>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<div class="np-card__actions">
			<button type="button" class="np-btn np-btn--add" onclick="<?php echo esc_attr( $js_add ); ?>">
				<i class="fa fa-plus"></i> <?php esc_html_e( 'Add', 'pizzalayer' ); ?>
			</button>
			<button type="button" class="np-btn np-btn--remove" style="display:none;" onclick="<?php echo esc_attr( $js_remove ); ?>">
				<i class="fa fa-times"></i> <?php esc_html_e( 'Remove', 'pizzalayer' ); ?>
			</button>
		</div>
	</div>
	<?php
	do_action( 'pizzalayer_after_layer_card', $post, 'toppings' );
	return apply_filters( 'pizzalayer_card_html', ob_get_clean(), $post, 'toppings' );
}
endif;

// Render card HTML for each tab
$crusts_html = '';
foreach ( $crusts as $post ) { $crusts_html .= pzt_nightpie_exclusive_card( $post, 'crust', $np_var, 100 ); }
if ( ! $crusts_html ) { $crusts_html = '<p class="np-empty"><i class="fa fa-circle-exclamation"></i> ' . esc_html__( 'No crusts found.', 'pizzalayer' ) . '</p>'; }

$sauces_html = '';
foreach ( $sauces as $post ) { $sauces_html .= pzt_nightpie_exclusive_card( $post, 'sauce', $np_var, 150 ); }
if ( ! $sauces_html ) { $sauces_html = '<p class="np-empty">' . esc_html__( 'No sauces found.', 'pizzalayer' ) . '</p>'; }

$cheeses_html = '';
foreach ( $cheeses as $post ) { $cheeses_html .= pzt_nightpie_exclusive_card( $post, 'cheese', $np_var, 200 ); }
if ( ! $cheeses_html ) { $cheeses_html = '<p class="np-empty">' . esc_html__( 'No cheeses found.', 'pizzalayer' ) . '</p>'; }

$drizzles_html = '';
foreach ( $drizzles as $post ) { $drizzles_html .= pzt_nightpie_exclusive_card( $post, 'drizzle', $np_var, 900 ); }
if ( ! $drizzles_html ) { $drizzles_html = '<p class="np-empty">' . esc_html__( 'No drizzles found.', 'pizzalayer' ) . '</p>'; }

$toppings_html = '';
$t_z = 400;
foreach ( $toppings as $post ) { $toppings_html .= pzt_nightpie_topping_card( $post, $np_var, $t_z ); $t_z += 10; }
if ( ! $toppings_html ) { $toppings_html = '<p class="np-empty">' . esc_html__( 'No toppings found.', 'pizzalayer' ) . '</p>'; }

$cuts_html = '';
foreach ( $cuts as $post ) { $cuts_html .= pzt_nightpie_exclusive_card( $post, 'cut', $np_var, 950 ); }
if ( ! $cuts_html ) { $cuts_html = '<p class="np-empty">' . esc_html__( 'No cut styles found.', 'pizzalayer' ) . '</p>'; }

// Use PizzaBuilder for the initial pizza display
$builder = new \PizzaLayer\Builder\PizzaBuilder();
$initial_pizza = $builder->build_dynamic(
	$atts['default_crust']  ?? '',
	$atts['default_sauce']  ?? '',
	$atts['default_cheese'] ?? ''
);

// Pass $np_var, $instance_id to custom.js via data attribute on root
?>
<!-- ═══════════════════════════════════════════════════
     NIGHTPIE TEMPLATE — PizzaLayer
     Instance: <?php echo esc_html( $instance_id ); ?>
═══════════════════════════════════════════════════ -->
<div id="<?php echo esc_attr( $instance_id ); ?>"
     class="np-root"
     data-instance="<?php echo esc_attr( $instance_id ); ?>"
     data-np-var="<?php echo esc_attr( $np_var ); ?>"
     data-max-toppings="<?php echo esc_attr( (string) $max_toppings ); ?>"
     data-pizza-shape="<?php echo esc_attr( $pizza_shape ); ?>"
     data-pizza-aspect="<?php echo esc_attr( $pizza_aspect ); ?>"
     data-pizza-radius="<?php echo esc_attr( $pizza_radius ); ?>"
     data-layer-anim="<?php echo esc_attr( $layer_anim ); ?>"
     data-layer-anim-speed="<?php echo esc_attr( (string) $layer_anim_speed ); ?>">

	<!-- Mobile mini-bar -->
	<div class="np-mobile-preview-bar">
		<div class="np-mobile-preview-bar__inner">
			<span class="np-mobile-preview-bar__label"><i class="fa fa-pizza-slice"></i> <?php esc_html_e( 'Live Preview', 'pizzalayer' ); ?></span>
			<div class="np-mobile-preview-bar__pizza" id="<?php echo esc_attr( $instance_id ); ?>-pizza-mobile-slot"></div>
			<button class="np-mobile-preview-bar__toggle" id="<?php echo esc_attr( $instance_id ); ?>-mobile-toggle" aria-label="<?php esc_attr_e( 'Toggle pizza preview', 'pizzalayer' ); ?>">
				<i class="fa fa-chevron-down"></i>
			</button>
		</div>
		<div class="np-mobile-preview-bar__expanded" id="<?php echo esc_attr( $instance_id ); ?>-mobile-expanded" aria-hidden="true"></div>
	</div>

	<!-- Main layout -->
	<div class="np-layout">
		<div class="np-layout__row">

			<!-- LEFT: sticky pizza visualizer -->
			<div class="np-pizza-col" id="<?php echo esc_attr( $instance_id ); ?>-pizza-col">
				<div class="np-pizza-sticky">
					<div class="np-pizza-sticky__header">
						<i class="fa fa-pizza-slice"></i>
						<span><?php esc_html_e( 'Your Pizza', 'pizzalayer' ); ?></span>
					</div>
					<div class="np-pizza-sticky__canvas" id="<?php echo esc_attr( $instance_id ); ?>-canvas">
						<?php echo $initial_pizza; // phpcs:ignore WordPress.Security.EscapeOutput -- built by PizzaBuilder with proper escaping ?>
					</div>
					<div class="np-pizza-sticky__footer">
						<button type="button" class="np-btn np-btn--ghost np-btn--sm"
						        onclick="ClearPizza(); window['<?php echo esc_js( $np_var ); ?>']&&window['<?php echo esc_js( $np_var ); ?>'].resetAll();">
							<i class="fa fa-rotate-left"></i> <?php esc_html_e( 'Reset', 'pizzalayer' ); ?>
						</button>
						<span class="np-topping-counter">
							<i class="fa fa-layer-group"></i>
							<span id="<?php echo esc_attr( $instance_id ); ?>-count">0</span> / <?php echo esc_html( (string) $max_toppings ); ?> <?php esc_html_e( 'toppings', 'pizzalayer' ); ?>
						</span>
					</div>

					<!-- Action bar: PizzaLayerPro hooks here for WC cart button -->
					<?php do_action( 'pizzalayer_builder_action_bar', $instance_id ); ?>
				</div>
			</div>

			<!-- RIGHT: tabbed builder -->
			<div class="np-tabs-col">
				<div class="np-builder">

					<nav class="np-tabnav" id="<?php echo esc_attr( $instance_id ); ?>-tabnav" role="tablist">
						<?php
						$tab_meta = [
							'crust'     => [ 'fa-circle',      __( 'Crust',     'pizzalayer' ) ],
							'sauce'     => [ 'fa-droplet',     __( 'Sauce',     'pizzalayer' ) ],
							'cheese'    => [ 'fa-cheese',      __( 'Cheese',    'pizzalayer' ) ],
							'toppings'  => [ 'fa-seedling',    __( 'Toppings',  'pizzalayer' ) ],
							'drizzle'   => [ 'fa-wine-glass',  __( 'Drizzle',   'pizzalayer' ) ],
							'slicing'   => [ 'fa-pizza-slice', __( 'Slicing',   'pizzalayer' ) ],
							'yourpizza' => [ 'fa-receipt',     __( 'Your Pizza','pizzalayer' ) ],
						];
						$first_tab = true;
						foreach ( $visible_tabs as $tab ) :
							if ( ! isset( $tab_meta[ $tab ] ) ) { continue; }
							[ $icon, $label ] = $tab_meta[ $tab ];
							$active = $first_tab ? 'active' : '';
							$selected = $first_tab ? 'true' : 'false';
							$first_tab = false;
						?>
						<button class="np-tab <?php echo esc_attr( $active ); ?>"
						        data-tab="<?php echo esc_attr( $tab ); ?>"
						        data-instance="<?php echo esc_attr( $instance_id ); ?>"
						        role="tab" aria-selected="<?php echo esc_attr( $selected ); ?>"
						        aria-controls="<?php echo esc_attr( $instance_id . '-panel-' . $tab ); ?>">
							<span class="np-tab__icon"><i class="fa <?php echo esc_attr( $icon ); ?>"></i></span>
							<span class="np-tab__label"><?php echo esc_html( $label ); ?></span>
						</button>
						<?php endforeach; ?>
					</nav>

					<!-- Progress dots -->
					<div class="np-progress" aria-hidden="true">
						<?php foreach ( $visible_tabs as $s ) : ?>
						<span class="np-progress__dot" data-step="<?php echo esc_attr( $s ); ?>"></span>
						<?php endforeach; ?>
					</div>

					<!-- Tab panels -->
					<div class="np-panels">

						<?php if ( in_array( 'crust', $visible_tabs, true ) ) : ?>
						<?php do_action( 'pizzalayer_before_tab_crust', $instance_id ); ?>
						<section class="np-panel active" id="<?php echo esc_attr( $instance_id ); ?>-panel-crust" role="tabpanel">
							<div class="np-panel__header">
								<h2 class="np-panel__title"><i class="fa fa-circle"></i> <?php esc_html_e( 'Choose Your Crust', 'pizzalayer' ); ?></h2>
								<p class="np-panel__hint"><?php esc_html_e( 'Select one crust — it forms the base of your pizza.', 'pizzalayer' ); ?></p>
							</div>
							<div class="np-cards-grid np-cards-grid--exclusive"><?php echo $crusts_html; // phpcs:ignore ?></div>
							<div class="np-panel__nav">
								<span></span>
								<button class="np-btn np-btn--next" onclick="<?php echo esc_js( $np_var ); ?>.goTab('sauce')"><?php esc_html_e( 'Sauce', 'pizzalayer' ); ?> <i class="fa fa-arrow-right"></i></button>
							</div>
						</section>
						<?php do_action( 'pizzalayer_after_tab_crust', $instance_id ); ?>
						<?php endif; ?>

						<?php if ( in_array( 'sauce', $visible_tabs, true ) ) : ?>
						<?php do_action( 'pizzalayer_before_tab_sauce', $instance_id ); ?>
						<section class="np-panel" id="<?php echo esc_attr( $instance_id ); ?>-panel-sauce" role="tabpanel">
							<div class="np-panel__header">
								<h2 class="np-panel__title"><i class="fa fa-droplet"></i> <?php esc_html_e( 'Choose Your Sauce', 'pizzalayer' ); ?></h2>
								<p class="np-panel__hint"><?php esc_html_e( 'Select one sauce.', 'pizzalayer' ); ?></p>
							</div>
							<div class="np-cards-grid np-cards-grid--exclusive"><?php echo $sauces_html; // phpcs:ignore ?></div>
							<div class="np-panel__nav">
								<button class="np-btn np-btn--prev" onclick="<?php echo esc_js( $np_var ); ?>.goTab('crust')"><i class="fa fa-arrow-left"></i> <?php esc_html_e( 'Crust', 'pizzalayer' ); ?></button>
								<button class="np-btn np-btn--next" onclick="<?php echo esc_js( $np_var ); ?>.goTab('cheese')"><?php esc_html_e( 'Cheese', 'pizzalayer' ); ?> <i class="fa fa-arrow-right"></i></button>
							</div>
						</section>
						<?php do_action( 'pizzalayer_after_tab_sauce', $instance_id ); ?>
						<?php endif; ?>

						<?php if ( in_array( 'cheese', $visible_tabs, true ) ) : ?>
						<?php do_action( 'pizzalayer_before_tab_cheese', $instance_id ); ?>
						<section class="np-panel" id="<?php echo esc_attr( $instance_id ); ?>-panel-cheese" role="tabpanel">
							<div class="np-panel__header">
								<h2 class="np-panel__title"><i class="fa fa-cheese"></i> <?php esc_html_e( 'Choose Your Cheese', 'pizzalayer' ); ?></h2>
								<p class="np-panel__hint"><?php esc_html_e( 'Select one cheese.', 'pizzalayer' ); ?></p>
							</div>
							<div class="np-cards-grid np-cards-grid--exclusive"><?php echo $cheeses_html; // phpcs:ignore ?></div>
							<div class="np-panel__nav">
								<button class="np-btn np-btn--prev" onclick="<?php echo esc_js( $np_var ); ?>.goTab('sauce')"><i class="fa fa-arrow-left"></i> <?php esc_html_e( 'Sauce', 'pizzalayer' ); ?></button>
								<button class="np-btn np-btn--next" onclick="<?php echo esc_js( $np_var ); ?>.goTab('toppings')"><?php esc_html_e( 'Toppings', 'pizzalayer' ); ?> <i class="fa fa-arrow-right"></i></button>
							</div>
						</section>
						<?php do_action( 'pizzalayer_after_tab_cheese', $instance_id ); ?>
						<?php endif; ?>

						<?php if ( in_array( 'toppings', $visible_tabs, true ) ) : ?>
						<?php do_action( 'pizzalayer_before_tab_toppings', $instance_id ); ?>
						<section class="np-panel" id="<?php echo esc_attr( $instance_id ); ?>-panel-toppings" role="tabpanel">
							<div class="np-panel__header">
								<h2 class="np-panel__title"><i class="fa fa-seedling"></i> <?php esc_html_e( 'Choose Your Toppings', 'pizzalayer' ); ?></h2>
								<p class="np-panel__hint">
									<?php printf( esc_html__( 'Add up to %s toppings.', 'pizzalayer' ), '<strong>' . esc_html( (string) $max_toppings ) . '</strong>' ); ?>
								</p>
							</div>
							<div class="np-cards-grid np-cards-grid--toppings"><?php echo $toppings_html; // phpcs:ignore ?></div>
							<div class="np-panel__nav">
								<button class="np-btn np-btn--prev" onclick="<?php echo esc_js( $np_var ); ?>.goTab('cheese')"><i class="fa fa-arrow-left"></i> <?php esc_html_e( 'Cheese', 'pizzalayer' ); ?></button>
								<button class="np-btn np-btn--next" onclick="<?php echo esc_js( $np_var ); ?>.goTab('drizzle')"><?php esc_html_e( 'Drizzle', 'pizzalayer' ); ?> <i class="fa fa-arrow-right"></i></button>
							</div>
						</section>
						<?php do_action( 'pizzalayer_after_tab_toppings', $instance_id ); ?>
						<?php endif; ?>

						<?php if ( in_array( 'drizzle', $visible_tabs, true ) ) : ?>
						<?php do_action( 'pizzalayer_before_tab_drizzle', $instance_id ); ?>
						<section class="np-panel" id="<?php echo esc_attr( $instance_id ); ?>-panel-drizzle" role="tabpanel">
							<div class="np-panel__header">
								<h2 class="np-panel__title"><i class="fa fa-wine-glass"></i> <?php esc_html_e( 'Choose a Drizzle', 'pizzalayer' ); ?></h2>
								<p class="np-panel__hint"><?php esc_html_e( 'Optional finishing drizzle.', 'pizzalayer' ); ?></p>
							</div>
							<div class="np-cards-grid np-cards-grid--exclusive"><?php echo $drizzles_html; // phpcs:ignore ?></div>
							<div class="np-panel__nav">
								<button class="np-btn np-btn--prev" onclick="<?php echo esc_js( $np_var ); ?>.goTab('toppings')"><i class="fa fa-arrow-left"></i> <?php esc_html_e( 'Toppings', 'pizzalayer' ); ?></button>
								<button class="np-btn np-btn--next" onclick="<?php echo esc_js( $np_var ); ?>.goTab('slicing')"><?php esc_html_e( 'Slicing', 'pizzalayer' ); ?> <i class="fa fa-arrow-right"></i></button>
							</div>
						</section>
						<?php do_action( 'pizzalayer_after_tab_drizzle', $instance_id ); ?>
						<?php endif; ?>

						<?php if ( in_array( 'slicing', $visible_tabs, true ) ) : ?>
						<?php do_action( 'pizzalayer_before_tab_slicing', $instance_id ); ?>
						<section class="np-panel" id="<?php echo esc_attr( $instance_id ); ?>-panel-slicing" role="tabpanel">
							<div class="np-panel__header">
								<h2 class="np-panel__title"><i class="fa fa-pizza-slice"></i> <?php esc_html_e( 'How Should We Slice It?', 'pizzalayer' ); ?></h2>
								<p class="np-panel__hint"><?php esc_html_e( 'Choose a cut style.', 'pizzalayer' ); ?></p>
							</div>
							<div class="np-cards-grid np-cards-grid--exclusive"><?php echo $cuts_html; // phpcs:ignore ?></div>
							<div class="np-panel__nav">
								<button class="np-btn np-btn--prev" onclick="<?php echo esc_js( $np_var ); ?>.goTab('drizzle')"><i class="fa fa-arrow-left"></i> <?php esc_html_e( 'Drizzle', 'pizzalayer' ); ?></button>
								<button class="np-btn np-btn--next np-btn--cta" onclick="<?php echo esc_js( $np_var ); ?>.goTab('yourpizza')"><i class="fa fa-receipt"></i> <?php esc_html_e( 'See Your Pizza', 'pizzalayer' ); ?></button>
							</div>
						</section>
						<?php do_action( 'pizzalayer_after_tab_slicing', $instance_id ); ?>
						<?php endif; ?>

						<?php if ( in_array( 'yourpizza', $visible_tabs, true ) ) : ?>
						<?php do_action( 'pizzalayer_before_tab_yourpizza', $instance_id ); ?>
						<section class="np-panel" id="<?php echo esc_attr( $instance_id ); ?>-panel-yourpizza" role="tabpanel">
							<div class="np-panel__header">
								<h2 class="np-panel__title"><i class="fa fa-receipt"></i> <?php esc_html_e( 'Your Pizza', 'pizzalayer' ); ?></h2>
								<p class="np-panel__hint"><?php esc_html_e( "Here's everything you've built!", 'pizzalayer' ); ?></p>
							</div>
							<div class="np-yourpizza" id="<?php echo esc_attr( $instance_id ); ?>-summary">
								<?php
								$summary_rows = [
									'crust'    => [ 'fa-circle',      __( 'Crust',    'pizzalayer' ) ],
									'sauce'    => [ 'fa-droplet',     __( 'Sauce',    'pizzalayer' ) ],
									'cheese'   => [ 'fa-cheese',      __( 'Cheese',   'pizzalayer' ) ],
									'toppings' => [ 'fa-seedling',    __( 'Toppings', 'pizzalayer' ) ],
									'drizzle'  => [ 'fa-wine-glass',  __( 'Drizzle',  'pizzalayer' ) ],
									'slicing'  => [ 'fa-pizza-slice', __( 'Slicing',  'pizzalayer' ) ],
								];
								foreach ( $summary_rows as $key => [ $ico, $label ] ) :
								?>
								<div class="np-yourpizza__row" id="<?php echo esc_attr( $instance_id ); ?>-yp-<?php echo esc_attr( $key ); ?>">
									<div class="np-yourpizza__icon"><i class="fa <?php echo esc_attr( $ico ); ?>"></i></div>
									<div class="np-yourpizza__layer-name"><?php echo esc_html( $label ); ?></div>
									<div class="np-yourpizza__selection np-yourpizza__selection--empty" id="<?php echo esc_attr( $instance_id . '-yp-' . $key . '-val' ); ?>">
										<span class="np-yp-none">— <?php esc_html_e( 'none selected', 'pizzalayer' ); ?> —</span>
									</div>
									<button class="np-yourpizza__edit" onclick="<?php echo esc_js( $np_var ); ?>.goTab('<?php echo esc_js( $key ); ?>')"><i class="fa fa-pen"></i></button>
								</div>
								<?php endforeach; ?>
							</div>
							<div class="np-panel__nav">
								<button class="np-btn np-btn--prev" onclick="<?php echo esc_js( $np_var ); ?>.goTab('slicing')"><i class="fa fa-arrow-left"></i> <?php esc_html_e( 'Back', 'pizzalayer' ); ?></button>
								<button class="np-btn np-btn--ghost" onclick="ClearPizza(); window['<?php echo esc_js( $np_var ); ?>']&&window['<?php echo esc_js( $np_var ); ?>'].resetAll();"><i class="fa fa-rotate-left"></i> <?php esc_html_e( 'Start Over', 'pizzalayer' ); ?></button>
							</div>
						</section>
						<?php do_action( 'pizzalayer_after_tab_yourpizza', $instance_id ); ?>
						<?php endif; ?>

					</div><!-- /.np-panels -->
				</div><!-- /.np-builder -->
			</div><!-- /.np-tabs-col -->

		</div><!-- /.np-layout__row -->
	</div><!-- /.np-layout -->

	<div id="<?php echo esc_attr( $instance_id ); ?>-fly-container" aria-hidden="true"></div>

</div><!-- /#<?php echo esc_html( $instance_id ); ?> .np-root -->

<?php
// Initialize this instance via wp_add_inline_script (WP.org compliant — no inline <script>).
$np_init_js = "if(typeof NP!=='undefined'&&typeof NP.createInstance==='function'){"
	. "var " . esc_js( $np_var ) . "=NP.createInstance(" . wp_json_encode( $instance_id ) . ");"
	. "}";
wp_add_inline_script( 'pizzalayer-template-nightpie', $np_init_js );
