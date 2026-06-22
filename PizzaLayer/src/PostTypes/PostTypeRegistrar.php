<?php
namespace PizzaLayer\PostTypes;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Registers all 8 PizzaLayer custom post types plus the
 * pizzalayer_ingredient_group hierarchical taxonomy.
 */
class PostTypeRegistrar {

	/**
	 * CPT definitions: slug suffix → singular / plural / description / icon.
	 * Post type name = "pizzalayer_{slug}".
	 */
	private const TYPES = [
		'toppings'      => [ 'Topping',      'Toppings',      'Global toppings available for pizza building.',              'dashicons-carrot'      ],
		'crusts'        => [ 'Crust',         'Crusts',        'Crust options that form the base of each pizza.',           'dashicons-admin-generic' ],
		'sauces'        => [ 'Sauce',         'Sauces',        'Sauce layers applied on top of the crust.',                 'dashicons-food'        ],
		'cheeses'       => [ 'Cheese',        'Cheeses',       'Cheese layers applied on top of the sauce.',                'dashicons-admin-generic' ],
		'drizzles'      => [ 'Drizzle',       'Drizzles',      'Optional finishing drizzle layers.',                        'dashicons-admin-generic' ],
		'cuts'          => [ 'Cut',           'Cuts',          'Pizza slicing / cut style overlays.',                       'dashicons-admin-generic' ],
		'sizes'         => [ 'Size',          'Sizes',         'Pizza size options with dimension and pricing data.',        'dashicons-image-rotate' ],
		'presets'       => [ 'Preset',        'Presets',       'Pre-configured pizza combinations ready to select.',        'dashicons-food'        ],
	];

	/**
	 * CPT slugs that participate in ingredient grouping taxonomy.
	 * Cuts and sizes are excluded — they are structural, not ingredients.
	 */
	private const GROUPABLE_TYPES = [
		'pizzalayer_toppings',
		'pizzalayer_crusts',
		'pizzalayer_sauces',
		'pizzalayer_cheeses',
		'pizzalayer_drizzles',
	];

	public function register(): void {
		foreach ( self::TYPES as $slug => [ $singular, $plural, $description, $icon ] ) {
			$this->register_type( $slug, $singular, $plural, $description, $icon );
		}
		$this->register_ingredient_group_taxonomy();
		do_action( 'pizzalayer_cpt_registered' );
	}

	/**
	 * Register the pizzalayer_ingredient_group hierarchical taxonomy.
	 *
	 * Hierarchical (like categories) so admins can create parent groups
	 * (e.g. "Meat", "Vegetable") and optional sub-groups.
	 * Applied to all five ingredient CPTs so one taxonomy covers everything.
	 */
	private function register_ingredient_group_taxonomy(): void {
		$labels = [
			'name'              => _x( 'Ingredient Groups', 'Taxonomy General Name', 'pizzalayer' ),
			'singular_name'     => _x( 'Ingredient Group',  'Taxonomy Singular Name', 'pizzalayer' ),
			'menu_name'         => __( 'Ingredient Groups', 'pizzalayer' ),
			'all_items'         => __( 'All Groups',        'pizzalayer' ),
			'parent_item'       => __( 'Parent Group',      'pizzalayer' ),
			'parent_item_colon' => __( 'Parent Group:',     'pizzalayer' ),
			'new_item_name'     => __( 'New Group Name',    'pizzalayer' ),
			'add_new_item'      => __( 'Add New Group',     'pizzalayer' ),
			'edit_item'         => __( 'Edit Group',        'pizzalayer' ),
			'update_item'       => __( 'Update Group',      'pizzalayer' ),
			'view_item'         => __( 'View Group',        'pizzalayer' ),
			'search_items'      => __( 'Search Groups',     'pizzalayer' ),
			'not_found'         => __( 'Not Found',         'pizzalayer' ),
		];

		$args = [
			'labels'            => $labels,
			'hierarchical'      => true,   // Like categories, not tags.
			'public'            => false,   // Not publicly queryable on front-end.
			'show_ui'           => true,
			'show_in_menu'      => false,   // Accessed via each CPT's edit screen.
			'show_in_rest'      => true,    // Available via REST for block editor.
			'show_admin_column' => true,    // Show group column in CPT list tables.
			'rewrite'           => false,
		];

		register_taxonomy( 'pizzalayer_ingredient_group', self::GROUPABLE_TYPES, $args );
	}

	private function register_type( string $slug, string $singular, string $plural, string $description, string $icon ): void {
		$post_type = 'pizzalayer_' . $slug;

		$labels = [
			'name'                  => _x( $plural,          'Post Type General Name', 'pizzalayer' ),
			'singular_name'         => _x( $singular,        'Post Type Singular Name', 'pizzalayer' ),
			'menu_name'             => __( $plural,           'pizzalayer' ),
			'name_admin_bar'        => __( $singular,         'pizzalayer' ),
			'archives'              => sprintf( __( '%s List', 'pizzalayer' ), $plural ),
			'all_items'             => sprintf( __( 'All %s', 'pizzalayer' ), $plural ),
			'add_new_item'          => sprintf( __( 'Add New %s', 'pizzalayer' ), $singular ),
			'add_new'               => __( 'Add New', 'pizzalayer' ),
			'edit_item'             => sprintf( __( 'Edit %s', 'pizzalayer' ), $singular ),
			'update_item'           => sprintf( __( 'Update %s', 'pizzalayer' ), $singular ),
			'view_item'             => sprintf( __( 'View %s', 'pizzalayer' ), $singular ),
			'search_items'          => sprintf( __( 'Search %s', 'pizzalayer' ), $plural ),
			'not_found'             => __( 'Not found', 'pizzalayer' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'pizzalayer' ),
			'featured_image'        => sprintf( __( '%s Image', 'pizzalayer' ), $singular ),
			'set_featured_image'    => sprintf( __( 'Set %s image', 'pizzalayer' ), strtolower( $singular ) ),
			'remove_featured_image' => sprintf( __( 'Remove %s image', 'pizzalayer' ), strtolower( $singular ) ),
		];

		$args = [
			'label'               => __( $singular, 'pizzalayer' ),
			'description'         => __( $description, 'pizzalayer' ),
			'labels'              => $labels,
			'supports'            => [ 'title', 'editor', 'thumbnail' ],
			'taxonomies'          => [ 'category', 'post_tag' ],
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'menu_icon'           => $icon,
			'menu_position'       => 35,
			'show_in_admin_bar'   => false,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'page',
			'show_in_rest'        => true,
		];

		/**
		 * Filter CPT registration args for a specific type.
		 *
		 * @param array  $args      CPT args array.
		 * @param string $post_type Full post type name (e.g. 'pizzalayer_toppings').
		 */
		$args = apply_filters( "pizzalayer_cpt_args_{$slug}", $args, $post_type );

		register_post_type( $post_type, $args );
	}
}
