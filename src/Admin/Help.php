<?php
namespace PizzaLayer\Admin;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * PizzaLayer Help & Reference Page
 *
 * Sections:
 *  1. Quickstart — 5-step walkthrough + first-launch checklist
 *  2. Managing Content — per-CPT step-by-step guides
 *  3. Layer Types — visual stack reference with z-index, fields, image tips
 *  4. Shortcodes — all three shortcodes, full attribute tables, copy-paste examples
 *  5. Shape & Animation — shape presets, animation modes, accessibility
 *  6. Template System — file structure, CSS custom properties, custom template guide
 *  7. FAQ — 12+ Q&A cards in <details> accordion
 *  8. Developer Reference — hooks, JS API, REST endpoints, namespace conventions
 */
class Help {

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) { return; }

		$active   = isset( $_GET['section'] ) ? sanitize_key( $_GET['section'] ) : 'quickstart';
		$sections = $this->get_sections();
		if ( ! array_key_exists( $active, $sections ) ) { $active = 'quickstart'; }
		$hub = admin_url( 'admin.php?page=pizzalayer-help' );

		?>
		<div class="wrap plhelp-wrap">
		<?php $this->render_styles(); ?>

		<!-- ══ Header ═══════════════════════════════════════════════════ -->
		<div class="plhelp-header">
			<div>
				<h1 class="plhelp-header__title">
					<span class="dashicons dashicons-editor-help"></span>
					PizzaLayer Help &amp; Reference
				</h1>
				<p class="plhelp-header__sub">Full documentation for setup, content management, shortcodes, templates, and development.</p>
			</div>
			<div style="display:flex;gap:8px;flex-wrap:wrap;">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=pizzalayer-setup' ) ); ?>" class="button">
					<span class="dashicons dashicons-welcome-learn-more"></span> Setup Guide
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=pizzalayer-shortcodes' ) ); ?>" class="button">
					<span class="dashicons dashicons-editor-code"></span> Shortcode Generator
				</a>
			</div>
		</div>

		<!-- ══ Layout ════════════════════════════════════════════════════ -->
		<div class="plhelp-layout">

			<!-- Left nav -->
			<nav class="plhelp-nav">
				<?php foreach ( $sections as $key => $sec ) : ?>
				<a href="<?php echo esc_url( add_query_arg( 'section', $key, $hub ) ); ?>"
				   class="plhelp-nav__item<?php echo $key === $active ? ' plhelp-nav__item--active' : ''; ?>">
					<span class="plhelp-nav__icon"><?php echo $sec['icon']; ?></span>
					<?php echo esc_html( $sec['title'] ); ?>
				</a>
				<?php endforeach; ?>
			</nav>

			<!-- Content -->
			<article class="plhelp-content">
				<?php $this->render_section( $active, $sections[ $active ] ); ?>
			</article>

		</div><!-- /.plhelp-layout -->
		</div><!-- /.plhelp-wrap -->
		<?php
	}

	private function get_sections(): array {
		return [
			'quickstart' => [ 'icon' => '🚀', 'title' => 'Quickstart'           ],
			'content'    => [ 'icon' => '📦', 'title' => 'Managing Content'      ],
			'layers'     => [ 'icon' => '📚', 'title' => 'Layer Type Reference'  ],
			'shortcodes' => [ 'icon' => '</>', 'title' => 'Shortcodes'            ],
			'shapes'     => [ 'icon' => '◉',  'title' => 'Shape & Animation'     ],
			'templates'  => [ 'icon' => '🎨', 'title' => 'Template System'       ],
			'faq'        => [ 'icon' => '❓', 'title' => 'FAQ'                   ],
			'developer'  => [ 'icon' => '⚙',  'title' => 'Developer Reference'   ],
		];
	}

	private function render_section( string $key, array $meta ): void {
		echo '<h2 class="plhelp-section-title">' . $meta['icon'] . ' ' . esc_html( $meta['title'] ) . '</h2>';
		$method = 'section_' . $key;
		if ( method_exists( $this, $method ) ) { $this->$method(); }
	}

	// ═══════════════════════════════════════════════════════════════════
	// 1. QUICKSTART
	// ═══════════════════════════════════════════════════════════════════
	private function section_quickstart(): void { ?>
		<p class="plhelp-lead">Go from a fresh plugin install to a live interactive pizza builder in five steps. Each step links directly to the relevant admin page.</p>

		<div class="plhelp-steps">

			<div class="plhelp-step">
				<div class="plhelp-step__num">1</div>
				<div class="plhelp-step__body">
					<h3>Add your layer images</h3>
					<p>Every visual component (crust, sauce, cheese, toppings, drizzles, cuts) is a WordPress post with a <strong>layer image</strong> attached. Head to <strong>Content</strong> and create at least one Crust, one Sauce, one Cheese, and a few Toppings to start.</p>
					<div class="plhelp-checklist">
						<label><input type="checkbox"> Create at least 1 Crust with a layer image</label>
						<label><input type="checkbox"> Create at least 1 Sauce with a layer image</label>
						<label><input type="checkbox"> Create at least 1 Cheese with a layer image</label>
						<label><input type="checkbox"> Create 3–5 Toppings with layer images</label>
					</div>
					<p class="plhelp-tip-inline">💡 <strong>Image tips:</strong> Use transparent PNG or WebP at a consistent square canvas (500×500 px or 1000×1000 px). Keep files under 200 KB. All layers must use the same canvas size or they'll appear offset.</p>
					<a href="<?php echo esc_url( admin_url('admin.php?page=pizzalayer-content') ); ?>" class="button button-primary">Open Content Hub →</a>
				</div>
			</div>

			<div class="plhelp-step">
				<div class="plhelp-step__num">2</div>
				<div class="plhelp-step__body">
					<h3>Set global defaults in Settings</h3>
					<p>In Settings, configure which crust/sauce/cheese loads by default when the builder first appears. Also set your pizza shape, layer animation style, max toppings, and branding.</p>
					<div class="plhelp-checklist">
						<label><input type="checkbox"> Set a Default Crust, Sauce, and Cheese</label>
						<label><input type="checkbox"> Set Max Toppings (0 = unlimited)</label>
						<label><input type="checkbox"> Choose a Pizza Shape and Layer Animation</label>
					</div>
					<a href="<?php echo esc_url( admin_url('admin.php?page=pizzalayer-settings') ); ?>" class="button">Settings →</a>
				</div>
			</div>

			<div class="plhelp-step">
				<div class="plhelp-step__num">3</div>
				<div class="plhelp-step__body">
					<h3>Choose your template</h3>
					<p>Templates control the entire visual design of the builder. PizzaLayer ships with five built-in templates: <strong>NightPie</strong> (dark, modern), <strong>Metro</strong> (clean, card-based), <strong>Colorbox</strong>, <strong>Rustic</strong>, <strong>PocketPie</strong>, and <strong>Scaffold</strong> (bare-bones starting point). You can also create a custom template in your theme (see Template System).</p>
					<a href="<?php echo esc_url( admin_url('admin.php?page=pizzalayer-template') ); ?>" class="button">Template Settings →</a>
				</div>
			</div>

			<div class="plhelp-step">
				<div class="plhelp-step__num">4</div>
				<div class="plhelp-step__body">
					<h3>Embed the builder on a page</h3>
					<p>Edit any WordPress page and add:</p>
					<pre class="plhelp-code">[pizza_builder]</pre>
					<p>Or insert the <strong>Pizza Builder</strong> Gutenberg block from the block inserter. Use the Shortcode Generator to build more advanced shortcodes with per-page attribute overrides (custom shape, max toppings, hidden tabs, etc.).</p>
					<a href="<?php echo esc_url( admin_url('admin.php?page=pizzalayer-shortcodes') ); ?>" class="button">Shortcode Generator →</a>
				</div>
			</div>

			<div class="plhelp-step">
				<div class="plhelp-step__num">5</div>
				<div class="plhelp-step__body">
					<h3>Preview and verify</h3>
					<p>Visit the page on the front end. Select a crust, sauce, cheese, and add toppings — the visualizer should update in real time. Open the browser console (F12) if anything appears broken.</p>
					<div class="plhelp-alert plhelp-alert--warn">
						<strong>Common first-time issues:</strong>
						<ul>
							<li>Pizza preview blank → confirm your default Crust has a layer image set in the post's custom fields.</li>
							<li>Layers misaligned → all layer images must use the same canvas size (e.g. all 500×500 px).</li>
							<li>Builder styles missing → some caching plugins strip inline <code>&lt;style&gt;</code> tags; check your caching settings or switch to enqueueing CSS files.</li>
							<li>JavaScript errors in console → check that jQuery is loading (it's a dependency), and that no other plugin is conflicting with the <code>$</code> global.</li>
						</ul>
					</div>
				</div>
			</div>

		</div><!-- /.plhelp-steps -->
	<?php }

	// ═══════════════════════════════════════════════════════════════════
	// 2. MANAGING CONTENT
	// ═══════════════════════════════════════════════════════════════════
	private function section_content(): void { ?>
		<p class="plhelp-lead">All pizza content lives in the <strong>Content Hub</strong> — a single admin page with a vertical tab rail for each of the 8 layer types. Click any type in the left rail to switch instantly without leaving the page.</p>

		<div class="plhelp-info-box">
			<span class="dashicons dashicons-info-outline"></span>
			<div>
				<strong>Getting there:</strong> PizzaLayer → Content, or click the content type name in the top admin bar (PizzaLayer → Toppings, Crusts, etc.). Use the <strong>+ New</strong> pill next to each type in the admin bar to jump directly to the add-new screen.
			</div>
		</div>

		<h3>Adding a new layer item (any type)</h3>
		<ol class="plhelp-list plhelp-list--numbered">
			<li>In the Content Hub, click the layer type in the left rail (e.g. Toppings).</li>
			<li>Click <strong>Add New Topping</strong> (or the <strong>+</strong> icon beside the type in the rail).</li>
			<li>Enter a <strong>Title</strong> — this is the public name shown in the builder (e.g. "Pepperoni").</li>
			<li>Set the <strong>Featured Image</strong> — this is the selection card thumbnail.</li>
			<li>In the <strong>Layer Image</strong> custom field (below the editor), paste or upload the full-canvas transparent PNG/WebP for the visual stack.</li>
			<li>Add optional <strong>content</strong> for a description shown in some template styles.</li>
			<li>Click <strong>Publish</strong>.</li>
		</ol>

		<h3>Layer Image vs. Featured Image</h3>
		<table class="plhelp-attr-table">
			<thead><tr><th>Image</th><th>Where it appears</th><th>Recommended size</th></tr></thead>
			<tbody>
				<tr>
					<td><strong>Layer Image</strong> (custom field: <code>pzl_layer_image</code>)</td>
					<td>The pizza visualizer canvas — stacked with all other layers</td>
					<td>500×500 px or 1000×1000 px, transparent PNG/WebP</td>
				</tr>
				<tr>
					<td><strong>Featured Image</strong></td>
					<td>Selection card thumbnail in the builder UI</td>
					<td>200×200 px recommended, any format</td>
				</tr>
			</tbody>
		</table>

		<h3>Managing existing items</h3>
		<p>The Content Hub embeds the standard WordPress list table for each type. All native features work:</p>
		<ul class="plhelp-list">
			<li><strong>Search</strong> — use the search box above the table to find items by title.</li>
			<li><strong>Bulk actions</strong> — select multiple items with checkboxes and delete or trash in bulk.</li>
			<li><strong>Edit</strong> — click any title to open the native WordPress edit screen.</li>
			<li><strong>Quick Edit</strong> — hover over an item and click Quick Edit to change the title without leaving the list.</li>
			<li><strong>Sort</strong> — click column headers (Title, Date, Author) to re-sort the list.</li>
			<li><strong>Pagination</strong> — navigate pages if you have more than 20 items.</li>
		</ul>

		<h3>Sizes and pricing data</h3>
		<p>Sizes are not visual layers — they carry dimension metadata for PizzaLayerPro pricing calculations. For each Size post, add these custom fields:</p>
		<table class="plhelp-attr-table">
			<thead><tr><th>Custom Field</th><th>Example value</th><th>Description</th></tr></thead>
			<tbody>
				<tr><td><code>size_diameter_in</code></td><td><code>12</code></td><td>Diameter in inches</td></tr>
				<tr><td><code>size_area_sqin</code></td><td><code>113.1</code></td><td>Area in square inches (π × r²)</td></tr>
			</tbody>
		</table>

		<h3>Tips for a production-ready setup</h3>
		<ul class="plhelp-list">
			<li>Create a "Plain / No Sauce" sauce item so customers can opt out of sauce without breaking the builder.</li>
			<li>Create a "No Cheese" cheese item similarly.</li>
			<li>Keep your layer image filenames descriptive (e.g. <code>topping-pepperoni-layer.png</code>) for easier management in the Media Library.</li>
			<li>Use WordPress <strong>Categories</strong> (available on all PizzaLayer CPTs) to group items — some Pro templates use category filtering.</li>
		</ul>
	<?php }

	// ═══════════════════════════════════════════════════════════════════
	// 3. LAYER TYPES REFERENCE
	// ═══════════════════════════════════════════════════════════════════
	private function section_layers(): void {
		$types = [
			[
				'name'   => 'Crust',
				'icon'   => '⬤',
				'color'  => '#c8956c',
				'cpt'    => 'crusts',
				'z'      => 100,
				'desc'   => 'The base canvas. Every pizza starts with a crust. Only one crust can be selected at a time. The crust image defines the visible edge — all other layers must fit within it.',
				'fields' => [
					'Title'          => 'Public name shown in the builder (e.g. "Thin & Crispy", "Stuffed Crust").',
					'Layer Image'    => 'Full-canvas transparent PNG/WebP. The crust rim should be visible around the pizza edge.',
					'Featured Image' => 'Thumbnail shown in the selection card.',
					'Content'        => 'Optional description displayed in some template styles.',
				],
				'tips'   => 'Use a circular or correctly-shaped crust image on a transparent background. The crust anchors the visual stack — if it\'s off-center or wrong size, everything else will be too.',
			],
			[
				'name'   => 'Sauce',
				'icon'   => '🥫',
				'color'  => '#d63638',
				'cpt'    => 'sauces',
				'z'      => 200,
				'desc'   => 'Applied on top of the crust. Only one sauce is active at a time. Sits at z-index 200 in the visual stack.',
				'fields' => [
					'Title'          => 'Public name (e.g. "Classic Tomato", "Garlic White", "BBQ").',
					'Layer Image'    => 'Transparent PNG. Semi-transparent edges create a natural inset blend.',
					'Featured Image' => 'Selection card thumbnail.',
				],
				'tips'   => 'Keep the sauce layer slightly inset from the crust edge (around 5–8% of canvas width) so the crust rim stays visible and the pizza doesn\'t look flat.',
			],
			[
				'name'   => 'Cheese',
				'icon'   => '🧀',
				'color'  => '#dba633',
				'cpt'    => 'cheeses',
				'z'      => 300,
				'desc'   => 'Sits between sauce and toppings. Only one cheese active at a time. Z-index 300.',
				'fields' => [
					'Title'          => 'Public name (e.g. "Mozzarella", "Cheddar", "Dairy-Free").',
					'Layer Image'    => 'Transparent PNG with natural melt texture.',
					'Featured Image' => 'Selection card thumbnail.',
				],
				'tips'   => 'A slight golden-edge gradient on the cheese image looks convincingly melted. For a "No Cheese" option, create a Cheese post with no layer image — just a title of "No Cheese".',
			],
			[
				'name'   => 'Topping',
				'icon'   => '🥓',
				'color'  => '#f0b849',
				'cpt'    => 'toppings',
				'z'      => '400+',
				'desc'   => 'Multiple toppings can be active simultaneously. Each is a separate layer above cheese. Supports whole, half, and quarter coverage via CSS clip-path. Z-index starts at 400.',
				'fields' => [
					'Title'          => 'Public name (e.g. "Pepperoni", "Mushrooms", "Jalapeños").',
					'Layer Image'    => 'Full-canvas transparent PNG showing the topping distributed across the entire pizza area.',
					'Featured Image' => 'Selection card thumbnail.',
					'Content'        => 'Optional description (e.g. allergen info, flavor notes).',
				],
				'tips'   => 'Topping images should cover the whole pizza canvas — coverage (half/quarter) is applied via CSS clip-path at render time. No separate images needed for different coverages.',
			],
			[
				'name'   => 'Drizzle',
				'icon'   => '💧',
				'color'  => '#00a32a',
				'cpt'    => 'drizzles',
				'z'      => 900,
				'desc'   => 'Optional finishing layer above all toppings. Only one drizzle active at a time. Z-index 900.',
				'fields' => [
					'Title'          => 'Public name (e.g. "Balsamic Glaze", "Hot Honey", "Ranch").',
					'Layer Image'    => 'Transparent PNG with a flowing, organic drizzle pattern.',
					'Featured Image' => 'Selection card thumbnail.',
				],
				'tips'   => 'Drizzle images look best with an asymmetric, hand-poured feel — avoid perfectly symmetric radial patterns, which look computer-generated.',
			],
			[
				'name'   => 'Cut',
				'icon'   => '✂',
				'color'  => '#2271b1',
				'cpt'    => 'cuts',
				'z'      => 950,
				'desc'   => 'Slicing overlay applied above everything. Only one cut style active. Z-index 950.',
				'fields' => [
					'Title'          => 'Public name (e.g. "Classic Triangle", "Square Cut", "Party Style", "No Cut").',
					'Layer Image'    => 'Transparent PNG with thin slice lines. Light line weight so toppings show through.',
					'Featured Image' => 'Selection card thumbnail.',
				],
				'tips'   => 'Use ~15–20% opacity for slice lines so toppings remain visible beneath. Always create a "No Cut" option with a blank layer image.',
			],
			[
				'name'   => 'Size',
				'icon'   => '📏',
				'color'  => '#8c5af8',
				'cpt'    => 'sizes',
				'z'      => '—',
				'desc'   => 'Defines available pizza dimensions. Not a visual layer — carries metadata used by PizzaLayerPro for pricing.',
				'fields' => [
					'Title'            => 'Public name (e.g. "Small – 10″", "Medium – 12″", "Large – 16″").',
					'size_diameter_in' => 'Custom field: diameter in inches.',
					'size_area_sqin'   => 'Custom field: area in square inches (used for per-area topping pricing in Pro).',
				],
				'tips'   => 'Calculate area accurately: area = π × (diameter/2)². For a 12″ pizza: π × 36 ≈ 113.1 sq in.',
			],
		];
		?>
		<p class="plhelp-lead">PizzaLayer's visual stack is built from seven layer types. Each is a WordPress Custom Post Type. Here's the full reference for each type — fields, z-index order, image guidelines, and pro tips.</p>

		<div class="plhelp-stack-diagram">
			<div class="plhelp-stack-diagram__label">Visual Stack (bottom → top)</div>
			<div class="plhelp-stack-diagram__layers">
				<?php $stack = [
					['Crust', '#c8956c', '100'],
					['Sauce', '#d63638', '200'],
					['Cheese', '#dba633', '300'],
					['Toppings', '#f0b849', '400+'],
					['Drizzle', '#00a32a', '900'],
					['Cut', '#2271b1', '950'],
				];
				foreach ( $stack as $i => [$name, $color, $z] ) : ?>
				<div class="plhelp-stack-layer" style="--color:<?php echo esc_attr($color); ?>;--i:<?php echo esc_attr($i); ?>">
					<span class="plhelp-stack-layer__name"><?php echo esc_html($name); ?></span>
					<span class="plhelp-stack-layer__z">z: <?php echo esc_html($z); ?></span>
				</div>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="plhelp-layers-grid">
			<?php foreach ( $types as $t ) : ?>
			<div class="plhelp-layer-card">
				<div class="plhelp-layer-card__head" style="border-left-color:<?php echo esc_attr( $t['color'] ); ?>">
					<span class="plhelp-layer-card__icon" style="color:<?php echo esc_attr( $t['color'] ); ?>"><?php echo $t['icon']; ?></span>
					<div>
						<h3><?php echo esc_html( $t['name'] ); ?></h3>
						<span class="plhelp-badge" style="background:<?php echo esc_attr( $t['color'] ); ?>20;color:<?php echo esc_attr( $t['color'] ); ?>">z-index: <?php echo esc_html( $t['z'] ); ?></span>
					</div>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=pizzalayer-content&pl_cpt=' . $t['cpt'] ) ); ?>" class="button button-small" style="margin-left:auto">Manage →</a>
				</div>
				<p class="plhelp-layer-card__desc"><?php echo esc_html( $t['desc'] ); ?></p>
				<table class="plhelp-fields-table">
					<thead><tr><th>Field</th><th>Purpose</th></tr></thead>
					<tbody>
						<?php foreach ( $t['fields'] as $field => $purpose ) : ?>
						<tr>
							<td><code><?php echo esc_html( $field ); ?></code></td>
							<td><?php echo wp_kses_post( $purpose ); ?></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<div class="plhelp-tip">💡 <?php echo esc_html( $t['tips'] ); ?></div>
			</div>
			<?php endforeach; ?>
		</div>
	<?php }

	// ═══════════════════════════════════════════════════════════════════
	// 4. SHORTCODES
	// ═══════════════════════════════════════════════════════════════════
	private function section_shortcodes(): void { ?>
		<p class="plhelp-lead">PizzaLayer provides three shortcodes. Every attribute is optional — defaults come from the global Settings unless overridden at the shortcode level. Use the <a href="<?php echo esc_url( admin_url('admin.php?page=pizzalayer-shortcodes') ); ?>">Shortcode Generator</a> for a visual builder.</p>

		<!-- ── [pizza_builder] ─────────────────────────────────────────── -->
		<div class="plhelp-sc-block">
			<div class="plhelp-sc-block__head">
				<code class="plhelp-sc-name">[pizza_builder]</code>
				<span class="plhelp-sc-tag">Interactive builder</span>
			</div>
			<p>Renders the full interactive pizza builder. Customers select layers, add toppings, see coverage options, and review their pizza in real time.</p>

			<table class="plhelp-attr-table">
				<thead><tr><th>Attribute</th><th>Values / format</th><th>Default</th><th>Description</th></tr></thead>
				<tbody>
					<tr><td><code>id</code></td><td>any string</td><td>auto</td><td>Unique instance ID. Required only when placing two builders on the same page (e.g. <code>id="pizza-1"</code>).</td></tr>
					<tr><td><code>template</code></td><td>template slug</td><td>active template</td><td>Override the template for this builder only (e.g. <code>template="nightpie"</code>).</td></tr>
					<tr><td><code>max_toppings</code></td><td>integer</td><td>global setting</td><td>Maximum toppings this builder allows. <code>0</code> = unlimited.</td></tr>
					<tr><td><code>show_tabs</code></td><td>comma list</td><td>all</td><td>Whitelist of tabs to show: <code>crust,sauce,cheese,toppings,drizzle,slicing,yourpizza</code></td></tr>
					<tr><td><code>hide_tabs</code></td><td>comma list</td><td>none</td><td>Tabs to hide. Simpler than listing all tabs you want to keep.</td></tr>
					<tr><td><code>default_crust</code></td><td>slug string</td><td>global setting</td><td>Pre-select a crust slug on load (e.g. <code>default_crust="thin-crust"</code>).</td></tr>
					<tr><td><code>default_sauce</code></td><td>slug string</td><td>global setting</td><td>Pre-select a sauce on load.</td></tr>
					<tr><td><code>default_cheese</code></td><td>slug string</td><td>global setting</td><td>Pre-select a cheese on load.</td></tr>
					<tr><td><code>pizza_shape</code></td><td><code>round</code> <code>square</code> <code>rectangle</code> <code>custom</code></td><td>global setting</td><td>Override pizza shape for this builder.</td></tr>
					<tr><td><code>pizza_aspect</code></td><td>CSS ratio e.g. <code>4 / 3</code></td><td>global setting</td><td>Aspect ratio for rectangle/custom shapes.</td></tr>
					<tr><td><code>pizza_radius</code></td><td>CSS value e.g. <code>12px</code></td><td>global setting</td><td>Border radius for custom shape.</td></tr>
					<tr><td><code>layer_anim</code></td><td><code>fade</code> <code>scale-in</code> <code>slide-up</code> <code>flip-in</code> <code>drop-in</code> <code>instant</code></td><td>global setting</td><td>Animation when a layer is added.</td></tr>
				</tbody>
			</table>

			<h4>Copy-paste examples</h4>
			<pre class="plhelp-code"><?php echo esc_html(
'[pizza_builder]

[pizza_builder id="pizza-1" max_toppings="5" default_crust="thin-crust" default_sauce="classic-tomato"]

[pizza_builder hide_tabs="drizzle,slicing" pizza_shape="square"]

[pizza_builder pizza_shape="rectangle" pizza_aspect="4 / 3" layer_anim="scale-in"]

[pizza_builder id="gf-builder" template="nightpie" default_cheese="dairy-free" hide_tabs="sizes,yourpizza"]'
); ?></pre>
		</div>

		<!-- ── [pizza_static] ──────────────────────────────────────────── -->
		<div class="plhelp-sc-block">
			<div class="plhelp-sc-block__head">
				<code class="plhelp-sc-name">[pizza_static]</code>
				<span class="plhelp-sc-tag plhelp-sc-tag--green">Static display</span>
			</div>
			<p>Renders a static pizza image stack — no builder UI. Great for menu pages, featured pizzas, inline displays in blog posts, or order confirmation pages.</p>

			<table class="plhelp-attr-table">
				<thead><tr><th>Attribute</th><th>Values</th><th>Description</th></tr></thead>
				<tbody>
					<tr><td><code>crust</code></td><td>slug</td><td>Crust slug to render.</td></tr>
					<tr><td><code>sauce</code></td><td>slug</td><td>Sauce slug to render.</td></tr>
					<tr><td><code>cheese</code></td><td>slug</td><td>Cheese slug to render.</td></tr>
					<tr><td><code>toppings</code></td><td>comma list</td><td>Topping slugs to stack (e.g. <code>pepperoni,mushrooms</code>).</td></tr>
					<tr><td><code>drizzle</code></td><td>slug</td><td>Drizzle layer to render.</td></tr>
					<tr><td><code>cut</code></td><td>slug</td><td>Cut overlay to render.</td></tr>
				</tbody>
			</table>

			<h4>Copy-paste examples</h4>
			<pre class="plhelp-code"><?php echo esc_html(
'[pizza_static crust="thin-crust" sauce="classic-tomato" cheese="mozzarella" toppings="pepperoni,basil"]

[pizza_static crust="thick-crust" sauce="garlic-white" cheese="cheddar" toppings="chicken,bacon" drizzle="ranch"]

[pizza_static crust="thin-crust" sauce="bbq" cheese="mozzarella" toppings="chicken,red-onion" cut="square-cut"]'
); ?></pre>
		</div>

		<!-- ── [pizza_layer] ──────────────────────────────────────────── -->
		<div class="plhelp-sc-block">
			<div class="plhelp-sc-block__head">
				<code class="plhelp-sc-name">[pizza_layer]</code>
				<span class="plhelp-sc-tag plhelp-sc-tag--purple">Single image</span>
			</div>
			<p>Renders a single layer image anywhere on the page — useful for ingredient spotlights, menu cards, "featured topping" sections, or decorative use.</p>

			<table class="plhelp-attr-table">
				<thead><tr><th>Attribute</th><th>Values</th><th>Description</th></tr></thead>
				<tbody>
					<tr><td><code>type</code></td><td><code>crust</code> <code>sauce</code> <code>cheese</code> <code>topping</code> <code>drizzle</code> <code>cut</code></td><td>The layer type to look up.</td></tr>
					<tr><td><code>slug</code></td><td>layer slug</td><td>The specific layer item to render.</td></tr>
					<tr><td><code>field</code></td><td><code>list</code> (default) · <code>layer</code></td><td><code>list</code> = featured image thumbnail; <code>layer</code> = full canvas layer image.</td></tr>
					<tr><td><code>class</code></td><td>CSS class string</td><td>Extra class(es) added to the <code>&lt;img&gt;</code> element.</td></tr>
				</tbody>
			</table>

			<h4>Copy-paste examples</h4>
			<pre class="plhelp-code"><?php echo esc_html(
'[pizza_layer type="topping" slug="pepperoni"]

[pizza_layer type="crust" slug="thick-crust" field="layer"]

[pizza_layer type="sauce" slug="bbq" class="my-sauce-preview"]'
); ?></pre>
		</div>

		<div class="plhelp-info-box">
			<span class="dashicons dashicons-editor-code"></span>
			<div>
				<strong>Gutenberg Blocks:</strong> All three shortcodes are available as native Gutenberg blocks. The Pizza Builder block includes the same attribute controls in the block sidebar — including per-block shape, animation, and tab visibility overrides. No shortcode syntax required.
			</div>
		</div>
	<?php }

	// ═══════════════════════════════════════════════════════════════════
	// 5. SHAPE & ANIMATION
	// ═══════════════════════════════════════════════════════════════════
	private function section_shapes(): void { ?>
		<p class="plhelp-lead">PizzaLayer supports multiple pizza canvas shapes and six layer-add animations. Set site-wide defaults in <a href="<?php echo esc_url( admin_url('admin.php?page=pizzalayer-settings') ); ?>">Settings</a>, then override per-shortcode or per-block.</p>

		<h3>Pizza Shapes</h3>
		<div class="plhelp-shape-grid">
			<?php $shapes = [
				[ 'name' => 'Round',     'value' => 'round',     'style' => 'border-radius:50%;',                        'desc' => 'Classic circular pizza. Aspect ratio 1:1. Default.' ],
				[ 'name' => 'Square',    'value' => 'square',    'style' => 'border-radius:8px;',                        'desc' => 'Square pizza with rounded corners. Aspect ratio 1:1.' ],
				[ 'name' => 'Rectangle', 'value' => 'rectangle', 'style' => 'border-radius:12px;width:90px;height:68px;','desc' => 'Pan pizza or sheet pizza. Set your own aspect ratio.' ],
				[ 'name' => 'Custom',    'value' => 'custom',    'style' => 'border-radius:20px 6px;',                   'desc' => 'Full control: set both aspect-ratio and border-radius.' ],
			];
			foreach ( $shapes as $s ) : ?>
			<div class="plhelp-shape-card">
				<div class="plhelp-shape-preview" style="<?php echo esc_attr( $s['style'] ); ?>"></div>
				<strong><?php echo esc_html( $s['name'] ); ?></strong>
				<code>pizza_shape="<?php echo esc_html( $s['value'] ); ?>"</code>
				<p><?php echo esc_html( $s['desc'] ); ?></p>
			</div>
			<?php endforeach; ?>
		</div>

		<h4>Additional shape attributes</h4>
		<table class="plhelp-attr-table">
			<thead><tr><th>Attribute</th><th>Used with</th><th>Example</th><th>Description</th></tr></thead>
			<tbody>
				<tr><td><code>pizza_aspect</code></td><td>rectangle, custom</td><td><code>4 / 3</code>, <code>16 / 9</code></td><td>CSS aspect-ratio value. Controls width-to-height ratio.</td></tr>
				<tr><td><code>pizza_radius</code></td><td>custom</td><td><code>12px</code>, <code>50%</code>, <code>20px 6px</code></td><td>CSS border-radius. Overrides the preset's built-in radius.</td></tr>
			</tbody>
		</table>

		<h3>Layer Animations</h3>
		<p>The animation plays when a layer or topping is added to the pizza visualizer. Set a site-wide default in <a href="<?php echo esc_url( admin_url('admin.php?page=pizzalayer-settings') ); ?>">Settings → Layer Animation</a>. Override per-shortcode with <code>layer_anim="..."</code>.</p>

		<table class="plhelp-attr-table">
			<thead><tr><th>Value</th><th>Effect</th><th>Duration</th><th>Best for</th></tr></thead>
			<tbody>
				<tr><td><code>fade</code></td><td>Simple opacity 0→1</td><td>300ms</td><td>Default. Subtle, professional, works everywhere.</td></tr>
				<tr><td><code>scale-in</code></td><td>Starts at 55% size, springs to full — bouncy cubic-bezier</td><td>320ms</td><td>Playful, energetic menus targeting younger audiences.</td></tr>
				<tr><td><code>slide-up</code></td><td>Enters from 22% below, slides smoothly to position</td><td>320ms</td><td>Modern material-style UIs with vertical hierarchy.</td></tr>
				<tr><td><code>flip-in</code></td><td>3-D Y-axis rotate from 90° to 0° with slight bounce</td><td>400ms</td><td>High-impact, premium reveal feel.</td></tr>
				<tr><td><code>drop-in</code></td><td>Falls from 30% above the visualizer, snaps into place</td><td>320ms</td><td>Fun, gravity-driven interaction.</td></tr>
				<tr><td><code>instant</code></td><td>No animation — appears immediately</td><td>0ms</td><td>Accessibility needs, performance-critical contexts.</td></tr>
			</tbody>
		</table>

		<div class="plhelp-info-box">
			<span class="dashicons dashicons-universal-access-alt"></span>
			<div>
				<strong>Accessibility:</strong> PizzaLayer's animation engine respects the OS-level "Reduce Motion" preference (<code>prefers-reduced-motion: reduce</code>). Users with that setting active will always see instant layer changes, regardless of which animation mode is configured.
			</div>
		</div>
	<?php }

	// ═══════════════════════════════════════════════════════════════════
	// 6. TEMPLATE SYSTEM
	// ═══════════════════════════════════════════════════════════════════
	private function section_templates(): void { ?>
		<p class="plhelp-lead">Templates control the complete visual presentation of the pizza builder — layout, colours, fonts, card styles, and responsive behaviour. PizzaLayer ships with five production-ready templates (<strong>NightPie</strong>, <strong>Metro</strong>, <strong>Colorbox</strong>, <strong>Rustic</strong>, <strong>PocketPie</strong>) plus a bare <strong>Scaffold</strong> template for building your own from scratch.</p>

		<h3>How templates work</h3>
		<p>A template is a directory containing at minimum:</p>
		<pre class="plhelp-code">your-template-slug/
  pztp-containers-menu.php   ← main builder HTML + PHP logic
  template.css               ← template-specific styles
  custom.js                  ← template-specific JavaScript
  template-preview.jpg       ← screenshot shown in the Template picker (optional)</pre>

		<h3>Template load order</h3>
		<ol class="plhelp-list plhelp-list--numbered">
			<li><strong>Child theme:</strong> <code>/wp-content/themes/your-child-theme/pzttemplates/your-slug/</code></li>
			<li><strong>Parent theme:</strong> <code>/wp-content/themes/your-theme/pzttemplates/your-slug/</code></li>
			<li><strong>Plugin:</strong> <code>/wp-content/plugins/PizzaLayer/templates/your-slug/</code></li>
		</ol>
		<p>PizzaLayer checks the child theme first — your customisations survive plugin updates safely.</p>

		<h3>Creating a custom template</h3>
		<ol class="plhelp-list plhelp-list--numbered">
			<li>Copy the <code>nightpie</code> directory from <code>/plugins/PizzaLayer/templates/</code> to your theme's <code>pzttemplates/</code> folder.</li>
			<li>Rename the directory to your slug (e.g. <code>mypizzeria</code>).</li>
			<li>Edit <code>template.css</code> — all main variables are CSS custom properties at the top of the file.</li>
			<li>Restructure HTML in <code>pztp-containers-menu.php</code> as needed. The <code>$atts</code> and <code>$instance_id</code> variables are available.</li>
			<li>Go to <a href="<?php echo esc_url( admin_url('admin.php?page=pizzalayer-template') ); ?>">Settings → Template</a> and switch to your new template.</li>
		</ol>

		<h3>NightPie CSS custom properties</h3>
		<pre class="plhelp-code">/* Colours */
--np-accent:       #ff6b35   /* primary action colour */
--np-accent-hover: #e05a28   /* hover state */
--np-bg:           #1a1e23   /* outer dark background */
--np-surface:      #252a31   /* card / panel surfaces */
--np-border:       #2d3748   /* border colour */
--np-text:         #e2e8f0   /* primary text */
--np-text-muted:   #8d97a5   /* secondary / hint text */

/* Geometry */
--np-radius:       10px      /* card corner radius */
--np-radius-pill:  999px     /* pill buttons */

/* Change the accent colour only: */
:root { --np-accent: #e63946; --np-accent-hover: #c1121f; }</pre>

		<div class="plhelp-info-box plhelp-info-box--warn">
			<span class="dashicons dashicons-warning"></span>
			<div>
				<strong>Always use your theme directory for custom templates.</strong> Files inside <code>/plugins/PizzaLayer/templates/</code> are overwritten on plugin update. Anything in <code>/pzttemplates/</code> in your theme is safe.
			</div>
		</div>
	<?php }

	// ═══════════════════════════════════════════════════════════════════
	// 7. FAQ
	// ═══════════════════════════════════════════════════════════════════
	private function section_faq(): void {
		$faqs = [
			[ 'q' => 'The pizza preview is blank — nothing shows on the canvas.',
			  'a' => 'The most common cause is a missing or broken layer image on the default crust. Go to <strong>Content → Crusts</strong>, open your default crust post, and confirm the <code>pzl_layer_image</code> custom field has a valid image URL. Also open the browser console (F12 → Console) and check for JavaScript errors — if the builder script failed to load, no layers will appear.' ],
			[ 'q' => 'Layers look misaligned or don\'t stack correctly.',
			  'a' => 'All layer images must use the same canvas size with a transparent background. If your crust is 500×500 px but a topping was exported at 400×400 px (with no padding to reach 500×500), it will appear offset. Re-export on a consistent canvas across all assets.' ],
			[ 'q' => 'Can I place two builders on the same page?',
			  'a' => 'Yes. Give each shortcode a unique <code>id</code>: <code>[pizza_builder id="pizza-1"]</code> and <code>[pizza_builder id="pizza-2"]</code>. Each instance manages its own state independently.' ],
			[ 'q' => 'The builder CSS conflicts with my theme.',
			  'a' => 'PizzaLayer templates use namespaced CSS classes (e.g. <code>.np-*</code> for NightPie) to avoid conflicts. If you still see issues, open your browser inspector, identify the conflicting rule\'s selector, and add a more specific override in your theme\'s custom CSS or in a child template\'s <code>template.css</code>.' ],
			[ 'q' => 'How do I add WooCommerce cart integration?',
			  'a' => 'WooCommerce integration (add-to-cart, line item breakdown, per-topping pricing) is provided by <strong>PizzaLayerPro</strong>. The base plugin handles the visual builder; Pro handles the commerce layer.' ],
			[ 'q' => 'Can I display a static pizza without the full builder?',
			  'a' => 'Yes — use <code>[pizza_static crust="thin-crust" sauce="tomato" cheese="mozzarella" toppings="pepperoni"]</code>. Specify each layer directly in the shortcode attributes. No builder UI is rendered.' ],
			[ 'q' => 'How do I pre-load a state from JavaScript (e.g. from a WooCommerce cart)?',
			  'a' => 'Use the public API: <code>window.PizzaLayerAPI.setState("instance-id", stateObject)</code>. See the Developer Reference section for the full state object schema.' ],
			[ 'q' => 'Does PizzaLayer respect the "Reduce Motion" accessibility preference?',
			  'a' => 'Yes. The animation engine checks for <code>prefers-reduced-motion: reduce</code>. Users with that setting active always see instant layer changes regardless of the configured animation mode.' ],
			[ 'q' => 'My custom template doesn\'t appear in the Template picker.',
			  'a' => 'Check that the directory is placed in a scanned location (<code>pzttemplates/your-slug/</code> in your theme root or child theme root) and contains a <code>pztp-containers-menu.php</code> file. The slug equals the directory name. After adding it, go to Template settings and refresh.' ],
			[ 'q' => 'How do I set a "No Sauce" or "No Cheese" option?',
			  'a' => 'Create a Sauce (or Cheese) post with the title "No Sauce" and leave the Layer Image field empty. When a customer selects it, the sauce layer is cleared from the canvas. Set <code>default_sauce=""</code> on the shortcode to not pre-select any sauce.' ],
			[ 'q' => 'The + Add New link in the admin bar goes to the wrong screen.',
			  'a' => 'Make sure you\'re using the PizzaLayer admin bar item, not a separate WordPress CPT menu item. The PizzaLayer admin bar groups its CPTs under the PizzaLayer dropdown — the "+ New" pill next to each type links to <code>post-new.php?post_type=pizzalayer_{type}</code>.' ],
			[ 'q' => 'Can I use toppings in the "Your Pizza" summary tab without them counting against the max?',
			  'a' => 'The max toppings limit applies to the toppings panel only. Drizzle and cut layers are always unlimited (only one of each active at a time). To exclude certain toppings from the count, you would need a custom filter on <code>pizzalayer_max_toppings</code> (see Developer Reference).' ],
		];
		?>
		<p class="plhelp-lead">Common questions about setup, content management, and customisation.</p>
		<div class="plhelp-faq">
			<?php foreach ( $faqs as $i => $faq ) : ?>
			<details class="plhelp-faq__item" <?php echo $i === 0 ? 'open' : ''; ?>>
				<summary class="plhelp-faq__q">
					<span class="plhelp-faq__arrow">▶</span>
					<?php echo esc_html( $faq['q'] ); ?>
				</summary>
				<div class="plhelp-faq__a"><?php echo wp_kses_post( $faq['a'] ); ?></div>
			</details>
			<?php endforeach; ?>
		</div>
	<?php }

	// ═══════════════════════════════════════════════════════════════════
	// 8. DEVELOPER REFERENCE
	// ═══════════════════════════════════════════════════════════════════
	private function section_developer(): void { ?>
		<p class="plhelp-lead">PizzaLayer is built for extensibility. This reference documents public PHP hooks, the JavaScript API, REST endpoints, namespace conventions, and CPT meta keys.</p>

		<div class="plhelp-dev-banner">
			<div class="plhelp-dev-banner__badge">🔧 Expanding documentation</div>
			<p>A fully-searchable, versioned developer reference with code examples for every hook, endpoint, and API method is in progress. What's below is the complete current public surface — every hook and method PizzaLayer exposes today.</p>
		</div>

		<!-- PHP Actions ─────────────────────────────────────────────────── -->
		<h3>PHP Action Hooks</h3>
		<table class="plhelp-attr-table">
			<thead><tr><th>Hook</th><th>Args</th><th>Description</th></tr></thead>
			<tbody>
				<tr>
					<td><code>pizzalayer_cpt_registered</code></td>
					<td>—</td>
					<td>Fires after all 8 CPTs have been registered. Use to add taxonomies, modify CPT args, or register dependent functionality.</td>
				</tr>
				<tr>
					<td><code>pizzalayer_before_builder</code></td>
					<td><code>$instance_id</code>, <code>$atts</code></td>
					<td>Fires immediately before the builder HTML is output. Use to inject wrapper elements or enqueue additional scripts scoped to this instance.</td>
				</tr>
				<tr>
					<td><code>pizzalayer_after_builder</code></td>
					<td><code>$instance_id</code>, <code>$atts</code></td>
					<td>Fires immediately after the builder HTML. Use to inject post-builder UI (e.g. a WooCommerce add-to-cart form).</td>
				</tr>
				<tr>
					<td><code>pizzalayer_admin_bar_menu</code></td>
					<td><code>$wp_admin_bar</code></td>
					<td>Add custom items to the PizzaLayer dropdown in the WordPress admin bar.</td>
				</tr>
				<tr>
					<td><code>pizzalayer_admin_home_quicknav</code></td>
					<td>—</td>
					<td>Inject additional icon cards into the Dashboard's quick-nav row.</td>
				</tr>
				<tr>
					<td><code>pizzalayer_admin_home_cards</code></td>
					<td>—</td>
					<td>Inject full-width cards below the feature row on the Dashboard.</td>
				</tr>
			</tbody>
		</table>

		<!-- PHP Filters ─────────────────────────────────────────────────── -->
		<h3>PHP Filter Hooks</h3>
		<table class="plhelp-attr-table">
			<thead><tr><th>Filter</th><th>Args</th><th>Returns</th><th>Description</th></tr></thead>
			<tbody>
				<tr>
					<td><code>pizzalayer_cpt_args_{slug}</code></td>
					<td><code>$args, $post_type</code></td>
					<td><code>$args</code></td>
					<td>Modify CPT registration args per type. Replace <code>{slug}</code> with e.g. <code>toppings</code>. Fires before <code>register_post_type()</code>.</td>
				</tr>
				<tr>
					<td><code>pizzalayer_builder_atts</code></td>
					<td><code>$atts</code></td>
					<td><code>$atts</code></td>
					<td>Filter all resolved shortcode/block attributes before the builder template renders. Useful for dynamic defaults.</td>
				</tr>
				<tr>
					<td><code>pizzalayer_max_toppings</code></td>
					<td><code>$count, $instance_id</code></td>
					<td><code>$count</code></td>
					<td>Dynamically change the max topping count per builder instance (e.g. based on a selected pizza size or WooCommerce product).</td>
				</tr>
				<tr>
					<td><code>pizzalayer_template_path</code></td>
					<td><code>$path, $slug</code></td>
					<td><code>$path</code></td>
					<td>Override the resolved filesystem path for a template file — useful for plugin-to-plugin template sharing or testing.</td>
				</tr>
			</tbody>
		</table>

		<h4>Example: enforce a max toppings based on pizza size</h4>
		<pre class="plhelp-code"><?php echo esc_html(
'add_filter( \'pizzalayer_max_toppings\', function( $count, $instance_id ) {
    $size = WC()->session->get( \'selected_pizza_size\' );
    if ( $size === \'small\' ) { return 3; }
    return $count;
}, 10, 2 );'
); ?></pre>

		<!-- JavaScript API ──────────────────────────────────────────────── -->
		<h3>JavaScript API (<code>window.PizzaLayerAPI</code>)</h3>
		<p>Available on any page where the builder is loaded. All methods are synchronous unless noted.</p>

		<table class="plhelp-attr-table">
			<thead><tr><th>Method</th><th>Returns</th><th>Description</th></tr></thead>
			<tbody>
				<tr><td><code>getState(instanceId)</code></td><td>state object</td><td>Get the current pizza state for a builder instance.</td></tr>
				<tr><td><code>setState(instanceId, state)</code></td><td>void</td><td>Programmatically set the full pizza state (resets builder, then applies new state).</td></tr>
				<tr><td><code>getAllInstances()</code></td><td>string[]</td><td>List all active builder instance IDs on the page.</td></tr>
				<tr><td><code>renderPizza(layers)</code></td><td>Promise&lt;string&gt;</td><td>Async. Fetches server-rendered pizza HTML stack via REST. Resolves to HTML string.</td></tr>
				<tr><td><code>getLayerUrl(type, slug)</code></td><td>Promise&lt;string&gt;</td><td>Async. Resolves the layer image URL for a type + slug pair.</td></tr>
				<tr><td><code>renderStatic(selectorOrEl, state)</code></td><td>jQuery stage</td><td>Client-side: render a pizza state into any DOM container without a server request.</td></tr>
			</tbody>
		</table>

		<h4>State object schema</h4>
		<pre class="plhelp-code"><?php echo esc_html(
'{
  crust:   { slug: "thin-crust",  title: "Thin Crust",  layerImg: "https://...", thumb: "https://..." },
  sauce:   { slug: "tomato",      title: "Classic Tomato", layerImg: "https://...", thumb: "https://..." },
  cheese:  { slug: "mozzarella",  title: "Mozzarella",  layerImg: "https://...", thumb: "https://..." },
  drizzle: { slug: "balsamic",    title: "Balsamic",    layerImg: "https://...", thumb: "https://..." },
  cut:     { slug: "triangle",    title: "Triangle Cut", layerImg: "https://...", thumb: "https://..." },
  toppings: {
    "pepperoni": { slug: "pepperoni", title: "Pepperoni", layerImg: "https://...", thumb: "https://...", zindex: 400, coverage: "whole" },
    "mushrooms": { slug: "mushrooms", title: "Mushrooms", layerImg: "https://...", thumb: "https://...", zindex: 401, coverage: "half-left" }
  }
}'
); ?></pre>

		<h4>Full usage examples</h4>
		<pre class="plhelp-code"><?php echo esc_html(
'// Get state
var state = window.PizzaLayerAPI.getState(\'pizza-1\');

// Set state
window.PizzaLayerAPI.setState(\'pizza-1\', {
    crust:  { slug: \'thin-crust\', layerImg: \'...\', title: \'Thin Crust\' },
    sauce:  { slug: \'tomato\',     layerImg: \'...\', title: \'Classic Tomato\' },
    toppings: {
        pepperoni: { slug: \'pepperoni\', layerImg: \'...\', zindex: 400, coverage: \'whole\' }
    }
});

// List all instances
var ids = window.PizzaLayerAPI.getAllInstances();  // e.g. [\'pizza-1\', \'pizza-2\']

// Render pizza HTML via REST (async)
window.PizzaLayerAPI.renderPizza({
    crust: \'thin-crust\', sauce: \'tomato\', toppings: [\'pepperoni\', \'mushrooms\']
}).then(function(html) {
    document.getElementById(\'my-pizza\').innerHTML = html;
});

// Get a layer URL (async)
window.PizzaLayerAPI.getLayerUrl(\'topping\', \'pepperoni\').then(function(url) {
    myImg.src = url;
});

// Client-side static render (no server request)
window.PizzaLayerAPI.renderStatic(\'#my-container\', stateObject);'
); ?></pre>

		<!-- REST API ────────────────────────────────────────────────────── -->
		<h3>REST API Endpoints</h3>
		<p>All endpoints are under the <code>/wp-json/pizzalayer/v1/</code> namespace. Write endpoints require a valid WP nonce passed as <code>X-WP-Nonce</code> header.</p>

		<table class="plhelp-attr-table">
			<thead><tr><th>Method</th><th>Endpoint</th><th>Body / Params</th><th>Response</th></tr></thead>
			<tbody>
				<tr>
					<td><code>POST</code></td>
					<td><code>/pizzalayer/v1/render</code></td>
					<td><code>{ crust, sauce, cheese, toppings[], drizzle, cut, preset }</code></td>
					<td><code>{ html: "..." }</code> — full pizza stack HTML</td>
				</tr>
				<tr>
					<td><code>GET</code></td>
					<td><code>/pizzalayer/v1/layer-url</code></td>
					<td><code>?type=topping&amp;slug=pepperoni</code></td>
					<td><code>{ url: "https://..." }</code></td>
				</tr>
				<tr>
					<td><code>GET</code></td>
					<td><code>/pizzalayer/v1/layers</code></td>
					<td><code>?type=toppings</code></td>
					<td><code>[ { slug, title, layerImg, thumb }, ... ]</code></td>
				</tr>
			</tbody>
		</table>

		<!-- Namespace & Meta Keys ──────────────────────────────────────── -->
		<h3>PHP Namespace &amp; Class Map</h3>
		<pre class="plhelp-code"><?php echo esc_html(
'PizzaLayer\Admin\AdminMenu        — admin menu + submenu registration
PizzaLayer\Admin\AdminBar         — WP admin bar items
PizzaLayer\Admin\AdminHome        — dashboard home page
PizzaLayer\Admin\ContentHub       — unified content management (AJAX tab switcher)
PizzaLayer\Admin\Settings         — settings page (all options)
PizzaLayer\Admin\Help             — this help page
PizzaLayer\Admin\SetupGuide       — onboarding checklist
PizzaLayer\Admin\ShortcodeGenerator — visual shortcode builder
PizzaLayer\Admin\TemplateChoice   — template picker UI
PizzaLayer\PostTypes\PostTypeRegistrar — all 7 CPT registrations
PizzaLayer\Shortcodes\BuilderShortcode — [pizza_builder]
PizzaLayer\Shortcodes\StaticShortcode  — [pizza_static]
PizzaLayer\Shortcodes\LayerImageShortcode — [pizza_layer]
PizzaLayer\Template\TemplateLoader — template path resolution
PizzaLayer\API\RestApi            — REST route registration
PizzaLayer\Blocks\BlockRegistrar  — Gutenberg block registration'
); ?></pre>

		<h3>CPT Meta Keys</h3>
		<table class="plhelp-attr-table">
			<thead><tr><th>CPT</th><th>Meta key</th><th>Description</th></tr></thead>
			<tbody>
				<tr><td>All layer types</td><td><code>pzl_layer_image</code></td><td>Full-canvas layer image URL (used in the visual stack)</td></tr>
				<tr><td>Sizes</td><td><code>size_diameter_in</code></td><td>Pizza diameter in inches</td></tr>
				<tr><td>Sizes</td><td><code>size_area_sqin</code></td><td>Pizza area in square inches</td></tr>
			</tbody>
		</table>

		<div class="plhelp-info-box plhelp-info-box--dev">
			<span class="dashicons dashicons-admin-plugins"></span>
			<div>
				<strong>Building an add-on?</strong> Check for <code>class_exists('PizzaLayer\Plugin')</code> before hooking in. Use <code>pizzalayer_cpt_registered</code> as your init point to guarantee all CPTs are available. The public JS API is available on any page the builder loads — no additional enqueue needed.
			</div>
		</div>
	<?php }

	// ═══════════════════════════════════════════════════════════════════
	// STYLES
	// ═══════════════════════════════════════════════════════════════════
	private function render_styles(): void { ?>
	<style>
	/* ── Wrap ─────────────────────────────────────────────────────── */
	.plhelp-wrap { max-width: 1200px; }

	/* ── Header ───────────────────────────────────────────────────── */
	.plhelp-header {
		display: flex; align-items: center; justify-content: space-between;
		gap: 16px; flex-wrap: wrap;
		background: linear-gradient(135deg,#1a1e23,#2d3748);
		border-radius: 10px; padding: 22px 28px; margin-bottom: 20px;
	}
	.plhelp-header__title { margin: 0 0 4px; font-size: 22px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 10px; }
	.plhelp-header__title .dashicons { color: #ff6b35; font-size: 24px !important; width: 24px !important; height: 24px !important; }
	.plhelp-header__sub { margin: 0; color: #8d97a5; font-size: 13px; }
	.plhelp-header .button { display: inline-flex; align-items: center; gap: 5px; }
	.plhelp-header .dashicons { font-size: 14px !important; width: 14px !important; height: 14px !important; }

	/* ── Layout ───────────────────────────────────────────────────── */
	.plhelp-layout { display: flex; gap: 0; align-items: flex-start; background: #fff; border: 1px solid #e0e3e7; border-radius: 10px; overflow: hidden; }

	/* ── Left nav ─────────────────────────────────────────────────── */
	.plhelp-nav { width: 190px; flex-shrink: 0; background: #f8f9fa; border-right: 1px solid #e0e3e7; padding: 8px 0; align-self: stretch; }
	.plhelp-nav__item { display: flex; align-items: center; gap: 8px; padding: 10px 14px; text-decoration: none; color: #3c434a; font-size: 13px; border-left: 3px solid transparent; transition: background .12s, color .12s, border-color .12s; }
	.plhelp-nav__item:hover { background: #eef0f2; color: #1d2023; }
	.plhelp-nav__item--active { background: #fff; border-left-color: #ff6b35; color: #ff6b35; font-weight: 600; }
	.plhelp-nav__icon { font-size: 15px; width: 20px; text-align: center; }

	/* ── Content ──────────────────────────────────────────────────── */
	.plhelp-content { flex: 1; min-width: 0; padding: 28px 32px 40px; }
	.plhelp-section-title { margin: 0 0 18px; font-size: 20px; display: flex; align-items: center; gap: 10px; }
	.plhelp-lead { font-size: 14px; color: #3c434a; line-height: 1.7; margin: 0 0 24px; max-width: 740px; }
	.plhelp-content h3 { margin: 24px 0 10px; font-size: 15px; font-weight: 700; }
	.plhelp-content h4 { margin: 18px 0 8px; font-size: 13px; font-weight: 700; color: #646970; }
	.plhelp-content p { font-size: 13px; line-height: 1.65; margin: 0 0 12px; color: #3c434a; }

	/* ── Steps ────────────────────────────────────────────────────── */
	.plhelp-steps { display: flex; flex-direction: column; }
	.plhelp-step { display: flex; gap: 18px; align-items: flex-start; padding: 20px 0; border-bottom: 1px solid #f0f0f0; }
	.plhelp-step:last-child { border-bottom: none; }
	.plhelp-step__num { width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0; background: #ff6b35; color: #fff; font-size: 16px; font-weight: 700; display: flex; align-items: center; justify-content: center; margin-top: 2px; }
	.plhelp-step__body { flex: 1; }
	.plhelp-step__body h3 { margin: 0 0 8px; font-size: 15px; }
	.plhelp-step__body p { font-size: 13px; line-height: 1.65; color: #3c434a; margin: 0 0 10px; }
	.plhelp-step__body p:last-child { margin-bottom: 0; }

	/* ── Checklist ────────────────────────────────────────────────── */
	.plhelp-checklist { display: flex; flex-direction: column; gap: 6px; margin: 12px 0; }
	.plhelp-checklist label { display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer; color: #1d2023; }
	.plhelp-checklist input { margin: 0; }
	.plhelp-tip-inline { font-size: 12.5px !important; background: #fff8e6; border-left: 3px solid #f0b849; padding: 10px 12px; border-radius: 0 6px 6px 0; }

	/* ── Stack diagram ────────────────────────────────────────────── */
	.plhelp-stack-diagram { background: #f8f9fa; border: 1px solid #e0e3e7; border-radius: 8px; padding: 16px 20px; margin-bottom: 24px; }
	.plhelp-stack-diagram__label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #646970; margin-bottom: 10px; }
	.plhelp-stack-diagram__layers { display: flex; gap: 6px; flex-wrap: wrap; }
	.plhelp-stack-layer {
		display: flex; flex-direction: column; align-items: center; gap: 3px;
		padding: 10px 14px; border-radius: 6px; min-width: 80px;
		background: var(--color, #e0e3e7); background: color-mix(in srgb, var(--color) 15%, #f8f9fa);
		border: 1px solid color-mix(in srgb, var(--color) 40%, transparent);
		position: relative;
	}
	.plhelp-stack-layer__name { font-size: 12px; font-weight: 700; color: var(--color); }
	.plhelp-stack-layer__z { font-size: 10px; color: #646970; }

	/* ── Layer cards ──────────────────────────────────────────────── */
	.plhelp-layers-grid { display: flex; flex-direction: column; gap: 16px; }
	.plhelp-layer-card { border: 1px solid #e0e3e7; border-radius: 8px; overflow: hidden; }
	.plhelp-layer-card__head { display: flex; align-items: center; gap: 12px; padding: 14px 18px; background: #f8f9fa; border-left: 4px solid #e0e3e7; }
	.plhelp-layer-card__icon { font-size: 22px; flex-shrink: 0; }
	.plhelp-layer-card__head h3 { margin: 0 0 3px; font-size: 14px; }
	.plhelp-badge { font-size: 11px; font-weight: 600; padding: 2px 7px; border-radius: 4px; }
	.plhelp-layer-card__desc { margin: 12px 18px 8px; font-size: 13px; color: #3c434a; line-height: 1.6; }

	/* ── Fields table ─────────────────────────────────────────────── */
	.plhelp-fields-table { width: calc(100% - 36px); margin: 0 18px 12px; border-collapse: collapse; font-size: 12.5px; }
	.plhelp-fields-table th { background: #f0f0f1; padding: 6px 10px; text-align: left; font-weight: 600; }
	.plhelp-fields-table td { padding: 6px 10px; border-bottom: 1px solid #f0f0f0; vertical-align: top; }
	.plhelp-fields-table tr:last-child td { border-bottom: none; }
	.plhelp-fields-table code { background: #f0f0f1; padding: 1px 4px; border-radius: 3px; font-size: 11px; }

	/* ── Attribute table ──────────────────────────────────────────── */
	.plhelp-attr-table { width: 100%; border-collapse: collapse; font-size: 13px; margin: 12px 0; }
	.plhelp-attr-table th { background: #f8f9fa; padding: 8px 12px; text-align: left; font-weight: 600; border-bottom: 2px solid #e0e3e7; }
	.plhelp-attr-table td { padding: 8px 12px; border-bottom: 1px solid #f0f0f0; vertical-align: top; line-height: 1.55; }
	.plhelp-attr-table tr:last-child td { border-bottom: none; }
	.plhelp-attr-table code { background: #f0f0f1; padding: 1px 5px; border-radius: 3px; font-size: 11.5px; white-space: nowrap; }

	/* ── Tips ─────────────────────────────────────────────────────── */
	.plhelp-tip { margin: 0 18px 14px; padding: 10px 14px; background: #fff8e6; border-left: 3px solid #f0b849; border-radius: 0 6px 6px 0; font-size: 12.5px; color: #3c434a; line-height: 1.6; }

	/* ── Shortcode blocks ─────────────────────────────────────────── */
	.plhelp-sc-block { border: 1px solid #e0e3e7; border-radius: 8px; padding: 20px 22px; margin-bottom: 20px; }
	.plhelp-sc-block__head { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
	.plhelp-sc-name { font-size: 16px; background: #f0f0f1; padding: 4px 10px; border-radius: 5px; }
	.plhelp-sc-tag { font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 4px; background: #dce8f7; color: #2271b1; }
	.plhelp-sc-tag--green  { background: #d1f5dc; color: #00a32a; }
	.plhelp-sc-tag--purple { background: #ede8ff; color: #8c5af8; }
	.plhelp-sc-block h4 { margin: 16px 0 8px; font-size: 13px; }

	/* ── Code blocks ──────────────────────────────────────────────── */
	.plhelp-code { background: #1a1e23; color: #e2e8f0; padding: 14px 18px; border-radius: 6px; font-size: 12.5px; line-height: 1.6; overflow-x: auto; white-space: pre; margin: 10px 0; }

	/* ── Shape grid ───────────────────────────────────────────────── */
	.plhelp-shape-grid { display: flex; gap: 14px; flex-wrap: wrap; margin-bottom: 24px; }
	.plhelp-shape-card { flex: 1 1 140px; text-align: center; padding: 18px 14px; border: 1px solid #e0e3e7; border-radius: 8px; font-size: 12px; }
	.plhelp-shape-card strong { display: block; margin: 10px 0 4px; font-size: 13px; }
	.plhelp-shape-card code { display: block; background: #f0f0f1; padding: 2px 6px; border-radius: 3px; margin-bottom: 8px; }
	.plhelp-shape-card p { margin: 0; color: #646970; line-height: 1.5; }
	.plhelp-shape-preview { width: 70px; height: 70px; margin: 0 auto; background: linear-gradient(135deg,#ff8c42,#ff5722); box-shadow: 0 3px 12px rgba(0,0,0,.2); }

	/* ── FAQ ──────────────────────────────────────────────────────── */
	.plhelp-faq { display: flex; flex-direction: column; gap: 8px; }
	.plhelp-faq__item { border: 1px solid #e0e3e7; border-radius: 8px; overflow: hidden; }
	.plhelp-faq__item[open] { border-color: #2271b1; }
	.plhelp-faq__q { display: flex; align-items: center; gap: 10px; padding: 14px 18px; cursor: pointer; list-style: none; font-size: 13.5px; font-weight: 600; color: #1d2023; background: #f8f9fa; transition: background .12s; }
	.plhelp-faq__item[open] .plhelp-faq__q { background: #f0f5ff; color: #2271b1; }
	.plhelp-faq__q::-webkit-details-marker { display: none; }
	.plhelp-faq__arrow { font-size: 10px; transition: transform .2s; flex-shrink: 0; }
	.plhelp-faq__item[open] .plhelp-faq__arrow { transform: rotate(90deg); }
	.plhelp-faq__a { padding: 14px 18px 16px 38px; font-size: 13px; line-height: 1.7; color: #3c434a; border-top: 1px solid #e0e3e7; }
	.plhelp-faq__a strong { font-weight: 600; }
	.plhelp-faq__a code { background: #f0f0f1; padding: 1px 5px; border-radius: 3px; font-size: 11.5px; }

	/* ── Alerts ───────────────────────────────────────────────────── */
	.plhelp-alert { border-radius: 6px; padding: 12px 14px; margin: 12px 0; font-size: 13px; line-height: 1.65; }
	.plhelp-alert--warn { background: #fff8e6; border-left: 3px solid #f0b849; }
	.plhelp-alert ul { margin: 6px 0 0 18px; padding: 0; }
	.plhelp-alert li { margin-bottom: 4px; }
	.plhelp-alert code { background: rgba(0,0,0,.06); padding: 1px 4px; border-radius: 3px; font-size: 11.5px; }

	/* ── Info boxes ───────────────────────────────────────────────── */
	.plhelp-info-box { display: flex; gap: 12px; align-items: flex-start; background: #f0f5ff; border: 1px solid #b9d0f5; border-radius: 8px; padding: 14px 16px; margin: 20px 0; font-size: 13px; line-height: 1.65; }
	.plhelp-info-box--dev { background: #f5f0ff; border-color: #c9b5f5; }
	.plhelp-info-box--warn { background: #fff8e6; border-color: #f0b849; }
	.plhelp-info-box .dashicons { flex-shrink: 0; margin-top: 2px; color: #2271b1; }
	.plhelp-info-box--dev .dashicons { color: #8c5af8; }
	.plhelp-info-box--warn .dashicons { color: #b35309; }
	.plhelp-info-box strong { font-weight: 600; }
	.plhelp-info-box a { color: #2271b1; }

	/* ── Lists ────────────────────────────────────────────────────── */
	.plhelp-list { margin: 8px 0 12px 20px; padding: 0; font-size: 13px; color: #3c434a; line-height: 1.7; }
	.plhelp-list li { margin-bottom: 4px; }
	.plhelp-list--numbered { list-style: decimal; }
	.plhelp-list code { background: #f0f0f1; padding: 1px 5px; border-radius: 3px; font-size: 11.5px; }

	/* ── Developer banner ─────────────────────────────────────────── */
	.plhelp-dev-banner { background: linear-gradient(135deg,#1a1e23,#2d3748); color: #e2e8f0; border-radius: 8px; padding: 20px 24px; margin-bottom: 24px; font-size: 13.5px; line-height: 1.65; }
	.plhelp-dev-banner__badge { display: inline-flex; align-items: center; gap: 6px; background: #ff6b3530; color: #ff8c42; border: 1px solid #ff6b3560; border-radius: 4px; padding: 3px 10px; font-size: 12px; font-weight: 600; margin-bottom: 10px; }
	.plhelp-dev-banner p { color: #8d97a5; margin: 0; font-size: 13px; }

	@media (max-width: 782px) {
		.plhelp-layout { flex-direction: column; }
		.plhelp-nav { width: 100%; border-right: none; border-bottom: 1px solid #e0e3e7; display: flex; flex-wrap: wrap; padding: 4px; }
		.plhelp-nav__item { border-left: none; border-bottom: 3px solid transparent; padding: 7px 10px; font-size: 12px; }
		.plhelp-nav__item--active { border-bottom-color: #ff6b35; background: transparent; }
	}
	</style>
	<?php }
}
