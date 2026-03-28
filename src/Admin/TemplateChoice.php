<?php
namespace PizzaLayer\Admin;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Template selection page.
 * Scans: plugin /templates/ dir + theme /pizzalayer/ dir.
 * Shows template details from pztp-template-info.php.
 * Change requires confirmation modal.
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

		?>
		<div class="wrap ptc-wrap">
		<?php $this->render_styles(); ?>

		<!-- ══ Header ════════════════════════════════════════════════ -->
		<div class="ptc-header">
			<span class="dashicons dashicons-admin-appearance ptc-header__icon"></span>
			<div>
				<h1 class="ptc-header__title">Template</h1>
				<p class="ptc-header__sub">Choose the visual design for your pizza builder. Templates live in the plugin or your theme's <code>/pizzalayer/</code> folder.</p>
			</div>
		</div>

		<!-- ══ Template grid ═════════════════════════════════════════ -->
		<?php if ( empty( $templates ) ) : ?>
		<div class="ptc-card ptc-empty">
			<span class="dashicons dashicons-warning"></span>
			<p>No templates found. Make sure at least the <code>nightpie</code> folder exists in the plugin's <code>/templates/</code> directory.</p>
		</div>
		<?php else : ?>

		<div class="ptc-grid">
		<?php foreach ( $templates as $slug => $tpl ) :
			$info      = $tpl['info'];
			$is_active = $slug === $active;
		?>
			<div class="ptc-card<?php echo $is_active ? ' ptc-card--active' : ''; ?>" id="ptc-card-<?php echo esc_attr( $slug ); ?>">

				<!-- Preview image -->
				<div class="ptc-card__preview">
					<?php if ( $tpl['preview_url'] ) : ?>
					<img src="<?php echo esc_url( $tpl['preview_url'] ); ?>" alt="Preview: <?php echo esc_attr( $slug ); ?>" loading="lazy">
					<?php else : ?>
					<div class="ptc-card__preview-placeholder">
						<span class="dashicons dashicons-admin-appearance"></span>
						<span>No preview</span>
					</div>
					<?php endif; ?>
					<?php if ( $is_active ) : ?>
					<div class="ptc-card__active-badge"><span class="dashicons dashicons-yes"></span> Active</div>
					<?php endif; ?>
					<div class="ptc-card__source-badge ptc-card__source-badge--<?php echo esc_attr( $tpl['source'] ); ?>">
						<?php echo esc_html( ucfirst( $tpl['source'] ) ); ?>
					</div>
				</div>

				<!-- Details -->
				<div class="ptc-card__body">
					<div class="ptc-card__title">
						<?php echo esc_html( isset( $info['name'] ) ? $info['name'] : ucwords( str_replace( '-', ' ', $slug ) ) ); ?>
					</div>
					<?php if ( ! empty( $info['description'] ) ) : ?>
					<p class="ptc-card__desc"><?php echo esc_html( $info['description'] ); ?></p>
					<?php endif; ?>
					<div class="ptc-card__meta">
						<?php if ( ! empty( $info['version'] ) ) : ?>
						<span class="ptc-meta-item"><span class="dashicons dashicons-tag"></span> v<?php echo esc_html( $info['version'] ); ?></span>
						<?php endif; ?>
						<?php if ( ! empty( $info['author'] ) ) : ?>
						<span class="ptc-meta-item"><span class="dashicons dashicons-admin-users"></span> <?php echo esc_html( $info['author'] ); ?></span>
						<?php endif; ?>
						<?php if ( ! empty( $info['license'] ) ) : ?>
						<span class="ptc-meta-item"><span class="dashicons dashicons-lock"></span> <?php echo esc_html( $info['license'] ); ?></span>
						<?php endif; ?>
						<?php if ( ! empty( $info['tags'] ) && is_array( $info['tags'] ) ) : ?>
						<div class="ptc-tags">
							<?php foreach ( $info['tags'] as $tag ) : ?>
							<span class="ptc-tag"><?php echo esc_html( $tag ); ?></span>
							<?php endforeach; ?>
						</div>
						<?php endif; ?>
					</div>
				</div>

				<!-- Action -->
				<div class="ptc-card__footer">
					<?php if ( $is_active ) : ?>
					<button class="button button-secondary" disabled>✓ Currently Active</button>
					<?php else : ?>
					<button class="button button-primary ptc-activate-btn"
					        data-slug="<?php echo esc_attr( $slug ); ?>"
					        data-name="<?php echo esc_attr( isset( $info['name'] ) ? $info['name'] : $slug ); ?>">
						Activate Template
					</button>
					<?php endif; ?>
					<?php if ( ! empty( $info['support_url'] ) ) : ?>
					<a href="<?php echo esc_url( $info['support_url'] ); ?>" class="button button-secondary" target="_blank" rel="noopener">
						<span class="dashicons dashicons-sos"></span>
					</a>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
		</div><!-- /.ptc-grid -->

		<!-- ══ Custom template help ══════════════════════════════════ -->
		<div class="ptc-card ptc-devcard">
			<span class="dashicons dashicons-admin-plugins ptc-devcard__icon"></span>
			<div class="ptc-devcard__body">
				<h3>Add a Custom Template</h3>
				<p>Create a folder at <code><?php echo esc_html( get_stylesheet_directory() ); ?>/pizzalayer/your-template-slug/</code></p>
				<p>Copy any template folder from the plugin's <code>/templates/</code> directory as a starting point. Add a <code>pztp-template-info.php</code> file to show details here. Your template will appear in the list above automatically.</p>
			</div>
		</div>

		<?php endif; ?>

		<!-- ══ Confirmation modal ════════════════════════════════════ -->
		<div id="ptc-modal" class="ptc-modal" role="dialog" aria-modal="true" aria-labelledby="ptc-modal-title" style="display:none;">
			<div class="ptc-modal__box">
				<div class="ptc-modal__header">
					<h2 id="ptc-modal-title" class="ptc-modal__title"><span class="dashicons dashicons-admin-appearance"></span> Change Template?</h2>
				</div>
				<div class="ptc-modal__body">
					<p>You are about to activate the <strong id="ptc-modal-name"></strong> template.</p>
					<p class="ptc-modal__note">Your existing content and settings are unaffected — only the front-end visual design will change.</p>
				</div>
				<div class="ptc-modal__footer">
					<button id="ptc-modal-cancel" class="button button-secondary">Cancel</button>
					<form method="post" action="" id="ptc-activate-form" style="display:inline;">
						<?php wp_nonce_field( 'pizzalayer_activate_template' ); ?>
						<input type="hidden" name="pizzalayer_activate_template" id="ptc-modal-slug" value="">
						<button type="submit" class="button button-primary">Yes, Activate</button>
					</form>
				</div>
			</div>
			<div class="ptc-modal__overlay" id="ptc-modal-overlay"></div>
		</div>

		</div><!-- /.wrap -->

		<script>
		(function(){
			var modal     = document.getElementById('ptc-modal');
			var modalName = document.getElementById('ptc-modal-name');
			var modalSlug = document.getElementById('ptc-modal-slug');
			var cancelBtn = document.getElementById('ptc-modal-cancel');
			var overlay   = document.getElementById('ptc-modal-overlay');

			document.querySelectorAll('.ptc-activate-btn').forEach(function(btn){
				btn.addEventListener('click', function(){
					modalName.textContent = btn.dataset.name;
					modalSlug.value       = btn.dataset.slug;
					modal.style.display   = '';
					document.body.style.overflow = 'hidden';
				});
			});

			function closeModal(){
				modal.style.display = 'none';
				document.body.style.overflow = '';
			}
			cancelBtn.addEventListener('click', closeModal);
			overlay.addEventListener('click', closeModal);
			document.addEventListener('keydown', function(e){ if(e.key === 'Escape'){ closeModal(); } });
		})();
		</script>
		<?php
	}

	private function render_styles(): void { ?>
	<style>
	.ptc-wrap { max-width: 1100px; }
	.ptc-header { display:flex; align-items:center; gap:16px; background:linear-gradient(135deg,#1a1e23,#2d3748); color:#fff; border-radius:10px; padding:22px 28px; margin-bottom:20px; }
	.ptc-header__icon { font-size:36px !important; width:36px !important; height:36px !important; color:#ff6b35; flex-shrink:0; }
	.ptc-header__title { margin:0; font-size:22px; font-weight:700; color:#fff; }
	.ptc-header__sub { margin:3px 0 0; color:#8d97a5; font-size:13px; }
	.ptc-header__sub code { background:rgba(255,255,255,.1); padding:1px 5px; border-radius:3px; }
	/* Grid */
	.ptc-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:20px; margin-bottom:24px; }
	/* Card */
	.ptc-card { background:#fff; border:1px solid #e0e3e7; border-radius:12px; overflow:hidden; display:flex; flex-direction:column; transition:box-shadow .2s; }
	.ptc-card:hover { box-shadow:0 4px 16px rgba(0,0,0,.1); }
	.ptc-card--active { border:2px solid #2271b1; box-shadow:0 0 0 3px #dce8f7; }
	/* Preview */
	.ptc-card__preview { position:relative; background:#1a1e23; aspect-ratio:16/9; overflow:hidden; }
	.ptc-card__preview img { width:100%; height:100%; object-fit:cover; display:block; }
	.ptc-card__preview-placeholder { width:100%; height:100%; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:8px; color:#646970; }
	.ptc-card__preview-placeholder .dashicons { font-size:36px !important; width:36px !important; height:36px !important; color:#3d5063; }
	.ptc-card__active-badge { position:absolute; top:10px; right:10px; background:#2271b1; color:#fff; font-size:11px; font-weight:700; padding:3px 9px; border-radius:99px; display:inline-flex; align-items:center; gap:4px; }
	.ptc-card__active-badge .dashicons { font-size:12px !important; width:12px !important; height:12px !important; }
	.ptc-card__source-badge { position:absolute; top:10px; left:10px; font-size:10px; font-weight:700; padding:2px 7px; border-radius:99px; text-transform:uppercase; letter-spacing:.05em; }
	.ptc-card__source-badge--plugin { background:#1a1e23cc; color:#a3d977; }
	.ptc-card__source-badge--theme  { background:#8c5af8cc; color:#fff; }
	/* Body */
	.ptc-card__body { padding:16px 18px; flex:1; }
	.ptc-card__title { font-size:16px; font-weight:700; margin-bottom:6px; color:#1d2023; }
	.ptc-card__desc { font-size:12px; color:#646970; margin:0 0 10px; line-height:1.5; }
	.ptc-card__meta { display:flex; flex-wrap:wrap; gap:6px; align-items:center; }
	.ptc-meta-item { font-size:11px; color:#646970; display:inline-flex; align-items:center; gap:3px; }
	.ptc-meta-item .dashicons { font-size:12px !important; width:12px !important; height:12px !important; }
	.ptc-tags { display:flex; flex-wrap:wrap; gap:4px; width:100%; margin-top:4px; }
	.ptc-tag { background:#f0f0f1; color:#3c434a; font-size:10px; padding:2px 7px; border-radius:3px; }
	/* Footer */
	.ptc-card__footer { padding:12px 18px; border-top:1px solid #f0f0f0; display:flex; gap:8px; align-items:center; }
	.ptc-card__footer .button { display:inline-flex; align-items:center; gap:4px; }
	.ptc-card__footer .dashicons { font-size:14px !important; width:14px !important; height:14px !important; }
	/* Dev card */
	.ptc-devcard { display:flex; align-items:flex-start; gap:16px; padding:18px 20px; margin-bottom:20px; background:#f8f9fa; }
	.ptc-devcard__icon { font-size:28px !important; width:28px !important; height:28px !important; color:#2271b1; flex-shrink:0; margin-top:4px; }
	.ptc-devcard__body h3 { margin:0 0 6px; font-size:14px; }
	.ptc-devcard__body p { margin:0 0 6px; font-size:13px; color:#3c434a; }
	.ptc-devcard__body code { background:#e8eaed; padding:1px 5px; border-radius:3px; font-size:12px; }
	/* Empty */
	.ptc-empty { display:flex; align-items:center; gap:16px; padding:24px; }
	.ptc-empty .dashicons { font-size:28px !important; width:28px !important; height:28px !important; color:#d63638; flex-shrink:0; }
	.ptc-empty p { margin:0; font-size:13px; color:#3c434a; }
	/* Modal */
	.ptc-modal { position:fixed; inset:0; z-index:100000; display:flex; align-items:center; justify-content:center; }
	.ptc-modal__overlay { position:absolute; inset:0; background:rgba(0,0,0,.6); }
	.ptc-modal__box { position:relative; z-index:1; background:#fff; border-radius:12px; width:100%; max-width:480px; box-shadow:0 16px 48px rgba(0,0,0,.3); }
	.ptc-modal__header { padding:20px 24px 14px; border-bottom:1px solid #f0f0f0; }
	.ptc-modal__title { margin:0; font-size:17px; display:flex; align-items:center; gap:8px; }
	.ptc-modal__title .dashicons { color:#2271b1; }
	.ptc-modal__body { padding:16px 24px; font-size:14px; }
	.ptc-modal__body p { margin:0 0 10px; }
	.ptc-modal__note { font-size:13px; color:#646970; background:#f8f9fa; padding:10px 14px; border-radius:6px; }
	.ptc-modal__footer { padding:14px 24px; border-top:1px solid #f0f0f0; display:flex; justify-content:flex-end; gap:10px; }
	</style>
	<?php }
}
