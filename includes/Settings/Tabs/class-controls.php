<?php
/**
 * Controls Settings
 *
 * @package RSFA
 */

namespace RSFA\Settings;

use RSFA\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Audio frame controls.
 */
class Controls extends Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'controls';
		$this->label = __( 'Controls', 'really-simple-featured-audio' );

		parent::__construct();
	}

	/**
	 * Get sections.
	 *
	 * @return array
	 */
	public function get_sections() {
		$sections = array(
			''               => __( 'Standard', 'really-simple-featured-audio' ),
			'hover-autoplay' => __( 'Play on Hover', 'really-simple-featured-audio' ),
		);

		return apply_filters( 'rsfa_get_sections_' . $this->id, $sections );
	}

	/**
	 * Get settings array.
	 *
	 * @param string $current_section Current section ID.
	 *
	 * @return array
	 */
	public function get_settings( $current_section = '' ) {
		// The settings screen passes no section; it lives in a global.
		if ( '' === $current_section && ! empty( $GLOBALS['current_section'] ) ) {
			$current_section = sanitize_title( $GLOBALS['current_section'] );
		}

		if ( 'hover-autoplay' === $current_section ) {
			return apply_filters( 'rsfa_get_settings_' . $this->id, $this->get_hover_settings() );
		}

		$autoplay_note = __( 'Note: Autoplay will only work if mute sound is enabled as per browser policy.', 'really-simple-featured-audio' );

		$control_options = array(
			'controls' => __( 'Controls', 'really-simple-featured-audio' ),
			'autoplay' => __( 'Autoplay', 'really-simple-featured-audio' ),
			'loop'     => __( 'Loop', 'really-simple-featured-audio' ),
			'mute'     => __( 'Mute sound', 'really-simple-featured-audio' ),
		);

		// Only self-hosted files can offer a download button.
		$self_control_options             = $control_options;
		$self_control_options['download'] = __( 'Download', 'really-simple-featured-audio' );

		$default_controls = get_default_audio_controls();

		$settings = apply_filters(
			'rsfa_controls_settings',
			array(
				array(
					'title' => esc_html_x( 'Self-hosted audios', 'settings title', 'really-simple-featured-audio' ),
					'desc'  => __( 'Please select the controls you wish to enable for your self hosted audios.', 'really-simple-featured-audio' ),
					'class' => 'rsfa-self-audio-controls',
					'type'  => 'content',
					'id'    => 'rsfa-self-audio-controls',
				),
				array(
					'type' => 'title',
					'id'   => 'rsfa_self_audio_controls_title',
				),
				array(
					'title'   => '',
					'desc'    => $autoplay_note,
					'id'      => 'self_audio_controls',
					'default' => $default_controls,
					'type'    => 'multi-checkbox',
					'options' => $self_control_options,
				),
				array(
					'type' => 'sectionend',
					'id'   => 'rsfa_self_audio_controls_title',
				),
				array(
					'title' => esc_html_x( 'Embed audios', 'settings title', 'really-simple-featured-audio' ),
					'desc'  => __( 'Please select the controls you wish to enable for your embedded audios.', 'really-simple-featured-audio' ),
					'class' => 'rsfa-embed-audio-controls',
					'type'  => 'content',
					'id'    => 'rsfa-embed-audio-controls',
				),
				array(
					'type' => 'title',
					'id'   => 'rsfa_self_embed_controls_title',
				),
				array(
					'title'   => '',
					'desc'    => $autoplay_note,
					'id'      => 'embed_audio_controls',
					'default' => $default_controls,
					'type'    => 'multi-checkbox',
					'options' => $control_options,
				),
				array(
					'type' => 'sectionend',
					'id'   => 'rsfa_self_embed_controls_title',
				),
			)
		);

		return apply_filters( 'rsfa_get_settings_' . $this->id, $settings );
	}

	/**
	 * Get the Play on Hover section settings.
	 *
	 * @since 1.6.0
	 *
	 * @return array
	 */
	protected function get_hover_settings() {
		$settings = array(
			array(
				'title' => __( 'Play on Hover', 'really-simple-featured-audio' ),
				'desc'  => __( 'Plays a featured audio while the pointer is over its player, and pauses it when the pointer leaves. Browsers may block sound until the visitor has clicked somewhere on the page.', 'really-simple-featured-audio' ),
				'type'  => 'content',
				'id'    => 'rsfa-hover-autoplay-controls',
			),
			array(
				'type' => 'title',
				'id'   => 'rsfa_hover_autoplay',
			),
			array(
				'title'   => __( 'Enable Feature', 'really-simple-featured-audio' ),
				'id'      => 'enable_hover_autoplay',
				'default' => false,
				'type'    => 'checkbox',
			),
			array(
				'type' => 'sectionend',
				'id'   => 'rsfa_hover_autoplay',
			),
			array(
				'title' => esc_html_x( 'Audio Types', 'settings title', 'really-simple-featured-audio' ),
				'desc'  => __( 'Choose which audios play on hover.', 'really-simple-featured-audio' ),
				'type'  => 'content',
				'class' => 'rsfa-multi-checkbox-card',
				'id'    => 'rsfa-hover-autoplay-audio-types',
			),
			array(
				'type' => 'title',
				'id'   => 'rsfa_hover_autoplay_audio_types_title',
			),
			array(
				'title'   => '',
				'id'      => 'hover_autoplay_audio_types',
				'default' => array(
					'self'  => true,
					'embed' => true,
				),
				'type'    => 'multi-checkbox',
				'options' => array(
					'self'  => __( 'Self Hosted', 'really-simple-featured-audio' ),
					'embed' => __( 'Embed', 'really-simple-featured-audio' ),
				),
			),
			array(
				'type' => 'sectionend',
				'id'   => 'rsfa_hover_autoplay_audio_types_title',
			),
		);

		if ( ! Plugin::get_instance()->has_pro_active() ) {
			$settings = array_merge(
				$settings,
				array(
					array(
						'title' => esc_html_x( 'Screen Sizes', 'settings title', 'really-simple-featured-audio' ),
						'desc'  => __( 'Toggle the screen sizes you wish to enable/disable play on hover support. Available in Pro.', 'really-simple-featured-audio' ),
						'type'  => 'promo-content',
						'class' => 'rsfa-promo-multi-checkbox-card',
						'id'    => 'promo-rsfa-pro-hover-autoplay-screens',
					),
					array(
						'type' => 'title',
						'id'   => 'promo_hover_autoplay_screens_title',
					),
					array(
						'title'     => '',
						'id'        => 'promo-hover-autoplay-screens',
						'default'   => array(
							'desktop' => true,
							'mobile'  => true,
						),
						'type'      => 'promo-multi-checkbox',
						'options'   => array(
							'desktop' => __( 'Desktop', 'really-simple-featured-audio' ),
							'mobile'  => __( 'Mobile', 'really-simple-featured-audio' ),
						),
						'is_option' => false,
					),
					array(
						'type' => 'sectionend',
						'id'   => 'promo_hover_autoplay_screens_title',
					),
					array(
						'type' => 'title',
						'id'   => 'promo_hover_autoplay_extras',
					),
					array(
						'title'     => __( 'Set Mobile Breakpoint (px)', 'really-simple-featured-audio' ),
						'desc'      => __( 'Screen width below which device is considered mobile. Default: 768px', 'really-simple-featured-audio' ),
						'id'        => 'promo-hover-autoplay-mobile-breakpoint',
						'default'   => 768,
						'type'      => 'promo-number',
						'is_option' => false,
					),
					array(
						'title'     => __( 'Set Hover Delay (ms)', 'really-simple-featured-audio' ),
						'desc'      => __( 'Delay before audio starts playing on hover. Default: 100ms', 'really-simple-featured-audio' ),
						'id'        => 'promo-hover-autoplay-delay',
						'default'   => 100,
						'type'      => 'promo-number',
						'is_option' => false,
					),
					array(
						'type' => 'sectionend',
						'id'   => 'promo_hover_autoplay_extras',
					),
					array(
						'title' => __( 'Accessibility', 'really-simple-featured-audio' ),
						'desc'  => __( 'Toggle the accessibility features at play on hover. Available in Pro.', 'really-simple-featured-audio' ),
						'type'  => 'promo-content',
						'id'    => 'promo-hover-autoplay-accessibility',
						'class' => 'promo-hover-autoplay-accessibility',
					),
					array(
						'type' => 'title',
						'id'   => 'promo_hover_autoplay_accessibility',
					),
					array(
						'title'     => __( 'User Preferences', 'really-simple-featured-audio' ),
						'desc'      => __( 'Respect "reduced motion" preference.', 'really-simple-featured-audio' ),
						'id'        => 'promo-hover-autoplay-respect-user-prefs',
						'default'   => true,
						'type'      => 'promo-checkbox',
						'is_option' => false,
					),
					array(
						'title'     => __( 'Focus Events', 'really-simple-featured-audio' ),
						'desc'      => __( 'Enable focus events for keyboard navigation.', 'really-simple-featured-audio' ),
						'id'        => 'promo-hover-autoplay-focus-events',
						'default'   => true,
						'type'      => 'promo-checkbox',
						'is_option' => false,
					),
					array(
						'type' => 'sectionend',
						'id'   => 'promo_hover_autoplay_accessibility',
					),
				)
			);
		}

		/**
		 * Filters the Play on Hover section settings.
		 *
		 * @since 1.6.0
		 *
		 * @param array $settings Settings.
		 */
		return apply_filters( 'rsfa_controls_hover_autoplay_settings', $settings );
	}

	/**
	 * Save settings.
	 */
	public function save() {
		global $current_section;

		$settings = $this->get_settings( $current_section );

		Admin_Settings::save_fields( $settings );
		if ( $current_section ) {
			do_action( 'rsfa_update_options_' . $this->id . '_' . $current_section );
		}
	}
}

return new Controls();
