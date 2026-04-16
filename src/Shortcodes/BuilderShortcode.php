<?php
namespace PizzaLayer\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * [pizza_builder] shortcode — renders the interactive pizza builder.
 *
 * The template file receives these variables:
 *   $instance_id      — unique string ID for this builder instance
 *   $atts             — sanitised shortcode attribute array
 *   $template_slug    — active template slug (e.g. 'colorbox')
 *   $function_prefix  — safe PHP function prefix for this template (e.g. 'pzt_colorbox')
 *
 * Attributes:
 *   id              Unique instance ID. Auto-generated if omitted.
 *   template        Template slug override.
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
 *   layer_anim_speed  Animation duration in ms (80–800). Overrides global setting.
 */
class BuilderShortcode {

	/** Auto-increment for instances that don't provide an id. */
	private static int $counter = 0;

	public function render( $atts ): string {
		$atts = apply_filters( 'pizzalayer_builder_atts', shortcode_atts( [
			'id'               => '',
			'template'         => '',
			'max_toppings'     => '',
			'show_tabs'        => '',
			'hide_tabs'        => '',
			'default_crust'    => '',
			'default_sauce'    => '',
			'default_cheese'   => '',
			'default_toppings' => '',
			'default_drizzle'  => '',
			'default_cut'      => '',
			'restrict'         => '',
			'pizza_shape'      => '',
			'pizza_aspect'     => '',
			'pizza_radius'     => '',
			'layer_anim'       => '',
			'layer_anim_speed' => '',
		], $atts, 'pizza_builder' ) );

		// Ensure a unique instance ID
		if ( $atts['id'] === '' ) {
			self::$counter++;
			$atts['id'] = 'pizzabuilder-' . self::$counter;
		}
		$instance_id = sanitize_html_class( $atts['id'] );

		// Resolve template
		$loader = new \PizzaLayer\Template\TemplateLoader();
		$template_slug = $atts['template'] ? sanitize_key( $atts['template'] ) : $loader->get_active_slug();

		// Register this template's assets so AssetManager enqueues them on this page.
		// This handles the case where template="" differs from the global active template.
		\PizzaLayer\Assets\AssetManager::require_template( $template_slug );

		$menu_file = $loader->get_template_file( 'pztp-containers-menu.php', $template_slug );
		if ( ! file_exists( $menu_file ) ) {
			return '<p class="pizzalayer-error">' . esc_html__( 'PizzaLayer: template not found.', 'pizzalayer' ) . '</p>';
		}

		// Provide the function prefix so templates can guard against re-declaration
		$function_prefix = $loader->get_function_prefix( $template_slug );

		do_action( 'pizzalayer_before_builder', $instance_id, $atts );

		$html = $this->render_template( $menu_file, $instance_id, $atts, $template_slug, $function_prefix );

		do_action( 'pizzalayer_after_builder', $instance_id, $atts );

		return $html;
	}

	/**
	 * Isolate the template include in its own method scope.
	 * This prevents variables defined in render() from leaking into the template
	 * file's local scope beyond the four we intentionally expose.
	 */
	private function render_template(
		string $menu_file,
		string $instance_id,
		array  $atts,
		string $template_slug,
		string $function_prefix
	): string {
		ob_start();
		include $menu_file;
		return (string) ob_get_clean();
	}
}
