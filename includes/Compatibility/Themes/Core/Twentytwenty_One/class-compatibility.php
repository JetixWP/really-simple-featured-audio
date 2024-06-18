<?php
/**
 * Twenty Twenty One theme compatibility handler.
 *
 * @package RSFA
 */

namespace RSFA\Compatibility\Themes\Core\Twentytwenty_One;

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

		$this->id    = 'twentytwentyone';

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		// Register styles.
		wp_register_style( 'rsfa-twentytwentyone', $this->get_current_dir_url() . 'Core/Twentytwenty_One/styles.css', array(), filemtime( $this->get_current_dir() . 'Core/Twentytwenty_One/styles.css' ) );

		// Enqueue styles.
		wp_enqueue_style( 'rsfa-twentytwentyone' );

		// Add generated CSS.
		wp_add_inline_style( 'rsfa-twentytwentyone', Plugin::get_instance()->frontend_provider->generate_dynamic_css() );
	}
}
