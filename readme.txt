=== BMLT Enabled Stats ===
Contributors: bmlt-enabled
Donate link: https://bmlt.app
Tags: bmlt, statistics, meetings, recovery, dashboard
Requires at least: 5.8
Tested up to: 6.4
Stable tag: 1.0.0
Requires PHP: 8.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Displays engaging, visual statistics about all BMLT-enabled projects in one dashboard.

== Description ==

BMLT Enabled Stats provides a beautiful, modern dashboard displaying statistics from all BMLT-enabled projects. Show your visitors the global reach and active development of the BMLT ecosystem.

**Features:**

* **BMLT Meeting Statistics** - Display total meetings worldwide, root servers, groups, regions, and zones from the BMLT Tomato aggregator
* **GitHub Repository Stats** - Show stars, forks, and activity across all bmlt-enabled repositories
* **WordPress Plugin Stats** - Display downloads, active installs, and ratings for BMLT WordPress plugins
* **Animated Counters** - Numbers animate when they come into view
* **Interactive Charts** - Beautiful Chart.js visualizations
* **Gutenberg Block** - Easy to add via the block editor
* **Multiple Display Modes** - Full dashboard, summary cards, or individual sections
* **Theme Options** - Default, dark, and compact themes
* **Automatic Updates** - Data refreshes daily via WP-Cron

**Shortcodes:**

* `[bmlt_stats]` - Full stats display with all sections
* `[bmlt_stats type="summary"]` - Hero summary cards only
* `[bmlt_stats type="github"]` - GitHub stats only
* `[bmlt_stats type="wordpress"]` - WordPress plugin stats only
* `[bmlt_stats type="meetings"]` - BMLT meeting stats only

**Theme Attribute:**

Add `theme="dark"` or `theme="compact"` to any shortcode:
`[bmlt_stats type="summary" theme="dark"]`

== Installation ==

1. Upload the `bmlt-enabled-stats` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > BMLT Stats to configure options
4. Add the `[bmlt_stats]` shortcode to any page or use the Gutenberg block

== Frequently Asked Questions ==

= Where does the data come from? =

Data is collected from three sources:
* GitHub API for the bmlt-enabled organization
* WordPress.org Plugin API for BMLT plugins
* BMLT Tomato Aggregator for meeting statistics

= How often is the data updated? =

By default, data is cached for 24 hours (GitHub and WordPress.org) and 12 hours (BMLT meetings). A daily cron job refreshes all data automatically.

= Can I manually refresh the data? =

Yes! Go to Settings > BMLT Stats and click "Refresh Stats Now" to force an immediate refresh.

= Do I need a GitHub API token? =

No, but it's recommended. Without a token, you're limited to 60 API requests per hour. With a token, you get 5,000 requests per hour.

= Can I customize the appearance? =

The plugin provides three built-in themes (default, dark, compact). For further customization, you can override the CSS or create custom templates in your theme's `bmlt-enabled-stats/` directory.

== Screenshots ==

1. Full dashboard display with all statistics
2. Summary hero cards with animated counters
3. GitHub repository cards with stars and forks
4. WordPress plugin cards with ratings and downloads
5. BMLT meeting stats by zone
6. Admin settings page
7. Gutenberg block in the editor

== Changelog ==

= 1.0.0 =
* Initial release
* GitHub API integration for bmlt-enabled organization
* WordPress.org Plugin API integration
* BMLT Tomato Aggregator integration
* Shortcode support with multiple display types
* Gutenberg block with editor preview
* Animated number counters
* Chart.js visualizations
* Admin settings page
* WP-Cron daily data refresh
* Three theme options (default, dark, compact)

== Upgrade Notice ==

= 1.0.0 =
Initial release of BMLT Enabled Stats.

== Data Sources ==

**GitHub API**
Statistics are fetched from the bmlt-enabled GitHub organization:
https://github.com/bmlt-enabled

**WordPress.org Plugin API**
Plugin data is fetched for these BMLT plugins:
* bread - Meeting List Generator
* crouton - Tabbed Meeting Display
* bmlt-workflow - Workflow Automation
* fetch-jft - Daily Meditation
* nacc - Cleantime Calculator
* list-locations-bmlt - Location Listings
* contacts-bmlt - Service Body Contacts
* temporary-closures-bmlt - Temporary Closures
* bmlt-versions - Version Display

**BMLT Aggregator (Tomato)**
Meeting data is sourced from:
https://tomato.bmltenabled.org

== Privacy ==

This plugin fetches data from external APIs:
* GitHub API (api.github.com)
* WordPress.org API (api.wordpress.org)
* BMLT Tomato (tomato.bmltenabled.org)

No personal data is collected or transmitted. All data displayed is publicly available.
