/* global brezngeoBulk */
jQuery( function ( $ ) {
    var running    = false;
    var stopFlag   = false;
    var processed  = 0;
    var total      = 0;
    var failedItems = [];

    if ( brezngeoBulk.isLocked ) {
        showLockWarning( brezngeoBulk.lockAge );
    }

    loadStats();

    function showLockWarning( age ) {
        var msg = brezngeoBulk.i18n.lockWarning + ( age ? ' (' + brezngeoBulk.i18n.since + ' ' + age + 's)' : '' ) + '.';
        $( '#brezngeo-lock-warning' ).text( msg ).show();
        $( '#brezngeo-bulk-start' ).prop( 'disabled', true );
    }

    function hideLockWarning() {
        $( '#brezngeo-lock-warning' ).hide();
        $( '#brezngeo-bulk-start' ).prop( 'disabled', false );
    }

    function loadStats() {
        $.post( brezngeoBulk.ajaxUrl, { action: 'brezngeo_bulk_stats', nonce: brezngeoBulk.nonce } )
            .done( function ( res ) {
                if ( ! res.success ) return;
                var html = '<strong>' + brezngeoBulk.i18n.postsWithoutMeta + '</strong><ul>';
                var t = 0;
                $.each( res.data, function ( pt, count ) {
                    html += '<li>' + $( '<span>' ).text( pt ).html() + ': <strong>' + parseInt( count, 10 ) + '</strong></li>';
                    t += parseInt( count, 10 );
                } );
                html += '</ul><strong>' + brezngeoBulk.i18n.total + ' ' + t + '</strong>';
                total = t;
                $( '#brezngeo-bulk-stats' ).html( html );
                updateCostEstimate();
            } );
    }

    $( '#brezngeo-bulk-limit, #brezngeo-bulk-model, #brezngeo-bulk-provider' ).on( 'change', updateCostEstimate );

    function updateCostEstimate() {
        var limit        = parseInt( $( '#brezngeo-bulk-limit' ).val(), 10 ) || 20;
        var inputTokens  = limit * 800;
        var outputTokens = limit * 50;
        var costHtml     = '~' + inputTokens + ' ' + brezngeoBulk.i18n.inputTokens + ' + ' + outputTokens + ' ' + brezngeoBulk.i18n.outputTokens;

        var costData = brezngeoBulk.costs || {};
        var provider = $( '#brezngeo-bulk-provider' ).val();
        var model    = $( '#brezngeo-bulk-model' ).val();

        if ( costData[ provider ] && costData[ provider ][ model ] ) {
            var c      = costData[ provider ][ model ];
            var inCost = ( inputTokens  / 1000000 ) * parseFloat( c.input  || 0 );
            var outCost= ( outputTokens / 1000000 ) * parseFloat( c.output || 0 );
            var total  = inCost + outCost;
            if ( total > 0 ) {
                costHtml += ' \u2248 $' + total.toFixed( 4 );
            }
        }
        $( '#brezngeo-cost-estimate' ).text( costHtml );
    }

    $( '#brezngeo-bulk-start' ).on( 'click', function () {
        if ( running ) return;
        $.post( brezngeoBulk.ajaxUrl, { action: 'brezngeo_bulk_status', nonce: brezngeoBulk.nonce } )
            .done( function ( res ) {
                if ( res.success && res.data.locked ) {
                    showLockWarning( res.data.lock_age );
                    return;
                }
                startRun();
            } );
    } );

    function startRun() {
        running     = true;
        stopFlag    = false;
        processed   = 0;
        failedItems = [];

        $( '#brezngeo-bulk-start' ).prop( 'disabled', true );
        $( '#brezngeo-bulk-stop' ).show();
        $( '#brezngeo-progress-wrap' ).show();
        $( '#brezngeo-bulk-log' ).show().html( '' );
        $( '#brezngeo-failed-summary' ).hide().html( '' );
        hideLockWarning();

        var limit    = parseInt( $( '#brezngeo-bulk-limit' ).val(), 10 ) || 20;
        var provider = $( '#brezngeo-bulk-provider' ).val();
        var model    = $( '#brezngeo-bulk-model' ).val();

        log( brezngeoBulk.i18n.logStart.replace( '{limit}', limit ).replace( '{provider}', provider ) );
        runBatch( 'post', limit, provider, model, true );
    }

    $( '#brezngeo-bulk-stop' ).on( 'click', function () {
        stopFlag = true;
        log( '\u26A0 ' + brezngeoBulk.i18n.stopRequested, 'warn' );
        releaseLock();
    } );

    function releaseLock() {
        $.post( brezngeoBulk.ajaxUrl, { action: 'brezngeo_bulk_release', nonce: brezngeoBulk.nonce } );
    }

    function runBatch( postType, remaining, provider, model, isFirst ) {
        if ( stopFlag || remaining <= 0 ) {
            finish();
            return;
        }

        var batchSize = Math.min( 20, remaining );
        var isLast    = ( remaining - batchSize ) <= 0;

        log( brezngeoBulk.i18n.logProcess.replace( '{count}', batchSize ).replace( '{remaining}', remaining ) );

        $.post( brezngeoBulk.ajaxUrl, {
            action:     'brezngeo_bulk_generate',
            nonce:      brezngeoBulk.nonce,
            post_type:  postType,
            batch_size: batchSize,
            provider:   provider,
            model:      model,
            is_first:   isFirst ? 1 : 0,
            is_last:    isLast  ? 1 : 0,
        } ).done( function ( res ) {
            if ( ! res.success ) {
                if ( res.data && res.data.locked ) {
                    showLockWarning( res.data.lock_age );
                    finish();
                    return;
                }
                log( '\u2717 Fehler: ' + $( '<span>' ).text( ( res.data && res.data.message ) || brezngeoBulk.i18n.unknownError ).html(), 'error' );
                finish();
                return;
            }

            $.each( res.data.results, function ( i, item ) {
                if ( item.success ) {
                    var note = item.attempts > 1 ? ' (' + brezngeoBulk.i18n.attempt + ' ' + item.attempts + ')' : '';
                    log(
                        '\u2713 [' + item.id + '] ' +
                        $( '<span>' ).text( item.title ).html() + note +
                        '<br><small style="color:#9cdcfe;">' +
                        $( '<span>' ).text( item.description ).html() +
                        '</small>'
                    );
                } else {
                    failedItems.push( item );
                    log(
                        '\u2717 [' + item.id + '] ' +
                        $( '<span>' ).text( item.title ).html() +
                        ' \u2014 ' + $( '<span>' ).text( item.error ).html(),
                        'error'
                    );
                }
                processed++;
            } );

            updateProgress( processed, total );

            var newRemaining = remaining - batchSize;
            if ( res.data.remaining > 0 && ! stopFlag && newRemaining > 0 ) {
                setTimeout( function () {
                    runBatch( postType, newRemaining, provider, model, false );
                }, brezngeoBulk.rateDelay );
            } else {
                if ( isLast || res.data.remaining === 0 ) releaseLock();
                finish();
            }
        } ).fail( function () {
            log( '\u2717 ' + brezngeoBulk.i18n.networkError, 'error' );
            releaseLock();
            finish();
        } );
    }

    function updateProgress( done, t ) {
        var pct = t > 0 ? Math.round( ( done / t ) * 100 ) : 100;
        $( '#brezngeo-progress-bar' ).css( 'width', pct + '%' );
        $( '#brezngeo-progress-text' ).text( done + ' / ' + t + ' ' + brezngeoBulk.i18n.processed );
    }

    /**
     * Append a line to the log console.
     * @param {string} msg  Pre-escaped HTML string. User data MUST be escaped via
     *                      $('<span>').text(val).html() before passing here.
     * @param {string} type 'error' | 'warn' | undefined
     */
    function log( msg, type ) {
        var color = type === 'error' ? '#f48771' : type === 'warn' ? '#dcdcaa' : '#9cdcfe';
        $( '#brezngeo-bulk-log' ).append(
            '<div style="color:' + color + ';margin-bottom:4px;">' + msg + '</div>'
        );
        var el = document.getElementById( 'brezngeo-bulk-log' );
        el.scrollTop = el.scrollHeight;
    }

    function finish() {
        running = false;
        $( '#brezngeo-bulk-start' ).prop( 'disabled', false );
        $( '#brezngeo-bulk-stop' ).hide();
        log( brezngeoBulk.i18n.done );

        if ( failedItems.length > 0 ) {
            var html = '<strong>\u26A0 ' + failedItems.length + ' ' + brezngeoBulk.i18n.postsFailed + '</strong><ul>';
            $.each( failedItems, function ( i, item ) {
                html += '<li>[' + item.id + '] ' +
                    $( '<span>' ).text( item.title ).html() +
                    ': <em>' + $( '<span>' ).text( item.error ).html() + '</em></li>';
            } );
            html += '</ul>';
            $( '#brezngeo-failed-summary' ).html( html ).show();
        }
        loadStats();
    }
} );
