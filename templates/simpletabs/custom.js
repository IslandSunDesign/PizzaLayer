var $RequestedMenuItem;
var $PTpopupSlug;

function PTswitchToMenu($RequestedMenuItem){
jQuery('.pizzalayer-ui-menu-tab').fadeOut(100);
jQuery('#pizzalayer-ui-menu-section-' + $RequestedMenuItem).fadeIn(777);
jQuery('button.pizzalayer-icon-menu-item').removeClass('pizzalayer-icon-selected');
jQuery('button#pizzalayer-icon-menu-item-' + $RequestedMenuItem).addClass('pizzalayer-icon-selected');
jQuery('a.pizzalayer-icon-menu-item').removeClass('pizzalayer-icon-selected');
jQuery('a#pizzalayer-icon-menu-item-' + $RequestedMenuItem).addClass('pizzalayer-icon-selected');
jQuery('option.pizzalayer-icon-menu-item').removeAttr('selected');
jQuery('option#pizzalayer-icon-menu-item-' + $RequestedMenuItem).attr('selected','selected');
}

function PTselectToMenu($RequestedMenuItem){
jQuery('.pizzalayer-ui-menu-tab').fadeOut(100);
jQuery('#pizzalayer-ui-menu-section-' + $RequestedMenuItem).fadeIn(777);
}

function PThideMenu(){
jQuery('.pizzalayer-ui-menu-tab').fadeOut(777);
}


function handleDropEvent( event, ui ) {
  var draggable = ui.draggable;
  alert( 'The square with ID "' + draggable.attr('id') + '" was dropped onto me!' );
}

jQuery( document ).ready(function() {
    PTswitchToMenu('intro');
    jQuery("#pizzalayer-main-visualizer-container").css("height", jQuery('#pizzalayer-main-visualizer-container').width());
    jQuery("#pizzalayer-pizza").fadeIn(2500);
    
});

jQuery(window).resize(function(){
  jQuery("#pizzalayer-main-visualizer-container").css("height", jQuery('#pizzalayer-main-visualizer-container').width());
});

function PTtoggleToppingPopup($PTpopupSlug){
jQuery('#pizzalayer-halves-control-popup-' + $PTpopupSlug).slideToggle();
//pizzalayer-halves-control-popup-
}