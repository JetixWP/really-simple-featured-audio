/**
 * Thumbnail Cell Component
 *
 * Handles thumbnail display and set/remove actions on hover.
 *
 * @package RSFA
 */

import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

const ThumbnailCell = ( { post, onUpdate } ) => {
	const [ saving, setSaving ] = useState( false );
	const hasThumbnail = !! post.thumbnail;

	const openMediaUploader = () => {
		const frame = wp.media( {
			title: __( 'Select Featured Image', 'really-simple-featured-audio' ),
			button: {
				text: __( 'Set featured image', 'really-simple-featured-audio' ),
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

			setSaving( true );

			try {
				await apiFetch( {
					path: '/rsfa/v1/posts/update-thumbnail',
					method: 'POST',
					data: {
						post_id: post.id,
						thumbnail_id: attachment.id,
					},
				} );

				if ( onUpdate ) {
					onUpdate( post.id, {
						thumbnail: attachment.sizes?.thumbnail?.url || attachment.url,
					} );
				}
			} catch ( error ) {
				console.error( 'Error setting thumbnail:', error );
			} finally {
				setSaving( false );
			}
		} );

		frame.open();
	};

	const handleRemoveThumbnail = async ( e ) => {
		e.stopPropagation();

		setSaving( true );

		try {
			await apiFetch( {
				path: '/rsfa/v1/posts/update-thumbnail',
				method: 'POST',
				data: {
					post_id: post.id,
					thumbnail_id: 0,
				},
			} );

			if ( onUpdate ) {
				onUpdate( post.id, {
					thumbnail: '',
				} );
			}
		} catch ( error ) {
			console.error( 'Error removing thumbnail:', error );
		} finally {
			setSaving( false );
		}
	};

	if ( saving ) {
		return (
			<div className="rsfa-thumbnail-cell rsfa-thumbnail-saving">
				<span className="spinner is-active"></span>
			</div>
		);
	}

	if ( hasThumbnail ) {
		return (
			<div
				className="rsfa-thumbnail-cell rsfa-has-thumbnail"
				onClick={ openMediaUploader }
			>
				<img
					src={ post.thumbnail }
					alt={ post.title }
					className="rsfa-thumbnail"
				/>
				<div className="rsfa-thumbnail-overlay rsfa-thumbnail-remove">
					<button
						className="rsfa-thumbnail-action"
						onClick={ handleRemoveThumbnail }
						title={ __( 'Remove featured image', 'really-simple-featured-audio' ) }
					>
						<span className="dashicons dashicons-trash"></span>
					</button>
				</div>
			</div>
		);
	}

	return (
		<div
			className="rsfa-thumbnail-cell rsfa-no-thumbnail"
			onClick={ openMediaUploader }
		>
			<span className="dashicons dashicons-format-image"></span>
			<div className="rsfa-thumbnail-overlay rsfa-thumbnail-add">
				<button
					className="rsfa-thumbnail-action"
					title={ __( 'Set featured image', 'really-simple-featured-audio' ) }
				>
					<span className="dashicons dashicons-plus-alt2"></span>
				</button>
			</div>
		</div>
	);
};

export default ThumbnailCell;
