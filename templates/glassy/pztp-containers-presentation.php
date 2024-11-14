<?php
$pizzalayer_template_images_directory = plugin_dir_url(__FILE__) .'images/';


/* =============================================
PIZZALAYER : front-end UI */
function pizzalayer_toppings_visualizer_func( $atts ){
    global $pizzalayer_template_name;
return '
<!-- Pizzalayer : PIZZA DISPLAY ==================== -->
<div id="pizzalayer-ui-wrapper" class="pizzalayer-ui-wrapper pizzalayer-template-' . $pizzalayer_template_name . '">
' . pizzalayer_demo_notice() . '
<!-- Pizzalayer : PIZZA + -->
<div id="pizzalayer-ui-container" class="pizzalayer-ui-container pizzalayer-uirow">
<!-- row zero : conditional alert row -->
<!-- row one : pizza and "my recipe" column -->
<div id="pizzalayer-header" class="col-sm-12 pizzalayer-ui-menu pizzalayer-ui-menu-col">
<div id="pizzalayer-header-left" class="col-lg-4 col-md-12 col-sm-12">' . pizzalayer_branding_row_logo_or_byline() . '</div>
<div id="pizzalayer-header-middle" class="col-lg-4 col-md-12 col-sm-12">Your branding or custom content here!</div>
<div id="pizzalayer-header-right" class="col-lg-4 col-md-12 col-sm-12">' . pizzalayer_icons_menu_topright() . '</div>
</div>
<div id="pizzalayer-main-visualizer-container" class="pizzalayer-visualizer pt-visualizer-' . $atts['id'] . ' col-md-7 col-sm-12">
' . pizzalayer_pizza_dynamic_nested($atts['id'],$atts['crust'],$atts['sauce'],$atts['toppings']) . '
</div><div class="pizzalayer-tabs col-md-5 col-sm-12">' . pizzalayer_icons_menu_buttons() . pizzalayer_alert('Max : ' . get_option('pizzalayer_setting_topping_maxtoppings') . ' Toppings','max-toppings') . pizzalayer_panels() . pizzalayer_icons_menu_user_actions() . '</div>

<!-- row two : the navigator menu -->
<div class="pizzalayer-ui-menu pizzalayer-ui-menu-col pizzalayer-ui-menu-col-toppings col-md-12 col-sm-12"></div>
<div style="clear:both;"></div>
<div class="pizzalayer-ui-basics pizzalayer-ui-menu-col col-md-12 col-sm-12">' . pizzalayer_topvis_title('My Recipe','pizza-slice') . pizzalayer_selection_tile('Crust','crust','No Crust Chosen')
. pizzalayer_selection_tile('Sauce','sauce','No Sauce Chosen')
. pizzalayer_selection_tile('Cheese','cheese','No Cheese Chosen')
. pizzalayer_selection_tile('Drizzle','drizzle','No Drizzle Chosen')
. '
</div>
<div class="pizzalayer-ui-toppings pizzalayer-ui-menu-col col-md-12 col-sm-12">

<ul id="pizzalayer-current-toppings" class="horizontal"></ul>
<div style="clear:both;"></div>
</div></div>
' . pizzalayer_woocommerce_actions() . '
<div style="clear:both;"></div>
</div>
<!-- / Pizzalayer : PIZZA -->' . pizzalayer_javascript_hidden_vars() . pizzalayer_options_css_output() . '
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