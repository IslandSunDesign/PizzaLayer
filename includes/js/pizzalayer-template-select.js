jQuery(document).ready(function($) {
	$('.pizzalayer-use-template').on('click', function(e) {
		e.preventDefault();

		const $btn = $(this);
		const slug = $btn.data('slug');
		const confirmMsg = `Are you sure you want to use the "${slug}" template? This will change the active template.`;

		if (!confirm(confirmMsg)) {
			return;
		}

		$btn.prop('disabled', true).text('Setting...');

		$.post(PizzaLayerTemplate.ajax_url, {
			action: 'pizzalayer_set_template',
			security: PizzaLayerTemplate.nonce,
			template_slug: slug
		}, function(response) {
			if (response.success) {
				location.reload();
			} else {
				alert('Error: ' + response.data);
				$btn.prop('disabled', false).text('Use Template');
			}
		});
	});
});
