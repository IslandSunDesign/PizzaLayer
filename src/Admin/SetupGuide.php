<?php
namespace PizzaLayer\Admin;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * PizzaLayer Setup Guide — step-by-step automated checklist.
 */
class SetupGuide {

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) { return; }

		// Handle checklist item tick via POST
		if ( isset( $_POST['pizzalayer_setup_done'], $_POST['_wpnonce'] )
		     && wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'pizzalayer_setup_checklist' ) ) {
			$done = get_option( 'pizzalayer_setup_done', [] );
			$key  = sanitize_key( $_POST['pizzalayer_setup_done'] );
			if ( isset( $_POST['checked'] ) && $_POST['checked'] === '1' ) {
				$done[ $key ] = true;
			} else {
				unset( $done[ $key ] );
			}
			update_option( 'pizzalayer_setup_done', $done );
		}

		$done = get_option( 'pizzalayer_setup_done', [] );

		// ── Live stats for auto-detection ───────────────────────────────
		$stats = [
			'crusts'   => (int) ( wp_count_posts( 'pizzalayer_crusts'   )->publish ?? 0 ),
			'sauces'   => (int) ( wp_count_posts( 'pizzalayer_sauces'   )->publish ?? 0 ),
			'cheeses'  => (int) ( wp_count_posts( 'pizzalayer_cheeses'  )->publish ?? 0 ),
			'toppings' => (int) ( wp_count_posts( 'pizzalayer_toppings' )->publish ?? 0 ),
			'drizzles' => (int) ( wp_count_posts( 'pizzalayer_drizzles' )->publish ?? 0 ),
			'cuts'     => (int) ( wp_count_posts( 'pizzalayer_cuts'     )->publish ?? 0 ),
		];

		$has_template = get_option( 'pizzalayer_setting_global_template', '' ) !== '';
		$has_defaults = get_option( 'pizzalayer_setting_crust_defaultcrust', '' ) !== '';

		// ── Checklist definition ─────────────────────────────────────────
		$checklist = [
			[
				'key'        => 'install',
				'label'      => 'Install &amp; activate PizzaLayer',
				'desc'       => 'You\'re reading this — done!',
				'auto_done'  => true,
				'link'       => null,
				'link_label' => null,
			],
			[
				'key'        => 'crusts',
				'label'      => 'Add at least one Crust',
				'desc'       => 'Go to PizzaLayer → Crusts and publish a crust with a layer image.',
				'auto_done'  => $stats['crusts'] > 0,
				'link'       => admin_url( 'post-new.php?post_type=pizzalayer_crusts' ),
				'link_label' => 'Add Crust',
				'count'      => $stats['crusts'],
			],
			[
				'key'        => 'sauces',
				'label'      => 'Add at least one Sauce',
				'desc'       => 'Go to PizzaLayer → Sauces and publish a sauce with a layer image.',
				'auto_done'  => $stats['sauces'] > 0,
				'link'       => admin_url( 'post-new.php?post_type=pizzalayer_sauces' ),
				'link_label' => 'Add Sauce',
				'count'      => $stats['sauces'],
			],
			[
				'key'        => 'cheeses',
				'label'      => 'Add at least one Cheese',
				'desc'       => 'Go to PizzaLayer → Cheeses and publish a cheese with a layer image.',
				'auto_done'  => $stats['cheeses'] > 0,
				'link'       => admin_url( 'post-new.php?post_type=pizzalayer_cheeses' ),
				'link_label' => 'Add Cheese',
				'count'      => $stats['cheeses'],
			],
			[
				'key'        => 'toppings',
				'label'      => 'Add your Toppings',
				'desc'       => 'Toppings are the heart of the builder — add as many as your menu needs.',
				'auto_done'  => $stats['toppings'] > 0,
				'link'       => admin_url( 'post-new.php?post_type=pizzalayer_toppings' ),
				'link_label' => 'Add Topping',
				'count'      => $stats['toppings'],
			],
			[
				'key'        => 'drizzles',
				'label'      => 'Add Drizzles <em>(optional)</em>',
				'desc'       => 'Finishing touch layers — hot honey, balsamic, ranch. Optional but delightful.',
				'auto_done'  => $stats['drizzles'] > 0 || isset( $done['drizzles'] ),
				'optional'   => true,
				'link'       => admin_url( 'post-new.php?post_type=pizzalayer_drizzles' ),
				'link_label' => 'Add Drizzle',
				'count'      => $stats['drizzles'],
			],
			[
				'key'        => 'cuts',
				'label'      => 'Add Cut styles <em>(optional)</em>',
				'desc'       => 'Slice overlay layers — triangle, square, party, whole. Optional.',
				'auto_done'  => $stats['cuts'] > 0 || isset( $done['cuts'] ),
				'optional'   => true,
				'link'       => admin_url( 'post-new.php?post_type=pizzalayer_cuts' ),
				'link_label' => 'Add Cut Style',
				'count'      => $stats['cuts'],
			],
			[
				'key'        => 'template',
				'label'      => 'Choose a Template',
				'desc'       => 'Pick the visual theme for your pizza builder in PizzaLayer → Template.',
				'auto_done'  => $has_template,
				'link'       => admin_url( 'admin.php?page=pizzalayer-template' ),
				'link_label' => 'Choose Template',
			],
			[
				'key'        => 'settings',
				'label'      => 'Configure Plugin Settings',
				'desc'       => 'Set your default crust, sauce, max toppings and other options in PizzaLayer → Settings.',
				'auto_done'  => $has_defaults,
				'link'       => admin_url( 'admin.php?page=pizzalayer-settings' ),
				'link_label' => 'Open Settings',
			],
			[
				'key'        => 'shortcode',
				'label'      => 'Embed the Builder on a page',
				'desc'       => 'Use the Shortcode Generator to get your <code>[pizza_builder]</code> shortcode, then add it to any page.',
				'auto_done'  => isset( $done['shortcode'] ),
				'link'       => admin_url( 'admin.php?page=pizzalayer-shortcodes' ),
				'link_label' => 'Shortcode Generator',
			],
			[
				'key'        => 'test',
				'label'      => 'Place a test order end-to-end',
				'desc'       => 'Visit your builder page as a customer and confirm layers display correctly.',
				'auto_done'  => isset( $done['test'] ),
				'link'       => home_url( '/' ),
				'link_label' => 'View Site',
			],
		];

		$done_count  = count( array_filter( $checklist, fn( $i ) => $i['auto_done'] ?? false ) );
		$total_count = count( $checklist );
		$pct         = (int) round( $done_count / $total_count * 100 );

		// ── Layer guide tabs ─────────────────────────────────────────────
		$layer_tabs = [
			'crusts' => [
				'label' => 'Crusts',
				'icon'  => 'dashicons-tag',
				'intro' => 'Crusts are the foundation of every pizza in the builder. Add at least one crust before testing the visualizer.',
				'steps' => [
					'Go to <strong>PizzaLayer → Crusts</strong> and click <strong>Add New</strong>.',
					'Enter a clear title — e.g. <code>Thin Crust</code>, <code>Stuffed Crust</code>, <code>Gluten Free</code>.',
					'Upload a <strong>Crust Layer Image</strong> (transparent PNG, square canvas recommended at 800×800 px) in the SCF field named <code>crust_layer_image</code>.',
					'Optionally upload a <strong>Crust Image</strong> (<code>crust_image</code>) for the selection card thumbnail.',
					'Fill in the <strong>Price Grid</strong> with size and pricing rows if you need per-crust pricing.',
					'Click <strong>Publish</strong>. Repeat for each crust option.',
				],
				'tip'   => 'Use transparent PNGs on a consistent square canvas (800×800 px) for the cleanest layer stacking.',
				'cpt'   => 'crusts',
			],
			'sauces' => [
				'label' => 'Sauces',
				'icon'  => 'dashicons-admin-generic',
				'intro' => 'Sauces render as a layer directly on top of the crust. Add at least one to enable sauce selection in the builder.',
				'steps' => [
					'Go to <strong>PizzaLayer → Sauces</strong> and click <strong>Add New</strong>.',
					'Enter a title — e.g. <code>Classic Tomato</code>, <code>Garlic White</code>, <code>BBQ</code>.',
					'Upload a <strong>Sauce Layer Image</strong> (<code>sauce_layer_image</code>) — the visual overlay on the pizza circle.',
					'Optionally add a <strong>Sauce Image</strong> (<code>sauce_image</code>) for the selection card thumbnail.',
					'Set pricing in the <strong>Price Grid</strong> if sauces have an upcharge.',
					'Click <strong>Publish</strong>.',
				],
				'tip'   => 'Semi-transparent layer images with soft edges look most natural when layered on top of a crust.',
				'cpt'   => 'sauces',
			],
			'cheeses' => [
				'label' => 'Cheeses',
				'icon'  => 'dashicons-category',
				'intro' => 'Cheeses are a separate layer type that sits between the sauce and toppings — great for offering Mozzarella, Vegan, Provolone, and more.',
				'steps' => [
					'Go to <strong>PizzaLayer → Cheeses</strong> and click <strong>Add New</strong>.',
					'Give it a clear name — e.g. <code>Mozzarella</code>, <code>Provolone</code>, <code>Dairy Free</code>.',
					'Upload a <strong>Cheese Layer Image</strong> (<code>cheese_layer_image</code>) — the visual overlay.',
					'Optionally add a card thumbnail (<code>cheese_image</code>) and price grid rows.',
					'Click <strong>Publish</strong>.',
				],
				'tip'   => 'A subtle melt pattern with a golden edge makes cheese images look convincingly realistic.',
				'cpt'   => 'cheeses',
			],
			'toppings' => [
				'label' => 'Toppings',
				'icon'  => 'dashicons-star-filled',
				'intro' => 'Toppings are the heart of the builder. Each one gets its own layer image, price data, and supports whole / half / quarter coverage placement.',
				'steps' => [
					'Go to <strong>PizzaLayer → Toppings</strong> and click <strong>Add New</strong>.',
					'Enter a name — e.g. <code>Pepperoni</code>, <code>Mushrooms</code>, <code>Jalapeños</code>.',
					'Upload a <strong>Topping Layer Image</strong> (<code>topping_layer_image</code>) — this stacks on the pizza in real time when selected.',
					'Optionally add a <strong>Topping Image</strong> (<code>topping_image</code>) for the card thumbnail.',
					'Set the <strong>Price Grid</strong> pricing per size and fraction.',
					'Set a <strong>Max Toppings</strong> limit in <em>PizzaLayer → Settings</em>.',
					'Click <strong>Publish</strong>. Repeat for each topping.',
				],
				'tip'   => 'Use consistent 500×500 px transparent PNGs for all toppings — this keeps layers perfectly aligned across templates.',
				'cpt'   => 'toppings',
			],
			'drizzles' => [
				'label' => 'Drizzles',
				'icon'  => 'dashicons-admin-customizer',
				'intro' => 'Drizzles are optional finishing layers that appear on top of everything — balsamic glaze, hot honey, ranch swirl, etc.',
				'steps' => [
					'Go to <strong>PizzaLayer → Drizzles</strong> and click <strong>Add New</strong>.',
					'Enter a name — e.g. <code>Hot Honey</code>, <code>Balsamic</code>.',
					'Upload a <strong>Drizzle Layer Image</strong> (<code>drizzle_layer_image</code>).',
					'Add a card thumbnail (<code>drizzle_image</code>) and optional price grid rows.',
					'Click <strong>Publish</strong>.',
				],
				'tip'   => 'Asymmetric, flowing drizzle patterns look more handcrafted and appetizing than perfectly symmetrical ones.',
				'cpt'   => 'drizzles',
			],
			'cuts' => [
				'label' => 'Cuts',
				'icon'  => 'dashicons-editor-table',
				'intro' => 'Cut styles render as an overlay on the final pizza — triangle slices, square cuts, party-style, or whole.',
				'steps' => [
					'Go to <strong>PizzaLayer → Cuts</strong> and click <strong>Add New</strong>.',
					'Enter a name — e.g. <code>8 Slices</code>, <code>Square Cut</code>, <code>Party Style</code>.',
					'Upload a <strong>Cut Layer Image</strong> (<code>cut_layer_image</code>) — typically a thin line graphic on a transparent background.',
					'Click <strong>Publish</strong>.',
				],
				'tip'   => 'Keep cut line images subtle — a low-opacity thin line lets the toppings beneath remain the star.',
				'cpt'   => 'cuts',
			],
			'settings' => [
				'label' => 'Settings',
				'icon'  => 'dashicons-admin-settings',
				'intro' => 'Fine-tune PizzaLayer\'s behavior: set defaults, max toppings, template, and display options.',
				'steps' => [
					'Open <strong>PizzaLayer → Settings</strong>.',
					'Set your <strong>Default Crust</strong>, <strong>Default Sauce</strong>, and <strong>Default Cheese</strong> — these pre-load in the builder.',
					'Set <strong>Max Toppings</strong> to limit how many toppings a customer can add.',
					'Configure <strong>Pizza display size</strong>, border, and topping fraction options.',
					'Set a <strong>Demo Notice</strong> or custom <strong>Help Screen</strong> content if needed.',
					'Save all settings.',
				],
				'tip'   => 'Setting sensible defaults (pre-selected crust and sauce) reduces friction and helps customers start building faster.',
				'cpt'   => null,
			],
			'shortcode' => [
				'label' => 'Embed',
				'icon'  => 'dashicons-editor-code',
				'intro' => 'Once your content is populated, embed the builder on any page using a shortcode.',
				'steps' => [
					'Go to <strong>PizzaLayer → Shortcode Generator</strong>.',
					'Configure your builder options — template, max toppings, default layers, visible tabs.',
					'Copy the generated <code>[pizza_builder]</code> shortcode.',
					'Paste it into any WordPress page or post using the Block Editor or Classic Editor.',
					'Preview the page to confirm layers load and the visualizer responds to selections.',
				],
				'tip'   => 'You can place multiple builders on the same page by giving each a unique <code>id</code> attribute: <code>[pizza_builder id="pizza-1"]</code>.',
				'cpt'   => null,
			],
		];

		?>
		<div class="wrap psg-wrap">

		<?php $this->render_styles(); ?>

		<!-- ══ Header ══════════════════════════════════════════════════ -->
		<div class="psg-header">
			<span class="dashicons dashicons-welcome-learn-more psg-header__icon"></span>
			<div>
				<h1 class="psg-header__title">Setup Guide</h1>
				<p class="psg-header__sub">Everything you need to get PizzaLayer up and running — in the right order.</p>
			</div>
		</div>

		<!-- ══ Progress bar ════════════════════════════════════════════ -->
		<div class="psg-card psg-progress-card">
			<div class="psg-progress-bar-wrap">
				<div class="psg-progress-bar" style="width:<?php echo esc_attr( (string) $pct ); ?>%"></div>
			</div>
			<div class="psg-progress-labels">
				<span><?php printf( esc_html__( '%d of %d steps complete', 'pizzalayer' ), $done_count, $total_count ); ?></span>
				<span class="psg-pct"><?php echo esc_html( (string) $pct ); ?>%</span>
			</div>
		</div>

		<!-- ══ Checklist ════════════════════════════════════════════════ -->
		<div class="psg-card">
			<div class="psg-card__head">
				<h2><span class="dashicons dashicons-yes-alt"></span> Setup Checklist</h2>
				<p>Work through these steps in order. Auto-detected items update as you add content.</p>
			</div>
			<form method="post" action="">
				<?php wp_nonce_field( 'pizzalayer_setup_checklist' ); ?>
				<ol class="psg-checklist">
				<?php foreach ( $checklist as $idx => $item ) :
					$is_done = $item['auto_done'] ?? false;
					$optional = $item['optional'] ?? false;
					$auto     = ( $item['auto_done'] ?? false ) && $item['key'] !== 'test' && $item['key'] !== 'shortcode';
				?>
					<li class="psg-checklist__item<?php echo $is_done ? ' psg-checklist__item--done' : ''; ?><?php echo $optional ? ' psg-checklist__item--optional' : ''; ?>">
						<div class="psg-cl-status">
							<?php if ( $is_done ) : ?>
								<span class="psg-cl-check psg-cl-check--done dashicons dashicons-yes-alt"></span>
							<?php else : ?>
								<span class="psg-cl-check psg-cl-check--pending dashicons dashicons-marker"></span>
							<?php endif; ?>
						</div>
						<div class="psg-cl-body">
							<div class="psg-cl-title">
								<?php echo wp_kses_post( $item['label'] ); ?>
								<?php if ( isset( $item['count'] ) && $item['count'] > 0 ) : ?>
									<span class="psg-cl-badge"><?php echo esc_html( (string) $item['count'] ); ?> added</span>
								<?php endif; ?>
								<?php if ( $optional ) : ?>
									<span class="psg-cl-opt-badge">optional</span>
								<?php endif; ?>
							</div>
							<div class="psg-cl-desc"><?php echo wp_kses_post( $item['desc'] ); ?></div>
						</div>
						<div class="psg-cl-actions">
							<?php if ( $item['link'] && ! $auto ) : ?>
							<a href="<?php echo esc_url( $item['link'] ); ?>" class="button button-small">
								<?php echo esc_html( $item['link_label'] ?? 'Go' ); ?> →
							</a>
							<?php elseif ( $item['link'] ) : ?>
							<a href="<?php echo esc_url( $item['link'] ); ?>" class="button button-small button-secondary">
								<?php echo esc_html( $item['link_label'] ?? 'View' ); ?>
							</a>
							<?php endif; ?>
							<?php if ( ! $auto && ! $is_done ) : ?>
							<button type="submit" name="pizzalayer_setup_done" value="<?php echo esc_attr( $item['key'] ); ?>" class="button button-small psg-mark-done">
								<input type="hidden" name="checked" value="1">Mark done
							</button>
							<?php elseif ( ! $auto && $is_done ) : ?>
							<button type="submit" name="pizzalayer_setup_done" value="<?php echo esc_attr( $item['key'] ); ?>" class="button button-small psg-mark-undone">
								<input type="hidden" name="checked" value="0">Undo
							</button>
							<?php endif; ?>
						</div>
					</li>
				<?php endforeach; ?>
				</ol>
			</form>
		</div>

		<!-- ══ Layer-by-layer guide tabs ══════════════════════════════ -->
		<div class="psg-card psg-card--tabs">
			<div class="psg-card__head">
				<h2><span class="dashicons dashicons-category"></span> Layer-by-Layer Setup Guide</h2>
				<p>Select a section to see step-by-step instructions for setting it up.</p>
			</div>

			<nav class="psg-tabnav" role="tablist">
				<?php $first = true; foreach ( $layer_tabs as $slug => $tab ) : ?>
				<button class="psg-tab<?php echo $first ? ' psg-tab--active' : ''; ?>"
				        data-tab="<?php echo esc_attr( $slug ); ?>"
				        role="tab" aria-selected="<?php echo $first ? 'true' : 'false'; ?>"
				        aria-controls="psg-panel-<?php echo esc_attr( $slug ); ?>">
					<span class="dashicons <?php echo esc_attr( $tab['icon'] ); ?>"></span>
					<?php echo esc_html( $tab['label'] ); ?>
				</button>
				<?php $first = false; endforeach; ?>
			</nav>

			<div class="psg-panels">
				<?php $first = true; foreach ( $layer_tabs as $slug => $tab ) : ?>
				<div class="psg-panel<?php echo $first ? ' psg-panel--active' : ''; ?>"
				     id="psg-panel-<?php echo esc_attr( $slug ); ?>" role="tabpanel">
					<p class="psg-panel__intro"><?php echo esc_html( $tab['intro'] ); ?></p>
					<ol class="psg-steps">
						<?php foreach ( $tab['steps'] as $step ) : ?>
						<li class="psg-steps__item"><?php echo wp_kses_post( $step ); ?></li>
						<?php endforeach; ?>
					</ol>
					<div class="psg-panel__tip">
						<span class="dashicons dashicons-lightbulb"></span>
						<?php echo esc_html( $tab['tip'] ); ?>
					</div>
					<?php if ( $tab['cpt'] ) : ?>
					<div class="psg-panel__actions">
						<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=pizzalayer_' . $tab['cpt'] ) ); ?>" class="button">
							<span class="dashicons dashicons-list-view"></span> View All <?php echo esc_html( $tab['label'] ); ?>
						</a>
						<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=pizzalayer_' . $tab['cpt'] ) ); ?>" class="button button-primary">
							<span class="dashicons dashicons-plus-alt2"></span> Add New <?php echo esc_html( rtrim( $tab['label'], 's' ) ); ?>
						</a>
					</div>
					<?php elseif ( $slug === 'settings' ) : ?>
					<div class="psg-panel__actions">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=pizzalayer-settings' ) ); ?>" class="button button-primary">
							<span class="dashicons dashicons-admin-settings"></span> Open Settings
						</a>
					</div>
					<?php elseif ( $slug === 'shortcode' ) : ?>
					<div class="psg-panel__actions">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=pizzalayer-shortcodes' ) ); ?>" class="button button-primary">
							<span class="dashicons dashicons-editor-code"></span> Open Shortcode Generator
						</a>
					</div>
					<?php endif; ?>
				</div>
				<?php $first = false; endforeach; ?>
			</div>
		</div>

		<!-- ══ Help footer ════════════════════════════════════════════ -->
		<div class="psg-card psg-card--help">
			<span class="dashicons dashicons-sos"></span>
			<div>
				<h3>Need help?</h3>
				<p>Check the documentation or reach out through <a href="https://islandsundesign.com" target="_blank" rel="noopener">IslandSunDesign.com</a>.</p>
			</div>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=pizzalayer' ) ); ?>" class="button">← Back to Dashboard</a>
		</div>

		</div><!-- /.wrap -->

		<?php
	}

	private function render_styles(): void { ?>
	<style>
	.psg-wrap { max-width: 960px; }
	.psg-header { display:flex; align-items:center; gap:16px; background:linear-gradient(135deg,#1a1e23,#2d3748); color:#fff; border-radius:10px; padding:22px 28px; margin-bottom:20px; }
	.psg-header__icon { font-size:36px !important; width:36px !important; height:36px !important; color:#ff6b35; flex-shrink:0; }
	.psg-header__title { margin:0; font-size:22px; font-weight:700; color:#fff; }
	.psg-header__sub { margin:3px 0 0; color:#8d97a5; font-size:13px; }
	.psg-card { background:#fff; border:1px solid #e0e3e7; border-radius:10px; margin-bottom:20px; overflow:hidden; }
	.psg-card__head { padding:18px 24px 12px; border-bottom:1px solid #f0f0f0; }
	.psg-card__head h2 { margin:0 0 4px; font-size:15px; display:flex; align-items:center; gap:8px; }
	.psg-card__head p { margin:0; color:#646970; font-size:13px; }
	/* Progress */
	.psg-progress-card { padding:18px 24px; }
	.psg-progress-bar-wrap { height:10px; background:#e0e3e7; border-radius:99px; overflow:hidden; margin-bottom:8px; }
	.psg-progress-bar { height:100%; background:linear-gradient(90deg,#2271b1,#00a32a); border-radius:99px; transition:width .4s ease; }
	.psg-progress-labels { display:flex; justify-content:space-between; font-size:12px; color:#646970; }
	.psg-pct { font-weight:700; color:#2271b1; }
	/* Checklist */
	.psg-checklist { margin:0; padding:0 0 8px; list-style:none; }
	.psg-checklist__item { display:flex; align-items:flex-start; gap:12px; padding:12px 20px; border-bottom:1px solid #f5f5f5; transition:background .15s; }
	.psg-checklist__item:last-child { border-bottom:none; }
	.psg-checklist__item--done { background:#f6fdf6; }
	.psg-checklist__item--optional { opacity:.85; }
	.psg-cl-status { flex-shrink:0; padding-top:2px; }
	.psg-cl-check--done { color:#00a32a; font-size:20px !important; width:20px !important; height:20px !important; }
	.psg-cl-check--pending { color:#c3c4c7; font-size:20px !important; width:20px !important; height:20px !important; }
	.psg-cl-body { flex:1; }
	.psg-cl-title { font-size:13px; font-weight:600; color:#1d2023; margin-bottom:3px; display:flex; align-items:center; gap:6px; flex-wrap:wrap; }
	.psg-cl-desc { font-size:12px; color:#646970; }
	.psg-cl-badge { background:#dce8f7; color:#2271b1; border-radius:99px; font-size:11px; font-weight:700; padding:1px 7px; }
	.psg-cl-opt-badge { background:#f0f0f0; color:#787c82; border-radius:99px; font-size:10px; padding:1px 6px; }
	.psg-cl-actions { flex-shrink:0; display:flex; gap:6px; align-items:center; flex-wrap:wrap; }
	.psg-mark-done { color:#00a32a; border-color:#00a32a; }
	.psg-mark-undone { color:#787c82; font-size:11px !important; }
	/* Tab nav */
	.psg-tabnav { display:flex; flex-wrap:wrap; border-bottom:2px solid #e0e3e7; padding:0 16px; background:#f8f9fa; }
	.psg-tab { display:flex; align-items:center; gap:6px; padding:10px 14px; border:none; border-bottom:2px solid transparent; background:transparent; cursor:pointer; font-size:13px; font-weight:500; color:#646970; white-space:nowrap; margin-bottom:-2px; transition:color .15s,border-color .15s; }
	.psg-tab:hover { color:#1d2023; }
	.psg-tab--active { color:#2271b1; border-bottom-color:#2271b1; font-weight:600; }
	.psg-tab .dashicons { font-size:14px !important; width:14px !important; height:14px !important; }
	/* Panels */
	.psg-panels { padding:0; }
	.psg-panel { display:none; padding:22px 24px 24px; }
	.psg-panel--active { display:block; }
	.psg-panel__intro { margin:0 0 16px; font-size:14px; color:#3c434a; padding:12px 16px; background:#f8f9fa; border-left:4px solid #2271b1; border-radius:0 6px 6px 0; }
	.psg-steps { margin:0 0 18px; padding-left:0; list-style:none; counter-reset:psg-step; }
	.psg-steps__item { display:flex; align-items:flex-start; gap:12px; padding:10px 0; border-bottom:1px solid #f0f0f0; font-size:13px; counter-increment:psg-step; }
	.psg-steps__item:last-child { border-bottom:none; }
	.psg-steps__item::before { content:counter(psg-step); display:flex; align-items:center; justify-content:center; width:24px; height:24px; border-radius:50%; background:#dce8f7; color:#2271b1; font-size:11px; font-weight:700; flex-shrink:0; margin-top:1px; }
	.psg-steps__item code { background:#f0f0f1; padding:1px 5px; border-radius:3px; font-size:12px; }
	.psg-panel__tip { display:flex; align-items:flex-start; gap:10px; background:#fffbf0; border:1px solid #f0b849; border-radius:6px; padding:12px 14px; font-size:13px; color:#3c434a; margin-bottom:18px; }
	.psg-panel__tip .dashicons { color:#f0b849; flex-shrink:0; font-size:16px !important; width:16px !important; height:16px !important; }
	.psg-panel__actions { display:flex; gap:8px; flex-wrap:wrap; }
	.psg-panel__actions .button { display:inline-flex; align-items:center; gap:6px; }
	.psg-panel__actions .dashicons { font-size:14px !important; width:14px !important; height:14px !important; }
	/* Help */
	.psg-card--help { display:flex; align-items:center; gap:20px; flex-wrap:wrap; padding:18px 24px; background:#f6f7f7; border-top:none; }
	.psg-card--help .dashicons { font-size:28px !important; width:28px !important; height:28px !important; color:#646970; flex-shrink:0; }
	.psg-card--help h3 { margin:0 0 3px; font-size:14px; }
	.psg-card--help p { margin:0; font-size:13px; color:#646970; }
	.psg-card--help > div { flex:1; }
	</style>
	<?php }
}
