<?php
/**
 * Keep the audio registry in step with post meta.
 *
 * @package RSFA
 * @since   1.6.0
 */

namespace RSFA\Analytics;

defined( 'ABSPATH' ) || exit;

/**
 * Meta hooks batch into one sync per post at the end of the request.
 */
class Sync {

	/**
	 * Featured post IDs touched in this request.
	 *
	 * @var int[]
	 */
	private static $featured = array();

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public static function hooks() {
		add_action( 'added_post_meta', array( __CLASS__, 'catch_meta' ), 10, 3 );
		add_action( 'updated_post_meta', array( __CLASS__, 'catch_meta' ), 10, 3 );
		add_action( 'deleted_post_meta', array( __CLASS__, 'catch_meta' ), 10, 3 );
		add_action( 'shutdown', array( __CLASS__, 'flush' ) );
		add_action( 'wp_trash_post', array( __CLASS__, 'trash_post' ) );
		add_action( 'before_delete_post', array( __CLASS__, 'trash_post' ) );
		add_action( 'untrash_post', array( __CLASS__, 'untrash_post' ) );
		add_action( 'admin_init', array( __CLASS__, 'backfill' ), 20 );
	}

	/**
	 * Remember a post whose audio meta changed.
	 *
	 * @param int|int[] $meta_id   Meta id or ids. Unused.
	 * @param int       $object_id Post ID.
	 * @param string    $meta_key  Meta key.
	 * @return void
	 */
	public static function catch_meta( $meta_id, $object_id, $meta_key ) {
		unset( $meta_id );

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$object_id = absint( $object_id );
		$meta_key  = (string) $meta_key;

		if ( ! $object_id ) {
			return;
		}

		$featured_keys = array( RSFA_SOURCE_META_KEY, RSFA_META_KEY, RSFA_EMBED_META_KEY );

		if ( in_array( $meta_key, $featured_keys, true ) ) {
			self::$featured[ $object_id ] = $object_id;
		}
	}

	/**
	 * Sync each touched post once.
	 *
	 * @return void
	 */
	public static function flush() {
		foreach ( self::$featured as $post_id ) {
			Registry::sync_featured( $post_id );
		}

		self::$featured = array();
	}

	/**
	 * A trashed or deleted post leaves the registry row inactive.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public static function trash_post( $post_id ) {
		$post_id = absint( $post_id );
		$post    = get_post( $post_id );

		if ( ! $post instanceof \WP_Post ) {
			return;
		}

		Registry::mark_inactive( Registry::CONTEXT_FEATURED, $post_id );
	}

	/**
	 * Restoring a post re-reads its audio meta.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public static function untrash_post( $post_id ) {
		$post = get_post( absint( $post_id ) );

		if ( ! $post instanceof \WP_Post ) {
			return;
		}

		Registry::sync_featured( $post->ID );
	}

	/**
	 * Walk existing audios in small batches on admin requests.
	 *
	 * @return void
	 */
	public static function backfill() {
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) || wp_doing_ajax() ) {
			return;
		}

		if ( ! Install::tables_ready() ) {
			return;
		}

		$state = get_option( Install::BACKFILL_OPTION, array() );

		if ( ! is_array( $state ) || ! empty( $state['done'] ) ) {
			return;
		}

		if ( get_transient( 'rsfa_analytics_backfill_lock' ) ) {
			return;
		}

		set_transient( 'rsfa_analytics_backfill_lock', 1, 30 );

		$state = self::backfill_featured( $state );

		update_option( Install::BACKFILL_OPTION, $state, false );
		delete_transient( 'rsfa_analytics_backfill_lock' );
	}

	/**
	 * Sync a page of posts that already have featured-audio meta.
	 *
	 * @param array $state Queue state.
	 * @return array
	 */
	private static function backfill_featured( $state ) {
		global $wpdb;

		$last = isset( $state['featured_last_id'] ) ? absint( $state['featured_last_id'] ) : 0;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Backfill reads post meta ids. No core query returns this distinct set cheaply.
		$ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key IN (%s, %s) AND post_id > %d ORDER BY post_id ASC LIMIT 200",
				RSFA_META_KEY,
				RSFA_EMBED_META_KEY,
				$last
			)
		);

		if ( empty( $ids ) ) {
			$state['done'] = true;
			return $state;
		}

		foreach ( $ids as $post_id ) {
			$post_id = absint( $post_id );
			Registry::sync_featured( $post_id );
			$state['featured_last_id'] = $post_id;
		}

		return $state;
	}
}
