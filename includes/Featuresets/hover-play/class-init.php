<?php
/**
 * Play on Hover feature handler.
 *
 * @package RSFA
 */

namespace RSFA\Featuresets\Hover_Play;

use RSFA\Options;

defined( 'ABSPATH' ) || exit;

/**
 * Class Init
 *
 * Plays a featured audio while the pointer is over its player, and pauses it
 * when the pointer leaves.
 *
 * @since 1.6.0
 *
 * @package RSFA
 */
class Init {
	/**
	 * Class instance.
	 *
	 * @var $instance
	 */
	protected static $instance;

	/**
	 * Get a class instance.
	 *
	 * @return Init
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
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Get current settings with defaults.
	 *
	 * Uses the same keys as Really Simple Featured Video's autoplay on hover,
	 * so the PRO add-on can extend both in the same way.
	 *
	 * @return array
	 */
	public static function get_settings() {
		$default_settings = array(
			'enable_hover_autoplay'    => false,
			'audio_types'              => array(
				'self'  => true,
				'embed' => true,
			),
			'enable_on_desktop'        => true,
			'enable_on_mobile'         => true,
			'mobile_breakpoint'        => 768,
			'hover_delay'              => 100,
			'respect_user_preferences' => true,
			'enable_focus_events'      => true,
			'extra_selectors'          => '',
		);

		$options     = Options::get_instance();
		$audio_types = $options->has( 'hover_autoplay_audio_types' ) ? (array) $options->get( 'hover_autoplay_audio_types' ) : $default_settings['audio_types'];

		/**
		 * Filters the Play on Hover settings.
		 *
		 * @since 1.6.0
		 *
		 * @param array $settings Settings.
		 */
		$settings = apply_filters(
			'rsfa_hover_autoplay_options',
			array(
				'enable_hover_autoplay' => (bool) $options->get( 'enable_hover_autoplay', false ),
				'audio_types'           => array(
					'self'  => ! empty( $audio_types['self'] ),
					'embed' => ! empty( $audio_types['embed'] ),
				),
			)
		);

		return wp_parse_args( $settings, $default_settings );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		$settings = self::get_settings();

		if ( ! $settings['enable_hover_autoplay'] ) {
			return;
		}

		// No dependency on the player script: this listens for players as they mount.
		wp_enqueue_script(
			'rsfa-hover-play',
			RSFA_PLUGIN_URL . 'assets/js/rsfa-hover-play.js',
			array(),
			filemtime( RSFA_PLUGIN_DIR . 'assets/js/rsfa-hover-play.js' ),
			true
		);

		// JSON keeps booleans and numbers typed, unlike wp_localize_script().
		$script_data = array(
			'audioTypes'             => $settings['audio_types'],
			'enableOnDesktop'        => (bool) $settings['enable_on_desktop'],
			'enableOnMobile'         => (bool) $settings['enable_on_mobile'],
			'mobileBreakpoint'       => absint( $settings['mobile_breakpoint'] ),
			'hoverDelay'             => absint( $settings['hover_delay'] ),
			'respectUserPreferences' => (bool) $settings['respect_user_preferences'],
			'enableFocusEvents'      => (bool) $settings['enable_focus_events'],
			'extraSelectors'         => (string) $settings['extra_selectors'],
		);

		wp_add_inline_script( 'rsfa-hover-play', 'window.RSFAHoverPlaySettings = ' . wp_json_encode( $script_data ) . ';', 'before' );
	}
}

Init::get_instance();
