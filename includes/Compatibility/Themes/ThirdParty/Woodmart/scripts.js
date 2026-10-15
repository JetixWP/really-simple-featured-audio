/**
 * Woodmart gallery fixes for the featured audio slide.
 *
 * @package RSFA
 */

( function ( $ ) {
	var galleryHasAudio = function ( el ) {
		var gallery = el.closest( '.woocommerce-product-gallery' );

		return !! ( gallery && gallery.querySelector( '.rsfa-woodmart-slide' ) );
	};

	// Woodmart counts the audio slide when it opens the image lightbox, but the lightbox only lists images.
	var shiftLightbox = false;

	var wrapPhotoSwipe = function () {
		if ( 'undefined' === typeof woodmartThemeModule || 'function' !== typeof woodmartThemeModule.callPhotoSwipe || woodmartThemeModule.callPhotoSwipe.rsfaWrapped ) {
			return;
		}

		var callPhotoSwipe = woodmartThemeModule.callPhotoSwipe;

		woodmartThemeModule.callPhotoSwipe = function ( index, items ) {
			if ( shiftLightbox && index > 0 ) {
				index--;
			}

			shiftLightbox = false;

			return callPhotoSwipe.apply( this, [ index, items ].concat( Array.prototype.slice.call( arguments, 2 ) ) );
		};

		woodmartThemeModule.callPhotoSwipe.rsfaWrapped = true;
	};

	// Capture runs before Woodmart's delegated click handlers.
	document.addEventListener( 'click', function ( e ) {
		var target = e.target.closest( '.woocommerce-product-gallery__image > a, .woodmart-show-product-gallery' );

		if ( target && galleryHasAudio( target ) ) {
			wrapPhotoSwipe();
			shiftLightbox = true;
		}
	}, true );

	// Woodmart moves to the first slide on variation change, which is the audio here.
	$( document ).on( 'show_variation', '.variations_form', function ( e, variation ) {
		if ( ! variation || ! variation.image || ! variation.image.src ) {
			return;
		}

		setTimeout( function () {
			var mainGallery = document.querySelector( '.woocommerce-product-gallery__wrapper' );

			if ( mainGallery && mainGallery.swiper && galleryHasAudio( mainGallery ) && 0 === mainGallery.swiper.activeIndex ) {
				mainGallery.swiper.slideTo( 1 );
			}
		}, 0 );
	} );
}( jQuery ) );
