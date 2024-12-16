<?php
function pizzalayer_customize_register_glassy( $wp_customize ) {

/* =========== ADD CUSTOMIZER PANEL ============== v */    
$wp_customize->add_panel( 'pizzalayer_admin_panel_template_glassy', array(
	'title'          => 'Pizzalayer - Glassy Template Options',
	'description'    => 'The full setup for customizing the glassy template for PizzaLayer',
) );    
/* =========== ADD CUSTOMIZER PANEL SECTIONS ============== v */  
$wp_customize->add_section( 'pizzalayer_panel_section_template_features' , array(
	'title'             => 'Template Features',
	'description'       => 'Here you can choose which features of the Glassy Template to use',
	'panel'             => 'pizzalayer_admin_panel_template_glassy',
) );

$wp_customize->add_section( 'pizzalayer_panel_section_layout' , array(
	'title'             => 'Select Layout',
	'description'       => 'Here you can select one of the pre-defined layouts for the Glassy Template',
	'panel'             => 'pizzalayer_admin_panel_template_glassy',
) );

$wp_customize->add_section( 'pizzalayer_panel_section_element_styles' , array(
	'title'             => 'Select Element Styles',
	'description'       => 'Here you can select one of the pre-defined layouts for each of the display areas',
	'panel'             => 'pizzalayer_admin_panel_template_glassy',
) );

$wp_customize->add_section( 'pizzalayer_panel_section_display_options' , array(
	'title'             => 'Display Options',
	'description'       => 'Here you can choose basic display options for the Glassy Template',
	'panel'             => 'pizzalayer_admin_panel_template_glassy',
) );

$wp_customize->add_section( 'pizzalayer_panel_section_advanced' , array(
	'title'             => 'Advanced Options',
	'description'       => 'These are features that may require code or technical expertise. Please be careful when editing these fields.',
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
            'value1' => 'Default',
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
	'section'    => 'pizzalayer_panel_section_display_options',
	'settings'   => 'pizzalayer_setting_template_glass_pagination',
	'type'    => 'select',
        'choices'    => array(
            'value1' => 'Paginate',
            'value2' => 'Infinite Load',
            'value3' => 'Show All Items',
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
            'value1' => 'Top Bar',
            'value2' => 'Call to Action Bar',
            'value3' => 'Below Tab Content in Main Area',
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
	'section'    => 'pizzalayer_panel_section_template_features',
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
      		'label'      => 'Main Container Background Color',
	'description'=> 'Please choose a background color (may include transparency) for the main wrapper featured in the Glass template.',
	'section'    => 'pizzalayer_panel_section_display_options',
	'settings'   => 'pizzalayer_setting_template_glass_background',
    )));

/* === SETTING -->  a CSS effect for main container background ============== v */

$wp_customize->add_setting( 'pizzalayer_setting_template_glass_background_effect' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_template_glass_background_effect_control', array(
	'label'      => 'Background Effect',
	'description'=> 'Select a CSS effect for main container background',
	'section'    => 'pizzalayer_panel_section_display_options',
	'settings'   => 'pizzalayer_setting_template_glass_background_effect',
	 'type'    => 'select',
     'choices'    => array('none' => 'None','blur' => 'Blur','brightness' => 'Brightness','contrast' => 'Contrast','dropshadow' => 'Drop Shadow','grayscale' => 'Grayscale','huerotate' => 'Hue Rotate','invert' => 'Invert','opacity' => 'Opacity','sepia' => 'Sepia','saturate' => 'Saturate',),
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
        'section'  => 'pizzalayer_panel_section_display_options',
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
        'section'  => 'pizzalayer_panel_section_display_options',
        'settings' => 'pizzalayer_setting_template_glass_title_color',
    )));

/* === SETTING --> menu type for glass template header ============== v */

$wp_customize->add_setting( 'pizzalayer_setting_template_glass_menu_type' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_template_glass_menu_type_control', array(
	'label'      => 'Header Menu Type',
	'description'=> 'menu type for glass template header',
	'section'    => 'pizzalayer_panel_section_display_options',
	'settings'   => 'pizzalayer_setting_template_glass_menu_type',
	'type'    => 'select',
        'choices'    => array(
            'default' => 'Default',
            'icontext' => 'Icon and Text',
            'button' => 'Button',
            'disabled' => 'Disabled',
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
	'section'    => 'pizzalayer_panel_section_display_options',
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

/* === SETTING --> a PHP array of elements to include in header ============== v */

$wp_customize->add_setting( 'pizzalayer_setting_template_glass_header_elements_array' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_template_glass_header_elements_array_control', array(
	'label'      => 'Header Elements',
	'description'=> 'a PHP array of Header elements to display. Important - please be careful when formatting this, as this may break the header layout or cause other issues.',
	'section'    => 'pizzalayer_panel_section_advanced',
	'settings'   => 'pizzalayer_setting_template_glass_header_elements_array',
	'type'       => 'text',
) );

/* === SETTING --> Toppings Style ============== v */

$wp_customize->add_setting( 'pizzalayer_setting_template_glass_element_style_toppings' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_template_glass_element_style_toppings_control', array(
	'label'      => 'Toppings Style',
	'description'=> 'Select a style for the Toppings',
	'section'    => 'pizzalayer_panel_section_element_styles',
	'settings'   => 'pizzalayer_setting_template_glass_element_style_toppings',
		'type'    => 'select',
        'choices'    => array(
            'default' => 'Default',
        ),
) );

/* === SETTING --> Lists / Layers Menu in Tab Content Style ============== v */

$wp_customize->add_setting( 'pizzalayer_setting_template_glass_element_style_lists' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_template_glass_element_style_lists_control', array(
	'label'      => 'Lists and Layers Style',
	'description'=> 'Select a style for the Lists and Layers content',
	'section'    => 'pizzalayer_panel_section_element_styles',
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
	'section'    => 'pizzalayer_panel_section_element_styles',
	'settings'   => 'pizzalayer_setting_template_glass_element_style_myrecipe',
	'type'    => 'select',
        'choices'    => array(
            'default' => 'Default',
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
	'section'    => 'pizzalayer_panel_section_element_styles',
	'settings'   => 'pizzalayer_setting_template_glass_element_style_header',
		'type'    => 'select',
        'choices'    => array(
            'default' => 'Default',
        ),
) );

/* === SETTING --> Tab Style ============== v */

$wp_customize->add_setting( 'pizzalayer_setting_template_glass_element_style_tabs' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_template_glass_element_style_tabs_control', array(
	'label'      => 'Tab Style',
	'description'=> 'Select a style for the tab menu that shows up above the layer choices.',
	'section'    => 'pizzalayer_panel_section_element_styles',
	'settings'   => 'pizzalayer_setting_template_glass_element_style_tabs',
	'type'    => 'select',
        'choices'    => array(
            'default' => 'Default',
        ),
) );


/* =========== END ADDING SETTINGS  ============== */ 
} //function
add_action('customize_register', 'pizzalayer_customize_register_glassy', 5);
