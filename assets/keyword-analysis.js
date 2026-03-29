/* global jQuery, wp, brezngeoKeyword, tinyMCE, ajaxurl */
( function ( $ ) {
	'use strict';

	if ( typeof brezngeoKeyword === 'undefined' ) { return; }

	var cfg       = brezngeoKeyword;
	var i18n      = cfg.i18n;
	var isRunning = false;
	var debounceTimer = null;

	var $box       = $( '#brezngeo-keyword-box' );
	var $main      = $( '#brezngeo-keyword-main' );
	var $secList   = $( '#brezngeo-keyword-secondary-list' );
	var $addSec    = $( '#brezngeo-keyword-add-secondary' );
	var $analyze   = $( '#brezngeo-keyword-analyze' );
	var $status    = $( '#brezngeo-keyword-status' );
	var $results   = $( '#brezngeo-keyword-results' );
	var $aiActions = $( '#brezngeo-keyword-ai-actions' );
	var $aiResults = $( '#brezngeo-keyword-ai-results' );

	if ( ! $box.length ) { return; }

	/* ── Helpers ─────────────────────────────────────────── */

	function getContent() {
		if ( window.wp && wp.data && wp.data.select( 'core/editor' ) ) {
			try {
				return wp.data.select( 'core/editor' ).getEditedPostContent();
			} catch ( e ) { /* fall through */ }
		}
		if ( typeof tinyMCE !== 'undefined' && tinyMCE.activeEditor && ! tinyMCE.activeEditor.isHidden() ) {
			return tinyMCE.activeEditor.getContent();
		}
		return $( '#content' ).val() || '';
	}

	function setStatus( msg, isError ) {
		$status.text( msg ).css( 'color', isError ? '#dc3232' : '#46b450' );
		if ( msg ) {
			setTimeout( function () { $status.text( '' ); }, 5000 );
		}
	}

	function getSecondaryKeywords() {
		var keywords = [];
		$secList.find( 'input[name="brezngeo_keyword_secondary[]"]' ).each( function () {
			var val = $.trim( $( this ).val() );
			if ( val ) { keywords.push( val ); }
		} );
		return keywords;
	}

	/* ── Secondary keyword repeater ──────────────────────── */

	$addSec.on( 'click', function () {
		var $row = $( '<div class="brezngeo-keyword-secondary-row" style="display:flex;gap:6px;margin-bottom:4px;">' +
			'<input type="text" name="brezngeo_keyword_secondary[]" style="flex:1;box-sizing:border-box;">' +
			'<button type="button" class="button brezngeo-keyword-remove-secondary">&times;</button>' +
			'</div>' );
		$secList.append( $row );
		$row.find( 'input' ).focus();
	} );

	$secList.on( 'click', '.brezngeo-keyword-remove-secondary', function () {
		$( this ).closest( '.brezngeo-keyword-secondary-row' ).remove();
	} );

	/* ── Status icons ────────────────────────────────────── */

	function statusIcon( status ) {
		switch ( status ) {
			case 'pass': return '<span style="color:#46b450;">&#x2705;</span>';
			case 'warn': return '<span style="color:#ffb900;">&#x26A0;&#xFE0F;</span>';
			case 'fail': return '<span style="color:#dc3232;">&#x274C;</span>';
			default:     return '';
		}
	}

	/* ── Render results ──────────────────────────────────── */

	function renderResults( data ) {
		var html = '';

		// Main keyword results.
		if ( data.main && data.main.checks ) {
			html += '<h4 style="margin:0 0 8px;">' + escHtml( data.main.keyword ) + '</h4>';
			html += '<div style="margin-bottom:16px;">';
			$.each( data.main.checks, function ( i, check ) {
				html += '<div style="padding:3px 0;">' +
					statusIcon( check.status ) + ' ' +
					'<strong>' + escHtml( check.label ) + '</strong> &mdash; ' +
					escHtml( check.message ) +
					'</div>';
			} );
			html += '</div>';
		}

		// Secondary keyword results.
		if ( data.secondary ) {
			$.each( data.secondary, function ( kw, checks ) {
				html += '<h4 style="margin:12px 0 6px;font-size:13px;color:#555;">' + escHtml( kw ) + '</h4>';
				$.each( checks, function ( i, check ) {
					html += '<div style="padding:2px 0;font-size:13px;">' +
						statusIcon( check.status ) + ' ' +
						'<strong>' + escHtml( check.label ) + '</strong> &mdash; ' +
						escHtml( check.message ) +
						'</div>';
				} );
			} );
		}

		$results.html( html );

		// Show AI action buttons after results are displayed.
		if ( $aiActions.length ) {
			$aiActions.show();
		}
	}

	function escHtml( str ) {
		var div = document.createElement( 'div' );
		div.appendChild( document.createTextNode( str || '' ) );
		return div.innerHTML;
	}

	/* ── Core: run analysis ──────────────────────────────── */

	function runAnalysis() {
		var keyword = $.trim( $main.val() );
		if ( ! keyword ) {
			setStatus( i18n.noKeyword, true );
			return;
		}
		if ( isRunning ) { return; }

		var content = getContent();
		isRunning = true;
		$analyze.prop( 'disabled', true );
		setStatus( i18n.analyzing, false );

		$.post( cfg.ajaxUrl, {
			action:             'brezngeo_keyword_analyze',
			nonce:              cfg.nonce,
			post_id:            cfg.postId,
			post_content:       content,
			main_keyword:       keyword,
			secondary_keywords: JSON.stringify( getSecondaryKeywords() ),
		} )
		.done( function ( res ) {
			if ( res && res.success ) {
				renderResults( res.data );
				setStatus( '', false );
			} else {
				setStatus( res.data || i18n.error, true );
			}
		} )
		.fail( function () {
			setStatus( i18n.error, true );
		} )
		.always( function () {
			isRunning = false;
			$analyze.prop( 'disabled', false );
		} );
	}

	/* ── Triggers ────────────────────────────────────────── */

	// Manual mode: button click.
	$analyze.on( 'click', runAnalysis );

	// Live mode: debounced on editor changes.
	if ( cfg.updateMode === 'live' ) {
		function debouncedAnalysis() {
			clearTimeout( debounceTimer );
			debounceTimer = setTimeout( function () {
				if ( $.trim( $main.val() ) ) {
					runAnalysis();
				}
			}, cfg.debounceMs );
		}

		// Gutenberg.
		if ( window.wp && wp.data && wp.data.subscribe ) {
			var lastContent = '';
			wp.data.subscribe( function () {
				var content = getContent();
				if ( content !== lastContent ) {
					lastContent = content;
					debouncedAnalysis();
				}
			} );
		}

		// Classic editor.
		if ( typeof tinyMCE !== 'undefined' ) {
			$( document ).on( 'tinymce-editor-init', function ( evt, editor ) {
				editor.on( 'input', debouncedAnalysis );
			} );
		}
		$( '#content' ).on( 'input', debouncedAnalysis );
		$main.on( 'input', debouncedAnalysis );
	}

	// On-save mode.
	if ( cfg.updateMode === 'save' ) {
		if ( window.wp && wp.data && wp.data.subscribe ) {
			var wasSaving = false;
			wp.data.subscribe( function () {
				var isSaving = wp.data.select( 'core/editor' ).isSavingPost();
				if ( wasSaving && ! isSaving ) {
					runAnalysis();
				}
				wasSaving = isSaving;
			} );
		}
		$( '#publish, #save-post' ).on( 'click', function () {
			setTimeout( runAnalysis, 500 );
		} );
	}

	/* ── AI: Suggest keywords ────────────────────────────── */

	$( '#brezngeo-keyword-ai-suggest' ).on( 'click', function () {
		var $btn    = $( this );
		var content = getContent();
		$btn.prop( 'disabled', true );
		setStatus( i18n.suggesting, false );

		$.post( cfg.ajaxUrl, {
			action:       'brezngeo_keyword_ai_suggest',
			nonce:        cfg.nonce,
			post_id:      cfg.postId,
			post_content: content,
		} )
		.done( function ( res ) {
			if ( res && res.success && res.data ) {
				if ( res.data.main ) {
					$main.val( res.data.main );
				}
				if ( res.data.secondary && res.data.secondary.length ) {
					$secList.empty();
					$.each( res.data.secondary, function ( i, kw ) {
						var $row = $( '<div class="brezngeo-keyword-secondary-row" style="display:flex;gap:6px;margin-bottom:4px;">' +
							'<input type="text" name="brezngeo_keyword_secondary[]" value="' + escHtml( kw ) + '" style="flex:1;box-sizing:border-box;">' +
							'<button type="button" class="button brezngeo-keyword-remove-secondary">&times;</button>' +
							'</div>' );
						$secList.append( $row );
					} );
				}
				setStatus( '', false );
			} else {
				setStatus( res.data || i18n.error, true );
			}
		} )
		.fail( function () { setStatus( i18n.error, true ); } )
		.always( function () { $btn.prop( 'disabled', false ); } );
	} );

	/* ── AI: Optimization Tips ───────────────────────────── */

	$( '#brezngeo-keyword-ai-optimize' ).on( 'click', function () {
		var $btn    = $( this );
		var content = getContent();
		var keyword = $.trim( $main.val() );
		if ( ! keyword ) { setStatus( i18n.noKeyword, true ); return; }

		$btn.prop( 'disabled', true );
		setStatus( i18n.optimizing, false );

		$.post( cfg.ajaxUrl, {
			action:       'brezngeo_keyword_ai_optimize',
			nonce:        cfg.nonce,
			post_id:      cfg.postId,
			post_content: content,
			main_keyword: keyword,
		} )
		.done( function ( res ) {
			if ( res && res.success && res.data ) {
				var html = '<h4>' + escHtml( 'Optimization Tips' ) + '</h4><ul style="margin:6px 0 0 16px;">';
				$.each( res.data, function ( i, tip ) {
					html += '<li style="margin-bottom:4px;">' + escHtml( tip ) + '</li>';
				} );
				html += '</ul>';
				$aiResults.html( html );
				setStatus( '', false );
			} else {
				setStatus( res.data || i18n.error, true );
			}
		} )
		.fail( function () { setStatus( i18n.error, true ); } )
		.always( function () { $btn.prop( 'disabled', false ); } );
	} );

	/* ── AI: Semantic Analysis ───────────────────────────── */

	$( '#brezngeo-keyword-ai-semantic' ).on( 'click', function () {
		var $btn    = $( this );
		var content = getContent();
		var keyword = $.trim( $main.val() );
		if ( ! keyword ) { setStatus( i18n.noKeyword, true ); return; }

		$btn.prop( 'disabled', true );
		setStatus( i18n.semantic, false );

		$.post( cfg.ajaxUrl, {
			action:       'brezngeo_keyword_ai_semantic',
			nonce:        cfg.nonce,
			post_id:      cfg.postId,
			post_content: content,
			main_keyword: keyword,
		} )
		.done( function ( res ) {
			if ( res && res.success && res.data ) {
				var html = '<h4>' + escHtml( 'Semantic Analysis' ) + '</h4>';
				html += '<div style="white-space:pre-wrap;font-size:13px;line-height:1.5;">' + escHtml( res.data ) + '</div>';
				$aiResults.html( html );
				setStatus( '', false );
			} else {
				setStatus( res.data || i18n.error, true );
			}
		} )
		.fail( function () { setStatus( i18n.error, true ); } )
		.always( function () { $btn.prop( 'disabled', false ); } );
	} );

} )( jQuery );
