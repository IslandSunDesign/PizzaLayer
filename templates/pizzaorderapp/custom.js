jQuery( document ).ready(function() {
    PTswitchToMenu('intro');
    jQuery("#pizzalayer-main-visualizer-container").css("height", jQuery('#pizzalayer-main-visualizer-container').width());
    jQuery("#pizzalayer-pizza").fadeIn(2500);
    
});

jQuery(window).resize(function(){
  jQuery("#pizzalayer-main-visualizer-container").css("height", jQuery('#pizzalayer-main-visualizer-container').width());
});












/* ======================== NEW JAVASCRIPT ======================== */


/* +=== PZT: Mobile Order Navigation Logic ===+ */
jQuery(document).ready(function() {

    let tabOrder = ['home', 'crust', 'sauce', 'cheese', 'toppings', 'drizzle', 'slicing', 'order'];
    let currentTab = 0;
    let selections = { crust: null, sauce: null, cheese: null, drizzle: null, slicing: null, toppings: [] };

    let tabs = jQuery('.tab-btn');
    let dropdown = jQuery('#pztp-mobileorder-nav-dropdown');
    let contentArea = jQuery('#pztp-mobileorder-content');
    let preview = jQuery('.pztp-live-preview-placeholder');
    let orderSummary = jQuery('#order-summary');
    let toppingsCSV = jQuery('#toppings-csv');
    let nextBtn = jQuery('#next-btn');
    let prevBtn = jQuery('#prev-btn');

    function showTab(name) {
        jQuery('.tab-content').removeClass('active');
        jQuery('#tab-' + name).addClass('active');

        tabs.each(function() {
            let t = jQuery(this);
            t.toggleClass('active', t.data('tab') === name);
        });

        dropdown.val(name);
        currentTab = tabOrder.indexOf(name);

        prevBtn.css('display', currentTab === 0 ? 'none' : 'inline-block');
        nextBtn.text(name === 'home' ? 'Start Building my Pizza' : 'Next');
        nextBtn.css('display', name === 'order' ? 'none' : 'inline-block');

        if (name === 'order') {
            populateOrder();
        }
    }

    tabs.on('click', function() {
        showTab(jQuery(this).data('tab'));
    });

    dropdown.on('change', function() {
        showTab(jQuery(this).val());
    });

    nextBtn.on('click', function() {
        showTab(tabOrder[currentTab + 1] || tabOrder[currentTab]);
    });

    prevBtn.on('click', function() {
        showTab(tabOrder[currentTab - 1] || tabOrder[currentTab]);
    });

    contentArea.on('click', '.add-remove-btn', function() {
        let btn = jQuery(this);
        let card = btn.closest('.option-card');
        let layer = card.data('layer');
        let title = card.data('title');

        if (layer === 'toppings') {
            card.toggleClass('selected');
            if (card.hasClass('selected')) {
                selections.toppings.push(title);
                btn.text('Remove');
            } else {
                selections.toppings = selections.toppings.filter(t => t !== title);
                btn.text('Add');
            }
            toppingsCSV.text('Toppings: ' + selections.toppings.join(','));
        } else {
            jQuery(`.option-card[data-layer="${layer}"]`).removeClass('selected').find('.add-remove-btn').text('Add');
            card.addClass('selected');
            btn.text('Remove');
            selections[layer] = title;
        }

        let circle = card.find('.option-circle');
        let clone = circle.clone();
        let r = circle[0].getBoundingClientRect();
        let p = preview[0].getBoundingClientRect();

        clone.css({
            position: 'fixed',
            left: r.left + 'px',
            top: r.top + 'px',
            width: r.width + 'px',
            height: r.height + 'px',
            transition: 'all 0.8s ease'
        });

        jQuery('body').append(clone);

        requestAnimationFrame(function() {
            clone.css({
                left: (p.left + p.width / 2 - r.width / 2) + 'px',
                top: (p.top + p.height / 2 - r.height / 2) + 'px',
                transform: 'scale(0.2)',
                opacity: '0'
            });
        });

        setTimeout(function() {
            clone.remove();
        }, 800);
    });

    function populateOrder() {
        orderSummary.empty();
        toppingsCSV.text('');

        ['crust', 'sauce', 'cheese', 'drizzle', 'slicing'].forEach(function(l) {
            if (selections[l]) {
                let box = jQuery('<div>').addClass('order-box');
                let label = jQuery('<label>').text(l.charAt(0).toUpperCase() + l.slice(1));
                let input = jQuery('<input>').val(selections[l]).prop('readonly', true);
                box.append(label, input);
                orderSummary.append(box);
            }
        });

        toppingsCSV.text('Toppings: ' + selections.toppings.join(','));
    }

    showTab('home');

});




