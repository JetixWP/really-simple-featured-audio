<?php
/**
 * Plugin Name: Really Simple Featured Audio
 * Plugin URI:  https://jetixwp.com/plugins/really-simple-featured-audio
 * Description: Adds support for Featured Audio to WordPress posts, pages & WooCommerce products.
 * Version:     0.1.0
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

define( 'RSFA_VERSION', '0.1.0' );
define( 'RSFA_PLUGIN_FILE', __FILE__ );
define( 'RSFA_PLUGIN_URL', plugin_dir_url( RSFA_PLUGIN_FILE ) );
define( 'RSFA_PLUGIN_DIR', plugin_dir_path( RSFA_PLUGIN_FILE ) );
define( 'RSFA_PLUGIN_BASE', plugin_basename( RSFA_PLUGIN_FILE ) );
define( 'RSFA_PLUGIN_PRO_URL', 'https://jetixwp.com/plugins/really-simple-featured-audio' );

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
