<?php
/**
 * Theme compatibility handler.
 *
 * @package RSFA
 */

namespace RSFA\Compatibility;

use RSFA\Plugin;
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

				// Core.
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
				'twentytwentyfive'  => array(
					'title'       => __( 'Twenty Twenty-Five', 'really-simple-featured-audio' ),
					'file_source' => RSFA_PLUGIN_DIR . 'includes/Compatibility/Themes/Core/Twentytwenty_Five/class-compatibility.php',
					'class'       => 'RSFA\Compatibility\Themes\Core\Twentytwenty_Five\Compatibility',
				),
				'storefront'        => array(
					'title'       => __( 'Storefront', 'really-simple-featured-audio' ),
					'file_source' => RSFA_PLUGIN_DIR . 'includes/Compatibility/Themes/Core/Storefront/class-compatibility.php',
					'class'       => 'RSFA\Compatibility\Themes\Core\Storefront\Compatibility',
				),

				// Third-Party.
				'divi'              => array(
					'title'       => __( 'Divi (Free)', 'really-simple-featured-audio' ),
					'file_source' => RSFA_PLUGIN_DIR . 'includes/Compatibility/Themes/ThirdParty/Divi/class-compatibility.php',
					'class'       => 'RSFA\Compatibility\Themes\ThirdParty\Divi\Compatibility',
				),
				'neve'              => array(
					'title'       => __( 'Neve', 'really-simple-featured-audio' ),
					'file_source' => RSFA_PLUGIN_DIR . 'includes/Compatibility/Themes/ThirdParty/Neve/class-compatibility.php',
					'class'       => 'RSFA\Compatibility\Themes\ThirdParty\Neve\Compatibility',
				),
				'generatepress'     => array(
					'title'       => __( 'GeneratePress', 'really-simple-featured-audio' ),
					'file_source' => RSFA_PLUGIN_DIR . 'includes/Compatibility/Themes/ThirdParty/GeneratePress/class-compatibility.php',
					'class'       => 'RSFA\Compatibility\Themes\ThirdParty\GeneratePress\Compatibility',
				),
				'astra'             => array(
					'title'       => __( 'Astra', 'really-simple-featured-audio' ),
					'file_source' => RSFA_PLUGIN_DIR . 'includes/Compatibility/Themes/ThirdParty/Astra/class-compatibility.php',
					'class'       => 'RSFA\Compatibility\Themes\ThirdParty\Astra\Compatibility',
				),
				'go'                => array(
					'title'       => __( 'Go', 'really-simple-featured-audio' ),
					'file_source' => RSFA_PLUGIN_DIR . 'includes/Compatibility/Themes/ThirdParty/Go/class-compatibility.php',
					'class'       => 'RSFA\Compatibility\Themes\ThirdParty\Go\Compatibility',
				),
				'kadence'           => array(
					'title'       => __( 'Kadence', 'really-simple-featured-audio' ),
					'file_source' => RSFA_PLUGIN_DIR . 'includes/Compatibility/Themes/ThirdParty/Kadence/class-compatibility.php',
					'class'       => 'RSFA\Compatibility\Themes\ThirdParty\Kadence\Compatibility',
				),
				'hestia'            => array(
					'title'       => __( 'Hestia', 'really-simple-featured-audio' ),
					'file_source' => RSFA_PLUGIN_DIR . 'includes/Compatibility/Themes/ThirdParty/Hestia/class-compatibility.php',
					'class'       => 'RSFA\Compatibility\Themes\ThirdParty\Hestia\Compatibility',
				),
				'flatsome'          => array(
					'title'       => __( 'Flatsome', 'really-simple-featured-audio' ),
					'file_source' => RSFA_PLUGIN_DIR . 'includes/Compatibility/Themes/ThirdParty/Flatsome/class-compatibility.php',
					'class'       => 'RSFA\Compatibility\Themes\ThirdParty\Flatsome\Compatibility',
				),
				'dt-the7'           => array(
					'title'       => __( 'The7', 'really-simple-featured-audio' ),
					'file_source' => RSFA_PLUGIN_DIR . 'includes/Compatibility/Themes/ThirdParty/The7/class-compatibility.php',
					'class'       => 'RSFA\Compatibility\Themes\ThirdParty\The7\Compatibility',
				),
				'savoy'             => array(
					'title'       => __( 'Savoy', 'really-simple-featured-audio' ),
					'file_source' => RSFA_PLUGIN_DIR . 'includes/Compatibility/Themes/ThirdParty/Savoy/class-compatibility.php',
					'class'       => 'RSFA\Compatibility\Themes\ThirdParty\Savoy\Compatibility',
				),
				'ollie'             => array(
					'title'       => __( 'Ollie', 'really-simple-featured-audio' ),
					'file_source' => RSFA_PLUGIN_DIR . 'includes/Compatibility/Themes/ThirdParty/Ollie/class-compatibility.php',
					'class'       => 'RSFA\Compatibility\Themes\ThirdParty\Ollie\Compatibility',
				),
				'electro'           => array(
					'title'       => __( 'Electro', 'really-simple-featured-audio' ),
					'file_source' => RSFA_PLUGIN_DIR . 'includes/Compatibility/Themes/ThirdParty/Electro/class-compatibility.php',
					'class'       => 'RSFA\Compatibility\Themes\ThirdParty\Electro\Compatibility',
				),
				'woostify'          => array(
					'title'       => __( 'Woostify', 'really-simple-featured-audio' ),
					'file_source' => RSFA_PLUGIN_DIR . 'includes/Compatibility/Themes/ThirdParty/Woostify/class-compatibility.php',
					'class'       => 'RSFA\Compatibility\Themes\ThirdParty\Woostify\Compatibility',
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
		$theme_slug = strtolower( $theme->get_stylesheet() );
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

		// To make sure child themes don't escape parents.
		if ( str_contains( $theme_slug, '-child' ) ) {
			$theme_slug = str_replace( '-child', '', $theme_slug );
		}

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

		if ( ! Plugin::get_instance()->has_pro_active() ) {

			// Pro theme Engines for promo.
			$pro_selectable_engines = $this->get_selectable_pro_engine_options_promo();

			// Include promo engines.
			foreach ( $pro_selectable_engines as $engine_id => $engine_label ) {
				if ( ! array_key_exists( $engine_id, $selectable_engines ) ) {
					$selectable_engines[ $engine_id ] = $engine_label;
				}
			}
		}

		return $selectable_engines;
	}

	/**
	 * Returns the list of theme engines available in PRO plugin.
	 *
	 * @return array
	 */
	public function get_selectable_pro_engine_options_promo() {
		return array(
			'oceanwp'  => __( 'OceanWP (PRO)', 'really-simple-featured-audio' ),
			'jupiterx' => __( 'Jupiter X (PRO)', 'really-simple-featured-audio' ),
			'flatsome' => __( 'Flatsome (PRO)', 'really-simple-featured-audio' ),
			'wellco'   => __( 'Wellco (PRO)', 'really-simple-featured-audio' ),
			'avanam'   => __( 'Avanam (PRO)', 'really-simple-featured-audio' ),
			'divi-pro' => __( 'Divi Builder (PRO)', 'really-simple-featured-audio' ),
			'avada'    => __( 'Avada (PRO)', 'really-simple-featured-audio' ),
			'konte'    => __( 'Konte (PRO)', 'really-simple-featured-audio' ),
			'lay'      => __( 'Lay (PRO)', 'really-simple-featured-audio' ),
			'uncode'   => __( 'Uncode (PRO)', 'really-simple-featured-audio' ),
			'bravada'  => __( 'Bravada (PRO)', 'really-simple-featured-audio' ),
			'lodestar' => __( 'Lodestar (PRO)', 'really-simple-featured-audio' ),
		);
	}
}
