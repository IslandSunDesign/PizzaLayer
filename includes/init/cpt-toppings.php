<?php
// Register Custom Post Type
function pizzalayer_toppings_custom_post_type() {

	$labels = array(
		'name'                  => _x( 'toppings', 'Post Type General Name', 'text_domain' ),
		'singular_name'         => _x( 'topping', 'Post Type Singular Name', 'text_domain' ),
		'menu_name'             => __( 'Toppings', 'text_domain' ),
		'name_admin_bar'        => __( 'Topping', 'text_domain' ),
		'archives'              => __( 'Toppings List', 'text_domain' ),
		'attributes'            => __( 'Toppings Attributes', 'text_domain' ),
		'parent_item_colon'     => __( 'Parent Topping:', 'text_domain' ),
		'all_items'             => __( 'All Toppings', 'text_domain' ),
		'add_new_item'          => __( 'New Topping', 'text_domain' ),
		'add_new'               => __( 'Add New', 'text_domain' ),
		'new_item'              => __( 'New Topping', 'text_domain' ),
		'edit_item'             => __( 'Edit Topping', 'text_domain' ),
		'update_item'           => __( 'Update Topping', 'text_domain' ),
		'view_item'             => __( 'View Topping', 'text_domain' ),
		'view_items'            => __( 'View Toppings', 'text_domain' ),
		'search_items'          => __( 'Search Topping', 'text_domain' ),
		'not_found'             => __( 'Not found', 'text_domain' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
		'featured_image'        => __( 'Topping Image', 'text_domain' ),
		'set_featured_image'    => __( 'Set topping image', 'text_domain' ),
		'remove_featured_image' => __( 'Remove topping image', 'text_domain' ),
		'use_featured_image'    => __( 'Use as topping image', 'text_domain' ),
		'insert_into_item'      => __( 'Insert into Topping', 'text_domain' ),
		'uploaded_to_this_item' => __( 'Uploaded to this Topping', 'text_domain' ),
		'items_list'            => __( 'Toppings list', 'text_domain' ),
		'items_list_navigation' => __( 'Toppings list navigation', 'text_domain' ),
		'filter_items_list'     => __( 'Filter Toppings List', 'text_domain' ),
	);
	$args = array(
		'label'                 => __( 'topping', 'text_domain' ),
		'description'           => __( 'These are the global toppings that are available for selection when building your pizza products.', 'text_domain' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor' ),
		'taxonomies'            => array( 'category', 'post_tag' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => 'pizzalayer',
		'menu_position'         => 35,
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'page',
	);
	register_post_type( 'pizzalayer_toppings', $args );

}
add_action( 'init', 'pizzalayer_toppings_custom_post_type', 0 );