<?php
// Function to get WordPress post ID given the post title
function pizzalayer_getIdBySlug( $slug, $posttype ){
   $layer = get_page_by_path($slug,OBJECT,'pizzalayer_' . $posttype);
   if ( is_array( $layer ) ) {
    return $layer->ID;
} elseif ( is_object( $layer ) ) {
    return $layer->ID;
} else {
    $result = ''; // Optional fallback
};
   
}



/* ============================================= 
PIZZALAYER : DYNAMIC NESTED CUSTOM PIZZA DISPLAY - used for showing a buildable pizza on a page or post */
function pizzalayer_pizza_dynamic_nested(
    $ptpizza_dynamic_pizza_id = 'pizza-dynamic',
    $ptpizza_dynamic_pizza_css_id = 'pizza-dynamic-id',
    $ptpizza_dynamic_pizza_crust = '',
    $ptpizza_dynamic_pizza_sauce = '',
    $ptpizza_dynamic_pizza_cheese = '',
    $ptpizza_dynamic_pizza_toppings_array = '',
    $ptpizza_dynamic_pizza_drizzle = '',
    $ptpizza_dynamic_pizza_cut = ''
    ){
     do_action( 'func_pizzalayer_pizza_dynamic_nested_start' );
// GET LAYER PRESETS FROM PLUGIN OPTIONS


// PREPARE CRUST AS OUTERMOST LAYER
$ptpizza_layer_crust_option = get_option('pizzalayer_setting_crust_defaultcrust');
if($ptpizza_layer_crust_option != ''){ $ptpizza_dynamic_pizza_crust = $ptpizza_layer_crust_option; };
$ptpizza_layer_crust_id = pizzalayer_getIdBySlug($ptpizza_dynamic_pizza_crust,'crusts');
$ptpizza_layer_crust = pizzalayer_layer_nest(100,'crust', get_field('crust_layer_image', $ptpizza_layer_crust_id), $ptpizza_dynamic_pizza_crust);    
    
// PREPARE SAUCE AS 2ND LAYER
$ptpizza_layer_sauce_option = get_option('pizzalayer_setting_sauce_defaultsauce');
if($ptpizza_layer_sauce_option != ''){ $ptpizza_dynamic_pizza_sauce = $ptpizza_layer_sauce_option; };
$ptpizza_layer_sauce_id = pizzalayer_getIdBySlug($ptpizza_dynamic_pizza_sauce,'sauces');
$ptpizza_layer_sauce = pizzalayer_layer_nest(150,'sauce', get_field('sauce_layer_image', $ptpizza_layer_sauce_id), $ptpizza_dynamic_pizza_sauce);     

// PREPARE CHEESE AS 3RD LAYER
$ptpizza_layer_cheese_option = get_option('pizzalayer_setting_cheese_defaultcheese');
if($ptpizza_layer_cheese_option != ''){ $ptpizza_dynamic_pizza_cheese = $ptpizza_layer_cheese_option; };
$ptpizza_layer_cheese_id = pizzalayer_getIdBySlug($ptpizza_dynamic_pizza_cheese,'cheeses');
$ptpizza_layer_cheese = pizzalayer_layer_nest(200,'cheese', get_field('cheese_layer_image', $ptpizza_layer_cheese_id), $ptpizza_dynamic_pizza_cheese); 

// PREPARE DRIZZLE AS 5TH LAYER
$ptpizza_layer_drizzle_option = get_option('pizzalayer_setting_drizzle_defaultdrizzle');
if($ptpizza_layer_drizzle_option != ''){ $ptpizza_dynamic_pizza_drizzle = $ptpizza_layer_drizzle_option; };
$ptpizza_layer_drizzle_id = pizzalayer_getIdBySlug($ptpizza_dynamic_pizza_drizzle,'drizzles');
$ptpizza_layer_drizzle = pizzalayer_layer_nest(900,'drizzle', get_field('drizzle_layer_image', $ptpizza_layer_drizzle_id), $ptpizza_dynamic_pizza_drizzle); 

// ADD PIZZA CUT DIAGRAM AS TOP LAYER
$ptpizza_layer_cut_option = get_option('pizzalayer_setting_cut_defaultcut');
if($ptpizza_layer_cut_option != ''){ $ptpizza_dynamic_pizza_cut = $ptpizza_layer_cut_option; };
$ptpizza_layer_cut_id = pizzalayer_getIdBySlug($ptpizza_dynamic_pizza_cut,'cuts');
$ptpizza_layer_cut = pizzalayer_layer_nest(950,'cut', get_field('cut_layer_image', $ptpizza_layer_cut_id), $ptpizza_dynamic_pizza_cut); 

do_action( 'func_pizzalayer_pizza_dynamic_nested_after_layers_declared' );

// PREPARE TOPPINGS AS LAYER
$pizzalayer_this_toppings_array = explode(',',$ptpizza_dynamic_pizza_toppings_array); //convert toppings string into an array
$ptpizza_layer_index = 400; //layer # (z-index)
$ptpizza_toppings_output_html = '<div id="pizzalayer-toppings-wrapper" class="pizzalayer-toppings-wrapper pizzalayer-toppings-wrapper-dynamic">';

//START PARSING TOPPINGS - START LOOPING THROUGH ARRAY VALUES
foreach ($pizzalayer_this_toppings_array as $pizzalayer_this_layer) {
$ptpizza_layer_index += 5; // topping layer (z-index value)
$ptpizza_layer_slug = $pizzalayer_this_layer; // topping slug
$ptpizza_layer_id = pizzalayer_getIdBySlug($ptpizza_layer_slug,'toppings');
$ptpizza_layer_imageurl = get_field('topping_layer_image', $ptpizza_layer_id);
$ptpizza_layer_alt = 'Pizza topping : ' . $ptpizza_layer_slug; // topping alt / description

//PREPARE PIZZA LAYER FROM CURRENT ARRAY ITEM
$ptpizza_toppings_output_html .= pizzalayer_layer( $ptpizza_layer_index, $ptpizza_layer_slug, $ptpizza_layer_imageurl, $ptpizza_layer_alt );
}; // END foreach

// PREPARE DRIZZLE AS 5TH LAYER
$ptpizza_layer_drizzle = pizzalayer_layer_nest(950,'drizzle', '','drizzle') . '</div>';

// ADD PIZZA CUT DIAGRAM AS TOP LAYER
$ptpizza_layer_cut = pizzalayer_layer_nest(990,'cut', '','Slices') . '</div>';

do_action( 'func_pizzalayer_pizza_dynamic_nested_before_cooking' );

//BAKE THE PIZZA HTML AND CLOSE NESTED CONTAINERS
$pt_pizza_cooked = pizzalayer_ui_wrapper_pizza_dyn_1_start() . $ptpizza_layer_crust . $ptpizza_layer_sauce . $ptpizza_layer_cheese . $ptpizza_toppings_output_html . $ptpizza_layer_drizzle . '
</div><!-- // close pizza toppings wrapper -->' . $ptpizza_layer_cut . '
</div><!-- // close pizza cheese -->
</div><!-- // close pizza sauce -->
</div><!-- // close pizza crust -->
' . pizzalayer_ui_wrapper_pizza_dyn_1_end();
//RETURN RESULTS
return $pt_pizza_cooked . pizzalayer_swapper_js_output();
do_action( 'func_pizzalayer_pizza_dynamic_nested_end' );
} //end function






/* ============================================= 
PIZZALAYER : STATIC NESTED CUSTOM PIZZA DISPLAY - used for showing a static pizza example with pre-selected toppings */
function pizzalayer_pizza_static_nested(
    $ptpizza_static_pizza_id = 'pizza-static',
    $ptpizza_static_pizza_css_id = 'pizza-static-id',
    $ptpizza_static_pizza_crust = '',
    $ptpizza_static_pizza_sauce = '',
    $ptpizza_static_pizza_cheese = '',
    $ptpizza_static_pizza_toppings_array = '',
    $ptpizza_static_pizza_drizzle = '',
    $ptpizza_static_pizza_cut = ''
    ){
do_action( 'func_pizzalayer_pizza_static_nested_start' );
// PREPARE CRUST AS OUTERMOST LAYER
$ptpizza_layer_crust_option = get_option('pizzalayer_setting_crust_defaultcrust');
if($ptpizza_layer_crust_option != ''){ $ptpizza_static_pizza_crust = $ptpizza_layer_crust_option; };
$ptpizza_layer_crust_id = pizzalayer_getIdBySlug($ptpizza_static_pizza_crust,'crusts');
$ptpizza_layer_crust = pizzalayer_layer_nest(200,'crust', get_field('crust_layer_image', $ptpizza_layer_crust_id), $ptpizza_static_pizza_crust);    
    
// PREPARE SAUCE AS 2ND LAYER
$ptpizza_layer_sauce_option = get_option('pizzalayer_setting_sauce_defaultsauce');
if($ptpizza_layer_sauce_option != ''){ $ptpizza_static_pizza_sauce = $ptpizza_layer_sauce_option; };
$ptpizza_layer_sauce_id = pizzalayer_getIdBySlug($ptpizza_static_pizza_sauce,'sauces');
$ptpizza_layer_sauce = pizzalayer_layer_nest(250,'sauce', get_field('sauce_layer_image', $ptpizza_layer_sauce_id), $ptpizza_static_pizza_sauce);     

// PREPARE CHEESE AS 3RD LAYER
$ptpizza_layer_cheese_option = get_option('pizzalayer_setting_cheese_defaultcheese');
if($ptpizza_layer_cheese_option != ''){ $ptpizza_static_pizza_cheese = $ptpizza_layer_cheese_option; };
$ptpizza_layer_cheese_id = pizzalayer_getIdBySlug($ptpizza_static_pizza_cheese,'cheeses');
$ptpizza_layer_cheese = pizzalayer_layer_nest(300,'cheese', get_field('cheese_layer_image', $ptpizza_layer_cheese_id), $ptpizza_static_pizza_cheese); 

// PREPARE DRIZZLE AS 5TH LAYER
$ptpizza_layer_drizzle_option = get_option('pizzalayer_setting_drizzle_defaultdrizzle');
if($ptpizza_layer_drizzle_option != ''){ $ptpizza_static_pizza_drizzle = $ptpizza_layer_drizzle_option; };
$ptpizza_layer_drizzle_id = pizzalayer_getIdBySlug($ptpizza_static_pizza_drizzle,'drizzles');
$ptpizza_layer_drizzle = pizzalayer_layer_nest(990,'drizzle', get_field('drizzle_layer_image', $ptpizza_layer_drizzle_id), $ptpizza_static_pizza_drizzle); 

// ADD PIZZA CUT DIAGRAM AS TOP LAYER
$ptpizza_layer_cut_option = get_option('pizzalayer_setting_cut_defaultcut');
if($ptpizza_layer_cut_option != ''){ $ptpizza_static_pizza_cut = $ptpizza_layer_cut_option; };
$ptpizza_layer_cut_id = pizzalayer_getIdBySlug($ptpizza_static_pizza_cut,'cuts');
$ptpizza_layer_cut = pizzalayer_layer_nest(950,'cut', get_field('cut_layer_image', $ptpizza_layer_cut_id), $ptpizza_static_pizza_cut);

do_action( 'func_pizzalayer_pizza_static_nested_after_layers_declared' );

// USING TOPPINGS FROM SHORTCODE PARAMETERS, PREPARE TOPPINGS AS LAYERS
$pizzalayer_this_toppings_array = explode(',',$ptpizza_static_pizza_toppings_array); //convert toppings string into an array
$ptpizza_layer_index = 310; //layer # (z-index)
$ptpizza_toppings_output_html = '<div id="pizzalayer-toppings-wrapper" class="pizzalayer-toppings-wrapper pizzalayer-toppings-wrapper-static">';

//START PARSING TOPPINGS - START LOOPING THROUGH ARRAY VALUES
foreach ($pizzalayer_this_toppings_array as $pizzalayer_this_layer) {
$ptpizza_layer_index = $ptpizza_layer_index + 10; // topping layer (z-index value)
$ptpizza_layer_slug = $pizzalayer_this_layer; // topping slug
$ptpizza_layer_id = pizzalayer_getIdBySlug($ptpizza_layer_slug,'toppings');
$ptpizza_layer_imageurl = get_field('topping_layer_image', $ptpizza_layer_id);
$ptpizza_layer_alt = 'Pizza topping : ' . $ptpizza_layer_slug; // topping alt / description
//PREPARE PIZZA LAYER FROM CURRENT ARRAY ITEM
$ptpizza_toppings_output_html .= pizzalayer_layer($ptpizza_layer_index, $ptpizza_layer_slug, $ptpizza_layer_imageurl, $ptpizza_layer_alt);
}; 
// END foreach

do_action( 'func_pizzalayer_pizza_static_nested_before_cooking' );

//BAKE THE PIZZA HTML USING COMPILED STRING
$pt_pizza_cooked = pizzalayer_ui_wrapper_pizza_dyn_1_start() . $ptpizza_layer_crust . $ptpizza_layer_sauce . $ptpizza_layer_cheese . $ptpizza_toppings_output_html . '
</div><!-- // close pizza toppings wrapper -->' . $ptpizza_layer_cut . $ptpizza_layer_drizzle . '
</div><!-- // close pizza cheese -->
</div><!-- // close pizza sauce -->
</div><!-- // close pizza crust -->
' . pizzalayer_ui_wrapper_pizza_dyn_1_end();
//RETURN RESULTS
return $pt_pizza_cooked . pizzalayer_swapper_js_output(); 
do_action( 'func_pizzalayer_pizza_static_nested_end' );
} //end function

// STATIC DISPLAY SHORTCODE FUNCTION
function pizzalayer_static_pizza_func( $atts ) {
	$a = shortcode_atts( array(
		'crust' => '',
		'sauce' => '',
		'cheese' => '',
		'toppings' => '',
		'drizzle' => '',
		'slices' => '',
	), $atts );

    return pizzalayer_pizza_static_nested(
    $ptpizza_static_pizza_id = 'pizza-static',
    $ptpizza_static_pizza_css_id = 'pizza-static-id',
    $ptpizza_static_pizza_crust = $a['crust'],
    $ptpizza_static_pizza_sauce = $a['sauce'],
    $ptpizza_static_pizza_cheese = $a['cheese'],
    $ptpizza_static_pizza_toppings_array = $a['toppings'],
    $ptpizza_static_pizza_drizzle = $a['drizzle'],
    $ptpizza_static_pizza_slice = $a['slices'],
    );
}
add_shortcode( 'pizzalayer-static', 'pizzalayer_static_pizza_func' );

