<?php
/**
 * Full Stats Display Template
 *
 * @package BMLT_Enabled_Stats
 *
 * Variables available:
 * @var array  $stats      All stats data (github, WordPress, bmlt)
 * @var string $theme      Theme class (default, dark, compact)
 * @var string $stats_type Display type (full)
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use BLST\Shortcodes;

$github_stats = $stats['github'] ?? array();
$wp_stats     = $stats['wordpress'] ?? array();

// Calculate totals for summary.
$total_stars             = $github_stats['total_stars'] ?? 0;
$total_forks             = $github_stats['total_forks'] ?? 0;
$total_downloads         = $wp_stats['total_downloads'] ?? 0;
$total_active            = $wp_stats['total_active_installs'] ?? 0;
$total_repos             = $github_stats['total_repos'] ?? 0;
$total_release_downloads = $github_stats['total_release_downloads'] ?? 0;
?>

<div class="blst-stats-container blst-theme-<?php echo esc_attr( $theme ); ?>">

	<!-- Hero Summary Cards -->
	<section class="blst-section blst-summary-section">
		<div class="blst-hero-grid">
			<div class="blst-hero-card blst-card-stars">
				<div class="blst-card-icon">
					<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
				</div>
				<div class="blst-card-content">
					<span class="blst-stat-number" data-count="<?php echo esc_attr( $total_stars ); ?>">0</span>
					<span class="blst-stat-label"><?php esc_html_e( 'GitHub Stars', 'bmlt-enabled-stats' ); ?></span>
				</div>
			</div>

			<div class="blst-hero-card blst-card-downloads">
				<div class="blst-card-icon">
					<svg viewBox="0 0 24 24" fill="currentColor"><path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM17 13l-5 5-5-5h3V9h4v4h3z"/></svg>
				</div>
				<div class="blst-card-content">
					<span class="blst-stat-number" data-count="<?php echo esc_attr( $total_downloads ); ?>" data-suffix=""><?php echo esc_html( Shortcodes::format_number( $total_downloads ) ); ?></span>
					<span class="blst-stat-label"><?php esc_html_e( 'Plugin Downloads', 'bmlt-enabled-stats' ); ?></span>
				</div>
			</div>

			<div class="blst-hero-card blst-card-installs">
				<div class="blst-card-icon">
					<svg viewBox="0 0 24 24" fill="currentColor"><path d="M17 1.01L7 1c-1.1 0-2 .9-2 2v18c0 1.1.9 2 2 2h10c1.1 0 2-.9 2-2V3c0-1.1-.9-1.99-2-1.99zM17 19H7V5h10v14z"/></svg>
				</div>
				<div class="blst-card-content">
					<span class="blst-stat-number" data-count="<?php echo esc_attr( $total_active ); ?>" data-suffix="+"><?php echo esc_html( Shortcodes::format_number( $total_active ) ); ?>+</span>
					<span class="blst-stat-label"><?php esc_html_e( 'Active WordPress Sites', 'bmlt-enabled-stats' ); ?></span>
				</div>
			</div>

			<div class="blst-hero-card blst-card-repos">
				<div class="blst-card-icon">
					<svg viewBox="0 0 16 16" fill="currentColor"><path d="M2 2.5A2.5 2.5 0 014.5 0h8.75a.75.75 0 01.75.75v12.5a.75.75 0 01-.75.75h-2.5a.75.75 0 110-1.5h1.75v-2h-8a1 1 0 00-.714 1.7.75.75 0 01-1.072 1.05A2.495 2.495 0 012 11.5v-9zm10.5-1V9h-8c-.356 0-.694.074-1 .208V2.5a1 1 0 011-1h8zM5 12.25v3.25a.25.25 0 00.4.2l1.45-1.087a.25.25 0 01.3 0L8.6 15.7a.25.25 0 00.4-.2v-3.25a.25.25 0 00-.25-.25h-3.5a.25.25 0 00-.25.25z"/></svg>
				</div>
				<div class="blst-card-content">
					<span class="blst-stat-number" data-count="<?php echo esc_attr( $total_repos ); ?>">0</span>
					<span class="blst-stat-label"><?php esc_html_e( 'GitHub Repositories', 'bmlt-enabled-stats' ); ?></span>
				</div>
			</div>

			<div class="blst-hero-card blst-card-forks">
				<div class="blst-card-icon">
					<svg viewBox="0 0 16 16" fill="currentColor"><path d="M5 5.372v.878c0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75v-.878a2.25 2.25 0 111.5 0v.878a2.25 2.25 0 01-2.25 2.25h-1.5v2.128a2.251 2.251 0 11-1.5 0V8.5h-1.5A2.25 2.25 0 013.5 6.25v-.878a2.25 2.25 0 111.5 0zM5 3.25a.75.75 0 10-1.5 0 .75.75 0 001.5 0zm6.75.75a.75.75 0 100-1.5.75.75 0 000 1.5zm-3 8.75a.75.75 0 10-1.5 0 .75.75 0 001.5 0z"/></svg>
				</div>
				<div class="blst-card-content">
					<span class="blst-stat-number" data-count="<?php echo esc_attr( $total_forks ); ?>">0</span>
					<span class="blst-stat-label"><?php esc_html_e( 'GitHub Forks', 'bmlt-enabled-stats' ); ?></span>
				</div>
			</div>

			<?php if ( $total_release_downloads > 0 ) : ?>
			<div class="blst-hero-card blst-card-releases">
				<div class="blst-card-icon">
					<svg viewBox="0 0 16 16" fill="currentColor"><path d="M1 7.775V2.75C1 1.784 1.784 1 2.75 1h5.025c.464 0 .91.184 1.238.513l6.25 6.25a1.75 1.75 0 010 2.474l-5.026 5.026a1.75 1.75 0 01-2.474 0l-6.25-6.25A1.75 1.75 0 011 7.775zm1.5 0c0 .066.026.13.073.177l6.25 6.25a.25.25 0 00.354 0l5.025-5.025a.25.25 0 000-.354l-6.25-6.25a.25.25 0 00-.177-.073H2.75a.25.25 0 00-.25.25zM6 5a1 1 0 110 2 1 1 0 010-2z"/></svg>
				</div>
				<div class="blst-card-content">
					<span class="blst-stat-number" data-count="<?php echo esc_attr( $total_release_downloads ); ?>"><?php echo esc_html( Shortcodes::format_number( $total_release_downloads ) ); ?></span>
					<span class="blst-stat-label"><?php esc_html_e( 'Release Downloads', 'bmlt-enabled-stats' ); ?></span>
				</div>
			</div>
			<?php endif; ?>
		</div>
	</section>

	<!-- GitHub Stats Section -->
	<?php if ( ! empty( $github_stats['top_repos'] ) ) : ?>
	<section class="blst-section blst-github-section">
		<h2 class="blst-section-title">
			<svg viewBox="0 0 24 24" fill="currentColor" class="blst-title-icon"><path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/></svg>
			<?php esc_html_e( 'GitHub Stats', 'bmlt-enabled-stats' ); ?>
		</h2>

		<div class="blst-charts-row">
			<div class="blst-chart-container blst-chart-fixed-height">
				<h3 class="blst-chart-title"><?php esc_html_e( 'Stars by Repository', 'bmlt-enabled-stats' ); ?></h3>
				<canvas id="blst-github-stars-chart"></canvas>
			</div>
			<div class="blst-chart-container blst-chart-fixed-height">
				<h3 class="blst-chart-title"><?php esc_html_e( 'Forks by Repository', 'bmlt-enabled-stats' ); ?></h3>
				<canvas id="blst-github-forks-chart"></canvas>
			</div>
		</div>
		<div class="blst-charts-row">
			<?php if ( ! empty( $github_stats['release_downloads'] ) ) : ?>
			<div class="blst-chart-container blst-chart-fixed-height">
				<h3 class="blst-chart-title"><?php esc_html_e( 'Release Downloads by Repository', 'bmlt-enabled-stats' ); ?></h3>
				<canvas id="blst-github-downloads-chart"></canvas>
			</div>
			<?php endif; ?>
			<?php if ( ! empty( $github_stats['languages'] ) ) : ?>
			<div class="blst-chart-container blst-chart-fixed-height">
				<h3 class="blst-chart-title"><?php esc_html_e( 'Languages Used', 'bmlt-enabled-stats' ); ?></h3>
				<canvas id="blst-languages-chart"></canvas>
			</div>
			<?php endif; ?>
		</div>
	</section>
	<?php endif; ?>

	<!-- WordPress Plugins Section -->
	<?php if ( ! empty( $wp_stats['plugins'] ) ) : ?>
	<section class="blst-section blst-wordpress-section">
		<h2 class="blst-section-title">
			<svg viewBox="0 0 24 24" fill="currentColor" class="blst-title-icon"><path d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm0 19.5c-5.247 0-9.5-4.253-9.5-9.5S6.753 2.5 12 2.5s9.5 4.253 9.5 9.5-4.253 9.5-9.5 9.5zm-3.5-9.5l3.5-6.5 3.5 6.5h-2.5v5h-2v-5h-2.5z"/></svg>
			<?php esc_html_e( 'WordPress Plugins', 'bmlt-enabled-stats' ); ?>
		</h2>

		<!-- WordPress Charts -->
		<div class="blst-charts-row">
			<div class="blst-chart-container blst-chart-fixed-height">
				<h3 class="blst-chart-title"><?php esc_html_e( 'Active Installs by Plugin', 'bmlt-enabled-stats' ); ?></h3>
				<canvas id="blst-wp-installs-chart"></canvas>
			</div>
			<div class="blst-chart-container blst-chart-fixed-height">
				<h3 class="blst-chart-title"><?php esc_html_e( 'Downloads by Plugin', 'bmlt-enabled-stats' ); ?></h3>
				<canvas id="blst-wp-downloads-bar-chart"></canvas>
			</div>
		</div>

		<div class="blst-plugin-grid" style="margin-top: 2rem;">
			<?php foreach ( $wp_stats['plugins'] as $wp_plugin ) : ?>
			<div class="blst-plugin-card">
				<div class="blst-plugin-header">
					<h3 class="blst-plugin-name"><?php echo esc_html( $wp_plugin['display_name'] ); ?></h3>
					<?php if ( ! empty( $wp_plugin['rating'] ) ) : ?>
					<div class="blst-plugin-rating">
						<?php echo wp_kses_post( Shortcodes::render_stars( $wp_plugin['rating'] ) ); ?>
					</div>
					<?php endif; ?>
				</div>
				<div class="blst-plugin-stats">
					<div class="blst-plugin-stat">
						<span class="blst-stat-value"><?php echo esc_html( Shortcodes::format_number( $wp_plugin['active_installs'] ) ); ?>+</span>
						<span class="blst-stat-label"><?php esc_html_e( 'Active Installs', 'bmlt-enabled-stats' ); ?></span>
					</div>
					<div class="blst-plugin-stat">
						<span class="blst-stat-value"><?php echo esc_html( Shortcodes::format_number( $wp_plugin['downloaded'] ) ); ?></span>
						<span class="blst-stat-label"><?php esc_html_e( 'Downloads', 'bmlt-enabled-stats' ); ?></span>
					</div>
				</div>
				<div class="blst-plugin-bar">
					<div class="blst-bar-fill" style="width: <?php echo esc_attr( min( 100, ( $wp_plugin['active_installs'] / max( 1, $wp_stats['total_active_installs'] ) ) * 200 ) ); ?>%"></div>
				</div>
				<a href="<?php echo esc_url( $wp_plugin['plugin_url'] ); ?>" class="blst-plugin-link" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'View on WordPress.org', 'bmlt-enabled-stats' ); ?> &rarr;
				</a>
			</div>
			<?php endforeach; ?>
		</div>
	</section>
	<?php endif; ?>

	<!-- Footer -->
	<footer class="blst-footer">
		<p class="blst-updated">
			<?php
			printf(
				/* translators: %s: Last updated date */
				esc_html__( 'Data last updated: %s', 'bmlt-enabled-stats' ),
				esc_html( Shortcodes::format_date( $stats['updated'] ?? '' ) )
			);
			?>
		</p>
		<p class="blst-powered">
			<?php esc_html_e( 'Powered by', 'bmlt-enabled-stats' ); ?>
			<a href="https://bmlt.app" target="_blank" rel="noopener noreferrer">BMLT Enabled</a>
		</p>
	</footer>

</div>

<!-- Chart Data -->
<script type="application/json" id="blst-chart-data">
<?php
// Prepare forks data sorted by forks (with fallback for cached data).
$top_by_forks = $github_stats['top_repos_by_forks'] ?? array();
if ( empty( $top_by_forks ) && ! empty( $github_stats['top_repos'] ) ) {
	// Fallback: sort top_repos by forks for cached data without top_repos_by_forks.
	$top_by_forks = $github_stats['top_repos'];
	usort(
		$top_by_forks,
		function ( $a, $b ) {
			return ( $b['forks'] ?? 0 ) - ( $a['forks'] ?? 0 );
		}
	);
}
$top_by_forks    = array_slice( $top_by_forks, 0, 8 );
$release_dl_data = array_slice( $github_stats['release_downloads'] ?? array(), 0, 8 );

$languages_data = array_slice( $github_stats['languages'] ?? array(), 0, 10, true );

echo wp_json_encode(
	array(
		'github'    => array(
			'repos'          => array_column( array_slice( $github_stats['top_repos'] ?? array(), 0, 8 ), 'name' ),
			'stars'          => array_column( array_slice( $github_stats['top_repos'] ?? array(), 0, 8 ), 'stars' ),
			'forksRepos'     => array_column( $top_by_forks, 'name' ),
			'forks'          => array_column( $top_by_forks, 'forks' ),
			'downloadsRepos' => array_column( $release_dl_data, 'name' ),
			'downloads'      => array_column( $release_dl_data, 'downloads' ),
			'languages'      => array_keys( $languages_data ),
			'languageCounts' => array_values( $languages_data ),
		),
		'wordpress' => array(
			'plugins'   => array_column( $wp_stats['plugins'] ?? array(), 'display_name' ),
			'installs'  => array_column( $wp_stats['plugins'] ?? array(), 'active_installs' ),
			'downloads' => array_column( $wp_stats['plugins'] ?? array(), 'downloaded' ),
		),
	)
);
?>
</script>
