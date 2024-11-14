<?php
global $pizzalayer_path_images;

include plugin_dir_path(__FILE__) . 'topper-ui-lists.php';
include plugin_dir_path(__FILE__) . 'topper-ui-containers.php';
include plugin_dir_path(__FILE__) . 'topper-ui-controls.php';
include plugin_dir_path(__FILE__) . 'topper-ui-javascript.php';
include plugin_dir_path(__FILE__) . 'topper-ui-frontend-display.php';
include plugin_dir_path(__FILE__) . 'topper-ui-alert.php';

/* SHORTCODE FOR DISPLAY */
add_shortcode( 'pizzalayer-menu', 'pizzalayer_toppings_menu_func');

