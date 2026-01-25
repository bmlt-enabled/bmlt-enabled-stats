<?php
/**
 * BMLT Aggregator (Tomato) API Handler Class
 *
 * @package BMLT_Enabled_Stats
 */

namespace BLST;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * BMLT Aggregator API handler
 */
class BMLT_API {

    /**
     * Tomato API base URL
     */
    const API_BASE = 'https://tomato.bmltenabled.org/main_server/api/v1';

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
     * Get all BMLT stats
     *
     * @param bool $force_refresh Whether to bypass cache.
     * @return array
     */
    public function get_stats( $force_refresh = false ) {
        // Try to get from cache first
        if ( ! $force_refresh ) {
            $cached = $this->cache_manager->get( Cache_Manager::KEY_BMLT_AGGREGATOR );
            if ( false !== $cached ) {
                return $cached;
            }
        }

        // Fetch fresh data
        $root_servers = $this->fetch_root_servers();

        if ( is_wp_error( $root_servers ) ) {
            return $this->get_empty_stats();
        }

        // Process the data
        $stats = $this->process_root_server_data( $root_servers );

        // Cache the results (shorter duration for BMLT data)
        $this->cache_manager->set(
            Cache_Manager::KEY_BMLT_AGGREGATOR,
            $stats,
            $this->settings['bmlt_cache_duration'] ?? ( 12 * HOUR_IN_SECONDS )
        );

        return $stats;
    }

    /**
     * Fetch root servers from Tomato API
     *
     * @return array|\WP_Error
     */
    private function fetch_root_servers() {
        $url = self::API_BASE . '/rootservers';

        $response = wp_remote_get(
            $url,
            array(
                'timeout'    => 30,
                'user-agent' => 'BMLT-Enabled-Stats-Plugin/' . BLST_VERSION,
                'headers'    => array(
                    'Accept' => 'application/json',
                ),
            )
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        if ( $code !== 200 ) {
            return new \WP_Error(
                'bmlt_api_error',
                sprintf( 'BMLT Aggregator API returned status %d', $code )
            );
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            return new \WP_Error(
                'bmlt_api_error',
                'Invalid JSON response from BMLT Aggregator'
            );
        }

        return $data;
    }

    /**
     * Process root server data into aggregated stats
     *
     * @param array $root_servers Raw root server data.
     * @return array
     */
    private function process_root_server_data( $root_servers ) {
        $total_meetings   = 0;
        $total_groups     = 0;
        $total_areas      = 0;
        $total_regions    = 0;
        $active_servers   = 0;
        $servers_by_status = array(
            'online'  => 0,
            'offline' => 0,
            'unknown' => 0,
        );
        $zones            = array();
        $processed_servers = array();

        foreach ( $root_servers as $server ) {
            // Count stats
            $num_meetings = $server['num_meetings'] ?? 0;
            $num_groups   = $server['num_groups'] ?? 0;
            $num_areas    = $server['num_areas'] ?? 0;
            $num_regions  = $server['num_regions'] ?? 0;

            $total_meetings += $num_meetings;
            $total_groups   += $num_groups;
            $total_areas    += $num_areas;
            $total_regions  += $num_regions;

            // Track server status
            $is_active = ! empty( $server['last_successful_import'] );
            if ( $is_active ) {
                $active_servers++;
                $servers_by_status['online']++;
            } else {
                $servers_by_status['offline']++;
            }

            // Track zones
            $zone = $server['zone'] ?? 'Unknown';
            if ( ! isset( $zones[ $zone ] ) ) {
                $zones[ $zone ] = array(
                    'name'     => $zone,
                    'servers'  => 0,
                    'meetings' => 0,
                );
            }
            $zones[ $zone ]['servers']++;
            $zones[ $zone ]['meetings'] += $num_meetings;

            // Store processed server data
            $processed_servers[] = array(
                'id'              => $server['id'] ?? 0,
                'name'            => $server['name'] ?? '',
                'root_server_url' => $server['root_server_url'] ?? '',
                'num_meetings'    => $num_meetings,
                'num_groups'      => $num_groups,
                'num_areas'       => $num_areas,
                'num_regions'     => $num_regions,
                'zone'            => $zone,
                'is_active'       => $is_active,
                'last_import'     => $server['last_successful_import'] ?? null,
                'server_info'     => $server['server_info'] ?? '',
            );
        }

        // Sort servers by meeting count
        usort( $processed_servers, function( $a, $b ) {
            return $b['num_meetings'] - $a['num_meetings'];
        } );

        // Sort zones by meeting count
        uasort( $zones, function( $a, $b ) {
            return $b['meetings'] - $a['meetings'];
        } );

        // Get top servers
        $top_servers = array_slice( $processed_servers, 0, 10 );

        return array(
            'total_meetings'     => $total_meetings,
            'total_groups'       => $total_groups,
            'total_areas'        => $total_areas,
            'total_regions'      => $total_regions,
            'root_servers'       => count( $root_servers ),
            'active_servers'     => $active_servers,
            'servers_by_status'  => $servers_by_status,
            'zones'              => array_values( $zones ),
            'zone_count'         => count( $zones ),
            'top_servers'        => $top_servers,
            'all_servers'        => $processed_servers,
            'fetched_at'         => current_time( 'mysql' ),
        );
    }

    /**
     * Get empty stats structure
     *
     * @return array
     */
    private function get_empty_stats() {
        return array(
            'total_meetings'     => 0,
            'total_groups'       => 0,
            'total_areas'        => 0,
            'total_regions'      => 0,
            'root_servers'       => 0,
            'active_servers'     => 0,
            'servers_by_status'  => array(
                'online'  => 0,
                'offline' => 0,
            ),
            'zones'              => array(),
            'zone_count'         => 0,
            'top_servers'        => array(),
            'all_servers'        => array(),
            'fetched_at'         => null,
            'error'              => true,
        );
    }

    /**
     * Get summary stats only
     *
     * @return array
     */
    public function get_summary() {
        $stats = $this->get_stats();

        return array(
            'total_meetings' => $stats['total_meetings'],
            'root_servers'   => $stats['root_servers'],
            'total_groups'   => $stats['total_groups'],
            'zones'          => $stats['zone_count'],
        );
    }

    /**
     * Check if the API is reachable
     *
     * @return bool
     */
    public function is_api_available() {
        $response = wp_remote_head(
            self::API_BASE . '/rootservers',
            array( 'timeout' => 10 )
        );

        if ( is_wp_error( $response ) ) {
            return false;
        }

        $code = wp_remote_retrieve_response_code( $response );
        return $code >= 200 && $code < 300;
    }
}
