<?php
/**
 * Elementor Settings
 *
 * @package RSFA
 */

namespace RSFA\Compatibility\Plugins\Elementor;

use RSFA\Settings\Settings_Page;
use RSFA\Settings\Admin_Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Integrations controls.
 */
class Settings extends Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'elementor';
		$this->label = __( 'Elementor', 'really-simple-featured-audio' );

		parent::__construct();
	}

	/**
	 * Get sections.
	 *
	 * @return array
	 */
	public function get_sections() {
		return apply_filters( 'rsfa_get_sections_' . $this->id, array() );
	}

	/**
	 * Get settings array.
	 *
	 * @param string $current_section Current section ID.
	 *
	 * @return array
	 */
	public function get_settings( $current_section = '' ) {
		global $current_section;

		$settings = array();

		if ( '' === $current_section ) {
			$settings = array(
				array(
					'type' => 'title',
					'id'   => 'rsfa_elementor_title',
				),
				array(
					'title'   => __( 'Disable Elementor Support', 'really-simple-featured-audio' ),
					'desc'    => __( 'Toggle this on if in Elementor you see the site logo, footer logo or any other part of the site images getting replaced with featured audio.', 'really-simple-featured-audio' ),
					'id'      => 'disable_elementor_support',
					'default' => false,
					'type'    => 'checkbox',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'rsfa_elementor_title',
				),
			);

			$settings = apply_filters(
				'rsfa_' . $this->id . '_settings',
				$settings
			);
		}

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

return new Settings();
