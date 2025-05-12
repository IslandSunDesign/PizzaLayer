<?php
//PIZZA-SPECIFIC CSS - values applied from customizer / global meta

function pizzalayer_template_glass_options_css_output(){
// static variables
$pizzalayer_template_glass_options_string = '';

// background color and opacity - main wrapper
//pizzalayer_setting_template_glass_background_tabscontainer
$pizzalayer_template_glass_option_color_background = get_option( 'pizzalayer_setting_template_glass_background' );
$pizzalayer_template_glass_option_background_opacity = get_option( 'pizzalayer_setting_template_glass_background_opacity' );
$pizzalayer_template_glass_option_tabscontainer_opacity = get_option( 'pizzalayer_setting_template_glass_tabscontainer_opacity' );
$pizzalayer_template_glass_option_background_effect = get_option( 'pizzalayer_setting_template_glass_background_effect' );
$pizzalayer_template_glass_option_text_color = get_option( 'pizzalayer_setting_template_glass_text_color' );
$pizzalayer_template_glass_option_title_color = get_option( 'pizzalayer_setting_template_glass_title_color' );
$pizzalayer_template_glass_option_wrapper_effect = get_option( 'pizzalayer_setting_template_glass_wrapper_effect' );
$pizzalayer_template_glass_option_tabscontainer_background = get_option( 'pizzalayer_setting_template_glass_background_tabscontainer' );
$pizzalayer_template_glass_option_sidebyside_switch = get_option( 'pizzalayer_setting_template_glass_sidebyside_switch' );

/* == setting : main container background color and opacity == */
if($pizzalayer_template_glass_option_color_background && $pizzalayer_template_glass_option_background_opacity != ''){
    $pizzalayer_template_glass_option_background_opacity_css = .01 * $pizzalayer_template_glass_option_background_opacity;
    $pizzalayer_template_glass_options_string .= 'body #pizzalayer-ui-wrapper{background:' . hex2rgba($pizzalayer_template_glass_option_color_background,$pizzalayer_template_glass_option_background_opacity_css) . ';}'; 
} else {
    $pizzalayer_template_glass_options_string .= 'body #pizzalayer-ui-wrapper{background:' . $pizzalayer_template_glass_option_color_background . ';}';
};

/* == setting : tabs container background color and opacity == */
 //$pizzalayer_template_glass_option_tabscontainer_opacity + $pizzalayer_template_glass_option_tabscontainer_background
if($pizzalayer_template_glass_option_tabscontainer_background && $pizzalayer_template_glass_option_tabscontainer_opacity != ''){
    $pizzalayer_template_glass_option_tabscontainer_background_opacity_css = .01 * $pizzalayer_template_glass_option_tabscontainer_opacity;
    $pizzalayer_template_glass_options_string .= 'body .pizzalayer-tabs .pizzalayer-ui-menu{background:' . hex2rgba($pizzalayer_template_glass_option_tabscontainer_background,$pizzalayer_template_glass_option_tabscontainer_background_opacity_css) . ';}'; 
} else if($pizzalayer_template_glass_option_tabscontainer_background){
    $pizzalayer_template_glass_options_string .= 'body .pizzalayer-tabs .pizzalayer-ui-menu{background:' . $pizzalayer_template_glass_option_tabscontainer_background . ';}';
};

/* == setting : background CSS effect == */
if($pizzalayer_template_glass_option_background_effect){
$pizzalayer_template_glass_option_background_effect_array = array(
    'none' => 'None',
    'blur' => 'blur(5px)',
    'brightness' => 'brightness(60%)',
    'contrast' => 'contrast(80%)',
    'dropshadow' => 'drop-shadow(4px 4px 10px rgba(0,0,0,.8)',
    'grayscale' => 'grayscale(80%)',
    'huerotate' => 'hue-rotate(120deg)',
    'invert' => 'invert(80%)',
    'opacity' => 'opacity(20%)',
    'sepia' => 'sepia(80%)',
    'saturate' => 'saturate(80%)'
    );  //close array  
    
  $pizzalayer_template_glass_options_string .= 'body .pizzalayer-effect-glassy,body .pizzalayer-ui-wrapper{
  backdrop-filter: ' . $pizzalayer_template_glass_option_background_effect_array[$pizzalayer_template_glass_option_background_effect] . ';
 -webkit-backdrop-filter: ' . $pizzalayer_template_glass_option_background_effect_array[$pizzalayer_template_glass_option_background_effect] . '; 
  }';  
};

/* == setting : text color == */
//pizzalayer_setting_template_glass_text_color
 if($pizzalayer_template_glass_option_text_color){
     $pizzalayer_template_glass_options_string .= 'body .pizzalayer-ui-wrapper{color:' . $pizzalayer_template_glass_option_text_color . ';}';
  };
 
 /* == setting : title color == */
 if($pizzalayer_template_glass_option_title_color){
     $pizzalayer_template_glass_options_string .= 'body .pizzalayer-ui-wrapper h2,body .pizzalayer-ui-wrapper h3,body .pizzalayer-ui-wrapper h3 .fa,body .pizzalayer-ui-wrapper h2 *,body .pizzalayer-ui-wrapper h3 *{color:' . $pizzalayer_template_glass_option_title_color . ';}';
  };

 /* == setting : wrapper effects == */
 if($pizzalayer_template_glass_option_wrapper_effect == 'dropshadow'){
     $pizzalayer_template_glass_options_string .= '.pizzalayer-effect-glassy,.pizzalayer-ui-wrapper{box-shadow: 0 4px 30px rgba(0, 0, 0, 0.3);}';
 } else if($pizzalayer_template_glass_option_wrapper_effect == 'borderless'){
     $pizzalayer_template_glass_options_string .= '.pizzalayer-effect-glassy,.pizzalayer-ui-wrapper,.pizzalayer-tabs{box-shadow: 0px none;border:0px none !important;}'; 
 };
 
 /* == setting : side by side layout : switch sides == */
//$pizzalayer_template_glass_option_sidebyside_switch
 if($pizzalayer_template_glass_option_sidebyside_switch == 'yes'){
     $pizzalayer_template_glass_options_string .= 'body #pizzalayer-main-visualizer-container{float:right;}.pizzalayer-tabs{float:left;}';
  };
  
  

//gather CSS
$pizzalayer_options_css = '<style>
/*-- Dynamic CSS from Template/pztp-template-css.php --*/
' . $pizzalayer_template_glass_options_string . '
</style>';

//finally, return combined CSS
return $pizzalayer_options_css;
    
    
} //close function