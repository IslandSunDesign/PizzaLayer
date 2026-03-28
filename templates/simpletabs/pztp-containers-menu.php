<?php
do_action('pizzalayer_file_pztp-containers-menu_start');
function pizzalayer_toppings_menu_func(){
return '
<div id="pizzalayer-ui-menu-ingredients" class="pizzalayer-ui">
<div class="pizzalayer-user-controls-bar">
<a href="javascript:RemoveAllToppings();">Clear Toppings</a>
</div>

<div id="pizza-toppings">
<div class="pizza-toppings-list">
<h2 class="pizza-toppings-list-title">Toppings</h2>
' . pizzalayer_tpv_toppings_list() . '
</div>
<div style="clear:both;"></div></div>

<div id="pizza-sauces">
<div class="pizza-toppings-list">
<h2 class="pizza-toppings-list-title">Sauces</h2>
' . pizzalayer_tpv_sauces_list() . '
</div>
<div style="clear:both;"></div></div>

<div id="pizza-cheeses">
<div class="pizza-toppings-list">
<h2 class="pizza-toppings-list-title">Cheeses</h2>
' . pizzalayer_tpv_cheeses_list() . '
</div>
<div style="clear:both;"></div></div>

<div id="pizza-crusts">
<div class="pizza-toppings-list">
<h2 class="pizza-toppings-list-title">Crusts</h2>
' . pizzalayer_tpv_crusts_list() . '
</div>
<div style="clear:both;"></div>
</div>
';
}
do_action('pizzalayer_file_pztp-containers-menu_end');