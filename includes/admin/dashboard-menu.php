<?php
add_action( 'admin_menu', 'pztp_add_admin_menu' );
add_action( 'admin_init', 'pztp_settings_init' );


function pztp_add_admin_menu(  ) { 

add_menu_page( 'PizzaLayer', 'PizzaLayer', 'manage_options', 'pizzalayer', 'pztp_options_page' );
}


add_action('admin_menu', 'wpdocs_register_my_custom_submenu_page');
 
function wpdocs_register_my_custom_submenu_page() {
    add_submenu_page(
        'pizzalayer',
        'Pizzalayer Settings',
        'Pizzalayer Settings.',
        'manage_options',
        'pztp_options_page',
        'pztp_options_page' );
}

function pztp_options_page(  ) { 

		?>
		<form action='options.php' method='post'>

			<h2>PizzaLayer</h2>

			PizzaLayer is mostly configured in Customizer.

		</form>
		<?php

}

