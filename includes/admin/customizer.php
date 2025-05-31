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

/*-- ============================ --*/


function pizzalayer_get_layer_options_as_array_OLD($tpv_query_layer_options_posttype){
//return false if no cpt type passed
if(!$tpv_query_layer_options_posttype){ return false; } 

// -- assemble query to get options by cpt
  
// -- query loop arguments
$args = array(  
'post_type' => array('pizzalayer_' . $tpv_query_layer_options_posttype),
'post_status' => 'publish',
'posts_per_page' => '-1', 
'orderby' => 'title', 
'order' => 'ASC',
);

// -- ui css class

// -- create array to return
$pizzalayer_layer_options_array = [];
    
// -- get cpt posts and build
/* Desired output : array(
            'default' => 'Default',
            'whole' => 'Whole Pizza Only',
            'halves' => 'Whole + Halves',
            'quarters' => 'Whole + Quarters',
    ); */
    
$loop = new WP_Query( $args );        
while ( $loop->have_posts() ) : $loop->the_post();
$pztp_tli_short_slug = get_post_field( 'post_name', get_post() );
//$pztp_tli_short_slug_capitalized = strtoupper($pztp_tli_short_slug);
$pizzalayer_layer_options_array[$pztp_tli_short_slug] = $pztp_tli_short_slug;

// end post 
endwhile; //end main cpt loop
wp_reset_postdata(); 
return $pizzalayer_layer_options_array;    
    
}




/*-- ============================ --*/






function pizzalayer_get_layer_options_as_array($tpv_query_layer_options_posttype) {
    // Initialize an empty array
    $cuts_array = array();

    // Arguments for WP_Query
   $args = array(  
        'post_type' => array('pizzalayer_' . $tpv_query_layer_options_posttype),
        'post_status' => 'publish',
        'posts_per_page' => -1, 
        'orderby' => 'title', 
        'order' => 'ASC',
        );

    // Custom query
    $query = new WP_Query($args);

    // Loop through the posts
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $title = get_the_title();
            $key = strtolower($title);
            $value = ucfirst($title);
            $this_loop_array[$key] = $value;
        }
        wp_reset_postdata(); // Reset post data after the loop
    }
    
    $this_loop_array[''] = 'None';

    return $this_loop_array;
}











/*-- ============================ --*/

function pizzalayer_customize_register( $wp_customize ) {
// Create our panels

$wp_customize->add_panel( 'pizzalayer_admin_panel_basics', array(
	'title'          => 'Pizzalayer',
	'description'    => 'The full setup for customizing PizzaLayer! Please read each choice carefully - these control most of the UI and a few key backend settings',
) );

// Create our sections

$wp_customize->add_section( 'pizzalayer_panel_section_template_selection' , array(
	'title'             => 'Template Selection',
	'description'       => 'Here you\'ll find the options for choosing a template for the front-end design',
	'panel'             => 'pizzalayer_admin_panel_basics',
) );

$wp_customize->add_section( 'pizzalayer_panel_section_element_style' , array(
	'title'             => 'Element Styles',
	'description'       => 'Here you can choose a premade style for major layout elements',
	'panel'             => 'pizzalayer_admin_panel_basics',
) );

$wp_customize->add_section( 'pizzalayer_panel_section_branding' , array(
	'title'             => 'Branding',
	'description'       => 'Here you\'ll find the options for adding your branding',
	'panel'             => 'pizzalayer_admin_panel_basics',
) );

$wp_customize->add_section( 'pizzalayer_panel_section_pizza' , array(
	'title'             => 'Pizza',
	'description'       => 'Here you\'ll find the options for customizing how the pizza as a whole is displayed',
	'panel'             => 'pizzalayer_admin_panel_basics',
) );

$wp_customize->add_section( 'pizzalayer_panel_section_crusts' , array(
	'title'             => 'Crusts',
	'description'       => 'Here you\'ll find the options for customizing how crusts are displayed',
	'panel'             => 'pizzalayer_admin_panel_basics',
) );

$wp_customize->add_section( 'pizzalayer_panel_section_sauces' , array(
	'title'             => 'Sauces',
	'description'       => 'Here you\'ll find the options for customizing how sauces are displayed',
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

$pizzalayer_setting_global_template_description = 'Choose a global template for the frontend display.<h6 style="padding-top:0px;"><strong>DEVELOPERS</strong><br/>The built-in template directory on the server is :</strong><br/>' . pizzalayer_template_get_templates_file_path() . '</h6>';

$wp_customize->add_setting( 'pizzalayer_setting_global_template' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_global_template_control', array(
	'label'      => 'Pizzalayer Main Template',
	'description'=> $pizzalayer_setting_global_template_description,
	'section'    => 'pizzalayer_panel_section_template_selection',
	'settings'   => 'pizzalayer_setting_global_template',
	'type'       => 'select',
    'choices'    => pizzalayer_template_get_templates_as_array(),
) );

/* =========== SETTING --------> global > pizza size max ============== v */

$wp_customize->add_setting( 'pizzalayer_setting_pizza_size_max' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_pizza_size_max_control', array(
	'label'      => 'Pizza Size - Max',
	'description'=> 'Pizza Max Size (px or percent). Make sure to include unit.',
	'section'    => 'pizzalayer_panel_section_pizza',
	'settings'   => 'pizzalayer_setting_pizza_size_max',
	'type'       => 'text',
) );
	
/* =========== SETTING --------> global > pizza size min ============== v */

$wp_customize->add_setting( 'pizzalayer_setting_pizza_size_min' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_pizza_size_min_control', array(
	'label'      => 'Pizza Size - Min',
	'description'=> 'Pizza Min Size (px or percent). Make sure to include unit.',
	'section'    => 'pizzalayer_panel_section_pizza',
	'settings'   => 'pizzalayer_setting_pizza_size_min',
	'type'       => 'text',
) );

/* =========== SETTING --------> global > pizza border size ============== v */

$wp_customize->add_setting( 'pizzalayer_setting_pizza_border' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_pizza_border_control', array(
	'label'      => 'Pizza Border Size',
	'description'=> 'Please enter any valid CSS width for the pizza border',
	'section'    => 'pizzalayer_panel_section_pizza',
	'settings'   => 'pizzalayer_setting_pizza_border',
	'type'       => 'text',
) );

/* =========== SETTING --------> global > pizza border color ============== v */

$wp_customize->add_setting( 'pizzalayer_setting_pizza_border_color' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control(
  new WP_Customize_Color_Control( $wp_customize, 'pizzalayer_setting_pizza_border_color',
  array(
    'label' => __( 'Pizza Border Color' ),
    'description' => __( 'What color should the pizza border be?' ),
    'section'    => 'pizzalayer_panel_section_pizza', // Add a default or your own section
) ) );


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














/* =========== SETTING --------> cuts > default slicing  ============== */

$wp_customize->add_setting( 'pizzalayer_setting_cut_defaultcut' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );

$wp_customize->add_control( 'pizzalayer_setting_cut_defaultcut_control', array(
	'label'      => 'Default Slicing',
	'description'=> 'How many slices should be shown on the slicing overlay over the pizza?',
	'section'    => 'pizzalayer_panel_section_cuts',
	'settings'   => 'pizzalayer_setting_cut_defaultcut',
	'type'    => 'select',
    'choices'    => pizzalayer_get_layer_options_as_array('cuts'),
) );


/* =========== SETTING --------> sauces > default sauce  ============== v */

$wp_customize->add_setting( 'pizzalayer_setting_sauce_defaultsauce' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_sauce_defaultsauce_control', array(
	'label'      => 'Default Sauce',
	'description'=> 'Which sauce should be applied by default?',
	'section'    => 'pizzalayer_panel_section_sauces',
	'settings'   => 'pizzalayer_setting_sauce_defaultsauce',
	'type'    => 'select',
    'choices'    => pizzalayer_get_layer_options_as_array('sauces'),
) );

/* =========== SETTING --------> drizzles > default drizzle  ============== v */

$wp_customize->add_setting( 'pizzalayer_setting_drizzle_defaultdrizzle' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_drizzle_defaultdrizzle_control', array(
	'label'      => 'Default drizzle',
	'description'=> 'Which drizzle should be applied by default?',
	'section'    => 'pizzalayer_panel_section_drizzles',
	'settings'   => 'pizzalayer_setting_drizzle_defaultdrizzle',
	'type'    => 'select',
   'choices'    => pizzalayer_get_layer_options_as_array('drizzles'),
) );

/* =========== SETTING --------> crusts > default crust  ============== v */

$wp_customize->add_setting( 'pizzalayer_setting_crust_defaultcrust' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_crust_defaultcrust_control', array(
	'label'      => 'Default crust',
	'description'=> 'Which crust should be applied by default?',
	'section'    => 'pizzalayer_panel_section_crusts',
	'settings'   => 'pizzalayer_setting_crust_defaultcrust',
	'type'    => 'select',
    'choices'    => pizzalayer_get_layer_options_as_array('crusts'),
) );

/* =========== SETTING --------> cheeses > default cheese  ============== v */

$wp_customize->add_setting( 'pizzalayer_setting_cheese_defaultcheese' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_cheese_defaultcheese_control', array(
	'label'      => 'Default cheese',
	'description'=> 'Which cheese should be applied by default?',
	'section'    => 'pizzalayer_panel_section_cheeses',
	'settings'   => 'pizzalayer_setting_cheese_defaultcheese',
	'type'    => 'select',
    'choices'    => pizzalayer_get_layer_options_as_array('cheeses'),
) );

/* =========== SETTING --------> cheeses > cheese distance ==============  */

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
	
/* =========== SETTING --------> cheeses > padding  ============== */

$wp_customize->add_setting( 'pizzalayer_setting_cheese_padding' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_cheese_padding_control', array(
	'label'      => 'Cheese Padding',
	'description'=> 'How much padding should there be between the cheese and the toppings?',
	'section'    => 'pizzalayer_panel_section_cheeses',
	'settings'   => 'pizzalayer_setting_cheese_padding',
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

/* =========== SETTING --------> crusts > padding  ============== (need to dx)*/

$wp_customize->add_setting( 'pizzalayer_setting_crust_padding' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_crust_padding_control', array(
	'label'      => 'Crust Padding',
	'description'=> 'How much crust should there be?',
	'section'    => 'pizzalayer_panel_section_crusts',
	'settings'   => 'pizzalayer_setting_crust_padding',
	'type'       => 'text',
) );





/* =========== SETTING --------> toppings > fractions available for toppings ============== */
$wp_customize->add_setting( 'pizzalayer_setting_topping_fractions' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_topping_fractions_control', array(
	'label'      => __('Please choose the portions of the pizza where customers can customize their toppings.'),
	'section'    => 'pizzalayer_panel_section_toppings',
	'settings'   => 'pizzalayer_setting_topping_fractions',
	'type'    => 'select',
    'choices'    => array(
            'default' => 'Default',
            'whole' => 'Whole Pizza Only',
            'halves' => 'Whole + Halves',
            'quarters' => 'Whole + Quarters',
    ),
) );

	
/* =========== SETTING --------> sauces > padding  ============== */

$wp_customize->add_setting( 'pizzalayer_setting_sauce_padding' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_sauce_padding_control', array(
	'label'      => 'Sauce Padding',
	'description'=> 'How much padding should there be between the cheese and the edge of the sauce?',
	'section'    => 'pizzalayer_panel_section_sauces',
	'settings'   => 'pizzalayer_setting_sauce_padding',
	'type'       => 'text',
) );



/* =========== SETTING --------> features > layer thumbnails  ============== */

$wp_customize->add_setting( 'pizzalayer_setting_show_thumbnails' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_features_show_thumbnails_control', array(
	'label'      => 'Menu Thumbnails',
	'description'=> 'Show Thumbnails throughout UI',
	'section'    => 'pizzalayer_panel_section_features',
	'settings'   => 'pizzalayer_setting_show_thumbnails',
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


/* =========== SETTING --------> SETTINGS > Help screen content  ============== */

$wp_customize->add_setting( 'pizzalayer_setting_global_help_content' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_global_help_content_control', array(
	'label'      => 'Help Screen Content',
	'description'=> 'Content for the tab shown when visitors click the help icon',
	'section'    => 'pizzalayer_panel_section_settings',
	'settings'   => 'pizzalayer_setting_global_help_content',
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
    	'description'=> 'Please select a logo to be used in the header or sidebar for some templates',
		'section'  => 'pizzalayer_panel_section_branding',
		'settings' => 'pizzalayer_setting_branding_altlogo',
	)
) );


/* =========== SETTING --------> Branding > Menu Title  ============== v */

$wp_customize->add_setting( 'pizzalayer_setting_branding_menu_title', array(
  'capability' => 'edit_theme_options',
  'default' => 'Lorem Ipsum Dolor Sit amet',
  'sanitize_callback' => 'sanitize_textarea_field',
	'type'          => 'option',
	'transport'     => 'refresh'
) );

$wp_customize->add_control( 'pizzalayer_setting_branding_menu_title_control', array(
  'type' => 'textarea',
  'section' => 'pizzalayer_panel_section_branding',
  'label' => __( 'Content above menu icons' ),
  'description' => __( 'Please enter any content you would like to appear on top of the icons menu, such as an intro or custom logo' ),
  'settings' => 'pizzalayer_setting_branding_menu_title',
) );


/* =========== SETTING --------> Branding > Header > Custom Content  ============== */

$wp_customize->add_setting( 'pizzalayer_setting_branding_header_custom_content', array(
  'capability' => 'edit_theme_options',
  'default' => 'Lorem Ipsum Dolor Sit amet',
  'sanitize_callback' => 'sanitize_textarea_field',
	'type'          => 'option',
	'transport'     => 'refresh'
) );

$wp_customize->add_control( 'pizzalayer_setting_branding_header_custom_content_control', array(
  'type' => 'textarea',
  'section' => 'pizzalayer_panel_section_branding',
  'label' => __( 'Content above menu icons' ),
  'description' => __( 'custom HTML content for the branding area in header' ),
  'settings' => 'pizzalayer_setting_branding_header_custom_content',
) );


/* === SETTING --> Toppings > Layer Choice Style ============== v */

$wp_customize->add_setting( 'pizzalayer_setting_element_style_layers' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_element_style_layers_control', array(
	'label'      => 'Layer Choice Style',
	'description'=> 'Select a style for the Layer choices other than Toppings',
	'section'    => 'pizzalayer_panel_section_element_style',
	'settings'   => 'pizzalayer_setting_element_style_layers',
		'type'    => 'select',
        'choices'    => array(
            'default' => 'Default',
            'thumblabel' => 'Thumb with Label',
            'thumbcorner' => 'Thumb Corner',
            'thumbcircle' => 'Thumb Circle',
            'labeloverthumb' => 'Label over Thumb',
            'thumbrow' => 'Thumb Row',
            'textrow' => 'Text Row',
            'icontext' => 'Icon and Text',
            'text' => 'Text',
            'appsidetrigger' => 'App Row with Side Triggers',
        ),
) );


/* === SETTING --> Toppings > Toppings Style ============== v */

$wp_customize->add_setting( 'pizzalayer_setting_element_style_toppings' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_element_style_toppings_control', array(
	'label'      => 'Toppings Style',
	'description'=> 'Select a style for the Toppings',
	'section'    => 'pizzalayer_panel_section_element_style',
	'settings'   => 'pizzalayer_setting_element_style_toppings',
	'type'    => 'select',
    'choices'    => array(
            'default' => 'Default',
            'controlbox' => 'Control Box',
            'thumbcorner' => 'Thumb Corner',
            'bgtoggle' => 'Background Toggle',
            'modern' => 'Modern Offset',
            'cornertag' => 'Corner Tag',
            'appadd' => 'App Add',
        ),
) );

/* === SETTING --> Toppings > Topping Choice Menu Style ============== v */

$wp_customize->add_setting( 'pizzalayer_setting_element_style_topping_choice_menu' , array(
	'type'          => 'option',
	'transport'     => 'refresh',
) );
$wp_customize->add_control( 'pizzalayer_setting_element_style_topping_choice_menu_control', array(
	'label'      => 'Topping Choice Menu Style',
	'description'=> 'Select a style for the Toppings choice menu for each topping',
	'section'    => 'pizzalayer_panel_section_element_style',
	'settings'   => 'pizzalayer_setting_element_style_topping_choice_menu',
		'type'    => 'select',
        'choices'    => array(
            'default' => 'Default',
            'minimal' => 'Minimal',
            'iconwfraction' => 'Icon (with fraction)',
            'iconnofraction' => 'Icon (no fraction)',
        ),
) );


/* =========== END ADDING SETTINGS  ============== */ 
} //function
add_action( 'customize_register', 'pizzalayer_customize_register', 1 );


