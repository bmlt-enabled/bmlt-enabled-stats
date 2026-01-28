# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

BMLT Enabled Stats is a WordPress plugin that displays statistics about BMLT-enabled projects. It fetches data from GitHub API (bmlt-enabled organization), WordPress.org Plugin API (by author "bmltenabled"), and displays it in a dashboard with charts and animated counters.

## Architecture

### Core Components

- **Main Plugin** (`bmlt-enabled-stats.php`) - Entry point, defines constants, registers activation/deactivation hooks, autoloader, and cron schedule
- **Plugin Class** (`includes/class-plugin.php`) - Orchestrator that initializes all components and manages dependencies
- **Cache Manager** (`includes/class-cache-manager.php`) - WordPress transient-based caching with configurable durations
- **API Handlers**:
  - `class-github-api.php` - Fetches repos from `github.com/bmlt-enabled` organization
  - `class-wordpress-api.php` - Queries plugins by author "bmltenabled" from WordPress.org API v1.2
- **Shortcodes** (`includes/class-shortcodes.php`) - Registers `[bmlt_stats]` shortcode with type/theme attributes
- **Scheduler** (`includes/class-scheduler.php`) - Handles WP-Cron for automatic 4-hour data refresh

### Data Flow

1. Stats requested via shortcode or block
2. Shortcodes class calls appropriate API handler
3. API handler checks cache (transients) first
4. If cache miss, fetches from external API and caches result
5. Template renders the data with Chart.js visualizations

### Namespace

All classes use the `BLST` namespace. Class files follow WordPress naming: `class-{name}.php` maps to `BLST\{Name}`.

### Templates

Located in `templates/`:
- `stats-full.php` - Complete dashboard with hero cards, charts, plugin details
- `stats-summary.php` - Hero cards only
- `stats-github.php`, `stats-wordpress.php` - Individual sections

Templates can be overridden by themes in `bmlt-enabled-stats/` directory.

### Admin

`admin/class-admin.php` provides:
- Settings page under Settings > BMLT Stats
- Cache status display with stats preview
- Manual refresh and clear cache buttons via AJAX
- GitHub token configuration for higher API rate limits

### Frontend Assets

- `assets/css/stats-display.css` - Dashboard styling with CSS variables for theming
- `assets/js/stats-display.js` - Animated counters and Chart.js initialization

## Key Constants

- `BLST_DEFAULT_CACHE_DURATION` - 4 hours (14400 seconds)
- `BLST_TRANSIENT_PREFIX` - "blst_"
- Cache keys: `blst_github_repos`, `blst_wporg_plugins`

## Shortcode Usage

```
[bmlt_stats]                          # Full dashboard
[bmlt_stats type="summary"]           # Hero cards only
[bmlt_stats type="github"]            # GitHub stats only
[bmlt_stats type="wordpress"]         # WordPress plugin stats only
[bmlt_stats theme="dark"]             # Dark theme variant
```

## External APIs

- **GitHub**: `https://api.github.com/orgs/bmlt-enabled/repos`
- **WordPress.org**: `https://api.wordpress.org/plugins/info/1.2/?action=query_plugins&request[author]=bmltenabled`

## WordPress Hooks

- `blst_stats_refresh` - Cron hook for automatic refresh (every 4 hours)
- `cron_schedules` filter - Adds custom "four_hours" schedule

## Development Commands

```bash
composer install          # Install dev dependencies
composer lint             # Run PHPCS linter
composer lint:fix         # Auto-fix linting issues with PHPCBF
```

## CI/CD

GitHub Actions workflow (`.github/workflows/build.yml`):
- Runs PHPCS linting on all PHP files
- Builds plugin zip artifact on push/PR to main
- Artifacts available for download from Actions tab (30-day retention)
