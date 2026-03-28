<?php
namespace PizzaLayer\Admin;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * PizzaLayer – Layer Image Maker Meta Box
 *
 * Adds a "Layer Image Maker" meta box to the edit/new screens for every
 * image-bearing CPT (all types except 'sizes'). The box is collapsed by default
 * and shows only a prompt until the user opens it.
 *
 * When opened it embeds a compact version of the image-maker tool (same Canvas/
 * filter pipeline as the standalone page) with a "Set as Layer Image" button that
 * writes the result directly to the post's {type}_layer_image meta field – no
 * download step required.
 *
 * Field write strategy
 * --------------------
 * SCF/ACF stores image fields as either:
 *   (a) an integer attachment ID  (when "Return Format" = ID or Object)
 *   (b) a URL string              (when "Return Format" = URL)
 *
 * Because we don't know which format the user chose, we:
 *  1. Upload the PNG to the media library (via media_handle_sideload) → get an ID.
 *  2. Write the ID to  post_meta("{type}_layer_image").
 *  3. Also write the URL to post_meta("{type}_layer_image") — SCF will use
 *     whichever it finds.  In practice, writing both the ID and the URL to the
 *     same key would conflict, so we write the *attachment ID* (integer) which
 *     SCF/ACF handles regardless of return format.
 *
 * If SCF/ACF is not active we fall back to storing the URL directly.
 */
class LayerImageMetaBox {

	/** CPT slugs that have layer images (sizes has none). */
	private const IMAGE_TYPES = [ 'toppings', 'crusts', 'sauces', 'cheeses', 'drizzles', 'cuts' ];

	// ── Registration ─────────────────────────────────────────────────────────

	public function register_hooks(): void {
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
		add_action( 'wp_ajax_pizzalayer_metabox_set_layer_image', [ $this, 'ajax_set_layer_image' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	public function add_meta_boxes(): void {
		foreach ( self::IMAGE_TYPES as $slug ) {
			$post_type = 'pizzalayer_' . $slug;
			add_meta_box(
				'pizzalayer_layer_image_maker',
				'<span class="dashicons dashicons-format-image" style="color:#ff6b35;font-size:14px;width:14px;height:14px;vertical-align:middle;margin-right:5px;"></span> ' . esc_html__( 'Layer Image Maker', 'pizzalayer' ),
				[ $this, 'render_meta_box' ],
				$post_type,
				'side',
				'default',
				[ 'slug' => $slug ]
			);
		}
	}

	public function enqueue_assets( string $hook ): void {
		// Only on post edit/new screens for our CPTs
		if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) { return; }
		$screen = get_current_screen();
		if ( ! $screen ) { return; }
		$cpt = $screen->post_type ?? '';
		if ( strpos( $cpt, 'pizzalayer_' ) !== 0 ) { return; }
		$slug = str_replace( 'pizzalayer_', '', $cpt );
		if ( ! in_array( $slug, self::IMAGE_TYPES, true ) ) { return; }

		wp_enqueue_media();
	}

	// ── Meta box HTML ─────────────────────────────────────────────────────────

	public function render_meta_box( \WP_Post $post, array $box ): void {
		$slug       = $box['args']['slug'] ?? '';
		$type       = rtrim( $slug, 's' ); // toppings→topping etc.
		$field_key  = $type . '_layer_image';
		$post_id    = $post->ID;

		// Current value — try SCF/ACF get_field first, fall back to raw meta
		$current_url = '';
		$current_id  = 0;
		if ( function_exists( 'get_field' ) ) {
			$val = get_field( $field_key, $post_id );
			if ( is_array( $val ) && ! empty( $val['url'] ) ) {
				$current_url = $val['url'];
				$current_id  = (int) ( $val['ID'] ?? 0 );
			} elseif ( is_string( $val ) && $val ) {
				$current_url = $val;
			} elseif ( is_int( $val ) && $val ) {
				$current_id  = $val;
				$current_url = (string) wp_get_attachment_url( $val );
			}
		}
		if ( ! $current_url ) {
			$meta = get_post_meta( $post_id, $field_key, true );
			if ( is_array( $meta ) && ! empty( $meta['url'] ) ) {
				$current_url = $meta['url'];
				$current_id  = (int) ( $meta['ID'] ?? 0 );
			} elseif ( is_string( $meta ) && $meta ) {
				$current_url = $meta;
			} elseif ( is_numeric( $meta ) && $meta ) {
				$current_id  = (int) $meta;
				$current_url = (string) wp_get_attachment_url( $current_id );
			}
		}

		$aspect_raw = get_option( 'pizzalayer_setting_pizza_aspect', '4 / 3' );
		$aspect_js  = preg_replace( '/\s+/', '', $aspect_raw );
		$singular   = ucfirst( $type );
		$nonce      = wp_create_nonce( 'pizzalayer_layer_image_maker' );
		$meta_nonce = wp_create_nonce( 'pzl_metabox_set_layer_image_' . $post_id );

		?>
		<div class="pzlmb-wrap" id="pzlmb-wrap-<?php echo esc_attr( $slug ); ?>"
		     data-post-id="<?php echo esc_attr( $post_id ); ?>"
		     data-field-key="<?php echo esc_attr( $field_key ); ?>"
		     data-type="<?php echo esc_attr( $type ); ?>"
		     data-ajax="<?php echo esc_url( admin_url('admin-ajax.php') ); ?>"
		     data-nonce="<?php echo esc_attr( $nonce ); ?>"
		     data-meta-nonce="<?php echo esc_attr( $meta_nonce ); ?>"
		     data-aspect="<?php echo esc_attr( $aspect_js ); ?>">

			<!-- ── Collapsed prompt ──────────────────────────────────────── -->
			<div class="pzlmb-prompt" id="pzlmb-prompt-<?php echo esc_attr( $slug ); ?>">
				<?php if ( $current_url ) : ?>
				<div class="pzlmb-current">
					<img src="<?php echo esc_url( $current_url ); ?>" alt="Current layer image"
					     class="pzlmb-current-img" id="pzlmb-current-img-<?php echo esc_attr( $slug ); ?>">
					<span class="pzlmb-current-label"><?php esc_html_e( 'Current layer image', 'pizzalayer' ); ?></span>
				</div>
				<?php else : ?>
				<div class="pzlmb-empty">
					<span class="dashicons dashicons-format-image pzlmb-empty-icon"></span>
					<span class="pzlmb-empty-label"><?php esc_html_e( 'No layer image set', 'pizzalayer' ); ?></span>
				</div>
				<?php endif; ?>

				<button type="button" class="button pzlmb-open-btn"
				        id="pzlmb-open-btn-<?php echo esc_attr( $slug ); ?>">
					<span class="dashicons dashicons-edit"></span>
					<?php echo $current_url ? esc_html__( 'Edit / Replace Layer Image', 'pizzalayer' ) : esc_html__( 'Create Layer Image', 'pizzalayer' ); ?>
				</button>
			</div>

			<!-- ── Expanded editor (hidden until button click) ───────────── -->
			<div class="pzlmb-editor" id="pzlmb-editor-<?php echo esc_attr( $slug ); ?>" style="display:none;">

				<!-- Source buttons -->
				<div class="pzlmb-source-row">
					<div class="pzlmb-drop-zone" id="pzlmb-drop-<?php echo esc_attr( $slug ); ?>"
					     tabindex="0" role="button" aria-label="<?php esc_attr_e( 'Upload image', 'pizzalayer' ); ?>">
						<span class="dashicons dashicons-upload"></span>
						<span><?php esc_html_e( 'Drop / click to upload', 'pizzalayer' ); ?></span>
						<input type="file" class="pzlmb-file-input" accept="image/*" style="display:none;">
					</div>
					<button type="button" class="button pzlmb-media-btn">
						<span class="dashicons dashicons-admin-media"></span> <?php esc_html_e( 'Media Library', 'pizzalayer' ); ?>
					</button>
				</div>

				<!-- Canvas stage -->
				<div class="pzlmb-stage" id="pzlmb-stage-<?php echo esc_attr( $slug ); ?>">
					<div class="pzlmb-canvas-empty" id="pzlmb-canvas-empty-<?php echo esc_attr( $slug ); ?>">
						<span class="dashicons dashicons-format-image"></span>
						<p><?php esc_html_e( 'Upload an image to begin', 'pizzalayer' ); ?></p>
					</div>
					<canvas class="pzlmb-canvas" style="display:none;max-width:100%;"></canvas>
					<svg class="pzlmb-guide-svg" style="display:none;position:absolute;top:0;left:0;pointer-events:none;width:100%;height:100%;"></svg>
				</div>

				<!-- Mini toolbar -->
				<div class="pzlmb-toolbar">
					<button type="button" class="pzlmb-tbtn pzlmb-tbtn--rotate-ccw" title="<?php esc_attr_e( 'Rotate left', 'pizzalayer' ); ?>">↺</button>
					<button type="button" class="pzlmb-tbtn pzlmb-tbtn--rotate-cw"  title="<?php esc_attr_e( 'Rotate right', 'pizzalayer' ); ?>">↻</button>
					<button type="button" class="pzlmb-tbtn pzlmb-tbtn--flip-h"     title="<?php esc_attr_e( 'Flip H', 'pizzalayer' ); ?>">⇄</button>
					<button type="button" class="pzlmb-tbtn pzlmb-tbtn--flip-v"     title="<?php esc_attr_e( 'Flip V', 'pizzalayer' ); ?>">⇅</button>
					<span class="pzlmb-toolbar-sep"></span>
					<label class="pzlmb-tbtn-label" title="Show guide">
						<input type="checkbox" class="pzlmb-show-guide" checked> <?php esc_html_e( 'Guide', 'pizzalayer' ); ?>
					</label>
				</div>

				<!-- Adjustments (compact) -->
				<div class="pzlmb-adj">
					<?php
					$mb_sliders = [
						[ 'key' => 'brightness', 'label' => __( 'Bright', 'pizzalayer' ), 'min' => -100, 'max' => 100, 'def' => 0   ],
						[ 'key' => 'contrast',   'label' => __( 'Contrast', 'pizzalayer' ),'min' => -100, 'max' => 100, 'def' => 0   ],
						[ 'key' => 'saturation', 'label' => __( 'Sat', 'pizzalayer' ),    'min' => -100, 'max' => 100, 'def' => 0   ],
						[ 'key' => 'opacity',    'label' => __( 'Opacity', 'pizzalayer' ), 'min' => 0,   'max' => 100, 'def' => 100 ],
					];
					foreach ( $mb_sliders as $s ) : ?>
					<div class="pzlmb-adj-row">
						<span class="pzlmb-adj-label"><?php echo esc_html( $s['label'] ); ?></span>
						<input type="range" class="pzlmb-slider pzlmb-slider--<?php echo esc_attr( $s['key'] ); ?>"
						       min="<?php echo (int) $s['min']; ?>" max="<?php echo (int) $s['max']; ?>"
						       value="<?php echo (int) $s['def']; ?>" step="1">
						<span class="pzlmb-adj-val"><?php echo (int) $s['def']; ?></span>
					</div>
					<?php endforeach; ?>
					<button type="button" class="button-link pzlmb-reset-adj">↺ <?php esc_html_e( 'Reset', 'pizzalayer' ); ?></button>
				</div>

				<!-- Action row -->
				<div class="pzlmb-actions">
					<button type="button" class="button button-primary pzlmb-set-btn" disabled>
						<span class="dashicons dashicons-yes"></span>
						<?php printf( esc_html__( 'Set as %s Layer Image', 'pizzalayer' ), esc_html( $singular ) ); ?>
					</button>
					<button type="button" class="button pzlmb-cancel-btn"><?php esc_html_e( 'Cancel', 'pizzalayer' ); ?></button>
				</div>
				<p class="pzlmb-status" id="pzlmb-status-<?php echo esc_attr( $slug ); ?>"></p>
			</div>

		</div><!-- /.pzlmb-wrap -->

		<?php $this->render_meta_box_assets( $slug ); ?>
		<?php
	}

	// ── AJAX: upload PNG and set post meta ────────────────────────────────────

	public function ajax_set_layer_image(): void {
		$post_id   = (int) ( $_POST['post_id']   ?? 0 );
		$field_key = sanitize_key( $_POST['field_key'] ?? '' );
		$filename  = sanitize_file_name( $_POST['filename']  ?? 'layer-image.png' );
		$data      = $_POST['data'] ?? ''; // phpcs:ignore

		check_ajax_referer( 'pzl_metabox_set_layer_image_' . $post_id, 'meta_nonce' );

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( 'Forbidden' );
		}
		if ( ! $field_key || ! $post_id ) {
			wp_send_json_error( 'Missing parameters' );
		}

		// Strip data-URI header
		if ( strpos( $data, 'base64,' ) !== false ) {
			[ , $data ] = explode( 'base64,', $data );
		}
		$raw = base64_decode( $data ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
		if ( ! $raw ) { wp_send_json_error( 'Bad image data' ); }

		if ( ! str_ends_with( $filename, '.png' ) ) {
			$filename = pathinfo( $filename, PATHINFO_FILENAME ) . '.png';
		}

		$tmp = wp_tempnam( $filename );
		file_put_contents( $tmp, $raw ); // phpcs:ignore WordPress.WP.AlternativeFunctions

		$att_id = media_handle_sideload(
			[
				'name'     => $filename,
				'type'     => 'image/png',
				'tmp_name' => $tmp,
				'error'    => 0,
				'size'     => strlen( $raw ),
			],
			$post_id,
			'',
			[ 'post_title' => pathinfo( $filename, PATHINFO_FILENAME ) ]
		);

		@unlink( $tmp ); // phpcs:ignore

		if ( is_wp_error( $att_id ) ) {
			wp_send_json_error( $att_id->get_error_message() );
		}

		$url = (string) wp_get_attachment_url( $att_id );

		// Write to post meta — store attachment ID (integer) so SCF/ACF handles it
		// regardless of return format. Also update_field if SCF/ACF is active.
		update_post_meta( $post_id, $field_key, $att_id );
		if ( function_exists( 'update_field' ) ) {
			update_field( $field_key, $att_id, $post_id );
		}

		wp_send_json_success( [ 'id' => $att_id, 'url' => $url ] );
	}

	// ── Styles + script (output once per meta box instance) ──────────────────

	private static bool $assets_printed = false;

	private function render_meta_box_assets( string $slug ): void {
		// Styles printed once, script printed per instance (unique IDs per slug)
		if ( ! self::$assets_printed ) {
			self::$assets_printed = true;
			$this->render_mb_styles();
		}
		$this->render_mb_script( $slug );
	}

	private function render_mb_styles(): void { ?>
		<style>
		/* ── Meta box wrapper ──────────────────────────────────────── */
		.pzlmb-wrap { font-size: 13px; }

		/* ── Prompt state ─────────────────────────────────────────── */
		.pzlmb-current { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
		.pzlmb-current-img {
			width: 56px; height: 56px; object-fit: cover;
			border-radius: 6px; border: 1px solid #e0e3e7;
			background: repeating-conic-gradient(#ccc 0% 25%, #fff 0% 50%) 0 0 / 10px 10px;
			flex-shrink: 0;
		}
		.pzlmb-current-label { font-size: 12px; color: #646970; }
		.pzlmb-empty { display: flex; align-items: center; gap: 8px; margin-bottom: 10px; color: #aaa; }
		.pzlmb-empty-icon { font-size: 22px !important; width: 22px !important; height: 22px !important; }
		.pzlmb-empty-label { font-size: 12px; }
		.pzlmb-open-btn {
			width: 100%;
			display: flex !important; align-items: center; justify-content: center; gap: 5px;
			font-size: 12px !important;
		}
		.pzlmb-open-btn .dashicons { font-size: 13px !important; width: 13px !important; height: 13px !important; }

		/* ── Editor state ─────────────────────────────────────────── */
		.pzlmb-source-row { display: flex; flex-direction: column; gap: 6px; margin-bottom: 8px; }
		.pzlmb-drop-zone {
			border: 2px dashed #c3c4c7; border-radius: 6px;
			padding: 10px; text-align: center; cursor: pointer;
			font-size: 12px; color: #646970;
			display: flex; align-items: center; justify-content: center; gap: 6px;
			transition: border-color .15s, background .15s, color .15s;
		}
		.pzlmb-drop-zone:hover,
		.pzlmb-drop-zone--over { border-color: #ff6b35; background: #fff5f0; color: #ff6b35; }
		.pzlmb-drop-zone .dashicons { font-size: 14px !important; width: 14px !important; height: 14px !important; }
		.pzlmb-media-btn { width: 100%; display: flex !important; align-items: center; justify-content: center; gap: 5px; font-size: 12px !important; }
		.pzlmb-media-btn .dashicons { font-size: 13px !important; width: 13px !important; height: 13px !important; }

		/* ── Canvas stage ─────────────────────────────────────────── */
		.pzlmb-stage {
			position: relative; background: #1a1a2e; border-radius: 8px;
			min-height: 130px; margin-bottom: 8px; overflow: hidden;
			display: flex; align-items: center; justify-content: center;
		}
		.pzlmb-canvas-empty { text-align: center; color: #555; font-size: 12px; padding: 16px; }
		.pzlmb-canvas-empty .dashicons { font-size: 28px !important; width: 28px !important; height: 28px !important; display: block; margin: 0 auto 6px; }
		.pzlmb-canvas-empty p { margin: 4px 0 0; }
		.pzlmb-canvas { display: block; max-width: 100%; max-height: 180px; }

		/* ── Toolbar ──────────────────────────────────────────────── */
		.pzlmb-toolbar {
			display: flex; align-items: center; gap: 4px;
			margin-bottom: 8px; flex-wrap: wrap;
		}
		.pzlmb-tbtn {
			background: #f6f7f7; border: 1px solid #c3c4c7; border-radius: 4px;
			padding: 3px 7px; font-size: 13px; cursor: pointer; line-height: 1.4;
			transition: background .12s, border-color .12s;
		}
		.pzlmb-tbtn:hover { background: #fff5f0; border-color: #ff6b35; color: #ff6b35; }
		.pzlmb-toolbar-sep { flex: 1; }
		.pzlmb-tbtn-label { font-size: 11px; color: #646970; display: flex; align-items: center; gap: 3px; cursor: pointer; }

		/* ── Adjustments ──────────────────────────────────────────── */
		.pzlmb-adj { margin-bottom: 10px; }
		.pzlmb-adj-row {
			display: grid; grid-template-columns: 46px 1fr 28px;
			align-items: center; gap: 5px; margin-bottom: 4px;
		}
		.pzlmb-adj-label { font-size: 11px; color: #646970; white-space: nowrap; }
		.pzlmb-slider { width: 100%; accent-color: #ff6b35; cursor: pointer; }
		.pzlmb-adj-val { font-size: 11px; color: #1d2023; font-weight: 600; text-align: right; }
		.pzlmb-reset-adj { font-size: 11px; color: #646970; padding: 0; cursor: pointer; }
		.pzlmb-reset-adj:hover { color: #ff6b35; }

		/* ── Actions ──────────────────────────────────────────────── */
		.pzlmb-actions { display: flex; flex-direction: column; gap: 5px; }
		.pzlmb-set-btn {
			width: 100%; display: flex !important; align-items: center;
			justify-content: center; gap: 5px; font-size: 12px !important;
		}
		.pzlmb-set-btn .dashicons { font-size: 13px !important; width: 13px !important; height: 13px !important; }
		.pzlmb-cancel-btn { width: 100%; font-size: 12px !important; }
		.pzlmb-status { font-size: 11px; margin: 5px 0 0; min-height: 16px; }
		.pzlmb-status--ok  { color: #00a32a; }
		.pzlmb-status--err { color: #d63638; }
		</style>
	<?php }

	private function render_mb_script( string $slug ): void {
		$wrap_id   = 'pzlmb-wrap-'        . esc_js( $slug );
		$prompt_id = 'pzlmb-prompt-'      . esc_js( $slug );
		$editor_id = 'pzlmb-editor-'      . esc_js( $slug );
		$open_id   = 'pzlmb-open-btn-'    . esc_js( $slug );
		$stage_id  = 'pzlmb-stage-'       . esc_js( $slug );
		$empty_id  = 'pzlmb-canvas-empty-'. esc_js( $slug );
		$status_id = 'pzlmb-status-'      . esc_js( $slug );
		$curr_img  = 'pzlmb-current-img-' . esc_js( $slug );
		?>
		<script>
		(function(){
		'use strict';
		var $wrap    = document.getElementById(<?php echo wp_json_encode( $wrap_id ); ?>);
		if(!$wrap) return;

		var postId    = $wrap.dataset.postId;
		var fieldKey  = $wrap.dataset.fieldKey;
		var ajaxUrl   = $wrap.dataset.ajax;
		var nonce     = $wrap.dataset.nonce;
		var metaNonce = $wrap.dataset.metaNonce;
		var aspectStr = $wrap.dataset.aspect || '4/3';
		var aspectParts = aspectStr.split('/');
		var ASP_W = parseFloat(aspectParts[0])||4;
		var ASP_H = parseFloat(aspectParts[1])||3;

		var $prompt  = document.getElementById(<?php echo wp_json_encode( $prompt_id ); ?>);
		var $editor  = document.getElementById(<?php echo wp_json_encode( $editor_id ); ?>);
		var $openBtn = document.getElementById(<?php echo wp_json_encode( $open_id ); ?>);
		var $stage   = document.getElementById(<?php echo wp_json_encode( $stage_id ); ?>);
		var $empty   = document.getElementById(<?php echo wp_json_encode( $empty_id ); ?>);
		var $status  = document.getElementById(<?php echo wp_json_encode( $status_id ); ?>);
		var $currImg = document.getElementById(<?php echo wp_json_encode( $curr_img ); ?>);

		var $drop     = $wrap.querySelector('.pzlmb-drop-zone');
		var $fileIn   = $wrap.querySelector('.pzlmb-file-input');
		var $mediaBtn = $wrap.querySelector('.pzlmb-media-btn');
		var $canvas   = $wrap.querySelector('.pzlmb-canvas');
		var $guide    = $wrap.querySelector('.pzlmb-guide-svg');
		var $setBtn   = $wrap.querySelector('.pzlmb-set-btn');
		var $cancelBtn= $wrap.querySelector('.pzlmb-cancel-btn');
		var $resetAdj = $wrap.querySelector('.pzlmb-reset-adj');
		var $showGuide= $wrap.querySelector('.pzlmb-show-guide');
		var ctx       = $canvas ? $canvas.getContext('2d') : null;

		// ── State ────────────────────────────────────────────────────
		var st = {
			imgEl: null, rotation: 0, flipH: false, flipV: false,
			brightness: 0, contrast: 0, saturation: 0, opacity: 100,
			cropX: 0.08, cropY: 0.08, cropW: 0.84, cropH: 0.84,
			showGuide: true,
		};

		// Adjust crop to aspect ratio
		function resetCrop(){
			var asp = ASP_W / ASP_H;
			var maxW = 0.88, maxH = 0.88;
			var w = maxW, h = maxW / asp;
			if(h > maxH){ h = maxH; w = maxH * asp; }
			st.cropW = Math.min(w,1); st.cropH = Math.min(h,1);
			st.cropX = (1-st.cropW)/2; st.cropY = (1-st.cropH)/2;
		}
		resetCrop();

		// ── Open / close ─────────────────────────────────────────────
		if($openBtn){
			$openBtn.addEventListener('click', function(){
				if($prompt) $prompt.style.display = 'none';
				if($editor) $editor.style.display = 'block';
			});
		}
		if($cancelBtn){
			$cancelBtn.addEventListener('click', function(){
				if($editor) $editor.style.display = 'none';
				if($prompt) $prompt.style.display = 'block';
			});
		}

		// ── Image loading ────────────────────────────────────────────
		function loadSrc(src){
			var img = new Image();
			img.onload = function(){
				st.imgEl = img;
				st.rotation = 0; st.flipH = false; st.flipV = false;
				resetCrop();
				showCanvas();
				render();
				if($setBtn) $setBtn.disabled = false;
			};
			img.src = src;
		}

		function showCanvas(){
			if($empty)  $empty.style.display  = 'none';
			if($canvas) $canvas.style.display = 'block';
			if($guide)  $guide.style.display  = 'block';
		}

		// ── Drop zone ────────────────────────────────────────────────
		if($drop){
			$drop.addEventListener('click', function(){ $fileIn && $fileIn.click(); });
			$drop.addEventListener('keydown', function(e){ if(e.key==='Enter'||e.key===' '){ e.preventDefault(); $fileIn && $fileIn.click(); }});
			$drop.addEventListener('dragover', function(e){ e.preventDefault(); $drop.classList.add('pzlmb-drop-zone--over'); });
			$drop.addEventListener('dragleave', function(){ $drop.classList.remove('pzlmb-drop-zone--over'); });
			$drop.addEventListener('drop', function(e){
				e.preventDefault(); $drop.classList.remove('pzlmb-drop-zone--over');
				var f = e.dataTransfer.files[0];
				if(f && f.type.startsWith('image/')){ readFile(f); }
			});
		}
		if($fileIn){
			$fileIn.addEventListener('change', function(){ if($fileIn.files[0]) readFile($fileIn.files[0]); });
		}
		function readFile(f){
			var r = new FileReader();
			r.onload = function(e){ loadSrc(e.target.result); };
			r.readAsDataURL(f);
		}

		// ── Media library ────────────────────────────────────────────
		if($mediaBtn){
			$mediaBtn.addEventListener('click', function(){
				if(typeof wp === 'undefined' || !wp.media) return;
				var frame = wp.media({ title:'Choose Image', button:{text:'Use Image'}, multiple:false, library:{type:'image'} });
				frame.on('select', function(){
					var att = frame.state().get('selection').first().toJSON();
					loadSrc(att.url);
				});
				frame.open();
			});
		}

		// ── Render ───────────────────────────────────────────────────
		function getRotW(){ var img=st.imgEl; if(!img) return 1; return (st.rotation%180===0)?img.naturalWidth:img.naturalHeight; }
		function getRotH(){ var img=st.imgEl; if(!img) return 1; return (st.rotation%180===0)?img.naturalHeight:img.naturalWidth; }

		function render(){
			if(!st.imgEl||!ctx) return;
			var dw = getRotW(), dh = getRotH();
			$canvas.width = dw; $canvas.height = dh;
			ctx.save();
			ctx.clearRect(0,0,dw,dh);
			ctx.translate(dw/2,dh/2);
			if(st.flipH) ctx.scale(-1,1);
			if(st.flipV) ctx.scale(1,-1);
			ctx.rotate(st.rotation*Math.PI/180);
			ctx.drawImage(st.imgEl, -st.imgEl.naturalWidth/2, -st.imgEl.naturalHeight/2);
			ctx.restore();
			var f = '';
			if(st.brightness!==0) f+='brightness('+(1+st.brightness/100)+') ';
			if(st.contrast  !==0) f+='contrast('  +(1+st.contrast  /100)+') ';
			if(st.saturation!==0) f+='saturate('  +(1+st.saturation/100)+') ';
			$canvas.style.filter  = f.trim()||'none';
			$canvas.style.opacity = st.opacity/100;
			drawGuide();
		}

		// ── Guide ────────────────────────────────────────────────────
		function drawGuide(){
			if(!$guide||!$canvas) return;
			var cw = $canvas.offsetWidth||$canvas.width||1;
			var ch = $canvas.offsetHeight||$canvas.height||1;
			$guide.setAttribute('viewBox','0 0 '+cw+' '+ch);
			$guide.setAttribute('width', cw); $guide.setAttribute('height', ch);
			$guide.innerHTML = '';
			if(!st.imgEl||!st.showGuide) return;
			var cx=st.cropX*cw, cy=st.cropY*ch, crw=st.cropW*cw, crh=st.cropH*ch;
			// Darken outside crop
			var mask = mkSVG('path');
			mask.setAttribute('fill','rgba(0,0,0,0.45)'); mask.setAttribute('fill-rule','evenodd');
			mask.setAttribute('d','M 0 0 L '+cw+' 0 L '+cw+' '+ch+' L 0 '+ch+' Z M '+cx+' '+cy+' L '+(cx+crw)+' '+cy+' L '+(cx+crw)+' '+(cy+crh)+' L '+cx+' '+(cy+crh)+' Z');
			$guide.appendChild(mask);
			// Crop border
			var rect = mkSVG('rect');
			rect.setAttribute('x',cx); rect.setAttribute('y',cy); rect.setAttribute('width',crw); rect.setAttribute('height',crh);
			rect.setAttribute('fill','none'); rect.setAttribute('stroke','#ff6b35'); rect.setAttribute('stroke-width','1.5');
			$guide.appendChild(rect);
			// Pizza ellipse hint
			var pr = Math.min(crw,crh)*0.43;
			var ellipse = mkSVG('ellipse');
			ellipse.setAttribute('cx',cx+crw/2); ellipse.setAttribute('cy',cy+crh/2);
			ellipse.setAttribute('rx',pr*1.1); ellipse.setAttribute('ry',pr);
			ellipse.setAttribute('fill','none'); ellipse.setAttribute('stroke','rgba(255,200,0,0.5)');
			ellipse.setAttribute('stroke-width','1'); ellipse.setAttribute('stroke-dasharray','5 3');
			$guide.appendChild(ellipse);
			// Thirds
			for(var i=1;i<3;i++){
				var lv=mkSVG('line'); lv.setAttribute('x1',cx+crw*i/3); lv.setAttribute('y1',cy); lv.setAttribute('x2',cx+crw*i/3); lv.setAttribute('y2',cy+crh); lv.setAttribute('stroke','rgba(255,255,255,0.2)'); lv.setAttribute('stroke-width','1'); $guide.appendChild(lv);
				var lh=mkSVG('line'); lh.setAttribute('x1',cx); lh.setAttribute('y1',cy+crh*i/3); lh.setAttribute('x2',cx+crw); lh.setAttribute('y2',cy+crh*i/3); lh.setAttribute('stroke','rgba(255,255,255,0.2)'); lh.setAttribute('stroke-width','1'); $guide.appendChild(lh);
			}
		}
		function mkSVG(tag){ return document.createElementNS('http://www.w3.org/2000/svg',tag); }

		// ── Crop drag ────────────────────────────────────────────────
		var drag = null;
		if($guide){
			$guide.style.pointerEvents = 'auto';
			$guide.addEventListener('mousedown',function(e){
				if(!st.imgEl) return;
				var cw=$canvas.offsetWidth||1, ch=$canvas.offsetHeight||1;
				var r=$guide.getBoundingClientRect();
				var nx=(e.clientX-r.left)/cw, ny=(e.clientY-r.top)/ch;
				drag={startNx:nx,startNy:ny,origX:st.cropX,origY:st.cropY,origW:st.cropW,origH:st.cropH};
				e.preventDefault();
			});
		}
		document.addEventListener('mousemove',function(e){
			if(!drag) return;
			var cw=$canvas.offsetWidth||1, ch=$canvas.offsetHeight||1;
			var r=$guide.getBoundingClientRect();
			var nx=(e.clientX-r.left)/cw, ny=(e.clientY-r.top)/ch;
			st.cropX=clamp(drag.origX+(nx-drag.startNx),0,1-st.cropW);
			st.cropY=clamp(drag.origY+(ny-drag.startNy),0,1-st.cropH);
			drawGuide();
		});
		document.addEventListener('mouseup',function(){ drag=null; });

		// ── Mini toolbar ─────────────────────────────────────────────
		function tbtn(cls, fn){ var el=$wrap.querySelector('.'+cls); if(el) el.addEventListener('click',fn); }
		tbtn('pzlmb-tbtn--rotate-ccw', function(){ st.rotation=(st.rotation-90+360)%360; render(); });
		tbtn('pzlmb-tbtn--rotate-cw',  function(){ st.rotation=(st.rotation+90)%360;     render(); });
		tbtn('pzlmb-tbtn--flip-h',     function(){ st.flipH=!st.flipH; render(); });
		tbtn('pzlmb-tbtn--flip-v',     function(){ st.flipV=!st.flipV; render(); });
		if($showGuide){ $showGuide.addEventListener('change',function(){ st.showGuide=this.checked; drawGuide(); }); }

		// ── Adjustment sliders ───────────────────────────────────────
		var adjKeys = ['brightness','contrast','saturation','opacity'];
		adjKeys.forEach(function(key){
			var el = $wrap.querySelector('.pzlmb-slider--'+key);
			var valEl = el ? el.parentNode.querySelector('.pzlmb-adj-val') : null;
			if(el){ el.addEventListener('input',function(){ st[key]=parseFloat(this.value); if(valEl) valEl.textContent=this.value; render(); }); }
		});
		if($resetAdj){
			$resetAdj.addEventListener('click',function(){
				var defs={brightness:0,contrast:0,saturation:0,opacity:100};
				Object.assign(st,defs);
				adjKeys.forEach(function(key){
					var el=$wrap.querySelector('.pzlmb-slider--'+key);
					var valEl=el?el.parentNode.querySelector('.pzlmb-adj-val'):null;
					if(el) el.value=defs[key];
					if(valEl) valEl.textContent=defs[key];
				});
				render();
			});
		}

		// ── Build output canvas and upload ───────────────────────────
		if($setBtn){
			$setBtn.addEventListener('click',function(){
				var oc = buildOutput();
				if(!oc){ showStatus('No image loaded.','err'); return; }
				$setBtn.disabled = true;
				showStatus('Uploading…','');
				oc.toBlob(function(blob){
					var fr = new FileReader();
					fr.onload = function(ev){
						var fd = new FormData();
						fd.append('action',     'pizzalayer_metabox_set_layer_image');
						fd.append('nonce',      nonce);
						fd.append('meta_nonce', metaNonce);
						fd.append('post_id',    postId);
						fd.append('field_key',  fieldKey);
						fd.append('filename',   fieldKey+'-'+postId+'.png');
						fd.append('data',       ev.target.result);
						fetch(ajaxUrl,{method:'POST',body:fd})
							.then(function(r){ return r.json(); })
							.then(function(d){
								if(d.success){
									showStatus('✓ Layer image set!','ok');
									// Update the prompt thumbnail
									if($currImg){
										$currImg.src = d.data.url;
									} else {
										// Build current preview if it didn't exist
										var div = $prompt ? $prompt.querySelector('.pzlmb-empty') : null;
										if(div){
											div.outerHTML = '<div class="pzlmb-current"><img src="'+d.data.url+'" alt="Current layer image" class="pzlmb-current-img" id="pzlmb-current-img-<?php echo esc_js( $slug ); ?>"><span class="pzlmb-current-label"><?php esc_html_e( 'Current layer image', 'pizzalayer' ); ?></span></div>';
										}
									}
									// Update open button text
									if(document.getElementById(<?php echo wp_json_encode( $open_id ); ?>)){
										document.getElementById(<?php echo wp_json_encode( $open_id ); ?>).innerHTML = '<span class="dashicons dashicons-edit"></span> Edit / Replace Layer Image';
									}
									setTimeout(function(){
										if($editor) $editor.style.display='none';
										if($prompt) $prompt.style.display='block';
									}, 1200);
								} else {
									showStatus('Error: '+(d.data||'unknown'),'err');
								}
							})
							.catch(function(){ showStatus('Upload failed.','err'); })
							.finally(function(){ $setBtn.disabled=false; });
					};
					fr.readAsDataURL(blob);
				},'image/png');
			});
		}

		function buildOutput(){
			if(!st.imgEl) return null;
			var asp = ASP_W/ASP_H;
			var iw=getRotW(), ih=getRotH();
			var srcX=st.cropX*iw, srcY=st.cropY*ih, srcW=st.cropW*iw, srcH=st.cropH*ih;
			var outW=1024, outH=Math.round(1024/asp);
			if(asp<1){ outH=1024; outW=Math.round(1024*asp); }
			var oc=document.createElement('canvas'); oc.width=outW; oc.height=outH;
			var octx=oc.getContext('2d');
			var f='';
			if(st.brightness!==0) f+='brightness('+(1+st.brightness/100)+') ';
			if(st.contrast  !==0) f+='contrast('  +(1+st.contrast  /100)+') ';
			if(st.saturation!==0) f+='saturate('  +(1+st.saturation/100)+') ';
			octx.filter=f.trim()||'none';
			octx.globalAlpha=st.opacity/100;
			// Draw rotated/flipped to temp canvas
			var tmp=document.createElement('canvas'); tmp.width=iw; tmp.height=ih;
			var tc=tmp.getContext('2d');
			tc.translate(iw/2,ih/2);
			if(st.flipH) tc.scale(-1,1);
			if(st.flipV) tc.scale(1,-1);
			tc.rotate(st.rotation*Math.PI/180);
			tc.drawImage(st.imgEl,-st.imgEl.naturalWidth/2,-st.imgEl.naturalHeight/2);
			octx.drawImage(tmp,srcX,srcY,srcW,srcH,0,0,outW,outH);
			return oc;
		}

		function showStatus(msg, cls){
			if(!$status) return;
			$status.textContent = msg;
			$status.className = 'pzlmb-status' + (cls ? ' pzlmb-status--'+cls : '');
		}
		function clamp(v,a,b){ return Math.max(a,Math.min(b,v)); }

		})();
		</script>
		<?php
	}
}
