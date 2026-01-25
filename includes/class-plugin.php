<?php
/**
 * Main Plugin Class
 *
 * @package BMLT_Enabled_Stats
 */

namespace BLST;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Main Plugin orchestrator class
 */
class Plugin {

    /**
     * GitHub API handler instance
     *
     * @var GitHub_API
     */
    public $github_api;

    /**
     * WordPress.org API handler instance
     *
     * @var WordPress_API
     */
    public $wordpress_api;

    /**
     * BMLT API handler instance
     *
     * @var BMLT_API
     */
    public $bmlt_api;

    /**
     * Cache manager instance
     *
     * @var Cache_Manager
     */
    public $cache_manager;

    /**
     * Scheduler instance
     *
     * @var Scheduler
     */
    public $scheduler;

    /**
     * Shortcodes instance
     *
     * @var Shortcodes
     */
    public $shortcodes;

    /**
     * Block instance
     *
     * @var Block
     */
    public $block;

    /**
     * Plugin settings
     *
     * @var array
     */
    private $settings;

    /**
     * Initialize the plugin
     */
    public function init() {
        // Load settings
        $this->settings = $this->get_settings();

        // Load dependencies
        $this->load_dependencies();

        // Initialize components
        $this->init_components();

        // Register hooks
        $this->register_hooks();
    }

    /**
     * Get plugin settings with defaults
     *
     * @return array
     */
    public function get_settings() {
        $defaults = array(
            'github_token'         => '',
            'cache_duration'       => BLST_DEFAULT_CACHE_DURATION,
            'display_sections'     => array( 'summary', 'github', 'wordpress' ),
        );

        $settings = get_option( 'blst_settings', array() );
        return wp_parse_args( $settings, $defaults );
    }

    /**
     * Load required dependency files
     */
    private function load_dependencies() {
        require_once BLST_PLUGIN_DIR . 'includes/class-cache-manager.php';
        require_once BLST_PLUGIN_DIR . 'includes/class-github-api.php';
        require_once BLST_PLUGIN_DIR . 'includes/class-wordpress-api.php';
        require_once BLST_PLUGIN_DIR . 'includes/class-bmlt-api.php';
        require_once BLST_PLUGIN_DIR . 'includes/class-scheduler.php';
        require_once BLST_PLUGIN_DIR . 'includes/class-shortcodes.php';
        require_once BLST_PLUGIN_DIR . 'includes/class-block.php';
    }

    /**
     * Initialize all plugin components
     */
    private function init_components() {
        // Initialize cache manager first (other components depend on it)
        $this->cache_manager = new Cache_Manager( $this->settings );

        // Initialize API handlers
        $this->github_api    = new GitHub_API( $this->cache_manager, $this->settings );
        $this->wordpress_api = new WordPress_API( $this->cache_manager, $this->settings );
        $this->bmlt_api      = new BMLT_API( $this->cache_manager, $this->settings );

        // Initialize scheduler
        $this->scheduler = new Scheduler( $this );

        // Initialize shortcodes
        $this->shortcodes = new Shortcodes( $this );

        // Initialize block
        $this->block = new Block( $this );
    }

    /**
     * Register WordPress hooks
     */
    private function register_hooks() {
        // Enqueue frontend assets
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );

        // Schedule cron event (every 4 hours)
        add_action( 'blst_stats_refresh', array( $this->scheduler, 'refresh_all_stats' ) );
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        // Only load on pages with our shortcode or block
        global $post;
        if ( ! is_a( $post, 'WP_Post' ) ) {
            return;
        }

        $has_shortcode = has_shortcode( $post->post_content, 'bmlt_stats' );
        $has_block     = has_block( 'bmlt-enabled-stats/stats-display', $post );

        if ( ! $has_shortcode && ! $has_block ) {
            return;
        }

        // Enqueue Chart.js from CDN
        wp_enqueue_script(
            'chartjs',
            'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js',
            array(),
            '4.4.1',
            true
        );

        // Enqueue our JS
        wp_enqueue_script(
            'blst-stats-display',
            BLST_PLUGIN_URL . 'assets/js/stats-display.js',
            array( 'jquery', 'chartjs' ),
            BLST_VERSION,
            true
        );

        // Enqueue our CSS
        wp_enqueue_style(
            'blst-stats-display',
            BLST_PLUGIN_URL . 'assets/css/stats-display.css',
            array(),
            BLST_VERSION
        );
    }

    /**
     * Get all stats data
     *
     * @param bool $force_refresh Whether to bypass cache.
     * @return array
     */
    public function get_all_stats( $force_refresh = false ) {
        return array(
            'github'    => $this->github_api->get_stats( $force_refresh ),
            'wordpress' => $this->wordpress_api->get_stats( $force_refresh ),
            'bmlt'      => $this->bmlt_api->get_stats( $force_refresh ),
            'updated'   => current_time( 'mysql' ),
        );
    }

    /**
     * Get summary stats for hero cards
     *
     * @return array
     */
    public function get_summary_stats() {
        $github    = $this->github_api->get_stats();
        $wordpress = $this->wordpress_api->get_stats();
        $bmlt      = $this->bmlt_api->get_stats();

        return array(
            'total_github_stars'    => $github['total_stars'] ?? 0,
            'total_forks'           => $github['total_forks'] ?? 0,
            'total_downloads'       => $wordpress['total_downloads'] ?? 0,
            'total_active_installs' => $wordpress['total_active_installs'] ?? 0,
            'total_repos'           => $github['total_repos'] ?? 0,
        );
    }
}
