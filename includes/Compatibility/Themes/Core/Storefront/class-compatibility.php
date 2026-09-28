<?php
/**
 * Storefront theme compatibility handler.
 *
 * @package RSFA
 */

namespace RSFA\Compatibility\Themes\Core\Storefront;

use RSFA\Compatibility\Themes\Base_Compatibility;
use RSFA\Options;
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

		$this->id = 'storefront';

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		// Register styles.
		wp_register_style( 'rsfa-storefront', $this->get_current_dir_url() . 'Core/Storefront/styles.css', array(), filemtime( $this->get_current_dir() . 'Core/Storefront/styles.css' ) );

		// Enqueue styles.
		wp_enqueue_style( 'rsfa-storefront' );

		// Add generated CSS.
		wp_add_inline_style( 'rsfa-storefront', Plugin::get_instance()->frontend_provider->generate_dynamic_css() );
	}
}
