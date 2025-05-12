<?php
function pizzalayer_customize_register_glassy( $wp_customize ) {

/* =========== ADD CUSTOMIZER PANEL ============== v */    
$wp_customize->add_panel( 'pizzalayer_admin_panel_template_glassy', array(
	'title'          => 'Pizzalayer - Glassy Template Options',
	'description'    => 'The full setup for customizing the glassy template for PizzaLayer',
) );    
/* =========== ADD CUSTOMIZER PANEL SECTIONS ============== v */  
$wp_customize->add_section( 'pizzalayer_panel_section_layout' , array(
	'title'             => 'Layout',
	'description'       => 'Here you can select one of the pre-defined layouts for the Glassy Template',
	'panel'             => 'pizzalayer_admin_panel_template_glassy',
) );

$wp_customize->add_section( 'pizzalayer_panel_section_template_features' , array(
	'title'             => 'Features',
	'description'       => 'Here you can choose which features of the Glassy Template to display and use',
	'panel'             => 'pizzalayer_admin_panel_template_glassy',
) );

$wp_customize->add_section( 'pizzalayer_panel_section_tabs_options' , array(
	'title'             => 'Tabs Options',
	'description'       => 'Here you can choose choose settings for the Tabs',
	'panel'             => 'pizzalayer_admin_panel_template_glassy',
) );

$wp_customize->add_section( 'pizzalayer_panel_section_colors' , array(
	'title'             => 'Colors',
	'description'       => 'Easily set all template colors from one place',
	'panel'             => 'pizzalayer_admin_panel_template_glassy',
) );

$wp_customize->add_section( 'pizzalayer_panel_section_display_options' , array(
	'title'             => 'Display Options',
	'description'       => 'Here you can choose basic display options for the Glassy Template',
	'panel'             => 'pizzalayer_admin_panel_template_glassy',
) );

$wp_customize->add_section( 'pizzalayer_panel_section_advanced' , array(
	'title'             => 'Advanced Options',
	'description'       => 'These are features that may require code or technical expertise. Please be careful when editing these fields. These do not have to be set for PizzaLayer to work.',
	'panel'             => 'pizzalayer_admin_panel_template_glassy',
) );

/* =========== START ADDING SETTINGS TO CUSTOMIZER PANELS ============== v */  

/* === SETTING --> glass template layout selected ============== v */

$wp_customize->add_setting( 'pizzalayer_setting_template_glass_layout' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_template_glass_layout_control', array(
	'label'      => 'Layout',
	'description'=> 'select a layout option for the Glass Template. To see examples, please refer to the plugin documentation.',
	'section'    => 'pizzalayer_panel_section_layout',
	'settings'   => 'pizzalayer_setting_template_glass_layout',
    'type'    => 'select',
        'choices'    => array(
            'sidebyside' => 'Side by Side',
            'noterecipe' => 'Note Recipe',
            'appstyle' => 'App Style',
            'orderboard' => 'Order Board',
            'pizzaonly' => 'Minimal',
        ),
) );


/* === SETTING --> paginate tab content (all types) ============== v */

$wp_customize->add_setting( 'pizzalayer_setting_template_glass_pagination' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_template_glass_pagination_control', array(
	'label'      => 'Pagination for tab content',
	'description'=> 'pagination or all scrolling for layer options',
	'section'    => 'pizzalayer_panel_section_tabs_options',
	'settings'   => 'pizzalayer_setting_template_glass_pagination',
	'type'    => 'select',
        'choices'    => array(
            '12' => 'Pagination',
            '-1' => 'Show All Items',
        ),
) );

/* === SETTING --> choose location for reset button ============== v */

$wp_customize->add_setting( 'pizzalayer_setting_template_glass_button_reset_location' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_template_glass_button_reset_location_control', array(
	'label'      => 'Reset Button Location',
	'description'=> 'choose location for reset button',
	'section'    => 'pizzalayer_panel_section_display_options',
	'settings'   => 'pizzalayer_setting_template_glass_button_reset_location',
		'type'    => 'select',
        'choices'    => array(
            'top' => 'Top Bar',
            'cta' => 'Call to Action Bar',
            'foot' => 'Below Tab Content in Main Area',
            'all' => 'Show in All Locations',
        ),
) );

/* === SETTING --> switch sides for side-by-side template ============== v */

$wp_customize->add_setting( 'pizzalayer_setting_template_glass_sidebyside_switch' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_template_glass_sidebyside_switch_control', array(
	'label'      => 'Switch SideBySide Sides',
	'description'=> 'For Side-By-Side template, move tabs to left and pizza to right.',
	'section'    => 'pizzalayer_panel_section_display_options',
	'settings'   => 'pizzalayer_setting_template_glass_sidebyside_switch',
		'type'    => 'select',
        'choices'    => array(
            'yes' => 'yes',
            'no' => 'no',
        ),
) );

/* === SETTING --> Display my Recipe section? ============== v */

$wp_customize->add_setting( 'pizzalayer_setting_template_glass_display_my_recipe' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_template_glass_display_my_recipe_control', array(
	'label'      => 'Display My Recipe Section',
	'description'=> 'Toggle the My Recipe Section',
	'section'    => 'pizzalayer_panel_section_template_features',
	'settings'   => 'pizzalayer_setting_template_glass_display_my_recipe',
	 'type'    => 'select',
     'choices'    => array('show' => 'Show','hide' => 'Hide'),
));

/* === SETTING --> Display Icon menu below the Tab content? ============== v */

$wp_customize->add_setting( 'pizzalayer_setting_template_glass_display_tabs_icons_menu' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_template_glass_display_tabs_icons_menu_control', array(
	'label'      => 'Display Icon Menu',
	'description'=> 'toggle the icons menu at the bottom of the tabs',
	'section'    => 'pizzalayer_panel_section_template_features',
	'settings'   => 'pizzalayer_setting_template_glass_display_tabs_icons_menu',
	 'type'    => 'select',
     'choices'    => array('show' => 'Show','hide' => 'Hide'),
));

/* === SETTING --> Main Wrapper effect ============== v */

$wp_customize->add_setting( 'pizzalayer_setting_template_glass_wrapper_effect' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_template_glass_wrapper_effect_control', array(
	'label'      => 'Main Wrapper Effect',
	'description'=> 'Select a CSS effect for the main wrapper',
	'section'    => 'pizzalayer_panel_section_display_options',
	'settings'   => 'pizzalayer_setting_template_glass_wrapper_effect',
	 'type'    => 'select',
     'choices'    => array('none' => 'None','dropshadow' => 'Drop Shadow','borderless' => 'Borderless'),
));

/* === SETTING --> toggle the UI header ============== v */

$wp_customize->add_setting( 'pizzalayer_setting_template_glass_display_header' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_template_glass_display_header_control', array(
	'label'      => 'Display the UI Header',
	'description'=> 'Toggle the UI Header',
	'section'    => 'pizzalayer_panel_section_template_features',
	'settings'   => 'pizzalayer_setting_template_glass_display_header',
		 'type'    => 'select',
    'choices'    => array('show' => 'Show','hide' => 'Hide'),
) );

/* === SETTING --> toggle the collected toppings list ============== v */

$wp_customize->add_setting( 'pizzalayer_setting_template_glass_display_topping_list' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_template_glass_display_topping_list_control', array(
	'label'      => 'Display the Current Toppings List',
	'description'=> 'Toggle the Current Toppings List',
	'section'    => 'pizzalayer_panel_section_template_features',
	'settings'   => 'pizzalayer_setting_template_glass_display_topping_list',
		 'type'    => 'select',
    'choices'    => array('show' => 'Show','hide' => 'Hide'),
) );

/* === SETTING --> toggle the CTA bar at the bottom ============== v */

$wp_customize->add_setting( 'pizzalayer_setting_template_glass_display_action_bar' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_template_glass_display_action_bar_control', array(
	'label'      => 'Display Call to Action Bar',
	'description'=> 'Toggle the call to action bar at the bottom',
	'section'    => 'pizzalayer_panel_section_template_features',
	'settings'   => 'pizzalayer_setting_template_glass_display_action_bar',
		 'type'    => 'select',
 'choices'    => array('show' => 'Show','hide' => 'Hide'),
) );

/* === SETTING --> toggle next and previous bar in each section ============== v */

$wp_customize->add_setting( 'pizzalayer_setting_template_glass_display_section_nextprev' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_template_glass_display_section_nextprev_control', array(
	'label'      => 'Display Next/Previous bar',
	'description'=> 'Toggle the Next/Previous bar in each tab at the bottom of the content',
	'section'    => 'pizzalayer_panel_section_tabs_options',
	'settings'   => 'pizzalayer_setting_template_glass_display_section_nextprev',
		 'type'    => 'select',
     'choices'    => array('show' => 'Show','hide' => 'Hide'),
) );

/* === SETTING --> Custom Vars for Glass Template ============== v */

$wp_customize->add_setting( 'pizzalayer_setting_template_glass_vars' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_template_glass_vars_control', array(
	'label'      => 'Custom / Expansion Variables',
	'description'=> 'This stores additional information for your Template, to be used programmatically. Caution : Please edit with care - this data must be formatted carefully to avoid causing any errors.',
	'section'    => 'pizzalayer_panel_section_advanced',
	'settings'   => 'pizzalayer_setting_template_glass_vars',
	'type'       => 'text',
) );

/* === SETTING --> background color for main container ============== v */

  $wp_customize->add_setting('pizzalayer_setting_template_glass_background', array(
        'default'           => 'rgba(0,0,0,.4)',
        'capability'        => 'edit_theme_options',
        'type'           => 'option',
        'transport' => 'refresh',
    ));
 $wp_customize->add_control( new WP_Customize_Color_Control($wp_customize, 'pizzalayer_setting_template_glass_background_control', array(
      		'label'      => 'Main Wrapper Background Color',
	'description'=> 'Please choose a background color (may include transparency) for the main wrapper featured in the Glass template.',
	'section'    => 'pizzalayer_panel_section_colors',
	'settings'   => 'pizzalayer_setting_template_glass_background',
    )));
    
/* === SETTING --> background opacity for main container ============== v */

$wp_customize->add_setting( 'pizzalayer_setting_template_glass_background_opacity' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_template_glass_background_opacity_control', array(
	'label'      => 'Main Wrapper Background Opacity',
	'description'=> '0 to 100, where 100 is fully opaque and 0 is transparent',
	'section'    => 'pizzalayer_panel_section_colors',
	'settings'   => 'pizzalayer_setting_template_glass_background_opacity',
	'type'       => 'text',
) );

/* === SETTING -->  a CSS effect for main container background ============== v */

$wp_customize->add_setting( 'pizzalayer_setting_template_glass_background_effect' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_template_glass_background_effect_control', array(
	'label'      => 'Main Wrapper Background Effect',
	'description'=> 'Select a CSS effect for main container background',
	'section'    => 'pizzalayer_panel_section_display_options',
	'settings'   => 'pizzalayer_setting_template_glass_background_effect',
	 'type'    => 'select',
     'choices'    => array('none' => 'None','blur' => 'Blur','brightness' => 'Brightness','contrast' => 'Contrast','dropshadow' => 'Drop Shadow','grayscale' => 'Grayscale','huerotate' => 'Hue Rotate','invert' => 'Invert','opacity' => 'Opacity','sepia' => 'Sepia','saturate' => 'Saturate',),
) );
    
/* === SETTING --> background color for tabs container ============== v */

  $wp_customize->add_setting('pizzalayer_setting_template_glass_background_tabscontainer', array(
        'default'           => 'rgba(0,0,0,.4)',
        'capability'        => 'edit_theme_options',
        'type'           => 'option',
        'transport' => 'refresh',
    ));
 $wp_customize->add_control( new WP_Customize_Color_Control($wp_customize, 'pizzalayer_setting_template_glass_background_tabscontainer_control', array(
      		'label'      => 'Tabs Container Background Color',
	'description'=> 'Please choose a background color (may include transparency) for the tabs and content container featured in the Glass template.',
	'section'    => 'pizzalayer_panel_section_colors',
	'settings'   => 'pizzalayer_setting_template_glass_background_tabscontainer',
    )));
    
/* === SETTING --> background opacity for tabs container ============== v */

$wp_customize->add_setting( 'pizzalayer_setting_template_glass_tabscontainer_opacity' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_template_glass_tabscontainer_opacity_control', array(
	'label'      => 'Tabs Container Background Opacity',
	'description'=> '0 to 100, where 100 is fully opaque and 0 is transparent',
	'section'    => 'pizzalayer_panel_section_colors',
	'settings'   => 'pizzalayer_setting_template_glass_tabscontainer_opacity',
	'type'       => 'text',
) );
    
/* === SETTING --> Header text content ============== v */

$wp_customize->add_setting( 'pizzalayer_setting_template_glass_header_text' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_template_glass_header_text_control', array(
	'label'      => 'Header Middle Content',
	'description'=> 'Optional text/HTML content for the middle pane in the header',
	'section'    => 'pizzalayer_panel_section_display_options',
	'settings'   => 'pizzalayer_setting_template_glass_header_text',
	'type'       => 'textarea',
) );



/* === SETTING --> text color in glass template UI ============== v */

  $wp_customize->add_setting('pizzalayer_setting_template_glass_text_color', array(
        'default'           => '#000000',
        'sanitize_callback' => 'sanitize_hex_color',
        'capability'        => 'edit_theme_options',
        'type'           => 'option',
        'transport' => 'refresh',
    ));
 $wp_customize->add_control( new WP_Customize_Color_Control($wp_customize, 'pizzalayer_setting_template_glass_text_color_control', array(
        'label'      => 'Text Color',
        'section'  => 'pizzalayer_panel_section_colors',
        'settings' => 'pizzalayer_setting_template_glass_text_color',
    )));

/* === SETTING --> title color in glass template UI ============== v */

  $wp_customize->add_setting('pizzalayer_setting_template_glass_title_color', array(
        'default'           => '#000000',
        'sanitize_callback' => 'sanitize_hex_color',
        'capability'        => 'edit_theme_options',
        'type'           => 'option',
        'transport' => 'refresh',
    ));
 $wp_customize->add_control( new WP_Customize_Color_Control($wp_customize, 'pizzalayer_setting_template_glass_title_color_control', array(
       	'label'      => 'Title Color',
	    'description'=> 'title color in glass template UI',
        'section'  => 'pizzalayer_panel_section_colors',
        'settings' => 'pizzalayer_setting_template_glass_title_color',
    )));

/* === SETTING --> menu type for glass template header ============== v */

$wp_customize->add_setting( 'pizzalayer_setting_template_glass_header_menu_type' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_template_glass_header_menu_type_control', array(
	'label'      => 'Header Menu Type',
	'description'=> 'menu type for glass template header',
	'section'    => 'pizzalayer_panel_section_display_options',
	'settings'   => 'pizzalayer_setting_template_glass_header_menu_type',
	'type'    => 'select',
        'choices'    => array(
            'default' => 'Default',
            'button' => 'Button',
            'icontext' => 'Icon and Text',
            'mobilebars' => 'Mobile - Bars',
            'mobilelabel' => 'Mobile - Label',
            'none' => 'None',
        ),
) );

/* === SETTING --> accent color for glass template elements ============== v */

  $wp_customize->add_setting('pizzalayer_setting_template_glass_accent_color', array(
        'default'           => '#000000',
        'sanitize_callback' => 'sanitize_hex_color',
        'capability'        => 'edit_theme_options',
        'type'           => 'option',
        'transport' => 'refresh',
    ));
 $wp_customize->add_control( new WP_Customize_Color_Control($wp_customize, 'pizzalayer_setting_template_glass_accent_color_control', array(
      	'label'      => 'Accent Color',
	'description'=> 'accent color for glass template elements',
	'section'    => 'pizzalayer_panel_section_colors',
        'settings' => 'pizzalayer_setting_template_glass_accent_color',
    )));

/* === SETTING --> a JS array of animations for each element on load ============== v */

$wp_customize->add_setting( 'pizzalayer_setting_template_glass_animations_array' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_template_glass_animations_array_control', array(
	'label'      => 'Custom Animations Array',
	'description'=> 'An optional javascript array of animations to apply to UI elements. Warning - improper settings may cause errors that prevent your Pizza builder from working. Please see the plugin documentation for instructions. ',
	'section'    => 'pizzalayer_panel_section_advanced',
	'settings'   => 'pizzalayer_setting_template_glass_animations_array',
	'type'       => 'text',
) );

/* === SETTING --> Lists / Layers Menu in Tab Content Style ============== v */

$wp_customize->add_setting( 'pizzalayer_setting_template_glass_element_style_lists' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_template_glass_element_style_lists_control', array(
	'label'      => 'Lists and Layers Style',
	'description'=> 'Select a style for the Lists and Layers content',
	'section'    => 'pizzalayer_panel_section_tabs_options',
	'settings'   => 'pizzalayer_setting_template_glass_element_style_lists',
		'type'    => 'select',
        'choices'    => array(
            'default' => 'Default',
        ),
) );

/* === SETTING --> My Recipe Style ============== v */

$wp_customize->add_setting( 'pizzalayer_setting_template_glass_element_style_myrecipe' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_template_glass_element_style_myrecipe_control', array(
	'label'      => 'My Recipe Style',
	'description'=> 'Select a style for the My Recipe section.',
	'section'    => 'pizzalayer_panel_section_element_style',
	'settings'   => 'pizzalayer_setting_template_glass_element_style_myrecipe',
	'type'    => 'select',
        'choices'    => array(
            'keyingredients' => 'Highlighted Ingredients w/Toppings',
            'bubblebuttons' => 'Bubble Buttons',
            'checklist' => 'Checklist',
            'dualchecklist' => 'Dual Checklist',
            'singlechecklist' => 'Single Checklist',
        ),
) );

/* === SETTING --> Header Style ============== v */

$wp_customize->add_setting( 'pizzalayer_setting_template_glass_element_style_header' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_template_glass_element_style_header_control', array(
	'label'      => 'Header Style',
	'description'=> 'Select a style for the header at the top of the UI.',
	'section'    => 'pizzalayer_panel_section_element_style',
	'settings'   => 'pizzalayer_setting_template_glass_element_style_header',
		'type'    => 'select',
        'choices'    => array(
            'traditional' => '1 - Traditional',
            'crisp' => '2 - Crisp',
            'center' => '3 - Center',
            'curve' => '4 - Curve',
            'thin' => '5 - Thin',
            'logocentric' => '6 - Logocentric',
        ),
) );

/* === SETTING --> Tab Menu Style ============== v */

$wp_customize->add_setting( 'pizzalayer_setting_template_glass_element_style_tabs' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_template_glass_element_style_tabs_control', array(
	'label'      => 'Tab Menu Style',
	'description'=> 'Select a style for the tab menu that shows up above the layer choices.',
	'section'    => 'pizzalayer_panel_section_element_style',
	'settings'   => 'pizzalayer_setting_template_glass_element_style_tabs',
	'type'    => 'select',
        'choices'    => array(
            'buttonicon' => 'Button w/Icon',
            'buttonrounded' => 'Rounded Button',
            'textunderline' => 'Underlined Text',
            'texticonunderline' => 'Underlined Text w/Icon',
            'dropdown' => 'Dropdown Menu w/ current item displayed',
            'icononly' => 'Icon Only',
            'mobile' => 'Mobile / Bar Menu',
        ),
) );


/* =========== END ADDING SETTINGS  ============== */ 
} //function
add_action('customize_register', 'pizzalayer_customize_register_glassy', 5);
