<?php
/**
 * Helper Functions
 *
 * @package BMLT_Enabled_Stats
 */

namespace BLST;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the main plugin instance
 *
 * @return Plugin|null
 */
function blst_get_plugin_instance() {
	global $blst_plugin;

	if ( ! isset( $blst_plugin ) ) {
		require_once BLST_PLUGIN_DIR . 'includes/class-cache-manager.php';
		require_once BLST_PLUGIN_DIR . 'includes/class-github-api.php';
		require_once BLST_PLUGIN_DIR . 'includes/class-wordpress-api.php';
		require_once BLST_PLUGIN_DIR . 'includes/class-bmlt-api.php';
		require_once BLST_PLUGIN_DIR . 'includes/class-scheduler.php';
		require_once BLST_PLUGIN_DIR . 'includes/class-shortcodes.php';
		require_once BLST_PLUGIN_DIR . 'includes/class-block.php';

		$blst_plugin = new Plugin();
		$blst_plugin->init();
	}

	return $blst_plugin;
}
