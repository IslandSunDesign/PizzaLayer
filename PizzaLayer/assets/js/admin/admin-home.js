/* global jQuery */
( function ( $ ) {
	'use strict';

	// Layer Manager tabs
	var tabs   = document.querySelectorAll( '.plh-tab' );
	var panels = document.querySelectorAll( '.plh-panel' );

	tabs.forEach( function ( tab ) {
		tab.addEventListener( 'click', function () {
			tabs.forEach( function ( t ) {
				t.classList.remove( 'plh-tab--active' );
				t.setAttribute( 'aria-selected', 'false' );
			} );
			panels.forEach( function ( p ) {
				p.classList.remove( 'plh-panel--active' );
			} );
			tab.classList.add( 'plh-tab--active' );
			tab.setAttribute( 'aria-selected', 'true' );
			var panel = document.getElementById( 'plh-panel-' + tab.dataset.tab );
			if ( panel ) {
				panel.classList.add( 'plh-panel--active' );
			}
		} );
	} );

	// Tips rotator
	$( function () {
		$( '.pizzalayer-rotator' ).each( function () {
			var $rot  = $( this );
			var ms    = parseInt( $rot.attr( 'data-interval' ), 10 ) || 6000;
			var $sl   = $rot.find( '.pz-rotator-slide' );
			var $dots = $rot.closest( '.plh-rotator-wrap' ).find( '.plh-rotator-dot' );
			var idx   = 0;
			var busy  = false;

			$sl.hide().attr( 'aria-hidden', 'true' );
			$sl.first().show().addClass( 'is-active' ).attr( 'aria-hidden', 'false' );

			function advance() {
				if ( busy ) { return; }
				busy = true;
				var $cur  = $sl.eq( idx );
				var next  = ( idx + 1 ) % $sl.length;
				var $next = $sl.eq( next );
				$dots.eq( idx ).removeClass( 'is-active' );
				$cur.fadeOut( 350, function () {
					$cur.removeClass( 'is-active' ).attr( 'aria-hidden', 'true' );
					$next.fadeIn( 350, function () {
						$next.addClass( 'is-active' ).attr( 'aria-hidden', 'false' );
						$dots.eq( next ).addClass( 'is-active' );
						idx  = next;
						busy = false;
					} );
				} );
			}

			setInterval( advance, ms );
		} );
	} );

} )( jQuery );
