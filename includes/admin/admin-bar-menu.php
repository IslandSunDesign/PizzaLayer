<?php
function create_pztp_menu() {
global $wp_admin_bar;
$menu_id = 'pztp';
$wp_admin_bar->add_menu(array('id' => $menu_id, 'title' => __('PizzaLayer'), 'href' => '/'));
$wp_admin_bar->add_menu(array('parent' => $menu_id, 'title' => __('View Pizzas'), 'id' => 'pztp-view-pizzas', 'href' => '/', 'meta' => array('target' => '_blank')));
$wp_admin_bar->add_menu(array('parent' => $menu_id, 'title' => __('Add a Topping'), 'id' => 'pztp-add-topping', 'href' => '/', 'meta' => array('target' => '_blank')));
$wp_admin_bar->add_menu(array('parent' => $menu_id, 'title' => __('Add a Cheese'), 'id' => 'pztp-add-cheese', 'href' => 'edit.php?post_status=draft&post_type=post'));
$wp_admin_bar->add_menu(array('parent' => $menu_id, 'title' => __('Add a Sauce'), 'id' => 'pztp-add-sauce', 'href' => '/', 'meta' => array('target' => '_blank')));
$wp_admin_bar->add_menu(array('parent' => $menu_id, 'title' => __('Add a Crust'), 'id' => 'pztp-add-crust', 'href' => '/'));
$wp_admin_bar->add_menu(array('parent' => $menu_id, 'title' => __('Settings'), 'id' => 'pztp-settings', 'href' => '/'));
}
add_action('admin_bar_menu', 'create_pztp_menu', 2000);
?>