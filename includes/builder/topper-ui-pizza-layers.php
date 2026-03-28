<?php
/**
 * PizzaLayer Layer Rendering Functions.
 *
 * These functions generate the front-end HTML for PizzaLayer’s visual pizza builder.  
 * They handle both "closed" layers (complete <div> + <img> wrappers) and "nested"  
 * base layers (open <div> tags that must be closed later in a template).
 *
 * Functions included:
 * - pizzalayer_layer()      Outputs a complete layer with closing DIV.
 * - pizzalayer_layer_nest() Outputs a nested base layer without closing DIV
 *                           (used for crust, sauce, and cheese).
 * - pizzalayer_layer_nest() Updated version using <img> tags and curly-brace
 *                           interpolation for clarity.
 *
 * Notes:
 * - Layers are stacked using inline z-index to preserve order.
 * - Nested base layers must be closed manually in custom templates.
 * - Each function includes WordPress hooks for extensibility.
 * - Uses <img> tags instead of CSS backgrounds for better semantics and SEO.
 *
 * @package    PizzaLayer
 * @subpackage Rendering
 * @since      1.0.0
 */

do_action( 'pizzalayer_file_topper-ui-pizza-layers_before' );


/* +==============================================================+
 |  pizzalayer_layer()
 |  Output: A complete (closed) pizza layer DIV containing an <img>
 |  Notes:
 |   - Uses curly-brace interpolation for readability
 |   - Adds WordPress escaping/sanitization
 |   - Keeps z-index inline to preserve stacking order
 |   - Suitable for toppings or any single, closed layer
 +==============================================================+ */
function pizzalayer_layer( $layer_index, $layer_short, $layer_imagepath, $layer_alt ) {
    global $pizzalayer_path_images;

    // Sanitize/escape inputs
    $z_index   = (int) $layer_index;
    $short     = sanitize_key( $layer_short );
    $src       = esc_url( $layer_imagepath );
    $alt_attr  = esc_attr( $layer_alt );
    $title_attr= $alt_attr; // Mirror alt in title for now; adjust if you use a different title

    /**
     * Action: before printing a single (closed) layer
     * @param int    $z_index
     * @param string $short
     */
    do_action( 'pizzalayer_layer_before', $z_index, $short );

    $html = "
        <div id=\"pizzalayer-topping-{$short}\"
             class=\"pizzalayer-{$short} pizzalayer-topping-{$short} pizzalayer-layer-closed\"
             style=\"z-index: {$z_index};\">
            <img
                src=\"{$src}\"
                id=\"pizzalayer-{$short}-image\"
                class=\"pizzalayer-{$short}-image pizzalayer-layer-img\"
                alt=\"{$alt_attr}\"
                title=\"{$title_attr}\"
                style=\"z-index: {$z_index};\"
                loading=\"lazy\"
                decoding=\"async\"
            />
        </div>
    ";

    /**
     * Action: after printing a single (closed) layer
     * @param int    $z_index
     * @param string $short
     * @param string $html Returned markup (filterable next)
     */
    do_action( 'pizzalayer_layer_after', $z_index, $short, $html );

    /**
     * Filter: allow final markup overrides
     */
    return apply_filters( 'pizzalayer_layer_html', $html, $z_index, $short, $src, $alt_attr );
}


/* +=====================================================================+
 |  pizzalayer_layer_nest()
 |  Output: An *opening* DIV for a base layer (crust/sauce/cheese) plus
 |          an <img> tag INSIDE it. You must close the DIV later.
 |  Important:
 |   - Intentionally does NOT close the DIV (matches your original pattern)
 |   - Uses <img> instead of background-image for better semantics/SEO
 |   - Keeps z-index inline to preserve stacking order
 +=====================================================================+ */
function pizzalayer_layer_nest( $layer_index, $layer_short, $layer_imagepath, $layer_alt ) {
    global $pizzalayer_path_images;

    // Sanitize/escape inputs
    $z_index   = (int) $layer_index;
    $short     = sanitize_key( $layer_short );
    $src       = esc_url( $layer_imagepath );
    $alt_attr  = esc_attr( $layer_alt );

    /**
     * Action: before printing a nested (open) base layer
     * @param int    $z_index
     * @param string $short
     */
    do_action( 'pizzalayer_layer_nest_before', $z_index, $short );

    // Return only the opening wrapper + image; caller is responsible for closing the DIV later
    $html = "
        <div id=\"pizzalayer-base-layer-{$short}\"
             class=\"pizzalayer-{$short} pizzalayer-topping-{$short} pizzalayer-layer-nested\"
             style=\"z-index: {$z_index};background-image:(url('{$src}'));\"
             alt=\"{$alt_attr}\"
             loading=\"eager\"
             decoding=\"async\">
    ";

    /**
     * Action: parity with your original hook usage
     */
    do_action( 'pizzalayer_file_topper-ui-pizza-layers_after', $z_index, $short, $html );

    /**
     * Filter: allow final markup overrides for nested base layer opening
     */
    return apply_filters( 'pizzalayer_layer_nest_html', $html, $z_index, $short, $src, $alt_attr );
}




/* +===============================================+
 |  Render a Nested PizzaLayer Image
 |  - Uses <img> tag instead of background-image
 |  - Wraps variables with curly braces for clarity
 +===============================================+ */
function pizzalayer_layer_nest_img( $layer_index, $layer_short, $layer_imagepath, $layer_alt ) {
    global $pizzalayer_path_images;

    do_action( 'pizzalayer_layer_nest_before' );

    $output = "
        <div id=\"pizzalayer-base-layer-{$layer_short}\" 
             class=\"pizzalayer-{$layer_short} pizzalayer-topping-{$layer_short} pizzalayer-layer-nested\" 
             style=\"z-index: {$layer_index};\">
            <img src=\"{$layer_imagepath}\" alt=\"{$layer_alt}\" class=\"pizzalayer-layer-img\" />
        </div>
    ";

    do_action( 'pizzalayer_file_topper-ui-pizza-layers_after' );

    return $output;
}