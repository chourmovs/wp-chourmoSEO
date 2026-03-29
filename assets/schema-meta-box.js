( function () {
	var sel = document.getElementById( 'brezngeo-schema-type' );

	function toggle() {
		var v = sel.value;
		document.querySelectorAll( '.brezngeo-schema-fields' ).forEach( function ( el ) {
			el.style.display = el.dataset.breType === v ? '' : 'none';
		} );
	}

	if ( sel ) {
		sel.addEventListener( 'change', toggle );
		toggle();
	}
} )();
