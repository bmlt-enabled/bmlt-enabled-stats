<?php
/**
 * Cache Manager Class
 *
 * Handles transient caching for API responses
 *
 * @package BMLT_Enabled_Stats
 */

namespace BLST;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cache Manager class using WordPress transients
 */
class Cache_Manager {

	/**
	 * Plugin settings
	 *
	 * @var array
	 */
	private $settings;

	/**
	 * Cache keys
	 */
	const KEY_GITHUB_ORG      = 'blst_github_org';
	const KEY_GITHUB_REPOS    = 'blst_github_repos';
	const KEY_WPORG_PLUGINS   = 'blst_wporg_plugins';
	const KEY_BMLT_AGGREGATOR = 'blst_bmlt_aggregator';

	/**
	 * Constructor
	 *
	 * @param array $settings Plugin settings.
	 */
	public function __construct( $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Get cached data
	 *
	 * @param string $key Cache key.
	 * @return mixed|false Cached data or false if not found/expired.
	 */
	public function get( $key ) {
		$data = get_transient( $key );

		if ( false === $data ) {
			return false;
		}

		return $data;
	}

	/**
	 * Set cached data
	 *
	 * @param string $key        Cache key.
	 * @param mixed  $data       Data to cache.
	 * @param int    $expiration Expiration in seconds (optional, uses default).
	 * @return bool
	 */
	public function set( $key, $data, $expiration = null ) {
		if ( null === $expiration ) {
			$expiration = $this->get_cache_duration( $key );
		}

		return set_transient( $key, $data, $expiration );
	}

	/**
	 * Delete cached data
	 *
	 * @param string $key Cache key.
	 * @return bool
	 */
	public function delete( $key ) {
		return delete_transient( $key );
	}

	/**
	 * Clear all plugin cache
	 *
	 * @return void
	 */
	public function clear_all() {
		$keys = array(
			self::KEY_GITHUB_ORG,
			self::KEY_GITHUB_REPOS,
			self::KEY_WPORG_PLUGINS,
			self::KEY_BMLT_AGGREGATOR,
		);

		foreach ( $keys as $key ) {
			$this->delete( $key );
		}
	}

	/**
	 * Get cache duration for a specific key
	 *
	 * @param string $key Cache key.
	 * @return int Duration in seconds.
	 */
	private function get_cache_duration( $key ) {
		// BMLT data has shorter cache duration.
		if ( self::KEY_BMLT_AGGREGATOR === $key ) {
			return isset( $this->settings['bmlt_cache_duration'] )
				? (int) $this->settings['bmlt_cache_duration']
				: 12 * HOUR_IN_SECONDS;
		}

		// Default cache duration for other data.
		return isset( $this->settings['cache_duration'] )
			? (int) $this->settings['cache_duration']
			: BLST_DEFAULT_CACHE_DURATION;
	}

	/**
	 * Get cache status information
	 *
	 * @return array
	 */
	public function get_cache_status() {
		global $wpdb;

		$keys = array(
			self::KEY_GITHUB_ORG,
			self::KEY_GITHUB_REPOS,
			self::KEY_WPORG_PLUGINS,
			self::KEY_BMLT_AGGREGATOR,
		);

		$status = array();

		foreach ( $keys as $key ) {
			$transient_key = '_transient_timeout_' . $key;
			$timeout       = get_option( $transient_key );
			$data          = $this->get( $key );

			$status[ $key ] = array(
				'exists'     => false !== $data,
				'expires'    => $timeout ? gmdate( 'Y-m-d H:i:s', $timeout ) : null,
				'expires_in' => $timeout ? max( 0, $timeout - time() ) : null,
			);
		}

		return $status;
	}

	/**
	 * Check if cache needs refresh
	 *
	 * @param string $key Cache key.
	 * @return bool
	 */
	public function needs_refresh( $key ) {
		return false === $this->get( $key );
	}
}
