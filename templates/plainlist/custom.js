/**
 * Plainlist Template — custom.js
 *
 * Text-only checklist interaction: exclusive toggles, multi-select toppings,
 * step-by-step wizard navigation, and a live selection summary.
 *
 * The PL namespace exposes createInstance(instanceId, opts) which is called
 * inline from pztp-containers-menu.php after the builder HTML.
 *
 * Compatible with the broader PizzaLayer JS ecosystem:
 *   - Calls ClearPizza() / AddPizzaLayer() / RemovePizzaLayer() if available.
 *   - Dispatches 'pizzalayer:selection_changed' on the root element.
 */

/* jshint browser:true */
/* global ClearPizza, AddPizzaLayer, RemovePizzaLayer */

(function ( window, document ) {
	'use strict';

	// ── State store per instance ──────────────────────────────────────
	var instances = {};

	/**
	 * Create a Plainlist instance.
	 *
	 * @param {string} instanceId  The data-instance value on .pl-root
	 * @param {Object} opts        Configuration passed from PHP
	 */
	function createInstance( instanceId, opts ) {

		var root = document.getElementById( instanceId );
		if ( ! root ) { return null; }

		var cfg = {
			tabs:          opts.tabs          || [],
			maxToppings:   opts.maxToppings   || 99,
			stepMode:      opts.stepMode      || false,
			requireSelect: opts.requireSelect || false,
			showSummary:   opts.showSummary   !== false
		};

		// ── Per-instance state ────────────────────────────────────────
		var state = {
			exclusive: {},   // layer_type → { slug, title, layerUrl }
			toppings:  {},   // slug → { title, layerUrl, zindex }
			currentStep: 0
		};

		// ── DOM helpers ───────────────────────────────────────────────

		function q( sel ) { return root.querySelector( sel ); }
		function qa( sel ) { return Array.prototype.slice.call( root.querySelectorAll( sel ) ); }

		var summaryList  = document.getElementById( instanceId + '-summary-list' );
		var toppingCount = document.getElementById( instanceId + '-topping-count' );
		var progressBar  = document.getElementById( instanceId + '-progress-bar' );
		var progressCurr = q( '.pl-progress__current' );
		var stepPrev     = document.getElementById( instanceId + '-step-prev' );
		var stepNext     = document.getElementById( instanceId + '-step-next' );
		var stepSections = qa( '.pl-section--step' );

		// ── Core toggles ──────────────────────────────────────────────

		/**
		 * Toggle an exclusive-select item (crust/sauce/cheese/drizzle/cut).
		 * Deselects the previously selected item in the same section.
		 */
		function plToggleExclusive( layerType, slug, title, layerUrl, itemEl ) {
			var isSelected = itemEl.classList.contains( 'pl-item--selected' );

			// Deselect previous in this layer
			var prev = root.querySelector( '.pl-item--exclusive[data-layer="' + layerType + '"].pl-item--selected' );
			if ( prev ) {
				prev.classList.remove( 'pl-item--selected' );
				prev.setAttribute( 'aria-checked', 'false' );
				var prevInput = prev.querySelector( '.pl-item__input' );
				if ( prevInput ) { prevInput.checked = false; }
				// Notify layer system
				if ( typeof RemovePizzaLayer === 'function' && state.exclusive[ layerType ] ) {
					try { RemovePizzaLayer( layerType, state.exclusive[ layerType ].slug ); } catch(e) {}
				}
				delete state.exclusive[ layerType ];
			}

			if ( ! isSelected ) {
				// Select this item
				itemEl.classList.add( 'pl-item--selected' );
				itemEl.setAttribute( 'aria-checked', 'true' );
				var input = itemEl.querySelector( '.pl-item__input' );
				if ( input ) { input.checked = true; }
				state.exclusive[ layerType ] = { slug: slug, title: title, layerUrl: layerUrl };
				// Notify layer system
				if ( layerUrl && typeof AddPizzaLayer === 'function' ) {
					try { AddPizzaLayer( layerType, slug, layerUrl, title ); } catch(e) {}
				}
			}

			refreshSummary();
			dispatchChange();
			refreshStepNext();
		}

		/**
		 * Toggle a topping item (multi-select with max limit).
		 */
		function plToggleTopping( zindex, slug, layerUrl, title, layerId, _layerId2, _thumbUrl, itemEl ) {
			var isSelected = itemEl.classList.contains( 'pl-item--selected' );

			if ( isSelected ) {
				// Remove
				itemEl.classList.remove( 'pl-item--selected' );
				itemEl.setAttribute( 'aria-checked', 'false' );
				var input = itemEl.querySelector( '.pl-item__input' );
				if ( input ) { input.checked = false; }
				delete state.toppings[ slug ];
				if ( typeof RemovePizzaLayer === 'function' ) {
					try { RemovePizzaLayer( layerId, slug ); } catch(e) {}
				}
			} else {
				// Check max
				var currentCount = Object.keys( state.toppings ).length;
				if ( currentCount >= cfg.maxToppings ) {
					root.dispatchEvent( new CustomEvent( 'pizzalayer:max_toppings', { bubbles: true, detail: { instanceId: instanceId } } ) );
					return;
				}
				// Add
				itemEl.classList.add( 'pl-item--selected' );
				itemEl.setAttribute( 'aria-checked', 'true' );
				var inp = itemEl.querySelector( '.pl-item__input' );
				if ( inp ) { inp.checked = true; }
				state.toppings[ slug ] = { title: title, layerUrl: layerUrl, zindex: zindex };
				if ( layerUrl && typeof AddPizzaLayer === 'function' ) {
					try { AddPizzaLayer( 'topping', slug, layerUrl, title, zindex ); } catch(e) {}
				}
			}

			// Update topping badge
			var count = Object.keys( state.toppings ).length;
			if ( toppingCount ) {
				toppingCount.textContent = count;
				toppingCount.style.display = count > 0 ? '' : 'none';
			}

			refreshSummary();
			dispatchChange();
		}

		/**
		 * Reset all selections.
		 */
		function plReset() {
			// Deselect all items
			qa( '.pl-item--selected' ).forEach( function( el ) {
				el.classList.remove( 'pl-item--selected' );
				el.setAttribute( 'aria-checked', 'false' );
				var inp = el.querySelector( '.pl-item__input' );
				if ( inp ) { inp.checked = false; }
			} );

			state.exclusive = {};
			state.toppings  = {};

			if ( toppingCount ) {
				toppingCount.textContent = '0';
				toppingCount.style.display = 'none';
			}

			if ( typeof ClearPizza === 'function' ) {
				try { ClearPizza(); } catch(e) {}
			}

			refreshSummary();
			dispatchChange();
			refreshStepNext();
		}

		// ── Summary ───────────────────────────────────────────────────

		function refreshSummary() {
			if ( ! cfg.showSummary || ! summaryList ) { return; }

			var items = [];

			// Exclusive layers in tab order
			var exclusiveOrder = [ 'crust', 'sauce', 'cheese', 'drizzle', 'cut' ];
			exclusiveOrder.forEach( function( layer ) {
				if ( state.exclusive[ layer ] ) {
					items.push( { section: layer.charAt(0).toUpperCase() + layer.slice(1), title: state.exclusive[ layer ].title } );
				}
			} );

			// Toppings
			Object.keys( state.toppings ).forEach( function( slug ) {
				items.push( { section: 'Topping', title: state.toppings[ slug ].title } );
			} );

			if ( items.length === 0 ) {
				summaryList.innerHTML = '<li class="pl-summary__empty">' +
					( summaryList.getAttribute( 'data-empty-text' ) || 'No items selected yet.' ) + '</li>';
				return;
			}

			var html = '';
			items.forEach( function( item ) {
				html += '<li class="pl-summary__item">' +
					'<span class="pl-summary__item-section">' + escHtml( item.section ) + '</span>' +
					'<span class="pl-summary__item-title">' + escHtml( item.title ) + '</span>' +
					'</li>';
			} );
			summaryList.innerHTML = html;
		}

		// ── Step mode ─────────────────────────────────────────────────

		function goToStep( index ) {
			if ( ! cfg.stepMode || stepSections.length === 0 ) { return; }
			index = Math.max( 0, Math.min( stepSections.length - 1, index ) );
			state.currentStep = index;

			stepSections.forEach( function( el, i ) {
				var active = ( i === index );
				el.classList.toggle( 'pl-section--active', active );
				el.setAttribute( 'aria-hidden', active ? 'false' : 'true' );
			} );

			if ( stepPrev ) { stepPrev.disabled = ( index === 0 ); }
			if ( stepNext ) { stepNext.textContent = ( index === stepSections.length - 1 ) ? '✓ Done' : ( stepNext.getAttribute( 'data-label-next' ) || 'Next →' ); }

			// Progress
			var pct = Math.round( ( ( index + 1 ) / stepSections.length ) * 100 );
			if ( progressBar ) { progressBar.style.width = pct + '%'; }
			if ( progressCurr ) { progressCurr.textContent = index + 1; }

			refreshStepNext();
		}

		function refreshStepNext() {
			if ( ! cfg.stepMode || ! cfg.requireSelect || ! stepNext ) { return; }
			var currentSection = stepSections[ state.currentStep ];
			if ( ! currentSection ) { stepNext.disabled = false; return; }
			var tab = currentSection.getAttribute( 'data-section' );
			var isExclusiveTab = ( tab !== 'toppings' );
			if ( isExclusiveTab ) {
				var hasSelection = !! ( state.exclusive[ tab === 'slicing' ? 'cut' : tab ] );
				stepNext.disabled = ! hasSelection;
			} else {
				stepNext.disabled = false; // toppings are optional
			}
		}

		// ── Event wiring ──────────────────────────────────────────────

		if ( cfg.stepMode ) {
			if ( stepPrev ) {
				stepPrev.addEventListener( 'click', function() {
					goToStep( state.currentStep - 1 );
				} );
			}
			if ( stepNext ) {
				stepNext.setAttribute( 'data-label-next', stepNext.textContent );
				stepNext.addEventListener( 'click', function() {
					if ( state.currentStep < stepSections.length - 1 ) {
						goToStep( state.currentStep + 1 );
					}
				} );
			}
			goToStep( 0 );
		}

		// ── Dispatch helper ───────────────────────────────────────────

		function dispatchChange() {
			var detail = {
				instanceId: instanceId,
				exclusive:  state.exclusive,
				toppings:   state.toppings
			};
			root.dispatchEvent( new CustomEvent( 'pizzalayer:selection_changed', { bubbles: true, detail: detail } ) );
		}

		// ── Utility ───────────────────────────────────────────────────

		function escHtml( str ) {
			return String( str )
				.replace( /&/g, '&amp;' )
				.replace( /</g, '&lt;' )
				.replace( />/g, '&gt;' )
				.replace( /"/g, '&quot;' );
		}

		// ── Public API ────────────────────────────────────────────────
		var api = {
			plToggleExclusive: plToggleExclusive,
			plToggleTopping:   plToggleTopping,
			plReset:           plReset,
			getState:          function() {
				/* Return both the raw state (for internal use) and a normalised
				   layers array so PizzaLayerPro frontend-builder.js can read
				   selections via the standard getTemplateLayersNow() path. */
				var layers = [];
				/* Exclusive layers: crust, sauce, cheese, drizzle, cut */
				Object.keys( state.exclusive ).forEach( function( layerType ) {
					var e = state.exclusive[ layerType ];
					if ( e && e.slug ) {
						layers.push({
							id:        e.slug,
							layerId:   e.slug,
							title:     e.title  || e.slug,
							layerName: e.title  || e.slug,
							type:      layerType,
							layerType: layerType,
							fraction:  'Whole',
							coverage:  'whole'
						});
					}
				});
				/* Toppings */
				Object.keys( state.toppings ).forEach( function( slug ) {
					var t = state.toppings[ slug ];
					layers.push({
						id:        slug,
						layerId:   slug,
						title:     t.title  || slug,
						layerName: t.title  || slug,
						type:      'topping',
						layerType: 'topping',
						fraction:  t.coverage || 'whole',
						coverage:  t.coverage || 'whole'
					});
				});
				return {
					exclusive:    state.exclusive,
					toppings:     state.toppings,
					currentStep:  state.currentStep,
					layers:       layers
				};
			}
		};

		instances[ instanceId ] = api;
		return api;
	}

	// ── Expose namespace ──────────────────────────────────────────────
	window.PL = window.PL || {};
	window.PL.createInstance = createInstance;

	/* PizzaLayerAPI — standard surface consumed by PizzaLayerPro */
	window.PizzaLayerAPI = window.PizzaLayerAPI || {
		getState: function ( instanceId ) {
			var inst = instances[ instanceId ];
			return inst ? inst.getState() : null;
		},
		getAllInstances: function () {
			return Object.keys( instances );
		}
	};

}( window, document ) );
