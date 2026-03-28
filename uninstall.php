<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) { exit; }
// Clean up plugin options on delete
$options = [
    'pizzalayer_setting_global_template',
    'pizzalayer_setting_topping_maxtoppings',
    'pizzalayer_setting_crust_defaultcrust',
    'pizzalayer_setting_sauce_defaultsauce',
    'pizzalayer_setting_cheese_defaultcheese',
    'pizzalayer_setting_drizzle_defaultdrizzle',
    'pizzalayer_setting_cut_defaultcut',
];
foreach ( $options as $opt ) { delete_option( $opt ); }
// Note: CPT posts are preserved on uninstall (standard WP convention).
// To also delete CPT data, implement a separate "Reset Data" admin action.
