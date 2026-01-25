<?php
/**
 * Uninstall Script
 *
 * Fired when the plugin is uninstalled to clean up data.
 *
 * @package BMLT_Enabled_Stats
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Clean up plugin data on uninstall
 */

// Delete plugin options.
delete_option( 'blst_settings' );
delete_option( 'blst_last_refresh' );

// Delete all transients used by the plugin.
$transient_keys = array(
	'blst_github_org',
	'blst_github_repos',
	'blst_wporg_plugins',
	'blst_bmlt_aggregator',
);

foreach ( $transient_keys as $key ) {
	delete_transient( $key );
}

// Clear any scheduled cron events.
$timestamp = wp_next_scheduled( 'blst_daily_stats_refresh' );
if ( $timestamp ) {
	wp_unschedule_event( $timestamp, 'blst_daily_stats_refresh' );
}

// Clear all cron hooks for this plugin.
wp_clear_scheduled_hook( 'blst_daily_stats_refresh' );
