<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
do_action( 'pizzalayer_file_pztp-containers-menu_start' );

/* =========================================================
   NIGHTPIE TEMPLATE — Main [pizzalayer-menu] shortcode
   Layout: sticky side-by-side on desktop (pizza left, tabs right)
           stacked on mobile (pizza mini bar top, tabs below)
   ========================================================= */

function pizzalayer_toppings_menu_func() {

    /* ── gather max toppings ── */
    $max_toppings = intval( get_option( 'pizzalayer_setting_topping_maxtoppings' ) );
    if ( $max_toppings < 1 ) { $max_toppings = 99; }

    /* ── query all CPTs we need ── */
    $query_args_base = [
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => 'menu_order title',
        'order'          => 'ASC',
    ];

    $crusts   = get_posts( array_merge( $query_args_base, [ 'post_type' => 'pizzalayer_crusts'   ] ) );
    $sauces   = get_posts( array_merge( $query_args_base, [ 'post_type' => 'pizzalayer_sauces'   ] ) );
    $cheeses  = get_posts( array_merge( $query_args_base, [ 'post_type' => 'pizzalayer_cheeses'  ] ) );
    $drizzles = get_posts( array_merge( $query_args_base, [ 'post_type' => 'pizzalayer_drizzles' ] ) );
    $toppings = get_posts( array_merge( $query_args_base, [ 'post_type' => 'pizzalayer_toppings' ] ) );
    $cuts     = get_posts( array_merge( $query_args_base, [ 'post_type' => 'pizzalayer_cuts'     ] ) );

    /* ── build per-tab panels ── */

    /* helper: single exclusive-select card (crust / sauce / cheese / drizzle / cut) */
    function np_exclusive_card( $post, $layer_type, $zindex = 200 ) {
        $id        = $post->ID;
        $title     = get_the_title( $post );
        $slug      = sanitize_title( $title );
        $img_field = $layer_type . '_image';
        $lyr_field = $layer_type . '_layer_image';

        // prefer product image for the thumb (menu image)
        $thumb_url = get_field( $img_field, $id );
        if ( ! $thumb_url ) { $thumb_url = get_field( $lyr_field, $id ); }
        if ( ! $thumb_url ) { $thumb_url = get_the_post_thumbnail_url( $id, 'medium' ); }

        $layer_url = get_field( $lyr_field, $id );
        if ( ! $layer_url ) { $layer_url = $thumb_url; }

        $js_title  = esc_js( $title );
        $js_layer  = esc_js( $layer_url ? $layer_url : '' );

        // JS call for base-layer swap
        $js_add    = "NP.swapBase('{$layer_type}','{$slug}','{$js_title}','{$js_layer}',this)";
        $js_remove = "NP.removeBase('{$layer_type}','{$slug}',this)";

        ob_start();
        ?>
        <div class="np-card np-card--exclusive"
             data-layer="<?php echo esc_attr( $layer_type ); ?>"
             data-slug="<?php echo esc_attr( $slug ); ?>"
             data-title="<?php echo esc_attr( $title ); ?>"
             data-thumb="<?php echo esc_attr( $thumb_url ); ?>"
             data-layer-img="<?php echo esc_attr( $layer_url ); ?>">
            <div class="np-card__thumb-wrap">
                <?php if ( $thumb_url ) : ?>
                    <img class="np-card__thumb" src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy" />
                <?php else : ?>
                    <div class="np-card__thumb np-card__thumb--placeholder"></div>
                <?php endif; ?>
                <div class="np-card__check"><i class="fa fa-check"></i></div>
            </div>
            <div class="np-card__body">
                <span class="np-card__name"><?php echo esc_html( $title ); ?></span>
            </div>
            <div class="np-card__actions">
                <button type="button" class="np-btn np-btn--add"
                        onclick="<?php echo esc_attr( $js_add ); ?>">
                    <i class="fa fa-plus"></i> Add
                </button>
                <button type="button" class="np-btn np-btn--remove" style="display:none;"
                        onclick="<?php echo esc_attr( $js_remove ); ?>">
                    <i class="fa fa-times"></i> Remove
                </button>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /* helper: topping card (multi-select, with quadrant coverage picker) */
    function np_topping_card( $post, $zindex ) {
        $id        = $post->ID;
        $title     = get_the_title( $post );
        $slug      = sanitize_title( $title );
        $layer_id  = 'pizzalayer-topping-' . $slug;

        $thumb_url = get_field( 'topping_image', $id );
        if ( ! $thumb_url ) { $thumb_url = get_field( 'topping_layer_image', $id ); }
        if ( ! $thumb_url ) { $thumb_url = get_the_post_thumbnail_url( $id, 'medium' ); }

        $layer_url = get_field( 'topping_layer_image', $id );
        if ( ! $layer_url ) { $layer_url = $thumb_url; }

        $js_title  = esc_js( $title );
        $js_slug   = esc_js( $slug );
        $js_layer  = esc_js( $layer_url ? $layer_url : '' );
        $fn_slug   = 'pizzalayer-topping-' . $slug;

        // JS: AddPizzaLayer( zindex, slug, layerimage, title, cssid, menuitemcssid )
        $js_add    = "NP.addTopping({$zindex},'{$js_slug}','{$js_layer}','{$js_title}','{$fn_slug}','{$fn_slug}',this)";
        $js_remove = "NP.removeTopping('pizzalayer-topping-{$js_slug}','{$js_slug}',this)";

        ob_start();
        ?>
        <div class="np-card np-card--topping"
             data-layer="toppings"
             data-slug="<?php echo esc_attr( $slug ); ?>"
             data-title="<?php echo esc_attr( $title ); ?>"
             data-thumb="<?php echo esc_attr( $thumb_url ); ?>"
             data-layer-img="<?php echo esc_attr( $layer_url ); ?>"
             data-zindex="<?php echo esc_attr( $zindex ); ?>">
            <div class="np-card__thumb-wrap">
                <?php if ( $thumb_url ) : ?>
                    <img class="np-card__thumb" src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy" />
                <?php else : ?>
                    <div class="np-card__thumb np-card__thumb--placeholder"></div>
                <?php endif; ?>
                <div class="np-card__check"><i class="fa fa-check"></i></div>
            </div>
            <div class="np-card__body">
                <span class="np-card__name"><?php echo esc_html( $title ); ?></span>
                <div class="np-coverage" style="display:none;">
                    <span class="np-coverage__label">Coverage:</span>
                    <div class="np-coverage__btns">
                        <button type="button" class="np-cov-btn" data-fraction="whole"
                                onclick="NP.setCoverage('<?php echo esc_js( $slug ); ?>','whole',this)">
                            <span class="np-cov-ico np-cov-ico--whole"></span> Whole
                        </button>
                        <button type="button" class="np-cov-btn" data-fraction="half-left"
                                onclick="NP.setCoverage('<?php echo esc_js( $slug ); ?>','half-left',this)">
                            <span class="np-cov-ico np-cov-ico--left"></span> Left
                        </button>
                        <button type="button" class="np-cov-btn" data-fraction="half-right"
                                onclick="NP.setCoverage('<?php echo esc_js( $slug ); ?>','half-right',this)">
                            <span class="np-cov-ico np-cov-ico--right"></span> Right
                        </button>
                        <button type="button" class="np-cov-btn" data-fraction="quarter-top-left"
                                onclick="NP.setCoverage('<?php echo esc_js( $slug ); ?>','quarter-top-left',this)">
                            <span class="np-cov-ico np-cov-ico--q1"></span> Q1
                        </button>
                        <button type="button" class="np-cov-btn" data-fraction="quarter-top-right"
                                onclick="NP.setCoverage('<?php echo esc_js( $slug ); ?>','quarter-top-right',this)">
                            <span class="np-cov-ico np-cov-ico--q2"></span> Q2
                        </button>
                        <button type="button" class="np-cov-btn" data-fraction="quarter-bottom-left"
                                onclick="NP.setCoverage('<?php echo esc_js( $slug ); ?>','quarter-bottom-left',this)">
                            <span class="np-cov-ico np-cov-ico--q3"></span> Q3
                        </button>
                        <button type="button" class="np-cov-btn" data-fraction="quarter-bottom-right"
                                onclick="NP.setCoverage('<?php echo esc_js( $slug ); ?>','quarter-bottom-right',this)">
                            <span class="np-cov-ico np-cov-ico--q4"></span> Q4
                        </button>
                    </div>
                </div>
            </div>
            <div class="np-card__actions">
                <button type="button" class="np-btn np-btn--add"
                        onclick="<?php echo esc_attr( $js_add ); ?>">
                    <i class="fa fa-plus"></i> Add
                </button>
                <button type="button" class="np-btn np-btn--remove" style="display:none;"
                        onclick="<?php echo esc_attr( $js_remove ); ?>">
                    <i class="fa fa-times"></i> Remove
                </button>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /* ── render all cards ── */
    ob_start();

    // crusts
    $crusts_html = '';
    foreach ( $crusts as $post ) { $crusts_html .= np_exclusive_card( $post, 'crust', 100 ); }
    if ( ! $crusts_html ) { $crusts_html = '<p class="np-empty"><i class="fa fa-circle-exclamation"></i> No crusts found.</p>'; }

    // sauces
    $sauces_html = '';
    foreach ( $sauces as $post ) { $sauces_html .= np_exclusive_card( $post, 'sauce', 150 ); }
    if ( ! $sauces_html ) { $sauces_html = '<p class="np-empty"><i class="fa fa-circle-exclamation"></i> No sauces found.</p>'; }

    // cheeses
    $cheeses_html = '';
    foreach ( $cheeses as $post ) { $cheeses_html .= np_exclusive_card( $post, 'cheese', 200 ); }
    if ( ! $cheeses_html ) { $cheeses_html = '<p class="np-empty"><i class="fa fa-circle-exclamation"></i> No cheeses found.</p>'; }

    // drizzles
    $drizzles_html = '';
    foreach ( $drizzles as $post ) { $drizzles_html .= np_exclusive_card( $post, 'drizzle', 900 ); }
    if ( ! $drizzles_html ) { $drizzles_html = '<p class="np-empty"><i class="fa fa-circle-exclamation"></i> No drizzles found.</p>'; }

    // toppings
    $toppings_html = '';
    $t_zindex = 400;
    foreach ( $toppings as $post ) {
        $toppings_html .= np_topping_card( $post, $t_zindex );
        $t_zindex += 10;
    }
    if ( ! $toppings_html ) { $toppings_html = '<p class="np-empty"><i class="fa fa-circle-exclamation"></i> No toppings found.</p>'; }

    // cuts (slicing) — same exclusive pattern as crust
    $cuts_html = '';
    foreach ( $cuts as $post ) { $cuts_html .= np_exclusive_card( $post, 'cut', 950 ); }
    if ( ! $cuts_html ) { $cuts_html = '<p class="np-empty"><i class="fa fa-circle-exclamation"></i> No cut styles found.</p>'; }

    ?>

<!-- ═══════════════════════════════════════════════════
     NIGHTPIE TEMPLATE
═══════════════════════════════════════════════════ -->
<div id="np-root" class="np-root" data-max-toppings="<?php echo esc_attr( $max_toppings ); ?>">

    <!-- ── Mobile mini-bar pizza (collapsed strip, always visible) ── -->
    <div class="np-mobile-preview-bar d-lg-none">
        <div class="np-mobile-preview-bar__inner">
            <span class="np-mobile-preview-bar__label"><i class="fa fa-pizza-slice"></i> Live Preview</span>
            <div class="np-mobile-preview-bar__pizza" id="np-pizza-mobile-slot">
                <!-- JS will mirror/clone the main pizza here on mobile -->
            </div>
            <button class="np-mobile-preview-bar__toggle" id="np-mobile-toggle" aria-label="Toggle pizza preview">
                <i class="fa fa-chevron-down"></i>
            </button>
        </div>
        <div class="np-mobile-preview-bar__expanded" id="np-mobile-expanded" aria-hidden="true">
            <!-- full pizza duplicated here when expanded -->
        </div>
    </div>

    <!-- ── Main layout: pizza col + tabs col ── -->
    <div class="np-layout container-fluid">
        <div class="row np-layout__row">

            <!-- ── LEFT: Sticky pizza visualizer ── -->
            <div class="col-lg-5 col-xl-4 np-pizza-col d-none d-lg-flex" id="np-pizza-col">
                <div class="np-pizza-sticky" id="np-pizza-sticky">
                    <div class="np-pizza-sticky__header">
                        <i class="fa fa-pizza-slice"></i>
                        <span>Your Pizza</span>
                    </div>
                    <div class="np-pizza-sticky__canvas" id="np-pizza-canvas">
                        <?php echo pizzalayer_pizza_dynamic_nested( 'pizzalayer-pizza', 'np-pizza-main' ); ?>
                    </div>
                    <div class="np-pizza-sticky__footer">
                        <button type="button" class="np-btn np-btn--ghost np-btn--sm" onclick="ClearPizza(); NP.resetAll();">
                            <i class="fa fa-rotate-left"></i> Reset
                        </button>
                        <span class="np-topping-counter" id="np-topping-counter">
                            <i class="fa fa-layer-group"></i>
                            <span id="np-topping-count">0</span> / <?php echo esc_html( $max_toppings ); ?> toppings
                        </span>
                    </div>
                </div>
            </div>

            <!-- ── RIGHT: Tabbed builder ── -->
            <div class="col-lg-7 col-xl-8 np-tabs-col">
                <div class="np-builder">

                    <!-- Tab nav -->
                    <nav class="np-tabnav" id="np-tabnav" role="tablist" aria-label="Pizza builder steps">
                        <button class="np-tab active" data-tab="crust"    role="tab" aria-selected="true"  aria-controls="np-panel-crust">
                            <span class="np-tab__icon"><i class="fa fa-circle"></i></span>
                            <span class="np-tab__label">Crust</span>
                        </button>
                        <button class="np-tab" data-tab="sauce"    role="tab" aria-selected="false" aria-controls="np-panel-sauce">
                            <span class="np-tab__icon"><i class="fa fa-droplet"></i></span>
                            <span class="np-tab__label">Sauce</span>
                        </button>
                        <button class="np-tab" data-tab="cheese"   role="tab" aria-selected="false" aria-controls="np-panel-cheese">
                            <span class="np-tab__icon"><i class="fa fa-cheese"></i></span>
                            <span class="np-tab__label">Cheese</span>
                        </button>
                        <button class="np-tab" data-tab="toppings" role="tab" aria-selected="false" aria-controls="np-panel-toppings">
                            <span class="np-tab__icon"><i class="fa fa-seedling"></i></span>
                            <span class="np-tab__label">Toppings</span>
                        </button>
                        <button class="np-tab" data-tab="drizzle"  role="tab" aria-selected="false" aria-controls="np-panel-drizzle">
                            <span class="np-tab__icon"><i class="fa fa-wine-glass"></i></span>
                            <span class="np-tab__label">Drizzle</span>
                        </button>
                        <button class="np-tab" data-tab="slicing"  role="tab" aria-selected="false" aria-controls="np-panel-slicing">
                            <span class="np-tab__icon"><i class="fa fa-pizza-slice"></i></span>
                            <span class="np-tab__label">Slicing</span>
                        </button>
                        <button class="np-tab" data-tab="yourpizza" role="tab" aria-selected="false" aria-controls="np-panel-yourpizza">
                            <span class="np-tab__icon"><i class="fa fa-receipt"></i></span>
                            <span class="np-tab__label">Your Pizza</span>
                        </button>
                    </nav>

                    <!-- Progress dots -->
                    <div class="np-progress" id="np-progress" aria-hidden="true">
                        <?php
                        $steps = ['crust','sauce','cheese','toppings','drizzle','slicing','yourpizza'];
                        foreach ( $steps as $s ) {
                            echo '<span class="np-progress__dot" data-step="' . esc_attr($s) . '"></span>';
                        }
                        ?>
                    </div>

                    <!-- Tab panels -->
                    <div class="np-panels" id="np-panels">

                        <!-- Crust -->
                        <section class="np-panel active" id="np-panel-crust" role="tabpanel" aria-label="Choose your crust">
                            <div class="np-panel__header">
                                <h2 class="np-panel__title"><i class="fa fa-circle"></i> Choose Your Crust</h2>
                                <p class="np-panel__hint">Select one crust — it forms the base of your pizza.</p>
                            </div>
                            <div class="np-cards-grid np-cards-grid--exclusive">
                                <?php echo $crusts_html; ?>
                            </div>
                            <div class="np-panel__nav">
                                <span></span>
                                <button class="np-btn np-btn--next" onclick="NP.goTab('sauce')">Sauce <i class="fa fa-arrow-right"></i></button>
                            </div>
                        </section>

                        <!-- Sauce -->
                        <section class="np-panel" id="np-panel-sauce" role="tabpanel" aria-label="Choose your sauce">
                            <div class="np-panel__header">
                                <h2 class="np-panel__title"><i class="fa fa-droplet"></i> Choose Your Sauce</h2>
                                <p class="np-panel__hint">Select one sauce — swaps the sauce layer on your pizza.</p>
                            </div>
                            <div class="np-cards-grid np-cards-grid--exclusive">
                                <?php echo $sauces_html; ?>
                            </div>
                            <div class="np-panel__nav">
                                <button class="np-btn np-btn--prev" onclick="NP.goTab('crust')"><i class="fa fa-arrow-left"></i> Crust</button>
                                <button class="np-btn np-btn--next" onclick="NP.goTab('cheese')">Cheese <i class="fa fa-arrow-right"></i></button>
                            </div>
                        </section>

                        <!-- Cheese -->
                        <section class="np-panel" id="np-panel-cheese" role="tabpanel" aria-label="Choose your cheese">
                            <div class="np-panel__header">
                                <h2 class="np-panel__title"><i class="fa fa-cheese"></i> Choose Your Cheese</h2>
                                <p class="np-panel__hint">Select one cheese — layers on top of the sauce.</p>
                            </div>
                            <div class="np-cards-grid np-cards-grid--exclusive">
                                <?php echo $cheeses_html; ?>
                            </div>
                            <div class="np-panel__nav">
                                <button class="np-btn np-btn--prev" onclick="NP.goTab('sauce')"><i class="fa fa-arrow-left"></i> Sauce</button>
                                <button class="np-btn np-btn--next" onclick="NP.goTab('toppings')">Toppings <i class="fa fa-arrow-right"></i></button>
                            </div>
                        </section>

                        <!-- Toppings -->
                        <section class="np-panel" id="np-panel-toppings" role="tabpanel" aria-label="Choose your toppings">
                            <div class="np-panel__header">
                                <h2 class="np-panel__title"><i class="fa fa-seedling"></i> Choose Your Toppings</h2>
                                <p class="np-panel__hint">
                                    Add up to <strong id="np-max-display"><?php echo esc_html( $max_toppings ); ?></strong> toppings.
                                    Choose coverage for each.
                                    <span class="np-topping-counter-inline">
                                        (<span id="np-topping-count-inline">0</span> added)
                                    </span>
                                </p>
                            </div>
                            <div class="np-cards-grid np-cards-grid--toppings">
                                <?php echo $toppings_html; ?>
                            </div>
                            <div class="np-panel__nav">
                                <button class="np-btn np-btn--prev" onclick="NP.goTab('cheese')"><i class="fa fa-arrow-left"></i> Cheese</button>
                                <button class="np-btn np-btn--next" onclick="NP.goTab('drizzle')">Drizzle <i class="fa fa-arrow-right"></i></button>
                            </div>
                        </section>

                        <!-- Drizzle -->
                        <section class="np-panel" id="np-panel-drizzle" role="tabpanel" aria-label="Choose your drizzle">
                            <div class="np-panel__header">
                                <h2 class="np-panel__title"><i class="fa fa-wine-glass"></i> Choose a Drizzle</h2>
                                <p class="np-panel__hint">Optional finishing drizzle on top.</p>
                            </div>
                            <div class="np-cards-grid np-cards-grid--exclusive">
                                <?php echo $drizzles_html; ?>
                            </div>
                            <div class="np-panel__nav">
                                <button class="np-btn np-btn--prev" onclick="NP.goTab('toppings')"><i class="fa fa-arrow-left"></i> Toppings</button>
                                <button class="np-btn np-btn--next" onclick="NP.goTab('slicing')">Slicing <i class="fa fa-arrow-right"></i></button>
                            </div>
                        </section>

                        <!-- Slicing -->
                        <section class="np-panel" id="np-panel-slicing" role="tabpanel" aria-label="Choose how to slice">
                            <div class="np-panel__header">
                                <h2 class="np-panel__title"><i class="fa fa-pizza-slice"></i> How Should We Slice It?</h2>
                                <p class="np-panel__hint">Choose a cut style for your pizza.</p>
                            </div>
                            <div class="np-cards-grid np-cards-grid--exclusive">
                                <?php echo $cuts_html; ?>
                            </div>
                            <div class="np-panel__nav">
                                <button class="np-btn np-btn--prev" onclick="NP.goTab('drizzle')"><i class="fa fa-arrow-left"></i> Drizzle</button>
                                <button class="np-btn np-btn--next np-btn--cta" onclick="NP.goTab('yourpizza')"><i class="fa fa-receipt"></i> See Your Pizza</button>
                            </div>
                        </section>

                        <!-- Your Pizza summary -->
                        <section class="np-panel" id="np-panel-yourpizza" role="tabpanel" aria-label="Your pizza summary">
                            <div class="np-panel__header">
                                <h2 class="np-panel__title"><i class="fa fa-receipt"></i> Your Pizza</h2>
                                <p class="np-panel__hint">Here's everything you've built. Looks delicious!</p>
                            </div>

                            <div class="np-yourpizza" id="np-yourpizza-summary">

                                <!-- layer rows injected by JS -->
                                <div class="np-yourpizza__row" id="np-yp-crust">
                                    <div class="np-yourpizza__icon"><i class="fa fa-circle"></i></div>
                                    <div class="np-yourpizza__layer-name">Crust</div>
                                    <div class="np-yourpizza__selection np-yourpizza__selection--empty" id="np-yp-crust-val">
                                        <span class="np-yp-none">— none selected —</span>
                                    </div>
                                    <button class="np-yourpizza__edit" onclick="NP.goTab('crust')"><i class="fa fa-pen"></i></button>
                                </div>

                                <div class="np-yourpizza__row" id="np-yp-sauce">
                                    <div class="np-yourpizza__icon"><i class="fa fa-droplet"></i></div>
                                    <div class="np-yourpizza__layer-name">Sauce</div>
                                    <div class="np-yourpizza__selection np-yourpizza__selection--empty" id="np-yp-sauce-val">
                                        <span class="np-yp-none">— none selected —</span>
                                    </div>
                                    <button class="np-yourpizza__edit" onclick="NP.goTab('sauce')"><i class="fa fa-pen"></i></button>
                                </div>

                                <div class="np-yourpizza__row" id="np-yp-cheese">
                                    <div class="np-yourpizza__icon"><i class="fa fa-cheese"></i></div>
                                    <div class="np-yourpizza__layer-name">Cheese</div>
                                    <div class="np-yourpizza__selection np-yourpizza__selection--empty" id="np-yp-cheese-val">
                                        <span class="np-yp-none">— none selected —</span>
                                    </div>
                                    <button class="np-yourpizza__edit" onclick="NP.goTab('cheese')"><i class="fa fa-pen"></i></button>
                                </div>

                                <div class="np-yourpizza__row np-yourpizza__row--toppings" id="np-yp-toppings-row">
                                    <div class="np-yourpizza__icon"><i class="fa fa-seedling"></i></div>
                                    <div class="np-yourpizza__layer-name">Toppings</div>
                                    <div class="np-yourpizza__selection" id="np-yp-toppings-val">
                                        <span class="np-yp-none">— none added —</span>
                                    </div>
                                    <button class="np-yourpizza__edit" onclick="NP.goTab('toppings')"><i class="fa fa-pen"></i></button>
                                </div>

                                <div class="np-yourpizza__row" id="np-yp-drizzle">
                                    <div class="np-yourpizza__icon"><i class="fa fa-wine-glass"></i></div>
                                    <div class="np-yourpizza__layer-name">Drizzle</div>
                                    <div class="np-yourpizza__selection np-yourpizza__selection--empty" id="np-yp-drizzle-val">
                                        <span class="np-yp-none">— none selected —</span>
                                    </div>
                                    <button class="np-yourpizza__edit" onclick="NP.goTab('drizzle')"><i class="fa fa-pen"></i></button>
                                </div>

                                <div class="np-yourpizza__row" id="np-yp-slicing">
                                    <div class="np-yourpizza__icon"><i class="fa fa-pizza-slice"></i></div>
                                    <div class="np-yourpizza__layer-name">Slicing</div>
                                    <div class="np-yourpizza__selection np-yourpizza__selection--empty" id="np-yp-slicing-val">
                                        <span class="np-yp-none">— none selected —</span>
                                    </div>
                                    <button class="np-yourpizza__edit" onclick="NP.goTab('slicing')"><i class="fa fa-pen"></i></button>
                                </div>

                            </div><!-- /.np-yourpizza -->

                            <div class="np-panel__nav">
                                <button class="np-btn np-btn--prev" onclick="NP.goTab('slicing')"><i class="fa fa-arrow-left"></i> Back</button>
                                <button class="np-btn np-btn--ghost" onclick="ClearPizza(); NP.resetAll();">
                                    <i class="fa fa-rotate-left"></i> Start Over
                                </button>
                            </div>
                        </section>

                    </div><!-- /.np-panels -->
                </div><!-- /.np-builder -->
            </div><!-- /.np-tabs-col -->

        </div><!-- /.row -->
    </div><!-- /.np-layout -->

    <!-- Fly-to animation clone container (injected by JS) -->
    <div id="np-fly-container" aria-hidden="true"></div>

</div><!-- /#np-root -->

<?php
    return ob_get_clean();
}

do_action( 'pizzalayer_file_pztp-containers-menu_end' );
