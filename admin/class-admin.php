<?php
/**
 * Admin Settings Page Class
 *
 * @package BMLT_Enabled_Stats
 */

namespace BLST;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin settings page
 */
class Admin {

	/**
	 * Settings page slug
	 */
	const MENU_SLUG = 'bmlt-enabled-stats';

	/**
	 * Option name
	 */
	const OPTION_NAME = 'blst_settings';

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'wp_ajax_blst_refresh_stats', array( $this, 'ajax_refresh_stats' ) );
		add_action( 'wp_ajax_blst_clear_cache', array( $this, 'ajax_clear_cache' ) );
	}

	/**
	 * Add menu page
	 */
	public function add_menu_page() {
		add_options_page(
			__( 'BMLT Stats Settings', 'bmlt-enabled-stats' ),
			__( 'BMLT Stats', 'bmlt-enabled-stats' ),
			'manage_options',
			self::MENU_SLUG,
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register settings
	 */
	public function register_settings() {
		register_setting(
			'blst_settings_group',
			self::OPTION_NAME,
			array( $this, 'sanitize_settings' )
		);

		// API Settings Section.
		add_settings_section(
			'blst_api_section',
			__( 'API Settings', 'bmlt-enabled-stats' ),
			array( $this, 'render_api_section' ),
			self::MENU_SLUG
		);

		add_settings_field(
			'github_token',
			__( 'GitHub API Token', 'bmlt-enabled-stats' ),
			array( $this, 'render_github_token_field' ),
			self::MENU_SLUG,
			'blst_api_section'
		);

		// Cache Settings Section.
		add_settings_section(
			'blst_cache_section',
			__( 'Cache Settings', 'bmlt-enabled-stats' ),
			array( $this, 'render_cache_section' ),
			self::MENU_SLUG
		);

		add_settings_field(
			'cache_duration',
			__( 'Cache Duration', 'bmlt-enabled-stats' ),
			array( $this, 'render_cache_duration_field' ),
			self::MENU_SLUG,
			'blst_cache_section'
		);
	}

	/**
	 * Sanitize settings
	 *
	 * @param array $input Input values.
	 * @return array
	 */
	public function sanitize_settings( $input ) {
		$sanitized = array();

		// GitHub token.
		if ( isset( $input['github_token'] ) ) {
			$sanitized['github_token'] = sanitize_text_field( $input['github_token'] );
		}

		// Cache duration.
		if ( isset( $input['cache_duration'] ) ) {
			$sanitized['cache_duration'] = absint( $input['cache_duration'] );
			if ( $sanitized['cache_duration'] < HOUR_IN_SECONDS ) {
				$sanitized['cache_duration'] = HOUR_IN_SECONDS;
			}
		}

		// Display sections.
		if ( isset( $input['display_sections'] ) && is_array( $input['display_sections'] ) ) {
			$sanitized['display_sections'] = array_map( 'sanitize_key', $input['display_sections'] );
		}

		return $sanitized;
	}

	/**
	 * Enqueue admin assets
	 *
	 * @param string $hook Page hook.
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( 'settings_page_' . self::MENU_SLUG !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'blst-admin',
			BLST_PLUGIN_URL . 'admin/css/admin.css',
			array(),
			BLST_VERSION
		);

		wp_enqueue_script(
			'blst-admin',
			BLST_PLUGIN_URL . 'admin/js/admin.js',
			array( 'jquery' ),
			BLST_VERSION,
			true
		);

		wp_localize_script(
			'blst-admin',
			'blstAdmin',
			array(
				'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
				'refreshNonce' => wp_create_nonce( 'blst_refresh_stats' ),
				'clearNonce'   => wp_create_nonce( 'blst_clear_cache' ),
				'refreshing'   => __( 'Refreshing...', 'bmlt-enabled-stats' ),
				'refreshed'    => __( 'Stats refreshed successfully!', 'bmlt-enabled-stats' ),
				'clearing'     => __( 'Clearing...', 'bmlt-enabled-stats' ),
				'cleared'      => __( 'Cache cleared successfully!', 'bmlt-enabled-stats' ),
				'error'        => __( 'An error occurred. Please try again.', 'bmlt-enabled-stats' ),
			)
		);
	}

	/**
	 * Render settings page
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings     = get_option( self::OPTION_NAME, array() );
		$last_refresh = get_option( 'blst_last_refresh', '' );
		$next_cron    = wp_next_scheduled( 'blst_stats_refresh' );

		// Get cache status.
		$cache_manager = new Cache_Manager( $settings );
		$cache_status  = $cache_manager->get_cache_status();
		?>
		<div class="wrap blst-admin-wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<!-- Status Cards -->
			<div class="blst-admin-cards">
				<div class="blst-admin-card">
					<h3><?php esc_html_e( 'Last Refresh', 'bmlt-enabled-stats' ); ?></h3>
					<p class="blst-admin-value">
						<?php
						if ( $last_refresh ) {
							echo esc_html( human_time_diff( strtotime( $last_refresh ) ) . ' ' . __( 'ago', 'bmlt-enabled-stats' ) );
						} else {
							esc_html_e( 'Never', 'bmlt-enabled-stats' );
						}
						?>
					</p>
				</div>

				<div class="blst-admin-card">
					<h3><?php esc_html_e( 'Next Scheduled Refresh', 'bmlt-enabled-stats' ); ?></h3>
					<p class="blst-admin-value">
						<?php
						if ( $next_cron ) {
							echo esc_html( human_time_diff( $next_cron ) . ' ' . __( 'from now', 'bmlt-enabled-stats' ) );
						} else {
							esc_html_e( 'Not scheduled', 'bmlt-enabled-stats' );
						}
						?>
					</p>
				</div>

				<div class="blst-admin-card">
					<h3><?php esc_html_e( 'Cache Status', 'bmlt-enabled-stats' ); ?></h3>
					<ul class="blst-cache-list">
						<?php foreach ( $cache_status as $key => $status ) : ?>
						<li>
							<span class="blst-cache-key"><?php echo esc_html( str_replace( 'blst_', '', $key ) ); ?></span>
							<?php if ( $status['exists'] ) : ?>
							<span class="blst-cache-status blst-cached"><?php esc_html_e( 'Cached', 'bmlt-enabled-stats' ); ?></span>
							<?php else : ?>
							<span class="blst-cache-status blst-not-cached"><?php esc_html_e( 'Not cached', 'bmlt-enabled-stats' ); ?></span>
							<?php endif; ?>
						</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>

			<!-- Action Buttons -->
			<div class="blst-admin-actions">
				<button type="button" id="blst-refresh-stats" class="button button-primary">
					<?php esc_html_e( 'Refresh Stats Now', 'bmlt-enabled-stats' ); ?>
				</button>
				<button type="button" id="blst-clear-cache" class="button">
					<?php esc_html_e( 'Clear Cache', 'bmlt-enabled-stats' ); ?>
				</button>
				<span id="blst-action-status"></span>
			</div>

			<!-- Stats Preview -->
			<?php
			$plugin_instance = blst_get_plugin_instance();
			$github_stats    = $plugin_instance->github_api->get_stats();
			$wp_stats        = $plugin_instance->wordpress_api->get_stats();
			?>
			<div class="blst-admin-section">
				<h2><?php esc_html_e( 'Current Stats Preview', 'bmlt-enabled-stats' ); ?></h2>

				<h3><?php esc_html_e( 'GitHub Stats', 'bmlt-enabled-stats' ); ?></h3>
				<table class="widefat fixed">
					<tbody>
						<tr>
							<td><strong><?php esc_html_e( 'Total Repositories', 'bmlt-enabled-stats' ); ?></strong></td>
							<td><?php echo esc_html( $github_stats['total_repos'] ?? 0 ); ?></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Total Stars', 'bmlt-enabled-stats' ); ?></strong></td>
							<td><?php echo esc_html( $github_stats['total_stars'] ?? 0 ); ?></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Total Forks', 'bmlt-enabled-stats' ); ?></strong></td>
							<td><?php echo esc_html( $github_stats['total_forks'] ?? 0 ); ?></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Top Repos', 'bmlt-enabled-stats' ); ?></strong></td>
							<td>
								<?php
								$top_repos = $github_stats['top_repos'] ?? array();
								if ( ! empty( $top_repos ) ) {
									$repo_names = array_column( array_slice( $top_repos, 0, 5 ), 'name' );
									echo esc_html( implode( ', ', $repo_names ) );
								} else {
									esc_html_e( 'No data', 'bmlt-enabled-stats' );
								}
								?>
							</td>
						</tr>
					</tbody>
				</table>

				<h3 style="margin-top: 20px;"><?php esc_html_e( 'WordPress Plugin Stats', 'bmlt-enabled-stats' ); ?></h3>
				<?php
				// Debug: Show raw API response.
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					$debug_url = 'https://api.wordpress.org/plugins/info/1.2/?action=query_plugins&request[author]=bmltenabled&request[per_page]=10';
					echo '<p><small>Debug API URL: <a href="' . esc_url( $debug_url ) . '" target="_blank">' . esc_html( $debug_url ) . '</a></small></p>';
				}
				?>
				<table class="widefat fixed">
					<tbody>
						<tr>
							<td><strong><?php esc_html_e( 'Total Plugins Found', 'bmlt-enabled-stats' ); ?></strong></td>
							<td><?php echo esc_html( $wp_stats['plugin_count'] ?? 0 ); ?></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Total Downloads', 'bmlt-enabled-stats' ); ?></strong></td>
							<td><?php echo esc_html( number_format( $wp_stats['total_downloads'] ?? 0 ) ); ?></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Total Active Installs', 'bmlt-enabled-stats' ); ?></strong></td>
							<td><?php echo esc_html( number_format( $wp_stats['total_active_installs'] ?? 0 ) ); ?></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Plugins', 'bmlt-enabled-stats' ); ?></strong></td>
							<td>
								<?php
								$plugins = $wp_stats['plugins'] ?? array();
								if ( ! empty( $plugins ) ) {
									$plugin_names = array_column( $plugins, 'name' );
									echo esc_html( implode( ', ', $plugin_names ) );
								} else {
									esc_html_e( 'No plugins found', 'bmlt-enabled-stats' );
								}
								?>
							</td>
						</tr>
					</tbody>
				</table>

				<?php if ( ! empty( $plugins ) ) : ?>
				<h4 style="margin-top: 15px;"><?php esc_html_e( 'Plugin Details', 'bmlt-enabled-stats' ); ?></h4>
				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Plugin', 'bmlt-enabled-stats' ); ?></th>
							<th><?php esc_html_e( 'Active Installs', 'bmlt-enabled-stats' ); ?></th>
							<th><?php esc_html_e( 'Downloads', 'bmlt-enabled-stats' ); ?></th>
							<th><?php esc_html_e( 'Rating', 'bmlt-enabled-stats' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $plugins as $plugin ) : ?>
						<tr>
							<td><?php echo esc_html( $plugin['name'] ?? $plugin['slug'] ); ?></td>
							<td><?php echo esc_html( number_format( $plugin['active_installs'] ?? 0 ) ); ?>+</td>
							<td><?php echo esc_html( number_format( $plugin['downloaded'] ?? 0 ) ); ?></td>
							<td><?php echo esc_html( ( $plugin['rating'] ?? 0 ) . '%' ); ?></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<?php endif; ?>
			</div>

			<!-- Settings Form -->
			<form method="post" action="options.php">
				<?php
				settings_fields( 'blst_settings_group' );
				do_settings_sections( self::MENU_SLUG );
				submit_button();
				?>
			</form>

			<!-- Shortcode Reference -->
			<div class="blst-admin-section">
				<h2><?php esc_html_e( 'Shortcode Reference', 'bmlt-enabled-stats' ); ?></h2>
				<table class="widefat fixed">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Shortcode', 'bmlt-enabled-stats' ); ?></th>
							<th><?php esc_html_e( 'Description', 'bmlt-enabled-stats' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><code>[bmlt_stats]</code></td>
							<td><?php esc_html_e( 'Full stats display with all sections', 'bmlt-enabled-stats' ); ?></td>
						</tr>
						<tr>
							<td><code>[bmlt_stats type="summary"]</code></td>
							<td><?php esc_html_e( 'Summary hero cards only', 'bmlt-enabled-stats' ); ?></td>
						</tr>
						<tr>
							<td><code>[bmlt_stats type="github"]</code></td>
							<td><?php esc_html_e( 'GitHub stats only', 'bmlt-enabled-stats' ); ?></td>
						</tr>
						<tr>
							<td><code>[bmlt_stats type="wordpress"]</code></td>
							<td><?php esc_html_e( 'WordPress plugin stats only', 'bmlt-enabled-stats' ); ?></td>
						</tr>
						<tr>
							<td><code>[bmlt_stats type="meetings"]</code></td>
							<td><?php esc_html_e( 'BMLT meeting stats only', 'bmlt-enabled-stats' ); ?></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<?php
	}

	/**
	 * Render API section description
	 */
	public function render_api_section() {
		echo '<p>' . esc_html__( 'Configure API access for better rate limits.', 'bmlt-enabled-stats' ) . '</p>';
	}

	/**
	 * Render cache section description
	 */
	public function render_cache_section() {
		echo '<p>' . esc_html__( 'Configure how long data is cached before being refreshed.', 'bmlt-enabled-stats' ) . '</p>';
	}

	/**
	 * Render GitHub token field
	 */
	public function render_github_token_field() {
		$settings = get_option( self::OPTION_NAME, array() );
		$token    = $settings['github_token'] ?? '';
		?>
		<input type="password"
				name="<?php echo esc_attr( self::OPTION_NAME ); ?>[github_token]"
				value="<?php echo esc_attr( $token ); ?>"
				class="regular-text"
				autocomplete="new-password">
		<p class="description">
			<?php
			printf(
				/* translators: %s: GitHub settings URL */
				esc_html__( 'Optional. Increases API rate limit from 60 to 5000 requests per hour. Create a token at %s', 'bmlt-enabled-stats' ),
				'<a href="https://github.com/settings/tokens" target="_blank" rel="noopener noreferrer">GitHub Settings</a>'
			);
			?>
		</p>
		<?php
	}

	/**
	 * Render cache duration field
	 */
	public function render_cache_duration_field() {
		$settings = get_option( self::OPTION_NAME, array() );
		$duration = $settings['cache_duration'] ?? ( 4 * HOUR_IN_SECONDS );
		$hours    = $duration / HOUR_IN_SECONDS;
		?>
		<select name="<?php echo esc_attr( self::OPTION_NAME ); ?>[cache_duration]">
			<option value="<?php echo esc_attr( HOUR_IN_SECONDS ); ?>" <?php selected( $hours, 1 ); ?>>1 <?php esc_html_e( 'hour', 'bmlt-enabled-stats' ); ?></option>
			<option value="<?php echo esc_attr( 2 * HOUR_IN_SECONDS ); ?>" <?php selected( $hours, 2 ); ?>>2 <?php esc_html_e( 'hours', 'bmlt-enabled-stats' ); ?></option>
			<option value="<?php echo esc_attr( 4 * HOUR_IN_SECONDS ); ?>" <?php selected( $hours, 4 ); ?>>4 <?php esc_html_e( 'hours', 'bmlt-enabled-stats' ); ?> (<?php esc_html_e( 'recommended', 'bmlt-enabled-stats' ); ?>)</option>
			<option value="<?php echo esc_attr( 6 * HOUR_IN_SECONDS ); ?>" <?php selected( $hours, 6 ); ?>>6 <?php esc_html_e( 'hours', 'bmlt-enabled-stats' ); ?></option>
		</select>
		<p class="description">
			<?php esc_html_e( 'How long to cache GitHub and WordPress.org data. Stats refresh automatically every 4 hours.', 'bmlt-enabled-stats' ); ?>
		</p>
		<?php
	}

	/**
	 * AJAX handler for refreshing stats
	 */
	public function ajax_refresh_stats() {
		check_ajax_referer( 'blst_refresh_stats', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Permission denied.', 'bmlt-enabled-stats' ) );
		}

		// Get plugin instance and refresh.
		$settings      = get_option( self::OPTION_NAME, array() );
		$cache_manager = new Cache_Manager( $settings );
		$scheduler     = new Scheduler( blst_get_plugin_instance() );

		$result = $scheduler->trigger_manual_refresh();

		if ( $result ) {
			wp_send_json_success(
				array(
					'message'      => __( 'Stats refreshed successfully!', 'bmlt-enabled-stats' ),
					'last_refresh' => current_time( 'mysql' ),
				)
			);
		} else {
			wp_send_json_error( __( 'Failed to refresh stats.', 'bmlt-enabled-stats' ) );
		}
	}

	/**
	 * AJAX handler for clearing cache
	 */
	public function ajax_clear_cache() {
		check_ajax_referer( 'blst_clear_cache', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Permission denied.', 'bmlt-enabled-stats' ) );
		}

		$settings      = get_option( self::OPTION_NAME, array() );
		$cache_manager = new Cache_Manager( $settings );
		$cache_manager->clear_all();

		wp_send_json_success(
			array(
				'message' => __( 'Cache cleared successfully!', 'bmlt-enabled-stats' ),
			)
		);
	}
}

// Initialize admin.
new Admin();
