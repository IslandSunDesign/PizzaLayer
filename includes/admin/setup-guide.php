<?php
/* +===  Render the dashboard intro page  ===+ */
function pizzalayer_render_dashboard_intro_page() {
    ?>
    <div class="wrap">
        <h1><span class="dashicons dashicons-pizza" style="color:#d63638;"></span> Welcome to PizzaLayer</h1>
        <p class="description">Thanks for installing PizzaLayer! Below you'll find information about how to configure and use each section.</p>

        <!-- +=== WordPress Admin Tabs ===+ -->
        <h2 class="nav-tab-wrapper" id="pizzalayer-tabs">
            <a href="#" class="nav-tab nav-tab-active" data-tab="crusts">Crusts</a>
            <a href="#" class="nav-tab" data-tab="sauces">Sauces</a>
            <a href="#" class="nav-tab" data-tab="cheeses">Cheeses</a>
            <a href="#" class="nav-tab" data-tab="toppings">Toppings</a>
            <a href="#" class="nav-tab" data-tab="drizzles">Drizzles</a>
            <a href="#" class="nav-tab" data-tab="cuts">Cuts</a>
            <a href="#" class="nav-tab" data-tab="settings">Settings</a>
        </h2>

        <!-- +=== Tab Content Sections ===+ -->
        <div class="pizzalayer-tab-content" id="pizzalayer-tab-crusts">
            <h2>Crusts</h2>
            <p>Welcome to the crust builder! Here you’ll define all the delicious bases your pizzas can start with. Thin, thick, gluten-free – whatever your customers crave.</p>
            <p>Crusts can be managed from the “Crusts” menu under the PizzaLayer section. Add pricing, images, and variations as needed.</p>
        </div>

        <div class="pizzalayer-tab-content" id="pizzalayer-tab-sauces" style="display:none;">
            <h2>Sauces</h2>
            <p>Sauces are the flavor foundation. From marinara to pesto, you can define default choices and link them with specific crusts or toppings if needed.</p>
            <p>Use categories to group them for customers to choose more easily on the frontend.</p>
        </div>

        <div class="pizzalayer-tab-content" id="pizzalayer-tab-cheeses" style="display:none;">
            <h2>Cheeses</h2>
            <p>Not just mozzarella! Add cheeses as their own category for more flexibility. They can be required, optional, or multi-choice.</p>
            <p>You can assign cheeses to different product types or restrict by crust type.</p>
        </div>

        <div class="pizzalayer-tab-content" id="pizzalayer-tab-toppings" style="display:none;">
            <h2>Toppings</h2>
            <p>Toppings are the heart of your pizza offering. Pepperoni, mushrooms, pineapple – it’s all possible here. Toppings can include price, stock control, and even conditional logic.</p>
            <p>Add new toppings via the “Toppings” menu, or attach custom images and descriptions using ACF or custom meta fields.</p>
        </div>

        <div class="pizzalayer-tab-content" id="pizzalayer-tab-drizzles" style="display:none;">
            <h2>Drizzles</h2>
            <p>Drizzles add a finishing touch — balsamic glaze, garlic oil, ranch swirl. They bring extra flavor and fun to every pizza creation.</p>
            <p>Manage drizzles like toppings, with images and prices if needed. Great for upselling and customer delight.</p>
        </div>

        <div class="pizzalayer-tab-content" id="pizzalayer-tab-cuts" style="display:none;">
            <h2>Cuts</h2>
            <p>Give your customers control over how their pizza is sliced. Square, triangle, or left whole — define the options here.</p>
            <p>These are simple display preferences but can help with customization UX.</p>
        </div>

        <div class="pizzalayer-tab-content" id="pizzalayer-tab-settings" style="display:none;">
            <h2>Settings</h2>
            <p>To further configure PizzaLayer, open the Customizer where additional options may appear depending on your theme and setup.</p>
            <a href="<?php echo admin_url('customize.php'); ?>" target="_blank" class="button button-primary">Open Customizer</a>
        </div>

        <!-- +=== JavaScript to Handle Tabs ===+ -->
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const tabs = document.querySelectorAll('#pizzalayer-tabs .nav-tab');
                const contents = document.querySelectorAll('.pizzalayer-tab-content');

                tabs.forEach(tab => {
                    tab.addEventListener('click', function (e) {
                        e.preventDefault();

                        // Remove active class from all tabs
                        tabs.forEach(t => t.classList.remove('nav-tab-active'));

                        // Hide all content
                        contents.forEach(c => c.style.display = 'none');

                        // Activate current tab
                        tab.classList.add('nav-tab-active');

                        // Show matching content
                        const tabId = 'pizzalayer-tab-' + tab.dataset.tab;
                        const contentEl = document.getElementById(tabId);
                        if (contentEl) {
                            contentEl.style.display = 'block';
                        }
                    });
                });
            });
        </script>
    </div>
    <?php
}
