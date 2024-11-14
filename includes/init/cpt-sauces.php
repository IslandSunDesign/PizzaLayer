<?php
// Register Custom Post Type
function pizzalayer_sauces_custom_post_type() {

	$labels = array(
		'name'                  => _x( 'sauces', 'Post Type General Name', 'text_domain' ),
		'singular_name'         => _x( 'sauce', 'Post Type Singular Name', 'text_domain' ),
		'menu_name'             => __( 'Sauces', 'text_domain' ),
		'name_admin_bar'        => __( 'Sauce', 'text_domain' ),
		'archives'              => __( 'Sauces List', 'text_domain' ),
		'attributes'            => __( 'Sauces Attributes', 'text_domain' ),
		'parent_item_colon'     => __( 'Parent Sauce:', 'text_domain' ),
		'all_items'             => __( 'All Sauces', 'text_domain' ),
		'add_new_item'          => __( 'New Sauce', 'text_domain' ),
		'add_new'               => __( 'Add New', 'text_domain' ),
		'new_item'              => __( 'New Sauce', 'text_domain' ),
		'edit_item'             => __( 'Edit Sauce', 'text_domain' ),
		'update_item'           => __( 'Update Sauce', 'text_domain' ),
		'view_item'             => __( 'View Sauce', 'text_domain' ),
		'view_items'            => __( 'View Sauces', 'text_domain' ),
		'search_items'          => __( 'Search Sauce', 'text_domain' ),
		'not_found'             => __( 'Not found', 'text_domain' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
		'featured_image'        => __( 'Sauce Image', 'text_domain' ),
		'set_featured_image'    => __( 'Set sauce image', 'text_domain' ),
		'remove_featured_image' => __( 'Remove sauce image', 'text_domain' ),
		'use_featured_image'    => __( 'Use as sauce image', 'text_domain' ),
		'insert_into_item'      => __( 'Insert into Sauce', 'text_domain' ),
		'uploaded_to_this_item' => __( 'Uploaded to this Sauce', 'text_domain' ),
		'items_list'            => __( 'Sauces list', 'text_domain' ),
		'items_list_navigation' => __( 'Sauces list navigation', 'text_domain' ),
		'filter_items_list'     => __( 'Filter Sauces List', 'text_domain' ),
	);
	$args = array(
		'label'                 => __( 'sauce', 'text_domain' ),
		'description'           => __( 'These are the global sauces that are available for selection when building your pizza products.', 'text_domain' ),
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
	register_post_type( 'pizzalayer_sauces', $args );

}
add_action( 'init', 'pizzalayer_sauces_custom_post_type', 0 );