<?php
// Register Custom Post Type
function pizzalayer_crusts_custom_post_type() {

	$labels = array(
		'name'                  => _x( 'crusts', 'Post Type General Name', 'text_domain' ),
		'singular_name'         => _x( 'crust', 'Post Type Singular Name', 'text_domain' ),
		'menu_name'             => __( 'Crusts', 'text_domain' ),
		'name_admin_bar'        => __( 'Crust', 'text_domain' ),
		'archives'              => __( 'Crusts List', 'text_domain' ),
		'attributes'            => __( 'Crusts Attributes', 'text_domain' ),
		'parent_item_colon'     => __( 'Parent Crust:', 'text_domain' ),
		'all_items'             => __( 'All Crusts', 'text_domain' ),
		'add_new_item'          => __( 'New Crust', 'text_domain' ),
		'add_new'               => __( 'Add New', 'text_domain' ),
		'new_item'              => __( 'New Crust', 'text_domain' ),
		'edit_item'             => __( 'Edit Crust', 'text_domain' ),
		'update_item'           => __( 'Update Crust', 'text_domain' ),
		'view_item'             => __( 'View Crust', 'text_domain' ),
		'view_items'            => __( 'View Crusts', 'text_domain' ),
		'search_items'          => __( 'Search Crust', 'text_domain' ),
		'not_found'             => __( 'Not found', 'text_domain' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
		'featured_image'        => __( 'Crust Image', 'text_domain' ),
		'set_featured_image'    => __( 'Set crust image', 'text_domain' ),
		'remove_featured_image' => __( 'Remove crust image', 'text_domain' ),
		'use_featured_image'    => __( 'Use as crust image', 'text_domain' ),
		'insert_into_item'      => __( 'Insert into Crust', 'text_domain' ),
		'uploaded_to_this_item' => __( 'Uploaded to this Crust', 'text_domain' ),
		'items_list'            => __( 'Crusts list', 'text_domain' ),
		'items_list_navigation' => __( 'Crusts list navigation', 'text_domain' ),
		'filter_items_list'     => __( 'Filter Crusts List', 'text_domain' ),
	);
	$args = array(
		'label'                 => __( 'crust', 'text_domain' ),
		'description'           => __( 'These are the global crusts that are available for selection when building your pizza products.', 'text_domain' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor' ),
		'taxonomies'            => array( 'category', 'post_tag' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => 'pizzalayer',
		'menu_position'         => 25,
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'page',
	);
	register_post_type( 'pizzalayer_crusts', $args );

}
add_action( 'init', 'pizzalayer_crusts_custom_post_type', 0 );