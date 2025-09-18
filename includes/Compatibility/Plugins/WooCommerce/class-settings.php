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

		if ( ! \RSFA\Plugin::get_instance()->has_pro_active() ) {
			$settings = array_merge(
				$settings,
				array(
					array(
						'type' => 'title',
						'id'   => 'rsfa_pro_woocommerce',
					),
					array(
						'title'   => __( 'Featured Audio Order', 'really-simple-featured-audio' ),
						'desc'    => __( 'Set audio order on single product page/gallery, available in the Pro version.', 'really-simple-featured-audio' ),
						'id'      => 'promo-global-woo-audio-order',
						'default' => 'first',
						'type'    => 'promo-select',
						'options' => array(
							'first'  => __( 'First (Default)', 'really-simple-featured-audio' ),
							'second' => __( 'Second', 'really-simple-featured-audio' ),
							'last'   => __( 'Last', 'really-simple-featured-audio' ),
						),
					),
					array(
						'type' => 'sectionend',
						'id'   => 'rsfa_pro_woocommerce',
					),
					array(
						'type' => 'title',
						'id'   => 'rsfa_pro_change_default_gallery_thumb',
					),
					array(
						'title'   => __( 'Default Gallery Thumb', 'really-simple-featured-audio' ),
						'desc'    => __( 'Set default product gallery thumb on single product page.', 'really-simple-featured-audio' ),
						'id'      => 'promo-default-woo-gallery-audio-thumb',
						'default' => RSFA_PLUGIN_URL . 'assets/images/audio_frame.png',
						'type'    => 'promo-media-image',
					),
					array(
						'type' => 'sectionend',
						'id'   => 'rsfa_pro_change_default_gallery_thumb',
					),
				)
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
