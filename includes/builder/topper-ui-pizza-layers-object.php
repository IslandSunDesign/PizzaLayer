<?php
/**
 * PizzaLayer: Object-Oriented Layer Rendering
 *
 * Provides an object-oriented API for rendering a single PizzaLayer "layer"
 * (e.g., crust, sauce, cheese, topping) as a wrapper <div> with a nested <img>,
 * mirroring the structure produced by older procedural helpers.
 *
 * @package    PizzaLayer
 * @subpackage Rendering
 * @since      1.0.0
 */

/**
 * Value Object: Layer details to render.
 *
 * @since 1.0.0
 */
class Pizzalayer_Layer_Details {

	/** @var int|string Z-index or stacking index for the layer. */
	public $index = 1;

	/** @var string Short machine name for the layer (e.g., 'crust', 'sauce', 'cheese', 'pepperoni'). */
	public $short = '';

	/** @var string Absolute or relative URL to the layer image. */
	public $image_url = '';

	/** @var string Alt text for the <img>. */
	public $alt = '';

	/** @var string Optional title attribute for the <img>. */
	public $title = '';

	/** @var string[] Additional CSS classes to apply to the wrapper <div>. */
	public $extra_div_classes = array();

	/** @var string[] Additional CSS classes to apply to the <img>. */
	public $extra_img_classes = array();

	/** @var array<string,string> Extra attributes (key => value) for the wrapper <div>. */
	public $extra_div_attrs = array();

	/** @var array<string,string> Extra attributes (key => value) for the <img>. */
	public $extra_img_attrs = array();

	/** @var bool If false, closes the wrapper </div>. Set true to leave the wrapper open (for nested base layers). */
	public $leave_wrapper_open = false;

	/**
	 * Convenience constructor.
	 *
	 * @param array|object $args Properties to hydrate (array or object).
	 */
	public function __construct( $args = array() ) {
		$defaults = get_object_vars( $this );
		$incoming = is_object( $args ) ? get_object_vars( $args ) : (array) $args;
		foreach ( array_merge( $defaults, $incoming ) as $k => $v ) {
			if ( property_exists( $this, $k ) ) {
				$this->{$k} = $v;
			}
		}
	}
}

/**
 * Layer renderer.
 *
 * @since 1.0.0
 */
class Pizzalayer_Layer_Renderer {

	/**
	 * Render a layer from a Pizzalayer_Layer_Details object.
	 *
	 * Produces markup:
	 * <div id="pizzalayer-topping-{$short}" class="pizzalayer-{$short} pizzalayer-topping-{$short} pizzalayer-layer-closed ... " style="z-index:{$index};" ...>
	 *   <img src="..." id="pizzalayer-{$short}-image" class="pizzalayer-{$short}-image ..." alt="..." title="..." style="z-index:{$index};" ... />
	 * </div> (optional)
	 *
	 * @param Pizzalayer_Layer_Details $d Layer details value object.
	 * @return string Safe HTML for output (use echo or return in templates).
	 */
	public function render( Pizzalayer_Layer_Details $d ) {
		$short = sanitize_key( $d->short );
		$z     = is_numeric( $d->index ) ? (int) $d->index : 1;

		$div_id        = 'pizzalayer-topping-' . $short;
		$div_classes   = array_filter( array_merge(
			array( 'pizzalayer-' . $short, 'pizzalayer-topping-' . $short, 'pizzalayer-layer-closed' ),
			array_map( 'sanitize_html_class', (array) $d->extra_div_classes )
		) );
		$img_id        = 'pizzalayer-' . $short . '-image';
		$img_classes   = array_filter( array_merge(
			array( 'pizzalayer-' . $short . '-image' ),
			array_map( 'sanitize_html_class', (array) $d->extra_img_classes )
		) );

		// Base attributes with escaping.
		$div_attrs = array_merge(
			array(
				'id'    => $div_id,
				'class' => implode( ' ', $div_classes ),
				'style' => 'z-index:' . $z . ';',
			),
			$this->sanitize_attr_array( (array) $d->extra_div_attrs )
		);

		$img_attrs = array_merge(
			array(
				'src'   => esc_url( $d->image_url ),
				'id'    => $img_id,
				'class' => implode( ' ', $img_classes ),
				'alt'   => $d->alt,   // Escaped in attribute builder.
				'title' => $d->title, // Escaped in attribute builder.
				'style' => 'z-index:' . $z . ';',
			),
			$this->sanitize_attr_array( (array) $d->extra_img_attrs )
		);

		// Build tag strings.
		$div_attr_str = $this->build_attr_string( $div_attrs );
		$img_attr_str = $this->build_attr_string( $img_attrs );

		$html  = '<div ' . $div_attr_str . '>';
		$html .= '<img ' . $img_attr_str . ' />';
		if ( false === (bool) $d->leave_wrapper_open ) {
			$html .= '</div>';
		}

		/**
		 * Filter the rendered PizzaLayer layer HTML.
		 *
		 * @param string                    $html Final HTML.
		 * @param Pizzalayer_Layer_Details $d    Original details object.
		 */
		return apply_filters( 'pizzalayer_render_layer_html', $html, $d );
	}

	/**
	 * Static convenience: render from a generic object (stdClass) or array.
	 *
	 * @param array|object $args Layer details.
	 * @return string HTML.
	 */
	public static function render_from_object( $args ) {
		$renderer = new self();
		$details  = new Pizzalayer_Layer_Details( $args );
		return $renderer->render( $details );
	}

	/**
	 * Build a safe HTML attribute string from an associative array.
	 *
	 * @param array $attrs Key/value attributes.
	 * @return string
	 */
	private function build_attr_string( array $attrs ) {
		$parts = array();
		foreach ( $attrs as $k => $v ) {
			if ( '' === $v && 'value' !== $k ) {
				// Allow boolean-style attributes only if explicitly needed; skip otherwise.
				continue;
			}
			$parts[] = sprintf( '%s="%s"', esc_attr( $k ), esc_attr( (string) $v ) );
		}
		return implode( ' ', $parts );
	}

	/**
	 * Sanitize an arbitrary attribute array (best-effort).
	 *
	 * @param array $attrs Attributes to sanitize.
	 * @return array
	 */
	private function sanitize_attr_array( array $attrs ) {
		$sanitized = array();
		foreach ( $attrs as $k => $v ) {
			$sanitized[ sanitize_key( $k ) ] = is_scalar( $v ) ? (string) $v : '';
		}
		return $sanitized;
	}
}

/**
 * Procedural convenience wrapper (optional).
 *
 * Mirrors the old "function-style" API while using the OO renderer under the hood.
 *
 * @param object $layer_details Object with properties mirroring Pizzalayer_Layer_Details.
 * @return string HTML
 */
function pizzalayer_render_layer_from_object( $layer_details ) {
	return Pizzalayer_Layer_Renderer::render_from_object( $layer_details );
}
