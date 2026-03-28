<?php
/**
 * Scaffold Template — main builder render file.
 *
 * This file queries all CPTs, builds card HTML via helpers, then assembles
 * everything by including the HTML partials from ./partials/.
 *
 * ─── PARTIAL FILE SYSTEM ─────────────────────────────────────────────────────
 * Each visual element lives in its own file under ./partials/. To customise
 * a partial without touching this file:
 *   1. Duplicate the partial file and rename it (e.g. pizza-stage-custom.html).
 *   2. Override the path via the shortcode attribute partial_<name>=, e.g.:
 *        [pizzalayer-menu partial_pizza_stage="pizza-stage-custom.html"]
 *   3. Or filter: add_filter('pizzalayer_scaffold_partial', function($file, $name){
 *          if ($name === 'pizza-stage') return 'my-stage.html';
 *          return $file;
 *      }, 10, 2);
 *
 * ─── VARIABLES AVAILABLE IN ALL PARTIALS ─────────────────────────────────────
 *   $instance_id   string  unique builder instance ID
 *   $sc_var        string  JS window-object name, e.g. SC_pizzabuilder_1
 *   $atts          array   shortcode attributes
 *   $template_slug string  'scaffold'
 *   $pizza_shape   string  round | square | rectangle | custom
 *   $pizza_aspect  string  CSS aspect-ratio, e.g. '1 / 1'
 *   $pizza_radius  string  CSS border-radius, e.g. '8px'
 *   $visible_tabs  array   tab slugs to render
 *   $summary_title string  localised heading for summary panel
 *   $max_toppings  int     maximum allowed toppings
 * ─────────────────────────────────────────────────────────────────────────────
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

// ── Guard against direct inclusion without context ───────────────────────────
if ( ! isset( $instance_id ) )      { $instance_id    = 'pizzabuilder-1'; }
if ( ! isset( $atts ) )             { $atts           = []; }
if ( ! isset( $template_slug ) )    { $template_slug  = 'scaffold'; }
if ( ! isset( $function_prefix ) )  { $function_prefix = 'pzt_scaffold'; }

// ── JS namespace ──────────────────────────────────────────────────────────────
$sc_var = 'SC_' . preg_replace( '/[^a-zA-Z0-9_]/', '_', $instance_id );

// ── Max toppings ──────────────────────────────────────────────────────────────
$max_toppings = isset( $atts['max_toppings'] ) && (int) $atts['max_toppings'] > 0
    ? (int) $atts['max_toppings']
    : (int) get_option( 'pizzalayer_setting_topping_maxtoppings', 0 );
if ( $max_toppings < 1 ) { $max_toppings = 99; }
$max_toppings = (int) apply_filters( 'pizzalayer_max_toppings', $max_toppings, $instance_id );

// ── Pizza shape ───────────────────────────────────────────────────────────────
$valid_shapes  = [ 'round', 'square', 'rectangle', 'custom' ];
$pizza_shape   = sanitize_key( $atts['pizza_shape']  ?? get_option( 'pizzalayer_setting_pizza_shape',  'round'  ) );
if ( ! in_array( $pizza_shape, $valid_shapes, true ) ) { $pizza_shape = 'round'; }
$pizza_aspect  = sanitize_text_field( $atts['pizza_aspect']  ?? get_option( 'pizzalayer_setting_pizza_aspect',  '1 / 1' ) );
$pizza_radius  = sanitize_text_field( $atts['pizza_radius']  ?? get_option( 'pizzalayer_setting_pizza_radius',  '8px'   ) );

// ── Visible tabs ──────────────────────────────────────────────────────────────
$hide_tabs_raw = $atts['hide_tabs'] ?? '';
$show_tabs_raw = $atts['show_tabs'] ?? '';
$all_tabs      = [ 'crust', 'sauce', 'cheese', 'toppings', 'drizzle', 'slicing', 'yourpizza' ];
$all_tabs      = (array) apply_filters( 'pizzalayer_tab_order', $all_tabs, $instance_id );

if ( $show_tabs_raw ) {
    $visible_tabs = array_values( array_intersect( $all_tabs, array_map( 'trim', explode( ',', $show_tabs_raw ) ) ) );
} elseif ( $hide_tabs_raw ) {
    $visible_tabs = array_values( array_diff( $all_tabs, array_map( 'trim', explode( ',', $hide_tabs_raw ) ) ) );
} else {
    $visible_tabs = $all_tabs;
}

// ── Summary heading ───────────────────────────────────────────────────────────
$summary_title = sanitize_text_field(
    (string) get_option( 'scaffold_setting_summary_title', __( 'Your Pizza', 'pizzalayer' ) )
);

// ── CPT queries ───────────────────────────────────────────────────────────────
$_q_base  = [
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'orderby'        => [ 'menu_order' => 'ASC', 'title' => 'ASC' ],
];
$crusts   = (array) apply_filters( 'pizzalayer_query_args_crusts',   get_posts( array_merge( $_q_base, [ 'post_type' => 'pizzalayer_crusts'   ] ) ), 'crusts'   );
$sauces   = (array) apply_filters( 'pizzalayer_query_args_sauces',   get_posts( array_merge( $_q_base, [ 'post_type' => 'pizzalayer_sauces'   ] ) ), 'sauces'   );
$cheeses  = (array) apply_filters( 'pizzalayer_query_args_cheeses',  get_posts( array_merge( $_q_base, [ 'post_type' => 'pizzalayer_cheeses'  ] ) ), 'cheeses'  );
$drizzles = (array) apply_filters( 'pizzalayer_query_args_drizzles', get_posts( array_merge( $_q_base, [ 'post_type' => 'pizzalayer_drizzles' ] ) ), 'drizzles' );
$toppings = (array) apply_filters( 'pizzalayer_query_args_toppings', get_posts( array_merge( $_q_base, [ 'post_type' => 'pizzalayer_toppings' ] ) ), 'toppings' );
$cuts     = (array) apply_filters( 'pizzalayer_query_args_cuts',     get_posts( array_merge( $_q_base, [ 'post_type' => 'pizzalayer_cuts'     ] ) ), 'cuts'     );

// ── Partial loader ─────────────────────────────────────────────────────────────
/**
 * pzt_scaffold_partial()
 *
 * Resolves and includes a partial file, passing the current scope.
 * Partial name is the filename without the .html extension.
 *
 * Resolution order:
 *  1. Shortcode attribute partial_<snake_name>= (e.g. partial_pizza_stage=)
 *  2. 'pizzalayer_scaffold_partial' filter
 *  3. Default ./partials/<name>.html
 *
 * @param string $name          Partial name, e.g. 'pizza-stage'
 * @param array  $extra_vars    Additional variables to extract into partial scope
 * @param array  $atts_ref      Reference to $atts for shortcode overrides
 */
if ( ! function_exists( 'pzt_scaffold_partial' ) ) :
function pzt_scaffold_partial( string $name, array $extra_vars = [], array $atts_ref = [] ): void {
    // Check shortcode attr override (dashes → underscores for attr name)
    $attr_key = 'partial_' . str_replace( '-', '_', $name );
    $filename  = isset( $atts_ref[ $attr_key ] ) ? sanitize_file_name( $atts_ref[ $attr_key ] ) : ( $name . '.html' );

    // Allow filter override
    $filename = (string) apply_filters( 'pizzalayer_scaffold_partial', $filename, $name );

    $path = __DIR__ . '/partials/' . $filename;
    if ( ! file_exists( $path ) ) {
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
        trigger_error( "PizzaLayer Scaffold: partial not found: {$path}", E_USER_WARNING );
        return;
    }

    // Extract extra vars into this scope
    if ( $extra_vars ) { extract( $extra_vars, EXTR_SKIP ); } // phpcs:ignore WordPress.PHP.DontExtract
    include $path;
}
endif;

// ── Card render helpers ────────────────────────────────────────────────────────
/**
 * Render a single base-layer item card.
 * Returns HTML string — partial vars are resolved here before include.
 */
if ( ! function_exists( 'pzt_scaffold_render_item_card' ) ) :
function pzt_scaffold_render_item_card( \WP_Post $post, string $layer_type, string $sc_var ): string {
    $id         = $post->ID;
    $title      = get_the_title( $post );
    $slug       = sanitize_title( $title );
    $img_field  = $layer_type . '_image';
    $lyr_field  = $layer_type . '_layer_image';
    $thumb_url  = (string) ( get_field( $img_field, $id ) ?: get_field( $lyr_field, $id ) ?: get_the_post_thumbnail_url( $id, 'thumbnail' ) );
    $layer_url  = (string) ( get_field( $lyr_field, $id ) ?: $thumb_url );

    do_action( 'pizzalayer_before_layer_card', $post, $layer_type );

    ob_start();
    include __DIR__ . '/partials/item-card.html';
    $html = ob_get_clean();

    return (string) apply_filters( 'pizzalayer_card_html', $html, $post, $layer_type );
}
endif;

/**
 * Render a single topping card (multi-select + coverage).
 * Returns HTML string.
 */
if ( ! function_exists( 'pzt_scaffold_render_topping_card' ) ) :
function pzt_scaffold_render_topping_card( \WP_Post $post, string $sc_var, int $zindex ): string {
    $id        = $post->ID;
    $title     = get_the_title( $post );
    $slug      = sanitize_title( $title );
    $thumb_url = (string) ( get_field( 'topping_image', $id ) ?: get_field( 'topping_layer_image', $id ) ?: get_the_post_thumbnail_url( $id, 'thumbnail' ) );
    $layer_url = (string) ( get_field( 'topping_layer_image', $id ) ?: $thumb_url );
    $layer_id  = 'pizzalayer-topping-' . $slug;

    do_action( 'pizzalayer_before_layer_card', $post, 'toppings' );

    ob_start();
    include __DIR__ . '/partials/item-card-topping.html';
    $html = ob_get_clean();

    return (string) apply_filters( 'pizzalayer_card_html', $html, $post, 'toppings' );
}
endif;

// ── Pre-render all card HTML pools ─────────────────────────────────────────────
$crusts_html   = '';
foreach ( $crusts   as $post ) { if ( $post instanceof \WP_Post ) { $crusts_html   .= pzt_scaffold_render_item_card( $post, 'crust',   $sc_var ); } }
$sauces_html   = '';
foreach ( $sauces   as $post ) { if ( $post instanceof \WP_Post ) { $sauces_html   .= pzt_scaffold_render_item_card( $post, 'sauce',   $sc_var ); } }
$cheeses_html  = '';
foreach ( $cheeses  as $post ) { if ( $post instanceof \WP_Post ) { $cheeses_html  .= pzt_scaffold_render_item_card( $post, 'cheese',  $sc_var ); } }
$drizzles_html = '';
foreach ( $drizzles as $post ) { if ( $post instanceof \WP_Post ) { $drizzles_html .= pzt_scaffold_render_item_card( $post, 'drizzle', $sc_var ); } }
$cuts_html     = '';
foreach ( $cuts     as $post ) { if ( $post instanceof \WP_Post ) { $cuts_html     .= pzt_scaffold_render_item_card( $post, 'slicing', $sc_var ); } }
$toppings_html = '';
$_tz = 500;
foreach ( $toppings as $post ) {
    if ( ! ( $post instanceof \WP_Post ) ) { continue; }
    $toppings_html .= pzt_scaffold_render_topping_card( $post, $sc_var, $_tz );
    $_tz += 10;
}

// Map panel slug → [html pool, empty message]
$_panel_map = [
    'crust'    => [ $crusts_html,   __( 'No crusts found.',   'pizzalayer' ) ],
    'sauce'    => [ $sauces_html,   __( 'No sauces found.',   'pizzalayer' ) ],
    'cheese'   => [ $cheeses_html,  __( 'No cheeses found.',  'pizzalayer' ) ],
    'toppings' => [ $toppings_html, __( 'No toppings found.', 'pizzalayer' ) ],
    'drizzle'  => [ $drizzles_html, __( 'No drizzles found.', 'pizzalayer' ) ],
    'slicing'  => [ $cuts_html,     __( 'No cuts found.',     'pizzalayer' ) ],
];

// ── Template options ─────────────────────────────────────────────────────────
$_opt = fn( string $key, string $default = '' ) => (string) get_option( $key, $default );

$sc_accent_color   = $_opt( 'scaffold_setting_accent_color',   '#2271b1' );
$sc_bg_color       = $_opt( 'scaffold_setting_bg_color',       '#ffffff' );
$sc_text_color     = $_opt( 'scaffold_setting_text_color',     '#1e1e1e' );
$sc_border_color   = $_opt( 'scaffold_setting_border_color',   '#dcdcde' );
$sc_font_family    = $_opt( 'scaffold_setting_font_family',    'inherit' );
$sc_font_custom    = $_opt( 'scaffold_setting_font_custom',    '' );
$sc_base_font_size = $_opt( 'scaffold_setting_base_font_size', '14px'    );
$sc_card_radius    = $_opt( 'scaffold_setting_card_radius',    '6px'     );
$sc_card_cols      = $_opt( 'scaffold_setting_card_cols',      '3'       );
$sc_thumb_size     = $_opt( 'scaffold_setting_thumb_size',     '56px'    );
$sc_tab_style      = $_opt( 'scaffold_setting_tab_style',      'underline' );
$sc_builder_width  = $_opt( 'scaffold_setting_builder_width',  '' );
$sc_show_labels    = $_opt( 'scaffold_setting_show_labels',    'yes'     );
$sc_custom_css     = $_opt( 'scaffold_setting_custom_css',     '' );
$sc_anim_speed     = $_opt( 'scaffold_setting_anim_speed',     '200ms'   );

// Resolve font stack
$_fonts = [
    'inherit'    => 'inherit',
    'system'     => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
    'serif'      => 'Georgia, "Times New Roman", serif',
    'mono'       => '"Courier New", Courier, monospace',
    'custom'     => $sc_font_custom ? $sc_font_custom : 'inherit',
];
$sc_font_stack = $_fonts[ $sc_font_family ] ?? 'inherit';

$_cols_map = [ '2' => 'repeat(2,1fr)', '3' => 'repeat(3,1fr)', '4' => 'repeat(4,1fr)', 'auto' => 'repeat(auto-fill,minmax(80px,1fr))' ];
$sc_grid_cols_css = $_cols_map[ $sc_card_cols ] ?? 'repeat(3,1fr)';

$sc_width_css = $sc_builder_width ? 'max-width:' . esc_attr( $sc_builder_width ) . ';' : '';

// ── Topping JS data (needed by base JS engine) ────────────────────────────────
$_topping_data = [];
foreach ( $toppings as $_tp ) {
    if ( ! ( $_tp instanceof \WP_Post ) ) { continue; }
    $_ts    = sanitize_title( get_the_title( $_tp ) );
    $_tu    = (string) ( get_field( 'topping_layer_image', $_tp->ID ) ?: get_field( 'topping_image', $_tp->ID ) ?: get_the_post_thumbnail_url( $_tp->ID, 'full' ) );
    $_topping_data[] = [ 'slug' => $_ts, 'url' => $_tu, 'title' => get_the_title( $_tp ) ];
}
// ── Default selections from options ──────────────────────────────────────────
$_defaults = [
    'crust'   => sanitize_text_field( $atts['default_crust']  ?? (string) get_option( 'pizzalayer_setting_crust_defaultcrust',    '' ) ),
    'sauce'   => sanitize_text_field( $atts['default_sauce']  ?? (string) get_option( 'pizzalayer_setting_sauce_defaultsauce',    '' ) ),
    'cheese'  => sanitize_text_field( $atts['default_cheese'] ?? (string) get_option( 'pizzalayer_setting_cheese_defaultcheese',  '' ) ),
    'drizzle' => sanitize_text_field( $atts['default_drizzle'] ?? (string) get_option( 'pizzalayer_setting_drizzle_defaultdrizzle', '' ) ),
    'slicing' => sanitize_text_field( $atts['default_slicing'] ?? (string) get_option( 'pizzalayer_setting_cut_defaultcut',        '' ) ),
];
// ─────────────────────────────────────────────────────────────────────────────
// BEGIN HTML OUTPUT
// ─────────────────────────────────────────────────────────────────────────────
do_action( 'pizzalayer_before_builder', $instance_id, $template_slug );
?>
<div
  id="<?php echo esc_attr( $instance_id ); ?>"
  class="sc-root sc-tab-style--<?php echo esc_attr( $sc_tab_style ); ?>"
  data-instance="<?php echo esc_attr( $instance_id ); ?>"
  data-template="scaffold"
  data-max-toppings="<?php echo esc_attr( (string) $max_toppings ); ?>"
  style="<?php echo esc_attr( $sc_width_css ); ?>"
>

<!-- ── Inline CSS variables ─────────────────────────────────────────────── -->
<style>
#<?php echo esc_attr( $instance_id ); ?> {
  --sc-accent:      <?php echo esc_attr( $sc_accent_color ); ?>;
  --sc-bg:          <?php echo esc_attr( $sc_bg_color ); ?>;
  --sc-text:        <?php echo esc_attr( $sc_text_color ); ?>;
  --sc-border:      <?php echo esc_attr( $sc_border_color ); ?>;
  --sc-font:        <?php echo esc_attr( $sc_font_stack ); ?>;
  --sc-font-size:   <?php echo esc_attr( $sc_base_font_size ); ?>;
  --sc-card-radius: <?php echo esc_attr( $sc_card_radius ); ?>;
  --sc-thumb-size:  <?php echo esc_attr( $sc_thumb_size ); ?>;
  --sc-grid-cols:   <?php echo esc_attr( $sc_grid_cols_css ); ?>;
  --sc-anim-speed:  <?php echo esc_attr( $sc_anim_speed ); ?>;
}
<?php if ( 'yes' !== $sc_show_labels ) : ?>
#<?php echo esc_attr( $instance_id ); ?> .sc-card__label { display:none; }
<?php endif; ?>
<?php if ( $sc_custom_css ) : ?>
/* Custom CSS — Scaffold template */
<?php echo wp_strip_all_tags( $sc_custom_css ); // phpcs:ignore — template custom CSS ?>
<?php endif; ?>
</style>

<?php do_action( 'pizzalayer_scaffold_before_stage', $instance_id ); ?>

<!-- ── Pizza stage ───────────────────────────────────────────────────────── -->
<?php pzt_scaffold_partial( 'pizza-stage', compact( 'instance_id', 'sc_var', 'pizza_shape', 'pizza_aspect', 'pizza_radius', 'atts' ), $atts ); ?>

<?php do_action( 'pizzalayer_scaffold_before_tabs', $instance_id ); ?>

<!-- ── Tab bar ───────────────────────────────────────────────────────────── -->
<?php pzt_scaffold_partial( 'tab-bar', compact( 'instance_id', 'sc_var', 'visible_tabs', 'atts' ), $atts ); ?>

<?php do_action( 'pizzalayer_scaffold_before_panels', $instance_id ); ?>

<!-- ── Category panels ──────────────────────────────────────────────────── -->
<div class="sc-panels" data-instance="<?php echo esc_attr( $instance_id ); ?>">
<?php foreach ( $visible_tabs as $tab_slug ) :
    if ( 'yourpizza' === $tab_slug ) { continue; } // rendered separately below
    $panel_html = '';
    $empty_msg  = __( 'No items found.', 'pizzalayer' );
    if ( isset( $_panel_map[ $tab_slug ] ) ) {
        [ $panel_html, $empty_msg ] = $_panel_map[ $tab_slug ];
    }
    pzt_scaffold_partial( 'category-panel', compact( 'instance_id', 'sc_var', 'tab_slug', 'panel_html', 'empty_msg', 'atts' ), $atts );
endforeach; ?>

<!-- ── Summary panel ────────────────────────────────────────────────────── -->
<?php if ( in_array( 'yourpizza', $visible_tabs, true ) ) :
    pzt_scaffold_partial( 'summary-panel', compact( 'instance_id', 'sc_var', 'summary_title', 'atts' ), $atts );
endif; ?>

</div><!-- /.sc-panels -->

<?php do_action( 'pizzalayer_scaffold_after_panels', $instance_id ); ?>

<?php do_action( 'pizzalayer_builder_action_bar', $instance_id ); ?>

<!-- ── Bootstrap JS ─────────────────────────────────────────────────────── -->
<script>
(function() {
  'use strict';

  var ROOT       = document.getElementById( <?php echo wp_json_encode( $instance_id ); ?> );
  var VAR        = <?php echo wp_json_encode( $sc_var ); ?>;
  var DEFAULTS   = <?php echo wp_json_encode( $_defaults ); ?>;
  var MAX_TOP    = <?php echo (int) $max_toppings; ?>;
  var TOPPINGS   = <?php echo wp_json_encode( $_topping_data ); ?>;

  if ( ! ROOT ) { return; }

  /** Activate a tab and show its panel, hide all others. */
  function activateTab( slug ) {
    ROOT.querySelectorAll( '.sc-tab-btn' ).forEach( function( btn ) {
      var active = btn.getAttribute( 'data-tab' ) === slug;
      btn.classList.toggle( 'sc-tab-btn--active', active );
      btn.setAttribute( 'aria-selected', active ? 'true' : 'false' );
    } );
    ROOT.querySelectorAll( '.sc-panel' ).forEach( function( panel ) {
      var show = panel.getAttribute( 'data-panel' ) === slug;
      if ( show ) { panel.removeAttribute( 'hidden' ); }
      else        { panel.setAttribute( 'hidden', '' ); }
    } );
  }

  /** Swap an exclusive base layer (crust/sauce/cheese/drizzle/slicing). */
  function swapBase( layerType, slug, title, layerImg, triggerEl ) {
    // Deselect previous card
    ROOT.querySelectorAll( '.sc-card--exclusive[data-layer="' + layerType + '"]' ).forEach( function( c ) {
      c.classList.remove( 'sc-card--selected' );
    } );
    // Select this card
    var card = ( triggerEl && triggerEl.closest ) ? triggerEl.closest( '.sc-card' ) : null;
    if ( card ) { card.classList.add( 'sc-card--selected' ); }

    // Update layer image in pizza stage
    var img = document.getElementById( ROOT.id + '-layer-' + layerType );
    if ( img ) {
      img.src = layerImg;
      img.style.display = layerImg ? 'block' : 'none';
    }

    updateSummary();
    ROOT.dispatchEvent( new CustomEvent( 'pizzalayer:layerChanged', { detail: { layerType: layerType, slug: slug, title: title, layerImg: layerImg }, bubbles: true } ) );
  }

  /** Remove an exclusive base layer. */
  function removeBase( layerType, slug, triggerEl ) {
    var card = ( triggerEl && triggerEl.closest ) ? triggerEl.closest( '.sc-card' ) : null;
    if ( card ) { card.classList.remove( 'sc-card--selected' ); }
    var img = document.getElementById( ROOT.id + '-layer-' + layerType );
    if ( img ) { img.src = ''; img.style.display = 'none'; }
    updateSummary();
  }

  /** Add a topping. */
  function addTopping( zindex, slug, layerImg, title, layerId, inputId, triggerEl ) {
    var selected = ROOT.querySelectorAll( '.sc-card--topping.sc-card--selected' ).length;
    if ( selected >= MAX_TOP ) {
      ROOT.dispatchEvent( new CustomEvent( 'pizzalayer:maxToppings', { detail: { max: MAX_TOP }, bubbles: true } ) );
      return;
    }
    var card = ( triggerEl && triggerEl.closest ) ? triggerEl.closest( '.sc-card' ) : null;
    if ( card ) {
      card.classList.add( 'sc-card--selected' );
      var addBtn = card.querySelector( '.sc-card__btn--add' );
      var remBtn = card.querySelector( '.sc-card__btn--remove' );
      var covEl  = card.querySelector( '.sc-coverage' );
      if ( addBtn ) { addBtn.style.display = 'none'; }
      if ( remBtn ) { remBtn.style.display = ''; }
      if ( covEl )  { covEl.style.display = ''; }
    }
    // Inject layer image into stage
    var stage = document.getElementById( ROOT.id + '-stage' );
    if ( stage && layerImg ) {
      var existing = stage.querySelector( '[data-topping-slug="' + slug + '"]' );
      if ( ! existing ) {
        var el = document.createElement( 'img' );
        el.id                        = ROOT.id + '-tslot-' + slug;
        el.className                 = 'sc-layer sc-layer--topping';
        el.src                       = layerImg;
        el.alt                       = title;
        el.setAttribute( 'data-topping-slug', slug );
        el.style.zIndex              = String( zindex );
        stage.appendChild( el );
      }
    }
    updateSummary();
    ROOT.dispatchEvent( new CustomEvent( 'pizzalayer:toppingAdded', { detail: { slug: slug, title: title }, bubbles: true } ) );
  }

  /** Remove a topping. */
  function removeTopping( layerId, slug, triggerEl ) {
    var card = ( triggerEl && triggerEl.closest ) ? triggerEl.closest( '.sc-card' ) : null;
    if ( card ) {
      card.classList.remove( 'sc-card--selected' );
      var addBtn = card.querySelector( '.sc-card__btn--add' );
      var remBtn = card.querySelector( '.sc-card__btn--remove' );
      var covEl  = card.querySelector( '.sc-coverage' );
      if ( addBtn ) { addBtn.style.display = ''; }
      if ( remBtn ) { remBtn.style.display = 'none'; }
      if ( covEl )  { covEl.style.display = 'none'; }
    }
    var layerEl = document.getElementById( ROOT.id + '-tslot-' + slug );
    if ( layerEl ) { layerEl.remove(); }
    updateSummary();
    ROOT.dispatchEvent( new CustomEvent( 'pizzalayer:toppingRemoved', { detail: { slug: slug }, bubbles: true } ) );
  }

  /** Set coverage fraction on a selected topping. */
  function setCoverage( slug, fraction, triggerEl ) {
    var card = ( triggerEl && triggerEl.closest ) ? triggerEl.closest( '.sc-card' ) : null;
    if ( card ) {
      card.setAttribute( 'data-coverage', fraction );
      card.querySelectorAll( '.sc-cov-btn' ).forEach( function( b ) {
        b.classList.toggle( 'sc-cov-btn--active', b.getAttribute( 'data-fraction' ) === fraction );
      } );
    }
    // TODO: pass fraction through to layer clip-path for visual coverage
    ROOT.dispatchEvent( new CustomEvent( 'pizzalayer:coverageSet', { detail: { slug: slug, fraction: fraction }, bubbles: true } ) );
  }

  /** Collect current state as a plain object. */
  function getState() {
    var state = { layers: {}, toppings: [] };
    ROOT.querySelectorAll( '.sc-card--exclusive.sc-card--selected' ).forEach( function( c ) {
      state.layers[ c.getAttribute( 'data-layer' ) ] = {
        slug:  c.getAttribute( 'data-slug' ),
        title: c.getAttribute( 'data-title' ),
        img:   c.getAttribute( 'data-layer-img' ),
      };
    } );
    ROOT.querySelectorAll( '.sc-card--topping.sc-card--selected' ).forEach( function( c ) {
      state.toppings.push( {
        slug:     c.getAttribute( 'data-slug' ),
        title:    c.getAttribute( 'data-title' ),
        img:      c.getAttribute( 'data-layer-img' ),
        coverage: c.getAttribute( 'data-coverage' ) || 'whole',
      } );
    } );
    return state;
  }

  /** Update the summary panel list. */
  function updateSummary() {
    var list  = document.getElementById( ROOT.id + '-summary-rows' );
    var empty = ROOT.querySelector( '.sc-summary__empty' );
    if ( ! list ) { return; }

    var state = getState();
    var rows  = '';
    var layerLabels = { crust:'Crust', sauce:'Sauce', cheese:'Cheese', drizzle:'Drizzle', slicing:'Slicing' };

    Object.keys( state.layers ).forEach( function( ltype ) {
      var l = state.layers[ ltype ];
      var label = ( layerLabels[ ltype ] || ltype );
      rows += '<li class="sc-summary__row"><span class="sc-summary__layer-type">' + label + '</span><span class="sc-summary__layer-name">' + l.title + '</span></li>';
    } );
    state.toppings.forEach( function( t ) {
      rows += '<li class="sc-summary__row sc-summary__row--topping"><span class="sc-summary__layer-type">Topping</span><span class="sc-summary__layer-name">' + t.title + '</span><span class="sc-summary__coverage">' + t.coverage + '</span></li>';
    } );

    list.innerHTML = rows;
    var hasContent = !! rows;
    if ( empty ) { empty.style.display = hasContent ? 'none' : ''; }

    ROOT.dispatchEvent( new CustomEvent( 'pizzalayer:stateChanged', { detail: getState(), bubbles: true } ) );
  }

  /** Reset all choices. */
  function resetAll() {
    ROOT.querySelectorAll( '.sc-card--selected' ).forEach( function( c ) { c.classList.remove( 'sc-card--selected' ); } );
    ROOT.querySelectorAll( '.sc-card__btn--add'    ).forEach( function( b ) { b.style.display = ''; } );
    ROOT.querySelectorAll( '.sc-card__btn--remove' ).forEach( function( b ) { b.style.display = 'none'; } );
    ROOT.querySelectorAll( '.sc-coverage'          ).forEach( function( c ) { c.style.display = 'none'; } );
    ROOT.querySelectorAll( '.sc-layer' ).forEach( function( img ) { img.src = ''; img.style.display = 'none'; } );
    // Remove injected topping layers
    ROOT.querySelectorAll( '.sc-layer--topping' ).forEach( function( el ) { el.remove(); } );
    updateSummary();
    ROOT.dispatchEvent( new CustomEvent( 'pizzalayer:reset', { bubbles: true } ) );
  }

  // ── Public API ──────────────────────────────────────────────────────────────
  window[ VAR ] = {
    activateTab:   activateTab,
    swapBase:      swapBase,
    removeBase:    removeBase,
    addTopping:    addTopping,
    removeTopping: removeTopping,
    setCoverage:   setCoverage,
    getState:      getState,
    resetAll:      resetAll,
  };

  // ── Wire tab clicks ─────────────────────────────────────────────────────────
  ROOT.querySelectorAll( '.sc-tab-btn' ).forEach( function( btn ) {
    btn.addEventListener( 'click', function() {
      activateTab( btn.getAttribute( 'data-tab' ) );
    } );
  } );

  // ── Activate first tab ──────────────────────────────────────────────────────
  var firstTab = ROOT.querySelector( '.sc-tab-btn' );
  if ( firstTab ) { activateTab( firstTab.getAttribute( 'data-tab' ) ); }

  // ── Apply defaults ──────────────────────────────────────────────────────────
  (function applyDefaults() {
    Object.keys( DEFAULTS ).forEach( function( layer ) {
      var defaultSlug = DEFAULTS[ layer ];
      if ( ! defaultSlug ) { return; }
      var card = ROOT.querySelector( '.sc-card--exclusive[data-layer="' + layer + '"][data-slug="' + defaultSlug + '"]' );
      if ( ! card ) { return; }
      var btn = card.querySelector( '.sc-card__btn--select' );
      if ( btn ) { btn.click(); }
    } );
  })();

})();
</script>

</div><!-- /.sc-root -->

<?php do_action( 'pizzalayer_after_builder', $instance_id, $template_slug ); ?>
