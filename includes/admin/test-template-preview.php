<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Plugin/Theme Utility: PizzaLayer Step-by-Step Customizer (App Test)
 *
 * Shortcode: [pizzalayer-template-app-test]
 *
 * Summary:
 * - Renders a 5-stage pizza customizer (Crust & Size, Sauce, Cheese, Toppings, Checkout).
 * - Populates choices from PizzaLayer CPTs: crusts, sauces, cheeses, toppings.
 * - Includes a slide-up preview "tab" docked at the bottom (open by default), with an overlapping circular handle.
 * - The stage content area auto-fits the remaining viewport height, recalculating on resize and on preview open/close.
 * - Exclusive choices for Crust, Sauce, Cheese; multiple Toppings with coverage options: Whole, Left, Right, and (if desired) Quarters.
 * - Checkout tab shows selected layers as pill tags with “x” remove buttons (grouped by layer type).
 *
 * Usage:
 * - Add this file to your plugin or theme (e.g., include from functions.php or your plugin’s includes/ directory).
 * - Use the shortcode [pizzalayer-template-app-test] on any page/post.
 * - Or call pizzalayer_template_app_test() directly to get the HTML string for embedding elsewhere.
 *
 * Developer Notes:
 * - Sizes are pulled from the 'pizzalayer_sizes' option if present, else a sensible default. Filter via 'pizzalayer_app_sizes'.
 * - Dashicons are enqueued for the minimalist top navigation bar icons.
 * - CPT slugs assumed:
 *     - Crusts:    pizzalayer_crusts
 *     - Sauces:    pizzalayer_sauces
 *     - Cheeses:   pizzalayer_cheeses
 *     - Toppings:  pizzalayer_toppings
 * - This is framework/scaffolding code; hook your pricing/preview-image logic into the noted JS “TODO” sections.
 *
 * @package  PizzaLayer
 * @author   You
 * @version  1.0.0
 */

if ( ! function_exists( 'pizzalayer_template_app_test' ) ) {

	/**
	 * Render the PizzaLayer App Test customizer UI.
	 *
	 * @param array $atts Shortcode attributes (currently unused).
	 * @return string HTML markup for the customizer UI.
	 */
	function pizzalayer_template_app_test( $atts = array() ) {

		// Ensure Dashicons are available on the front end.
		if ( ! wp_style_is( 'dashicons', 'enqueued' ) ) {
			wp_enqueue_style( 'dashicons' );
		}

		// Unique instance ID to scope CSS/JS when multiple shortcodes are on a page.
		$uid = uniqid( 'pztp_', false );

		// Fetch data from CPTs.
		$crusts = get_posts( array(
			'post_type'      => 'pizzalayer_crusts',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'orderby'        => 'title',
			'order'          => 'ASC',
		) );

		$sauces = get_posts( array(
			'post_type'      => 'pizzalayer_sauces',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'orderby'        => 'title',
			'order'          => 'ASC',
		) );

		$cheeses = get_posts( array(
			'post_type'      => 'pizzalayer_cheeses',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'orderby'        => 'title',
			'order'          => 'ASC',
		) );

		$toppings = get_posts( array(
			'post_type'      => 'pizzalayer_toppings',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'orderby'        => 'title',
			'order'          => 'ASC',
		) );

		// Sizes (Option or fallback). Developers can filter this.
		$default_sizes = array(
			array( 'slug' => 'sm-10', 'label' => 'Small (10")' ),
			array( 'slug' => 'md-12', 'label' => 'Medium (12")' ),
			array( 'slug' => 'lg-14', 'label' => 'Large (14")' ),
			array( 'slug' => 'xl-16', 'label' => 'X‑Large (16")' ),
		);
		$option_sizes = get_option( 'pizzalayer_sizes' );
		$sizes        = is_array( $option_sizes ) && ! empty( $option_sizes ) ? $option_sizes : $default_sizes;
		$sizes        = apply_filters( 'pizzalayer_app_sizes', $sizes ); // Allow themes/plugins to modify.

		ob_start();
		?>
		<style>
			/* ============ PizzaLayer App Test Styles (scoped by instance) ============ */
			#<?php echo esc_attr( $uid ); ?> { --pztp-radius: 16px; --pztp-gap: 14px; --pztp-pad: 16px; --pztp-bar: 54px; --pztp-accent: #111; --pztp-muted:#f4f4f5; --pztp-pill:#eaeaec; --pztp-border:#e3e3e7; font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; }
			#<?php echo esc_attr( $uid ); ?> * { box-sizing: border-box; }
			#<?php echo esc_attr( $uid ); ?> .pztp-app { position: relative; min-height: 100vh; background: #fff; color:#111; }

			/* Top Icon Bar */
			#<?php echo esc_attr( $uid ); ?> .pztp-topbar {
				position: sticky; top: 0; z-index: 20;
				height: var(--pztp-bar);
				background: #000; color: #fff;
				display: grid; grid-template-columns: repeat(5, 1fr);
				align-items: center; border-bottom: 1px solid #111;
			}
			#<?php echo esc_attr( $uid ); ?> .pztp-topbtn {
				appearance: none; background: transparent; color:#fff; border:0; width:100%; height:100%;
				display:flex; align-items:center; justify-content:center; gap:8px; cursor:pointer;
				font-size: 13px; letter-spacing: .2px;
			}
			#<?php echo esc_attr( $uid ); ?> .pztp-topbtn .dashicons { font-size: 18px; width: 18px; height: 18px; }
			#<?php echo esc_attr( $uid ); ?> .pztp-topbtn.active { background: rgba(255,255,255,.10); }
			#<?php echo esc_attr( $uid ); ?> .pztp-stagewrap { position: relative; padding: var(--pztp-pad); }
			#<?php echo esc_attr( $uid ); ?> .pztp-stages { position: relative; }
			#<?php echo esc_attr( $uid ); ?> .pztp-stage { display:none; }
			#<?php echo esc_attr( $uid ); ?> .pztp-stage.active { display:block; }

			/* Responsive grid for choice lists */
			#<?php echo esc_attr( $uid ); ?> .pztp-choices {
				display: grid;
				grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
				gap: var(--pztp-gap);
				overflow: auto;
				padding-bottom: calc(var(--pztp-pad) * 2.5);
			}
			#<?php echo esc_attr( $uid ); ?> .pztp-card {
				border: 1px solid var(--pztp-border);
				border-radius: var(--pztp-radius);
				background: #fff;
				padding: var(--pztp-pad);
				display:flex; flex-direction:column; gap:10px;
				box-shadow: 0 1px 0 rgba(0,0,0,.04);
			}
			#<?php echo esc_attr( $uid ); ?> .pztp-card h4 { margin:0; font-size: 16px; }
			#<?php echo esc_attr( $uid ); ?> .pztp-radio { display:flex; align-items:center; gap:10px; }
			#<?php echo esc_attr( $uid ); ?> .pztp-radio input { transform: scale(1.2); }
			#<?php echo esc_attr( $uid ); ?> .pztp-pillbar { display:flex; gap: 10px; flex-wrap: wrap; }
			#<?php echo esc_attr( $uid ); ?> .pztp-pill {
				display:inline-flex; align-items:center; gap:8px;
				background: var(--pztp-pill); padding: 6px 10px; border-radius: 999px; border:1px solid var(--pztp-border);
				font-size: 13px;
			}
			#<?php echo esc_attr( $uid ); ?> .pztp-pill button { border:0; background:transparent; cursor:pointer; line-height: 1; }
			#<?php echo esc_attr( $uid ); ?> .pztp-meta { font-size:12px; color:#444; }

			/* Topping coverage buttons */
			#<?php echo esc_attr( $uid ); ?> .pztp-coverage { display:flex; gap:8px; flex-wrap:wrap; }
			#<?php echo esc_attr( $uid ); ?> .pztp-covbtn {
				display:inline-flex; align-items:center; gap:6px; padding:8px 10px; border-radius: 10px; border:1px solid var(--pztp-border);
				cursor:pointer; background:#fff; font-size: 12px;
			}
			#<?php echo esc_attr( $uid ); ?> .pztp-covbtn.active { outline: 2px solid #111; }
			/* Simple inline "pizza" coverage icons made of 4 squares */
			#<?php echo esc_attr( $uid ); ?> .pztp-ico { width:16px; height:16px; display:grid; grid-template-columns: repeat(2, 8px); grid-template-rows: repeat(2, 8px); gap:0; border:1px solid #bbb; }
			#<?php echo esc_attr( $uid ); ?> .pztp-ico span { width:8px; height:8px; background:#eee; }
			#<?php echo esc_attr( $uid ); ?> .ico-whole span { background:#111; }
			#<?php echo esc_attr( $uid ); ?> .ico-left span:nth-child(1), #<?php echo esc_attr( $uid ); ?> .ico-left span:nth-child(3) { background:#111; }
			#<?php echo esc_attr( $uid ); ?> .ico-right span:nth-child(2), #<?php echo esc_attr( $uid ); ?> .ico-right span:nth-child(4) { background:#111; }
			#<?php echo esc_attr( $uid ); ?> .ico-q1 span:nth-child(1) { background:#111; }
			#<?php echo esc_attr( $uid ); ?> .ico-q2 span:nth-child(2) { background:#111; }
			#<?php echo esc_attr( $uid ); ?> .ico-q3 span:nth-child(3) { background:#111; }
			#<?php echo esc_attr( $uid ); ?> .ico-q4 span:nth-child(4) { background:#111; }

			/* Bottom Preview Tab */
			#<?php echo esc_attr( $uid ); ?> .pztp-preview {
				position: fixed; left:0; right:0; bottom:0; z-index: 30;
				background:#fff; border-top-left-radius: var(--pztp-radius); border-top-right-radius: var(--pztp-radius);
				border-top: 1px solid var(--pztp-border);
				box-shadow: 0 -8px 24px rgba(0,0,0,.08);
				overflow: hidden;
			}
			#<?php echo esc_attr( $uid ); ?> .pztp-preview.open { height: 34vh; }
			#<?php echo esc_attr( $uid ); ?> .pztp-preview.closed { height: 48px; }

			/* Handle with overlapping circular button */
			#<?php echo esc_attr( $uid ); ?> .pztp-handle {
				position: absolute; top: -18px; left: 50%; transform: translateX(-50%);
				background: transparent; width: 36px; height: 36px; pointer-events: none;
			}
			#<?php echo esc_attr( $uid ); ?> .pztp-handle .pztp-handle-btn {
				position: absolute; inset:0; margin:auto; width:36px; height:36px; border-radius: 999px;
				background:#111; color:#fff; display:flex; align-items:center; justify-content:center;
				box-shadow: 0 4px 12px rgba(0,0,0,.25); pointer-events: all; border: 2px solid #fff; cursor:pointer;
			}
			#<?php echo esc_attr( $uid ); ?> .pztp-preview-head {
				padding: 10px 14px; display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid var(--pztp-border);
			}
			#<?php echo esc_attr( $uid ); ?> .pztp-preview-body { height: calc(100% - 50px); overflow:auto; padding: 12px 14px; }

			/* Checkout layout */
			#<?php echo esc_attr( $uid ); ?> .pztp-checkout { display:grid; gap: var(--pztp-gap); grid-template-columns: 1fr; }
			#<?php echo esc_attr( $uid ); ?> .pztp-checksec { border:1px solid var(--pztp-border); border-radius: var(--pztp-radius); padding: var(--pztp-pad); background:#fff; }
			#<?php echo esc_attr( $uid ); ?> .pztp-checksec h4 { margin:0 0 8px 0; }

			/* Utility */
			#<?php echo esc_attr( $uid ); ?> .muted { color:#666; }
			#<?php echo esc_attr( $uid ); ?> .pztp-hr { height:1px; background: var(--pztp-border); margin: 6px 0 0; }
			@media (min-width: 900px) {
				#<?php echo esc_attr( $uid ); ?> .pztp-checkout { grid-template-columns: repeat(2, 1fr); }
			}
		</style>

		<div id="<?php echo esc_attr( $uid ); ?>" class="pztp-app" data-instance="<?php echo esc_attr( $uid ); ?>">
			<!-- Top Icon Bar -->
			<nav class="pztp-topbar" role="tablist" aria-label="Pizza builder steps">
				<button class="pztp-topbtn active" data-stage="stage-crust" aria-selected="true" aria-controls="<?php echo esc_attr( $uid ); ?>-stage-crust">
					<span class="dashicons dashicons-pressthis" aria-hidden="true"></span><span class="sr">Crust & Size</span>
				</button>
				<button class="pztp-topbtn" data-stage="stage-sauce" aria-selected="false" aria-controls="<?php echo esc_attr( $uid ); ?>-stage-sauce">
					<span class="dashicons dashicons-admin-appearance" aria-hidden="true"></span><span class="sr">Sauce</span>
				</button>
				<button class="pztp-topbtn" data-stage="stage-cheese" aria-selected="false" aria-controls="<?php echo esc_attr( $uid ); ?>-stage-cheese">
					<span class="dashicons dashicons-smiley" aria-hidden="true"></span><span class="sr">Cheese</span>
				</button>
				<button class="pztp-topbtn" data-stage="stage-toppings" aria-selected="false" aria-controls="<?php echo esc_attr( $uid ); ?>-stage-toppings">
					<span class="dashicons dashicons-carrot" aria-hidden="true"></span><span class="sr">Toppings</span>
				</button>
				<button class="pztp-topbtn" data-stage="stage-checkout" aria-selected="false" aria-controls="<?php echo esc_attr( $uid ); ?>-stage-checkout">
					<span class="dashicons dashicons-cart" aria-hidden="true"></span><span class="sr">Checkout</span>
				</button>
			</nav>

			<div class="pztp-stagewrap">
				<div class="pztp-stages" id="<?php echo esc_attr( $uid ); ?>-stages">
					<!-- Stage: Crust & Size -->
					<section class="pztp-stage active" id="<?php echo esc_attr( $uid ); ?>-stage-crust" role="tabpanel" aria-labelledby="Crust & Size">
						<div class="pztp-choices" data-scrollable>
							<div class="pztp-card">
								<h4>Choose Size</h4>
								<div class="pztp-meta">Pick exactly one size</div>
								<div class="pztp-hr"></div>
								<div class="pztp-pillbar" id="<?php echo esc_attr( $uid ); ?>-sizebar">
									<?php foreach ( $sizes as $size ) :
										$slug  = isset( $size['slug'] ) ? sanitize_title( $size['slug'] ) : sanitize_title( $size['label'] );
										$label = isset( $size['label'] ) ? $size['label'] : $slug;
									?>
									<label class="pztp-radio">
										<input type="radio" name="<?php echo esc_attr( $uid ); ?>-size" value="<?php echo esc_attr( $slug ); ?>" />
										<span><?php echo esc_html( $label ); ?></span>
									</label>
									<?php endforeach; ?>
								</div>
							</div>
							<div class="pztp-card">
								<h4>Choose Crust</h4>
								<div class="pztp-meta">Pick 1 crust</div>
								<div class="pztp-hr"></div>
								<?php if ( ! empty( $crusts ) ) : ?>
									<?php foreach ( $crusts as $post ) : ?>
									<label class="pztp-radio">
										<input type="radio" name="<?php echo esc_attr( $uid ); ?>-crust" value="<?php echo esc_attr( $post->ID ); ?>" data-name="<?php echo esc_attr( get_the_title( $post ) ); ?>" />
										<span><?php echo esc_html( get_the_title( $post ) ); ?></span>
									</label>
									<?php endforeach; ?>
								<?php else : ?>
									<p class="muted">No crusts found.</p>
								<?php endif; ?>
							</div>
						</div>
					</section>

					<!-- Stage: Sauce -->
					<section class="pztp-stage" id="<?php echo esc_attr( $uid ); ?>-stage-sauce" role="tabpanel" aria-labelledby="Sauce">
						<div class="pztp-choices" data-scrollable>
							<div class="pztp-card">
								<h4>Choose Sauce</h4>
								<div class="pztp-meta">Pick 1 sauce</div>
								<div class="pztp-hr"></div>
								<?php if ( ! empty( $sauces ) ) : ?>
									<?php foreach ( $sauces as $post ) : ?>
									<label class="pztp-radio">
										<input type="radio" name="<?php echo esc_attr( $uid ); ?>-sauce" value="<?php echo esc_attr( $post->ID ); ?>" data-name="<?php echo esc_attr( get_the_title( $post ) ); ?>" />
										<span><?php echo esc_html( get_the_title( $post ) ); ?></span>
									</label>
									<?php endforeach; ?>
								<?php else : ?>
									<p class="muted">No sauces found.</p>
								<?php endif; ?>
							</div>
						</div>
					</section>

					<!-- Stage: Cheese -->
					<section class="pztp-stage" id="<?php echo esc_attr( $uid ); ?>-stage-cheese" role="tabpanel" aria-labelledby="Cheese">
						<div class="pztp-choices" data-scrollable>
							<div class="pztp-card">
								<h4>Choose Cheese</h4>
								<div class="pztp-meta">Pick 1 cheese</div>
								<div class="pztp-hr"></div>
								<?php if ( ! empty( $cheeses ) ) : ?>
									<?php foreach ( $cheeses as $post ) : ?>
									<label class="pztp-radio">
										<input type="radio" name="<?php echo esc_attr( $uid ); ?>-cheese" value="<?php echo esc_attr( $post->ID ); ?>" data-name="<?php echo esc_attr( get_the_title( $post ) ); ?>" />
										<span><?php echo esc_html( get_the_title( $post ) ); ?></span>
									</label>
									<?php endforeach; ?>
								<?php else : ?>
									<p class="muted">No cheeses found.</p>
								<?php endif; ?>
							</div>
						</div>
					</section>

					<!-- Stage: Toppings -->
					<section class="pztp-stage" id="<?php echo esc_attr( $uid ); ?>-stage-toppings" role="tabpanel" aria-labelledby="Toppings">
						<div class="pztp-choices" data-scrollable>
							<?php if ( ! empty( $toppings ) ) : ?>
								<?php foreach ( $toppings as $post ) : ?>
								<div class="pztp-card" data-topping-id="<?php echo esc_attr( $post->ID ); ?>" data-topping-name="<?php echo esc_attr( get_the_title( $post ) ); ?>">
									<h4><?php echo esc_html( get_the_title( $post ) ); ?></h4>
									<div class="pztp-meta">Add topping with coverage</div>
									<div class="pztp-hr"></div>
									<div class="pztp-coverage">
										<button type="button" class="pztp-covbtn" data-fraction="whole" title="Whole">
											<span class="pztp-ico ico-whole">
												<span></span><span></span><span></span><span></span>
											</span>
											<span>Whole</span>
										</button>
										<button type="button" class="pztp-covbtn" data-fraction="left" title="Left">
											<span class="pztp-ico ico-left">
												<span></span><span></span><span></span><span></span>
											</span>
											<span>Left</span>
										</button>
										<button type="button" class="pztp-covbtn" data-fraction="right" title="Right">
											<span class="pztp-ico ico-right">
												<span></span><span></span><span></span><span></span>
											</span>
											<span>Right</span>
										</button>
										<button type="button" class="pztp-covbtn" data-fraction="q1" title="Top-Left">
											<span class="pztp-ico ico-q1">
												<span></span><span></span><span></span><span></span>
											</span>
											<span>Q1</span>
										</button>
										<button type="button" class="pztp-covbtn" data-fraction="q2" title="Top-Right">
											<span class="pztp-ico ico-q2">
												<span></span><span></span><span></span><span></span>
											</span>
											<span>Q2</span>
										</button>
										<button type="button" class="pztp-covbtn" data-fraction="q3" title="Bottom-Left">
											<span class="pztp-ico ico-q3">
												<span></span><span></span><span></span><span></span>
											</span>
											<span>Q3</span>
										</button>
										<button type="button" class="pztp-covbtn" data-fraction="q4" title="Bottom-Right">
											<span class="pztp-ico ico-q4">
												<span></span><span></span><span></span><span></span>
											</span>
											<span>Q4</span>
										</button>
									</div>
								</div>
								<?php endforeach; ?>
							<?php else : ?>
								<div class="pztp-card">
									<p class="muted">No toppings found.</p>
								</div>
							<?php endif; ?>
						</div>
					</section>

					<!-- Stage: Checkout -->
					<section class="pztp-stage" id="<?php echo esc_attr( $uid ); ?>-stage-checkout" role="tabpanel" aria-labelledby="Checkout">
						<div class="pztp-checkout" data-scrollable>
							<div class="pztp-checksec">
								<h4>Crust & Size</h4>
								<div class="pztp-pillbar" id="<?php echo esc_attr( $uid ); ?>-chk-crustsize"></div>
							</div>
							<div class="pztp-checksec">
								<h4>Sauce</h4>
								<div class="pztp-pillbar" id="<?php echo esc_attr( $uid ); ?>-chk-sauce"></div>
							</div>
							<div class="pztp-checksec">
								<h4>Cheese</h4>
								<div class="pztp-pillbar" id="<?php echo esc_attr( $uid ); ?>-chk-cheese"></div>
							</div>
							<div class="pztp-checksec" style="grid-column: 1 / -1;">
								<h4>Toppings</h4>
								<div class="pztp-pillbar" id="<?php echo esc_attr( $uid ); ?>-chk-toppings"></div>
							</div>
						</div>
					</section>
				</div>
			</div>

			<!-- Bottom Slide-Up Preview -->
			<div class="pztp-preview open" id="<?php echo esc_attr( $uid ); ?>-preview" aria-expanded="true">
				<div class="pztp-handle">
					<button type="button" class="pztp-handle-btn" id="<?php echo esc_attr( $uid ); ?>-preview-toggle" aria-label="Toggle preview">
						<span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>
					</button>
				</div>
				<div class="pztp-preview-head">
					<strong>Preview</strong>
					<span class="muted" id="<?php echo esc_attr( $uid ); ?>-preview-meta">Open</span>
				</div>
				<div class="pztp-preview-body" id="<?php echo esc_attr( $uid ); ?>-preview-body">
					<!-- TODO: Replace with your live canvas/image stack -->
					<p class="muted">Your pizza preview will appear here (e.g., stacked layer images).</p>
				</div>
			</div>
		</div>

		<script>
			(function() {
				const root      = document.getElementById('<?php echo esc_js( $uid ); ?>');
				const topBtns   = root.querySelectorAll('.pztp-topbtn');
				const stages    = root.querySelectorAll('.pztp-stage');
				const preview   = document.getElementById('<?php echo esc_js( $uid ); ?>-preview');
				const toggleBtn = document.getElementById('<?php echo esc_js( $uid ); ?>-preview-toggle');
				const previewMeta = document.getElementById('<?php echo esc_js( $uid ); ?>-preview-meta');
				const stageWrap = root.querySelector('.pztp-stagewrap');

				// Scrollable panels that must fit remaining viewport height.
				const scrollables = root.querySelectorAll('[data-scrollable]');

				// State
				const state = {
					size: null,
					crust: null,  // { id, name }
					sauce: null,  // { id, name }
					cheese: null, // { id, name }
					toppings: []  // [{ id, name, fraction }]
				};

				// Helpers
				function setActiveStage(stageId) {
					stages.forEach(s => s.classList.remove('active'));
					root.querySelector('#' + stageId.replace('stage-', '<?php echo esc_js( $uid ); ?>-stage-')).classList.add('active');
					topBtns.forEach(b => {
						b.classList.toggle('active', b.dataset.stage === stageId);
						b.setAttribute('aria-selected', b.dataset.stage === stageId ? 'true' : 'false');
					});
					fitScrollableAreas();
				}

				function fitScrollableAreas() {
					const topBarH    = root.querySelector('.pztp-topbar').offsetHeight || 0;
					const previewH   = preview.classList.contains('open') ? preview.offsetHeight : preview.offsetHeight; // offsetHeight is fine
					const vh         = window.innerHeight;
					const pad        = 8; // small buffer
					const available  = Math.max(200, vh - topBarH - previewH - pad);

					scrollables.forEach(el => {
						el.style.maxHeight = available + 'px';
						el.style.height    = available + 'px';
					});
				}

				function upsertExclusive(layerKey, objOrNull) {
					state[layerKey] = objOrNull; // { id, name } or null
					updateCheckout();
					updatePreviewMeta();
				}

				function upsertSize(slugOrNull, label) {
					state.size = slugOrNull ? { slug: slugOrNull, label: label || slugOrNull } : null;
					updateCheckout();
					updatePreviewMeta();
				}

				function upsertTopping(tId, tName, fraction) {
					// For simplicity we keep one entry per topping; fraction replaces previous selection for same topping.
					const idx = state.toppings.findIndex(t => String(t.id) === String(tId));
					const entry = { id: tId, name: tName, fraction: fraction };

					if (idx >= 0) {
						state.toppings[idx] = entry;
					} else {
						state.toppings.push(entry);
					}
					updateCheckout();
					updatePreviewMeta();
				}

				function removeTopping(tId) {
					state.toppings = state.toppings.filter(t => String(t.id) !== String(tId));
					updateCheckout();
					updatePreviewMeta();
				}

				function pill(label, type, id, extra) {
					const span = document.createElement('span');
					span.className = 'pztp-pill';
					span.dataset.type = type;
					if (id !== undefined && id !== null) span.dataset.id = id;
					if (extra) span.dataset.extra = extra;

					const txt = document.createElement('span');
					txt.textContent = label;
					span.appendChild(txt);

					const btn = document.createElement('button');
					btn.type = 'button';
					btn.setAttribute('aria-label', 'Remove');
					btn.innerHTML = '&times;';
					btn.addEventListener('click', () => {
						// Remove handler varies by type
						if (type === 'size')        upsertSize(null);
						if (type === 'crust')       upsertExclusive('crust', null);
						if (type === 'sauce')       upsertExclusive('sauce', null);
						if (type === 'cheese')      upsertExclusive('cheese', null);
						if (type === 'topping')     removeTopping(id);
						// Also uncheck radios/visuals
						if (type !== 'topping') {
							const inputName = '<?php echo esc_js( $uid ); ?>-' + type;
							root.querySelectorAll('input[name="'+inputName+'"]').forEach(i => { i.checked = false; });
						}
					});
					span.appendChild(btn);

					return span;
				}

				function updateCheckout() {
					// Targets
					const elCrustSize = document.getElementById('<?php echo esc_js( $uid ); ?>-chk-crustsize');
					const elSauce     = document.getElementById('<?php echo esc_js( $uid ); ?>-chk-sauce');
					const elCheese    = document.getElementById('<?php echo esc_js( $uid ); ?>-chk-cheese');
					const elTops      = document.getElementById('<?php echo esc_js( $uid ); ?>-chk-toppings');

					[elCrustSize, elSauce, elCheese, elTops].forEach(el => { if (el) el.innerHTML = ''; });

					if (state.size)  elCrustSize.appendChild(pill(state.size.label || state.size.slug, 'size'));
					if (state.crust) elCrustSize.appendChild(pill(state.crust.name, 'crust', state.crust.id));

					if (state.sauce) elSauce.appendChild(pill(state.sauce.name, 'sauce', state.sauce.id));
					if (state.cheese) elCheese.appendChild(pill(state.cheese.name, 'cheese', state.cheese.id));

					state.toppings.forEach(t => {
						elTops.appendChild(pill(t.name + ' (' + t.fraction + ')', 'topping', t.id, t.fraction));
					});
				}

				function updatePreviewMeta() {
					const parts = [];
					if (state.size)  parts.push(state.size.label || state.size.slug);
					if (state.crust) parts.push(state.crust.name);
					if (state.sauce) parts.push(state.sauce.name);
					if (state.cheese) parts.push(state.cheese.name);
					if (state.toppings.length) parts.push(state.toppings.length + ' topping' + (state.toppings.length>1?'s':''));
					previewMeta.textContent = (preview.classList.contains('open') ? 'Open • ' : 'Closed • ') + (parts.join(' · ') || 'No selections yet');
					// TODO: Hook into your live preview renderer from here (images / canvas / etc.)
				}

				function setCovActive(card, fraction) {
					card.querySelectorAll('.pztp-covbtn').forEach(btn => {
						btn.classList.toggle('active', btn.dataset.fraction === fraction);
					});
				}

				// Event: top bar stage switching
				topBtns.forEach(btn => {
					btn.addEventListener('click', () => setActiveStage(btn.dataset.stage));
				});

				// Event: Preview open/close
				toggleBtn.addEventListener('click', () => {
					const isOpen = preview.classList.contains('open');
					preview.classList.toggle('open', !isOpen);
					preview.classList.toggle('closed', isOpen);
					toggleBtn.innerHTML = isOpen
						? '<span class="dashicons dashicons-arrow-up-alt2"></span>'
						: '<span class="dashicons dashicons-arrow-down-alt2"></span>';
					preview.setAttribute('aria-expanded', String(!isOpen));
					updatePreviewMeta();
					fitScrollableAreas();
				});

				// Event: Size selection (exclusive)
				root.querySelectorAll('input[name="<?php echo esc_js( $uid ); ?>-size"]').forEach(radio => {
					radio.addEventListener('change', () => {
						const lbl = radio.parentElement ? radio.parentElement.textContent.trim() : radio.value;
						upsertSize(radio.value, lbl);
					});
				});

				// Event: Crust/Sauce/Cheese exclusive selections
				['crust','sauce','cheese'].forEach(key => {
					root.querySelectorAll('input[name="<?php echo esc_js( $uid ); ?>-' + key + '"]').forEach(radio => {
						radio.addEventListener('change', () => {
							const name = radio.dataset.name || radio.value;
							upsertExclusive(key, { id: radio.value, name: name });
						});
					});
				});

				// Event: Topping coverage clicks
				root.querySelectorAll('[data-topping-id]').forEach(card => {
					card.querySelectorAll('.pztp-covbtn').forEach(btn => {
						btn.addEventListener('click', () => {
							const tId   = card.getAttribute('data-topping-id');
							const tName = card.getAttribute('data-topping-name');
							const frac  = btn.getAttribute('data-fraction');
							upsertTopping(tId, tName, frac);
							setCovActive(card, frac);
						});
					});
				});

				// Initial layout pass
				function onResize() { fitScrollableAreas(); }
				window.addEventListener('resize', onResize, { passive: true });
				preview.classList.add('open'); // open by default
				preview.classList.remove('closed');
				updatePreviewMeta();
				fitScrollableAreas();
			})();
		</script>
		<?php
		return ob_get_clean();
	}
}

/* Register Shortcode */
if ( ! shortcode_exists( 'pizzalayer-template-app-test' ) ) {
	add_shortcode( 'pizzalayer-template-app-test', 'pizzalayer_template_app_test' );
	



/**
 * PizzaLayer → Preview (submenu) + Full-width Live Preview Page
 * - Parent menu slug: pizzalayer_main_menu
 * - Full-width preview pane
 * - Admin notice: current template + copyable template dir URL
 * - Templates dropdown from all sources
 * - Icon-only button (dashicon) to AJAX-refresh preview WITHOUT saving option
 *
 * @package PizzaLayer
 */

/* +=============================================================+
   | 0) Bootstrap constants (safe fallbacks)                     |
   +=============================================================+ */
if ( ! function_exists( 'pizzalayer_preview_bootstrap_constants' ) ) {
	function pizzalayer_preview_bootstrap_constants() {
		if ( ! defined( 'PIZZALAYER_TEMPLATES_PATH' ) || ! defined( 'PIZZALAYER_TEMPLATES_URL' ) ) {
			$plugin_root   = dirname( dirname( __FILE__ ) ); // plugin/
			$plugin_folder = basename( $plugin_root );
			$tpl_path      = trailingslashit( $plugin_root ) . 'templates/';
			$tpl_url       = trailingslashit( plugins_url( $plugin_folder . '/templates/' ) );

			if ( ! defined( 'PIZZALAYER_TEMPLATES_PATH' ) ) {
				define( 'PIZZALAYER_TEMPLATES_PATH', $tpl_path );
			}
			if ( ! defined( 'PIZZALAYER_TEMPLATES_URL' ) ) {
				define( 'PIZZALAYER_TEMPLATES_URL', $tpl_url );
			}
		}
	}
}

/* +=============================================================+
   | 1) Templates list helper                                    |
   +=============================================================+ */
if ( ! function_exists( 'pizzalayer_get_all_templates_map' ) ) {
	/**
	 * Return available templates as [slug => path].
	 * Uses project helper if available; else shallow-scan /templates dir.
	 *
	 * @return array
	 */
	function pizzalayer_get_all_templates_map() {
		pizzalayer_preview_bootstrap_constants();

		// Prefer project helper if present.
		if ( function_exists( 'pizzalayer_get_available_templates_from_dirs' ) ) {
			$map = (array) pizzalayer_get_available_templates_from_dirs();
			if ( ! empty( $map ) ) {
				return $map;
			}
		}

		// Fallback scan.
		$map  = array();
		$base = trailingslashit( PIZZALAYER_TEMPLATES_PATH );

		if ( is_dir( $base ) && is_readable( $base ) ) {
			$dirs = glob( $base . '*', GLOB_ONLYDIR );
			if ( $dirs ) {
				foreach ( $dirs as $dir ) {
					$slug        = basename( $dir );
					$map[ $slug ] = $dir;
				}
			}
		}
		return $map;
	}
}

/* +=============================================================+
   | 2) Render preview for a given template slug (no save)       |
   +=============================================================+ */
if ( ! function_exists( 'pizzalayer_render_preview_for_template' ) ) {
	/**
	 * Render preview HTML for a selected template WITHOUT changing the saved option.
	 *
	 * @param string $template_slug
	 * @return string
	 */
	function pizzalayer_render_preview_for_template( $template_slug ) {
		if ( ! function_exists( 'pizzalayer_template_app_test' ) ) {
			return '<div class="notice notice-warning" style="margin:0;"><p><strong>' .
				esc_html__( 'Preview function not found:', 'pizzalayer' ) .
				'</strong> ' .
				esc_html__( 'The function pizzalayer_template_app_test() is not available. Ensure it is loaded.', 'pizzalayer' ) .
			'</p></div>';
		}

		// Temporarily override the option value.
		$filter = static function( $value ) use ( $template_slug ) {
			return $template_slug;
		};
		add_filter( 'pre_option_pizzalayer_setting_global_template', $filter, 99 );

		ob_start();
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo pizzalayer_template_app_test();
		$html = ob_get_clean();

		remove_filter( 'pre_option_pizzalayer_setting_global_template', $filter, 99 );

		return $html;
	}
}

/* +=============================================================+
   | 3) AJAX: live-switch preview (no save)                      |
   +=============================================================+ */
if ( ! function_exists( 'pizzalayer_ajax_preview_switch_template' ) ) {
	function pizzalayer_ajax_preview_switch_template() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized', 'pizzalayer' ) ), 403 );
		}

		check_ajax_referer( 'pizzalayer_preview_switch', 'nonce' );

		$template = isset( $_POST['template'] ) ? sanitize_key( wp_unslash( $_POST['template'] ) ) : '';
		if ( '' === $template ) {
			wp_send_json_error( array( 'message' => __( 'Missing template', 'pizzalayer' ) ) );
		}

		$templates = pizzalayer_get_all_templates_map();
		if ( empty( $templates[ $template ] ) ) {
			wp_send_json_error( array( 'message' => __( 'Unknown template', 'pizzalayer' ) ) );
		}

		pizzalayer_preview_bootstrap_constants();

		$html = pizzalayer_render_preview_for_template( $template );

		wp_send_json_success( array(
			'html'             => $html,
			'template'         => $template,
			'template_dir_url' => trailingslashit( trailingslashit( PIZZALAYER_TEMPLATES_URL ) . $template ),
		) );
	}
	add_action( 'wp_ajax_pizzalayer_preview_switch_template', 'pizzalayer_ajax_preview_switch_template' );
}




/* +=============================================================+
   | 4) Register submenu under pizzalayer_main_menu              |
   +=============================================================+ */
   
   /**
 * Tiny helper to safely return int 99 without causing parse issues if pasted oddly.
 * (Some editors accidentally introduce trailing spaces or BOM; this avoids magic numbers inline.)
 *
 * @return int
 */
if ( ! function_exists( 'ninety_nine_priority_fix_for_php' ) ) {
	function ninety_nine_priority_fix_for_php() {
		return 99;
	}
}



if ( ! function_exists( 'pizzalayer_register_preview_submenu' ) ) {
	function pizzalayer_register_preview_submenu() {
		$parent_slug = 'pizzalayer_main_menu';

		// Ensure parent exists; if not, create a minimal top-level with this slug.
		global $menu;
		$parent_exists = false;
		if ( is_array( $menu ) ) {
			foreach ( $menu as $m ) {
				if ( isset( $m[2] ) && $m[2] === $parent_slug ) {
					$parent_exists = true;
					break;
				}
			}
		}
		if ( ! $parent_exists ) {
			// Lightweight top-level so submenu will attach cleanly.
			add_menu_page(
				__( 'PizzaLayer', 'pizzalayer' ),
				__( 'PizzaLayer', 'pizzalayer' ),
				'manage_options',
				$parent_slug,
				'__return_null',
				'dashicons-pizza',
				56
			);
		}

		add_submenu_page(
			$parent_slug,
			__( 'PizzaLayer Preview', 'pizzalayer' ),
			__( 'Preview', 'pizzalayer' ),
			'manage_options',
			'pizzalayer-preview',
			'pizzalayer_render_preview_page'
		);
	}
	// Late priority to let the main menu register first.
	add_action( 'admin_menu', 'pizzalayer_register_preview_submenu',  ninety_nine_priority_fix_for_php() );
}



/* +=============================================================+
   | 5) Page renderer                                            |
   +=============================================================+ */
if ( ! function_exists( 'pizzalayer_render_preview_page' ) ) {
	function pizzalayer_render_preview_page() {
		pizzalayer_preview_bootstrap_constants();

		$active_template  = get_option( 'pizzalayer_setting_global_template', 'default' );
		$template_dir_url = trailingslashit( trailingslashit( PIZZALAYER_TEMPLATES_URL ) . $active_template );

		$templates = pizzalayer_get_all_templates_map();
		ksort( $templates, SORT_NATURAL | SORT_FLAG_CASE );

		// Non-JS fallback via ?preview_template=
		$selected_for_preview = isset( $_GET['preview_template'] )
			? sanitize_key( wp_unslash( $_GET['preview_template'] ) )
			: $active_template;

		if ( empty( $templates[ $selected_for_preview ] ) ) {
			$selected_for_preview = $active_template;
		}

		$nonce = wp_create_nonce( 'pizzalayer_preview_switch' );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'PizzaLayer Preview', 'pizzalayer' ); ?></h1>

			<!-- Admin notice -->
			<div class="notice notice-info is-dismissible" style="margin-top:16px;">
				<div style="display:flex; gap:16px; align-items:flex-start; flex-wrap:wrap;">

					<div>
						<p style="margin:0 0 8px 0;">
							<strong><?php esc_html_e( 'Current Template (saved):', 'pizzalayer' ); ?></strong>
							<?php echo esc_html( $active_template ); ?>
                        </p>

						<div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
							<label for="pizzalayer-template-dir-url" style="font-weight:600;">
								<?php esc_html_e( 'Template Directory URL:', 'pizzalayer' ); ?>
							</label>
							<input
								type="text"
								id="pizzalayer-template-dir-url"
								class="regular-text"
								style="min-width:420px;"
								readonly
								onfocus="this.select();"
								value="<?php echo esc_attr( $template_dir_url ); ?>"
							/>
							<button type="button" class="button button-secondary" id="pizzalayer-copy-template-url" aria-label="<?php esc_attr_e( 'Copy template directory URL', 'pizzalayer' ); ?>">
								<span class="dashicons dashicons-clipboard" aria-hidden="true"></span>
								<span class="screen-reader-text"><?php esc_html_e( 'Copy URL', 'pizzalayer' ); ?></span>
							</button>
						</div>
					</div>

					<div>
						<p style="margin:0 0 8px 0;">
							<strong><?php esc_html_e( 'Preview With (does not save):', 'pizzalayer' ); ?></strong>
						</p>
						<div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
							<label class="screen-reader-text" for="pizzalayer-preview-template"><?php esc_html_e( 'Select template to preview', 'pizzalayer' ); ?></label>
							<select id="pizzalayer-preview-template">
								<?php foreach ( $templates as $slug => $path ) : ?>
									<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $slug, $selected_for_preview ); ?>>
										<?php echo esc_html( $slug ); ?>
									</option>
								<?php endforeach; ?>
							</select>

							<button
								type="button"
								class="button"
								id="pizzalayer-refresh-preview"
								aria-label="<?php esc_attr_e( 'Refresh preview with selected template (does not save)', 'pizzalayer' ); ?>"
								title="<?php esc_attr_e( 'Refresh preview (no save)', 'pizzalayer' ); ?>"
							>
								<span class="dashicons dashicons-update" aria-hidden="true"></span>
								<span class="screen-reader-text"><?php esc_html_e( 'Refresh Preview', 'pizzalayer' ); ?></span>
							</button>

							<!-- Non-JS fallback -->
							<a class="button button-link" href="<?php echo esc_url( add_query_arg( 'preview_template', $selected_for_preview ) ); ?>">
								<?php esc_html_e( 'Reload w/ selected', 'pizzalayer' ); ?>
							</a>
						</div>
						<p class="description" style="margin:.5em 0 0;">
							<?php esc_html_e( 'Switch the preview without changing the saved template option.', 'pizzalayer' ); ?>
						</p>
					</div>

				</div>
			</div>

			<!-- Full-width preview pane -->
			<div id="pizzalayer-preview-pane" aria-label="<?php esc_attr_e( 'Full-width preview pane', 'pizzalayer' ); ?>" style="margin:20px -20px 0 -20px; padding:0;">
				<div class="notice-inline" style="background:#fff; border-top:1px solid #c3c4c7; border-bottom:1px solid #c3c4c7;">
					<div style="padding:12px 20px; border-bottom:1px solid #dcdcde; display:flex; align-items:center; justify-content:space-between;">
						<strong><?php esc_html_e( 'Shortcode Preview', 'pizzalayer' ); ?></strong>
						<span class="description"><?php esc_html_e( 'Rendering pizzalayer_template_app_test()', 'pizzalayer' ); ?></span>
					</div>

					<div id="pizzalayer-preview-output" style="padding:20px;">
						<?php
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo pizzalayer_render_preview_for_template( $selected_for_preview );
						?>
					</div>
				</div>
			</div>

			<!-- Inline, dependency-free JS -->
			<script>
			(function(){
				var $ = function(s){ return document.querySelector(s); };
				var copyBtn     = $('#pizzalayer-copy-template-url');
				var urlInput    = $('#pizzalayer-template-dir-url');
				var sel         = $('#pizzalayer-preview-template');
				var refreshBtn  = $('#pizzalayer-refresh-preview');
				var out         = $('#pizzalayer-preview-output');
				var ajaxurl     = window.ajaxurl || '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>';
				var nonce       = '<?php echo esc_js( $nonce ); ?>';

				// Clipboard icon
				if (copyBtn && urlInput) {
					copyBtn.addEventListener('click', function(){
						try {
							urlInput.focus();
							urlInput.select();
							document.execCommand('copy');
							var original = copyBtn.innerHTML;
							copyBtn.innerHTML = '<span class="dashicons dashicons-yes"></span><span class="screen-reader-text"><?php echo esc_js( __( 'Copied', 'pizzalayer' ) ); ?></span>';
							setTimeout(function(){ copyBtn.innerHTML = original; }, 1200);
						} catch(e) { if(window.console){ console.warn('Copy failed:', e); } }
					});
				}

				// AJAX refresh (no save)
				function doRefresh() {
					if (!sel || !out) return;
					var template = sel.value || '';
					if (!template) return;

					if (refreshBtn) {
						refreshBtn.setAttribute('disabled','disabled');
						var original = refreshBtn.innerHTML;
						refreshBtn.dataset._original = original;
						refreshBtn.innerHTML = '<span class="dashicons dashicons-update pizzalayer-spin"></span>';
					}

					var xhr = new XMLHttpRequest();
					var data = new FormData();
					data.append('action', 'pizzalayer_preview_switch_template');
					data.append('nonce', nonce);
					data.append('template', template);

					xhr.open('POST', ajaxurl, true);
					xhr.onreadystatechange = function(){
						if (xhr.readyState === 4) {
							if (refreshBtn) {
								refreshBtn.removeAttribute('disabled');
								refreshBtn.innerHTML = refreshBtn.dataset._original || '<span class="dashicons dashicons-update"></span>';
							}
							try {
								var res = JSON.parse(xhr.responseText);
								if (res && res.success) {
									out.innerHTML = res.data.html || '';
									if (urlInput && res.data.template_dir_url) {
										urlInput.value = res.data.template_dir_url;
									}
								} else {
									alert((res && res.data && res.data.message) ? res.data.message : '<?php echo esc_js( __( 'Preview update failed.', 'pizzalayer' ) ); ?>');
                                }
							} catch(e) {
								if(window.console){ console.error('Bad JSON:', e); }
								alert('<?php echo esc_js( __( 'Preview update failed due to an unexpected response.', 'pizzalayer' ) ); ?>');
							}
						}
					};
					xhr.send(data);
				}

				if (refreshBtn) {
					refreshBtn.addEventListener('click', doRefresh);
				}
				// Optional auto-refresh on change:
				// if (sel) sel.addEventListener('change', doRefresh);
			})();
			</script>

			<style>
				/* Minimal spinner for dashicons */
				.pizzalayer-spin {
					display:inline-block;
					animation: pizzalayer-spin-key 1s linear infinite;
					transform-origin: 50% 50%;
				}
				@keyframes pizzalayer-spin-key { from { transform: rotate(0deg);} to { transform: rotate(360deg);} }
			</style>
		</div>
		<?php
	}
}}