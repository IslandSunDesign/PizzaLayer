<?php
/**
 * PriceGrid — metabox for topping/sauce pricing data (base layer, no WC).
 * The WC read-only metabox lives in PizzaLayerPro.
 *
 * @package PizzaLayer\Admin
 */

namespace PizzaLayer\Admin;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class PriceGrid {

	private array $supported_cpts = [
		'pizzalayer_toppings',
		'pizzalayer_sauces',
		'pizzalayer_cheeses',
		'pizzalayer_drizzles',
		'pizzalayer_crusts',
	];

	public function register_metabox(): void {
		foreach ( $this->supported_cpts as $cpt ) {
			add_meta_box(
				'pizzalayer_price_grid',
				__( 'Pricing Grid (CSV)', 'pizzalayer' ),
				[ $this, 'render_metabox' ],
				$cpt,
				'normal',
				'default'
			);
		}

		/**
		 * Fires after PizzaLayer price grid metaboxes are registered.
		 *
		 * @since 1.0.0
		 */
		do_action( 'pizzalayer_price_grid_metabox_registered' );
	}

	public function render_metabox( \WP_Post $post ): void {
		wp_nonce_field( 'pizzalayer_price_grid_save', 'pizzalayer_price_grid_nonce' );

		$type     = str_replace( 'pizzalayer_', '', $post->post_type );
		$type     = rtrim( $type, 's' ); // 'toppings' → 'topping'
		$csv_key  = $type . '_cost_csv';
		$csv_val  = get_post_meta( $post->ID, $csv_key, true );
		?>
		<p style="color:#555;font-size:13px;">
			<?php esc_html_e( 'Enter pricing as CSV. First row is the header. Each following row: Size, Full, Half, Quarter.', 'pizzalayer' ); ?>
		</p>
		<p><code>Size,Full,Half,Quarter<br>"small",.99,.55,.44<br>"medium",1.29,.69,.44</code></p>
		<textarea name="<?php echo esc_attr( $csv_key ); ?>" style="width:100%;height:120px;font-family:monospace;"><?php echo esc_textarea( $csv_val ); ?></textarea>
		<p style="color:#888;font-size:12px;"><?php esc_html_e( 'Leave blank if this item has no size-based pricing.', 'pizzalayer' ); ?></p>
		<?php

		/**
		 * Fires inside the price grid metabox (for Pro to add WC view).
		 *
		 * @since 1.0.0
		 * @param \WP_Post $post
		 */
		do_action( 'pizzalayer_price_grid_metabox_inner', $post );
	}

	public function save( int $post_id, \WP_Post $post ): void {
		if (
			! isset( $_POST['pizzalayer_price_grid_nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pizzalayer_price_grid_nonce'] ) ), 'pizzalayer_price_grid_save' )
		) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
		if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }
		if ( ! in_array( $post->post_type, $this->supported_cpts, true ) ) { return; }

		$type    = rtrim( str_replace( 'pizzalayer_', '', $post->post_type ), 's' );
		$csv_key = $type . '_cost_csv';

		if ( isset( $_POST[ $csv_key ] ) ) {
			update_post_meta( $post_id, $csv_key, sanitize_textarea_field( wp_unslash( $_POST[ $csv_key ] ) ) );
		}

		/**
		 * Fires after price grid meta is saved.
		 *
		 * @since 1.0.0
		 * @param int      $post_id
		 * @param \WP_Post $post
		 */
		do_action( 'pizzalayer_price_grid_saved', $post_id, $post );
	}
}
