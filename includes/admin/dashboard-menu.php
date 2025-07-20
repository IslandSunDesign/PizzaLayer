<?php
add_action('admin_menu', 'pizzalayer_add_admin_menu');
add_action('admin_enqueue_scripts', 'pizzalayer_enqueue_admin_assets');
add_action('wp_ajax_pizzalayer_load_cpt_tab', 'pizzalayer_load_cpt_tab');

function pizzalayer_add_admin_menu() {
    add_menu_page(
        'PizzaLayer',                // Page title (appears on the page itself)
        'PizzaLayer',                // Menu title (appears in the sidebar)
        'manage_options',
        'pizzalayer_main_menu',
        'pizzalayer_render_dashboard_home_page',
        'dashicons-chart-pie',
        83
    );

    // +-- Home submenu (matches parent) --+
    add_submenu_page(
        'pizzalayer_main_menu',
        'PizzaLayer',
        'Home',
        'manage_options',
        'pizzalayer_main_menu',
        'pizzalayer_render_dashboard_home_page'
    );

    // +-- Custom admin pages --+
    add_submenu_page(
        'pizzalayer_main_menu',
        'Edit Pizza Layers',
        'Edit Pizza Layers',
        'manage_options',
        'pizzalayer_layers',
        'pizzalayer_render_layers_page'
    );

    add_submenu_page(
        'pizzalayer_main_menu',
        'Get Embed Code',
        'Get Embed Code',
        'manage_options',
        'pizzalayer_shortcode_generator',
        'pztpro_shortcode_generator_page'
    );

    add_submenu_page(
        'pizzalayer_main_menu',
        'Help',
        'Help',
        'manage_options',
        'pizzalayer_help',
        'pizzalayer_render_help_page'
    );
    
      add_submenu_page(
        'pizzalayer_main_menu',                // Parent slug
        'PizzaLayer Setup Guide',                   // Page title
        'Setup Guide',                              // Menu title
        'manage_options',                     // Capability
        'pizzalayer_intro',                   // Menu slug
        'pizzalayer_render_dashboard_intro_page' // Callback function
    );

    // +-- Separator --+
    add_submenu_page(
        'pizzalayer_main_menu',
        '', // Hidden title
        '──────────', // Visual separator
        'read',
        'pizzalayer_separator',
        '__return_null'
    );

    // +-- CPT Links --+
    foreach (get_post_types(array('_builtin' => false), 'objects') as $cpt) {
        if (strpos($cpt->name, 'pizzalayer_') === 0) {
            $label = $cpt->labels->name ?: ucfirst(str_replace('pizzalayer_', '', $cpt->name));
            add_submenu_page(
                'pizzalayer_main_menu',
                $label,
                $label,
                'edit_posts',
                'edit.php?post_type=' . $cpt->name,
                '' // No callback needed; handled by WordPress
            );
        }
    }
}


function pizzalayer_enqueue_admin_assets($hook) {
    if (strpos($hook, 'pizzalayer_layers') === false) return;

    wp_enqueue_script('jquery');
    wp_enqueue_script('thickbox');
    wp_enqueue_style('thickbox');

    wp_enqueue_script(
        'pizzalayer-admin-tabs',
        plugin_dir_url(__FILE__) . 'admin-tabs.js',
        ['jquery', 'thickbox'],
        null,
        true
    );

    wp_localize_script('pizzalayer-admin-tabs', 'pizzalayer_ajax',
        [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('pizzalayer_ajax_nonce'),
        ]
    );

    wp_enqueue_style(
        'pizzalayer-admin-tabs-css',
        plugin_dir_url(__FILE__) . 'admin-tabs.css'
    );
}

function pizzalayer_render_layers_page() {
    $cpts = [
        'pizzalayer_crusts'   => 'Crusts',
        'pizzalayer_sauces'   => 'Sauces',
        'pizzalayer_cheeses'  => 'Cheeses',
        'pizzalayer_toppings' => 'Toppings',
        'pizzalayer_drizzles' => 'Drizzles',
        'pizzalayer_cuts'     => 'Cuts',
        'pizzalayer_pizzas'   => 'Preset Pizzas',
    ];
    ?>
    <div class="wrap">
        <h1>Pizza Layers</h1>
        <p>Manage your crusts, sauces, cheeses, toppings, and more using the tabs below. Click a tab to view and manage each layer without leaving this page.</p>

        <h2 class="nav-tab-wrapper">
            <?php foreach ($cpts as $slug => $label): ?>
                <a href="#" class="nav-tab pizzalayer-tab" data-cpt="<?php echo esc_attr($slug); ?>">
                    <?php echo esc_html($label); ?>
                </a>
            <?php endforeach; ?>
        </h2>

        <div id="pizzalayer-tab-content" style="margin-top:20px;">
            <p>Select a layer type tab to load and manage its entries here.</p>
        </div>
    </div>
    <?php
}

function pizzalayer_load_cpt_tab() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pizzalayer_ajax_nonce')) {
        wp_send_json_error(['message' => 'Nonce verification failed'], 403);
    }

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorized'], 403);
    }

    $cpt = sanitize_text_field($_POST['cpt'] ?? '');
    if (!$cpt || !post_type_exists($cpt)) {
        wp_send_json_error(['message' => 'Invalid CPT'], 400);
    }

    $paged = isset($_POST['paged']) ? max(1, intval($_POST['paged'])) : 1;
    $search = sanitize_text_field($_POST['search'] ?? '');

    $args = [
        'post_type'      => $cpt,
        'post_status'    => 'any',
        'posts_per_page' => 100,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'paged'          => $paged,
    ];

    if ($search) {
        $args['s'] = $search;
    }

    $query = new WP_Query($args);

    ob_start();

    echo '<div style="margin-bottom:10px;">';
    echo '<a href="#TB_inline?width=400&height=200&inlineId=pizzalayer-quick-add-modal" class="button button-primary thickbox" data-cpt="' . esc_attr($cpt) . '" id="pizzalayer-quick-add-button">Add New</a> ';
    echo '<a href="' . esc_url(admin_url('edit.php?post_type=' . $cpt)) . '" class="button">View All in WP</a>';
    echo '</div>';

    echo '<div style="margin-bottom:10px;">';
    echo '<input type="text" id="pizzalayer-search-input" placeholder="Search..." style="width: 300px;" value="' . esc_attr($search) . '"> ';
    echo '<button id="pizzalayer-search-button" class="button">Search</button>';
    echo '</div>';

    if ($query->have_posts()) {
   echo '<table class="widefat striped"><thead><tr><th>Title</th><th>Date</th><th>Actions</th></tr></thead><tbody>';
while ($query->have_posts()) {
    $query->the_post();
    $edit_link = get_edit_post_link(get_the_ID());
    echo '<tr>';
    echo '<td>' . esc_html(get_the_title()) . '</td>';
    echo '<td>' . esc_html(get_the_date()) . '</td>';
    echo '<td>
        <a href="' . esc_url($edit_link) . '" class="button button-small">Edit</a> 
        <button class="button button-small pizzalayer-delete-item" data-post-id="' . get_the_ID() . '">Delete</button>
    </td>';
    echo '</tr>';
}
echo '</tbody></table>';
echo '<div id="pizzalayer-quick-add-modal" style="display:none;">
    <h2>Add New Item</h2>
    <p><input type="text" id="pizzalayer-quick-add-title" placeholder="Enter title..." style="width:90%;"></p>
    <p>
        <button class="button button-primary" id="pizzalayer-quick-add-save">Save</button>
        <button class="button" id="pizzalayer-quick-add-cancel">Cancel</button>
    </p>
    <p id="pizzalayer-quick-add-status"></p>
</div>
<?php
';

        // Pagination
        $total_pages = $query->max_num_pages;
        echo '<div style="margin-top:10px;">';
        if ($paged > 1) {
            echo '<button class="button pizzalayer-pagination" data-page="' . ($paged - 1) . '">&laquo; Previous</button> ';
        }
        if ($paged < $total_pages) {
            echo '<button class="button pizzalayer-pagination" data-page="' . ($paged + 1) . '">Next &raquo;</button>';
        }
        echo '</div>';
    } else {
        echo '<p>No entries found for this layer.</p>';
    }

    wp_reset_postdata();

    $html = ob_get_clean();

    wp_send_json_success(['html' => $html]);
}



function pizzalayer_render_help_page() {
    ?>
    <div class="wrap">
        <h1>PizzaLayer Help</h1>
        <p>Here you will find help on how to manage your pizza layers and use the plugin effectively:</p>
        <ul>
            <li>Manage layers from the Layers tabbed interface</li>
            <li>Use the shortcode generator to display pizzas on your site</li>
            <li>Reach out to support if you need help</li>
        </ul>
    </div>
    <?php
}


add_action('wp_ajax_pizzalayer_quick_add_item', 'pizzalayer_quick_add_item');

function pizzalayer_quick_add_item() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pizzalayer_ajax_nonce')) {
        wp_send_json_error(['message' => 'Nonce verification failed'], 403);
    }

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorized'], 403);
    }

    $cpt = sanitize_text_field($_POST['cpt'] ?? '');
    $title = sanitize_text_field($_POST['title'] ?? '');

    if (!$cpt || !post_type_exists($cpt)) {
        wp_send_json_error(['message' => 'Invalid CPT'], 400);
    }

    if (empty($title)) {
        wp_send_json_error(['message' => 'Title is required'], 400);
    }

    $new_post = [
        'post_title'   => $title,
        'post_status'  => 'publish',
        'post_type'    => $cpt,
    ];

    $post_id = wp_insert_post($new_post);

    if (is_wp_error($post_id) || !$post_id) {
        wp_send_json_error(['message' => 'Failed to add item'], 500);
    }

    wp_send_json_success(['message' => 'Item added successfully']);
}

add_action('wp_ajax_pizzalayer_quick_delete_item', 'pizzalayer_quick_delete_item');

function pizzalayer_quick_delete_item() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pizzalayer_ajax_nonce')) {
        wp_send_json_error(['message' => 'Nonce verification failed'], 403);
    }

    if (!current_user_can('delete_posts')) {
        wp_send_json_error(['message' => 'Unauthorized'], 403);
    }

    $post_id = intval($_POST['post_id'] ?? 0);

    if (!$post_id || get_post_status($post_id) === false) {
        wp_send_json_error(['message' => 'Invalid post ID'], 400);
    }

    $result = wp_delete_post($post_id, true);

    if ($result === false) {
        wp_send_json_error(['message' => 'Failed to delete item'], 500);
    }

    wp_send_json_success(['message' => 'Item deleted successfully']);
}


?>
