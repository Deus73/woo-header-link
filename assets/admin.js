/* global jQuery, wp, WHL_ADMIN */
( function ( $ ) {
	'use strict';

	$( function () {
		var frame;

		$( '#whl-select-image' ).on( 'click', function ( e ) {
			e.preventDefault();

			if ( frame ) {
				frame.open();
				return;
			}

			frame = wp.media( {
				title: WHL_ADMIN.title,
				button: { text: WHL_ADMIN.button },
				library: { type: 'image' },
				multiple: false,
			} );

			frame.on( 'select', function () {
				var attachment = frame.state().get( 'selection' ).first().toJSON();
				var url = attachment.url;

				if ( attachment.sizes && attachment.sizes.full ) {
					url = attachment.sizes.full.url;
				}

				$( '#whl_image_id' ).val( attachment.id );
				$( '#whl_image_url' ).val( url );
				$( '#whl-image-preview-img' ).attr( 'src', url );
				$( '.whl-image-preview' ).show();
				$( '#whl-remove-image' ).show();
			} );

			frame.open();
		} );

		$( '#whl-remove-image' ).on( 'click', function ( e ) {
			e.preventDefault();
			$( '#whl_image_id' ).val( '' );
			$( '#whl_image_url' ).val( '' );
			$( '#whl-image-preview-img' ).attr( 'src', '' );
			$( '.whl-image-preview' ).hide();
			$( this ).hide();
		} );

		// Toon/verberg de afstand-velden bij inline positionering.
		var $position = $( '#whl_position' );
		var $offsets = $( '.whl-offsets' );

		function toggleOffsets() {
			if ( 'inline' === $position.val() ) {
				$offsets.hide();
			} else {
				$offsets.show();
			}
		}

		$position.on( 'change', toggleOffsets );
		toggleOffsets();

		// Zwevend menu: items versleepbaar maken en de volgorde opslaan.
		var $sortable = $( '#whl-fab-sortable' );
		var $order    = $( '#whl-fab-order' );

		function syncOrder() {
			var tokens = $sortable.find( 'li' ).map( function () {
				return $( this ).data( 'token' );
			} ).get();

			$order.val( tokens.join( ',' ) );
		}

		if ( $sortable.length && $order.length && typeof $sortable.sortable === 'function' ) {
			$sortable.sortable( {
				items: '> li',
				handle: '.whl-fab-sortable__handle',
				axis: 'y',
				cursor: 'move',
				opacity: 0.8,
				placeholder: 'whl-fab-sortable__placeholder',
				forcePlaceholderSize: true,
				update: syncOrder,
			} );
			syncOrder();
		}
	} );
} )( jQuery );
