/**
 * Audio Type Select Component
 *
 * Handles audio type selection for each post.
 *
 * @package RSFA
 */

import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

const AudioTypeSelect = ( { post, onUpdate } ) => {
	const [ saving, setSaving ] = useState( false );

	/**
	 * Calculate has_audio based on source type and available data.
	 *
	 * @param {string} source Audio source type.
	 * @return {boolean} Whether audio exists for the source.
	 */
	const calculateHasAudio = ( source ) => {
		if ( source === 'self' && post.audio_id ) {
			return true;
		}
		if ( source === 'embed' && post.embed_url ) {
			return true;
		}
		return false;
	};

	const handleSourceChange = async ( newSource ) => {
		setSaving( true );

		try {
			await apiFetch( {
				path: '/rsfa/v1/posts/update-source',
				method: 'POST',
				data: {
					post_id: post.id,
					audio_source: newSource,
				},
			} );

			if ( onUpdate ) {
				onUpdate( post.id, {
					audio_source: newSource,
					has_audio: calculateHasAudio( newSource ),
				} );
			}
		} catch ( error ) {
			console.error( 'Error updating audio source:', error );
		} finally {
			setSaving( false );
		}
	};

	return (
		<div className="rsfa-audio-type-select">
			<select
				value={ post.audio_source || '' }
				onChange={ ( e ) => handleSourceChange( e.target.value ) }
				disabled={ saving }
				className="rsfa-audio-source-select"
			>
				<option value="">{ __( 'Select Type', 'really-simple-featured-audio' ) }</option>
				<option value="self">{ __( 'Self Hosted', 'really-simple-featured-audio' ) }</option>
				<option value="embed">{ __( 'Embed', 'really-simple-featured-audio' ) }</option>
			</select>
			{ saving && (
				<span className="spinner is-active rsfa-inline-spinner"></span>
			) }
		</div>
	);
};

export default AudioTypeSelect;
