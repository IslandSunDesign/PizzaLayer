<?php
// Register Custom Post Type
function pizzalayer_pizzas_custom_post_type() {

	$labels = array(
		'name'                  => _x( 'Preset Pizzas', 'Post Type General Name', 'text_domain' ),
		'singular_name'         => _x( 'Preset Pizza', 'Post Type Singular Name', 'text_domain' ),
		'menu_name'             => __( 'Preset Pizzas', 'text_domain' ),
		'name_admin_bar'        => __( 'Preset Pizzas', 'text_domain' ),
		'archives'              => __( 'Preset Pizzas List', 'text_domain' ),
		'attributes'            => __( 'Preset Pizzas s Attributes', 'text_domain' ),
		'parent_item_colon'     => __( 'Parent Preset Pizza:', 'text_domain' ),
		'all_items'             => __( 'All Preset Pizzas', 'text_domain' ),
		'add_new_item'          => __( 'New Preset Pizzas', 'text_domain' ),
		'add_new'               => __( 'Add New', 'text_domain' ),
		'new_item'              => __( 'New Preset Pizza', 'text_domain' ),
		'edit_item'             => __( 'Edit Preset Pizza', 'text_domain' ),
		'update_item'           => __( 'Update Preset Pizza', 'text_domain' ),
		'view_item'             => __( 'View Preset Pizza', 'text_domain' ),
		'view_items'            => __( 'View Preset Pizzas', 'text_domain' ),
		'search_items'          => __( 'Search Preset Pizza', 'text_domain' ),
		'not_found'             => __( 'Not found', 'text_domain' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
		'featured_image'        => __( 'Preset Pizza Image', 'text_domain' ),
		'set_featured_image'    => __( 'Set Preset Pizza image', 'text_domain' ),
		'remove_featured_image' => __( 'Remove Preset Pizza image', 'text_domain' ),
		'use_featured_image'    => __( 'Use as Preset Pizza image', 'text_domain' ),
		'insert_into_item'      => __( 'Insert into Preset Pizza', 'text_domain' ),
		'uploaded_to_this_item' => __( 'Uploaded to this Preset Pizza', 'text_domain' ),
		'items_list'            => __( 'Preset Pizzas list', 'text_domain' ),
		'items_list_navigation' => __( 'Preset Pizzas list navigation', 'text_domain' ),
		'filter_items_list'     => __( 'Filter Preset Pizzas List', 'text_domain' ),
	);
	$args = array(
		'label'                 => __( 'Preset Pizza', 'text_domain' ),
		'description'           => __( 'These are the global pizzas that are available for displaying as a preset.', 'text_domain' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor' ),
		'taxonomies'            => array( 'category', 'post_tag' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => 'pizzalayer',
		'menu_position'         => 55,
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'page',
	);
	register_post_type( 'pizzalayer_pizzas', $args );

}
add_action( 'init', 'pizzalayer_pizzas_custom_post_type', 0 );