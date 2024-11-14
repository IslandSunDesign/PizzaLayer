<?php
 
/**
 
 * @package pizzalayer
 
 */
 
/*
 
Plugin Name: Pizza Layer
Plugin URI: https://pizzalayer.com 
Description: pizza toppings customizer and pizza enhancements for woocommerce 
Version: .9
Author: RyanBishop 
Author URI: http://www.pizzalayer.com
License: GPLv2 or later 
Text Domain: pizzalayer
 
*/


/* +===  READ PLUGIN OPTIONS THEN ASSEMBLE A FEW VARS === */


/* +===  ENQUEUE BASE CSS & JS +=========  */
wp_register_style( 'pizzalayer-css', plugins_url( 'includes/css/pizzalayer.css', __FILE__ ) );
wp_enqueue_style( 'pizzalayer-css', plugins_url( 'includes/css/pizzalayer.css', __FILE__ ) );

wp_register_style( 'pizzalayer-bootsrap-grid-css', plugins_url( 'includes/css/bootstrap-grid-system.css', __FILE__ ) );
wp_enqueue_style( 'pizzalayer-bootstrap-grid-css', plugins_url( 'includes/css/bootstrap-grid-system.css', __FILE__ ) );

wp_register_style( 'pizzalayer-fontawesome', plugins_url( 'includes/fontawesome/all.css', __FILE__ ) );
wp_enqueue_style( 'pizzalayer-fontawesome', plugins_url( 'includes/fontawesome/all.css', __FILE__ ) );

wp_enqueue_script( 'pizzalayer-js', plugins_url( 'includes/js/pizzalayer-main.js', __FILE__ ), array(), '0.9.0', true );

/* +===  LOAD TEMPLATE +=========  */
include plugin_dir_path( __FILE__ ) . 'templates/template.php';

/* +===  PATH VARIABLES +=========  */
$pizzalayer_path = plugin_dir_url( __FILE__ );
$pizzalayer_path_assets = plugin_dir_url( __FILE__ ) . 'assets/';
$pizzalayer_path_images = plugin_dir_url( __FILE__ ) . 'assets/images/';

/* +===  PLUGIN OPTIONS & DASHBOARD MENU PAGE +=========  */
include plugin_dir_path( __FILE__ ) . 'includes/admin/dashboard-menu.php';
include plugin_dir_path( __FILE__ ) . 'includes/admin/admin-bar-menu.php';
include plugin_dir_path( __FILE__ ) . 'includes/admin/customizer.php';

/* +===  CUSTOMIZER CSS  +=========  */
include plugin_dir_path(__FILE__) . 'includes/public/topper-ui-css.php';

/* +===  PIZZA BUILDER  +=========  */
include plugin_dir_path(__FILE__) . 'includes/builder/topper-ui-pizza-layers.php';
include plugin_dir_path(__FILE__) . 'includes/builder/topper-ui-pizza-builder.php';

/* +===  CREATE CPTs FOR TOPPINGS, SAUCES, CRUSTS +=========  */
include plugin_dir_path(__FILE__) . 'includes/init/cpt-toppings.php';
include plugin_dir_path(__FILE__) . 'includes/init/cpt-cheeses.php';
include plugin_dir_path(__FILE__) . 'includes/init/cpt-sauces.php';
include plugin_dir_path(__FILE__) . 'includes/init/cpt-drizzles.php';
include plugin_dir_path(__FILE__) . 'includes/init/cpt-crusts.php';

/* +===  CREATE CPTs FOR PIZZA PRESETS +=========  */
include plugin_dir_path(__FILE__) . 'includes/init/cpt-pizza-presets.php';

/* +===  CREATE CUSTOM FIELDS FOR TOPPINGS, SAUCES, AND CRUSTS +=========  */
/* Custom fields now integrated using Advanced Custom Fields, located in the 'ACF' menu in your WordPress dashboard */

/* +===  CREATE CPT + CUSTOM FIELDS FOR SLICING / CUT CHART LAYERS +=========  */
include plugin_dir_path(__FILE__) . 'includes/init/cpt-cuts.php';

/* +===  TOPPINGS VISUALIZER / TOPPER +=========  */
include plugin_dir_path(__FILE__) . 'includes/public/topper.php';

/* +===  MENU - DASHBOARD +=========  */


/* +===  MENU - WP TOP BAR +=========  */
