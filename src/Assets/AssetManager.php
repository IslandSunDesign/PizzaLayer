<?php
namespace PizzaLayer\Assets;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class AssetManager {

	public function enqueue_frontend(): void {
		$v = PIZZALAYER_VERSION;

		wp_enqueue_style( 'pizzalayer-css',            PIZZALAYER_ASSETS_URL . 'css/pizzalayer.css',            [], $v );
		wp_enqueue_style( 'pizzalayer-bootstrap-grid', PIZZALAYER_ASSETS_URL . 'css/bootstrap-grid-system.css', [], $v );
		wp_enqueue_script( 'pizzalayer-js',            PIZZALAYER_ASSETS_URL . 'js/pizzalayer-main.js',         [ 'jquery' ], $v, true );

		$loader = new \PizzaLayer\Template\TemplateLoader();
		$slug   = $loader->get_active_slug();

		if ( file_exists( $loader->get_template_file( 'template.css', $slug ) ) ) {
			wp_enqueue_style( 'pizzalayer-template-' . $slug, $loader->get_template_url( 'template.css', $slug ), [ 'pizzalayer-css' ], $v );
		}
		if ( file_exists( $loader->get_template_file( 'custom.js', $slug ) ) ) {
			wp_enqueue_script( 'pizzalayer-template-' . $slug, $loader->get_template_url( 'custom.js', $slug ), [ 'jquery', 'pizzalayer-js' ], $v, true );
		}
	}

	/**
	 * Enqueue styles in the block editor so server-side-rendered previews
	 * (ServerSideRender) look correct inside the editor iframe/canvas.
	 * This is called on the enqueue_block_editor_assets hook.
	 */
	public function enqueue_block_editor(): void {
		$v = PIZZALAYER_VERSION;

		// Template CSS in the editor so SSR previews are styled
		$loader = new \PizzaLayer\Template\TemplateLoader();
		$slug   = $loader->get_active_slug();

		wp_enqueue_style( 'pizzalayer-css', PIZZALAYER_ASSETS_URL . 'css/pizzalayer.css', [], $v );

		if ( file_exists( $loader->get_template_file( 'template.css', $slug ) ) ) {
			wp_enqueue_style(
				'pizzalayer-template-' . $slug,
				$loader->get_template_url( 'template.css', $slug ),
				[ 'pizzalayer-css' ],
				$v
			);
		}
	}

	public function enqueue_admin( string $hook ): void {
		if ( false === strpos( $hook, 'pizzalayer' ) ) { return; }
		wp_enqueue_style( 'pizzalayer-admin-tabs', PIZZALAYER_ASSETS_URL . 'css/admin-tabs.css', [], PIZZALAYER_VERSION );
		wp_enqueue_script( 'pizzalayer-admin-js',  PIZZALAYER_ASSETS_URL . 'js/admin-tabs.js',   [ 'jquery' ], PIZZALAYER_VERSION, true );
	}
}
