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
			echo '<div class="notice notice-success is-dismissible"><p>Template <strong>' . esc_html( $slug ) . '</strong> activated.</p></div>';
		}

		$active = (string) get_option( 'pizzalayer_setting_global_template', 'nightpie' );

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
				<h1 class="ptc-header__title">Template</h1>
				<p class="ptc-header__sub">Hover any template to preview it live. Click <strong>Activate</strong> to apply it to your site.</p>
			</div>
			<div class="ptc-header__actions">
				<button type="button" class="button ptc-edit-preview-url" id="ptc-edit-preview-url">
					<span class="dashicons dashicons-admin-links"></span> Preview URL
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
					Preview page URL — enter any page on your site that contains <code>[pizza_builder]</code>:
				</label>
				<div class="ptc-preview-url-row">
					<input type="url" name="pizzalayer_template_preview_url"
					       class="ptc-preview-url-input"
					       value="<?php echo esc_attr( (string) get_option( 'pizzalayer_template_preview_url', '' ) ); ?>"
					       placeholder="<?php echo esc_attr( $preview_page_url ); ?>">
					<button type="submit" class="button button-primary">Save</button>
					<button type="button" class="button ptc-cancel-preview-url" id="ptc-cancel-preview-url">Cancel</button>
				</div>
			</form>
		</div>

		<?php
		// Handle preview URL save
		if ( isset( $_POST['pizzalayer_save_preview_url'], $_POST['_wpnonce'] )
		     && wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'pizzalayer_save_preview_url' ) ) {
			$url = esc_url_raw( wp_unslash( $_POST['pizzalayer_template_preview_url'] ?? '' ) );
			update_option( 'pizzalayer_template_preview_url', $url );
			echo '<div class="notice notice-success is-dismissible"><p>Preview URL saved.</p></div>';
		}
		?>

		<?php if ( $preview_page_auto ) : ?>
		<div class="ptc-notice ptc-notice--info">
			<span class="dashicons dashicons-info-outline"></span>
			Auto-detected preview page: <strong><?php echo esc_html( $preview_page_url ); ?></strong>
			— click <strong>Preview URL</strong> above to change it.
		</div>
		<?php elseif ( $preview_page_url === trailingslashit( home_url( '/' ) ) ) : ?>
		<div class="ptc-notice ptc-notice--warn">
			<span class="dashicons dashicons-warning"></span>
			No page with <code>[pizza_builder]</code> found — previewing the homepage.
			Click <strong>Preview URL</strong> above to set the correct page.
		</div>
		<?php endif; ?>

		<?php if ( empty( $templates ) ) : ?>
		<div class="ptc-card ptc-empty">
			<span class="dashicons dashicons-warning"></span>
			<p>No templates found. Make sure at least the <code>nightpie</code> folder exists in the plugin's <code>/templates/</code> directory.</p>
		</div>
		<?php else : ?>

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
							<span class="ptc-item__active-badge">Active</span>
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
							<span class="dashicons dashicons-visibility"></span> Preview
						</button>
						<?php endif; ?>
						<?php if ( $is_active ) : ?>
						<span class="ptc-item__check dashicons dashicons-yes-alt"></span>
						<?php else : ?>
						<button class="button button-primary ptc-activate-btn"
						        data-slug="<?php echo esc_attr( $slug ); ?>"
						        data-name="<?php echo esc_attr( $info['name'] ?? $slug ); ?>">
							Activate
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
						<?php echo esc_html( $info['name'] ?? ucwords( str_replace( '-', ' ', $active ) ) ); ?> — Live Preview
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
						<span class="dashicons dashicons-admin-appearance"></span> Activate Template?
					</h2>
				</div>
				<div class="ptc-modal__body">
					<p>Activating <strong id="ptc-modal-name"></strong> will apply it to your live site immediately.</p>
					<p class="ptc-modal__note">Your existing content and settings are unaffected — only the front-end visual design will change.</p>
				</div>
				<div class="ptc-modal__footer">
					<button id="ptc-modal-cancel" class="button button-secondary">Cancel</button>
					<form method="post" action="" id="ptc-activate-form" style="display:inline;">
						<?php wp_nonce_field( 'pizzalayer_activate_template' ); ?>
						<input type="hidden" name="pizzalayer_activate_template" id="ptc-modal-slug" value="">
						<button type="submit" class="button button-primary">
							<span class="dashicons dashicons-yes"></span> Yes, Activate
						</button>
					</form>
				</div>
			</div>
			<div class="ptc-modal__overlay" id="ptc-modal-overlay"></div>
		</div>

		</div><!-- /.wrap -->
		<?php
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
	.ptc-header__actions { margin-left:auto; }
	.ptc-header__actions .button { display:inline-flex; align-items:center; gap:5px; }
	.ptc-header__actions .dashicons { font-size:14px !important; width:14px !important; height:14px !important; }

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
		display: grid;
		grid-template-columns: 72px 1fr auto;
		gap: 10px;
		align-items: center;
		padding: 10px 12px;
		border-bottom: 1px solid #e8eaed;
		cursor: pointer;
		transition: background 0.12s;
		position: relative;
	}
	.ptc-item:last-of-type { border-bottom: none; }
	.ptc-item:hover { background: #fff; }
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
		width: 72px; height: 48px;
		border-radius: 6px;
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
	.ptc-item__action { flex-shrink: 0; display: flex; align-items: center; gap: 6px; flex-wrap: wrap; justify-content: flex-end; }
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
	</style>
	<?php }
}
