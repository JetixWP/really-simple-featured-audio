<?php
/**
 * Astra theme compatibility handler.
 *
 * @package RSFA
 */

namespace RSFA\Compatibility\Themes\ThirdParty\Astra;

use RSFA\Compatibility\Themes\Base_Compatibility;
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

		$this->id = 'astra';

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		// Register styles.
		wp_register_style( 'rsfa-astra', $this->get_current_dir_url() . 'ThirdParty/Astra/styles.css', array(), filemtime( $this->get_current_dir() . 'ThirdParty/Astra/styles.css' ) );

		// Enqueue styles.
		wp_enqueue_style( 'rsfa-astra' );

		// Add generated CSS.
		wp_add_inline_style( 'rsfa-astra', Plugin::get_instance()->frontend_provider->generate_dynamic_css() );
	}
}
