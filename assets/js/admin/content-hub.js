( function () {
	'use strict';

	var cfg      = window.pizzalayerContentHub || {};
	var NONCE    = cfg.nonce    || '';
	var AJAX_URL = cfg.ajaxUrl  || '';
	var CPT_DATA = cfg.cptData  || {};
	var current  = cfg.active   || '';

	var $rail        = document.querySelector( '.plch-rail' );
	var $panel       = document.getElementById( 'plch-panel-content' );
	var $loading     = document.getElementById( 'plch-loading' );
	var $header      = document.getElementById( 'plch-header' );
	var $headerIcon  = document.querySelector( '.plch-header-icon' );
	var $headerLabel = document.getElementById( 'plch-header-label' );
	var $headerDesc  = document.getElementById( 'plch-header-desc' );
	var $addBtn      = document.getElementById( 'plch-add-btn' );
	var $addSingular = document.getElementById( 'plch-add-singular' );

	var panelCache  = {};
	if ( $panel ) {
		panelCache[ current ] = $panel.innerHTML;
	}

	function showLoading() {
		if ( $loading ) { $loading.style.display = 'flex'; }
		if ( $panel )   { $panel.classList.add( 'plch-fading' ); }
	}

	function hideLoading() {
		if ( $loading ) { $loading.style.display = 'none'; }
		if ( $panel )   { $panel.classList.remove( 'plch-fading' ); }
	}

	function setActiveRailItem( slug ) {
		var meta = CPT_DATA[ slug ];
		if ( ! meta ) { return; }

		document.querySelectorAll( '.plch-rail__item' ).forEach( function ( el ) {
			var s        = el.getAttribute( 'data-slug' );
			var isActive = ( s === slug );
			el.classList.toggle( 'plch-rail__item--active', isActive );
			el.setAttribute( 'aria-current', isActive ? 'page' : 'false' );

			var icon = el.querySelector( '.plch-rail__icon' );
			var m    = CPT_DATA[ s ];
			if ( m && icon ) {
				icon.style.background = isActive ? m.color + '20' : '';
				icon.style.color      = isActive ? m.color : '';
			}

			var count = el.querySelector( '.plch-rail__count' );
			if ( count ) {
				count.style.background = isActive ? '#dce8f7' : '';
				count.style.color      = isActive ? '#2271b1' : '';
			}
		} );

		if ( $headerIcon )  { $headerIcon.className = 'dashicons ' + meta.icon + ' plch-header-icon'; $headerIcon.style.color = meta.color; }
		if ( $headerLabel ) { $headerLabel.textContent = meta.label; }
		if ( $headerDesc )  { $headerDesc.textContent  = meta.desc; }
		if ( $addBtn )      { $addBtn.href = meta.addUrl; }
		if ( $addSingular ) { $addSingular.textContent = meta.singular; }
		if ( $header )      { $header.style.borderColor = meta.color + '60'; }
	}

	function reinitTableLinks( slug ) {
		var hubBase = AJAX_URL.replace( 'admin-ajax.php', 'admin.php' ) + '?page=pizzalayer-content';
		document.querySelectorAll( '.plch-main .wp-list-table th a' ).forEach( function ( a ) {
			try {
				var url     = new URL( a.href, window.location.href );
				var orderby = url.searchParams.get( 'orderby' );
				var order   = url.searchParams.get( 'order' );
				if ( orderby ) {
					a.href = hubBase + '&pl_cpt=' + encodeURIComponent( slug ) +
					         '&orderby=' + encodeURIComponent( orderby ) +
					         '&order=' + encodeURIComponent( order || 'asc' );
				}
			} catch ( e ) {}
		} );
	}

	function loadPanel( slug ) {
		if ( slug === current ) { return; }

		if ( panelCache[ slug ] ) {
			current = slug;
			setActiveRailItem( slug );
			if ( $panel ) { $panel.innerHTML = panelCache[ slug ]; }
			reinitTableLinks( slug );
			history.replaceState( null, '', window.location.pathname + '?page=pizzalayer-content&pl_cpt=' + slug );
			return;
		}

		document.querySelectorAll( '.plch-rail__item' ).forEach( function ( el ) {
			el.classList.toggle(
				'plch-rail__item--loading',
				el.getAttribute( 'data-slug' ) !== current && el.getAttribute( 'data-slug' ) !== slug
			);
		} );
		showLoading();

		var formData = new FormData();
		formData.append( 'action', 'pizzalayer_content_panel' );
		formData.append( 'nonce', NONCE );
		formData.append( 'cpt', slug );

		fetch( AJAX_URL, { method: 'POST', body: formData } )
			.then( function ( r ) { return r.json(); } )
			.then( function ( d ) {
				if ( d.success && d.data && d.data.html ) {
					panelCache[ slug ] = d.data.html;
					current = slug;
					setActiveRailItem( slug );
					if ( $panel ) { $panel.innerHTML = d.data.html; }
					reinitTableLinks( slug );
					history.replaceState( null, '', window.location.pathname + '?page=pizzalayer-content&pl_cpt=' + slug );
				}
			} )
			.catch( function ( err ) { console.error( 'PizzaLayer ContentHub:', err ); } )
			.finally( function () {
				hideLoading();
				document.querySelectorAll( '.plch-rail__item' ).forEach( function ( el ) {
					el.classList.remove( 'plch-rail__item--loading' );
				} );
			} );
	}

	if ( $rail ) {
		$rail.addEventListener( 'click', function ( e ) {
			var item = e.target.closest( '.plch-rail__item' );
			if ( ! item ) { return; }
			if ( e.target.closest( '.plch-rail__add' ) ) { return; }
			e.preventDefault();
			var slug = item.getAttribute( 'data-slug' );
			if ( slug ) { loadPanel( slug ); }
		} );
	}

	reinitTableLinks( current );

} )();
