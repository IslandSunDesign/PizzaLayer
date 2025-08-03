<?php

/* +===  Render PizzaLayer Dashboard Panel  ===+ */
function pizzalayer_dashboard_home_tab_panel($pz_panel_slug,$pz_panel_title,$pz_panel_content,$pz_panel_is_active){
    if($pz_panel_is_active == 'yes'){$pz_panel_active_status_css = ' active';} else {$pz_panel_active_status_css = '';};
    return '<!-- +===  Tab Content Area : ' . $pz_panel_title . '  ===+ -->
        <div id="pizzalayer-tab-' . $pz_panel_slug . '" class="pizzalayer-tab-content' . $pz_panel_active_status_css .'">
            <h2>' . $pz_panel_title . '</h2>
            <p>' . $pz_panel_content . '</p>
        </div>
        ';
}
function pizzalayer_dashboard_home_box_section($pz_home_box_title,$pz_home_box_content,$pz_home_box_icon){
return '<div class="pizzalayer-section">
                <h3><span class="dashicons ' . $pz_home_box_icon . '"></span> ' . $pz_home_box_title . '</h3>
                <p>' . $pz_home_box_content . '</p>
            </div>';
}





/* +===  Render PizzaLayer Dashboard Homepage  ===+ */
function pizzalayer_render_dashboard_home_page() {
// TABS CONTENT    
$pz_panel_crusts_content_description = '<p>The foundation of every great pizza starts with the perfect crust. From thin and crispy to thick and fluffy, build your base your way.</p>';
        
$pz_panel_sauces_content_description = '<p>Splash on the flavor with savory sauces that set the tone. Classic tomato, creamy white, or spicy surprises—your pizza’s story starts here.</p>';
        
$pz_panel_cheeses_content_description = '<p>Get gooey with it! Whether you melt, stretch, or crumble, cheese brings the magic to every bite.</p>';
        
$pz_panel_toppings_content_description = '<p>This is where the fun begins—load up your pie with everything from pepperoni to pineapple. Every topping is a personality!</p>';
        
$pz_panel_drizzles_content_description = '<p>Finish strong with a final flourish! Sweet, spicy, or zesty, a drizzle adds that chef’s-kiss moment.</p>';
        
$pz_panel_cuts_content_description = '<p>Shape your masterpiece with precision. Whether squares or slices, how you cut is how you conquer.</p>';
    
    
// BOX : Getting Started
$pizzalayer_dashboard_home_element_gettingstarted = 'Getting started content here.';

// BOX : Tips and Tricks
$pizzalayer_dashboard_home_element_tipsandtricks = 'Tips and tricks content here.';

// BOX : Extending Pizzalayer
$pizzalayer_dashboard_home_element_extendpizzalayer = 'extend pizzalayer/dev help here.';

// OUTPUT
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
            <a href="#pizzalayer-tab-settings" class="nav-tab pizzalayer-open-customizer">Settings <span class="dashicons dashicons-external"></span></a>
        </h2>

        <!-- +===  Tab Content Areas  ===+ -->
        <?php
        echo pizzalayer_dashboard_home_tab_panel('crusts','Crusts',$pz_panel_crusts_content_description,'yes'); 
        echo pizzalayer_dashboard_home_tab_panel('sauces','Sauces',$pz_panel_sauces_content_description,'no');
        echo pizzalayer_dashboard_home_tab_panel('cheeses','Cheeses',$pz_panel_cheeses_content_description,'no'); 
        echo pizzalayer_dashboard_home_tab_panel('toppings','Toppings',$pz_panel_toppings_content_description,'no');
        echo pizzalayer_dashboard_home_tab_panel('drizzles','Drizzles',$pz_panel_drizzles_content_description,'no');
        echo pizzalayer_dashboard_home_tab_panel('cuts','Cuts',$pz_panel_cuts_content_description,'no'); 
        ?>

        <!-- +===  Placeholder Sections  ===+ -->
        <hr>
        <div class="pizzalayer-layout-sections">
        <?php
        echo pizzalayer_dashboard_home_box_section('Getting Started', $pizzalayer_dashboard_home_element_gettingstarted,'dashicons-info');
        echo pizzalayer_dashboard_home_box_section('Tips & Tricks', $pizzalayer_dashboard_home_element_tipsandtricks,'dashicons-admin-tools');
        echo pizzalayer_dashboard_home_box_section('Extend PizzaLayer', $pizzalayer_dashboard_home_element_extendpizzalayer,'dashicons-admin-plugins');
        ?>
        </div>
        
        
    <!-- +=== Responsive Info Boxes Row (3 Columns on Desktop) ===+ -->
<div class="pizzalayer-info-boxes" style="margin-top: 20px; display: flex; flex-wrap: wrap; gap: 20px;">
  <?php
  // Box titles and content (editable individually)
  $boxes = [
    'active_template' => [
      'title'   => 'Active Template',
      'content' => 'You are currently using the default Glassy template. Switch templates using the My Template menu.'
    ],
    'basic_stats' => [
      'title'   => 'Basic Stats',
      'content' => 'PizzaLayer is active on 3 products and 18 custom layers.'
    ],
    'setup_checklist' => [
      'title'   => 'Setup Checklist',
      'content' => 'Complete the setup guide to enable all pizza customization features.'
    ],
  ];

  // Output each box
  foreach ( $boxes as $id => $data ) : ?>
    <div style="
      flex: 1 calc(33.333% - 20px) calc(33.333% - 20px);
      min-width: 280px;
      background: #fff;
      padding: 20px;
      border: 1px solid #ccd0d4;
      box-sizing: border-box;
    ">
      <h2 style="margin-top: 0;"><?php echo esc_html( $data['title'] ); ?></h2>
      <p><?php echo esc_html( $data['content'] ); ?></p>
    </div>
  <?php endforeach; ?>
</div>

  <!-- +=== Two-Column Panel with Video ===+ -->
  <div style="display:flex; gap:20px; margin-top:30px; flex-wrap:wrap;">
    <div style="flex:1; min-width:280px; background:#fff; padding:20px; border:1px solid #ccd0d4;">
      <h2>Getting Started</h2>
      <p>This video will walk you through the basics of using PizzaLayer to build dynamic pizza options.</p>
    </div>
    <div style="width:550px; max-width:100%; background:#fff; padding:10px; border:1px solid #ccd0d4;">
      <iframe width="100%" height="380" src="https://www.youtube.com/embed/VIDEO_ID" frameborder="0" allowfullscreen></iframe>
    </div>
  </div>

  <!-- +=== Full-Width Button Row ===+ -->
  <div style="margin-top:30px; padding:20px 0; border-top:1px solid #ccd0d4; display:flex; gap:10px; flex-wrap:wrap;">
    <a href="#" class="button button-primary">Help</a>
    <a href="#" class="button">Setup Guide</a>
    <a href="#" class="button">Get Embed Code</a>
    <a href="#" class="button">Edit Pizza Layers</a>
  </div>

  <!-- +=== Credits Section ===+ -->
  <div style="margin-top:40px; padding:20px; background:transparent;">
    <h2>Credits</h2>
    <p>PizzaLayer is proudly developed by Ryan Bishop, a WordPress plugin author dedicated to building powerful tools for creative sites.</p>
    <p>For custom plugin work, visit <a href="https://islandsundesign.com" target="_blank">IslandSunDesign.com</a>.</p>
  </div>
  
  
  
</div>

    
    
    <div class="wrap">
        
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