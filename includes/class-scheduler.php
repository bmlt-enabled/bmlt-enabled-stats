<?php
/**
 * Scheduler Class
 *
 * Handles WP-Cron scheduling for data refresh
 *
 * @package BMLT_Enabled_Stats
 */

namespace BLST;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Scheduler class for WP-Cron events
 */
class Scheduler {

	/**
	 * Plugin instance
	 *
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * Cron hook name
	 */
	const CRON_HOOK = 'blst_stats_refresh';

	/**
	 * Constructor
	 *
	 * @param Plugin $plugin Main plugin instance.
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Refresh all stats data
	 *
	 * Called by WP-Cron daily
	 *
	 * @return void
	 */
	public function refresh_all_stats() {
		// Clear existing cache.
		$this->plugin->cache_manager->clear_all();

		// Fetch fresh data from all APIs.
		$this->plugin->github_api->get_stats( true );
		$this->plugin->wordpress_api->get_stats( true );
		$this->plugin->bmlt_api->get_stats( true );

		// Log the refresh.
		$this->log_refresh();
	}

	/**
	 * Log refresh timestamp
	 *
	 * @return void
	 */
	private function log_refresh() {
		update_option( 'blst_last_refresh', current_time( 'mysql' ) );
	}

	/**
	 * Get last refresh timestamp
	 *
	 * @return string|false
	 */
	public function get_last_refresh() {
		return get_option( 'blst_last_refresh', false );
	}

	/**
	 * Get next scheduled refresh
	 *
	 * @return int|false Timestamp or false if not scheduled.
	 */
	public function get_next_scheduled() {
		return wp_next_scheduled( self::CRON_HOOK );
	}

	/**
	 * Manually trigger refresh
	 *
	 * @return bool
	 */
	public function trigger_manual_refresh() {
		try {
			$this->refresh_all_stats();
			return true;
		} catch ( \Exception $e ) {
			error_log( 'BLST Manual Refresh Error: ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			return false;
		}
	}

	/**
	 * Reschedule the cron event
	 *
	 * @return bool
	 */
	public function reschedule() {
		// Clear existing schedule.
		$timestamp = $this->get_next_scheduled();
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, self::CRON_HOOK );
		}

		// Schedule new event every 4 hours.
		return wp_schedule_event( time(), 'four_hours', self::CRON_HOOK );
	}

	/**
	 * Check if cron is properly scheduled
	 *
	 * @return bool
	 */
	public function is_scheduled() {
		return false !== $this->get_next_scheduled();
	}
}
