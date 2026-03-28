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
 *   ├─ 🍕 Presets      [All]  [+ New]
 *   │
 *   ├─ ── ADD NEW ──
 *   ├─ + New Topping
 *   ├─ + New Crust
 *   ├─ + New Sauce
 *   ├─ + New Cheese
 *   ├─ + New Drizzle
 *   ├─ + New Cut
 *   ├─ + New Size
 *   ├─ + New Preset
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
		'toppings' => [ 'label' => 'Toppings', 'singular' => 'Topping',  'emoji' => '🍕', 'icon' => 'dashicons-carrot'           ],
		'crusts'   => [ 'label' => 'Crusts',   'singular' => 'Crust',    'emoji' => '⬤',  'icon' => 'dashicons-admin-generic'    ],
		'sauces'   => [ 'label' => 'Sauces',   'singular' => 'Sauce',    'emoji' => '🥫', 'icon' => 'dashicons-food'             ],
		'cheeses'  => [ 'label' => 'Cheeses',  'singular' => 'Cheese',   'emoji' => '🧀', 'icon' => 'dashicons-category'         ],
		'drizzles' => [ 'label' => 'Drizzles', 'singular' => 'Drizzle',  'emoji' => '💧', 'icon' => 'dashicons-admin-customizer' ],
		'cuts'     => [ 'label' => 'Cuts',     'singular' => 'Cut',      'emoji' => '✂',  'icon' => 'dashicons-editor-table'     ],
		'sizes'    => [ 'label' => 'Sizes',    'singular' => 'Size',     'emoji' => '📏', 'icon' => 'dashicons-image-rotate'     ],
		'pizzas'   => [ 'label' => 'Presets',  'singular' => 'Preset',   'emoji' => '🍕', 'icon' => 'dashicons-pizza'            ],
	];

	public function register( \WP_Admin_Bar $bar ): void {
		if ( ! current_user_can( 'manage_options' ) ) { return; }

		$hub       = admin_url( 'admin.php?page=pizzalayer-content' );
		$dashboard = admin_url( 'admin.php?page=pizzalayer' );

		// ── Root ─────────────────────────────────────────────────────────
		$bar->add_menu( [
			'id'    => 'pizzalayer',
			'title' => '<span class="ab-icon dashicons dashicons-pizza pzlab-root-icon"></span>'
			         . '<span class="ab-label">PizzaLayer</span>',
			'href'  => $dashboard,
			'meta'  => [ 'title' => 'PizzaLayer Dashboard' ],
		] );

		// ── Dashboard link ───────────────────────────────────────────────
		$bar->add_menu( [
			'parent' => 'pizzalayer',
			'id'     => 'pizzalayer-dashboard',
			'title'  => '<span class="dashicons dashicons-dashboard pzlab-icon"></span> Dashboard',
			'href'   => $dashboard,
			'meta'   => [ 'title' => 'PizzaLayer Dashboard' ],
		] );

		// ── CONTENT group separator ──────────────────────────────────────
		$bar->add_menu( [
			'parent' => 'pizzalayer',
			'id'     => 'pizzalayer-grp-content',
			'title'  => '<span class="pzlab-group-label">Content</span>',
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
				'title'  => '<span class="dashicons ' . esc_attr( $meta['icon'] ) . ' pzlab-icon"></span>'
				          . esc_html( $meta['label'] ),
				'href'   => $list_url,
				'meta'   => [ 'class' => 'pzlab-cpt-row', 'title' => 'Manage ' . $meta['label'] ],
			] );

			// Sub-link: All
			$bar->add_menu( [
				'parent' => 'pizzalayer-cpt-' . $slug,
				'id'     => 'pizzalayer-cpt-' . $slug . '-all',
				'title'  => '<span class="dashicons dashicons-list-view pzlab-icon"></span> All ' . esc_html( $meta['label'] ),
				'href'   => $list_url,
				'meta'   => [ 'title' => 'View all ' . $meta['label'] ],
			] );

			// Sub-link: Add New
			$bar->add_menu( [
				'parent' => 'pizzalayer-cpt-' . $slug,
				'id'     => 'pizzalayer-cpt-' . $slug . '-new',
				'title'  => '<span class="dashicons dashicons-plus-alt2 pzlab-icon"></span> Add New ' . esc_html( $meta['singular'] ),
				'href'   => $new_url,
				'meta'   => [ 'title' => 'Add a new ' . $meta['singular'] ],
			] );
		}

		// ── ADD NEW group separator ──────────────────────────────────────
		$bar->add_menu( [
			'parent' => 'pizzalayer',
			'id'     => 'pizzalayer-grp-addnew',
			'title'  => '<span class="pzlab-group-label">Add New</span>',
			'href'   => '#',
			'meta'   => [ 'class' => 'pzlab-group-header' ],
		] );

		// ── Add New quick-links ──────────────────────────────────────────
		foreach ( self::CPTS as $slug => $meta ) {
			$bar->add_menu( [
				'parent' => 'pizzalayer',
				'id'     => 'pizzalayer-new-' . $slug,
				'title'  => '<span class="pzlab-addnew-plus">+</span> New ' . esc_html( $meta['singular'] ),
				'href'   => admin_url( 'post-new.php?post_type=pizzalayer_' . $slug ),
				'meta'   => [ 'class' => 'pzlab-addnew-item', 'title' => 'Add new ' . $meta['singular'] ],
			] );
		}

		// ── TOOLS group separator ────────────────────────────────────────
		$bar->add_menu( [
			'parent' => 'pizzalayer',
			'id'     => 'pizzalayer-grp-tools',
			'title'  => '<span class="pzlab-group-label">Tools</span>',
			'href'   => '#',
			'meta'   => [ 'class' => 'pzlab-group-header' ],
		] );

		// ── Tool items ───────────────────────────────────────────────────
		$tools = [
			[
				'id'    => 'pizzalayer-setup',
				'icon'  => 'dashicons-welcome-learn-more',
				'label' => 'Setup Guide',
				'href'  => admin_url( 'admin.php?page=pizzalayer-setup' ),
				'tip'   => 'Step-by-step onboarding guide',
			],
			[
				'id'    => 'pizzalayer-shortcodes',
				'icon'  => 'dashicons-editor-code',
				'label' => 'Shortcode Generator',
				'href'  => admin_url( 'admin.php?page=pizzalayer-shortcodes' ),
				'tip'   => 'Build shortcodes with a visual UI',
			],
			[
				'id'    => 'pizzalayer-template',
				'icon'  => 'dashicons-admin-appearance',
				'label' => 'Template',
				'href'  => admin_url( 'admin.php?page=pizzalayer-template' ),
				'tip'   => 'Switch or preview templates',
			],
			[
				'id'    => 'pizzalayer-settings',
				'icon'  => 'dashicons-admin-settings',
				'label' => 'Settings',
				'href'  => admin_url( 'admin.php?page=pizzalayer-settings' ),
				'tip'   => 'Global plugin settings',
			],
			[
				'id'    => 'pizzalayer-help',
				'icon'  => 'dashicons-editor-help',
				'label' => 'Help & Reference',
				'href'  => admin_url( 'admin.php?page=pizzalayer-help' ),
				'tip'   => 'Full documentation and developer reference',
			],
		];

		foreach ( $tools as $tool ) {
			$bar->add_menu( [
				'parent' => 'pizzalayer',
				'id'     => $tool['id'],
				'title'  => '<span class="dashicons ' . esc_attr( $tool['icon'] ) . ' pzlab-icon"></span> ' . esc_html( $tool['label'] ),
				'href'   => $tool['href'],
				'meta'   => [ 'title' => $tool['tip'] ],
			] );
		}

		// ── Tool sub-links: Shortcode Generator actions ──────────────────
		$sc_types = [
			[ 'label' => 'Builder shortcode',  'hash' => '#builder' ],
			[ 'label' => 'Static shortcode',   'hash' => '#static'  ],
			[ 'label' => 'Layer image shortcode', 'hash' => '#layer' ],
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
			[ 'label' => 'Default Layers',  'id' => 'defaults' ],
			[ 'label' => 'Pizza Shape',     'id' => 'shape'    ],
			[ 'label' => 'Layer Animation', 'id' => 'animation'],
			[ 'label' => 'Branding',        'id' => 'branding' ],
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
			[ 'key' => 'quickstart', 'label' => 'Quickstart Guide'       ],
			[ 'key' => 'content',    'label' => 'Managing Content'       ],
			[ 'key' => 'layers',     'label' => 'Layer Type Reference'   ],
			[ 'key' => 'shortcodes', 'label' => 'Shortcode Reference'    ],
			[ 'key' => 'shapes',     'label' => 'Shape & Animation'      ],
			[ 'key' => 'templates',  'label' => 'Template System'        ],
			[ 'key' => 'faq',        'label' => 'FAQ'                    ],
			[ 'key' => 'developer',  'label' => 'Developer Reference'    ],
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
				'title'  => '<span class="dashicons dashicons-visibility pzlab-icon"></span> View Demo',
				'href'   => home_url( '/?pizzalayer_demo=1' ),
				'meta'   => [ 'target' => '_blank', 'title' => 'Open front-end demo in new tab' ],
			] );
		}

		// ── Hook for Pro / custom additions ─────────────────────────────
		do_action( 'pizzalayer_admin_bar_menu', $bar );

		// ── Inject styles — works on both admin and front-end bar ────────
		add_action( 'admin_head', [ $this, 'print_styles' ] );
		add_action( 'wp_head',    [ $this, 'print_styles' ] );
	}

	public function print_styles(): void {
		?>
		<style id="pzl-admin-bar-styles">
		/* ── PizzaLayer Admin Bar ───────────────────────────────────── */

		/* Root icon */
		#wpadminbar #wp-admin-bar-pizzalayer > .ab-item .pzlab-root-icon {
			color: #ff6b35 !important;
			font-size: 18px !important;
			width: 18px !important;
			height: 18px !important;
			top: 2px;
			position: relative;
		}

		/* Dropdown icons */
		#wpadminbar .pzlab-icon {
			font-size: 14px !important;
			width: 14px !important;
			height: 14px !important;
			vertical-align: middle !important;
			margin-right: 5px !important;
			opacity: .7 !important;
			flex-shrink: 0;
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
			border-top: 1px solid rgba(240,246,252,.07) !important;
			margin-top: 2px !important;
		}

		/* CPT rows — flex so the item label and +New sub-links align */
		#wpadminbar .pzlab-cpt-row > .ab-item {
			display: flex !important;
			align-items: center !important;
		}

		/* Add-new items in the "Add New" group */
		#wpadminbar .pzlab-addnew-item > .ab-item {
			display: flex !important;
			align-items: center !important;
			gap: 6px !important;
		}
		.pzlab-addnew-plus {
			font-size: 16px !important;
			font-weight: 700 !important;
			color: #ff6b35 !important;
			width: 18px !important;
			text-align: center !important;
			flex-shrink: 0 !important;
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
