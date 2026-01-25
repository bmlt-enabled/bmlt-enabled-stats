<?php
/**
 * WordPress Plugins Stats Display Template
 *
 * @package BMLT_Enabled_Stats
 *
 * Variables available:
 * @var array  $stats      WordPress plugin stats data
 * @var string $theme      Theme class
 * @var string $stats_type Display type (WordPress)
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use BLST\Shortcodes;
?>

<div class="blst-stats-container blst-wordpress-only blst-theme-<?php echo esc_attr( $theme ); ?>">

	<!-- WordPress Summary -->
	<div class="blst-hero-grid blst-hero-small">
		<div class="blst-hero-card blst-card-plugins">
			<div class="blst-card-icon">
				<svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.5 11H19V7c0-1.1-.9-2-2-2h-4V3.5C13 2.12 11.88 1 10.5 1S8 2.12 8 3.5V5H4c-1.1 0-1.99.9-1.99 2v3.8H3.5c1.49 0 2.7 1.21 2.7 2.7s-1.21 2.7-2.7 2.7H2V20c0 1.1.9 2 2 2h3.8v-1.5c0-1.49 1.21-2.7 2.7-2.7 1.49 0 2.7 1.21 2.7 2.7V22H17c1.1 0 2-.9 2-2v-4h1.5c1.38 0 2.5-1.12 2.5-2.5S21.88 11 20.5 11z"/></svg>
			</div>
			<div class="blst-card-content">
				<span class="blst-stat-number" data-count="<?php echo esc_attr( $stats['plugin_count'] ?? 0 ); ?>">0</span>
				<span class="blst-stat-label"><?php esc_html_e( 'Plugins', 'bmlt-enabled-stats' ); ?></span>
			</div>
		</div>

		<div class="blst-hero-card blst-card-downloads">
			<div class="blst-card-icon">
				<svg viewBox="0 0 24 24" fill="currentColor"><path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM17 13l-5 5-5-5h3V9h4v4h3z"/></svg>
			</div>
			<div class="blst-card-content">
				<span class="blst-stat-number" data-count="<?php echo esc_attr( $stats['total_downloads'] ?? 0 ); ?>"><?php echo esc_html( Shortcodes::format_number( $stats['total_downloads'] ?? 0 ) ); ?></span>
				<span class="blst-stat-label"><?php esc_html_e( 'Total Downloads', 'bmlt-enabled-stats' ); ?></span>
			</div>
		</div>

		<div class="blst-hero-card blst-card-installs">
			<div class="blst-card-icon">
				<svg viewBox="0 0 24 24" fill="currentColor"><path d="M17 1.01L7 1c-1.1 0-2 .9-2 2v18c0 1.1.9 2 2 2h10c1.1 0 2-.9 2-2V3c0-1.1-.9-1.99-2-1.99zM17 19H7V5h10v14z"/></svg>
			</div>
			<div class="blst-card-content">
				<span class="blst-stat-number" data-count="<?php echo esc_attr( $stats['total_active_installs'] ?? 0 ); ?>" data-suffix="+"><?php echo esc_html( Shortcodes::format_number( $stats['total_active_installs'] ?? 0 ) ); ?>+</span>
				<span class="blst-stat-label"><?php esc_html_e( 'Active Installs', 'bmlt-enabled-stats' ); ?></span>
			</div>
		</div>

		<div class="blst-hero-card blst-card-rating">
			<div class="blst-card-icon">
				<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
			</div>
			<div class="blst-card-content">
				<span class="blst-stat-number"><?php echo esc_html( number_format( ( $stats['average_rating'] ?? 0 ) / 20, 1 ) ); ?></span>
				<span class="blst-stat-label"><?php esc_html_e( 'Avg Rating', 'bmlt-enabled-stats' ); ?></span>
			</div>
		</div>
	</div>

	<!-- Plugin Cards -->
	<?php if ( ! empty( $stats['plugins'] ) ) : ?>
	<section class="blst-section blst-wordpress-section">
		<h2 class="blst-section-title">
			<?php esc_html_e( 'BMLT WordPress Plugins', 'bmlt-enabled-stats' ); ?>
		</h2>

		<div class="blst-plugin-grid">
			<?php foreach ( $stats['plugins'] as $wp_plugin ) : ?>
			<div class="blst-plugin-card">
				<div class="blst-plugin-header">
					<h3 class="blst-plugin-name"><?php echo esc_html( $wp_plugin['display_name'] ); ?></h3>
					<?php if ( ! empty( $wp_plugin['version'] ) ) : ?>
					<span class="blst-plugin-version">v<?php echo esc_html( $wp_plugin['version'] ); ?></span>
					<?php endif; ?>
				</div>

				<?php if ( ! empty( $wp_plugin['short_description'] ) ) : ?>
				<p class="blst-plugin-desc"><?php echo esc_html( wp_trim_words( $wp_plugin['short_description'], 12 ) ); ?></p>
				<?php endif; ?>

				<?php if ( ! empty( $wp_plugin['rating'] ) ) : ?>
				<div class="blst-plugin-rating">
					<?php echo wp_kses_post( Shortcodes::render_stars( $wp_plugin['rating'] ) ); ?>
				</div>
				<?php endif; ?>

				<div class="blst-plugin-stats">
					<div class="blst-plugin-stat">
						<span class="blst-stat-value"><?php echo esc_html( Shortcodes::format_number( $wp_plugin['active_installs'] ?? 0 ) ); ?>+</span>
						<span class="blst-stat-label"><?php esc_html_e( 'Active', 'bmlt-enabled-stats' ); ?></span>
					</div>
					<div class="blst-plugin-stat">
						<span class="blst-stat-value"><?php echo esc_html( Shortcodes::format_number( $wp_plugin['downloaded'] ?? 0 ) ); ?></span>
						<span class="blst-stat-label"><?php esc_html_e( 'Downloads', 'bmlt-enabled-stats' ); ?></span>
					</div>
				</div>

				<div class="blst-plugin-bar">
					<div class="blst-bar-fill" style="width: <?php echo esc_attr( min( 100, ( ( $wp_plugin['active_installs'] ?? 0 ) / max( 1, $stats['total_active_installs'] ) ) * 200 ) ); ?>%"></div>
				</div>

				<?php if ( ! empty( $wp_plugin['last_updated'] ) ) : ?>
				<p class="blst-plugin-updated">
					<?php
					printf(
						/* translators: %s: Last updated date */
						esc_html__( 'Updated %s', 'bmlt-enabled-stats' ),
						esc_html( Shortcodes::format_date( $wp_plugin['last_updated'] ) )
					);
					?>
				</p>
				<?php endif; ?>

				<a href="<?php echo esc_url( $wp_plugin['plugin_url'] ?? '#' ); ?>" class="blst-plugin-link" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'View on WordPress.org', 'bmlt-enabled-stats' ); ?> &rarr;
				</a>
			</div>
			<?php endforeach; ?>
		</div>
	</section>
	<?php endif; ?>

	<!-- Charts -->
	<div class="blst-charts-row">
		<div class="blst-chart-container">
			<h3 class="blst-chart-title"><?php esc_html_e( 'Active Installs', 'bmlt-enabled-stats' ); ?></h3>
			<canvas id="blst-wp-installs-chart"></canvas>
		</div>
		<div class="blst-chart-container">
			<h3 class="blst-chart-title"><?php esc_html_e( 'Downloads', 'bmlt-enabled-stats' ); ?></h3>
			<canvas id="blst-wp-downloads-chart"></canvas>
		</div>
	</div>

</div>

<!-- Chart Data -->
<script type="application/json" id="blst-chart-data">
<?php
echo wp_json_encode(
	array(
		'wordpress' => array(
			'plugins'   => array_column( $stats['plugins'] ?? array(), 'display_name' ),
			'installs'  => array_column( $stats['plugins'] ?? array(), 'active_installs' ),
			'downloads' => array_column( $stats['plugins'] ?? array(), 'downloaded' ),
		),
	)
);
?>
</script>
