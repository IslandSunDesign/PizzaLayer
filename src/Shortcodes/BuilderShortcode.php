<?php
namespace PizzaLayer\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * [pizza_builder] shortcode — renders the interactive NightPie pizza builder.
 *
 * Attributes:
 *   id              Unique instance ID. Auto-generated if omitted.
 *   template        Template slug (default: plugin setting).
 *   max_toppings    Override global max topping count.
 *   show_tabs       Comma-separated list of tabs to show (default: all).
 *   hide_tabs       Comma-separated list of tabs to hide.
 *   default_crust   Pre-select a crust slug on load.
 *   default_sauce   Pre-select a sauce slug on load.
 *   default_cheese  Pre-select a cheese slug on load.
 *   pizza_shape     Shape of the pizza: round | square | rectangle | custom.
 *   pizza_aspect    CSS aspect-ratio override (used with rectangle/custom shapes).
 *   pizza_radius    CSS border-radius override (used with custom shape).
 *   layer_anim      Layer add animation: fade | scale-in | slide-up | flip-in | drop-in | instant.
 */
class BuilderShortcode {

	/** Auto-increment for instances that don't provide an id. */
	private static int $counter = 0;

	public function render( $atts ): string {
		$atts = apply_filters( 'pizzalayer_builder_atts', shortcode_atts( [
			'id'             => '',
			'template'       => '',
			'max_toppings'   => '',
			'show_tabs'      => '',
			'hide_tabs'      => '',
			'default_crust'  => '',
			'default_sauce'  => '',
			'default_cheese' => '',
			'pizza_shape'    => '',   // round | square | rectangle | custom
			'pizza_aspect'   => '',   // CSS aspect-ratio e.g. "4 / 3"
			'pizza_radius'   => '',   // CSS border-radius e.g. "12px"
			'layer_anim'     => '',   // fade | scale-in | slide-up | flip-in | drop-in | instant
		], $atts, 'pizza_builder' ) );

		// Ensure a unique instance ID
		if ( $atts['id'] === '' ) {
			self::$counter++;
			$atts['id'] = 'pizzabuilder-' . self::$counter;
		}
		$instance_id = sanitize_html_class( $atts['id'] );

		// Template override
		$loader = new \PizzaLayer\Template\TemplateLoader();
		$slug   = $atts['template'] ? sanitize_key( $atts['template'] ) : $loader->get_active_slug();

		$menu_file = $loader->get_template_file( 'pztp-containers-menu.php', $slug );
		if ( ! file_exists( $menu_file ) ) {
			return '<p class="pizzalayer-error">' . esc_html__( 'PizzaLayer: template not found.', 'pizzalayer' ) . '</p>';
		}

		do_action( 'pizzalayer_before_builder', $instance_id, $atts );

		ob_start();
		// Expose $instance_id and $atts to the template file
		include $menu_file;
		$html = ob_get_clean();

		do_action( 'pizzalayer_after_builder', $instance_id, $atts );

		return (string) $html;
	}
}
