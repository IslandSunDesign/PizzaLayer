<?php
/* ============================================= 
PIZZALAYER : TOPPING LAYERS 
example usage: pizzalayer_layer(250,'sauce','crusts/plain.png','plain crust')
*/


//outputs complete layers with closing div tags
function pizzalayer_layer( $layer_index, $layer_short, $layer_imagepath, $layer_alt ){
global $pizzalayer_path_images;
return '<div id="pizzalayer-topping-' . $layer_short . '" class="pizzalayer-' . $layer_short . ' pizzalayer-topping-' . $layer_short . ' pizzalayer-layer-closed" style="z-index:' . $layer_index . ';"><img src="' . $layer_imagepath . '" id="pizzalayer-' . $layer_short . '-image" class="pizzalayer-' . $layer_short . '-image" alt="' . $layer_alt . '" title="' . $layer_alt . '" style="z-index:' . $layer_index . ';" /></div>';
}


//outputs nested layer without closing div tag for the three base layers : crust, sauce, and cheese. Important - You need to close these open div tags if using a custom template or code.
function pizzalayer_layer_nest( $layer_index, $layer_short, $layer_imagepath, $layer_alt ){
global $pizzalayer_path_images;
return '<div id="pizzalayer-base-layer-' . $layer_short . '" class="pizzalayer-' . $layer_short . ' pizzalayer-topping-' . $layer_short . ' pizzalayer-layer-nested" style="z-index:' . $layer_index . ';background: url(\'' . $layer_imagepath . '\') no-repeat;">';
}
