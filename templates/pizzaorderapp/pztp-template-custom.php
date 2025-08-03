<?php
do_action( 'pizzalayer_file_pztp-template-custom_start' );

function hex2rgba( $color,$alpha ) {
    if ($color[0] == '#') {
        $color = substr($color, 1);
    }
    list($r, $g, $b) = array_map("hexdec", str_split($color, (strlen( $color ) / 3)));
    //return array( 'red' => $r, 'green' => $g, 'blue' => $b );
    return 'rgba(' . $r . ',' . $g . ',' . $b . ',' . $alpha . ')';
}


function pizzalayer_topvis_title($title,$icon){
    return '<h3 class="pizzalayer-toppings-visualizer-subtitle"><i class="fa fa-solid fa-' . $icon . '"></i> ' . $title . '</h2>';
}

function pizzalayer_template_myrecipe_section_title($pizzalayer_template_myrecipe_title_stringIn,$pizzalayer_template_myrecipe_title_stringOut){
if($pizzalayer_template_myrecipe_title_stringIn != ''){return $pizzalayer_template_myrecipe_title_stringIn;} else {return $pizzalayer_template_myrecipe_title_stringOut;};
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
$pizzalayer_template_glassy_option_header_menu_type = get_option('pizzalayer_setting_template_glass_header_menu_type');
 return '<div class="pizzalayer-template-glassy-header-menu pizzalayer-template-glassy-header-menu-type-' . $pizzalayer_template_glassy_option_header_menu_type . '">
 ' . pizzalayer_template_glass_options_reset_button('top') . '</div>';   
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
<li class="col-lg-3 col-md-3 col-sm-3 col-3">
<a href="javascript:PTswitchToMenu(\'intro\');" title="Home" id="pizzalayer-icon-menu-item-intro" class="pizzalayer-icon-menu-item pizzalayer-icon-selected"><div class="pizzalayer-icon-row"><i class="fa fa-solid fa-home"></i></div>Home</a>
</li>

<li class="col-lg-3 col-md-3 col-sm-3 col-3">
<a href="javascript:PTswitchToMenu(\'crusts\');" title="Crusts" id="pizzalayer-icon-menu-item-crusts" class="pizzalayer-icon-menu-item">
<div>
<div class="pizzalayer-icon-row"><i class="fa fa-solid fa-circle"></i></div>Crusts
</div></a>
</li>

<li class="col-lg-3 col-md-3 col-sm-3 col-3">
<a href="javascript:PTswitchToMenu(\'sauces\');" title="Sauces" id="pizzalayer-icon-menu-item-sauces" class="pizzalayer-icon-menu-item">
<div>
<div class="pizzalayer-icon-row"><i class="fa fa-solid fa-tint"></i></div>Sauces
</div></a>
</li>
<li class="col-lg-3 col-md-3 col-sm-3 col-3">
<a href="javascript:PTswitchToMenu(\'cheeses\');" title="Cheeses" id="pizzalayer-icon-menu-item-cheeses" class="pizzalayer-icon-menu-item">
<div>
<div class="pizzalayer-icon-row"><i class="fa fa-solid fa-cheese"></i></div><br/>Cheeses
</div></a>
</li>
<li class="col-lg-3 col-md-3 col-sm-3 col-3">
<a href="javascript:PTswitchToMenu(\'drizzles\');" title="Drizzles" id="pizzalayer-icon-menu-item-drizzles" class="pizzalayer-icon-menu-item">
<div>
<div class="pizzalayer-icon-row"><i class="fa fa-solid fa-water"></i></div>Drizzles
</div></a>
</li>
<li class="col-lg-3 col-md-3 col-sm-3 col-3">
<a href="javascript:PTswitchToMenu(\'toppings\');" title="Toppings" id="pizzalayer-icon-menu-item-toppings" class="pizzalayer-icon-menu-item">
<div>
<div class="pizzalayer-icon-row"><i class="fa fa-solid fa-cookie"></i></div>Toppings
</div></a>
</li>
<li class="col-lg-3 col-md-3 col-sm-3 col-3">
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
$pizzalayer_template_glassy_element_style_tabs = get_option('pizzalayer_setting_template_glass_element_style_tabs');

return '<!-- Pizzalayer : MENU UI -->
<div class="pizzalayer-ui-menu pizzalayer-ui-menu-row pizzalayer-ui-menu-icons pizzalayer-ui-menu-icons-' . $pizzalayer_template_glassy_element_style_tabs . '">' . pizzalayer_icons_menu_header() . '
<div id="pizzalayer-ui-menu-items" class="col-sm-12">
<ul>


<li class="col-lg-3 col-md-3 col-sm-3 col-3">
<button onClick="PTswitchToMenu(\'intro\');" title="Home" id="pizzalayer-icon-menu-item-intro" class="pizzalayer-icon-menu-item pizzalayer-icon-selected">
<div class="pizzalayer-icon-menu-item-icon-row"><i class="fa fa-solid fa-home"></i></div>Home
</button>
</li>


<li class="col-lg-3 col-md-3 col-sm-3 col-3">
<button onClick="PTswitchToMenu(\'crusts\');" title="Crusts" id="pizzalayer-icon-menu-item-crusts" class="pizzalayer-icon-menu-item">
<div class="pizzalayer-icon-menu-item-icon-row"><i class="fa fa-solid fa-circle"></i></div>Crusts
</button>
</li>


<li class="col-lg-3 col-md-3 col-sm-3 col-3">
<button onClick="PTswitchToMenu(\'sauces\');" title="Sauces" id="pizzalayer-icon-menu-item-sauces" class="pizzalayer-icon-menu-item">
<div class="pizzalayer-icon-menu-item-icon-row"><i class="fa fa-solid fa-tint"></i></div>Sauces
</button>
</li>

<li class="col-lg-3 col-md-3 col-sm-3 col-3">
<button onClick="PTswitchToMenu(\'cheeses\');" title="Cheeses" id="pizzalayer-icon-menu-item-cheeses" class="pizzalayer-icon-menu-item">
<div>
<div class="pizzalayer-icon-menu-item-icon-row"><i class="fa fa-solid fa-cheese"></i></div>Cheeses
</div></button>
</li>

<li class="col-lg-3 col-md-3 col-sm-3 col-3">
<button onClick="PTswitchToMenu(\'toppings\');" title="Toppings" id="pizzalayer-icon-menu-item-toppings" class="pizzalayer-icon-menu-item">
<div>
<div class="pizzalayer-icon-menu-item-icon-row"><i class="fa fa-solid fa-cookie"></i></div>Toppings
</div></button>
</li>

<li class="col-lg-3 col-md-3 col-sm-3 col-3">
<button onClick="PTswitchToMenu(\'drizzles\');" title="Drizzles" id="pizzalayer-icon-menu-item-drizzles" class="pizzalayer-icon-menu-item">
<div>
<div class="pizzalayer-icon-menu-item-icon-row"><i class="fa fa-solid fa-water"></i></div>Drizzles
</div></button>
</li>

<li class="col-lg-3 col-md-3 col-sm-3 col-3">
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
if(get_option('pizzalayer_setting_template_glass_display_tabs_icons_menu') == 'hide'){ return ''; };
return'<!-- Pizzalayer : MENU UI -->
<div class="pizzalayer-ui-menu pizzalayer-ui-menu-row pizzalayer-ui-menu-icons pizzalayer-ui-menu-icons-user-actions">
<div id="pizzalayer-ui-menu-items-actions" class="col-sm-12">
<ul>
<a href="javascript:PTswitchToMenu(\'intro\');" title="Home"><i class="fa fa-solid fa-house-user"></i></a>
<a href="javascript:PThideMenu();" title="close menu"><i class="fa fa-solid fa-eye-slash"></i></a>
<a href="javascript:PTswitchToMenu(\'help\');" title="help"><i class="fa fa-solid fa-question"></i></a>
' . pizzalayer_template_glass_options_reset_button('foot') . '
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
<li class="col-3 col-sm-3 col-md-2"><a href="javascript:PTswitchToMenu(\'crusts\');" title="Crusts" style="width:100%;"><i class="fa fa-solid fa-circle"></i><span class="pizzalayer-ui-icon-menu-tab"></span></a></li>
<li class="col-3 col-sm-3 col-md-2"><a href="javascript:PTswitchToMenu(\'sauces\');" title="Sauces"><i class="fa fa-solid fa-tint"></i><span class="pizzalayer-ui-icon-menu-tab">Sauces</span></a></li>
<li class="col-3 col-sm-3 col-md-2"><a href="javascript:PTswitchToMenu(\'cheeses\');" title="Cheeses"><i class="fa fa-solid fa-cheese"></i><span class="pizzalayer-ui-icon-menu-tab">Cheeses</span></a></li>
<li class="col-3 col-sm-3 col-md-2"><a href="javascript:PTswitchToMenu(\'drizzles\');" title="Drizzles"><i class="fa fa-solid fa-water"></i><span class="pizzalayer-ui-icon-menu-tab">Drizzles</span></a></li>
<li class="col-3 col-sm-3 col-md-2"><a href="javascript:PTswitchToMenu(\'toppings\');" title="Toppings"><i class="fa fa-solid fa-cookie"></i><span class="pizzalayer-ui-icon-menu-tab">Toppings</span></a></li>
<li class="col-3 col-sm-3 col-md-2"><a href="javascript:PTswitchToMenu(\'slices\');" title="Slicing"><i class="fa fa-solid fa-pizza-slice"></i><span class="pizzalayer-ui-icon-menu-tab">Slicing</span></a></li>
<li class="col-3 col-sm-3 col-md-2"><a href="javascript:PTswitchToMenu(\'intro\');" title="Home"><i class="fa fa-solid fa-circle-left"></i><span class="pizzalayer-ui-icon-menu-tab">Home</span></a></li>
<li class="col-3 col-sm-3 col-md-2"><a href="javascript:PThideMenu();"><i class="fa fa-solid fa-eye-slash"></i><span class="pizzalayer-ui-icon-menu-tab">Hide</span></a></li>
<li class="col-3 col-sm-3 col-md-2"><a href="javascript:PThideMenu();"><i class="fa fa-solid fa-gear"></i><span class="pizzalayer-ui-icon-menu-tab">Settings</span></a></li>
</ul>
</div><div style="clear:both;padding:0;margin:0;"></div>
</div>';
}

/* -------- Tabs menu style : Buttons --------- */
function pizzalayer_tabs_menu_buttons(){
$pizzalayer_template_glassy_element_style_tabs = get_option('pizzalayer_setting_template_glass_element_style_tabs');

return '<!-- Pizzalayer : MENU UI -->
<div class="pizzalayer-ui-menu pizzalayer-ui-menu-row pizzalayer-ui-menu-icons pizzalayer-ui-menu-icons-' . $pizzalayer_template_glassy_element_style_tabs . '">' . pizzalayer_icons_menu_header() . '
<div id="pizzalayer-ui-menu-items" class="col-sm-12">
<ul>
<li class="col-lg-3 col-md-3 col-sm-3 col-3">
<button onClick="PTswitchToMenu(\'intro\');" title="Home" id="pizzalayer-icon-menu-item-intro" class="pizzalayer-icon-menu-item pizzalayer-icon-selected">
<div class="pizzalayer-icon-menu-item-icon-row"><i class="fa fa-solid fa-home"></i></div>Home
</button>
</li>
<li class="col-lg-3 col-md-3 col-sm-3 col-3">
<button onClick="PTswitchToMenu(\'crusts\');" title="Crusts" id="pizzalayer-icon-menu-item-crusts" class="pizzalayer-icon-menu-item">
<div class="pizzalayer-icon-menu-item-icon-row"><i class="fa fa-solid fa-circle"></i></div>Crusts
</button>
</li>
<li class="col-lg-3 col-md-3 col-sm-3 col-3">
<button onClick="PTswitchToMenu(\'sauces\');" title="Sauces" id="pizzalayer-icon-menu-item-sauces" class="pizzalayer-icon-menu-item">
<div class="pizzalayer-icon-menu-item-icon-row"><i class="fa fa-solid fa-tint"></i></div>Sauces
</button>
</li>
<li class="col-lg-3 col-md-3 col-sm-3 col-3">
<button onClick="PTswitchToMenu(\'cheeses\');" title="Cheeses" id="pizzalayer-icon-menu-item-cheeses" class="pizzalayer-icon-menu-item">
<div>
<div class="pizzalayer-icon-menu-item-icon-row"><i class="fa fa-solid fa-cheese"></i></div>Cheeses
</div></button>
</li>
<li class="col-lg-3 col-md-3 col-sm-3 col-3">
<button onClick="PTswitchToMenu(\'toppings\');" title="Toppings" id="pizzalayer-icon-menu-item-toppings" class="pizzalayer-icon-menu-item">
<div>
<div class="pizzalayer-icon-menu-item-icon-row"><i class="fa fa-solid fa-cookie"></i></div>Toppings
</div></button>
</li>
<li class="col-lg-3 col-md-3 col-sm-3 col-3">
<button onClick="PTswitchToMenu(\'drizzles\');" title="Drizzles" id="pizzalayer-icon-menu-item-drizzles" class="pizzalayer-icon-menu-item">
<div>
<div class="pizzalayer-icon-menu-item-icon-row"><i class="fa fa-solid fa-water"></i></div>Drizzles
</div></button>
</li>
<li class="col-lg-3 col-md-3 col-sm-3 col-3">
<button onClick="PTswitchToMenu(\'slices\');" title="Slicing" id="pizzalayer-icon-menu-item-slices" class="pizzalayer-icon-menu-item">
<div>
<div class="pizzalayer-icon-menu-item-icon-row"><i class="fa fa-solid fa-pizza-slice"></i></div>Slicing
</div></button>
</li>
</ul>
</div><div style="clear:both;padding:0;margin:0;"></div>
</div>';
}

/* -------- Tabs menu style : Buttons --------- */
function pizzalayer_tabs_menu_textbuttons(){
$pizzalayer_template_glassy_element_style_tabs = get_option('pizzalayer_setting_template_glass_element_style_tabs');

return '<!-- Pizzalayer : MENU UI -->
<div class="pizzalayer-ui-menu pizzalayer-ui-menu-row pizzalayer-ui-menu-icons pizzalayer-ui-menu-icons-' . $pizzalayer_template_glassy_element_style_tabs . '">' . pizzalayer_icons_menu_header() . '
<div id="pizzalayer-ui-menu-items" class="col-sm-12">
<ul>
<li class="col-lg-3 col-md-3 col-sm-3 col-3">
<button onClick="PTswitchToMenu(\'intro\');" title="Home" id="pizzalayer-icon-menu-item-intro" class="pizzalayer-icon-menu-item pizzalayer-icon-selected">Home</button>
</li>
<li class="col-lg-3 col-md-3 col-sm-3 col-3">
<button onClick="PTswitchToMenu(\'crusts\');" title="Crusts" id="pizzalayer-icon-menu-item-crusts" class="pizzalayer-icon-menu-item">Crusts</button>
</li>
<li class="col-lg-3 col-md-3 col-sm-3 col-3">
<button onClick="PTswitchToMenu(\'sauces\');" title="Sauces" id="pizzalayer-icon-menu-item-sauces" class="pizzalayer-icon-menu-item">Sauces</button>
</li>
<li class="col-lg-3 col-md-3 col-sm-3 col-3">
<button onClick="PTswitchToMenu(\'cheeses\');" title="Cheeses" id="pizzalayer-icon-menu-item-cheeses" class="pizzalayer-icon-menu-item">Cheeses</button>
</li>
<li class="col-lg-3 col-md-3 col-sm-3 col-3">
<button onClick="PTswitchToMenu(\'toppings\');" title="Toppings" id="pizzalayer-icon-menu-item-toppings" class="pizzalayer-icon-menu-item">Toppings</button>
</li>
<li class="col-lg-3 col-md-3 col-sm-3 col-3">
<button onClick="PTswitchToMenu(\'drizzles\');" title="Drizzles" id="pizzalayer-icon-menu-item-drizzles" class="pizzalayer-icon-menu-item">Drizzles</button>
</li>
<li class="col-lg-3 col-md-3 col-sm-3 col-3">
<button onClick="PTswitchToMenu(\'slices\');" title="Slicing" id="pizzalayer-icon-menu-item-slices" class="pizzalayer-icon-menu-item">Slicing</button>
</li>
</ul>
</div><div style="clear:both;padding:0;margin:0;"></div>
</div>';
}

/* -------- Tabs menu style : Icon Only --------- */
function pizzalayer_tabs_menu_icononly(){
$pizzalayer_template_glassy_element_style_tabs = get_option('pizzalayer_setting_template_glass_element_style_tabs');

return '<!-- Pizzalayer : MENU UI -->
<div class="pizzalayer-ui-menu pizzalayer-ui-menu-row pizzalayer-ui-menu-icons pizzalayer-ui-menu-icons-' . $pizzalayer_template_glassy_element_style_tabs . '">' . pizzalayer_icons_menu_header() . '
<div id="pizzalayer-ui-menu-items" class="col-sm-12">
<ul>
<li class="col-lg-1 col-md-1 col-sm-3 col-3">
<button onClick="PTswitchToMenu(\'intro\');" title="Home" id="pizzalayer-icon-menu-item-intro" class="pizzalayer-icon-menu-item pizzalayer-icon-selected">
<div class="pizzalayer-icon-menu-item-icon-row"><i class="fa fa-solid fa-home"></i></div>
</button>
</li>
<li class="col-lg-1 col-md-1 col-sm-3 col-3">
<button onClick="PTswitchToMenu(\'crusts\');" title="Crusts" id="pizzalayer-icon-menu-item-crusts" class="pizzalayer-icon-menu-item">
<div class="pizzalayer-icon-menu-item-icon-row"><i class="fa fa-solid fa-circle"></i></div>
</button>
</li>
<li class="col-lg-1 col-md-1 col-sm-3 col-3">
<button onClick="PTswitchToMenu(\'sauces\');" title="Sauces" id="pizzalayer-icon-menu-item-sauces" class="pizzalayer-icon-menu-item">
<div class="pizzalayer-icon-menu-item-icon-row"><i class="fa fa-solid fa-tint"></i></div>
</button>
</li>
<li class="col-lg-1 col-md-1 col-sm-3 col-3">
<button onClick="PTswitchToMenu(\'cheeses\');" title="Cheeses" id="pizzalayer-icon-menu-item-cheeses" class="pizzalayer-icon-menu-item">
<div>
<div class="pizzalayer-icon-menu-item-icon-row"><i class="fa fa-solid fa-cheese"></i></div>
</div></button>
</li>
<li class="col-lg-1 col-md-1 col-sm-3 col-3">
<button onClick="PTswitchToMenu(\'toppings\');" title="Toppings" id="pizzalayer-icon-menu-item-toppings" class="pizzalayer-icon-menu-item">
<div>
<div class="pizzalayer-icon-menu-item-icon-row"><i class="fa fa-solid fa-cookie"></i></div>
</div></button>
</li>
<li class="col-lg-1 col-md-1 col-sm-3 col-3">
<button onClick="PTswitchToMenu(\'drizzles\');" title="Drizzles" id="pizzalayer-icon-menu-item-drizzles" class="pizzalayer-icon-menu-item">
<div>
<div class="pizzalayer-icon-menu-item-icon-row"><i class="fa fa-solid fa-water"></i></div>
</div></button>
</li>
<li class="col-lg-1 col-md-1 col-sm-3 col-3">
<button onClick="PTswitchToMenu(\'slices\');" title="Slicing" id="pizzalayer-icon-menu-item-slices" class="pizzalayer-icon-menu-item">
<div>
<div class="pizzalayer-icon-menu-item-icon-row"><i class="fa fa-solid fa-pizza-slice"></i></div>
</div></button>
</li>
</ul>
</div><div style="clear:both;padding:0;margin:0;"></div>
</div>';
}

/* -------- Tabs menu style : Text with Icons --------- */
function pizzalayer_tabs_menu_text_with_icons(){
$pizzalayer_template_glassy_element_style_tabs = get_option('pizzalayer_setting_template_glass_element_style_tabs');
return '<!-- Pizzalayer : MENU UI -->
<div class="pizzalayer-ui-menu pizzalayer-ui-menu-row pizzalayer-ui-menu-icons pizzalayer-ui-menu-icons-' . $pizzalayer_template_glassy_element_style_tabs . '">' . pizzalayer_icons_menu_header() . '
<div id="pizzalayer-ui-menu-items" class="col-sm-12">
<a onClick="PTswitchToMenu(\'intro\');" title="Home" id="pizzalayer-icon-menu-item-intro" class="pizzalayer-icon-menu-item pizzalayer-icon-selected"><i class="fa fa-solid fa-home"></i> Home</a>
<a onClick="PTswitchToMenu(\'crusts\');" title="Crusts" id="pizzalayer-icon-menu-item-crusts" class="pizzalayer-icon-menu-item"><i class="fa fa-solid fa-circle"></i> Crusts</a>
<a onClick="PTswitchToMenu(\'sauces\');" title="Sauces" id="pizzalayer-icon-menu-item-sauces" class="pizzalayer-icon-menu-item"><i class="fa fa-solid fa-tint"></i> Sauces</a>
<a onClick="PTswitchToMenu(\'cheeses\');" title="Cheeses" id="pizzalayer-icon-menu-item-cheeses" class="pizzalayer-icon-menu-item"><i class="fa fa-solid fa-cheese"></i> Cheeses</a>
<a onClick="PTswitchToMenu(\'toppings\');" title="Toppings" id="pizzalayer-icon-menu-item-toppings" class="pizzalayer-icon-menu-item"><i class="fa fa-solid fa-cookie"></i> Toppings</a>
<a onClick="PTswitchToMenu(\'drizzles\');" title="Drizzles" id="pizzalayer-icon-menu-item-drizzles" class="pizzalayer-icon-menu-item"><i class="fa fa-solid fa-water"></i> Drizzles</a>
<a onClick="PTswitchToMenu(\'slices\');" title="Slicing" id="pizzalayer-icon-menu-item-slices" class="pizzalayer-icon-menu-item"><i class="fa fa-solid fa-pizza-slice"></i> Slicing</a>
</div><div style="clear:both;padding:0;margin:0;"></div>
</div>';
}

/* -------- Tabs menu style : Text Links --------- */
function pizzalayer_tabs_menu_text_links(){
$pizzalayer_template_glassy_element_style_tabs = get_option('pizzalayer_setting_template_glass_element_style_tabs');
return '<!-- Pizzalayer : MENU UI -->
<div class="pizzalayer-ui-menu pizzalayer-ui-menu-row pizzalayer-ui-menu-icons pizzalayer-ui-menu-icons-' . $pizzalayer_template_glassy_element_style_tabs . '">' . pizzalayer_icons_menu_header() . '
<div id="pizzalayer-ui-menu-items" class="col-sm-12">
<a onClick="PTswitchToMenu(\'intro\');" title="Home" id="pizzalayer-icon-menu-item-intro" class="pizzalayer-icon-menu-item pizzalayer-icon-selected">Home</a>
<a onClick="PTswitchToMenu(\'crusts\');" title="Crusts" id="pizzalayer-icon-menu-item-crusts" class="pizzalayer-icon-menu-item">Crusts</a>
<a onClick="PTswitchToMenu(\'sauces\');" title="Sauces" id="pizzalayer-icon-menu-item-sauces" class="pizzalayer-icon-menu-item">Sauces</a>
<a onClick="PTswitchToMenu(\'cheeses\');" title="Cheeses" id="pizzalayer-icon-menu-item-cheeses" class="pizzalayer-icon-menu-item">Cheeses</a>
<a onClick="PTswitchToMenu(\'toppings\');" title="Toppings" id="pizzalayer-icon-menu-item-toppings" class="pizzalayer-icon-menu-item">Toppings</a>
<a onClick="PTswitchToMenu(\'drizzles\');" title="Drizzles" id="pizzalayer-icon-menu-item-drizzles" class="pizzalayer-icon-menu-item">Drizzles</a>
<a onClick="PTswitchToMenu(\'slices\');" title="Slicing" id="pizzalayer-icon-menu-item-slices" class="pizzalayer-icon-menu-item">Slicing</a>
</div><div style="clear:both;padding:0;margin:0;"></div>
</div>';
}

/* -------- Tabs menu style : Dropdown --------- */
function pizzalayer_tabs_menu_dropdown(){
$pizzalayer_template_glassy_element_style_tabs = get_option('pizzalayer_setting_template_glass_element_style_tabs');
return '<!-- Pizzalayer : MENU UI -->
<div class="pizzalayer-ui-menu pizzalayer-ui-menu-row pizzalayer-ui-menu-icons pizzalayer-ui-menu-icons-' . $pizzalayer_template_glassy_element_style_tabs . '">' . pizzalayer_icons_menu_header() . '
<select name="pizzalayer-ui-menu-tabs-dropdown" id="pizzalayer-ui-menu-tabs-dropdown" onChange="PizzaLayerChangeTabBySelect()">
  <option value="intro" id="pizzalayer-icon-menu-item-intro" selected>Home</option>
  <option value="crusts" id="pizzalayer-icon-menu-item-crusts">Crusts</option>
  <option value="sauces" id="pizzalayer-icon-menu-item-sauces">Sauces</option>
  <option value="cheeses" id="pizzalayer-icon-menu-item-cheeses">Cheeses</option>
  <option value="toppings" id="pizzalayer-icon-menu-item-toppings">Toppings</option>
  <option value="drizzles" id="pizzalayer-icon-menu-item-drizzles">Drizzles</option>
  <option value="slices" id="pizzalayer-icon-menu-item-slices">Slices</option>
</select>
<script>
  function PizzaLayerChangeTabBySelect(){
    var selectElement = document.querySelector(\'#pizzalayer-ui-menu-tabs-dropdown\');
    PTselectToMenu(selectElement.value);
  }
</script></div>';
}

/* -------- Tabs menu style : Buttons --------- */
function pizzalayer_tabs_menu_mobile(){
$pizzalayer_template_glassy_element_style_tabs = get_option('pizzalayer_setting_template_glass_element_style_tabs');

return '<!-- Pizzalayer : MENU UI -->
<div class="pizzalayer-ui-menu pizzalayer-ui-menu-row pizzalayer-ui-menu-icons pizzalayer-ui-menu-icons-' . $pizzalayer_template_glassy_element_style_tabs . '">' . pizzalayer_icons_menu_header() . '
<div id="pizzalayer-ui-menu-items" class="col-sm-12">
<ul>
<li class="col-lg-3 col-md-3 col-sm-3 col-3">
<button onClick="PTswitchToMenu(\'intro\');" title="Home" id="pizzalayer-icon-menu-item-intro" class="pizzalayer-icon-menu-item pizzalayer-icon-selected">
<div class="pizzalayer-icon-menu-item-icon-row"><i class="fa fa-solid fa-home"></i></div>Home
</button>
</li>
<li class="col-lg-3 col-md-3 col-sm-3 col-3">
<button onClick="PTswitchToMenu(\'crusts\');" title="Crusts" id="pizzalayer-icon-menu-item-crusts" class="pizzalayer-icon-menu-item">
<div class="pizzalayer-icon-menu-item-icon-row"><i class="fa fa-solid fa-circle"></i></div>Crusts
</button>
</li>
<li class="col-lg-3 col-md-3 col-sm-3 col-3">
<button onClick="PTswitchToMenu(\'sauces\');" title="Sauces" id="pizzalayer-icon-menu-item-sauces" class="pizzalayer-icon-menu-item">
<div class="pizzalayer-icon-menu-item-icon-row"><i class="fa fa-solid fa-tint"></i></div>Sauces
</button>
</li>
<li class="col-lg-3 col-md-3 col-sm-3 col-3">
<button onClick="PTswitchToMenu(\'cheeses\');" title="Cheeses" id="pizzalayer-icon-menu-item-cheeses" class="pizzalayer-icon-menu-item">
<div>
<div class="pizzalayer-icon-menu-item-icon-row"><i class="fa fa-solid fa-cheese"></i></div>Cheeses
</div></button>
</li>
<li class="col-lg-3 col-md-3 col-sm-3 col-3">
<button onClick="PTswitchToMenu(\'toppings\');" title="Toppings" id="pizzalayer-icon-menu-item-toppings" class="pizzalayer-icon-menu-item">
<div>
<div class="pizzalayer-icon-menu-item-icon-row"><i class="fa fa-solid fa-cookie"></i></div>Toppings
</div></button>
</li>
<li class="col-lg-3 col-md-3 col-sm-3 col-3">
<button onClick="PTswitchToMenu(\'drizzles\');" title="Drizzles" id="pizzalayer-icon-menu-item-drizzles" class="pizzalayer-icon-menu-item">
<div>
<div class="pizzalayer-icon-menu-item-icon-row"><i class="fa fa-solid fa-water"></i></div>Drizzles
</div></button>
</li>
<li class="col-lg-3 col-md-3 col-sm-3 col-3">
<button onClick="PTswitchToMenu(\'slices\');" title="Slicing" id="pizzalayer-icon-menu-item-slices" class="pizzalayer-icon-menu-item">
<div>
<div class="pizzalayer-icon-menu-item-icon-row"><i class="fa fa-solid fa-pizza-slice"></i></div>Slicing
</div></button>
</li>
</ul>
</div><div style="clear:both;padding:0;margin:0;"></div>
</div>';
}

/* -------- Panels Container --------- */
function pizzalayer_panels_container(){
    
};

/* -------- Panels --------- */
function pizzalayer_panels(){
    return '<div class="pizzalayer-ui-menu row">

<!-- Panel 1: Intro -->    
<div id="pizzalayer-ui-menu-section-intro" class="pizzalayer-ui-menu-col pizzalayer-ui-menu-tab pizzalayer-ui-menu-col-intro col-sm-12 pizzalayer-full-height">' . pizzalayer_topvis_title('Welcome','pizza-slice') . '<div id="pizzalayer-intro-content">Thanks for trying out PizzaLayer! Please check out each section in the menu above to customize your Pizza. <hr/><strong>
Important - This is just a demo</strong></div>
' . pizzalayer_control_nextprev('','crusts','pizzalayer-controls-panel1') . '
</div>

<!-- Panel 2: Crusts -->
<div id="pizzalayer-ui-menu-section-crusts" class="pizzalayer-ui-menu-col pizzalayer-ui-menu-tab pizzalayer-ui-menu-col-crusts col-sm-12 pizzalayer-full-height">' . pizzalayer_topvis_title('Crusts','pizza-slice') .
pizzalayer_ui_approws_v2('crusts','') . pizzalayer_control_nextprev('intro','sauces','pizzalayer-controls-panel2') . '</div>

<!-- Panel 3: Sauces -->
<div id="pizzalayer-ui-menu-section-sauces" class="pizzalayer-ui-menu-col pizzalayer-ui-menu-tab pizzalayer-ui-menu-col-sauces col-sm-12 pizzalayer-full-height">' . pizzalayer_topvis_title('Sauces','leaf') . pizzalayer_ui_approws_v2('sauces','') . pizzalayer_control_nextprev('crusts','cheeses','pizzalayer-controls-panel2') . '</div>

<!-- Panel 4: Cheeses -->
<div id="pizzalayer-ui-menu-section-cheeses" class="pizzalayer-ui-menu-col pizzalayer-ui-menu-tab pizzalayer-ui-menu-col-cheeses col-sm-12 pizzalayer-full-height">' . pizzalayer_topvis_title('Cheeses','cheese') . pizzalayer_ui_approws_v2('cheeses','') . pizzalayer_control_nextprev('sauces','drizzles','pizzalayer-controls-panel4') . '</div>

<!-- Panel 5: Drizzles -->
<div id="pizzalayer-ui-menu-section-drizzles" class="pizzalayer-ui-menu-col pizzalayer-ui-menu-tab pizzalayer-ui-menu-col-drizzles col-sm-12 pizzalayer-full-height">' . pizzalayer_topvis_title('Drizzles','water') . pizzalayer_ui_approws_v2('drizzles','') . pizzalayer_control_nextprev('cheeses','toppings','pizzalayer-controls-panel2') . '</div>

<!-- Panel 6: Toppings -->
<div id="pizzalayer-ui-menu-section-toppings" class="pizzalayer-ui-menu-col pizzalayer-ui-menu-tab pizzalayer-ui-menu-col-toppings col-sm-12 pizzalayer-full-height">' . pizzalayer_topvis_title('Toppings','pizza-slice') . pizzalayer_ui_approws_toppings('toppings','') . pizzalayer_control_nextprev('drizzles','slices','pizzalayer-controls-panel2') . '</div>    

<!-- Panel 7: Slicing -->
<div id="pizzalayer-ui-menu-section-slices" class="pizzalayer-ui-menu-col pizzalayer-ui-menu-tab pizzalayer-ui-menu-col-slices col-sm-12 pizzalayer-full-height">' . pizzalayer_topvis_title('Slices','pizza-slice') . pizzalayer_ui_approws_v2('cuts','') . pizzalayer_control_nextprev('toppings','','pizzalayer-controls-panel2') . '</div>

<!-- Panel 8: Help -->
<div id="pizzalayer-ui-menu-section-help" class="pizzalayer-ui-menu-col pizzalayer-ui-menu-tab pizzalayer-ui-menu-col-toppings col-sm-12 pizzalayer-full-height">' . pizzalayer_topvis_title('Help','pizza-slice') . '</div>

<!-- Panel 9: Settings -->
<div id="pizzalayer-ui-menu-section-settings" class="pizzalayer-ui-menu-col pizzalayer-ui-menu-tab pizzalayer-ui-menu-col-toppings col-sm-12 pizzalayer-full-height">' . pizzalayer_topvis_title('Settings','pizza-slice') . pizzalayer_ui_settings_menu() . '</div>

</div>

<!-- / Pizzalayer : MENU UI -->';    
}

function pizzalayer_ui_settings_menu(){ return <<<HTML
<h4>Rotate pizza slowly?</h4>
<div>
<a href="javascript:RotatePizza('pizzalayer-pizza',1);" class="pizzalayer-button">1X</a>
<a href="javascript:RotatePizza('pizzalayer-pizza',.5);" class="pizzalayer-button">.5X</a>
<a href="javascript:RotatePizza('pizzalayer-pizza',.25);" class="pizzalayer-button">.25X</a>
<a href="javascript:RotatePizza('pizzalayer-pizza',.05);" class="pizzalayer-button">.05X</a>
<a href="javascript:StopPizza('pizzalayer-pizza');" class="pizzalayer-button">STOP</a>
</div>
HTML;
}

function pizzalayer_woocommerce_actions(){
    return '<!-- Pizzalayer : ORDER ACTIONS  -->
<div class="pizzalayer-ui-menu row">
<div class="pizzalayer-ui-menu-col col-lg-12 col-md-12"> 
<a href="#" class="pizzalayer-button"><i class="fa fa-cart-plus"></i> Add to Cart</a>' . pizzalayer_template_glass_options_reset_button('cta') . '
<a href="#" class="pizzalayer-button" style="float:right;"><i class="fa fa-pizza-slice"></i> $0.00</a>
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



function pizzalayer_current_toppings(){
    return '<div id="pizzalayer-ui-menu-section-toppings" class="pizzalayer-ui-menu-col pizzalayer-ui-menu-tab pizzalayer-ui-menu-col-toppings col-sm-12 pizzalayer-full-height"></div><div id="pizzalayer-current-toppings-footer"></div>';
}

function pizzalayer_selection_tile($pizzalayer_tile_title,$pizzalayer_tile_slug,$pizzalayer_tile_placeholder){
    return '<div id="pizzalayer-basics-tile-' . $pizzalayer_tile_slug . '" class="col-md-3 col-sm-12 pizzalayer-basics-tile"><div class="pizzalayer-basics-tile-wrapper"><h3>' . $pizzalayer_tile_title . '</h3><div id="pizzalayer-basics-tile-title-' . $pizzalayer_tile_slug . '" class="pizzalayers-basics-tile-title">' . $pizzalayer_tile_placeholder . '</div></div></div>';
}
function pizzalayer_selection_tile_100percent($pizzalayer_tile_title,$pizzalayer_tile_slug,$pizzalayer_tile_placeholder){
    return '<div id="pizzalayer-basics-tile-' . $pizzalayer_tile_slug . '" class="col-md-12 col-sm-12 pizzalayer-basics-tile"><div class="pizzalayer-basics-tile-wrapper"><h3>' . $pizzalayer_tile_title . '</h3><div id="pizzalayer-basics-tile-title-' . $pizzalayer_tile_slug . '" class="pizzalayers-basics-tile-title">' . $pizzalayer_tile_placeholder . '</div></div></div>';
}

function pizzalayer_template_glass_options_reset_button($ResetButtonLocation){
//get field for reset location from user options     
$ResetButtonLocationPreference = get_option('pizzalayer_setting_template_glass_button_reset_location');
if($ResetButtonLocationPreference !== $ResetButtonLocation && $ResetButtonLocationPreference !== 'all'){ return false; }
//compare field and passed $ResetButtonLocation to determine if reset button should be returned
//possible values:top,cta,foot,all
if($ResetButtonLocation == 'top'){ return '<a href="javascript:ClearPizza();"><i class="fa fa-solid fa-trash"></i> Reset Pizza</a>'; };
if($ResetButtonLocation == 'cta'){ return '<a href="javascript:ClearPizza()" class="pizzalayer-button"><i class="fa fa-solid fa-trash"></i> Reset Pizza</a>'; };
if($ResetButtonLocation == 'foot'){ return '<a href="javascript:ClearPizza();" title="reset"><i class="fa fa-solid fa-trash"></i></a>'; };
return false;
} //close function

/* +==============================================================+
   | PIZZALAYER : Render dynamic CPT options with fallback images |
   +==============================================================+ */
function pizzalayer_render_options_from_cpt($cpt, $layer_slug) {
	$args = array(
		'post_type'      => $cpt,
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	);

	$query = new WP_Query($args);
	$html = '<div class="options-grid">';

	// Determine the short singular name of the CPT (e.g. pizzalayer_cheeses → cheese)
	$short_singular = strtolower(rtrim(str_replace('pizzalayer_', '', $cpt), 's'));

	if ($query->have_posts()) {
		while ($query->have_posts()) {
			$query->the_post();

			$title = get_the_title(); // Full display title
			$desc  = get_the_excerpt();
			$post_id = get_the_ID();

			// Attempt ACF fields in priority order
			$image_url = get_field($short_singular . '_layer_image', $post_id);

			if (!$image_url) {
				$image_url = get_field($short_singular . '_image', $post_id);
			}

			if (!$image_url) {
				$image_url = get_the_post_thumbnail_url($post_id, 'medium');
			}

			// Generate safe slug
			$title_slug = sanitize_title($title);
			$layer_slug_formatted = str_replace(' ', '-', strtolower($title)); // used as second param in JS
			$function_slug = 'pizzalayer-topping-' . $short_singular;
//'#menu-pizzalayer-topping-' + NewPizzaLayerShort
      
			$function_alt = 'pizzalayer-' . $short_singular . '-' . esc_js($title);

			// JS call string
			$js = "javascript:AddPizzaLayer('', '{$layer_slug_formatted}', '{$image_url}', '" . esc_js($title) . "', '{$function_slug}', '{$function_alt}')";

			// Output card
			$html .= '<div class="option-card" data-layer="' . esc_attr($layer_slug) . '" data-title="' . esc_attr($title) . '">';
			$html .= '  <div class="option-circle" style="background-image:url(' . esc_url($image_url) . '); background-size:cover;"></div>';
			$html .= '  <div class="option-title">' . esc_html($title) . '</div>';
			$html .= '  <div class="option-description">' . esc_html($desc) . '</div>';
			$html .= '  <div class="option-action"><button class="add-remove-btn" onclick="' . esc_attr($js) . '">Add</button></div>';
			$html .= '</div>';
		}
		wp_reset_postdata();
	} else {
		$html .= '<p>No options available.</p>';
	}

	$html .= '</div>';
	return $html;
}






do_action( 'pizzalayer_file_pztp-template-custom_end' );