/**
 * Really Simple Featured Audio — anonymous view and play counts.
 *
 * Views: player is at least half on screen, once per tab session.
 * Plays: a real click or keypress on the player. Play on hover does not count.
 *
 * Playback events come from the JWP player instance (element.rsfaPlayer),
 * because the player plays through a detached Audio object that DOM
 * listeners on the page never see.
 *
 * @package RSFA
 */
( function () {
	'use strict';

	if ( typeof rsfaAnalytics === 'undefined' || ! rsfaAnalytics.endpoint ) {
		return;
	}

	var endpoint = rsfaAnalytics.endpoint;
	var allowed = Array.isArray( rsfaAnalytics.events ) ? rsfaAnalytics.events : [ 'view', 'play' ];
	var queue = [];
	var timer = null;
	var lastInput = { time: 0, target: null };

	/**
	 * Session key for one audio, event, and surface.
	 *
	 * @param {number} audioId Registry id.
	 * @param {string} eventName Event.
	 * @param {string} surface Surface key.
	 * @return {string} Key.
	 */
	function storageKey( audioId, eventName, surface ) {
		return 'rsfa:' + audioId + ':' + eventName + ':' + surface;
	}

	/**
	 * Whether this tab already counted the event.
	 *
	 * @param {number} audioId Registry id.
	 * @param {string} eventName Event.
	 * @param {string} surface Surface key.
	 * @return {boolean} True when counted.
	 */
	function already( audioId, eventName, surface ) {
		try {
			return window.sessionStorage.getItem( storageKey( audioId, eventName, surface ) ) === '1';
		} catch ( error ) {
			return false;
		}
	}

	/**
	 * Remember a counted event for this tab session.
	 *
	 * @param {number} audioId Registry id.
	 * @param {string} eventName Event.
	 * @param {string} surface Surface key.
	 */
	function remember( audioId, eventName, surface ) {
		try {
			window.sessionStorage.setItem( storageKey( audioId, eventName, surface ), '1' );
		} catch ( error ) {
			// Private browsing can block storage. The event can still be sent once.
		}
	}

	/**
	 * Send the queued events. The server adds 1 per event.
	 */
	function flush() {
		if ( ! queue.length || ! navigator.sendBeacon ) {
			return;
		}

		while ( queue.length ) {
			var batch = queue.splice( 0, 20 );
			var blob = new Blob( [ JSON.stringify( { events: batch } ) ], { type: 'application/json' } );

			navigator.sendBeacon( endpoint, blob );
		}
	}

	/**
	 * Send soon, and again if more events arrive.
	 */
	function schedule() {
		if ( timer ) {
			window.clearTimeout( timer );
		}

		timer = window.setTimeout( flush, 800 );
	}

	/**
	 * Host name of an outside referrer, or empty.
	 *
	 * @return {string} Host.
	 */
	function referrerHost() {
		try {
			if ( ! document.referrer ) {
				return '';
			}

			var url = new URL( document.referrer );

			if ( url.host === window.location.host ) {
				return '';
			}

			return url.host.replace( /^www\./, '' ).slice( 0, 191 );
		} catch ( error ) {
			return '';
		}
	}

	/**
	 * Queue one event. Everything except listen time counts once per session.
	 *
	 * @param {number|string} audioId Registry id.
	 * @param {string}        eventName Event.
	 * @param {string}        surface Surface key.
	 * @param {number}        seconds Seconds listened, for listen events.
	 */
	function track( audioId, eventName, surface, seconds ) {
		var id = parseInt( audioId, 10 );

		if ( ! id || ! surface || allowed.indexOf( eventName ) === -1 ) {
			return;
		}

		if ( eventName !== 'watch' ) {
			if ( already( id, eventName, surface ) ) {
				return;
			}

			remember( id, eventName, surface );
		}

		var item = {
			audio_id: id,
			event: eventName,
			surface: surface,
			seconds: Math.max( 0, Math.round( seconds || 0 ) ),
		};

		if ( eventName === 'view' && rsfaAnalytics.audience ) {
			item.referrer = referrerHost();
		}

		queue.push( item );
		schedule();
	}

	window.rsfaTrack = track;

	/**
	 * Whether the visitor just clicked or pressed a key inside this player.
	 *
	 * @param {HTMLElement} node Player element.
	 * @return {boolean} True for a user-started play.
	 */
	function startedByVisitor( node ) {
		return Date.now() - lastInput.time < 1500 && lastInput.target && node.contains( lastInput.target );
	}

	/**
	 * Listen to one mounted player.
	 *
	 * @param {HTMLElement} node   Stamped player element.
	 * @param {Object}      player JWP player instance.
	 */
	function watchPlayer( node, player ) {
		if ( ! player || typeof player.on !== 'function' || node.rsfaAnalyticsBound ) {
			return;
		}

		node.rsfaAnalyticsBound = true;

		var audioId = node.getAttribute( 'data-rsfa-audio-id' );
		var surface = node.getAttribute( 'data-rsfa-surface' ) || 'shortcode';
		var marks = {};
		var last = 0;
		var listened = 0;

		function sendListened() {
			var seconds = Math.round( listened );

			if ( seconds > 0 ) {
				track( audioId, 'watch', surface, seconds );
				listened = 0;
			}
		}

		player.on( 'play', function () {
			if ( startedByVisitor( node ) ) {
				track( audioId, 'play', surface );
			}
		} );

		player.on( 'timeupdate', function () {
			var audio = player.audio;

			if ( ! audio || ! audio.duration || ! isFinite( audio.duration ) ) {
				return;
			}

			var current = audio.currentTime;
			var percent = ( current / audio.duration ) * 100;

			[ 25, 50, 75 ].forEach( function ( mark ) {
				if ( ! marks[ mark ] && percent >= mark ) {
					marks[ mark ] = true;
					track( audioId, 'progress_' + mark, surface );
				}
			} );

			// Count normal playback only, not seeks.
			if ( current > last && current - last < 1.5 ) {
				listened += current - last;
			}

			last = current;
		} );

		player.on( 'ended', function () {
			track( audioId, 'complete', surface );
			sendListened();
		} );

		player.on( 'pause', sendListened );
		window.addEventListener( 'pagehide', sendListened );
	}

	/**
	 * Watch one stamped player for views, then for playback once it mounts.
	 *
	 * @param {HTMLElement} node Element with data-rsfa-analytics.
	 */
	function bind( node ) {
		if ( node.getAttribute( 'data-rsfa-bound' ) === '1' ) {
			return;
		}

		node.setAttribute( 'data-rsfa-bound', '1' );

		var audioId = node.getAttribute( 'data-rsfa-audio-id' );
		var surface = node.getAttribute( 'data-rsfa-surface' ) || 'shortcode';

		if ( 'IntersectionObserver' in window ) {
			var observer = new IntersectionObserver(
				function ( entries ) {
					entries.forEach( function ( entry ) {
						if ( entry.isIntersecting && entry.intersectionRatio >= 0.5 ) {
							track( audioId, 'view', surface );
							observer.disconnect();
						}
					} );
				},
				{ threshold: [ 0.5 ] }
			);

			observer.observe( node );
		}

		if ( node.rsfaPlayer ) {
			watchPlayer( node, node.rsfaPlayer );
		}
	}

	/**
	 * Bind every stamped player in the document.
	 */
	function scan() {
		Array.prototype.forEach.call( document.querySelectorAll( '[data-rsfa-analytics="1"]' ), bind );
	}

	[ 'pointerdown', 'keydown' ].forEach( function ( type ) {
		document.addEventListener(
			type,
			function ( event ) {
				if ( ! event.isTrusted ) {
					return;
				}

				if ( type === 'keydown' && event.key !== 'Enter' && event.key !== ' ' ) {
					return;
				}

				lastInput = { time: Date.now(), target: event.target };
			},
			true
		);
	} );

	// Players mount after this script runs, and again in AJAX-loaded content.
	document.addEventListener( 'rsfa:player-ready', function ( event ) {
		var node = event.target;

		if ( ! node || ! node.getAttribute || node.getAttribute( 'data-rsfa-analytics' ) !== '1' ) {
			return;
		}

		bind( node );
		watchPlayer( node, event.detail && event.detail.player );
	} );

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', scan );
	} else {
		scan();
	}

	window.addEventListener( 'pagehide', flush );
} )();
