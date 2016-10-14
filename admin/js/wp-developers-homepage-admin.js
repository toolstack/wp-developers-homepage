(function( $ ) {
	'use strict';

	// Load main content via Ajax on inital page load.
	$( document ).ready( function() {
		wdh_load_ajax_content();
	});

	// Trigger actions after Ajax load.
	$( document ).on( 'wdhRefreshAfter', function() {

		// Open correct tab.
		if ( location.href.indexOf( 'orderby' ) >= 0 ) {
			$( '.button[data-wdh-tab-target="info"]' ).addClass( 'red') ;
			wdh_toggle_tabs( $( '.button[data-wdh-tab-target="info"]' ) );
		}

		// Hook up responsive row expand/collapse functionality.
		$( '.wdh-sub-tab-info tbody' ).on( 'click', '.toggle-row', function() {
			$( this ).closest( 'tr' ).toggleClass( 'is-expanded' );
		});

	});

	// Hook up refresh button functionality.
	$( '#wp-developers-homepage-settings' ).on( 'click', '.wdh-button-refresh', function( e ) {
		e.preventDefault();

		var button = $( this );
		wdh_load_ajax_content( true, button );

	});


	$( '#wp-developers-homepage-settings' ).on( 'click', '.wdh-sub-tab-nav .button', function( e ) {

		e.preventDefault();

		var button = $( this );

		wdh_toggle_tabs( button );

	});

	// Fix broken table-sorting links after Ajax.
	$( document ).on( 'wdhRefreshAfter', function() {

		$( '.wdh-sub-tab a[href*="admin-ajax.php"]' ).each( function() {
			var $link,
				linkIdentifier = 'admin-ajax.php',
				href,
				hrefParams,
				newHref,

			$link = $( this );
			href = $link.attr( 'href' );
			hrefParams = href.substr( href.indexOf( linkIdentifier ) + linkIdentifier.length + 1 );
			newHref = location.href;
			newHref += ( newHref.indexOf( '?' ) >= 0 ? '&' : '?' ) + hrefParams;

			// Set new href.
			$link.attr( 'href', newHref );

		});

	});

	/**
	 * Load content into main container via Ajax.
	 *
	 * @since 1.0.0
	 *
	 * @param {bool} forceRefresh Whether or not to force a cache-busting fetch.
	 * @param {JQuery} button Button used to call refresh, if used.
	 */
	var wdh_load_ajax_content = function( forceRefresh, button ) {

		var $ajaxContainer,
			objectType,
			ticketType,
			data;

		// Set up all variables and objects.
		$ajaxContainer = $( '.wdh-ajax-container' );
		objectType = $ajaxContainer.attr( 'data-wdh-object-type' );
		ticketType = $ajaxContainer.attr( 'data-wdh-ticket-type' );

		// Set up button stuff.
		if ( button ) {
			var buttonOrigText,
				refreshingTexts,
				buttonRefreshingText,
				spinner,
				buttons,

			buttons = $( '.wdh-button-refresh' );
			buttonOrigText = button.val();
			refreshingTexts = wdhSettings.fetch_messages;
			buttonRefreshingText = buttons.attr( 'data-wdh-refreshing-text' );
			spinner = buttons.next( '.spinner' ).toggleClass( 'is-active');

			// Get refresh message.
			buttonRefreshingText = refreshingTexts[ Math.floor( Math.random() * refreshingTexts.length ) ];

			buttons.val( buttonRefreshingText ).prop( 'disabled', true );
		}

		data = {
			'action':         'refresh_wdh',
			'object_type':    objectType,
			'ticket_type':    ticketType,
			'force_refresh':  forceRefresh,
			'current_url':    location.href,
		};

		// Trigger event before refresh.
		$( document ).trigger( 'wdhRefreshBefore' );

		// Run Ajax request.
		jQuery.post( ajaxurl, data, function( response ) {
			var lastSortList, lastTicketsOrder, lastStatsOrder;
			
			lastSortList = $( '#wdh_tickets_table' );
			if( lastSortList.length > 0 ) {
				lastTicketsOrder = lastSortList[0].config.sortList;
			} else {
				lastTicketsOrder = [[4,1]];
			}
			
			lastSortList = $( '.wdh-stats-table' );
			if( lastSortList.length > 0 ) {
				lastStatsOrder = lastSortList[0].config.sortList;
			} else {
				lastStatsOrder = [[0,0]];
			}

			$ajaxContainer.fadeTo( 'slow', 1 ).html( response );

			if ( button ) {
				buttons.val( buttonOrigText ).prop( 'disabled', false );
				spinner.toggleClass( 'is-active');
			}

			$( '#wdh_tickets_table' ).tablesorter({ 
		        sortList: lastTicketsOrder 
			    }); 

			$( '.wdh-stats-table' ).tablesorter({ 
		        sortList: lastStatsOrder 
			    }); 

			// Trigger event after refresh.
			$( document ).trigger( 'wdhRefreshAfter' );

		});

	}

	var wdh_toggle_tabs = function( button ) {

		var targetTabClass,
			$tabsContainer;

		// Don't do anything if this is already the active button.
		if ( ! button.hasClass( 'button-primary' ) ) {

			// Toggle "active" status.
			button.addClass( 'button-primary' ).siblings( '.button' ).removeClass( 'button-primary' );

			// Toggle visibility of tabs.
			targetTabClass = button.attr( 'data-wdh-tab-target' );
			$tabsContainer = $( '.wdh-sub-tab-container' );

			$tabsContainer.find( '.wdh-sub-tab' ).removeClass( 'active' );
			$tabsContainer.find( '.wdh-sub-tab-' + targetTabClass ).addClass( 'active' );

		}

		// Remove focus/outline from button.
		button.blur();

	}

})( jQuery );
