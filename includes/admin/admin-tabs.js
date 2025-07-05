jQuery(document).ready(function($) {
    $('.pizzalayer-tab').on('click', function(e) {
        e.preventDefault();
        var $tab = $(this);
        var cpt = $tab.data('cpt');

        $('.pizzalayer-tab').removeClass('nav-tab-active');
        $tab.addClass('nav-tab-active');

        $('#pizzalayer-tab-content').html('<p>Loading...</p>');

        $.post(pizzalayer_ajax.ajax_url, {
            action: 'pizzalayer_load_cpt_tab',
            cpt: cpt,
            nonce: pizzalayer_ajax.nonce
        }, function(response) {
            if (response.success) {
                $('#pizzalayer-tab-content').html(response.data.html);
            } else {
                $('#pizzalayer-tab-content').html('<p>Error: ' + (response.data && response.data.message ? response.data.message : 'Unable to load content.') + '</p>');
            }
        }).fail(function(xhr) {
            $('#pizzalayer-tab-content').html('<p>Error: ' + xhr.status + ' - ' + xhr.statusText + '</p>');
        });
    });
});
