<?php
/**
 * GitHub Stats Display Template
 *
 * @package BMLT_Enabled_Stats
 *
 * Variables available:
 * @var array  $stats      GitHub stats data
 * @var string $theme      Theme class
 * @var string $stats_type Display type (github)
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use BLST\Shortcodes;
?>

<div class="blst-stats-container blst-github-only blst-theme-<?php echo esc_attr( $theme ); ?>">

    <!-- GitHub Summary -->
    <div class="blst-hero-grid blst-hero-small">
        <div class="blst-hero-card blst-card-repos">
            <div class="blst-card-icon">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/></svg>
            </div>
            <div class="blst-card-content">
                <span class="blst-stat-number" data-count="<?php echo esc_attr( $stats['total_repos'] ?? 0 ); ?>">0</span>
                <span class="blst-stat-label"><?php esc_html_e( 'Repositories', 'bmlt-enabled-stats' ); ?></span>
            </div>
        </div>

        <div class="blst-hero-card blst-card-stars">
            <div class="blst-card-icon">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
            </div>
            <div class="blst-card-content">
                <span class="blst-stat-number" data-count="<?php echo esc_attr( $stats['total_stars'] ?? 0 ); ?>">0</span>
                <span class="blst-stat-label"><?php esc_html_e( 'Total Stars', 'bmlt-enabled-stats' ); ?></span>
            </div>
        </div>

        <div class="blst-hero-card blst-card-forks">
            <div class="blst-card-icon">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M6 2a3 3 0 100 6 3 3 0 000-6zm0 2a1 1 0 110 2 1 1 0 010-2zm12 0a3 3 0 100 6 3 3 0 000-6zm0 2a1 1 0 110 2 1 1 0 010-2zM6 16a3 3 0 100 6 3 3 0 000-6zm0 2a1 1 0 110 2 1 1 0 010-2zm6-11V5H7v2h5zm0 2v6h-1V9zm-5 0v4.5a1.5 1.5 0 001.5 1.5H12v2H7a3.5 3.5 0 01-3.5-3.5V9H5zm12 0v4.5a3.5 3.5 0 01-3.5 3.5H12v-2h4.5a1.5 1.5 0 001.5-1.5V9h2z"/></svg>
            </div>
            <div class="blst-card-content">
                <span class="blst-stat-number" data-count="<?php echo esc_attr( $stats['total_forks'] ?? 0 ); ?>">0</span>
                <span class="blst-stat-label"><?php esc_html_e( 'Total Forks', 'bmlt-enabled-stats' ); ?></span>
            </div>
        </div>

        <div class="blst-hero-card blst-card-issues">
            <div class="blst-card-icon">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
            </div>
            <div class="blst-card-content">
                <span class="blst-stat-number" data-count="<?php echo esc_attr( $stats['total_open_issues'] ?? 0 ); ?>">0</span>
                <span class="blst-stat-label"><?php esc_html_e( 'Open Issues', 'bmlt-enabled-stats' ); ?></span>
            </div>
        </div>
    </div>

    <!-- Top Repositories -->
    <?php if ( ! empty( $stats['top_repos'] ) ) : ?>
    <section class="blst-section blst-github-section">
        <h2 class="blst-section-title">
            <?php esc_html_e( 'Top Repositories', 'bmlt-enabled-stats' ); ?>
        </h2>

        <div class="blst-repo-grid">
            <?php foreach ( array_slice( $stats['top_repos'], 0, 9 ) as $repo ) : ?>
            <a href="<?php echo esc_url( $repo['html_url'] ); ?>" class="blst-repo-card" target="_blank" rel="noopener noreferrer">
                <h3 class="blst-repo-name"><?php echo esc_html( $repo['name'] ); ?></h3>
                <?php if ( ! empty( $repo['description'] ) ) : ?>
                <p class="blst-repo-desc"><?php echo esc_html( wp_trim_words( $repo['description'], 15 ) ); ?></p>
                <?php endif; ?>
                <div class="blst-repo-meta">
                    <?php if ( ! empty( $repo['language'] ) ) : ?>
                    <span class="blst-repo-lang">
                        <span class="blst-lang-dot" style="background-color: <?php echo esc_attr( Shortcodes::get_language_color( $repo['language'] ) ); ?>"></span>
                        <?php echo esc_html( $repo['language'] ); ?>
                    </span>
                    <?php endif; ?>
                    <span class="blst-repo-stars">
                        <svg viewBox="0 0 16 16" fill="currentColor"><path d="M8 .25a.75.75 0 01.673.418l1.882 3.815 4.21.612a.75.75 0 01.416 1.279l-3.046 2.97.719 4.192a.75.75 0 01-1.088.791L8 12.347l-3.766 1.98a.75.75 0 01-1.088-.79l.72-4.194L.818 6.374a.75.75 0 01.416-1.28l4.21-.611L7.327.668A.75.75 0 018 .25z"/></svg>
                        <?php echo esc_html( number_format( $repo['stars'] ) ); ?>
                    </span>
                    <span class="blst-repo-forks">
                        <svg viewBox="0 0 16 16" fill="currentColor"><path d="M5 3.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm0 2.122a2.25 2.25 0 10-1.5 0v.878A2.25 2.25 0 005.75 8.5h1.5v2.128a2.251 2.251 0 101.5 0V8.5h1.5a2.25 2.25 0 002.25-2.25v-.878a2.25 2.25 0 10-1.5 0v.878a.75.75 0 01-.75.75h-4.5A.75.75 0 015 6.25v-.878zm3.75 7.378a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm3-8.75a.75.75 0 100-1.5.75.75 0 000 1.5z"/></svg>
                        <?php echo esc_html( number_format( $repo['forks'] ) ); ?>
                    </span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Languages Breakdown -->
    <?php if ( ! empty( $stats['languages'] ) ) : ?>
    <section class="blst-section blst-languages-section">
        <h3 class="blst-subsection-title"><?php esc_html_e( 'Languages Used', 'bmlt-enabled-stats' ); ?></h3>
        <div class="blst-language-tags">
            <?php foreach ( array_slice( $stats['languages'], 0, 10, true ) as $lang => $count ) : ?>
            <span class="blst-language-tag" style="border-color: <?php echo esc_attr( Shortcodes::get_language_color( $lang ) ); ?>">
                <span class="blst-lang-dot" style="background-color: <?php echo esc_attr( Shortcodes::get_language_color( $lang ) ); ?>"></span>
                <?php echo esc_html( $lang ); ?>
                <span class="blst-lang-count"><?php echo esc_html( $count ); ?></span>
            </span>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Chart -->
    <div class="blst-charts-row">
        <div class="blst-chart-container blst-chart-wide">
            <h3 class="blst-chart-title"><?php esc_html_e( 'Stars by Repository', 'bmlt-enabled-stats' ); ?></h3>
            <canvas id="blst-github-stars-chart"></canvas>
        </div>
    </div>

    <footer class="blst-footer blst-footer-inline">
        <a href="https://github.com/bmlt-enabled" class="blst-github-link" target="_blank" rel="noopener noreferrer">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/></svg>
            <?php esc_html_e( 'View on GitHub', 'bmlt-enabled-stats' ); ?>
        </a>
    </footer>

</div>

<!-- Chart Data -->
<script type="application/json" id="blst-chart-data">
<?php
echo wp_json_encode(
    array(
        'github' => array(
            'repos' => array_column( array_slice( $stats['top_repos'] ?? array(), 0, 8 ), 'name' ),
            'stars' => array_column( array_slice( $stats['top_repos'] ?? array(), 0, 8 ), 'stars' ),
        ),
    )
);
?>
</script>
