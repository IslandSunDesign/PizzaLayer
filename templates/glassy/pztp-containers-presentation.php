<?php
$pizzalayer_template_images_directory = plugin_dir_url(__FILE__) .'images/';


/* =============================================
PIZZALAYER : front-end UI */
function pizzalayer_toppings_visualizer_func( $atts ){
global $pizzalayer_template_name;

/* ============= GET USER OPTIONS ============= */
$pizzalayer_template_glassy_option_topping_maxtoppings = get_option('pizzalayer_setting_topping_maxtoppings');

$pizzalayer_template_glassy_option_display_header = get_option('pizzalayer_setting_template_glass_display_header');
$pizzalayer_template_glassy_option_display_myrecipe = get_option('pizzalayer_setting_template_glass_display_my_recipe');
$pizzalayer_template_glassy_option_display_toppinglist = get_option('pizzalayer_setting_template_glass_display_topping_list');
$pizzalayer_template_glassy_option_display_actionbar = get_option('pizzalayer_setting_template_glass_display_action_bar');


/* ============= BUILD LAYOUT PARTS CONDITIONALLY ============= */
if($pizzalayer_template_glassy_option_display_header == 'show'){
$pizzalayer_template_glassy_part_header_left = '<div id="pizzalayer-header-left" class="col-lg-4 col-md-12 col-sm-12">' . pizzalayer_branding_row_logo_or_byline() . '</div>';
$pizzalayer_template_glassy_part_header_middle = '<div id="pizzalayer-header-middle" class="col-lg-4 col-md-12 col-sm-12">Your branding or custom content here!</div>';
$pizzalayer_template_glassy_part_header_right = '<div id="pizzalayer-header-right" class="col-lg-4 col-md-12 col-sm-12">' . pizzalayer_icons_menu_topright() . '</div>';
$pizzalayer_template_glassy_part_header = '<div id="pizzalayer-header" class="col-sm-12 pizzalayer-ui-menu pizzalayer-ui-menu-col">' . $pizzalayer_template_glassy_part_header_left . $pizzalayer_template_glassy_part_header_middle . $pizzalayer_template_glassy_part_header_right . '</div>';
} else {
 $pizzalayer_template_glassy_part_header = '';   
};
$pizzalayer_template_glassy_part_maindisplay = '<div id="pizzalayer-main-visualizer-container" class="pizzalayer-visualizer pt-visualizer-' . $atts['id'] . ' col-md-7 col-sm-12">
' . pizzalayer_pizza_dynamic_nested($atts['id'],$atts['crust'],$atts['sauce'],$atts['toppings']) . '
</div>';

$pizzalayer_template_glassy_part_tabsmenu = '<div class="pizzalayer-tabs col-md-5 col-sm-12">' . pizzalayer_icons_menu_buttons() . pizzalayer_alert('Max : ' . $pizzalayer_template_glassy_option_topping_maxtoppings . ' Toppings','max-toppings') . pizzalayer_panels() . pizzalayer_icons_menu_user_actions() . '</div>';



if($pizzalayer_template_glassy_option_display_myrecipe == 'show'){
$pizzalayer_template_glassy_part_myrecipe = '<!-- row : my recipe -->

<div class="pizzalayer-ui-basics pizzalayer-ui-menu-col col-md-12 col-sm-12">'
. pizzalayer_topvis_title('My Recipe','pizza-slice')
. pizzalayer_selection_tile('Crust','crust','No Crust Chosen')
. pizzalayer_selection_tile('Sauce','sauce','No Sauce Chosen')
. pizzalayer_selection_tile('Cheese','cheese','No Cheese Chosen')
. pizzalayer_selection_tile('Drizzle','drizzle','No Drizzle Chosen')
. '
</div>';
} else { 
    $pizzalayer_template_glassy_part_myrecipe = '';
};



if( $pizzalayer_template_glassy_option_display_toppinglist == 'show' ){
$pizzalayer_template_glassy_part_toppinglist = '<!-- row : current toppings list -->
<div class="pizzalayer-ui-toppings pizzalayer-ui-menu-col col-md-12 col-sm-12">
<ul id="pizzalayer-current-toppings" class="horizontal"></ul>
<div style="clear:both;"></div>
</div>';
} else { 
    $pizzalayer_template_glassy_part_toppinglist = '';
};



if( $pizzalayer_template_glassy_option_display_actionbar == 'show' ){
$pizzalayer_template_glassy_part_actionbar = '<!-- row : woocommerce action bar -->' . pizzalayer_woocommerce_actions();
} else { 
$pizzalayer_template_glassy_part_actionbar = '';
};



/* ============= ASSEMBLE PARTS AND RETURN UI ============= */
return '
<!-- Pizzalayer : PIZZA DISPLAY ==================== -->
<div id="pizzalayer-ui-wrapper" class="pizzalayer-ui-wrapper pizzalayer-template-' . $pizzalayer_template_name . '">
' . pizzalayer_demo_notice() . '
<!-- Pizzalayer : PIZZA + -->
<div id="pizzalayer-ui-container" class="pizzalayer-ui-container pizzalayer-uirow">
' . $pizzalayer_template_glassy_part_header . $pizzalayer_template_glassy_part_maindisplay . $pizzalayer_template_glassy_part_tabsmenu . 
    $pizzalayer_template_glassy_part_myrecipe . 
    $pizzalayer_template_glassy_part_toppinglist . 
'</div>
' . $pizzalayer_template_glassy_part_actionbar . '
<div style="clear:both;"></div>
</div>
<!-- / Pizzalayer : PIZZA -->' . pizzalayer_javascript_hidden_vars() . pizzalayer_options_css_output() . pizzalayer_template_glass_options_css_output() . '
<div style="clear:both;"></div>
</div>
<!-- / Pizzalayer ==================== -->'; 
};

add_Shortcode( 'pizzalayer-visualizer', 'pizzalayer_toppings_visualizer_func' );








/* default static displays */
function pizzalayer_ui_wrapper_pizza_1_start(){ return '<!-- PT : PIZZA -->
<div id="pizzalayer-pizza" class="pizzalayer-pizza-static">';}

function pizzalayer_ui_wrapper_pizza_1_end(){ return '<div style="clear:both;"></div>
</div>
<!-- / PT : PIZZA -->';}

function pizzalayer_ui_wrapper_pizza_dyn_1_start(){ return '<!-- PT : PIZZA -->
<div id="pizzalayer-pizza" class="pizzalayer-pizza-dynamic">';}

function pizzalayer_ui_wrapper_pizza_dyn_1_end(){ return '<div style="clear:both;"></div>
</div>
<!-- / PT : PIZZA -->';}


function pizzalayer_ui_container_shell_1(){
    
    
    
    
}