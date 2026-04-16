<?php
/**
 * PizzaLayerPro — Checkout Bar: Scaffold
 * Token-inheriting skeleton — picks up --sc-accent, --sc-bg, --sc-text, --sc-border.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! isset( $instance_id ) ) { $instance_id = ''; }
$show_qty   = class_exists('PizzaLayerPro\Pro\WooCommerce\CartIntegration') && (bool) pztpro_get_setting('show_quantity_selector', true);
$max_qty    = max(1, (int) pztpro_get_setting('max_quantity', 99));
$show_notes = (bool) pztpro_get_setting('enable_order_notes', false);
$note_ph    = pztpro_get_setting('order_note_placeholder', '') ?: __('Any special requests?', 'pizzalayerpro');
?>
<div class="pztpro-checkout-bar pztpro-checkout-bar--scaffold sc-root"
     id="pztpro-checkout-bar-<?php echo esc_attr($instance_id); ?>"
     data-instance="<?php echo esc_attr($instance_id); ?>">

    <div class="pztpro-bar-row">
        <div class="pztpro-bar-row__summary">
            <span class="pztpro-bar-row__size-label" id="pztpro-bar-size-<?php echo esc_attr($instance_id); ?>"></span>
            <span class="pztpro-bar-row__price" id="pztpro-bar-price-<?php echo esc_attr($instance_id); ?>">—</span>
        </div>

        <?php if ($show_qty) : ?>
        <div class="pztpro-bar-qty" data-instance="<?php echo esc_attr($instance_id); ?>" data-max="<?php echo esc_attr($max_qty); ?>">
            <button type="button" class="pztpro-qty-btn pztpro-qty-btn--minus" data-instance="<?php echo esc_attr($instance_id); ?>" disabled aria-label="<?php esc_attr_e('Decrease quantity','pizzalayerpro'); ?>">−</button>
            <span class="pztpro-qty-value" id="pztpro-qty-<?php echo esc_attr($instance_id); ?>" data-qty="1">1</span>
            <button type="button" class="pztpro-qty-btn pztpro-qty-btn--plus"  data-instance="<?php echo esc_attr($instance_id); ?>" aria-label="<?php esc_attr_e('Increase quantity','pizzalayerpro'); ?>">+</button>
        </div>
        <?php endif; ?>

        <button type="button"
                class="pztpro-bar-row__btn pztpro-add-to-cart-btn"
                id="pztpro-checkout-btn-<?php echo esc_attr($instance_id); ?>"
                data-instance="<?php echo esc_attr($instance_id); ?>"
                aria-live="polite">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
            <?php esc_html_e('Add to Cart', 'pizzalayerpro'); ?>
        </button>
    </div>

    <?php if ($show_notes) : ?>
    <div class="pztpro-bar-notes" style="margin-top:10px;">
        <textarea class="pztpro-bar-notes__input pztpro-order-note-input"
                  data-instance="<?php echo esc_attr($instance_id); ?>"
                  rows="2" maxlength="500"
                  placeholder="<?php echo esc_attr($note_ph); ?>"></textarea>
    </div>
    <?php endif; ?>
</div>
