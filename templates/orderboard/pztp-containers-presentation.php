<?php
$pizzalayer_template_images_directory = plugin_dir_url(__FILE__) .'images/';
do_action( 'pizzalayer_file_pztp-containers-presentation_start' );

/* =============================================
PIZZALAYER : front-end UI */
function pizzalayer_toppings_visualizer_func( $atts ){
global $pizzalayer_template_name;
do_action( 'func_pizzalayer_toppings_visualizer_func_before' );
if(!isset($atts['id'])){ $atts['id'] = ''; };
if(!isset($atts['crust'])){ $atts['crust'] = ''; };
if(!isset($atts['sauce'])){ $atts['sauce'] = ''; };
if(!isset($atts['toppings'])){ $atts['toppings'] = ''; };

/* ============= GET USER OPTIONS ============= */
$pizzalayer_template_orderboard_option_topping_maxtoppings = get_option('pizzalayer_setting_topping_maxtoppings');
$pizzalayer_template_orderboard_option_display_header = get_option('pizzalayer_setting_template_orderboard_display_header');
$pizzalayer_template_orderboard_option_display_myrecipe = get_option('pizzalayer_setting_template_orderboard_display_my_recipe');
$pizzalayer_template_orderboard_option_display_toppinglist = get_option('pizzalayer_setting_template_orderboard_display_topping_list');
$pizzalayer_template_orderboard_option_display_actionbar = get_option('pizzalayer_setting_template_orderboard_display_action_bar');
$pizzalayer_template_orderboard_element_style_header = get_option('pizzalayer_setting_template_orderboard_element_style_header');
$pizzalayer_template_orderboard_element_style_tabs = get_option('pizzalayer_setting_template_orderboard_element_style_tabs');
$pizzalayer_template_orderboard_option_header_text = get_option('pizzalayer_setting_template_orderboard_header_text');
$pizzalayer_template_orderboard_option_layout = get_option('pizzalayer_setting_template_orderboard_layout');
$pizzalayer_global_option_element_style_toppings = get_option('pizzalayer_setting_element_style_toppings');
$pizzalayer_global_option_element_style_layers = get_option('pizzalayer_setting_element_style_layers');

do_action( 'func_pizzalayer_toppings_visualizer_func_after_get_user_options' );

/* ============= BUILD LAYOUT PART : HEADER ============= */
if($pizzalayer_template_orderboard_option_display_header == 'show'){
$pizzalayer_template_orderboard_part_header_left = '<div id="pizzalayer-header-left" class="col-lg-4 col-md-12 col-sm-12">' . pizzalayer_branding_row_logo_or_byline() . '</div>';
$pizzalayer_template_orderboard_part_header_middle = '<div id="pizzalayer-header-middle" class="col-lg-4 col-md-12 col-sm-12">' . $pizzalayer_template_orderboard_option_header_text . '</div>';
$pizzalayer_template_orderboard_part_header_right = '<div id="pizzalayer-header-right" class="col-lg-4 col-md-12 col-sm-12">' . pizzalayer_icons_menu_topright() . '</div>';
$pizzalayer_template_orderboard_part_header = '<div id="pizzalayer-header" class="col-sm-12 pizzalayer-ui-menu pizzalayer-ui-menu-col pizzalayer-header-style-' . $pizzalayer_template_orderboard_element_style_header . '">' . $pizzalayer_template_orderboard_part_header_left . $pizzalayer_template_orderboard_part_header_middle . $pizzalayer_template_orderboard_part_header_right . '</div>';
} else {
 $pizzalayer_template_orderboard_part_header = '';   
};

/* ============= BUILD LAYOUT PART : MAIN CONTENT ============= */
$pizzalayer_template_orderboard_part_maindisplay = '<div id="pizzalayer-main-visualizer-container" class="pizzalayer-visualizer pt-visualizer-' . $atts['id'] . ' col-md-7 col-sm-12">
' . pizzalayer_pizza_dynamic_nested($atts['id'],$atts['crust'],$atts['sauce'],$atts['toppings']) . '
</div>';

/* ============= BUILD LAYOUT PART : MAIN CONTENT - FULL WIDTH ============= */
$pizzalayer_template_orderboard_part_maindisplay_100percent = '<div id="pizzalayer-main-visualizer-container" class="pizzalayer-visualizer pt-visualizer-' . $atts['id'] . ' col-md-12 col-sm-12">
' . pizzalayer_pizza_dynamic_nested($atts['id'],$atts['crust'],$atts['sauce'],$atts['toppings']) . '
</div>';

/* ============= BUILD LAYOUT PART : MAIN CONTENT - THIRD ============= */
$pizzalayer_template_orderboard_part_maindisplay_orderboard = '<div id="pizzalayer-main-visualizer-container" class="pizzalayer-visualizer pt-visualizer-' . $atts['id'] . ' col-md-4 col-sm-12">
' . pizzalayer_pizza_dynamic_nested($atts['id'],$atts['crust'],$atts['sauce'],$atts['toppings']) . '
</div>';

/* ============= BUILD LAYOUT PART : OPEN TABS CONTAINER   ============= */
$pizzalayer_template_orderboard_part_opentabcontainer = '<div class="pizzalayer-tabs col-md-5 col-sm-12">';

/* ============= BUILD LAYOUT PART : OPEN TABS CONTAINER - FULL   ============= */
$pizzalayer_template_orderboard_part_opentabcontainer_100percent = '<div class="pizzalayer-tabs col-md-12 col-sm-12">';

/* ============= BUILD LAYOUT PART : OPEN TABS CONTAINER - ORDERBOARD   ============= */
$pizzalayer_template_orderboard_part_opentabcontainer_orderboard = '<div class="pizzalayer-tabs col-md-4 col-sm-12">';

/* ============= BUILD LAYOUT PART : TABS MENU ============= */
$tab_menu_functions = [
	'buttonicon'        => 'pizzalayer_tabs_menu_buttons',
	'buttonrounded'     => 'pizzalayer_tabs_menu_textbuttons',
	'textonly'          => 'pizzalayer_tabs_menu_text_links',
	'textunderline'     => 'pizzalayer_tabs_menu_text_links',
	'texticonunderline' => 'pizzalayer_tabs_menu_text_with_icons',
	'dropdown'          => 'pizzalayer_tabs_menu_dropdown',
	'mobile'            => 'pizzalayer_tabs_menu_dropdown',
	'icononly'          => 'pizzalayer_tabs_menu_icononly',
];

$pizzalayer_template_orderboard_part_tabsmenu = call_user_func($tab_menu_functions[$pizzalayer_template_orderboard_element_style_tabs]);

/* ============= BUILD LAYOUT PART : ALERT   ============= */
$pizzalayer_template_orderboard_part_alert = pizzalayer_alert('Max : ' . $pizzalayer_template_orderboard_option_topping_maxtoppings . ' Toppings','max-toppings');

/* ============= BUILD LAYOUT PART : PANELS   ============= */
$pizzalayer_template_orderboard_part_panels = pizzalayer_panels();

/* ============= BUILD LAYOUT PART : USER ACTIONS  ============= */
$pizzalayer_template_orderboard_part_useractions = pizzalayer_icons_menu_user_actions();

/* ============= BUILD LAYOUT PART : CLOSE TABS CONTAINER   ============= */
$pizzalayer_template_orderboard_part_closetabcontainer = '</div>';

/* ============= BUILD LAYOUT PART : MY RECIPE ============= */

/* v = default/sidebyside */
if($pizzalayer_template_orderboard_option_display_myrecipe == 'show'){
$pizzalayer_template_orderboard_part_myrecipe = '<!-- row : my recipe -->
<div class="pizzalayer-ui-basics pizzalayer-ui-menu-col col-md-12 col-sm-12">'
. pizzalayer_topvis_title('My Recipe','pizza-slice')
. pizzalayer_selection_tile('Crust','crust', pizzalayer_template_myrecipe_section_title( get_option('pizzalayer_setting_crust_defaultcrust'),'No Crust Chosen.'))
. pizzalayer_selection_tile('Sauce','sauce', pizzalayer_template_myrecipe_section_title( get_option('pizzalayer_setting_sauce_defaultsauce'),'No Sauce Chosen.'))
. pizzalayer_selection_tile('Cheese','cheese', pizzalayer_template_myrecipe_section_title( get_option('pizzalayer_setting_cheese_defaultcheese'),'No Cheese Chosen.'))
. pizzalayer_selection_tile('Drizzle','drizzle', pizzalayer_template_myrecipe_section_title( get_option('pizzalayer_setting_drizzle_defaultdrizzle'),'No Drizzle Chosen.'))
. '
</div>';
} else { 
   $pizzalayer_template_orderboard_part_myrecipe = '';
};

/* v = note recipe */
if($pizzalayer_template_orderboard_option_display_myrecipe == 'show'){
$pizzalayer_template_orderboard_part_myrecipe_noterecipe = '<!-- row : my recipe -->
<div class="pizzalayer-ui-basics pizzalayer-ui-menu-col col-md-5 col-sm-12">'
. pizzalayer_topvis_title('My Recipe','pizza-slice')
. pizzalayer_selection_tile_100percent('Crust','crust', pizzalayer_template_myrecipe_section_title( get_option('pizzalayer_setting_crust_defaultcrust'),'No Crust Chosen.'))
. pizzalayer_selection_tile_100percent('Sauce','sauce', pizzalayer_template_myrecipe_section_title( get_option('pizzalayer_setting_sauce_defaultsauce'),'No Sauce Chosen.'))
. pizzalayer_selection_tile_100percent('Cheese','cheese', pizzalayer_template_myrecipe_section_title( get_option('pizzalayer_setting_cheese_defaultcheese'),'No Cheese Chosen.'))
. pizzalayer_selection_tile_100percent('Drizzle','drizzle', pizzalayer_template_myrecipe_section_title( get_option('pizzalayer_setting_drizzle_defaultdrizzle'),'No Drizzle Chosen.'))
. '
</div>';
} else { 
    $pizzalayer_template_orderboard_part_myrecipe_noterecipe = '';
};

/* v = app style */
if($pizzalayer_template_orderboard_option_display_myrecipe == 'show'){
$pizzalayer_template_orderboard_part_myrecipe_orderboard = '<!-- row : my recipe -->
<div class="pizzalayer-ui-basics pizzalayer-ui-menu-col col-md-4 col-sm-12">'
. pizzalayer_topvis_title('My Recipe','pizza-slice')
. pizzalayer_selection_tile_100percent('Crust','crust', pizzalayer_template_myrecipe_section_title( get_option('pizzalayer_setting_crust_defaultcrust'),'No Crust Chosen.'))
. pizzalayer_selection_tile_100percent('Sauce','sauce', pizzalayer_template_myrecipe_section_title( get_option('pizzalayer_setting_sauce_defaultsauce'),'No Sauce Chosen.'))
. pizzalayer_selection_tile_100percent('Cheese','cheese', pizzalayer_template_myrecipe_section_title( get_option('pizzalayer_setting_cheese_defaultcheese'),'No Cheese Chosen.'))
. pizzalayer_selection_tile_100percent('Drizzle','drizzle', pizzalayer_template_myrecipe_section_title( get_option('pizzalayer_setting_drizzle_defaultdrizzle'),'No Drizzle Chosen.'))
. '
</div>';
} else { 
    $pizzalayer_template_orderboard_part_myrecipe_orderboard = '';
};

/* ============= BUILD LAYOUT PART : MY TOPPING LIST ============= */
if( $pizzalayer_template_orderboard_option_display_toppinglist == 'show' ){
$pizzalayer_template_orderboard_part_toppinglist = '<!-- row : current toppings list -->
<div class="pizzalayer-ui-toppings pizzalayer-ui-menu-col col-md-12 col-sm-12">
<ul id="pizzalayer-current-toppings" class="horizontal"></ul>
<div style="clear:both;"></div>
</div>';
} else { 
    $pizzalayer_template_orderboard_part_toppinglist = '';
};

/* ============= BUILD LAYOUT PART : ACTION BAR ============= */
if( $pizzalayer_template_orderboard_option_display_actionbar == 'show' ){
$pizzalayer_template_orderboard_part_actionbar = '<!-- row : woocommerce action bar -->' . pizzalayer_woocommerce_actions();
} else { 
$pizzalayer_template_orderboard_part_actionbar = '';
};

/* ============= ASSEMBLE PARTS AND RETURN UI ============= */
//pizzalayer_setting_template_orderboard_layout

$pizzalayer_template_orderboard_part_content =  $pizzalayer_template_orderboard_part_header . $pizzalayer_template_orderboard_part_maindisplay_100percent .  $pizzalayer_template_orderboard_part_opentabcontainer_100percent . $pizzalayer_template_orderboard_part_alert . $pizzalayer_template_orderboard_part_tabsmenu . $pizzalayer_template_orderboard_part_panels . $pizzalayer_template_orderboard_part_useractions . $pizzalayer_template_orderboard_part_closetabcontainer . $pizzalayer_template_orderboard_part_myrecipe . $pizzalayer_template_orderboard_part_toppinglist;


do_action( 'func_pizzalayer_toppings_visualizer_func_before_return' );

return '
<!-- Pizzalayer : PIZZA DISPLAY ==================== -->
<div id="pizzalayer-ui-wrapper" class="pizzalayer-ui-wrapper pizzalayer-template-' . $pizzalayer_template_name . '">
' . pizzalayer_demo_notice() . '
<!-- Pizzalayer : PIZZA + -->
<div id="pizzalayer-ui-container" class="pizzalayer-ui-container pizzalayer-uirow">
' . $pizzalayer_template_orderboard_part_content . 
'</div>
' . $pizzalayer_template_orderboard_part_actionbar . '
<div style="clear:both;"></div>
</div>
<!-- / Pizzalayer : PIZZA -->' . pizzalayer_javascript_hidden_vars() . pizzalayer_options_css_output() . pizzalayer_template_orderboard_options_css_output() . '
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

do_action( 'pizzalayer_file_pztp-containers-presentation_end' );