<?php
global $pizzalayer_path_images;


/* lists - unstyled bulleted */

function pizzalayer_tpv_toppings_list(){ 
$pizzalayer_toppings_list_array_toppings = '';
$args = array(  
'post_type' => array('pizzalayer_toppings'),
'post_status' => 'publish',
'posts_per_page' => -1, 
'orderby' => 'title', 
'order' => 'ASC',
);
$pizzalayer_toppings_list_current_zindex = 900;
$loop = new WP_Query( $args );        
while ( $loop->have_posts() ) : $loop->the_post();
// start topping    
$pztp_tli_short = get_the_title();
$pztp_tli_short_slug = sanitize_title($pztp_tli_short);
$pztp_tli_image = get_field( 'topping_layer_image' );
$pizzalayer_toppings_list_current_zindex += 20;

$pztp_tli_link_add_layer = ' <a id="pztp-button-add-' . $pztp_tli_short_slug . '" class="pzpt-ui-button" href="javascript:AddPizzaLayer(\'' . $pizzalayer_toppings_list_current_zindex . '\',\'' . $pztp_tli_short . '\',\'' . $pztp_tli_image . '\',\'' . $pztp_tli_short . '\',\'pizzalayer-topping-' . $pztp_tli_short_slug . '\');">Add</a>';
$pztp_tli_link_remove_layer = '<a id="pztp-button-remove-' . $pztp_tli_short_slug . '" class="pztp-ui-button" href="javascript:RemovePizzaLayer(\'pizzalayer-topping-' . $pztp_tli_short_slug . '\',\'\',\'' . $pztp_tli_short_slug . '\');">Remove</a>';    

$pizzalayer_toppings_list_array_toppings .= '<li>' . $pztp_tli_short . ' - ' . $pztp_tli_link_add_layer . ' | ' . $pztp_tli_link_remove_layer . '</li>';
// end topping
endwhile;
wp_reset_postdata();
return '<ul class="pizzalayer-toppings-list-linkboxes">' . $pizzalayer_toppings_list_array_toppings . '</ul>';
}

/*  ========================================================= */

function pizzalayer_tpv_toppings_list_linkboxes(){ 
$pizzalayer_toppings_list_array_toppings = '';
$args = array(  
'post_type' => array('pizzalayer_toppings'),
'post_status' => 'publish',
'posts_per_page' => -1, 
'orderby' => 'title', 
'order' => 'ASC',
);
$pizzalayer_toppings_list_current_zindex = 900;
$loop = new WP_Query( $args );        
while ( $loop->have_posts() ) : $loop->the_post();
// start topping
$pztp_tli_image = get_field( 'topping_layer_image' ); //the layer image for the visualizer
$pztp_tli_image_thumbnail = get_field( 'topping_list_image' ); //the thumbnail image for the topping displays
if($pztp_tli_image_thumbnail){ 
$pztp_tli_image_final = $pztp_tli_image_thumbnail;
} else {
$pztp_tli_image_final = $pztp_tli_image;
}
$pztp_tli_image_featured = '<div class="pizzalayer-topping-image-row col-sm-12"><img src="' . $pztp_tli_image_final . '" class="pizzalayer-topping-img"></div>';
$pztp_tli_short = get_the_title();
$pztp_tli_topping_title = '<div class="pizzalayer-topping-title">' . $pztp_tli_short . '</div>';
$pztp_tli_short_slug = sanitize_title($pztp_tli_short);
$pizzalayer_toppings_list_current_zindex += 20;
$pztp_tli_link_add_layer = ' <a id="pztp-button-add-' . $pztp_tli_short_slug . '" class="pzpt-ui-button" href="javascript:AddPizzaLayer(\'' . $pizzalayer_toppings_list_current_zindex . '\',\'' . 
$pztp_tli_short . '\',\'' . $pztp_tli_image . '\',\'' . $pztp_tli_short . '\',\'pizzalayer-topping-' . $pztp_tli_short_slug . '\');"><i class="fa fa-pizza-slice"></i> Add</a>';
$pztp_tli_link_remove_layer = '<a id="pztp-button-remove-' . $pztp_tli_short_slug . '" class="pzpt-ui-button" href="javascript:RemovePizzaLayer(\'pizzalayer-topping-' . $pztp_tli_short_slug . '\');"><i class="fa fa-trash"></i></a>';
$pizzalayer_toppings_list_array_toppings .= '<li id="pizzalayer-topping-' . $pztp_tli_short . '" class="pizzalayer-topping col-lg-6 col-md-12 col-sm-12">' . $pztp_tli_image_featured . $pztp_tli_topping_title . '<div class="pizzalayer-topping-actions">' . $pztp_tli_link_add_layer . '&nbsp;' . $pztp_tli_link_remove_layer . '</div></li>';
// end topping
endwhile;
wp_reset_postdata();
return '<ul class="pizzalayer-toppings-list-linkboxes">' . $pizzalayer_toppings_list_array_toppings . '<li style="clear:both;width:100%;padding:0;margin:0;"></li></ul>';
}


/*  ========================================================= */


function pizzalayer_tpv_toppings_list_linkrows(){ 
$pizzalayer_toppings_list_array_toppings = '';
$args = array(  
'post_type' => array('pizzalayer_toppings'),
'post_status' => 'publish',
'posts_per_page' => -1, 
'orderby' => 'title', 
'order' => 'ASC',
);
$pizzalayer_toppings_list_current_zindex = 900;
$loop = new WP_Query( $args );        
while ( $loop->have_posts() ) : $loop->the_post();
// start topping
$pztp_tli_image = get_field( 'topping_layer_image' ); //the layer image for the visualizer
$pztp_tli_image_thumbnail = get_field( 'topping_list_image' ); //the thumbnail image for the topping displays
if($pztp_tli_image_thumbnail){ 
$pztp_tli_image_final = $pztp_tli_image_thumbnail;
} else {
$pztp_tli_image_final = $pztp_tli_image;
}    
$pztp_tli_image_featured = '<img src="' . $pztp_tli_image_final . '" class="pizzalayer-topping-img" />';
$pztp_tli_short = get_the_title();
$pztp_tli_topping_title = '<div class="pizzalayer-topping-title">' . $pztp_tli_short . '</div>';
$pztp_tli_short_slug = sanitize_title($pztp_tli_short);
$pizzalayer_toppings_list_current_zindex += 20;
    
$pztp_tli_link_add_layer = ' <a id="pztp-button-add-' . $pztp_tli_short_slug . '" class="pzpt-ui-button" href="javascript:AddPizzaLayer(\'' . $pizzalayer_toppings_list_current_zindex . '\',\'' . 
$pztp_tli_short_slug . '\',\'' . $pztp_tli_image . '\',\'' . $pztp_tli_short . '\',\'pizzalayer-topping-' . $pztp_tli_short_slug . '\',\'pizzalayer-topping-' . $pztp_tli_short . '\');"><i class="fa fa-pizza-slice"></i></a>';

$pztp_tli_link_add_layer_link = 'javascript:AddPizzaLayer(\'' . $pizzalayer_toppings_list_current_zindex . '\',\'' . 
$pztp_tli_short_slug . '\',\'' . $pztp_tli_image . '\',\'' . $pztp_tli_short . '\',\'pizzalayer-topping-' . $pztp_tli_short_slug . '\',\'pizzalayer-topping-' . $pztp_tli_short . '\');';
    
$pztp_tli_link_remove_layer = '<a id="pztp-button-remove-' . $pztp_tli_short_slug . '" class="pzpt-ui-button" href="javascript:RemovePizzaLayer(\'pizzalayer-topping-' . $pztp_tli_short_slug . '\',\'\',\'' . $pztp_tli_short_slug . '\');"><i class="fa fa-trash"></i></a>';
    
$pizzalayer_toppings_list_array_toppings .= '<a href="' . $pztp_tli_link_add_layer_link . '">
<li id="menu-pizzalayer-topping-' . $pztp_tli_short_slug . '" class="pizzalayer-topping col-lg-12 col-md-12 col-sm-12">
<div class="col-sm-3 pizzalayer-topping-image-row">' . $pztp_tli_image_featured . '</div>
<div class="col-sm-7 pizzalayer-topping-title">' . $pztp_tli_topping_title . '</div>
<div class="col-sm-2 pizzalayer-topping-actions">' . $pztp_tli_link_add_layer . $pztp_tli_link_remove_layer . '</div>
</li></a>';
// end topping
endwhile;
wp_reset_postdata();
return '<ul class="pizzalayer-toppings-list-linkboxes">' . $pizzalayer_toppings_list_array_toppings . '<li style="clear:both;width:100%;padding:0;margin:0;"></li></ul>';
}


/*  ========================================================= */


function pizzalayer_tpv_sauces_list(){ 
$pizzalayer_sauces_list_array_sauces = '';
$args = array(
'post_type' => array('pizzalayer_sauces'),
'post_status' => 'publish',
'posts_per_page' => -1, 
'orderby' => 'title', 
'order' => 'ASC',
);    
    
$loop = new WP_Query( $args );        
while ( $loop->have_posts() ) : $loop->the_post();
$pizzalayer_sauces_list_item_image = get_field( 'sauce_layer_image' );
$pizzalayer_sauces_list_array_sauces .= '<a href="javascript:SwapBasePizzaLayer(\'pizzalayer-base-layer-sauce\',\'' . get_the_title() . '\',\'' . $pizzalayer_sauces_list_item_image . '\');"><li class="col-md-3 col-sm-12"><div class="image-preview"><img src="' . $pizzalayer_sauces_list_item_image . '"/></div><div class="text-center">' . get_the_title() . '</div></a></li>';     
endwhile;
wp_reset_postdata();    
return '<ul>' . $pizzalayer_sauces_list_array_sauces . '</ul><div style="clear:both;"></div>';
}


/*  ========================================================= */


function pizzalayer_tpv_cheeses_list(){ 
$pizzalayer_cheeses_list_array_cheeses = '';
$args = array(  
'post_type' => array('pizzalayer_cheeses'),
'post_status' => 'publish',
'posts_per_page' => -1, 
'orderby' => 'title', 
'order' => 'ASC',
);
$loop = new WP_Query( $args );        
while ( $loop->have_posts() ) : $loop->the_post();
$pizzalayer_cheeses_list_item_image = get_field( 'cheese_layer_image' );
$pizzalayer_cheeses_list_array_cheeses .= '<a href="javascript:SwapBasePizzaLayer(\'pizzalayer-base-layer-cheese\',\'' . get_the_title() . '\',\'' . $pizzalayer_cheeses_list_item_image . '\');"><li class="col-md-3 col-sm-12"><div class="image-preview"><img src="' . $pizzalayer_cheeses_list_item_image . '"/></div><div class="text-center">' . get_the_title() . '</li></a>';
endwhile;
wp_reset_postdata();    
return '<ul class="pizzalayer-toppings-list">' . $pizzalayer_cheeses_list_array_cheeses . '</ul><div style="clear:both;"></div>';
}


/*  ========================================================= */


function pizzalayer_tpv_cuts_list(){ 
$pizzalayer_cuts_list_array_cuts = '';
$args = array(  
'post_type' => array('pizzalayer_cuts'),
'post_status' => 'publish',
'posts_per_page' => -1, 
'orderby' => 'title', 
'order' => 'ASC',
);
$loop = new WP_Query( $args );        
while ( $loop->have_posts() ) : $loop->the_post();
$pizzalayer_cuts_list_item_image = get_field( 'cut_layer_image' );
$pizzalayer_cuts_list_array_cuts .= '<a href="javascript:SwapBasePizzaLayer(\'pizzalayer-topping-cut\',\'' . get_the_title() . '\',\'' . $pizzalayer_cuts_list_item_image . '\');"><li class="col-md-3 col-sm-12"><div class="image-preview"><img src="' . $pizzalayer_cuts_list_item_image . '"/></div><div class="text-center">' . get_the_title() . '</div></li></a>';
endwhile;
wp_reset_postdata();    
return '<ul class="pizzalayer-toppings-list">' . $pizzalayer_cuts_list_array_cuts . '</ul><div style="clear:both;"></div>';
}


/*  ========================================================= */


function pizzalayer_tpv_drizzles_list(){ 
$pizzalayer_drizzles_list_array_drizzles = '';
$args = array(  
'post_type' => array('pizzalayer_drizzles'),
'post_status' => 'publish',
'posts_per_page' => -1, 
'orderby' => 'title', 
'order' => 'ASC',
);
$loop = new WP_Query( $args );        
while ( $loop->have_posts() ) : $loop->the_post();
$pizzalayer_drizzles_list_item_image = get_field( 'drizzle_layer_image' );
$pizzalayer_drizzles_list_array_drizzles .= '<a href="javascript:SwapBasePizzaLayer(\'pizzalayer-base-layer-drizzle\',\'' . get_the_title() . '\',\'' . $pizzalayer_drizzles_list_item_image . '\');"><li class="col-md-3 col-sm-12"><div class="image-preview"><img src="' . $pizzalayer_drizzles_list_item_image . '"/></div><div class="text-center">' . get_the_title() . '</div></li></a>';
endwhile;
wp_reset_postdata();    
return '<ul class="pizzalayer-toppings-list">' . $pizzalayer_drizzles_list_array_drizzles . '</ul><div style="clear:both;"></div>';
}


/*  ========================================================= */


function pizzalayer_tpv_crusts_list(){ 
$pizzalayer_crusts_list_array_crusts = '';
$args = array(  
'post_type' => array('pizzalayer_crusts'),
'post_status' => 'publish',
'posts_per_page' => -1, 
'orderby' => 'title', 
'order' => 'ASC',
);
$loop = new WP_Query( $args );        
while ( $loop->have_posts() ) : $loop->the_post();
$pizzalayer_crusts_list_item_image = get_field( 'crust_layer_image' );
$pizzalayer_crusts_list_array_crusts .= '<a href="javascript:SwapBasePizzaLayer(\'pizzalayer-base-layer-crust\',\'' . get_the_title() . '\',\'' . $pizzalayer_crusts_list_item_image . '\');"><li class="col-md-4 col-sm-12 text-center"><div class="image-preview"><img src="' . $pizzalayer_crusts_list_item_image . '"/></div><div class="text-center">' . get_the_title() . '</div></li></a>';     
endwhile;
wp_reset_postdata();    
return '<ul class="pizzalayer-toppings-list">' . $pizzalayer_crusts_list_array_crusts . '</ul><div style="clear:both;"></div>';
}


/*  ========================================================= */


function pizzalayer_tpv_toppings_ui(){ return 'TOPPINGS ACTIONS'; }

add_shortcode( 'pizzalayer-toppings', 'pizzalayer_tpv_toppings_list' );


/*  ========================================================= */
/*  ========================================================= */

// UNIVERSAL CONTROL ELEMENTS
/* v2 work below - multi variable layer work


/*  ==== UE : LIST : APPROWS ============================== */

function shortcode_pizzalayer_ui_approws( $atts ) {
    pizzalayer_ui_approws($atts['type'],$atts['css-class']);
};


function pizzalayer_ui_approws($tpv_query_posttype,$tpv_query_css_class){
// -- resets
$pizzalayer_toppings_list_array_toppings = '';

// -- post type
// options: crusts,sauces,cheeses,toppings,drizzles,cuts
if($tpv_query_posttype){$tpv_query_posttype_final = $tpv_query_posttype;} else {$tpv_query_posttype_final = 'toppings';};

// -- css
if($tpv_query_css_class){$tpv_query_css_class_final = ' ' . $tpv_query_css_class;} else { $tpv_query_css_class_final = '';};

// -- query loop arguments
$args = array(  
'post_type' => array('pizzalayer_' . $tpv_query_posttype_final),
'post_status' => 'publish',
'posts_per_page' => -1, 
'orderby' => 'title', 
'order' => 'ASC',
);

// -- ui css class
    
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
/*  ========================================================= */
/*  ========================================================= */

/*  ==== LIST : IMAGE BOXES ============================== */



