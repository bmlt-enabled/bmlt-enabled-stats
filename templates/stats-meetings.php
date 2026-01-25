<?php
/**
 * BMLT Meetings Stats Display Template
 *
 * @package BMLT_Enabled_Stats
 *
 * Variables available:
 * @var array  $stats      BMLT stats data
 * @var string $theme      Theme class
 * @var string $stats_type Display type (meetings)
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use BLST\Shortcodes;
?>

<div class="blst-stats-container blst-meetings-only blst-theme-<?php echo esc_attr( $theme ); ?>">

	<!-- BMLT Summary -->
	<div class="blst-hero-grid blst-hero-small">
		<div class="blst-hero-card blst-card-meetings">
			<div class="blst-card-icon">
				<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
			</div>
			<div class="blst-card-content">
				<span class="blst-stat-number" data-count="<?php echo esc_attr( $stats['total_meetings'] ?? 0 ); ?>">0</span>
				<span class="blst-stat-label"><?php esc_html_e( 'Total Meetings', 'bmlt-enabled-stats' ); ?></span>
			</div>
		</div>

		<div class="blst-hero-card blst-card-servers">
			<div class="blst-card-icon">
				<svg viewBox="0 0 24 24" fill="currentColor"><path d="M20 13H4c-.55 0-1 .45-1 1v6c0 .55.45 1 1 1h16c.55 0 1-.45 1-1v-6c0-.55-.45-1-1-1zM7 19c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zM20 3H4c-.55 0-1 .45-1 1v6c0 .55.45 1 1 1h16c.55 0 1-.45 1-1V4c0-.55-.45-1-1-1zM7 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/></svg>
			</div>
			<div class="blst-card-content">
				<span class="blst-stat-number" data-count="<?php echo esc_attr( $stats['root_servers'] ?? 0 ); ?>">0</span>
				<span class="blst-stat-label"><?php esc_html_e( 'Root Servers', 'bmlt-enabled-stats' ); ?></span>
			</div>
		</div>

		<div class="blst-hero-card blst-card-groups">
			<div class="blst-card-icon">
				<svg viewBox="0 0 24 24" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
			</div>
			<div class="blst-card-content">
				<span class="blst-stat-number" data-count="<?php echo esc_attr( $stats['total_groups'] ?? 0 ); ?>">0</span>
				<span class="blst-stat-label"><?php esc_html_e( 'Groups', 'bmlt-enabled-stats' ); ?></span>
			</div>
		</div>

		<div class="blst-hero-card blst-card-zones">
			<div class="blst-card-icon">
				<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
			</div>
			<div class="blst-card-content">
				<span class="blst-stat-number" data-count="<?php echo esc_attr( $stats['zone_count'] ?? 0 ); ?>">0</span>
				<span class="blst-stat-label"><?php esc_html_e( 'Zones', 'bmlt-enabled-stats' ); ?></span>
			</div>
		</div>
	</div>

	<!-- Additional Stats Row -->
	<div class="blst-meetings-overview">
		<div class="blst-meeting-stat">
			<span class="blst-stat-value"><?php echo esc_html( number_format( $stats['total_areas'] ?? 0 ) ); ?></span>
			<span class="blst-stat-label"><?php esc_html_e( 'Service Areas', 'bmlt-enabled-stats' ); ?></span>
		</div>
		<div class="blst-meeting-stat">
			<span class="blst-stat-value"><?php echo esc_html( number_format( $stats['total_regions'] ?? 0 ) ); ?></span>
			<span class="blst-stat-label"><?php esc_html_e( 'Regions', 'bmlt-enabled-stats' ); ?></span>
		</div>
		<div class="blst-meeting-stat">
			<span class="blst-stat-value"><?php echo esc_html( $stats['active_servers'] ?? 0 ); ?></span>
			<span class="blst-stat-label"><?php esc_html_e( 'Active Servers', 'bmlt-enabled-stats' ); ?></span>
		</div>
	</div>

	<!-- Zones Breakdown -->
	<?php if ( ! empty( $stats['zones'] ) ) : ?>
	<section class="blst-section blst-zones-section">
		<h2 class="blst-section-title">
			<?php esc_html_e( 'Meetings by Zone', 'bmlt-enabled-stats' ); ?>
		</h2>

		<div class="blst-zones-list">
			<div class="blst-zone-bars">
				<?php foreach ( $stats['zones'] as $zone ) : ?>
				<div class="blst-zone-item">
					<div class="blst-zone-info">
						<span class="blst-zone-name"><?php echo esc_html( $zone['name'] ); ?></span>
						<span class="blst-zone-count">
							<?php echo esc_html( number_format( $zone['meetings'] ) ); ?> <?php esc_html_e( 'meetings', 'bmlt-enabled-stats' ); ?>
							<span class="blst-zone-servers">(<?php echo esc_html( $zone['servers'] ); ?> <?php echo esc_html( _n( 'server', 'servers', $zone['servers'], 'bmlt-enabled-stats' ) ); ?>)</span>
						</span>
					</div>
					<div class="blst-zone-bar">
						<div class="blst-bar-fill" style="width: <?php echo esc_attr( ( $zone['meetings'] / max( 1, $stats['total_meetings'] ) ) * 100 ); ?>%"></div>
					</div>
				</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<!-- Top Root Servers -->
	<?php if ( ! empty( $stats['top_servers'] ) ) : ?>
	<section class="blst-section blst-servers-section">
		<h2 class="blst-section-title">
			<?php esc_html_e( 'Largest Root Servers', 'bmlt-enabled-stats' ); ?>
		</h2>

		<div class="blst-server-grid">
			<?php foreach ( array_slice( $stats['top_servers'], 0, 8 ) as $server ) : ?>
			<div class="blst-server-card">
				<h3 class="blst-server-name"><?php echo esc_html( $server['name'] ); ?></h3>
				<div class="blst-server-stats">
					<div class="blst-server-stat">
						<span class="blst-stat-value"><?php echo esc_html( number_format( $server['num_meetings'] ) ); ?></span>
						<span class="blst-stat-label"><?php esc_html_e( 'Meetings', 'bmlt-enabled-stats' ); ?></span>
					</div>
					<div class="blst-server-stat">
						<span class="blst-stat-value"><?php echo esc_html( number_format( $server['num_groups'] ) ); ?></span>
						<span class="blst-stat-label"><?php esc_html_e( 'Groups', 'bmlt-enabled-stats' ); ?></span>
					</div>
				</div>
				<span class="blst-server-zone"><?php echo esc_html( $server['zone'] ); ?></span>
				<?php if ( $server['is_active'] ) : ?>
				<span class="blst-server-status blst-status-online"><?php esc_html_e( 'Online', 'bmlt-enabled-stats' ); ?></span>
				<?php else : ?>
				<span class="blst-server-status blst-status-offline"><?php esc_html_e( 'Offline', 'bmlt-enabled-stats' ); ?></span>
				<?php endif; ?>
			</div>
			<?php endforeach; ?>
		</div>
	</section>
	<?php endif; ?>

	<footer class="blst-footer blst-footer-inline">
		<p class="blst-powered">
			<?php esc_html_e( 'Data from', 'bmlt-enabled-stats' ); ?>
			<a href="https://tomato.bmltenabled.org" target="_blank" rel="noopener noreferrer">BMLT Tomato</a>
		</p>
	</footer>

</div>
