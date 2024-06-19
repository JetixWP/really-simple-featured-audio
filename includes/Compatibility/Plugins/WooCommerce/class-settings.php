<?php
/**
 * WooCommerce Settings
 *
 * @package RSFA
 */

namespace RSFA\Compatibility\Plugins\WooCommerce;

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
		$this->id    = 'woocommerce';
		$this->label = __( 'WooCommerce', 'really-simple-featured-audio' );

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
					'id'   => 'rsfa_woocommerce_title',
				),
				array(
					'title'   => __( 'Show audios at Product archives', 'really-simple-featured-audio' ),
					'desc'    => __( 'When toggled on, it shows set audios at product archives such as Shop and Product category etc.', 'really-simple-featured-audio' ),
					'id'      => 'product_archives_visibility',
					'default' => true,
					'type'    => 'checkbox',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'rsfa_woocommerce_title',
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
