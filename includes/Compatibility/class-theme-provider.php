<?php
/**
 * Theme compatibility handler.
 *
 * @package RSFA
 */

namespace RSFA\Compatibility;

use RSFA\Compatibility\Themes\Base_Compatibility;
use RSFA\Options;

/**
 * Class Theme_Provider
 *
 * @package RSFA
 */
class Theme_Provider {
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
		add_action( 'after_setup_theme', array( $this, 'load_theme_compat' ) );
	}

	/**
	 * Get a class instance.
	 *
	 * @return Object
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Get theme engines.
	 *
	 * @return array
	 */
	public function get_theme_engines() {
		return apply_filters(
			'rsfa_theme_compatibility_engines',
			array(
				'default'           => array(
					'title'       => __( 'Default', 'really-simple-featured-audio' ),
					'file_source' => RSFA_PLUGIN_DIR . 'includes/Compatibility/Themes/Fallback/class-compatibility.php',
					'class'       => 'RSFA\Compatibility\Themes\Fallback\Compatibility',
				),
				'twentytwenty'      => array(
					'title'       => __( 'Twenty Twenty', 'really-simple-featured-audio' ),
					'file_source' => RSFA_PLUGIN_DIR . 'includes/Compatibility/Themes/Core/Twentytwenty/class-compatibility.php',
					'class'       => 'RSFA\Compatibility\Themes\Core\Twentytwenty\Compatibility',
				),
				'twentytwentyone'   => array(
					'title'       => __( 'Twenty Twenty-One', 'really-simple-featured-audio' ),
					'file_source' => RSFA_PLUGIN_DIR . 'includes/Compatibility/Themes/Core/Twentytwenty_One/class-compatibility.php',
					'class'       => 'RSFA\Compatibility\Themes\Core\Twentytwenty_One\Compatibility',
				),
				'twentytwentytwo'   => array(
					'title'       => __( 'Twenty Twenty-Two', 'really-simple-featured-audio' ),
					'file_source' => RSFA_PLUGIN_DIR . 'includes/Compatibility/Themes/Core/Twentytwenty_Two/class-compatibility.php',
					'class'       => 'RSFA\Compatibility\Themes\Core\Twentytwenty_Two\Compatibility',
				),
				'twentytwentythree' => array(
					'title'       => __( 'Twenty Twenty-Three', 'really-simple-featured-audio' ),
					'file_source' => RSFA_PLUGIN_DIR . 'includes/Compatibility/Themes/Core/Twentytwenty_Three/class-compatibility.php',
					'class'       => 'RSFA\Compatibility\Themes\Core\Twentytwenty_Three\Compatibility',
				),
				'twentytwentyfour'  => array(
					'title'       => __( 'Twenty Twenty-Four', 'really-simple-featured-audio' ),
					'file_source' => RSFA_PLUGIN_DIR . 'includes/Compatibility/Themes/Core/Twentytwenty_Four/class-compatibility.php',
					'class'       => 'RSFA\Compatibility\Themes\Core\Twentytwenty_Four\Compatibility',
				),
			)
		);
	}

	/**
	 * Load theme compatibility.
	 *
	 * @return void
	 */
	public function load_theme_compat() {
		$theme      = wp_get_theme();
		$theme_slug = $theme->get_stylesheet();
		$options    = Options::get_instance();

		$compatibility_engine = $options->get( 'theme-compatibility-engine' );

		// For when there is an engine set.
		if ( 'disabled' === $compatibility_engine ) {
			// Exits early.
			$options->set( 'active-theme-engine', $compatibility_engine );
			$options->delete( 'automatic-theme-engine' );
			return;
		} elseif ( $compatibility_engine && 'auto' !== $compatibility_engine ) {
			$theme_slug = $compatibility_engine;
		}

		$theme_compat = null;

		$theme_engines = $this->get_theme_engines();

		if ( ! in_array( $theme_slug, array_keys( $theme_engines ), true ) ) {
			$theme_slug = 'default';
		}

		require_once $theme_engines[ $theme_slug ]['file_source'];
		$theme_compat = $theme_engines[ $theme_slug ]['class']::get_instance();

		if ( ! $theme_compat instanceof Base_Compatibility ) {
			$options->set( 'theme-engine-error', __( 'Failed at registration', 'really-simple-featured-audio' ) );
			$options->set( 'active-theme-engine', __( 'Unregistered', 'really-simple-featured-audio' ) );
			return;
		}

		// For when it defaults to auto.
		if ( ! $compatibility_engine || 'auto' === $compatibility_engine ) {
			$options->set( 'automatic-theme-engine', $theme_compat->get_id() );
		}

		// Stores the final engine active.
		$options->set( 'active-theme-engine', $theme_compat->get_id() );
	}

	/**
	 * Get registered engines id and title.
	 *
	 * @return array
	 */
	public function get_available_engines() {
		$registered_engines = array();
		$theme_engines      = $this->get_theme_engines();

		foreach ( $theme_engines as $engine_id => $engine_data ) {
			$registered_engines[ $engine_id ] = $engine_data['title'];
		}

		return $registered_engines;
	}

	/**
	 * Get selectable engines for user settings.
	 *
	 * @return array
	 */
	public function get_selectable_engine_options() {
		$selectable_engines = array(
			'disabled' => __( 'Disabled (Legacy)', 'really-simple-featured-audio' ),
			'auto'     => __( 'Auto (Do it for me)', 'really-simple-featured-audio' ),
		);

		$theme_engines = $this->get_theme_engines();

		foreach ( $theme_engines as $engine_id => $engine_data ) {
			$selectable_engines[ $engine_id ] = $engine_data['title'];
		}

		return $selectable_engines;
	}
}
