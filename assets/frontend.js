/* Woo Header Link - zwevend, versleepbaar menu. */
( function () {
	'use strict';

	/**
	 * Initialiseer het zwevende menu. Wordt pas aangeroepen als de DOM klaar is,
	 * zodat het werkt ongeacht waar WordPress het script in de pagina plaatst.
	 */
	function whlInitFab() {
		var fab = document.getElementById( 'whl-fab' );
		if ( ! fab ) {
			return;
		}

		var toggle = document.getElementById( 'whl-fab-toggle' );
		var menu   = document.getElementById( 'whl-fab-menu' );
		if ( ! toggle || ! menu ) {
			return;
		}

		var STORAGE_KEY = 'whlFabPos';
		var MARGIN      = 8; // Minimale afstand tot de schermrand.
		var DRAG_SLOP   = 5; // Pixels voordat we het als slepen zien.

		var dragging      = false;
		var moved         = false;
		var suppressClick = false; // Negeer de klik die na een pointer-actie volgt.
		var pointerId     = null;
		var startX        = 0;
		var startY        = 0;
		var startLeft     = 0;
		var startTop      = 0;

		function clamp( value, min, max ) {
			return Math.min( Math.max( value, min ), max );
		}

		/**
		 * Zet de knop op een absolute left/top en schakel right/bottom uit.
		 */
		function setPosition( left, top ) {
			fab.style.left   = left + 'px';
			fab.style.top    = top + 'px';
			fab.style.right  = 'auto';
			fab.style.bottom = 'auto';
		}

		/**
		 * Houd de knop volledig binnen het scherm.
		 */
		function clampToViewport() {
			var rect = fab.getBoundingClientRect();
			var maxLeft = window.innerWidth - rect.width - MARGIN;
			var maxTop  = window.innerHeight - rect.height - MARGIN;

			setPosition(
				clamp( rect.left, MARGIN, Math.max( MARGIN, maxLeft ) ),
				clamp( rect.top, MARGIN, Math.max( MARGIN, maxTop ) )
			);
		}

		function savePosition() {
			var rect = fab.getBoundingClientRect();
			try {
				localStorage.setItem( STORAGE_KEY, JSON.stringify( { left: rect.left, top: rect.top } ) );
			} catch ( e ) {}
		}

		function restorePosition() {
			var saved = null;
			try {
				saved = JSON.parse( localStorage.getItem( STORAGE_KEY ) || 'null' );
			} catch ( e ) {}

			if ( saved && typeof saved.left === 'number' && typeof saved.top === 'number' ) {
				setPosition( saved.left, saved.top );
				clampToViewport();
			}
		}

		function isOpen() {
			return fab.classList.contains( 'whl-fab--open' );
		}

		/**
		 * Kies de richting van het menu op basis van de beschikbare ruimte.
		 */
		function positionMenu() {
			var rect  = fab.getBoundingClientRect();
			var menuH = menu.offsetHeight;
			var spaceAbove = rect.top;
			var spaceBelow = window.innerHeight - rect.bottom;

			var openDown = spaceAbove < menuH + 20 && spaceBelow > spaceAbove;
			fab.classList.toggle( 'whl-fab--menu-down', openDown );

			// Begrens de hoogte op de beschikbare ruimte zodat het menu niet
			// buiten het scherm valt, ook bij veel categorieën.
			var space = ( openDown ? spaceBelow : spaceAbove ) - 20;
			menu.style.maxHeight = Math.max( 120, space ) + 'px';

			var alignLeft = rect.left < window.innerWidth / 2;
			fab.classList.toggle( 'whl-fab--menu-left', alignLeft );
		}

		function setMenuOpen( open ) {
			fab.classList.toggle( 'whl-fab--open', open );
			toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			menu.setAttribute( 'aria-hidden', open ? 'false' : 'true' );
			if ( open ) {
				positionMenu();
			}
		}

		function toggleMenu() {
			setMenuOpen( ! isOpen() );
		}

		/* ---- Openen/sluiten: via click, werkt met muis, touch en toetsenbord ---- */

		toggle.addEventListener( 'click', function ( e ) {
			if ( suppressClick ) {
				e.preventDefault();
				suppressClick = false; // Vlag verbruiken zodat een volgende klik wél werkt.
				return;
			}
			toggleMenu();
		} );

		/* ---- Slepen: via pointer events ---- */

		if ( window.PointerEvent ) {
			toggle.addEventListener( 'pointerdown', function ( e ) {
				if ( e.button !== undefined && e.button !== 0 ) {
					return;
				}

				suppressClick = false;
				dragging  = true;
				moved     = false;
				pointerId = e.pointerId;

				var rect = fab.getBoundingClientRect();
				startX    = e.clientX;
				startY    = e.clientY;
				startLeft = rect.left;
				startTop  = rect.top;

				fab.classList.add( 'whl-fab--dragging' );
				try {
					toggle.setPointerCapture( pointerId );
				} catch ( err ) {}
			} );

			toggle.addEventListener( 'pointermove', function ( e ) {
				if ( ! dragging || e.pointerId !== pointerId ) {
					return;
				}

				var dx = e.clientX - startX;
				var dy = e.clientY - startY;

				if ( ! moved && Math.abs( dx ) + Math.abs( dy ) > DRAG_SLOP ) {
					moved = true;
					setMenuOpen( false );
				}
				if ( ! moved ) {
					return;
				}

				var rect   = fab.getBoundingClientRect();
				var maxLeft = window.innerWidth - rect.width - MARGIN;
				var maxTop  = window.innerHeight - rect.height - MARGIN;

				setPosition(
					clamp( startLeft + dx, MARGIN, Math.max( MARGIN, maxLeft ) ),
					clamp( startTop + dy, MARGIN, Math.max( MARGIN, maxTop ) )
				);

				e.preventDefault();
			} );

			function endDrag() {
				if ( ! dragging ) {
					return;
				}
				dragging = false;
				fab.classList.remove( 'whl-fab--dragging' );

				try {
					if ( pointerId !== null && toggle.hasPointerCapture && toggle.hasPointerCapture( pointerId ) ) {
						toggle.releasePointerCapture( pointerId );
					}
				} catch ( err ) {}

				if ( moved ) {
					clampToViewport();
					savePosition();
					suppressClick = true; // De klik na het slepen negeren.
				}

				pointerId = null;
			}

			toggle.addEventListener( 'pointerup', endDrag );
			toggle.addEventListener( 'pointercancel', function () {
				dragging = false;
				fab.classList.remove( 'whl-fab--dragging' );
				pointerId = null;
			} );
		}

		/* Sluiten bij klik buiten de knop of met Escape. */
		document.addEventListener( 'click', function ( e ) {
			if ( isOpen() && ! fab.contains( e.target ) ) {
				setMenuOpen( false );
			}
		} );

		document.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Escape' && isOpen() ) {
				setMenuOpen( false );
			}
		} );

		/* Bij resize opnieuw klemmen. */
		window.addEventListener( 'resize', function () {
			clampToViewport();
			if ( isOpen() ) {
				positionMenu();
			}
		} );

		restorePosition();
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', whlInitFab );
	} else {
		whlInitFab();
	}
} )();
