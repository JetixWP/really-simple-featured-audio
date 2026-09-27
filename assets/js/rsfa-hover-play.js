/**
 * Really Simple Featured Audio: Play on Hover.
 *
 * Plays a featured audio while the pointer is over its player and pauses it
 * when the pointer leaves. Browsers may block sound until the visitor has
 * interacted with the page; in that case nothing plays until they do.
 *
 * @package RSFA
 */

( function () {
	'use strict';

	var settings = window.RSFAHoverPlaySettings || {};
	var types = settings.audioTypes || { self: true, embed: true };
	var delay = parseInt( settings.hoverDelay, 10 ) || 0;
	var breakpoint = parseInt( settings.mobileBreakpoint, 10 ) || 768;

	// Extra selectors wrap a player, e.g. a product card; hovering them plays it.
	var extraSelectors = String( settings.extraSelectors || '' )
		.split( /[\n,]/ )
		.map( function ( selector ) {
			return selector.trim();
		} )
		.filter( Boolean )
		.join( ', ' );

	function screenAllowed() {
		var isMobile = window.innerWidth < breakpoint;

		return isMobile ? settings.enableOnMobile !== false : settings.enableOnDesktop !== false;
	}

	function prefersReducedMotion() {
		return settings.respectUserPreferences !== false &&
			window.matchMedia &&
			window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	}

	function sourceOf( el ) {
		try {
			var config = JSON.parse( el.getAttribute( 'data-rsfa-player' ) );
			return ( config && config.rsfa && config.rsfa.source ) || 'self';
		} catch ( error ) {
			return 'self';
		}
	}

	function hoverTarget( el ) {
		if ( extraSelectors && el.closest ) {
			try {
				return el.closest( extraSelectors ) || el;
			} catch ( error ) {
				return el;
			}
		}

		return el;
	}

	/**
	 * Wire hover handling to one mounted player.
	 *
	 * @param {Element} el Player element.
	 * @param {Object} player JWP player instance.
	 * @return {void}
	 */
	function bind( el, player ) {
		if ( ! el || ! player || el.rsfaHoverBound ) {
			return;
		}

		el.rsfaHoverBound = true;

		if ( ! types[ sourceOf( el ) ] ) {
			return;
		}

		var target = hoverTarget( el );
		var timer = null;
		var startedByHover = false;

		function start() {
			if ( ! screenAllowed() || prefersReducedMotion() ) {
				return;
			}

			clearTimeout( timer );
			timer = setTimeout( function () {
				if ( player.audio && ! player.audio.paused ) {
					return;
				}

				startedByHover = true;

				var result = player.play();

				if ( result && typeof result.catch === 'function' ) {
					result.catch( function () {
						startedByHover = false;
					} );
				}
			}, delay );
		}

		function stop() {
			clearTimeout( timer );

			if ( startedByHover ) {
				startedByHover = false;
				player.pause();
			}
		}

		target.addEventListener( 'mouseenter', start );
		target.addEventListener( 'mouseleave', stop );

		if ( settings.enableFocusEvents !== false ) {
			target.addEventListener( 'focusin', start );
			target.addEventListener( 'focusout', function ( event ) {
				if ( ! target.contains( event.relatedTarget ) ) {
					stop();
				}
			} );
		}

		// Once the visitor uses the player themselves, leave it alone.
		el.addEventListener(
			'click',
			function () {
				clearTimeout( timer );
				startedByHover = false;
			},
			true
		);
	}

	document.addEventListener( 'rsfa:player-ready', function ( event ) {
		bind( event.target, event.detail && event.detail.player );
	} );

	// Players mounted before this script ran.
	function scan() {
		Array.prototype.forEach.call( document.querySelectorAll( '[data-rsfa-ready]' ), function ( el ) {
			bind( el, el.rsfaPlayer );
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', scan );
	} else {
		scan();
	}
} )();
