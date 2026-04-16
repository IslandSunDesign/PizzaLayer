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

		<?php
		// Config passed to JS via wp_localize_script( 'pizzalayer-layer-image-maker', 'plimConfig', [...] )
		?>
		// JS enqueued via wp_enqueue_script( 'pizzalayer-layer-image-maker' )
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
	// render_script() removed — JS extracted to assets/js/admin/layer-image-maker.js
	// and enqueued via AssetManager::enqueue_admin()
}
