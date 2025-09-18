<?php
/**
 * Version Control Settings
 *
 * @package RSFA
 */

namespace RSFA\Settings;

use RSFA\Featuresets\Rollback\Init as Rollback;

defined( 'ABSPATH' ) || exit;

/**
 * Version_Control_Settings.
 */
class Version_Control_Settings extends Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'version_control';
		$this->label = __( 'Version Control', 'really-simple-featured-audio' );

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

		$rollback_controls = array();

		if ( current_user_can( 'update_plugins' ) ) {
			array_push(
				$rollback_controls,
				array(
					'title' => __( 'Rollback Versions', 'really-simple-featured-audio' ),
					'desc'  => __( 'If you are having issues with current version of Really Simple Featured Audio, you can rollback to a previous stable version.', 'really-simple-featured-audio' ),
					'type'  => 'title',
					'id'    => 'rsfa_plugin_rollback_version',
				),
				array(
					'title'     => __( 'Rollback RSFA', 'really-simple-featured-audio' ),
					'id'        => 'rsfa_rollback_version_select_option',
					'type'      => 'select',
					'class'     => 'rsfa-enhanced-select',
					'desc_tip'  => true,
					'options'   => $this->get_rollback_versions(),
					'is_option' => false,
				),
				array(
					'id'    => 'rsfa_rollback_version_button',
					'type'  => 'button',
					'class' => 'rsfa-rollback-version-button rsfa-button button-secondary',
					'value' => __( 'Reinstall this version', 'really-simple-featured-audio' ),
				),
				array(
					'type' => 'sectionend',
					'id'   => 'rsfa_plugin_rollback',
				)
			);
		}

		$settings = apply_filters(
			'rsfa_version_control_settings',
			$rollback_controls
		);

		return apply_filters( 'rsfa_get_settings_' . $this->id, $settings );
	}

	/**
	 * Get recent rollback versions in key/value pair.
	 *
	 * @return array
	 */
	public function get_rollback_versions() {
		$keys = Rollback::get_rollback_versions();
		$data = array();
		foreach ( $keys as $key => $value ) {
			$data[ $value ] = $value;
		}

		return $data;
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

return new Version_Control_Settings();
