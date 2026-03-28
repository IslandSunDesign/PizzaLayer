( function () {
	'use strict';

	/* Wait for DOM — script is in footer but be defensive */
	function init() {

		/* ── Elements ─────────────────────────────────────────────── */
		var frame      = document.getElementById( 'ptc-preview-frame' );
		var loading    = document.getElementById( 'ptc-iframe-loading' );
		var label      = document.getElementById( 'ptc-preview-label' );
		var reloadBtn  = document.getElementById( 'ptc-preview-reload' );
		var modal      = document.getElementById( 'ptc-modal' );
		var modalName  = document.getElementById( 'ptc-modal-name' );
		var modalSlug  = document.getElementById( 'ptc-modal-slug' );
		var cancelBtn  = document.getElementById( 'ptc-modal-cancel' );
		var overlay    = document.getElementById( 'ptc-modal-overlay' );
		var editUrlBtn = document.getElementById( 'ptc-edit-preview-url' );
		var urlBar     = document.getElementById( 'ptc-preview-url-bar' );
		var cancelUrl  = document.getElementById( 'ptc-cancel-preview-url' );
		var items      = document.querySelectorAll( '.ptc-item' );

		/* If the split-pane layout isn't present, nothing to do */
		if ( ! frame ) { return; }

		/* ── Preview URL editor toggle ───────────────────────────── */
		if ( editUrlBtn && urlBar ) {
			editUrlBtn.addEventListener( 'click', function () {
				urlBar.style.display = urlBar.style.display === 'none' ? '' : 'none';
			} );
		}
		if ( cancelUrl && urlBar ) {
			cancelUrl.addEventListener( 'click', function () {
				urlBar.style.display = 'none';
			} );
		}

		/* ── Loading overlay helpers ─────────────────────────────── */
		var loadTimer = null;

		function showLoading() {
			if ( loading ) {
				loading.style.display   = 'flex';
				loading.style.opacity   = '1';
				loading.style.pointerEvents = 'auto';
			}
			frame.style.opacity = '0.25';
		}

		function hideLoading() {
			if ( loading ) {
				loading.style.opacity      = '0';
				loading.style.pointerEvents = 'none';
				/* Delay hiding so fade-out completes before display:none */
				setTimeout( function () {
					if ( loading.style.opacity === '0' ) {
						loading.style.display = 'none';
					}
				}, 250 );
			}
			frame.style.opacity = '1';
		}

		/* Hide spinner once iframe fires load */
		frame.addEventListener( 'load', function () {
			clearTimeout( loadTimer );
			setTimeout( hideLoading, 100 );
		} );
		frame.addEventListener( 'error', function () {
			clearTimeout( loadTimer );
			hideLoading();
		} );

		/* ── Load a URL into the preview iframe ──────────────────── */
		function loadPreview( url, templateName ) {
			showLoading();
			/* Safety timeout: if iframe never fires load, clear spinner anyway */
			clearTimeout( loadTimer );
			loadTimer = setTimeout( hideLoading, 10000 );

			/* Only update src if the URL actually changed */
			if ( frame.src !== url ) {
				frame.src = url;
			}

			if ( label ) {
				label.textContent = ( templateName || 'Template' ) + ' — Live Preview';
			}
		}

		/* ── Debounce hover ──────────────────────────────────────── */
		var hoverTimer        = null;
		var currentPreviewing = null;

		function schedulePreview( url, name ) {
			clearTimeout( hoverTimer );
			hoverTimer = setTimeout( function () {
				loadPreview( url, name );
			}, 200 );
		}

		/* ── Wire up template item rows ──────────────────────────── */
		items.forEach( function ( item ) {
			var previewUrl = item.getAttribute( 'data-preview-url' );
			var name       = item.getAttribute( 'data-name' );
			var slug       = item.getAttribute( 'data-slug' );

			if ( ! previewUrl ) { return; }

			/* Hover → debounced preview */
			item.addEventListener( 'mouseenter', function () {
				if ( slug === currentPreviewing ) { return; }
				currentPreviewing = slug;

				document.querySelectorAll( '.ptc-item' ).forEach( function ( el ) {
					el.classList.remove( 'ptc-item--previewing' );
				} );
				item.classList.add( 'ptc-item--previewing' );

				schedulePreview( previewUrl, name );
			} );

			/* Click anywhere on the row (except the Activate button) */
			item.addEventListener( 'click', function ( e ) {
				if ( e.target.closest( '.ptc-activate-btn' ) ) { return; }
				clearTimeout( hoverTimer );
				currentPreviewing = slug;
				loadPreview( previewUrl, name );
			} );
		} );

		/* Mouse leaves the list → cancel pending debounced load */
		var list = document.getElementById( 'ptc-list' );
		if ( list ) {
			list.addEventListener( 'mouseleave', function () {
				clearTimeout( hoverTimer );
				currentPreviewing = null;
			} );
		}

		/* ── Reload button ───────────────────────────────────────── */
		if ( reloadBtn ) {
			reloadBtn.addEventListener( 'click', function () {
				var src = frame.getAttribute( 'src' ) || '';
				if ( ! src ) { return; }
				showLoading();
				clearTimeout( loadTimer );
				loadTimer = setTimeout( hideLoading, 10000 );
				frame.src = '';
				setTimeout( function () { frame.src = src; }, 50 );
			} );
		}

		/* ── Activate modal ──────────────────────────────────────── */
		function openModal( name, slug ) {
			if ( modalName ) { modalName.textContent = name; }
			if ( modalSlug ) { modalSlug.value       = slug; }
			if ( modal )     { modal.style.display   = ''; }
			document.body.style.overflow = 'hidden';
		}

		function closeModal() {
			if ( modal ) { modal.style.display = 'none'; }
			document.body.style.overflow = '';
		}

		document.querySelectorAll( '.ptc-activate-btn' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function ( e ) {
				e.stopPropagation();
				openModal( btn.getAttribute( 'data-name' ), btn.getAttribute( 'data-slug' ) );
			} );
		} );

		if ( cancelBtn ) { cancelBtn.addEventListener( 'click', closeModal ); }
		if ( overlay )   { overlay.addEventListener(   'click', closeModal ); }

		document.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Escape' ) { closeModal(); }
		} );

		/* ── Trigger initial load of active template ─────────────── */
		/* The iframe already has src set in PHP, but call showLoading
		   so the spinner appears until it fires the load event */
		if ( frame.src && frame.src !== 'about:blank' && frame.src !== window.location.href ) {
			showLoading();
			clearTimeout( loadTimer );
			loadTimer = setTimeout( hideLoading, 10000 );
		}
	}

	/* Run after DOM is ready */
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}

} )();
