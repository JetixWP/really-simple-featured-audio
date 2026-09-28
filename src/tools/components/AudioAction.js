/**
 * Audio Action Component
 *
 * Handles audio upload/embed action for each post.
 *
 * @package RSFA
 */

import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

const AudioAction = ( { post, onUpdate } ) => {
	const [ embedUrl, setEmbedUrl ] = useState( post.embed_url || '' );
	const [ saving, setSaving ] = useState( false );
	const [ savingCover, setSavingCover ] = useState( false );

	const audioSource = post.audio_source || '';

	// No audio type selected.
	if ( ! audioSource ) {
		return <span className="rsfa-no-action">—</span>;
	}

	const openMediaUploader = () => {
		const frame = wp.media( {
			title: __( 'Select or Upload Audio', 'really-simple-featured-audio' ),
			button: {
				text: __( 'Use this audio', 'really-simple-featured-audio' ),
			},
			library: {
				type: 'audio',
			},
			multiple: false,
		} );

		frame.on( 'select', async () => {
			const attachment = frame
				.state()
				.get( 'selection' )
				.first()
				.toJSON();

			setSaving( true );

			try {
				await apiFetch( {
					path: '/rsfa/v1/posts/update-audio',
					method: 'POST',
					data: {
						post_id: post.id,
						audio_source: 'self',
						audio_id: attachment.id,
					},
				} );

				if ( onUpdate ) {
					onUpdate( post.id, {
						audio_source: 'self',
						audio_id: attachment.id,
						audio_url: attachment.url,
						has_audio: true,
					} );
				}
			} catch ( error ) {
				console.error( 'Error saving audio:', error );
				alert( error.message || __( 'Error saving audio.', 'really-simple-featured-audio' ) );
			} finally {
				setSaving( false );
			}
		} );

		frame.open();
	};

	const openCoverUploader = () => {
		const frame = wp.media( {
			title: __( 'Select Cover Image', 'really-simple-featured-audio' ),
			button: {
				text: __( 'Use this image', 'really-simple-featured-audio' ),
			},
			library: {
				type: 'image',
			},
			multiple: false,
		} );

		frame.on( 'select', async () => {
			const attachment = frame
				.state()
				.get( 'selection' )
				.first()
				.toJSON();

			setSavingCover( true );

			try {
				await apiFetch( {
					path: '/rsfa/v1/posts/update-cover',
					method: 'POST',
					data: {
						post_id: post.id,
						cover_id: attachment.id,
					},
				} );

				if ( onUpdate ) {
					onUpdate( post.id, {
						cover_id: attachment.id,
						cover_url: attachment.url,
					} );
				}
			} catch ( error ) {
				console.error( 'Error saving cover:', error );
				alert( error.message || __( 'Error saving cover.', 'really-simple-featured-audio' ) );
			} finally {
				setSavingCover( false );
			}
		} );

		frame.open();
	};

	/**
	 * Validate URL format.
	 *
	 * @param {string} url URL to validate.
	 * @return {boolean} True if valid URL.
	 */
	const isValidUrl = ( url ) => {
		if ( ! url ) {
			return false;
		}
		try {
			const parsedUrl = new URL( url );
			return [ 'http:', 'https:' ].includes( parsedUrl.protocol );
		} catch ( e ) {
			return false;
		}
	};

	const handleEmbedSave = async () => {
		// Client-side URL validation.
		if ( embedUrl && ! isValidUrl( embedUrl ) ) {
			alert( __( 'Please enter a valid URL.', 'really-simple-featured-audio' ) );
			return;
		}

		setSaving( true );

		try {
			await apiFetch( {
				path: '/rsfa/v1/posts/update-audio',
				method: 'POST',
				data: {
					post_id: post.id,
					audio_source: 'embed',
					embed_url: embedUrl,
				},
			} );

			if ( onUpdate ) {
				onUpdate( post.id, {
					audio_source: 'embed',
					embed_url: embedUrl,
					has_audio: !! embedUrl,
				} );
			}
		} catch ( error ) {
			console.error( 'Error saving audio link:', error );
			alert( error.message || __( 'Error saving audio link.', 'really-simple-featured-audio' ) );
		} finally {
			setSaving( false );
		}
	};

	const handleRemoveAudio = async () => {
		if ( ! confirm( __( 'Are you sure you want to remove the audio?', 'really-simple-featured-audio' ) ) ) {
			return;
		}

		setSaving( true );

		try {
			await apiFetch( {
				path: '/rsfa/v1/posts/update-audio',
				method: 'POST',
				data: {
					post_id: post.id,
					audio_source: 'self',
					audio_id: 0,
				},
			} );

			if ( onUpdate ) {
				onUpdate( post.id, {
					audio_id: 0,
					audio_url: '',
					has_audio: false,
				} );
			}
		} catch ( error ) {
			console.error( 'Error removing audio:', error );
		} finally {
			setSaving( false );
		}
	};

	const handleRemoveCover = async () => {
		if ( ! confirm( __( 'Are you sure you want to remove the cover?', 'really-simple-featured-audio' ) ) ) {
			return;
		}

		setSavingCover( true );

		try {
			await apiFetch( {
				path: '/rsfa/v1/posts/update-cover',
				method: 'POST',
				data: {
					post_id: post.id,
					cover_id: 0,
				},
			} );

			if ( onUpdate ) {
				onUpdate( post.id, {
					cover_id: 0,
					cover_url: '',
				} );
			}
		} catch ( error ) {
			console.error( 'Error removing cover:', error );
		} finally {
			setSavingCover( false );
		}
	};

	const hasCover = !! post.cover_id;
	const coverButtonText = hasCover
		? __( 'Edit Cover', 'really-simple-featured-audio' )
		: __( 'Set Cover', 'really-simple-featured-audio' );

	// Cover image works for both sources.
	const coverRow = post.has_audio && (
		<div className="rsfa-action-row">
			<button
				className="button button-small"
				onClick={ openCoverUploader }
				disabled={ saving || savingCover }
			>
				{ savingCover ? __( 'Saving...', 'really-simple-featured-audio' ) : coverButtonText }
			</button>
			{ hasCover && (
				<button
					className="button button-small button-link-delete"
					onClick={ handleRemoveCover }
					disabled={ saving || savingCover }
				>
					{ __( 'Remove', 'really-simple-featured-audio' ) }
				</button>
			) }
		</div>
	);

	// Self-hosted audio action.
	if ( audioSource === 'self' ) {
		const hasAudio = !! post.audio_id;
		const audioButtonText = hasAudio
			? __( 'Edit Audio', 'really-simple-featured-audio' )
			: __( 'Upload Audio', 'really-simple-featured-audio' );
		const audioButtonClass = hasAudio
			? 'button button-small'
			: 'button button-small button-primary';

		return (
			<div className="rsfa-audio-action rsfa-self-action">
				<div className="rsfa-action-row">
					<button
						className={ audioButtonClass }
						onClick={ openMediaUploader }
						disabled={ saving || savingCover }
					>
						{ saving ? __( 'Saving...', 'really-simple-featured-audio' ) : audioButtonText }
					</button>
					{ hasAudio && (
						<button
							className="button button-small button-link-delete button-warning"
							onClick={ handleRemoveAudio }
							disabled={ saving || savingCover }
						>
							{ __( 'Remove', 'really-simple-featured-audio' ) }
						</button>
					) }
				</div>
				{ coverRow }
			</div>
		);
	}

	// Audio link action.
	if ( audioSource === 'embed' ) {
		const urlIsValid = ! embedUrl || isValidUrl( embedUrl );
		const hasChanged = embedUrl !== ( post.embed_url || '' );

		return (
			<div className="rsfa-audio-action rsfa-embed-action">
				<div className="rsfa-action-row">
					<input
						type="url"
						className={ `rsfa-embed-input${ ! urlIsValid ? ' rsfa-invalid-url' : '' }` }
						value={ embedUrl }
						onChange={ ( e ) => setEmbedUrl( e.target.value ) }
						placeholder={ __( 'Enter audio link...', 'really-simple-featured-audio' ) }
						disabled={ saving }
					/>
					<button
						className="button button-small button-primary"
						onClick={ handleEmbedSave }
						disabled={ saving || ! hasChanged || ( embedUrl && ! urlIsValid ) }
					>
						{ saving ? __( 'Saving...', 'really-simple-featured-audio' ) : __( 'Save', 'really-simple-featured-audio' ) }
					</button>
				</div>
				{ coverRow }
			</div>
		);
	}

	return null;
};

export default AudioAction;
