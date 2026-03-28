=== Pizza Layer ===
Contributors: islandsundesign
Tags: pizza, restaurant, food, customizer, builder, woocommerce, interactive, menu
Requires at least: 6.2
Tested up to: 6.7
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

An interactive pizza builder and visualizer for WordPress. Let customers build their perfect pizza with a live layered image preview.

== Description ==

**Pizza Layer** is a fully-featured, interactive pizza customizer for WordPress. Customers select their crust, sauce, cheese, toppings, drizzle, and cut style while watching a live layered pizza image update in real time. Embed the builder anywhere with a simple shortcode or Gutenberg block.

Built and maintained by [Ryan Bishop](https://islandsundesign.com) at [Island Sun Design](https://islandsundesign.com).

= Key Features =

* **Live visual pizza builder** — layered transparent PNG images stack and update as customers make selections
* **7 built-in templates** — choose from Colorbox, Metro, NightPie, Rustic, PocketPie, Plainlist, and Scaffold
* **Shortcode & Gutenberg block support** — embed anywhere with `[pizza_builder]` or the Pizza Builder block
* **Custom Post Types** — manage Toppings, Crusts, Sauces, Cheeses, Drizzles, Cuts, and Sizes via the WordPress admin
* **Static pizza shortcode** — render a non-interactive layered pizza image with `[pizza_static]`
* **Layer image shortcode** — display a single ingredient layer image with `[pizza_layer]`
* **REST API** — render pizzas programmatically via `/wp-json/pizzalayer/v1/render`
* **Theme-compatible** — CSS custom properties let you match any theme's colour palette and typography
* **Dark mode support** — opt-in dark mode toggle via settings
* **Developer-friendly** — action/filter hooks throughout, public PHP and JS APIs, Scaffold starter template for custom builds
* **WooCommerce ready** — pairs with the Pizza Layer Pro extension for full cart, pricing, and order management

= Templates =

**Colorbox** — Bright, playful builder with colorful category tiles, pill tabs, and light dashboard-style surfaces. Great for family-friendly and fast-casual brands.

**Metro** — Clean, modern single-scroll layout. The pizza floats in a centered hero; ingredient sections flow below. Built for fast-casual and artisan brands.

**NightPie** — Modern dark UI with sticky split-screen pizza preview, tabbed sections, fly-to animation, and a "Your Pizza" summary panel.

**Rustic** — Warm, homestyle template with earthy tones, serif typography, and vintage accents. Ideal for Neapolitan and wood-fired pizzerias.

**PocketPie** — Compact mobile-first builder with multiple layout modes: Corner Quad, Layer Deck, Slide Drawer, and Stack Panel. Ideal for embedded storefronts and small spaces.

**Plainlist** — Text-first checklist layout with no visual pizza canvas. Accessible, print-friendly, and available in single-scroll or step-by-step wizard modes.

**Scaffold** — A bare-bones developer starter template with fully modular HTML partials and clean hooks. Duplicate any partial and build from there.

= Shortcodes =

**`[pizza_builder]`** — Renders the full interactive builder.

Attributes: `id`, `template`, `max_toppings`, `show_tabs`, `hide_tabs`, `default_crust`, `default_sauce`, `default_cheese`, `pizza_shape`, `pizza_aspect`, `pizza_radius`, `layer_anim`, `layer_anim_speed`

**`[pizza_static]`** — Renders a non-interactive layered pizza image.

Attributes: `crust`, `sauce`, `cheese`, `toppings`, `drizzle`, `cut`

Example: `[pizza_static crust="thin-crust" sauce="classic-tomato" cheese="mozzarella" toppings="pepperoni,mushrooms"]`

**`[pizza_layer]`** — Renders a single ingredient layer image.

Attributes: `type`, `slug`, `size`

**`[pizza_layer_info]`** — Renders text metadata about a layer (name, description, price if Pro is active).

= REST API =

`POST /wp-json/pizzalayer/v1/render` — Render a pizza layer stack and return HTML.

`GET /wp-json/pizzalayer/v1/layer-url` — Retrieve the image URL for a given layer type and slug.

`GET /wp-json/pizzalayer/v1/presets` — List available saved pizza presets.

= For Developers =

Pizza Layer exposes a public PHP API for use in themes and other plugins:

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

Action and filter hooks are available throughout the builder render pipeline. See the plugin documentation at [pizzalayer.com](https://pizzalayer.com) for a full hook reference.

= Pro Version =

**Pizza Layer Pro** extends this plugin with full WooCommerce integration:

* Per-layer live pricing
* Add-to-cart from the builder
* Custom "Pizza" WooCommerce product type
* Order meta — full ingredient breakdown saved with every order
* Product configurator admin meta box

Learn more at [pizzalayer.com](https://pizzalayer.com).

== Installation ==

1. Upload the `PizzaLayer` folder to the `/wp-content/plugins/` directory, or install via the WordPress Plugins screen.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Go to **Pizza Layer → Setup Guide** for a step-by-step walkthrough.
4. Add ingredient images via **Pizza Layer → Content** for each CPT (Toppings, Crusts, Sauces, Cheeses, Drizzles, Cuts).
5. Choose a template under **Pizza Layer → Template**.
6. Embed the builder using the `[pizza_builder]` shortcode or the **Pizza Builder** Gutenberg block.

== Frequently Asked Questions ==

= What image format should I use for ingredient layers? =

Use transparent PNG files. All layer images are stacked on top of each other, so transparency is required for the layers below to show through. Recommended size: 800×800px or 1200×1200px at 1:1 aspect ratio.

= Can I use multiple builders on the same page? =

Yes. Each `[pizza_builder]` shortcode generates a unique instance ID. You can place as many builders on a single page as needed.

= How do I match the builder's colours to my theme? =

Go to **Pizza Layer → Settings → Colours**. All colour values are applied as CSS custom properties and cascade through the active template. You can also add custom CSS under **Settings → Advanced**.

= Can I create my own template? =

Yes. Duplicate the **Scaffold** template folder, give it a new name and `function_prefix` in `pztp-template-info.php`, and register it by placing it in your theme or via the `pizzalayer_template_dirs` filter. The Scaffold template includes detailed comments and modular HTML partials designed for this purpose.

= Does this plugin work with page builders like Elementor or Divi? =

The `[pizza_builder]` shortcode works anywhere shortcodes are supported. A dedicated Elementor widget is on the roadmap.

= Is WooCommerce required? =

No. Pizza Layer is fully functional as a standalone visualizer and customizer without WooCommerce. The Pro extension adds WooCommerce integration for e-commerce functionality.

= Where can I get support? =

Visit [pizzalayer.com/support](https://pizzalayer.com/support) or use the WordPress.org support forum.

== Screenshots ==

1. NightPie template — dark split-screen builder with live pizza preview
2. Colorbox template — bright tile-based builder
3. Metro template — modern single-scroll layout
4. Rustic template — warm artisan style
5. PocketPie template — compact mobile-first layout
6. Admin dashboard — Pizza Layer overview and quick stats
7. Content Hub — manage all ingredient CPTs from one screen
8. Settings — colour palette, typography, and layout controls

== Changelog ==

= 1.0.0 =
* Initial public release
* 7 built-in templates: Colorbox, Metro, NightPie, Rustic, PocketPie, Plainlist, Scaffold
* Shortcodes: `[pizza_builder]`, `[pizza_static]`, `[pizza_layer]`, `[pizza_layer_info]`
* Gutenberg blocks: Pizza Builder, Pizza Layer, Pizza Static
* Custom Post Types: Toppings, Crusts, Sauces, Cheeses, Drizzles, Cuts, Sizes
* REST API endpoints: `/render`, `/layer-url`, `/presets`
* Full settings page: Typography, Colours, Spacing, Builder Layout, Customer Experience, Performance, Accessibility, Advanced
* Admin: Dashboard, Setup Guide, Content Hub, Shortcode Generator, Template Chooser, Help
* Layer Image Maker tool — generate and upload layer images from the admin
* WooCommerce-ready hooks for Pro extension integration
* Dark mode toggle
* Developer PHP and JS public APIs
* `.pot` translation file included

== Upgrade Notice ==

= 1.0.0 =
Initial release. No upgrade steps required.

== Credits ==

Pizza Layer was created by **Ryan Bishop** of [Island Sun Design](https://islandsundesign.com).
