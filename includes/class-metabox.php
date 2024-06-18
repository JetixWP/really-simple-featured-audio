<?php
/**
 * Metabox handler.
 *
 * @package RSFA
 */

namespace RSFA;

use function RSFA\Settings\get_post_types;
use function RSFA\Settings\get_audio_controls;

/**
 * Class Metabox
 */
class Metabox {
	/**
	 * Class instance.
	 *
	 * @var $instance
	 */
	protected static $instance;

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Adding meta box for featured audio.
		add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );

		// Loading Admin scripts here.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Saving post by updating "featured_audio_uploading" meta key.
		add_action( 'save_post', array( $this, 'save_audio' ), 10, 1 );

		// Allows display property at style attribute for wp_kses.
		add_filter(
			'safe_style_css',
			function( $styles ) {
				$styles[] = 'display';
				return $styles;
			}
		);
	}

	/**
	 * Get an instance of class.
	 *
	 * @return Metabox
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Register Featured Audio metabox.
	 *
	 * @return void
	 */
	public function add_metabox() {
		// Get enabled post types.
		$post_types = get_post_types();
		if ( ! empty( $post_types ) ) {
			add_meta_box( 'featured-audio', __( 'Featured Audio', 'rsfa' ), array( $this, 'upload_audio' ), $post_types, 'side', 'low' );
		}
	}

	/**
	 * Enqueues scripts required for media uploader.
	 *
	 * @retun void
	 */
	public function enqueue_scripts() {
		global $pagenow;

		if ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) {
			// Enqueue all necessary WP Media APIs.
			wp_enqueue_media();
			// Enqueue plugin script.
			wp_enqueue_script( 'rsfa_custom_script', RSFA_PLUGIN_URL . 'assets/js/rsfa-media.js', array( 'jquery' ), RSFA_VERSION, true );

			wp_localize_script(
				'rsfa_custom_script',
				'RSFA',
				array(
					'uploader_title'    => __( 'Insert Audio', 'rsfa' ),
					'uploader_btn_text' => __( 'Use this audio', 'rsfa' ),
				)
			);
		}
	}


	/**
	 * Uploads the audio.
	 *
	 * @param object $post Post object which holds post data.
	 * @return void
	 */
	public function upload_audio( $post ) {

		// Generate nonce field.
		wp_nonce_field( 'rsfa_inner_custom_box', 'rsfa_inner_custom_box_nonce' );

		// Get the meta value of audio source.
		$audio_source = get_post_meta( $post->ID, RSFA_SOURCE_META_KEY, true );
		$audio_source = $audio_source ? $audio_source : 'self';

		// Get the meta value of audio attachment.
		$audio_id = get_post_meta( $post->ID, RSFA_META_KEY, true );

		// Get the meta value of audio embed url.
		$embed_url = get_post_meta( $post->ID, RSFA_EMBED_META_KEY, true );

		$image     = ' button">' . __( 'Upload Audio', 'rsfa' );
		$display   = 'none';
		$audio_url = wp_get_attachment_url( $audio_id );

		$audio_controls = get_audio_controls();

		// Get autoplay option.
		$is_autoplay = ( is_array( $audio_controls ) && isset( $audio_controls['autoplay'] ) ) && $audio_controls['autoplay'];
		$is_autoplay = $is_autoplay ? 'autoplay' : '';

		// Get loop option.
		$is_loop = ( is_array( $audio_controls ) && isset( $audio_controls['loop'] ) ) && $audio_controls['loop'];
		$is_loop = $is_loop ? 'loop' : '';

		// Get mute option.
		$is_muted = ( is_array( $audio_controls ) && isset( $audio_controls['mute'] ) ) && $audio_controls['mute'];
		$is_muted = $is_muted ? 'muted' : '';

		// Get PictureInPicture option.
		$is_pip = ( is_array( $audio_controls ) && isset( $audio_controls['pip'] ) ) && $audio_controls['pip'];
		$is_pip = $is_pip ? 'autopictureinpicture' : '';

		// Get audio controls option.
		$has_controls = ( is_array( $audio_controls ) && isset( $audio_controls['controls'] ) ) && $audio_controls['controls'];
		$has_controls = $has_controls ? 'controls' : '';

		if ( $audio_url ) {
			$image   = '"><audio src="' . esc_url( $audio_url ) . '" style="max-width:95%;display:block;" ' . esc_attr( $has_controls ) . ' ' . esc_attr( $is_autoplay ) . ' ' . esc_attr( $is_loop ) . ' ' . esc_attr( $is_muted ) . ' ' . esc_attr( $is_pip ) . '></audio>';
			$display = 'inline-block';
		}

		$uploader_markup = sprintf(
			'<div class="rsfa-self"><a href="#" class="rsfa-upload-audio-btn%1$s</a><input type="hidden" name="%2$s" id="%2$s" value="%3$s" /><a href="#" class="remove-audio" style="display:%4$s;">%5$s</a></div>',
			$image,
			RSFA_META_KEY,
			$audio_id,
			$display,
			__( 'Remove Audio', 'rsfa' )
		);

		$embed_markup = sprintf(
			'<div class="rsfa-embed"><input type="url" name="%1$s" id="%1$s" value="%2$s" placeholder="%3$s" /><span><br><br>%4$s</span></div>',
			RSFA_EMBED_META_KEY,
			$embed_url,
			__( 'Audio url goes here', 'rsfa' ),
			__( 'Directly copy &amp; paste audio urls from Youtube, Vimeo &amp; Dailymotion.', 'rsfa' )
		);

		$self_input = sprintf(
			'<input type="radio" id="self" name="%1$s" value="self" %2$s><label for="self">%3$s</label><br>%4$s',
			RSFA_SOURCE_META_KEY,
			checked( 'self', $audio_source, false ),
			__( 'Self', 'rsfa' ),
			$uploader_markup
		);

		$embed_input = sprintf(
			'<input type="radio" id="embed" name="%1$s" value="embed" %2$s><label for="embed">%3$s</label><br>%4$s',
			RSFA_SOURCE_META_KEY,
			checked( 'embed', $audio_source, false ),
			__( 'Embed', 'rsfa' ),
			$embed_markup
		);

		$select_source = sprintf(
			'<div><p>%1$s</p>%2$s%3$s</div>',
			__( 'Please select a audio source', 'rsfa' ),
			$self_input,
			$embed_input
		);

		$styles = '<style>.rsfa-self, .rsfa-embed { padding: 10px 0; } .remove-audio { margin-top: 6px; }</style>';

		printf(
			'%1$s%2$s',
			wp_kses( $select_source, $this->get_allowed_html() ),
			wp_kses( $styles, $this->get_allowed_html() ),
		);
	}

	/**
	 * Saves selected audio.
	 *
	 * @param string $post_id Holds post id.
	 * @return string
	 */
	public function save_audio( $post_id ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		$nonce = isset( $_POST['rsfa_inner_custom_box_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['rsfa_inner_custom_box_nonce'] ) ) : '';

		// Check if nonce is set.
		if ( empty( $nonce ) ) {
			return $post_id;
		}

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'rsfa_inner_custom_box' ) ) {
			return $post_id;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		$keys = array(
			RSFA_SOURCE_META_KEY,
			RSFA_META_KEY,
			RSFA_EMBED_META_KEY,
		);

		foreach ( $keys as $key ) {
			// Get updated value.
			$key_value = isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';

			// Save key value in meta key.
			update_post_meta( $post_id, $key, $key_value );
		}

		return $post_id;
	}

	/**
	 * Get a list of allowed html elements.
	 *
	 * @return array Allowed html elements.
	 */
	public function get_allowed_html() {
		return array(
			'audio' => array(
				'src'                  => array(),
				'style'                => array(),
				'loop'                 => array(),
				'muted'                => array(),
				'autopictureinpicture' => array(),
				'autoplay'             => array(),
				'controls'             => array(),
			),
			'input' => array(
				'type'        => array(),
				'id'          => array(),
				'name'        => array(),
				'value'       => array(),
				'placeholder' => array(),
				'checked'     => array(),
			),
			'label' => array(
				'for' => array(),
			),
			'div'   => array(
				'class' => array(),
			),
			'a'     => array(
				'href'  => array(),
				'class' => array(),
				'style' => array(),
			),
			'p'     => array(),
			'span'  => array(),
			'br'    => array(),
			'i'     => array(),
			'style' => array(),
		);
	}
}
