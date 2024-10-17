<?php
/**
 * Plugin Name: Really Simple Featured Audio
 * Plugin URI:  https://jetixwp.com/plugins/really-simple-featured-audio
 * Description: Adds support for Featured Audio to WordPress posts, pages & WooCommerce products.
 * Version:     0.7.0
 * Author:      JetixWP Plugins
 * Author URI:  https://jetixwp.com
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: really-simple-featured-audio
 * Domain Path: /languages/
 *
 * @package RSFA
 */

defined( 'ABSPATH' ) || exit;

define( 'RSFA_VERSION', '0.7.0' );
define( 'RSFA_PLUGIN_FILE', __FILE__ );
define( 'RSFA_PLUGIN_URL', plugin_dir_url( RSFA_PLUGIN_FILE ) );
define( 'RSFA_PLUGIN_DIR', plugin_dir_path( RSFA_PLUGIN_FILE ) );
define( 'RSFA_PLUGIN_BASE', plugin_basename( RSFA_PLUGIN_FILE ) );
define( 'RSFA_PLUGIN_PRO_URL', 'https://jetixwp.com/plugins/really-simple-featured-audio' );

if ( ! function_exists( 'rsfa_fs' ) ) {
	/**
	 * Create a helper function for easy SDK access.
	 */
	function rsfa_fs() {
		global $rsfa_fs;

		if ( ! isset( $rsfa_fs ) ) {
			// Include Freemius SDK.
			require_once __DIR__ . '/freemius/start.php';

			$rsfa_fs = fs_dynamic_init(
				array(
					'id'             => '15832',
					'slug'           => 'really-simple-featured-audio',
					'type'           => 'plugin',
					'public_key'     => 'pk_966ab730c951fb6730786c41ce9ad',
					'is_premium'     => false,
					'has_addons'     => false,
					'has_paid_plans' => false,
					'menu'           => array(
						'slug'       => 'rsfa-settings',
						'first-path' => 'options-general.php?page=rsfa-settings',
						'support'    => false,
						'parent'     => array(
							'slug' => 'options-general.php',
						),
					),
				)
			);
		}

		return $rsfa_fs;
	}

	// Init Freemius.
	rsfa_fs();
	// Signal that SDK was initiated.
	do_action( 'rsfa_fs_loaded' );
}

/**
 * Fire up plugin instance.
 */
add_action(
	'plugins_loaded',
	static function () {

		require_once RSFA_PLUGIN_DIR . 'includes/class-plugin.php';

		// Main instance.
		\RSFA\Plugin::get_instance();
	}
);
