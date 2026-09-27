<?php
/**
 * Divi Settings
 *
 * @package RSFA_Pro
 */

namespace RSFA\Compatibility\Plugins\Divi;

use RSFA\Plugin;
use RSFA\Settings\Settings_Page;
use RSFA\Settings\Admin_Settings;

if ( ! class_exists( '\RSFA\Settings\Admin_Settings' ) ) {
	return;
}

defined( 'ABSPATH' ) || exit;

/**
 * Integrations controls.
 */
class Settings extends Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'divi';
		$this->label = __( 'Divi', 'really-simple-featured-audio' );

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
			$settings = array();

			if ( ! Plugin::get_instance()->has_pro_active() ) {
				$settings = array_merge(
					$settings,
					array(
						array(
							'type' => 'title',
							'id'   => 'rsfa_pro_divi_woocommerce_title',
						),
						array(
							'title'   => __( 'Featured Audio for Divi Woo Product Images', 'really-simple-featured-audio' ),
							'desc'    => __( 'When toggled on, it shows Featured Product Audios in Divi Woo Product Images Module/Widget. Turn it off if you\'re having problem with WooCommerce templates.', 'really-simple-featured-audio' ),
							'id'      => 'promo-has-divi-woo-featured_audio',
							'default' => false,
							'type'    => 'promo-checkbox',
						),
						array(
							'type' => 'sectionend',
							'id'   => 'rsfa_pro_divi_woocommerce_title',
						),
					)
				);
			}

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
