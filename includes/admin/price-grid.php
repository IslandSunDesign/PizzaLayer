<?php
// +------------------------------------------------------------+
// | Pizzalayer Price Grid Admin Interface                      |
// | File: includes/admin/price-grid.php                        |
// +------------------------------------------------------------+

// +------------------------------------------------------------+
// | Register Price Grid Metabox for Toppings & Sauces CPTs     |
// +------------------------------------------------------------+
add_action( 'add_meta_boxes', 'pizzalayer_add_topping_price_grid_metabox' );
function pizzalayer_add_topping_price_grid_metabox() {
	$post_types = ['pizzalayer_toppings', 'pizzalayer_sauces'];
	foreach ( $post_types as $cpt ) {
		add_meta_box(
			'pizzalayer_topping_price_grid',
			__( 'Active Pricing Grid', 'pizzalayer' ),
			'pizzalayer_render_price_grid_metabox',
			$cpt,
			'normal',
			'default'
		);
	}
}

// +------------------------------------------------------------+
// | Render Editable Price Grid Table in Toppings UI            |
// +------------------------------------------------------------+
function pizzalayer_render_price_grid_metabox( $post ) {
	wp_nonce_field( 'pizzalayer_grid_save_' . $post->ID, 'pizzalayer_grid_nonce' );

	// Load existing pricing from ACF
	$rows = get_field( 'topping_cost', $post->ID );
	$pricing = [];
	if ( is_array($rows) ) {
		foreach ( $rows as $row ) {
			if ( !empty($row['size']) ) {
				$slug = sanitize_title($row['size']);
				$pricing[$slug] = [
					'label'   => $row['size'],
					'full'    => $row['full'] ?? '',
					'half'    => $row['half'] ?? '',
					'quarter' => $row['quarter'] ?? ''
				];
			}
		}
	}

	$sizes = get_posts([
		'post_type'      => 'pizzalayer_sizes',
		'posts_per_page' => -1,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	]);
	$portions = ['full', 'half', 'quarter'];

	echo '<style>.pl-grid-input{width:70px;text-align:right}.pl-missing{background:#fff6bf}</style>';

	// Copy dropdown and reset button
	echo '<p><label for="pizzalayer_copy_from">Copy pricing from: </label>';
	echo '<select id="pizzalayer_copy_from" name="pizzalayer_copy_from">';
	echo '<option value="">-- Select Topping --</option>';
	$others = get_posts([
		'post_type'      => $post->post_type,
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'post__not_in'   => [$post->ID],
		'orderby'        => 'title',
		'order'          => 'ASC',
	]);
	foreach ( $others as $other ) {
		echo '<option value="' . esc_attr( $other->ID ) . '">' . esc_html( $other->post_title ) . '</option>';
	}
	echo '</select></p>';
	echo '<p><input type="submit" name="pizzalayer_reset_grid" class="button" value="Reset Pricing Grid" onclick="return confirm(\'Are you sure you want to reset all pricing values?\');"></p>';

	// Render editable grid table
	echo '<table class="widefat striped" style="max-width:600px"><thead><tr><th>Size</th>';
	foreach ( $portions as $portion ) echo '<th>' . ucfirst( $portion ) . '</th>';
	echo '</tr></thead><tbody>';
	foreach ( $sizes as $size ) {
		$slug = sanitize_title( $size->post_title );
		$row = $pricing[$slug] ?? ['label' => $size->post_title, 'full'=>'', 'half'=>'', 'quarter'=>''];
		echo '<tr><td><strong>' . esc_html( $size->post_title ) . '</strong></td>';
		foreach ( $portions as $portion ) {
			$value = $row[$portion] ?? '';
			$class = $value === '' ? 'pl-missing' : '';
			echo '<td><input type="number" step="0.01" class="pl-grid-input ' . $class . '" name="pizzalayer_grid[' . esc_attr( $slug ) . '][' . esc_attr( $portion ) . ']" value="' . esc_attr( $value ) . '"></td>';
		}
		// Include actual label in hidden field
		echo '<input type="hidden" name="pizzalayer_grid[' . esc_attr( $slug ) . '][size_label]" value="' . esc_attr( $size->post_title ) . '">';
		echo '</tr>';
	}
	echo '</tbody></table>';
	echo '<p class="description">Enter price per portion per size. Missing values are highlighted.</p>';
}

// +------------------------------------------------------------+
// | Save Pricing Grid to ACF (Runs on Post Save)               |
// +------------------------------------------------------------+
add_action( 'save_post', 'pizzalayer_save_topping_grid' );
function pizzalayer_save_topping_grid( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! isset( $_POST['pizzalayer_grid_nonce'] ) || ! wp_verify_nonce( $_POST['pizzalayer_grid_nonce'], 'pizzalayer_grid_save_' . $post_id ) ) return;

	if ( isset( $_POST['pizzalayer_reset_grid'] ) ) {
		update_field( 'topping_cost', [], $post_id );
		return;
	}

	if ( ! empty( $_POST['pizzalayer_copy_from'] ) ) {
		$source_id = intval( $_POST['pizzalayer_copy_from'] );
		$source_grid = get_field( 'topping_cost', $source_id );
		if ( is_array( $source_grid ) ) {
			update_field( 'topping_cost', $source_grid, $post_id );
			return;
		}
	}

	if ( isset( $_POST['pizzalayer_grid'] ) && is_array( $_POST['pizzalayer_grid'] ) ) {
		$submitted = $_POST['pizzalayer_grid'];
		$acf_rows = [];

		foreach ( $submitted as $slug => $data ) {
			if ( empty( $data['size_label'] ) ) continue;
			$row = [
				'size'    => sanitize_text_field( $data['size_label'] ),
				'full'    => isset($data['full']) && is_numeric($data['full']) ? floatval($data['full']) : null,
				'half'    => isset($data['half']) && is_numeric($data['half']) ? floatval($data['half']) : null,
				'quarter' => isset($data['quarter']) && is_numeric($data['quarter']) ? floatval($data['quarter']) : null
			];
			$acf_rows[] = $row;
		}
		update_field( 'topping_cost', $acf_rows, $post_id );
	}
}

// +------------------------------------------------------------+
// | WooCommerce Read-Only Price Grid for Pizza Products        |
// +------------------------------------------------------------+
add_action( 'add_meta_boxes', 'pizzalayer_add_wc_grid_view_metabox' );
function pizzalayer_add_wc_grid_view_metabox() {
	add_meta_box(
		'pztpro_price_grid_view',
		__( 'Price Grid', 'pztpro' ),
		'pizzalayer_render_locked_price_grid_for_wc',
		'product',
		'normal',
		'low'
	);
}

function pizzalayer_render_locked_price_grid_for_wc( $post ) {
	if ( get_post_type( $post ) !== 'product' ) return;
	$product_type = function_exists( 'wc_get_product' ) ? wc_get_product( $post )->get_type() : '';
	if ( $product_type !== 'pizza' ) return;

	$rows = get_field( 'topping_cost', $post->ID );
	if ( ! is_array( $rows ) ) {
		echo '<p>No pricing grid found for this product.</p>';
		return;
	}

	$pricing = [];
	foreach ( $rows as $row ) {
		$slug = sanitize_title( $row['size'] ?? '' );
		if ( $slug ) {
			$pricing[$slug] = [
				'label'   => $row['size'],
				'full'    => $row['full'] ?? '',
				'half'    => $row['half'] ?? '',
				'quarter' => $row['quarter'] ?? ''
			];
		}
	}

	$sizes = get_posts([
		'post_type'      => 'pizzalayer_sizes',
		'posts_per_page' => -1,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	]);
	$portions = ['full', 'half', 'quarter'];
	echo '<table class="widefat striped" style="max-width:600px"><thead><tr><th>Size</th>';
	foreach ( $portions as $portion ) echo '<th>' . ucfirst( $portion ) . '</th>';
	echo '</tr></thead><tbody>';
	foreach ( $sizes as $size ) {
		$slug = sanitize_title( $size->post_title );
		$row = $pricing[$slug] ?? [];
		echo '<tr><td>' . esc_html( $size->post_title ) . '</td>';
		foreach ( $portions as $portion ) {
			echo '<td>' . ( isset($row[$portion]) ? wc_price( $row[$portion] ) : '-' ) . '</td>';
		}
		echo '</tr>';
	}
	echo '</tbody></table>';
}

// +------------------------------------------------------------+
// | Export Pricing Grid to CSV on Save                         |
// +------------------------------------------------------------+
add_action( 'save_post', 'pizzalayer_export_grid_to_csv_on_save', 20, 1 );
function pizzalayer_export_grid_to_csv_on_save( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

	$post_type = get_post_type( $post_id );
	if ( ! in_array( $post_type, ['pizzalayer_toppings', 'pizzalayer_sauces'], true ) ) return;

	$grid = get_field( 'topping_cost', $post_id );
	if ( ! is_array( $grid ) || empty( $grid ) ) return;

	$csv_lines = ['Size,Full,Half,Quarter'];
	foreach ( $grid as $row ) {
		$csv_lines[] = sprintf(
			'"%s",%s,%s,%s',
			esc_attr( $row['size'] ?? '' ),
			is_numeric( $row['full'] ) ? $row['full'] : '',
			is_numeric( $row['half'] ) ? $row['half'] : '',
			is_numeric( $row['quarter'] ) ? $row['quarter'] : ''
		);
	}

	$csv_string = implode( "\n", $csv_lines );
	$meta_key = ( $post_type === 'pizzalayer_toppings' ) ? 'topping_cost_csv' : 'sauce_cost_csv';
	update_post_meta( $post_id, $meta_key, $csv_string );
}

// +------------------------------------------------------------+
// | CSV Download Button + Handler in Admin                     |
// +------------------------------------------------------------+
add_action( 'add_meta_boxes', 'pizzalayer_add_download_csv_button_metabox' );
function pizzalayer_add_download_csv_button_metabox() {
	$post_types = ['pizzalayer_toppings', 'pizzalayer_sauces'];
	foreach ( $post_types as $cpt ) {
		add_meta_box(
			'pizzalayer_download_csv_button',
			__( 'Export Pricing Grid', 'pizzalayer' ),
			'pizzalayer_render_download_csv_button',
			$cpt,
			'side',
			'low'
		);
	}
}

function pizzalayer_render_download_csv_button( $post ) {
	$nonce = wp_create_nonce( 'pizzalayer_download_csv_' . $post->ID );
	$url = admin_url( 'admin-post.php?action=pizzalayer_download_csv&post_id=' . $post->ID . '&nonce=' . $nonce );

	echo '<a href="' . esc_url( $url ) . '" class="button button-primary" style="width:100%;text-align:center;">';
	echo __( 'Download Pricing Grid CSV', 'pizzalayer' );
	echo '</a>';
}

add_action( 'admin_post_pizzalayer_download_csv', 'pizzalayer_handle_csv_download' );
function pizzalayer_handle_csv_download() {
	$post_id = isset( $_GET['post_id'] ) ? intval( $_GET['post_id'] ) : 0;
	$nonce   = isset( $_GET['nonce'] ) ? sanitize_text_field( $_GET['nonce'] ) : '';

	if ( ! $post_id || ! wp_verify_nonce( $nonce, 'pizzalayer_download_csv_' . $post_id ) ) {
		wp_die( __( 'Invalid request.', 'pizzalayer' ) );
	}

	$post_type = get_post_type( $post_id );
	if ( ! in_array( $post_type, ['pizzalayer_toppings', 'pizzalayer_sauces'], true ) ) {
		wp_die( __( 'Invalid post type.', 'pizzalayer' ) );
	}

	$grid = get_field( 'topping_cost', $post_id );
	if ( ! is_array( $grid ) || empty( $grid ) ) {
		wp_die( __( 'No pricing grid found for export.', 'pizzalayer' ) );
	}

	$csv_lines = ['Size,Full,Half,Quarter'];
	foreach ( $grid as $row ) {
		$csv_lines[] = sprintf(
			'"%s",%s,%s,%s',
			esc_attr( $row['size'] ?? '' ),
			is_numeric( $row['full'] ) ? $row['full'] : '',
			is_numeric( $row['half'] ) ? $row['half'] : '',
			is_numeric( $row['quarter'] ) ? $row['quarter'] : ''
		);
	}

	$csv_output = implode( "\n", $csv_lines );
	$filename = sanitize_title( get_the_title( $post_id ) ) . '-pricing-grid.csv';

	header( 'Content-Type: text/csv' );
	header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
	header( 'Pragma: no-cache' );
	header( 'Expires: 0' );

	echo $csv_output;
	exit;
}
