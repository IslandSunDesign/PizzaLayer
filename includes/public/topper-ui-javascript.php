<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
do_action( 'pizzalayer_file_topper-ui-javascript_start' );

function pizzalayer_swapper_js_output(){
$max_toppings = intval( get_option('pizzalayer_setting_topping_maxtoppings') );
return '
<script type="text/javascript">

//formatting
var NewPizzaLayerSlug;
function convertToSlug(NewPizzaLayerSlug){
var str = NewPizzaLayerSlug;
str = str.replace(/[^a-zA-Z0-9\s]/g,"");
str = str.toLowerCase();
str = str.replace(/\s/g,\'-\');
return str;
}

//pizza part arrays
' . pizzalayer_swapper_arrays() . '
</script>
<input type="hidden" name="MaxToppings" id="MaxToppings" value="' . esc_attr( $max_toppings ) . '" />
<input type="hidden" name="CurrentToppingsCount" id="CurrentToppingsCount" value="0" />
';
}


function pizzalayer_swapper_arrays(){
$toppings_data  = [];
$sauces_data    = [];
$crusts_data    = [];
$cheeses_data   = [];

$args = array(
'post_type'      => array('pizzalayer_toppings','pizzalayer_cheeses','pizzalayer_sauces','pizzalayer_crusts'),
'post_status'    => 'publish',
'posts_per_page' => -1,
'orderby'        => 'title',
'order'          => 'ASC',
);
do_action( 'func_pizzalayer_swapper_arrays_before_loop' );
$loop = new WP_Query( $args );
while ( $loop->have_posts() ) : $loop->the_post();
$post_type = get_post_type( get_the_ID() );
if ( $post_type === 'pizzalayer_toppings' ) {
    $toppings_data[] = [ get_the_title(), (string) get_field( 'topping_layer_image' ) ];
}
if ( $post_type === 'pizzalayer_sauces' ) {
    $sauces_data[] = [ get_the_title(), (string) get_field( 'sauce_layer_image' ) ];
}
if ( $post_type === 'pizzalayer_crusts' ) {
    $crusts_data[] = [ get_the_title(), (string) get_field( 'crust_layer_image' ) ];
}
if ( $post_type === 'pizzalayer_cheeses' ) {
    $cheeses_data[] = [ get_the_title(), (string) get_field( 'cheese_layer_image' ) ];
}
endwhile;
wp_reset_postdata();
do_action( 'func_pizzalayer_swapper_arrays_before_return' );
return '
var pztpToppings = ' . wp_json_encode( $toppings_data ) . ';
var pztpSauces = '   . wp_json_encode( $sauces_data )   . ';
var pztpCrusts = '   . wp_json_encode( $crusts_data )   . ';
var pztpCheeses = '  . wp_json_encode( $cheeses_data )  . ';
';
}

do_action( 'pizzalayer_file_topper-ui-javascript_end' );