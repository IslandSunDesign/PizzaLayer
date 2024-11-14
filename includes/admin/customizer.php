<?php

function ic_sanitize_image( $file, $setting ) {

	$mimes = array(
		'jpg|jpeg|jpe' => 'image/jpeg',
		'gif'          => 'image/gif',
		'png'          => 'image/png',
		'bmp'          => 'image/bmp',
		'tif|tiff'     => 'image/tiff',
		'ico'          => 'image/x-icon'
	);

	//check file type from file name
	$file_ext = wp_check_filetype( $file, $mimes );

	//if file has a valid mime type return it, otherwise return default
	return ( $file_ext['ext'] ? $file : $setting->default );
}

function pizzalayer_customize_register( $wp_customize ) {

// Create our panels

$wp_customize->add_panel( 'pizzalayer_admin_panel_basics', array(
	'title'          => 'Pizzalayer',
	'description'    => 'The full setup for customizing PizzaLayer! Please read each choice carefully - these control most of the UI and a few key backend settings',
) );
		
// Create our sections

$wp_customize->add_section( 'pizzalayer_panel_section_template' , array(
	'title'             => 'Template',
	'description'       => 'Here you\'ll find the options for choosing and setting up the template for the front-end design',
	'panel'             => 'pizzalayer_admin_panel_basics',
) );

$wp_customize->add_section( 'pizzalayer_panel_section_branding' , array(
	'title'             => 'Branding',
	'description'       => 'Here you\'ll find the options for adding your branding',
	'panel'             => 'pizzalayer_admin_panel_basics',
) );

$wp_customize->add_section( 'pizzalayer_panel_section_crusts' , array(
	'title'             => 'Crusts',
	'description'       => 'Here you\'ll find the options for customizing how crusts are displayed',
	'panel'             => 'pizzalayer_admin_panel_basics',
) );

$wp_customize->add_section( 'pizzalayer_panel_section_sauces' , array(
	'title'             => 'Sauces',
	'description'       => 'Here you\'ll find the options for customizing how saucess are displayed',
	'panel'             => 'pizzalayer_admin_panel_basics',
) );

$wp_customize->add_section( 'pizzalayer_panel_section_cheeses' , array(
	'title'             => 'Cheeses',
	'description'       => 'Here you\'ll find the options for customizing how cheeses are displayed',
	'panel'             => 'pizzalayer_admin_panel_basics',
) );

$wp_customize->add_section( 'pizzalayer_panel_section_toppings' , array(
	'title'             => 'Toppings',
	'description'       => 'Here you can find the options for customizing how toppings are displayed',
	'panel'             => 'pizzalayer_admin_panel_basics',
) );

$wp_customize->add_section( 'pizzalayer_panel_section_drizzles' , array(
	'title'             => 'Drizzles',
	'description'       => 'Here you can find the options for customizing how drizzles are displayed',
	'panel'             => 'pizzalayer_admin_panel_basics',
) );

$wp_customize->add_section( 'pizzalayer_panel_section_cuts' , array(
	'title'             => 'Cuts',
	'description'       => 'Here you can find the options for customizing how the slicing is displayed',
	'panel'             => 'pizzalayer_admin_panel_basics',
) );

$wp_customize->add_section( 'pizzalayer_panel_section_features' , array(
	'title'             => 'Features',
	'description'       => 'Here you can select which features you want on the frontend',
	'panel'             => 'pizzalayer_admin_panel_basics',
) );

$wp_customize->add_section( 'pizzalayer_panel_section_settings' , array(
	'title'             => 'Plugin Settings',
	'description'       => 'General Plugin Settings and Information',
	'panel'             => 'pizzalayer_admin_panel_basics',
) );

$wp_customize->add_section( 'pizzalayer_panel_section_advanced' , array(
	'title'             => 'Advanced',
	'description'       => 'Advanced settings for more technical users and developers',
	'panel'             => 'pizzalayer_admin_panel_basics',
) );
		
// Create our settings

/* =========== SETTING --------> global > template ============== v */

$wp_customize->add_setting( 'pizzalayer_setting_global_template' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_global_template_control', array(
	'label'      => 'Pizzalayer Main Template',
	'description'=> 'Choose a global template for the frontend display. Options are <div style="border:solid 1px red;height:40px;width:100%;">fsafasfsa</div>' . pizzalayer_template_get_templates(),
	'section'    => 'pizzalayer_panel_section_template',
	'settings'   => 'pizzalayer_setting_global_template',
	'type'       => 'text',
) );

/* =========== SETTING --------> global > accent color ============== v */
		
$wp_customize->add_setting( 'pizzalayer_setting_global_color' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control(
  new WP_Customize_Color_Control( $wp_customize, 'pizzalayer_setting_global_color',
  array(
    'label' => __( 'Accent Color Setting' ),
    'description' => __( 'Select a color for something' ),
    'section' => 'pizzalayer_panel_section_template', // Add a default or your own section
) ) );

/* =========== SETTING --------> cheeses > cheese distance ============== v */

$wp_customize->add_setting( 'pizzalayer_cheese_setting_cheesedistance' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_cheese_setting_cheesedistance_control', array(
	'label'      => 'Cheese distance from edge',
	'description'=> 'How far should the cheese be from the edge of the pizza?',
	'section'    => 'pizzalayer_panel_section_cheeses',
	'settings'   => 'pizzalayer_cheese_setting_cheesedistance',
	'type'       => 'text',
) );

/* =========== SETTING --------> toppings > max toppings ============== v */

$wp_customize->add_setting( 'pizzalayer_setting_topping_maxtoppings' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_global_color_control', array(
	'label'      => 'Max Toppings',
	'description'=> 'How many topping should customers be able to choose?',
	'section'    => 'pizzalayer_panel_section_toppings',
	'settings'   => 'pizzalayer_setting_topping_maxtoppings',
	'type'       => 'number',
) );

/* =========== SETTING --------> crusts > global aspect ratio  ============== (need to dx)*/

$wp_customize->add_setting( 'pizzalayer_setting_crust_aspectratio' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_crust_aspectratio_control', array(
	'label'      => 'Crust aspect ratio / Shape',
	'description'=> 'What aspect ratio should the crust be presented in by default? A normal Pizza is 1:1',
	'section'    => 'pizzalayer_panel_section_crusts',
	'settings'   => 'pizzalayer_setting_crust_aspectratio',
	'type'       => 'text',
) );

/* =========== SETTING --------> cuts > default slicing  ============== */

$wp_customize->add_setting( 'pizzalayer_setting_cut_defaultslicing' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_cut_defaultslicing_control', array(
	'label'      => 'Default Slicing',
	'description'=> 'How many slices should the pizza be cut into by default?',
	'section'    => 'pizzalayer_panel_section_cuts',
	'settings'   => 'pizzalayer_setting_cut_defaultslicing',
	'type'       => 'text',
) );

/* =========== SETTING --------> cuts > fractions available for toppings  ============== */
$wp_customize->add_setting( 'pizzalayer_setting_topping_fractions' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_topping_fractions_control', array(
	'label'      => 'Available fractions for toppings',
	'description'=> 'Which topping coverage options should be available?',
	'section'    => 'pizzalayer_panel_section_cuts',
	'settings'   => 'pizzalayer_setting_topping_fractions',
	'type'       => 'checkbox',
        'choices'    => array( 
          '1_2' => 'Halves',
          '1_3' => 'Thirds',
          '1_4' => 'Fourths',
          '1_5' => 'Fifths',
          '1_6' => 'Sixths',
          '1_7' => 'Sevenths',
          '1_8' => 'Eighths'
        ),
) );

/* =========== SETTING --------> sauces > default sauce  ============== */

$wp_customize->add_setting( 'pizzalayer_setting_sauce_defaultsauce' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_sauce_defaultsauce_control', array(
	'label'      => 'Default Sauce',
	'description'=> 'Which sauce should be applied by default?',
	'section'    => 'pizzalayer_panel_section_sauces',
	'settings'   => 'pizzalayer_setting_sauce_defaultsauce',
	'type'       => 'text',
) );

/* =========== SETTING --------> drizzles > default drizzle  ============== */

$wp_customize->add_setting( 'pizzalayer_setting_drizzle_defaultdrizzle' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_drizzle_defaultdrizzle_control', array(
	'label'      => 'Default drizzle',
	'description'=> 'Which drizzle should be applied by default?',
	'section'    => 'pizzalayer_panel_section_drizzles',
	'settings'   => 'pizzalayer_setting_drizzle_defaultdrizzle',
	'type'       => 'text',
) );

/* =========== SETTING --------> features > layer thumbnails  ============== */

$wp_customize->add_setting( 'pizzalayer_setting_features_show_thumbnails' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_features_show_thumbnails_control', array(
	'label'      => 'Menu Thumnails',
	'description'=> 'Show Thumbnails?',
	'section'    => 'pizzalayer_panel_section_features',
	'settings'   => 'pizzalayer_setting_features_show_thumbnails',
	'type'       => 'text',
) );

/* =========== SETTING --------> SETTINGS > Demo mode notice  ============== */

$wp_customize->add_setting( 'pizzalayer_setting_settings_demonotice' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_settings_demonotice_control', array(
	'label'      => 'Demo Notice',
	'description'=> 'If set, this will show this field\'s value as an announcement bar above all pages using PizzaLayer',
	'section'    => 'pizzalayer_panel_section_settings',
	'settings'   => 'pizzalayer_setting_settings_demonotice',
	'type'       => 'textarea',
) );

/* =========== SETTING --------> Branding > Alt Logo  ============== */

$wp_customize->add_setting( 'pizzalayer_setting_branding_altlogo', array(
	'capability'        => 'edit_theme_options',
	'default'           => '',
	'sanitize_callback' => 'ic_sanitize_image',
	'type'          => 'option',
	'transport'     => 'refresh'
) );
$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'logo',
	array(
			'label'      => 'Logo for template header',
    	'description'=> 'Please select a logo to be used in the header or sidebar for some templates Please see your template documentation if unsure whether this applies',
		'section'  => 'pizzalayer_panel_section_branding',
		'settings' => 'pizzalayer_setting_branding_altlogo',
	)
) );

/* =========== SETTING --------> Branding > Menu Title  ============== */

$wp_customize->add_setting( 'pizzalayer_setting_branding_menu_title', array(
  'capability' => 'edit_theme_options',
  'default' => 'Lorem Ipsum Dolor Sit amet',
  'sanitize_callback' => 'sanitize_textarea_field',
	'type'          => 'option',
	'transport'     => 'refresh'
) );

$wp_customize->add_control( 'pizzalayer_setting_branding_menu_title', array(
  'type' => 'textarea',
  'section' => 'pizzalayer_panel_section_branding', // // Add a default or your own section
  'label' => __( 'Content above menu icons' ),
  'description' => __( 'Please enter any content you would like to appear on top of the icons menu, such as an intro or custom logo' ),
) );








}
add_action( 'customize_register', 'pizzalayer_customize_register' );


