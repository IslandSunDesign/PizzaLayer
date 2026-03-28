( function () {
	'use strict';

	var modal     = document.getElementById( 'ptc-modal' );
	var modalName = document.getElementById( 'ptc-modal-name' );
	var modalSlug = document.getElementById( 'ptc-modal-slug' );
	var cancelBtn = document.getElementById( 'ptc-modal-cancel' );
	var overlay   = document.getElementById( 'ptc-modal-overlay' );

	if ( ! modal ) { return; }

	document.querySelectorAll( '.ptc-activate-btn' ).forEach( function ( btn ) {
		btn.addEventListener( 'click', function () {
			if ( modalName ) { modalName.textContent = btn.dataset.name; }
			if ( modalSlug ) { modalSlug.value       = btn.dataset.slug; }
			modal.style.display          = '';
			document.body.style.overflow = 'hidden';
		} );
	} );

	function closeModal() {
		modal.style.display          = 'none';
		document.body.style.overflow = '';
	}

	if ( cancelBtn ) { cancelBtn.addEventListener( 'click', closeModal ); }
	if ( overlay )   { overlay.addEventListener( 'click', closeModal ); }
	document.addEventListener( 'keydown', function ( e ) {
		if ( e.key === 'Escape' ) { closeModal(); }
	} );

} )();
