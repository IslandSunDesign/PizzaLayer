<?php
do_action( 'pizzalayer_file_admin-bar-menu_before' );

/**
 * +=== Add PizzaLayer menu to the WordPress admin bar ===+
 */
function create_pztp_menu() {
	global $wp_admin_bar;

	$menu_id = 'pztp';

	/* +-- Main PizzaLayer Menu --+ */
	$wp_admin_bar->add_menu(array(
		'id'    => $menu_id,
		'title' => __('PizzaLayer'),
		'href'  => admin_url('edit.php?post_type=product') // Main WooCommerce product list
	));

	/* +-- View Pizza Products --+ */
	$wp_admin_bar->add_menu(array(
		'parent' => $menu_id,
		'title'  => __('View Pizzas'),
		'id'     => 'pztp-view-pizzas',
		'href'   => admin_url('edit.php?post_type=product'),
		'meta'   => array('target' => '_blank')
	));

	/* +-- Separator --+ */
	$wp_admin_bar->add_menu(array(
		'parent' => $menu_id,
		'id'     => 'pztp-separator-1',
		'title'  => '<span style="display:block;border-top:1px solid #ccc;margin:3px 0;"></span>',
		'meta'   => array('class' => 'pztp-separator')
	));

	/* +-- Topping (CPT: pizzalayer_toppings) --+ */
	$wp_admin_bar->add_menu(array(
		'parent' => $menu_id,
		'title'  => __('Toppings'),
		'id'     => 'pztp-toppings',
		'href'   => admin_url('edit.php?post_type=pizzalayer_toppings'),
		'meta'   => array('target' => '_blank')
	));
	$wp_admin_bar->add_menu(array(
		'parent' => 'pztp-toppings',
		'title'  => __('+ Add New Topping'),
		'id'     => 'pztp-add-new-topping',
		'href'   => admin_url('post-new.php?post_type=pizzalayer_toppings'),
	));

	/* +-- Cheese (CPT: pizzalayer_cheeses) --+ */
	$wp_admin_bar->add_menu(array(
		'parent' => $menu_id,
		'title'  => __('Cheeses'),
		'id'     => 'pztp-cheeses',
		'href'   => admin_url('edit.php?post_type=pizzalayer_cheeses'),
		'meta'   => array('target' => '_blank')
	));
	$wp_admin_bar->add_menu(array(
		'parent' => 'pztp-cheeses',
		'title'  => __('+ Add New Cheese'),
		'id'     => 'pztp-add-new-cheese',
		'href'   => admin_url('post-new.php?post_type=pizzalayer_cheeses'),
	));

	/* +-- Sauce (CPT: pizzalayer_sauces) --+ */
	$wp_admin_bar->add_menu(array(
		'parent' => $menu_id,
		'title'  => __('Sauces'),
		'id'     => 'pztp-sauces',
		'href'   => admin_url('edit.php?post_type=pizzalayer_sauces'),
		'meta'   => array('target' => '_blank')
	));
	$wp_admin_bar->add_menu(array(
		'parent' => 'pztp-sauces',
		'title'  => __('+ Add New Sauce'),
		'id'     => 'pztp-add-new-sauce',
		'href'   => admin_url('post-new.php?post_type=pizzalayer_sauces'),
	));

	/* +-- Crust (CPT: pizzalayer_crusts) --+ */
	$wp_admin_bar->add_menu(array(
		'parent' => $menu_id,
		'title'  => __('Crusts'),
		'id'     => 'pztp-crusts',
		'href'   => admin_url('edit.php?post_type=pizzalayer_crusts'),
		'meta'   => array('target' => '_blank')
	));
	$wp_admin_bar->add_menu(array(
		'parent' => 'pztp-crusts',
		'title'  => __('+ Add New Crust'),
		'id'     => 'pztp-add-new-crust',
		'href'   => admin_url('post-new.php?post_type=pizzalayer_crusts'),
	));

	/* +-- PizzaLayer Settings Page --+ */
	$wp_admin_bar->add_menu(array(
		'parent' => $menu_id,
		'title'  => __('Settings'),
		'id'     => 'pztp-settings',
		'href'   => admin_url('admin.php?page=pizzalayer_settings'),
	));
}
add_action('admin_bar_menu', 'create_pztp_menu', 2000);

do_action( 'pizzalayer_file_admin-bar-menu_after' );
?>
