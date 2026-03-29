/* global jQuery, ajaxurl */
jQuery( function ( $ ) {
	var $box = $( '#brezngeo-geo-box' );
	if ( ! $box.length ) return;

	var postId    = $box.data( 'post-id' );
	var nonce     = $box.data( 'nonce' );
	var $generate = $( '#brezngeo-geo-generate' );
	var $clear    = $( '#brezngeo-geo-clear' );
	var $status   = $( '#brezngeo-geo-status' );
	var $summary  = $( '#brezngeo-geo-summary' );
	var $bullets  = $( '#brezngeo-geo-bullets' );
	var $faq      = $( '#brezngeo-geo-faq' );
	var $lock     = $( '#brezngeo-geo-lock' );

	function setStatus( msg, isError ) {
		$status.text( msg ).css( 'color', isError ? '#dc3232' : '#46b450' );
		if ( msg ) {
			setTimeout( function () { $status.text( '' ); }, 4000 );
		}
	}

	function populateFields( data ) {
		$summary.val( data.summary || '' );
		$bullets.val( ( data.bullets || [] ).join( '\n' ) );
		var faqLines = ( data.faq || [] ).map( function ( item ) {
			return item.q + ' | ' + item.a;
		} );
		$faq.val( faqLines.join( '\n' ) );
		// AI-generated content resets the lock
		$lock.prop( 'checked', false );
	}

	// Track manual edits → auto-set lock to protect from overwrite
	$summary.add( $bullets ).add( $faq ).on( 'input', function () {
		$lock.prop( 'checked', true );
	} );

	if ( $generate.length ) {
		$generate.on( 'click', function () {
			$generate.prop( 'disabled', true ).text( '…' );
			setStatus( '' );
			$.post( ajaxurl, {
				action:  'brezngeo_geo_generate',
				nonce:   nonce,
				post_id: postId,
			} ).done( function ( res ) {
				if ( res.success ) {
					populateFields( res.data );
					setStatus( 'Generated ✓', false );
					$generate.text( 'Regenerate' );
				} else {
					setStatus( res.data || 'Error', true );
				}
			} ).fail( function () {
				setStatus( 'Connection error', true );
			} ).always( function () {
				$generate.prop( 'disabled', false );
			} );
		} );
	}

	if ( $clear.length ) {
		$clear.on( 'click', function () {
			if ( ! window.confirm( 'Really clear GEO fields?' ) ) return;
			$clear.prop( 'disabled', true );
			$.post( ajaxurl, {
				action:  'brezngeo_geo_clear',
				nonce:   nonce,
				post_id: postId,
			} ).done( function ( res ) {
				if ( res.success ) {
					$summary.val( '' );
					$bullets.val( '' );
					$faq.val( '' );
					$lock.prop( 'checked', false );
					setStatus( 'Cleared', false );
				}
			} ).always( function () {
				$clear.prop( 'disabled', false );
			} );
		} );
	}
} );
