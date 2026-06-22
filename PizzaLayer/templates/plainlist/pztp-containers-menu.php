<?php
/**
 * Plainlist Template — [pizza_builder] output.
 *
 * A text-first, checklist-style pizza builder with no visual pizza canvas.
 * Two modes controlled by plainlist_setting_layout_mode:
 *   - 'single-list'  : All sections rendered on one scrollable page.
 *   - 'step-by-step' : Sections shown one at a time with Prev/Next navigation.
 *
 * Exclusive sections (crust, sauce, cheese, drizzle, cut) use radio-like
 * single-select. Toppings use multi-select with optional max limit.
 *
 * Variables available (from BuilderShortcode):
 *   $instance_id   — unique ID string
 *   $atts          — shortcode attribute array
 *   $template_slug — 'plainlist'
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! isset( $instance_id ) )    { $instance_id    = 'pizzabuilder-1'; }
if ( ! isset( $atts ) )           { $atts           = []; }
if ( ! isset( $template_slug ) )  { $template_slug  = 'plainlist'; }
if ( ! isset( $function_prefix ) ) { $function_prefix = 'pzt_plainlist'; }

// ── Read Plainlist settings ───────────────────────────────────────────────────
$pl_layout       = sanitize_key( get_option( 'plainlist_setting_layout_mode',       'single-list' ) );
$pl_check_style  = sanitize_key( get_option( 'plainlist_setting_check_style',       'checkbox'    ) );
$pl_columns      = sanitize_key( get_option( 'plainlist_setting_columns',           '1'           ) );
$pl_show_dividers  = get_option( 'plainlist_setting_show_dividers',  'yes' ) === 'yes';
$pl_show_icons     = get_option( 'plainlist_setting_show_section_icons', 'yes' ) === 'yes';
$pl_show_prices    = get_option( 'plainlist_setting_show_prices',    'no'  ) === 'yes';
$pl_show_count     = get_option( 'plainlist_setting_show_item_count','no'  ) === 'yes';
$pl_show_summary   = get_option( 'plainlist_setting_show_summary',   'yes' ) === 'yes';
$pl_show_reset     = get_option( 'plainlist_setting_show_reset',     'yes' ) === 'yes';
$pl_intro_text     = sanitize_text_field( get_option( 'plainlist_setting_intro_text', '' ) );
$pl_footer_note    = wp_kses_post( get_option( 'plainlist_setting_footer_note', '' ) );
$pl_summary_heading = sanitize_text_field( get_option( 'plainlist_setting_summary_heading', 'Your Selection' ) );
$pl_reset_label    = sanitize_text_field( get_option( 'plainlist_setting_reset_label', 'Clear all' ) );
$pl_step_next      = sanitize_text_field( get_option( 'plainlist_setting_step_btn_label_next', 'Next →' ) );
$pl_step_prev      = sanitize_text_field( get_option( 'plainlist_setting_step_btn_label_prev', '← Back' ) );
$pl_step_progress  = get_option( 'plainlist_setting_step_show_progress', 'yes' ) === 'yes';
$pl_step_require   = get_option( 'plainlist_setting_step_require_selection', 'no' ) === 'yes';

// Column CSS class
$pl_col_class_map = [
	'2'    => 'pl-list--cols-2',
	'3'    => 'pl-list--cols-3',
	'auto' => 'pl-list--cols-auto',
];
$pl_col_class = $pl_col_class_map[ $pl_columns ] ?? '';

// Visible tabs (respects show_tabs / hide_tabs shortcode attr)
$hide_tabs_raw = $atts['hide_tabs'] ?? '';
$show_tabs_raw = $atts['show_tabs'] ?? '';
$all_tabs      = [ 'crust', 'sauce', 'cheese', 'toppings', 'drizzle', 'slicing' ];
$all_tabs      = apply_filters( 'pizzalayer_tab_order', $all_tabs, $instance_id );

if ( $show_tabs_raw ) {
	$visible_tabs = array_intersect( $all_tabs, array_map( 'trim', explode( ',', $show_tabs_raw ) ) );
} elseif ( $hide_tabs_raw ) {
	$hide_set     = array_map( 'trim', explode( ',', $hide_tabs_raw ) );
	$visible_tabs = array_diff( $all_tabs, $hide_set );
} else {
	$visible_tabs = $all_tabs;
}
$visible_tabs = array_values( $visible_tabs );

// Max toppings
$max_toppings = isset( $atts['max_toppings'] ) && (int) $atts['max_toppings'] > 0
	? (int) $atts['max_toppings']
	: intval( get_option( 'pizzalayer_setting_topping_maxtoppings', 0 ) );
if ( $max_toppings < 1 ) { $max_toppings = 99; }
$max_toppings = (int) apply_filters( 'pizzalayer_max_toppings', $max_toppings, $instance_id );

// ── Query CPTs ────────────────────────────────────────────────────────────────
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

// ── Item builders ─────────────────────────────────────────────────────────────

/**
 * Build the <li> for an exclusive item (crust/sauce/cheese/drizzle/cut).
 */
if ( ! function_exists( 'pzt_plainlist_exclusive_item' ) ) :
function pzt_plainlist_exclusive_item( $post, string $layer_type, bool $show_price, string $pl_var ): string {
	if ( ! ( $post instanceof \WP_Post ) ) { return ''; }
	$id     = $post->ID;
	$title  = get_the_title( $post );
	$slug   = sanitize_title( $title );
	$input_id = 'pl-' . esc_attr( $layer_type ) . '-' . esc_attr( $slug );

	$price_raw = '';
	if ( $show_price ) {
		$price_raw = get_field( $layer_type . '_price', $id ) ?: get_post_meta( $id, $layer_type . '_price', true );
	}

	// JS: reuse the same API as other templates for compatibility
	$layer_url = get_field( $layer_type . '_layer_image', $id ) ?: '';
	$js_title  = esc_js( $title );
	$js_layer  = esc_js( (string) $layer_url );
	$js_toggle = "window['{$pl_var}']&&window['{$pl_var}'].plToggleExclusive('{$layer_type}','{$slug}','{$js_title}','{$js_layer}',this)";

	ob_start();
	do_action( 'pizzalayer_before_layer_card', $post, $layer_type );
	?>
	<li class="pl-item pl-item--exclusive"
	    data-layer="<?php echo esc_attr( $layer_type ); ?>"
	    data-slug="<?php echo esc_attr( $slug ); ?>"
	    data-title="<?php echo esc_attr( $title ); ?>"
	    onclick="<?php echo esc_attr( $js_toggle ); ?>"
	    role="radio"
	    aria-checked="false"
	    tabindex="0"
	    onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();<?php echo esc_attr( $js_toggle ); ?>}">
		<span class="pl-item__check" aria-hidden="true"></span>
		<input class="pl-item__input" type="radio"
		       name="pl-<?php echo esc_attr( $instance_id ); ?>-<?php echo esc_attr( $layer_type ); ?>"
		       id="<?php echo esc_attr( $input_id ); ?>"
		       value="<?php echo esc_attr( $slug ); ?>"
		       tabindex="-1">
		<label class="pl-item__label" for="<?php echo esc_attr( $input_id ); ?>" onclick="return false;">
			<?php echo esc_html( $title ); ?>
			<?php if ( $price_raw ) : ?>
			<span class="pl-item__price"><?php echo esc_html( $price_raw ); ?></span>
			<?php endif; ?>
		</label>
	</li>
	<?php
	do_action( 'pizzalayer_after_layer_card', $post, $layer_type );
	return apply_filters( 'pizzalayer_card_html', ob_get_clean(), $post, $layer_type );
}
endif;

/**
 * Build the <li> for a topping item (multi-select).
 */
if ( ! function_exists( 'pzt_plainlist_topping_item' ) ) :
function pzt_plainlist_topping_item( $post, bool $show_price, int $zindex, string $pl_var ): string {
	if ( ! ( $post instanceof \WP_Post ) ) { return ''; }
	$id       = $post->ID;
	$title    = get_the_title( $post );
	$slug     = sanitize_title( $title );
	$layer_id = 'pizzalayer-topping-' . $slug;
	$input_id = 'pl-topping-' . $slug;

	$price_raw = '';
	if ( $show_price ) {
		$price_raw = get_field( 'topping_price', $id ) ?: get_post_meta( $id, 'topping_price', true );
	}

	$layer_url = get_field( 'topping_layer_image', $id ) ?: '';
	$thumb_url = get_field( 'topping_image', $id ) ?: $layer_url;
	$js_title  = esc_js( $title );
	$js_slug   = esc_js( $slug );
	$js_layer  = esc_js( (string) $layer_url );
	$js_thumb  = esc_js( (string) $thumb_url );

	$js_toggle = "window['{$pl_var}']&&window['{$pl_var}'].plToggleTopping({$zindex},'{$js_slug}','{$js_layer}','{$js_title}','{$layer_id}','{$layer_id}','{$js_thumb}',this)";

	ob_start();
	do_action( 'pizzalayer_before_layer_card', $post, 'toppings' );
	?>
	<li class="pl-item pl-item--topping"
	    data-layer="toppings"
	    data-slug="<?php echo esc_attr( $slug ); ?>"
	    data-title="<?php echo esc_attr( $title ); ?>"
	    data-zindex="<?php echo esc_attr( (string) $zindex ); ?>"
	    onclick="<?php echo esc_attr( $js_toggle ); ?>"
	    role="checkbox"
	    aria-checked="false"
	    tabindex="0"
	    onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();<?php echo esc_attr( $js_toggle ); ?>}">
		<span class="pl-item__check" aria-hidden="true"></span>
		<input class="pl-item__input" type="checkbox"
		       id="<?php echo esc_attr( $input_id ); ?>"
		       value="<?php echo esc_attr( $slug ); ?>"
		       tabindex="-1">
		<label class="pl-item__label" for="<?php echo esc_attr( $input_id ); ?>" onclick="return false;">
			<?php echo esc_html( $title ); ?>
			<?php if ( $price_raw ) : ?>
			<span class="pl-item__price"><?php echo esc_html( $price_raw ); ?></span>
			<?php endif; ?>
		</label>
	</li>
	<?php
	do_action( 'pizzalayer_after_layer_card', $post, 'toppings' );
	return apply_filters( 'pizzalayer_card_html', ob_get_clean(), $post, 'toppings' );
}
endif;

// ── Build HTML per section ────────────────────────────────────────────────────
$pl_var = 'PL_' . preg_replace( '/[^a-zA-Z0-9_]/', '_', $instance_id );

$section_meta = [
	'crust'    => [ 'fa-layer-group',    __( 'Crust',    'pizzalayer' ) ],
	'sauce'    => [ 'fa-droplet',        __( 'Sauce',    'pizzalayer' ) ],
	'cheese'   => [ 'fa-cheese',         __( 'Cheese',   'pizzalayer' ) ],
	'toppings' => [ 'fa-leaf',           __( 'Toppings', 'pizzalayer' ) ],
	'drizzle'  => [ 'fa-bottle-droplet', __( 'Drizzle',  'pizzalayer' ) ],
	'slicing'  => [ 'fa-pizza-slice',    __( 'Slicing',  'pizzalayer' ) ],
];

$sections_data = [];

// Crusts
$items_html = '';
foreach ( $crusts as $post ) { $items_html .= pzt_plainlist_exclusive_item( $post, 'crust', $pl_show_prices, $pl_var ); }
$sections_data['crust'] = $items_html ?: '<li class="pl-empty">' . esc_html__( 'No crusts found.', 'pizzalayer' ) . '</li>';

// Sauces
$items_html = '';
foreach ( $sauces as $post ) { $items_html .= pzt_plainlist_exclusive_item( $post, 'sauce', $pl_show_prices, $pl_var ); }
$sections_data['sauce'] = $items_html ?: '<li class="pl-empty">' . esc_html__( 'No sauces found.', 'pizzalayer' ) . '</li>';

// Cheeses
$items_html = '';
foreach ( $cheeses as $post ) { $items_html .= pzt_plainlist_exclusive_item( $post, 'cheese', $pl_show_prices, $pl_var ); }
$sections_data['cheese'] = $items_html ?: '<li class="pl-empty">' . esc_html__( 'No cheeses found.', 'pizzalayer' ) . '</li>';

// Drizzles
$items_html = '';
foreach ( $drizzles as $post ) { $items_html .= pzt_plainlist_exclusive_item( $post, 'drizzle', $pl_show_prices, $pl_var ); }
$sections_data['drizzle'] = $items_html ?: '<li class="pl-empty">' . esc_html__( 'No drizzles found.', 'pizzalayer' ) . '</li>';

// Toppings
$items_html = '';
$t_z = 400;
foreach ( $toppings as $post ) {
	$items_html .= pzt_plainlist_topping_item( $post, $pl_show_prices, $t_z, $pl_var );
	$t_z += 10;
}
$sections_data['toppings'] = $items_html ?: '<li class="pl-empty">' . esc_html__( 'No toppings found.', 'pizzalayer' ) . '</li>';

// Cuts / Slicing
$items_html = '';
foreach ( $cuts as $post ) { $items_html .= pzt_plainlist_exclusive_item( $post, 'cut', $pl_show_prices, $pl_var ); }
$sections_data['slicing'] = $items_html ?: '<li class="pl-empty">' . esc_html__( 'No cut styles found.', 'pizzalayer' ) . '</li>';

// ── Item counts ───────────────────────────────────────────────────────────────
$section_counts = [
	'crust'    => count( $crusts ),
	'sauce'    => count( $sauces ),
	'cheese'   => count( $cheeses ),
	'toppings' => count( $toppings ),
	'drizzle'  => count( $drizzles ),
	'slicing'  => count( $cuts ),
];

$is_step = ( $pl_layout === 'step-by-step' );
$total_steps = count( $visible_tabs );
?>
<!-- ═══════════════════════════════════════════════════════════════
     PLAINLIST TEMPLATE — PizzaLayer
     Instance: <?php echo esc_html( $instance_id ); ?>
     Mode: <?php echo esc_html( $pl_layout ); ?>
══════════════════════════════════════════════════════════════════ -->
<div id="<?php echo esc_attr( $instance_id ); ?>"
     class="pl-root pl-root--check-<?php echo esc_attr( $pl_check_style ); ?><?php echo $is_step ? ' pl-root--step-mode' : ' pl-root--list-mode'; ?>"
     data-instance="<?php echo esc_attr( $instance_id ); ?>"
     data-pl-var="<?php echo esc_attr( $pl_var ); ?>"
     data-layout="<?php echo esc_attr( $pl_layout ); ?>"
     data-max-toppings="<?php echo esc_attr( (string) $max_toppings ); ?>"
     data-require-selection="<?php echo $pl_step_require ? 'yes' : 'no'; ?>">

	<div class="pl-inner">

		<?php if ( $pl_intro_text ) : ?>
		<p class="pl-intro"><?php echo esc_html( $pl_intro_text ); ?></p>
		<?php endif; ?>

		<?php if ( $is_step ) : ?>
		<!-- ── Step mode: progress indicator ──────────────── -->
		<?php if ( $pl_step_progress ) : ?>
		<div class="pl-progress" id="<?php echo esc_attr( $instance_id ); ?>-progress" aria-live="polite">
			<span class="pl-progress__text">
				<?php
				/* translators: 1: current step, 2: total steps */
				printf( esc_html__( 'Step %1$s of %2$s', 'pizzalayer' ),
					'<span class="pl-progress__current">1</span>',
					'<span class="pl-progress__total">' . esc_html( (string) $total_steps ) . '</span>'
				);
				?>
			</span>
			<div class="pl-progress__bar-wrap">
				<div class="pl-progress__bar" id="<?php echo esc_attr( $instance_id ); ?>-progress-bar"
				     style="width: <?php echo esc_attr( round( 100 / max(1, $total_steps) ) ); ?>%"></div>
			</div>
		</div>
		<?php endif; ?>
		<?php endif; ?>

		<!-- ── Sections ────────────────────────────────────── -->
		<?php
		$step_index = 0;
		foreach ( $visible_tabs as $tab ) :
			if ( ! isset( $section_meta[ $tab ], $sections_data[ $tab ] ) ) { continue; }
			[ $icon, $label ] = $section_meta[ $tab ];
			$is_first = ( $step_index === 0 );
			$section_classes = 'pl-section';
			if ( $pl_show_dividers ) { $section_classes .= ' pl-section--with-divider'; }
			if ( $is_step ) {
				$section_classes .= ' pl-section--step';
				if ( $is_first ) { $section_classes .= ' pl-section--active'; }
			}
		?>
		<?php do_action( 'pizzalayer_before_tab_' . $tab, $instance_id ); ?>
		<section class="<?php echo esc_attr( $section_classes ); ?>"
		         id="<?php echo esc_attr( $instance_id . '-section-' . $tab ); ?>"
		         data-section="<?php echo esc_attr( $tab ); ?>"
		         data-step-index="<?php echo esc_attr( (string) $step_index ); ?>"
		         <?php if ( $is_step ) : ?>aria-hidden="<?php echo $is_first ? 'false' : 'true'; ?>"<?php endif; ?>>

			<div class="pl-section__header">
				<?php if ( $pl_show_icons ) : ?>
				<span class="pl-section__icon" aria-hidden="true"><i class="fa <?php echo esc_attr( $icon ); ?>"></i></span>
				<?php endif; ?>
				<h2 class="pl-section__title"><?php echo esc_html( $label ); ?></h2>
				<?php if ( $pl_show_count && isset( $section_counts[ $tab ] ) && $section_counts[ $tab ] > 0 ) : ?>
				<span class="pl-section__badge"><?php echo esc_html( (string) $section_counts[ $tab ] ); ?></span>
				<?php endif; ?>
				<?php if ( $tab === 'toppings' ) : ?>
				<span class="pl-section__badge pl-section__badge--selected" id="<?php echo esc_attr( $instance_id ); ?>-topping-count" style="display:none;">0</span>
				<?php endif; ?>
			</div>

			<ul class="pl-list<?php echo $pl_col_class ? ' ' . esc_attr( $pl_col_class ) : ''; ?>"
			    role="<?php echo ( $tab === 'toppings' ) ? 'group' : 'radiogroup'; ?>"
			    aria-label="<?php echo esc_attr( $label ); ?>">
				<?php echo $sections_data[ $tab ]; // phpcs:ignore WordPress.Security.EscapeOutput -- built by safe functions above ?>
			</ul>

			<?php if ( $tab === 'slicing' ) : ?>
			<!-- Action bar: PizzaLayerPro / WooCommerce hooks here -->
			<div class="pl-action-bar">
				<?php do_action( 'pizzalayer_builder_action_bar', $instance_id ); ?>
			</div>
			<?php endif; ?>

		</section>
		<?php do_action( 'pizzalayer_after_tab_' . $tab, $instance_id ); ?>
		<?php $step_index++; endforeach; ?>

		<?php if ( $is_step ) : ?>
		<!-- ── Step navigation buttons ─────────────────────── -->
		<nav class="pl-step-nav" id="<?php echo esc_attr( $instance_id ); ?>-step-nav" aria-label="<?php esc_attr_e( 'Step navigation', 'pizzalayer' ); ?>">
			<button type="button"
			        class="pl-step-nav__btn pl-step-nav__btn--prev"
			        id="<?php echo esc_attr( $instance_id ); ?>-step-prev"
			        disabled>
				<?php echo esc_html( $pl_step_prev ); ?>
			</button>
			<button type="button"
			        class="pl-step-nav__btn pl-step-nav__btn--next"
			        id="<?php echo esc_attr( $instance_id ); ?>-step-next">
				<?php echo esc_html( $pl_step_next ); ?>
			</button>
		</nav>
		<?php endif; ?>

		<?php if ( $pl_show_summary ) : ?>
		<!-- ── Selection summary ────────────────────────────── -->
		<div class="pl-summary" id="<?php echo esc_attr( $instance_id ); ?>-summary">
			<h3 class="pl-summary__heading"><?php echo esc_html( $pl_summary_heading ); ?></h3>
			<ul class="pl-summary__list" id="<?php echo esc_attr( $instance_id ); ?>-summary-list">
				<li class="pl-summary__empty"><?php esc_html_e( 'No items selected yet.', 'pizzalayer' ); ?></li>
			</ul>
		</div>
		<?php endif; ?>

		<?php if ( $pl_show_reset ) : ?>
		<!-- ── Reset button ─────────────────────────────────── -->
		<button type="button"
		        class="pl-reset-btn"
		        id="<?php echo esc_attr( $instance_id ); ?>-reset"
		        onclick="window['<?php echo esc_js( $pl_var ); ?>']&&window['<?php echo esc_js( $pl_var ); ?>'].plReset();">
			<i class="fa fa-rotate-left" aria-hidden="true"></i>
			<?php echo esc_html( $pl_reset_label ); ?>
		</button>
		<?php endif; ?>

		<?php if ( $pl_footer_note ) : ?>
		<!-- ── Footer note ──────────────────────────────────── -->
		<div class="pl-footer-note">
			<?php echo $pl_footer_note; // phpcs:ignore WordPress.Security.EscapeOutput — sanitized via wp_kses_post on read ?>
		</div>
		<?php endif; ?>

	</div><!-- /.pl-inner -->

</div><!-- /#<?php echo esc_html( $instance_id ); ?> .pl-root -->

<?php
// Initialize this instance via wp_add_inline_script (WP.org compliant — no inline <script>).
$pl_init_js = "(function(){"
	. "if(typeof PL!=='undefined'&&typeof PL.createInstance==='function'){"
	. "window[" . wp_json_encode( $pl_var ) . "]=PL.createInstance(" . wp_json_encode( $instance_id ) . ","
	. wp_json_encode( [
		'tabs'          => array_values( $visible_tabs ),
		'maxToppings'   => (int) $max_toppings,
		'stepMode'      => (bool) $is_step,
		'requireSelect' => (bool) $pl_step_require,
		'showSummary'   => (bool) $pl_show_summary,
	] )
	. ");}})();";
wp_add_inline_script( 'pizzalayer-template-plainlist', $pl_init_js );
