/* PizzaLayer Layer Builder Wizard — admin JS */
/* eslint-disable no-var */
	(function($){
		'use strict';

		var nonce        = (window.pizzalayerLBW && window.pizzalayerLBW.nonce)        || '';
		var ajaxUrl      = (window.pizzalayerLBW && window.pizzalayerLBW.ajaxUrl)      || '';
		var limUrl       = (window.pizzalayerLBW && window.pizzalayerLBW.limUrl)       || '';
		var layerTypes   = (window.pizzalayerLBW && window.pizzalayerLBW.layerTypes)   || {};

		/* ── State ─────────────────────────────────────────── */
		var state = {
			step     : 1,
			typeSlug : '',
			typeLabel: '',
			typeCpt  : '',
			typeColor: '',
			typeExtra: [],
			name     : '',
			slug     : '',
			desc     : '',
			price    : '0.00',
			imageId  : 0,
			imageUrl : '',
			meta     : {}
		};

		/* ── Step navigation ───────────────────────────────── */
		function goStep(n) {
			state.step = n;
			$('.plbw-panel').hide();
			$('#plbw-panel-' + n).show();
			$('.plbw-step').removeClass('is-active is-done');
			for (var i = 1; i < n; i++) {
				$('.plbw-step[data-step="' + i + '"]').addClass('is-done');
			}
			$('.plbw-step[data-step="' + n + '"]').addClass('is-active');
			$('#plbw-progress').attr('aria-valuenow', n);
			$('html,body').animate({ scrollTop: 0 }, 200);

			if (n === 4) { buildReview(); }
		}

		/* ── Step 1: type selection ────────────────────────── */
		$(document).on('click', '.plbw-type-card', function() {
			$('.plbw-type-card').removeClass('is-selected');
			$(this).addClass('is-selected');
			state.typeSlug  = $(this).data('type');
			state.typeLabel = $(this).data('label');
			state.typeCpt   = $(this).data('cpt');
			state.typeColor = $(this).data('color');
			state.typeExtra = $(this).data('extra') || [];
			$('#plbw-step1-next').prop('disabled', false);
		});

		$('#plbw-step1-next').on('click', function() {
			if (!state.typeSlug) { return; }
			// Update step 2 title
			$('#plbw-step2-title').text('<?php esc_html_e( 'Details for your', 'pizzalayer' ); ?> ' + state.typeLabel);
			showExtraFields(state.typeSlug);
			goStep(2);
		});

		function showExtraFields(typeSlug) {
			// Show/hide extra field rows based on type
			$('.plbw-extra').each(function(){
				var forTypes = ($(this).data('for') || '').split(' ');
				if (forTypes.indexOf(typeSlug) !== -1) {
					$(this).show();
				} else {
					$(this).hide();
				}
			});
		}

		/* ── Step 2: details ───────────────────────────────── */
		// Auto-generate slug from name
		$('#plbw-name').on('input', function(){
			state.name = $(this).val();
			if (!$('#plbw-slug').data('manual')) {
				var slug = state.name
					.toLowerCase()
					.replace(/[^a-z0-9\s\-]/g, '')
					.replace(/\s+/g, '-')
					.replace(/-+/g, '-')
					.substring(0, 60);
				$('#plbw-slug').val(slug);
				state.slug = slug;
			}
		});
		$('#plbw-slug').on('input', function(){
			$(this).data('manual', $(this).val() !== '');
			state.slug = $(this).val();
		});
		$('#plbw-description').on('input', function(){ state.desc  = $(this).val(); });
		$('#plbw-price').on('input',       function(){ state.price = $(this).val(); });

		$('#plbw-step2-next').on('click', function(){
			state.name  = $.trim($('#plbw-name').val());
			state.slug  = $.trim($('#plbw-slug').val());
			state.desc  = $.trim($('#plbw-description').val());
			state.price = $('#plbw-price').val();
			if (!state.name) {
				$('#plbw-name').focus().closest('.plbw-field-row').addClass('plbw-field-error');
				return;
			}
			$('#plbw-name').closest('.plbw-field-row').removeClass('plbw-field-error');
			// Collect meta
			state.meta = {};
			if ($('#plbw-calories').val())   { state.meta.calories        = $('#plbw-calories').val(); }
			if ($('#plbw-thickness').val())  { state.meta.thickness       = $('#plbw-thickness').val(); }
			if ($('#plbw-diameter').val())   { state.meta.diameter_inches = $('#plbw-diameter').val(); }
			if ($('#plbw-spice').val())      { state.meta.spice_level     = $('#plbw-spice').val(); }
			if ($('#plbw-is-vegetarian').is(':checked')) { state.meta.is_vegetarian  = '1'; }
			if ($('#plbw-is-vegan').is(':checked'))      { state.meta.is_vegan        = '1'; }
			if ($('#plbw-is-gf').is(':checked'))         { state.meta.is_gluten_free  = '1'; }
			if ($('#plbw-is-dairyfree').is(':checked'))  { state.meta.is_dairy_free   = '1'; }
			state.meta.sort_order = $('#plbw-sort-order').val() || '0';
			goStep(3);
		});

		/* ── Step 2: back buttons ──────────────────────────── */
		$(document).on('click', '.plbw-back-btn', function(){
			goStep( parseInt($(this).data('target'), 10) );
		});

		/* ── Step 3: image ─────────────────────────────────── */
		var mediaFrame = null;

		function openMedia(mode) {
			if (mediaFrame) { mediaFrame.off('select'); }
			mediaFrame = wp.media({
				title  : mode === 'upload' ? '<?php esc_html_e( 'Upload Layer Image', 'pizzalayer' ); ?>' : '<?php esc_html_e( 'Choose Layer Image', 'pizzalayer' ); ?>',
				button : { text: '<?php esc_html_e( 'Use this image', 'pizzalayer' ); ?>' },
				library: mode === 'upload' ? { type: 'image', uploadedTo: null } : { type: 'image' },
				multiple: false
			});
			if (mode === 'upload') { mediaFrame.on('open', function(){ mediaFrame.state().get('selection').reset(); }); }
			mediaFrame.on('select', function(){
				var att = mediaFrame.state().get('selection').first().toJSON();
				setImage(att.id, att.url);
			});
			mediaFrame.open();
		}

		function setImage(id, url) {
			state.imageId  = id;
			state.imageUrl = url;
			$('#plbw-image-id').val(id);
			$('#plbw-image-url').val(url);
			$('#plbw-image-preview').html('<img src="' + url + '" alt="" style="max-width:100%;max-height:200px;border-radius:4px;">');
			$('#plbw-remove-image').show();
		}

		$('#plbw-choose-image').on('click', function(){ openMedia('library'); });
		$('#plbw-upload-image').on('click', function(){ openMedia('upload'); });
		$('#plbw-remove-image').on('click', function(){
			state.imageId  = 0;
			state.imageUrl = '';
			$('#plbw-image-id').val('');
			$('#plbw-image-url').val('');
			$('#plbw-image-preview').html('<span class="dashicons dashicons-format-image plbw-img-icon"></span><p><?php esc_html_e( 'No image selected', 'pizzalayer' ); ?></p>');
			$(this).hide();
		});
		$('#plbw-open-lim').on('click', function(){
			window.open(limUrl, '_blank');
		});
		$('#plbw-step3-next').on('click', function(){ goStep(4); });

		/* ── Step 4: review ────────────────────────────────── */
		function buildReview() {
			var typeInfo = layerTypes[state.typeSlug] || {};
			var slugVal  = state.slug || slugify(state.name);
			var html = '';
			html += '<div class="plbw-review-type" style="--plbw-accent:' + state.typeColor + '">';
			html += '<span class="plbw-review-emoji">' + (typeInfo.emoji || '') + '</span>';
			html += '<span class="plbw-review-type-label">' + escHtml(state.typeLabel) + '</span>';
			html += '</div>';

			html += '<table class="plbw-review-table">';
			html += reviewRow('<?php esc_html_e( 'Name', 'pizzalayer' ); ?>',  escHtml(state.name));
			html += reviewRow('<?php esc_html_e( 'Slug', 'pizzalayer' ); ?>',  '<code>' + escHtml(slugVal) + '</code>');
			if (state.desc) {
				html += reviewRow('<?php esc_html_e( 'Description', 'pizzalayer' ); ?>', escHtml(state.desc));
			}
			if (parseFloat(state.price) > 0) {
				html += reviewRow('<?php esc_html_e( 'Price modifier', 'pizzalayer' ); ?>', escHtml(state.price));
			}
			// Meta
			if (state.meta.thickness)      { html += reviewRow('<?php esc_html_e( 'Thickness', 'pizzalayer' ); ?>', escHtml(state.meta.thickness)); }
			if (state.meta.calories)        { html += reviewRow('<?php esc_html_e( 'Calories', 'pizzalayer' ); ?>', escHtml(state.meta.calories)); }
			if (state.meta.diameter_inches) { html += reviewRow('<?php esc_html_e( 'Diameter', 'pizzalayer' ); ?>', escHtml(state.meta.diameter_inches) + '″'); }
			if (state.meta.spice_level)     { html += reviewRow('<?php esc_html_e( 'Spice level', 'pizzalayer' ); ?>', escHtml(state.meta.spice_level)); }

			var flags = [];
			if (state.meta.is_vegetarian) { flags.push('<?php esc_html_e( 'Vegetarian', 'pizzalayer' ); ?>'); }
			if (state.meta.is_vegan)      { flags.push('<?php esc_html_e( 'Vegan', 'pizzalayer' ); ?>'); }
			if (state.meta.is_gluten_free){ flags.push('<?php esc_html_e( 'Gluten-Free', 'pizzalayer' ); ?>'); }
			if (state.meta.is_dairy_free) { flags.push('<?php esc_html_e( 'Dairy-Free', 'pizzalayer' ); ?>'); }
			if (flags.length) {
				html += reviewRow('<?php esc_html_e( 'Dietary', 'pizzalayer' ); ?>', flags.join(', '));
			}

			if (state.imageUrl) {
				html += reviewRow('<?php esc_html_e( 'Image', 'pizzalayer' ); ?>', '<img src="' + state.imageUrl + '" style="max-height:80px;border-radius:4px;vertical-align:middle;">');
			} else {
				html += reviewRow('<?php esc_html_e( 'Image', 'pizzalayer' ); ?>', '<em><?php esc_html_e( 'None (can be added later)', 'pizzalayer' ); ?></em>');
			}
			html += '</table>';

			$('#plbw-review-card').html(html);
		}
		function reviewRow(label, value) {
			return '<tr><th>' + label + '</th><td>' + value + '</td></tr>';
		}

		/* ── Save ──────────────────────────────────────────── */
		$('#plbw-save-btn').on('click', function(){
			var $btn = $(this);
			$btn.prop('disabled', true);
			$('#plbw-saving-overlay').show();

			var slugVal = state.slug || slugify(state.name);

			$.post(ajaxUrl, {
				action  : 'pizzalayer_wizard_save_layer',
				nonce   : nonce,
				type    : state.typeSlug,
				cpt     : state.typeCpt,
				name    : state.name,
				slug    : slugVal,
				desc    : state.desc,
				price   : state.price,
				image_id: state.imageId,
				meta    : JSON.stringify(state.meta)
			}, function(resp){
				$('#plbw-saving-overlay').hide();
				$btn.prop('disabled', false);

				if (resp.success) {
					showSuccess(resp.data);
				} else {
					alert('<?php esc_html_e( 'Error saving layer:', 'pizzalayer' ); ?> ' + (resp.data && resp.data.message ? resp.data.message : '<?php esc_html_e( 'Unknown error.', 'pizzalayer' ); ?>'));
				}
			}).fail(function(){
				$('#plbw-saving-overlay').hide();
				$btn.prop('disabled', false);
				alert('<?php esc_html_e( 'Network error. Please try again.', 'pizzalayer' ); ?>');
			});
		});

		function showSuccess(data) {
			$('.plbw-panel').hide();
			var html = '';
			html += '<div class="plbw-success-check"><span class="dashicons dashicons-yes-alt"></span></div>';
			html += '<h2 class="plbw-success-title">' + escHtml(data.name) + ' <?php esc_html_e( 'was saved!', 'pizzalayer' ); ?></h2>';
			html += '<p><?php esc_html_e( 'Your new layer has been created. Use the shortcode below to include it on any page.', 'pizzalayer' ); ?></p>';

			html += '<div class="plbw-shortcode-box">';
			html += '<code id="plbw-shortcode-output">' + escHtml(data.shortcode) + '</code>';
			html += '<button type="button" class="button plbw-copy-btn" data-clipboard="' + escAttr(data.shortcode) + '">';
			html += '<span class="dashicons dashicons-clipboard"></span> <?php esc_html_e( 'Copy', 'pizzalayer' ); ?>';
			html += '</button>';
			html += '</div>';

			html += '<div class="plbw-success-actions">';
			html += '<a href="' + escAttr(data.edit_url) + '" class="button button-primary">';
			html += '<span class="dashicons dashicons-edit"></span> <?php esc_html_e( 'Edit Layer', 'pizzalayer' ); ?></a> ';
			html += '<a href="' + escAttr(data.list_url) + '" class="button">';
			html += '<span class="dashicons dashicons-list-view"></span> <?php esc_html_e( 'All', 'pizzalayer' ); ?> ' + escHtml(state.typeLabel + 's') + '</a> ';
			html += '<button type="button" class="button" id="plbw-build-another">';
			html += '<span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e( 'Build Another Layer', 'pizzalayer' ); ?></button>';
			html += '</div>';

			$('#plbw-success-inner').html(html);
			$('#plbw-success-panel').show();
			$('html,body').animate({ scrollTop: 0 }, 200);

			// Update progress to show all done
			$('.plbw-step').addClass('is-done').removeClass('is-active');
		}

		$(document).on('click', '#plbw-build-another', function(){
			// Reset state
			state = { step:1, typeSlug:'', typeLabel:'', typeCpt:'', typeColor:'', typeExtra:[], name:'', slug:'', desc:'', price:'0.00', imageId:0, imageUrl:'', meta:{} };
			$('.plbw-type-card').removeClass('is-selected');
			$('#plbw-name,#plbw-slug,#plbw-description').val('');
			$('#plbw-price').val('0.00');
			$('#plbw-sort-order').val('0');
			$('#plbw-image-id,#plbw-image-url').val('');
			$('#plbw-image-preview').html('<span class="dashicons dashicons-format-image plbw-img-icon"></span><p><?php esc_html_e( 'No image selected', 'pizzalayer' ); ?></p>');
			$('#plbw-remove-image').hide();
			$('#plbw-step1-next').prop('disabled', true);
			$('#plbw-slug').data('manual', false);
			$('.plbw-field-error').removeClass('plbw-field-error');
			$('#plbw-success-panel').hide();
			goStep(1);
		});

		$(document).on('click', '.plbw-copy-btn', function(){
			var text = $(this).data('clipboard');
			if (navigator.clipboard) {
				navigator.clipboard.writeText(text);
			} else {
				var ta = document.createElement('textarea');
				ta.value = text; document.body.appendChild(ta);
				ta.select(); document.execCommand('copy');
				document.body.removeChild(ta);
			}
			$(this).text('<?php esc_html_e( 'Copied!', 'pizzalayer' ); ?>').addClass('plbw-copied');
			var $btn = $(this);
			setTimeout(function(){ $btn.html('<span class="dashicons dashicons-clipboard"></span> <?php esc_html_e( 'Copy', 'pizzalayer' ); ?>').removeClass('plbw-copied'); }, 1800);
		});

		/* ── Helpers ───────────────────────────────────────── */
		function slugify(s) {
			return (s || '').toLowerCase().replace(/[^a-z0-9\s\-]/g,'').replace(/\s+/g,'-').replace(/-+/g,'-').substring(0,60);
		}
		function escHtml(s) {
			return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
		}
		function escAttr(s) { return escHtml(s); }

	})(jQuery);
