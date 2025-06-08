<?php
/* +===  Render PizzaLayer Dashboard Homepage  ===+ */
function pizzalayer_render_dashboard_home_page() {
    ?>
    <div class="wrap">
        <h1><span class="dashicons dashicons-pizza"></span> Welcome to PizzaLayer</h1>
        <p class="description">The ultimate toolkit for building and managing custom pizza layers with WordPress + WooCommerce.</p>

        <!-- +===  Tab Menu  ===+ -->
        <h2 class="nav-tab-wrapper pizzalayer-tabs">
            <a href="#pizzalayer-tab-crusts" class="nav-tab nav-tab-active">Crusts</a>
            <a href="#pizzalayer-tab-sauces" class="nav-tab">Sauces</a>
            <a href="#pizzalayer-tab-cheeses" class="nav-tab">Cheeses</a>
            <a href="#pizzalayer-tab-toppings" class="nav-tab">Toppings</a>
            <a href="#pizzalayer-tab-drizzles" class="nav-tab">Drizzles</a>
            <a href="#pizzalayer-tab-cuts" class="nav-tab">Cuts</a>
            <a href="#pizzalayer-tab-settings" class="nav-tab pizzalayer-open-customizer">Settings</a>
        </h2>

        <!-- +===  Tab Content Areas  ===+ -->
        <div id="pizzalayer-tab-crusts" class="pizzalayer-tab-content active">
            <h2>Crusts</h2>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla condimentum nisi a turpis fermentum, ut accumsan velit tincidunt.</p>
            <p>Curabitur dignissim feugiat nisi, nec efficitur sapien malesuada a. Donec tincidunt commodo neque, ut facilisis justo.</p>
        </div>

        <div id="pizzalayer-tab-sauces" class="pizzalayer-tab-content">
            <h2>Sauces</h2>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium.</p>
            <p>Fusce ac ligula nec velit sollicitudin porttitor. In hac habitasse platea dictumst.</p>
        </div>

        <div id="pizzalayer-tab-cheeses" class="pizzalayer-tab-content">
            <h2>Cheeses</h2>
            <p>Various cheese styles from all over the world can be layered onto your pizza. Mozzarella, cheddar, and beyond!</p>
            <p>Donec laoreet bibendum risus, in viverra ipsum finibus vel.</p>
        </div>

        <div id="pizzalayer-tab-toppings" class="pizzalayer-tab-content">
            <h2>Toppings</h2>
            <p>Add meats, vegetables, and more! Quisque pretium, sapien non tempus gravida, magna elit ultricies sem, at sagittis nisi justo sed purus.</p>
        </div>
        
        <div id="pizzalayer-tab-drizzles" class="pizzalayer-tab-content">
            <h2>Drizzles</h2>
            <p>Add a little extra with Drizzles - always on top.</p>
        </div>

        <div id="pizzalayer-tab-cuts" class="pizzalayer-tab-content">
            <h2>Cuts</h2>
            <p>Customize the way your pizza is sliced and served with visual options for triangle, square, or custom layouts.</p>
        </div>
        
     

        <!-- +===  Placeholder Sections  ===+ -->
        <hr>
        <div class="pizzalayer-layout-sections">
            <div class="pizzalayer-section">
                <h3><span class="dashicons dashicons-info"></span> Getting Started</h3>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec odio. Praesent libero.</p>
            </div>
            <div class="pizzalayer-section">
                <h3><span class="dashicons dashicons-admin-tools"></span> Tips & Tricks</h3>
                <p>Sed cursus ante dapibus diam. Sed nisi. Nulla quis sem at nibh elementum imperdiet.</p>
            </div>
            <div class="pizzalayer-section">
                <h3><span class="dashicons dashicons-admin-plugins"></span> Extend PizzaLayer</h3>
                <p>Maecenas mattis. Sed convallis tristique sem. Proin ut ligula vel nunc egestas porttitor.</p>
            </div>
        </div>
    </div>

    <!-- +===  Tabbed Navigation Script  ===+ -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const tabs = document.querySelectorAll(".pizzalayer-tabs a");
            const contents = document.querySelectorAll(".pizzalayer-tab-content");

            tabs.forEach(tab => {
                tab.addEventListener("click", function (e) {
                    e.preventDefault();

                    if (this.classList.contains("pizzalayer-open-customizer")) {
                        window.open("<?php echo admin_url('customize.php'); ?>", "_blank");
                        return;
                    }

                    tabs.forEach(t => t.classList.remove("nav-tab-active"));
                    contents.forEach(c => c.classList.remove("active"));

                    this.classList.add("nav-tab-active");
                    const target = document.querySelector(this.getAttribute("href"));
                    if (target) {
                        target.classList.add("active");
                    }
                });
            });
        });
    </script>

    <!-- +===  Basic Styling for Layout  ===+ -->
    <style>
        .pizzalayer-tab-content {
            display: none;
            background: #fff;
            border: 1px solid #ccd0d4;
            padding: 20px;
            margin-top: -1px;
        }

        .pizzalayer-tab-content.active {
            display: block;
        }

        .pizzalayer-layout-sections {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 40px;
        }

        .pizzalayer-section {
            background: #f8f9fa;
            border: 1px solid #e1e4e8;
            padding: 20px;
            border-radius: 6px;
        }

        .pizzalayer-section h3 {
            margin-top: 0;
        }
    </style>
    <?php
}