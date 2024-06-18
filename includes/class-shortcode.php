<?php
/**
 * Shortcode handler.
 *
 * @package RSFA
 */

namespace RSFA;

use function RSFA\Settings\get_post_types;
use function RSFA\Settings\get_audio_controls;

/**
 * Class Shortcode
 */
class Shortcode {
	/**
	 * Class instance.
	 *
	 * @var $instance
	 */
	protected static $instance;

	/**
	 * Shortcode constructor.
	 */
	public function __construct() {
		// Shortcode to display the audio on pages, or posts.
		add_shortcode( 'rsfa', array( $this, 'show_audio' ) );

		// Shortcode to display using post id.
		add_shortcode( 'rsfa_by_postid', array( $this, 'show_audio_by_post_id' ) );
	}

	/**
	 * Get an instance of class.
	 *
	 * @return Shortcode
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Show audio on posts & pages.
	 *
	 * @return string
	 */
	public function show_audio() {
		global $post;

		return $this->get_audio_markup( $post->ID, $post->post_type );
	}

	/**
	 * Show audio by post id.
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string
	 */
	public function show_audio_by_post_id( $atts ) {
		if ( is_array( $atts ) && ! isset( $atts['post_id'] ) ) {
			return esc_html__( 'Please add a post id!', 'rsfa' );
		}

		$post = get_post( $atts['post_id'] );

		return $this->get_audio_markup( $post->ID, $post->post_type );
	}

	/**
	 * Creates audio markup for showing at frontend.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $post_type Post type.
	 *
	 * @return string
	 */
	public function get_audio_markup( $post_id, $post_type ) {
		// Get enabled post types.
		$post_types = get_post_types();

		// Get the meta value of audio embed url.
		$audio_source = get_post_meta( $post_id, RSFA_SOURCE_META_KEY, true );
		$audio_source = $audio_source ? $audio_source : 'self';

		$audio_controls = 'self' !== $audio_source ? get_audio_controls( 'embed' ) : get_audio_controls();

		// Get autoplay option.
		$is_autoplay = ( is_array( $audio_controls ) && isset( $audio_controls['autoplay'] ) ) && $audio_controls['autoplay'];

		// Get loop option.
		$is_loop = ( is_array( $audio_controls ) && isset( $audio_controls['loop'] ) ) && $audio_controls['loop'];

		// Get mute option.
		$is_muted = ( is_array( $audio_controls ) && isset( $audio_controls['mute'] ) ) && $audio_controls['mute'];

		// Get PictureInPicture option.
		$is_pip = ( is_array( $audio_controls ) && isset( $audio_controls['pip'] ) ) && $audio_controls['pip'];

		// Get audio controls option.
		$has_controls = ( is_array( $audio_controls ) && isset( $audio_controls['controls'] ) ) && $audio_controls['controls'];

		if ( ! empty( $post_types ) ) {
			if ( in_array( $post_type, $post_types, true ) ) {

				if ( 'self' === $audio_source ) {
					$audio_id  = get_post_meta( $post_id, RSFA_META_KEY, true );
					$audio_url = wp_get_attachment_url( $audio_id );

					// Prepare mark up attributes.
					$is_autoplay  = $is_autoplay ? 'autoplay playsinline' : '';
					$is_loop      = $is_loop ? 'loop' : '';
					$is_muted     = $is_muted ? 'muted' : '';
					$is_pip       = $is_pip ? 'autopictureinpicture' : '';
					$has_controls = $has_controls ? 'controls' : '';

					if ( $audio_url ) {
						return '<audio class="rsfa-audio" id="rsfa-audio-' . esc_attr( $post_id ) . '" src="' . esc_url( $audio_url ) . '" style="max-width:100%;display:block;" ' . esc_attr( $has_controls ) . ' ' . esc_attr( $is_autoplay ) . ' ' . esc_attr( $is_loop ) . ' ' . esc_attr( $is_muted ) . ' ' . esc_attr( $is_pip ) . '></audio>';
					}
				}

				// Get the meta value for audio url.
				$input_url = esc_url( get_post_meta( $post_id, RSFA_EMBED_META_KEY, true ) );

				// Generate audio embed URL.
				$embed_url = Plugin::get_instance()->frontend_provider->generate_embed_url( $input_url );

				// Prepare mark up attributes.
				$is_autoplay  = $is_autoplay ? 'autoplay=1&' : 'autoplay=0&';
				$is_loop      = $is_loop ? 'loop=1&' : '';
				$is_muted     = $is_muted ? 'mute=1&muted=1&' : '';
				$is_pip       = $is_pip ? 'picture-in-picture=1&' : '';
				$has_controls = $has_controls ? 'controls=1&' : 'controls=0&';

				if ( $embed_url ) {
					return '<div><iframe class="rsfa-audio" width="100%" height="540" src="' . esc_url( $embed_url . '?' . esc_attr( $has_controls ) . esc_attr( $is_autoplay ) . esc_attr( $is_loop ) . esc_attr( $is_muted ) . esc_attr( $is_pip ) ) . '" allow="" frameborder="0"></iframe></div>';
				}
			}
		}
	}

}
