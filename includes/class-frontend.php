<?php
/**
 * Frontend handler.
 *
 * @package RSFA
 */

namespace RSFA;

use function RSFA\Settings\get_post_types;

/**
 * Class FrontEnd
 *
 * @package RSFA
 */
class FrontEnd {
	/**
	 * Class instance.
	 *
	 * @var $instance
	 */
	protected static $instance;

	/**
	 * Front_End constructor.
	 */
	public function __construct() {
		$this->get_posts_hooks();
	}

	/**
	 * Get a class instance.
	 *
	 * @return FrontEnd
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Get posts hooks.
	 *
	 * @return void
	 */
	public function get_posts_hooks() {
		add_filter( 'post_thumbnail_html', array( $this, 'get_post_audio' ), 10, 5 );
		add_filter( 'wp_kses_allowed_html', array( $this, 'update_wp_kses_allowed_html' ), 10, 2 );
	}

	/**
	 * Filter method for getting audio markup at posts & pages.
	 *
	 * @param string $html Holds markup data.
	 * @param int    $post_id Post ID.
	 * @param int    $post_thumbnail_id Thumbnail ID.
	 * @param int    $size Requested image size.
	 * @param string $attr Query string or array of attributes.
	 *
	 * @return string
	 */
	public function get_post_audio( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
		global $post;

		if ( 'object' !== gettype( $post ) ) {
			return $html;
		}

		return self::get_featured_audio_markup( $post->ID, $html );
	}

	/**
	 * Get featured audio markup.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $markup Holds markup data.
	 *
	 * @return string
	 */
	public static function get_featured_audio_markup( $post_id, $markup = '' ) {

		// Exit early if no post id is provided.
		if ( ! $post_id ) {
			return $markup;
		}

		$post = get_post( $post_id );

		if ( 'object' !== gettype( $post ) ) {
			return $markup;
		}

		// Get enabled post types.
		$post_types = get_post_types();

		if ( ! empty( $post_types ) ) {
			if ( in_array( $post->post_type, $post_types, true ) ) {
				// Get the meta value of audio embed url.
				$audio_source = get_post_meta( $post->ID, RSFA_SOURCE_META_KEY, true );
				$audio_source = $audio_source ? $audio_source : 'self';

				if ( 'self' === $audio_source ) {
					// Get the meta value of audio attachment.
					$audio_id = get_post_meta( $post->ID, RSFA_META_KEY, true );

					if ( $audio_id ) {
						return '<div style="clear:both">' . do_shortcode( '[rsfa]' ) . '</div>';
					}
				} else {
					// Get the meta value of audio embed url.
					$embed_url = get_post_meta( $post_id, RSFA_EMBED_META_KEY, true );

					if ( $embed_url ) {
						return '<div style="clear:both">' . do_shortcode( '[rsfa]' ) . '</div>';
					}
				}
			}
		}

		return $markup;
	}

	/**
	 * Method for checking if the post has a featured audio set.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return bool
	 */
	public static function has_featured_audio( $post_id ) {

		// Exit early if no post id is provided.
		if ( empty( $post_id ) ) {
			return false;
		}

		$post = get_post( $post_id );

		if ( 'object' !== gettype( $post ) ) {
			return false;
		}

		// Get enabled post types.
		$post_types = get_post_types();

		if ( ! empty( $post_types ) ) {
			if ( in_array( $post->post_type, $post_types, true ) ) {

				// Get the meta value of audio embed url.
				$audio_source = get_post_meta( $post->ID, RSFA_SOURCE_META_KEY, true );
				$audio_source = $audio_source ? $audio_source : 'self';

				if ( 'self' === $audio_source ) {
					// Get the meta value of audio attachment.
					$audio_id = get_post_meta( $post->ID, RSFA_META_KEY, true );

					if ( $audio_id ) {
						return true;
					}
				} else {
					// Get the meta value of audio embed url.
					$embed_url = get_post_meta( $post_id, RSFA_EMBED_META_KEY, true );

					if ( $embed_url ) {
						return true;
					}
				}
			}
		}
		return false;
	}

	/**
	 * Parses embed data via URL.
	 *
	 * @param string $url Audio URL.
	 *
	 * @return array|string
	 */
	public function parse_embed_url( $url ) {

		if ( empty( $url ) ) {
			return $url;
		}

		$parsed = wp_parse_url( esc_url( $url ) );

		switch ( $parsed['host'] ) {
			case 'www.youtube.com':
			case 'youtube.com':
			case 'youtu.be':
				$pattern = '/(?:youtube(?:-nocookie)?\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|vi|e(?:mbed)?)\/|\S*?[?&]v=|\S*?[?&]vi=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/';

				$result = preg_match( $pattern, $url, $matches );

				if ( false !== $result ) {
					$id = $matches[1];
				} else {
					$id = false;
				}

				return array(
					'host' => 'youtube',
					'id'   => $id,
				);

			case 'vimeo.com':
			case 'player.vimeo.com':
				$pattern = '/\/\/(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/(?:[^\/]*)\/audios\/|album\/(?:\d+)\/audio\/|audio\/|)(\d+)(?:[a-zA-Z0-9_\-]+)?/i';

				$result = preg_match(
					$pattern,
					$url,
					$matches
				);

				if ( false !== $result ) {
					$id = $matches[1];
				} else {
					$id = false;
				}

				return array(
					'host' => 'vimeo',
					'id'   => $id,
				);

			case 'dailymotion.com':
			case 'www.dailymotion.com':
			case 'dai.ly':
				$pattern = '/^(?:(?:https?):)?(?:\/\/)?(?:www\.)?(?:(?:dailymotion\.com(?:\/embed|\/hub)?\/audio)|dai\.ly)\/([a-zA-Z0-9]+)(?:_[\w_-]+)?$/';

				$result = preg_match(
					$pattern,
					$url,
					$matches
				);

				if ( $result ) {
					$id = $matches[1];
				} else {
					$id = false;
				}

				return array(
					'host' => 'dailymotion',
					'id'   => $id,
				);

			default:
				return $url;
		}
	}

	/**
	 * Generate an embed URL.
	 *
	 * @param string $url Audio URL.
	 *
	 * @return string
	 */
	public function generate_embed_url( $url ) {
		$embed_data = $this->parse_embed_url( $url );

		if ( is_array( $embed_data ) && isset( $embed_data['host'] ) && 'youtube' === $embed_data['host'] ) {
			$embed_url = 'https://www.youtube.com/embed/' . $embed_data['id'];
		} elseif ( is_array( $embed_data ) && isset( $embed_data['host'] ) && 'vimeo' === $embed_data['host'] ) {
			$embed_url = 'https://player.vimeo.com/audio/' . $embed_data['id'];
		} elseif ( is_array( $embed_data ) && isset( $embed_data['host'] ) && 'dailymotion' === $embed_data['host'] ) {
			$embed_url = 'https://www.dailymotion.com/embed/audio/' . $embed_data['id'];
		} else {
			$embed_url = $url;
		}

		return $embed_url;
	}

	/**
	 * Get allowed HTML elements.
	 *
	 * @return array List of elements.
	 */
	public function get_allowed_html() {
		return apply_filters(
			'rsfa_allowed_html',
			array(
				'audio'  => array(
					'id'                   => array(),
					'class'                => array(),
					'src'                  => array(),
					'style'                => array(),
					'loop'                 => array(),
					'muted'                => array(),
					'controls'             => array(),
					'autopictureinpicture' => array(),
					'autoplay'             => array(),
					'playsinline'          => array(),
				),
				'iframe' => array(
					'id'              => array(),
					'class'           => array(),
					'src'             => array(),
					'width'           => array(),
					'style'           => array(),
					'height'          => array(),
					'frameborder'     => array(),
					'allowfullscreen' => array(),
				),
				'div'    => array(
					'class'             => array(),
					'id'                => array(),
					'data-thumb'        => array(),
					'style'             => array(),
					'data-slide-number' => array(),
				),
				'img'    => array(
					'src'       => array(),
					'alt'       => array(),
					'class'     => array(),
					'draggable' => array(),
					'width'     => array(),
					'height'    => array(),
				),
				'a'      => array(
					'href'  => array(),
					'class' => array(),
					'style' => array(),
				),
				'p'      => array(),
				'span'   => array(),
				'br'     => array(),
				'i'      => array(),
				'strong' => array(),
			)
		);
	}

	/**
	 * Generate dynamic CSS.
	 *
	 * @return string
	 */
	public function generate_dynamic_css() {
		$css = '';

		return apply_filters( 'rsfa_generated_dynamic_css', $css );
	}

	/**
	 * Override wp_kses_post allowed html array to make way for audios.
	 *
	 * @param array  $allowed_html List of html tags.
	 * @param string $context Key of current context.
	 *
	 * @return array
	 */
	public function update_wp_kses_allowed_html( $allowed_html, $context ) {

		// Keep only for 'post' contenxt.
		if ( 'post' === $context ) {
			$additional_html = $this->get_allowed_html();

			return array_merge( $allowed_html, $additional_html );
		}

		return $allowed_html;
	}
}
