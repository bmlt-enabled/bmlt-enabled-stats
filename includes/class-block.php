<?php
/**
 * Gutenberg Block Registration Class
 *
 * @package BMLT_Enabled_Stats
 */

namespace BLST;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Block registration and rendering
 */
class Block {

	/**
	 * Plugin instance
	 *
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * Constructor
	 *
	 * @param Plugin $plugin Main plugin instance.
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
		$this->register_hooks();
	}

	/**
	 * Register WordPress hooks
	 */
	private function register_hooks() {
		add_action( 'init', array( $this, 'register_block' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
	}

	/**
	 * Register the block
	 */
	public function register_block() {
		// Check if block editor is available.
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		register_block_type(
			BLST_PLUGIN_DIR . 'block',
			array(
				'render_callback' => array( $this, 'render_block' ),
			)
		);
	}

	/**
	 * Enqueue block editor assets
	 */
	public function enqueue_editor_assets() {
		wp_enqueue_script(
			'blst-block-editor',
			BLST_PLUGIN_URL . 'block/index.js',
			array(
				'wp-blocks',
				'wp-element',
				'wp-editor',
				'wp-components',
				'wp-i18n',
				'wp-block-editor',
			),
			BLST_VERSION,
			true
		);

		wp_enqueue_style(
			'blst-block-editor',
			BLST_PLUGIN_URL . 'assets/css/stats-display.css',
			array(),
			BLST_VERSION
		);

		// Localize script with preview data.
		$preview_data = $this->get_preview_data();
		wp_localize_script(
			'blst-block-editor',
			'blstBlockData',
			array(
				'previewData' => $preview_data,
			)
		);
	}

	/**
	 * Render block on frontend
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	public function render_block( $attributes ) {
		$display_type = $attributes['displayType'] ?? 'full';
		$theme        = $attributes['theme'] ?? 'default';

		// Use the shortcode handler for rendering.
		return $this->plugin->shortcodes->render_stats(
			array(
				'type'  => $display_type,
				'theme' => $theme,
			)
		);
	}

	/**
	 * Get preview data for block editor
	 *
	 * @return array
	 */
	private function get_preview_data() {
		// Return cached stats summary for editor preview.
		$summary = $this->plugin->get_summary_stats();

		return array(
			'total_github_stars'    => $summary['total_github_stars'] ?? 150,
			'total_downloads'       => $summary['total_downloads'] ?? 500000,
			'total_active_installs' => $summary['total_active_installs'] ?? 10000,
			'total_repos'           => $summary['total_repos'] ?? 50,
		);
	}
}
