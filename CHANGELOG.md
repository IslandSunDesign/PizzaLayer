# Changelog

All notable changes to Pizza Layer are documented here.  
Format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).  
Versions follow [Semantic Versioning](https://semver.org/).

---

## [1.1.3] – 2026

### Fixed
- Settings Wizard option keys corrected throughout to match actual Settings page keys — saves now write to the correct database options (e.g. `pizzalayer_setting_topping_maxtoppings`, `pizzalayer_setting_layout_hide_empty`, `pizzalayer_setting_cx_special_instructions`, `pizzalayer_setting_perf_lazy_load`, `pizzalayer_setting_typo_font_family`)
- Settings Wizard removed references to non-existent options: `dark_mode`, `cx_allow_name`, `cx_require_name`, `cx_show_price_live`, `layout_show_step_numbers`, `builder_title`, `confirm_button_text`, `builder_intro_text`, `builder_help_text`
- Settings Wizard `a11y_focus_ring` field corrected from toggle to select (options: theme default / bold / glow / none) matching the actual Settings page control
- Settings Wizard animation values corrected to match plugin values: `scale-in`, `slide-up`, `flip-in`, `drop-in`, `instant`
- Settings Wizard `pizza_shape` options corrected to `round`, `square`, `rectangle`, `custom`
- Settings Wizard Messaging step now uses real option keys: `pizzalayer_setting_branding_tagline` and `pizzalayer_setting_settings_demonotice`
- Help page template count corrected to six built-in templates (NightPie, Metro, Colorbox, Fornaia, PocketPie, Plainlist) plus Scaffold developer starter
- Help page Content Hub and Layer Types reference corrected from 8 to 7 layer types/CPTs
- Help page Developer Reference class map rebuilt with all actual classes, correct `PizzaLayer\Api` namespace, and organised by category
- Help page `[pizza_layer_info]` shortcode added to Shortcodes section with full attribute table and copy-paste examples
- Help page Gutenberg info box updated to correctly state which three of the four shortcodes have native block equivalents

### Improved
- Settings Wizard Topping Rules step simplified to the one real setting (`max toppings`) — duplicate toppings toggle removed as it is not a standalone option in this version
- Settings Wizard now exposes `layout_step_by_step` and `cx_show_start_over` which are real, working settings previously missing from the wizard
- Settings Wizard Customer Experience step now shows the `cx_show_start_over` toggle alongside the other UX controls

---

## [1.1.1] – 2026

### Fixed
- Extracted all inline `<script>` blocks from admin PHP to properly enqueued JS files via `wp_enqueue_script` and `wp_localize_script` — resolves WordPress.org submission blocker
- Replaced per-instance inline `<style>` in Scaffold, Metro, and Plainlist templates with `wp_add_inline_style()` calls
- Added `Requires at least: 6.2` and `Tested up to: 6.7` to plugin header
- Removed artifact `includes/{css,js}/` directory from packaged build
- Fixed CHANGELOG.md release year timestamps

---

## [1.1.0] – 2026

### Added
- **Layer Builder Wizard** — step-by-step guided workflow for adding new ingredients with image upload, field population, and instant publish
- **Settings Wizard** — guided first-run configuration walkthrough covering template selection, fractions, colours, and layout
- **Admin dark mode** toggle for all Pizza Layer admin screens
- Spanish (`es_ES`) and German (`de_DE`) translation files bundled
- `[pizza_layer_info]` shortcode for displaying layer metadata inline in content
- `pizzalayer_builder_action_bar` action hook for Pro extension checkout bar integration
- `pizzalayer_tab_order` filter for reordering or removing builder tabs
- `pizzalayer_query_args_toppings` filter for customising topping query arguments
- `restrict` shortcode attribute — limit visible ingredients to a comma-separated slug list
- REST API `/presets` endpoint listing saved pizza presets

### Changed
- Colorbox template updated to v1.1.0 with improved touch targets and accessibility enhancements
- Template loader now falls back to first available template rather than hard-coded default when active template is missing
- Admin Content Hub consolidated ingredient management with AJAX panel switching
- Settings export uses a detached JS-constructed form to avoid nested-form issues
- `get_posts()` orderby uses array syntax for reliable `WP_Post` object returns

### Fixed
- `ServerSideRender` in Gutenberg block editor now returns a static branded preview when called via REST context (avoids missing template globals)
- PHP 7.4 compatibility — replaced all `str_ends_with()` / `str_starts_with()` calls with `substr()` / `strpos() === 0` equivalents

---

## [1.0.4] – 2025

### Fixed
- Checkout bar (Add to Cart row) relocated to the very bottom of all 7 template layouts
- Checkout bar now renders at 100% width regardless of template

---

## [1.0.3] – 2025

### Security
- Validate base64-decoded image bytes via `finfo::buffer()` before writing to disk in Layer Image Maker and Layer Image Meta Box upload handlers — rejects payloads that are not a recognised image type (PNG, JPEG, GIF, WebP)
- Derive file extension from the real MIME type of uploaded bytes rather than the client-supplied filename; pass the verified MIME type to `media_handle_sideload`
- Added `upload_files` capability check to Layer Image Meta Box AJAX handler
- Added allowlist validation to the `field_key` parameter in Layer Image Meta Box AJAX handler — only known layer image meta keys are accepted, preventing arbitrary meta key writes
- Added `sanitize_callback` to the `toppings` argument of the `/render` REST endpoint

### Docs
- Corrected REST API section of Help page — both endpoints are read-only and public; removed false "write endpoints require nonce" statement
- Corrected CPT count in Help page source reference

---

## [1.0.2] – 2025

### Security
- Added `current_user_can('manage_options')` capability check to template preview override handler (paired with existing nonce verification)
- Strip `</style>` sequences from admin-entered custom CSS before output to prevent style-block breakout
- Added `escHtml()` helper to settings-page admin JS; applied to all values injected via `innerHTML` in the layer picker modal and trigger button
- Added `scEscHtml()` helper to Scaffold template JS; applied to layer titles and coverage values in summary panel `innerHTML` construction
- Escaped media library attachment URL values before injecting into logo preview `innerHTML`

### Compatibility
- Replaced `str_ends_with()` calls in `LayerImageMaker.php` and `LayerImageMetaBox.php` with `substr()` equivalents for PHP 7.4
- Replaced `str_starts_with()` call in `TemplateLoader.php` with `strpos() === 0` for PHP 7.4

---

## [1.0.1] – 2025

Initial public release — see 1.0.0 for full feature list.

---

## [1.0.0] – 2025

### Added
- 7 built-in templates: Colorbox, Metro, NightPie, Fornaia, PocketPie, Plainlist, Scaffold
- Shortcodes: `[pizza_builder]`, `[pizza_static]`, `[pizza_layer]`, `[pizza_layer_info]`
- Gutenberg blocks: Pizza Builder, Pizza Layer Image, Pizza Static
- Custom Post Types: Toppings, Crusts, Sauces, Cheeses, Drizzles, Cuts, Sizes
- REST API endpoints: `/render`, `/layer-url`, `/presets` (opt-in, disabled by default)
- Full settings page: Typography, Colours, Spacing, Builder Layout, Customer Experience, Performance, Accessibility, Advanced
- Admin pages: Dashboard, Setup Guide, Content Hub, Shortcode Generator, Template Chooser, Help
- Layer Image Maker tool — generate and upload transparent layer PNG images from the admin
- WooCommerce-ready hooks for Pro extension integration
- Admin dark mode toggle
- Developer PHP and JS public APIs
- `.pot` translation file
