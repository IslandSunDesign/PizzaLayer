<?php
do_action( 'pizzalayer_file_topper-ui-css_start' );

//PIZZA-SPECIFIC CSS - values applied from customizer / global meta

function pizzalayer_options_css_output(){

/* ==  Get global options == */
$pizzalayer_global_options_string = '';
$pizzalayer_global_option_cheese_distance = get_option( 'pizzalayer_cheese_setting_cheesedistance' );
$pizzalayer_global_option_crust_aspectratio = get_option( 'pizzalayer_setting_crust_aspectratio' );
$pizzalayer_global_option_crust_padding = get_option( 'pizzalayer_setting_crust_padding' );
$pizzalayer_global_option_cheese_padding = get_option( 'pizzalayer_setting_cheese_padding' );
$pizzalayer_global_option_advanced_css = get_option( 'pizzalayer_setting_advanced_css' );
$pizzalayer_global_option_color = get_option( 'pizzalayer_setting_global_color' );

$pizzalayer_template_glass_option_pizza_size_max = get_option( 'pizzalayer_setting_pizza_size_max' );
$pizzalayer_template_glass_option_pizza_size_min = get_option( 'pizzalayer_setting_pizza_size_min' );
$pizzalayer_template_glass_option_pizza_border = get_option( 'pizzalayer_setting_pizza_border' );
$pizzalayer_template_glass_option_pizza_border_color = get_option( 'pizzalayer_setting_pizza_border_color' );

do_action( 'func_pizzalayer_options_css_output_after_vars' );

/* ==  Generate CSS for inline placement based on global option values == */

if($pizzalayer_global_option_color){ $pizzalayer_global_options_string .= '.pizzalayer-ui-menu{background:' . $pizzalayer_global_option_color . ';}'; };

if($pizzalayer_global_option_cheese_distance){ $pizzalayer_global_options_string .= '
#pizzalayer-base-layer-sauce{padding:' . $pizzalayer_global_option_cheese_distance . ';}'; };

if($pizzalayer_global_option_crust_aspectratio){ $pizzalayer_global_options_string .= '
#pizzalayer-base-layer-sauce{aspect-ratio:' . $pizzalayer_global_option_crust_aspectratio . ';}'; };

if($pizzalayer_global_option_crust_padding){ $pizzalayer_global_options_string .= '
#pizzalayer-base-layer-crust{padding:' . $pizzalayer_global_option_crust_padding . 'px !important;}'; };

if($pizzalayer_global_option_cheese_padding){ $pizzalayer_global_options_string .= '
#pizzalayer-base-layer-cheese,body .pizzalayer-cheese{padding:' . $pizzalayer_global_option_cheese_padding . 'px !important;}'; };

if($pizzalayer_template_glass_option_pizza_size_min){ $pizzalayer_global_options_string .= '
#pizzalayer-pizza{min-width:' . $pizzalayer_template_glass_option_pizza_size_min . ';min-height:' . $pizzalayer_template_glass_option_pizza_size_min . ';}'; };

if($pizzalayer_template_glass_option_pizza_size_max){ $pizzalayer_global_options_string .= '
#pizzalayer-pizza{max-width:' . $pizzalayer_template_glass_option_pizza_size_max . ';}'; };

if($pizzalayer_template_glass_option_pizza_border_color && $pizzalayer_template_glass_option_pizza_border){
    $pizzalayer_global_options_string .= '
    body .pizzalayer-crust{border:solid ' . $pizzalayer_template_glass_option_pizza_border . ' ' . $pizzalayer_template_glass_option_pizza_border_color . ';}';
} else if($pizzalayer_template_glass_option_pizza_border){ 
    $pizzalayer_global_options_string .= '
    body .pizzalayer-crust{border:solid ' . $pizzalayer_template_glass_option_pizza_border . ' #FFFFFF;}'; 
};

if($pizzalayer_global_option_advanced_css){ $pizzalayer_global_options_string .= $pizzalayer_global_option_advanced_css; };

/* ==  Wrap CSS in style tags == */
$pizzalayer_options_css = '<style>
/*-- Dynamic CSS from topper-ui-css.php --*/
' . $pizzalayer_global_options_string . '
</style>';

do_action( 'func_pizzalayer_options_css_output_before_return' );

/* ==  finally, return combined CSS == */
return $pizzalayer_options_css;

} //close function

do_action( 'pizzalayer_file_topper-ui-css_end' );