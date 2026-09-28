<?php
/**
 * Frontend analytics script.
 *
 * @package RSFA
 * @since   1.6.0
 */

namespace RSFA\Analytics;

defined( 'ABSPATH' ) || exit;

/**
 * Loads the tracker only when this request printed a player.
 */
class Tracker {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public static function hooks() {
		add_action( 'wp_footer', array( __CLASS__, 'maybe_enqueue' ), 1 );
	}

	/**
	 * Register the script and its endpoint.
	 *
	 * @return void
	 */
	public static function register_script() {
		if ( ! Stats::enabled() ) {
			return;
		}

		if ( wp_script_is( 'rsfa-analytics', 'registered' ) ) {
			return;
		}

		$path = RSFA_PLUGIN_DIR . 'assets/js/analytics.js';

		if ( ! file_exists( $path ) ) {
			return;
		}

		wp_register_script(
			'rsfa-analytics',
			RSFA_PLUGIN_URL . 'assets/js/analytics.js',
			array( 'rsfa-player' ),
			(string) filemtime( $path ),
			true
		);

		wp_localize_script(
			'rsfa-analytics',
			'rsfaAnalytics',
			apply_filters(
				'rsfa_analytics_script_data',
				array(
					'endpoint' => esc_url_raw( rest_url( REST_API::NAMESPACE . '/analytics/collect' ) ),
					'audience' => 0,
					'events'   => Stats::event_types(),
				)
			)
		);
	}

	/**
	 * Enqueue on the front when a player was stamped.
	 *
	 * @return void
	 */
	public static function maybe_enqueue() {
		if ( is_admin() || ! Stats::enabled() || ! Stamp::needed() ) {
			return;
		}

		// Editors' own visits are left out, so don't load the counter for them.
		if ( Stats::ignore_editors() && current_user_can( 'edit_posts' ) ) {
			return;
		}

		if ( isset( $_GET['elementor-preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Presence check only. The preview must not record counts.
			return;
		}

		self::register_script();
		wp_enqueue_script( 'rsfa-analytics' );
	}
}
