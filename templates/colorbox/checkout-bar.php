<?php
/**
 * PizzaLayerPro — Checkout Bar (Colorbox template)
 *
 * Only rendered when PizzaLayerPro is active.
 * Template tokens: --cb-accent (#ff4d4d), --cb-surface, --cb-radius
 *
 * Variables provided by CartIntegration::render_cart_button():
 *   $instance_id  (string)
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! class_exists( 'PizzaLayerPro\\Pro\\Plugin' ) ) { return; }
if ( ! isset( $instance_id ) ) { $instance_id = ''; }

$show_qty          = (bool) pztpro_get_setting( 'show_quantity_selector', true );
$show_notes        = (bool) pztpro_get_setting( 'enable_order_notes', false );
$notes_label       = (string) pztpro_get_setting( 'order_note_label', '' );
$notes_placeholder = (string) pztpro_get_setting( 'order_note_placeholder', '' );
if ( '' === $notes_label )       { $notes_label       = __( 'Special instructions', 'pizzalayerpro' ); }
if ( '' === $notes_placeholder ) { $notes_placeholder = __( 'e.g. extra crispy, no garlic…', 'pizzalayerpro' ); }
?>
<div
	class="pztpro-checkout-bar pztpro-checkout-bar--colorbox"
	id="pztpro-checkout-bar-<?php echo esc_attr( $instance_id ); ?>"
	data-instance="<?php echo esc_attr( $instance_id ); ?>"
	role="region"
	aria-label="<?php esc_attr_e( 'Pizza order summary', 'pizzalayerpro' ); ?>"
>

	<?php if ( $show_notes ) : ?>
	<div class="pztpro-bar-notes">
		<label class="pztpro-bar-notes__label" for="pztpro-bar-notes-<?php echo esc_attr( $instance_id ); ?>">
			<svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true" focusable="false"><path d="M2 3h12M2 7h8M2 11h6"/></svg>
			<?php echo esc_html( $notes_label ); ?>
		</label>
		<textarea
			id="pztpro-bar-notes-<?php echo esc_attr( $instance_id ); ?>"
			class="pztpro-bar-notes__input pztpro-order-note-input"
			rows="2"
			maxlength="500"
			placeholder="<?php echo esc_attr( $notes_placeholder ); ?>"
			data-instance="<?php echo esc_attr( $instance_id ); ?>"
		></textarea>
	</div>
	<?php endif; ?>

	<div class="pztpro-bar-row">

		<div class="pztpro-bar-row__summary">
			<span class="pztpro-bar-row__size-label" id="pztpro-bar-size-<?php echo esc_attr( $instance_id ); ?>"></span>
			<span class="pztpro-bar-row__price" id="pztpro-bar-price-<?php echo esc_attr( $instance_id ); ?>">
				<span class="pztpro-checkout-bar__currency"></span><span class="pztpro-checkout-bar__amount">—</span>
			</span>
		</div>

		<?php if ( $show_qty ) : ?>
		<div class="pztpro-bar-qty" role="group" aria-label="<?php esc_attr_e( 'Quantity', 'pizzalayerpro' ); ?>">
			<button type="button" class="pztpro-qty-btn pztpro-qty-btn--minus" data-instance="<?php echo esc_attr( $instance_id ); ?>" aria-label="<?php esc_attr_e( 'Decrease quantity', 'pizzalayerpro' ); ?>">−</button>
			<span class="pztpro-qty-value" id="pztpro-qty-<?php echo esc_attr( $instance_id ); ?>" aria-live="polite">1</span>
			<button type="button" class="pztpro-qty-btn pztpro-qty-btn--plus" data-instance="<?php echo esc_attr( $instance_id ); ?>" aria-label="<?php esc_attr_e( 'Increase quantity', 'pizzalayerpro' ); ?>">+</button>
		</div>
		<?php endif; ?>

		<button
			type="button"
			class="pztpro-bar-row__btn pztpro-add-to-cart-btn"
			id="pztpro-checkout-btn-<?php echo esc_attr( $instance_id ); ?>"
			data-instance="<?php echo esc_attr( $instance_id ); ?>"
			aria-live="polite"
		>
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
				<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
				<path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
			</svg>
			<span><?php esc_html_e( 'Add to Cart', 'pizzalayerpro' ); ?></span>
		</button>

	</div><!-- .pztpro-bar-row -->

</div><!-- .pztpro-checkout-bar--colorbox -->
