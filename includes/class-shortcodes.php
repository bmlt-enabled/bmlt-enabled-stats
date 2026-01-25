<?php
/**
 * Shortcodes Class
 *
 * @package BMLT_Enabled_Stats
 */

namespace BLST;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shortcodes registration and rendering
 */
class Shortcodes {

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
		$this->register_shortcodes();
	}

	/**
	 * Register all shortcodes
	 */
	private function register_shortcodes() {
		add_shortcode( 'bmlt_stats', array( $this, 'render_stats' ) );
	}

	/**
	 * Render stats shortcode
	 *
	 * @param array  $atts    Shortcode attributes.
	 * @param string $content Shortcode content (unused but required by WordPress).
	 * @return string
	 */
	public function render_stats( $atts, $content = '' ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		$atts = shortcode_atts(
			array(
				'type'  => 'full',
				'theme' => 'default',
			),
			$atts,
			'bmlt_stats'
		);

		// Get stats data based on type.
		$data = $this->get_stats_for_type( $atts['type'] );

		// Load appropriate template.
		$template = $this->get_template_file( $atts['type'] );

		if ( ! file_exists( $template ) ) {
			return '<!-- BMLT Stats: Template not found -->';
		}

		// Start output buffering.
		ob_start();

		// Make data and attributes available to template.
		$stats      = $data;
		$theme      = sanitize_html_class( $atts['theme'] );
		$stats_type = $atts['type'];

		// Include template.
		include $template;

		return ob_get_clean();
	}

	/**
	 * Get stats data for a specific type
	 *
	 * @param string $type Stats type.
	 * @return array
	 */
	private function get_stats_for_type( $type ) {
		switch ( $type ) {
			case 'summary':
				return $this->plugin->get_summary_stats();

			case 'github':
				return $this->plugin->github_api->get_stats();

			case 'WordPress':
				return $this->plugin->wordpress_api->get_stats();

			case 'meetings':
				return $this->plugin->bmlt_api->get_stats();

			case 'full':
			default:
				return $this->plugin->get_all_stats();
		}
	}

	/**
	 * Get template file path for a type
	 *
	 * @param string $type Stats type.
	 * @return string
	 */
	private function get_template_file( $type ) {
		$templates = array(
			'full'      => 'stats-full.php',
			'summary'   => 'stats-summary.php',
			'github'    => 'stats-github.php',
			'wordpress' => 'stats-wordpress.php',
			'meetings'  => 'stats-meetings.php',
		);

		$file = $templates[ $type ] ?? 'stats-full.php';

		// Allow themes to override templates.
		$theme_template = locate_template( 'bmlt-enabled-stats/' . $file );
		if ( $theme_template ) {
			return $theme_template;
		}

		return BLST_PLUGIN_DIR . 'templates/' . $file;
	}

	/**
	 * Format large numbers with K/M suffix
	 *
	 * @param int $number Number to format.
	 * @return string
	 */
	public static function format_number( $number ) {
		if ( $number >= 1000000 ) {
			return round( $number / 1000000, 1 ) . 'M';
		}
		if ( $number >= 1000 ) {
			return round( $number / 1000, 1 ) . 'K';
		}
		return number_format( $number );
	}

	/**
	 * Format a date for display
	 *
	 * @param string $date Date string.
	 * @return string
	 */
	public static function format_date( $date ) {
		if ( empty( $date ) ) {
			return __( 'Unknown', 'bmlt-enabled-stats' );
		}

		$timestamp = strtotime( $date );
		if ( ! $timestamp ) {
			return $date;
		}

		// If within last 30 days, show relative time.
		$diff = time() - $timestamp;
		if ( $diff < 30 * DAY_IN_SECONDS ) {
			return human_time_diff( $timestamp ) . ' ' . __( 'ago', 'bmlt-enabled-stats' );
		}

		return date_i18n( get_option( 'date_format' ), $timestamp );
	}

	/**
	 * Render star rating
	 *
	 * @param float $rating Rating out of 100.
	 * @return string
	 */
	public static function render_stars( $rating ) {
		$stars = round( ( $rating / 100 ) * 5, 1 );
		$full  = floor( $stars );
		$half  = ( $stars - $full ) >= 0.5 ? 1 : 0;
		$empty = 5 - $full - $half;

		$output  = '<span class="blst-stars">';
		$output .= str_repeat( '<span class="star full">&#9733;</span>', $full );
		if ( $half ) {
			$output .= '<span class="star half">&#9733;</span>';
		}
		$output .= str_repeat( '<span class="star empty">&#9734;</span>', $empty );
		$output .= '<span class="rating-number">(' . number_format( $stars, 1 ) . ')</span>';
		$output .= '</span>';

		return $output;
	}

	/**
	 * Get language color for badges
	 *
	 * @param string $language Programming language.
	 * @return string Hex color code.
	 */
	public static function get_language_color( $language ) {
		$colors = array(
			'PHP'        => '#4F5D95',
			'JavaScript' => '#f1e05a',
			'TypeScript' => '#2b7489',
			'HTML'       => '#e34c26',
			'CSS'        => '#563d7c',
			'Swift'      => '#F05138',
			'Kotlin'     => '#A97BFF',
			'Java'       => '#b07219',
			'Python'     => '#3572A5',
			'Ruby'       => '#701516',
			'Shell'      => '#89e051',
			'Dockerfile' => '#384d54',
		);

		return $colors[ $language ] ?? '#6e7681';
	}
}
