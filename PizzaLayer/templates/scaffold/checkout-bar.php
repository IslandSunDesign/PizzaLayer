<?php
/**
 * PizzaLayerPro — Checkout Bar
 *
 * Included by PizzaLayerPro's CartIntegration when a pizza product page is loaded.
 * Lives inside the template folder so it can be customised per-template.
 *
 * Available variables (provided by CartIntegration::render_cart_button()):
 *   $instance_id  (string) — the builder instance ID
 *
 * Styling is driven by assets/css/frontend.css in PizzaLayerPro.
 * Override by copying this file to your child theme under:
 *   your-theme/pizzalayerpro/checkout-bar.php
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! isset( $instance_id ) ) { $instance_id = ''; }
?>
<div class="pztpro-checkout-bar" id="pztpro-checkout-bar-<?php echo esc_attr( $instance_id ); ?>" data-instance="<?php echo esc_attr( $instance_id ); ?>" role="region" aria-label="<?php esc_attr_e( 'Pizza order summary', 'pizzalayerpro' ); ?>">

	<div class="pztpro-checkout-bar__summary">
		<span class="pztpro-checkout-bar__size-label" id="pztpro-bar-size-<?php echo esc_attr( $instance_id ); ?>"></span>
		<span class="pztpro-checkout-bar__price" id="pztpro-bar-price-<?php echo esc_attr( $instance_id ); ?>">
			<span class="pztpro-checkout-bar__currency"></span><span class="pztpro-checkout-bar__amount">0.00</span>
		</span>
	</div>

	<button
		type="button"
		class="pztpro-checkout-bar__btn pztpro-add-to-cart-btn"
		id="pztpro-checkout-btn-<?php echo esc_attr( $instance_id ); ?>"
		data-instance="<?php echo esc_attr( $instance_id ); ?>"
		aria-live="polite"
	>
		<svg class="pztpro-checkout-bar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
			<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
			<path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
		</svg>
		<span class="pztpro-checkout-bar__btn-text"><?php esc_html_e( 'Add to Cart', 'pizzalayerpro' ); ?></span>
	</button>

</div><!-- .pztpro-checkout-bar -->
