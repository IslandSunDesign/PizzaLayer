<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

do_action( 'pizzalayer_file_topper-ui-controls_start' );

// ========= HALVES SELECTION FOR TOPPINGS

function pizzalayer_control_halves($PizzalayerControlID,$ToppingLayerID){
global $pizzalayer_template_images_directory;
    
$pizzalayer_halves_visual_input = '<div id="pizzalayer-halves-control-' . esc_attr( $PizzalayerControlID ) . '" class="col-sm-12">
<img src="' . esc_url( $pizzalayer_template_images_directory ) . 'button-half-pizza-left.png" class="pizzalayer-halves-control pizzalayer-halves-control-half-left"  onClick="SetToppingCoverage(\'half-left\',\'' . esc_js( $ToppingLayerID ) . '\');" />
<img src="' . esc_url( $pizzalayer_template_images_directory ) . 'button-half-pizza-whole.png" class="pizzalayer-halves-control pizzalayer-halves-control-whole"  onClick="SetToppingCoverage(\'whole\',\'' . esc_js( $ToppingLayerID ) . '\');" />
<img src="' . esc_url( $pizzalayer_template_images_directory ) . 'button-half-pizza-right.png" class="pizzalayer-halves-control pizzalayer-halves-control-half-right"  onClick="SetToppingCoverage(\'half-right\',\'' . esc_js( $ToppingLayerID ) . '\');" />
</div>';
    
$pizzalayer_halves_form_controls = '<div id="' . esc_attr( $PizzalayerControlID ) . '" class="pizzalayer-halves-control-radiobutton-set">
<input type="radio" id="' . esc_attr( $PizzalayerControlID ) . '-half-left" name="' . esc_attr( $PizzalayerControlID ) . '" value="half-left">
<input type="radio" id="' . esc_attr( $PizzalayerControlID ) . '-whole" name="' . esc_attr( $PizzalayerControlID ) . '" value="whole" checked>
<input type="radio" id="' . esc_attr( $PizzalayerControlID ) . '-half-right" name="' . esc_attr( $PizzalayerControlID ) . '" value="half-right">
</div>';

do_action( 'func_pizzalayer_control_halves_before_return' );
return $pizzalayer_halves_visual_input . $pizzalayer_halves_form_controls;
}

// ========= COMBINED HALVES SELECTION AND ADD/REMOVE BUTTONS

function pizzalayer_control_choice_options($PizzalayerControlID,$ToppingLayerID){
global $pizzalayer_template_images_directory;
    
$pizzalayer_halves_visual_input = '<div id="pizzalayer-halves-control-' . esc_attr( $PizzalayerControlID ) . '" class="col-sm-12">
<img src="' . esc_url( $pizzalayer_template_images_directory ) . 'button-half-pizza-left.png" class="pizzalayer-halves-control pizzalayer-halves-control-half-left"  onClick="SetToppingCoverage(\'half-left\',\'' . esc_js( $ToppingLayerID ) . '\');" />
<img src="' . esc_url( $pizzalayer_template_images_directory ) . 'button-half-pizza-whole.png" class="pizzalayer-halves-control pizzalayer-halves-control-whole"  onClick="SetToppingCoverage(\'whole\',\'' . esc_js( $ToppingLayerID ) . '\');" />
<img src="' . esc_url( $pizzalayer_template_images_directory ) . 'button-half-pizza-right.png" class="pizzalayer-halves-control pizzalayer-halves-control-half-right"  onClick="SetToppingCoverage(\'half-right\',\'' . esc_js( $ToppingLayerID ) . '\');" />
<img src="' . esc_url( $pizzalayer_template_images_directory ) . 'add-to-pizza.png" class="pizzalayer-halves-control pzpt-ui-button-add"  onClick="AddPizzaLayer(\'400\',\'' . esc_js( $ToppingLayerID ) . '\',\'\',\'\',\'' . esc_js( $ToppingLayerID ) . '\',\'\');" />
<img src="' . esc_url( $pizzalayer_template_images_directory ) . 'trash.png" class="pizzalayer-halves-control pzpt-ui-button-remove"  onClick="RemovePizzaLayer(\'' . esc_js( $ToppingLayerID ) . '\',\'\',\'\');" />
</div>';
    
$pizzalayer_halves_form_controls = '<div id="'. $PizzalayerControlID . '" class="pizzalayer-halves-control-radiobutton-set">
<input type="radio" id="'. $PizzalayerControlID . '-half-left" name="'. $PizzalayerControlID . '" value="half-left">
<input type="radio" id="'. $PizzalayerControlID . '-whole" name="'. $PizzalayerControlID . '" value="whole" checked>
<input type="radio" id="'. $PizzalayerControlID . '-half-right" name="'. $PizzalayerControlID . '" value="half-right">
</div>';

do_action( 'func_pizzalayer_control_choice_options_before_return' );
return $pizzalayer_halves_visual_input . $pizzalayer_halves_form_controls;

}


// ========= NEXT/PREV CONTROLS ========

function pizzalayer_control_nextprev($PizzalayerPanePrev,$PizzalayerPaneNext,$ControlCSSid){
    if( get_option('pizzalayer_setting_template_glass_display_section_nextprev') != 'show' ){ return ''; };
if($PizzalayerPaneNext){
    $pizzalayer_This_NextLink = '<a href="javascript:PTswitchToMenu(\'' . $PizzalayerPaneNext . '\');" class="pizzalayer-control-nextprev nextprev-next">' . $PizzalayerPaneNext . '  &gt;</a>';
    } else {$pizzalayer_This_NextLink = '';};    
if($PizzalayerPanePrev){ 
    $pizzalayer_This_PrevLink = '<a href="javascript:PTswitchToMenu(\'' . $PizzalayerPanePrev . '\');" class="pizzalayer-control-nextprev nextprev-prev"> &lt; ' . $PizzalayerPanePrev . '</a>';
} else {$pizzalayer_This_PrevLink = '';};
do_action( 'func_pizzalayer_control_nextprev_before_return' );
return '<div id="' . $ControlCSSid .'" class="pizzalayer-control-nextprev col-sm-12">' . $pizzalayer_This_PrevLink . $pizzalayer_This_NextLink . '</div>';
}

do_action( 'pizzalayer_file_topper-ui-controls_end' );