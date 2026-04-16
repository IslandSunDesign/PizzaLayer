<?php
namespace PizzaLayer\Admin;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * PizzaLayer Admin Bar
 *
 * Top-bar structure:
 *   🍕 PizzaLayer  ───────────────────────────────────────── root
 *   ├─ 🏠 Dashboard
 *   │
 *   ├─ ── CONTENT ──
 *   ├─ 🍕 Toppings     [All]  [+ New]
 *   ├─ ⬤  Crusts       [All]  [+ New]
 *   ├─ 🥫 Sauces       [All]  [+ New]
 *   ├─ 🧀 Cheeses      [All]  [+ New]
 *   ├─ 💧 Drizzles     [All]  [+ New]
 *   ├─ ✂  Cuts         [All]  [+ New]
 *   ├─ 📏 Sizes        [All]  [+ New]
 *   │
 *   ├─ ── TOOLS ──
 *   ├─ 📋 Setup Guide
 *   ├─ </> Shortcode Generator
 *   ├─ 🎨 Template
 *   ├─ ⚙  Settings
 *   └─ ❓ Help
 */
class AdminBar {

	/** CPT definitions */
	private const CPTS = [
		'toppings' => [ 'label' => 'Toppings', 'singular' => 'Topping',  'emoji' => '🍕', 'icon' => 'dashicons-tag'              ],
		'crusts'   => [ 'label' => 'Crusts',   'singular' => 'Crust',    'emoji' => '⬤',  'icon' => 'dashicons-admin-page'       ],
		'sauces'   => [ 'label' => 'Sauces',   'singular' => 'Sauce',    'emoji' => '🥫', 'icon' => 'dashicons-portfolio'        ],
		'cheeses'  => [ 'label' => 'Cheeses',  'singular' => 'Cheese',   'emoji' => '🧀', 'icon' => 'dashicons-star-filled'      ],
		'drizzles' => [ 'label' => 'Drizzles', 'singular' => 'Drizzle',  'emoji' => '💧', 'icon' => 'dashicons-admin-customizer' ],
		'cuts'     => [ 'label' => 'Cuts',     'singular' => 'Cut',      'emoji' => '✂',  'icon' => 'dashicons-image-crop'       ],
		'sizes'    => [ 'label' => 'Sizes',    'singular' => 'Size',     'emoji' => '📏', 'icon' => 'dashicons-editor-expand'    ],

	];

	public function register( \WP_Admin_Bar $bar ): void {
		if ( ! current_user_can( 'manage_options' ) ) { return; }

		$hub       = admin_url( 'admin.php?page=pizzalayer-content' );
		$dashboard = admin_url( 'admin.php?page=pizzalayer' );

		// ── Root ─────────────────────────────────────────────────────────
		$bar->add_menu( [
			'id'    => 'pizzalayer',
			'title' => '<span class="ab-icon pzlab-root-icon" aria-hidden="true">🍕</span>'
			         . '<span class="ab-label">PizzaLayer</span>',
			'href'  => $dashboard,
			'meta'  => [ 'title' => __( 'PizzaLayer Dashboard', 'pizzalayer' ) ],
		] );

		// ── Dashboard link ───────────────────────────────────────────────
		$bar->add_menu( [
			'parent' => 'pizzalayer',
			'id'     => 'pizzalayer-dashboard',
			'title'  => esc_html__( 'Dashboard', 'pizzalayer' ),
			'href'   => $dashboard,
			'meta'   => [ 'title' => __( 'PizzaLayer Dashboard', 'pizzalayer' ) ],
		] );

		// ── CONTENT group separator ──────────────────────────────────────
		$bar->add_menu( [
			'parent' => 'pizzalayer',
			'id'     => 'pizzalayer-grp-content',
			'title'  => '<span class="pzlab-group-label">' . esc_html__( 'Content', 'pizzalayer' ) . '</span>',
			'href'   => $hub,
			'meta'   => [ 'class' => 'pzlab-group-header' ],
		] );

		// ── CPT items (All + +New sub-links) ─────────────────────────────
		foreach ( self::CPTS as $slug => $meta ) {
			$cpt      = 'pizzalayer_' . $slug;
			$list_url = add_query_arg( 'pl_cpt', $slug, $hub );
			$new_url  = admin_url( 'post-new.php?post_type=' . $cpt );

			// Parent row — links to "All" in ContentHub
			$bar->add_menu( [
				'parent' => 'pizzalayer',
				'id'     => 'pizzalayer-cpt-' . $slug,
				'title'  => esc_html( $meta['label'] ),
				'href'   => $list_url,
				'meta'   => [ 'class' => 'pzlab-cpt-row', 'title' => sprintf( __( 'Manage %s', 'pizzalayer' ), $meta['label'] ) ],
			] );

			// Sub-link: All
			$bar->add_menu( [
				'parent' => 'pizzalayer-cpt-' . $slug,
				'id'     => 'pizzalayer-cpt-' . $slug . '-all',
				'title'  => sprintf( esc_html__( 'All %s', 'pizzalayer' ), esc_html( $meta['label'] ) ),
				'href'   => $list_url,
				'meta'   => [ 'title' => sprintf( __( 'View all %s', 'pizzalayer' ), $meta['label'] ) ],
			] );

			// Sub-link: Add New
			$bar->add_menu( [
				'parent' => 'pizzalayer-cpt-' . $slug,
				'id'     => 'pizzalayer-cpt-' . $slug . '-new',
				'title'  => sprintf( esc_html__( 'Add New %s', 'pizzalayer' ), esc_html( $meta['singular'] ) ),
				'href'   => $new_url,
				'meta'   => [ 'title' => sprintf( __( 'Add a new %s', 'pizzalayer' ), $meta['singular'] ) ],
			] );
		}

		// ── TOOLS group separator ────────────────────────────────────────
		$bar->add_menu( [
			'parent' => 'pizzalayer',
			'id'     => 'pizzalayer-grp-tools',
			'title'  => '<span class="pzlab-group-label">' . esc_html__( 'Tools', 'pizzalayer' ) . '</span>',
			'href'   => '#',
			'meta'   => [ 'class' => 'pzlab-group-header' ],
		] );

		// ── Tool items ───────────────────────────────────────────────────
		$tools = [
			[
				'id'    => 'pizzalayer-setup',
				'icon'  => 'dashicons-welcome-learn-more',
				'label' => __( 'Setup Guide', 'pizzalayer' ),
				'href'  => admin_url( 'admin.php?page=pizzalayer-setup' ),
				'tip'   => __( 'Step-by-step onboarding guide', 'pizzalayer' ),
			],
			[
				'id'    => 'pizzalayer-shortcodes',
				'icon'  => 'dashicons-editor-code',
				'label' => __( 'Shortcode Generator', 'pizzalayer' ),
				'href'  => admin_url( 'admin.php?page=pizzalayer-shortcodes' ),
				'tip'   => __( 'Build shortcodes with a visual UI', 'pizzalayer' ),
			],
			[
				'id'    => 'pizzalayer-template',
				'icon'  => 'dashicons-admin-appearance',
				'label' => __( 'Template', 'pizzalayer' ),
				'href'  => admin_url( 'admin.php?page=pizzalayer-template' ),
				'tip'   => __( 'Switch or preview templates', 'pizzalayer' ),
			],
			[
				'id'    => 'pizzalayer-settings',
				'icon'  => 'dashicons-admin-settings',
				'label' => __( 'Settings', 'pizzalayer' ),
				'href'  => admin_url( 'admin.php?page=pizzalayer-settings' ),
				'tip'   => __( 'Global plugin settings', 'pizzalayer' ),
			],
			[
				'id'    => 'pizzalayer-help',
				'icon'  => 'dashicons-editor-help',
				'label' => __( 'Help & Reference', 'pizzalayer' ),
				'href'  => admin_url( 'admin.php?page=pizzalayer-help' ),
				'tip'   => __( 'Full documentation and developer reference', 'pizzalayer' ),
			],
		];

		foreach ( $tools as $tool ) {
			$bar->add_menu( [
				'parent' => 'pizzalayer',
				'id'     => $tool['id'],
				'title'  => esc_html( $tool['label'] ),
				'href'   => $tool['href'],
				'meta'   => [ 'title' => $tool['tip'] ],
			] );
		}

		// ── Tool sub-links: Shortcode Generator actions ──────────────────
		$sc_types = [
			[ 'label' => __( 'Builder shortcode', 'pizzalayer' ),  'hash' => '#builder' ],
			[ 'label' => __( 'Static shortcode', 'pizzalayer' ),   'hash' => '#static'  ],
			[ 'label' => __( 'Layer image shortcode', 'pizzalayer' ), 'hash' => '#layer' ],
		];
		foreach ( $sc_types as $sc ) {
			$bar->add_menu( [
				'parent' => 'pizzalayer-shortcodes',
				'id'     => 'pizzalayer-sc-' . sanitize_title( $sc['label'] ),
				'title'  => esc_html( $sc['label'] ),
				'href'   => admin_url( 'admin.php?page=pizzalayer-shortcodes' ) . $sc['hash'],
			] );
		}

		// ── Settings sub-links ───────────────────────────────────────────
		$settings_sections = [
			[ 'label' => __( 'Default Layers', 'pizzalayer' ),  'id' => 'defaults' ],
			[ 'label' => __( 'Pizza Shape', 'pizzalayer' ),     'id' => 'shape'    ],
			[ 'label' => __( 'Layer Animation', 'pizzalayer' ), 'id' => 'animation'],
			[ 'label' => __( 'Branding', 'pizzalayer' ),        'id' => 'branding' ],
		];
		foreach ( $settings_sections as $sec ) {
			$bar->add_menu( [
				'parent' => 'pizzalayer-settings',
				'id'     => 'pizzalayer-settings-' . $sec['id'],
				'title'  => esc_html( $sec['label'] ),
				'href'   => admin_url( 'admin.php?page=pizzalayer-settings' ) . '#' . $sec['id'],
			] );
		}

		// ── Help sub-links (section navigation) ──────────────────────────
		$help_sections = [
			[ 'key' => 'quickstart', 'label' => __( 'Quickstart Guide', 'pizzalayer' )       ],
			[ 'key' => 'content',    'label' => __( 'Managing Content', 'pizzalayer' )       ],
			[ 'key' => 'layers',     'label' => __( 'Layer Type Reference', 'pizzalayer' )   ],
			[ 'key' => 'shortcodes', 'label' => __( 'Shortcode Reference', 'pizzalayer' )    ],
			[ 'key' => 'shapes',     'label' => __( 'Shape & Animation', 'pizzalayer' )      ],
			[ 'key' => 'templates',  'label' => __( 'Template System', 'pizzalayer' )        ],
			[ 'key' => 'faq',        'label' => __( 'FAQ', 'pizzalayer' )                    ],
			[ 'key' => 'developer',  'label' => __( 'Developer Reference', 'pizzalayer' )    ],
		];
		foreach ( $help_sections as $sec ) {
			$bar->add_menu( [
				'parent' => 'pizzalayer-help',
				'id'     => 'pizzalayer-help-' . $sec['key'],
				'title'  => esc_html( $sec['label'] ),
				'href'   => add_query_arg( 'section', $sec['key'], admin_url( 'admin.php?page=pizzalayer-help' ) ),
			] );
		}

		// ── View Demo (if configured) ────────────────────────────────────
		if ( get_option( 'pizzalayer_setting_settings_demonotice', '' ) ) {
			$bar->add_menu( [
				'parent' => 'pizzalayer',
				'id'     => 'pizzalayer-view-demo',
				'title'  => esc_html__( 'View Demo', 'pizzalayer' ),
				'href'   => home_url( '/?pizzalayer_demo=1' ),
				'meta'   => [ 'target' => '_blank', 'title' => __( 'Open front-end demo in new tab', 'pizzalayer' ) ],
			] );
		}

		// ── Hook for Pro / custom additions ─────────────────────────────
		do_action( 'pizzalayer_admin_bar_menu', $bar );

		// ── Inject styles — works on both admin and front-end bar ────────
		add_action( 'admin_head', [ $this, 'print_styles' ] );
		add_action( 'wp_head',    [ $this, 'print_styles' ] );

		// Ensure dashicons are available on the front end (for the admin bar)
		add_action( 'wp_enqueue_scripts', function() {
			if ( is_admin_bar_showing() ) {
				wp_enqueue_style( 'dashicons' );
			}
		} );
	}

	public function print_styles(): void {
		?>
		<style id="pzl-admin-bar-styles">
		/* ── PizzaLayer Admin Bar ───────────────────────────────────── */

		/* Root emoji icon */
		#wpadminbar #wp-admin-bar-pizzalayer > .ab-item .pzlab-root-icon {
			display: inline-block !important;
			font-size: 16px !important;
			line-height: 1 !important;
			margin-right: 4px !important;
			vertical-align: middle !important;
			position: relative;
			top: -1px;
		}

		/* Group headers — visual separators with uppercase label */
		#wpadminbar .pzlab-group-header > .ab-item {
			pointer-events: none !important;
			cursor: default !important;
			padding: 0 !important;
			height: auto !important;
			background: transparent !important;
		}
		#wpadminbar .pzlab-group-header > .ab-item:hover { color: inherit !important; }
		.pzlab-group-label {
			display: block !important;
			font-size: 9.5px !important;
			font-weight: 700 !important;
			letter-spacing: .1em !important;
			text-transform: uppercase !important;
			color: rgba(240,246,252,.28) !important;
			padding: 8px 14px 3px !important;
			border-top: 1px solid rgba(240,246,252,.14) !important;
			margin-top: 4px !important;
		}

		/* Spacer lines between submenu items at section boundaries */
		#wpadminbar #wp-admin-bar-pizzalayer-dashboard > .ab-item {
			border-bottom: 1px solid rgba(240,246,252,.07) !important;
			margin-bottom: 2px !important;
		}

		/* CPT rows — flex so the item label and +New sub-links align */
		#wpadminbar .pzlab-cpt-row > .ab-item {
			display: flex !important;
			align-items: center !important;
		}

		/* Highlight currently-active CPT */
		#wpadminbar #wp-admin-bar-pizzalayer-cpt-<?php echo esc_js( $this->get_current_cpt_slug() ); ?> > .ab-item {
			color: #ff8c42 !important;
		}

		/* Compact: don't let the dropdown get too tall on mobile */
		@media screen and (max-width: 600px) {
			#wpadminbar #wp-admin-bar-pizzalayer .ab-sub-wrapper { max-height: 80vh; overflow-y: auto; }
		}
		</style>
		<?php
	}

	/** Return the current CPT slug for sidebar/bar highlighting. */
	private function get_current_cpt_slug(): string {
		global $pagenow;
		if ( ! isset( $pagenow ) ) { return ''; }
		if ( $pagenow === 'admin.php'
			&& isset( $_GET['page'] )
			&& $_GET['page'] === 'pizzalayer-content'
			&& isset( $_GET['pl_cpt'] ) ) {
			return sanitize_key( $_GET['pl_cpt'] );
		}
		if ( in_array( $pagenow, [ 'edit.php', 'post-new.php', 'post.php' ], true ) ) {
			$pt = sanitize_key( $_GET['post_type'] ?? get_post_type() ?? '' );
			return str_replace( 'pizzalayer_', '', $pt );
		}
		return '';
	}
}
