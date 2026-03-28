<?php
namespace PizzaLayer\PostTypes;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Registers all 8 PizzaLayer custom post types.
 * Replaces 8 separate cpt-*.php files (~400 lines) with one class.
 * Text domain bug fixed: was 'text_domain', now 'pizzalayer'.
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
		'pizzas'        => [ 'Pizza Preset',  'Pizza Presets', 'Pre-configured pizza combinations (presets).',              'dashicons-pizza'       ],
	];

	public function register(): void {
		foreach ( self::TYPES as $slug => [ $singular, $plural, $description, $icon ] ) {
			$this->register_type( $slug, $singular, $plural, $description, $icon );
		}
		do_action( 'pizzalayer_cpt_registered' );
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
			// Hidden from sidebar — all CPTs accessible via the Content hub page.
			// show_ui stays true so edit/list screens still work at their native URLs.
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
