/* Woo Header Link - zwevend, versleepbaar menu. */
( function () {
	'use strict';

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

	var dragging  = false;
	var moved     = false;
	var pointerId = null;
	var startX    = 0;
	var startY    = 0;
	var startLeft = 0;
	var startTop  = 0;

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

	function openMenu() {
		setMenuOpen( true );
	}

	function closeMenu() {
		setMenuOpen( false );
	}

	/* ---- Slepen ---- */

	toggle.addEventListener( 'pointerdown', function ( e ) {
		if ( e.button !== undefined && e.button !== 0 ) {
			return;
		}

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
			closeMenu();
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

	function endDrag( e ) {
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
		} else {
			// Echte klik: menu openen of sluiten.
			if ( isOpen() ) {
				closeMenu();
			} else {
				openMenu();
			}
		}

		pointerId = null;
	}

	toggle.addEventListener( 'pointerup', endDrag );
	toggle.addEventListener( 'pointercancel', function ( e ) {
		dragging = false;
		fab.classList.remove( 'whl-fab--dragging' );
		pointerId = null;
	} );

	/* Toetsenbord: Enter/Spatie op de knop wordt al door de browser als click
	   afgehandeld, maar pointerup vangt dat niet altijd. Vang click apart af
	   wanneer er geen pointer-actie was. */
	toggle.addEventListener( 'keydown', function ( e ) {
		if ( e.key === 'Enter' || e.key === ' ' || e.key === 'Spacebar' ) {
			e.preventDefault();
			if ( isOpen() ) {
				closeMenu();
			} else {
				openMenu();
			}
		}
	} );

	/* Sluiten bij klik buiten de knop of met Escape. */
	document.addEventListener( 'click', function ( e ) {
		if ( isOpen() && ! fab.contains( e.target ) ) {
			closeMenu();
		}
	} );

	document.addEventListener( 'keydown', function ( e ) {
		if ( e.key === 'Escape' && isOpen() ) {
			closeMenu();
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
} )();
