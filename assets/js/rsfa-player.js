/**
 * Really Simple Featured Audio: player loader.
 *
 * Mounts a JWP Audio Player on every element that carries a `data-rsfa-player`
 * attribute (a JSON player config). The instance is kept on the element as
 * `element.rsfaPlayer`, and an `rsfa:player-ready` event bubbles up from it so
 * other features can hook into the player.
 *
 * Markup added after page load (AJAX, sliders, page builders) is picked up
 * automatically, and `window.RSFAPlayer.init( root )` can be called by hand.
 *
 * @package RSFA
 */

( function () {
	'use strict';

	var SELECTOR = '[data-rsfa-player]';

	/**
	 * Mount a player on one element.
	 *
	 * @param {Element} el Element with a data-rsfa-player attribute.
	 * @return {Object|null} Player instance.
	 */
	function mount( el ) {
		if ( el.rsfaPlayer ) {
			return el.rsfaPlayer;
		}

		if ( ! window.JWP_Audio_Player || ! window.JWP_Audio_Player.Player ) {
			return null;
		}

		var config;

		try {
			config = JSON.parse( el.getAttribute( 'data-rsfa-player' ) );
		} catch ( error ) {
			return null;
		}

		if ( ! config || ! config.audio || ! config.audio.src ) {
			return null;
		}

		config.container = el;

		var player = new window.JWP_Audio_Player.Player( config );

		el.rsfaPlayer = player;
		el.setAttribute( 'data-rsfa-ready', '' );

		// The player's own autoplay handling reads this global.
		window.JWP_Audio_Player_Instance = player;

		var event;

		try {
			event = new CustomEvent( 'rsfa:player-ready', {
				bubbles: true,
				detail: { player: player },
			} );
		} catch ( error ) {
			event = document.createEvent( 'CustomEvent' );
			event.initCustomEvent( 'rsfa:player-ready', true, false, { player: player } );
		}

		el.dispatchEvent( event );

		return player;
	}

	/**
	 * Mount players inside a root element, including the root itself.
	 *
	 * @param {Element|Document} root Where to look. Defaults to the document.
	 * @return {void}
	 */
	function init( root ) {
		root = root || document;

		if ( root.nodeType === 1 && root.matches && root.matches( SELECTOR ) ) {
			mount( root );
		}

		if ( root.querySelectorAll ) {
			Array.prototype.forEach.call( root.querySelectorAll( SELECTOR ), mount );
		}
	}

	/**
	 * Get the player instance mounted on an element, if any.
	 *
	 * @param {Element} el Player element.
	 * @return {Object|null} Player instance.
	 */
	function get( el ) {
		return ( el && el.rsfaPlayer ) || null;
	}

	/**
	 * Watch for player markup added after load.
	 *
	 * @return {void}
	 */
	function observe() {
		if ( ! window.MutationObserver || ! document.body ) {
			return;
		}

		new MutationObserver( function ( mutations ) {
			mutations.forEach( function ( mutation ) {
				Array.prototype.forEach.call( mutation.addedNodes, function ( node ) {
					if ( node.nodeType !== 1 ) {
						return;
					}

					if ( ( node.matches && node.matches( SELECTOR ) ) || ( node.querySelector && node.querySelector( SELECTOR ) ) ) {
						init( node );
					}
				} );
			} );
		} ).observe( document.body, { childList: true, subtree: true } );
	}

	/**
	 * Stop clicks on the player from following the featured image link
	 * that some block themes wrap around the post thumbnail.
	 *
	 * @param {Event} event Click event.
	 * @return {void}
	 */
	function guardFeaturedLinks( event ) {
		var link = event.target.closest && event.target.closest( '.rsfa-has-audio > figure.wp-block-post-featured-image > a' );

		if ( link && event.target !== link ) {
			event.preventDefault();
		}
	}

	window.RSFAPlayer = {
		init: init,
		get: get,
	};

	document.addEventListener( 'click', guardFeaturedLinks );

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', function () {
			init();
			observe();
		} );
	} else {
		init();
		observe();
	}
} )();
