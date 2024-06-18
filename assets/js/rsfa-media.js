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
							$( button ).removeClass( 'button' ).html( '<audio controls="" src="' + attachment.url + '"></audio>' ).next().val( attachment.id ).next().show();
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
					$( this ).hide().prev().val( '' ).prev().addClass( 'button' ).html( 'Upload Audio' );
					return false;
				}
			);

			// Toggles audio input source.
			function toggleAudioInput( val ) {
				console.log( val, typeof val );
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
