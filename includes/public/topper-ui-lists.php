<?php
global $pizzalayer_path_images;
do_action( 'pizzalayer_file_topper-ui-lists_start' );

/*  ========================================================= */

// UNIVERSAL CONTROL ELEMENTS
/* v2 work below - multi variable layer work

/*  ======================================================== */
/*  ==== UE : LIST : APPROWS ============================== */

function shortcode_pizzalayer_ui_approws( $atts ) {
    do_action( 'func_shortcode_pizzalayer_ui_approws_before_return' );
    pizzalayer_ui_approws($atts['type'],$atts['css-class']);
};


function pizzalayer_ui_approws($tpv_query_posttype,$tpv_query_css_class){
    do_action( 'func_pizzalayer_ui_approws_start' );
// -- resets
$pizzalayer_toppings_list_array_toppings = '';
global $pizzalayer_toppings_list_current_zindex;
// -- post type
// options: crusts,sauces,cheeses,toppings,drizzles,cuts
if($tpv_query_posttype){$tpv_query_posttype_final = $tpv_query_posttype;} else {$tpv_query_posttype_final = 'toppings';};

// -- css
if($tpv_query_css_class){$tpv_query_css_class_final = ' ' . $tpv_query_css_class;} else { $tpv_query_css_class_final = '';};

// -- get global options and create variables for use
$pizzalayer_global_option_pagination = get_option('pizzalayer_setting_template_glass_pagination');
if(!$pizzalayer_global_option_pagination){$pizzalayer_global_option_pagination = '-1';};

// -- query loop arguments
$args = array(  
'post_type' => array('pizzalayer_' . $tpv_query_posttype_final),
'post_status' => 'publish',
'posts_per_page' => $pizzalayer_global_option_pagination, 
'orderby' => 'title', 
'order' => 'ASC',
);

// -- ui css class

do_action( 'func_pizzalayer_ui_approws_before_loop' );
    
// -- get cpt posts and build
$loop = new WP_Query( $args );        
while ( $loop->have_posts() ) : $loop->the_post();

$pztp_tli_short_slug = get_post_field( 'post_name', get_post() );
$pztp_tli_link_remove_layer = '<a id="pztp-button-remove-' . $pztp_tli_short_slug . '" class="pztp-ui-button" href="javascript:RemovePizzaLayer(\'pizzalayer-topping-' . $pztp_tli_short_slug . '\',\'\',\'' . $pztp_tli_short_slug . '\');">Remove</a>';    


// crusts ============================
if ($tpv_query_posttype_final == 'crusts'){
$pizzalayer_crusts_list_item_image = get_field( 'crust_layer_image' );
$pizzalayer_crusts_list_array_crusts .= '<a href="javascript:SwapBasePizzaLayer(\'pizzalayer-base-layer-crust\',\'' . get_the_title() . '\',\'' . $pizzalayer_crusts_list_item_image . '\');">
<li id="menu-pizzalayer-topping-' . $pztp_tli_short_slug . '" class="pizzalayer-topping col-lg-6 col-md-6 col-sm-12"><div class="pizzalayer-inner-tile">
<div class="col-sm-3 pizzalayer-topping-image-row"><img src="' . $pizzalayer_crusts_list_item_image . '"/></div>
<div class="col-sm-9 pizzalayer-topping-title">' . get_the_title() . '</div><div style="clear:both;"></div></div>
</li></a>';     
};


// sauces ============================
if ($tpv_query_posttype_final == 'sauces'){
$pizzalayer_sauces_list_item_image = get_field( 'sauce_layer_image' );
$pizzalayer_sauces_list_array_sauces .= '
<a href="javascript:SwapBasePizzaLayer(\'pizzalayer-base-layer-sauce\',\'' . get_the_title() . '\',\'' . $pizzalayer_sauces_list_item_image . '\');">
<li id="menu-pizzalayer-topping-' . $pztp_tli_short_slug . '" class="pizzalayer-topping col-lg-6 col-md-6 col-sm-12"><div class="pizzalayer-inner-tile">
<div class="col-sm-3 pizzalayer-topping-image-row"><img src="' . $pizzalayer_sauces_list_item_image . '"/></div>
<div class="col-sm-9 pizzalayer-topping-title">' . get_the_title() . '</div><div style="clear:both;"></div></div>
</li></a>';     
};


// cheeses ============================
if ($tpv_query_posttype_final == 'cheeses'){
$pizzalayer_cheeses_list_item_image = get_field( 'cheese_layer_image' );
$pizzalayer_cheeses_list_array_cheeses .= '
<a href="javascript:SwapBasePizzaLayer(\'pizzalayer-base-layer-cheese\',\'' . get_the_title() . '\',\'' . $pizzalayer_cheeses_list_item_image . '\');">
<li id="menu-pizzalayer-topping-' . $pztp_tli_short_slug . '" class="pizzalayer-topping col-lg-6 col-md-6 col-sm-12"><div class="pizzalayer-inner-tile">
<div class="col-sm-3 pizzalayer-topping-image-row"><img src="' . $pizzalayer_cheeses_list_item_image . '"/></div>
<div class="col-sm-9 pizzalayer-topping-title">' . get_the_title() . '</div><div style="clear:both;"></div></div>

</li></a>

';
};


// toppings ============================
if ($tpv_query_posttype_final == 'toppings'){
$pztp_tli_image = get_field( 'topping_layer_image' ); //the layer image for the visualizer
$pztp_tli_image_thumbnail = get_field( 'topping_list_image' ); //the thumbnail image for the topping displays
if($pztp_tli_image_thumbnail){ $pztp_tli_image_final = $pztp_tli_image_thumbnail; } else { $pztp_tli_image_final = $pztp_tli_image; };
$pztp_tli_image_featured = '<img src="' . $pztp_tli_image_final . '" class="pizzalayer-topping-img" />';
$pztp_tli_short = get_the_title();
$pztp_tli_topping_title = '<div class="pizzalayer-topping-title">' . $pztp_tli_short . '</div>';
$pztp_tli_short_slug = sanitize_title($pztp_tli_short);

$pztp_tli_link_add_layer = ' <a id="pztp-button-add-' . $pztp_tli_short_slug . '" class="pzpt-ui-button pzpt-ui-button-add" href="javascript:AddPizzaLayer(\'' . $pizzalayer_toppings_list_current_zindex . '\',\'' . 
$pztp_tli_short_slug . '\',\'' . $pztp_tli_image . '\',\'' . $pztp_tli_short . '\',\'pizzalayer-topping-' . $pztp_tli_short_slug . '\',\'pizzalayer-topping-' . $pztp_tli_short . '\');"><i class="fa fa-pizza-slice"></i></a>';

$pztp_tli_link_add_layer_link = 'javascript:AddPizzaLayer(\'' . $pizzalayer_toppings_list_current_zindex . '\',\'' . 
$pztp_tli_short_slug . '\',\'' . $pztp_tli_image . '\',\'' . $pztp_tli_short . '\',\'pizzalayer-topping-' . $pztp_tli_short_slug . '\',\'pizzalayer-topping-' . $pztp_tli_short . '\');';

$pztp_tli_link_add_layer_link_for_js = 'AddPizzaLayer(\'' . $pizzalayer_toppings_list_current_zindex . '\',\'' . 
$pztp_tli_short_slug . '\',\'' . $pztp_tli_image . '\',\'' . $pztp_tli_short . '\',\'pizzalayer-topping-' . $pztp_tli_short_slug . '\',\'pizzalayer-topping-' . $pztp_tli_short . '\')';

$pztp_tli_link_remove_layer_link_for_js = 'RemovePizzaLayer(\'pizzalayer-topping-' . $pztp_tli_short_slug . '\',\'\',\'' . $pztp_tli_short_slug . '\');';

$pztp_tli_link_remove_layer = '<a id="pztp-button-remove-' . $pztp_tli_short_slug . '" class="pzpt-ui-button pzpt-ui-button-remove" href="javascript:RemovePizzaLayer(\'pizzalayer-topping-' . $pztp_tli_short_slug . '\',\'\',\'' . $pztp_tli_short_slug . '\');"><i class="fa fa-trash"></i></a>';

$PizzalayerControlID = 'halfcontrol-' . $pztp_tli_short_slug;
$ToppingLayerID = 'pizzalayer-topping-' . $pztp_tli_short_slug;

global $pizzalayer_template_images_directory;
do_action( 'func_pizzalayer_ui_approws_after_topping_vars' );

    
//$PizzalayerControlID,$ToppingLayerID
$pizzalayer_halves_visual_input = '<div id="pizzalayer-halves-control-' . $PizzalayerControlID . '" class="pizzalayer-halves-control col-sm-12">
<img src="' . $pizzalayer_template_images_directory . 'button-half-pizza-left.png" class="pizzalayer-halves-control pizzalayer-halves-control-half-left"  onClick="SetToppingCoverage(\'half-left\',\'' . $ToppingLayerID . '\');" />
<img src="' . $pizzalayer_template_images_directory . 'button-half-pizza-whole.png" class="pizzalayer-halves-control pizzalayer-halves-control-whole"  onClick="SetToppingCoverage(\'whole\',\'' . $ToppingLayerID . '\');" />
<img src="' . $pizzalayer_template_images_directory . 'button-half-pizza-right.png" class="pizzalayer-halves-control pizzalayer-halves-control-half-right"  onClick="SetToppingCoverage(\'half-right\',\'' . $ToppingLayerID . '\');" />
<img src="' . $pizzalayer_template_images_directory . 'add-to-pizza.png" id="pztp-button-add-' . $pztp_tli_short_slug . '" class="pzpt-ui-button-add"  onClick="' . $pztp_tli_link_add_layer_link_for_js . '" />
<img src="' . $pizzalayer_template_images_directory . 'trash.png" id="pztp-button-remove-' . $pztp_tli_short_slug . '" class="pzpt-ui-button-remove"  onClick="' . $pztp_tli_link_remove_layer_link_for_js . '" />
</div>';
    
$pizzalayer_halves_form_controls = '<div id="'. $PizzalayerControlID . '" class="pizzalayer-halves-control-radiobutton-set">
<input type="radio" id="'. $PizzalayerControlID . '-half-left" name="'. $PizzalayerControlID . '" value="half-left">
<input type="radio" id="'. $PizzalayerControlID . '-whole" name="'. $PizzalayerControlID . '" value="whole" checked>
<input type="radio" id="'. $PizzalayerControlID . '-half-right" name="'. $PizzalayerControlID . '" value="half-right">
</div>';

//return $pizzalayer_halves_visual_input . $pizzalayer_halves_form_controls;




$pizzalayer_toppings_list_array_toppings .= '
<li id="menu-pizzalayer-topping-' . $pztp_tli_short_slug . '" class="pizzalayer-topping col-sm-6"><div class="pizzalayer-inner-tile"><a class="pizzatopper-approw-item-link" href="' . $pztp_tli_link_add_layer_link . '">
<div class="col-sm-12 pizzalayer-topping-image-row">' . $pztp_tli_image_featured . '</div>
<div class="col-sm-12 pizzalayer-topping-title-row">' . $pztp_tli_topping_title . '</div></a>
<div class="col-sm-12 pizzalayer-topping-fraction"><div class="col-sm-12">' . $pizzalayer_halves_visual_input . $pizzalayer_halves_form_controls . '</div></div><div style="clear:both;"></div></div>
</li>';
};


// drizzles ============================
if ($tpv_query_posttype_final == 'drizzles'){
$pizzalayer_drizzles_list_item_image = get_field( 'drizzle_layer_image' );
$pizzalayer_drizzles_list_array_drizzles .= '
<a href="javascript:SwapBasePizzaLayer(\'pizzalayer-base-layer-drizzle\',\'' . get_the_title() . '\',\'' . $pizzalayer_drizzles_list_item_image . '\');">
<li id="menu-pizzalayer-topping-' . $pztp_tli_short_slug . '" class="pizzalayer-topping col-lg-6 col-md-6 col-sm-12"><div class="pizzalayer-inner-tile">
<div class="col-sm-3 pizzalayer-topping-image-row"><img src="' . $pizzalayer_drizzles_list_item_image . '"/></div>
<div class="col-sm-9 pizzalayer-topping-title">' . get_the_title() . '</div><div style="clear:both;"></div></div>

</li></a>';
};


// cuts (aka slices/slicing) ============================
if ($tpv_query_posttype_final == 'cuts'){
$pizzalayer_cuts_list_item_image = get_field( 'cut_layer_image' );
$pizzalayer_cuts_list_array_cuts .= '
<a href="javascript:SwapBasePizzaLayer(\'pizzalayer-base-layer-cut\',\'' . get_the_title() . '\',\'' . $pizzalayer_cuts_list_item_image . '\');">
<li id="menu-pizzalayer-topping-' . $pztp_tli_short_slug . '" class="pizzalayer-topping col-lg-6 col-md-6 col-sm-12"><div class="pizzalayer-inner-tile">
<div class="col-sm-3 pizzalayer-topping-image-row"><img src="' . $pizzalayer_cuts_list_item_image . '"/></div>
<div class="col-sm-9 pizzalayer-topping-title">' . get_the_title() . '</div><div style="clear:both;"></div></div>
</li></a>';
};


// end post 
endwhile; //end main cpt loop
wp_reset_postdata(); 

// ==== Finally, structure and return the results by cpt type

// Return : crusts
if ($tpv_query_posttype_final == 'crusts'){
return '<ul class="pizzalayer-crusts-list pizzalayer-ui-menu-approws' . $tpv_query_css_class_final . '">' . $pizzalayer_crusts_list_array_crusts . '</ul><div style="clear:both;"></div>';
};
// Return : sauces
if ($tpv_query_posttype_final == 'sauces'){
return '<ul class="pizzalayer-sauces-list pizzalayer-ui-menu-approws' . $tpv_query_css_class_final . '">' . $pizzalayer_sauces_list_array_sauces . '</ul><div style="clear:both;"></div>';
};
// Return : cheeses
if ($tpv_query_posttype_final == 'cheeses'){
return '<ul class="pizzalayer-cheeses-list pizzalayer-ui-menu-approws' . $tpv_query_css_class_final . '">' . $pizzalayer_cheeses_list_array_cheeses . '</ul><div style="clear:both;"></div>';
};
// Return : toppings
if ($tpv_query_posttype_final == 'toppings'){
return '<ul class="pizzalayer-toppings-list-linkboxes pizzalayer-ui-menu-approws' . $tpv_query_css_class_final . '">' . $pizzalayer_toppings_list_array_toppings . '</ul>';
};
// Return : drizzles
if ($tpv_query_posttype_final == 'drizzles'){
return '<ul class="pizzalayer-drizzles-list pizzalayer-ui-menu-approws' . $tpv_query_css_class_final . '">' . $pizzalayer_drizzles_list_array_drizzles . '</ul><div style="clear:both;"></div>';
};
// Return : cuts
if ($tpv_query_posttype_final == 'cuts'){
return '<ul class="pizzalayer-cuts-list pizzalayer-ui-menu-approws' . $tpv_query_css_class_final . '">' . $pizzalayer_cuts_list_array_cuts . '</ul><div style="clear:both;"></div>';
};

}

/*  ======================================================== */
/*  ==== UE : LIST : APPROWS V2 ============================== */
$pizzalayer_list_item_style = get_option('pizzalayer_setting_element_style_layers');


function pizzalayer_ui_approws_v2_item($tpv_query_posttype_singular,$tpv_item_title,$tpv_item_slug){
global $pizzalayer_list_item_style;
global $pizzalayer_toppings_list_current_zindex;
do_action( 'func_pizzalayer_ui_approws_v2_item_start' );
$pizzalayer_list_item_image = get_field( $tpv_query_posttype_singular . '_layer_image' );


if($pizzalayer_list_item_style == 'default' || $pizzalayer_list_item_style == ''){
$pizzalayer_list_item_output = '<a href="javascript:SwapBasePizzaLayer(\'pizzalayer-base-layer-' . $tpv_query_posttype_singular . '\',\'' . $tpv_item_title . '\',\'' . $pizzalayer_list_item_image . '\');">
<li id="menu-pizzalayer-topping-' . $tpv_item_slug . '" class="pizzalayer-topping pizzalayer-topping-style-' . $pizzalayer_list_item_style . ' col-lg-6 col-md-6 col-sm-12"><div class="pizzalayer-inner-tile">
<div class="col-sm-3 pizzalayer-topping-image-row"><img src="' . $pizzalayer_list_item_image . '"/></div>
<div class="col-sm-9 pizzalayer-topping-title">' . $tpv_item_title . '</div><div style="clear:both;"></div></div>
</li></a>';


} else if($pizzalayer_list_item_style == 'thumblabel'){
$pizzalayer_list_item_output = '<a href="javascript:SwapBasePizzaLayer(\'pizzalayer-base-layer-' . $tpv_query_posttype_singular . '\',\'' . $tpv_item_title . '\',\'' . $pizzalayer_list_item_image . '\');">
<li id="menu-pizzalayer-topping-' . $tpv_item_slug . '" class="pizzalayer-topping pizzalayer-topping-style-' . $pizzalayer_list_item_style . ' col-lg-12 col-md-12 col-sm-12"><div class="pizzalayer-inner-tile">
<div class="col-sm-3 pizzalayer-topping-image-row"><img src="' . $pizzalayer_list_item_image . '"/></div>
<div class="col-sm-9 pizzalayer-topping-title">' . $tpv_item_title . '</div><div style="clear:both;"></div></div>
</li></a>';    
    
    
} else if($pizzalayer_list_item_style == 'thumbcorner'){
$pizzalayer_list_item_output = '<a href="javascript:SwapBasePizzaLayer(\'pizzalayer-base-layer-' . $tpv_query_posttype_singular . '\',\'' . $tpv_item_title . '\',\'' . $pizzalayer_list_item_image . '\');">
<li id="menu-pizzalayer-topping-' . $tpv_item_slug . '" class="pizzalayer-topping pizzalayer-topping-style-' . $pizzalayer_list_item_style . ' col-lg-6 col-md-6 col-sm-12"><div class="pizzalayer-inner-tile">
<div class="col-sm-12 pizzalayer-topping-image-row"><img src="' . $pizzalayer_list_item_image . '"/><div class="pizzalayer-topping-image-row-thumbcorner-tab-container"><div class="pizzalayer-topping-image-row-thumbcorner-tab">&nbsp;</div></div></div>
<div style="clear:both;"></div></div>
</li></a>';    
    
    
} else if($pizzalayer_list_item_style == 'thumbcircle'){
$pizzalayer_list_item_output = '<a href="javascript:SwapBasePizzaLayer(\'pizzalayer-base-layer-' . $tpv_query_posttype_singular . '\',\'' . $tpv_item_title . '\',\'' . $pizzalayer_list_item_image . '\');">
<li id="menu-pizzalayer-topping-' . $tpv_item_slug . '" class="pizzalayer-topping pizzalayer-topping-style-' . $pizzalayer_list_item_style . ' col-lg-6 col-md-6 col-sm-12"><div class="pizzalayer-inner-tile">
<div class="col-sm-12 pizzalayer-topping-image-row"><img src="' . $pizzalayer_list_item_image . '"/></div>
<div style="clear:both;"></div></div>
</li></a>';   
    
    
    
} else if($pizzalayer_list_item_style == 'labeloverthumb'){
$pizzalayer_list_item_output = '<a href="javascript:SwapBasePizzaLayer(\'pizzalayer-base-layer-' . $tpv_query_posttype_singular . '\',\'' . $tpv_item_title . '\',\'' . $pizzalayer_list_item_image . '\');">
<li id="menu-pizzalayer-topping-' . $tpv_item_slug . '" class="pizzalayer-topping pizzalayer-topping-style-' . $pizzalayer_list_item_style . ' col-lg-6 col-md-6 col-sm-12"><div class="pizzalayer-inner-tile">
<div class="col-sm-12 pizzalayer-topping-image-row" style="background-image:url(\'' . $pizzalayer_list_item_image . '\');"><div class="col-sm-12 pizzalayer-topping-title">' . $tpv_item_title . '</div><div style="clear:both;"></div></div>
</li></a>';    
    
    

} else if($pizzalayer_list_item_style == 'thumbrow'){
$pizzalayer_list_item_output = '<a href="javascript:SwapBasePizzaLayer(\'pizzalayer-base-layer-' . $tpv_query_posttype_singular . '\',\'' . $tpv_item_title . '\',\'' . $pizzalayer_list_item_image . '\');">
<li id="menu-pizzalayer-topping-' . $tpv_item_slug . '" class="col-sm-12 pizzalayer-topping pizzalayer-topping-style-' . $pizzalayer_list_item_style . '"><div class="pizzalayer-inner-tile col-sm-12">
<div class="pizzalayer-topping-title"><img src="' . $pizzalayer_list_item_image . '"/>' . $tpv_item_title . '</div><div style="clear:both;"></div></div>
</li></a>';    
    
    
    
} else if($pizzalayer_list_item_style == 'textrow'){
$pizzalayer_list_item_output = '<a href="javascript:SwapBasePizzaLayer(\'pizzalayer-base-layer-' . $tpv_query_posttype_singular . '\',\'' . $tpv_item_title . '\',\'' . $pizzalayer_list_item_image . '\');">
<li id="menu-pizzalayer-topping-' . $tpv_item_slug . '" class="col-sm-12 pizzalayer-topping pizzalayer-topping-style-' . $pizzalayer_list_item_style . '"><div class="pizzalayer-inner-tile col-sm-12">
<div class="pizzalayer-topping-title">' . $tpv_item_title . '</div><div style="clear:both;"></div></div>
</li></a>';    
    
    
    
} else if($pizzalayer_list_item_style == 'icontext'){
$pizzalayer_list_item_icon = get_field( 'pizzalayer_wp_menu_item_icon' );
if($pizzalayer_list_item_icon){$pizzalayer_list_item_icon_final = '<i class="fa fa-solid ' . $pizzalayer_list_item_icon . '"></i> ';} else {$pizzalayer_list_item_icon_final = '';};
$pizzalayer_list_item_output = '<a href="javascript:SwapBasePizzaLayer(\'pizzalayer-base-layer-' . $tpv_query_posttype_singular . '\',\'' . $tpv_item_title . '\',\'' . $pizzalayer_list_item_image . '\');">
<li id="menu-pizzalayer-topping-' . $tpv_item_slug . '" class="col-sm-12 pizzalayer-topping pizzalayer-topping-style-' . $pizzalayer_list_item_style . '"><div class="pizzalayer-inner-tile col-sm-12">
<div class="pizzalayer-topping-title">' . $pizzalayer_list_item_icon_final . $tpv_item_title . '</div><div style="clear:both;"></div></div>
</li></a>';    
    
    
    
} else if($pizzalayer_list_item_style == 'text'){
$pizzalayer_list_item_output = '<a href="javascript:SwapBasePizzaLayer(\'pizzalayer-base-layer-' . $tpv_query_posttype_singular . '\',\'' . $tpv_item_title . '\',\'' . $pizzalayer_list_item_image . '\');">
<li id="menu-pizzalayer-topping-' . $tpv_item_slug . '" class="pizzalayer-topping pizzalayer-topping-style-' . $pizzalayer_list_item_style . ' col-lg-12 col-md-12 col-sm-12">
' . $tpv_item_title . '
</li></a>';    
    
    
    
} else if($pizzalayer_list_item_style == 'appsidetrigger'){    
$pizzalayer_list_item_output = '
<li id="menu-pizzalayer-topping-' . $tpv_item_slug . '" class="col-sm-12 pizzalayer-topping pizzalayer-topping-style-' . $pizzalayer_list_item_style . '">
<div class="col-sm-2 pizzalayer-topping-apptriggers-thumb"><img src="' . $pizzalayer_list_item_image . '"/></div>
<div class="col-sm-6 pizzalayer-topping-apptriggers-title">' . $tpv_item_title . '</div>
<div class="col-sm-2 pizzalayer-topping-apptriggers-action"><a href="javascript:SwapBasePizzaLayer(\'pizzalayer-base-layer-' . $tpv_query_posttype_singular . '\',\'' . $tpv_item_title . '\',\'' . $pizzalayer_list_item_image . '\');">+</a></div>
<div style="clear:both;"></div>
</li>';    
    
    
    
} else {
$pizzalayer_list_item_output = '<a href="javascript:SwapBasePizzaLayer(\'pizzalayer-base-layer-' . $tpv_query_posttype_singular . '\',\'' . $tpv_item_title . '\',\'' . $pizzalayer_list_item_image . '\');">
<li id="menu-pizzalayer-topping-' . $tpv_item_slug . '" class="pizzalayer-topping pizzalayer-topping-style-' . $pizzalayer_list_item_style . ' col-lg-6 col-md-6 col-sm-12"><div class="pizzalayer-inner-tile">
<div class="col-sm-3 pizzalayer-topping-image-row"><img src="' . $pizzalayer_list_item_image . '"/></div>
<div class="col-sm-9 pizzalayer-topping-title">' . $tpv_item_title . '</div><div style="clear:both;"></div></div>
</li></a>';
}


do_action( 'func_pizzalayer_ui_approws_v2_item_before_return' );


return $pizzalayer_list_item_output;
} //end function

function shortcode_pizzalayer_ui_approws_v2( $atts ) {
    pizzalayer_ui_approws($atts['type'],$atts['css-class']);
} //end function








function pizzalayer_ui_approws_v2($tpv_query_posttype,$tpv_query_css_class){
global $pizzalayer_toppings_list_current_zindex;

// -- resets and declare vars
$pizzalayer_toppings_list_array_toppings = '';
$pizzalayer_crusts_list_array_crusts = '';
$pizzalayer_sauces_list_array_sauces = '';
$pizzalayer_cheeses_list_array_cheeses = '';
$pizzalayer_drizzles_list_array_drizzles = '';
$pizzalayer_cuts_list_array_cuts = '';

do_action( 'func_pizzalayer_ui_approws_v2_start' );

// -- post type
// options: crusts,sauces,cheeses,toppings,drizzles,cuts
if($tpv_query_posttype){$tpv_query_posttype_final = $tpv_query_posttype;} else { return '';};

// -- css
if($tpv_query_css_class){$tpv_query_css_class_final = ' ' . $tpv_query_css_class;} else { $tpv_query_css_class_final = '';};

// -- get global options and create variables for use
$pizzalayer_global_option_pagination = get_option('pizzalayer_setting_template_glass_pagination');
if(!$pizzalayer_global_option_pagination){$pizzalayer_global_option_pagination = '-1';};

// -- query loop arguments
$args = array(  
'post_type' => array('pizzalayer_' . $tpv_query_posttype_final),
'post_status' => 'publish',
'posts_per_page' => $pizzalayer_global_option_pagination, 
'orderby' => 'title', 
'order' => 'ASC',
);

// -- ui css class

// -- get cpt posts and build
do_action( 'func_pizzalayer_ui_approws_v2_before_loop' );


$loop = new WP_Query( $args );        
while ( $loop->have_posts() ) : $loop->the_post();

$pztp_tli_short_slug = get_post_field( 'post_name', get_post() );
$pztp_tli_link_remove_layer = '<a id="pztp-button-remove-' . $pztp_tli_short_slug . '" class="pztp-ui-button" href="javascript:RemovePizzaLayer(\'pizzalayer-topping-' . $pztp_tli_short_slug . '\',\'\',\'' . $pztp_tli_short_slug . '\');">Remove</a>';    


// crusts ============================
if ($tpv_query_posttype_final == 'crusts'){
$pizzalayer_crusts_list_array_crusts .= pizzalayer_ui_approws_v2_item('crust',get_the_title(),get_post_field('post_name'));
};


// sauces ============================
if ($tpv_query_posttype_final == 'sauces'){
$pizzalayer_sauces_list_array_sauces .= pizzalayer_ui_approws_v2_item('sauce',get_the_title(),get_post_field('post_name'));  
};


// cheeses ============================
if ($tpv_query_posttype_final == 'cheeses'){
$pizzalayer_cheeses_list_array_cheeses .= pizzalayer_ui_approws_v2_item('cheese',get_the_title(),get_post_field('post_name'));  
};


// drizzles ============================
if ($tpv_query_posttype_final == 'drizzles'){
$pizzalayer_drizzles_list_array_drizzles .= pizzalayer_ui_approws_v2_item('drizzle',get_the_title(),get_post_field('post_name'));  
};


// cuts (aka slices/slicing) ============================
if ($tpv_query_posttype_final == 'cuts'){
$pizzalayer_cuts_list_array_cuts .= pizzalayer_ui_approws_v2_item('cut',get_the_title(),get_post_field('post_name'));  
};


// end post 
endwhile; //end main cpt loop
wp_reset_postdata(); 

// ==== Finally, structure and return the results by cpt type

do_action( 'func_pizzalayer_ui_approws_v2_before_return' );

// Return : crusts
if ($tpv_query_posttype_final == 'crusts'){
return '<ul class="pizzalayer-crusts-list pizzalayer-ui-menu-approws' . $tpv_query_css_class_final . '">' . $pizzalayer_crusts_list_array_crusts . '</ul><div style="clear:both;"></div>';
};
// Return : sauces
if ($tpv_query_posttype_final == 'sauces'){
return '<ul class="pizzalayer-sauces-list pizzalayer-ui-menu-approws' . $tpv_query_css_class_final . '">' . $pizzalayer_sauces_list_array_sauces . '</ul><div style="clear:both;"></div>';
};
// Return : cheeses
if ($tpv_query_posttype_final == 'cheeses'){
return '<ul class="pizzalayer-cheeses-list pizzalayer-ui-menu-approws' . $tpv_query_css_class_final . '">' . $pizzalayer_cheeses_list_array_cheeses . '</ul><div style="clear:both;"></div>';
};
// Return : drizzles
if ($tpv_query_posttype_final == 'drizzles'){
return '<ul class="pizzalayer-drizzles-list pizzalayer-ui-menu-approws' . $tpv_query_css_class_final . '">' . $pizzalayer_drizzles_list_array_drizzles . '</ul><div style="clear:both;"></div>';
};
// Return : cuts
if ($tpv_query_posttype_final == 'cuts'){
return '<ul class="pizzalayer-cuts-list pizzalayer-ui-menu-approws' . $tpv_query_css_class_final . '">' . $pizzalayer_cuts_list_array_cuts . '</ul><div style="clear:both;"></div>';
};

}

/*  ======================================================== */
/*  ==== UE : LIST : TOPPINGS ============================== */

function pizzalayer_ui_approws_toppings($tpv_query_posttype,$tpv_query_css_class){
// -- resets
$pizzalayer_toppings_list_array_toppings = '';

// -- css
if($tpv_query_css_class){$tpv_query_css_class_final = ' ' . $tpv_query_css_class;} else { $tpv_query_css_class_final = '';};

// -- get global options and create variables for use
global $pizzalayer_template_images_directory;
global $pizzalayer_toppings_list_current_zindex;

$pizzalayer_global_option_pagination = get_option('pizzalayer_setting_template_glass_pagination');
$pizzalayer_global_option_element_style_toppings = get_option('pizzalayer_setting_element_style_toppings');
$pizzalayer_global_option_element_style_topping_choice_menu = get_option('pizzalayer_setting_element_style_topping_choice_menu');
$pizzalayer_global_option_element_style_layers = get_option('pizzalayer_setting_element_style_layers');

$pizzalayer_global_option_element_style_topping_fractions = get_option('pizzalayer_setting_topping_fractions');

do_action( 'func_pizzalayer_ui_approws_toppings_after_vars' );

// -- query loop arguments
$args = array(  
'post_type' => array('pizzalayer_toppings'),
'post_status' => 'publish',
'posts_per_page' => $pizzalayer_global_option_pagination, 
'orderby' => 'title', 
'order' => 'ASC',
);

// -- ui css class
    
// -- get cpt posts and build
$loop = new WP_Query( $args );        
while ( $loop->have_posts() ) : $loop->the_post();

// -----------------------------------------------------
//topping - basic variables for use in topping templates
$pztp_tli_image = get_field( 'topping_layer_image' ); //the layer image for the visualizer
$pztp_tli_image_thumbnail = get_field( 'topping_list_image' ); //the thumbnail image for the topping displays
if($pztp_tli_image_thumbnail){ $pztp_tli_image_final = $pztp_tli_image_thumbnail; } else { $pztp_tli_image_final = $pztp_tli_image; };
$pztp_tli_image_featured = '<img src="' . $pztp_tli_image_final . '" class="pizzalayer-topping-img" />';
$pztp_tli_short = get_the_title();
$pztp_tli_topping_title = '<div class="pizzalayer-topping-title">' . $pztp_tli_short . '</div>';
$pztp_tli_short_slug = sanitize_title($pztp_tli_short);
$PizzalayerControlID = 'halfcontrol-' . $pztp_tli_short_slug;
$ToppingLayerID = 'pizzalayer-topping-' . $pztp_tli_short_slug;

do_action( 'func_pizzalayer_ui_approws_toppings_in_loop_after_vars' );

// -----------------------------------------------------
//topping - create URLs for use

$pztp_tli_link_add_layer = 'javascript:AddPizzaLayer(\'' . $pizzalayer_toppings_list_current_zindex . '\',\'' . 
$pztp_tli_short_slug . '\',\'' . $pztp_tli_image . '\',\'' . $pztp_tli_short . '\',\'pizzalayer-topping-' . $pztp_tli_short_slug . '\',\'pizzalayer-topping-' . $pztp_tli_short . '\');';

$pztp_tli_link_remove_layer = 'javascript:RemovePizzaLayer(\'pizzalayer-topping-' . $pztp_tli_short_slug . '\',\'\',\'' . $pztp_tli_short_slug . '\');';

$pztp_tli_link_add_layer_link = 'javascript:AddPizzaLayer(\'' . $pizzalayer_toppings_list_current_zindex . '\',\'' . 
$pztp_tli_short_slug . '\',\'' . $pztp_tli_image . '\',\'' . $pztp_tli_short . '\',\'pizzalayer-topping-' . $pztp_tli_short_slug . '\',\'pizzalayer-topping-' . $pztp_tli_short . '\');';

$pztp_tli_link_add_layer_link_for_js = 'AddPizzaLayer(\'' . $pizzalayer_toppings_list_current_zindex . '\',\'' . 
$pztp_tli_short_slug . '\',\'' . $pztp_tli_image . '\',\'' . $pztp_tli_short . '\',\'pizzalayer-topping-' . $pztp_tli_short_slug . '\',\'pizzalayer-topping-' . $pztp_tli_short . '\')';

$pztp_tli_link_remove_layer_link_for_js = 'RemovePizzaLayer(\'pizzalayer-topping-' . $pztp_tli_short_slug . '\',\'\',\'' . $pztp_tli_short_slug . '\');';

//topping - prepare universal UI elements
$pizzalayer_halves_form_controls_fractions_images_otherfractions = '';

//topping - fractions - halves
if($pizzalayer_global_option_element_style_topping_fractions == 'halves'){
$pizzalayer_halves_form_controls = '<div id="'. $PizzalayerControlID . '" class="pizzalayer-halves-control-radiobutton-set">
<input type="radio" id="'. $PizzalayerControlID . '-half-left" name="'. $PizzalayerControlID . '" value="half-left">D
<input type="radio" id="'. $PizzalayerControlID . '-whole" name="'. $PizzalayerControlID . '" value="whole" checked>
<input type="radio" id="'. $PizzalayerControlID . '-half-right" name="'. $PizzalayerControlID . '" value="half-right">
</div>';
$pizzalayer_halves_form_controls_fractions_box_close_button = '';
$pizzalayer_halves_form_controls_fractions_images_otherfractions .= '
<img src="' . $pizzalayer_template_images_directory . 'button-half-pizza-left.png" class="pizzalayer-halves-control pizzalayer-halves-control-half-left"  onClick="SetToppingCoverage(\'half-left\',\'' . $ToppingLayerID . '\',\'' . $pztp_tli_short_slug . '\');" />
<img src="' . $pizzalayer_template_images_directory . 'button-half-pizza-right.png" class="pizzalayer-halves-control pizzalayer-halves-control-half-right"  onClick="SetToppingCoverage(\'half-right\',\'' . $ToppingLayerID . '\',\'' . $pztp_tli_short_slug . '\');" />'; 
//topping - fractions - quarters 
} else if($pizzalayer_global_option_element_style_topping_fractions == 'quarters'){
$pizzalayer_halves_form_controls = '<div id="'. $PizzalayerControlID . '" class="pizzalayer-halves-control-radiobutton-set">
<input type="radio" id="'. $PizzalayerControlID . '-whole" name="'. $PizzalayerControlID . '" value="whole" checked>
<input type="radio" id="'. $PizzalayerControlID . '-top-left" name="'. $PizzalayerControlID . '" value="top-left">
<input type="radio" id="'. $PizzalayerControlID . '-top-right" name="'. $PizzalayerControlID . '" value="top-right">
<input type="radio" id="'. $PizzalayerControlID . '-bottom-left" name="'. $PizzalayerControlID . '" value="bottom-left">
<input type="radio" id="'. $PizzalayerControlID . '-bottom-right name="'. $PizzalayerControlID . '" value="bottom-right">
</div>';
$pizzalayer_halves_form_controls_fractions_images_otherfractions .= '
<img src="' . $pizzalayer_template_images_directory . 'button-quarter-pizza-top-left.png" id="topping-' . $pztp_tli_short_slug . '-halves-control-button-top-left" class="pizzalayer-halves-control pizzalayer-halves-control-top-left"  onClick="SetToppingCoverage(\'quarter-top-left\',\'' . $ToppingLayerID . '\',\'' . $pztp_tli_short_slug . '\');" />
<img src="' . $pizzalayer_template_images_directory . 'button-quarter-pizza-top-right.png" id="topping-' . $pztp_tli_short_slug . '-halves-control-button-top-right" class="pizzalayer-halves-control pizzalayer-halves-control-top-right"  onClick="SetToppingCoverage(\'quarter-top-right\',\'' . $ToppingLayerID . '\',\'' . $pztp_tli_short_slug . '\');" />
<img src="' . $pizzalayer_template_images_directory . 'button-quarter-pizza-bottom-left.png"  id="topping-' . $pztp_tli_short_slug . '-halves-control-button-bottom-left" class="pizzalayer-halves-control pizzalayer-halves-control-bottom-left"  onClick="SetToppingCoverage(\'quarter-bottom-left\',\'' . $ToppingLayerID . '\',\'' . $pztp_tli_short_slug . '\');" />
<img src="' . $pizzalayer_template_images_directory . 'button-quarter-pizza-bottom-right.png"  id="topping-' . $pztp_tli_short_slug . '-halves-control-button-bottom-right" class="pizzalayer-halves-control pizzalayer-halves-control-bottom-right"  onClick="SetToppingCoverage(\'quarter-bottom-right\',\'' . $ToppingLayerID . '\',\'' . $pztp_tli_short_slug . '\');" />';
$pizzalayer_halves_form_controls_fractions_box_close_button = '<img src="' . $pizzalayer_template_images_directory . 'button-topping-close-fraction-box.png" id="topping-' . $pztp_tli_short_slug . '-halves-control-button-close" class="pizzalayer-halves-control pizzalayer-halves-control-bottom-right"  onClick="CloseToppingFractionBox(\'' . $pztp_tli_short_slug . '\');" />';
};


//topping - fractions - assemble UI elements
$pizzalayer_halves_form_controls_fractions_images = '<img src="' . $pizzalayer_template_images_directory . 'button-half-pizza-whole.png" class="pizzalayer-halves-control pizzalayer-halves-control-whole"  onClick="SetToppingCoverage(\'whole\',\'' . $ToppingLayerID . '\');" />' . $pizzalayer_halves_form_controls_fractions_images_otherfractions . $pizzalayer_halves_form_controls_fractions_box_close_button;

$pizzalayer_halves_form_controls_fractions_images_selected = '<img src="' . $pizzalayer_template_images_directory . 'button-half-pizza-whole.png" id="topping-fraction-thumb-' . $pztp_tli_short_slug . '" class="pizzalayer-halves-control pizzalayer-halves-control-whole"  onClick="OpenToppingFractionBox(\'' . $pztp_tli_short_slug . '\');" />';



//====== choice menu type : default =====
if($pizzalayer_global_option_element_style_topping_choice_menu == 'default' || $pizzalayer_global_option_element_style_topping_choice_menu == ''){
$pizzalayer_halves_visual_input = '<div id="pizzalayer-halves-control-' . $PizzalayerControlID . '" class="pizzalayer-halves-control pizzalayer-halves-control-type-default col-sm-12">' . $pizzalayer_halves_form_controls_fractions_images . '
<img src="' . $pizzalayer_template_images_directory . 'add-to-pizza.png" id="pztp-button-add-' . $pztp_tli_short_slug . '" class="pzpt-ui-button-add"  onClick="' . $pztp_tli_link_add_layer_link_for_js . '" />
<img src="' . $pizzalayer_template_images_directory . 'trash.png" id="pztp-button-remove-' . $pztp_tli_short_slug . '" class="pzpt-ui-button-remove"  onClick="' . $pztp_tli_link_remove_layer_link_for_js . '" />
</div>';
;

//====== choice menu type : minimal =====
} else if($pizzalayer_global_option_element_style_topping_choice_menu == 'minimal'){
$pizzalayer_halves_visual_input = '<div id="pizzalayer-halves-control-' . $PizzalayerControlID . '" class="pizzalayer-halves-control pizzalayer-halves-control-type-minimal col-sm-12">
' . $pizzalayer_halves_form_controls_fractions_images . '
<img src="' . $pizzalayer_template_images_directory . 'add-to-pizza.png" id="pztp-button-add-' . $pztp_tli_short_slug . '" class="pzpt-ui-button-add"  onClick="' . $pztp_tli_link_add_layer_link_for_js . '" />
<img src="' . $pizzalayer_template_images_directory . 'trash.png" id="pztp-button-remove-' . $pztp_tli_short_slug . '" class="pzpt-ui-button-remove"  onClick="' . $pztp_tli_link_remove_layer_link_for_js . '" />
</div>';

//====== choice menu type : icon w/fraction =====
} else if($pizzalayer_global_option_element_style_topping_choice_menu == 'iconwfraction'){
$pizzalayer_halves_visual_input = '<div id="pizzalayer-halves-control-' . $PizzalayerControlID . '" class="pizzalayer-halves-control pizzalayer-halves-control-type-iconwfraction col-sm-12">
<div class="pizzalayer-halves-control-col-left col-sm-2"><img src="' . $pizzalayer_template_images_directory . 'trash.png" id="pztp-button-remove-' . $pztp_tli_short_slug . '" class="pzpt-ui-button-remove"  onClick="' . $pztp_tli_link_remove_layer_link_for_js . '" /><img src="' . $pizzalayer_template_images_directory . 'add-to-pizza.png" id="pztp-button-add-' . $pztp_tli_short_slug . '" class="pzpt-ui-button-add"  onClick="' . $pztp_tli_link_add_layer_link_for_js . '" /></div>
<div class="pizzalayer-halves-control-col-left col-sm-8">' . $pizzalayer_halves_form_controls_fractions_images_selected . '</div>
<div class="pizzalayer-halves-control-col-left col-sm-2">
</div>
<div style="width:100%;clear:both;"></div>
</div>
<div id="pizzalayer-halves-control-fraction-' . $pztp_tli_short_slug . '" class="pizzalayer-halves-control pizzalayer-halves-control-fraction pizzalayer-halves-control-type-iconwfraction col-sm-12">' . $pizzalayer_halves_form_controls_fractions_images . '</div>';

//====== choice menu type : icon no fraction =====
} else if($pizzalayer_global_option_element_style_topping_choice_menu == 'iconnofraction'){
$pizzalayer_halves_visual_input = '<div id="pizzalayer-halves-control-' . $PizzalayerControlID . '" class="pizzalayer-halves-control pizzalayer-halves-control-type-iconnofraction col-sm-12">
<img src="' . $pizzalayer_template_images_directory . 'add-to-pizza.png" id="pztp-button-add-' . $pztp_tli_short_slug . '" class="pzpt-ui-button-add"  onClick="' . $pztp_tli_link_add_layer_link_for_js . '" />
<img src="' . $pizzalayer_template_images_directory . 'trash.png" id="pztp-button-remove-' . $pztp_tli_short_slug . '" class="pzpt-ui-button-remove"  onClick="' . $pztp_tli_link_remove_layer_link_for_js . '" />
</div>';
};

// -----------------------------------------------------
//topping - render the topping with the element style needed



//------ topping style - Default
if($pizzalayer_global_option_element_style_toppings == 'default' || $pizzalayer_global_option_element_style_toppings == ''){
$pizzalayer_toppings_list_array_toppings .= '
<li id="menu-pizzalayer-topping-' . $pztp_tli_short_slug . '" class="pizzalayer-topping col-sm-6 pizzalayer-topping-style-topui-' . $pizzalayer_global_option_element_style_toppings . ' pizzalayer-topping-menu-style-' . $pizzalayer_global_option_element_style_topping_choice_menu. '"><div class="pizzalayer-inner-tile"><a class="pizzatopper-approw-item-link" href="' . $pztp_tli_link_add_layer_link . '">
<div class="col-sm-12 pizzalayer-topping-image-row">' . $pztp_tli_image_featured . '</div>
<div class="col-sm-12 pizzalayer-topping-title-row">' . $pztp_tli_topping_title . '</div></a>
<div class="col-sm-12 pizzalayer-topping-fraction"><div class="col-sm-12">' . $pizzalayer_halves_visual_input . $pizzalayer_halves_form_controls . '</div></div><div style="clear:both;"></div></div>
</li>';
}; // end if


//------ topping style - Control Box
if($pizzalayer_global_option_element_style_toppings == 'controlbox'){
$pizzalayer_toppings_list_array_toppings .= '
<li id="menu-pizzalayer-topping-' . $pztp_tli_short_slug . '" class="pizzalayer-topping col-sm-6 pizzalayer-topping-style-topui-' . $pizzalayer_global_option_element_style_toppings . ' pizzalayer-topping-menu-style-' . $pizzalayer_global_option_element_style_topping_choice_menu. '">CONTROLBOX<div class="pizzalayer-inner-tile"><a class="pizzatopper-approw-item-link" href="' . $pztp_tli_link_add_layer_link . '">
<div class="col-sm-12 pizzalayer-topping-image-row">' . $pztp_tli_image_featured . '</div>
<div class="col-sm-12 pizzalayer-topping-title-row">' . $pztp_tli_topping_title . '</div></a>
<div class="col-sm-12 pizzalayer-topping-fraction"><div class="col-sm-12">' . $pizzalayer_halves_visual_input . $pizzalayer_halves_form_controls . '</div></div><div style="clear:both;"></div></div>
</li>';
}; // end if



//------ topping style - Thumb Corner
if($pizzalayer_global_option_element_style_toppings == 'thumbcorner'){
$pizzalayer_toppings_list_array_toppings .= '
<li id="menu-pizzalayer-topping-' . $pztp_tli_short_slug . '" class="pizzalayer-topping pizzalayer-topping-style-topui-' . $pizzalayer_list_item_style . ' pizzalayer-topping-style-topui-' . $pizzalayer_global_option_element_style_toppings . ' col-lg-6 col-md-6 col-sm-12 pizzalayer-topping-menu-style-' . $pizzalayer_global_option_element_style_topping_choice_menu. '"><div class="pizzalayer-inner-tile">
<a href="javascript:PTtoggleToppingPopup(\'' . $pztp_tli_short_slug . '\')"><div class="col-sm-12 pizzalayer-topping-image-row">' . $pztp_tli_image_featured . '<div class="pizzalayer-topping-image-row-thumbcorner-tab-container"><div class="pizzalayer-topping-image-row-thumbcorner-tab">&nbsp;</div></div></div></a>
<div class="col-sm-12 pizzalayer-topping-fraction"><div class="col-sm-12">' . $pizzalayer_halves_visual_input . $pizzalayer_halves_form_controls . '</div></div>
<div style="clear:both;"></div></div>
</li>
';
}; // end if



//------ topping style - Background Toggle
if($pizzalayer_global_option_element_style_toppings == 'bgtoggle'){
$pizzalayer_toppings_list_array_toppings .= '
<li id="menu-pizzalayer-topping-' . $pztp_tli_short_slug . '" class="pizzalayer-topping pizzalayer-topping-style-topui-' . $pizzalayer_list_item_style . ' pizzalayer-topping-style-topui-' . $pizzalayer_global_option_element_style_toppings . ' col-lg-6 col-md-6 col-sm-12 pizzalayer-topping-menu-style-' . $pizzalayer_global_option_element_style_topping_choice_menu. '"> <div class="pizzalayer-inner-tile" style="background-image:url(\'' . $pztp_tli_image_final . '\');">
<a href="javascript:PTtoggleToppingPopup(\'' . $pztp_tli_short_slug . '\')"><div class="col-sm-12 pizzalayer-topping-image-row"></div></a>
<div class="col-sm-12 pizzalayer-topping-fraction"><div class="col-sm-12">' . $pizzalayer_halves_visual_input . $pizzalayer_halves_form_controls . '</div></div>
<div style="clear:both;"></div></div>
</li>';
}; // end if



//------ topping style - Modern Offset
if($pizzalayer_global_option_element_style_toppings == 'modern'){
$pizzalayer_toppings_list_array_toppings .= '
<li id="menu-pizzalayer-topping-' . $pztp_tli_short_slug . '" class="pizzalayer-topping col-sm-12 pizzalayer-topping-style-topui-' . $pizzalayer_global_option_element_style_toppings . ' pizzalayer-topping-menu-style-' . $pizzalayer_global_option_element_style_topping_choice_menu. '">
<div class="pizzalayer-inner-tile">
<div class="col-sm-8 pizzalayer-topping-title-fraction-row">' . $pztp_tli_topping_title . $pizzalayer_halves_visual_input . $pizzalayer_halves_form_controls . '</div>
<div class="col-sm-4 pizzalayer-topping-image-row"><div class="col-sm-12">' . $pztp_tli_image_featured . '</div></div><div style="clear:both;"></div>
</div>
</li>';
}; // end if



//------ topping style - Corner Tag
if($pizzalayer_global_option_element_style_toppings == 'cornertag'){
$pizzalayer_toppings_list_array_toppings .= '
<li id="menu-pizzalayer-topping-' . $pztp_tli_short_slug . '" class="pizzalayer-topping pizzalayer-topping-style-topui-' . $pizzalayer_list_item_style . ' pizzalayer-topping-style-topui-' . $pizzalayer_global_option_element_style_toppings . ' col-lg-12 col-md-12 col-sm-12 pizzalayer-topping-menu-style-' . $pizzalayer_global_option_element_style_topping_choice_menu. '"> <div class="pizzalayer-inner-tile" style="background-image:url(\'' . $pztp_tli_image_final . '\');">
<a href="javascript:PTtoggleToppingPopup(\'' . $pztp_tli_short_slug . '\')"><div class="col-sm-12 pizzalayer-topping-image-row"><div class="col-sm-12 pizzalayer-topping-title-row"><a class="pizzatopper-approw-item-link" href="' . $pztp_tli_link_add_layer_link . '">' . $pztp_tli_topping_title . '</a></div></div></a>
<div class="col-sm-12 pizzalayer-topping-fraction"><div class="col-sm-12">' . $pizzalayer_halves_visual_input . $pizzalayer_halves_form_controls . '</div></div>
<div style="clear:both;"></div></div>
</li>';
}; // end if



//------ topping style - App Add
if($pizzalayer_global_option_element_style_toppings == 'appadd'){
$pizzalayer_toppings_list_array_toppings .= '
<li id="menu-pizzalayer-topping-' . $pztp_tli_short_slug . '" class="pizzalayer-topping col-sm-12 pizzalayer-topping-style-topui-' . $pizzalayer_global_option_element_style_toppings . ' pizzalayer-topping-menu-style-' . $pizzalayer_global_option_element_style_topping_choice_menu. '"><div class="pizzalayer-inner-tile">

<div class="col-sm-3 pizzalayer-topping-fraction"><div class="col-sm-12">' . $pizzalayer_halves_visual_input . $pizzalayer_halves_form_controls . '</div></div>

<div class="col-sm-9 pizzalayer-topping-title-row"><a class="pizzatopper-approw-item-link" href="' . $pztp_tli_link_add_layer_link . '">' . $pztp_tli_topping_title . '</a></div>
<div style="clear:both;"></div></div>
</li>';
}; // end if


    
do_action( 'func_pizzalayer_ui_approws_toppings_in_loop_end_of_item' );


// end post 
endwhile; //end main cpt loop
wp_reset_postdata(); 

do_action( 'func_pizzalayer_ui_approws_toppings_before_return' );
// ==== Finally, structure and return the results by cpt type
return '<ul class="pizzalayer-toppings-list-linkboxes pizzalayer-ui-menu-approws' . $tpv_query_css_class_final . ' pizzalayer-toppings-style-' . $pizzalayer_global_option_element_style_toppings . ' pizzalayer-layers-style-' . $pizzalayer_global_option_element_style_layers . '">' . $pizzalayer_toppings_list_array_toppings . '</ul>';
}

do_action( 'pizzalayer_file_topper-ui-lists_end' );
