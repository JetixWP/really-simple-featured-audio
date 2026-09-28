<?php
/**
 * Remove analytics tables when the plugin is deleted.
 *
 * @package RSFA
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

$audios = $wpdb->prefix . 'rsfa_audios';
$stats  = $wpdb->prefix . 'rsfa_stats_daily';

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Uninstall drops this plugin's analytics tables. Names are prefixed constants.
$wpdb->query( "DROP TABLE IF EXISTS {$audios}" );
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Uninstall drops this plugin's analytics tables. Names are prefixed constants.
$wpdb->query( "DROP TABLE IF EXISTS {$stats}" );

delete_option( 'rsfa_analytics_backfill' );
wp_clear_scheduled_hook( 'rsfa_analytics_prune' );

$options = get_option( 'rsfa_options', array() );

if ( is_array( $options ) ) {
	unset( $options['analytics_enabled'], $options['analytics_ignore_editors'], $options['analytics_retention'] );
	update_option( 'rsfa_options', $options );
}
