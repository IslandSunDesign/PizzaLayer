=== PizzaLayer ===
Contributors: islandsundesign
Tags: pizza, restaurant, woocommerce, customizer, builder
Requires at least: 6.2
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.1.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

An interactive pizza builder and visualizer for WordPress. Let customers build their perfect pizza with a live layered image preview.

== Description ==

**PizzaLayer** is a fully-featured, interactive pizza customizer for WordPress. Customers select their crust, sauce, cheese, toppings, drizzle, and cut style while watching a live layered pizza image update in real time. Embed the builder anywhere with a simple shortcode or Gutenberg block.

Built and maintained by [Ryan Bishop](https://islandsundesign.com) at [Island Sun Design](https://islandsundesign.com).

= Key Features =

* **Live visual pizza builder** — layered transparent PNG images stack and update as customers make selections
* **7 built-in templates** — choose from Colorbox, Metro, NightPie, Fornaia, PocketPie, Plainlist, and Scaffold
* **Shortcode & Gutenberg block support** — embed anywhere with `[pizza_builder]` or the Pizza Builder block
* **Custom Post Types** — manage Toppings, Crusts, Sauces, Cheeses, Drizzles, Cuts, and Sizes via the WordPress admin
* **Static pizza shortcode** — render a non-interactive layered pizza image with `[pizza_static]`
* **Layer image shortcode** — display a single ingredient layer image with `[pizza_layer]`
* **Layer info shortcode** — display ingredient metadata with `[pizza_layer_info]`
* **REST API** — render pizzas programmatically (opt-in, disabled by default)
* **Layer Image Maker** — generate and upload transparent layer images from inside the admin
* **Layer Builder Wizard** — step-by-step guided workflow for adding new ingredients
* **Settings Wizard** — guided first-run setup walkthrough
* **Admin dark mode** — toggle for the PizzaLayer admin screens
* **Theme-compatible** — CSS custom properties let you match any theme's colour palette and typography
* **Developer-friendly** — action/filter hooks throughout, public PHP and JS APIs, Scaffold starter template for custom builds
* **Translation-ready** — `.pot` file included, Spanish and German translations bundled
* **WooCommerce ready** — pairs with the PizzaLayer Pro extension for full cart, pricing, and order management

= Templates =

**Colorbox** — Bright, playful builder with colorful category tiles, pill tabs, and light dashboard-style surfaces. Great for family-friendly and fast-casual brands.

**Metro** — Clean, modern single-scroll layout. The pizza floats in a centered hero; ingredient sections flow below. Built for fast-casual and artisan brands.

**NightPie** — Modern dark UI with sticky split-screen pizza preview, tabbed sections, fly-to animation, and a "Your Pizza" summary panel.

**Fornaia** — Warm, homestyle template with earthy tones, aged-paper texture, serif typography, and vintage badge accents. Ideal for Neapolitan and wood-fired pizzerias.

**PocketPie** — Compact mobile-first builder with multiple layout modes: Corner Quad, Layer Deck, Slide Drawer, and Stack Panel. Ideal for embedded storefronts and small spaces.

**Plainlist** — Text-first checklist layout with no visual pizza canvas. Accessible, print-friendly, and available in single-scroll or step-by-step wizard modes.

**Scaffold** — A bare-bones developer starter template with fully modular HTML partials and clean hooks. Duplicate any partial and build from there.

= Shortcodes =

**`[pizza_builder]`** — Renders the full interactive builder.

Attributes: `id`, `template`, `max_toppings`, `show_tabs`, `hide_tabs`, `default_crust`, `default_sauce`, `default_cheese`, `pizza_shape`, `pizza_aspect`, `pizza_radius`, `layer_anim`, `layer_anim_speed`, `restrict`

**`[pizza_static]`** — Renders a non-interactive layered pizza image.

Attributes: `crust`, `sauce`, `cheese`, `toppings`, `drizzle`, `cut`, `preset`

Example: `[pizza_static crust="thin-crust" sauce="classic-tomato" cheese="mozzarella" toppings="pepperoni,mushrooms"]`

**`[pizza_layer]`** — Renders a single ingredient layer image.

Attributes: `type`, `slug`, `size`

**`[pizza_layer_info]`** — Renders text metadata about a layer (name, description, price if Pro is active).

= REST API =

The REST API is disabled by default. Enable it under **PizzaLayer → Settings → Advanced**.

`POST /wp-json/pizzalayer/v1/render` — Render a pizza layer stack and return HTML.

`GET /wp-json/pizzalayer/v1/layer-url` — Retrieve the image URL for a given layer type and slug.

`GET /wp-json/pizzalayer/v1/presets` — List available saved pizza presets.

= For Developers =

PizzaLayer exposes a public PHP API for use in themes and other plugins:

    // Render a full pizza stack as HTML
    $html = PizzaLayer\Builder\PizzaBuilder::render_pizza_stack([
        'crust'    => 'thin-crust',
        'sauce'    => 'classic-tomato',
        'cheese'   => 'mozzarella',
        'toppings' => ['pepperoni', 'mushrooms'],
        'drizzle'  => 'hot-honey',
        'cut'      => '8-slices',
    ]);

    // Get a layer image URL
    $url = PizzaLayer\Builder\PizzaBuilder::get_layer_url( 'topping', 'pepperoni' );

**Filters:**

* `pizzalayer_template_dirs` — Register additional template directory paths
* `pizzalayer_query_args_toppings` — Modify the WP_Query args used to fetch toppings
* `pizzalayer_tab_order` — Reorder or remove builder tabs
* `pizzalayer_builder_shortcode_atts` — Filter parsed shortcode attributes before render

**Actions:**

* `pizzalayer_before_builder` — Fires before the builder canvas renders
* `pizzalayer_after_builder` — Fires after the builder canvas renders
* `pizzalayer_builder_action_bar` — Fires inside the builder action bar (used by Pro for the checkout bar)

See the plugin documentation at [pizzalayer.com](https://pizzalayer.com) for a full hook reference.

= Custom Templates =

Duplicate the **Scaffold** template folder, give it a unique `function_prefix` in `pztp-template-info.php`, and register it via the `pizzalayer_template_dirs` filter. The Scaffold template includes detailed comments and modular HTML partials designed for this purpose.

= Pro Version =

**PizzaLayer Pro** extends this plugin with full WooCommerce integration:

* Custom "Pizza" WooCommerce product type
* Per-layer live pricing with 6 engine modes (add-on per layer, flat per size, highest wins, tiered by count, free first N, bundle)
* Add-to-cart AJAX flow with server-side price verification
* Order meta — full ingredient breakdown saved with every order and displayed in admin
* Cart display — size, toppings, base price, and order notes shown in cart and checkout
* Order emails — pizza configuration in WC order emails or as a standalone summary
* Nutrition display — calories and nutritional data per ingredient
* Cart editing — "Edit pizza" link in cart rehydrates the builder with saved configuration
* Order again — redirects to the builder pre-filled from a previous order
* JSON-LD schema markup for pizza products
* Full German and Spanish translations

Learn more at [pizzalayer.com](https://pizzalayer.com).

== Installation ==

1. Upload the `PizzaLayer` folder to the `/wp-content/plugins/` directory, or install via the WordPress Plugins screen.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Go to **PizzaLayer → Setup Guide** for a step-by-step walkthrough.
4. Add ingredient images via **PizzaLayer → Content** for each CPT (Toppings, Crusts, Sauces, Cheeses, Drizzles, Cuts).
5. Choose a template under **PizzaLayer → Template**.
6. Embed the builder using the `[pizza_builder]` shortcode or the **Pizza Builder** Gutenberg block.

== Frequently Asked Questions ==

= What image format should I use for ingredient layers? =

Use transparent PNG files. All layer images are stacked on top of each other, so transparency is required for the layers below to show through. Recommended size: 800×800px or 1200×1200px at a 1:1 aspect ratio.

= Can I use multiple builders on the same page? =

Yes. Each `[pizza_builder]` shortcode generates a unique instance ID. You can place as many builders on a single page as needed.

= How do I match the builder's colours to my theme? =

Go to **PizzaLayer → Settings → Colours**. All colour values are applied as CSS custom properties and cascade through the active template. You can also add custom CSS under **Settings → Advanced**.

= Can I create my own template? =

Yes. Duplicate the **Scaffold** template folder, give it a unique `function_prefix` in `pztp-template-info.php`, and register it via the `pizzalayer_template_dirs` filter. The Scaffold template includes detailed comments and modular HTML partials designed for this purpose.

= Does this plugin work with page builders like Elementor or Divi? =

The `[pizza_builder]` shortcode works anywhere shortcodes are supported. A dedicated Elementor widget is on the roadmap.

= Is WooCommerce required? =

No. PizzaLayer is fully functional as a standalone visualizer and customizer without WooCommerce. The Pro extension adds WooCommerce integration for e-commerce functionality.

= What PHP version is required? =

PHP 7.4 or higher. The plugin is tested on PHP 7.4, 8.0, 8.1, and 8.2.

= Does the REST API expose my ingredient data publicly? =

The REST API is disabled by default. When enabled (under Settings → Advanced), the `/render` and `/layer-url` endpoints are public read-only endpoints that return rendered HTML or image URLs — the same data already visible on the front end. No write endpoints are exposed.

= Where can I get support? =

Visit [pizzalayer.com/support](https://pizzalayer.com/support) or use the WordPress.org support forum.

== Screenshots ==

1. NightPie template — dark split-screen builder with live pizza preview
2. Colorbox template — bright tile-based builder
3. Metro template — modern single-scroll layout
4. Fornaia template — warm artisan style with earthy tones
5. PocketPie template — compact mobile-first layout
6. Admin dashboard — PizzaLayer overview and quick stats
7. Content Hub — manage all ingredient CPTs from one screen
8. Layer Image Maker — generate and upload transparent layer images from the admin
9. Settings — colour palette, typography, and layout controls
10. Setup Guide — step-by-step guided walkthrough

== Changelog ==

= 1.1.6 =
* Remove: Colorbox template removed; Command Center is now the seventh template
* Fix: Command Center template — selection cards no longer clipped at the bottom; panels now scroll freely to full content height
* Update: Template default fallback updated from colorbox to nightpie

= 1.1.3 =
* Fix: Settings Wizard option keys corrected to match actual Settings page — wizard saves now write to the correct database options
* Fix: Settings Wizard removed non-existent options (dark_mode, cx_allow_name, cx_require_name, cx_show_price_live, layout_show_step_numbers, builder_title, confirm_button_text)
* Fix: Settings Wizard a11y_focus_ring field changed from toggle to select to match actual field type (theme default / bold / glow / none)
* Fix: Settings Wizard animation values updated to match plugin values (scale-in, slide-up, flip-in, drop-in, instant)
* Fix: Settings Wizard pizza_shape options corrected (round, square, rectangle, custom)
* Fix: Settings Wizard step-by-step mode and cx_show_start_over added as they are real, working settings
* Improvement: Settings Wizard Topping Rules step simplified — duplicate toppings toggle removed (not a standalone setting in this version)
* Improvement: Settings Wizard Messaging step updated to use real branding_tagline and settings_demonotice option keys
* Fix: Help page — template count corrected to six built-in templates (NightPie, Metro, Colorbox, Fornaia, PocketPie, Plainlist) plus Scaffold
* Fix: Help page — Content Hub and Layer Types reference corrected from 8 to 7 layer types/CPTs
* Fix: Help page — Developer Reference class map updated with all actual classes, correct PizzaLayer\Api namespace, and proper grouping
* Fix: Help page — [pizza_layer_info] shortcode added to Shortcodes section with full attribute table and examples
* Fix: Help page — Gutenberg info box updated to correctly identify which three of the four shortcodes have native blocks

= 1.1.2 =
* Fix: Standardized plugin name to one-word form "PizzaLayer" throughout all user-facing strings, block labels, admin menus, and readme
* Fix: Added GPL license.txt to plugin root
* Fix: Extracted all inline <script> blocks from admin PHP to properly enqueued JS files via wp_enqueue_script and wp_localize_script — resolves WordPress.org submission blocker
* Fix: Replaced per-instance inline <style> in Scaffold, Metro, and Plainlist templates with wp_add_inline_style() calls  
* Fix: Added Requires at least: 6.2 and Tested up to: 6.7 to plugin header
* Fix: Removed artifact includes/{css,js}/ directory from build

= 1.1.0 =
* New: Layer Builder Wizard — step-by-step guided workflow for adding new ingredients with image upload, field population, and instant publish
* New: Settings Wizard — guided first-run configuration walkthrough covering template selection, fractions, colours, and layout
* New: Admin dark mode toggle for all PizzaLayer admin screens
* New: Colorbox template updated to v1.1.0 with improved touch targets and accessibility enhancements
* New: Spanish (es_ES) and German (de_DE) translation files bundled
* New: `[pizza_layer_info]` shortcode for displaying layer metadata inline in content
* New: `pizzalayer_builder_action_bar` action hook for Pro extension checkout bar integration
* New: `pizzalayer_tab_order` filter for reordering or removing builder tabs
* New: `pizzalayer_query_args_toppings` filter for customising topping query arguments
* New: `restrict` shortcode attribute — limit visible ingredients to a comma-separated slug list
* New: REST API `/presets` endpoint listing saved pizza presets
* Improvement: Template loader falls back to first available template rather than hard-coded default when active template is missing
* Improvement: Admin Content Hub consolidated ingredient management with AJAX panel switching
* Improvement: Settings export uses a detached JS-constructed form to avoid nested-form issues
* Improvement: `get_posts()` orderby now uses array syntax for reliable `WP_Post` object returns
* Fix: `ServerSideRender` in Gutenberg block editor returns a static branded preview when called via REST context
* Fix: PHP 7.4 compatibility — replaced all `str_ends_with()` / `str_starts_with()` calls with `substr()` / `strpos() === 0` equivalents

= 1.0.4 =
* Fix: Checkout bar relocated to the very bottom of all 7 template layouts
* Fix: Checkout bar now renders at 100% width regardless of template

= 1.0.3 =
* Security: validate base64-decoded image bytes via `finfo::buffer()` before writing to disk in Layer Image Maker and Layer Image Meta Box upload handlers
* Security: derive file extension from the real MIME type of uploaded bytes rather than the client-supplied filename
* Security: added `upload_files` capability check to Layer Image Meta Box AJAX handler
* Security: added allowlist validation to the `field_key` parameter in Layer Image Meta Box AJAX handler — only known meta keys accepted
* Security: added `sanitize_callback` to the `toppings` argument of the `/render` REST endpoint
* Docs: corrected REST API section of Help page

= 1.0.2 =
* Security: added `current_user_can('manage_options')` capability check to template preview override handler
* Security: strip `</style>` sequences from admin-entered custom CSS before output
* Security: added `escHtml()` helper to settings-page admin JS, applied to all `innerHTML` injections in the layer picker modal
* Security: added `scEscHtml()` helper to Scaffold template JS, applied to layer titles and coverage values in summary panel
* Security: escaped media library attachment URL values before injecting into logo preview `innerHTML`
* Compatibility: replaced `str_ends_with()` calls with `substr()` equivalents for PHP 7.4 compatibility
* Compatibility: replaced `str_starts_with()` call with `strpos() === 0` for PHP 7.4 compatibility

= 1.0.1 =
* Fix: Corrected plugin header text domain declaration
* Fix: Resolved edge-case PHP notice on first activation before any CPT content exists
* Fix: Template loader correctly falls back to first available template when active template directory is missing

= 1.0.0 =
* 7 built-in templates: Colorbox, Metro, NightPie, Fornaia, PocketPie, Plainlist, Scaffold
* Shortcodes: `[pizza_builder]`, `[pizza_static]`, `[pizza_layer]`, `[pizza_layer_info]`
* Gutenberg blocks: Pizza Builder, PizzaLayer Image, Pizza Static
* Custom Post Types: Toppings, Crusts, Sauces, Cheeses, Drizzles, Cuts, Sizes
* REST API endpoints: `/render`, `/layer-url`, `/presets` (opt-in, disabled by default)
* Settings page: Typography, Colours, Spacing, Builder Layout, Customer Experience, Performance, Accessibility, Advanced
* Admin: Dashboard, Setup Guide, Content Hub, Shortcode Generator, Template Chooser, Help
* Layer Image Maker tool — generate and upload layer images from the admin
* WooCommerce-ready hooks for Pro extension integration
* Admin dark mode toggle
* Developer PHP and JS public APIs
* `.pot` translation file included

= 0.9.0 =
* Beta: Full 7-template suite finalized — Colorbox, Metro, NightPie, Fornaia, PocketPie, Plainlist, Scaffold
* Beta: Layer Image Maker tool — generate transparent PNG layer images from the admin without external software
* Beta: Settings page scaffolded with Typography, Colours, Spacing, Builder Layout, Customer Experience, Performance, Accessibility, and Advanced tabs
* Beta: Admin dark mode toggle introduced
* Beta: PHP and JS public APIs documented and stabilized for Pro extension handshake

= 0.8.0 =
* Alpha: PocketPie template — compact mobile-first builder with Corner Quad, Layer Deck, Slide Drawer, and Stack Panel layout modes
* Alpha: Plainlist template — accessible text-first checklist builder with single-scroll and step-by-step wizard modes
* Alpha: Scaffold template — bare-bones developer starter with modular HTML partials and hook points
* Alpha: `pizzalayer_template_dirs` filter added to support external template registration
* Alpha: `pizzalayer_builder_action_bar` action hook added for checkout bar injection point (Pro integration)

= 0.7.0 =
* Alpha: Fornaia (Rustic) template — earthy, homestyle aesthetic with aged-paper texture, serif typography, and vintage badge accents
* Alpha: NightPie template — dark split-screen layout with sticky preview panel, tabbed sections, and fly-to animation
* Alpha: Template folder slug convention established: `rustic/` on disk for Fornaia
* Alpha: `function_exists()` guards added to shared template function names for safe multi-template coexistence

= 0.6.0 =
* Alpha: Colorbox template — bright tile-based builder with pill tabs and category swatches
* Alpha: Metro template — clean single-scroll layout with centered pizza hero
* Alpha: Template info file spec (`pztp-template-info.php`) defined
* Alpha: CSS custom properties system introduced for theme-compatible colour and typography overrides
* Alpha: Layer animation system — CSS transition stack for ingredient selection

= 0.5.0 =
* Alpha: Gutenberg block registered — Pizza Builder, PizzaLayer Image, Pizza Static blocks
* Alpha: `ServerSideRender` integration with static branded preview for REST context
* Alpha: Shortcode system: `[pizza_builder]`, `[pizza_static]`, `[pizza_layer]`
* Alpha: Shortcode attributes defined: `id`, `template`, `max_toppings`, `show_tabs`, `hide_tabs`, `default_*`, `pizza_shape`, `pizza_aspect`, `restrict`
* Alpha: REST API scaffolded: `/render`, `/layer-url` endpoints (disabled by default)

= 0.4.0 =
* Alpha: Custom Post Types registered — Toppings, Crusts, Sauces, Cheeses, Drizzles, Cuts, Sizes
* Alpha: Admin Content Hub introduced — consolidated CPT management with AJAX panel switching
* Alpha: Layer image meta box added to all CPT edit screens
* Alpha: `get_posts()` orderby switched to array syntax for reliable `WP_Post` returns

= 0.3.0 =
* Alpha: Plugin namespace established (`PizzaLayer\Builder`)
* Alpha: Core `PizzaBuilder` class with `render_pizza_stack()` and `get_layer_url()` public API methods
* Alpha: Live layered PNG stacking system — transparent images composited client-side in real time
* Alpha: JS public API (`window.PizzaLayerAPI`) with `getState()`, `setState()`, and event bus

= 0.2.0 =
* Proof of concept: layered image preview system functional with manual ingredient arrays
* Proof of concept: Admin screen scaffolded under PizzaLayer menu
* Proof of concept: Basic CPT registration for Toppings

= 0.1.0 =
* Initial proof of concept — pizza builder rendered from hardcoded layer images
* Plugin file structure established

== Upgrade Notice ==

= 1.1.2 =
Maintenance release. Fixes inline script extraction (WordPress.org compliance), standardizes plugin name, and adds GPL license file. No database changes. Safe to update in place.

= 1.1.0 =
Feature release. Adds the Layer Builder Wizard, Settings Wizard, admin dark mode, and bundled Spanish/German translations. No database changes. Safe to update in place.

= 1.0.4 =
Fix release. Relocates the Pro checkout bar to the bottom of all templates. No database changes. Safe to update in place.

= 1.0.3 =
Security update. Hardens Layer Image Maker upload handling and REST endpoint sanitization. No database changes. Safe to update in place.

= 1.0.2 =
Security update. Adds capability checks, output escaping, and PHP 7.4 compatibility fixes. No database changes. Safe to update in place.

= 1.0.1 =
Fix release. Corrects plugin header text domain and resolves first-activation edge cases. No database changes. Safe to update in place.

== Credits ==

PizzaLayer was created by **Ryan Bishop** of [Island Sun Design](https://islandsundesign.com).
