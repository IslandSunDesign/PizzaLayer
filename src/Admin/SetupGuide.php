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

		// Handle quickstart CTA dismissal
		if (
			isset( $_GET['pizzalayer_dismiss_quickstart_cta'] )
			&& check_admin_referer( 'pizzalayer_dismiss_quickstart_cta' )
		) {
			update_user_meta( get_current_user_id(), 'pizzalayer_quickstart_cta_dismissed', true );
		}
		$show_quickstart_cta = ! get_user_meta( get_current_user_id(), 'pizzalayer_quickstart_cta_dismissed', true );

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

		// ── Extra auto-detection signals ────────────────────────────────
		$has_layer_images = $this->any_layer_image_exists();          // images step
		$builder_embedded = $this->builder_is_embedded();             // shortcode step
		$builder_viewed   = (bool) get_option( 'pizzalayer_builder_viewed', false ); // test step

		// ── Checklist definition ─────────────────────────────────────────
		$checklist = [
			[
				'key'        => 'install',
				'label'      => __( 'Install &amp; activate PizzaLayer', 'pizzalayer' ),
				'desc'       => __( 'You\'re reading this — done!', 'pizzalayer' ),
				'auto_done'  => true,
				'link'       => null,
				'link_label' => null,
			],
			[
				'key'        => 'images',
				'label'      => __( 'Prepare your layer images', 'pizzalayer' ),
				'desc'       => __( 'Each ingredient needs a transparent PNG layer image (800×800 px). Use PizzaLayer → Layer Image Maker to crop, adjust, and export your images — or create them when adding each item. Auto-completes once any layer has an image.', 'pizzalayer' ),
				'detected'   => $has_layer_images,
				'auto_done'  => $has_layer_images || isset( $done['images'] ),
				'manual'     => true,
				'link'       => admin_url( 'admin.php?page=pizzalayer-layer-maker' ),
				'link_label' => __( 'Layer Image Maker', 'pizzalayer' ),
			],
			[
				'key'        => 'crusts',
				'label'      => __( 'Add at least one Crust', 'pizzalayer' ),
				'desc'       => __( 'Go to PizzaLayer → Crusts and publish a crust with a layer image.', 'pizzalayer' ),
				'auto_done'  => $stats['crusts'] > 0,
				'link'       => admin_url( 'post-new.php?post_type=pizzalayer_crusts' ),
				'link_label' => __( 'Add Crust', 'pizzalayer' ),
				'count'      => $stats['crusts'],
			],
			[
				'key'        => 'sauces',
				'label'      => __( 'Add at least one Sauce', 'pizzalayer' ),
				'desc'       => __( 'Go to PizzaLayer → Sauces and publish a sauce with a layer image.', 'pizzalayer' ),
				'auto_done'  => $stats['sauces'] > 0,
				'link'       => admin_url( 'post-new.php?post_type=pizzalayer_sauces' ),
				'link_label' => __( 'Add Sauce', 'pizzalayer' ),
				'count'      => $stats['sauces'],
			],
			[
				'key'        => 'cheeses',
				'label'      => __( 'Add at least one Cheese', 'pizzalayer' ),
				'desc'       => __( 'Go to PizzaLayer → Cheeses and publish a cheese with a layer image.', 'pizzalayer' ),
				'auto_done'  => $stats['cheeses'] > 0,
				'link'       => admin_url( 'post-new.php?post_type=pizzalayer_cheeses' ),
				'link_label' => __( 'Add Cheese', 'pizzalayer' ),
				'count'      => $stats['cheeses'],
			],
			[
				'key'        => 'toppings',
				'label'      => __( 'Add your Toppings', 'pizzalayer' ),
				'desc'       => __( 'Toppings are the heart of the builder — add as many as your menu needs.', 'pizzalayer' ),
				'auto_done'  => $stats['toppings'] > 0,
				'link'       => admin_url( 'post-new.php?post_type=pizzalayer_toppings' ),
				'link_label' => __( 'Add Topping', 'pizzalayer' ),
				'count'      => $stats['toppings'],
			],
			[
				'key'        => 'drizzles',
				'label'      => __( 'Add Drizzles <em>(optional)</em>', 'pizzalayer' ),
				'desc'       => __( 'Finishing touch layers — hot honey, balsamic, ranch. Optional but delightful.', 'pizzalayer' ),
				'auto_done'  => $stats['drizzles'] > 0 || isset( $done['drizzles'] ),
				'detected'   => $stats['drizzles'] > 0,
				'optional'   => true,
				'manual'     => true,
				'link'       => admin_url( 'post-new.php?post_type=pizzalayer_drizzles' ),
				'link_label' => __( 'Add Drizzle', 'pizzalayer' ),
				'count'      => $stats['drizzles'],
			],
			[
				'key'        => 'cuts',
				'label'      => __( 'Add Cut styles <em>(optional)</em>', 'pizzalayer' ),
				'desc'       => __( 'Slice overlay layers — triangle, square, party, whole. Optional.', 'pizzalayer' ),
				'auto_done'  => $stats['cuts'] > 0 || isset( $done['cuts'] ),
				'detected'   => $stats['cuts'] > 0,
				'optional'   => true,
				'manual'     => true,
				'link'       => admin_url( 'post-new.php?post_type=pizzalayer_cuts' ),
				'link_label' => __( 'Add Cut Style', 'pizzalayer' ),
				'count'      => $stats['cuts'],
			],
			[
				'key'        => 'template',
				'label'      => __( 'Choose a Template', 'pizzalayer' ),
				'desc'       => __( 'Pick the visual theme for your pizza builder in PizzaLayer → Template.', 'pizzalayer' ),
				'auto_done'  => $has_template,
				'link'       => admin_url( 'admin.php?page=pizzalayer-template' ),
				'link_label' => __( 'Choose Template', 'pizzalayer' ),
			],
			[
				'key'        => 'settings',
				'label'      => __( 'Configure Plugin Settings', 'pizzalayer' ),
				'desc'       => __( 'Set your default crust, sauce, max toppings and other options in PizzaLayer → Settings. New to WordPress? Use the Settings Wizard for a friendly guided walk-through.', 'pizzalayer' ),
				'auto_done'  => $has_defaults,
				'link'       => admin_url( 'admin.php?page=pizzalayer-wizard' ),
				'link_label' => __( '✦ Settings Wizard', 'pizzalayer' ),
			],
			[
				'key'        => 'shortcode',
				'label'      => __( 'Embed the Builder on a page', 'pizzalayer' ),
				'desc'       => __( 'Use the Shortcode Generator to get your <code>[pizza_builder]</code> shortcode, then add it to any page. Auto-completes once the shortcode is found in published content.', 'pizzalayer' ),
				'detected'   => $builder_embedded,
				'auto_done'  => $builder_embedded || isset( $done['shortcode'] ),
				'manual'     => true,
				'link'       => admin_url( 'admin.php?page=pizzalayer-shortcodes' ),
				'link_label' => __( 'Shortcode Generator', 'pizzalayer' ),
			],
			[
				'key'        => 'test',
				'label'      => __( 'View your builder on the front end', 'pizzalayer' ),
				'desc'       => __( 'Visit your builder page as a customer and confirm the layers display correctly. Auto-completes the first time the builder renders on your live site.', 'pizzalayer' ),
				'detected'   => $builder_viewed,
				'auto_done'  => $builder_viewed || isset( $done['test'] ),
				'manual'     => true,
				'link'       => home_url( '/' ),
				'link_label' => __( 'View Site', 'pizzalayer' ),
			],
		];

		$done_count  = count( array_filter( $checklist, fn( $i ) => $i['auto_done'] ?? false ) );
		$total_count = count( $checklist );
		$pct         = (int) round( $done_count / $total_count * 100 );

		?>
		<div class="wrap psg-wrap">

		<?php $this->render_styles(); ?>

		<!-- ══ Header ══════════════════════════════════════════════════ -->
		<div class="psg-header">
			<span class="dashicons dashicons-welcome-learn-more psg-header__icon"></span>
			<div style="flex:1;">
				<h1 class="psg-header__title"><?php esc_html_e( 'Setup Guide', 'pizzalayer' ); ?></h1>
				<p class="psg-header__sub"><?php esc_html_e( 'Everything you need to get PizzaLayer up and running — in the right order.', 'pizzalayer' ); ?></p>
			</div>
			<div style="display:flex;gap:8px;flex-wrap:wrap;flex-shrink:0;">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=pizzalayer' ) ); ?>" class="button" style="background:rgba(255,255,255,.15);border-color:rgba(255,255,255,.3);color:#fff;">
					<span class="dashicons dashicons-dashboard" style="font-size:14px;width:14px;height:14px;"></span> <?php esc_html_e( 'Dashboard', 'pizzalayer' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=pizzalayer-settings' ) ); ?>" class="button" style="background:rgba(255,255,255,.15);border-color:rgba(255,255,255,.3);color:#fff;">
					<span class="dashicons dashicons-admin-generic" style="font-size:14px;width:14px;height:14px;"></span> <?php esc_html_e( 'Settings', 'pizzalayer' ); ?>
				</a>
			</div>
		</div>

		<!-- ══ Progress bar ════════════════════════════════════════════ -->
		<div class="psg-card psg-progress-card">
			<div class="psg-progress-bar-wrap">
				<div class="psg-progress-bar" style="width:<?php echo esc_attr( (string) $pct ); ?>%"></div>
			</div>
			<div class="psg-progress-labels">
				<span><?php printf( /* translators: 1: completed step count, 2: total step count. */ esc_html__( '%1$d of %2$d steps complete', 'pizzalayer' ), (int) $done_count, (int) $total_count ); ?></span>
				<span class="psg-pct"><?php echo esc_html( (string) $pct ); ?>%</span>
			</div>
		</div>

		<!-- ══ Checklist ════════════════════════════════════════════════ -->
		<div class="psg-card">
			<div class="psg-card__head">
				<h2><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'Setup Checklist', 'pizzalayer' ); ?></h2>
				<p><?php esc_html_e( 'Work through these steps in order. Auto-detected items update as you add content.', 'pizzalayer' ); ?></p>
			</div>
			<form method="post" action="">
				<?php wp_nonce_field( 'pizzalayer_setup_checklist' ); ?>
				<ol class="psg-checklist">
				<?php foreach ( $checklist as $idx => $item ) :
					$is_done  = $item['auto_done'] ?? false;
					$optional = $item['optional'] ?? false;
					$manual   = ! empty( $item['manual'] );           // step supports a manual fallback toggle
					$detected = ! empty( $item['detected'] );          // real auto-signal fired (independent of manual mark)
					// "Undo" only makes sense when the step is done purely because it
					// was hand-marked — never when an auto-signal is holding it true.
					$manual_only_done = $is_done && $manual && ! $detected;
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
									<span class="psg-cl-badge"><?php echo esc_html( (string) $item['count'] ); ?> <?php esc_html_e( 'added', 'pizzalayer' ); ?></span>
								<?php endif; ?>
								<?php if ( $optional ) : ?>
									<span class="psg-cl-opt-badge"><?php esc_html_e( 'optional', 'pizzalayer' ); ?></span>
								<?php endif; ?>
							</div>
							<div class="psg-cl-desc"><?php echo wp_kses_post( $item['desc'] ); ?></div>
						</div>
						<div class="psg-cl-actions">
							<?php if ( ! empty( $item['link'] ) && ! $is_done ) : ?>
							<a href="<?php echo esc_url( $item['link'] ); ?>" class="button button-small">
								<?php echo esc_html( $item['link_label'] ?? __( 'Go', 'pizzalayer' ) ); ?> →
							</a>
							<?php elseif ( ! empty( $item['link'] ) ) : ?>
							<a href="<?php echo esc_url( $item['link'] ); ?>" class="button button-small button-secondary">
								<?php echo esc_html( $item['link_label'] ?? __( 'View', 'pizzalayer' ) ); ?>
							</a>
							<?php endif; ?>
							<?php if ( $manual && ! $is_done ) : ?>
							<button type="submit" name="pizzalayer_setup_done" value="<?php echo esc_attr( $item['key'] ); ?>" class="button button-small psg-mark-done">
								<input type="hidden" name="checked" value="1"><?php echo $optional ? esc_html__( 'Skip / mark done', 'pizzalayer' ) : esc_html__( 'Mark done', 'pizzalayer' ); ?>
							</button>
							<?php elseif ( $manual_only_done ) : ?>
							<button type="submit" name="pizzalayer_setup_done" value="<?php echo esc_attr( $item['key'] ); ?>" class="button button-small psg-mark-undone">
								<input type="hidden" name="checked" value="0"><?php esc_html_e( 'Undo', 'pizzalayer' ); ?>
							</button>
							<?php endif; ?>
						</div>
					</li>
				<?php endforeach; ?>
				</ol>
			</form>
		</div>

		<!-- ══ Quickstart CTA ══════════════════════════════════════════ -->
		<?php if ( $show_quickstart_cta ) : ?>
		<div class="psg-quickstart-cta">
			<div class="psg-quickstart-cta__icon">🚀</div>
			<div class="psg-quickstart-cta__body">
				<?php echo wp_kses_post( __( '<strong>New to PizzaLayer?</strong> Head to the full Help &amp; Reference page for the complete Quickstart guide — five clear steps from a blank install to a live interactive builder.', 'pizzalayer' ) ); ?>
				<div class="psg-quickstart-cta__actions">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=pizzalayer-help&section=quickstart' ) ); ?>" class="button button-primary">
						<span class="dashicons dashicons-book-alt"></span> <?php esc_html_e( 'View Quickstart Guide', 'pizzalayer' ); ?>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=pizzalayer-help' ) ); ?>" class="button">
						<span class="dashicons dashicons-editor-help"></span> <?php esc_html_e( 'Help &amp; Reference', 'pizzalayer' ); ?>
					</a>
				</div>
			</div>
			<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'pizzalayer_dismiss_quickstart_cta', '1' ), 'pizzalayer_dismiss_quickstart_cta' ) ); ?>"
			   class="psg-quickstart-cta__dismiss" title="<?php esc_attr_e( 'Dismiss', 'pizzalayer' ); ?>">✕</a>
		</div>
		<?php endif; ?>

		<!-- ══ Help footer ════════════════════════════════════════════ -->
		<div class="psg-card psg-card--help">
			<span class="dashicons dashicons-sos"></span>
			<div>
				<h3><?php esc_html_e( 'Need help?', 'pizzalayer' ); ?></h3>
				<p><?php printf( wp_kses_post( /* translators: %s = contact link. */ __( 'Check the documentation or reach out through %s.', 'pizzalayer' ) ), '<a href="https://islandsundesign.com" target="_blank" rel="noopener">IslandSunDesign.com</a>' ); ?></p>
			</div>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=pizzalayer' ) ); ?>" class="button"><?php esc_html_e( '← Back to Dashboard', 'pizzalayer' ); ?></a>
		</div>

		</div><!-- /.wrap -->

		<?php
	}

	/**
	 * Whether any published layer post already has an image — either a native
	 * "{type}_layer_image" meta value or a featured image. Works with or without
	 * SCF/ACF. One bounded query; the Setup Guide is loaded infrequently.
	 */
	private function any_layer_image_exists(): bool {
		global $wpdb;

		$post_types = "'pizzalayer_crusts','pizzalayer_sauces','pizzalayer_cheeses','pizzalayer_toppings','pizzalayer_drizzles','pizzalayer_cuts'";
		$meta_keys  = "'crust_layer_image','sauce_layer_image','cheese_layer_image','topping_layer_image','drizzle_layer_image','cut_layer_image'";

		// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL, PluginCheck.Security.DirectDB.UnescapedDBParameter -- $post_types/$meta_keys are hardcoded constant lists defined above, not user input; no injection vector.
		$found = $wpdb->get_var(
			"SELECT pm.post_id
			   FROM {$wpdb->postmeta} pm
			   INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			  WHERE p.post_status = 'publish'
			    AND p.post_type IN ( {$post_types} )
			    AND (
			        ( pm.meta_key IN ( {$meta_keys} ) AND pm.meta_value <> '' AND pm.meta_value <> '0' )
			        OR pm.meta_key = '_thumbnail_id'
			    )
			  LIMIT 1"
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL, PluginCheck.Security.DirectDB.UnescapedDBParameter

		return ! empty( $found );
	}

	/**
	 * Whether the [pizza_builder] shortcode appears in any published content.
	 */
	private function builder_is_embedded(): bool {
		global $wpdb;
		$like = '%' . $wpdb->esc_like( '[pizza_builder' ) . '%';

		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		$found = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts}
				  WHERE post_status = 'publish'
				    AND post_content LIKE %s
				  LIMIT 1",
				$like
			)
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery

		return ! empty( $found );
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
	/* Quickstart CTA */
	.psg-quickstart-cta {
		display:flex; align-items:flex-start; gap:14px;
		background:linear-gradient(135deg,#f0f6ff,#e8f3ff); border:1px solid #b9d4f5;
		border-radius:10px; padding:18px 20px; margin-bottom:20px; position:relative;
	}
	.psg-quickstart-cta__icon { font-size:26px; flex-shrink:0; margin-top:1px; }
	.psg-quickstart-cta__body { flex:1; font-size:13px; color:#1d2023; line-height:1.6; }
	.psg-quickstart-cta__body strong { font-weight:700; }
	.psg-quickstart-cta__actions { display:flex; gap:8px; flex-wrap:wrap; margin-top:12px; }
	.psg-quickstart-cta__actions .button { display:inline-flex; align-items:center; gap:5px; }
	.psg-quickstart-cta__actions .dashicons { font-size:14px !important; width:14px !important; height:14px !important; }
	.psg-quickstart-cta__dismiss {
		color:#787c82; text-decoration:none; font-size:14px; flex-shrink:0;
		padding:4px 7px; border-radius:4px; transition:background .15s; line-height:1;
		border:1px solid transparent;
	}
	.psg-quickstart-cta__dismiss:hover { background:#dce8f7; color:#2271b1; border-color:#b9d4f5; }
	</style>
	<?php }
}
