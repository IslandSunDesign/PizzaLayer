jQuery(document).ready(function($) {
    let currentCPT = '';

    $('.pizzalayer-tab').on('click', function(e) {
        e.preventDefault();
        var $tab = $(this);
        var cpt = $tab.data('cpt');
        currentCPT = cpt;

        $('.pizzalayer-tab').removeClass('nav-tab-active');
        $tab.addClass('nav-tab-active');

        loadLayerContent(cpt);
    });

    function loadLayerContent(cpt, paged = 1, search = '') {
        $('#pizzalayer-tab-content').html('<p>Loading...</p>');

        $.post(pizzalayer_ajax.ajax_url, {
            action: 'pizzalayer_load_cpt_tab',
            cpt: cpt,
            nonce: pizzalayer_ajax.nonce,
            paged: paged,
            search: search
        }, function(response) {
            if (response.success) {
                $('#pizzalayer-tab-content').html(response.data.html);

                $('.pizzalayer-pagination').on('click', function(e) {
                    e.preventDefault();
                    var nextPage = $(this).data('page');
                    loadLayerContent(cpt, nextPage, $('#pizzalayer-search-input').val());
                });

                $('#pizzalayer-search-button').on('click', function() {
                    loadLayerContent(cpt, 1, $('#pizzalayer-search-input').val());
                });
            } else {
                $('#pizzalayer-tab-content').html('<p>Error: ' + (response.data && response.data.message ? response.data.message : 'Unable to load content.') + '</p>');
            }
        }).fail(function(xhr) {
            $('#pizzalayer-tab-content').html('<p>Error: ' + xhr.status + ' - ' + xhr.statusText + '</p>');
        });
    }

    $(document).on('click', '#pizzalayer-quick-add-save', function() {
        var title = $('#pizzalayer-quick-add-title').val();
        $('#pizzalayer-quick-add-status').text('Saving...');

        $.post(pizzalayer_ajax.ajax_url, {
            action: 'pizzalayer_quick_add_item',
            cpt: currentCPT,
            title: title,
            nonce: pizzalayer_ajax.nonce
        }, function(response) {
            if (response.success) {
                $('#pizzalayer-quick-add-status').text('Item added!');
                $('#pizzalayer-quick-add-title').val('');
                loadLayerContent(currentCPT);
                setTimeout(function() { tb_remove(); }, 1000);
            } else {
                $('#pizzalayer-quick-add-status').text('Error: ' + (response.data && response.data.message ? response.data.message : 'Unable to add item.'));
            }
        }).fail(function(xhr) {
            $('#pizzalayer-quick-add-status').text('Error: ' + xhr.status + ' - ' + xhr.statusText);
        });
    });

    $(document).on('click', '#pizzalayer-quick-add-cancel', function() {
        tb_remove();
    });
    
    $(document).on('click', '.pizzalayer-delete-item', function(e) {
    e.preventDefault();
    var postId = $(this).data('post-id');

    if (!confirm('Are you sure you want to delete this item? This cannot be undone.')) {
        return;
    }

    $('#pizzalayer-tab-content').prepend('<p>Deleting item...</p>');

    $.post(pizzalayer_ajax.ajax_url, {
        action: 'pizzalayer_quick_delete_item',
        post_id: postId,
        nonce: pizzalayer_ajax.nonce
    }, function(response) {
        if (response.success) {
            loadLayerContent(currentCPT);
        } else {
            alert('Error: ' + (response.data && response.data.message ? response.data.message : 'Unable to delete item.'));
        }
    }).fail(function(xhr) {
        alert('Error: ' + xhr.status + ' - ' + xhr.statusText);
    });
});
});
