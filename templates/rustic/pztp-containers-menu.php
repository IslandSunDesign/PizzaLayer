<?php
/**
 * Fornaia Template — [pizza_builder] output.
 *
 * Rustic artisan pizza builder: warm earthy palette, aged-paper texture,
 * serif typography, vintage badge accents, horizontal step-nav,
 * and sticky split-screen layout matching NightPie architecture.
 *
 * Variables available from the shortcode:
 *   $instance_id  — unique ID string (e.g. "pizza-1", "pizzabuilder-1")
 *   $atts         — shortcode attribute array
 *
 * Multi-instance support: every JS reference uses $rp_var (the per-instance
 * namespace) so multiple builders on one page maintain independent state.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Ensure we have all expected variables
if ( ! isset( $instance_id ) )     { $instance_id     = 'pizzabuilder-1'; }
if ( ! isset( $atts ) )            { $atts             = []; }
if ( ! isset( $template_slug ) )   { $template_slug    = 'rustic'; }
if ( ! isset( $function_prefix ) ) { $function_prefix  = 'pzt_rustic'; }

// Per-instance JS namespace: RP_pizza1, RP_pizzabuilder2, etc.
$rp_var = 'RP_' . preg_replace( '/[^a-zA-Z0-9_]/', '_', $instance_id );

// Resolve max toppings
$max_toppings = isset( $atts['max_toppings'] ) && (int) $atts['max_toppings'] > 0
    ? (int) $atts['max_toppings']
    : intval( get_option( 'pizzalayer_setting_topping_maxtoppings', 0 ) );
if ( $max_toppings < 1 ) { $max_toppings = 99; }
$max_toppings = (int) apply_filters( 'pizzalayer_max_toppings', $max_toppings, $instance_id );

// Resolve pizza shape
$valid_shapes = [ 'round', 'square', 'rectangle', 'custom' ];
$pizza_shape  = sanitize_key( $atts['pizza_shape'] ?? get_option( 'pizzalayer_setting_pizza_shape', 'round' ) );
if ( ! in_array( $pizza_shape, $valid_shapes, true ) ) { $pizza_shape = 'round'; }
$pizza_aspect = sanitize_text_field( $atts['pizza_aspect'] ?? get_option( 'pizzalayer_setting_pizza_aspect', '1 / 1' ) );
$pizza_radius = sanitize_text_field( $atts['pizza_radius'] ?? get_option( 'pizzalayer_setting_pizza_radius', '8px' ) );

// Resolve layer animation
$valid_anims      = [ 'fade', 'scale-in', 'slide-up', 'flip-in', 'drop-in', 'instant' ];
$layer_anim       = sanitize_key( $atts['layer_anim'] ?? get_option( 'pizzalayer_setting_layer_anim', 'fade' ) );
if ( ! in_array( $layer_anim, $valid_anims, true ) ) { $layer_anim = 'fade'; }
$layer_anim_speed = max( 80, min( 800, (int) get_option( 'pizzalayer_setting_layer_anim_speed', 320 ) ) );

// Resolve hidden/visible tabs
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

// ── Fornaia template settings ────────────────────────────────────────────
// Helper: read a rustic_ option with a fallback default.
$rp_opt = function( string $key, string $default = '' ): string {
    return (string) get_option( $key, $default );
};
$rp_on = function( string $key, string $default = 'yes' ): bool {
    return get_option( $key, $default ) === 'yes';
};

// Badge copy
$rp_badge_visible = $rp_on( 'rustic_setting_show_badge' );
$rp_badge_top     = $rp_opt( 'rustic_setting_badge_top_text',    'Your' );
$rp_badge_main    = $rp_opt( 'rustic_setting_badge_main_text',   'Pizza' );
$rp_badge_bottom  = $rp_opt( 'rustic_setting_badge_bottom_text', 'Artisanale' );

// Button / action labels
$rp_label_choose  = $rp_opt( 'rustic_setting_choose_label', 'Choose' );
$rp_label_add     = $rp_opt( 'rustic_setting_add_label',    'Add' );
$rp_label_remove  = $rp_opt( 'rustic_setting_remove_label', 'Remove' );
$rp_label_reset   = $rp_opt( 'rustic_setting_reset_label',  'Reset' );

// Order summary copy
$rp_order_title   = $rp_opt( 'rustic_setting_order_title',   'Your Order' );
$rp_order_tagline = $rp_opt( 'rustic_setting_order_tagline', 'Crafted by your hands, baked by ours.' );

// ── Query all CPTs ───────────────────────────────────────────────────────
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
 */
if ( ! function_exists( 'pzt_rustic_exclusive_card' ) ) :
function pzt_rustic_exclusive_card( $post, string $layer_type, string $rp_var, int $zindex = 200, string $add_label = 'Choose', string $remove_label = 'Remove' ): string {
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
    $js_add    = "window['{$rp_var}']&&window['{$rp_var}'].swapBase('{$layer_type}','{$slug}','{$js_title}','{$js_layer}',this)";
    $js_remove = "window['{$rp_var}']&&window['{$rp_var}'].removeBase('{$layer_type}','{$slug}',this)";

    ob_start();
    do_action( 'pizzalayer_before_layer_card', $post, $layer_type );
    ?>
    <div class="rp-card rp-card--exclusive"
         data-layer="<?php echo esc_attr( $layer_type ); ?>"
         data-slug="<?php echo esc_attr( $slug ); ?>"
         data-title="<?php echo esc_attr( $title ); ?>"
         data-thumb="<?php echo esc_attr( (string) $thumb_url ); ?>"
         data-layer-img="<?php echo esc_attr( (string) $layer_url ); ?>">
        <div class="rp-card__thumb-wrap">
            <?php if ( $thumb_url ) : ?>
                <img class="rp-card__thumb" src="<?php echo esc_url( (string) $thumb_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy" />
            <?php else : ?>
                <div class="rp-card__thumb rp-card__thumb--placeholder"></div>
            <?php endif; ?>
            <div class="rp-card__check"><i class="fa fa-check"></i></div>
        </div>
        <div class="rp-card__body">
            <span class="rp-card__name"><?php echo esc_html( $title ); ?></span>
        </div>
        <div class="rp-card__actions">
            <button type="button" class="rp-btn rp-btn--add" onclick="<?php echo esc_attr( $js_add ); ?>">
                <i class="fa fa-plus"></i> <?php echo esc_html( $add_label ); ?>
            </button>
            <button type="button" class="rp-btn rp-btn--remove" style="display:none;" onclick="<?php echo esc_attr( $js_remove ); ?>">
                <i class="fa fa-times"></i> <?php echo esc_html( $remove_label ); ?>
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
if ( ! function_exists( 'pzt_rustic_topping_card' ) ) :
function pzt_rustic_topping_card( $post, string $rp_var, int $zindex, string $add_label = 'Add', string $remove_label = 'Remove' ): string {
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

    $js_add    = "window['{$rp_var}']&&window['{$rp_var}'].addTopping({$zindex},'{$js_slug}','{$js_layer}','{$js_title}','{$layer_id}','{$layer_id}',this)";
    $js_remove = "window['{$rp_var}']&&window['{$rp_var}'].removeTopping('pizzalayer-topping-{$js_slug}','{$js_slug}',this)";

    ob_start();
    do_action( 'pizzalayer_before_layer_card', $post, 'toppings' );
    ?>
    <div class="rp-card rp-card--topping"
         data-layer="toppings"
         data-slug="<?php echo esc_attr( $slug ); ?>"
         data-title="<?php echo esc_attr( $title ); ?>"
         data-thumb="<?php echo esc_attr( (string) $thumb_url ); ?>"
         data-layer-img="<?php echo esc_attr( (string) $layer_url ); ?>"
         data-zindex="<?php echo esc_attr( (string) $zindex ); ?>">
        <div class="rp-card__thumb-wrap">
            <?php if ( $thumb_url ) : ?>
                <img class="rp-card__thumb" src="<?php echo esc_url( (string) $thumb_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy" />
            <?php else : ?>
                <div class="rp-card__thumb rp-card__thumb--placeholder"></div>
            <?php endif; ?>
            <div class="rp-card__check"><i class="fa fa-check"></i></div>
        </div>
        <div class="rp-card__body">
            <span class="rp-card__name"><?php echo esc_html( $title ); ?></span>
            <div class="rp-coverage" style="display:none;">
                <span class="rp-coverage__label"><?php esc_html_e( 'Coverage:', 'pizzalayer' ); ?></span>
                <div class="rp-coverage__btns">
                    <?php
                    $coverages = [
                        'whole'               => 'Whole',
                        'half-left'           => 'Left',
                        'half-right'          => 'Right',
                        'quarter-top-left'    => 'Q1',
                        'quarter-top-right'   => 'Q2',
                        'quarter-bottom-left' => 'Q3',
                        'quarter-bottom-right'=> 'Q4',
                    ];
                    foreach ( $coverages as $fraction => $label ) :
                        $js_cov = "window['{$rp_var}']&&window['{$rp_var}'].setCoverage('" . esc_js( $slug ) . "','" . esc_js( $fraction ) . "',this)";
                    ?>
                    <button type="button" class="rp-cov-btn" data-fraction="<?php echo esc_attr( $fraction ); ?>"
                            onclick="<?php echo esc_attr( $js_cov ); ?>">
                        <span class="rp-cov-ico rp-cov-ico--<?php echo esc_attr( str_replace( [ 'half-', 'quarter-' ], [ '', '' ], $fraction ) ); ?>"></span>
                        <?php echo esc_html( $label ); ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="rp-card__actions">
            <button type="button" class="rp-btn rp-btn--add" onclick="<?php echo esc_attr( $js_add ); ?>">
                <i class="fa fa-plus"></i> <?php echo esc_html( $add_label ); ?>
            </button>
            <button type="button" class="rp-btn rp-btn--remove" style="display:none;" onclick="<?php echo esc_attr( $js_remove ); ?>">
                <i class="fa fa-times"></i> <?php echo esc_html( $remove_label ); ?>
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
foreach ( $crusts as $post ) { $crusts_html .= pzt_rustic_exclusive_card( $post, 'crust', $rp_var, 100, $rp_label_choose, $rp_label_remove ); }
if ( ! $crusts_html ) { $crusts_html = '<p class="rp-empty"><i class="fa fa-circle-exclamation"></i> ' . esc_html__( 'No crusts found.', 'pizzalayer' ) . '</p>'; }

$sauces_html = '';
foreach ( $sauces as $post ) { $sauces_html .= pzt_rustic_exclusive_card( $post, 'sauce', $rp_var, 150, $rp_label_choose, $rp_label_remove ); }
if ( ! $sauces_html ) { $sauces_html = '<p class="rp-empty">' . esc_html__( 'No sauces found.', 'pizzalayer' ) . '</p>'; }

$cheeses_html = '';
foreach ( $cheeses as $post ) { $cheeses_html .= pzt_rustic_exclusive_card( $post, 'cheese', $rp_var, 200, $rp_label_choose, $rp_label_remove ); }
if ( ! $cheeses_html ) { $cheeses_html = '<p class="rp-empty">' . esc_html__( 'No cheeses found.', 'pizzalayer' ) . '</p>'; }

$drizzles_html = '';
foreach ( $drizzles as $post ) { $drizzles_html .= pzt_rustic_exclusive_card( $post, 'drizzle', $rp_var, 900, $rp_label_choose, $rp_label_remove ); }
if ( ! $drizzles_html ) { $drizzles_html = '<p class="rp-empty">' . esc_html__( 'No drizzles found.', 'pizzalayer' ) . '</p>'; }

$toppings_html = '';
$t_z = 400;
foreach ( $toppings as $post ) { $toppings_html .= pzt_rustic_topping_card( $post, $rp_var, $t_z, $rp_label_add, $rp_label_remove ); $t_z += 10; }
if ( ! $toppings_html ) { $toppings_html = '<p class="rp-empty">' . esc_html__( 'No toppings found.', 'pizzalayer' ) . '</p>'; }

$cuts_html = '';
foreach ( $cuts as $post ) { $cuts_html .= pzt_rustic_exclusive_card( $post, 'cut', $rp_var, 950, $rp_label_choose, $rp_label_remove ); }
if ( ! $cuts_html ) { $cuts_html = '<p class="rp-empty">' . esc_html__( 'No cut styles found.', 'pizzalayer' ) . '</p>'; }

// Initial pizza via PizzaBuilder
$builder       = new \PizzaLayer\Builder\PizzaBuilder();
$initial_pizza = $builder->build_dynamic(
    $atts['default_crust']  ?? '',
    $atts['default_sauce']  ?? '',
    $atts['default_cheese'] ?? ''
);
?>
<!-- ═══════════════════════════════════════════════════
     FORNAIA TEMPLATE — PizzaLayer
     Instance: <?php echo esc_html( $instance_id ); ?>

═══════════════════════════════════════════════════ -->
<div id="<?php echo esc_attr( $instance_id ); ?>"
     class="rp-root"
     data-instance="<?php echo esc_attr( $instance_id ); ?>"
     data-rp-var="<?php echo esc_attr( $rp_var ); ?>"
     data-max-toppings="<?php echo esc_attr( (string) $max_toppings ); ?>"
     data-pizza-shape="<?php echo esc_attr( $pizza_shape ); ?>"
     data-pizza-aspect="<?php echo esc_attr( $pizza_aspect ); ?>"
     data-pizza-radius="<?php echo esc_attr( $pizza_radius ); ?>"
     data-layer-anim="<?php echo esc_attr( $layer_anim ); ?>"
     data-layer-anim-speed="<?php echo esc_attr( (string) $layer_anim_speed ); ?>">

    <!-- Main two-column layout -->
    <div class="rp-layout">
        <div class="rp-layout__row">

            <!-- LEFT: sticky pizza preview -->
            <div class="rp-pizza-col" id="<?php echo esc_attr( $instance_id ); ?>-pizza-col">
                <div class="rp-pizza-sticky">

                    <?php if ( $rp_badge_visible ) : ?>
                    <!-- Vintage badge header -->
                    <div class="rp-preview-badge">
                        <div class="rp-preview-badge__inner">
                            <span class="rp-preview-badge__top"><?php echo esc_html( $rp_badge_top ); ?></span>
                            <span class="rp-preview-badge__main"><?php echo esc_html( $rp_badge_main ); ?></span>
                            <span class="rp-preview-badge__bottom"><?php echo esc_html( $rp_badge_bottom ); ?></span>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Pizza canvas -->
                    <div class="rp-pizza-canvas" id="<?php echo esc_attr( $instance_id ); ?>-canvas">
                        <?php echo $initial_pizza; // phpcs:ignore WordPress.Security.EscapeOutput ?>
                    </div>

                    <!-- Topping counter -->
                    <div class="rp-pizza-footer">
                        <button type="button" class="rp-btn rp-btn--ghost rp-btn--sm"
                                onclick="ClearPizza(); window['<?php echo esc_js( $rp_var ); ?>']&&window['<?php echo esc_js( $rp_var ); ?>'].resetAll();">
                            <i class="fa fa-rotate-left"></i> <?php echo esc_html( $rp_label_reset ); ?>
                        </button>
                        <span class="rp-topping-counter">
                            <i class="fa fa-layer-group"></i>
                            <span id="<?php echo esc_attr( $instance_id ); ?>-count">0</span>&thinsp;/&thinsp;<?php echo esc_html( (string) $max_toppings ); ?>
                        </span>
                    </div>

                    <!-- Pro action bar hook -->
                    <?php do_action( 'pizzalayer_builder_action_bar', $instance_id ); ?>

                </div>
            </div><!-- /.rp-pizza-col -->

            <!-- RIGHT: step builder -->
            <div class="rp-builder-col">
                <div class="rp-builder">

                    <!-- Step nav (horizontal numbered steps) -->
                    <nav class="rp-stepnav" id="<?php echo esc_attr( $instance_id ); ?>-stepnav" role="tablist">
                        <?php
                        $step_meta = [
                            'crust'     => [ 'fa-circle',       __( 'Crust',      'pizzalayer' ) ],
                            'sauce'     => [ 'fa-droplet',      __( 'Sauce',      'pizzalayer' ) ],
                            'cheese'    => [ 'fa-cheese',       __( 'Cheese',     'pizzalayer' ) ],
                            'toppings'  => [ 'fa-seedling',     __( 'Toppings',   'pizzalayer' ) ],
                            'drizzle'   => [ 'fa-wine-glass',   __( 'Drizzle',    'pizzalayer' ) ],
                            'slicing'   => [ 'fa-pizza-slice',  __( 'Slicing',    'pizzalayer' ) ],
                            'yourpizza' => [ 'fa-scroll',       __( 'Your Order', 'pizzalayer' ) ],
                        ];
                        $step_num  = 1;
                        $first_tab = true;
                        foreach ( $visible_tabs as $tab ) :
                            if ( ! isset( $step_meta[ $tab ] ) ) { continue; }
                            [ $icon, $label ] = $step_meta[ $tab ];
                            $active   = $first_tab ? 'active' : '';
                            $selected = $first_tab ? 'true' : 'false';
                            $first_tab = false;
                        ?>
                        <button class="rp-step <?php echo esc_attr( $active ); ?>"
                                data-tab="<?php echo esc_attr( $tab ); ?>"
                                data-instance="<?php echo esc_attr( $instance_id ); ?>"
                                role="tab" aria-selected="<?php echo esc_attr( $selected ); ?>"
                                aria-controls="<?php echo esc_attr( $instance_id . '-panel-' . $tab ); ?>">
                            <span class="rp-step__num"><?php echo esc_html( (string) $step_num++ ); ?></span>
                            <span class="rp-step__icon"><i class="fa <?php echo esc_attr( $icon ); ?>"></i></span>
                            <span class="rp-step__label"><?php echo esc_html( $label ); ?></span>
                        </button>
                        <?php endforeach; ?>
                    </nav>

                    <!-- Step connector line (decorative) -->
                    <div class="rp-step-line" aria-hidden="true"></div>

                    <!-- Panels -->
                    <div class="rp-panels">

                        <?php if ( in_array( 'crust', $visible_tabs, true ) ) : ?>
                        <?php do_action( 'pizzalayer_before_tab_crust', $instance_id ); ?>
                        <section class="rp-panel active" id="<?php echo esc_attr( $instance_id ); ?>-panel-crust" role="tabpanel">
                            <div class="rp-panel__header">
                                <div class="rp-panel__badge">
                                    <span class="rp-panel__badge-num">01</span>
                                    <div class="rp-panel__badge-text">
                                        <h2 class="rp-panel__title"><?php esc_html_e( 'Choose Your Crust', 'pizzalayer' ); ?></h2>
                                        <p class="rp-panel__hint"><?php esc_html_e( 'The foundation of every great pie.', 'pizzalayer' ); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="rp-cards-grid rp-cards-grid--exclusive"><?php echo $crusts_html; // phpcs:ignore ?></div>
                            <div class="rp-panel__nav">
                                <span></span>
                                <button class="rp-btn rp-btn--next" onclick="<?php echo esc_js( $rp_var ); ?>.goTab('sauce')"><?php esc_html_e( 'Next: Sauce', 'pizzalayer' ); ?> <i class="fa fa-arrow-right"></i></button>
                            </div>
                        </section>
                        <?php do_action( 'pizzalayer_after_tab_crust', $instance_id ); ?>
                        <?php endif; ?>

                        <?php if ( in_array( 'sauce', $visible_tabs, true ) ) : ?>
                        <?php do_action( 'pizzalayer_before_tab_sauce', $instance_id ); ?>
                        <section class="rp-panel" id="<?php echo esc_attr( $instance_id ); ?>-panel-sauce" role="tabpanel">
                            <div class="rp-panel__header">
                                <div class="rp-panel__badge">
                                    <span class="rp-panel__badge-num">02</span>
                                    <div class="rp-panel__badge-text">
                                        <h2 class="rp-panel__title"><?php esc_html_e( 'Choose Your Sauce', 'pizzalayer' ); ?></h2>
                                        <p class="rp-panel__hint"><?php esc_html_e( 'Slow-cooked and full of character.', 'pizzalayer' ); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="rp-cards-grid rp-cards-grid--exclusive"><?php echo $sauces_html; // phpcs:ignore ?></div>
                            <div class="rp-panel__nav">
                                <button class="rp-btn rp-btn--prev" onclick="<?php echo esc_js( $rp_var ); ?>.goTab('crust')"><i class="fa fa-arrow-left"></i> <?php esc_html_e( 'Crust', 'pizzalayer' ); ?></button>
                                <button class="rp-btn rp-btn--next" onclick="<?php echo esc_js( $rp_var ); ?>.goTab('cheese')"><?php esc_html_e( 'Next: Cheese', 'pizzalayer' ); ?> <i class="fa fa-arrow-right"></i></button>
                            </div>
                        </section>
                        <?php do_action( 'pizzalayer_after_tab_sauce', $instance_id ); ?>
                        <?php endif; ?>

                        <?php if ( in_array( 'cheese', $visible_tabs, true ) ) : ?>
                        <?php do_action( 'pizzalayer_before_tab_cheese', $instance_id ); ?>
                        <section class="rp-panel" id="<?php echo esc_attr( $instance_id ); ?>-panel-cheese" role="tabpanel">
                            <div class="rp-panel__header">
                                <div class="rp-panel__badge">
                                    <span class="rp-panel__badge-num">03</span>
                                    <div class="rp-panel__badge-text">
                                        <h2 class="rp-panel__title"><?php esc_html_e( 'Choose Your Cheese', 'pizzalayer' ); ?></h2>
                                        <p class="rp-panel__hint"><?php esc_html_e( 'Fresh-pulled or aged to perfection.', 'pizzalayer' ); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="rp-cards-grid rp-cards-grid--exclusive"><?php echo $cheeses_html; // phpcs:ignore ?></div>
                            <div class="rp-panel__nav">
                                <button class="rp-btn rp-btn--prev" onclick="<?php echo esc_js( $rp_var ); ?>.goTab('sauce')"><i class="fa fa-arrow-left"></i> <?php esc_html_e( 'Sauce', 'pizzalayer' ); ?></button>
                                <button class="rp-btn rp-btn--next" onclick="<?php echo esc_js( $rp_var ); ?>.goTab('toppings')"><?php esc_html_e( 'Next: Toppings', 'pizzalayer' ); ?> <i class="fa fa-arrow-right"></i></button>
                            </div>
                        </section>
                        <?php do_action( 'pizzalayer_after_tab_cheese', $instance_id ); ?>
                        <?php endif; ?>

                        <?php if ( in_array( 'toppings', $visible_tabs, true ) ) : ?>
                        <?php do_action( 'pizzalayer_before_tab_toppings', $instance_id ); ?>
                        <section class="rp-panel" id="<?php echo esc_attr( $instance_id ); ?>-panel-toppings" role="tabpanel">
                            <div class="rp-panel__header">
                                <div class="rp-panel__badge">
                                    <span class="rp-panel__badge-num">04</span>
                                    <div class="rp-panel__badge-text">
                                        <h2 class="rp-panel__title"><?php esc_html_e( 'Choose Your Toppings', 'pizzalayer' ); ?></h2>
                                        <p class="rp-panel__hint">
                                            <?php printf( esc_html__( 'Pile on up to %s toppings — market-fresh.', 'pizzalayer' ), '<strong>' . esc_html( (string) $max_toppings ) . '</strong>' ); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="rp-cards-grid rp-cards-grid--toppings"><?php echo $toppings_html; // phpcs:ignore ?></div>
                            <div class="rp-panel__nav">
                                <button class="rp-btn rp-btn--prev" onclick="<?php echo esc_js( $rp_var ); ?>.goTab('cheese')"><i class="fa fa-arrow-left"></i> <?php esc_html_e( 'Cheese', 'pizzalayer' ); ?></button>
                                <button class="rp-btn rp-btn--next" onclick="<?php echo esc_js( $rp_var ); ?>.goTab('drizzle')"><?php esc_html_e( 'Next: Drizzle', 'pizzalayer' ); ?> <i class="fa fa-arrow-right"></i></button>
                            </div>
                        </section>
                        <?php do_action( 'pizzalayer_after_tab_toppings', $instance_id ); ?>
                        <?php endif; ?>

                        <?php if ( in_array( 'drizzle', $visible_tabs, true ) ) : ?>
                        <?php do_action( 'pizzalayer_before_tab_drizzle', $instance_id ); ?>
                        <section class="rp-panel" id="<?php echo esc_attr( $instance_id ); ?>-panel-drizzle" role="tabpanel">
                            <div class="rp-panel__header">
                                <div class="rp-panel__badge">
                                    <span class="rp-panel__badge-num">05</span>
                                    <div class="rp-panel__badge-text">
                                        <h2 class="rp-panel__title"><?php esc_html_e( 'Choose a Drizzle', 'pizzalayer' ); ?></h2>
                                        <p class="rp-panel__hint"><?php esc_html_e( 'A finishing touch of old-world flavour.', 'pizzalayer' ); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="rp-cards-grid rp-cards-grid--exclusive"><?php echo $drizzles_html; // phpcs:ignore ?></div>
                            <div class="rp-panel__nav">
                                <button class="rp-btn rp-btn--prev" onclick="<?php echo esc_js( $rp_var ); ?>.goTab('toppings')"><i class="fa fa-arrow-left"></i> <?php esc_html_e( 'Toppings', 'pizzalayer' ); ?></button>
                                <button class="rp-btn rp-btn--next" onclick="<?php echo esc_js( $rp_var ); ?>.goTab('slicing')"><?php esc_html_e( 'Next: Slicing', 'pizzalayer' ); ?> <i class="fa fa-arrow-right"></i></button>
                            </div>
                        </section>
                        <?php do_action( 'pizzalayer_after_tab_drizzle', $instance_id ); ?>
                        <?php endif; ?>

                        <?php if ( in_array( 'slicing', $visible_tabs, true ) ) : ?>
                        <?php do_action( 'pizzalayer_before_tab_slicing', $instance_id ); ?>
                        <section class="rp-panel" id="<?php echo esc_attr( $instance_id ); ?>-panel-slicing" role="tabpanel">
                            <div class="rp-panel__header">
                                <div class="rp-panel__badge">
                                    <span class="rp-panel__badge-num">06</span>
                                    <div class="rp-panel__badge-text">
                                        <h2 class="rp-panel__title"><?php esc_html_e( 'How Should We Slice It?', 'pizzalayer' ); ?></h2>
                                        <p class="rp-panel__hint"><?php esc_html_e( 'Cut fresh from the stone floor oven.', 'pizzalayer' ); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="rp-cards-grid rp-cards-grid--exclusive"><?php echo $cuts_html; // phpcs:ignore ?></div>
                            <div class="rp-panel__nav">
                                <button class="rp-btn rp-btn--prev" onclick="<?php echo esc_js( $rp_var ); ?>.goTab('drizzle')"><i class="fa fa-arrow-left"></i> <?php esc_html_e( 'Drizzle', 'pizzalayer' ); ?></button>
                                <button class="rp-btn rp-btn--next rp-btn--cta" onclick="<?php echo esc_js( $rp_var ); ?>.goTab('yourpizza')"><i class="fa fa-scroll"></i> <?php esc_html_e( 'Review Your Order', 'pizzalayer' ); ?></button>
                            </div>
                        </section>
                        <?php do_action( 'pizzalayer_after_tab_slicing', $instance_id ); ?>
                        <?php endif; ?>

                        <?php if ( in_array( 'yourpizza', $visible_tabs, true ) ) : ?>
                        <?php do_action( 'pizzalayer_before_tab_yourpizza', $instance_id ); ?>
                        <section class="rp-panel" id="<?php echo esc_attr( $instance_id ); ?>-panel-yourpizza" role="tabpanel">
                            <div class="rp-panel__header">
                                <div class="rp-panel__badge">
                                    <span class="rp-panel__badge-num"><i class="fa fa-scroll"></i></span>
                                    <div class="rp-panel__badge-text">
                                        <h2 class="rp-panel__title"><?php echo esc_html( $rp_order_title ); ?></h2>
                                        <p class="rp-panel__hint"><?php echo esc_html( $rp_order_tagline ); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="rp-yourpizza" id="<?php echo esc_attr( $instance_id ); ?>-summary">
                                <?php
                                $summary_rows = [
                                    'crust'    => [ 'fa-circle',     __( 'Crust',    'pizzalayer' ) ],
                                    'sauce'    => [ 'fa-droplet',    __( 'Sauce',    'pizzalayer' ) ],
                                    'cheese'   => [ 'fa-cheese',     __( 'Cheese',   'pizzalayer' ) ],
                                    'toppings' => [ 'fa-seedling',   __( 'Toppings', 'pizzalayer' ) ],
                                    'drizzle'  => [ 'fa-wine-glass', __( 'Drizzle',  'pizzalayer' ) ],
                                    'slicing'  => [ 'fa-pizza-slice',__( 'Slicing',  'pizzalayer' ) ],
                                ];
                                foreach ( $summary_rows as $key => [ $ico, $label ] ) :
                                ?>
                                <div class="rp-yourpizza__row" id="<?php echo esc_attr( $instance_id ); ?>-yp-<?php echo esc_attr( $key ); ?>">
                                    <div class="rp-yourpizza__icon"><i class="fa <?php echo esc_attr( $ico ); ?>"></i></div>
                                    <div class="rp-yourpizza__layer-name"><?php echo esc_html( $label ); ?></div>
                                    <div class="rp-yourpizza__selection rp-yourpizza__selection--empty" id="<?php echo esc_attr( $instance_id . '-yp-' . $key . '-val' ); ?>">
                                        <span class="rp-yp-none">— <?php esc_html_e( 'none selected', 'pizzalayer' ); ?> —</span>
                                    </div>
                                    <button class="rp-yourpizza__edit" onclick="<?php echo esc_js( $rp_var ); ?>.goTab('<?php echo esc_js( $key ); ?>')"><i class="fa fa-pen"></i></button>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="rp-panel__nav">
                                <button class="rp-btn rp-btn--prev" onclick="<?php echo esc_js( $rp_var ); ?>.goTab('slicing')"><i class="fa fa-arrow-left"></i> <?php esc_html_e( 'Back', 'pizzalayer' ); ?></button>
                                <button class="rp-btn rp-btn--ghost" onclick="ClearPizza(); window['<?php echo esc_js( $rp_var ); ?>']&&window['<?php echo esc_js( $rp_var ); ?>'].resetAll();"><i class="fa fa-rotate-left"></i> <?php esc_html_e( 'Start Over', 'pizzalayer' ); ?></button>
                            </div>
                        </section>
                        <?php do_action( 'pizzalayer_after_tab_yourpizza', $instance_id ); ?>
                        <?php endif; ?>

                    </div><!-- /.rp-panels -->
                </div><!-- /.rp-builder -->
            </div><!-- /.rp-builder-col -->

        </div><!-- /.rp-layout__row -->
    </div><!-- /.rp-layout -->

    <div id="<?php echo esc_attr( $instance_id ); ?>-fly-container" aria-hidden="true"></div>

</div><!-- /#<?php echo esc_html( $instance_id ); ?> .rp-root -->

<?php
// Initialize this instance's RP namespace via inline script
$rp_json_instance = wp_json_encode( $instance_id );
echo "<script>\nif (typeof RP !== 'undefined' && typeof RP.createInstance === 'function') {\n    var " . esc_js( $rp_var ) . " = RP.createInstance(" . $rp_json_instance . ");\n}\n</script>\n";
