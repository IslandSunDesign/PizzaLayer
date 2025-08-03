<?php
$pizzalayer_template_images_directory = plugin_dir_url(__FILE__) .'images/';
do_action( 'pizzalayer_file_pztp-containers-presentation_start' );

/* =============================================
PIZZALAYER : front-end UI */
function pizzalayer_toppings_visualizer_func( $atts ){
global $pizzalayer_template_name;
do_action( 'func_pizzalayer_toppings_visualizer_func_before' );
if(!isset($atts['id'])){ $atts['id'] = ''; };
if(!isset($atts['crust'])){ $atts['crust'] = ''; };
if(!isset($atts['sauce'])){ $atts['sauce'] = ''; };
if(!isset($atts['toppings'])){ $atts['toppings'] = ''; };

/* ============= GET USER OPTIONS ============= */
$pizzalayer_template_glassy_option_topping_maxtoppings = get_option('pizzalayer_setting_topping_maxtoppings');
//$pizzalayer_template_glassy_option_display_header = get_option('pizzalayer_setting_template_glass_display_header');
$pizzalayer_global_option_element_style_toppings = get_option('pizzalayer_setting_element_style_toppings');
$pizzalayer_global_option_element_style_layers = get_option('pizzalayer_setting_element_style_layers');
$pizzalayer_global_option_element_alt_logo = get_option('pizzalayer_setting_branding_altlogo');

do_action( 'func_pizzalayer_toppings_visualizer_func_after_get_user_options' );







/* ============= BUILD LAYOUT PART : MAIN CONTENT - FULL WIDTH ============= */
$pizzalayer_template_glassy_part_maindisplay_100percent = '<div id="pizzalayer-main-visualizer-container" class="pizzalayer-visualizer pt-visualizer-' . $atts['id'] . ' col-md-12 col-sm-12"></div>';

/* ============= BUILD LAYOUT PART : ALERT   ============= */
$pizzalayer_template_glassy_part_alert = pizzalayer_alert('Max : ' . $pizzalayer_template_glassy_option_topping_maxtoppings . ' Toppings','max-toppings');

/* ============= BUILD LAYOUT PART : HEADER AND LOGO   ============= */
$pizzalayer_template_glassy_part_header_and_logo = '<header id="pztp-mobileorder-header">
      <img src="' . $pizzalayer_global_option_element_alt_logo . '" alt="Logo" id="pztp-logo">
    </header>';

/* ============= BUILD LAYOUT PART : PANELS   ============= */
$pizzalayer_template_glassy_part_panels = pizzalayer_panels();

/* ============= BUILD LAYOUT PART : USER ACTIONS  ============= */
$pizzalayer_template_glassy_part_useractions = pizzalayer_icons_menu_user_actions();

/* ============= BUILD LAYOUT PART : CLOSE TABS CONTAINER   ============= */
$pizzalayer_template_glassy_part_closetabcontainer = '</div>';



do_action( 'func_pizzalayer_toppings_visualizer_func_before_return' );

return '<!-- Pizzalayer : PIZZA DISPLAY NEW ==================== -->

<div id="pztp-containers-presentation" class="pztp-mobileorder-wrapper pizzalayer-template-mobileorder">

    ' . $pizzalayer_template_glassy_part_header_and_logo . '

    <!-- Dropdown Nav for mobile -->
    <select id="pztp-mobileorder-nav-dropdown">
      <option value="home">Home</option>
      <option value="crust">Crust</option>
      <option value="sauce">Sauce</option>
      <option value="cheese">Cheese</option>
      <option value="toppings">Toppings</option>
      <option value="drizzle">Drizzle</option>
      <option value="slicing">Slicing</option>
      <option value="order">Order</option>
    </select>

    <!-- Button Nav -->
    <nav id="pztp-mobileorder-nav">
      <button class="tab-btn active" data-tab="home">Home</button>
      <button class="tab-btn" data-tab="crust">Crust</button>
      <button class="tab-btn" data-tab="sauce">Sauce</button>
      <button class="tab-btn" data-tab="cheese">Cheese</button>
      <button class="tab-btn" data-tab="toppings">Toppings</button>
      <button class="tab-btn" data-tab="drizzle">Drizzle</button>
      <button class="tab-btn" data-tab="slicing">Slicing</button>
      <button class="tab-btn" data-tab="order">Order</button>
    </nav>
    <!-- Preview -->
    <div id="pztp-mobileorder-preview">' . pizzalayer_pizza_dynamic_nested($atts['id'],$atts['crust'],$atts['sauce'],$atts['toppings']) . '</div>
    <!-- Content -->
    <div id="pztp-mobileorder-content">
      <div class="tab-content active" id="tab-home">
        <p>Welcome! Build your pizza step-by-step.</p>
      </div>

<div class="tab-content" id="tab-crust">
  <p>Select your crust.</p>
  ' . pizzalayer_render_options_from_cpt('pizzalayer_crusts', 'crust') . '
</div>

<div class="tab-content" id="tab-sauce">
  <p>Select your sauce.</p>
  ' . pizzalayer_render_options_from_cpt('pizzalayer_sauces', 'sauce') . '
</div>

<div class="tab-content" id="tab-cheese">
  <p>Select your cheese.</p>
  ' . pizzalayer_render_options_from_cpt('pizzalayer_cheeses', 'cheese') . '
</div>

<div class="tab-content" id="tab-toppings">
  <p>Select your toppings.</p>
  ' . pizzalayer_render_options_from_cpt('pizzalayer_toppings', 'toppings') . '
</div>

<div class="tab-content" id="tab-drizzle">
  <p>Select your drizzle.</p>
  ' . pizzalayer_render_options_from_cpt('pizzalayer_drizzles', 'drizzle') . '
</div>

<div class="tab-content" id="tab-slicing">
  <p>Select your slicing.</p>
  ' . pizzalayer_render_options_from_cpt('pizzalayer_cuts', 'cut') . '
</div>


      <div class="tab-content" id="tab-order">
        <p>Review your order:</p>
        <div id="order-summary"></div>
        <div id="toppings-csv"></div>
      </div>
    </div>

    <!-- Footer -->
    <footer id="pztp-mobileorder-footer">
      <div id="nav-controls">
        <button id="prev-btn" class="add-remove-btn">Previous</button>
        <button id="next-btn" class="add-remove-btn">Next</button>
      </div>
      <hr>
      <div id="footer-summary">
        <div>Total: <span id="pztp-total-price">$0.00</span></div>
        <div>
          <button class="add-remove-btn" id="pztp-view-summary" title="View Summary">View</button>
          <button class="add-remove-btn" id="pztp-reset" title="Reset Order">Reset</button>
        </div>
      </div>
    </footer>

  </div>



<!-- / Pizzalayer new ==================== -->




';
do_action( 'func_pizzalayer_toppings_visualizer_func_after' );
};

add_Shortcode( 'pizzalayer-visualizer', 'pizzalayer_toppings_visualizer_func' );








/* default static displays */
function pizzalayer_ui_wrapper_pizza_1_start(){ return '<!-- PT : PIZZA -->
<div id="pizzalayer-pizza" class="pizzalayer-pizza-static">';}

function pizzalayer_ui_wrapper_pizza_1_end(){ return '<div style="clear:both;"></div>
</div>
<!-- / PT : PIZZA -->';}

function pizzalayer_ui_wrapper_pizza_dyn_1_start(){ return '<!-- PT : PIZZA -->
<div id="pizzalayer-pizza" class="pizzalayer-pizza-dynamic">';}

function pizzalayer_ui_wrapper_pizza_dyn_1_end(){ return '<div style="clear:both;"></div>
</div>
<!-- / PT : PIZZA -->';}


function pizzalayer_ui_container_shell_1(){
    
    
    
    
}

do_action( 'pizzalayer_file_pztp-containers-presentation_end' );