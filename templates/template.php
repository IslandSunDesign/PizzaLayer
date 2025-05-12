<?php
//check global settings which template to load, or default to 'glassy'
$pizzalayer_template_name_default = get_option( 'pizzalayer_setting_global_template' );
$pizzalayer_templates_folder_path = plugins_url( '' , __FILE__ );
$pizzalayer_templates_theme_folder_path = get_stylesheet_directory() . '/pzttemplates/';
define( 'Pizzalayer_TEMPLATES_PATH', plugin_dir_path( __FILE__ ) );

//choose and validate template name from query url parameter or plugin options
if($pizzalayer_template_name_default && file_exists($pizzalayer_templates_folder_path . $pizzalayer_template_name_default . '/')){ $pizzalayer_template_name = $pizzalayer_template_name_default;} else { $pizzalayer_template_name = 'glassy'; };


//load stylesheets
wp_register_style( 'pizzalayer-template-base-css-' . $pizzalayer_template_name, plugins_url( $pizzalayer_template_name . '/template.css', __FILE__ ) );
wp_enqueue_style( 'pizzalayer-template-base-css-' . $pizzalayer_template_name, plugins_url( $pizzalayer_template_name . '/template.css', __FILE__ ) );

//load the template files from the theme if folder exists, otherwise load template files from the built-in templates folder

if( is_dir($pizzalayer_templates_theme_folder_path . $pizzalayer_template_name_default) ){
// -- if the folder is in the user's theme under '/pzttemplates/'
include $pizzalayer_templates_theme_folder_path. $pizzalayer_template_name_default . '/pztp-containers-menu.php';
include $pizzalayer_templates_theme_folder_path . $pizzalayer_template_name_default . '/pztp-containers-presentation.php';
include $pizzalayer_templates_theme_folder_path . $pizzalayer_template_name_default . '/pztp-template-custom.php';
include $pizzalayer_templates_theme_folder_path . $pizzalayer_template_name_default . '/pztp-template-css.php';
include $pizzalayer_templates_theme_folder_path . $pizzalayer_template_name_default . '/pztp-template-options.php';
} else if( is_dir($pizzalayer_templates_folder_path . $pizzalayer_template_name_default) ){
// -- if the folder is in the plugin directory under '/templates/'
include Pizzalayer_TEMPLATES_PATH . '/' . $pizzalayer_template_name_default . '/pztp-containers-menu.php';
include Pizzalayer_TEMPLATES_PATH . '/' . $pizzalayer_template_name_default . '/pztp-containers-presentation.php';
include Pizzalayer_TEMPLATES_PATH . '/' . $pizzalayer_template_name_default . '/pztp-template-custom.php';
include Pizzalayer_TEMPLATES_PATH . '/' . $pizzalayer_template_name_default . '/pztp-template-css.php';
include Pizzalayer_TEMPLATES_PATH . '/' . $pizzalayer_template_name_default . '/pztp-template-options.php';   
} else {
// -- for a fallback, choose the 'glassy' template in the plugin templates
include Pizzalayer_TEMPLATES_PATH . '/glassy/pztp-containers-menu.php';
include Pizzalayer_TEMPLATES_PATH . '/glassy/pztp-containers-presentation.php';
include Pizzalayer_TEMPLATES_PATH . '/glassy/pztp-template-custom.php';
include Pizzalayer_TEMPLATES_PATH . '/glassy/pztp-template-css.php';
include Pizzalayer_TEMPLATES_PATH . '/glassy/pztp-template-options.php';   
}; //end if





//load custom javascript
function enqueue_pizzalayer_template_js() {
    global $pizzalayer_template_name;
    wp_register_script( 'pizzalayer_template_custom_javascript', plugin_dir_url( __FILE__ ) . $pizzalayer_template_name . '/custom.js', array('jquery'), null, true );
    wp_enqueue_script('pizzalayer_template_custom_javascript');
}

add_action('wp_enqueue_scripts', 'enqueue_pizzalayer_template_js');

function pizzalayer_template_get_templates(){
//add the built-in templates
$pizzalayer_templates_folder_path = plugin_dir_path( __FILE__ );
$pizzalayer_templates_list = '';
$pizzalayer_templates_directories = glob($pizzalayer_templates_folder_path . '/*' , GLOB_ONLYDIR);
foreach($pizzalayer_templates_directories as $pizzalayer_templates_directory){
$pizzalayer_templates_list .= '<li style="">' . basename($pizzalayer_templates_directory) . '</li>';
}
//basename($yourpath)
//$pizzalayer_templates_list = print_r($pizzalayer_templates_directories, true);
return '<ul class="pizzalayer-templates-list-ul">' . $pizzalayer_templates_list . '</ul><style type="text/css">.pizzalayer-templates-list-ul{padding:8px 16px;font-size:22px;text-transform:uppercase;font-weight:600;}</style>';
}

function pizzalayer_template_get_templates_as_array(){
$pizzalayer_templates_folder_path = plugin_dir_path( __FILE__ );
$pizzalayer_templates_theme_folder_path = get_stylesheet_directory() . '/pzttemplates/';
$pizzalayer_templates_list_as_array = [];

//add the built-in templates
$pizzalayer_templates_directories = glob($pizzalayer_templates_folder_path . '/*' , GLOB_ONLYDIR);
foreach($pizzalayer_templates_directories as $pizzalayer_templates_directory){
$pizzalayer_templates_list_as_array[basename($pizzalayer_templates_directory)] = basename($pizzalayer_templates_directory);
}

//if the 'pzttemplates' directory exists in the active theme's root directory, add templates found in the 'pzttemplates' directory
if( is_dir($pizzalayer_templates_theme_folder_path) ){
$pizzalayer_theme_templates_directories = glob($pizzalayer_templates_theme_folder_path . '/*' , GLOB_ONLYDIR);
foreach($pizzalayer_theme_templates_directories as $pizzalayer_theme_templates_directory){
$pizzalayer_templates_list_as_array[basename($pizzalayer_theme_templates_directory)] = basename($pizzalayer_theme_templates_directory);
}
} //end is_dir

return $pizzalayer_templates_list_as_array;
}


function pizzalayer_template_get_templates_file_path(){
    return plugin_dir_path( __FILE__ );
}