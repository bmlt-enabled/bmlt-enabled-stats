<?php
/**
 * WordPress.org Plugin API Handler Class
 *
 * @package BMLT_Enabled_Stats
 */

namespace BLST;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WordPress.org Plugin API handler
 */
class WordPress_API {

	/**
	 * WordPress.org Plugin API URL
	 */
	const API_URL = 'https://api.wordpress.org/plugins/info/1.2/';

	/**
	 * Cache manager instance
	 *
	 * @var Cache_Manager
	 */
	private $cache_manager;

	/**
	 * Plugin settings
	 *
	 * @var array
	 */
	private $settings;

	/**
	 * Constructor
	 *
	 * @param Cache_Manager $cache_manager Cache manager instance.
	 * @param array         $settings      Plugin settings.
	 */
	public function __construct( Cache_Manager $cache_manager, $settings ) {
		$this->cache_manager = $cache_manager;
		$this->settings      = $settings;
	}

	/**
	 * Get all WordPress.org plugin stats
	 *
	 * @param bool $force_refresh Whether to bypass cache.
	 * @return array
	 */
	public function get_stats( $force_refresh = false ) {
		// Try to get from cache first.
		if ( ! $force_refresh ) {
			$cached = $this->cache_manager->get( Cache_Manager::KEY_WPORG_PLUGINS );
			if ( false !== $cached ) {
				return $cached;
			}
		}

		// Fetch fresh data for all plugins.
		$plugins = $this->fetch_all_plugins();

		// Aggregate stats.
		$stats = $this->aggregate_stats( $plugins );

		// Cache the results.
		$this->cache_manager->set( Cache_Manager::KEY_WPORG_PLUGINS, $stats );

		return $stats;
	}

	/**
	 * Fetch plugins by author from WordPress.org API
	 *
	 * @return array List of plugin data.
	 */
	private function fetch_plugins_by_author() {
		// Build the API URL directly (don't use add_query_arg for bracket params).
		$url = self::API_URL . '?' . http_build_query(
			array(
				'action'  => 'query_plugins',
				'request' => array(
					'author'   => 'bmltenabled',
					'per_page' => 100,
					'fields'   => array(
						'active_installs' => true,
						'downloaded'      => true,
						'last_updated'    => true,
						'rating'          => true,
						'num_ratings'     => true,
					),
				),
			)
		);

		$response = wp_remote_get(
			$url,
			array(
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			error_log( 'BLST WordPress API Error: ' . $response->get_error_message() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			return array();
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $code ) {
			error_log( 'BLST WordPress API HTTP Error: ' . $code ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			return array();
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( JSON_ERROR_NONE !== json_last_error() ) {
			error_log( 'BLST WordPress API JSON Error: ' . json_last_error_msg() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			return array();
		}

		return $data['plugins'] ?? array();
	}

	/**
	 * Fetch data for all tracked plugins
	 *
	 * @return array
	 */
	private function fetch_all_plugins() {
		$plugins = array();

		// Get plugins directly from WordPress.org API by author.
		$api_plugins = $this->fetch_plugins_by_author();

		if ( empty( $api_plugins ) ) {
			return $plugins;
		}

		foreach ( $api_plugins as $plugin_data ) {
			// Convert object to array if needed.
			if ( is_object( $plugin_data ) ) {
				$plugin_data = (array) $plugin_data;
			}

			$slug = $plugin_data['slug'] ?? '';
			if ( empty( $slug ) ) {
				continue;
			}

			$plugins[ $slug ] = array(
				'slug'              => $slug,
				'name'              => $plugin_data['name'] ?? $slug,
				'display_name'      => $plugin_data['name'] ?? $slug,
				'version'           => $plugin_data['version'] ?? '',
				'author'            => $plugin_data['author'] ?? '',
				'requires'          => $plugin_data['requires'] ?? '',
				'tested'            => $plugin_data['tested'] ?? '',
				'requires_php'      => $plugin_data['requires_php'] ?? '',
				'rating'            => $plugin_data['rating'] ?? 0,
				'num_ratings'       => $plugin_data['num_ratings'] ?? 0,
				'active_installs'   => $plugin_data['active_installs'] ?? 0,
				'downloaded'        => $plugin_data['downloaded'] ?? 0,
				'last_updated'      => $plugin_data['last_updated'] ?? '',
				'added'             => $plugin_data['added'] ?? '',
				'homepage'          => $plugin_data['homepage'] ?? '',
				'short_description' => $plugin_data['short_description'] ?? '',
				'plugin_url'        => 'https://wordpress.org/plugins/' . $slug . '/',
			);
		}

		return $plugins;
	}

	/**
	 * Fetch plugin information from WordPress.org API
	 *
	 * @param string $slug Plugin slug.
	 * @return array|\WP_Error
	 */
	private function fetch_plugin_info( $slug ) {
		$url = add_query_arg(
			array(
				'action'  => 'plugin_information',
				'request' => array(
					'slug'   => $slug,
					'fields' => array(
						'description'       => false,
						'sections'          => false,
						'screenshots'       => false,
						'tags'              => false,
						'versions'          => false,
						'donate_link'       => false,
						'reviews'           => false,
						'banners'           => false,
						'icons'             => false,
						'active_installs'   => true,
						'downloaded'        => true,
						'last_updated'      => true,
						'rating'            => true,
						'num_ratings'       => true,
						'short_description' => true,
					),
				),
			),
			self::API_URL
		);

		// Use POST request for complex queries.
		$response = wp_remote_post(
			self::API_URL,
			array(
				'timeout' => 30,
				'body'    => array(
					'action'  => 'plugin_information',
					'request' => serialize( // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize -- Required by WordPress.org API.
						(object) array(
							'slug'   => $slug,
							'fields' => array(
								'description'       => false,
								'sections'          => false,
								'screenshots'       => false,
								'tags'              => false,
								'versions'          => false,
								'donate_link'       => false,
								'reviews'           => false,
								'banners'           => false,
								'icons'             => false,
								'active_installs'   => true,
								'downloaded'        => true,
								'last_updated'      => true,
								'rating'            => true,
								'num_ratings'       => true,
								'short_description' => true,
							),
						)
					),
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $code ) {
			return new \WP_Error(
				'wporg_api_error',
				sprintf( 'WordPress.org API returned status %d', $code )
			);
		}

		$body = wp_remote_retrieve_body( $response );
		$data = maybe_unserialize( $body );

		if ( ! is_object( $data ) && ! is_array( $data ) ) {
			$data = json_decode( $body, true );
		}

		if ( is_object( $data ) ) {
			$data = (array) $data;
		}

		return $data;
	}

	/**
	 * Aggregate stats from all plugins
	 *
	 * @param array $plugins Individual plugin data.
	 * @return array
	 */
	private function aggregate_stats( $plugins ) {
		$total_downloads       = 0;
		$total_active_installs = 0;
		$total_ratings         = 0;
		$rating_count          = 0;

		foreach ( $plugins as $plugin ) {
			$total_downloads       += $plugin['downloaded'] ?? 0;
			$total_active_installs += $plugin['active_installs'] ?? 0;

			if ( ! empty( $plugin['rating'] ) ) {
				$total_ratings += $plugin['rating'];
				++$rating_count;
			}
		}

		// Sort plugins by active installs.
		uasort(
			$plugins,
			function ( $a, $b ) {
				return ( $b['active_installs'] ?? 0 ) - ( $a['active_installs'] ?? 0 );
			}
		);

		return array(
			'total_downloads'       => $total_downloads,
			'total_active_installs' => $total_active_installs,
			'average_rating'        => $rating_count > 0 ? round( $total_ratings / $rating_count, 1 ) : 0,
			'plugin_count'          => count( $plugins ),
			'plugins'               => $plugins,
			'fetched_at'            => current_time( 'mysql' ),
		);
	}

	/**
	 * Get stats for a specific plugin
	 *
	 * @param string $slug Plugin slug.
	 * @return array|null
	 */
	public function get_plugin_stats( $slug ) {
		$stats = $this->get_stats();

		return $stats['plugins'][ $slug ] ?? null;
	}
}
