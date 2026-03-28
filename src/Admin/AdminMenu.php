<?php
namespace PizzaLayer\Admin;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * PizzaLayer Admin Menu
 *
 * Sidebar structure:
 *   PizzaLayer
 *   ├─ Dashboard
 *   ├─ ── CONTENT ──          (non-clickable group header)
 *   ├─ Toppings               → ContentHub?pl_cpt=toppings
 *   ├─ Crusts                 → ContentHub?pl_cpt=crusts
 *   ├─ Sauces                 → ContentHub?pl_cpt=sauces
 *   ├─ Cheeses                → ContentHub?pl_cpt=cheeses
 *   ├─ Drizzles               → ContentHub?pl_cpt=drizzles
 *   ├─ Cuts                   → ContentHub?pl_cpt=cuts
 *   ├─ Sizes                  → ContentHub?pl_cpt=sizes
 *   ├─ ── TOOLS ──            (non-clickable group header)
 *   ├─ Setup Guide
 *   ├─ Shortcode Generator
 *   ├─ Template
 *   ├─ Settings
 *   └─ Help
 *
 * CPT items link directly into the ContentHub with the correct tab
 * pre-selected — no page navigation, no extra submenu page registered.
 * WordPress supports external URL slugs (http/https) in add_submenu_page;
 * we use that to point straight at the hub with ?pl_cpt= query param.
 *
 * Group headers are registered as submenu pages with a blank callback
 * and styled as non-interactive via inline CSS + a global class.
 */
class AdminMenu {

	/** CPT definitions — slug → display meta */
	private const CPTS = [
		'toppings' => [ 'label' => 'Toppings', 'singular' => 'Topping',  'icon' => '🍕' ],
		'crusts'   => [ 'label' => 'Crusts',   'singular' => 'Crust',    'icon' => '⬤'  ],
		'sauces'   => [ 'label' => 'Sauces',   'singular' => 'Sauce',    'icon' => '🥫' ],
		'cheeses'  => [ 'label' => 'Cheeses',  'singular' => 'Cheese',   'icon' => '🧀' ],
		'drizzles' => [ 'label' => 'Drizzles', 'singular' => 'Drizzle',  'icon' => '💧' ],
		'cuts'     => [ 'label' => 'Cuts',     'singular' => 'Cut',      'icon' => '✂'  ],
		'sizes'    => [ 'label' => 'Sizes',    'singular' => 'Size',     'icon' => '📏' ],

	];

	private function get_icon(): string {
		$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">'
			. '<path fill="black" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z'
			. 'M11.5 3.07c.17-.01.33-.01.5-.01 3.77 0 7.04 2.1 8.74 5.2L11.5 12.37V3.07z'
			. 'M4 12c0-3.86 2.42-7.17 5.86-8.48L4.48 18.02C4.17 16.1 4 14.06 4 12z'
			. 'M12 21c-2.02 0-3.9-.59-5.48-1.6l1.52-2.64L12 21z'
			. 'M10.5 16.5l-1.5 2.6C7.41 18.08 6.08 16.41 5.5 14.5l5-8.66v10.66z"/>'
			. '</svg>';
		return 'data:image/svg+xml;base64,' . base64_encode( $svg );
	}

	public function register(): void {

		// ── Top-level menu ───────────────────────────────────────────────
		add_menu_page(
			__( 'PizzaLayer', 'pizzalayer' ),
			__( 'PizzaLayer', 'pizzalayer' ),
			'manage_options',
			'pizzalayer',
			[ $this, 'render_home' ],
			$this->get_icon(),
			30
		);

		// Dashboard — same slug as parent makes it the landing page
		add_submenu_page( 'pizzalayer', __( 'Dashboard', 'pizzalayer' ), __( 'Dashboard', 'pizzalayer' ), 'manage_options', 'pizzalayer', [ $this, 'render_home' ] );

		// ── CONTENT group header (non-clickable separator) ───────────────
		// Registered as a submenu with a unique slug; styled to be non-interactive via CSS.
		add_submenu_page( 'pizzalayer', '', '<span class="pzl-menu-group-header">' . esc_html__( 'Content', 'pizzalayer' ) . '</span>', 'manage_options', 'pizzalayer-group-content', '__return_null' );

		// ── CPT items — each links directly into the ContentHub ──────────
		// WordPress accepts full http URLs as menu slugs since WP 3.0.
		$hub = admin_url( 'admin.php?page=pizzalayer-content' );

		foreach ( self::CPTS as $slug => $meta ) {
			$url   = esc_url( add_query_arg( 'pl_cpt', $slug, $hub ) );
			$label = '<span class="pzl-cpt-item">'
			       . '<span class="pzl-cpt-icon">' . $meta['icon'] . '</span>'
			       . '<span class="pzl-cpt-label">' . esc_html( $meta['label'] ) . '</span>'
			       . '</span>';

			// Using the full URL as the $menu_slug — WP renders it as-is in <a href>
			add_submenu_page( 'pizzalayer', $meta['label'], $label, 'manage_options', $url, null );
		}

		// ── TOOLS group header ───────────────────────────────────────────
		add_submenu_page( 'pizzalayer', '', '<span class="pzl-menu-group-header">' . esc_html__( 'Tools', 'pizzalayer' ) . '</span>', 'manage_options', 'pizzalayer-group-tools', '__return_null' );

		// ── Tool pages ───────────────────────────────────────────────────
		add_submenu_page( 'pizzalayer', __( 'Layer Image Maker',   'pizzalayer' ), __( 'Layer Image Maker',   'pizzalayer' ), 'manage_options', 'pizzalayer-layer-maker',[ $this, 'render_layer_maker'] );
		add_submenu_page( 'pizzalayer', __( 'Setup Guide',         'pizzalayer' ), __( 'Setup Guide',         'pizzalayer' ), 'manage_options', 'pizzalayer-setup',      [ $this, 'render_setup'      ] );
		add_submenu_page( 'pizzalayer', __( 'Shortcode Generator', 'pizzalayer' ), __( 'Shortcode Generator', 'pizzalayer' ), 'manage_options', 'pizzalayer-shortcodes', [ $this, 'render_shortcodes' ] );
		add_submenu_page( 'pizzalayer', __( 'Template',            'pizzalayer' ), __( 'Template',            'pizzalayer' ), 'manage_options', 'pizzalayer-template',   [ $this, 'render_template'   ] );
		add_submenu_page( 'pizzalayer', __( 'Settings',            'pizzalayer' ), __( 'Settings',            'pizzalayer' ), 'manage_options', 'pizzalayer-settings',   [ $this, 'render_settings'   ] );
		add_submenu_page( 'pizzalayer', __( 'Settings Wizard',     'pizzalayer' ), __( '✦ Settings Wizard',   'pizzalayer' ), 'manage_options', 'pizzalayer-wizard',     [ $this, 'render_wizard'     ] );
		add_submenu_page( 'pizzalayer', __( 'Help',                'pizzalayer' ), __( 'Help',                'pizzalayer' ), 'manage_options', 'pizzalayer-help',       [ $this, 'render_help'       ] );

		// Register Content Hub page (still needs to exist as a real page)
		add_submenu_page( 'pizzalayer', __( 'Content Hub', 'pizzalayer' ), '', 'manage_options', 'pizzalayer-content', [ $this, 'render_content' ] );

		// Enqueue sidebar CSS
		add_action( 'admin_head', [ $this, 'render_menu_styles' ] );

		// Dark mode: add body class + enqueue CSS when setting is 'dark'
		add_filter( 'admin_body_class', [ $this, 'maybe_add_dark_body_class' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_dark_css' ] );
	}

	/** Append pzl-admin-dark to <body> when the dark mode option is set. */
	public function maybe_add_dark_body_class( string $classes ): string {
		$mode = (string) get_option( 'pizzalayer_setting_dark_mode', 'auto' );
		if ( $mode === 'dark' ) {
			$classes .= ' pzl-admin-dark';
		} elseif ( $mode === 'auto' ) {
			// We can't detect OS preference server-side; add a JS-toggled class instead
			$classes .= ' pzl-admin-dark-auto';
		}
		return $classes;
	}

	/** Enqueue dark mode stylesheet on all PizzaLayer admin pages. */
	public function enqueue_admin_dark_css(): void {
		$screen = get_current_screen();
		if ( ! $screen ) { return; }
		// Only on our pages
		if ( strpos( $screen->id, 'pizzalayer' ) === false ) { return; }
		$mode = (string) get_option( 'pizzalayer_setting_dark_mode', 'auto' );
		if ( $mode === 'dark' ) {
			wp_enqueue_style(
				'pizzalayer-admin-dark',
				PIZZALAYER_PLUGIN_URL . 'assets/css/admin-dark.css',
				[],
				PIZZALAYER_VERSION
			);
		} elseif ( $mode === 'auto' ) {
			// Enqueue with a prefers-color-scheme media query via inline override
			wp_enqueue_style(
				'pizzalayer-admin-dark',
				PIZZALAYER_PLUGIN_URL . 'assets/css/admin-dark.css',
				[],
				PIZZALAYER_VERSION,
				'(prefers-color-scheme: dark)'
			);
			// Also add the body class swap so our CSS selectors fire
			add_action( 'admin_head', function() {
				echo '<script>if(window.matchMedia&&window.matchMedia("(prefers-color-scheme: dark)").matches){document.body.classList.add("pzl-admin-dark");}</script>' . "\n";
			} );
		}
	}

	public function render_menu_styles(): void {
		$hub_base = admin_url( 'admin.php?page=pizzalayer-content' );
		// Detect which CPT is currently active for sidebar highlighting
		$active_cpt = '';
		if ( isset( $_GET['page'] ) && $_GET['page'] === 'pizzalayer-content' && isset( $_GET['pl_cpt'] ) ) {
			$active_cpt = sanitize_key( $_GET['pl_cpt'] );
		}
		?>
		<style>
		/* ── PizzaLayer Sidebar Menu ─────────────────────────────── */

		/* Group headers — non-interactive separators */
		#adminmenu .pzl-menu-group-header {
			display: block;
			padding: 12px 0 3px 8px;
			font-size: 10px;
			font-weight: 700;
			letter-spacing: .09em;
			text-transform: uppercase;
			color: rgba(240,246,252,.35);
			pointer-events: none;
			cursor: default;
			line-height: 1;
			border-top: 1px solid rgba(255,255,255,.06);
			margin-top: 2px;
		}
		/* Make the group header row itself non-hoverable */
		#adminmenu li:has(.pzl-menu-group-header) > a {
			pointer-events: none !important;
			cursor: default !important;
			padding: 0 !important;
		}
		#adminmenu li:has(.pzl-menu-group-header) > a:hover,
		#adminmenu li:has(.pzl-menu-group-header) > a:focus {
			background: transparent !important;
			color: inherit !important;
			box-shadow: none !important;
		}

		/* Top-level PizzaLayer menu item — flex row so icon + label sit left-aligned */
		#adminmenu #toplevel_page_pizzalayer > a.menu-top {
			display: flex !important;
			align-items: center !important;
			text-align: left !important;
		}
		#adminmenu #toplevel_page_pizzalayer > a .wp-menu-image {
			flex-shrink: 0;
			width: 36px !important;
			display: flex !important;
			align-items: center !important;
			justify-content: center !important;
			float: none !important;
		}
		#adminmenu #toplevel_page_pizzalayer > a .wp-menu-name {
			flex: 1 1 auto;
			text-align: left !important;
			display: block !important;
			padding-left: 0 !important;
		}
		#adminmenu li:has(.pzl-cpt-item) > a {
			display: flex !important;
			align-items: center !important;
			padding-right: 6px !important;
		}

		/* CPT item row — sits inside the flex <a>, fills remaining space */
		#adminmenu .pzl-cpt-item {
			display: contents; /* children become direct flex children of the <a> */
		}
		#adminmenu .pzl-cpt-icon {
			width: 16px;
			text-align: center;
			font-size: 12px;
			opacity: .7;
			flex-shrink: 0;
			margin-right: 2px;
		}
		/* The label text — takes up remaining space, pushing + to the right */
		#adminmenu .pzl-cpt-label {
			flex: 1 1 auto;
			min-width: 0;
			overflow: hidden;
			text-overflow: ellipsis;
			white-space: nowrap;
		}

		/* + Add New pill — pinned to the right edge */
		#adminmenu .pzl-cpt-addnew {
			flex-shrink: 0;
			display: inline-block;
			font-size: 11px;
			font-weight: 700;
			line-height: 17px;
			padding: 0 5px;
			border-radius: 3px;
			background: rgba(240,246,252,.1);
			color: rgba(240,246,252,.5) !important;
			text-decoration: none !important;
			opacity: 0;
			transition: opacity .15s, background .15s, color .15s;
			pointer-events: auto;
		}
		#adminmenu li:hover .pzl-cpt-addnew,
		#adminmenu li:focus-within .pzl-cpt-addnew {
			opacity: 1;
		}
		#adminmenu .pzl-cpt-addnew:hover {
			background: #ff6b35 !important;
			color: #fff !important;
		}

		/* Hide the empty-label Content Hub from sidebar (it's only a real page) */
		#adminmenu a[href="admin.php?page=pizzalayer-content"]:not([href*="pl_cpt"]) {
			display: none !important;
		}

		/* Active CPT item highlight */
		<?php if ( $active_cpt ) : ?>
		#adminmenu a[href*="pl_cpt=<?php echo esc_js( $active_cpt ); ?>"] {
			color: #ff8c42 !important;
			font-weight: 600;
		}
		#adminmenu a[href*="pl_cpt=<?php echo esc_js( $active_cpt ); ?>"]:before {
			border-left-color: #ff6b35 !important;
		}
		<?php endif; ?>
		</style>
		<?php
	}

	// ── Page renderers ────────────────────────────────────────────────
	public function render_layer_maker():   void { ( new LayerImageMaker() )->render(); }
	public function render_home():       void { ( new AdminHome() )->render(); }
	public function render_content():    void { ( new ContentHub() )->render(); }
	public function render_setup():      void { ( new SetupGuide() )->render(); }
	public function render_shortcodes(): void { ( new ShortcodeGenerator() )->render(); }
	public function render_template():   void { ( new TemplateChoice() )->render(); }
	public function render_settings():   void { ( new Settings() )->render(); }
	public function render_wizard():     void { ( new SettingsWizard() )->render(); }
	public function render_help():       void { ( new Help() )->render(); }
}
