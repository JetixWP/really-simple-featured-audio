<?php
/**
 * Migrations handler.
 *
 * @package RSFA
 */

namespace RSFA;

defined( 'ABSPATH' ) || exit;

/**
 * Class Updater
 */
class Updater {

	/**
	 * The slug of db option.
	 *
	 * @var string
	 */
	const OPTION = 'rsfa_db_version';

	/**
	 * The slug of db option.
	 *
	 * @var string
	 */
	const PREVIOUS_OPTION = 'rsfa_previous_db_version';

	/**
	 * Hooked into admin_init and walks through an array of upgrade methods.
	 *
	 * @return void
	 */
	public function init() {
		$routines = array(
			'1.6.0' => 'upgrade_1_6_0',
		);

		$version = get_option( self::OPTION, '0.1.0' );

		if ( version_compare( RSFA_VERSION, $version, '=' ) ) {
			return;
		}

		foreach ( $routines as $routine_version => $routine ) {
			$this->run_upgrade_routine( $routine, $routine_version, $version );
		}
		$this->finish_up( $version );
	}

	/**
	 * Runs the upgrade routine.
	 *
	 * @param string $routine The method to call.
	 * @param string $version The new version.
	 * @param string $current_version The current set version.
	 *
	 * @return void
	 */
	protected function run_upgrade_routine( $routine, $version, $current_version ) {
		if ( version_compare( $current_version, $version, '<' ) ) {
			$this->$routine( $current_version );
		}
	}

	/**
	 * Runs the needed cleanup after an update, setting the DB version to latest version, flushing caches etc.
	 *
	 * @param string $previous_version The previous version.
	 *
	 * @return void
	 */
	protected function finish_up( $previous_version ) {
		update_option( self::PREVIOUS_OPTION, $previous_version );
		update_option( self::OPTION, RSFA_VERSION );
	}

	/**
	 * Upgrade to 1.6.0.
	 *
	 * Creates the analytics tables. Existing sites start with analytics off,
	 * so nothing is recorded until an admin turns it on; new sites keep the
	 * default (on). Safe to run more than once.
	 *
	 * @param string $current_version Stored version before this update.
	 *
	 * @return void
	 */
	protected function upgrade_1_6_0( $current_version ) {
		\RSFA\Analytics\Install::activate();

		$options = Options::get_instance();

		if ( $options->has( 'analytics_enabled' ) ) {
			return;
		}

		if ( $this->is_existing_install( $current_version ) ) {
			$options->set( 'analytics_enabled', false );
		}
	}

	/**
	 * Whether this site used the plugin before this update.
	 *
	 * @param string $current_version Stored version before this update.
	 *
	 * @return bool
	 */
	protected function is_existing_install( $current_version ) {
		if ( false !== get_option( self::OPTION, false ) && '0.1.0' !== $current_version ) {
			return true;
		}

		global $wpdb;

		// Sites from before the version option existed still have audio meta.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- One-time check during the upgrade.
		$found = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT meta_id FROM {$wpdb->postmeta} WHERE meta_key IN (%s, %s) LIMIT 1",
				RSFA_META_KEY,
				RSFA_EMBED_META_KEY
			)
		);

		return ! empty( $found );
	}
}
