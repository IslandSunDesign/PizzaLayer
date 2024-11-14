<?php
// Register Custom Post Type
function pizzalayer_cuts_custom_post_type() {

	$labels = array(
		'name'                  => _x( 'cuts', 'Post Type General Name', 'text_domain' ),
		'singular_name'         => _x( 'cut', 'Post Type Singular Name', 'text_domain' ),
		'menu_name'             => __( 'Cuts', 'text_domain' ),
		'name_admin_bar'        => __( 'Cut', 'text_domain' ),
		'archives'              => __( 'Cuts List', 'text_domain' ),
		'attributes'            => __( 'Cuts Attributes', 'text_domain' ),
		'parent_item_colon'     => __( 'Parent Cut:', 'text_domain' ),
		'all_items'             => __( 'All Cuts', 'text_domain' ),
		'add_new_item'          => __( 'New Cut', 'text_domain' ),
		'add_new'               => __( 'Add New', 'text_domain' ),
		'new_item'              => __( 'New Cut', 'text_domain' ),
		'edit_item'             => __( 'Edit Cut', 'text_domain' ),
		'update_item'           => __( 'Update Cut', 'text_domain' ),
		'view_item'             => __( 'View Cut', 'text_domain' ),
		'view_items'            => __( 'View Cuts', 'text_domain' ),
		'search_items'          => __( 'Search Cut', 'text_domain' ),
		'not_found'             => __( 'Not found', 'text_domain' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
		'featured_image'        => __( 'Cut Layer Image', 'text_domain' ),
		'set_featured_image'    => __( 'Set cut image', 'text_domain' ),
		'remove_featured_image' => __( 'Remove cut image', 'text_domain' ),
		'use_featured_image'    => __( 'Use as cut image', 'text_domain' ),
		'insert_into_item'      => __( 'Insert into Cut', 'text_domain' ),
		'uploaded_to_this_item' => __( 'Uploaded to this Cut', 'text_domain' ),
		'items_list'            => __( 'Cuts list', 'text_domain' ),
		'items_list_navigation' => __( 'Cuts list navigation', 'text_domain' ),
		'filter_items_list'     => __( 'Filter Cuts List', 'text_domain' ),
	);
	$args = array(
		'label'                 => __( 'cut', 'text_domain' ),
		'description'           => __( 'These are the global slicing cuts that are available for selection when building your pizza products.', 'text_domain' ),
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
	register_post_type( 'pizzalayer_cuts', $args );

}
add_action( 'init', 'pizzalayer_cuts_custom_post_type', 0 );