<?php
/**
 * PocketPie Template — [pizza_builder] / [pizzalayer-menu] output.
 *
 * Layouts:
 *   corner-quad   — pizza centred, four corner menus expand inward
 *   layer-deck    — pizza dominant, thumbnail strip below; click opens modal
 *   slide-drawer  — pizza on top half, bottom drawer slides up per category
 *   stack-panel   — pizza inline, compact bottom-sheet overlay for choices
 *
 * Variables from BuilderShortcode:
 *   $instance_id, $atts, $template_slug, $function_prefix
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! isset( $instance_id ) )     { $instance_id    = 'pizzabuilder-1'; }
if ( ! isset( $atts ) )            { $atts           = []; }
if ( ! isset( $template_slug ) )   { $template_slug  = 'pocketpie'; }
if ( ! isset( $function_prefix ) ) { $function_prefix = 'pzt_pocketpie'; }

// JS namespace
$pp_var = 'PP_' . preg_replace( '/[^a-zA-Z0-9_]/', '_', $instance_id );

// Resolve max toppings
$max_toppings = isset( $atts['max_toppings'] ) && (int) $atts['max_toppings'] > 0
    ? (int) $atts['max_toppings']
    : intval( get_option( 'pizzalayer_setting_topping_maxtoppings', 0 ) );
if ( $max_toppings < 1 ) { $max_toppings = 99; }
$max_toppings = (int) apply_filters( 'pizzalayer_max_toppings', $max_toppings, $instance_id );

// Resolve layout mode
$valid_layouts = [ 'corner-quad', 'layer-deck', 'slide-drawer', 'stack-panel' ];
$layout = sanitize_key( $atts['layout'] ?? 'corner-quad' );
if ( ! in_array( $layout, $valid_layouts, true ) ) { $layout = 'corner-quad'; }

// Pizza shape
$valid_shapes  = [ 'round', 'square', 'rectangle', 'custom' ];
$pizza_shape   = sanitize_key( $atts['pizza_shape'] ?? get_option( 'pizzalayer_setting_pizza_shape', 'round' ) );
if ( ! in_array( $pizza_shape, $valid_shapes, true ) ) { $pizza_shape = 'round'; }
$pizza_aspect  = sanitize_text_field( $atts['pizza_aspect'] ?? get_option( 'pizzalayer_setting_pizza_aspect', '1 / 1' ) );
$pizza_radius  = sanitize_text_field( $atts['pizza_radius'] ?? get_option( 'pizzalayer_setting_pizza_radius', '8px' ) );


// ── PizzaLayerPro: inline size selector ──────────────────────────────────────
if ( ! function_exists( 'pzt_get_pro_sizes' ) ) :
function pzt_get_pro_sizes(): array {
	if ( ! function_exists( 'pztpro_get_setting' ) || ! class_exists( 'PizzaLayerPro\\Pro\\PriceGrid\\Grid' ) ) { return []; }
	$product_id = ( function_exists( 'get_queried_object_id' ) ? (int) get_queried_object_id() : 0 );
	if ( ! $product_id ) { global $post; if ( $post instanceof \WP_Post ) { $product_id = $post->ID; } }
	$grid = new \PizzaLayerPro\Pro\PriceGrid\Grid(); return $grid->get_sizes( $product_id );
}
endif;
if ( ! function_exists( 'pzt_render_inline_size_selector' ) ) :
function pzt_render_inline_size_selector( array $sizes, string $instance_id, string $css_prefix = 'cb' ): void {
	if ( empty( $sizes ) ) { return; }
	// Extract numeric suffix from instance_id (handles pztpro-1, pizzabuilder-1, pztpro-1-2, etc)
	preg_match( '/-(\d+)$/', $instance_id, $_m_suf );
	$radio_name_raw = ! empty( $_m_suf[1] ) ? $_m_suf[1] : preg_replace( '/[^a-zA-Z0-9_]/', '_', $instance_id );
	$radio_name = 'pztpro_size_' . $radio_name_raw;
	$heading = function_exists( 'pztpro_get_setting' ) ? (string) pztpro_get_setting( 'size_selector_label', '' ) : '';
	if ( '' === $heading ) { $heading = __( 'Choose a Size', 'pizzalayer' ); }
	?>
	<div class="<?php echo esc_attr( $css_prefix ); ?>-size-selector pztpro-inline-size-selector" id="<?php echo esc_attr( $instance_id ); ?>-size-selector" role="group" aria-label="<?php echo esc_attr( $heading ); ?>">
		<p class="<?php echo esc_attr( $css_prefix ); ?>-size-selector__heading"><?php echo esc_html( $heading ); ?></p>
		<div class="<?php echo esc_attr( $css_prefix ); ?>-size-selector__options">
			<?php foreach ( $sizes as $i => $size ) :
				$inp_id = esc_attr( $instance_id ) . '-sz-' . sanitize_html_class( strtolower( $size ) ); ?>
			<label class="<?php echo esc_attr( $css_prefix ); ?>-size-option pztpro-size-option<?php echo 0 === $i ? ' pztpro-size-option--active' : ''; ?>" for="<?php echo esc_attr( $inp_id ); ?>">
				<input type="radio" id="<?php echo esc_attr( $inp_id ); ?>" name="<?php echo esc_attr( $radio_name ); ?>" value="<?php echo esc_attr( $size ); ?>" class="pztpro-size-radio" <?php checked( 0, $i ); ?> />
				<span class="<?php echo esc_attr( $css_prefix ); ?>-size-option__name"><?php echo esc_html( $size ); ?></span>
			</label>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}
endif;

$_pro_sizes = pzt_get_pro_sizes();
$_has_pro   = ! empty( $_pro_sizes );

// Hidden tabs
$hide_tabs_raw = $atts['hide_tabs'] ?? '';
$show_tabs_raw = $atts['show_tabs'] ?? '';
$all_tabs      = array_merge( $_has_pro ? [ 'size' ] : [], [ 'crust', 'sauce', 'cheese', 'toppings', 'drizzle', 'slicing', 'yourpizza' ] );
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
 * Helper: render a compact item chip for any layer type.
 * Used inside overlay modals and drawer panels.
 */
if ( ! function_exists( 'pzt_pocketpie_chip' ) ) :
function pzt_pocketpie_chip( $post, string $layer_type, string $pp_var, int $zindex = 200 ): string {
    if ( ! ( $post instanceof \WP_Post ) ) { return ''; }
    $id        = $post->ID;
    $title     = get_the_title( $post );
    $slug      = sanitize_title( $title );
    $img_field = $layer_type . '_image';
    $lyr_field = $layer_type . '_layer_image';

    $thumb_url = get_field( $img_field, $id ) ?: get_field( $lyr_field, $id ) ?: (string) get_the_post_thumbnail_url( $id, 'thumbnail' );
    $layer_url = get_field( $lyr_field, $id ) ?: $thumb_url;

    $js_add    = esc_attr( "window['{$pp_var}']&&window['{$pp_var}'].swapBase('{$layer_type}','".esc_js($slug)."','".esc_js($title)."','".esc_js((string)$layer_url)."',this)" );
    $js_remove = esc_attr( "window['{$pp_var}']&&window['{$pp_var}'].removeBase('{$layer_type}','".esc_js($slug)."',this)" );

    ob_start();
    do_action( 'pizzalayer_before_layer_card', $post, $layer_type );
    ?>
    <div class="pp-chip pp-chip--exclusive"
         data-layer="<?php echo esc_attr( $layer_type ); ?>"
         data-slug="<?php echo esc_attr( $slug ); ?>"
         data-title="<?php echo esc_attr( $title ); ?>"
         data-thumb="<?php echo esc_attr( (string) $thumb_url ); ?>"
         data-layer-img="<?php echo esc_attr( (string) $layer_url ); ?>">
        <?php if ( $thumb_url ) : ?>
            <img class="pp-chip__img" src="<?php echo esc_url( (string) $thumb_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy" />
        <?php else : ?>
            <div class="pp-chip__img pp-chip__img--placeholder"></div>
        <?php endif; ?>
        <span class="pp-chip__name"><?php echo esc_html( $title ); ?></span>
        <span class="pp-chip__check">&#10003;</span>
        <button type="button" class="pp-chip__add-btn" onclick="<?php echo $js_add; ?>">
            <span class="pp-chip__add-label"><?php esc_html_e( 'Select', 'pizzalayer' ); ?></span>
        </button>
        <button type="button" class="pp-chip__remove-btn" style="display:none;" onclick="<?php echo $js_remove; ?>">
            <span class="pp-chip__remove-label">&#x2715;</span>
        </button>
    </div>
    <?php
    do_action( 'pizzalayer_after_layer_card', $post, $layer_type );
    return apply_filters( 'pizzalayer_card_html', ob_get_clean(), $post, $layer_type );
}
endif;

/**
 * Helper: render a topping chip (multi-select, coverage buttons).
 */
if ( ! function_exists( 'pzt_pocketpie_topping_chip' ) ) :
function pzt_pocketpie_topping_chip( $post, string $pp_var, int $zindex ): string {
    if ( ! ( $post instanceof \WP_Post ) ) { return ''; }
    $id        = $post->ID;
    $title     = get_the_title( $post );
    $slug      = sanitize_title( $title );

    $thumb_url = get_field( 'topping_image', $id ) ?: get_field( 'topping_layer_image', $id ) ?: (string) get_the_post_thumbnail_url( $id, 'thumbnail' );
    $layer_url = get_field( 'topping_layer_image', $id ) ?: $thumb_url;
    $layer_id  = 'pizzalayer-topping-' . $slug;

    $js_add    = esc_attr( "window['{$pp_var}']&&window['{$pp_var}'].addTopping({$zindex},'".esc_js($slug)."','".esc_js((string)$layer_url)."','".esc_js($title)."','{$layer_id}','{$layer_id}',this)" );
    $js_remove = esc_attr( "window['{$pp_var}']&&window['{$pp_var}'].removeTopping('pizzalayer-topping-".esc_js($slug)."','".esc_js($slug)."',this)" );

    ob_start();
    do_action( 'pizzalayer_before_layer_card', $post, 'toppings' );
    ?>
    <div class="pp-chip pp-chip--topping"
         data-layer="toppings"
         data-slug="<?php echo esc_attr( $slug ); ?>"
         data-title="<?php echo esc_attr( $title ); ?>"
         data-thumb="<?php echo esc_attr( (string) $thumb_url ); ?>"
         data-layer-img="<?php echo esc_attr( (string) $layer_url ); ?>"
         data-zindex="<?php echo esc_attr( (string) $zindex ); ?>">
        <?php if ( $thumb_url ) : ?>
            <img class="pp-chip__img" src="<?php echo esc_url( (string) $thumb_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy" />
        <?php else : ?>
            <div class="pp-chip__img pp-chip__img--placeholder"></div>
        <?php endif; ?>
        <span class="pp-chip__name"><?php echo esc_html( $title ); ?></span>
        <span class="pp-chip__check">&#10003;</span>
        <div class="pp-coverage" style="display:none;">
            <?php
            $coverages = [
                'whole'               => '&#9679;',
                'half-left'           => '&#9680;',
                'half-right'          => '&#9681;',
                'quarter-top-left'    => 'Q1',
                'quarter-top-right'   => 'Q2',
                'quarter-bottom-left' => 'Q3',
                'quarter-bottom-right'=> 'Q4',
            ];
            foreach ( $coverages as $fraction => $label ) :
                $js_cov = esc_attr( "window['{$pp_var}']&&window['{$pp_var}'].setCoverage('".esc_js($slug)."','".esc_js($fraction)."',this)" );
            ?>
            <button type="button" class="pp-cov-btn" data-fraction="<?php echo esc_attr( $fraction ); ?>" onclick="<?php echo $js_cov; ?>">
                <?php echo $label; // phpcs:ignore -- safe, ascii symbols ?>
            </button>
            <?php endforeach; ?>
        </div>
        <button type="button" class="pp-chip__add-btn" onclick="<?php echo $js_add; ?>">
            <span class="pp-chip__add-label">+</span>
        </button>
        <button type="button" class="pp-chip__remove-btn" style="display:none;" onclick="<?php echo $js_remove; ?>">
            <span>&#x2715;</span>
        </button>
    </div>
    <?php
    do_action( 'pizzalayer_after_layer_card', $post, 'toppings' );
    return apply_filters( 'pizzalayer_card_html', ob_get_clean(), $post, 'toppings' );
}
endif;

// Build all HTML pools
$crusts_html = '';
foreach ( $crusts as $post )  { $crusts_html  .= pzt_pocketpie_chip( $post, 'crust',  $pp_var, 100 ); }
if ( ! $crusts_html )  { $crusts_html  = '<p class="pp-empty">' . esc_html__( 'No crusts found.', 'pizzalayer' ) . '</p>'; }

$sauces_html = '';
foreach ( $sauces as $post )  { $sauces_html  .= pzt_pocketpie_chip( $post, 'sauce',  $pp_var, 150 ); }
if ( ! $sauces_html )  { $sauces_html  = '<p class="pp-empty">' . esc_html__( 'No sauces found.', 'pizzalayer' ) . '</p>'; }

$cheeses_html = '';
foreach ( $cheeses as $post ) { $cheeses_html .= pzt_pocketpie_chip( $post, 'cheese', $pp_var, 200 ); }
if ( ! $cheeses_html ) { $cheeses_html = '<p class="pp-empty">' . esc_html__( 'No cheeses found.', 'pizzalayer' ) . '</p>'; }

$drizzles_html = '';
foreach ( $drizzles as $post ){ $drizzles_html.= pzt_pocketpie_chip( $post, 'drizzle',$pp_var, 900 ); }
if ( ! $drizzles_html ){ $drizzles_html = '<p class="pp-empty">' . esc_html__( 'No drizzles found.', 'pizzalayer' ) . '</p>'; }

$toppings_html = '';
$t_z = 400;
foreach ( $toppings as $post ){ $toppings_html .= pzt_pocketpie_topping_chip( $post, $pp_var, $t_z ); $t_z += 10; }
if ( ! $toppings_html ){ $toppings_html = '<p class="pp-empty">' . esc_html__( 'No toppings found.', 'pizzalayer' ) . '</p>'; }

$cuts_html = '';
foreach ( $cuts as $post )    { $cuts_html    .= pzt_pocketpie_chip( $post, 'cut',    $pp_var, 950 ); }
if ( ! $cuts_html )    { $cuts_html    = '<p class="pp-empty">' . esc_html__( 'No cut styles found.', 'pizzalayer' ) . '</p>'; }

// Initial pizza render
$builder       = new \PizzaLayer\Builder\PizzaBuilder();
$initial_pizza = $builder->build_dynamic(
    $atts['default_crust']    ?? '',
    $atts['default_sauce']    ?? '',
    $atts['default_cheese']   ?? '',
    $atts['default_toppings'] ?? '',
    $atts['default_drizzle']  ?? '',
    $atts['default_cut']      ?? ''
);

// Tab meta (icons + labels)
$tab_meta = [
    'size'      => [ '&#9654;',  __( 'Size',       'pizzalayer' ), '' ],
    'crust'     => [ '&#9711;',  __( 'Crust',      'pizzalayer' ), $crusts_html   ],
    'sauce'     => [ '&#128138;',__( 'Sauce',      'pizzalayer' ), $sauces_html   ],
    'cheese'    => [ '&#129472;',__( 'Cheese',     'pizzalayer' ), $cheeses_html  ],
    'toppings'  => [ '&#127807;',__( 'Toppings',   'pizzalayer' ), $toppings_html ],
    'drizzle'   => [ '&#127863;',__( 'Drizzle',    'pizzalayer' ), $drizzles_html ],
    'slicing'   => [ '&#127829;',__( 'Slicing',    'pizzalayer' ), $cuts_html     ],
    'yourpizza' => [ '&#128203;',__( 'Your Pizza', 'pizzalayer' ), ''             ],
];

// Corner assignments for corner-quad layout (skip yourpizza)
$corner_tabs   = array_filter( $visible_tabs, fn($t) => $t !== 'yourpizza' );
$corner_tabs   = array_values( $corner_tabs );
$corners       = [ 'tl', 'tr', 'bl', 'br' ];

$ii = esc_attr( $instance_id );
$pv = esc_js( $pp_var );

// Shorthand for summary rows
$summary_rows = [
    'size'     => [ '&#9654;', __( 'Size',    'pizzalayer' ) ],
    'crust'    => [ '&#9711;',   __( 'Crust',    'pizzalayer' ) ],
    'sauce'    => [ '&#128138;', __( 'Sauce',    'pizzalayer' ) ],
    'cheese'   => [ '&#129472;', __( 'Cheese',   'pizzalayer' ) ],
    'toppings' => [ '&#127807;', __( 'Toppings', 'pizzalayer' ) ],
    'drizzle'  => [ '&#127863;', __( 'Drizzle',  'pizzalayer' ) ],
    'slicing'  => [ '&#127829;', __( 'Slicing',  'pizzalayer' ) ],
];
?>

<!-- ═══════════════════════════════════════════════════
     POCKETPIE TEMPLATE — PizzaLayer
     Instance: <?php echo esc_html( $instance_id ); ?>
     Layout: <?php echo esc_html( $layout ); ?>
═══════════════════════════════════════════════════ -->
<div id="<?php echo $ii; ?>"
     class="pp-root pp-layout--<?php echo esc_attr( $layout ); ?>"
     data-instance="<?php echo $ii; ?>"
     data-pp-var="<?php echo esc_attr( $pp_var ); ?>"
     data-layout="<?php echo esc_attr( $layout ); ?>"
     data-max-toppings="<?php echo esc_attr( (string) $max_toppings ); ?>"
     data-pizza-shape="<?php echo esc_attr( $pizza_shape ); ?>"
     data-pizza-aspect="<?php echo esc_attr( $pizza_aspect ); ?>"
     data-pizza-radius="<?php echo esc_attr( $pizza_radius ); ?>">

    <?php if ( $_has_pro ) : ?>
    <?php
    // Size selection using PocketPie's own visual card-tap pattern
    preg_match( '/-(\d+)$/', $instance_id, $_pp_m );
    $_pp_radio_sfx  = ! empty( $_pp_m[1] ) ? $_pp_m[1] : preg_replace( '/[^a-zA-Z0-9_]/', '_', $instance_id );
    $_pp_radio_name = 'pztpro_size_' . $_pp_radio_sfx;
    $_pp_size_label = function_exists( 'pztpro_get_setting' ) ? (string) pztpro_get_setting( 'size_selector_label', '' ) : '';
    if ( '' === $_pp_size_label ) { $_pp_size_label = __( 'Choose Size', 'pizzalayer' ); }
    ?>
    <div class="pp-size-row" id="<?php echo esc_attr( $instance_id ); ?>-size-row"
         role="group" aria-label="<?php echo esc_attr( $_pp_size_label ); ?>">
        <div class="pp-size-row__label">
            <span class="pp-size-row__icon">&#9654;</span>
            <span class="pp-size-row__text"><?php echo esc_html( $_pp_size_label ); ?></span>
        </div>
        <div class="pp-size-row__options">
            <?php foreach ( $_pro_sizes as $i => $size ) :
                $_pp_sz_id = esc_attr( $instance_id ) . '-sz-' . sanitize_html_class( strtolower( $size ) );
            ?>
            <label class="pp-size-chip pztpro-size-option<?php echo 0 === $i ? ' pp-size-chip--active pztpro-size-option--active' : ''; ?>"
                   for="<?php echo esc_attr( $_pp_sz_id ); ?>">
                <input type="radio"
                       id="<?php echo esc_attr( $_pp_sz_id ); ?>"
                       name="<?php echo esc_attr( $_pp_radio_name ); ?>"
                       value="<?php echo esc_attr( $size ); ?>"
                       class="pztpro-size-radio"
                       <?php checked( 0, $i ); ?> />
                <span class="pp-size-chip__name"><?php echo esc_html( $size ); ?></span>
            </label>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>


    <?php /* ─────────────────────────────────────────────────────────────────
           LAYOUT 1 — CORNER QUAD
           Four corner panels expand inward; pizza is centred.
           Corners: TL=crust, TR=sauce, BL=cheese/drizzle, BR=toppings/slicing
           ───────────────────────────────────────────────────────────────── */ ?>
    <?php if ( $layout === 'corner-quad' ) : ?>
    <div class="pp-cq-wrap">

        <?php
        // Assign tabs to corners (up to 4; extras go to overflow)
        $corner_assignments = [];
        foreach ( $corners as $ci => $corner ) {
            if ( isset( $corner_tabs[ $ci ] ) ) {
                $corner_assignments[ $corner ] = $corner_tabs[ $ci ];
            }
        }
        $overflow_tabs = array_slice( $corner_tabs, 4 );
        ?>

        <?php foreach ( $corners as $ci => $corner ) :
            $ctab = $corner_assignments[ $corner ] ?? null;
            if ( ! $ctab || ! isset( $tab_meta[ $ctab ] ) ) { continue; }
            [ $icon, $label, $html ] = $tab_meta[ $ctab ];
            $is_topping = ( $ctab === 'toppings' );
        ?>
        <div class="pp-cq-corner pp-cq-corner--<?php echo esc_attr( $corner ); ?>"
             data-tab="<?php echo esc_attr( $ctab ); ?>">
            <button type="button" class="pp-cq-trigger"
                    onclick="window['<?php echo $pv; ?>']&&window['<?php echo $pv; ?>'].cqToggle('<?php echo esc_js( $ii ); ?>','<?php echo esc_attr( $corner ); ?>')"
                    aria-expanded="false">
                <span class="pp-cq-trigger__icon"><?php echo $icon; // phpcs:ignore ?></span>
                <span class="pp-cq-trigger__label"><?php echo esc_html( $label ); ?></span>
                <span class="pp-cq-trigger__badge" id="<?php echo $ii; ?>-cq-badge-<?php echo esc_attr( $corner ); ?>"></span>
            </button>
            <div class="pp-cq-panel" id="<?php echo $ii; ?>-cq-panel-<?php echo esc_attr( $corner ); ?>" aria-hidden="true">
                <div class="pp-cq-panel__inner">
                    <div class="pp-cq-panel__title"><?php echo esc_html( $label ); ?></div>
                    <div class="pp-chips-grid <?php echo $is_topping ? 'pp-chips-grid--toppings' : ''; ?>">
                        <?php echo $html; // phpcs:ignore ?>
                    </div>
                    <?php if ( $is_topping ) : ?>
                    <div class="pp-cq-panel__counter">
                        <span id="<?php echo $ii; ?>-cq-count">0</span> / <?php echo esc_html( (string) $max_toppings ); ?> <?php esc_html_e( 'toppings', 'pizzalayer' ); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if ( ! empty( $overflow_tabs ) ) : ?>
        <!-- Overflow tabs as bottom pill bar -->
        <div class="pp-cq-overflow-bar">
            <?php foreach ( $overflow_tabs as $otab ) :
                if ( ! isset( $tab_meta[ $otab ] ) ) { continue; }
                [ $oicon, $olabel, $ohtml ] = $tab_meta[ $otab ];
            ?>
            <button type="button" class="pp-cq-overflow-btn"
                    onclick="window['<?php echo $pv; ?>']&&window['<?php echo $pv; ?>'].openModal('<?php echo $ii; ?>','<?php echo esc_js( $otab ); ?>')">
                <span><?php echo $oicon; // phpcs:ignore ?></span>
                <span><?php echo esc_html( $olabel ); ?></span>
            </button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Centre pizza -->
        <div class="pp-cq-pizza" id="<?php echo $ii; ?>-cq-pizza">
            <div class="pp-pizza-stage-wrap" id="<?php echo $ii; ?>-canvas">
                <?php echo $initial_pizza; // phpcs:ignore ?>
            </div>
            <div class="pp-cq-pizza__controls">
                <button type="button" class="pp-cq-reset"
                        onclick="ClearPizza();window['<?php echo $pv; ?>']&&window['<?php echo $pv; ?>'].resetAll();"
                        title="<?php esc_attr_e( 'Reset', 'pizzalayer' ); ?>">&#8635;</button>
                <button type="button" class="pp-cq-summary-btn"
                        onclick="window['<?php echo $pv; ?>']&&window['<?php echo $pv; ?>'].openModal('<?php echo $ii; ?>','yourpizza')"
                        title="<?php esc_attr_e( 'View summary', 'pizzalayer' ); ?>">&#128203;</button>
            </div>
                <!-- Action bar moved to root level below -->
        </div>

    </div><!-- /.pp-cq-wrap -->

    <?php /* ─────────────────────────────────────────────────────────────────
           LAYOUT 2 — LAYER DECK
           Pizza dominates the top. Thumbnail strip below shows all layers.
           Click a thumb to reveal that layer's selection in an expanded card.
           ───────────────────────────────────────────────────────────────── */ ?>
    <?php elseif ( $layout === 'layer-deck' ) : ?>
    <div class="pp-ld-wrap">

        <!-- Pizza stage -->
        <div class="pp-ld-pizza-zone">
            <div class="pp-pizza-stage-wrap" id="<?php echo $ii; ?>-canvas">
                <?php echo $initial_pizza; // phpcs:ignore ?>
            </div>
            <div class="pp-ld-topping-badge" id="<?php echo $ii; ?>-ld-count-wrap">
                <span id="<?php echo $ii; ?>-ld-count">0</span> / <?php echo esc_html( (string) $max_toppings ); ?>
            </div>
        </div>

        <!-- Deck strip -->
        <div class="pp-ld-deck" id="<?php echo $ii; ?>-ld-deck">
            <?php foreach ( $visible_tabs as $tab ) :
                if ( $tab === 'yourpizza' ) { continue; }
                if ( ! isset( $tab_meta[ $tab ] ) ) { continue; }
                [ $icon, $label, $html ] = $tab_meta[ $tab ];
            ?>
            <button type="button"
                    class="pp-ld-deck-thumb"
                    data-tab="<?php echo esc_attr( $tab ); ?>"
                    id="<?php echo $ii; ?>-ld-thumb-<?php echo esc_attr( $tab ); ?>"
                    onclick="window['<?php echo $pv; ?>']&&window['<?php echo $pv; ?>'].ldSelect('<?php echo $ii; ?>','<?php echo esc_js( $tab ); ?>')">
                <span class="pp-ld-deck-thumb__icon"><?php echo $icon; // phpcs:ignore ?></span>
                <span class="pp-ld-deck-thumb__label"><?php echo esc_html( $label ); ?></span>
                <span class="pp-ld-deck-thumb__sel" id="<?php echo $ii; ?>-ld-sel-<?php echo esc_attr( $tab ); ?>"></span>
            </button>
            <?php endforeach; ?>
            <button type="button" class="pp-ld-deck-thumb pp-ld-deck-thumb--summary"
                    onclick="window['<?php echo $pv; ?>']&&window['<?php echo $pv; ?>'].openModal('<?php echo $ii; ?>','yourpizza')">
                <span class="pp-ld-deck-thumb__icon">&#128203;</span>
                <span class="pp-ld-deck-thumb__label"><?php esc_html_e( 'Review', 'pizzalayer' ); ?></span>
            </button>
        </div>

        <!-- Expanded selection card (fills box, shows selected layer image big) -->
        <div class="pp-ld-expand" id="<?php echo $ii; ?>-ld-expand" aria-hidden="true">
            <div class="pp-ld-expand__header">
                <span class="pp-ld-expand__title" id="<?php echo $ii; ?>-ld-expand-title"></span>
                <button type="button" class="pp-ld-expand__close"
                        onclick="window['<?php echo $pv; ?>']&&window['<?php echo $pv; ?>'].ldClose('<?php echo $ii; ?>')">&#10005;</button>
            </div>
            <!-- Selected layer preview image -->
            <div class="pp-ld-expand__preview-img" id="<?php echo $ii; ?>-ld-preview-img">
                <img src="" alt="" id="<?php echo $ii; ?>-ld-preview-img-tag" />
                <div class="pp-ld-expand__preview-img-empty" id="<?php echo $ii; ?>-ld-preview-img-empty"><?php esc_html_e( 'Tap a choice below', 'pizzalayer' ); ?></div>
            </div>
            <!-- Chips for active tab -->
            <?php foreach ( $visible_tabs as $tab ) :
                if ( $tab === 'yourpizza' ) { continue; }
                if ( ! isset( $tab_meta[ $tab ] ) ) { continue; }
                [ , , $html ] = $tab_meta[ $tab ];
                $is_top = ( $tab === 'toppings' );
            ?>
            <div class="pp-ld-expand__chips <?php echo $is_top ? 'pp-chips-grid--toppings' : ''; ?>"
                 id="<?php echo $ii; ?>-ld-chips-<?php echo esc_attr( $tab ); ?>"
                 style="display:none;">
                <div class="pp-chips-grid">
                    <?php echo $html; // phpcs:ignore ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Reset bar -->
        <div class="pp-ld-controls">
            <button type="button" class="pp-btn pp-btn--ghost pp-btn--sm"
                    onclick="ClearPizza();window['<?php echo $pv; ?>']&&window['<?php echo $pv; ?>'].resetAll();">
                &#8635; <?php esc_html_e( 'Reset', 'pizzalayer' ); ?>
            </button>
                <!-- Action bar moved to root level below -->
        </div>

    </div><!-- /.pp-ld-wrap -->

    <?php /* ─────────────────────────────────────────────────────────────────
           LAYOUT 3 — SLIDE DRAWER
           Pizza occupies the top half. A category pill-bar sits at the bottom
           of the pizza zone. Tapping a pill slides a drawer up from below
           with that category's options.
           ───────────────────────────────────────────────────────────────── */ ?>
    <?php elseif ( $layout === 'slide-drawer' ) : ?>
    <div class="pp-sd-wrap">

        <!-- Pizza zone with category pills overlaid at bottom -->
        <div class="pp-sd-pizza-zone">
            <div class="pp-pizza-stage-wrap" id="<?php echo $ii; ?>-canvas">
                <?php echo $initial_pizza; // phpcs:ignore ?>
            </div>
            <!-- Category pills -->
            <div class="pp-sd-pills">
                <?php foreach ( $visible_tabs as $tab ) :
                    if ( $tab === 'yourpizza' ) { continue; }
                    if ( ! isset( $tab_meta[ $tab ] ) ) { continue; }
                    [ $icon, $label, ] = $tab_meta[ $tab ];
                ?>
                <button type="button"
                        class="pp-sd-pill"
                        data-tab="<?php echo esc_attr( $tab ); ?>"
                        id="<?php echo $ii; ?>-sd-pill-<?php echo esc_attr( $tab ); ?>"
                        onclick="window['<?php echo $pv; ?>']&&window['<?php echo $pv; ?>'].sdOpen('<?php echo $ii; ?>','<?php echo esc_js( $tab ); ?>')">
                    <?php echo $icon; // phpcs:ignore ?>
                    <span><?php echo esc_html( $label ); ?></span>
                    <span class="pp-sd-pill__dot" id="<?php echo $ii; ?>-sd-dot-<?php echo esc_attr( $tab ); ?>"></span>
                </button>
                <?php endforeach; ?>
                <button type="button" class="pp-sd-pill pp-sd-pill--summary"
                        onclick="window['<?php echo $pv; ?>']&&window['<?php echo $pv; ?>'].openModal('<?php echo $ii; ?>','yourpizza')">
                    &#128203; <?php esc_html_e( 'Review', 'pizzalayer' ); ?>
                </button>
            </div>
            <div class="pp-sd-pizza-controls">
                <span class="pp-sd-count" id="<?php echo $ii; ?>-sd-count-wrap">
                    &#127807; <span id="<?php echo $ii; ?>-sd-count">0</span>/<?php echo esc_html( (string) $max_toppings ); ?>
                </span>
                <button type="button" class="pp-sd-reset"
                        onclick="ClearPizza();window['<?php echo $pv; ?>']&&window['<?php echo $pv; ?>'].resetAll();">&#8635;</button>
            </div>
        </div>

        <!-- Slide-up drawer -->
        <div class="pp-sd-drawer" id="<?php echo $ii; ?>-sd-drawer" aria-hidden="true">
            <div class="pp-sd-drawer__handle"></div>
            <div class="pp-sd-drawer__header">
                <span class="pp-sd-drawer__title" id="<?php echo $ii; ?>-sd-drawer-title"></span>
                <button type="button" class="pp-sd-drawer__close"
                        onclick="window['<?php echo $pv; ?>']&&window['<?php echo $pv; ?>'].sdClose('<?php echo $ii; ?>')">&#10005;</button>
            </div>
            <?php foreach ( $visible_tabs as $tab ) :
                if ( $tab === 'yourpizza' ) { continue; }
                if ( ! isset( $tab_meta[ $tab ] ) ) { continue; }
                [ , , $html ] = $tab_meta[ $tab ];
                $is_top = ( $tab === 'toppings' );
            ?>
            <div class="pp-sd-drawer__panel <?php echo $is_top ? 'pp-chips-grid--toppings' : ''; ?>"
                 id="<?php echo $ii; ?>-sd-panel-<?php echo esc_attr( $tab ); ?>"
                 style="display:none;">
                <div class="pp-chips-grid"><?php echo $html; // phpcs:ignore ?></div>
            </div>
            <?php endforeach; ?>
                <!-- Action bar moved to root level below -->
        </div>

    </div><!-- /.pp-sd-wrap -->

    <?php /* ─────────────────────────────────────────────────────────────────
           LAYOUT 4 — STACK PANEL
           Compact vertical layout: small pizza preview, progress dots, and
           a step-based bottom-sheet that slides in from the bottom.
           ───────────────────────────────────────────────────────────────── */ ?>
    <?php else : /* stack-panel */ ?>
    <div class="pp-sp-wrap">

        <!-- Compact pizza + step indicator -->
        <div class="pp-sp-top">
            <div class="pp-sp-pizza-mini" id="<?php echo $ii; ?>-canvas">
                <?php echo $initial_pizza; // phpcs:ignore ?>
            </div>
            <div class="pp-sp-step-info">
                <div class="pp-sp-step-dots" id="<?php echo $ii; ?>-sp-dots">
                    <?php foreach ( $visible_tabs as $dot ) : ?>
                    <span class="pp-sp-dot" data-step="<?php echo esc_attr( $dot ); ?>"></span>
                    <?php endforeach; ?>
                </div>
                <div class="pp-sp-step-label" id="<?php echo $ii; ?>-sp-label">
                    <?php esc_html_e( 'Start building your pizza', 'pizzalayer' ); ?>
                </div>
            </div>
        </div>

        <!-- Step nav bar -->
        <div class="pp-sp-stepbar" id="<?php echo $ii; ?>-sp-stepbar">
            <?php $first_sp = true; foreach ( $visible_tabs as $tab ) :
                if ( $tab === 'yourpizza' ) { continue; }
                if ( ! isset( $tab_meta[ $tab ] ) ) { continue; }
                [ $icon, $label, ] = $tab_meta[ $tab ];
                $active_class = $first_sp ? 'active' : '';
                $first_sp = false;
            ?>
            <button type="button"
                    class="pp-sp-step <?php echo esc_attr( $active_class ); ?>"
                    data-tab="<?php echo esc_attr( $tab ); ?>"
                    onclick="window['<?php echo $pv; ?>']&&window['<?php echo $pv; ?>'].spOpen('<?php echo $ii; ?>','<?php echo esc_js( $tab ); ?>')">
                <span class="pp-sp-step__icon"><?php echo $icon; // phpcs:ignore ?></span>
                <span class="pp-sp-step__label"><?php echo esc_html( $label ); ?></span>
                <span class="pp-sp-step__dot" id="<?php echo $ii; ?>-sp-step-dot-<?php echo esc_attr( $tab ); ?>"></span>
            </button>
            <?php endforeach; ?>
            <button type="button" class="pp-sp-step pp-sp-step--summary"
                    onclick="window['<?php echo $pv; ?>']&&window['<?php echo $pv; ?>'].openModal('<?php echo $ii; ?>','yourpizza')">
                <span class="pp-sp-step__icon">&#128203;</span>
                <span class="pp-sp-step__label"><?php esc_html_e( 'Review', 'pizzalayer' ); ?></span>
            </button>
        </div>

        <!-- Bottom sheet panel -->
        <div class="pp-sp-sheet" id="<?php echo $ii; ?>-sp-sheet" aria-hidden="true">
            <div class="pp-sp-sheet__grip"></div>
            <div class="pp-sp-sheet__header">
                <span class="pp-sp-sheet__title" id="<?php echo $ii; ?>-sp-sheet-title"></span>
                <button type="button" class="pp-sp-sheet__close"
                        onclick="window['<?php echo $pv; ?>']&&window['<?php echo $pv; ?>'].spClose('<?php echo $ii; ?>')">&#10005;</button>
            </div>
            <?php foreach ( $visible_tabs as $tab ) :
                if ( $tab === 'yourpizza' ) { continue; }
                if ( ! isset( $tab_meta[ $tab ] ) ) { continue; }
                [ , , $html ] = $tab_meta[ $tab ];
                $is_top = ( $tab === 'toppings' );
            ?>
            <div class="pp-sp-sheet__panel <?php echo $is_top ? 'pp-chips-grid--toppings' : ''; ?>"
                 id="<?php echo $ii; ?>-sp-panel-<?php echo esc_attr( $tab ); ?>"
                 style="display:none;">
                <div class="pp-chips-grid"><?php echo $html; // phpcs:ignore ?></div>
                <?php if ( $is_top ) : ?>
                <div class="pp-sp-topping-count">
                    <span id="<?php echo $ii; ?>-sp-count">0</span>/<?php echo esc_html( (string) $max_toppings ); ?> <?php esc_html_e( 'toppings', 'pizzalayer' ); ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <div class="pp-sp-sheet__actions">
                <button type="button" class="pp-btn pp-btn--ghost pp-btn--sm"
                        onclick="ClearPizza();window['<?php echo $pv; ?>']&&window['<?php echo $pv; ?>'].resetAll();">
                    &#8635; <?php esc_html_e( 'Reset', 'pizzalayer' ); ?>
                </button>
                <!-- Action bar moved to root level below -->
            </div>
        </div>

    </div><!-- /.pp-sp-wrap -->
    <?php endif; // end layout switch ?>

    <!-- ═══════════════════════════════════════
         SHARED: Summary Modal (all layouts)
         ═══════════════════════════════════════ -->
    <div class="pp-modal-overlay" id="<?php echo $ii; ?>-modal-overlay" aria-hidden="true"
         onclick="window['<?php echo $pv; ?>']&&window['<?php echo $pv; ?>'].closeModal('<?php echo $ii; ?>')">
    </div>
    <div class="pp-modal" id="<?php echo $ii; ?>-modal" role="dialog" aria-hidden="true">
        <div class="pp-modal__header">
            <span class="pp-modal__title" id="<?php echo $ii; ?>-modal-title"><?php esc_html_e( 'Your Pizza', 'pizzalayer' ); ?></span>
            <button type="button" class="pp-modal__close"
                    onclick="window['<?php echo $pv; ?>']&&window['<?php echo $pv; ?>'].closeModal('<?php echo $ii; ?>')">&#10005;</button>
        </div>
        <div class="pp-modal__body" id="<?php echo $ii; ?>-modal-body">
            <!-- Dynamic content depending on which tab triggered this modal -->
        </div>
        <!-- Overflow tab content injected here for corner-quad when > 4 tabs -->
        <?php foreach ( $visible_tabs as $tab ) :
            if ( in_array( $tab, array_slice( $corner_tabs, 0, 4 ), true ) && $layout === 'corner-quad' ) { continue; }
            if ( $tab === 'yourpizza' ) { continue; }
            if ( ! isset( $tab_meta[ $tab ] ) ) { continue; }
            [ , , $html ] = $tab_meta[ $tab ];
            $is_top = ( $tab === 'toppings' );
        ?>
        <div class="pp-modal__tab-panel <?php echo $is_top ? 'pp-chips-grid--toppings' : ''; ?>"
             id="<?php echo $ii; ?>-modal-panel-<?php echo esc_attr( $tab ); ?>"
             style="display:none;">
            <div class="pp-chips-grid"><?php echo $html; // phpcs:ignore ?></div>
        </div>
        <?php endforeach; ?>
        <!-- Summary panel -->
        <div class="pp-modal__summary" id="<?php echo $ii; ?>-modal-summary" style="display:none;">
            <?php foreach ( $summary_rows as $key => [ $ico, $slabel ] ) : ?>
            <div class="pp-summary-row" id="<?php echo $ii; ?>-modal-yp-<?php echo esc_attr( $key ); ?>">
                <span class="pp-summary-row__icon"><?php echo $ico; // phpcs:ignore ?></span>
                <span class="pp-summary-row__label"><?php echo esc_html( $slabel ); ?></span>
                <span class="pp-summary-row__val pp-summary-row__val--empty"
                      id="<?php echo $ii; ?>-modal-yp-<?php echo esc_attr( $key ); ?>-val">
                    — <?php esc_html_e( 'none', 'pizzalayer' ); ?> —
                </span>
            </div>
            <?php endforeach; ?>
            <div class="pp-modal__summary-actions">
                <!-- Action bar moved to root level below -->
                <button type="button" class="pp-btn pp-btn--ghost pp-btn--sm"
                        onclick="ClearPizza();window['<?php echo $pv; ?>']&&window['<?php echo $pv; ?>'].resetAll();window['<?php echo $pv; ?>']&&window['<?php echo $pv; ?>'].closeModal('<?php echo $ii; ?>')">
                    &#8635; <?php esc_html_e( 'Start Over', 'pizzalayer' ); ?>
                </button>
            </div>
        </div>
    </div><!-- /.pp-modal -->

	<?php do_action( 'pizzalayer_builder_action_bar', $instance_id ); ?>

</div><!-- /.pp-root -->

<?php
// Initialize this instance via wp_add_inline_script (WP.org compliant — no inline <script>).
$pp_init_js = "if(typeof PP!=='undefined'&&typeof PP.createInstance==='function'){"
	. "var " . esc_js( $pp_var ) . "=PP.createInstance(" . wp_json_encode( $instance_id ) . ");"
	. "}";
wp_add_inline_script( 'pizzalayer-template-pocketpie', $pp_init_js );
