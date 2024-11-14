<?php
// Register Custom Post Type
function pizzalayer_drizzles_custom_post_type() {

	$labels = array(
		'name'                  => _x( 'drizzles', 'Post Type General Name', 'text_domain' ),
		'singular_name'         => _x( 'drizzle', 'Post Type Singular Name', 'text_domain' ),
		'menu_name'             => __( 'Drizzles', 'text_domain' ),
		'name_admin_bar'        => __( 'Drizzle', 'text_domain' ),
		'archives'              => __( 'Drizzles List', 'text_domain' ),
		'attributes'            => __( 'Drizzles Attributes', 'text_domain' ),
		'parent_item_colon'     => __( 'Parent Drizzle:', 'text_domain' ),
		'all_items'             => __( 'All Drizzles', 'text_domain' ),
		'add_new_item'          => __( 'New Drizzle', 'text_domain' ),
		'add_new'               => __( 'Add New', 'text_domain' ),
		'new_item'              => __( 'New Drizzle', 'text_domain' ),
		'edit_item'             => __( 'Edit Drizzle', 'text_domain' ),
		'update_item'           => __( 'Update Drizzle', 'text_domain' ),
		'view_item'             => __( 'View Drizzle', 'text_domain' ),
		'view_items'            => __( 'View Drizzles', 'text_domain' ),
		'search_items'          => __( 'Search Drizzle', 'text_domain' ),
		'not_found'             => __( 'Not found', 'text_domain' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
		'featured_image'        => __( 'Drizzle Image', 'text_domain' ),
		'set_featured_image'    => __( 'Set drizzle image', 'text_domain' ),
		'remove_featured_image' => __( 'Remove drizzle image', 'text_domain' ),
		'use_featured_image'    => __( 'Use as drizzle image', 'text_domain' ),
		'insert_into_item'      => __( 'Insert into Drizzle', 'text_domain' ),
		'uploaded_to_this_item' => __( 'Uploaded to this Drizzle', 'text_domain' ),
		'items_list'            => __( 'Drizzles list', 'text_domain' ),
		'items_list_navigation' => __( 'Drizzles list navigation', 'text_domain' ),
		'filter_items_list'     => __( 'Filter Drizzles List', 'text_domain' ),
	);
	$args = array(
		'label'                 => __( 'drizzle', 'text_domain' ),
		'description'           => __( 'These are the global drizzles that are available for selection when building your pizza products.', 'text_domain' ),
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
	register_post_type( 'pizzalayer_drizzles', $args );

}
add_action( 'init', 'pizzalayer_drizzles_custom_post_type', 0 );