<?php
do_action( 'pizzalayer_file_topper-ui-frontend-display_start' );
// CUSTOM PIZZA SHORTCODE
function pizzalayer_custom_shortcode_func( $atts ) {
    // usage : [pizzalayer-custom id="" crust="" sauce="" toppings=""]
	$a = shortcode_atts( array(
		'id' => '',
		'crust' => '',
        'sauce' => '',
        'toppings' =>'',
	), $atts );

do_action( 'func_pizzalayer_custom_shortcode_func_before_return' );
	return pizzalayer_pizza_static_nested($atts['id'],$atts['crust'],$atts['sauce'],$atts['toppings']);
}
add_shortcode( 'pizzalayer-custom', 'pizzalayer_custom_shortcode_func' );

// CUSTOM PIZZA SHORTCODE
function pizzalayer_custom_nested_shortcode_func( $atts ) {
    // usage : [pizzalayer-custom id="" crust="" sauce="" toppings=""]
	$a = shortcode_atts( array(
		'id' => '',
		'crust' => '',
        'sauce' => '',
        'toppings' =>'',
	), $atts );

do_action( 'func_pizzalayer_custom_nested_shortcode_func_before_return' );
	return pizzalayer_pizza_static_nested($atts['id'],$atts['crust'],$atts['sauce'],$atts['toppings']);
}
add_shortcode( 'pizzalayer-custom-nested', 'pizzalayer_custom_nested_shortcode_func' );
do_action( 'pizzalayer_file_topper-ui-frontend-display_end' );
