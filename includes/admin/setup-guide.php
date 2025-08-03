<?php

function pizzalayer_render_dashboard_intro_page_layer_guide($pizzalayer_intro_section_cpt_basic,$pizzalayer_intro_section_cpt_example_title){
    return '<h2><span class="dashicons dashicons-list-view"></span> 2. Add ' . $pizzalayer_intro_section_cpt_basic . ' Layers</h2>
    <ol>
      <li>Go to <strong>PizzaLayer &gt; Add ' . $pizzalayer_intro_section_cpt_basic . '</strong></li>
      <li>Enter a title, e.g., <code>' . $pizzalayer_intro_section_cpt_example_title . '</code></li>
      <li>Upload a layer image using <strong>Featured Image</strong> or <code>topping_layer_image</code> ACF field</li>
      <li>Fill in optional description</li>
      <li>Fill in the layer details below the description.</li>
      <li>Fill in the <strong>Price Grid</strong> box and add size/fraction pricing rows</li>
      <li>Click <strong>Publish</strong></li>
    </ol>';
}



/* +===  Render the dashboard intro page  ===+ */
function pizzalayer_render_dashboard_intro_page() {
    ?>
    <div class="wrap">
        <h1><span class="dashicons dashicons-pizza" style="color:#d63638;"></span> Setup Guide</h1>
        <p class="description">Thanks for installing PizzaLayer! Below you'll find information about how to configure and use each section.</p>

        <!-- +=== Meta Box Container ===+ -->
        <div class="postbox" style="padding: 20px; background: #fff; border: 1px solid #ccd0d4;">

            <!-- +=== WordPress Admin Tabs ===+ -->
            <h2 class="nav-tab-wrapper" id="pizzalayer-tabs">
                <a href="#" class="nav-tab nav-tab-active" data-tab="checklist">Setup Checklist</a>
                <a href="#" class="nav-tab" data-tab="crusts">Crusts</a>
                <a href="#" class="nav-tab" data-tab="sauces">Sauces</a>
                <a href="#" class="nav-tab" data-tab="cheeses">Cheeses</a>
                <a href="#" class="nav-tab" data-tab="toppings">Toppings</a>
                <a href="#" class="nav-tab" data-tab="drizzles">Drizzles</a>
                <a href="#" class="nav-tab" data-tab="cuts">Cuts</a>
                <a href="#" class="nav-tab" data-tab="pizzas">Pizzas</a>
                <a href="#" class="nav-tab" data-tab="settings">Settings</a>
            </h2>

            <!-- +=== Checklist Tab ===+ -->
            <div class="pizzalayer-tab-content" id="pizzalayer-tab-checklist">
                <h2><span class="dashicons dashicons-lightbulb"></span> Summary Checklist</h2>
                <div style="display: flex; flex-wrap: wrap; gap: 20px;">
                    <?php
                    $items = [
                        'Install & Activate PizzaLayer',
                        'Add entries for all layers (Crust, Sauce, etc.)',
                        'Configure pricing in Price Grid',
                        'Create WooCommerce “Pizza” product',
                        'Insert builder shortcode into a page',
                        'Test the end-to-end ordering process',
                    ];
                    foreach ( $items as $item ) {
                        $icon = ( rand(0,1) ? 'yes' : 'checkbox' );
                        echo '<div style="flex:1 1 45%; background:#f6f7f7; padding:15px; border:1px solid #ccd0d4; border-left:5px solid #0073aa;">';
                        echo '<span class="dashicons dashicons-' . $icon . '" style="float:left; margin-right:10px;"></span>';
                        echo '<p style="margin-left:30px;">' . esc_html($item) . '</p>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>

            <?php
            // +=== Helper: Reusable CPT tab rendering ===+
            function pizzalayer_render_layer_tab($id, $label, $desc, $sample, $cpt_slug) {
                ?>
                <div class="pizzalayer-tab-content" id="pizzalayer-tab-<?php echo esc_attr($id); ?>" style="display:none;">
                    <h2><?php echo esc_html($label); ?></h2>
                    <p><?php echo esc_html($desc); ?></p>
                    <p><?php echo pizzalayer_render_dashboard_intro_page_layer_guide($id, $sample); ?></p>

                    <!-- +=== Quick Links Footer ===+ -->
                    <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #ddd;">
                        <h3>Quick Links</h3>
                        <a href="<?php echo admin_url("edit.php?post_type=pizzalayer_{$cpt_slug}"); ?>" class="button"><span class="dashicons dashicons-admin-home"></span> View All</a>
                        <a href="<?php echo admin_url("post-new.php?post_type=pizzalayer_{$cpt_slug}"); ?>" class="button button-primary"><span class="dashicons dashicons-plus-alt2"></span> Add New</a>
                    </div>
                </div>
                <?php
            }

            pizzalayer_render_layer_tab('crusts', 'Crusts', 'Welcome to the crust builder! Here you’ll define all the delicious bases your pizzas can start with.', 'Thin Crust', 'crusts');
            pizzalayer_render_layer_tab('sauces', 'Sauces', 'Sauces are the flavor foundation. From marinara to pesto, define default choices here.', 'Red Sauce', 'sauces');
            pizzalayer_render_layer_tab('cheeses', 'Cheeses', 'Add cheeses as their own category for flexibility. They can be required or optional.', 'Mozzarella', 'cheeses');
            pizzalayer_render_layer_tab('toppings', 'Toppings', 'Toppings are the heart of your pizza offering. Include price, stock, or conditional logic.', 'Pepperoni', 'toppings');
            pizzalayer_render_layer_tab('drizzles', 'Drizzles', 'Add finishing touches like balsamic glaze or ranch swirl.', 'Ranch Sauce', 'drizzles');
            pizzalayer_render_layer_tab('cuts', 'Cuts', 'Define how your pizza is sliced. Square, triangle, or left whole.', '8 slices', 'cuts');
            ?>

            <!-- +=== Preset Pizzas Tab ===+ -->
            <div class="pizzalayer-tab-content" id="pizzalayer-tab-pizzas" style="display:none;">
                <h2>Preset Pizzas</h2>
                <ol>
                    <li>Go to <strong>Products > Add New</strong></li>
                    <li>Set product type to <strong>Pizza</strong></li>
                    <li>Fill in the <strong>Pizza Details</strong> tab</li>
                    <li>Click <strong>Publish</strong></li>
                </ol>
            </div>

            <!-- +=== Settings Tab ===+ -->
            <div class="pizzalayer-tab-content" id="pizzalayer-tab-settings" style="display:none;">
                <h2>Settings</h2>
                <p>Use the Customizer to adjust your PizzaLayer setup depending on your template.</p>
                <a href="<?php echo admin_url('customize.php'); ?>" target="_blank" class="button button-primary">Open Customizer</a>
            </div>
        </div><!-- end .postbox -->

        <!-- +=== JavaScript to Handle Tabs ===+ -->
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const tabs = document.querySelectorAll('#pizzalayer-tabs .nav-tab');
                const contents = document.querySelectorAll('.pizzalayer-tab-content');
                tabs.forEach(tab => {
                    tab.addEventListener('click', function (e) {
                        e.preventDefault();
                        tabs.forEach(t => t.classList.remove('nav-tab-active'));
                        contents.forEach(c => c.style.display = 'none');
                        tab.classList.add('nav-tab-active');
                        const tabId = 'pizzalayer-tab-' + tab.dataset.tab;
                        const contentEl = document.getElementById(tabId);
                        if (contentEl) contentEl.style.display = 'block';
                    });
                });
            });
        </script>
    </div>
    <?php
}

