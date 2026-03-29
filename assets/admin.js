/* global brezngeoAdmin, brezngeoL10n */
jQuery( function ( $ ) {
    function updateProviderRows() {
        var active = $( '#brezngeo-provider' ).val();
        $( '.brezngeo-provider-row' ).removeClass( 'active' );
        $( '.brezngeo-provider-row[data-provider="' + active + '"]' ).addClass( 'active' );
    }
    updateProviderRows();
    $( '#brezngeo-provider' ).on( 'change', updateProviderRows );

    $( document ).on( 'click', '.brezngeo-test-btn', function () {
        var btn        = $( this );
        var providerId = btn.data( 'provider' );
        var resultEl   = $( '#test-result-' + providerId );

        resultEl.removeClass( 'success error' ).text( brezngeoAdmin.testing );
        btn.prop( 'disabled', true );

        $.post( brezngeoAdmin.ajaxUrl, {
            action:   'brezngeo_test_connection',
            nonce:    brezngeoAdmin.nonce,
            provider: providerId,
        } ).done( function ( res ) {
            if ( res.success ) {
                resultEl.addClass( 'success' ).text( '\u2713 ' + res.data );
            } else {
                resultEl.addClass( 'error' ).text( '\u2717 ' + res.data );
            }
        } ).fail( function () {
            resultEl.addClass( 'error' ).text( '\u2717 ' + brezngeoAdmin.networkError );
        } ).always( function () {
            btn.prop( 'disabled', false );
        } );
    } );

    $( '#brezngeo-reset-prompt' ).on( 'click', function () {
        if ( ! confirm( brezngeoAdmin.resetConfirm ) ) return;
        $.post( brezngeoAdmin.ajaxUrl, {
            action: 'brezngeo_get_default_prompt',
            nonce:  brezngeoAdmin.nonce,
        } ).done( function ( res ) {
            if ( res.success ) {
                $( 'textarea[name*="prompt"]' ).val( res.data );
            }
        } );
    } );

    $( '#brezngeo-dismiss-welcome' ).on( 'click', function () {
        $( '#brezngeo-welcome-notice' ).slideUp( 200 );
        $.post( brezngeoAdmin.ajaxUrl, {
            action: 'brezngeo_dismiss_welcome',
            nonce:  brezngeoAdmin.nonce,
        } );
    } );

    function updateAiFields() {
        if ( $( '#brezngeo-ai-enabled' ).is( ':checked' ) ) {
            $( '#brezngeo-ai-fields' ).show();
        } else {
            $( '#brezngeo-ai-fields' ).hide();
        }
    }
    if ( $( '#brezngeo-ai-enabled' ).length ) {
        updateAiFields();
        $( '#brezngeo-ai-enabled' ).on( 'change', updateAiFields );
    }

    // llms.txt cache clear button
    $( '#brezngeo-llms-clear-cache' ).on( 'click', function () {
        $.post( brezngeoAdmin.ajaxUrl, {
            action: 'brezngeo_llms_clear_cache',
            nonce:  brezngeoAdmin.nonce,
        } ).done( function ( res ) {
            $( '#brezngeo-cache-result' ).text( res.success ? brezngeoAdmin.cacheCleared : brezngeoAdmin.error );
            setTimeout( function () { $( '#brezngeo-cache-result' ).text( '' ); }, 3000 );
        } );
    } );

    // Link analysis dashboard widget
    if ( typeof brezngeoL10n !== 'undefined' && $( '#brezngeo-link-analysis-content' ).length ) {
        $.post( brezngeoAdmin.ajaxUrl, {
            action: 'brezngeo_link_analysis',
            nonce:  brezngeoAdmin.nonce,
        } ).done( function ( res ) {
            if ( ! res.success ) {
                $( '#brezngeo-link-analysis-content' ).text( brezngeoL10n.analysisError );
                return;
            }
            var d = res.data, h = '';
            h += '<p><strong>' + brezngeoL10n.noLinksHeading + ' (' + d.no_internal_links.length + ')</strong></p>';
            if ( d.no_internal_links.length ) {
                h += '<ul style="margin:0 0 10px 20px;">';
                $.each( d.no_internal_links.slice( 0, 10 ), function ( i, p ) {
                    h += '<li>' + $( '<span>' ).text( p.title ).html() + '</li>';
                } );
                if ( d.no_internal_links.length > 10 ) h += '<li>\u2026</li>';
                h += '</ul>';
            } else {
                h += '<p>' + brezngeoL10n.allLinked + '</p>';
            }
            h += '<p><strong>' + brezngeoL10n.manyExternalPre + d.threshold + ')</strong></p>';
            if ( d.too_many_external.length ) {
                h += '<ul style="margin:0 0 10px 20px;">';
                $.each( d.too_many_external.slice( 0, 5 ), function ( i, p ) {
                    h += '<li>' + $( '<span>' ).text( p.title ).html() + ' (' + p.count + ')</li>';
                } );
                h += '</ul>';
            } else {
                h += '<p>' + brezngeoL10n.noExternalIssues + '</p>';
            }
            h += '<p><strong>' + brezngeoL10n.pillarHeading + '</strong></p>';
            if ( d.pillar_pages.length ) {
                h += '<ul style="margin:0 0 10px 20px;">';
                $.each( d.pillar_pages, function ( i, p ) {
                    h += '<li><a href="' + $( '<span>' ).text( p.url ).html() + '" target="_blank">' + $( '<span>' ).text( p.url ).html() + '</a> (' + p.count + 'x)</li>';
                } );
                h += '</ul>';
            } else {
                h += '<p>' + brezngeoL10n.noData + '</p>';
            }
            $( '#brezngeo-link-analysis-content' ).html( h );
        } ).fail( function () {
            $( '#brezngeo-link-analysis-content' ).text( brezngeoL10n.connectionError );
        } );
    }
} );
