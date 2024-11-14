<?php
// Register Custom Post Type
function pizzalayer_cheeses_custom_post_type() {

	$labels = array(
		'name'                  => _x( 'cheeses', 'Post Type General Name', 'text_domain' ),
		'singular_name'         => _x( 'cheese', 'Post Type Singular Name', 'text_domain' ),
		'menu_name'             => __( 'Cheeses', 'text_domain' ),
		'name_admin_bar'        => __( 'Cheese', 'text_domain' ),
		'archives'              => __( 'Cheeses List', 'text_domain' ),
		'attributes'            => __( 'Cheeses Attributes', 'text_domain' ),
		'parent_item_colon'     => __( 'Parent Cheese:', 'text_domain' ),
		'all_items'             => __( 'All Cheeses', 'text_domain' ),
		'add_new_item'          => __( 'New Cheese', 'text_domain' ),
		'add_new'               => __( 'Add New', 'text_domain' ),
		'new_item'              => __( 'New Cheese', 'text_domain' ),
		'edit_item'             => __( 'Edit Cheese', 'text_domain' ),
		'update_item'           => __( 'Update Cheese', 'text_domain' ),
		'view_item'             => __( 'View Cheese', 'text_domain' ),
		'view_items'            => __( 'View Cheeses', 'text_domain' ),
		'search_items'          => __( 'Search Cheese', 'text_domain' ),
		'not_found'             => __( 'Not found', 'text_domain' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
		'featured_image'        => __( 'Cheese Image', 'text_domain' ),
		'set_featured_image'    => __( 'Set cheese image', 'text_domain' ),
		'remove_featured_image' => __( 'Remove cheese image', 'text_domain' ),
		'use_featured_image'    => __( 'Use as cheese image', 'text_domain' ),
		'insert_into_item'      => __( 'Insert into Cheese', 'text_domain' ),
		'uploaded_to_this_item' => __( 'Uploaded to this Cheese', 'text_domain' ),
		'items_list'            => __( 'Cheeses list', 'text_domain' ),
		'items_list_navigation' => __( 'Cheeses list navigation', 'text_domain' ),
		'filter_items_list'     => __( 'Filter Cheeses List', 'text_domain' ),
	);
	$args = array(
		'label'                 => __( 'cheese', 'text_domain' ),
		'description'           => __( 'These are the global cheeses that are available for selection when building your pizza products.', 'text_domain' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor' ),
		'taxonomies'            => array( 'category', 'post_tag' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => 'pizzalayer',
		'menu_position'         => 30,
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'page',
	);
	register_post_type( 'pizzalayer_cheeses', $args );

}
add_action( 'init', 'pizzalayer_cheeses_custom_post_type', 0 );