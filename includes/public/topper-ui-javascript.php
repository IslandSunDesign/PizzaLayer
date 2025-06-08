<?php
function pizzalayer_swapper_js_output(){
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
<input type="hidden" name="MaxToppings" id="MaxToppings" value="' . get_option('pizzalayer_setting_topping_maxtoppings') . '" />
<input type="hidden" name="CurrentToppingsCount" id="CurrentToppingsCount" value="0" />
';
}


function pizzalayer_swapper_arrays(){
$pizzalayer_swapper_array_toppings = '';
$pizzalayer_swapper_array_sauces = '';
$pizzalayer_swapper_array_crusts = '';
$pizzalayer_swapper_array_cheeses = '';
$args = array(  
'post_type' => array('pizzalayer_toppings','pizzalayer_cheeses','pizzalayer_sauces','pizzalayer_crusts'),
'post_status' => 'publish',
'posts_per_page' => -1, 
'orderby' => 'title', 
'order' => 'ASC',
);
$loop = new WP_Query( $args );        
while ( $loop->have_posts() ) : $loop->the_post();    
if( get_post_type( get_the_ID() ) == 'pizzalayer_toppings' ){ $pizzalayer_swapper_array_toppings .= '[\'' . get_the_title() . '\',\'' . get_field( 'topping_layer_image' ) . '\'],'; }
if( get_post_type( get_the_ID() ) == 'pizzalayer_sauces' ){ $pizzalayer_swapper_array_sauces .= '[\'' . get_the_title() . '\',\'' . get_field( 'sauce_layer_image' ) . '\'],'; }
if( get_post_type( get_the_ID() ) == 'pizzalayer_crusts' ){ $pizzalayer_swapper_array_crusts .= '[\'' . get_the_title() . '\',\'' . get_field( 'crust_layer_image' ) . '\'],'; }
if( get_post_type( get_the_ID() ) == 'pizzalayer_cheeses' ){ $pizzalayer_swapper_array_cheeses .= '[\'' . get_the_title() . '\',\'' . get_field( 'cheese_layer_image' ) . '\'],'; } 
endwhile;
wp_reset_postdata();    
return '
var pztpToppings = [' . $pizzalayer_swapper_array_toppings . '];
var pztpSauces = [' . $pizzalayer_swapper_array_sauces . '];
var pztpCrusts = [' . $pizzalayer_swapper_array_crusts . '];
';
}