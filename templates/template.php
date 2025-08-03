<?php
/**
 * Template Loader for PizzaLayer Templates
 */

if ( ! function_exists( 'pizzalayer_load_template_files' ) ) {
	function pizzalayer_load_template_files() {

		do_action( 'pizzalayer_file_template_start' );

		// +=== Global Vars and Defaults ===+
		$pizzalayer_template_name_default     = 'glassy';
		$pizzalayer_template_name_set_by_user = get_option( 'pizzalayer_setting_global_template' );
		$pizzalayer_templates_folder_path     = plugin_dir_path( __FILE__ );
		$pizzalayer_templates_theme_folder_path = get_stylesheet_directory() . '/pzttemplates/';
		define( 'Pizzalayer_TEMPLATES_PATH', $pizzalayer_templates_folder_path );

		do_action( 'pizzalayer_file_template_after_vars' );

		// +=== Choose Template ===+
		if ( $pizzalayer_template_name_set_by_user && file_exists( $pizzalayer_templates_folder_path . $pizzalayer_template_name_set_by_user . '/' ) ) {
			$pizzalayer_template_name = $pizzalayer_template_name_set_by_user;
		} else {
			$pizzalayer_template_name = $pizzalayer_template_name_default;
		}

	/*	if ( function_exists( 'write_log' ) ) {
			write_log( $pizzalayer_template_name . ' | ' . $pizzalayer_template_name_default );
		} */

		// +=== Enqueue Template CSS and JS ===+
		add_action( 'wp_enqueue_scripts', function () use ( $pizzalayer_template_name ) {
			wp_register_style(
				'pizzalayer-template-base-css-' . $pizzalayer_template_name,
				plugins_url( $pizzalayer_template_name . '/template.css', __FILE__ )
			);
			wp_enqueue_style( 'pizzalayer-template-base-css-' . $pizzalayer_template_name );

			wp_register_script(
				'pizzalayer_template_custom_javascript',
				plugin_dir_url( __FILE__ ) . $pizzalayer_template_name . '/custom.js',
				[ 'jquery' ],
				null,
				true
			);
			wp_enqueue_script( 'pizzalayer_template_custom_javascript' );
		} );

		// +=== Load Template Files ===+
		if ( is_dir( $pizzalayer_templates_theme_folder_path . $pizzalayer_template_name ) ) {
			$template_base = $pizzalayer_templates_theme_folder_path . $pizzalayer_template_name . '/';
		} elseif ( is_dir( $pizzalayer_templates_folder_path . $pizzalayer_template_name ) ) {
			$template_base = $pizzalayer_templates_folder_path . $pizzalayer_template_name . '/';
		} else {
			$template_base = $pizzalayer_templates_folder_path . $pizzalayer_template_name_default . '/';
		}

		foreach ( [
			'pztp-containers-menu.php',
			'pztp-containers-presentation.php',
			'pztp-template-custom.php',
			'pztp-template-css.php',
			'pztp-template-options.php',
			'pztp-template-info.php',
		] as $template_file ) {
			$template_path = $template_base . $template_file;
			if ( file_exists( $template_path ) ) {
				include $template_path;
			}
		}
	} // end pizzalayer_load_template_files()
}

// +=== Utility Functions ===+

if ( ! function_exists( 'pizzalayer_template_get_templates' ) ) {
	function pizzalayer_template_get_templates() {
		$folder = plugin_dir_path( __FILE__ );
		do_action( 'func_pizzalayer_template_get_templates_after_folder_path' );
		$list = '';
		foreach ( glob( $folder . '/*', GLOB_ONLYDIR ) as $dir ) {
			$list .= '<li>' . basename( $dir ) . '</li>';
		}
		return '<ul class="pizzalayer-templates-list-ul">' . $list . '</ul><style>.pizzalayer-templates-list-ul{padding:8px 16px;font-size:22px;text-transform:uppercase;font-weight:600;}</style>';
	}
}

if ( ! function_exists( 'pizzalayer_template_get_templates_as_array' ) ) {
	function pizzalayer_template_get_templates_as_array() {
		$list = [];
		$plugin_path = plugin_dir_path( __FILE__ );
		$theme_path  = get_stylesheet_directory() . '/pzttemplates/';

		do_action( 'func_pizzalayer_template_get_templates_as_array_after_folder_paths' );

		foreach ( glob( $plugin_path . '/*', GLOB_ONLYDIR ) as $dir ) {
			$name         = basename( $dir );
			$list[ $name ] = $name;
		}

		if ( is_dir( $theme_path ) ) {
			foreach ( glob( $theme_path . '/*', GLOB_ONLYDIR ) as $dir ) {
				$name         = basename( $dir );
				$list[ $name ] = $name;
			}
		}

		return $list;
	}
}

if ( ! function_exists( 'pizzalayer_template_get_templates_file_path' ) ) {
	function pizzalayer_template_get_templates_file_path() {
		return plugin_dir_path( __FILE__ );
	}
}

// +=== Execute Template Loader ===+
pizzalayer_load_template_files();
