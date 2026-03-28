/**
 * PizzaLayer Legacy Builder JS
 *
 * Contains the jQuery-based layer management functions used by older-style
 * template PHP onclick= handlers and legacy integrations.
 *
 * All internal helpers are scoped inside an IIFE to avoid global pollution.
 * Three functions are intentionally exposed on window because template PHP
 * renders them directly into onclick= attributes:
 *   window.ClearPizza()
 *   window.RotatePizza( id, speed )
 *   window.StopPizza( id )
 */
( function ( $ ) {
	'use strict';

	// ── Private state ─────────────────────────────────────────────────
	var rotationIntervals = {};

	// ── Internal helpers ──────────────────────────────────────────────

	function convertToSlug( text ) {
		return text.toLowerCase()
			.replace( / /g, '-' )
			.replace( /[^\w-]+/g, '' );
	}

	function UnderMaxToppings( currentCount ) {
		var max = $( '#MaxToppings' ).val();
		if ( ! max ) { max = 9999; }
		if ( currentCount < max ) {
			$( '#pizzalayer-alert' ).fadeOut( 500 );
			return true;
		} else {
			$( '#pizzalayer-alert' ).fadeIn( 500 );
			return false;
		}
	}

	// ── Layer management (called from legacy template PHP via window.*) ─

	function SwapPizzaLayer( targetLayer, name, imageUrl ) {
		$( '#' + targetLayer ).fadeOut( 100 ).attr( 'src', imageUrl ).fadeIn( 600 );
	}

	function AddPizzaLayer( zIndex, shortSlug, imageUrl, alt, layerName, menuItemId ) {
		if ( $( '#' + layerName ).length ) { return false; }
		var currentCount = parseInt( $( '#CurrentToppingsCount' ).val(), 10 );
		if ( ! UnderMaxToppings( currentCount ) ) {
			$( '#pizzalayer-ui-menu-section-toppings' ).css( 'outline', '2px solid red' );
			setTimeout( function () {
				$( '#pizzalayer-ui-menu-section-toppings' ).css( 'outline', '' );
			}, 600 );
			return false;
		}
		var layerHtml = '<div id="' + layerName + '" class="pizzalayer-topping ' + layerName +
			'" style="z-index:' + zIndex + ';"><img title="' + alt + '" alt="' + alt +
			'" src="' + imageUrl + '" onload="jQuery(this).hide().fadeIn(1300);"></div>';
		var liHtml = '<li id="current-topping-' + layerName + '" class="pizza-topping-li-' + zIndex + '">' +
			alt + '<a href="javascript:window.RemovePizzaLayer(\'' + layerName + '\',\'' +
			zIndex + '\',\'' + shortSlug + '\');" class="topping-list-remove-button">' +
			'<i class="fa fa-solid fa-trash"></i></a></li>';
		$( '#pizzalayer-toppings-wrapper' ).delay( 301 ).append( layerHtml );
		$( '#pizzalayer-current-toppings' ).delay( 20 ).append( liHtml ).delay( 20 ).fadeIn( 400 );
		$( '#menu-pizzalayer-topping-' + shortSlug ).addClass( 'ToppingSelected' );
		$( '#' + layerName ).removeClass( 'tcg-half-left tcg-half-right tcg-whole tcg-quarter-topleft tcg-quarter-topright tcg-quarter-bottomleft tcg-quarter-bottomright' );
		var coverage = $( "input[type='radio']:checked", '#pztp-topcoverage-control-' + shortSlug ).val();
		$( '#' + layerName ).addClass( 'tcg-' + coverage );
		$( '#CurrentToppingsCount' ).val( currentCount + 1 );
	}

	function RemovePizzaLayer( layerName, zIndex, shortSlug ) {
		$( '.' + layerName ).fadeOut( 1200 ).remove();
		$( 'li#current-topping-' + layerName ).fadeOut( 900 ).remove();
		$( '.pizza-topping-li-' + shortSlug ).fadeOut( 600 ).remove();
		$( '#menu-pizzalayer-topping-' + shortSlug ).removeClass( 'ToppingSelected' );
		var currentCount = parseInt( $( '#CurrentToppingsCount' ).val(), 10 ) - 1;
		$( '#CurrentToppingsCount' ).val( currentCount );
		var max = $( '#MaxToppings' ).val();
		if ( currentCount < max ) {
			$( '#pizzalayer-alert' ).fadeOut( 500 );
		} else {
			$( '#pizzalayer-alert' ).fadeIn( 500 );
		}
	}

	function RemoveAllToppings() {
		$( '.pizzalayer-topping' ).fadeOut( 600 ).remove();
		$( '#CurrentToppingsCount' ).val( 0 );
	}

	function SwapBasePizzaLayer( targetLayer, name, imageUrl ) {
		var wrapped   = 'url(' + imageUrl + ')';
		var titleId   = targetLayer.replace( 'pizzalayer-base-layer-', 'pizzalayer-basics-tile-title-' );
		var typeSlug  = targetLayer.replace( 'pizzalayer-base-layer-', '' );
		$( '#' + targetLayer ).fadeOut( 100 ).delay( 20 ).css( 'backgroundImage', wrapped ).delay( 20 ).fadeIn( 900 );
		$( '#' + titleId ).html( name );
		var newShort = 'menu-pizzalayer-topping-' + convertToSlug( name );
		$( '.pizzalayer-' + typeSlug + 's-list li' ).removeClass( 'ToppingSelected' );
		$( '#' + newShort ).addClass( 'ToppingSelected' );
	}

	function ChangeSlicing( targetLayer, name, imageUrl ) {
		var wrapped = 'url(' + imageUrl + ')';
		$( '#' + targetLayer ).fadeOut( 100 ).css( 'backgroundImage', wrapped ).fadeIn( 400 );
		$( '#' + targetLayer ).parent().append( $( '#' + targetLayer ) );
	}

	function SetToppingCoverage( area, toppingId, toppingShort ) {
		$( '#' + toppingId ).removeClass( 'tcg-half-left tcg-half-right tcg-whole tcg-quarter-top-left tcg-quarter-top-right tcg-quarter-bottom-left tcg-quarter-bottom-right' );
		$( '#' + toppingId ).addClass( 'tcg-' + area );
		var toppingShortSlug = toppingId.replace( 'pizzalayer-topping-', '' );
		var radioId          = 'halfcontrol-' + toppingShortSlug + '-' + area;
		$( '#pizzalayer-halves-control-halfcontrol-' + toppingShortSlug + ' img.pizzalayer-halves-control' )
			.removeClass( 'pizzalayer-halves-control-highlighted' );
		$( '#pizzalayer-halves-control-halfcontrol-' + toppingShortSlug + ' img.pizzalayer-halves-control-' + area )
			.addClass( 'pizzalayer-halves-control-highlighted' );
		var areaNoQuarter = area.replace( 'quarter-', '' );
		var imgSrc = $( '#topping-' + toppingShort + '-halves-control-button-' + areaNoQuarter ).attr( 'src' );
		$( '#topping-fraction-thumb-' + toppingShort ).attr( 'src', imgSrc );
		$( '#' + radioId )[ 0 ].checked = true;
	}

	function OpenToppingFractionBox( toppingId ) {
		$( '#pizzalayer-halves-control-halfcontrol-' + toppingId ).fadeOut( 999 );
		$( '#pizzalayer-halves-control-fraction-' + toppingId ).fadeIn( 1200 );
	}

	function CloseToppingFractionBox( toppingId ) {
		$( '#pizzalayer-halves-control-halfcontrol-' + toppingId ).fadeIn( 999 );
		$( '#pizzalayer-halves-control-fraction-' + toppingId ).fadeOut( 1200 );
	}

	// ── Pizza Rotation ────────────────────────────────────────────────
	/*
	 * Usage:
	 *   window.RotatePizza( 'myPizzaDiv', 2 );  // faster
	 *   window.RotatePizza( 'myPizzaDiv', 0.5 ); // slower
	 *   window.StopPizza( 'myPizzaDiv' );
	 */
	function RotatePizza( divId, speed ) {
		if ( speed === undefined ) { speed = 1; }
		var el = document.getElementById( divId );
		if ( ! el ) {
			window.console && window.console.error( 'PizzaLayer: RotatePizza — element not found:', divId );
			return;
		}
		var angle = 0;
		function rotate() {
			angle = ( angle + speed ) % 360;
			el.style.transform = 'rotate(' + angle + 'deg)';
			rotationIntervals[ divId ] = requestAnimationFrame( rotate );
		}
		rotate();
	}

	function StopPizza( divId ) {
		if ( rotationIntervals[ divId ] ) {
			cancelAnimationFrame( rotationIntervals[ divId ] );
			delete rotationIntervals[ divId ];
		}
	}

	// ── Global exports ────────────────────────────────────────────────
	// Only these three are exposed because template PHP renders them in onclick= attributes.
	// All other functions remain private to this IIFE.
	window.ClearPizza = function () {
		$( '#pizzalayer-pizza .pizzalayer-sauce,' +
			'#pizzalayer-pizza .pizzalayer-cheese,' +
			'#pizzalayer-pizza .pizzalayer-drizzle,' +
			'#pizzalayer-pizza .pizzalayer-cut' ).css( { background: 'none' } );
		$( '#pizzalayer-pizza .pizzalayer-topping' ).fadeOut( 900 ).remove();
		$( '#pizzalayer-current-toppings *' ).fadeOut( 600 ).remove();
		$( '.pizzalayer-toppings-list-linkboxes .pizza-topping,' +
			'.pizzalayer-ui-menu-tab .pizzalayer-topping,' +
			'.pizzalayer-inner-tile' ).removeClass( 'ToppingSelected' );
		$( '#pizzalayer-basics-tile-title-crust' ).html( 'No Crust Chosen' );
		$( '#pizzalayer-basics-tile-title-sauce' ).html( 'No Sauce Chosen' );
		$( '#pizzalayer-basics-tile-title-cheese' ).html( 'No Cheese Chosen' );
		$( '#pizzalayer-basics-tile-title-drizzle' ).html( 'No Drizzle Chosen' );
		$( '#CurrentToppingsCount' ).val( 0 );
	};
	window.RotatePizza = RotatePizza;
	window.StopPizza   = StopPizza;
	// Legacy compatibility: also expose via window for any external code calling these directly.
	// These should not be relied on in new integrations.
	window.RemovePizzaLayer       = RemovePizzaLayer;
	window.AddPizzaLayer          = AddPizzaLayer;
	window.SwapPizzaLayer         = SwapPizzaLayer;
	window.SwapBasePizzaLayer     = SwapBasePizzaLayer;
	window.ChangeSlicing          = ChangeSlicing;
	window.SetToppingCoverage     = SetToppingCoverage;
	window.OpenToppingFractionBox = OpenToppingFractionBox;
	window.CloseToppingFractionBox= CloseToppingFractionBox;
	window.RemoveAllToppings      = RemoveAllToppings;

} )( jQuery );

