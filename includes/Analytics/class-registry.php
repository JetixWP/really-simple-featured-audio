<?php
/**
 * Registry of featured audios.
 *
 * @package RSFA
 * @since   1.6.0
 */

namespace RSFA\Analytics;

defined( 'ABSPATH' ) || exit;

/**
 * One row per featured audio slot. Writers reach this through post meta, not REST.
 */
class Registry {

	const CONTEXT_FEATURED = 'featured';

	/**
	 * Read the final featured-audio meta and upsert one row.
	 *
	 * @param int $post_id Post ID.
	 * @return int Registry id, or 0 when there is nothing to store.
	 */
	public static function sync_featured( $post_id ) {
		$post_id = absint( $post_id );

		if ( ! $post_id || ! Install::tables_ready() ) {
			return 0;
		}

		$post = get_post( $post_id );

		if ( ! $post instanceof \WP_Post ) {
			return 0;
		}

		if ( 'revision' === $post->post_type || wp_is_post_autosave( $post_id ) ) {
			return 0;
		}

		$source = get_post_meta( $post_id, RSFA_SOURCE_META_KEY, true );
		$source = $source ? sanitize_key( $source ) : 'self';

		if ( ! in_array( $source, array( 'self', 'embed' ), true ) ) {
			$source = 'self';
		}

		$attachment_id = absint( get_post_meta( $post_id, RSFA_META_KEY, true ) );
		$embed_url     = esc_url_raw( (string) get_post_meta( $post_id, RSFA_EMBED_META_KEY, true ) );
		$active        = ( 'self' === $source && $attachment_id > 0 ) || ( 'embed' === $source && '' !== $embed_url );

		return self::upsert( $post_id, $post, $source, $active, $attachment_id, $embed_url );
	}

	/**
	 * Registry id for a featured slot, creating the row when it is missing.
	 *
	 * @param int $post_id Post ID.
	 * @return int
	 */
	public static function ensure_featured( $post_id ) {
		$id = self::find_id( self::CONTEXT_FEATURED, $post_id );

		if ( $id ) {
			return $id;
		}

		return self::sync_featured( $post_id );
	}

	/**
	 * Look up a registry id.
	 *
	 * @param string $context   Context, featured.
	 * @param int    $object_id Post ID.
	 * @return int
	 */
	public static function find_id( $context, $object_id ) {
		if ( ! Install::tables_ready() ) {
			return 0;
		}

		global $wpdb;

		$table = Install::audios_table();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Custom analytics table; name is prefixed and not user input.
		$id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE context = %s AND object_id = %d",
				sanitize_key( $context ),
				absint( $object_id )
			)
		);

		return absint( $id );
	}

	/**
	 * Mark a slot inactive. Existing daily counts stay.
	 *
	 * @param string $context   Context, featured.
	 * @param int    $object_id Post ID.
	 * @return void
	 */
	public static function mark_inactive( $context, $object_id ) {
		if ( ! Install::tables_ready() ) {
			return;
		}

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom analytics table.
		$wpdb->update(
			Install::audios_table(),
			array(
				'status'     => 'inactive',
				'updated_at' => current_time( 'mysql' ),
			),
			array(
				'context'   => sanitize_key( $context ),
				'object_id' => absint( $object_id ),
			),
			array( '%s', '%s' ),
			array( '%s', '%d' )
		);
	}

	/**
	 * Whether an active registry row exists.
	 *
	 * @param int $audio_id Registry id.
	 * @return bool
	 */
	public static function exists( $audio_id ) {
		$audio_id = absint( $audio_id );

		if ( ! $audio_id || ! Install::tables_ready() ) {
			return false;
		}

		global $wpdb;

		$table = Install::audios_table();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Custom analytics table; name is prefixed and not user input.
		$found = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE id = %d AND status = 'active'",
				$audio_id
			)
		);

		return absint( $found ) === $audio_id;
	}

	/**
	 * Insert or update one slot.
	 *
	 * @param int      $object_id     Post ID.
	 * @param \WP_Post $post          Post object.
	 * @param string   $source        self or embed.
	 * @param bool     $active        Whether the slot currently has audio.
	 * @param int      $attachment_id Self-hosted attachment id.
	 * @param string   $embed_url     Audio link.
	 * @return int
	 */
	private static function upsert( $object_id, $post, $source, $active, $attachment_id, $embed_url ) {
		$existing = self::find_id( self::CONTEXT_FEATURED, $object_id );

		if ( ! $active && ! $existing ) {
			return 0;
		}

		// Provider is where the audio plays from: the media library or a link.
		$provider = 'unknown';
		$asset    = '';

		if ( 'self' === $source && $attachment_id > 0 ) {
			$provider = 'self';
			$asset    = (string) $attachment_id;
		} elseif ( 'embed' === $source && '' !== $embed_url ) {
			$provider = 'link';
			$asset    = md5( $embed_url );
		}

		$now  = current_time( 'mysql' );
		$data = array(
			'object_type' => sanitize_key( $post->post_type ),
			'source'      => $source,
			'provider'    => $provider,
			'asset_key'   => substr( sanitize_text_field( $asset ), 0, 191 ),
			'title'       => sanitize_text_field( html_entity_decode( wp_strip_all_tags( get_the_title( $post ) ), ENT_QUOTES, 'UTF-8' ) ),
			'status'      => $active ? 'active' : 'inactive',
			'updated_at'  => $now,
		);

		global $wpdb;

		$table = Install::audios_table();

		if ( $existing ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom analytics table.
			$wpdb->update(
				$table,
				$data,
				array( 'id' => $existing ),
				array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' ),
				array( '%d' )
			);

			return $existing;
		}

		$data['context']    = self::CONTEXT_FEATURED;
		$data['object_id']  = $object_id;
		$data['created_at'] = $now;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom analytics table.
		$wpdb->insert(
			$table,
			$data,
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s' )
		);

		if ( $wpdb->insert_id ) {
			return absint( $wpdb->insert_id );
		}

		return self::find_id( self::CONTEXT_FEATURED, $object_id );
	}
}
