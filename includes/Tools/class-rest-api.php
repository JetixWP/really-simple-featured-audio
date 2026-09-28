<?php
/**
 * REST API Handler for Tools.
 *
 * @package RSFA
 */

namespace RSFA\Tools;

use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use function RSFA\Settings\get_post_types;

defined( 'ABSPATH' ) || exit;

/**
 * REST_API Class.
 */
class REST_API {
	/**
	 * API Namespace.
	 *
	 * @var string
	 */
	const NAMESPACE = 'rsfa/v1';

	/**
	 * Class instance.
	 *
	 * @var REST_API
	 */
	protected static $instance;

	/**
	 * Get class instance.
	 *
	 * @return REST_API
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register REST API routes.
	 */
	public function register_routes() {
		register_rest_route(
			self::NAMESPACE,
			'/posts',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_posts' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => array(
					'post_type' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'page'      => array(
						'required'          => false,
						'type'              => 'integer',
						'default'           => 1,
						'sanitize_callback' => 'absint',
					),
					'per_page'  => array(
						'required'          => false,
						'type'              => 'integer',
						'default'           => 20,
						'sanitize_callback' => 'absint',
					),
					'search'    => array(
						'required'          => false,
						'type'              => 'string',
						'default'           => '',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/posts/update-source',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'update_audio_source' ),
				'permission_callback' => array( $this, 'check_edit_post_permission' ),
				'args'                => array(
					'post_id'      => array(
						'required'          => true,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					),
					'audio_source' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/posts/update-audio',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'update_audio' ),
				'permission_callback' => array( $this, 'check_edit_post_permission' ),
				'args'                => array(
					'post_id'      => array(
						'required'          => true,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					),
					'audio_source' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'audio_id'     => array(
						'required'          => false,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					),
					'embed_url'    => array(
						'required' => false,
						'type'     => 'string',
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/posts/update-cover',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'update_cover' ),
				'permission_callback' => array( $this, 'check_edit_post_permission' ),
				'args'                => array(
					'post_id'   => array(
						'required'          => true,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					),
					'cover_id' => array(
						'required'          => false,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/posts/update-thumbnail',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'update_thumbnail' ),
				'permission_callback' => array( $this, 'check_edit_post_permission' ),
				'args'                => array(
					'post_id'      => array(
						'required'          => true,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					),
					'thumbnail_id' => array(
						'required'          => false,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		/**
		 * Fires after RSFA Tools REST routes are registered.
		 *
		 * Use this hook to register additional REST routes for the Tools page.
		 *
		 * @since 1.6.0
		 *
		 * @param REST_API $this REST_API instance.
		 */
		do_action( 'rsfa_tools_register_routes', $this );
	}

	/**
	 * Check if user has permission.
	 *
	 * @return bool|WP_Error
	 */
	public function check_permission() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'You do not have permission to access this endpoint.', 'really-simple-featured-audio' ),
				array( 'status' => 403 )
			);
		}

		return true;
	}

	/**
	 * Check if user can edit the target post.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return bool|WP_Error
	 */
	public function check_edit_post_permission( $request ) {
		$permission = $this->check_permission();

		if ( is_wp_error( $permission ) ) {
			return $permission;
		}

		$post_id = absint( $request->get_param( 'post_id' ) );

		if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) || ! in_array( get_post_type( $post_id ), get_post_types(), true ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'You do not have permission to edit this post.', 'really-simple-featured-audio' ),
				array( 'status' => 403 )
			);
		}

		return true;
	}

	/**
	 * Get posts with featured audio data.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_posts( WP_REST_Request $request ) {
		$post_type = $request->get_param( 'post_type' );
		$page      = $request->get_param( 'page' );
		$per_page  = $request->get_param( 'per_page' );

		// Validate post type is enabled in plugin settings.
		$enabled_types = get_post_types();

		if ( ! in_array( $post_type, $enabled_types, true ) ) {
			return new WP_Error(
				'invalid_post_type',
				__( 'The specified post type is not enabled for featured audios.', 'really-simple-featured-audio' ),
				array( 'status' => 400 )
			);
		}

		// Double-check post type exists in WordPress.
		if ( ! post_type_exists( $post_type ) ) {
			return new WP_Error(
				'invalid_post_type',
				__( 'Invalid post type.', 'really-simple-featured-audio' ),
				array( 'status' => 400 )
			);
		}

		// Query posts.
		$args = array(
			'post_type'      => sanitize_key( $post_type ),
			'post_status'    => 'any',
			'posts_per_page' => min( absint( $per_page ), 100 ), // Limit max per page to 100.
			'paged'          => absint( $page ),
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		// Add search if provided.
		$search = $request->get_param( 'search' );
		if ( ! empty( $search ) ) {
			$args['s'] = $search;
		}

		$query = new \WP_Query( $args );
		$posts = array();

		foreach ( $query->posts as $post ) {
			$posts[] = $this->prepare_post_data( $post );
		}

		$response = new WP_REST_Response( $posts, 200 );
		$response->header( 'X-WP-Total', $query->found_posts );
		$response->header( 'X-WP-TotalPages', $query->max_num_pages );

		return $response;
	}

	/**
	 * Prepare post data for response.
	 *
	 * @param \WP_Post $post Post object.
	 *
	 * @return array
	 */
	protected function prepare_post_data( $post ) {
		$audio_source = get_post_meta( $post->ID, RSFA_SOURCE_META_KEY, true );
		$audio_id     = get_post_meta( $post->ID, RSFA_META_KEY, true );
		$embed_url    = get_post_meta( $post->ID, RSFA_EMBED_META_KEY, true );
		$cover_id    = get_post_meta( $post->ID, RSFA_COVER_META_KEY, true );

		// Determine has_audio based on the selected audio source.
		$has_audio = false;
		if ( 'self' === $audio_source && ! empty( $audio_id ) ) {
			$has_audio = true;
		} elseif ( 'embed' === $audio_source && ! empty( $embed_url ) ) {
			$has_audio = true;
		}

		$thumbnail = get_the_post_thumbnail_url( $post->ID, 'thumbnail' );

		// Get audio URL for self-hosted audios.
		$audio_url = '';
		if ( $audio_id ) {
			$audio_url = wp_get_attachment_url( $audio_id );
		}

		// Get cover URL.
		$cover_url = '';
		if ( $cover_id ) {
			$cover_url = wp_get_attachment_url( $cover_id );
		}

		// Build edit link manually to avoid context issues.
		$edit_link = admin_url( 'post.php?post=' . $post->ID . '&action=edit' );

		$data = array(
			'id'           => absint( $post->ID ),
			'title'        => html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' ),
			'permalink'    => esc_url_raw( get_permalink( $post ) ),
			'edit_link'    => esc_url_raw( $edit_link ),
			'thumbnail'    => $thumbnail ? esc_url_raw( $thumbnail ) : '',
			'has_audio'    => (bool) $has_audio,
			'audio_source' => sanitize_key( $audio_source ),
			'audio_id'     => $audio_id ? absint( $audio_id ) : 0,
			'audio_url'    => $audio_url ? esc_url_raw( $audio_url ) : '',
			'embed_url'    => $embed_url ? esc_url_raw( $embed_url ) : '',
			'cover_id'    => $cover_id ? absint( $cover_id ) : 0,
			'cover_url'   => $cover_url ? esc_url_raw( $cover_url ) : '',
		);

		/**
		 * Filter post data returned by the Tools REST API.
		 *
		 * @since 1.6.0
		 *
		 * @param array    $data Post data array.
		 * @param \WP_Post $post Post object.
		 */
		return apply_filters( 'rsfa_tools_post_data', $data, $post );
	}

	/**
	 * Update audio source for a post.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_audio_source( WP_REST_Request $request ) {
		$post_id      = $request->get_param( 'post_id' );
		$audio_source = $request->get_param( 'audio_source' );

		$post = get_post( $post_id );

		if ( ! $post ) {
			return new WP_Error(
				'invalid_post',
				__( 'Post not found.', 'really-simple-featured-audio' ),
				array( 'status' => 404 )
			);
		}

		// Validate audio source.
		if ( ! in_array( $audio_source, array( 'self', 'embed', '' ), true ) ) {
			return new WP_Error(
				'invalid_source',
				__( 'Invalid audio source type.', 'really-simple-featured-audio' ),
				array( 'status' => 400 )
			);
		}

		if ( empty( $audio_source ) ) {
			delete_post_meta( $post_id, RSFA_SOURCE_META_KEY );
		} else {
			update_post_meta( $post_id, RSFA_SOURCE_META_KEY, sanitize_key( $audio_source ) );
		}

		return new WP_REST_Response(
			array(
				'success'      => true,
				'post_id'      => absint( $post_id ),
				'audio_source' => sanitize_key( $audio_source ),
			),
			200
		);
	}

	/**
	 * Update audio for a post.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_audio( WP_REST_Request $request ) {
		$post_id      = absint( $request->get_param( 'post_id' ) );
		$audio_source = sanitize_key( $request->get_param( 'audio_source' ) );
		$audio_id     = absint( $request->get_param( 'audio_id' ) );
		$raw_url      = trim( (string) $request->get_param( 'embed_url' ) );
		$embed_url    = esc_url_raw( $raw_url );

		// A link that sanitizes to nothing was unsafe; don't treat it as a removal.
		if ( '' !== $raw_url && '' === $embed_url ) {
			return new WP_Error(
				'invalid_url',
				__( 'Invalid audio link.', 'really-simple-featured-audio' ),
				array( 'status' => 400 )
			);
		}

		$post = get_post( $post_id );

		if ( ! $post ) {
			return new WP_Error(
				'invalid_post',
				__( 'Post not found.', 'really-simple-featured-audio' ),
				array( 'status' => 404 )
			);
		}

		// Validate audio source.
		if ( ! in_array( $audio_source, array( 'self', 'embed' ), true ) ) {
			return new WP_Error(
				'invalid_source',
				__( 'Invalid audio source type.', 'really-simple-featured-audio' ),
				array( 'status' => 400 )
			);
		}

		// Validate audio_id is a valid attachment.
		if ( 'self' === $audio_source && $audio_id ) {
			$attachment = get_post( $audio_id );
			if ( ! $attachment || 'attachment' !== $attachment->post_type ) {
				return new WP_Error(
					'invalid_audio',
					__( 'Invalid audio attachment.', 'really-simple-featured-audio' ),
					array( 'status' => 400 )
				);
			}

			// Verify it's an audio mime type.
			$mime_type = get_post_mime_type( $audio_id );
			if ( strpos( $mime_type, 'audio/' ) !== 0 ) {
				return new WP_Error(
					'invalid_mime_type',
					__( 'Selected file is not an audio.', 'really-simple-featured-audio' ),
					array( 'status' => 400 )
				);
			}
		}

		// Validate embed URL.
		if ( 'embed' === $audio_source && ! empty( $embed_url ) ) {
			// Use wp_http_validate_url for additional URL validation.
			if ( ! wp_http_validate_url( $embed_url ) ) {
				return new WP_Error(
					'invalid_url',
					__( 'Invalid audio link.', 'really-simple-featured-audio' ),
					array( 'status' => 400 )
				);
			}

			if ( ! in_array( strtolower( (string) wp_parse_url( $embed_url, PHP_URL_SCHEME ) ), array( 'http', 'https' ), true ) ) {
				return new WP_Error(
					'invalid_url',
					__( 'Audio links must start with http or https.', 'really-simple-featured-audio' ),
					array( 'status' => 400 )
				);
			}
		}

		// Update audio source.
		update_post_meta( $post_id, RSFA_SOURCE_META_KEY, sanitize_key( $audio_source ) );

		if ( 'self' === $audio_source ) {
			if ( $audio_id ) {
				update_post_meta( $post_id, RSFA_META_KEY, absint( $audio_id ) );
			} else {
				// Remove audio if audio_id is 0.
				delete_post_meta( $post_id, RSFA_META_KEY );
			}
			// Clear embed URL when switching to self-hosted.
			delete_post_meta( $post_id, RSFA_EMBED_META_KEY );
		} elseif ( 'embed' === $audio_source ) {
			update_post_meta( $post_id, RSFA_EMBED_META_KEY, esc_url_raw( $embed_url ) );
			// Clear self-hosted audio when switching to embed.
			delete_post_meta( $post_id, RSFA_META_KEY );
		}

		return new WP_REST_Response(
			array(
				'success'      => true,
				'post_id'      => $post_id,
				'audio_source' => $audio_source,
				'audio_id'     => $audio_id,
				'embed_url'    => $embed_url,
			),
			200
		);
	}

	/**
	 * Update cover image for a post.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_cover( WP_REST_Request $request ) {
		$post_id   = absint( $request->get_param( 'post_id' ) );
		$cover_id = absint( $request->get_param( 'cover_id' ) );

		$post = get_post( $post_id );

		if ( ! $post ) {
			return new WP_Error(
				'invalid_post',
				__( 'Post not found.', 'really-simple-featured-audio' ),
				array( 'status' => 404 )
			);
		}

		// Validate cover_id is a valid image attachment.
		if ( $cover_id ) {
			$attachment = get_post( $cover_id );
			if ( ! $attachment || 'attachment' !== $attachment->post_type ) {
				return new WP_Error(
					'invalid_cover',
					__( 'Invalid cover image.', 'really-simple-featured-audio' ),
					array( 'status' => 400 )
				);
			}

			// Verify it's an image mime type.
			$mime_type = get_post_mime_type( $cover_id );
			if ( strpos( $mime_type, 'image/' ) !== 0 ) {
				return new WP_Error(
					'invalid_mime_type',
					__( 'Selected file is not an image.', 'really-simple-featured-audio' ),
					array( 'status' => 400 )
				);
			}

			update_post_meta( $post_id, RSFA_COVER_META_KEY, absint( $cover_id ) );
			$cover_url = wp_get_attachment_url( $cover_id );
		} else {
			delete_post_meta( $post_id, RSFA_COVER_META_KEY );
			$cover_url = '';
		}

		return new WP_REST_Response(
			array(
				'success'    => true,
				'post_id'    => $post_id,
				'cover_id'  => $cover_id,
				'cover_url' => $cover_url ? esc_url_raw( $cover_url ) : '',
			),
			200
		);
	}

	/**
	 * Update thumbnail (featured image) for a post.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_thumbnail( WP_REST_Request $request ) {
		$post_id      = absint( $request->get_param( 'post_id' ) );
		$thumbnail_id = absint( $request->get_param( 'thumbnail_id' ) );

		$post = get_post( $post_id );

		if ( ! $post ) {
			return new WP_Error(
				'invalid_post',
				__( 'Post not found.', 'really-simple-featured-audio' ),
				array( 'status' => 404 )
			);
		}

		// Validate thumbnail_id is a valid image attachment.
		if ( $thumbnail_id ) {
			$attachment = get_post( $thumbnail_id );
			if ( ! $attachment || 'attachment' !== $attachment->post_type ) {
				return new WP_Error(
					'invalid_thumbnail',
					__( 'Invalid thumbnail image.', 'really-simple-featured-audio' ),
					array( 'status' => 400 )
				);
			}

			// Verify it's an image mime type.
			$mime_type = get_post_mime_type( $thumbnail_id );
			if ( strpos( $mime_type, 'image/' ) !== 0 ) {
				return new WP_Error(
					'invalid_mime_type',
					__( 'Selected file is not an image.', 'really-simple-featured-audio' ),
					array( 'status' => 400 )
				);
			}

			set_post_thumbnail( $post_id, $thumbnail_id );
			$thumbnail_url = get_the_post_thumbnail_url( $post_id, 'thumbnail' );
		} else {
			delete_post_thumbnail( $post_id );
			$thumbnail_url = '';
		}

		return new WP_REST_Response(
			array(
				'success'       => true,
				'post_id'       => $post_id,
				'thumbnail_id'  => $thumbnail_id,
				'thumbnail_url' => $thumbnail_url ? esc_url_raw( $thumbnail_url ) : '',
			),
			200
		);
	}
}
