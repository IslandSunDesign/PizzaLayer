<?php
//check global settings which template to load, or default to 'glassy'
$pizzalayer_template_name_query = sanitize_text_field($_GET['previewtemplate']);
$pizzalayer_template_name_default = get_option( 'pizzalayer_setting_global_template' );
$pizzalayer_templates_folder_path = plugins_url( '' , __FILE__ );

//choose and validate template name from query url parameter or plugin options
$pizzalayer_template_name = 'glassy'; //ultimate fallback
if($pizzalayer_template_name_default && file_exists($pizzalayer_templates_folder_path . $pizzalayer_template_name_default . '/')){ $pizzalayer_template_name = $pizzalayer_template_name_default;};
if($pizzalayer_template_name_query && file_exists($pizzalayer_templates_folder_path . $pizzalayer_template_name_query . '/')){ $pizzalayer_template_name = $pizzalayer_template_name_query;};

//load stylesheets
wp_register_style( 'pizzalayer-template-base-css', plugins_url( $pizzalayer_template_name . '/template.css', __FILE__ ) );
wp_enqueue_style( 'pizzalayer-template-base-css', plugins_url( $pizzalayer_template_name . '/template.css', __FILE__ ) );

//load the presentation files
define( 'Pizzalayer_TEMPLATES_PATH', plugin_dir_path( __FILE__ ) );
include Pizzalayer_TEMPLATES_PATH . '/' . $pizzalayer_template_name . '/pztp-containers-menu.php';
include Pizzalayer_TEMPLATES_PATH . '/' . $pizzalayer_template_name . '/pztp-containers-presentation.php';
include Pizzalayer_TEMPLATES_PATH . '/' . $pizzalayer_template_name . '/pztp-containers-widgets.php';    
include Pizzalayer_TEMPLATES_PATH . '/' . $pizzalayer_template_name . '/pztp-template-custom.php';
include Pizzalayer_TEMPLATES_PATH . '/' . $pizzalayer_template_name . '/pztp-template-presets.php';

//load custom javascript
function enqueue_pizzalayer_template_js() {
    global $pizzalayer_template_name;
    wp_register_script( 'pizzalayer_template_custom_javascript', plugin_dir_url( __FILE__ ) . $pizzalayer_template_name . '/custom.js', array('jquery'), null, true );
    wp_enqueue_script('pizzalayer_template_custom_javascript');
}

add_action('wp_enqueue_scripts', 'enqueue_pizzalayer_template_js');

function pizzalayer_template_get_templates(){
$pizzalayer_templates_folder_path = plugin_dir_path( __FILE__ );

} //function