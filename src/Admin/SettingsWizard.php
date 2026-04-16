<?php
namespace PizzaLayer\Admin;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * PizzaLayer Settings Wizard
 *
 * A friendly, novice-oriented step-through of the key plugin settings.
 * Designed for small business owners and non-developers.
 * Each step maps to a real Settings option and can be marked done or skipped.
 */
class SettingsWizard {

	/** Steps stored persistently so the user can resume any time. */
	private function get_done(): array {
		return (array) get_option( 'pizzalayer_wizard_done', [] );
	}

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) { return; }

		// ── Handle step save ─────────────────────────────────────────────
		if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'pizzalayer_wizard' ) ) {

			// Mark/unmark step done
			if ( isset( $_POST['pzwiz_mark_done'] ) ) {
				$done_arr = $this->get_done();
				$key = sanitize_key( $_POST['pzwiz_mark_done'] );
				$state = ( isset( $_POST['pzwiz_state'] ) && $_POST['pzwiz_state'] === '0' ) ? false : true;
				if ( $state ) {
					$done_arr[ $key ] = true;
				} else {
					unset( $done_arr[ $key ] );
				}
				update_option( 'pizzalayer_wizard_done', $done_arr );
			}

			// Save wizard step settings
			if ( isset( $_POST['pzwiz_save'] ) ) {
				$this->save_step( sanitize_key( $_POST['pzwiz_save'] ) );
			}

			// Reset wizard
			if ( isset( $_POST['pzwiz_reset'] ) ) {
				delete_option( 'pizzalayer_wizard_done' );
			}

			wp_safe_redirect( remove_query_arg( [] ) );
			exit;
		}

		$done       = $this->get_done();
		$active_tab = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : '';

		// ── Load CPT options for dropdowns ────────────────────────────────
		$q        = [ 'posts_per_page' => -1, 'post_status' => 'publish', 'orderby' => 'title', 'order' => 'ASC' ];
		$crusts   = get_posts( array_merge( $q, [ 'post_type' => 'pizzalayer_crusts' ] ) );
		$sauces   = get_posts( array_merge( $q, [ 'post_type' => 'pizzalayer_sauces' ] ) );
		$cheeses  = get_posts( array_merge( $q, [ 'post_type' => 'pizzalayer_cheeses' ] ) );
		$drizzles = get_posts( array_merge( $q, [ 'post_type' => 'pizzalayer_drizzles' ] ) );
		$cuts     = get_posts( array_merge( $q, [ 'post_type' => 'pizzalayer_cuts' ] ) );

		$g = fn( string $key, string $default = '' ) => (string) get_option( $key, $default );

		// ── Step definitions ──────────────────────────────────────────────
		// Each step: key, title, icon, plain-English intro, fields array
		$steps = $this->get_steps( $crusts, $sauces, $cheeses, $drizzles, $cuts, $g );

		$total      = count( $steps );
		$done_count = count( array_filter( $steps, fn( $s ) => ! empty( $done[ $s['key'] ] ) ) );
		$pct        = $total > 0 ? (int) round( $done_count / $total * 100 ) : 0;

		if ( $active_tab === '' ) {
			$active_tab = $steps[0]['key'];
		}

		?>
		<div class="wrap pzwiz-wrap">
		<?php $this->render_styles(); ?>

		<!-- ═══ Header ═══════════════════════════════════════════════════ -->
		<div class="pzwiz-header">
			<span class="dashicons dashicons-admin-settings pzwiz-header__icon"></span>
			<div class="pzwiz-header__text">
				<h1 class="pzwiz-header__title"><?php esc_html_e( 'Settings Wizard', 'pizzalayer' ); ?></h1>
				<p class="pzwiz-header__sub"><?php esc_html_e( 'Step-by-step settings guide — no jargon, plain English. Work through each section at your own pace.', 'pizzalayer' ); ?></p>
			</div>
			<div class="pzwiz-header__actions">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=pizzalayer-settings' ) ); ?>" class="button">
					<span class="dashicons dashicons-admin-settings" style="margin-top:3px;"></span>
					<?php esc_html_e( 'Full Settings Page', 'pizzalayer' ); ?>
				</a>
			</div>
		</div>

		<!-- ═══ Progress bar ══════════════════════════════════════════════ -->
		<div class="pzwiz-progress-wrap">
			<div class="pzwiz-progress-bar" style="width:<?php echo esc_attr( (string) $pct ); ?>%"></div>
		</div>
		<p class="pzwiz-progress-label">
			<?php printf( esc_html__( '%1$d of %2$d sections complete (%3$d%%)', 'pizzalayer' ), $done_count, $total, $pct ); ?>
			<?php if ( $done_count > 0 ) : ?>
				&nbsp;·&nbsp;
				<form method="post" action="" style="display:inline;">
					<?php wp_nonce_field( 'pizzalayer_wizard' ); ?>
					<button type="submit" name="pzwiz_reset" value="1" class="pzwiz-reset-link"
					        onclick="return confirm('<?php esc_attr_e( 'Reset all wizard progress?', 'pizzalayer' ); ?>');">
						<?php esc_html_e( 'Reset progress', 'pizzalayer' ); ?>
					</button>
				</form>
			<?php endif; ?>
		</p>

		<!-- ═══ Two-column layout: sidebar + content ══════════════════════ -->
		<div class="pzwiz-layout">

			<!-- Sidebar step list -->
			<nav class="pzwiz-sidebar" aria-label="Wizard steps">
				<?php foreach ( $steps as $idx => $step ) :
					$is_done    = ! empty( $done[ $step['key'] ] );
					$is_active  = $step['key'] === $active_tab;
				?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=pizzalayer-wizard&step=' . $step['key'] ) ); ?>"
				   class="pzwiz-step-link<?php echo $is_active ? ' pzwiz-step-link--active' : ''; ?><?php echo $is_done ? ' pzwiz-step-link--done' : ''; ?>">
					<span class="pzwiz-step-num"><?php echo $is_done ? '✓' : ( $idx + 1 ); ?></span>
					<span class="pzwiz-step-label">
						<span class="pzwiz-step-title"><?php echo esc_html( $step['title'] ); ?></span>
						<?php if ( $is_done ) : ?>
						<span class="pzwiz-step-badge pzwiz-step-badge--done"><?php esc_html_e( 'Done', 'pizzalayer' ); ?></span>
						<?php elseif ( ! empty( $step['optional'] ) ) : ?>
						<span class="pzwiz-step-badge pzwiz-step-badge--opt"><?php esc_html_e( 'Optional', 'pizzalayer' ); ?></span>
						<?php endif; ?>
					</span>
				</a>
				<?php endforeach; ?>
			</nav>

			<!-- Step content panel -->
			<?php foreach ( $steps as $idx => $step ) :
				if ( $step['key'] !== $active_tab ) { continue; }
				$is_done = ! empty( $done[ $step['key'] ] );

				// Prev/next
				$prev_key = $idx > 0             ? $steps[ $idx - 1 ]['key'] : null;
				$next_key = $idx < $total - 1    ? $steps[ $idx + 1 ]['key'] : null;
			?>
			<div class="pzwiz-content">

				<div class="pzwiz-step-header">
					<span class="pzwiz-step-header__num"><?php echo esc_html( (string)( $idx + 1 ) ); ?></span>
					<div>
						<h2 class="pzwiz-step-header__title">
							<span class="dashicons <?php echo esc_attr( $step['icon'] ); ?>"></span>
							<?php echo esc_html( $step['title'] ); ?>
							<?php if ( ! empty( $step['optional'] ) ) : ?><span class="pzwiz-opt-tag"><?php esc_html_e( 'Optional', 'pizzalayer' ); ?></span><?php endif; ?>
						</h2>
						<p class="pzwiz-step-header__desc"><?php echo wp_kses_post( $step['intro'] ); ?></p>
					</div>
				</div>

				<?php if ( $is_done ) : ?>
				<div class="pzwiz-done-banner">
					<span class="dashicons dashicons-yes-alt"></span>
					<?php esc_html_e( 'You\'ve marked this section as done. You can still edit the settings below and save anytime.', 'pizzalayer' ); ?>
					<form method="post" action="" style="display:inline;margin-left:10px;">
						<?php wp_nonce_field( 'pizzalayer_wizard' ); ?>
						<input type="hidden" name="pzwiz_mark_done" value="<?php echo esc_attr( $step['key'] ); ?>">
						<input type="hidden" name="pzwiz_state" value="0">
						<button type="submit" class="button button-small"><?php esc_html_e( 'Mark undone', 'pizzalayer' ); ?></button>
					</form>
				</div>
				<?php endif; ?>

				<!-- Settings form for this step -->
				<form method="post" action="">
					<?php wp_nonce_field( 'pizzalayer_wizard' ); ?>
					<input type="hidden" name="pzwiz_save" value="<?php echo esc_attr( $step['key'] ); ?>">

					<div class="pzwiz-fields">
						<?php $this->render_step_fields( $step, $g ); ?>
					</div>

					<div class="pzwiz-step-footer">
						<button type="submit" class="button button-primary pzwiz-save-btn">
							<span class="dashicons dashicons-yes"></span>
							<?php esc_html_e( 'Save this section', 'pizzalayer' ); ?>
						</button>

						<?php if ( ! $is_done ) : ?>
						<button type="submit" name="pzwiz_mark_done" value="<?php echo esc_attr( $step['key'] ); ?>" class="button">
							<input type="hidden" name="pzwiz_state" value="1">
							<?php esc_html_e( 'Skip / Mark done', 'pizzalayer' ); ?>
						</button>
						<?php endif; ?>

						<div class="pzwiz-nav-btns">
							<?php if ( $prev_key ) : ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=pizzalayer-wizard&step=' . $prev_key ) ); ?>" class="button">← <?php esc_html_e( 'Previous', 'pizzalayer' ); ?></a>
							<?php endif; ?>
							<?php if ( $next_key ) : ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=pizzalayer-wizard&step=' . $next_key ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Next', 'pizzalayer' ); ?> →</a>
							<?php endif; ?>
							<?php if ( ! $next_key ) : ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=pizzalayer-setup' ) ); ?>" class="button button-primary">
								🎉 <?php esc_html_e( 'Finish — view Setup Guide', 'pizzalayer' ); ?>
							</a>
							<?php endif; ?>
						</div>
					</div>

				</form>

			</div><!-- /.pzwiz-content -->
			<?php endforeach; ?>

		</div><!-- /.pzwiz-layout -->
		</div><!-- /.wrap -->
		<?php
	}

	// ── Save step settings ────────────────────────────────────────────────────

	private function save_step( string $step_key ): void {
		/** Map of step key → option keys it owns (must match actual Settings.php keys) */
		$step_options = [
			'defaults'    => [
				'pizzalayer_setting_crust_defaultcrust',
				'pizzalayer_setting_sauce_defaultsauce',
				'pizzalayer_setting_cheese_defaultcheese',
				'pizzalayer_setting_drizzle_defaultdrizzle',
				'pizzalayer_setting_cut_defaultcut',
			],
			'toppings'    => [
				'pizzalayer_setting_topping_maxtoppings',
			],
			'display'     => [
				'pizzalayer_setting_pizza_size_max',
				'pizzalayer_setting_pizza_shape',
				'pizzalayer_setting_pizza_border_color',
				'pizzalayer_setting_global_color',
			],
			'appearance'  => [
				'pizzalayer_setting_branding_primary_color',
				'pizzalayer_setting_branding_secondary_color',
				'pizzalayer_setting_typo_font_family',
			],
			'layout'      => [
				'pizzalayer_setting_layout_tab_order',
				'pizzalayer_setting_layout_hide_empty',
				'pizzalayer_setting_layout_step_by_step',
			],
			'messaging'   => [
				'pizzalayer_setting_branding_tagline',
				'pizzalayer_setting_settings_demonotice',
			],
			'ux'          => [
				'pizzalayer_setting_cx_show_summary',
				'pizzalayer_setting_cx_special_instructions',
				'pizzalayer_setting_cx_special_instr_max',
				'pizzalayer_setting_cx_review_modal',
				'pizzalayer_setting_cx_show_start_over',
			],
			'animations'  => [
				'pizzalayer_setting_layer_anim',
				'pizzalayer_setting_layer_anim_speed',
			],
			'accessibility' => [
				'pizzalayer_setting_a11y_focus_ring',
				'pizzalayer_setting_a11y_reduce_motion',
				'pizzalayer_setting_perf_lazy_load',
			],
		];

		if ( ! isset( $step_options[ $step_key ] ) ) {
			return;
		}

		$yes_no_keys = [
			'pizzalayer_setting_layout_hide_empty',
			'pizzalayer_setting_layout_step_by_step',
			'pizzalayer_setting_cx_show_summary',
			'pizzalayer_setting_cx_special_instructions',
			'pizzalayer_setting_cx_review_modal',
			'pizzalayer_setting_cx_show_start_over',
			'pizzalayer_setting_a11y_reduce_motion',
			'pizzalayer_setting_perf_lazy_load',
		];

		foreach ( $step_options[ $step_key ] as $opt_key ) {
			if ( in_array( $opt_key, $yes_no_keys, true ) ) {
				$val = isset( $_POST[ $opt_key ] ) && $_POST[ $opt_key ] === 'yes' ? 'yes' : 'no';
				update_option( sanitize_key( $opt_key ), $val );
			} elseif ( isset( $_POST[ $opt_key ] ) ) {
				update_option( sanitize_key( $opt_key ), sanitize_text_field( wp_unslash( (string) $_POST[ $opt_key ] ) ) );
			}
		}

		// Also mark step done when saving
		$done = $this->get_done();
		$done[ $step_key ] = true;
		update_option( 'pizzalayer_wizard_done', $done );
	}

	// ── Step definitions ──────────────────────────────────────────────────────

	private function get_steps( array $crusts, array $sauces, array $cheeses, array $drizzles, array $cuts, callable $g ): array {
		return [
			[
				'key'   => 'defaults',
				'title' => __( 'Default Selections', 'pizzalayer' ),
				'icon'  => 'dashicons-category',
				'intro' => __( 'When someone opens your pizza builder, what should already be selected? Think of this like "the house pizza" — the default crust, sauce, and cheese that loads up automatically so customers can start customizing right away without having to pick from scratch.', 'pizzalayer' ),
				'fields' => [
					[
						'type'    => 'layer_picker',
						'key'     => 'pizzalayer_setting_crust_defaultcrust',
						'label'   => __( 'Default Crust', 'pizzalayer' ),
						'tip'     => __( 'Which crust should be pre-selected? Pick your most popular or standard crust.', 'pizzalayer' ),
						'items'   => $crusts,
						'current' => $g( 'pizzalayer_setting_crust_defaultcrust' ),
					],
					[
						'type'    => 'layer_picker',
						'key'     => 'pizzalayer_setting_sauce_defaultsauce',
						'label'   => __( 'Default Sauce', 'pizzalayer' ),
						'tip'     => __( 'The sauce shown when the builder first opens. Classic tomato is a safe default for most pizzerias.', 'pizzalayer' ),
						'items'   => $sauces,
						'current' => $g( 'pizzalayer_setting_sauce_defaultsauce' ),
					],
					[
						'type'    => 'layer_picker',
						'key'     => 'pizzalayer_setting_cheese_defaultcheese',
						'label'   => __( 'Default Cheese', 'pizzalayer' ),
						'tip'     => __( 'Pre-selected cheese type. Mozzarella is the most common default.', 'pizzalayer' ),
						'items'   => $cheeses,
						'current' => $g( 'pizzalayer_setting_cheese_defaultcheese' ),
					],
					[
						'type'    => 'layer_picker',
						'key'     => 'pizzalayer_setting_drizzle_defaultdrizzle',
						'label'   => __( 'Default Drizzle (optional)', 'pizzalayer' ),
						'tip'     => __( 'Leave blank if you don\'t want a drizzle pre-selected.', 'pizzalayer' ),
						'items'   => $drizzles,
						'current' => $g( 'pizzalayer_setting_drizzle_defaultdrizzle' ),
						'blank'   => __( '— None —', 'pizzalayer' ),
					],
					[
						'type'    => 'layer_picker',
						'key'     => 'pizzalayer_setting_cut_defaultcut',
						'label'   => __( 'Default Cut Style (optional)', 'pizzalayer' ),
						'tip'     => __( 'Leave blank if you don\'t want a cut style pre-selected.', 'pizzalayer' ),
						'items'   => $cuts,
						'current' => $g( 'pizzalayer_setting_cut_defaultcut' ),
						'blank'   => __( '— None —', 'pizzalayer' ),
					],
				],
			],
			[
				'key'   => 'toppings',
				'title' => __( 'Topping Rules', 'pizzalayer' ),
				'icon'  => 'dashicons-star-filled',
				'intro' => __( 'Set the maximum number of toppings a customer can add. Set to 0 for unlimited. This helps you control the ordering experience and match how your kitchen actually works.', 'pizzalayer' ),
				'fields' => [
					[
						'type'    => 'number',
						'key'     => 'pizzalayer_setting_topping_maxtoppings',
						'label'   => __( 'Maximum Toppings Allowed', 'pizzalayer' ),
						'tip'     => __( 'The most toppings a customer can add to one pizza. Set to 0 for unlimited. Most pizzerias allow 5–8 toppings on a standard build.', 'pizzalayer' ),
						'value'   => $g( 'pizzalayer_setting_topping_maxtoppings', '0' ),
						'min'     => 0,
						'max'     => 99,
						'placeholder' => '0 = unlimited',
					],
				],
			],
			[
				'key'   => 'display',
				'title' => __( 'Pizza Display', 'pizzalayer' ),
				'icon'  => 'dashicons-format-image',
				'intro' => __( 'Control how the pizza visualizer looks — its maximum size on screen, whether it\'s round or square, and its border/accent colors. This is purely visual and doesn\'t affect ordering. Pick whatever looks best on your website.', 'pizzalayer' ),
				'fields' => [
					[
						'type'    => 'number',
						'key'     => 'pizzalayer_setting_pizza_size_max',
						'label'   => __( 'Pizza Max Display Size (pixels)', 'pizzalayer' ),
						'tip'     => __( 'The maximum width of the pizza visualizer. 600px is a good default for most layouts. The pizza will still scale down on smaller screens.', 'pizzalayer' ),
						'value'   => $g( 'pizzalayer_setting_pizza_size_max', '600' ),
						'min'     => 100,
						'max'     => 1200,
						'placeholder' => '600',
					],
					[
						'type'    => 'select',
						'key'     => 'pizzalayer_setting_pizza_shape',
						'label'   => __( 'Pizza Shape', 'pizzalayer' ),
						'tip'     => __( 'Round (circle) looks like a real pizza. Square works well for pan/Detroit-style pizzas. Rectangle is good for sheet pizzas. Custom lets you set your own aspect ratio and radius.', 'pizzalayer' ),
						'value'   => $g( 'pizzalayer_setting_pizza_shape', 'round' ),
						'options' => [
							'round'     => __( 'Round (circle)', 'pizzalayer' ),
							'square'    => __( 'Square', 'pizzalayer' ),
							'rectangle' => __( 'Rectangle (pan/sheet style)', 'pizzalayer' ),
							'custom'    => __( 'Custom (set aspect ratio and radius)', 'pizzalayer' ),
						],
					],
					[
						'type'    => 'color',
						'key'     => 'pizzalayer_setting_pizza_border_color',
						'label'   => __( 'Pizza Border Color', 'pizzalayer' ),
						'tip'     => __( 'The thin line around the pizza image. Use a warm brown or orange to make it look like a crust edge.', 'pizzalayer' ),
						'value'   => $g( 'pizzalayer_setting_pizza_border_color', '#c8a46e' ),
					],
					[
						'type'    => 'color',
						'key'     => 'pizzalayer_setting_global_color',
						'label'   => __( 'Accent / Highlight Color', 'pizzalayer' ),
						'tip'     => __( 'The main color used for selected items, buttons, and highlights throughout the builder. Pick something that matches your brand.', 'pizzalayer' ),
						'value'   => $g( 'pizzalayer_setting_global_color', '#ff6b35' ),
					],
				],
			],
			[
				'key'   => 'appearance',
				'title' => __( 'Look & Feel', 'pizzalayer' ),
				'icon'  => 'dashicons-art',
				'intro' => __( 'Give the builder your brand\'s personality. Set your primary and secondary brand colors, and choose a font family. These work alongside your chosen template\'s own styling.', 'pizzalayer' ),
				'fields' => [
					[
						'type'    => 'color',
						'key'     => 'pizzalayer_setting_branding_primary_color',
						'label'   => __( 'Brand Primary Color', 'pizzalayer' ),
						'tip'     => __( 'Your main brand color — used for buttons, selected states, and highlights. This should match the primary color on your website.', 'pizzalayer' ),
						'value'   => $g( 'pizzalayer_setting_branding_primary_color', '#ff6b35' ),
					],
					[
						'type'    => 'color',
						'key'     => 'pizzalayer_setting_branding_secondary_color',
						'label'   => __( 'Brand Secondary Color', 'pizzalayer' ),
						'tip'     => __( 'A supporting color — used for hover states, secondary buttons, and accents. Often a darker or lighter shade of your primary color.', 'pizzalayer' ),
						'value'   => $g( 'pizzalayer_setting_branding_secondary_color', '#e55a2b' ),
					],
					[
						'type'    => 'text',
						'key'     => 'pizzalayer_setting_typo_font_family',
						'label'   => __( 'Font Family', 'pizzalayer' ),
						'tip'     => __( 'The font used inside the builder. Leave blank to use your theme\'s default font. To use a Google Font, enter its exact name (e.g. "Lato" or "Poppins") — make sure the font is already loaded by your theme.', 'pizzalayer' ),
						'value'   => $g( 'pizzalayer_setting_typo_font_family', '' ),
						'placeholder' => __( 'e.g. Lato, Poppins, or leave blank', 'pizzalayer' ),
					],
				],
			],
			[
				'key'   => 'layout',
				'title' => __( 'Builder Layout', 'pizzalayer' ),
				'icon'  => 'dashicons-layout',
				'intro' => __( 'Control the order of the ingredient tabs and a couple of layout options. The tab order determines the steps customers follow when building their pizza — put the most important choices first.', 'pizzalayer' ),
				'fields' => [
					[
						'type'    => 'text',
						'key'     => 'pizzalayer_setting_layout_tab_order',
						'label'   => __( 'Tab Order', 'pizzalayer' ),
						'tip'     => __( 'List the ingredient categories in the order you want them to appear as tabs, separated by commas. For example: crust, sauce, cheese, toppings, drizzle, slicing. Leave blank for the default order.', 'pizzalayer' ),
						'value'   => $g( 'pizzalayer_setting_layout_tab_order', '' ),
						'placeholder' => 'crust, sauce, cheese, toppings, drizzle, slicing',
					],
					[
						'type'    => 'toggle',
						'key'     => 'pizzalayer_setting_layout_hide_empty',
						'label'   => __( 'Hide Tabs With No Items', 'pizzalayer' ),
						'tip'     => __( 'If you haven\'t added any drizzles yet, should the "Drizzle" tab be hidden? Turn this on to keep the builder tidy — tabs only appear once you\'ve added content for them.', 'pizzalayer' ),
						'value'   => $g( 'pizzalayer_setting_layout_hide_empty', 'no' ),
						'toggle_label' => __( 'Hide tabs that have no published items', 'pizzalayer' ),
					],
					[
						'type'    => 'toggle',
						'key'     => 'pizzalayer_setting_layout_step_by_step',
						'label'   => __( 'Step-by-Step Mode', 'pizzalayer' ),
						'tip'     => __( 'Locks customers to one tab at a time, stepping through crust → sauce → cheese → toppings in order. Great for guided ordering workflows.', 'pizzalayer' ),
						'value'   => $g( 'pizzalayer_setting_layout_step_by_step', 'no' ),
						'toggle_label' => __( 'Guide customers through tabs one step at a time', 'pizzalayer' ),
					],
				],
			],
			[
				'key'   => 'messaging',
				'title' => __( 'Text & Messaging', 'pizzalayer' ),
				'icon'  => 'dashicons-format-chat',
				'intro' => __( 'Customise the words customers see inside the builder — the tagline shown in the header and an optional demo/notice banner. This is your chance to give the experience your restaurant\'s voice and personality.', 'pizzalayer' ),
				'fields' => [
					[
						'type'    => 'text',
						'key'     => 'pizzalayer_setting_branding_tagline',
						'label'   => __( 'Builder Tagline', 'pizzalayer' ),
						'tip'     => __( 'A short tagline shown in the builder header — something like "Build Your Perfect Pizza" or "Create Your Masterpiece". Leave blank to hide it.', 'pizzalayer' ),
						'value'   => $g( 'pizzalayer_setting_branding_tagline', '' ),
						'placeholder' => __( 'e.g. Build Your Perfect Pizza', 'pizzalayer' ),
					],
					[
						'type'    => 'text',
						'key'     => 'pizzalayer_setting_settings_demonotice',
						'label'   => __( 'Demo / Notice Banner', 'pizzalayer' ),
						'tip'     => __( 'An optional notice shown above the builder — useful for "This is a demo" messages, promotions, or reminders like "Delivery orders close at 9 PM". Leave blank to hide it.', 'pizzalayer' ),
						'value'   => $g( 'pizzalayer_setting_settings_demonotice', '' ),
						'placeholder' => __( 'Leave blank to hide', 'pizzalayer' ),
					],
				],
			],
			[
				'key'      => 'ux',
				'title'    => __( 'Customer Experience', 'pizzalayer' ),
				'icon'     => 'dashicons-smiley',
				'optional' => true,
				'intro'    => __( 'Fine-tune the little extras that make the ordering experience smooth and professional. These are all optional — turn on the ones that fit your workflow.', 'pizzalayer' ),
				'fields' => [
					[
						'type'    => 'toggle',
						'key'     => 'pizzalayer_setting_cx_show_summary',
						'label'   => __( 'Show Order Summary Panel', 'pizzalayer' ),
						'tip'     => __( 'Shows a running list of what the customer has chosen so far (e.g. "Thin Crust + Tomato Sauce + Mozzarella + Pepperoni"). Helps customers review their choices before confirming.', 'pizzalayer' ),
						'value'   => $g( 'pizzalayer_setting_cx_show_summary', 'no' ),
						'toggle_label' => __( 'Show a summary of selected ingredients', 'pizzalayer' ),
					],
					[
						'type'    => 'toggle',
						'key'     => 'pizzalayer_setting_cx_show_start_over',
						'label'   => __( 'Show "Start Over" Button', 'pizzalayer' ),
						'tip'     => __( 'Adds a "Start Over" button that resets all selections. Useful for customers who want to try a completely different build.', 'pizzalayer' ),
						'value'   => $g( 'pizzalayer_setting_cx_show_start_over', 'yes' ),
						'toggle_label' => __( 'Show a button to reset all selections', 'pizzalayer' ),
					],
					[
						'type'    => 'toggle',
						'key'     => 'pizzalayer_setting_cx_special_instructions',
						'label'   => __( 'Allow Special Instructions', 'pizzalayer' ),
						'tip'     => __( 'Adds a text box where customers can type notes for the kitchen — like "well done", "no salt", or "nut allergy". Useful for accommodating dietary needs.', 'pizzalayer' ),
						'value'   => $g( 'pizzalayer_setting_cx_special_instructions', 'no' ),
						'toggle_label' => __( 'Show a "Special instructions" text field', 'pizzalayer' ),
					],
					[
						'type'    => 'toggle',
						'key'     => 'pizzalayer_setting_cx_review_modal',
						'label'   => __( 'Show Confirmation Screen', 'pizzalayer' ),
						'tip'     => __( 'Pops up a final "review your order" screen before the customer confirms. Reduces mistakes and gives them one last chance to check everything.', 'pizzalayer' ),
						'value'   => $g( 'pizzalayer_setting_cx_review_modal', 'no' ),
						'toggle_label' => __( 'Show a "Review your order" pop-up before confirming', 'pizzalayer' ),
					],
				],
			],
			[
				'key'      => 'animations',
				'title'    => __( 'Animations', 'pizzalayer' ),
				'icon'     => 'dashicons-controls-play',
				'optional' => true,
				'intro'    => __( 'Control whether ingredient layers animate when added to the pizza. Animations look great and make the builder feel alive — but you can turn them off if you prefer a simpler, faster feel.', 'pizzalayer' ),
				'fields' => [
					[
						'type'    => 'select',
						'key'     => 'pizzalayer_setting_layer_anim',
						'label'   => __( 'Layer Animation Style', 'pizzalayer' ),
						'tip'     => __( 'How ingredients appear on the pizza when selected. "Fade in" is subtle and clean. "Drop in" feels more dramatic. "Instant" shows them immediately with no animation.', 'pizzalayer' ),
						'value'   => $g( 'pizzalayer_setting_layer_anim', 'fade' ),
						'options' => [
							'instant'  => __( 'Instant — no animation', 'pizzalayer' ),
							'fade'     => __( 'Fade in — smooth and subtle', 'pizzalayer' ),
							'drop-in'  => __( 'Drop in — falls from above', 'pizzalayer' ),
							'scale-in' => __( 'Scale in — grows from small', 'pizzalayer' ),
							'slide-up' => __( 'Slide up — enters from below', 'pizzalayer' ),
							'flip-in'  => __( 'Flip in — 3D rotation reveal', 'pizzalayer' ),
						],
					],
					[
						'type'    => 'range',
						'key'     => 'pizzalayer_setting_layer_anim_speed',
						'label'   => __( 'Animation Speed', 'pizzalayer' ),
						'tip'     => __( 'How fast the animation plays, in milliseconds. 200ms is quick and snappy. 500ms is slower and more dramatic. Most people prefer 250–350ms.', 'pizzalayer' ),
						'value'   => $g( 'pizzalayer_setting_layer_anim_speed', '320' ),
						'min'     => 50,
						'max'     => 800,
						'step'    => 10,
						'suffix'  => 'ms',
					],
				],
			],
			[
				'key'      => 'accessibility',
				'title'    => __( 'Accessibility & Performance', 'pizzalayer' ),
				'icon'     => 'dashicons-universal-access',
				'optional' => true,
				'intro'    => __( 'A few settings that make the builder more usable for everyone — including customers with disabilities — and can help the page load faster. These are safe to leave on their defaults.', 'pizzalayer' ),
				'fields' => [
					[
						'type'    => 'select',
						'key'     => 'pizzalayer_setting_a11y_focus_ring',
						'label'   => __( 'Keyboard Focus Ring Style', 'pizzalayer' ),
						'tip'     => __( 'When someone navigates with a keyboard instead of a mouse, this shows a visible outline around the currently focused item. Important for accessibility and required by some regulations.', 'pizzalayer' ),
						'value'   => $g( 'pizzalayer_setting_a11y_focus_ring', 'default' ),
						'options' => [
							'default' => __( 'Theme default', 'pizzalayer' ),
							'bold'    => __( 'Bold outline (high visibility)', 'pizzalayer' ),
							'glow'    => __( 'Glow ring', 'pizzalayer' ),
							'none'    => __( 'None (not recommended)', 'pizzalayer' ),
						],
					],
					[
						'type'    => 'toggle',
						'key'     => 'pizzalayer_setting_a11y_reduce_motion',
						'label'   => __( 'Respect "Reduce Motion" Setting', 'pizzalayer' ),
						'tip'     => __( 'Some users turn on "Reduce Motion" in their device settings (common for people with vestibular disorders or motion sensitivity). Turning this on respects that preference and disables animations for them.', 'pizzalayer' ),
						'value'   => $g( 'pizzalayer_setting_a11y_reduce_motion', 'no' ),
						'toggle_label' => __( 'Disable animations for users who prefer reduced motion', 'pizzalayer' ),
					],
					[
						'type'    => 'toggle',
						'key'     => 'pizzalayer_setting_perf_lazy_load',
						'label'   => __( 'Lazy Load Ingredient Images', 'pizzalayer' ),
						'tip'     => __( 'Only load ingredient images when they\'re about to come into view on screen. This makes the page load faster, especially if you have a lot of toppings. Recommended: on.', 'pizzalayer' ),
						'value'   => $g( 'pizzalayer_setting_perf_lazy_load', 'yes' ),
						'toggle_label' => __( 'Load images only when needed (faster page load)', 'pizzalayer' ),
					],
				],
			],
		];
	}

	// ── Render fields for a step ──────────────────────────────────────────────

	private function render_step_fields( array $step, callable $g ): void {
		foreach ( $step['fields'] as $field ) {
			$key = $field['key'];
			?>
			<div class="pzwiz-field">
				<div class="pzwiz-field__head">
					<label class="pzwiz-field__label" for="pzwiz-<?php echo esc_attr( $key ); ?>">
						<?php echo esc_html( $field['label'] ); ?>
					</label>
					<?php if ( ! empty( $field['tip'] ) ) : ?>
					<p class="pzwiz-field__tip"><?php echo esc_html( $field['tip'] ); ?></p>
					<?php endif; ?>
				</div>
				<div class="pzwiz-field__control">
					<?php
					switch ( $field['type'] ) {

						case 'layer_picker':
							$blank = $field['blank'] ?? null;
							echo '<select name="' . esc_attr( $key ) . '" id="pzwiz-' . esc_attr( $key ) . '" class="pzwiz-select">';
							if ( $blank !== null ) {
								echo '<option value=""' . selected( $field['current'], '', false ) . '>' . esc_html( $blank ) . '</option>';
							}
							foreach ( $field['items'] as $post ) {
								echo '<option value="' . esc_attr( $post->post_name ) . '"' . selected( $field['current'], $post->post_name, false ) . '>' . esc_html( $post->post_title ) . '</option>';
							}
							echo '</select>';
							if ( empty( $field['items'] ) ) {
								echo '<p class="pzwiz-field__notice">⚠ ' . esc_html__( 'No items found. Add some content first, then come back here.', 'pizzalayer' ) . '</p>';
							}
							break;

						case 'toggle':
							$checked = ( $field['value'] === 'yes' ) ? ' checked' : '';
							echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="no">';
							echo '<label class="pzwiz-toggle">';
							echo '<input type="checkbox" id="pzwiz-' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="yes"' . $checked . '>'; // phpcs:ignore
							echo '<span class="pzwiz-toggle__track"><span class="pzwiz-toggle__thumb"></span></span>';
							echo '<span class="pzwiz-toggle__lbl">' . esc_html( $field['toggle_label'] ?? $field['label'] ) . '</span>';
							echo '</label>';
							break;

						case 'number':
							$min  = isset( $field['min'] )  ? ' min="' . esc_attr( (string) $field['min'] ) . '"'  : '';
							$max  = isset( $field['max'] )  ? ' max="' . esc_attr( (string) $field['max'] ) . '"'  : '';
							$ph   = isset( $field['placeholder'] ) ? ' placeholder="' . esc_attr( $field['placeholder'] ) . '"' : '';
							echo '<input type="number" id="pzwiz-' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $field['value'] ) . '" class="pzwiz-input"' . $min . $max . $ph . '>'; // phpcs:ignore
							break;

						case 'text':
							$ph = isset( $field['placeholder'] ) ? ' placeholder="' . esc_attr( $field['placeholder'] ) . '"' : '';
							echo '<input type="text" id="pzwiz-' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $field['value'] ) . '" class="pzwiz-input pzwiz-input--wide"' . $ph . '>'; // phpcs:ignore
							break;

						case 'select':
							echo '<select name="' . esc_attr( $key ) . '" id="pzwiz-' . esc_attr( $key ) . '" class="pzwiz-select">';
							foreach ( $field['options'] as $ov => $ol ) {
								echo '<option value="' . esc_attr( $ov ) . '"' . selected( $field['value'], $ov, false ) . '>' . esc_html( $ol ) . '</option>';
							}
							echo '</select>';
							break;

						case 'color':
							echo '<div class="pzwiz-color-wrap">';
							echo '<input type="color" id="pzwiz-' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $field['value'] ) . '" class="pzwiz-color">';
							echo '<input type="text" name="' . esc_attr( $key ) . '_text_display" value="' . esc_attr( $field['value'] ) . '" class="pzwiz-color-text" maxlength="7" readonly>';
							echo '</div>';
							// color sync handled by settings-wizard.js via .pzwiz-color class delegation
							break;

						case 'range':
							$min    = isset( $field['min'] )  ? ' min="' . esc_attr( (string) $field['min'] ) . '"'    : '';
							$max    = isset( $field['max'] )  ? ' max="' . esc_attr( (string) $field['max'] ) . '"'    : '';
							$step_a = isset( $field['step'] ) ? ' step="' . esc_attr( (string) $field['step'] ) . '"'  : '';
							$suffix = $field['suffix'] ?? '';
							$id     = 'pzwiz-' . esc_attr( $key );
							echo '<div class="pzwiz-range-wrap">';
							echo '<input type="range" id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $field['value'] ) . '" class="pzwiz-range"' . $min . $max . $step_a . '>'; // phpcs:ignore
							echo '<span class="pzwiz-range-val" id="' . esc_attr( $id ) . '-val" data-suffix="' . esc_attr( $suffix ) . '">' . esc_html( $field['value'] ) . esc_html( $suffix ) . '</span>';
							echo '</div>';
							// range sync handled by settings-wizard.js via .pzwiz-range class delegation
							break;
					}
					?>
				</div>
			</div>
			<?php
		}
	}

	// ── Styles ────────────────────────────────────────────────────────────────

	private function render_styles(): void { ?>
	<style>
	/* Wizard wrapper */
	.pzwiz-wrap { max-width: 1100px; }

	/* Header */
	.pzwiz-header { display:flex; align-items:center; gap:16px; background:linear-gradient(135deg,#1a1e23,#2d3748); color:#fff; border-radius:10px; padding:22px 28px; margin-bottom:14px; flex-wrap:wrap; }
	.pzwiz-header__icon { font-size:36px !important; width:36px !important; height:36px !important; color:#ff6b35; flex-shrink:0; }
	.pzwiz-header__text { flex:1; }
	.pzwiz-header__title { margin:0; font-size:22px; font-weight:700; color:#fff; }
	.pzwiz-header__sub   { margin:3px 0 0; color:#8d97a5; font-size:13px; }
	.pzwiz-header__actions { flex-shrink:0; }
	.pzwiz-header__actions .button { display:inline-flex; align-items:center; gap:5px; }
	.pzwiz-header__actions .dashicons { font-size:14px !important; width:14px !important; height:14px !important; }

	/* Progress */
	.pzwiz-progress-wrap { height:8px; background:#e0e3e7; border-radius:99px; overflow:hidden; margin-bottom:6px; }
	.pzwiz-progress-bar  { height:100%; background:linear-gradient(90deg,#2271b1,#00a32a); border-radius:99px; transition:width .4s ease; }
	.pzwiz-progress-label { font-size:12px; color:#646970; margin-bottom:18px; }
	.pzwiz-reset-link { background:none; border:none; color:#646970; cursor:pointer; font-size:12px; text-decoration:underline; padding:0; }
	.pzwiz-reset-link:hover { color:#b32d2e; }

	/* Layout */
	.pzwiz-layout { display:grid; grid-template-columns: 240px 1fr; gap:20px; align-items:start; }
	@media (max-width: 782px) { .pzwiz-layout { grid-template-columns:1fr; } }

	/* Sidebar */
	.pzwiz-sidebar { background:#fff; border:1px solid #e0e3e7; border-radius:10px; overflow:hidden; position:sticky; top:32px; }
	.pzwiz-step-link { display:flex; align-items:center; gap:10px; padding:11px 14px; text-decoration:none; border-bottom:1px solid #f0f0f0; color:#3c434a; font-size:13px; transition:background .15s; }
	.pzwiz-step-link:last-child { border-bottom:none; }
	.pzwiz-step-link:hover { background:#f6f7f7; color:#1d2023; }
	.pzwiz-step-link--active { background:#f0f6ff; color:#2271b1; font-weight:600; border-left:3px solid #2271b1; }
	.pzwiz-step-link--done   { color:#00a32a; }
	.pzwiz-step-num { display:flex; align-items:center; justify-content:center; width:22px; height:22px; border-radius:50%; background:#e0e3e7; color:#646970; font-size:11px; font-weight:700; flex-shrink:0; }
	.pzwiz-step-link--active  .pzwiz-step-num { background:#2271b1; color:#fff; }
	.pzwiz-step-link--done    .pzwiz-step-num { background:#00a32a; color:#fff; }
	.pzwiz-step-label { flex:1; line-height:1.3; }
	.pzwiz-step-title { display:block; }
	.pzwiz-step-badge { display:inline-block; border-radius:99px; font-size:10px; font-weight:700; padding:1px 6px; margin-top:2px; }
	.pzwiz-step-badge--done { background:#d4edda; color:#00a32a; }
	.pzwiz-step-badge--opt  { background:#f0f0f0; color:#787c82; }

	/* Content panel */
	.pzwiz-content { background:#fff; border:1px solid #e0e3e7; border-radius:10px; padding:28px 30px; }

	/* Step header */
	.pzwiz-step-header { display:flex; align-items:flex-start; gap:14px; margin-bottom:24px; padding-bottom:18px; border-bottom:1px solid #f0f0f0; }
	.pzwiz-step-header__num { display:flex; align-items:center; justify-content:center; width:38px; height:38px; border-radius:50%; background:#2271b1; color:#fff; font-size:16px; font-weight:700; flex-shrink:0; margin-top:2px; }
	.pzwiz-step-header__title { margin:0 0 6px; font-size:18px; font-weight:700; color:#1d2023; display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
	.pzwiz-step-header__title .dashicons { color:#ff6b35; font-size:20px !important; width:20px !important; height:20px !important; }
	.pzwiz-step-header__desc  { margin:0; font-size:13.5px; color:#646970; line-height:1.65; }
	.pzwiz-opt-tag { font-size:11px; font-weight:600; background:#f0f0f0; color:#787c82; border-radius:99px; padding:2px 8px; }

	/* Done banner */
	.pzwiz-done-banner { display:flex; align-items:center; gap:8px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:11px 14px; font-size:13px; color:#166534; margin-bottom:20px; flex-wrap:wrap; }
	.pzwiz-done-banner .dashicons { color:#00a32a; flex-shrink:0; }

	/* Fields */
	.pzwiz-fields { display:flex; flex-direction:column; gap:24px; margin-bottom:28px; }
	.pzwiz-field { display:grid; grid-template-columns: 1fr 1fr; gap:16px 24px; align-items:start; padding-bottom:22px; border-bottom:1px solid #f5f5f5; }
	.pzwiz-field:last-child { border-bottom:none; padding-bottom:0; }
	@media (max-width:900px) { .pzwiz-field { grid-template-columns:1fr; } }
	.pzwiz-field__label { font-size:14px; font-weight:600; color:#1d2023; margin-bottom:4px; display:block; }
	.pzwiz-field__tip   { margin:4px 0 0; font-size:13px; color:#646970; line-height:1.6; }
	.pzwiz-field__notice { font-size:12px; color:#b32d2e; margin:6px 0 0; }
	.pzwiz-input  { width:120px; padding:7px 10px; border:1px solid #8c8f94; border-radius:4px; font-size:13px; }
	.pzwiz-input--wide { width:100%; max-width:340px; box-sizing:border-box; }
	.pzwiz-select { min-width:180px; padding:7px 10px; border:1px solid #8c8f94; border-radius:4px; font-size:13px; }

	/* Toggle */
	.pzwiz-toggle { display:flex; align-items:center; gap:10px; cursor:pointer; }
	.pzwiz-toggle input[type=checkbox] { position:absolute; opacity:0; width:0; height:0; }
	.pzwiz-toggle__track { display:inline-flex; align-items:center; width:40px; height:22px; border-radius:99px; background:#c3c4c7; transition:background .2s; flex-shrink:0; position:relative; }
	.pzwiz-toggle input:checked ~ .pzwiz-toggle__track { background:#00a32a; }
	.pzwiz-toggle__thumb { position:absolute; left:3px; width:16px; height:16px; border-radius:50%; background:#fff; box-shadow:0 1px 3px rgba(0,0,0,.3); transition:left .2s; }
	.pzwiz-toggle input:checked ~ .pzwiz-toggle__track .pzwiz-toggle__thumb { left:21px; }
	.pzwiz-toggle__lbl { font-size:13px; color:#3c434a; }

	/* Color */
	.pzwiz-color-wrap { display:flex; align-items:center; gap:10px; }
	.pzwiz-color { width:48px; height:36px; padding:2px; border:1px solid #8c8f94; border-radius:4px; cursor:pointer; }
	.pzwiz-color-text { width:80px; padding:7px 10px; border:1px solid #8c8f94; border-radius:4px; font-size:13px; font-family:monospace; background:#f6f7f7; }

	/* Range */
	.pzwiz-range-wrap { display:flex; align-items:center; gap:12px; }
	.pzwiz-range { width:200px; }
	.pzwiz-range-val { font-size:13px; font-weight:600; color:#2271b1; min-width:50px; }

	/* Footer */
	.pzwiz-step-footer { display:flex; align-items:center; gap:10px; flex-wrap:wrap; padding-top:20px; border-top:1px solid #f0f0f0; }
	.pzwiz-save-btn { background:#2271b1; border-color:#2271b1; color:#fff; display:inline-flex; align-items:center; gap:5px; }
	.pzwiz-save-btn:hover { background:#135e96; border-color:#135e96; color:#fff; }
	.pzwiz-save-btn .dashicons { font-size:14px !important; width:14px !important; height:14px !important; }
	.pzwiz-nav-btns { display:flex; gap:8px; margin-left:auto; }
	</style>
	<?php }
}
