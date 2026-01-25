<?php
/**
 * Plugin Name: BMLT Enabled Stats
 * Plugin URI: https://github.com/bmlt-enabled/bmlt-enabled-stats
 * Description: Displays engaging, visual statistics about all BMLT-enabled projects in one dashboard.
 * Version: 1.0.0
 * Author: BMLT Enabled
 * Author URI: https://bmlt.app
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: bmlt-enabled-stats
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 *
 * @package BMLT_Enabled_Stats
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Plugin version.
define( 'BLST_VERSION', '1.0.0' );

// Plugin directory path.
define( 'BLST_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// Plugin URL.
define( 'BLST_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Plugin basename.
define( 'BLST_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Transient prefix.
define( 'BLST_TRANSIENT_PREFIX', 'blst_' );

// Default cache duration (4 hours in seconds).
define( 'BLST_DEFAULT_CACHE_DURATION', 4 * HOUR_IN_SECONDS );

/**
 * Add custom cron schedule for 4 hours.
 *
 * @param array $schedules Existing cron schedules.
 * @return array Modified schedules.
 */
function blst_add_cron_interval( $schedules ) {
	$schedules['four_hours'] = array(
		'interval' => 4 * HOUR_IN_SECONDS,
		'display'  => __( 'Every 4 Hours', 'bmlt-enabled-stats' ),
	);
	return $schedules;
}
add_filter( 'cron_schedules', 'blst_add_cron_interval' );

/**
 * Activation hook
 */
function blst_activate() {
	// Schedule stats refresh every 4 hours.
	if ( ! wp_next_scheduled( 'blst_stats_refresh' ) ) {
		wp_schedule_event( time(), 'four_hours', 'blst_stats_refresh' );
	}

	// Set default options.
	$default_options = array(
		'github_token'     => '',
		'cache_duration'   => BLST_DEFAULT_CACHE_DURATION,
		'display_sections' => array( 'summary', 'github', 'wordpress' ),
	);

	if ( false === get_option( 'blst_settings' ) ) {
		add_option( 'blst_settings', $default_options );
	}

	// Flush rewrite rules.
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'blst_activate' );

/**
 * Deactivation hook
 */
function blst_deactivate() {
	// Clear scheduled event.
	$timestamp = wp_next_scheduled( 'blst_stats_refresh' );
	if ( $timestamp ) {
		wp_unschedule_event( $timestamp, 'blst_stats_refresh' );
	}

	// Flush rewrite rules.
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'blst_deactivate' );

/**
 * Autoloader for plugin classes
 */
spl_autoload_register(
	function ( $class_name ) {
		// Plugin namespace prefix.
		$prefix = 'BLST\\';

		// Base directory for the namespace prefix.
		$base_dir = BLST_PLUGIN_DIR . 'includes/';

		// Does the class use the namespace prefix?
		$len = strlen( $prefix );
		if ( 0 !== strncmp( $prefix, $class_name, $len ) ) {
				return;
		}

		// Get the relative class name.
		$relative_class = substr( $class_name, $len );

		// Replace namespace separators with directory separators,
		// replace underscores with hyphens, lowercase, and add prefix/suffix.
		$file = $base_dir . 'class-' . strtolower( str_replace( array( '\\', '_' ), array( '/', '-' ), $relative_class ) ) . '.php';

		// If the file exists, require it.
		if ( file_exists( $file ) ) {
			require $file;
		}
	}
);

/**
 * Load required files
 */
require_once BLST_PLUGIN_DIR . 'includes/class-plugin.php';

/**
 * Initialize the plugin
 */
function blst_init() {
	$plugin = new BLST\Plugin();
	$plugin->init();
}
add_action( 'plugins_loaded', 'blst_init' );

/**
 * Load helper functions
 */
require_once BLST_PLUGIN_DIR . 'includes/functions.php';

/**
 * Load admin class if in admin
 */
if ( is_admin() ) {
	require_once BLST_PLUGIN_DIR . 'admin/class-admin.php';
}
