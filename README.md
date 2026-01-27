# BMLT Enabled Stats

![CI](https://github.com/bmlt-enabled/bmlt-enabled-stats/actions/workflows/ci.yml/badge.svg)
[![codecov](https://codecov.io/gh/bmlt-enabled/bmlt-enabled-stats/branch/main/graph/badge.svg)](https://codecov.io/gh/bmlt-enabled/bmlt-enabled-stats)

WordPress plugin displaying statistics for BMLT-enabled projects.

## Features

- GitHub organization statistics (stars, forks, repos, release downloads)
- WordPress.org plugin statistics (downloads, active installs)
- Interactive charts using Chart.js
- Gutenberg block and shortcode support
- Automatic data refresh via WP-Cron

## Installation

1. Download the latest release
2. Upload to WordPress plugins directory
3. Activate the plugin
4. Use `[bmlt_stats]` shortcode or the Gutenberg block

## Shortcodes

```
[bmlt_stats]                          # Full stats display
[bmlt_stats type="summary"]           # Hero cards only
[bmlt_stats type="github"]            # GitHub stats only
[bmlt_stats type="wordpress"]         # WordPress plugin stats only
[bmlt_stats theme="dark"]             # Dark theme variant
```

## Configuration

Navigate to **Settings > BMLT Stats** in the WordPress admin to:

- Configure GitHub API token for higher rate limits
- Manually refresh cached data
- View current cache status

## Development

```bash
# Install dependencies
make install

# Run PHPCS linter
make lint

# Auto-fix linting issues
make lint-fix

# Run PHPUnit tests
make test

# Run tests with coverage
make coverage

# Build plugin zip
make build
```

## External APIs

- **GitHub API**: Organization repos and release data from `github.com/bmlt-enabled`
- **WordPress.org API**: Plugin stats by author "bmltenabled"
- **BMLT Tomato**: Meeting aggregator data from `tomato.bmltenabled.org`

## License

GPL-2.0-or-later
