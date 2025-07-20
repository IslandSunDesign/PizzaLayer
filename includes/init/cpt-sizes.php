<?php
// Register Custom Post Type
function pizzalayer_sizes_custom_post_type() {

	$labels = array(
		'name'                  => _x( 'sizes', 'Post Type General Name', 'text_domain' ),
		'singular_name'         => _x( 'size', 'Post Type Singular Name', 'text_domain' ),
		'menu_name'             => __( 'Sizes', 'text_domain' ),
		'name_admin_bar'        => __( 'Size', 'text_domain' ),
		'archives'              => __( 'Sizes List', 'text_domain' ),
		'attributes'            => __( 'Sizes Attributes', 'text_domain' ),
		'parent_item_colon'     => __( 'Parent Size:', 'text_domain' ),
		'all_items'             => __( 'All Sizes', 'text_domain' ),
		'add_new_item'          => __( 'New Size', 'text_domain' ),
		'add_new'               => __( 'Add New', 'text_domain' ),
		'new_item'              => __( 'New Size', 'text_domain' ),
		'edit_item'             => __( 'Edit Size', 'text_domain' ),
		'update_item'           => __( 'Update Size', 'text_domain' ),
		'view_item'             => __( 'View Size', 'text_domain' ),
		'view_items'            => __( 'View Sizes', 'text_domain' ),
		'search_items'          => __( 'Search Size', 'text_domain' ),
		'not_found'             => __( 'Not found', 'text_domain' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
		'featured_image'        => __( 'Size Image', 'text_domain' ),
		'set_featured_image'    => __( 'Set size image', 'text_domain' ),
		'remove_featured_image' => __( 'Remove size image', 'text_domain' ),
		'use_featured_image'    => __( 'Use as size image', 'text_domain' ),
		'insert_into_item'      => __( 'Insert into Size', 'text_domain' ),
		'uploaded_to_this_item' => __( 'Uploaded to this Size', 'text_domain' ),
		'items_list'            => __( 'Sizes list', 'text_domain' ),
		'items_list_navigation' => __( 'Sizes list navigation', 'text_domain' ),
		'filter_items_list'     => __( 'Filter Sizes List', 'text_domain' ),
	);
	$args = array(
		'label'                 => __( 'size', 'text_domain' ),
		'description'           => __( 'These are the global sizes that are available for selection when building your pizza products.', 'text_domain' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor' ),
		'taxonomies'            => array( 'category', 'post_tag' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => 'pizzalayer',
		'menu_position'         => 38,
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'page',
	);
	register_post_type( 'pizzalayer_sizes', $args );

}
add_action( 'init', 'pizzalayer_sizes_custom_post_type', 0 );