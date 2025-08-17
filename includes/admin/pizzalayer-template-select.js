/* global PZTPLayerTemplate, jQuery */
(function() {
	'use strict';

	jQuery(document).ready(function() {

		jQuery(document).on('click', '.pizzalayer-use-template', function(e) {
			e.preventDefault();

			var $btn = jQuery(this);
			var slug = $btn.data('slug');

			if (!slug) { return; }

			if (!window.confirm(PZTPLayerTemplate.confirmMsg || 'Use this template?')) {
				return;
			}

			$btn.prop('disabled', true).text('Saving...');

			jQuery.post(PZTPLayerTemplate.ajaxUrl, {
				action: 'pizzalayer_set_template',
				nonce: PZTPLayerTemplate.nonce,
				slug: slug
			})
			.done(function(resp) {
				if (resp && resp.success) {
					// Update the visible active template label
					jQuery('#pzt-active-template').text(slug);
					// Update the hidden field so Save Settings posts the same value.
					jQuery('#pzt-selected-template').val(slug);

					// Small success notice
					var msg = PZTPLayerTemplate.successMsg || 'Template saved.';
					wpNotify(msg, 'updated');
				} else {
					var err = (resp && resp.data && resp.data.message) ? resp.data.message : 'Error saving template.';
					wpNotify(err, 'error');
				}
			})
			.fail(function() {
				wpNotify('Network error saving template.', 'error');
			})
			.always(function() {
				$btn.prop('disabled', false).text('Use Template');
			});

		});

		// Simple notice helper that mimics WP admin notices
		function wpNotify(message, type) {
			type = type || 'updated'; // 'updated' or 'error'
			var $notice = jQuery(
				'<div class="notice is-dismissible ' + type + '" style="margin-top:15px;"><p>' + message + '</p></div>'
			);
			// Put notice just after the main heading area
			jQuery('.wrap .wp-header-end').after($notice);
			// Dismiss
			$notice.on('click', '.notice-dismiss', function() {
				$notice.remove();
			});
		}
	});
})();
