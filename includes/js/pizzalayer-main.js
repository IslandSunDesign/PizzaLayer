//declare vars
var NewPizzaLayerImageUrl;var NewPizzaLayerName;var NewPizzaTargetLayer;var NewPizzaLayerContent;
var MenuItemID;
var NewPizzaLayerIndex;
var NewPizzaLayerShort;
var NewPizzaLayerImageUrlWrapped;
var NewPizzaLayerAlt;
var SwapPizzaLayerNewTitle;
var ToppingCoverageArea;
var NewToppingCoverageArea;
var ToppingID;
var TargetRadioButtonID;
var ToppingCoverageShort;
var CurrentToppingsCount;
var MaxToppings;
var ImageURLtoSet;
let rotationIntervals = {};

function SwapPizzaLayer(NewPizzaTargetLayer,NewPizzaLayerName,NewPizzaLayerImageUrl){
jQuery('#' + NewPizzaTargetLayer).fadeOut(100).attr("src",NewPizzaLayerImageUrl).fadeIn(600);
}

function AddPizzaLayer(NewPizzaLayerIndex,NewPizzaLayerShort,NewPizzaLayerImageUrl,NewPizzaLayerAlt,NewPizzaLayerName,MenuItemID){
if( jQuery('#' + NewPizzaLayerName).length ){ return false; }
CurrentToppingsCount = parseInt(jQuery('#CurrentToppingsCount').val());
MaxToppings = jQuery('#MaxToppings').val();
if( !UnderMaxToppings(CurrentToppingsCount) ){ 
jQuery('#pizzalayer-ui-menu-section-toppings').animate({background:red}, 100 ).wait().animate({background:transparent}, 200 );return false;
} //if
var NewPizzaLayerContent = '';
NewPizzaLayerContent = '<div id="' + NewPizzaLayerName + '" class="pizzalayer-topping ' + NewPizzaLayerName + '" style="z-index:' + NewPizzaLayerIndex + ';"><img title="' + NewPizzaLayerAlt + '" alt="' + NewPizzaLayerAlt + '" src="' + NewPizzaLayerImageUrl + '" onload="jQuery(this).hide().fadeIn(1300);"></div>';
var NewPizzaLayerCurrentToppingLI;
NewPizzaLayerCurrentToppingLI = '<li id="current-topping-' + NewPizzaLayerName + '" class="pizza-topping-li-' + NewPizzaLayerIndex + '">' + NewPizzaLayerAlt + '<a href="javascript:RemovePizzaLayer(\'' + NewPizzaLayerName + '\',\'' + NewPizzaLayerIndex + '\',\'' + NewPizzaLayerShort + '\');" class="topping-list-remove-button"><i class="fa fa-solid fa-trash"></i></a></li>';
jQuery('#pizzalayer-toppings-wrapper').delay(301).append(NewPizzaLayerContent);
jQuery('#pizzalayer-current-toppings').delay(20).append(NewPizzaLayerCurrentToppingLI).delay(20).fadeIn(400);
jQuery('#menu-pizzalayer-topping-' + NewPizzaLayerShort).addClass('ToppingSelected');
jQuery('#' + NewPizzaLayerName).removeClass('tcg-half-left tcg-half-right tcg-whole tcg-quarter-topleft tcg-quarter-topright tcg-quarter-bottomleft tcg-quarter-bottomright');
ToppingCoverageArea = jQuery("input[type='radio']:checked", '#halfcontrol-' + NewPizzaLayerShort).val();
jQuery('#' + NewPizzaLayerName).addClass('tcg-' + ToppingCoverageArea);
jQuery('#CurrentToppingsCount').val(CurrentToppingsCount + 1);
//window.alert('Current Toppings Count = ' + CurrentToppingsCount + '. Max Toppings = ' + MaxToppings);
}

function RemovePizzaLayer(NewPizzaLayerName,NewPizzaLayerIndex,NewPizzaLayerShort){
jQuery('.' + NewPizzaLayerName).fadeOut(1200).remove();
jQuery('li#current-topping-' + NewPizzaLayerName).fadeOut(900).remove(); // remove pizza image layer
jQuery('.pizza-topping-li-' + NewPizzaLayerShort).fadeOut(600).remove(); //remove item in current toppings list
jQuery('#menu-pizzalayer-topping-' + NewPizzaLayerShort).removeClass('ToppingSelected'); //remove the dark background highlight in toppings list item
CurrentToppingsCount = parseInt(jQuery('#CurrentToppingsCount').val());
CurrentToppingsCount -= 1;
jQuery('#CurrentToppingsCount').val(CurrentToppingsCount);
if(CurrentToppingsCount < MaxToppings){ jQuery('#pizzalayer-alert').fadeOut(500);} else { jQuery('#pizzalayer-alert').fadeIn(500); }
}

function ClearPizza(){
jQuery('#pizzalayer-pizza .pizzalayer-sauce,#pizzalayer-pizza .pizzalayer-cheese,#pizzalayer-pizza .pizzalayer-drizzle,#pizzalayer-pizza .pizzalayer-cut').css({"background":"none"}); //reset backgrounds for non-crust and non-topping pizza layers
jQuery('#pizzalayer-pizza .pizzalayer-topping').fadeOut(900).remove();  //remove pizza image layers
jQuery('#pizzalayer-current-toppings *').fadeOut(600).remove(); //remove items in current toppings list
jQuery('.pizzalayer-toppings-list-linkboxes .pizza-topping,.pizzalayer-ui-menu-tab .pizzalayer-topping,.pizzalayer-inner-tile').removeClass('ToppingSelected'); //remove the dark background highlight in toppings list item
//reset ingredient tiles
jQuery('#pizzalayer-basics-tile-title-crust').html("No Crust Chosen");
jQuery('#pizzalayer-basics-tile-title-sauce').html("No Sauce Chosen");
jQuery('#pizzalayer-basics-tile-title-cheese').html("No Cheese Chosen");
jQuery('#pizzalayer-basics-tile-title-drizzle').html("No Drizzle Chosen");
jQuery('#CurrentToppingsCount').val(0);
}

function RemoveAllToppings(){
jQuery('.pizzalayer-topping').fadeOut(600).remove();
jQuery('#CurrentToppingsCount').val(0);
}

function UnderMaxToppings(CurrentToppingsCount){
MaxToppings = jQuery('#MaxToppings').val();
if(!MaxToppings){ MaxToppings = 9999; }
if(CurrentToppingsCount < MaxToppings){ jQuery('#pizzalayer-alert').fadeOut(500); return true;  } else { jQuery('#pizzalayer-alert').fadeIn(500); return false;  }
} //function

function convertToSlug(Text) {
  return Text.toLowerCase()
    .replace(/ /g, "-")
    .replace(/[^\w-]+/g, "");
}

function SwapBasePizzaLayer(PizzaTargetLayer,NewPizzaLayerName,NewPizzaLayerImageUrl){
NewPizzaLayerImageUrlWrapped = 'url(' + NewPizzaLayerImageUrl + ')';
SwapPizzaLayerNewTitle = PizzaTargetLayer.replace('pizzalayer-base-layer-','pizzalayer-basics-tile-title-'); 
ThisLayerTypeSlug = PizzaTargetLayer.replace('pizzalayer-base-layer-',''); 
jQuery('#' + PizzaTargetLayer).fadeOut(100).delay(20).css("backgroundImage",NewPizzaLayerImageUrlWrapped).delay(20).fadeIn(900);
jQuery('#' + SwapPizzaLayerNewTitle).html(NewPizzaLayerName);
NewPizzaLayerShort = 'menu-pizzalayer-topping-' + convertToSlug(NewPizzaLayerName);
jQuery('.pizzalayer-' + ThisLayerTypeSlug + 's-list li').removeClass('ToppingSelected');
jQuery('#' + NewPizzaLayerShort).addClass('ToppingSelected');
}

function ChangeSlicing(PizzaTargetLayer,NewPizzaLayerName,NewPizzaLayerImageUrl){
NewPizzaLayerImageUrlWrapped = 'url(' + NewPizzaLayerImageUrl + ')';
jQuery('#' + PizzaTargetLayer).fadeOut(100).css("backgroundImage",NewPizzaLayerImageUrlWrapped).fadeIn(400);
jQuery('#' + PizzaTargetLayer).parent().append(jQuery('#' + PizzaTargetLayer));
}

function SetToppingCoverage(ToppingCoverageArea,ToppingID,ToppingShort){
jQuery('#' + ToppingID).removeClass('tcg-half-left tcg-half-right tcg-whole tcg-quarter-top-left tcg-quarter-top-right tcg-quarter-bottom-left tcg-quarter-bottom-right');
jQuery('#' + ToppingID).addClass('tcg-' + ToppingCoverageArea);
ToppingCoverageShort =  ToppingID.replace('pizzalayer-topping-','');
TargetRadioButtonID = 'halfcontrol-' + ToppingCoverageShort + '-' + ToppingCoverageArea;
jQuery('#pizzalayer-halves-control-halfcontrol-' + ToppingCoverageShort + ' img.pizzalayer-halves-control').removeClass('pizzalayer-halves-control-highlighted');
jQuery('#pizzalayer-halves-control-halfcontrol-' + ToppingCoverageShort + ' img.pizzalayer-halves-control-' + ToppingCoverageArea).addClass('pizzalayer-halves-control-highlighted');
//1.create the new image string based on the topping short and area
//topping-' . $pztp_tli_short_slug . '-halves-control-button-top-left
NewToppingCoverageArea = ToppingCoverageArea.replace('quarter-','');
ImageURLtoSet = jQuery('#topping-' + ToppingShort + '-halves-control-button-' + NewToppingCoverageArea).attr('src');
//2. write new image string to the topping-fraction-thumb-shortslug image src
jQuery('#topping-fraction-thumb-' + ToppingShort).attr('src',ImageURLtoSet);
jQuery('#' + TargetRadioButtonID)[0].checked = true;
}

function OpenToppingFractionBox(ToppingID){
jQuery('#pizzalayer-halves-control-halfcontrol-' + ToppingID).fadeOut(999);
jQuery('#pizzalayer-halves-control-fraction-' + ToppingID).fadeIn(1200);
}

function CloseToppingFractionBox(ToppingID){
jQuery('#pizzalayer-halves-control-halfcontrol-' + ToppingID).fadeIn(999);
jQuery('#pizzalayer-halves-control-fraction-' + ToppingID).fadeOut(1200);
}

// Pizza Rotation - Credit ChatGPT
/* USAGE:
RotatePizza('myPizzaDiv', 2); // Faster rotation
RotatePizza('myPizzaDiv', 0.5); // Slower rotation
*/

function RotatePizza($PizzaToRotate, speed = 1) {
    let pizzaElement = document.getElementById($PizzaToRotate);
    if (!pizzaElement) {
        console.error('Element not found:', $PizzaToRotate);
        return;
    }
    
    let angle = 0;
    function rotate() {
        angle = (angle + speed) % 360; // Increment angle based on speed
        pizzaElement.style.transform = `rotate(${angle}deg)`;
        rotationIntervals[$PizzaToRotate] = requestAnimationFrame(rotate);
    }
    
    rotate();
}

function StopPizza($PizzaToRotate) {
    if (rotationIntervals[$PizzaToRotate]) {
        cancelAnimationFrame(rotationIntervals[$PizzaToRotate]);
        delete rotationIntervals[$PizzaToRotate];
    }
}