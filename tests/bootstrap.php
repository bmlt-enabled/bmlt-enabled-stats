<?php
/**
 * PHPUnit Bootstrap File
 *
 * @package BMLT_Enabled_Stats
 */

// Composer autoloader (includes Brain Monkey).
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Define constants that WordPress would normally define.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__ ) . '/' );
}

if ( ! defined( 'WPINC' ) ) {
	define( 'WPINC', 'wp-includes' );
}

// Plugin constants.
if ( ! defined( 'BLST_VERSION' ) ) {
	define( 'BLST_VERSION', '1.0.0' );
}

if ( ! defined( 'BLST_PLUGIN_DIR' ) ) {
	define( 'BLST_PLUGIN_DIR', dirname( __DIR__ ) . '/' );
}

if ( ! defined( 'BLST_PLUGIN_URL' ) ) {
	define( 'BLST_PLUGIN_URL', 'http://example.com/wp-content/plugins/bmlt-enabled-stats/' );
}

if ( ! defined( 'BLST_PLUGIN_BASENAME' ) ) {
	define( 'BLST_PLUGIN_BASENAME', 'bmlt-enabled-stats/bmlt-enabled-stats.php' );
}

if ( ! defined( 'BLST_TRANSIENT_PREFIX' ) ) {
	define( 'BLST_TRANSIENT_PREFIX', 'blst_' );
}

if ( ! defined( 'BLST_DEFAULT_CACHE_DURATION' ) ) {
	define( 'BLST_DEFAULT_CACHE_DURATION', 4 * 3600 );
}

// WordPress time constants.
if ( ! defined( 'HOUR_IN_SECONDS' ) ) {
	define( 'HOUR_IN_SECONDS', 3600 );
}

if ( ! defined( 'DAY_IN_SECONDS' ) ) {
	define( 'DAY_IN_SECONDS', 86400 );
}

// Load all plugin class files once at bootstrap.
// This prevents "cannot redeclare class" errors when tests load files.
require_once BLST_PLUGIN_DIR . 'includes/class-cache-manager.php';
require_once BLST_PLUGIN_DIR . 'includes/class-github-api.php';
require_once BLST_PLUGIN_DIR . 'includes/class-wordpress-api.php';
require_once BLST_PLUGIN_DIR . 'includes/class-bmlt-api.php';
require_once BLST_PLUGIN_DIR . 'includes/class-scheduler.php';
require_once BLST_PLUGIN_DIR . 'includes/class-shortcodes.php';
require_once BLST_PLUGIN_DIR . 'includes/class-block.php';
require_once BLST_PLUGIN_DIR . 'includes/class-plugin.php';
require_once BLST_PLUGIN_DIR . 'includes/functions.php';

// Load our base test case.
require_once __DIR__ . '/TestCase.php';
