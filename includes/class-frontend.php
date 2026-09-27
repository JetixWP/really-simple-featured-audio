<?php
/**
 * Frontend handler.
 *
 * @package RSFA
 */

namespace RSFA;

use RSFA\Options;
use function RSFA\Settings\get_post_types;
use function RSFA\Settings\get_audio_controls;

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
		// Register on init: block themes render the page body before wp_head,
		// and players enqueue their assets while rendering.
		add_action( 'init', array( $this, 'register_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Registers the audio player assets.
	 *
	 * They are enqueued early on pages known to show a featured audio, and
	 * otherwise by the markup builders while a player renders (shortcodes,
	 * loops, widgets).
	 *
	 * @since 1.6.0
	 *
	 * @return void
	 */
	public function register_assets() {
		if ( wp_script_is( 'rsfa-player', 'registered' ) ) {
			return;
		}

		// Register Audio Player.
		wp_register_style( 'rsfa-audio-player', RSFA_PLUGIN_URL . 'assets/css/jwp-audio-player.css', array(), filemtime( RSFA_PLUGIN_DIR . 'assets/css/jwp-audio-player.css' ) );
		wp_register_script( 'rsfa-audio-player', RSFA_PLUGIN_URL . 'assets/js/jwp-audio-player.min.js', array(), RSFA_VERSION, true );
		wp_register_script( 'rsfa-player', RSFA_PLUGIN_URL . 'assets/js/rsfa-player.js', array( 'rsfa-audio-player' ), filemtime( RSFA_PLUGIN_DIR . 'assets/js/rsfa-player.js' ), true );

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
	}

	/**
	 * Enqueue the player assets early on pages known to show a featured audio.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		$load_early = is_singular() && self::has_featured_audio( get_queried_object_id() );

		/**
		 * Filters whether the audio player assets load in the page head.
		 *
		 * They are enqueued anyway wherever a player is rendered; loading them
		 * early only avoids a late stylesheet on pages that show audio.
		 *
		 * @since 1.6.0
		 *
		 * @param bool $load_early Whether to enqueue now.
		 */
		if ( apply_filters( 'rsfa_load_player_assets_early', $load_early ) ) {
			self::enqueue_player_assets();
		}
	}

	/**
	 * Enqueue the audio player assets.
	 *
	 * Safe to call more than once, and from inside the page body.
	 *
	 * @since 1.6.0
	 *
	 * @return void
	 */
	public static function enqueue_player_assets() {
		if ( ! wp_script_is( 'rsfa-player', 'registered' ) ) {
			if ( ! did_action( 'init' ) ) {
				return;
			}

			self::get_instance()->register_assets();
		}

		wp_enqueue_style( 'rsfa-audio-player' );
		wp_enqueue_script( 'rsfa-player' );
	}

	/**
	 * Get the audio URL and source for a post.
	 *
	 * @since 1.6.0
	 *
	 * @param int  $post_id Post ID.
	 * @param bool $fallback_to_embed Whether a self source without a file falls back to the embed URL.
	 *
	 * @return array{url: string, source: string} Empty URL when no audio is set.
	 */
	public static function get_audio_source_url( $post_id, $fallback_to_embed = true ) {
		$source = get_post_meta( $post_id, RSFA_SOURCE_META_KEY, true );
		$source = $source ? $source : 'self';
		$url    = '';

		if ( 'self' === $source ) {
			$audio_id = get_post_meta( $post_id, RSFA_META_KEY, true );
			$url      = $audio_id ? wp_get_attachment_url( $audio_id ) : '';
		}

		if ( ! $url && ( 'self' !== $source || $fallback_to_embed ) ) {
			$input_url = esc_url_raw( (string) get_post_meta( $post_id, RSFA_EMBED_META_KEY, true ) );
			$url       = Plugin::get_instance()->frontend_provider->generate_embed_url( $input_url );
		}

		$audio = array(
			'url'    => $url ? esc_url_raw( $url ) : '',
			'source' => $source,
		);

		/**
		 * Filters the audio URL and source used for a post's player.
		 *
		 * @since 1.6.0
		 *
		 * @param array $audio   Array with `url` and `source` keys. Empty URL means no audio.
		 * @param int   $post_id Post ID.
		 */
		$audio = apply_filters( 'rsfa_audio_source_url', $audio, $post_id );

		return array(
			'url'    => ! empty( $audio['url'] ) ? esc_url_raw( $audio['url'] ) : '',
			'source' => ( isset( $audio['source'] ) && 'embed' === $audio['source'] ) ? 'embed' : 'self',
		);
	}

	/**
	 * Get the player settings from the Controls tab for an audio source.
	 *
	 * @since 1.6.0
	 *
	 * @param string $source Audio source, `self` or `embed`.
	 *
	 * @return array{autoplay: bool, loop: bool, muted: bool, download: bool}
	 */
	public static function get_player_controls( $source = 'self' ) {
		$controls = 'self' !== $source ? get_audio_controls( 'embed' ) : get_audio_controls();
		$controls = is_array( $controls ) ? $controls : array();

		return array(
			'autoplay' => ! empty( $controls['autoplay'] ),
			'loop'     => ! empty( $controls['loop'] ),
			'muted'    => ! empty( $controls['mute'] ),
			'download' => ! empty( $controls['download'] ),
		);
	}

	/**
	 * Build the JWP player config for a post's audio.
	 *
	 * Title and artist are plain text with all tags stripped: the player writes them
	 * with innerHTML and also copies them into a data attribute for its marquee,
	 * where HTML entities would show literally.
	 *
	 * @since 1.6.0
	 *
	 * @param int    $post_id Post ID.
	 * @param string $audio_url Audio URL.
	 * @param string $source Audio source, `self` or `embed`.
	 *
	 * @return array
	 */
	public static function get_player_config( $post_id, $audio_url, $source = 'self' ) {
		$post     = get_post( $post_id );
		$controls = self::get_player_controls( $source );

		$audio = array(
			'title'  => $post instanceof \WP_Post ? self::player_text( $post->post_title ) : '',
			'artist' => $post instanceof \WP_Post ? self::player_text( get_the_author_meta( 'display_name', $post->post_author ) ) : '',
			'src'    => esc_url_raw( $audio_url ),
		);

		$cover_url = self::get_cover_url( $post_id );

		if ( $cover_url ) {
			$audio['cover'] = $cover_url;
		}

		$config = array(
			'autoPlay' => $controls['autoplay'],
			'loop'     => $controls['loop'],
			'muted'    => $controls['muted'],
			'audio'    => $audio,
			// Read by the plugin's own scripts; the player ignores it.
			'rsfa'     => array(
				'postId' => (int) $post_id,
				'source' => 'embed' === $source ? 'embed' : 'self',
			),
		);

		if ( $controls['download'] ) {
			$config['download'] = true;
		}

		/**
		 * Filters the JWP Audio Player config for a post's audio.
		 *
		 * @since 1.6.0
		 *
		 * @param array  $config Player config.
		 * @param int    $post_id Post ID.
		 * @param string $source Audio source, `self` or `embed`.
		 */
		return apply_filters( 'rsfa_player_config', $config, $post_id, $source );
	}

	/**
	 * Get the cover image URL set for a post's audio.
	 *
	 * @since 1.6.0
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return string Empty when no cover is set, so the player's default cover shows.
	 */
	public static function get_cover_url( $post_id ) {
		$cover_id  = absint( get_post_meta( $post_id, RSFA_COVER_META_KEY, true ) );
		$cover_url = $cover_id ? wp_get_attachment_image_url( $cover_id, 'medium' ) : '';

		/**
		 * Filters the cover image URL for a post's audio.
		 *
		 * @since 1.6.0
		 *
		 * @param string $cover_url Cover image URL, empty for the default cover.
		 * @param int    $post_id Post ID.
		 */
		return (string) apply_filters( 'rsfa_audio_cover_url', $cover_url ? esc_url_raw( $cover_url ) : '', $post_id );
	}

	/**
	 * Turn a title or name into plain text the player can show.
	 *
	 * @since 1.6.0
	 *
	 * @param string $text Text that may contain entities or tags.
	 *
	 * @return string
	 */
	public static function player_text( $text ) {
		$text = html_entity_decode( (string) $text, ENT_QUOTES | ENT_HTML5, get_bloginfo( 'charset' ) );

		return trim( wp_strip_all_tags( $text ) );
	}

	/**
	 * Get the `data-rsfa-player` attribute for a player config.
	 *
	 * Also enqueues the player assets, since markup with this attribute is about to be printed.
	 *
	 * @since 1.6.0
	 *
	 * @param array $config Player config.
	 *
	 * @return string Attribute with a leading space.
	 */
	public static function get_player_attribute( $config ) {
		self::enqueue_player_assets();

		// Hex-encode quotes, ampersands and angle brackets so entities in the values survive the attribute round trip.
		$json = wp_json_encode( $config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT );

		return ' data-rsfa-player="' . esc_attr( $json ) . '"';
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

		$options                  = Options::get_instance();
		$blog_archives_visibility = $options->get( 'blog_archives_visibility' );
		$blog_single_visibility   = $options->get( 'blog_single_visibility' );

		if ( ( ( is_home() || is_archive() ) && ( $options->has( 'blog_archives_visibility' ) && ! $blog_archives_visibility ) ) ) {
			return $html;
		}

		if ( ( ( is_single() ) && ( $options->has( 'blog_single_visibility' ) && ! $blog_single_visibility ) ) ) {
			return $html;
		}

		// Use the post the thumbnail belongs to; themes also print thumbnails of
		// other posts (next/previous links, related posts) on single pages.
		$post_id = $post_id ? $post_id : ( is_object( $post ) ? $post->ID : 0 );

		if ( ! $post_id ) {
			return $html;
		}

		return self::get_featured_audio_markup( $post_id, $html );
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

		if ( ! self::has_featured_audio( $post->ID ) ) {
			return $markup;
		}

		$player = Plugin::get_instance()->shortcode_provider->get_audio_markup( $post->ID, $post->post_type );

		if ( ! $player ) {
			return $markup;
		}

		return '<div class="rsfa-shortcode-wrapper" style="clear:both">' . $player . '</div>';
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

		/**
		 * Filters whether a post has a featured audio.
		 *
		 * @since 1.6.0
		 *
		 * @param bool $has_audio Whether the post has a featured audio.
		 * @param int  $post_id   Post ID.
		 */
		return (bool) apply_filters( 'rsfa_has_featured_audio', self::has_saved_featured_audio( $post_id ), $post_id );
	}

	/**
	 * Whether the post has a featured audio saved in its own meta.
	 *
	 * @since 1.6.0
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return bool
	 */
	protected static function has_saved_featured_audio( $post_id ) {

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

		// Clean for storage and reuse; escape again at output.
		return esc_url_raw( $url );
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
	 * Generate dynamic CSS.
	 *
	 * @return string
	 */
	public function generate_dynamic_css() {
		$css = '';

		return apply_filters( 'rsfa_generated_dynamic_css', $css );
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
					'preload'     => array(),
				),
				'div'    => array(
					'class'             => array(),
					'id'                => array(),
					'data-thumb'        => array(),
					'style'             => array(),
					'data-slide-number' => array(),
					'data-rsfa-player'  => array(),
					'data-*'            => true,
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
	 * @deprecated 1.6.0 Players now mount from a `data-rsfa-player` attribute,
	 *             see get_player_config() and get_player_attribute(). Kept for
	 *             third-party code that still calls it.
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

		self::enqueue_player_assets();

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
