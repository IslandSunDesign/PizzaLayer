<?php 
function pizzalayer_topvis_title($title,$icon){
    return '<h3 class="pizzalayer-toppings-visualizer-subtitle"><i class="fa fa-solid fa-' . $icon . '"></i> ' . $title . '</h2>';
}

function pizzalayer_branding_row_justlogo(){ 
return '<!-- Pizzalayer : BRANDING ROW -->
<div class="pizzalayer-ui-branding row">
<h2>Logo Here</h2>
</div>
<!-- / Pizzalayer : BRANDING ROW -->';
}

function pizzalayer_branding_row_logo_or_byline(){
if (get_option('pizzalayer_setting_branding_altlogo')){ 
$pizzalayer_icons_menu_title = '<img class="pizzalayer-main-logo" src="' . get_option('pizzalayer_setting_branding_altlogo') . '" />'; 
} else { $pizzalayer_icons_menu_title = '<h3>Pizza Builder Menu</h3>';};
return '<div class="pizzalayer-ui-menu-top-text col-sm-12">' . $pizzalayer_icons_menu_title . '</div>';
}

function pizzalayer_branding_row_settings(){
return '<!-- Pizzalayer : BRANDING ROW -->
<div class="pizzalayer-ui-branding row">
<div class="col-sm-6 pizzalayer-branding-logo"><h2>My Business</h2></div>
<div class="col-sm-6 pizzalayer-branding-settings">
<a href="#" class="pizzalayer-settings-button" alt="Settings"><i class="fa fa-solid fa-screwdriver"></i></a>
</div>
</div>
<!-- / Pizzalayer : BRANDING ROW -->';
}

function pizzalayer_loadscreen_intro(){
return '<div id="pizzalayer-ui-loadscreen" class="pizzalayer-ui-loadscreen">
<div class="col-sm-6 pizzalayer-subheader-mainline">
<div class="">
<h2>Ready to customize your Pizza?</h2>
</div>
</div>
<div class="col-sm-6 pizzalayer-subheader-byline">
To get started, simply select the options you\'d like from the tabs menu below your pizza display!
</div>
</div>';
}

function pizzalayer_tabs_menu(){
    return'<!-- Pizzalayer : MENU UI -->
<div class="pizzalayer-ui-menu pizzalayer-ui-menu-row">
<div id="pizzalayer-ui-menu-items">
<a href="javascript:PTswitchToMenu(\'intro\');" title="Home"><i class="fa fa-solid fa-circle-left"></i> Intro</a>
<a href="javascript:PTswitchToMenu(\'crusts\');" title="Crusts"><i class="fa fa-solid fa-circle"></i> Crusts</a>
<a href="javascript:PTswitchToMenu(\'sauces\');" title="Sauces"><i class="fa fa-solid fa-tint"></i> Sauces</a>
<a href="javascript:PTswitchToMenu(\'cheeses\');" title="Cheeses"><i class="fa fa-solid fa-cheese"></i> Cheeses</a>
<a href="javascript:PTswitchToMenu(\'drizzles\');" title="Drizzles"><i class="fa fa-solid fa-water"></i> Drizzles</a>
<a href="javascript:PTswitchToMenu(\'toppings\');" title="Toppings"><i class="fa fa-solid fa-cookie"></i> Toppings</a>
<a href="javascript:PTswitchToMenu(\'slices\');" title="Slicing"><i class="fa fa-solid fa-pizza-slice"></i> Slicing</a>
<a href="javascript:PThideMenu();"><i class="fa fa-eye-slash"></i> Hide</a>
<a href="javascript:PThideMenu();"><i class="fa fa-gear"></i> Settings</a>
</div>
</div>';
}

function pizzalayer_icons_menu_topright(){
 return '<a href="javascript:ClearPizza();"><i class="fa fa-solid fa-trash"></i> Reset Pizza</a>';   
}

function pizzalayer_icons_menu_item($IconMenuItemIcon,$IconMenuItemSwitchToMenuID,$IconMenuItemTitle,$IconMenuItemShort,$IconMenuItemSelected){
if($IconMenuItemSelected == 'selected'){$IconMenuItemSelectedCSS = ' pizzalayer-icon-selected';} else {$IconMenuItemSelectedCSS = '';}
return '<a href="javascript:PTswitchToMenu(\'' . $IconMenuItemSwitchToMenuID . '\');" title="' . $IconMenuItemTitle . '" id="pizzalayer-icon-menu-item-' . $IconMenuItemSwitchToMenuID . '" class="pizzalayer-icon-menu-item' . $IconMenuItemSelectedCSS . '">
<div class="col-lg-3 col-md-3 col-sm-3">
<i class="fa fa-solid fa-' . $IconMenuItemIcon . '"></i><br/>'.  $IconMenuItemTitle . '
</div></a>';
}


function pizzalayer_icons_menu(){
return '<!-- Pizzalayer : MENU UI -->
<div class="pizzalayer-ui-menu pizzalayer-ui-menu-row pizzalayer-ui-menu-icons">' . pizzalayer_icons_menu_header() . '
<div id="pizzalayer-ui-menu-items" class="col-sm-12">
<ul>
<li class="col-lg-3 col-md-3 col-sm-3">
<a href="javascript:PTswitchToMenu(\'intro\');" title="Home" id="pizzalayer-icon-menu-item-intro" class="pizzalayer-icon-menu-item pizzalayer-icon-selected"><div class="pizzalayer-icon-row"><i class="fa fa-solid fa-home"></i></div>Home</a>
</li>

<li class="col-lg-3 col-md-3 col-sm-3">
<a href="javascript:PTswitchToMenu(\'crusts\');" title="Crusts" id="pizzalayer-icon-menu-item-crusts" class="pizzalayer-icon-menu-item">
<div>
<div class="pizzalayer-icon-row"><i class="fa fa-solid fa-circle"></i></div>Crusts
</div></a>
</li>

<li class="col-lg-3 col-md-3 col-sm-3">
<a href="javascript:PTswitchToMenu(\'sauces\');" title="Sauces" id="pizzalayer-icon-menu-item-sauces" class="pizzalayer-icon-menu-item">
<div>
<div class="pizzalayer-icon-row"><i class="fa fa-solid fa-tint"></i></div>Sauces
</div></a>
</li>
<li class="col-lg-3 col-md-3 col-sm-3">
<a href="javascript:PTswitchToMenu(\'cheeses\');" title="Cheeses" id="pizzalayer-icon-menu-item-cheeses" class="pizzalayer-icon-menu-item">
<div>
<div class="pizzalayer-icon-row"><i class="fa fa-solid fa-cheese"></i></div><br/>Cheeses
</div></a>
</li>
<li class="col-lg-3 col-md-3 col-sm-3">
<a href="javascript:PTswitchToMenu(\'drizzles\');" title="Drizzles" id="pizzalayer-icon-menu-item-drizzles" class="pizzalayer-icon-menu-item">
<div>
<div class="pizzalayer-icon-row"><i class="fa fa-solid fa-water"></i></div>Drizzles
</div></a>
</li>
<li class="col-lg-3 col-md-3 col-sm-3">
<a href="javascript:PTswitchToMenu(\'toppings\');" title="Toppings" id="pizzalayer-icon-menu-item-toppings" class="pizzalayer-icon-menu-item">
<div>
<div class="pizzalayer-icon-row"><i class="fa fa-solid fa-cookie"></i></div>Toppings
</div></a>
</li>
<li class="col-lg-3 col-md-3 col-sm-3">
<a href="javascript:PTswitchToMenu(\'slices\');" title="Slicing" id="pizzalayer-icon-menu-item-slices" class="pizzalayer-icon-menu-item">
<div>
<div class="pizzalayer-icon-row"><i class="fa fa-solid fa-pizza-slice"></i></div>Slicing
</div></a>
</li>


</ul>
</div><div style="clear:both;padding:0;margin:0;"></div>
</div>';
}

function pizzalayer_icons_menu_buttons(){
return '<!-- Pizzalayer : MENU UI -->
<div class="pizzalayer-ui-menu pizzalayer-ui-menu-row pizzalayer-ui-menu-icons">' . pizzalayer_icons_menu_header() . '
<div id="pizzalayer-ui-menu-items" class="col-sm-12">
<ul>


<li class="col-lg-3 col-md-3 col-sm-3">
<button onClick="PTswitchToMenu(\'intro\');" title="Home" id="pizzalayer-icon-menu-item-intro" class="pizzalayer-icon-menu-item pizzalayer-icon-selected">
<div class="pizzalayer-icon-menu-item-icon-row"><i class="fa fa-solid fa-home"></i></div>Home
</button>
</li>


<li class="col-lg-3 col-md-3 col-sm-3">
<button onClick="PTswitchToMenu(\'crusts\');" title="Crusts" id="pizzalayer-icon-menu-item-crusts" class="pizzalayer-icon-menu-item">
<div class="pizzalayer-icon-menu-item-icon-row"><i class="fa fa-solid fa-circle"></i></div>Crusts
</button>
</li>


<li class="col-lg-3 col-md-3 col-sm-3">
<button onClick="PTswitchToMenu(\'sauces\');" title="Sauces" id="pizzalayer-icon-menu-item-sauces" class="pizzalayer-icon-menu-item">
<div class="pizzalayer-icon-menu-item-icon-row"><i class="fa fa-solid fa-tint"></i></div>Sauces
</button>
</li>

<li class="col-lg-3 col-md-3 col-sm-3">
<button onClick="PTswitchToMenu(\'cheeses\');" title="Cheeses" id="pizzalayer-icon-menu-item-cheeses" class="pizzalayer-icon-menu-item">
<div>
<div class="pizzalayer-icon-menu-item-icon-row"><i class="fa fa-solid fa-cheese"></i></div>Cheeses
</div></button>
</li>

<li class="col-lg-3 col-md-3 col-sm-3">
<button onClick="PTswitchToMenu(\'toppings\');" title="Toppings" id="pizzalayer-icon-menu-item-toppings" class="pizzalayer-icon-menu-item">
<div>
<div class="pizzalayer-icon-menu-item-icon-row"><i class="fa fa-solid fa-cookie"></i></div>Toppings
</div></button>
</li>

<li class="col-lg-3 col-md-3 col-sm-3">
<button onClick="PTswitchToMenu(\'drizzles\');" title="Drizzles" id="pizzalayer-icon-menu-item-drizzles" class="pizzalayer-icon-menu-item">
<div>
<div class="pizzalayer-icon-menu-item-icon-row"><i class="fa fa-solid fa-water"></i></div>Drizzles
</div></button>
</li>

<li class="col-lg-3 col-md-3 col-sm-3">
<button onClick="PTswitchToMenu(\'slices\');" title="Slicing" id="pizzalayer-icon-menu-item-slices" class="pizzalayer-icon-menu-item">
<div>
<div class="pizzalayer-icon-menu-item-icon-row"><i class="fa fa-solid fa-pizza-slice"></i></div>Slicing
</div></button>
</li>


</ul>
</div><div style="clear:both;padding:0;margin:0;"></div>
</div>';
}

function pizzalayer_icons_menu_user_actions(){
return'<!-- Pizzalayer : MENU UI -->
<div class="pizzalayer-ui-menu pizzalayer-ui-menu-row pizzalayer-ui-menu-icons pizzalayer-ui-menu-icons-user-actions">
<div id="pizzalayer-ui-menu-items-actions" class="col-sm-12">
<ul>
<a href="javascript:PTswitchToMenu(\'intro\');" title="Home"><i class="fa fa-solid fa-house-user"></i></a>
<a href="javascript:PThideMenu();" title="close menu"><i class="fa fa-solid fa-eye-slash"></i></a>
<a href="javascript:PTswitchToMenu(\'help\');" title="help"><i class="fa fa-solid fa-question"></i></a>
<a href="javascript:PTswitchToMenu(\'settings\');" title="my settings"><i class="fa fa-solid fa-screwdriver"></i></a>
</ul>
</div><div style="clear:both;padding:0;margin:0;"></div>
</div>';
}

function pizzalayer_icons_menu_header(){
    if(get_option('pizzalayer_setting_branding_menu_title')){
    return '<div id="pizzalayer-menu-header-content">' . get_option('pizzalayer_setting_branding_menu_title') . '</div>';
} else { return ''; };
}


function pizzalayer_tabs_menu_3x(){
    return'<!-- Pizzalayer : MENU UI -->
<div class="pizzalayer-ui-menu pizzalayer-ui-menu-row">
<div id="pizzalayer-ui-menu-items" class="col-sm-12">
<ul>
<li class="col-sm-3 col-md-2"><a href="javascript:PTswitchToMenu(\'crusts\');" title="Crusts" style="width:100%;"><i class="fa fa-solid fa-circle"></i><span class="pizzalayer-ui-icon-menu-tab"></span></a></li>
<li class="col-sm-3 col-md-2"><a href="javascript:PTswitchToMenu(\'sauces\');" title="Sauces"><i class="fa fa-solid fa-tint"></i><span class="pizzalayer-ui-icon-menu-tab">Sauces</span></a></li>
<li class="col-sm-3 col-md-2"><a href="javascript:PTswitchToMenu(\'cheeses\');" title="Cheeses"><i class="fa fa-solid fa-cheese"></i><span class="pizzalayer-ui-icon-menu-tab">Cheeses</span></a></li>
<li class="col-sm-3 col-md-2"><a href="javascript:PTswitchToMenu(\'drizzles\');" title="Drizzles"><i class="fa fa-solid fa-water"></i><span class="pizzalayer-ui-icon-menu-tab">Drizzles</span></a></li>
<li class="col-sm-3 col-md-2"><a href="javascript:PTswitchToMenu(\'toppings\');" title="Toppings"><i class="fa fa-solid fa-cookie"></i><span class="pizzalayer-ui-icon-menu-tab">Toppings</span></a></li>
<li class="col-sm-3 col-md-2"><a href="javascript:PTswitchToMenu(\'slices\');" title="Slicing"><i class="fa fa-solid fa-pizza-slice"></i><span class="pizzalayer-ui-icon-menu-tab">Slicing</span></a></li>
<li class="col-sm-3 col-md-2"><a href="javascript:PTswitchToMenu(\'intro\');" title="Home"><i class="fa fa-solid fa-circle-left"></i><span class="pizzalayer-ui-icon-menu-tab">Home</span></a></li>
<li class="col-sm-3 col-md-2"><a href="javascript:PThideMenu();"><i class="fa fa-solid fa-eye-slash"></i><span class="pizzalayer-ui-icon-menu-tab">Hide</span></a></li>
<li class="col-sm-3 col-md-2"><a href="javascript:PThideMenu();"><i class="fa fa-solid fa-gear"></i><span class="pizzalayer-ui-icon-menu-tab">Settings</span></a></li>
</ul>
</div><div style="clear:both;padding:0;margin:0;"></div>
</div>';
}



function pizzalayer_panels_container(){
    
};

function pizzalayer_panels(){
    return '<div class="pizzalayer-ui-menu row">

<!-- Panel 1: Intro -->    
<div id="pizzalayer-ui-menu-section-intro" class="pizzalayer-ui-menu-col pizzalayer-ui-menu-tab pizzalayer-ui-menu-col-intro col-sm-12 pizzalayer-full-height">' . pizzalayer_topvis_title('Welcome','pizza-slice') . '<div id="pizzalayer-intro-content">Thanks for trying out PizzaLayer! Please check out each section in the menu above to customize your Pizza. <hr/><strong>
Important - This is just a demo</strong></div>
' . pizzalayer_control_nextprev('','crusts','pizzalayer-controls-panel1') . '
</div>

<!-- Panel 2: Crusts -->
<div id="pizzalayer-ui-menu-section-crusts" class="pizzalayer-ui-menu-col pizzalayer-ui-menu-tab pizzalayer-ui-menu-col-crusts col-sm-12 pizzalayer-full-height">' . pizzalayer_topvis_title('Crusts','pizza-slice') . pizzalayer_ui_approws('crusts','') . pizzalayer_control_nextprev('intro','sauces','pizzalayer-controls-panel2') . '</div>

<!-- Panel 3: Sauces -->
<div id="pizzalayer-ui-menu-section-sauces" class="pizzalayer-ui-menu-col pizzalayer-ui-menu-tab pizzalayer-ui-menu-col-sauces col-sm-12 pizzalayer-full-height">' . pizzalayer_topvis_title('Sauces','leaf') . pizzalayer_ui_approws('sauces','') . pizzalayer_control_nextprev('crusts','cheeses','pizzalayer-controls-panel2') . '</div>

<!-- Panel 4: Cheeses -->
<div id="pizzalayer-ui-menu-section-cheeses" class="pizzalayer-ui-menu-col pizzalayer-ui-menu-tab pizzalayer-ui-menu-col-cheeses col-sm-12 pizzalayer-full-height">' . pizzalayer_topvis_title('Cheeses','cheese') . pizzalayer_ui_approws('cheeses','') . pizzalayer_control_nextprev('sauces','drizzles','pizzalayer-controls-panel4') . '</div>

<!-- Panel 5: Drizzles -->
<div id="pizzalayer-ui-menu-section-drizzles" class="pizzalayer-ui-menu-col pizzalayer-ui-menu-tab pizzalayer-ui-menu-col-drizzles col-sm-12 pizzalayer-full-height">' . pizzalayer_topvis_title('Drizzles','water') . pizzalayer_ui_approws('drizzles','') . pizzalayer_control_nextprev('cheeses','toppings','pizzalayer-controls-panel2') . '</div>

<!-- Panel 6: Toppings -->
<div id="pizzalayer-ui-menu-section-toppings" class="pizzalayer-ui-menu-col pizzalayer-ui-menu-tab pizzalayer-ui-menu-col-toppings col-sm-12 pizzalayer-full-height">' . pizzalayer_topvis_title('Toppings','pizza-slice') . pizzalayer_ui_approws('toppings','') . pizzalayer_control_nextprev('drizzles','slices','pizzalayer-controls-panel2') . '</div>    

<!-- Panel 7: Slicing -->
<div id="pizzalayer-ui-menu-section-slices" class="pizzalayer-ui-menu-col pizzalayer-ui-menu-tab pizzalayer-ui-menu-col-slices col-sm-12 pizzalayer-full-height">' . pizzalayer_topvis_title('Slices','pizza-slice') . pizzalayer_ui_approws('cuts','') . pizzalayer_control_nextprev('toppings','','pizzalayer-controls-panel2') . '</div>

<!-- Panel 8: Help -->
<div id="pizzalayer-ui-menu-section-help" class="pizzalayer-ui-menu-col pizzalayer-ui-menu-tab pizzalayer-ui-menu-col-toppings col-sm-12 pizzalayer-full-height">' . pizzalayer_topvis_title('Help','pizza-slice') . '</div>

<!-- Panel 9: Settings -->
<div id="pizzalayer-ui-menu-section-settings" class="pizzalayer-ui-menu-col pizzalayer-ui-menu-tab pizzalayer-ui-menu-col-toppings col-sm-12 pizzalayer-full-height">' . pizzalayer_topvis_title('Settings','pizza-slice') . '</div>

</div>

<!-- / Pizzalayer : MENU UI -->';    
}

function pizzalayer_woocommerce_actions(){
    return '<!-- Pizzalayer : ORDER ACTIONS  -->
<div class="pizzalayer-ui-menu row">
<div class="pizzalayer-ui-menu-col col-lg-12 col-md-12"> 
<a href="#" class="pizzalayer-button"><i class="fa fa-cart-plus"></i> Add to Cart</a>
<a href="javascript:ClearPizza()" class="pizzalayer-button"><i class="fa fa-arrows-spin"></i> Reset Pizza</a>
<a href="#" class="pizzalayer-button"><i class="fa fa-pizza-slice"></i> $0.00</a>
</div>
</div>
<!-- / Pizzalayer : ORDER ACTIONS -->';
}

function pizzalayer_javascript_hidden_vars(){
    return '<!-- Pizzalayer : HIDDEN FORM VARS AND RECIPE STORAGE -->
<form name="pizzalayer-current-recipe-storage">
<input type="hidden" name="pztp-chosen-crust" value="" />
<input type="hidden" name="pztp-chosen-sauce" value="" />
<input type="hidden" name="pztp-chosen-cheese" value="" />
<input type="hidden" name="pztp-chosen-toppings" value="" />
<input type="hidden" name="pztp-chosen-drizzle" value="" />
<input type="hidden" name="pztp-chosen-cut" value="" />
</form>
<!-- / Pizzalayer : HIDDEN FORM VARS AND RECIPE STORAGE -->';
}

//PIZZA-SPECIFIC CSS - values applied from customizer / global meta

function pizzalayer_template_glass_options_css_output(){
// static variables
$pizzalayer_template_glass_options_string = '';

// background color - main wrapper
$pizzalayer_template_glass_option_color_background = get_option( 'pizzalayer_setting_template_glass_background' );
if($pizzalayer_template_glass_option_color_background){ $pizzalayer_template_glass_options_string .= 'body #pizzalayer-ui-wrapper{background:' . $pizzalayer_template_glass_option_color_background . ';'; };

//gather CSS for 
$pizzalayer_options_css = '<style type="text/css">' . $pizzalayer_template_glass_options_string . '</style>';

//finally, return combined CSS
return $pizzalayer_options_css;
    
    
} //close function

function pizzalayer_current_toppings(){
    return '<div id="pizzalayer-ui-menu-section-toppings" class="pizzalayer-ui-menu-col pizzalayer-ui-menu-tab pizzalayer-ui-menu-col-toppings col-sm-12 pizzalayer-full-height"></div><div id="pizzalayer-current-toppings-footer"></div>';
}

function pizzalayer_selection_tile($pizzalayer_tile_title,$pizzalayer_tile_slug,$pizzalayer_tile_placeholder){
    return '<div id="pizzalayer-basics-tile-' . $pizzalayer_tile_slug . '" class="col-md-3 col-sm-12 pizzalayer-basics-tile"><div class="pizzalayer-basics-tile-wrapper"><h3>' . $pizzalayer_tile_title . '</h3><div id="pizzalayer-basics-tile-title-' . $pizzalayer_tile_slug . '" class="pizzalayers-basics-tile-title">' . $pizzalayer_tile_placeholder . '</div></div></div>';
}
