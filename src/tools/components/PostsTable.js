/**
 * Posts Table Component
 *
 * @package RSFA
 */

import { useState, useMemo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import AudioTypeSelect from './AudioTypeSelect';
import AudioAction from './AudioAction';
import AudioPreview from './AudioPreview';
import ThumbnailCell from './ThumbnailCell';
import { applyFilters, doAction } from '../hooks';

const PostsTable = ( { posts: initialPosts, onRefresh } ) => {
	const [ posts, setPosts ] = useState( initialPosts );

	// Get columns from config, allowing extensions to add more.
	const columns = useMemo( () => {
		const baseColumns = window.rsfaTools?.columns || {};
		return applyFilters( 'rsfa_tools_columns', baseColumns );
	}, [] );

	// Update posts when initialPosts changes.
	if ( initialPosts !== posts && initialPosts.length !== posts.length ) {
		setPosts( initialPosts );
	}

	if ( ! posts || posts.length === 0 ) {
		return (
			<div className="rsfa-no-posts">
				<p>{ __( 'No posts found for this post type.', 'really-simple-featured-audio' ) }</p>
			</div>
		);
	}

	const getAudioStatusBadge = ( post ) => {
		if ( post.has_audio ) {
			return (
				<span className="rsfa-badge rsfa-badge-success">
					{ __( 'Has Audio', 'really-simple-featured-audio' ) }
				</span>
			);
		}
		return (
			<span className="rsfa-badge rsfa-badge-default">
				{ __( 'No Audio', 'really-simple-featured-audio' ) }
			</span>
		);
	};

	const handlePostUpdate = ( postId, updates ) => {
		setPosts( ( currentPosts ) =>
			currentPosts.map( ( post ) =>
				post.id === postId ? { ...post, ...updates } : post
			)
		);

		// Trigger action for extensions to listen to.
		doAction( 'rsfa_tools_post_updated', postId, updates );
	};

	/**
	 * Render cell content based on column key.
	 *
	 * @param {string} columnKey Column key.
	 * @param {Object} post      Post data.
	 * @return {JSX.Element|string} Cell content.
	 */
	const renderCellContent = ( columnKey, post ) => {
		// Allow extensions to override cell content.
		const customContent = applyFilters(
			'rsfa_tools_cell_content',
			null,
			columnKey,
			post,
			handlePostUpdate
		);

		if ( customContent !== null ) {
			return customContent;
		}

		// Default cell renderers.
		switch ( columnKey ) {
			case 'thumbnail':
				return (
					<ThumbnailCell post={ post } onUpdate={ handlePostUpdate } />
				);

			case 'title':
				return (
					<>
						<strong>
							<a
								href={ post.edit_link }
								target="_blank"
								rel="noopener noreferrer"
							>
								{ post.title || __( '(No title)', 'really-simple-featured-audio' ) }
							</a>
						</strong>
						<div className="row-actions">
							<span className="edit">
								<a
									href={ post.edit_link }
									target="_blank"
									rel="noopener noreferrer"
								>
									{ __( 'Edit', 'really-simple-featured-audio' ) }
								</a>
							</span>
							{ ' | ' }
							<span className="view">
								<a
									href={ post.permalink }
									target="_blank"
									rel="noopener noreferrer"
								>
									{ __( 'View', 'really-simple-featured-audio' ) }
								</a>
							</span>
						</div>
					</>
				);

			case 'status_type':
				return (
					<div className="rsfa-status-type">
						{ getAudioStatusBadge( post ) }
						<AudioTypeSelect
							post={ post }
							onUpdate={ handlePostUpdate }
						/>
					</div>
				);

			case 'audio_action':
				return (
					<AudioAction post={ post } onUpdate={ handlePostUpdate } />
				);

			case 'audio_preview':
				return <AudioPreview post={ post } />;

			default:
				// For unknown columns, check if post has data for it.
				return post[ columnKey ] || '';
		}
	};

	return (
		<table className="rsfa-posts-table wp-list-table widefat fixed striped">
			<thead>
				<tr>
					{ Object.entries( columns ).map( ( [ key, column ] ) => (
						<th key={ key } className={ column.class || '' }>
							{ column.label }
						</th>
					) ) }
				</tr>
			</thead>
			<tbody>
				{ posts.map( ( post ) => (
					<tr key={ post.id }>
						{ Object.entries( columns ).map( ( [ key, column ] ) => (
							<td key={ key } className={ column.class || '' }>
								{ renderCellContent( key, post ) }
							</td>
						) ) }
					</tr>
				) ) }
			</tbody>
		</table>
	);
};

export default PostsTable;
