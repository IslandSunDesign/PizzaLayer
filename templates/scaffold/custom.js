/* Scaffold Template — Builder JS
 * Instance config is read from data-sc-cfg attribute on .sc-root.
 * This file is enqueued by AssetManager; no inline <script> blocks.
 */

/** HTML-escape a value before inserting into innerHTML. */
function scEscHtml( s ) {
  return String( s )
    .replace( /&/g, '&amp;' )
    .replace( /</g, '&lt;' )
    .replace( />/g, '&gt;' )
    .replace( /"/g, '&quot;' )
    .replace( /'/g, '&#39;' );
}

/* initScaffoldInstance — run once per .sc-root on this page. */
function initScaffoldInstance( ROOT, cfg ) {
  'use strict';

  var VAR      = cfg.varName;
  var DEFAULTS = cfg.defaults;
  var MAX_TOP  = cfg.maxToppings;
  var TOPPINGS = cfg.toppings;

  if ( ! ROOT ) { return; }

  /** Activate a tab and show its panel, hide all others. */
  function activateTab( slug ) {
    ROOT.querySelectorAll( '.sc-tab-btn' ).forEach( function( btn ) {
      var active = btn.getAttribute( 'data-tab' ) === slug;
      btn.classList.toggle( 'sc-tab-btn--active', active );
      btn.setAttribute( 'aria-selected', active ? 'true' : 'false' );
    } );
    ROOT.querySelectorAll( '.sc-panel' ).forEach( function( panel ) {
      var show = panel.getAttribute( 'data-panel' ) === slug;
      if ( show ) { panel.removeAttribute( 'hidden' ); }
      else        { panel.setAttribute( 'hidden', '' ); }
    } );
  }

  /** Swap an exclusive base layer (crust/sauce/cheese/drizzle/slicing). */
  function swapBase( layerType, slug, title, layerImg, triggerEl ) {
    // Deselect previous card
    ROOT.querySelectorAll( '.sc-card--exclusive[data-layer="' + layerType + '"]' ).forEach( function( c ) {
      c.classList.remove( 'sc-card--selected' );
    } );
    // Select this card
    var card = ( triggerEl && triggerEl.closest ) ? triggerEl.closest( '.sc-card' ) : null;
    if ( card ) { card.classList.add( 'sc-card--selected' ); }

    // Update layer image in pizza stage
    var img = document.getElementById( ROOT.id + '-layer-' + layerType );
    if ( img ) {
      img.src = layerImg;
      img.style.display = layerImg ? 'block' : 'none';
    }

    updateSummary();
    ROOT.dispatchEvent( new CustomEvent( 'pizzalayer:layerChanged', { detail: { layerType: layerType, slug: slug, title: title, layerImg: layerImg }, bubbles: true } ) );
  }

  /** Remove an exclusive base layer. */
  function removeBase( layerType, slug, triggerEl ) {
    var card = ( triggerEl && triggerEl.closest ) ? triggerEl.closest( '.sc-card' ) : null;
    if ( card ) { card.classList.remove( 'sc-card--selected' ); }
    var img = document.getElementById( ROOT.id + '-layer-' + layerType );
    if ( img ) { img.src = ''; img.style.display = 'none'; }
    updateSummary();
  }

  /** Add a topping. */
  function addTopping( zindex, slug, layerImg, title, layerId, inputId, triggerEl ) {
    var selected = ROOT.querySelectorAll( '.sc-card--topping.sc-card--selected' ).length;
    if ( selected >= MAX_TOP ) {
      ROOT.dispatchEvent( new CustomEvent( 'pizzalayer:maxToppings', { detail: { max: MAX_TOP }, bubbles: true } ) );
      return;
    }
    var card = ( triggerEl && triggerEl.closest ) ? triggerEl.closest( '.sc-card' ) : null;
    if ( card ) {
      card.classList.add( 'sc-card--selected' );
      var addBtn = card.querySelector( '.sc-card__btn--add' );
      var remBtn = card.querySelector( '.sc-card__btn--remove' );
      var covEl  = card.querySelector( '.sc-coverage' );
      if ( addBtn ) { addBtn.style.display = 'none'; }
      if ( remBtn ) { remBtn.style.display = ''; }
      if ( covEl )  { covEl.style.display = ''; }
    }
    // Inject layer image into stage
    var stage = document.getElementById( ROOT.id + '-stage' );
    if ( stage && layerImg ) {
      var existing = stage.querySelector( '[data-topping-slug="' + slug + '"]' );
      if ( ! existing ) {
        var el = document.createElement( 'img' );
        el.id                        = ROOT.id + '-tslot-' + slug;
        el.className                 = 'sc-layer sc-layer--topping';
        el.src                       = layerImg;
        el.alt                       = title;
        el.setAttribute( 'data-topping-slug', slug );
        el.style.zIndex              = String( zindex );
        stage.appendChild( el );
      }
    }
    updateSummary();
    ROOT.dispatchEvent( new CustomEvent( 'pizzalayer:toppingAdded', { detail: { slug: slug, title: title }, bubbles: true } ) );
  }

  /** Remove a topping. */
  function removeTopping( layerId, slug, triggerEl ) {
    var card = ( triggerEl && triggerEl.closest ) ? triggerEl.closest( '.sc-card' ) : null;
    if ( card ) {
      card.classList.remove( 'sc-card--selected' );
      var addBtn = card.querySelector( '.sc-card__btn--add' );
      var remBtn = card.querySelector( '.sc-card__btn--remove' );
      var covEl  = card.querySelector( '.sc-coverage' );
      if ( addBtn ) { addBtn.style.display = ''; }
      if ( remBtn ) { remBtn.style.display = 'none'; }
      if ( covEl )  { covEl.style.display = 'none'; }
    }
    var layerEl = document.getElementById( ROOT.id + '-tslot-' + slug );
    if ( layerEl ) { layerEl.remove(); }
    updateSummary();
    ROOT.dispatchEvent( new CustomEvent( 'pizzalayer:toppingRemoved', { detail: { slug: slug }, bubbles: true } ) );
  }

  /** Set coverage fraction on a selected topping. */
  function setCoverage( slug, fraction, triggerEl ) {
    var card = ( triggerEl && triggerEl.closest ) ? triggerEl.closest( '.sc-card' ) : null;
    if ( card ) {
      card.setAttribute( 'data-coverage', fraction );
      card.querySelectorAll( '.sc-cov-btn' ).forEach( function( b ) {
        b.classList.toggle( 'sc-cov-btn--active', b.getAttribute( 'data-fraction' ) === fraction );
      } );
    }
    // TODO: pass fraction through to layer clip-path for visual coverage
    ROOT.dispatchEvent( new CustomEvent( 'pizzalayer:coverageSet', { detail: { slug: slug, fraction: fraction }, bubbles: true } ) );
  }

  /** Collect current state as a plain object. */
  function getState() {
    var state = { layers: {}, toppings: [] };
    ROOT.querySelectorAll( '.sc-card--exclusive.sc-card--selected' ).forEach( function( c ) {
      state.layers[ c.getAttribute( 'data-layer' ) ] = {
        slug:  c.getAttribute( 'data-slug' ),
        title: c.getAttribute( 'data-title' ),
        img:   c.getAttribute( 'data-layer-img' ),
      };
    } );
    ROOT.querySelectorAll( '.sc-card--topping.sc-card--selected' ).forEach( function( c ) {
      state.toppings.push( {
        slug:     c.getAttribute( 'data-slug' ),
        title:    c.getAttribute( 'data-title' ),
        img:      c.getAttribute( 'data-layer-img' ),
        coverage: c.getAttribute( 'data-coverage' ) || 'whole',
      } );
    } );
    return state;
  }

  /** Update the summary panel list. */
  function updateSummary() {
    var list  = document.getElementById( ROOT.id + '-summary-rows' );
    var empty = ROOT.querySelector( '.sc-summary__empty' );
    if ( ! list ) { return; }

    var state = getState();
    var rows  = '';
    var layerLabels = { crust:'Crust', sauce:'Sauce', cheese:'Cheese', drizzle:'Drizzle', slicing:'Slicing' };

    Object.keys( state.layers ).forEach( function( ltype ) {
      var l = state.layers[ ltype ];
      var label = ( layerLabels[ ltype ] || ltype );
      rows += '<li class="sc-summary__row"><span class="sc-summary__layer-type">' + scEscHtml( label ) + '</span><span class="sc-summary__layer-name">' + scEscHtml( l.title ) + '</span></li>';
    } );
    state.toppings.forEach( function( t ) {
      rows += '<li class="sc-summary__row sc-summary__row--topping"><span class="sc-summary__layer-type">Topping</span><span class="sc-summary__layer-name">' + scEscHtml( t.title ) + '</span><span class="sc-summary__coverage">' + scEscHtml( t.coverage ) + '</span></li>';
    } );

    list.innerHTML = rows;
    var hasContent = !! rows;
    if ( empty ) { empty.style.display = hasContent ? 'none' : ''; }

    ROOT.dispatchEvent( new CustomEvent( 'pizzalayer:stateChanged', { detail: getState(), bubbles: true } ) );
  }

  /** Reset all choices. */
  function resetAll() {
    ROOT.querySelectorAll( '.sc-card--selected' ).forEach( function( c ) { c.classList.remove( 'sc-card--selected' ); } );
    ROOT.querySelectorAll( '.sc-card__btn--add'    ).forEach( function( b ) { b.style.display = ''; } );
    ROOT.querySelectorAll( '.sc-card__btn--remove' ).forEach( function( b ) { b.style.display = 'none'; } );
    ROOT.querySelectorAll( '.sc-coverage'          ).forEach( function( c ) { c.style.display = 'none'; } );
    ROOT.querySelectorAll( '.sc-layer' ).forEach( function( img ) { img.src = ''; img.style.display = 'none'; } );
    // Remove injected topping layers
    ROOT.querySelectorAll( '.sc-layer--topping' ).forEach( function( el ) { el.remove(); } );
    updateSummary();
    ROOT.dispatchEvent( new CustomEvent( 'pizzalayer:reset', { bubbles: true } ) );
  }

  // ── Public API ──────────────────────────────────────────────────────────────
  window[ VAR ] = {
    activateTab:   activateTab,
    swapBase:      swapBase,
    removeBase:    removeBase,
    addTopping:    addTopping,
    removeTopping: removeTopping,
    setCoverage:   setCoverage,
    getState:      getState,
    resetAll:      resetAll,
  };

  // ── Wire tab clicks ─────────────────────────────────────────────────────────
  ROOT.querySelectorAll( '.sc-tab-btn' ).forEach( function( btn ) {
    btn.addEventListener( 'click', function() {
      activateTab( btn.getAttribute( 'data-tab' ) );
    } );
  } );

  // ── Activate first tab ──────────────────────────────────────────────────────
  var firstTab = ROOT.querySelector( '.sc-tab-btn' );
  if ( firstTab ) { activateTab( firstTab.getAttribute( 'data-tab' ) ); }

  // ── Apply defaults ──────────────────────────────────────────────────────────
  (function applyDefaults() {
    Object.keys( DEFAULTS ).forEach( function( layer ) {
      var defaultSlug = DEFAULTS[ layer ];
      if ( ! defaultSlug ) { return; }
      var card = ROOT.querySelector( '.sc-card--exclusive[data-layer="' + layer + '"][data-slug="' + defaultSlug + '"]' );
      if ( ! card ) { return; }
      var btn = card.querySelector( '.sc-card__btn--select' );
      if ( btn ) { btn.click(); }
    } );
  })();

}

/* Boot — initialise every .sc-root[data-sc-cfg] on the page. */
document.querySelectorAll( '.sc-root[data-sc-cfg]' ).forEach( function( rootEl ) {
  try {
    var cfg = JSON.parse( rootEl.getAttribute( 'data-sc-cfg' ) );
    initScaffoldInstance( rootEl, cfg );
  } catch(e) {
    // eslint-disable-next-line no-console
    if ( window.console ) { console.warn( 'PizzaLayer Scaffold: config parse error', e ); }
  }
} );
