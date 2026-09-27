<?php
/**
 * Shortcode handler.
 *
 * @package RSFA
 */

namespace RSFA;

use function RSFA\Settings\get_post_types;

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
		$post = get_post();

		if ( ! $post instanceof \WP_Post ) {
			return '';
		}

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
		if ( ! is_array( $atts ) || empty( $atts['post_id'] ) ) {
			return esc_html__( 'Please add a post id!', 'really-simple-featured-audio' );
		}

		$post = get_post( absint( $atts['post_id'] ) );

		if ( ! $post instanceof \WP_Post ) {
			return '';
		}

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

		if ( empty( $post_types ) || ! in_array( $post_type, $post_types, true ) ) {
			return '';
		}

		$audio = FrontEnd::get_audio_source_url( $post_id );

		if ( ! $audio['url'] ) {
			return '';
		}

		$controls = FrontEnd::get_player_controls( $audio['source'] );
		$config   = FrontEnd::get_player_config( $post_id, $audio['url'], $audio['source'] );

		// Fallback player for when JavaScript is off; the JWP player replaces it.
		$fallback = sprintf(
			'<audio class="rsfa-audio" id="rsfa-audio-%1$s" src="%2$s" style="max-width:100%%;display:block;" controls preload="none"%3$s%4$s></audio>',
			esc_attr( $post_id ),
			esc_url( $audio['url'] ),
			$controls['loop'] ? ' loop' : '',
			$controls['muted'] ? ' muted' : ''
		);

		$html = '<div id="rsfa-id-' . esc_attr( $post_id ) . '" class="rsfa-audio-wrapper"' . FrontEnd::get_player_attribute( $config ) . '>' . $fallback . '</div>';

		/**
		 * Filters the shortcode audio player markup.
		 *
		 * @since 1.6.0
		 *
		 * @param string $html Player markup.
		 * @param int    $post_id Post ID.
		 * @param array  $config Player config.
		 */
		return apply_filters( 'rsfa_shortcode_audio_output', $html, $post_id, $config );
	}
}
