<?php
/**
 * Plugin Name: Really Simple Featured Audio
 * Plugin URI:  https://jetixwp.com/plugins/really-simple-featured-audio
 * Description: Sell beats, music samples, audiobooks, and podcasts with seamless audio previews all inside WooCommerce and WordPress.
 * Version:     1.4.0
 * Author:      JetixWP Plugins
 * Author URI:  https://jetixwp.com
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: really-simple-featured-audio
 * Domain Path: /languages/
 * Requires at least: 6.0
 * Requires PHP: 8.0
 *
 * @package RSFA
 */

defined( 'ABSPATH' ) || exit;

define( 'RSFA_VERSION', '1.4.0' );
define( 'RSFA_PLUGIN_FILE', __FILE__ );
define( 'RSFA_PLUGIN_URL', plugin_dir_url( RSFA_PLUGIN_FILE ) );
define( 'RSFA_PLUGIN_DIR', plugin_dir_path( RSFA_PLUGIN_FILE ) );
define( 'RSFA_PLUGIN_BASE', plugin_basename( RSFA_PLUGIN_FILE ) );
define( 'RSFA_PLUGIN_PRO_URL', 'https://jetixwp.com/plugins/really-simple-featured-audio' );

// Third party dependencies.
$vendor_file = __DIR__ . '/vendor/autoload.php';

if ( is_readable( $vendor_file ) ) {
	require_once $vendor_file;
}

if ( ! function_exists( 'rsfa_fs' ) ) {
	/**
	 * Create a helper function for easy SDK access.
	 */
	function rsfa_fs() {
		global $rsfa_fs;

		if ( ! function_exists( 'fs_dynamic_init' ) && file_exists( __DIR__ . '/vendor/freemius/wordpress-sdk/start.php' ) ) {
			require_once __DIR__ . '/vendor/freemius/wordpress-sdk/start.php';
		}

		if ( ! isset( $rsfa_fs ) && function_exists( 'fs_dynamic_init' ) ) {
			$rsfa_fs = fs_dynamic_init(
				array(
					'id'             => '15832',
					'slug'           => 'really-simple-featured-audio',
					'type'           => 'plugin',
					'public_key'     => 'pk_966ab730c951fb6730786c41ce9ad',
					'is_premium'     => false,
					'has_addons'     => true,
					'has_paid_plans' => false,
					'menu'           => array(
						'slug'       => 'rsfa-settings',
						'first-path' => 'admin.php?page=rsfa-settings',
						'support'    => false,
						'account'    => false,
						'contact'    => false,
						'parent'     => array(
							'slug' => 'jetixwp',
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
