<?php
namespace PizzaLayer\Admin;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * PizzaLayer Layer Image Maker
 *
 * An in-browser tool that lets the user upload an image, crop it to a pizza-
 * appropriate aspect ratio, apply adjustments (brightness, contrast, saturation,
 * hue, blur, sharpen, opacity), and download a transparent-background PNG ready
 * to use as a layer image.
 *
 * All processing is done entirely in the browser (Canvas API); nothing is sent
 * to the server from this page. The user can optionally send the result directly
 * to the WordPress media library via a separate AJAX upload action.
 */
class LayerImageMaker {

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) { return; }

		$aspect_raw = get_option( 'pizzalayer_setting_pizza_aspect', '4 / 3' );
		// Normalise "4 / 3" → "4/3" for JS
		$aspect_js  = preg_replace( '/\s+/', '', $aspect_raw );

		wp_enqueue_media();   // needed so we can offer "Send to Media Library"
		?>
		<div class="wrap plim-wrap">
		<?php $this->render_styles(); ?>

		<!-- ══ Header ══════════════════════════════════════════════════ -->
		<div class="plim-header">
			<span class="dashicons dashicons-format-image plim-header__icon"></span>
			<div>
				<h1 class="plim-header__title"><?php esc_html_e( 'Layer Image Maker', 'pizzalayer' ); ?></h1>
				<p class="plim-header__sub"><?php esc_html_e( 'Upload an image, crop it to the correct aspect ratio for your pizza layers, adjust colour and transparency, then download as a transparent PNG.', 'pizzalayer' ); ?></p>
			</div>
		</div>

		<div class="plim-shell" id="plim-shell">

			<!-- ── Left panel: upload + controls ───────────────────────────── -->
			<aside class="plim-sidebar" id="plim-sidebar">

				<!-- Upload zone -->
				<div class="plim-card" id="plim-upload-card">
					<h2 class="plim-card-h"><span class="dashicons dashicons-upload"></span> <?php esc_html_e( 'Image Source', 'pizzalayer' ); ?></h2>
					<div class="plim-drop-zone" id="plim-drop-zone" tabindex="0" role="button" aria-aria-label="<?php esc_attr_e( 'Upload image', 'pizzalayer' ); ?>">
						<span class="dashicons dashicons-format-image plim-drop-icon"></span>
						<p><?php esc_html_e( 'Drop an image here, or click to browse', 'pizzalayer' ); ?></p>
						<input type="file" id="plim-file-input" accept="image/*" style="display:none;">
					</div>
					<button type="button" class="button plim-media-btn" id="plim-media-btn">
						<span class="dashicons dashicons-admin-media"></span> <?php esc_html_e( 'Choose from Media Library', 'pizzalayer' ); ?>
					</button>
				</div>

				<!-- Aspect ratio -->
				<div class="plim-card" id="plim-ratio-card">
					<h2 class="plim-card-h"><span class="dashicons dashicons-image-crop"></span> <?php esc_html_e( 'Guide Overlay', 'pizzalayer' ); ?></h2>
					<label class="plim-label">Aspect Ratio
						<select id="plim-aspect-preset" class="plim-select">
							<option value="1/1">1:1 — Square</option>
							<option value="4/3" selected>4:3 — Standard Pizza (default)</option>
							<option value="3/2">3:2 — Classic</option>
							<option value="16/9">16:9 — Wide</option>
							<option value="3/4">3:4 — Portrait</option>
							<option value="custom">Custom…</option>
						</select>
					</label>
					<div id="plim-custom-ratio-row" class="plim-custom-ratio-row" style="display:none;">
						<label class="plim-label-sm">W <input type="number" id="plim-ratio-w" min="1" max="99" value="4" class="plim-num-input"></label>
						<span class="plim-ratio-sep">:</span>
						<label class="plim-label-sm">H <input type="number" id="plim-ratio-h" min="1" max="99" value="3" class="plim-num-input"></label>
					</div>
					<label class="plim-label plim-toggle-row">
						<input type="checkbox" id="plim-show-guide" checked>
						<?php esc_html_e( 'Show pizza outline guide', 'pizzalayer' ); ?>
					</label>
					<label class="plim-label plim-toggle-row" style="margin-top:4px;">
						<input type="checkbox" id="plim-show-thirds" checked>
						<?php esc_html_e( 'Show rule-of-thirds grid', 'pizzalayer' ); ?>
					</label>
				</div>

				<!-- Adjustments -->
				<div class="plim-card" id="plim-adj-card">
					<h2 class="plim-card-h"><span class="dashicons dashicons-admin-appearance"></span> <?php esc_html_e( 'Adjustments', 'pizzalayer' ); ?>
						<button type="button" class="plim-reset-adj button-link" id="plim-reset-adj" title="<?php esc_attr_e( 'Reset all adjustments', 'pizzalayer' ); ?>">↺ <?php esc_html_e( 'Reset', 'pizzalayer' ); ?></button>
					</h2>

					<?php
					$sliders = [
						[ 'id' => 'plim-brightness',  'label' => __( 'Brightness', 'pizzalayer' ),  'min' => -100, 'max' => 100, 'def' => 0,   'unit' => '' ],
						[ 'id' => 'plim-contrast',    'label' => __( 'Contrast', 'pizzalayer' ),    'min' => -100, 'max' => 100, 'def' => 0,   'unit' => '' ],
						[ 'id' => 'plim-saturation',  'label' => __( 'Saturation', 'pizzalayer' ),  'min' => -100, 'max' => 100, 'def' => 0,   'unit' => '' ],
						[ 'id' => 'plim-hue',         'label' => __( 'Hue Shift', 'pizzalayer' ),   'min' => -180, 'max' => 180, 'def' => 0,   'unit' => '°' ],
						[ 'id' => 'plim-blur',        'label' => __( 'Blur', 'pizzalayer' ),        'min' => 0,    'max' => 20,  'def' => 0,   'unit' => 'px' ],
						[ 'id' => 'plim-sharpen',     'label' => __( 'Sharpen', 'pizzalayer' ),     'min' => 0,    'max' => 10,  'def' => 0,   'unit' => '' ],
						[ 'id' => 'plim-opacity',     'label' => __( 'Opacity', 'pizzalayer' ),     'min' => 0,    'max' => 100, 'def' => 100, 'unit' => '%' ],
					];
					foreach ( $sliders as $s ) {
						$mid = esc_attr( $s['id'] );
						$min = (int) $s['min'];
						$max = (int) $s['max'];
						$def = (int) $s['def'];
						$unit = esc_html( $s['unit'] );
						?>
						<div class="plim-slider-row">
							<label class="plim-slider-label" for="<?php echo $mid; ?>">
								<?php echo esc_html( $s['label'] ); ?>
								<span class="plim-slider-val" id="<?php echo $mid; ?>-val"><?php echo $def . $unit; ?></span>
							</label>
							<input type="range" id="<?php echo $mid; ?>" class="plim-slider" data-unit="<?php echo $unit; ?>"
							       min="<?php echo $min; ?>" max="<?php echo $max; ?>" value="<?php echo $def; ?>" step="1">
						</div>
						<?php
					}
					?>

					<!-- Transparency tools -->
					<div class="plim-separator"></div>
					<label class="plim-label plim-toggle-row">
						<input type="checkbox" id="plim-remove-bg">
						<?php esc_html_e( 'Remove background (threshold)', 'pizzalayer' ); ?>
					</label>
					<div id="plim-bg-row" class="plim-slider-row" style="display:none;">
						<label class="plim-slider-label" for="plim-bg-thresh">
							Threshold <span class="plim-slider-val" id="plim-bg-thresh-val">30</span>
						</label>
						<input type="range" id="plim-bg-thresh" class="plim-slider" min="1" max="128" value="30" step="1">
					</div>
					<label class="plim-label plim-toggle-row" id="plim-bg-invert-row" style="display:none;margin-top:4px;">
						<input type="checkbox" id="plim-bg-invert">
						<?php esc_html_e( 'Invert selection (keep background)', 'pizzalayer' ); ?>
					</label>
				</div>

				<!-- Output -->
				<div class="plim-card" id="plim-out-card">
					<h2 class="plim-card-h"><span class="dashicons dashicons-download"></span> <?php esc_html_e( 'Export', 'pizzalayer' ); ?></h2>
					<label class="plim-label"><?php esc_html_e( 'Output size', 'pizzalayer' ); ?>
						<select id="plim-out-size" class="plim-select">
							<option value="512">512 px</option>
							<option value="1024" selected>1024 px</option>
							<option value="2048">2048 px</option>
							<option value="original">Original</option>
						</select>
					</label>
					<label class="plim-label" style="margin-top:6px;"><?php esc_html_e( 'File name', 'pizzalayer' ); ?>
						<input type="text" id="plim-filename" class="plim-text-input" placeholder="layer-image" value="layer-image">
					</label>
					<div class="plim-out-btns">
						<button type="button" class="button button-primary plim-btn-full" id="plim-download-btn" disabled>
							<span class="dashicons dashicons-download"></span> <?php esc_html_e( 'Download PNG', 'pizzalayer' ); ?>
						</button>
						<button type="button" class="button plim-btn-full" id="plim-send-media-btn" disabled>
							<span class="dashicons dashicons-admin-media"></span> <?php esc_html_e( 'Send to Media Library', 'pizzalayer' ); ?>
						</button>
					</div>
					<p class="plim-out-note" id="plim-out-note"></p>
				</div>

			</aside><!-- /.plim-sidebar -->

			<!-- ── Right panel: canvas ──────────────────────────────────────── -->
			<main class="plim-canvas-area" id="plim-canvas-area">
				<div class="plim-canvas-toolbar" id="plim-canvas-toolbar">
					<span class="plim-tool-group">
						<button type="button" class="plim-tool-btn plim-tool-btn--active" id="plim-tool-crop" title="<?php esc_attr_e( 'Crop / pan (C)', 'pizzalayer' ); ?>">
							<span class="dashicons dashicons-image-crop"></span> <?php esc_html_e( 'Crop', 'pizzalayer' ); ?>
						</button>
						<button type="button" class="plim-tool-btn" id="plim-tool-move" title="<?php esc_attr_e( 'Pan (M)', 'pizzalayer' ); ?>">
							<span class="dashicons dashicons-move"></span> <?php esc_html_e( 'Pan', 'pizzalayer' ); ?>
						</button>
					</span>
					<span class="plim-tool-group">
						<button type="button" class="plim-tool-btn" id="plim-zoom-out" title="<?php esc_attr_e( 'Zoom out (-)', 'pizzalayer' ); ?>">
							<span class="dashicons dashicons-minus"></span>
						</button>
						<span class="plim-zoom-level" id="plim-zoom-level">100%</span>
						<button type="button" class="plim-tool-btn" id="plim-zoom-in" title="<?php esc_attr_e( 'Zoom in (+)', 'pizzalayer' ); ?>">
							<span class="dashicons dashicons-plus"></span>
						</button>
						<button type="button" class="plim-tool-btn" id="plim-zoom-fit" title="<?php esc_attr_e( 'Fit (F)', 'pizzalayer' ); ?>">
							<span class="dashicons dashicons-fullscreen-alt"></span>
						</button>
					</span>
					<span class="plim-tool-group">
						<button type="button" class="plim-tool-btn" id="plim-rotate-ccw" title="<?php esc_attr_e( 'Rotate 90° left', 'pizzalayer' ); ?>">
							<span class="dashicons dashicons-undo"></span>
						</button>
						<button type="button" class="plim-tool-btn" id="plim-rotate-cw" title="<?php esc_attr_e( 'Rotate 90° right', 'pizzalayer' ); ?>">
							<span class="dashicons dashicons-redo"></span>
						</button>
						<button type="button" class="plim-tool-btn" id="plim-flip-h" title="<?php esc_attr_e( 'Flip horizontal', 'pizzalayer' ); ?>">⇄</button>
						<button type="button" class="plim-tool-btn" id="plim-flip-v" title="<?php esc_attr_e( 'Flip vertical', 'pizzalayer' ); ?>">⇅</button>
					</span>
					<span class="plim-tool-group plim-tool-group--right">
						<button type="button" class="plim-tool-btn" id="plim-undo-btn" title="<?php esc_attr_e( 'Undo (Ctrl+Z)', 'pizzalayer' ); ?>" disabled>
							<span class="dashicons dashicons-undo"></span> <?php esc_html_e( 'Undo', 'pizzalayer' ); ?>
						</button>
						<span class="plim-img-info" id="plim-img-info"></span>
					</span>
				</div>

				<!-- Canvas stage -->
				<div class="plim-stage" id="plim-stage">
					<div class="plim-empty-state" id="plim-empty-state">
						<span class="dashicons dashicons-format-image" style="font-size:52px;width:52px;height:52px;color:#ddd;"></span>
						<p><?php esc_html_e( 'Upload an image to get started', 'pizzalayer' ); ?></p>
					</div>
					<!-- Display canvas (what user sees) -->
					<canvas id="plim-canvas" style="display:none;"></canvas>
					<!-- Crop overlay SVG drawn over the canvas -->
					<svg id="plim-guide-svg" style="display:none;position:absolute;top:0;left:0;pointer-events:none;"></svg>
				</div>

				<!-- Preview strip -->
				<div class="plim-preview-strip" id="plim-preview-strip" style="display:none;">
					<div class="plim-preview-item">
						<div class="plim-preview-thumb plim-preview-thumb--dark" id="plim-preview-dark">
							<canvas id="plim-thumb-canvas-dark"></canvas>
						</div>
						<span class="plim-preview-label"><?php esc_html_e( 'On dark', 'pizzalayer' ); ?></span>
					</div>
					<div class="plim-preview-item">
						<div class="plim-preview-thumb plim-preview-thumb--check" id="plim-preview-check">
							<canvas id="plim-thumb-canvas-check"></canvas>
						</div>
						<span class="plim-preview-label"><?php esc_html_e( 'Transparency', 'pizzalayer' ); ?></span>
					</div>
					<div class="plim-preview-item">
						<div class="plim-preview-thumb plim-preview-thumb--pizza" id="plim-preview-pizza">
							<canvas id="plim-thumb-canvas-pizza"></canvas>
						</div>
						<span class="plim-preview-label"><?php esc_html_e( 'On pizza base', 'pizzalayer' ); ?></span>
					</div>
					<div class="plim-preview-item">
						<div class="plim-preview-thumb plim-preview-thumb--light" id="plim-preview-light">
							<canvas id="plim-thumb-canvas-light"></canvas>
						</div>
						<span class="plim-preview-label"><?php esc_html_e( 'On light', 'pizzalayer' ); ?></span>
					</div>
				</div>
			</main>

		</div><!-- /.plim-shell -->
		</div><!-- /.wrap -->

		<script>
		window.plimConfig = {
			ajaxUrl:    <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>,
			nonce:      <?php echo wp_json_encode( wp_create_nonce( 'pizzalayer_layer_image_maker' ) ); ?>,
			aspectRatio: <?php echo wp_json_encode( $aspect_js ); ?>
		};
		</script>
		<?php $this->render_script(); ?>
		<?php
	}

	// ── AJAX: receive base64 PNG, save to media library ──────────────────────
	public function ajax_upload_layer_image(): void {
		check_ajax_referer( 'pizzalayer_layer_image_maker', 'nonce' );
		if ( ! current_user_can( 'upload_files' ) ) { wp_send_json_error( 'Forbidden' ); }

		$data     = isset( $_POST['data'] ) ? sanitize_text_field( $_POST['data'] ) : '';
		$filename = isset( $_POST['filename'] ) ? sanitize_file_name( $_POST['filename'] ) : 'layer-image.png';

		// Strip data-URI header
		if ( strpos( $data, 'base64,' ) !== false ) {
			[ , $data ] = explode( 'base64,', $data );
		}
		$raw = base64_decode( $data ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
		if ( ! $raw ) { wp_send_json_error( 'Bad data' ); }

		// Verify the decoded bytes are a real image before touching the filesystem.
		$finfo     = new \finfo( FILEINFO_MIME_TYPE );
		$real_mime = $finfo->buffer( $raw );
		if ( ! in_array( $real_mime, [ 'image/png', 'image/jpeg', 'image/gif', 'image/webp' ], true ) ) {
			wp_send_json_error( 'Invalid image data' );
		}

		// Force a safe extension that matches the real MIME type.
		$ext_map  = [ 'image/png' => '.png', 'image/jpeg' => '.jpg', 'image/gif' => '.gif', 'image/webp' => '.webp' ];
		$safe_ext = $ext_map[ $real_mime ];
		$filename = pathinfo( $filename, PATHINFO_FILENAME ) . $safe_ext;

		// Write temp file
		$tmp = wp_tempnam( $filename );
		file_put_contents( $tmp, $raw ); // phpcs:ignore WordPress.WP.AlternativeFunctions

		$attachment_id = media_handle_sideload(
			[
				'name'     => $filename,
				'type'     => $real_mime,
				'tmp_name' => $tmp,
				'error'    => 0,
				'size'     => strlen( $raw ),
			],
			0,
			'',
			[ 'post_title' => pathinfo( $filename, PATHINFO_FILENAME ) ]
		);

		@unlink( $tmp ); // phpcs:ignore

		if ( is_wp_error( $attachment_id ) ) {
			wp_send_json_error( $attachment_id->get_error_message() );
		}

		$url = wp_get_attachment_url( $attachment_id );
		wp_send_json_success( [ 'id' => $attachment_id, 'url' => $url ] );
	}

	// ── Styles ───────────────────────────────────────────────────────────────
	private function render_styles(): void {
		?>
		<style>
		/* ── Wrap ───────────────────────────────────────────── */
		.plim-wrap { max-width: 1600px; }
		.plim-intro { color: #646970; margin: 4px 0 16px; font-size: 13px; }

		/* ── Header ─────────────────────────────────────────── */
		.plim-header {
			display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
			background: linear-gradient(135deg, #1a1e23 0%, #2d3748 100%);
			color: #fff; border-radius: 10px; padding: 22px 28px; margin-bottom: 20px;
		}
		.plim-header__icon {
			font-size: 36px !important; width: 36px !important; height: 36px !important;
			color: #ff6b35; flex-shrink: 0;
		}
		.plim-header__title { margin: 0; font-size: 22px; font-weight: 700; color: #fff; }
		.plim-header__sub   { margin: 3px 0 0; color: #8d97a5; font-size: 13px; }

		/* ── Shell: sidebar + canvas side-by-side ───────────── */
		.plim-shell {
			display: flex;
			gap: 18px;
			align-items: flex-start;
		}

		/* ── Sidebar ─────────────────────────────────────────── */
		.plim-sidebar {
			flex: 0 0 270px;
			min-width: 230px;
			max-width: 300px;
			display: flex;
			flex-direction: column;
			gap: 12px;
		}

		/* ── Cards ───────────────────────────────────────────── */
		.plim-card {
			background: #fff;
			border: 1px solid #e0e3e7;
			border-radius: 10px;
			padding: 14px 16px;
		}
		.plim-card-h {
			margin: 0 0 12px;
			font-size: 13px;
			font-weight: 700;
			color: #1d2023;
			display: flex;
			align-items: center;
			gap: 6px;
		}
		.plim-card-h .dashicons {
			font-size: 15px !important;
			width: 15px !important;
			height: 15px !important;
			color: #ff6b35;
		}

		/* ── Drop zone ───────────────────────────────────────── */
		.plim-drop-zone {
			border: 2px dashed #c3c4c7;
			border-radius: 8px;
			padding: 20px 12px;
			text-align: center;
			cursor: pointer;
			transition: border-color .2s, background .2s;
			color: #646970;
			font-size: 13px;
		}
		.plim-drop-zone:hover,
		.plim-drop-zone:focus,
		.plim-drop-zone--over {
			border-color: #ff6b35;
			background: #fff5f0;
			color: #ff6b35;
			outline: none;
		}
		.plim-drop-icon {
			font-size: 32px !important;
			width: 32px !important;
			height: 32px !important;
			display: block;
			margin: 0 auto 6px;
			color: #c3c4c7;
		}
		.plim-drop-zone:hover .plim-drop-icon,
		.plim-drop-zone--over .plim-drop-icon { color: #ff6b35; }
		.plim-drop-zone p { margin: 0; }
		.plim-media-btn {
			width: 100%;
			margin-top: 8px;
			display: flex !important;
			align-items: center;
			justify-content: center;
			gap: 5px;
		}
		.plim-media-btn .dashicons { font-size: 14px !important; width: 14px !important; height: 14px !important; }

		/* ── Form controls ───────────────────────────────────── */
		.plim-label {
			display: block;
			font-size: 12px;
			color: #50575e;
			margin-bottom: 6px;
		}
		.plim-label-sm { font-size: 12px; color: #50575e; display: inline-flex; align-items: center; gap: 4px; }
		.plim-select, .plim-text-input {
			width: 100%;
			margin-top: 3px;
			font-size: 12px;
			border: 1px solid #c3c4c7;
			border-radius: 4px;
			padding: 4px 8px;
			box-sizing: border-box;
			background: #fff;
			color: #1d2023;
		}
		.plim-num-input {
			width: 52px;
			font-size: 12px;
			border: 1px solid #c3c4c7;
			border-radius: 4px;
			padding: 4px 6px;
			text-align: center;
		}
		.plim-ratio-sep { padding: 0 3px; font-size: 14px; color: #646970; }
		.plim-custom-ratio-row { display: flex; align-items: center; margin-top: 8px; }
		.plim-toggle-row { display: flex; align-items: center; gap: 6px; cursor: pointer; }

		/* ── Sliders ─────────────────────────────────────────── */
		.plim-slider-row { margin-bottom: 8px; }
		.plim-slider-label {
			display: flex;
			justify-content: space-between;
			font-size: 12px;
			color: #50575e;
			margin-bottom: 3px;
		}
		.plim-slider-val { color: #1d2023; font-weight: 600; min-width: 32px; text-align: right; }
		.plim-slider {
			width: 100%;
			accent-color: #ff6b35;
			cursor: pointer;
		}
		.plim-separator { height: 1px; background: #e0e3e7; margin: 10px 0; }
		.plim-reset-adj {
			margin-left: auto;
			font-size: 11px;
			color: #646970;
			text-decoration: none;
			padding: 0;
			cursor: pointer;
		}
		.plim-reset-adj:hover { color: #ff6b35; }

		/* ── Output buttons ──────────────────────────────────── */
		.plim-out-btns { display: flex; flex-direction: column; gap: 6px; margin-top: 10px; }
		.plim-btn-full {
			width: 100%;
			display: flex !important;
			align-items: center;
			justify-content: center;
			gap: 5px;
			font-size: 12px !important;
		}
		.plim-btn-full .dashicons { font-size: 14px !important; width: 14px !important; height: 14px !important; }
		.plim-out-note { font-size: 11px; color: #646970; margin: 6px 0 0; text-align: center; }
		.plim-out-note.success { color: #00a32a; }
		.plim-out-note.error   { color: #d63638; }

		/* ── Canvas area ─────────────────────────────────────── */
		.plim-canvas-area {
			flex: 1 1 auto;
			min-width: 0;
			display: flex;
			flex-direction: column;
			gap: 12px;
		}

		/* ── Toolbar ─────────────────────────────────────────── */
		.plim-canvas-toolbar {
			background: #fff;
			border: 1px solid #e0e3e7;
			border-radius: 10px;
			padding: 8px 12px;
			display: flex;
			flex-wrap: wrap;
			gap: 8px;
			align-items: center;
		}
		.plim-tool-group {
			display: flex;
			align-items: center;
			gap: 4px;
		}
		.plim-tool-group--right { margin-left: auto; }
		.plim-tool-btn {
			display: inline-flex;
			align-items: center;
			gap: 4px;
			background: #f6f7f7;
			border: 1px solid #c3c4c7;
			border-radius: 5px;
			padding: 4px 8px;
			font-size: 12px;
			cursor: pointer;
			color: #3c434a;
			transition: background .15s, border-color .15s, color .15s;
			white-space: nowrap;
		}
		.plim-tool-btn:hover { background: #fff5f0; border-color: #ff6b35; color: #ff6b35; }
		.plim-tool-btn--active { background: #ff6b35; border-color: #ff6b35; color: #fff !important; }
		.plim-tool-btn:disabled { opacity: .5; cursor: not-allowed; pointer-events: none; }
		.plim-tool-btn .dashicons { font-size: 14px !important; width: 14px !important; height: 14px !important; }
		.plim-zoom-level { font-size: 12px; color: #646970; min-width: 38px; text-align: center; }
		.plim-img-info { font-size: 11px; color: #646970; }

		/* ── Stage / canvas ──────────────────────────────────── */
		.plim-stage {
			background: #1a1a2e;
			border-radius: 10px;
			min-height: 480px;
			position: relative;
			overflow: hidden;
			display: flex;
			align-items: center;
			justify-content: center;
		}
		.plim-empty-state {
			text-align: center;
			color: #555;
		}
		.plim-empty-state p { margin: 8px 0 0; font-size: 14px; color: #555; }
		#plim-canvas {
			display: block;
			cursor: crosshair;
			image-rendering: pixelated;
			max-width: 100%;
			max-height: 70vh;
			touch-action: none;
		}
		#plim-guide-svg {
			width: 100%;
			height: 100%;
			pointer-events: none;
		}

		/* ── Preview strip ───────────────────────────────────── */
		.plim-preview-strip {
			display: flex;
			gap: 12px;
			flex-wrap: wrap;
		}
		.plim-preview-item {
			display: flex;
			flex-direction: column;
			align-items: center;
			gap: 4px;
		}
		.plim-preview-thumb {
			width: 110px;
			height: 110px;
			border-radius: 8px;
			border: 1px solid #e0e3e7;
			overflow: hidden;
			display: flex;
			align-items: center;
			justify-content: center;
		}
		.plim-preview-thumb canvas { max-width: 100%; max-height: 100%; display: block; }
		.plim-preview-thumb--dark  { background: #1a1a1a; }
		.plim-preview-thumb--light { background: #fff; }
		.plim-preview-thumb--check {
			background-image: linear-gradient(45deg, #ccc 25%, transparent 25%),
				linear-gradient(-45deg, #ccc 25%, transparent 25%),
				linear-gradient(45deg, transparent 75%, #ccc 75%),
				linear-gradient(-45deg, transparent 75%, #ccc 75%);
			background-size: 12px 12px;
			background-position: 0 0, 0 6px, 6px -6px, -6px 0px;
			background-color: #fff;
		}
		.plim-preview-thumb--pizza { background: radial-gradient(circle, #c2803a 40%, #a06025 100%); }
		.plim-preview-label { font-size: 11px; color: #646970; }

		@media (max-width: 900px) {
			.plim-shell { flex-direction: column; }
			.plim-sidebar { flex: none; max-width: 100%; width: 100%; }
		}
		</style>
		<?php
	}

	// ── Script ───────────────────────────────────────────────────────────────
	private function render_script(): void {
		?>
		<script>
		(function(){
		'use strict';

		var cfg       = window.plimConfig || {};
		var AJAX_URL  = cfg.ajaxUrl  || '';
		var NONCE     = cfg.nonce    || '';

		// ── State ─────────────────────────────────────────────────────────────
		var state = {
			imgEl:        null,   // original Image element
			rotation:     0,      // multiples of 90
			flipH:        false,
			flipV:        false,
			zoom:         1,
			panX:         0,
			panY:         0,
			cropX:        0.1,    // normalised 0..1 relative to displayed image
			cropY:        0.1,
			cropW:        0.8,
			cropH:        0.8,
			activeTool:   'crop',
			aspectW:      4,
			aspectH:      3,
			showGuide:    true,
			showThirds:   true,
			brightness:   0,
			contrast:     0,
			saturation:   0,
			hue:          0,
			blur:         0,
			sharpen:      0,
			opacity:      100,
			removeBg:     false,
			bgThreshold:  30,
			bgInvert:     false,
			undoStack:    [],
		};

		// ── DOM refs ─────────────────────────────────────────────────────────
		var $shell          = document.getElementById('plim-shell');
		var $dropZone       = document.getElementById('plim-drop-zone');
		var $fileInput      = document.getElementById('plim-file-input');
		var $mediaBtn       = document.getElementById('plim-media-btn');
		var $canvas         = document.getElementById('plim-canvas');
		var ctx             = $canvas ? $canvas.getContext('2d') : null;
		var $guideSvg       = document.getElementById('plim-guide-svg');
		var $emptyState     = document.getElementById('plim-empty-state');
		var $stage          = document.getElementById('plim-stage');
		var $previewStrip   = document.getElementById('plim-preview-strip');
		var $imgInfo        = document.getElementById('plim-img-info');
		var $zoomLevel      = document.getElementById('plim-zoom-level');
		var $outNote        = document.getElementById('plim-out-note');
		var $downloadBtn    = document.getElementById('plim-download-btn');
		var $sendMediaBtn   = document.getElementById('plim-send-media-btn');
		var $undoBtn        = document.getElementById('plim-undo-btn');
		var $aspectPreset   = document.getElementById('plim-aspect-preset');
		var $customRatioRow = document.getElementById('plim-custom-ratio-row');
		var $ratioW         = document.getElementById('plim-ratio-w');
		var $ratioH         = document.getElementById('plim-ratio-h');
		var $showGuide      = document.getElementById('plim-show-guide');
		var $showThirds     = document.getElementById('plim-show-thirds');
		var $removeBg       = document.getElementById('plim-remove-bg');
		var $bgRow          = document.getElementById('plim-bg-row');
		var $bgInvertRow    = document.getElementById('plim-bg-invert-row');
		var $bgInvert       = document.getElementById('plim-bg-invert');
		var $bgThresh       = document.getElementById('plim-bg-thresh');
		var $bgThreshVal    = document.getElementById('plim-bg-thresh-val');
		var $filenameInput  = document.getElementById('plim-filename');
		var $outSizeSelect  = document.getElementById('plim-out-size');

		// ── Init aspect from settings ─────────────────────────────────────────
		(function(){
			var asp = cfg.aspectRatio || '4/3';
			var parts = asp.split('/');
			if(parts.length === 2){
				var w = parseFloat(parts[0]), h = parseFloat(parts[1]);
				if(w && h){
					state.aspectW = w;
					state.aspectH = h;
					// Try to find matching preset
					var val = Math.round(w)+'/'+Math.round(h);
					var found = false;
					if($aspectPreset){
						Array.from($aspectPreset.options).forEach(function(opt){
							if(opt.value === val){ opt.selected = true; found = true; }
						});
					}
					if(!found && $aspectPreset){ $aspectPreset.value = 'custom'; }
					if($ratioW) $ratioW.value = w;
					if($ratioH) $ratioH.value = h;
					updateCropToAspect();
				}
			}
		})();

		// ── Image loading ─────────────────────────────────────────────────────
		function loadImageSrc(src){
			var img = new Image();
			img.onload = function(){
				state.imgEl = img;
				state.rotation = 0; state.flipH = false; state.flipV = false;
				state.zoom = 1; state.panX = 0; state.panY = 0;
				updateCropToAspect();
				showCanvas();
				fitToStage();
				render();
				updatePreviews();
				if($imgInfo) $imgInfo.textContent = img.naturalWidth + ' × ' + img.naturalHeight + ' px';
				if($downloadBtn) $downloadBtn.disabled = false;
				if($sendMediaBtn) $sendMediaBtn.disabled = false;
				if($previewStrip) $previewStrip.style.display = 'flex';
			};
			img.src = src;
		}

		// ── Drop zone ─────────────────────────────────────────────────────────
		if($dropZone){
			$dropZone.addEventListener('click', function(){ $fileInput && $fileInput.click(); });
			$dropZone.addEventListener('keydown', function(e){ if(e.key==='Enter'||e.key===' '){ e.preventDefault(); $fileInput && $fileInput.click(); }});
			$dropZone.addEventListener('dragover', function(e){ e.preventDefault(); $dropZone.classList.add('plim-drop-zone--over'); });
			$dropZone.addEventListener('dragleave', function(){ $dropZone.classList.remove('plim-drop-zone--over'); });
			$dropZone.addEventListener('drop', function(e){
				e.preventDefault(); $dropZone.classList.remove('plim-drop-zone--over');
				var file = e.dataTransfer.files[0];
				if(file && file.type.startsWith('image/')){ readFile(file); }
			});
		}
		if($fileInput){
			$fileInput.addEventListener('change', function(){
				if($fileInput.files[0]) readFile($fileInput.files[0]);
			});
		}

		function readFile(file){
			var reader = new FileReader();
			reader.onload = function(e){ loadImageSrc(e.target.result); };
			reader.readAsDataURL(file);
			// Seed filename from file name
			if($filenameInput){
				$filenameInput.value = file.name.replace(/\.[^.]+$/, '') || 'layer-image';
			}
		}

		// ── Media library picker ─────────────────────────────────────────────
		if($mediaBtn){
			$mediaBtn.addEventListener('click', function(){
				if(typeof wp === 'undefined' || !wp.media) return;
				var frame = wp.media({ title: 'Choose Layer Image', button: { text: 'Use Image' }, multiple: false, library: { type: 'image' } });
				frame.on('select', function(){
					var att = frame.state().get('selection').first().toJSON();
					loadImageSrc(att.url);
					if($filenameInput) $filenameInput.value = (att.filename || 'layer-image').replace(/\.[^.]+$/,'');
				});
				frame.open();
			});
		}

		// ── Canvas display ────────────────────────────────────────────────────
		function showCanvas(){
			if($emptyState) $emptyState.style.display = 'none';
			if($canvas)     $canvas.style.display = 'block';
			if($guideSvg)   $guideSvg.style.display = 'block';
		}

		function fitToStage(){
			if(!state.imgEl || !$stage) return;
			var sw = $stage.clientWidth  || 600;
			var sh = $stage.clientHeight || 480;
			var iw = getRotatedW(), ih = getRotatedH();
			state.zoom = Math.min(1, (sw-40)/iw, (sh-40)/ih);
			state.panX = 0; state.panY = 0;
			updateZoomLabel();
		}

		function getRotatedW(){ var img=state.imgEl; if(!img) return 1; return (state.rotation%180===0)?img.naturalWidth:img.naturalHeight; }
		function getRotatedH(){ var img=state.imgEl; if(!img) return 1; return (state.rotation%180===0)?img.naturalHeight:img.naturalWidth; }

		// ── Main render ───────────────────────────────────────────────────────
		function render(){
			if(!state.imgEl || !ctx) return;
			var dw = getRotatedW(), dh = getRotatedH();

			// Size the canvas element to image natural (rotated) dims
			$canvas.width  = dw;
			$canvas.height = dh;

			ctx.save();
			ctx.clearRect(0,0,dw,dh);

			// Apply flip + rotation transforms
			ctx.translate(dw/2, dh/2);
			if(state.flipH) ctx.scale(-1,1);
			if(state.flipV) ctx.scale(1,-1);
			ctx.rotate(state.rotation * Math.PI / 180);
			ctx.drawImage(state.imgEl, -state.imgEl.naturalWidth/2, -state.imgEl.naturalHeight/2);
			ctx.restore();

			// Apply CSS filter for visual preview (non-destructive)
			var filterStr = buildFilterString();
			$canvas.style.filter = filterStr;
			$canvas.style.opacity = state.opacity / 100;

			// Apply zoom + pan via CSS transform
			$canvas.style.transform = 'scale('+state.zoom+') translate('+state.panX+'px,'+state.panY+'px)';
			$canvas.style.transformOrigin = 'center center';

			// Background removal: re-draw with pixel manipulation if active
			if(state.removeBg){ applyBgRemoval(); }

			drawGuide();
			updatePreviews();
		}

		function buildFilterString(){
			var f = '';
			if(state.brightness !== 0) f += 'brightness('+(1 + state.brightness/100)+') ';
			if(state.contrast   !== 0) f += 'contrast('  +(1 + state.contrast  /100)+') ';
			if(state.saturation !== 0) f += 'saturate('  +(1 + state.saturation/100)+') ';
			if(state.hue        !== 0) f += 'hue-rotate('+state.hue+'deg) ';
			if(state.blur       !== 0) f += 'blur('+state.blur+'px) ';
			return f.trim() || 'none';
		}

		// ── Background removal (pixel-level) ─────────────────────────────────
		function applyBgRemoval(){
			if(!ctx || !state.imgEl) return;
			var idata = ctx.getImageData(0,0,$canvas.width,$canvas.height);
			var d = idata.data;
			// Sample corner pixels to estimate background colour
			var samples = [
				[d[0],d[1],d[2]],
				[d[(($canvas.width-1)*4)], d[(($canvas.width-1)*4)+1], d[(($canvas.width-1)*4)+2]],
				[d[(($canvas.height-1)*$canvas.width)*4], d[(($canvas.height-1)*$canvas.width)*4+1], d[(($canvas.height-1)*$canvas.width)*4+2]],
			];
			var bgR = Math.round(samples.reduce(function(a,s){return a+s[0];},0)/samples.length);
			var bgG = Math.round(samples.reduce(function(a,s){return a+s[1];},0)/samples.length);
			var bgB = Math.round(samples.reduce(function(a,s){return a+s[2];},0)/samples.length);
			var thresh = state.bgThreshold;
			for(var i=0;i<d.length;i+=4){
				var dr=Math.abs(d[i]-bgR), dg=Math.abs(d[i+1]-bgG), db=Math.abs(d[i+2]-bgB);
				var dist = Math.sqrt(dr*dr+dg*dg+db*db);
				var isBg = dist < thresh;
				if(state.bgInvert) isBg = !isBg;
				if(isBg) d[i+3] = 0;
			}
			ctx.putImageData(idata,0,0);
		}

		// ── Guide overlay ─────────────────────────────────────────────────────
		function drawGuide(){
			if(!$guideSvg || !$canvas) return;
			var cw = $canvas.offsetWidth  || $canvas.width;
			var ch = $canvas.offsetHeight || $canvas.height;

			$guideSvg.setAttribute('viewBox','0 0 '+cw+' '+ch);
			$guideSvg.setAttribute('width',  cw);
			$guideSvg.setAttribute('height', ch);
			$guideSvg.innerHTML = '';

			if(!state.imgEl) return;

			// Crop rectangle in canvas-display coordinates
			var cx = state.cropX * cw;
			var cy = state.cropY * ch;
			var cwr = state.cropW * cw;
			var chr = state.cropH * ch;

			// Darken outside crop
			var mask = document.createElementNS('http://www.w3.org/2000/svg','path');
			mask.setAttribute('fill','rgba(0,0,0,0.48)');
			mask.setAttribute('fill-rule','evenodd');
			mask.setAttribute('d',
				'M 0 0 L '+cw+' 0 L '+cw+' '+ch+' L 0 '+ch+' Z '+
				'M '+cx+' '+cy+' L '+(cx+cwr)+' '+cy+' L '+(cx+cwr)+' '+(cy+chr)+' L '+cx+' '+(cy+chr)+' Z'
			);
			$guideSvg.appendChild(mask);

			if(state.showGuide){
				// Crop border
				var rect = document.createElementNS('http://www.w3.org/2000/svg','rect');
				rect.setAttribute('x', cx); rect.setAttribute('y', cy);
				rect.setAttribute('width', cwr); rect.setAttribute('height', chr);
				rect.setAttribute('fill', 'none');
				rect.setAttribute('stroke', '#ff6b35');
				rect.setAttribute('stroke-width', '2');
				$guideSvg.appendChild(rect);

				// Corner handles
				var hSize = 10;
				[[cx,cy],[cx+cwr,cy],[cx,cy+chr],[cx+cwr,cy+chr]].forEach(function(p){
					var h = document.createElementNS('http://www.w3.org/2000/svg','rect');
					h.setAttribute('x', p[0]-hSize/2); h.setAttribute('y', p[1]-hSize/2);
					h.setAttribute('width', hSize); h.setAttribute('height', hSize);
					h.setAttribute('fill','#ff6b35'); h.setAttribute('rx','2');
					h.setAttribute('data-handle','1');
					$guideSvg.appendChild(h);
				});

				// Simulated pizza circle guide
				var pr = Math.min(cwr,chr) * 0.44;
				var pcx = cx + cwr/2, pcy = cy + chr/2;
				var pizza = document.createElementNS('http://www.w3.org/2000/svg','ellipse');
				pizza.setAttribute('cx', pcx); pizza.setAttribute('cy', pcy);
				pizza.setAttribute('rx', pr*1.1); pizza.setAttribute('ry', pr);
				pizza.setAttribute('fill','none');
				pizza.setAttribute('stroke','rgba(255,200,0,0.55)');
				pizza.setAttribute('stroke-width','1.5');
				pizza.setAttribute('stroke-dasharray','6 4');
				$guideSvg.appendChild(pizza);

				// Label
				var lbl = document.createElementNS('http://www.w3.org/2000/svg','text');
				lbl.setAttribute('x', cx+4); lbl.setAttribute('y', cy-5);
				lbl.setAttribute('font-size','10'); lbl.setAttribute('fill','#ff6b35');
				lbl.setAttribute('font-family','sans-serif');
				lbl.textContent = Math.round(state.aspectW)+':'+Math.round(state.aspectH)+' crop';
				$guideSvg.appendChild(lbl);
			}

			if(state.showThirds){
				for(var i=1;i<3;i++){
					var lv = document.createElementNS('http://www.w3.org/2000/svg','line');
					lv.setAttribute('x1',cx+cwr*i/3); lv.setAttribute('y1',cy);
					lv.setAttribute('x2',cx+cwr*i/3); lv.setAttribute('y2',cy+chr);
					lv.setAttribute('stroke','rgba(255,255,255,0.25)'); lv.setAttribute('stroke-width','1');
					$guideSvg.appendChild(lv);
					var lh = document.createElementNS('http://www.w3.org/2000/svg','line');
					lh.setAttribute('x1',cx); lh.setAttribute('y1',cy+chr*i/3);
					lh.setAttribute('x2',cx+cwr); lh.setAttribute('y2',cy+chr*i/3);
					lh.setAttribute('stroke','rgba(255,255,255,0.25)'); lh.setAttribute('stroke-width','1');
					$guideSvg.appendChild(lh);
				}
			}
		}

		// ── Crop handle drag ──────────────────────────────────────────────────
		var dragState = null;
		if($guideSvg){
			$guideSvg.style.pointerEvents = 'auto';
			$guideSvg.addEventListener('mousedown', function(e){
				if(state.activeTool !== 'crop' || !state.imgEl) return;
				var cw = $canvas.offsetWidth  || 1;
				var ch = $canvas.offsetHeight || 1;
				var r  = $guideSvg.getBoundingClientRect();
				var mx = e.clientX - r.left, my = e.clientY - r.top;

				// Normalised pointer
				var nx = mx/cw, ny = my/ch;

				// Determine drag type: corner, edge, or move
				var cx=state.cropX,cy=state.cropY,cw2=state.cropW,ch2=state.cropH;
				var corners = [{x:cx,y:cy,dx:'l',dy:'t'},{x:cx+cw2,y:cy,dx:'r',dy:'t'},
				               {x:cx,y:cy+ch2,dx:'l',dy:'b'},{x:cx+cw2,y:cy+ch2,dx:'r',dy:'b'}];
				var tol = 0.04;
				var hit = null;
				corners.forEach(function(c){
					if(Math.abs(nx-c.x)<tol && Math.abs(ny-c.y)<tol) hit = c;
				});

				dragState = {
					type: hit ? 'corner' : 'move',
					corner: hit,
					startNx: nx, startNy: ny,
					origCx: state.cropX, origCy: state.cropY,
					origCw: state.cropW, origCh: state.cropH,
				};
				e.preventDefault();
			});
		}

		document.addEventListener('mousemove', function(e){
			if(!dragState || !$guideSvg || !state.imgEl) return;
			var cw = $canvas.offsetWidth || 1;
			var ch = $canvas.offsetHeight || 1;
			var r  = $guideSvg.getBoundingClientRect();
			var nx = (e.clientX - r.left)/cw;
			var ny = (e.clientY - r.top)/ch;
			var dx = nx - dragState.startNx;
			var dy = ny - dragState.startNy;

			if(dragState.type === 'move'){
				state.cropX = clamp(dragState.origCx + dx, 0, 1-state.cropW);
				state.cropY = clamp(dragState.origCy + dy, 0, 1-state.cropH);
			} else if(dragState.corner){
				var c = dragState.corner;
				var newX = dragState.origCx, newY = dragState.origCy;
				var newW = dragState.origCw, newH = dragState.origCh;
				if(c.dx==='r') newW = clamp(dragState.origCw+dx, 0.1, 1-newX);
				if(c.dx==='l'){ newX = clamp(dragState.origCx+dx, 0, dragState.origCx+dragState.origCw-0.1); newW = (dragState.origCx+dragState.origCw)-newX; }
				if(c.dy==='b') newH = clamp(dragState.origCh+dy, 0.05, 1-newY);
				if(c.dy==='t'){ newY = clamp(dragState.origCy+dy, 0, dragState.origCy+dragState.origCh-0.05); newH = (dragState.origCy+dragState.origCh)-newY; }
				// Maintain aspect ratio
				var asp = state.aspectW / state.aspectH;
				if(c.dx !== 'none'){
					var adjH = newW / asp;
					if(c.dy==='t') newY = (newY+newH) - adjH;
					newH = adjH;
				}
				state.cropX = newX; state.cropY = newY;
				state.cropW = newW; state.cropH = clamp(newH,0.05,1-newY);
			}
			drawGuide();
		});

		document.addEventListener('mouseup', function(){
			if(dragState){ dragState = null; updatePreviews(); }
		});

		// ── Pan tool ─────────────────────────────────────────────────────────
		var panDrag = null;
		if($canvas){
			$canvas.addEventListener('mousedown', function(e){
				if(state.activeTool !== 'move' || !state.imgEl) return;
				panDrag = { startX: e.clientX, startY: e.clientY, origPX: state.panX, origPY: state.panY };
				e.preventDefault();
			});
		}
		document.addEventListener('mousemove', function(e){
			if(!panDrag) return;
			state.panX = panDrag.origPX + (e.clientX - panDrag.startX);
			state.panY = panDrag.origPY + (e.clientY - panDrag.startY);
			if($canvas) $canvas.style.transform = 'scale('+state.zoom+') translate('+state.panX+'px,'+state.panY+'px)';
		});
		document.addEventListener('mouseup', function(){ panDrag = null; });

		// ── Zoom ─────────────────────────────────────────────────────────────
		if($stage){
			$stage.addEventListener('wheel', function(e){
				e.preventDefault();
				state.zoom = clamp(state.zoom * (e.deltaY < 0 ? 1.1 : 0.9), 0.1, 8);
				if($canvas) $canvas.style.transform = 'scale('+state.zoom+') translate('+state.panX+'px,'+state.panY+'px)';
				updateZoomLabel();
			}, {passive:false});
		}

		function btn(id, fn){ var el=document.getElementById(id); if(el) el.addEventListener('click',fn); }

		btn('plim-zoom-in',  function(){ state.zoom = clamp(state.zoom*1.2,0.1,8); if($canvas) $canvas.style.transform='scale('+state.zoom+') translate('+state.panX+'px,'+state.panY+'px)'; updateZoomLabel(); });
		btn('plim-zoom-out', function(){ state.zoom = clamp(state.zoom/1.2,0.1,8); if($canvas) $canvas.style.transform='scale('+state.zoom+') translate('+state.panX+'px,'+state.panY+'px)'; updateZoomLabel(); });
		btn('plim-zoom-fit', function(){ fitToStage(); if($canvas) $canvas.style.transform='scale('+state.zoom+') translate('+state.panX+'px,'+state.panY+'px)'; updateZoomLabel(); });

		function updateZoomLabel(){ if($zoomLevel) $zoomLevel.textContent = Math.round(state.zoom*100)+'%'; }

		// ── Rotate / flip ─────────────────────────────────────────────────────
		btn('plim-rotate-ccw', function(){ pushUndo(); state.rotation=(state.rotation-90+360)%360; render(); });
		btn('plim-rotate-cw',  function(){ pushUndo(); state.rotation=(state.rotation+90)%360; render(); });
		btn('plim-flip-h',     function(){ pushUndo(); state.flipH=!state.flipH; render(); });
		btn('plim-flip-v',     function(){ pushUndo(); state.flipV=!state.flipV; render(); });

		// ── Tool buttons ─────────────────────────────────────────────────────
		btn('plim-tool-crop', function(){
			state.activeTool='crop';
			document.getElementById('plim-tool-crop').classList.add('plim-tool-btn--active');
			document.getElementById('plim-tool-move').classList.remove('plim-tool-btn--active');
			if($canvas) $canvas.style.cursor='crosshair';
		});
		btn('plim-tool-move', function(){
			state.activeTool='move';
			document.getElementById('plim-tool-move').classList.add('plim-tool-btn--active');
			document.getElementById('plim-tool-crop').classList.remove('plim-tool-btn--active');
			if($canvas) $canvas.style.cursor='grab';
		});

		// ── Aspect ratio ─────────────────────────────────────────────────────
		if($aspectPreset){
			$aspectPreset.addEventListener('change', function(){
				if(this.value === 'custom'){
					if($customRatioRow) $customRatioRow.style.display='flex';
				} else {
					if($customRatioRow) $customRatioRow.style.display='none';
					var parts = this.value.split('/');
					state.aspectW = parseFloat(parts[0]);
					state.aspectH = parseFloat(parts[1]);
					updateCropToAspect();
					render();
				}
			});
		}

		[$ratioW,$ratioH].forEach(function(el){ if(el) el.addEventListener('input',function(){
			state.aspectW = parseFloat($ratioW.value)||1;
			state.aspectH = parseFloat($ratioH.value)||1;
			updateCropToAspect(); render();
		}); });

		function updateCropToAspect(){
			// Reset crop to centre with correct aspect
			var asp = state.aspectW / state.aspectH;
			var maxW = 0.85, maxH = 0.85;
			var w = maxW, h = maxW / asp;
			if(h > maxH){ h = maxH; w = maxH * asp; }
			state.cropW = Math.min(w, 1);
			state.cropH = Math.min(h, 1);
			state.cropX = (1-state.cropW)/2;
			state.cropY = (1-state.cropH)/2;
		}

		// ── Guide toggles ─────────────────────────────────────────────────────
		if($showGuide)  $showGuide.addEventListener('change',  function(){ state.showGuide=this.checked;  drawGuide(); });
		if($showThirds) $showThirds.addEventListener('change', function(){ state.showThirds=this.checked; drawGuide(); });

		// ── Adjustment sliders ────────────────────────────────────────────────
		var sliderMap = {
			'plim-brightness':'brightness','plim-contrast':'contrast','plim-saturation':'saturation',
			'plim-hue':'hue','plim-blur':'blur','plim-sharpen':'sharpen','plim-opacity':'opacity',
		};
		Object.keys(sliderMap).forEach(function(id){
			var el = document.getElementById(id);
			var key = sliderMap[id];
			if(el){
				el.addEventListener('input', function(){
					state[key] = parseFloat(this.value);
					var unit = this.getAttribute('data-unit') || '';
					var valEl = document.getElementById(id+'-val');
					if(valEl) valEl.textContent = this.value + unit;
					render();
				});
			}
		});

		// ── Reset adjustments ─────────────────────────────────────────────────
		btn('plim-reset-adj', function(){
			var defaults = {brightness:0,contrast:0,saturation:0,hue:0,blur:0,sharpen:0,opacity:100};
			Object.assign(state, defaults);
			Object.keys(sliderMap).forEach(function(id){
				var el = document.getElementById(id);
				var key = sliderMap[id];
				if(el){ el.value = defaults[key]; }
				var unit = el ? (el.getAttribute('data-unit')||'') : '';
				var valEl = document.getElementById(id+'-val');
				if(valEl) valEl.textContent = defaults[key] + unit;
			});
			render();
		});

		// ── Background removal ────────────────────────────────────────────────
		if($removeBg){
			$removeBg.addEventListener('change', function(){
				state.removeBg = this.checked;
				if($bgRow)       $bgRow.style.display       = this.checked ? 'block' : 'none';
				if($bgInvertRow) $bgInvertRow.style.display = this.checked ? 'flex'  : 'none';
				render();
			});
		}
		if($bgThresh){
			$bgThresh.addEventListener('input', function(){
				state.bgThreshold = parseInt(this.value);
				if($bgThreshVal) $bgThreshVal.textContent = this.value;
				render();
			});
		}
		if($bgInvert){
			$bgInvert.addEventListener('change', function(){ state.bgInvert = this.checked; render(); });
		}

		// ── Undo ─────────────────────────────────────────────────────────────
		function pushUndo(){
			state.undoStack.push({ rotation:state.rotation, flipH:state.flipH, flipV:state.flipV });
			if(state.undoStack.length > 30) state.undoStack.shift();
			if($undoBtn) $undoBtn.disabled = false;
		}
		btn('plim-undo-btn', function(){
			if(!state.undoStack.length) return;
			var prev = state.undoStack.pop();
			state.rotation = prev.rotation; state.flipH = prev.flipH; state.flipV = prev.flipV;
			if(!state.undoStack.length && $undoBtn) $undoBtn.disabled = true;
			render();
		});

		// ── Build output canvas ───────────────────────────────────────────────
		function buildOutputCanvas(){
			if(!state.imgEl) return null;
			var asp = state.aspectW / state.aspectH;
			var outSizeOpt = $outSizeSelect ? $outSizeSelect.value : '1024';
			var iw = getRotatedW(), ih = getRotatedH();
			// Final cropped pixel dims from source
			var srcX = state.cropX * iw;
			var srcY = state.cropY * ih;
			var srcW = state.cropW * iw;
			var srcH = state.cropH * ih;

			var outW, outH;
			if(outSizeOpt === 'original'){
				outW = Math.round(srcW); outH = Math.round(srcH);
			} else {
				var maxPx = parseInt(outSizeOpt);
				if(asp >= 1){ outW = maxPx; outH = Math.round(maxPx/asp); }
				else         { outH = maxPx; outW = Math.round(maxPx*asp); }
			}

			var oc = document.createElement('canvas');
			oc.width  = outW;
			oc.height = outH;
			var octx = oc.getContext('2d');

			// Apply filter
			octx.filter = buildFilterString();
			octx.globalAlpha = state.opacity / 100;

			// We need to draw the rotated+flipped image to a temp canvas first
			var tmp = document.createElement('canvas');
			tmp.width  = iw; tmp.height = ih;
			var tctx = tmp.getContext('2d');
			tctx.save();
			tctx.translate(iw/2, ih/2);
			if(state.flipH) tctx.scale(-1,1);
			if(state.flipV) tctx.scale(1,-1);
			tctx.rotate(state.rotation * Math.PI / 180);
			tctx.drawImage(state.imgEl, -state.imgEl.naturalWidth/2, -state.imgEl.naturalHeight/2);
			tctx.restore();

			octx.drawImage(tmp, srcX, srcY, srcW, srcH, 0, 0, outW, outH);

			if(state.removeBg){
				var idata = octx.getImageData(0,0,outW,outH);
				var d = idata.data;
				var corner = [d[0],d[1],d[2]];
				var thresh = state.bgThreshold;
				var bgR=corner[0],bgG=corner[1],bgB=corner[2];
				for(var i=0;i<d.length;i+=4){
					var dr=Math.abs(d[i]-bgR),dg=Math.abs(d[i+1]-bgG),db=Math.abs(d[i+2]-bgB);
					var dist=Math.sqrt(dr*dr+dg*dg+db*db);
					var isBg = dist < thresh;
					if(state.bgInvert) isBg = !isBg;
					if(isBg) d[i+3] = 0;
				}
				octx.putImageData(idata,0,0);
			}

			return oc;
		}

		// ── Previews ─────────────────────────────────────────────────────────
		function updatePreviews(){
			if(!state.imgEl) return;
			var oc = buildOutputCanvas();
			if(!oc) return;
			['dark','check','pizza','light'].forEach(function(id){
				var thumb = document.getElementById('plim-thumb-canvas-'+id);
				if(!thumb) return;
				var size = 110;
				thumb.width = size; thumb.height = size;
				var tc = thumb.getContext('2d');
				tc.clearRect(0,0,size,size);
				// Scale-to-fit
				var scale = Math.min(size/oc.width, size/oc.height);
				var dw = oc.width*scale, dh = oc.height*scale;
				tc.drawImage(oc, (size-dw)/2, (size-dh)/2, dw, dh);
			});
		}

		// ── Download ─────────────────────────────────────────────────────────
		btn('plim-download-btn', function(){
			var oc = buildOutputCanvas();
			if(!oc){ showNote('No image loaded.','error'); return; }
			var fname = ($filenameInput ? $filenameInput.value.trim() : '') || 'layer-image';
			if(!fname.endsWith('.png')) fname += '.png';
			oc.toBlob(function(blob){
				var url = URL.createObjectURL(blob);
				var a = document.createElement('a');
				a.href = url; a.download = fname;
				document.body.appendChild(a); a.click();
				setTimeout(function(){ document.body.removeChild(a); URL.revokeObjectURL(url); }, 1000);
				showNote('Downloaded as '+fname,'success');
			}, 'image/png');
		});

		// ── Send to Media Library ─────────────────────────────────────────────
		btn('plim-send-media-btn', function(){
			var oc = buildOutputCanvas();
			if(!oc){ showNote('No image loaded.','error'); return; }
			var fname = ($filenameInput ? $filenameInput.value.trim() : '') || 'layer-image';
			if(!fname.endsWith('.png')) fname += '.png';
			showNote('Uploading…','');
			if($sendMediaBtn) $sendMediaBtn.disabled = true;

			oc.toBlob(function(blob){
				var reader = new FileReader();
				reader.onload = function(ev){
					var dataUrl = ev.target.result;
					var fd = new FormData();
					fd.append('action',   'pizzalayer_upload_layer_image');
					fd.append('nonce',    NONCE);
					fd.append('data',     dataUrl);
					fd.append('filename', fname);
					fetch(AJAX_URL, { method:'POST', body:fd })
						.then(function(r){ return r.json(); })
						.then(function(d){
							if(d.success){
								showNote('✓ Added to Media Library (ID '+d.data.id+')', 'success');
							} else {
								showNote('Upload failed: '+(d.data||'unknown error'), 'error');
							}
						})
						.catch(function(){ showNote('Upload error.','error'); })
						.finally(function(){ if($sendMediaBtn) $sendMediaBtn.disabled=false; });
				};
				reader.readAsDataURL(blob);
			}, 'image/png');
		});

		function showNote(msg, cls){
			if(!$outNote) return;
			$outNote.textContent = msg;
			$outNote.className = 'plim-out-note' + (cls ? ' '+cls : '');
		}

		// ── Keyboard shortcuts ────────────────────────────────────────────────
		document.addEventListener('keydown', function(e){
			if(!state.imgEl) return;
			if(e.key === 'c' || e.key === 'C') document.getElementById('plim-tool-crop') && document.getElementById('plim-tool-crop').click();
			if(e.key === 'm' || e.key === 'M') document.getElementById('plim-tool-move') && document.getElementById('plim-tool-move').click();
			if(e.key === '+' || e.key === '=') document.getElementById('plim-zoom-in') && document.getElementById('plim-zoom-in').click();
			if(e.key === '-')                  document.getElementById('plim-zoom-out') && document.getElementById('plim-zoom-out').click();
			if(e.key === 'f' || e.key === 'F') document.getElementById('plim-zoom-fit') && document.getElementById('plim-zoom-fit').click();
			if((e.ctrlKey||e.metaKey) && e.key==='z'){ e.preventDefault(); document.getElementById('plim-undo-btn') && document.getElementById('plim-undo-btn').click(); }
		});

		// ── Helpers ───────────────────────────────────────────────────────────
		function clamp(v,mn,mx){ return Math.max(mn,Math.min(mx,v)); }

		})();
		</script>
		<?php
	}
}
