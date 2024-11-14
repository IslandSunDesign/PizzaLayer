<?php 

//PIZZA-SPECIFIC CSS - values applied from customizer / global meta

function pizzalayer_options_css_output(){
// static variables
$pizzalayer_global_options_string = '';

// get WP plugin option values
$pizzalayer_global_option_color = get_option( 'pizzalayer_setting_global_color' );
if($pizzalayer_global_option_color){ $pizzalayer_global_options_string .= '.pizzalayer-ui-menu{background:' . $pizzalayer_global_option_color . ';'; };

$pizzalayer_global_option_cheese_distance = get_option( 'pizzalayer_cheese_setting_cheesedistance' );
if($pizzalayer_global_option_cheese_distance){ $pizzalayer_global_options_string .= '#pizzalayer-base-layer-sauce{padding:' . $pizzalayer_global_option_cheese_distance . ';'; };

$pizzalayer_global_option_crust_aspectratio = get_option( 'pizzalayer_setting_crust_aspectratio' );
if($pizzalayer_global_option_crust_aspectratio){ $pizzalayer_global_options_string .= '#pizzalayer-base-layer-sauce{aspect-ratio:' . $pizzalayer_global_option_crust_aspectratio . ';'; };

$pizzalayer_global_option_advanced_css = get_option( 'pizzalayer_setting_advanced_css' );
if($pizzalayer_global_option_advanced_css){ $pizzalayer_global_options_string .= $pizzalayer_global_option_advanced_css; };

//gather CSS for 
$pizzalayer_options_css = '<style type="text/css">' . $pizzalayer_global_options_string . '</style>';

//finally, return combined CSS
return $pizzalayer_options_css;
    
    
} //close function