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
    
/* ============= BUILD LAYOUT PART : NAVIGATION   ============= */
$pizzalayer_template_glassy_part_navigation = '
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
    </nav>';    
    
/* ============= BUILD LAYOUT PART : PREVIEW ============= */
$pizzalayer_template_glassy_part_preview = '<!-- Preview -->
<div id="pztp-mobileorder-preview">' . pizzalayer_pizza_dynamic_nested($atts['id'],$atts['crust'],$atts['sauce'],$atts['toppings']) . '</div>';

/* ============= BUILD LAYOUT PART : PANELS   ============= */
$pizzalayer_template_glassy_part_panels = pizzalayer_panels();

/* ============= BUILD LAYOUT PART : USER ACTIONS  ============= */
$pizzalayer_template_glassy_part_useractions = pizzalayer_icons_menu_user_actions();

/* ============= BUILD LAYOUT PART : CLOSE TABS CONTAINER   ============= */
$pizzalayer_template_glassy_part_closetabcontainer = '</div>';

/* ============= BUILD LAYOUT PART : HOME TAB CONTENT   ============= */
$pizzalayer_template_glassy_part_hometab = ' <!-- ==================== HOME TAB ==================== -->
<div class="tab-content active" id="tab-home">
  <!-- Hero / Intro -->
  <section class="pztp-home-hero">
    <div class="pztp-hero-copy">
      <h2 class="pztp-hero-title">Build Your Perfect Pizza</h2>
      <p class="pztp-hero-sub">
        Start with a crust, add sauce and cheese, pile on toppings, then finish with a drizzle and slice style.
      </p>
      <ul class="pztp-hero-tips">
        <li>All choices are saved as you go.</li>
        <li>Change layers anytime — pricing updates live.</li>
        <li>Tap “Order” when you’re ready to checkout.</li>
      </ul>

      <div class="pztp-hero-cta">
        <button class="pztp-btn pztp-go-tab" data-tab="crust" aria-label="Start with Crust">Start with Crust</button>
        <button class="pztp-btn-alt pztp-go-tab" data-tab="toppings" aria-label="Skip to Toppings">Skip to Toppings</button>
      </div>
    </div>

    <div class="pztp-hero-art" aria-hidden="true">
      <!-- simple decorative SVG pizza -->
      <svg viewBox="0 0 200 200" role="img" class="pztp-pizza-svg">
        <circle cx="100" cy="100" r="90" />
        <circle cx="100" cy="100" r="70" class="ring" />
        <g class="toppings">
          <circle cx="70" cy="80" r="7" />
          <circle cx="125" cy="75" r="7" />
          <circle cx="95" cy="120" r="7" />
          <circle cx="135" cy="130" r="7" />
          <circle cx="60" cy="130" r="7" />
        </g>
        <line x1="100" y1="10" x2="100" y2="190" />
        <line x1="10" y1="100" x2="190" y2="100" />
      </svg>
    </div>
  </section>

  <!-- Layer Cards -->
  <section class="pztp-home-steps">
    <h3 class="pztp-steps-title">Pick Your Layers</h3>

    <div class="pztp-card-grid">
      <article class="pztp-card">
        <div class="pztp-card-icon">🍕</div>
        <h4>Crust</h4>
        <p>Choose your base: classic, thin, deep-dish, or gluten-friendly.</p>
        <button class="pztp-card-btn pztp-go-tab" data-tab="crust" aria-label="Go to Crust">Choose Crust</button>
      </article>

      <article class="pztp-card">
        <div class="pztp-card-icon">🍅</div>
        <h4>Sauce</h4>
        <p>Red, white, pesto, BBQ — set the flavor foundation.</p>
        <button class="pztp-card-btn pztp-go-tab" data-tab="sauce" aria-label="Go to Sauce">Choose Sauce</button>
      </article>

      <article class="pztp-card">
        <div class="pztp-card-icon">🧀</div>
        <h4>Cheese</h4>
        <p>Mozzarella, cheddar, feta and more — single or blended.</p>
        <button class="pztp-card-btn pztp-go-tab" data-tab="cheese" aria-label="Go to Cheese">Choose Cheese</button>
      </article>

      <article class="pztp-card">
        <div class="pztp-card-icon">🥓</div>
        <h4>Toppings</h4>
        <p>Meats, veggies, and gourmet picks. Add as many as you like.</p>
        <button class="pztp-card-btn pztp-go-tab" data-tab="toppings" aria-label="Go to Toppings">Add Toppings</button>
      </article>

      <article class="pztp-card">
        <div class="pztp-card-icon">🥫</div>
        <h4>Drizzle</h4>
        <p>Finish strong with ranch, hot honey, balsamic, or garlic oil.</p>
        <button class="pztp-card-btn pztp-go-tab" data-tab="drizzle" aria-label="Go to Drizzle">Add Drizzle</button>
      </article>

      <article class="pztp-card">
        <div class="pztp-card-icon">✂️</div>
        <h4>Slicing</h4>
        <p>Traditional, squares, or party cuts. Your call.</p>
        <button class="pztp-card-btn pztp-go-tab" data-tab="slicing" aria-label="Go to Slicing">Choose Slicing</button>
      </article>
    </div>

    <div class="pztp-steps-footer">
      <button class="pztp-btn-primary pztp-go-tab" data-tab="order" aria-label="Go to Order">Review &amp; Order</button>
    </div>
  </section>
</div>

<!-- ==================== CSS (add to template.css) ==================== -->
<style>
  /* Scope to Home tab only */
  #tab-home { padding: 16px 12px; }

  .pztp-home-hero{
    display:grid; gap:24px; align-items:center;
    grid-template-columns: 1fr;
    background: var(--pztp-hero-bg, #fff);
    border:1px solid rgba(0,0,0,.08);
    border-radius:16px; padding:24px;
    box-shadow: 0 6px 18px rgba(0,0,0,.06);
  }
  @media (min-width: 900px){
    .pztp-home-hero{ grid-template-columns: 1.2fr .8fr; padding:32px; }
  }

  .pztp-hero-title{ margin:0 0 8px; font-size: clamp(22px, 3vw, 34px); line-height:1.2; }
  .pztp-hero-sub{ margin:0 0 12px; font-size: 1.05rem; color:#333; }
  .pztp-hero-tips{ margin: 8px 0 18px 18px; color:#555; }
  .pztp-hero-tips li{ margin:6px 0; }

  .pztp-hero-cta{ display:flex; flex-wrap:wrap; gap:10px; }

  .pztp-btn, .pztp-btn-alt, .pztp-btn-primary, .pztp-card-btn{
    border:1px solid rgba(0,0,0,.12); border-radius:999px;
    padding:10px 16px; font-size:14px; cursor:pointer;
    transition: transform .08s ease, box-shadow .2s ease, background .2s ease;
    background:#fff;
  }
  .pztp-btn:hover, .pztp-btn-alt:hover, .pztp-card-btn:hover{
    transform: translateY(-1px);
    box-shadow:0 8px 18px rgba(0,0,0,.08);
  }
  .pztp-btn-primary{
    background:#111; color:#fff; border-color:#111;
  }
  .pztp-btn-primary:hover{ filter:brightness(1.05); transform: translateY(-1px); }

  .pztp-hero-art{ display:flex; justify-content:center; }
  .pztp-pizza-svg{ width: 260px; max-width:100%; height:auto; }
  .pztp-pizza-svg circle{ fill:#f6d29b; }
  .pztp-pizza-svg .ring{ fill:#ffdcae; }
  .pztp-pizza-svg .toppings circle{ fill:#b22222; }
  .pztp-pizza-svg line{ stroke:#c68b59; stroke-width:3; }

  .pztp-home-steps{ margin-top:24px; }
  .pztp-steps-title{ font-size:1.25rem; margin:0 0 12px; }

  .pztp-card-grid{
    display:grid; gap:14px;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  }

  .pztp-card{
    background:#fff; border:1px solid rgba(0,0,0,.08); border-radius:14px;
    padding:16px; display:flex; flex-direction:column; gap:8px;
    box-shadow:0 4px 12px rgba(0,0,0,.04);
    transition: transform .08s ease, box-shadow .2s ease;
  }
  .pztp-card:hover{ transform: translateY(-2px); box-shadow:0 10px 22px rgba(0,0,0,.08); }
  .pztp-card h4{ margin:0; font-size:1.05rem; }
  .pztp-card p{ margin:0 0 6px; color:#444; }
  .pztp-card-icon{
    width:42px; height:42px; display:grid; place-items:center;
    border-radius:50%; background:#f5f5f5; font-size:20px;
  }
  .pztp-card-btn{ align-self:flex-start; }

  .pztp-steps-footer{ margin-top:16px; display:flex; justify-content:flex-end; }
</style>

<!-- ==================== JS (light helper; keeps existing tab system) ==================== -->
<script>
  (function () {
    // On any ".pztp-go-tab" click, trigger the existing nav\'s tab button
    document.addEventListener(\'click\', function (e) {
      const btn = e.target.closest(\'.pztp-go-tab\');
      if (!btn) return;

      const target = btn.getAttribute(\'data-tab\');
      if (!target) return;

      // Try both possible selectors your template may use
      const navBtn =
        document.querySelector(\'.tab-btn[data-tab="\'+target+\'"]\') ||
        document.querySelector(\'.nav-tab[data-tab="\'+target+\'"]\');

      if (navBtn) {
        navBtn.click();
        // Smoothly scroll to top of the app after switching
        const root = document.querySelector(\'.pztp-mobileorder-wrapper\') || document.body;
        root.scrollIntoView({behavior: \'smooth\', block: \'start\'});
      }
    }, { passive: true });
  })();
</script>
';



do_action( 'func_pizzalayer_toppings_visualizer_func_before_return' );

return '<!-- Pizzalayer : PIZZA DISPLAY NEW ==================== -->

<div id="pztp-containers-presentation" class="pztp-mobileorder-wrapper pizzalayer-template-mobileorder">

    ' . $pizzalayer_template_glassy_part_header_and_logo . $pizzalayer_template_glassy_part_preview . $pizzalayer_template_glassy_part_navigation . '

   
  
    <!-- Content -->
<div id="pztp-mobileorder-content">

' . $pizzalayer_template_glassy_part_hometab . '

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