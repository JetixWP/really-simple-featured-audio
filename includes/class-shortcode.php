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
			return esc_html__( 'Please add a post id!', 'really-simple-featured-audio' );
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

		if ( ! empty( $post_types ) ) {
			if ( in_array( $post_type, $post_types, true ) ) {
				$post        = get_post( $post_id );
				$post_title  = $post instanceof \WP_Post ? esc_html( $post->post_title ) : '';
				$post_author = $post instanceof \WP_Post ? esc_html( get_the_author_meta( 'display_name', $post->post_author ) ) : '';

				// Prepare mark up attributes.
				$_autoplay = $is_autoplay ? 'autoplay playsinline' : '';
				$_loop     = $is_loop ? 'loop' : '';
				$_muted    = $is_muted ? 'muted' : '';

				if ( 'self' === $audio_source ) {
					$audio_id  = get_post_meta( $post_id, RSFA_META_KEY, true );
					$audio_url = wp_get_attachment_url( $audio_id );

					if ( $audio_url ) {
						$jwp_player_html = FrontEnd::render_jwp_player( $post_id, $audio_url, $post_title, $post_author, $is_autoplay, $is_loop, $is_muted );
						return $jwp_player_html . '<div id="rsfa-id-' . esc_attr( $post_id ) . '" class="rsfa-audio-wrapper"><audio class="rsfa-audio" id="rsfa-audio-' . esc_attr( $post_id ) . '" src="' . esc_url( $audio_url ) . '" style="max-width:100%;display:block;" ' . "{$_autoplay} {$_loop} {$_muted}" . '></audio></div>';
					}
				}

				// Get the meta value for audio url.
				$input_url = esc_url( get_post_meta( $post_id, RSFA_EMBED_META_KEY, true ) );

				// Generate audio embed URL.
				$embed_url = Plugin::get_instance()->frontend_provider->generate_embed_url( $input_url );

				if ( $embed_url ) {
					$jwp_player_html = FrontEnd::render_jwp_player( $post_id, $embed_url, $post_title, $post_author, $is_autoplay, $is_loop, $is_muted );
					return $jwp_player_html . '<div id="rsfa-id-' . esc_attr( $post_id ) . '" class="rsfa-audio-wrapper"><audio class="rsfa-audio" id="rsfa-audio-' . esc_attr( $post_id ) . '" src="' . esc_url( $embed_url ) . '" ' . "{$_autoplay} {$_loop} {$_muted}" . '></audio></div>';
				}
			}
		}
	}
}
