<?php
namespace PizzaLayer\Admin;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Template selection page — with live iframe preview.
 *
 * Layout: split pane — template cards on the left, live iframe on the right.
 * Hovering a card loads that template into the iframe via a signed preview URL.
 * Clicking Activate writes to the DB.
 */
class TemplateChoice {

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) { return; }

		// Handle activation
		if ( isset( $_POST['pizzalayer_activate_template'], $_POST['_wpnonce'] )
		     && wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'pizzalayer_activate_template' ) ) {
			$slug = sanitize_key( $_POST['pizzalayer_activate_template'] );
			update_option( 'pizzalayer_setting_global_template', $slug );
			echo '<div class="notice notice-success is-dismissible"><p>' . sprintf( esc_html__( 'Template %s activated.', 'pizzalayer' ), '<strong>' . esc_html( $slug ) . '</strong>' ) . '</p></div>';
		}

		$active = (string) get_option( 'pizzalayer_setting_global_template', 'nightpie' );

		// ── Handle template settings save ──────────────────────────
		if ( isset( $_POST['pizzalayer_template_settings_save'], $_POST['_wpnonce'] )
		     && wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'pizzalayer_template_settings_save' ) ) {
			$this->save_template_settings();
			echo '<div class="notice notice-success is-dismissible"><p><strong>' . esc_html__( 'Template settings saved.', 'pizzalayer' ) . '</strong></p></div>';
			// Re-read active after save
			$active = (string) get_option( 'pizzalayer_setting_global_template', 'nightpie' );
		}

		// ── Scan template directories ───────────────────────────────
		$plugin_dir = PIZZALAYER_TEMPLATES_DIR;
		$plugin_url = PIZZALAYER_TEMPLATES_URL;
		$theme_dir  = trailingslashit( get_stylesheet_directory() ) . 'pizzalayer/';
		$theme_url  = trailingslashit( get_stylesheet_directory_uri() ) . 'pizzalayer/';

		$templates = [];
		foreach ( [ [ $plugin_dir, $plugin_url, 'plugin' ], [ $theme_dir, $theme_url, 'theme' ] ] as [ $dir, $url, $source ] ) {
			if ( ! is_dir( $dir ) ) { continue; }
			foreach ( (array) scandir( $dir ) as $folder ) {
				if ( $folder === '.' || $folder === '..' || ! is_dir( $dir . $folder ) ) { continue; }
				$info_file = $dir . $folder . '/pztp-template-info.php';
				$info      = file_exists( $info_file ) ? include $info_file : [];
				if ( ! is_array( $info ) ) { $info = []; }

				$preview_url = '';
				foreach ( [ 'preview.jpg', 'preview.png', 'preview.webp' ] as $pf ) {
					if ( file_exists( $dir . $folder . '/' . $pf ) ) {
						$preview_url = $url . $folder . '/' . $pf;
						break;
					}
				}

				$templates[ $folder ] = [
					'slug'        => $folder,
					'source'      => $source,
					'dir'         => $dir . $folder . '/',
					'url'         => $url . $folder . '/',
					'info'        => $info,
					'preview_url' => $preview_url,
				];
			}
		}

		// ── Load active template settings fields ─────────────────────
		$template_settings = [];
		if ( $active ) {
			$options_paths = [
				get_stylesheet_directory() . '/pzttemplates/' . $active . '/pztp-template-options.php',
				PIZZALAYER_TEMPLATES_DIR . $active . '/pztp-template-options.php',
			];
			foreach ( $options_paths as $options_file ) {
				if ( file_exists( $options_file ) ) {
					$template_settings = include $options_file;
					if ( ! is_array( $template_settings ) ) { $template_settings = []; }
					break;
				}
			}
		}

		// ── Preview page URL ────────────────────────────────────────
		$preview_page_url  = (string) get_option( 'pizzalayer_template_preview_url', '' );
		$preview_page_auto = false; // true when we found a page automatically

		if ( ! $preview_page_url ) {
			// Search post_content for [pizza_builder] shortcode
			global $wpdb;
			$found_id = $wpdb->get_var(
				"SELECT ID FROM {$wpdb->posts}
				 WHERE post_status = 'publish'
				   AND post_type IN ('page','post')
				   AND post_content LIKE '%pizza_builder%'
				 LIMIT 1"
			);
			if ( $found_id ) {
				$preview_page_url  = (string) get_permalink( (int) $found_id );
				$preview_page_auto = true;
			} else {
				$preview_page_url = home_url( '/' );
			}
		}
		$preview_page_url = trailingslashit( esc_url_raw( $preview_page_url ) );

		// Build per-template preview URLs (signed with a nonce)
		$preview_urls = [];
		foreach ( $templates as $slug => $tpl ) {
			$nonce = wp_create_nonce( 'pizzalayer_preview_' . $slug );
			$preview_urls[ $slug ] = add_query_arg( [
				'pzl_preview' => $slug,
				'pzl_nonce'   => $nonce,
			], $preview_page_url );
		}

		// Active template preview URL (no override needed — just the raw page)
		$active_preview_url = $preview_urls[ $active ] ?? $preview_page_url;
		$active_name        = $templates[ $active ]['info']['name'] ?? ucwords( str_replace( '-', ' ', $active ) );

		?>
		<div class="wrap ptc-wrap">
		<?php $this->render_styles(); ?>

		<!-- ══ Header ════════════════════════════════════════════════ -->
		<div class="ptc-header">
			<span class="dashicons dashicons-admin-appearance ptc-header__icon"></span>
			<div>
				<h1 class="ptc-header__title"><?php esc_html_e( 'Choose a Template', 'pizzalayer' ); ?></h1>
				<p class="ptc-header__sub"><?php esc_html_e( 'Select the visual style for your pizza builder. Preview any template live, then activate it — your content and settings stay intact.', 'pizzalayer' ); ?></p>
			</div>
			<div class="ptc-header__actions">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=pizzalayer-settings' ) ); ?>" class="button">
					<span class="dashicons dashicons-admin-generic"></span> <?php esc_html_e( 'Settings', 'pizzalayer' ); ?>
				</a>
				<button type="button" class="button ptc-edit-preview-url" id="ptc-edit-preview-url">
					<span class="dashicons dashicons-admin-links"></span> <?php esc_html_e( 'Preview URL', 'pizzalayer' ); ?>
				</button>
			</div>
		</div>

		<!-- ══ Preview URL editor (inline collapsible) ══════════════ -->
		<div class="ptc-preview-url-bar" id="ptc-preview-url-bar" style="display:none;">
			<form method="post" action="" class="ptc-preview-url-form">
				<?php wp_nonce_field( 'pizzalayer_save_preview_url' ); ?>
				<input type="hidden" name="pizzalayer_save_preview_url" value="1">
				<label class="ptc-preview-url-label">
					<span class="dashicons dashicons-admin-links"></span>
					<?php echo wp_kses_post( __( 'Preview page URL — enter any page on your site that contains <code>[pizza_builder]</code>:', 'pizzalayer' ) ); ?>
				</label>
				<div class="ptc-preview-url-row">
					<input type="url" name="pizzalayer_template_preview_url"
					       class="ptc-preview-url-input"
					       value="<?php echo esc_attr( (string) get_option( 'pizzalayer_template_preview_url', '' ) ); ?>"
					       placeholder="<?php echo esc_attr( $preview_page_url ); ?>">
					<button type="submit" class="button button-primary"><?php esc_html_e( 'Save', 'pizzalayer' ); ?></button>
					<button type="button" class="button ptc-cancel-preview-url" id="ptc-cancel-preview-url"><?php esc_html_e( 'Cancel', 'pizzalayer' ); ?></button>
				</div>
			</form>
		</div>

		<?php
		// Handle preview URL save
		if ( isset( $_POST['pizzalayer_save_preview_url'], $_POST['_wpnonce'] )
		     && wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'pizzalayer_save_preview_url' ) ) {
			$url = esc_url_raw( wp_unslash( $_POST['pizzalayer_template_preview_url'] ?? '' ) );
			update_option( 'pizzalayer_template_preview_url', $url );
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Preview URL saved.', 'pizzalayer' ) . '</p></div>';
		}
		?>

		<?php if ( $preview_page_auto ) : ?>
		<div class="ptc-notice ptc-notice--info">
			<span class="dashicons dashicons-info-outline"></span>
			Auto-detected preview page: <strong><?php echo esc_html( $preview_page_url ); ?></strong>
			<?php esc_html_e( '— click', 'pizzalayer' ); ?> <strong><?php esc_html_e( 'Preview URL', 'pizzalayer' ); ?></strong> <?php esc_html_e( 'above to change it.', 'pizzalayer' ); ?>
		</div>
		<?php elseif ( $preview_page_url === trailingslashit( home_url( '/' ) ) ) : ?>
		<div class="ptc-notice ptc-notice--warn">
			<span class="dashicons dashicons-warning"></span>
			No page with <code>[pizza_builder]</code> found — previewing the homepage.
			<?php esc_html_e( 'Click', 'pizzalayer' ); ?> <strong><?php esc_html_e( 'Preview URL', 'pizzalayer' ); ?></strong> <?php esc_html_e( 'above to set the correct page.', 'pizzalayer' ); ?>
		</div>
		<?php endif; ?>

		<?php if ( empty( $templates ) ) : ?>
		<div class="ptc-card ptc-empty">
			<span class="dashicons dashicons-warning"></span>
			<p><?php echo wp_kses_post( __( 'No templates found. Make sure at least the <code>nightpie</code> folder exists in the plugin&#8217;s <code>/templates/</code> directory.', 'pizzalayer' ) ); ?></p>
		</div>
		<?php else : ?>

		<!-- ══ Hero section ══════════════════════════════════════════ -->
		<div class="ptc-hero">
			<div class="ptc-hero__left">
				<div class="ptc-hero__badge">
					<span class="dashicons dashicons-admin-appearance"></span>
					<?php echo esc_html( count( $templates ) ); ?> template<?php echo count( $templates ) !== 1 ? 's' : ''; ?> available
				</div>
				<h2 class="ptc-hero__heading"><?php esc_html_e( 'Pick your style, then make it yours.', 'pizzalayer' ); ?></h2>
				<p class="ptc-hero__body"><?php echo wp_kses_post( __( 'Each template is a complete, self-contained builder experience — different layout, different feel, same content. Hover a card to preview it live in the pane on the right. When you find the one, hit <strong>Activate</strong>.', 'pizzalayer' ) ); ?></p>
				<p class="ptc-hero__body"><?php echo wp_kses_post( __( 'Once activated, the <strong>Template Settings</strong> panel below lets you fine-tune colors, fonts, and layout options specific to that template.', 'pizzalayer' ) ); ?></p>
			</div>
			<div class="ptc-hero__right">
				<div class="ptc-hero__pill">
					<span class="dashicons dashicons-yes-alt ptc-hero__pill-icon ptc-hero__pill-icon--green"></span>
					<div>
						<span class="ptc-hero__pill-label"><?php esc_html_e( 'Currently Active', 'pizzalayer' ); ?></span>
						<span class="ptc-hero__pill-val"><?php echo esc_html( $active_name ); ?></span>
					</div>
				</div>
				<div class="ptc-hero__pill">
					<span class="dashicons dashicons-welcome-learn-more ptc-hero__pill-icon"></span>
					<div>
						<span class="ptc-hero__pill-label"><?php esc_html_e( 'How it works', 'pizzalayer' ); ?></span>
						<span class="ptc-hero__pill-val ptc-hero__pill-val--sm"><?php esc_html_e( 'Hover → Preview → Activate → Customise', 'pizzalayer' ); ?></span>
					</div>
				</div>
				<div class="ptc-hero__pill">
					<span class="dashicons dashicons-shield ptc-hero__pill-icon"></span>
					<div>
						<span class="ptc-hero__pill-label"><?php esc_html_e( 'Safe to switch', 'pizzalayer' ); ?></span>
						<span class="ptc-hero__pill-val ptc-hero__pill-val--sm"><?php esc_html_e( 'Content &amp; settings are never affected', 'pizzalayer' ); ?></span>
					</div>
				</div>
			</div>
		</div>

		<!-- ══ Main split layout ══════════════════════════════════════ -->
		<div class="ptc-split">

			<!-- Left: template list -->
			<div class="ptc-list" id="ptc-list">
				<?php foreach ( $templates as $slug => $tpl ) :
					$info      = $tpl['info'];
					$is_active = $slug === $active;
					$purl      = $preview_urls[ $slug ] ?? $preview_page_url;
				?>
				<div class="ptc-item<?php echo $is_active ? ' ptc-item--active' : ''; ?>"
				     id="ptc-item-<?php echo esc_attr( $slug ); ?>"
				     data-slug="<?php echo esc_attr( $slug ); ?>"
				     data-preview-url="<?php echo esc_attr( $purl ); ?>"
				     data-name="<?php echo esc_attr( $info['name'] ?? ucwords( str_replace( '-', ' ', $slug ) ) ); ?>">

					<!-- Thumbnail -->
					<div class="ptc-item__thumb">
						<?php if ( $tpl['preview_url'] ) : ?>
						<img src="<?php echo esc_url( $tpl['preview_url'] ); ?>"
						     alt="<?php echo esc_attr( $slug ); ?>" loading="lazy">
						<?php else : ?>
						<div class="ptc-item__thumb-placeholder">
							<span class="dashicons dashicons-admin-appearance"></span>
						</div>
						<?php endif; ?>
						<?php if ( $is_active ) : ?>
						<span class="ptc-item__active-dot" title="Active"></span>
						<?php endif; ?>
					</div>

					<!-- Info -->
					<div class="ptc-item__info">
						<div class="ptc-item__name">
							<?php echo esc_html( $info['name'] ?? ucwords( str_replace( '-', ' ', $slug ) ) ); ?>
							<?php if ( $is_active ) : ?>
							<span class="ptc-item__active-badge"><?php esc_html_e( 'Active', 'pizzalayer' ); ?></span>
							<?php endif; ?>
						</div>
						<?php if ( ! empty( $info['description'] ) ) : ?>
						<p class="ptc-item__desc"><?php echo esc_html( $info['description'] ); ?></p>
						<?php endif; ?>
						<?php if ( ! empty( $info['tags'] ) && is_array( $info['tags'] ) ) : ?>
						<div class="ptc-item__tags">
							<?php foreach ( array_slice( $info['tags'], 0, 4 ) as $tag ) : ?>
							<span class="ptc-item__tag"><?php echo esc_html( $tag ); ?></span>
							<?php endforeach; ?>
						</div>
						<?php endif; ?>
					</div>

					<!-- Action -->
					<div class="ptc-item__action">
						<?php if ( $purl ) : ?>
						<button type="button" class="button ptc-preview-btn"
						        data-preview-url="<?php echo esc_attr( $purl ); ?>"
						        data-name="<?php echo esc_attr( $info['name'] ?? $slug ); ?>">
							<span class="dashicons dashicons-visibility"></span> <?php esc_html_e( 'Preview', 'pizzalayer' ); ?>
						</button>
						<?php endif; ?>
						<?php if ( $is_active ) : ?>
						<span class="ptc-item__check dashicons dashicons-yes-alt"></span>
						<?php else : ?>
						<button class="button button-primary ptc-activate-btn"
						        data-slug="<?php echo esc_attr( $slug ); ?>"
						        data-name="<?php echo esc_attr( $info['name'] ?? $slug ); ?>">
						<?php esc_html_e( 'Activate', 'pizzalayer' ); ?>
						</button>
						<?php endif; ?>
					</div>

				</div>
				<?php endforeach; ?>

				<!-- Dev card at bottom of list -->
				<div class="ptc-list__devcard">
					<span class="dashicons dashicons-admin-plugins"></span>
					<div>
						<strong>Custom templates</strong> — add a folder at
						<code><?php echo esc_html( get_stylesheet_directory() ); ?>/pizzalayer/your-slug/</code>
					</div>
				</div>
			</div>

			<!-- Right: live preview iframe -->
			<div class="ptc-preview-pane" id="ptc-preview-pane">
				<div class="ptc-preview-bar">
					<div class="ptc-preview-bar__dots">
						<span></span><span></span><span></span>
					</div>
					<div class="ptc-preview-bar__url" id="ptc-preview-label">
						<?php echo esc_html( $info['name'] ?? ucwords( str_replace( '-', ' ', $active ) ) ); ?> — <?php esc_html_e( 'Live Preview', 'pizzalayer' ); ?>
					</div>
					<div class="ptc-preview-bar__actions">
						<button type="button" class="ptc-preview-bar__btn" id="ptc-preview-reload" title="Reload preview">
							<span class="dashicons dashicons-image-rotate"></span>
						</button>
						<a href="<?php echo esc_url( $preview_page_url ); ?>" target="_blank"
						   class="ptc-preview-bar__btn" title="Open in new tab">
							<span class="dashicons dashicons-external"></span>
						</a>
					</div>
				</div>
				<div class="ptc-iframe-wrap" id="ptc-iframe-wrap">
					<div class="ptc-iframe-loading" id="ptc-iframe-loading">
						<div class="ptc-iframe-loading__spinner"></div>
						<p>Loading preview…</p>
					</div>
					<iframe
						id="ptc-preview-frame"
						class="ptc-preview-frame"
						src="<?php echo esc_attr( $active_preview_url ); ?>"
						title="Live template preview"
						sandbox="allow-scripts allow-same-origin allow-forms allow-popups"
					></iframe>
				</div>
			</div>

		</div><!-- /.ptc-split -->

		<?php endif; ?>

		<!-- ══ Confirmation modal ════════════════════════════════════ -->
		<div id="ptc-modal" class="ptc-modal" role="dialog" aria-modal="true"
		     aria-labelledby="ptc-modal-title" style="display:none;">
			<div class="ptc-modal__box">
				<div class="ptc-modal__header">
					<h2 id="ptc-modal-title" class="ptc-modal__title">
						<span class="dashicons dashicons-admin-appearance"></span> <?php esc_html_e( 'Activate Template?', 'pizzalayer' ); ?>
					</h2>
				</div>
				<div class="ptc-modal__body">
					<p><?php esc_html_e( 'Activating', 'pizzalayer' ); ?> <strong id="ptc-modal-name"></strong> <?php esc_html_e( 'will apply it to your live site immediately.', 'pizzalayer' ); ?></p>
					<p class="ptc-modal__note"><?php esc_html_e( 'Your existing content and settings are unaffected — only the front-end visual design will change.', 'pizzalayer' ); ?></p>
				</div>
				<div class="ptc-modal__footer">
					<button id="ptc-modal-cancel" class="button button-secondary"><?php esc_html_e( 'Cancel', 'pizzalayer' ); ?></button>
					<form method="post" action="" id="ptc-activate-form" style="display:inline;">
						<?php wp_nonce_field( 'pizzalayer_activate_template' ); ?>
						<input type="hidden" name="pizzalayer_activate_template" id="ptc-modal-slug" value="">
						<button type="submit" class="button button-primary">
							<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Yes, Activate', 'pizzalayer' ); ?>
						</button>
					</form>
				</div>
			</div>
			<div class="ptc-modal__overlay" id="ptc-modal-overlay"></div>
		</div>

		<!-- ══ Template Settings panel ═════════════════════════════ -->
		<?php if ( $active && ! empty( $template_settings ) ) : ?>
		<div class="ptc-settings-card" id="template-settings">
			<div class="ptc-settings-card__head">
				<div>
					<h2>
						<span class="dashicons dashicons-admin-appearance"></span>
						<?php echo esc_html( ucwords( str_replace( '-', ' ', $active ) ) ); ?> Template Settings
						<span class="ptc-settings-card__badge"><?php esc_html_e( 'Active Template', 'pizzalayer' ); ?></span>
					</h2>
					<p>These settings apply only to the <strong><?php echo esc_html( ucwords( str_replace( '-', ' ', $active ) ) ); ?></strong> template. Switching templates shows that template\'s settings instead.</p>
				</div>
			</div>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=pizzalayer-template' ) ); ?>#template-settings" class="ptc-settings-form">
				<?php wp_nonce_field( 'pizzalayer_template_settings_save' ); ?>
				<input type="hidden" name="pizzalayer_template_settings_save" value="1">

				<?php
				// ── Color-scheme/preset chips ──────────────────────────────
				$has_metro_schemes    = ( $active === 'metro' );
				$has_plainlist_presets = ( $active === 'plainlist' );
				$schemes = [];
				if ( $has_metro_schemes ) {
					$schemes = $this->get_metro_color_schemes();
				} elseif ( $has_plainlist_presets ) {
					$schemes = $this->get_plainlist_presets();
				}
				if ( ! empty( $schemes ) ) :
				?>
				<div class="ptc-scheme-row">
					<span class="ptc-scheme-label">Quick Presets:</span>
					<div class="ptc-scheme-chips" id="ptc-scheme-chips">
						<?php foreach ( $schemes as $scheme ) :
							$colors_for_chips = isset( $scheme['colors'] ) ? $scheme['colors'] : array_values( $scheme['keys'] ?? [] );
							$data_key = isset( $scheme['keys'] ) ? 'keys' : 'colors';
							$safe = esc_attr( wp_json_encode( $has_metro_schemes ? $scheme['colors'] : $scheme['keys'] ) );
						?>
						<button type="button" class="ptc-scheme-chip"
						        data-scheme="<?php echo $safe; ?>"
						        title="<?php echo esc_attr( $scheme['name'] ); ?>">
							<span class="ptc-scheme-chip__swatches">
								<?php foreach ( array_slice( $colors_for_chips, 0, 3 ) as $c ) : ?>
								<span class="ptc-scheme-chip__dot" style="background:<?php echo esc_attr( (string) $c ); ?>;"></span>
								<?php endforeach; ?>
							</span>
							<span class="ptc-scheme-chip__name"><?php echo esc_html( $scheme['name'] ); ?></span>
						</button>
						<?php endforeach; ?>
					</div>
				</div>
				<?php endif; ?>

				<!-- Settings grid -->
				<div class="ptc-settings-grid">
				<?php foreach ( $template_settings as $field ) :
					if ( empty( $field['key'] ) || empty( $field['type'] ) ) { continue; }
					$fkey   = esc_attr( $field['key'] );
					$fval   = (string) get_option( $field['key'], $field['default'] ?? '' );
					$flabel = $field['label'] ?? $field['key'];
					$fdesc  = $field['desc']  ?? '';
				?>
				<div class="ptc-field<?php echo ( $field['type'] === 'textarea' || $field['type'] === 'text_wide' ) ? ' ptc-field--full' : ''; ?><?php echo ( $field['type'] === 'radio' ) ? ' ptc-field--full' : ''; ?>">
					<label class="ptc-field__label"><?php echo esc_html( $flabel ); ?></label>
					<?php if ( $fdesc ) : ?>
					<p class="ptc-field__desc"><?php echo esc_html( $fdesc ); ?></p>
					<?php endif; ?>
					<?php if ( $field['type'] === 'text' || $field['type'] === 'text_wide' ) : ?>
						<input type="text" name="<?php echo $fkey; ?>" value="<?php echo esc_attr( $fval ); ?>" class="ptc-field__input<?php echo $field['type'] === 'text_wide' ? ' ptc-field__input--wide' : ''; ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>">
					<?php elseif ( $field['type'] === 'number' ) : ?>
						<input type="number" name="<?php echo $fkey; ?>" value="<?php echo esc_attr( $fval ); ?>" class="ptc-field__input" min="<?php echo esc_attr( (string)( $field['min'] ?? '' ) ); ?>" max="<?php echo esc_attr( (string)( $field['max'] ?? '' ) ); ?>" step="<?php echo esc_attr( (string)( $field['step'] ?? '1' ) ); ?>">
					<?php elseif ( $field['type'] === 'color' ) : ?>
						<div class="ptc-color-wrap">
							<input type="color" name="<?php echo $fkey; ?>" id="ptc-color-<?php echo $fkey; ?>"
							       value="<?php echo esc_attr( $fval ?: ( $field['default'] ?? '#000000' ) ); ?>" class="ptc-color">
							<?php if ( ! empty( $field['default'] ) ) : ?>
							<button type="button" class="ptc-color-revert"
							        data-default="<?php echo esc_attr( $field['default'] ); ?>"
							        data-target="ptc-color-<?php echo $fkey; ?>"
							        title="Revert to default (<?php echo esc_attr( $field['default'] ); ?>)">
								<span class="dashicons dashicons-image-rotate"></span>
							</button>
							<span class="ptc-color-swatch" style="background:<?php echo esc_attr( $field['default'] ); ?>;" title="Default: <?php echo esc_attr( $field['default'] ); ?>"></span>
							<?php endif; ?>
						</div>
					<?php elseif ( $field['type'] === 'select' ) : ?>
						<select name="<?php echo $fkey; ?>" class="ptc-field__select">
							<?php foreach ( $field['options'] ?? [] as $ov => $ol ) : ?>
							<option value="<?php echo esc_attr( $ov ); ?>"<?php selected( $fval, $ov ); ?>><?php echo esc_html( $ol ); ?></option>
							<?php endforeach; ?>
						</select>
					<?php elseif ( $field['type'] === 'toggle' ) : ?>
						<label class="ptc-toggle">
							<input type="hidden" name="<?php echo $fkey; ?>" value="no">
							<input type="checkbox" name="<?php echo $fkey; ?>" value="yes"<?php checked( $fval, 'yes' ); ?>>
							<span class="ptc-toggle__track"><span class="ptc-toggle__thumb"></span></span>
							<span class="ptc-toggle__label"><?php echo esc_html( $field['toggle_label'] ?? 'Enabled' ); ?></span>
						</label>
					<?php elseif ( $field['type'] === 'textarea' ) : ?>
						<textarea name="<?php echo $fkey; ?>" class="ptc-field__textarea" rows="<?php echo esc_attr( (string)( $field['rows'] ?? 3 ) ); ?>"><?php echo esc_textarea( $fval ); ?></textarea>
					<?php elseif ( $field['type'] === 'radio' ) : ?>
						<div class="ptc-radio-group">
							<?php foreach ( $field['options'] ?? [] as $ov => $ol ) : ?>
							<label class="ptc-radio-label">
								<input type="radio" name="<?php echo $fkey; ?>" value="<?php echo esc_attr( $ov ); ?>"<?php checked( $fval, $ov ); ?>>
								<?php echo esc_html( $ol ); ?>
							</label>
							<?php endforeach; ?>
						</div>
					<?php elseif ( $field['type'] === 'range' ) : ?>
						<div class="ptc-range-wrap">
							<input type="range" name="<?php echo $fkey; ?>" id="ptc-range-<?php echo $fkey; ?>"
							       value="<?php echo esc_attr( $fval ?: ( $field['default'] ?? '0' ) ); ?>"
							       min="<?php echo esc_attr( (string)( $field['min'] ?? 0 ) ); ?>"
							       max="<?php echo esc_attr( (string)( $field['max'] ?? 100 ) ); ?>"
							       step="<?php echo esc_attr( (string)( $field['step'] ?? 1 ) ); ?>"
							       class="ptc-range"
							       oninput="document.getElementById('ptc-range-val-<?php echo $fkey; ?>').textContent=this.value+'<?php echo esc_js( $field['unit'] ?? '' ); ?>'">
							<span class="ptc-range__val" id="ptc-range-val-<?php echo $fkey; ?>"><?php echo esc_html( $fval ?: ( $field['default'] ?? '0' ) ); ?><?php echo esc_html( $field['unit'] ?? '' ); ?></span>
						</div>
					<?php endif; ?>
				</div>
				<?php endforeach; ?>
				</div><!-- /.ptc-settings-grid -->

				<div class="ptc-settings-save-row">
					<button type="submit" class="button button-primary ptc-settings-save-btn">
						<span class="dashicons dashicons-saved"></span> <?php esc_html_e( 'Save Template Settings', 'pizzalayer' ); ?>
					</button>
				</div>
			</form>
		</div>
		<?php elseif ( $active ) : ?>
		<div class="ptc-settings-card ptc-settings-card--empty">
			<div class="ptc-settings-card__head">
				<h2><span class="dashicons dashicons-admin-appearance"></span> <?php echo esc_html( ucwords( str_replace( '-', ' ', $active ) ) ); ?> Template Settings</h2>
				<p><?php esc_html_e( 'This template has no customizable settings.', 'pizzalayer' ); ?></p>
			</div>
		</div>
		<?php endif; ?>

		</div><!-- /.wrap -->
		<?php
	}

	private function save_template_settings(): void {
		$active = (string) get_option( 'pizzalayer_setting_global_template', '' );
		if ( ! $active ) { return; }
		// Load the option keys for this template
		$options_paths = [
			get_stylesheet_directory() . '/pzttemplates/' . $active . '/pztp-template-options.php',
			PIZZALAYER_TEMPLATES_DIR . $active . '/pztp-template-options.php',
		];
		$fields = [];
		foreach ( $options_paths as $path ) {
			if ( file_exists( $path ) ) {
				$fields = include $path;
				if ( ! is_array( $fields ) ) { $fields = []; }
				break;
			}
		}
		foreach ( $fields as $field ) {
			if ( empty( $field['key'] ) || empty( $field['type'] ) ) { continue; }
			$key = $field['key'];
			$raw = $_POST[ $key ] ?? null;
			if ( $field['type'] === 'toggle' ) {
				// Hidden input sends 'no', checkbox overwrites with 'yes' if checked
				// We need to find the last value in the POST array for this key
				// PHP $_POST will have the checkbox value if checked, or the hidden 'no'
				$val = isset( $_POST[ $key ] ) ? sanitize_key( (string) $_POST[ $key ] ) : 'no';
				update_option( $key, $val === 'yes' ? 'yes' : 'no' );
			} elseif ( $field['type'] === 'color' ) {
				if ( $raw !== null ) { update_option( $key, sanitize_hex_color( (string) $raw ) ?: '' ); }
			} elseif ( $field['type'] === 'textarea' ) {
				if ( $raw !== null ) { update_option( $key, wp_kses_post( wp_unslash( (string) $raw ) ) ); }
			} elseif ( $field['type'] === 'number' || $field['type'] === 'range' ) {
				if ( $raw !== null ) {
					$int = (int) $raw;
					if ( isset( $field['min'] ) ) { $int = max( (int) $field['min'], $int ); }
					if ( isset( $field['max'] ) ) { $int = min( (int) $field['max'], $int ); }
					update_option( $key, (string) $int );
				}
			} else {
				// text, text_wide, select, radio
				if ( $raw !== null ) { update_option( $key, sanitize_text_field( wp_unslash( (string) $raw ) ) ); }
			}
		}
	}

	private function get_metro_color_schemes(): array {
		return [
			[ 'name' => 'Tomato',      'colors' => ['#e63946','#f7f7f5','#ffffff'], 'keys' => ['metro_setting_accent_color'=>'#e63946','metro_setting_background_color'=>'#f7f7f5','metro_setting_card_bg_color'=>'#ffffff'] ],
			[ 'name' => 'Night Blue',  'colors' => ['#2563eb','#0f1729','#1e2d4a'], 'keys' => ['metro_setting_accent_color'=>'#2563eb','metro_setting_background_color'=>'#0f1729','metro_setting_card_bg_color'=>'#1e2d4a'] ],
			[ 'name' => 'Garden',      'colors' => ['#2d6a4f','#f4f1e8','#fffef9'], 'keys' => ['metro_setting_accent_color'=>'#2d6a4f','metro_setting_background_color'=>'#f4f1e8','metro_setting_card_bg_color'=>'#fffef9'] ],
			[ 'name' => 'Ember',       'colors' => ['#c2410c','#fdf4ec','#ffffff'], 'keys' => ['metro_setting_accent_color'=>'#c2410c','metro_setting_background_color'=>'#fdf4ec','metro_setting_card_bg_color'=>'#ffffff'] ],
			[ 'name' => 'Slate Dark',  'colors' => ['#475569','#1e293b','#293548'], 'keys' => ['metro_setting_accent_color'=>'#475569','metro_setting_background_color'=>'#1e293b','metro_setting_card_bg_color'=>'#293548'] ],
			[ 'name' => 'Rose',        'colors' => ['#be185d','#fff0f6','#ffffff'], 'keys' => ['metro_setting_accent_color'=>'#be185d','metro_setting_background_color'=>'#fff0f6','metro_setting_card_bg_color'=>'#ffffff'] ],
			[ 'name' => 'Golden Hour', 'colors' => ['#b45309','#fffbeb','#ffffff'], 'keys' => ['metro_setting_accent_color'=>'#b45309','metro_setting_background_color'=>'#fffbeb','metro_setting_card_bg_color'=>'#ffffff'] ],
			[ 'name' => 'Violet Night','colors' => ['#7c3aed','#1a0533','#2a1045'], 'keys' => ['metro_setting_accent_color'=>'#7c3aed','metro_setting_background_color'=>'#1a0533','metro_setting_card_bg_color'=>'#2a1045'] ],
			[ 'name' => 'Sea Breeze',  'colors' => ['#0891b2','#f0f9ff','#ffffff'], 'keys' => ['metro_setting_accent_color'=>'#0891b2','metro_setting_background_color'=>'#f0f9ff','metro_setting_card_bg_color'=>'#ffffff'] ],
			[ 'name' => 'Monochrome',  'colors' => ['#18181b','#f4f4f5','#ffffff'], 'keys' => ['metro_setting_accent_color'=>'#18181b','metro_setting_background_color'=>'#f4f4f5','metro_setting_card_bg_color'=>'#ffffff'] ],
		];
	}

	private function get_plainlist_presets(): array {
		return [
			[ 'name' => 'Classic Black', 'colors' => ['#1a1a1a','#ffffff','#111111'], 'keys' => ['plainlist_setting_accent_color'=>'#1a1a1a','plainlist_setting_bg_color'=>'#ffffff','plainlist_setting_section_header_color'=>'#111111'] ],
			[ 'name' => 'Warm Paper',    'colors' => ['#7c3a00','#fdf6ec','#3d2000'], 'keys' => ['plainlist_setting_accent_color'=>'#7c3a00','plainlist_setting_bg_color'=>'#fdf6ec','plainlist_setting_section_header_color'=>'#3d2000'] ],
			[ 'name' => 'Dark Mode',     'colors' => ['#f97316','#18181b','#ffffff'], 'keys' => ['plainlist_setting_accent_color'=>'#f97316','plainlist_setting_bg_color'=>'#18181b','plainlist_setting_section_header_color'=>'#ffffff'] ],
			[ 'name' => 'Forest',        'colors' => ['#2d6a4f','#f4f9f6','#1b3d2d'], 'keys' => ['plainlist_setting_accent_color'=>'#2d6a4f','plainlist_setting_bg_color'=>'#f4f9f6','plainlist_setting_section_header_color'=>'#1b3d2d'] ],
			[ 'name' => 'Navy Clean',    'colors' => ['#1e3a8a','#f8faff','#0f2060'], 'keys' => ['plainlist_setting_accent_color'=>'#1e3a8a','plainlist_setting_bg_color'=>'#f8faff','plainlist_setting_section_header_color'=>'#0f2060'] ],
			[ 'name' => 'Rose',          'colors' => ['#be185d','#fff0f6','#7c103d'], 'keys' => ['plainlist_setting_accent_color'=>'#be185d','plainlist_setting_bg_color'=>'#fff0f6','plainlist_setting_section_header_color'=>'#7c103d'] ],
			[ 'name' => 'Slate',         'colors' => ['#475569','#f1f5f9','#1e293b'], 'keys' => ['plainlist_setting_accent_color'=>'#475569','plainlist_setting_bg_color'=>'#f1f5f9','plainlist_setting_section_header_color'=>'#1e293b'] ],
			[ 'name' => 'Newspaper',     'colors' => ['#222222','#f7f4ee','#000000'], 'keys' => ['plainlist_setting_accent_color'=>'#222222','plainlist_setting_bg_color'=>'#f7f4ee','plainlist_setting_section_header_color'=>'#000000','plainlist_setting_font_family'=>'georgia','plainlist_setting_check_style'=>'bullet'] ],
		];
	}

	private function render_styles(): void { ?>
	<style>
	/* ══ Notices ════════════════════════════════════════════════ */
	.ptc-notice {
		display: flex; align-items: flex-start; gap: 8px;
		padding: 10px 14px; border-radius: 7px; font-size: 13px;
		margin-bottom: 12px; line-height: 1.5;
	}
	.ptc-notice--info { background: #f0f6ff; border: 1px solid #b9d4f5; color: #1d2023; }
	.ptc-notice--warn { background: #fff8e6; border: 1px solid #f0b849; color: #1d2023; }
	.ptc-notice .dashicons { font-size:16px !important; width:16px !important; height:16px !important; flex-shrink:0; margin-top:1px; }
	.ptc-notice--info .dashicons { color: #2271b1; }
	.ptc-notice--warn .dashicons { color: #b35309; }
	.ptc-notice code { background: rgba(0,0,0,.06); padding: 1px 5px; border-radius: 3px; font-size: 12px; }

	/* ══ Wrap & header ═════════════════════════════════════════════ */
	/* Override WP admin so split fills width properly */
	#wpbody-content { padding-bottom: 0 !important; }
	.ptc-wrap { max-width: 100%; padding-right: 0 !important; margin-right: 0 !important; }
	.ptc-header {
		display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
		background: linear-gradient(135deg,#1a1e23,#2d3748);
		color: #fff; border-radius: 10px; padding: 18px 24px; margin-bottom: 16px;
	}
	.ptc-header__icon { font-size:32px !important; width:32px !important; height:32px !important; color:#ff6b35; flex-shrink:0; }
	.ptc-header__title { margin:0; font-size:20px; font-weight:700; color:#fff; }
	.ptc-header__sub { margin:2px 0 0; color:#8d97a5; font-size:13px; }
	.ptc-header__sub strong { color:#fff; font-weight:600; }
	.ptc-header__actions { margin-left:auto; display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
	.ptc-header__actions .button { display:inline-flex; align-items:center; gap:5px; }
	.ptc-header__actions .dashicons { font-size:14px !important; width:14px !important; height:14px !important; }

	/* ══ Hero section ════════════════════════════════════════════ */
	.ptc-hero {
		display: flex; align-items: flex-start; gap: 24px; flex-wrap: wrap;
		background: linear-gradient(135deg,#1a1e23 0%,#2d3748 60%,#1e3a5f 100%);
		border-radius: 10px; padding: 28px 28px 24px; margin-bottom: 16px;
	}
	.ptc-hero__left { flex: 1; min-width: 260px; }
	.ptc-hero__badge {
		display: inline-flex; align-items: center; gap: 6px;
		background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.15);
		border-radius: 99px; padding: 4px 12px 4px 8px;
		font-size: 11px; font-weight: 600; color: #a0aec0; letter-spacing: .04em;
		text-transform: uppercase; margin-bottom: 12px;
	}
	.ptc-hero__badge .dashicons { font-size:13px !important; width:13px !important; height:13px !important; color:#ff6b35; }
	.ptc-hero__heading { margin: 0 0 10px; font-size: 20px; font-weight: 700; color: #fff; line-height: 1.3; }
	.ptc-hero__body { margin: 0 0 10px; font-size: 13px; color: #a0aec0; line-height: 1.65; }
	.ptc-hero__body strong { color: #fff; }
	.ptc-hero__right {
		display: flex; flex-direction: column; gap: 10px;
		min-width: 220px; padding-top: 4px;
	}
	.ptc-hero__pill {
		display: flex; align-items: center; gap: 12px;
		background: rgba(255,255,255,.07); border-radius: 8px; padding: 12px 16px;
	}
	.ptc-hero__pill-icon { font-size:20px !important; width:20px !important; height:20px !important; color: #718096; flex-shrink:0; }
	.ptc-hero__pill-icon--green { color: #48bb78; }
	.ptc-hero__pill-label { display: block; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: #718096; margin-bottom: 2px; }
	.ptc-hero__pill-val { display: block; font-size: 15px; font-weight: 700; color: #fff; }
	.ptc-hero__pill-val--sm { font-size: 12px; font-weight: 500; color: #a0aec0; line-height: 1.4; }

	/* ══ Preview URL bar ══════════════════════════════════════════ */
	.ptc-preview-url-bar {
		background: #fff8e6; border: 1px solid #f0b849; border-radius: 8px;
		padding: 14px 18px; margin-bottom: 14px;
	}
	.ptc-preview-url-label { display:flex; align-items:center; gap:7px; font-size:13px; font-weight:600; margin-bottom:8px; }
	.ptc-preview-url-label .dashicons { font-size:15px !important; width:15px !important; height:15px !important; color:#b35309; }
	.ptc-preview-url-label code { background:rgba(0,0,0,.06); padding:1px 5px; border-radius:3px; font-size:12px; font-weight:400; }
	.ptc-preview-url-row { display:flex; gap:8px; align-items:center; }
	.ptc-preview-url-input { flex:1; padding:7px 10px; border:1px solid #8c8f94; border-radius:4px; font-size:13px; }

	/* ══ Split layout ════════════════════════════════════════════ */
	.ptc-split {
		display: grid;
		grid-template-columns: 320px 1fr;
		gap: 0;
		/* 72vh is reliable inside WP admin without fighting #wpwrap overflow:hidden */
		height: 72vh;
		min-height: 500px;
		max-height: 860px;
		background: #fff;
		border: 1px solid #e0e3e7;
		border-radius: 12px;
		overflow: hidden;
	}

	/* ══ Left: template list ═════════════════════════════════════ */
	.ptc-list {
		overflow-y: auto;
		border-right: 1px solid #e0e3e7;
		background: #f8f9fa;
	}
	.ptc-list::-webkit-scrollbar { width: 5px; }
	.ptc-list::-webkit-scrollbar-track { background: transparent; }
	.ptc-list::-webkit-scrollbar-thumb { background: #d0d3d7; border-radius: 3px; }

	/* Template item row */
	.ptc-item {
		display: flex;
		flex-direction: column;
		gap: 10px;
		align-items: stretch;
		padding: 14px 16px;
		border-bottom: 1px solid #e8eaed;
		transition: background 0.12s;
		position: relative;
	}
	.ptc-item:last-of-type { border-bottom: none; }
	.ptc-item--active { background: #fff; }
	.ptc-item--previewing { background: #f0f5ff; }
	.ptc-item--active.ptc-item--previewing { background: #f0f5ff; }

	/* Left accent line when previewing */
	.ptc-item--previewing::before {
		content: '';
		position: absolute;
		left: 0; top: 0; bottom: 0;
		width: 3px;
		background: #2271b1;
		border-radius: 0 2px 2px 0;
	}
	.ptc-item--active::before {
		content: '';
		position: absolute;
		left: 0; top: 0; bottom: 0;
		width: 3px;
		background: #00a32a;
		border-radius: 0 2px 2px 0;
	}

	/* Thumbnail */
	.ptc-item__thumb {
		width: 100%; height: 140px;
		border-radius: 8px;
		overflow: hidden;
		position: relative;
		background: #1a1e23;
		flex-shrink: 0;
		border: 1px solid #e0e3e7;
	}
	.ptc-item--active .ptc-item__thumb,
	.ptc-item--previewing .ptc-item__thumb { border-color: #2271b1; }
	.ptc-item__thumb img { width:100%; height:100%; object-fit:cover; display:block; }
	.ptc-item__thumb-placeholder {
		width:100%; height:100%;
		display:flex; align-items:center; justify-content:center;
	}
	.ptc-item__thumb-placeholder .dashicons {
		font-size:20px !important; width:20px !important; height:20px !important; color:#3d5063;
	}
	.ptc-item__active-dot {
		position: absolute; bottom:4px; right:4px;
		width:8px; height:8px; border-radius:50%;
		background: #00a32a;
		border: 1.5px solid #fff;
	}

	/* Info column */
	.ptc-item__info { min-width: 0; }
	.ptc-item__name {
		font-size: 13px; font-weight: 600; color: #1d2023;
		display: flex; align-items: center; gap: 6px;
		white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
	}
	.ptc-item__active-badge {
		font-size: 10px; font-weight: 700;
		background: #d1f5dc; color: #00a32a;
		border-radius: 3px; padding: 1px 5px;
		flex-shrink: 0;
	}
	.ptc-item__desc {
		font-size: 11px; color: #646970; margin: 2px 0 4px;
		overflow: hidden;
		display: -webkit-box;
		-webkit-line-clamp: 2;
		-webkit-box-orient: vertical;
	}
	.ptc-item__tags { display:flex; flex-wrap:wrap; gap:3px; }
	.ptc-item__tag { background:#ebebeb; color:#555; font-size:10px; padding:1px 5px; border-radius:3px; }

	/* Action column */
	.ptc-item__action { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
	.ptc-item__check {
		color: #00a32a;
		font-size: 22px !important; width: 22px !important; height: 22px !important;
	}
	.ptc-item__action .button { font-size:12px; padding:4px 10px; }
	.ptc-preview-btn { display:inline-flex !important; align-items:center; gap:4px; }
	.ptc-preview-btn .dashicons { font-size:13px !important; width:13px !important; height:13px !important; }
	.ptc-item--previewing .ptc-preview-btn { background:#dce8f7 !important; border-color:#2271b1 !important; color:#2271b1 !important; }

	/* Dev card at bottom */
	.ptc-list__devcard {
		display: flex; align-items: flex-start; gap: 10px;
		padding: 12px 14px;
		border-top: 1px solid #e0e3e7;
		background: #f0f0f1;
		font-size: 11.5px; color: #646970; line-height: 1.5;
	}
	.ptc-list__devcard .dashicons { font-size:16px !important; width:16px !important; height:16px !important; color:#2271b1; flex-shrink:0; margin-top:1px; }
	.ptc-list__devcard code { background:#e0e3e7; padding:1px 4px; border-radius:3px; font-size:11px; word-break:break-all; }

	/* ══ Right: iframe preview pane ══════════════════════════════ */
	.ptc-preview-pane {
		display: flex;
		flex-direction: column;
		background: #f0f0f1;
		position: relative;
		/* Required: grid cells need explicit min sizes to not collapse */
		min-width: 0;
		min-height: 0;
		overflow: hidden;
	}

	/* Browser-chrome bar */
	.ptc-preview-bar {
		display: flex; align-items: center; gap: 10px;
		background: #e8e8e8;
		border-bottom: 1px solid #d0d0d0;
		padding: 8px 14px;
		flex-shrink: 0;
	}
	.ptc-preview-bar__dots { display:flex; gap:5px; flex-shrink:0; }
	.ptc-preview-bar__dots span {
		width:10px; height:10px; border-radius:50%;
		background: #ccc;
	}
	.ptc-preview-bar__dots span:nth-child(1) { background:#ff5f57; }
	.ptc-preview-bar__dots span:nth-child(2) { background:#febc2e; }
	.ptc-preview-bar__dots span:nth-child(3) { background:#28c840; }
	.ptc-preview-bar__url {
		flex: 1;
		background: #fff;
		border: 1px solid #d0d0d0;
		border-radius: 6px;
		padding: 4px 12px;
		font-size: 12px;
		color: #444;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}
	.ptc-preview-bar__actions { display:flex; gap:4px; flex-shrink:0; }
	.ptc-preview-bar__btn {
		background: none; border: none; cursor: pointer;
		padding: 4px 6px; border-radius: 4px; color: #646970;
		display: flex; align-items: center;
		transition: background 0.12s;
	}
	.ptc-preview-bar__btn:hover { background: #d8d8d8; }
	.ptc-preview-bar__btn .dashicons { font-size:15px !important; width:15px !important; height:15px !important; }

	/* iframe wrapper — must have explicit height context for iframe height:100% to work */
	.ptc-iframe-wrap {
		flex: 1;
		position: relative;
		overflow: hidden;
		min-height: 0; /* required for flex child to shrink */
		display: block;
	}
	.ptc-preview-frame {
		position: absolute;
		top: 0; left: 0; right: 0; bottom: 0;
		width: 100%;
		height: 100%;
		border: none;
		display: block;
		transition: opacity 0.25s;
	}
	.ptc-preview-frame.is-loading { opacity: 0.3; }

	/* Loading overlay — hidden by default, shown via JS inline styles */
	.ptc-iframe-loading {
		position: absolute; inset: 0;
		display: none; /* JS sets display:flex when loading */
		flex-direction: column;
		align-items: center; justify-content: center;
		gap: 14px;
		background: rgba(248,249,250,0.92);
		z-index: 10;
		font-size: 13px; color: #646970;
		transition: opacity 0.2s;
	}
	.ptc-iframe-loading__spinner {
		width: 32px; height: 32px;
		border: 3px solid #e0e3e7;
		border-top-color: #2271b1;
		border-radius: 50%;
		animation: ptc-spin 0.7s linear infinite;
	}
	@keyframes ptc-spin { to { transform: rotate(360deg); } }

	/* ══ Modal ══════════════════════════════════════════════════ */
	.ptc-modal { position:fixed; inset:0; z-index:100000; display:flex; align-items:center; justify-content:center; }
	.ptc-modal__overlay { position:absolute; inset:0; background:rgba(0,0,0,.6); }
	.ptc-modal__box { position:relative; z-index:1; background:#fff; border-radius:12px; width:100%; max-width:460px; box-shadow:0 16px 48px rgba(0,0,0,.3); }
	.ptc-modal__header { padding:18px 22px 12px; border-bottom:1px solid #f0f0f0; }
	.ptc-modal__title { margin:0; font-size:16px; display:flex; align-items:center; gap:8px; }
	.ptc-modal__title .dashicons { color:#2271b1; }
	.ptc-modal__body { padding:14px 22px; font-size:13px; }
	.ptc-modal__body p { margin:0 0 8px; line-height:1.6; }
	.ptc-modal__note { font-size:12px; color:#646970; background:#f8f9fa; padding:9px 12px; border-radius:6px; margin:0; }
	.ptc-modal__footer { padding:12px 22px; border-top:1px solid #f0f0f0; display:flex; justify-content:flex-end; gap:8px; }
	.ptc-modal__footer .button { display:inline-flex; align-items:center; gap:5px; }
	.ptc-modal__footer .dashicons { font-size:14px !important; width:14px !important; height:14px !important; }

	/* ══ Template Settings Panel ════════════════════════════════ */
	.ptc-settings-card {
		background: #fff;
		border: 1px solid #e0e3e7;
		border-radius: 10px;
		margin-top: 20px;
		overflow: hidden;
	}
	.ptc-settings-card--empty .ptc-settings-card__head { border-bottom: none; }
	.ptc-settings-card__head {
		padding: 18px 24px 14px;
		border-bottom: 1px solid #f0f0f0;
	}
	.ptc-settings-card__head h2 {
		margin: 0 0 4px;
		font-size: 15px;
		display: flex;
		align-items: center;
		gap: 8px;
	}
	.ptc-settings-card__head h2 .dashicons { color: #ff6b35; }
	.ptc-settings-card__head p { margin: 0; color: #646970; font-size: 13px; }
	.ptc-settings-card__badge {
		font-size: 10px; font-weight: 700;
		background: #d1f5dc; color: #00a32a;
		border-radius: 3px; padding: 2px 6px;
		flex-shrink: 0;
	}
	.ptc-settings-form { padding: 20px 24px 24px; }

	/* Quick presets chips */
	.ptc-scheme-row {
		display: flex;
		align-items: center;
		gap: 10px;
		margin-bottom: 20px;
		padding-bottom: 16px;
		border-bottom: 1px solid #f0f0f0;
		flex-wrap: wrap;
	}
	.ptc-scheme-label { font-size: 12px; font-weight: 600; color: #646970; white-space: nowrap; }
	.ptc-scheme-chips { display: flex; flex-wrap: wrap; gap: 6px; flex: 1; }
	.ptc-scheme-chip {
		display: inline-flex; align-items: center; gap: 6px;
		padding: 4px 10px 4px 6px;
		border: 1px solid #e0e3e7;
		border-radius: 20px;
		background: #f8f9fa;
		cursor: pointer;
		font-size: 12px; font-weight: 500; color: #1d2023;
		transition: border-color 0.15s, background 0.15s, transform 0.1s;
		line-height: 1;
	}
	.ptc-scheme-chip:hover { border-color: #ff6b35; background: #fff5f0; transform: translateY(-1px); }
	.ptc-scheme-chip--active { border-color: #ff6b35; background: #fff0e8; }
	.ptc-scheme-chip__swatches { display: flex; gap: 2px; }
	.ptc-scheme-chip__dot { width: 10px; height: 10px; border-radius: 50%; border: 1px solid rgba(0,0,0,.12); flex-shrink: 0; }
	.ptc-scheme-chip__name { white-space: nowrap; }

	/* Settings grid */
	.ptc-settings-grid {
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: 18px 28px;
		margin-bottom: 22px;
	}
	@media (max-width: 900px) { .ptc-settings-grid { grid-template-columns: 1fr; } }
	.ptc-field { display: flex; flex-direction: column; gap: 4px; }
	.ptc-field--full { grid-column: 1 / -1; }
	.ptc-field__label { font-size: 13px; font-weight: 600; color: #1d2023; }
	.ptc-field__desc { font-size: 12px; color: #646970; margin: 0 0 4px; }
	.ptc-field__input { padding: 7px 10px; border: 1px solid #8c8f94; border-radius: 4px; font-size: 13px; width: 100%; max-width: 400px; }
	.ptc-field__input--wide { max-width: 100%; }
	.ptc-field__select { padding: 6px 10px; border: 1px solid #8c8f94; border-radius: 4px; font-size: 13px; min-width: 160px; }
	.ptc-field__textarea { width: 100%; padding: 8px 10px; border: 1px solid #8c8f94; border-radius: 4px; font-size: 13px; font-family: inherit; resize: vertical; }

	/* Color picker */
	.ptc-color-wrap { display: flex; align-items: center; gap: 8px; }
	.ptc-color { width: 44px; height: 34px; padding: 2px; border: 1px solid #8c8f94; border-radius: 4px; cursor: pointer; }
	.ptc-color-revert {
		background: none; border: 1px solid #ddd; border-radius: 4px;
		padding: 4px 6px; cursor: pointer; color: #646970;
		display: flex; align-items: center;
		transition: background 0.12s;
	}
	.ptc-color-revert:hover { background: #f0f0f0; }
	.ptc-color-revert .dashicons { font-size: 14px !important; width: 14px !important; height: 14px !important; }
	.ptc-color-swatch { width: 16px; height: 16px; border-radius: 3px; border: 1px solid rgba(0,0,0,.15); flex-shrink: 0; }

	/* Toggle */
	.ptc-toggle { display: inline-flex; align-items: center; gap: 10px; cursor: pointer; user-select: none; }
	.ptc-toggle input[type=checkbox] { position: absolute; opacity: 0; width: 0; height: 0; }
	.ptc-toggle input[type=hidden] { display: none; }
	.ptc-toggle__track {
		width: 36px; height: 20px; background: #c3c4c7; border-radius: 10px;
		position: relative; flex-shrink: 0; transition: background 0.2s;
	}
	.ptc-toggle input[type=checkbox]:checked ~ .ptc-toggle__track { background: #00a32a; }
	.ptc-toggle__thumb {
		position: absolute; top: 3px; left: 3px;
		width: 14px; height: 14px; border-radius: 50%;
		background: #fff; transition: left 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,.3);
	}
	.ptc-toggle input[type=checkbox]:checked ~ .ptc-toggle__track .ptc-toggle__thumb { left: 19px; }
	.ptc-toggle__label { font-size: 13px; color: #1d2023; }

	/* Radio group */
	.ptc-radio-group { display: flex; flex-direction: column; gap: 6px; }
	.ptc-radio-label { display: flex; align-items: flex-start; gap: 8px; font-size: 13px; color: #1d2023; cursor: pointer; line-height: 1.4; }
	.ptc-radio-label input { margin-top: 3px; flex-shrink: 0; accent-color: #2271b1; }

	/* Range */
	.ptc-range-wrap { display: flex; align-items: center; gap: 10px; }
	.ptc-range { flex: 1; max-width: 200px; accent-color: #2271b1; }
	.ptc-range__val { font-size: 13px; font-weight: 600; color: #2271b1; min-width: 40px; }

	/* Save row */
	.ptc-settings-save-row {
		padding-top: 16px;
		border-top: 1px solid #f0f0f0;
		display: flex;
		align-items: center;
		gap: 10px;
	}
	.ptc-settings-save-btn { display: inline-flex !important; align-items: center; gap: 5px; }
	.ptc-settings-save-btn .dashicons { font-size: 14px !important; width: 14px !important; height: 14px !important; }
	</style>
	<?php // Chip/revert JS is in assets/js/admin/template-choice.js (enqueued via AssetManager) ?>
	<?php }
}
