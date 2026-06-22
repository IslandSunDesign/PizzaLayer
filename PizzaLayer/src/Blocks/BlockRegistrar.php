<?php
namespace PizzaLayer\Blocks;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Registers PizzaLayer Gutenberg blocks.
 *
 * Each block is defined in blocks/{name}/block.json (no build step required).
 * The editor UI is plain vanilla JS (block.js).
 * Frontend rendering delegates directly to the existing shortcode classes
 * so there is zero duplication of logic.
 *
 * Requires WordPress 5.8+ (block.json registration API).
 */
class BlockRegistrar {

	/**
	 * Register all blocks via the init hook.
	 * Call: $this->loader->add_action( 'init', $blocks, 'register' );
	 */
	public function register(): void {
		if ( ! function_exists( 'register_block_type' ) ) {
			return; // WordPress < 5.0 guard
		}

		$blocks_dir = PIZZALAYER_BLOCKS_DIR;

		// Pizza Builder — interactive builder
		register_block_type(
			$blocks_dir . 'pizza-builder',
			[ 'render_callback' => [ $this, 'render_builder' ] ]
		);

		// Pizza Static — non-interactive display pizza
		register_block_type(
			$blocks_dir . 'pizza-static',
			[ 'render_callback' => [ $this, 'render_static' ] ]
		);

		// Pizza Layer Image — single layer <img>
		register_block_type(
			$blocks_dir . 'pizza-layer',
			[ 'render_callback' => [ $this, 'render_layer' ] ]
		);
	}

	/* ──────────────────────────────────────────────────────────────
	   RENDER CALLBACKS
	   These map block attributes → shortcode attributes and delegate
	   to the existing shortcode render methods.
	   ────────────────────────────────────────────────────────────── */

	/**
	 * Render callback for pizzalayer/pizza-builder.
	 *
	 * Maps block attributes to [pizza_builder] shortcode attributes.
	 *
	 * @param array $atts Block attributes from the editor.
	 * @return string HTML output.
	 */
	public function render_builder( array $atts ): string {
		$shortcode_atts = $this->filter_atts( [
			'id'             => $atts['instanceId']   ?? '',
			'template'       => $atts['template']     ?? '',
			'max_toppings'   => $atts['maxToppings']  ?? '',
			'show_tabs'      => $atts['showTabs']     ?? '',
			'hide_tabs'      => $atts['hideTabs']     ?? '',
			'default_crust'  => $atts['defaultCrust'] ?? '',
			'default_sauce'  => $atts['defaultSauce'] ?? '',
			'default_cheese' => $atts['defaultCheese'] ?? '',
			'pizza_shape'    => $atts['pizzaShape']   ?? '',
			'pizza_aspect'   => $atts['pizzaAspect']  ?? '',
			'pizza_radius'   => $atts['pizzaRadius']  ?? '',
			'layer_anim'     => $atts['layerAnim']    ?? '',
		] );

		$shortcode = new \PizzaLayer\Shortcodes\BuilderShortcode();
		return $shortcode->render( $shortcode_atts );
	}

	/**
	 * Render callback for pizzalayer/pizza-static.
	 *
	 * @param array $atts Block attributes.
	 * @return string HTML output.
	 */
	public function render_static( array $atts ): string {
		$shortcode_atts = $this->filter_atts( [
			'preset'   => $atts['preset']   ?? '',
			'crust'    => $atts['crust']    ?? '',
			'sauce'    => $atts['sauce']    ?? '',
			'cheese'   => $atts['cheese']   ?? '',
			'toppings' => $atts['toppings'] ?? '',
			'drizzle'  => $atts['drizzle']  ?? '',
			'cut'      => $atts['cut']      ?? '',
		] );

		$shortcode = new \PizzaLayer\Shortcodes\StaticShortcode();
		return $shortcode->render( $shortcode_atts );
	}

	/**
	 * Render callback for pizzalayer/pizza-layer.
	 *
	 * @param array $atts Block attributes.
	 * @return string HTML output.
	 */
	public function render_layer( array $atts ): string {
		$shortcode_atts = $this->filter_atts( [
			'type'  => $atts['layerType']  ?? 'crust',
			'slug'  => $atts['slug']       ?? '',
			'image' => $atts['imageField'] ?? 'list',
			'class' => $atts['cssClass']   ?? '',
		] );

		if ( empty( $shortcode_atts['slug'] ) ) {
			if ( is_admin() || wp_is_serving_rest_request() ) {
				return '<p style="color:#999;font-style:italic;font-size:13px;margin:0;">'
				     . esc_html__( 'Enter a layer slug in the block settings.', 'pizzalayer' )
				     . '</p>';
			}
			return '';
		}

		$shortcode = new \PizzaLayer\Shortcodes\LayerImageShortcode();
		return $shortcode->render( $shortcode_atts );
	}

	/* ──────────────────────────────────────────────────────────────
	   HELPERS
	   ────────────────────────────────────────────────────────────── */

	/**
	 * Strip empty-string values so shortcode_atts() defaults kick in.
	 *
	 * @param array $atts Raw attribute map.
	 * @return array Filtered map (empty strings preserved — shortcodes handle them).
	 */
	private function filter_atts( array $atts ): array {
		return array_map( 'strval', $atts );
	}
}
