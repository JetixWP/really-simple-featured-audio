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
 * Upgrade.
 */
class Upgrade extends Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'upgrade';
		$this->label = __( 'Upgrade', 'really-simple-featured-audio' );

		parent::__construct();

		add_action( 'rsfa_settings_' . $this->id, array( $this, 'upgrade_template' ) );
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
			'rsfa_upgrade_settings',
			array()
		);

		return apply_filters( 'rsfa_get_settings_' . $this->id, $settings );
	}

	/**
	 * Upgrade Tab Data.
	 */
	public function upgrade_template() {
		include RSFA_PLUGIN_DIR . 'includes/Settings/Views/html-admin-settings-upgrade.php';
	}

	/**
	 * Save settings.
	 */
	public function save() {
	}
}

return new Upgrade();
