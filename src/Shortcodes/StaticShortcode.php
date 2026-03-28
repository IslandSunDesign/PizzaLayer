<?php
namespace PizzaLayer\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * [pizza_static] shortcode — renders a non-interactive static pizza.
 *
 * Attributes:
 *   crust    Layer slug.
 *   sauce    Layer slug.
 *   cheese   Layer slug.
 *   toppings Comma-separated topping slugs.
 *   drizzle  Layer slug.
 *   cut      Layer slug.
 *   size     Size slug (adds a label; no visual layer).
 */
class StaticShortcode {

	public function render( $atts ): string {
		$atts = apply_filters( 'pizzalayer_static_atts', shortcode_atts( [
			'crust'    => '',
			'sauce'    => '',
			'cheese'   => '',
			'toppings' => '',
			'drizzle'  => '',
			'cut'      => '',
			'size'     => '',
			// Legacy support for [pizzalayer-static slices="..."]
			'slices'   => '',
		], $atts, 'pizza_static' ) );

		// Map legacy 'slices' → 'cut'
		if ( $atts['cut'] === '' && $atts['slices'] !== '' ) {
			$atts['cut'] = $atts['slices'];
		}

		do_action( 'pizzalayer_before_static_pizza', $atts );

		$builder = new \PizzaLayer\Builder\PizzaBuilder();
		$html    = '<div class="pizzalayer-static-wrap">'
		         . $builder->build_static(
		               $atts['crust'],
		               $atts['sauce'],
		               $atts['cheese'],
		               $atts['toppings'],
		               $atts['drizzle'],
		               $atts['cut'],
		               ''
		           )
		         . '</div>';

		do_action( 'pizzalayer_after_static_pizza', $atts );

		return $html;
	}
}
