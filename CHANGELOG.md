# Changelog

All notable changes to Pizza Layer are documented here.  
Format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).  
Versions follow [Semantic Versioning](https://semver.org/).

---

## [1.4.0] – 2026

### Added
- **Site Migration tool** — new `PizzaLayer\Admin\SiteMigration` class plus a Tools-group submenu page at `pizzalayer-migration`. Builds a single JSON export covering every plugin setting, all eight CPTs (toppings, crusts, sauces, cheeses, drizzles, cuts, sizes, presets) with title/slug/content/excerpt/status/menu_order/full meta/term assignments, the `pizzalayer_ingredient_group` taxonomy tree (parent/child resolved by slug), and a layer-image reference per post (URL + filename + alt + caption). Importable on a fresh installation to reconstruct the entire setup.
- **Image-by-URL transport** — layer images are exported as URL references rather than packaged binaries. On import, `media_handle_sideload` pulls each one from its source URL into the destination media library, mirroring the existing pattern used by `LayerImageMaker` and `LayerImageMetaBox`. Keeps export files small (~10 KB for a small store, scales linearly with post count).
- **Create-only-by-slug import semantics** — `get_page_by_path` lookup per post-type/slug pair before insert; same pattern for taxonomy terms via `get_term_by('slug')`. Settings overwrite (matching existing Settings export/import behaviour), but posts and terms never do. Re-importing the same payload produces zero new posts or terms.
- **Two new extension hooks**:
  - `pizzalayer_export_payload` (filter, `$payload`) — Pro and other add-ons contribute data to the export. Convention: contribute under the `pro` key (Pro) or a clearly namespaced top-level key (other add-ons).
  - `pizzalayer_import_payload` (action, `$payload, $results`) — fires after the free-plugin import sections have run, regardless of whether a `pro` section was present. Add-ons consume their own slice of the payload here.
- **Dashboard quick-nav entry** — Site Migration card added to the AdminHome quicknav row with the migrate dashicon.
- **Help → Site Migration section** — full how-to plus developer extension example showing both filter and action usage.
- `PizzaLayer\Admin\Settings::get_option_keys()` — public static accessor for the canonical option-key list. Existing internal `private const OPTIONS` is unchanged; the accessor is a thin read-only wrapper used by `SiteMigration` and available to extensions that need to enumerate plugin settings.

### Schema
- Export schema is versioned: `{"schema": "pizzalayer-site-export", "version": 1, ...}`. Imports validate both fields and reject mismatches with a clear error. Future schema bumps will be backward-compatible at the importer level.
- WordPress-internal meta keys (`_edit_lock`, `_edit_last`, `_wp_old_*`, `_wp_trash_meta_*`) and the resolvable `_pizzalayer_layer_image_id` are stripped from export. The image ID is re-derived from the sideloaded attachment on import.

### Docs
- Help → Developer Reference: new rows in the action-hooks table for `pizzalayer_import_payload` and in the filter-hooks table for `pizzalayer_export_payload`.
- Help → Developer Reference class map: added `Admin\SiteMigration`.
- Help → `pizzalayer_cpt_registered` description and class-map entry corrected from "7 CPTs" to "8 CPTs (7 layer types + Presets)" — the Presets CPT was always registered, just undercounted in the docs.

### Limits
- Import file size capped at 25 MB (vs. 1 MB for the legacy settings-only import). A site with thousands of posts and verbose meta per post can comfortably fit; the cap mainly exists to reject malicious oversized uploads.
- Image sideload uses `download_url` with a 30-second timeout per image. Very large media libraries imported across slow networks may need to be re-run; create-only-by-slug guarantees the second pass picks up where the first left off (posts already created are skipped, but a missing layer image is independently re-attempted on a fresh-create flow only — flagged as a follow-up if real users hit this).

---

## [1.3.0] – 2026

### Added
- Command Center, NightPie, and Colorbox templates now expose configurable settings on the Templates page. Previously these three templates rendered an empty "no customizable settings" panel; they now ship with field definitions in `pztp-template-options.php` plus settings-driven CSS-variable injection in `pztp-template-custom.php`, matching the established Metro/Plainlist pattern.
- Command Center settings: 8 colors (accent, accent hover, completed-step, page bg, surface, raised surface, text, muted text), font family, base font size, corner radius, and three behavioural toggles (show step numbers, show summary sidebar, accent glow).
- NightPie settings: 6 colors, font family, base font size, corner radius, sticky-preview toggle, accent-glow toggle.
- Colorbox settings: 5 base colors, 7 individually configurable per-category tile colors (Sizes / Crust / Sauce / Cheese / Toppings / Drizzle / Cuts), font family, base font size, corner radius, and a master "Colorful Category Tiles" toggle that collapses tiles to a neutral surface when off.

### Fixed
- Templates page — stray PHP single-quote escape inside literal HTML output (`template\'s`) replaced with `&rsquo;`. Was rendering a visible backslash.

### Docs
- Help → Quickstart step 3: Colorbox was previously omitted from the list of seven user-facing templates. Added.
- Help → Quickstart step 2: now mentions the Settings Wizard as the recommended first-run path with a primary-button entry point.
- Help → Managing Content: now leads with a Layer Builder Wizard fast-path callout above the manual flow.
- Help → Template System lead: removed duplicate Scaffold mention; added Colorbox.
- Help → Template System CSS variable reference: rewrote the NightPie token block (previous values such as `--np-accent: #ff6b35`, `--np-bg: #1a1e23`, and a non-existent `--np-accent-hover` did not match the actual `template.css`); added a parallel block for Command Center; new "Per-template settings" intro paragraph pointing users at the Templates page UI before reaching for raw CSS.
- Help → Developer Reference class map: added `Core\Loader`, `Core\Activator`, `Core\Deactivator`, and `Admin\Customizer`.

---

## [1.2.1] – 2026

### Fixed
- Layer Builder Wizard now saves correctly — nonce action mismatch between `AssetManager` (`pizzalayer_layer_builder`) and `LayerBuilderWizard::ajax_save_layer` (`pizzalayer_wizard_save`) corrected; both ends now use `pizzalayer_wizard_save`
- Layer Builder Wizard JS no longer ships with literal `<?php …?>` strings — all UI labels and alerts are now delivered via `wp_localize_script` (`pizzalayerLBW.i18n`) with English fallbacks if a key is missing
- Layer Image Meta Box JS rewritten to remove leftover `<?php …?>` blocks and a stray `</script>` literal; element discovery now happens via scoped `querySelector` calls inside the wrap, so multiple meta boxes on the same screen no longer collide
- Settings import now correctly preserves Custom CSS and Custom JS — these capability-gated raw fields are no longer passed through `wp_kses_post` on import (matching the live save path)
- Settings import hardened: requires a real HTTP upload (`is_uploaded_file`), checks `UPLOAD_ERR_OK`, caps file size at 1 MB, requires a `.json` extension
- Template activation in Templates page now validates the posted slug against `TemplateLoader::get_available_templates()` before writing the option; invalid slugs no longer silently break the front end
- Layer Builder Wizard `ajax_save_layer` now verifies the supplied `image_id` is actually an attachment with an `image/*` MIME type before associating it with the new layer post
- Help page “Shortcodes” section icon — the malformed `</>` glyph (which some browsers stripped as an empty close tag) is now `⌨`

### Security
- Template settings save (both `TemplateChoice::save_template_settings` and `Settings::save_settings` template-options branch) now namespace-guards keys: only options whose key contains `_setting_` may be written, preventing a malicious template-options.php from overwriting core options like `siteurl`

---



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
