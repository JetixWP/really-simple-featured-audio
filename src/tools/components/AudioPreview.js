/**
 * Audio Preview Component
 *
 * Small player for posts with featured audios.
 *
 * @package RSFA
 */

const AudioPreview = ( { post } ) => {
	const audioSource = post.audio_source || '';

	let src = '';

	if ( audioSource === 'self' && post.audio_id ) {
		src = post.audio_url || '';
	} else if ( audioSource === 'embed' ) {
		src = post.embed_url || '';
	}

	if ( ! src ) {
		return <span className="rsfa-no-audio">—</span>;
	}

	return (
		<div className="rsfa-audio-preview">
			{ post.cover_url && (
				<img
					className="rsfa-audio-preview-cover"
					src={ post.cover_url }
					alt=""
				/>
			) }
			<audio src={ src } controls preload="none" />
		</div>
	);
};

export default AudioPreview;
