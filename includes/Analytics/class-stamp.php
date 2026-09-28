<?php
/**
 * Mark rendered players so the frontend script can count them.
 *
 * @package RSFA
 * @since   1.6.0
 */

namespace RSFA\Analytics;

defined( 'ABSPATH' ) || exit;

/**
 * Surface context and data attributes. Does not change the audio URL.
 */
class Stamp {

	/**
	 * Surface used by the next shortcode render.
	 *
	 * @var string
	 */
	private static $surface = 'shortcode';

	/**
	 * Whether this request printed a player.
	 *
	 * @var bool
	 */
	private static $needed = false;

	/**
	 * Swap the current surface and return the previous one.
	 *
	 * @param string $surface Surface key.
	 * @return string
	 */
	public static function swap_surface( $surface ) {
		$previous      = self::$surface;
		self::$surface = self::sanitize_surface( $surface );

		return $previous;
	}

	/**
	 * Current surface.
	 *
	 * @return string
	 */
	public static function current_surface() {
		return self::$surface;
	}

	/**
	 * Whether the tracker script should load.
	 *
	 * @return bool
	 */
	public static function needed() {
		return self::$needed;
	}

	/**
	 * Add analytics attributes to the first wrapper div.
	 *
	 * @param string $html     Player HTML.
	 * @param int    $post_id  Post ID.
	 * @param string $surface  Surface key.
	 * @param string $context  Context, featured.
	 * @return string
	 */
	public static function decorate( $html, $post_id, $surface, $context = 'featured' ) {
		if ( ! Stats::enabled() || ! is_string( $html ) || '' === $html ) {
			return $html;
		}

		if ( is_admin() || is_feed() || is_customize_preview() || isset( $_GET['elementor-preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Presence check only. The editor preview must not record counts.
			return $html;
		}

		$surface = self::sanitize_surface( $surface );

		if ( ! in_array( $surface, Stats::surfaces(), true ) ) {
			return $html;
		}

		$registry_id = Registry::ensure_featured( $post_id );

		if ( ! $registry_id ) {
			return $html;
		}

		$attrs = sprintf(
			' data-rsfa-analytics="1" data-rsfa-audio-id="%d" data-rsfa-surface="%s"',
			$registry_id,
			esc_attr( $surface )
		);

		$updated = preg_replace( '/<div\b/', '<div' . $attrs, $html, 1 );

		if ( ! is_string( $updated ) ) {
			return $html;
		}

		self::$needed = true;

		return $updated;
	}

	/**
	 * Keep a known surface, otherwise shortcode.
	 *
	 * @param string $surface Raw surface.
	 * @return string
	 */
	private static function sanitize_surface( $surface ) {
		$surface = sanitize_key( $surface );

		if ( in_array( $surface, Stats::surfaces(), true ) ) {
			return $surface;
		}

		return 'shortcode';
	}
}
