<?php
/**
 * Abstract like class Base_Compatibility for Themes.
 *
 * @package RSFA
 */

namespace RSFA\Compatibility\Themes;

use function RSFA\Settings\get_post_types;


/**
 * Abstract class definition for controllers.
 *
 * @since 0.6.0
 */
abstract class Base_Compatibility {
	/**
	 * Compatibility id.
	 *
	 * @var string $id
	 */
	public $id = '';

	/**
	 * Class instance.
	 *
	 * @var $instance
	 */
	protected static $instance;

	/**
	 * Get instance.
	 *
	 * @return mixed
	 */
	final public static function get_instance() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	/**
	 * Cloning not allowed.
	 *
	 * @return void
	 */
	private function __clone() {}

	/**
	 * Constructor
	 */
	public function __construct() {
		// Update post classes for one or more conditions.
		add_filter( 'post_class', array( $this, 'set_post_classes' ) );

		// Update body classes for one or more conditions.
		add_filter( 'body_class', array( $this, 'set_body_classes' ) );
	}

	/**
	 * Get compatibility id.
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get compatibility title.
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Get the current namespace dir URL.
	 *
	 * @return string
	 */
	public function get_current_dir_url() {
		return RSFA_PLUGIN_URL . 'includes/Compatibility/Themes/';
	}

	/**
	 * Get the current namespace dir.
	 *
	 * @return string
	 */
	public function get_current_dir() {
		return RSFA_PLUGIN_DIR . 'includes/Compatibility/Themes/';
	}

	/**
	 * Set post classes.
	 *
	 * @param array $classes Post classes.
	 *
	 * @return array
	 */
	public function set_post_classes( $classes ) {
		$post_id = get_the_ID();

		// Get the meta value of audio embed url.
		$audio_source = get_post_meta( $post_id, RSFA_SOURCE_META_KEY, true );
		$media_id     = get_post_meta( $post_id, RSFA_META_KEY, true );
		$audio_url    = get_post_meta( $post_id, RSFA_EMBED_META_KEY, true );

		$enabled_post_types = get_post_types();

		if ( in_array( get_post_type(), $enabled_post_types, true ) && ( ( 'self' === $audio_source && $media_id ) || ( 'embed' === $audio_source && $audio_url ) ) ) {
			$classes[] = 'rsfa-has-audio';
		}

		return $classes;
	}

	/**
	 * Set body classes.
	 *
	 * @param array $classes Body classes.
	 *
	 * @return array
	 */
	public function set_body_classes( $classes ) {
		if ( is_singular() ) {
			$post_id = get_the_ID();

			// Get the meta value of audio embed url.
			$audio_source = get_post_meta( $post_id, RSFA_SOURCE_META_KEY, true );
			$media_id     = get_post_meta( $post_id, RSFA_META_KEY, true );
			$audio_url    = get_post_meta( $post_id, RSFA_EMBED_META_KEY, true );

			$enabled_post_types = get_post_types();

			if ( in_array( get_post_type(), $enabled_post_types, true ) && ( ( 'self' === $audio_source && $media_id ) || ( 'embed' === $audio_source && $audio_url ) ) ) {
				$classes[] = 'rsfa-has-audio';
			}
		}

		return apply_filters( 'rsfa_body_classes', $classes );
	}
}
