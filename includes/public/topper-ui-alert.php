<?php
function pizzalayer_alert($pizzalayer_alert_content,$pizzalayer_alert_style){
    if( !$pizzalayer_alert_content ){ return; }
    if( !$pizzalayer_alert_style ){ $pizzalayer_alert_style = 'general'; } //options are: general,error,warning,success
    $pizzalayer_alert_icon = '<i class="fa fa-bell"></i> ';
    return '<div class="pizzalayer-alert pizzalayer-alert-type-' . $pizzalayer_alert_style . '" id="pizzalayer-alert">' . $pizzalayer_alert_icon . '<div id="pizzalayer-alert-content">' . $pizzalayer_alert_content . '</div></div>';
}

function pizzalayer_demo_notice(){
    if( get_option('pizzalayer_setting_settings_demonotice') ){ return '<div class="pizzalayer-ui-menu-col pizzalayer-ui-menu-col-alert col-lg-12 col-md-12">' . get_option('pizzalayer_setting_settings_demonotice') . '</div>'; } else {return ''; };
}