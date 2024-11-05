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
		// add_filter( 'wp_kses_allowed_html', array( $this, 'update_wp_kses_allowed_html' ), 10, 2 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueues scripts required for media uploader.
	 *
	 * @retun void
	 */
	public function enqueue_scripts() {
		// Register Audio Player.
		wp_register_style( 'rsfa-audio-player', RSFA_PLUGIN_URL . 'assets/css/jwp-audio-player.css', array(), filemtime( RSFA_PLUGIN_DIR . 'assets/css/jwp-audio-player.css' ) );
		wp_register_script( 'rsfa-audio-player', RSFA_PLUGIN_URL . 'assets/js/jwp-audio-player.min.js', array( 'jquery' ), RSFA_VERSION, true );

		// Enqueue Audio Player.
		wp_enqueue_style( 'rsfa-audio-player' );
		wp_enqueue_script( 'rsfa-audio-player' );

		$cover_url      = RSFA_PLUGIN_URL . 'assets/images/audio_frame.png';
		$cover_dark_url = RSFA_PLUGIN_URL . 'assets/images/audio_dark_frame.png';

		// Add light and dark mode default player cover.
		wp_add_inline_style(
			'rsfa-audio-player',
			"
            .jwp {
                --cover-img-url: url('$cover_url');
            }

            /* Dark mode. */
            .jwp[data-theme=\"dark\"] {
                --cover-img-url: url('$cover_dark_url');
            }
            @media (prefers-color-scheme: dark) {
                .jwp[data-theme=\"auto\"] {
                    --cover-img-url: url('$cover_dark_url');
                }
            }
        "
		);

		// To prevent redirects on featured anchors at loop.
		wp_add_inline_script(
			'rsfa-audio-player',
			"jQuery(document).ready(function($) {
            $(
            '.rsfa-has-audio > figure.wp-block-post-featured-image > a'
            ).on('click', function(event) {
                 if (event.target !== this) {
                   event.preventDefault(); // for preventing anchor tag default functionality.
                   return;
                }
            });
        });"
		);
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
						return '<div  style="clear:both">' . do_shortcode( '[rsfa]' ) . '</div>';
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

		// Maybe some regex processing here.
		$escaped_url = esc_url( $url );

		return $escaped_url;
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

		// Do some post-processing here.
		if ( ! empty( $embed_data ) ) {
			$embed_url = $embed_data;
		} else {
			$embed_url = '';
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
					'id'          => array(),
					'class'       => array(),
					'src'         => array(),
					'style'       => array(),
					'loop'        => array(),
					'muted'       => array(),
					'controls'    => array(),
					'autoplay'    => array(),
					'playsinline' => array(),
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
	 * Override wp_kses_post allowed html array to make way for audios.
	 *
	 * @param array  $allowed_html List of html tags.
	 * @param string $context Key of current context.
	 *
	 * @return array
	 */
	public function update_wp_kses_allowed_html( $allowed_html, $context ) {

		// Keep only for 'post' context.
		if ( 'post' === $context ) {
			$additional_html = $this->get_allowed_html();

			$updated_tags = $allowed_html;

			// This fixes overriding existing html tag attributes data.
			foreach ( $additional_html as $tag => $attrs ) {
				if ( in_array( $tag, array_keys( $allowed_html ), true ) ) {
					if ( ! empty( $attrs ) ) {
						$ex_attrs = $allowed_html[ $tag ];

						if ( ! empty( $ex_attrs ) ) {
							foreach ( $attrs as $attr => $value ) {
								if ( ! empty( $ex_attrs[ $attr ] ) ) {
									$updated_tags[ $tag ][ $attr ] = $ex_attrs[ $attr ];
								} else {
									$updated_tags[ $tag ][ $attr ] = $value;
								}
							}
						} else {
							$updated_tags[ $tag ] = $attrs;
						}
					} else {
						$updated_tags[ $tag ] = $allowed_html[ $tag ];
					}

					continue;
				}

				$updated_tags[ $tag ] = $attrs;
			}

			return $updated_tags;
		}

		return $allowed_html;
	}

	/**
	 * Renders JWP Player with data.
	 *
	 * @param int    $id Post id.
	 * @param string $audio_url Audio URL.
	 * @param string $title Audio Title.
	 * @param string $artist Artist Name.
	 * @param bool   $autoplay To autoplay.
	 * @param bool   $loop To loop.
	 * @param bool   $muted To mute.
	 * @return string
	 */
	public static function render_jwp_player( $id, $audio_url, $title = '', $artist = '', $autoplay = false, $loop = false, $muted = false ) {
		$id        = esc_attr( $id );
		$audio_url = esc_url( $audio_url );
		$title     = esc_attr( $title );
		$artist    = esc_attr( $artist );
		$autoplay  = boolval( esc_attr( $autoplay ) );
		$loop      = boolval( esc_attr( $loop ) );
		$muted     = boolval( esc_attr( $muted ) );

		return "<script>
                            document.addEventListener(\"DOMContentLoaded\", function() {
                               const allSelectorsFound = document.querySelectorAll('#rsfa-id-{$id}');
                        
                               if (allSelectorsFound.length) {
                                   allSelectorsFound.forEach(function(selector) {
                                       window.JWP_Audio_Player_Instance = new JWP_Audio_Player.Player({
                                          container: selector,
                                          autoPlay: " . wp_json_encode( $autoplay ) . ',
                                          loop: ' . wp_json_encode( $loop ) . ',
                                          muted: ' . wp_json_encode( $muted ) . ",
                                          audio: {
                                            title: '$title',
                                            artist: '$artist',
                                            src: '$audio_url',
                                          }
                                        })
                                     })
                               }
                            });
</script>";
	}
}
