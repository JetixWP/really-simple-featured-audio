/**
 * File rsfa-media.js.
 *
 * Plugin media script.
 *
 * @package RSFA
 */

(function($, RSFA ){
	$(
		function() {
			// Selecting audio.
			$( 'body' ).on(
				'click',
				'.rsfa-upload-audio-btn',
				function (e) {
					e.preventDefault();
					var button     = $( this ),
					customUploader = wp.media(
						{
							title: RSFA.uploader_title,
							library: {
								type: 'audio'
							},
							button: {
								text: RSFA.uploader_btn_text // button label text.
							},
							multiple: false // for multiple image selection set to true.
						}
					).on(
						'select',
						function () { // it also has "open" and "close" events.
							var attachment = customUploader.state().get( 'selection' ).first().toJSON();
							var preview    = $( '<audio controls></audio>' ).attr( 'src', attachment.url );
							$( button ).removeClass( 'button' ).empty().append( preview ).next().val( attachment.id ).next().show();
						}
					)
					.open();
				}
			);

			// Removing audio.
			$( 'body' ).on(
				'click',
				'.remove-audio',
				function () {
					$( this ).hide().prev().val( '' ).prev().addClass( 'button' ).text( RSFA.upload_btn_text );
					return false;
				}
			);

			// Selecting a cover image.
			var coverFrame;

			$( document ).on(
				'click',
				'.rsfa-set-cover',
				function ( e ) {
					e.preventDefault();

					if ( coverFrame ) {
						coverFrame.open();
						return;
					}

					coverFrame = wp.media(
						{
							title: RSFA.cover_uploader_title,
							button: { text: RSFA.cover_uploader_btn_text },
							library: { type: 'image' },
							multiple: false
						}
					);

					coverFrame.on(
						'select',
						function () {
							var attachment = coverFrame.state().get( 'selection' ).first().toJSON();
							var size       = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium : attachment;

							$( '#' + RSFA.meta_cover_key ).val( attachment.id );
							$( '#rsfa-cover-preview' ).attr( 'src', size.url ).show();
							$( '.rsfa-remove-cover' ).show();
						}
					);

					coverFrame.open();
				}
			);

			// Removing the cover image.
			$( document ).on(
				'click',
				'.rsfa-remove-cover',
				function ( e ) {
					e.preventDefault();
					$( '#' + RSFA.meta_cover_key ).val( '' );
					$( '#rsfa-cover-preview' ).attr( 'src', '' ).hide();
					$( this ).hide();
				}
			);

			// Toggles audio input source.
			function toggleAudioInput( val ) {
				if ( 'self' === val ) {
					$( '.rsfa-self' ).show();
					$( '.rsfa-embed' ).hide();
				} else {
					$( '.rsfa-embed' ).show();
					$( '.rsfa-self' ).hide();
				}
			}

			toggleAudioInput( $( 'input[type=radio][name=rsfa_source]:checked' ).val() );
			$( 'input[type=radio][name=rsfa_source]' ).on(
				'change',
				function() {
					toggleAudioInput( $( this ).val() );
				}
			);

		}
	);
}( jQuery, RSFA ) );
