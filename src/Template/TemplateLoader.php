<?php
namespace PizzaLayer\Template;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Locates and loads the active PizzaLayer template.
 * Currently only NightPie ships with the base plugin.
 * Third-party templates can register via the pizzalayer_template_dirs filter.
 */
class TemplateLoader {

	/**
	 * Return all discovered template slugs → info arrays.
	 * Scans PIZZALAYER_TEMPLATES_DIR and any dirs added via filter.
	 *
	 * @return array<string, array> slug => info array from pztp-template-info.php
	 */
	public function get_available_templates(): array {
		$dirs      = apply_filters( 'pizzalayer_template_dirs', [ PIZZALAYER_TEMPLATES_DIR ] );
		$templates = [];

		foreach ( $dirs as $dir ) {
			if ( ! is_dir( $dir ) ) { continue; }
			foreach ( glob( trailingslashit( $dir ) . '*', GLOB_ONLYDIR ) as $template_dir ) {
				$info_file = trailingslashit( $template_dir ) . 'pztp-template-info.php';
				if ( ! file_exists( $info_file ) ) { continue; }
				$info = include $info_file; // should return array
				if ( is_array( $info ) ) {
					$slug              = basename( $template_dir );
					$templates[ $slug ] = $info;
				}
			}
		}
		return $templates;
	}

	/**
	 * Get the active template slug.
	 */
	public function get_active_slug(): string {
		$slug = (string) get_option( 'pizzalayer_setting_global_template', 'nightpie' );
		$templates = $this->get_available_templates();
		return isset( $templates[ $slug ] ) ? $slug : 'nightpie';
	}

	/**
	 * Get the filesystem path to a file within the active template directory.
	 */
	public function get_template_file( string $filename, string $slug = '' ): string {
		if ( ! $slug ) { $slug = $this->get_active_slug(); }
		return PIZZALAYER_TEMPLATES_DIR . $slug . '/' . $filename;
	}

	/**
	 * Get the URL to a file within the active template directory.
	 */
	public function get_template_url( string $filename, string $slug = '' ): string {
		if ( ! $slug ) { $slug = $this->get_active_slug(); }
		return PIZZALAYER_TEMPLATES_URL . $slug . '/' . $filename;
	}

	/**
	 * Include a template file (must be within plugin templates dir for safety).
	 */
	public function include_file( string $filename, string $slug = '' ): void {
		$path = $this->get_template_file( $filename, $slug );
		if ( file_exists( $path ) ) {
			include $path;
		}
	}
}
