<?php
/**
 * Get Pro Settings
 *
 * @package RSFA
 */

namespace RSFA\Settings;

defined( 'ABSPATH' ) || exit;

if ( class_exists( '\RSFA_Pro\Plugin' ) ) {
	return;
}

/**
 * GetPro.
 */
class GetPro extends Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'getpro';
		$this->label = __( 'Get PRO', 'really-simple-featured-audio' );

		parent::__construct();

		add_action( 'rsfa_settings_' . $this->id, array( $this, 'get_pro' ) );
	}

	/**
	 * Get settings array.
	 *
	 * @param string $current_section Current section ID.
	 *
	 * @return array
	 */
	public function get_settings( $current_section = '' ) {

		$settings = apply_filters(
			'rsfa_getpro_settings',
			array()
		);

		return apply_filters( 'rsfa_get_settings_' . $this->id, $settings );
	}

	/**
	 * Get Pro Tab Data.
	 */
	public function get_pro() {
		include RSFA_PLUGIN_DIR . 'includes/Settings/Views/html-admin-settings-getpro.php';
	}

	/**
	 * Save settings.
	 */
	public function save() {
	}
}

return new GetPro();
