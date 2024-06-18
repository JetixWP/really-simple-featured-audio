<?php
/**
 * Controls Settings
 *
 * @package RSFA
 */

namespace RSFA\Settings;

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
		$this->label = __( 'Controls', 'rsfa' );

		parent::__construct();
	}

	/**
	 * Get settings array.
	 *
	 * @param string $current_section Current section ID.
	 *
	 * @return array
	 */
	public function get_settings( $current_section = '' ) {
		$autoplay_note = __( 'Note: Autoplay will only work if mute sound is enabled as per browser policy.', 'rsfa' );

		$control_options = array(
			'controls' => __( 'Controls', 'rsfa' ),
			'autoplay' => __( 'Autoplay', 'rsfa' ),
			'loop'     => __( 'Loop', 'rsfa' ),
			'pip'      => __( 'Picture in Picture', 'rsfa' ),
			'mute'     => __( 'Mute sound', 'rsfa' ),
		);

		$default_controls = get_default_audio_controls();

		$settings = apply_filters(
			'rsfa_controls_settings',
			array(
				array(
					'title' => esc_html_x( 'Self-hosted audios', 'settings title', 'rsfa' ),
					'desc'  => __( 'Please select the controls you wish to enable for your self hosted audios.', 'rsfa' ),
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
					'options' => $control_options,
				),
				array(
					'type' => 'sectionend',
					'id'   => 'rsfa_self_audio_controls_title',
				),
				array(
					'title' => esc_html_x( 'Embed audios', 'settings title', 'rsfa' ),
					'desc'  => __( 'Please select the controls you wish to enable for your embedded audios.', 'rsfa' ),
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
					'id'   => 'rsfa_embed_audio_controls_title',
				),
			)
		);

		return apply_filters( 'rsfa_get_settings_' . $this->id, $settings );
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
