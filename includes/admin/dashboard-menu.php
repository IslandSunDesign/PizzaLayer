<?php
// Hook to admin_menu to register the custom menu and submenu
add_action('admin_menu', 'pizzalayer_add_admin_menu');

function pizzalayer_add_admin_menu() {
    do_action( 'func_pizzalayer_add_admin_menu_start' );
    // Add main menu page
    add_menu_page(
        'PizzaLayer',         // Page title
        'PizzaLayer',         // Menu title
        'manage_options',             // Capability
        'pizzalayer_main_menu',           // Menu slug
        'pizzalayer_render_dashboard_home_page',  // Function to display content
        'dashicons-chart-pie',            // Icon
        83                            // Position
    );

    /* +===  Submenu: Crusts  ===+ */
add_submenu_page(
    'pizzalayer_main_menu',
    'Crusts',
    'Crusts',
    'manage_options',
    'edit.php?post_type=pizzalayer_crusts'
);

/* +===  Submenu: Sauces  ===+ */
add_submenu_page(
    'pizzalayer_main_menu',
    'Sauces',
    'Sauces',
    'manage_options',
    'edit.php?post_type=pizzalayer_sauces'
);

/* +===  Submenu: Cheeses  ===+ */
add_submenu_page(
    'pizzalayer_main_menu',
    'Cheeses',
    'Cheeses',
    'manage_options',
    'edit.php?post_type=pizzalayer_cheeses'
);

/* +===  Submenu: Toppings  ===+ */
add_submenu_page(
    'pizzalayer_main_menu',
    'Toppings',
    'Toppings',
    'manage_options',
    'edit.php?post_type=pizzalayer_toppings'
);

/* +===  Submenu: Drizzles  ===+ */
add_submenu_page(
    'pizzalayer_main_menu',
    'Drizzles',
    'Drizzles',
    'manage_options',
    'edit.php?post_type=pizzalayer_drizzles'
);

/* +===  Submenu: Cuts  ===+ */
add_submenu_page(
    'pizzalayer_main_menu',
    'Cuts',
    'Cuts',
    'manage_options',
    'edit.php?post_type=pizzalayer_cuts'
);

/* +===  Submenu: Sizes  ===+ */
add_submenu_page(
    'pizzalayer_main_menu',
    'Pizza Sizes',
    'Pizza Sizes',
    'manage_options',
    'edit.php?post_type=pizzalayer_sizes'
);

/* +===  Submenu: Preset Pizzas  ===+ */
add_submenu_page(
    'pizzalayer_main_menu',
    'Preset Pizzas',
    'Preset Pizzas',
    'manage_options',
    'edit.php?post_type=pizzalayer_pizzas'
);

/* +===  Submenu: Shortcode Generator (aka Create Embed Code)  ===+ */
add_submenu_page(
    'pizzalayer_main_menu',    // Parent slug
    'Create Embed Code',                    // Page title
    'Create Embed Code',                    // Menu title
    'manage_options',          // Capability
    'pizzalayer_shortcode_generator',         // Menu slug
    'pztpro_shortcode_generator_page' // Callback function
);


/* +===  Submenu: Help  ===+ */
add_submenu_page(
    'pizzalayer_main_menu',    // Parent slug
    'Help',                    // Page title
    'Help',                    // Menu title
    'manage_options',          // Capability
    'pizzalayer_help',         // Menu slug
    'pizzalayer_render_help_page' // Callback function
);



do_action( 'func_pizzalayer_add_admin_menu_end' );
} // end function

// Callback for the main menu page (can be empty or a placeholder)
function pizzalayer_main_page_callback() {
    do_action( 'func_pizzalayer_main_page_callback_start' );
    echo '<div class="wrap"><h1>PizzaLayer</h1><p>Welcome to the PizzaLayer dashboard.</p></div>';
    do_action( 'func_pizzalayer_main_page_callback_end' );
}

// Callback for the Settings submenu page
function pizzalayer_settings_page_callback() {
    do_action( 'func_pizzalayer_settings_page_callback_start' );
    ?>
    <div class="wrap">
        <form action='options.php' method='post'>
            <h2>PizzaLayer</h2>
            coming soon.
        </form>
    </div>
    <?php
    do_action( 'func_pizzalayer_settings_page_callback_end' );
}

// Callback to display the help page content
function pizzalayer_render_help_page() {
    do_action( 'func_pizzalayer_render_help_page_start' );
    ?>
    <div class="wrap">
        <h1>PizzaLayer Help</h1>
        <p>This is a placeholder for the help documentation. More information will be added soon.</p>
        <ul>
            <li>How to create and manage pizza layers</li>
            <li>Working with crusts, sauces, cheeses, and toppings</li>
            <li>Troubleshooting and support links</li>
        </ul>
    </div>
    <?php
    do_action( 'func_pizzalayer_render_help_page_end' );
}

?>