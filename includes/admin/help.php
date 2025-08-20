<?php

function pizzalayer_render_help_page() {
	// Build common admin URLs for CPTs and pages
	$urls = array(
		'crusts_list'   => admin_url( 'edit.php?post_type=pizzalayer_crusts' ),
		'crusts_new'    => admin_url( 'post-new.php?post_type=pizzalayer_crusts' ),
		'sauces_list'   => admin_url( 'edit.php?post_type=pizzalayer_sauces' ),
		'sauces_new'    => admin_url( 'post-new.php?post_type=pizzalayer_sauces' ),
		'cheeses_list'  => admin_url( 'edit.php?post_type=pizzalayer_cheeses' ),
		'cheeses_new'   => admin_url( 'post-new.php?post_type=pizzalayer_cheeses' ),
		'toppings_list' => admin_url( 'edit.php?post_type=pizzalayer_toppings' ),
		'toppings_new'  => admin_url( 'post-new.php?post_type=pizzalayer_toppings' ),
		'drizzles_list' => admin_url( 'edit.php?post_type=pizzalayer_drizzles' ),
		'drizzles_new'  => admin_url( 'post-new.php?post_type=pizzalayer_drizzles' ),
		'cuts_list'     => admin_url( 'edit.php?post_type=pizzalayer_cuts' ),
		'cuts_new'      => admin_url( 'post-new.php?post_type=pizzalayer_cuts' ),

		// Known PizzaLayer pages (adjust slugs if your plugin uses different ones)
		'template_page' => admin_url( 'admin.php?page=pizzalayer_my_template' ),
		// If you have a shortcode generator page, replace with its real slug:
		'shortcode_page'=> admin_url( 'admin.php?page=pizzalayer_shortcode_generator' ),
	);

	?>
	<div class="wrap pizzalayer-help-wrap">
		<h1><span class="dashicons dashicons-editor-help" style="color:#2271b1;"></span> PizzaLayer Help</h1>

		<!-- Intro Row -->
		<div class="pzl-intro notice" style="background:#fff;">
			<div class="pzl-intro__inner">
				<div class="pzl-intro__icon">
					<span class="dashicons dashicons-pizza" aria-hidden="true"></span>
				</div>
				<div class="pzl-intro__content">
					<h2>Welcome to PizzaLayer</h2>
					<p class="description">
						Build fully‑custom pizzas with layered options—crusts, sauces, cheeses, toppings, drizzles, and cuts—
						then display them anywhere with shortcodes or templates. Use the quick links below to add items,
						configure templates, and learn the workflow.
					</p>
				</div>
			</div>
		</div>

		<!-- Three-Column Feature Row -->
		<div class="pzl-features">
			<div class="pzl-feature-card">
				<div class="pzl-feature-card__icon"><span class="dashicons dashicons-index-card"></span></div>
				<h3>Manage Layers</h3>
				<p>Create and edit all your layer types—make sure each has images and complete details.</p>
				<a class="button button-primary" href="<?php echo esc_url( $urls['toppings_new'] ); ?>">
					<span class="dashicons dashicons-plus-alt2" style="vertical-align:text-bottom;"></span> Add a Topping
				</a>
			</div>
			<div class="pzl-feature-card">
				<div class="pzl-feature-card__icon"><span class="dashicons dashicons-admin-appearance"></span></div>
				<h3>Choose a Template</h3>
				<p>Select and preview the active template to control the front‑end pizza builder UI.</p>
				<a class="button" href="<?php echo esc_url( $urls['template_page'] ); ?>">
					<span class="dashicons dashicons-visibility" style="vertical-align:text-bottom;"></span> Template Settings
				</a>
			</div>
			<div class="pzl-feature-card">
				<div class="pzl-feature-card__icon"><span class="dashicons dashicons-shortcode"></span></div>
				<h3>Embed & Display</h3>
				<p>Generate a shortcode and drop your pizza builder anywhere on your site.</p>
				<a class="button" href="<?php echo esc_url( $urls['shortcode_page'] ); ?>">
					<span class="dashicons dashicons-admin-page" style="vertical-align:text-bottom;"></span> Shortcode Generator
				</a>
			</div>
		</div>

		<!-- Quick Actions (full-width UI grid) -->
		<h2 class="pzl-section-title"><span class="dashicons dashicons-admin-tools"></span> Quick Actions</h2>
		<div class="pzl-actions-grid">
			<!-- Crusts -->
			<div class="pzl-action">
				<div class="pzl-action__icon"><span class="dashicons dashicons-carrot"></span></div>
				<div class="pzl-action__body">
					<h3>Crusts</h3>
					<p>Add new crusts or manage your crust library.</p>
					<div class="pzl-action__buttons">
						<a class="button button-primary" href="<?php echo esc_url( $urls['crusts_new'] ); ?>">
							<span class="dashicons dashicons-plus-alt2"></span> Add New
						</a>
						<a class="button" href="<?php echo esc_url( $urls['crusts_list'] ); ?>">
							<span class="dashicons dashicons-category"></span> View All
						</a>
					</div>
				</div>
			</div>

			<!-- Sauces -->
			<div class="pzl-action">
				<div class="pzl-action__icon"><span class="dashicons dashicons-buddicons-replies"></span></div>
				<div class="pzl-action__body">
					<h3>Sauces</h3>
					<p>Manage sauce styles and set their visuals.</p>
					<div class="pzl-action__buttons">
						<a class="button button-primary" href="<?php echo esc_url( $urls['sauces_new'] ); ?>">
							<span class="dashicons dashicons-plus-alt2"></span> Add New
						</a>
						<a class="button" href="<?php echo esc_url( $urls['sauces_list'] ); ?>">
							<span class="dashicons dashicons-category"></span> View All
						</a>
					</div>
				</div>
			</div>

			<!-- Cheeses -->
			<div class="pzl-action">
				<div class="pzl-action__icon"><span class="dashicons dashicons-smiley"></span></div>
				<div class="pzl-action__body">
					<h3>Cheeses</h3>
					<p>Add cheese types and variants for extra realism.</p>
					<div class="pzl-action__buttons">
						<a class="button button-primary" href="<?php echo esc_url( $urls['cheeses_new'] ); ?>">
							<span class="dashicons dashicons-plus-alt2"></span> Add New
						</a>
						<a class="button" href="<?php echo esc_url( $urls['cheeses_list'] ); ?>">
							<span class="dashicons dashicons-category"></span> View All
						</a>
					</div>
				</div>
			</div>

			<!-- Toppings -->
			<div class="pzl-action">
				<div class="pzl-action__icon"><span class="dashicons dashicons-yes-alt"></span></div>
				<div class="pzl-action__body">
					<h3>Toppings</h3>
					<p>Create your toppings catalog with images and pricing.</p>
					<div class="pzl-action__buttons">
						<a class="button button-primary" href="<?php echo esc_url( $urls['toppings_new'] ); ?>">
							<span class="dashicons dashicons-plus-alt2"></span> Add New
						</a>
						<a class="button" href="<?php echo esc_url( $urls['toppings_list'] ); ?>">
							<span class="dashicons dashicons-category"></span> View All
						</a>
					</div>
				</div>
			</div>

			<!-- Drizzles -->
			<div class="pzl-action">
				<div class="pzl-action__icon"><span class="dashicons dashicons-admin-generic"></span></div>
				<div class="pzl-action__body">
					<h3>Drizzles</h3>
					<p>Add finishing drizzles for extra flavor and style.</p>
					<div class="pzl-action__buttons">
						<a class="button button-primary" href="<?php echo esc_url( $urls['drizzles_new'] ); ?>">
							<span class="dashicons dashicons-plus-alt2"></span> Add New
						</a>
						<a class="button" href="<?php echo esc_url( $urls['drizzles_list'] ); ?>">
							<span class="dashicons dashicons-category"></span> View All
						</a>
					</div>
				</div>
			</div>

			<!-- Cuts -->
			<div class="pzl-action">
				<div class="pzl-action__icon"><span class="dashicons dashicons-admin-page"></span></div>
				<div class="pzl-action__body">
					<h3>Cuts</h3>
					<p>Control slice styles and counts for the final presentation.</p>
					<div class="pzl-action__buttons">
						<a class="button button-primary" href="<?php echo esc_url( $urls['cuts_new'] ); ?>">
							<span class="dashicons dashicons-plus-alt2"></span> Add New
						</a>
						<a class="button" href="<?php echo esc_url( $urls['cuts_list'] ); ?>">
							<span class="dashicons dashicons-category"></span> View All
						</a>
					</div>
				</div>
			</div>
		</div><!-- .pzl-actions-grid -->

		<!-- Accordion (full-width) -->
		<h2 class="pzl-section-title"><span class="dashicons dashicons-list-view"></span> Step‑by‑Step Instructions</h2>
		<div class="pzl-accordion">
			<?php
			$layers = array(
				array( 'slug' => 'crusts',   'title' => 'Crusts',   'icon' => 'carrot' ),
				array( 'slug' => 'sauces',   'title' => 'Sauces',   'icon' => 'buddicons-replies' ),
				array( 'slug' => 'cheeses',  'title' => 'Cheeses',  'icon' => 'smiley' ),
				array( 'slug' => 'toppings', 'title' => 'Toppings', 'icon' => 'yes-alt' ),
				array( 'slug' => 'drizzles', 'title' => 'Drizzles', 'icon' => 'admin-generic' ),
				array( 'slug' => 'cuts',     'title' => 'Cuts',     'icon' => 'admin-page' ),
			);
			foreach ( $layers as $layer ) :
				$list_url = $urls[ $layer['slug'] . '_list' ];
				$new_url  = $urls[ $layer['slug'] . '_new' ];
			?>
				<details class="pzl-acc-item">
					<summary>
						<span class="dashicons dashicons-<?php echo esc_attr( $layer['icon'] ); ?>"></span>
						<strong><?php echo esc_html( $layer['title'] ); ?></strong>
					</summary>
					<div class="pzl-acc-body">
						<ol>
							<li><strong>Add:</strong> Create a new <?php echo esc_html( strtolower( $layer['title'] ) ); ?> via
								<a href="<?php echo esc_url( $new_url ); ?>">Add New</a>. Upload clear images and set a descriptive title.</li>
							<li><strong>Edit:</strong> From <a href="<?php echo esc_url( $list_url ); ?>">All <?php echo esc_html( $layer['title'] ); ?></a>,
								open an item to refine its details. <em>Be sure to complete all ACF/SCF‑powered fields</em>
								(in addition to images and basics) so pricing, visibility, and front‑end rendering work correctly.</li>
							<li><strong>Organize:</strong> Use categories/attributes (if available) and consistent naming so the builder stays tidy.</li>
						</ol>
						<div class="pzl-acc-cta">
							<a class="button button-primary" href="<?php echo esc_url( $new_url ); ?>">
								<span class="dashicons dashicons-plus-alt2"></span> Add New <?php echo esc_html( $layer['title'] ); ?>
							</a>
							<a class="button" href="<?php echo esc_url( $list_url ); ?>">
								<span class="dashicons dashicons-category"></span> View All <?php echo esc_html( $layer['title'] ); ?>
							</a>
						</div>
					</div>
				</details>
			<?php endforeach; ?>
		</div>

		<!-- CTA Row -->
		<div class="pzl-cta notice notice-info is-dismissible">
			<div class="pzl-cta__inner">
				<div class="pzl-cta__icon"><span class="dashicons dashicons-sos"></span></div>
				<div class="pzl-cta__content">
					<h2>Want more guides & examples?</h2>
					<p>We’re building a comprehensive Help Center with tutorials, best practices, and developer tips.</p>
				</div>
				<div class="pzl-cta__actions">
					<a class="button button-primary" href="#" target="_blank" rel="noopener">
						<span class="dashicons dashicons-external"></span> Visit Help Website
					</a>
				</div>
			</div>
		</div>
	</div><!-- .wrap -->

	<style>
		.pizzalayer-help-wrap .pzl-section-title { display:flex; align-items:center; gap:.5rem; margin-top:2rem; }
		.pizzalayer-help-wrap .pzl-section-title .dashicons { font-size:20px; width:20px; height:20px; }

		/* Intro */
		.pzl-intro { border:1px solid #dcdcde; padding:16px; margin-top:12px; }
		.pzl-intro__inner { display:flex; gap:16px; align-items:flex-start; }
		.pzl-intro__icon .dashicons { font-size:40px; width:40px; height:40px; color:#d63638; }
		.pzl-intro__content h2 { margin:4px 0 6px; }

		/* Feature Cards */
		.pzl-features { margin-top:18px; display:grid; grid-template-columns:repeat(3,1fr); gap:16px; }
		.pzl-feature-card { background:#fff; border:1px solid #dcdcde; padding:16px; border-radius:4px; }
		.pzl-feature-card__icon .dashicons { font-size:28px; width:28px; height:28px; color:#2271b1; }
		.pzl-feature-card h3 { margin:8px 0; }
		.pzl-feature-card p { margin:0 0 10px; }
		.pzl-feature-card .button .dashicons { margin-right:4px; }

		/* Actions Grid */
		.pzl-actions-grid { margin-top:10px; display:grid; grid-template-columns:repeat(3,1fr); gap:16px; }
		.pzl-action { background:#fff; border:1px solid #dcdcde; padding:16px; border-radius:4px; display:flex; gap:12px; }
		.pzl-action__icon .dashicons { font-size:28px; width:28px; height:28px; color:#646970; }
		.pzl-action__body h3 { margin:0 0 6px; }
		.pzl-action__buttons { display:flex; flex-wrap:wrap; gap:8px; margin-top:8px; }
		.pzl-action__buttons .button .dashicons { margin-right:4px; }

		/* Accordion */
		.pzl-accordion { margin-top:10px; }
		.pzl-acc-item { background:#fff; border:1px solid #dcdcde; border-radius:4px; margin-bottom:10px; }
		.pzl-acc-item > summary { cursor:pointer; list-style:none; padding:12px 14px; display:flex; align-items:center; gap:8px; }
		.pzl-acc-item > summary::-webkit-details-marker { display:none; }
		.pzl-acc-item[open] > summary { border-bottom:1px solid #dcdcde; background:#f6f7f7; }
		.pzl-acc-body { padding:14px; }
		.pzl-acc-body ol { margin:0 0 10px 18px; }
		.pzl-acc-cta { display:flex; gap:8px; flex-wrap:wrap; }

		/* CTA footer */
		.pzl-cta { margin-top:18px; }
		.pzl-cta__inner { display:flex; gap:16px; align-items:center; }
		.pzl-cta__icon .dashicons { font-size:28px; width:28px; height:28px; color:#2271b1; }
		.pzl-cta__content h2 { margin:0 0 4px; }
		.pzl-cta__actions .button .dashicons { margin-right:4px; }

		/* Responsive */
		@media (max-width: 1100px) {
			.pzl-features { grid-template-columns:repeat(2,1fr); }
			.pzl-actions-grid { grid-template-columns:repeat(2,1fr); }
		}
		@media (max-width: 700px) {
			.pzl-intro__inner { flex-direction:column; }
			.pzl-features { grid-template-columns:1fr; }
			.pzl-actions-grid { grid-template-columns:1fr; }
		}
	</style>
	<?php
}
