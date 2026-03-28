<?php
namespace PizzaLayer\Api;

use PizzaLayer\Builder\PizzaBuilder;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * PizzaLayer REST API — Pizza Stack Endpoint
 *
 * POST /wp-json/pizzalayer/v1/render
 * GET  /wp-json/pizzalayer/v1/layer-url?type=crust&slug=thin-crust
 *
 * PHP usage (other plugins):
 *   $html = \PizzaLayer\Builder\PizzaBuilder::render_pizza_stack([...]);
 *   $url  = \PizzaLayer\Builder\PizzaBuilder::get_layer_url('crust','thin-crust');
 *
 * JS usage:
 *   window.PizzaLayerAPI.renderPizza({crust:'thin',sauce:'tomato'})
 *     .then(html => el.innerHTML = html);
 */
class PizzaRestApi {

	public function register_routes(): void {
		register_rest_route( 'pizzalayer/v1', '/render', [
			'methods'             => \WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'render_pizza' ],
			'permission_callback' => '__return_true',
			'args'                => [
				'crust'    => [ 'type' => 'string',              'default' => '', 'sanitize_callback' => 'sanitize_text_field' ],
				'sauce'    => [ 'type' => 'string',              'default' => '', 'sanitize_callback' => 'sanitize_text_field' ],
				'cheese'   => [ 'type' => 'string',              'default' => '', 'sanitize_callback' => 'sanitize_text_field' ],
				'drizzle'  => [ 'type' => 'string',              'default' => '', 'sanitize_callback' => 'sanitize_text_field' ],
				'cut'      => [ 'type' => 'string',              'default' => '', 'sanitize_callback' => 'sanitize_text_field' ],
				'preset'   => [ 'type' => 'string',              'default' => '', 'sanitize_callback' => 'sanitize_text_field' ],
				'toppings' => [ 'type' => [ 'array', 'string' ], 'default' => []  ],
			],
		] );

		register_rest_route( 'pizzalayer/v1', '/layer-url', [
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_layer_url' ],
			'permission_callback' => '__return_true',
			'args'                => [
				'type' => [ 'type' => 'string', 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ],
				'slug' => [ 'type' => 'string', 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ],
			],
		] );

		register_rest_route( 'pizzalayer/v1', '/presets', [
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_presets' ],
			'permission_callback' => '__return_true',
		] );
	}

	public function render_pizza( \WP_REST_Request $request ): \WP_REST_Response {
		$toppings = $request->get_param( 'toppings' );
		if ( is_array( $toppings ) ) {
			$toppings = implode( ',', array_map( 'sanitize_text_field', $toppings ) );
		}
		$html = PizzaBuilder::render_pizza_stack( [
			'crust'    => (string) $request->get_param( 'crust' ),
			'sauce'    => (string) $request->get_param( 'sauce' ),
			'cheese'   => (string) $request->get_param( 'cheese' ),
			'toppings' => (string) $toppings,
			'drizzle'  => (string) $request->get_param( 'drizzle' ),
			'cut'      => (string) $request->get_param( 'cut' ),
			'preset'   => (string) $request->get_param( 'preset' ),
		] );
		return new \WP_REST_Response( [ 'html' => $html ], 200 );
	}

	public function get_layer_url( \WP_REST_Request $request ): \WP_REST_Response {
		$url = PizzaBuilder::get_layer_url(
			(string) $request->get_param( 'type' ),
			(string) $request->get_param( 'slug' )
		);
		return new \WP_REST_Response( [ 'url' => $url ], 200 );
	}

	/**
	 * GET /wp-json/pizzalayer/v1/presets
	 *
	 * Returns all published pizza presets with their layer configuration
	 * and thumbnail URLs so templates can offer a "Start from preset" feature.
	 */
	public function get_presets( \WP_REST_Request $request ): \WP_REST_Response {
		$posts = get_posts( [
			'post_type'   => 'pizzalayer_presets',
			'post_status' => 'publish',
			'numberposts' => -1,
			'orderby'     => 'title',
			'order'       => 'ASC',
		] );

		$presets = [];
		foreach ( $posts as $post ) {
			$layers = get_post_meta( $post->ID, '_pztpro_preset_layers', true );
			if ( ! is_array( $layers ) ) {
				$layers = [];
			}
			$thumb = get_the_post_thumbnail_url( $post->ID, 'medium' );
			$presets[] = [
				'id'     => $post->ID,
				'slug'   => $post->post_name,
				'title'  => $post->post_title,
				'thumb'  => $thumb ?: '',
				'layers' => $layers,
			];
		}

		return new \WP_REST_Response( $presets, 200 );
	}
}
