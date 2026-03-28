<?php
namespace PizzaLayer\Core;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Activator {
	public static function activate(): void {
		// Set defaults on first activation
		if ( false === get_option( 'pizzalayer_setting_global_template' ) ) {
			update_option( 'pizzalayer_setting_global_template', 'nightpie' );
		}
		if ( false === get_option( 'pizzalayer_setting_topping_maxtoppings' ) ) {
			update_option( 'pizzalayer_setting_topping_maxtoppings', 10 );
		}
		flush_rewrite_rules();
	}
}
