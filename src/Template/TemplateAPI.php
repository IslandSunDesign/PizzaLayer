<?php
namespace PizzaLayer\Template;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Helper functions available to template files.
 * Templates can call PizzaLayer\Template\TemplateAPI::method() or use the
 * procedural helpers registered below.
 */
class TemplateAPI {

	/**
	 * Get all posts for a CPT type, ordered by menu_order then title.
	 *
	 * @param string $type CPT suffix (e.g. 'toppings', 'crusts').
	 * @param array  $extra_args Additional WP_Query args.
	 * @return \WP_Post[]
	 */
	public static function get_layer_posts( string $type, array $extra_args = [] ): array {
		$defaults = [
			'post_type'      => 'pizzalayer_' . $type,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
		];
		$args = apply_filters( "pizzalayer_query_args_{$type}", array_merge( $defaults, $extra_args ), $type );
		return get_posts( $args );
	}

	/**
	 * Resolve the preferred "list / thumbnail" image URL for a layer post.
	 * Falls back gracefully: SCF list image → SCF layer image → featured image.
	 */
	public static function get_list_image( int $post_id, string $type ): string {
		$field  = $type . '_image'; // e.g. topping_image, sauce_image
		$lfield = $type . '_layer_image';
		$url    = get_field( $field, $post_id );
		if ( ! $url ) { $url = get_field( $lfield, $post_id ); }
		if ( ! $url ) { $url = (string) get_the_post_thumbnail_url( $post_id, 'medium' ); }
		return (string) $url;
	}

	/**
	 * Resolve the layer-stack image URL (the transparent PNG used in the pizza visualization).
	 */
	public static function get_layer_image( int $post_id, string $type ): string {
		$url = get_field( $type . '_layer_image', $post_id );
		if ( ! $url ) { $url = self::get_list_image( $post_id, $type ); }
		return (string) $url;
	}
}
