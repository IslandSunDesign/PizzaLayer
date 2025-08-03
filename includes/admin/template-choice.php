<?php

// +=========================================================+
// | Get list of available templates from either plugin or theme source folders |
// +=========================================================+
function pizzalayer_get_available_templates_from_dirs() {
	$plugin_template_dir  = PIZZALAYER_TEMPLATES_PATH;
	$theme_template_dir  = get_stylesheet_directory() . '/pzttemplates/';
	$template_dirs       = [];

	foreach ( [ $plugin_template_dir, $theme_template_dir ] as $dir ) {
		if ( is_dir( $dir ) ) {
			$folders = scandir( $dir );
			foreach ( $folders as $folder ) {
				if ( $folder === '.' || $folder === '..' ) {
					continue;
				}
				if ( is_dir( $dir . $folder ) ) {
					$template_dirs[ $folder ] = $dir . $folder . '/pztp-template-info.php';
				}
			}
		}
	}
	return $template_dirs;
}

// +==================================================+
// | Callback to Render "My Template" Admin Page View |
// +==================================================+
function pizzalayer_render_my_template_page() {
	$active_template      = get_option( 'pizzalayer_setting_global_template', 'default' );
	$plugin_template_dir  = PIZZALAYER_TEMPLATES_PATH;
	$plugin_template_url  = PIZZALAYER_TEMPLATES_URL;
	$all_templates        = pizzalayer_get_available_templates_from_dirs();

	// Load active template info
	$active_info_path = isset( $all_templates[ $active_template ] ) ? $all_templates[ $active_template ] : '';
	$active_info      = ( $active_info_path && file_exists( $active_info_path ) ) ? include $active_info_path : [];

	?>
	<div class="wrap">
		<h1 class="wp-heading-inline"><span class="dashicons dashicons-admin-appearance"></span> My Template</h1>
		<hr class="wp-header-end">

		<!-- +======================+ -->
		<!-- | Active Template Box  | -->
		<!-- +======================+ -->
		<div class="notice notice-info" style="background:#fff; padding:20px; margin-bottom:30px; border-left:4px solid #0073aa;">
			<h2 style="margin-top:0;">Active Template</h2>
			<p style="font-size:16px; margin:0;"><?php echo esc_html( $active_template ); ?></p>
		</div>

		<!-- +=====================================+ -->
		<!-- | Active Template Info + Image Panel  | -->
		<!-- +=====================================+ -->
		<div style="display: flex; gap: 30px; margin-bottom: 40px;">
			<!-- Info Panel -->
			<div style="flex: 1; background: #fff; padding: 20px; border: 1px solid #ccd0d4;">
				<h3 style="margin-top:0;">Template Details</h3>
				<?php if ( ! empty( $active_info ) ) : ?>
					<ul style="margin-left:20px;">
						<?php foreach ( $active_info as $key => $value ) : ?>
							<li><strong><?php echo esc_html( ucfirst( $key ) ); ?>:</strong> <?php echo esc_html( $value ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
					<p>No info found for active template.</p>
				<?php endif; ?>
			</div>

			<!-- Preview Panel -->
			<div style="flex: 1; background: #fff; padding: 20px; border: 1px solid #ccd0d4; text-align:center;">
				<h3 style="margin-top:0;">Preview</h3>
				<img src="<?php echo esc_url( $plugin_template_url . $active_template . '/preview.jpg' ); ?>" alt="Preview Image at <?php echo $plugin_template_url . $active_template; ?>" style="max-width:100%; height:auto; border:1px solid #ccc;">
			</div>
		</div>

		<!-- +==========================+ -->
		<!-- | Available Templates Box  | -->
		<!-- +==========================+ -->
		<div class="notice notice-success" style="background:#fff; padding:20px; border-left:4px solid #46b450;">
			<h2 style="margin-top:0;">Available Templates</h2>

			<?php if ( ! empty( $all_templates ) ) : ?>
				<div style="display: flex; flex-wrap: wrap; gap: 20px; margin-top: 20px;">
<?php foreach ( $all_templates as $template_slug => $info_file ) :
	$info = file_exists( $info_file ) ? include $info_file : [];
?>
	<div style="flex: 1 1 300px; background: #f9f9f9; padding: 15px; border: 1px solid #ccd0d4; border-radius: 5px;">
		<strong style="font-size:16px;"><?php echo esc_html( $template_slug ); ?></strong>
		<?php if ( ! empty( $info ) ) : ?>
			<ul style="margin-top:10px; margin-left: 18px;">
				<?php foreach ( $info as $key => $value ) : ?>
					<li><strong><?php echo esc_html( ucfirst( $key ) ); ?>:</strong> <?php echo esc_html( $value ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php else : ?>
			<p style="margin-top:10px;">No info found.</p>
		<?php endif; ?>

		<button class="button pizzalayer-use-template" data-slug="<?php echo esc_attr( $template_slug ); ?>" style="margin-top:10px;">Use Template</button>
	</div>
<?php endforeach; ?>


				</div>
			<?php else : ?>
				<p>No templates found.</p>
			<?php endif; ?>
		</div>

		<!-- +===========================+ -->
		<!-- | Template Help Meta Box   | -->
		<!-- +===========================+ -->
		<div style="background: #f1f1f1; margin-top: 40px; padding: 20px; border-left: 4px solid #0073aa;">
			<h2>Template Help</h2>
			<p>Need help choosing or modifying a template? Visit the documentation or contact support for more guidance.</p>
		</div>

		<!-- +===========================+ -->
		<!-- | CTA Save and Refresh Bar | -->
		<!-- +===========================+ -->
		<div style="margin-top:30px; padding:20px; background:#0073aa; color:#fff; display:flex; justify-content:space-between; align-items:center;">
			<p style="margin:0; font-size:16px;">Be sure to save your settings and refresh to see template changes in action.</p>
			<form method="post">
				<?php submit_button( 'Save Settings', 'primary', 'submit', false ); ?>
			</form>
		</div>
	</div>
	<?php
}
