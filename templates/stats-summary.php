<?php
/**
 * Summary Stats Display Template
 *
 * Shows only hero cards with key metrics
 *
 * @package BMLT_Enabled_Stats
 *
 * Variables available:
 * @var array  $stats      Summary stats data
 * @var string $theme      Theme class
 * @var string $stats_type Display type (summary)
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use BLST\Shortcodes;
?>

<div class="blst-stats-container blst-summary-only blst-theme-<?php echo esc_attr( $theme ); ?>">

    <div class="blst-hero-grid">
        <div class="blst-hero-card blst-card-stars">
            <div class="blst-card-icon">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
            </div>
            <div class="blst-card-content">
                <span class="blst-stat-number" data-count="<?php echo esc_attr( $stats['total_github_stars'] ?? 0 ); ?>">0</span>
                <span class="blst-stat-label"><?php esc_html_e( 'GitHub Stars', 'bmlt-enabled-stats' ); ?></span>
            </div>
        </div>

        <div class="blst-hero-card blst-card-downloads">
            <div class="blst-card-icon">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM17 13l-5 5-5-5h3V9h4v4h3z"/></svg>
            </div>
            <div class="blst-card-content">
                <span class="blst-stat-number" data-count="<?php echo esc_attr( $stats['total_downloads'] ?? 0 ); ?>"><?php echo esc_html( Shortcodes::format_number( $stats['total_downloads'] ?? 0 ) ); ?></span>
                <span class="blst-stat-label"><?php esc_html_e( 'Plugin Downloads', 'bmlt-enabled-stats' ); ?></span>
            </div>
        </div>

        <div class="blst-hero-card blst-card-installs">
            <div class="blst-card-icon">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17 1.01L7 1c-1.1 0-2 .9-2 2v18c0 1.1.9 2 2 2h10c1.1 0 2-.9 2-2V3c0-1.1-.9-1.99-2-1.99zM17 19H7V5h10v14z"/></svg>
            </div>
            <div class="blst-card-content">
                <span class="blst-stat-number" data-count="<?php echo esc_attr( $stats['total_active_installs'] ?? 0 ); ?>" data-suffix="+"><?php echo esc_html( Shortcodes::format_number( $stats['total_active_installs'] ?? 0 ) ); ?>+</span>
                <span class="blst-stat-label"><?php esc_html_e( 'Active Sites', 'bmlt-enabled-stats' ); ?></span>
            </div>
        </div>

        <div class="blst-hero-card blst-card-repos">
            <div class="blst-card-icon">
                <svg viewBox="0 0 16 16" fill="currentColor"><path d="M2 2.5A2.5 2.5 0 014.5 0h8.75a.75.75 0 01.75.75v12.5a.75.75 0 01-.75.75h-2.5a.75.75 0 110-1.5h1.75v-2h-8a1 1 0 00-.714 1.7.75.75 0 01-1.072 1.05A2.495 2.495 0 012 11.5v-9zm10.5-1V9h-8c-.356 0-.694.074-1 .208V2.5a1 1 0 011-1h8zM5 12.25v3.25a.25.25 0 00.4.2l1.45-1.087a.25.25 0 01.3 0L8.6 15.7a.25.25 0 00.4-.2v-3.25a.25.25 0 00-.25-.25h-3.5a.25.25 0 00-.25.25z"/></svg>
            </div>
            <div class="blst-card-content">
                <span class="blst-stat-number" data-count="<?php echo esc_attr( $stats['total_repos'] ?? 0 ); ?>">0</span>
                <span class="blst-stat-label"><?php esc_html_e( 'Repositories', 'bmlt-enabled-stats' ); ?></span>
            </div>
        </div>

        <div class="blst-hero-card blst-card-forks">
            <div class="blst-card-icon">
                <svg viewBox="0 0 16 16" fill="currentColor"><path d="M5 5.372v.878c0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75v-.878a2.25 2.25 0 111.5 0v.878a2.25 2.25 0 01-2.25 2.25h-1.5v2.128a2.251 2.251 0 11-1.5 0V8.5h-1.5A2.25 2.25 0 013.5 6.25v-.878a2.25 2.25 0 111.5 0zM5 3.25a.75.75 0 10-1.5 0 .75.75 0 001.5 0zm6.75.75a.75.75 0 100-1.5.75.75 0 000 1.5zm-3 8.75a.75.75 0 10-1.5 0 .75.75 0 001.5 0z"/></svg>
            </div>
            <div class="blst-card-content">
                <span class="blst-stat-number" data-count="<?php echo esc_attr( $stats['total_forks'] ?? 0 ); ?>">0</span>
                <span class="blst-stat-label"><?php esc_html_e( 'Forks', 'bmlt-enabled-stats' ); ?></span>
            </div>
        </div>
    </div>

</div>
