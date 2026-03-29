/* global jQuery, ajaxurl */
jQuery( function ( $ ) {
    var $textarea = $( '#brezngeo-meta-description' );
    var $count    = $( '#brezngeo-meta-count' );
    var $btn      = $( '#brezngeo-regen-meta' );

    if ( ! $textarea.length ) return;

    $textarea.on( 'input', function () {
        $count.text( $( this ).val().length + ' / 160' );
    } );

    if ( ! $btn.length ) return;

    $btn.on( 'click', function () {
        $btn.prop( 'disabled', true ).text( '…' );
        $.post( ajaxurl, {
            action:  'brezngeo_regen_meta',
            nonce:   $btn.data( 'nonce' ),
            post_id: $btn.data( 'post-id' ),
        } ).done( function ( res ) {
            if ( res.success ) {
                $textarea.val( res.data.description );
                $count.text( res.data.description.length + ' / 160' );
            } else {
                alert( 'Fehler: ' + ( res.data || 'Unbekannt' ) );
            }
        } ).always( function () {
            $btn.prop( 'disabled', false ).text( 'Mit KI neu generieren' );
        } );
    } );
} );
