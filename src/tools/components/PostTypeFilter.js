/**
 * Post Type Filter Component
 *
 * @package RSFA
 */

import { __ } from '@wordpress/i18n';

const PostTypeFilter = ( {
	postTypes,
	selectedPostType,
	onChange,
	disabled = false,
} ) => {
	if ( ! postTypes || postTypes.length === 0 ) {
		return null;
	}

	return (
		<div className="rsfa-post-type-filter">
			<label htmlFor="rsfa-post-type-select">
				{ __( 'Post Type:', 'really-simple-featured-audio' ) }
			</label>
			<select
				id="rsfa-post-type-select"
				value={ selectedPostType }
				disabled={ disabled }
				onChange={ ( e ) => onChange( e.target.value ) }
			>
				{ postTypes.map( ( type ) => (
					<option key={ type.value } value={ type.value }>
						{ type.label }
					</option>
				) ) }
			</select>
		</div>
	);
};

export default PostTypeFilter;
