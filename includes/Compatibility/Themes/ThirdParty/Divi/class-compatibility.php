<?php
/**
 * Divi theme compatibility handler.
 *
 * @package RSFA
 */

namespace RSFA\Compatibility\Themes\ThirdParty\Divi;

use RSFA\Compatibility\Themes\Base_Compatibility;
use RSFA\Options;
use RSFA\Compatibility\Plugins\WooCommerce\Compatibility as BaseWooCompatibility;
use RSFA\Plugin;

/**
 * Class Compatibility
 *
 * @package RSFA
 */
class Compatibility extends Base_Compatibility {
	/**
	 * Class instance.
	 *
	 * @var $instance
	 */
	protected static $instance;

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();

		$this->id = 'divi';

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		// Register styles.
		wp_register_style( 'rsfa-divi', $this->get_current_dir_url() . 'ThirdParty/Divi/styles.css', array(), filemtime( $this->get_current_dir() . 'ThirdParty/Divi/styles.css' ) );

		// Enqueue styles.
		wp_enqueue_style( 'rsfa-divi' );

		// Add generated CSS.
		wp_add_inline_style( 'rsfa-divi', Plugin::get_instance()->frontend_provider->generate_dynamic_css() );
	}

	/**
	 * Overrides theme Woo templates.
	 *
	 * @return void
	 */
	public function override_woo_templates() {
	}
}
