<?php
/**
 * Analytics bootstrap.
 *
 * @package RSFA
 * @since   1.6.0
 */

namespace RSFA\Analytics;

defined( 'ABSPATH' ) || exit;

/**
 * Wires registry sync, collection, and the report.
 */
class Load {

	/**
	 * Instance.
	 *
	 * @var Load|null
	 */
	protected static $instance;

	/**
	 * Get the instance.
	 *
	 * @return Load
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		Sync::hooks();
		REST_API::hooks();
		Tracker::hooks();

		add_action( Install::CRON_HOOK, array( Stats::class, 'prune' ) );
		add_action( 'rsfa_update_options_analytics', array( Stats::class, 'prune' ) );
	}
}
