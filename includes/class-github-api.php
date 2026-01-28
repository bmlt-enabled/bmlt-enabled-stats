<?php
/**
 * GitHub API Handler Class
 *
 * @package BMLT_Enabled_Stats
 */

namespace BLST;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GitHub API handler for bmlt-enabled organization
 */
class GitHub_API {

	/**
	 * GitHub API base URL
	 */
	const API_BASE = 'https://api.github.com';

	/**
	 * Organization name
	 */
	const ORG_NAME = 'bmlt-enabled';

	/**
	 * Cache manager instance
	 *
	 * @var Cache_Manager
	 */
	private $cache_manager;

	/**
	 * Plugin settings
	 *
	 * @var array
	 */
	private $settings;

	/**
	 * Constructor
	 *
	 * @param Cache_Manager $cache_manager Cache manager instance.
	 * @param array         $settings      Plugin settings.
	 */
	public function __construct( Cache_Manager $cache_manager, $settings ) {
		$this->cache_manager = $cache_manager;
		$this->settings      = $settings;
	}

	/**
	 * Get all GitHub stats
	 *
	 * @param bool $force_refresh Whether to bypass cache.
	 * @return array
	 */
	public function get_stats( $force_refresh = false ) {
		// Try to get from cache first.
		if ( ! $force_refresh ) {
			$cached = $this->cache_manager->get( Cache_Manager::KEY_GITHUB_REPOS );
			if ( false !== $cached ) {
				return $cached;
			}
		}

		// Fetch fresh data.
		$repos = $this->fetch_all_repos();

		if ( is_wp_error( $repos ) ) {
			return $this->get_empty_stats();
		}

		// Process and aggregate stats.
		$stats = $this->process_repo_data( $repos );

		// Cache the results.
		$this->cache_manager->set( Cache_Manager::KEY_GITHUB_REPOS, $stats );

		return $stats;
	}

	/**
	 * Fetch all repositories from the organization
	 *
	 * @return array|\WP_Error
	 */
	private function fetch_all_repos() {
		$all_repos = array();
		$page      = 1;
		$per_page  = 100;

		do {
			$url = self::API_BASE . '/orgs/' . self::ORG_NAME . '/repos';
			$url = add_query_arg(
				array(
					'per_page' => $per_page,
					'page'     => $page,
					'type'     => 'public',
				),
				$url
			);

			$response = $this->make_request( $url );

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			$repos = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( empty( $repos ) || ! is_array( $repos ) ) {
				break;
			}

			$all_repos = array_merge( $all_repos, $repos );
			++$page;

			// Check if there are more pages.
			$headers = wp_remote_retrieve_headers( $response );
			$link    = isset( $headers['link'] ) ? $headers['link'] : '';

		} while ( strpos( $link, 'rel="next"' ) !== false && $page <= 10 );

		return $all_repos;
	}

	/**
	 * Process repository data into aggregated stats
	 *
	 * @param array $repos Raw repository data.
	 * @return array
	 */
	private function process_repo_data( $repos ) {
		$total_stars       = 0;
		$total_forks       = 0;
		$total_open_issues = 0;
		$languages         = array();
		$processed_repos   = array();

		foreach ( $repos as $repo ) {
			// Skip forks unless they're significant.
			if ( ! empty( $repo['fork'] ) && ( $repo['stargazers_count'] ?? 0 ) < 5 ) {
				continue;
			}

			$total_stars       += $repo['stargazers_count'] ?? 0;
			$total_forks       += $repo['forks_count'] ?? 0;
			$total_open_issues += $repo['open_issues_count'] ?? 0;

			// Track languages.
			$lang = $repo['language'] ?? 'Other';
			if ( ! isset( $languages[ $lang ] ) ) {
				$languages[ $lang ] = 0;
			}
			++$languages[ $lang ];

			// Store individual repo data.
			$processed_repos[] = array(
				'name'        => $repo['name'] ?? '',
				'full_name'   => $repo['full_name'] ?? '',
				'description' => $repo['description'] ?? '',
				'html_url'    => $repo['html_url'] ?? '',
				'stars'       => $repo['stargazers_count'] ?? 0,
				'forks'       => $repo['forks_count'] ?? 0,
				'open_issues' => $repo['open_issues_count'] ?? 0,
				'language'    => $repo['language'] ?? '',
				'updated_at'  => $repo['updated_at'] ?? '',
				'created_at'  => $repo['created_at'] ?? '',
				'archived'    => $repo['archived'] ?? false,
			);
		}

		// Sort repos by stars (descending).
		usort(
			$processed_repos,
			function ( $a, $b ) {
				return $b['stars'] - $a['stars'];
			}
		);

		// Sort languages by count.
		arsort( $languages );

		// Get top repos by stars (non-archived).
		$active_repos = array_values(
			array_filter(
				$processed_repos,
				function ( $repo ) {
					return ! $repo['archived'];
				}
			)
		);
		$top_repos    = array_slice( $active_repos, 0, 10 );

		// Get top repos by forks.
		$repos_by_forks = $active_repos;
		usort(
			$repos_by_forks,
			function ( $a, $b ) {
				return $b['forks'] - $a['forks'];
			}
		);
		$top_repos_by_forks = array_slice( $repos_by_forks, 0, 10 );

		// Fetch release downloads for top repos.
		$release_data = $this->fetch_release_downloads( $processed_repos );

		// Fetch closed issues for top repos.
		$closed_issues_by_repo = $this->fetch_closed_issues_count( $top_repos );

		// Fetch top contributors across repos.
		$top_contributors = $this->fetch_top_contributors( $top_repos );

		return array(
			'total_repos'             => count( $processed_repos ),
			'total_stars'             => $total_stars,
			'total_forks'             => $total_forks,
			'total_open_issues'       => $total_open_issues,
			'total_closed_issues'     => array_sum( $closed_issues_by_repo ),
			'closed_issues_by_repo'   => $closed_issues_by_repo,
			'total_contributors'      => $this->get_contributor_count(),
			'top_contributors'        => $top_contributors,
			'total_release_downloads' => $release_data['total'],
			'release_downloads'       => $release_data['per_repo'],
			'languages'               => $languages,
			'top_repos'               => $top_repos,
			'top_repos_by_forks'      => $top_repos_by_forks,
			'all_repos'               => $processed_repos,
			'fetched_at'              => current_time( 'mysql' ),
		);
	}

	/**
	 * Get contributor count across all repos
	 *
	 * @return int
	 */
	private function get_contributor_count() {
		// Get org members count as a proxy for contributors.
		$url      = self::API_BASE . '/orgs/' . self::ORG_NAME;
		$response = $this->make_request( $url );

		if ( is_wp_error( $response ) ) {
			return 0;
		}

		$org_data = json_decode( wp_remote_retrieve_body( $response ), true );

		// Return public members count.
		return isset( $org_data['public_members_url'] ) ?
			$this->count_public_members() : 0;
	}

	/**
	 * Count public members of the organization
	 *
	 * @return int
	 */
	private function count_public_members() {
		$url      = self::API_BASE . '/orgs/' . self::ORG_NAME . '/public_members';
		$response = $this->make_request( $url );

		if ( is_wp_error( $response ) ) {
			return 0;
		}

		$members = json_decode( wp_remote_retrieve_body( $response ), true );
		return is_array( $members ) ? count( $members ) : 0;
	}

	/**
	 * Make an authenticated request to GitHub API
	 *
	 * @param string $url          The URL to request.
	 * @param bool   $allow_202    Whether to accept HTTP 202 (stats endpoints return this while computing).
	 * @return array|\WP_Error
	 */
	private function make_request( $url, $allow_202 = false ) {
		$args = array(
			'timeout'    => 30,
			'user-agent' => 'BMLT-Enabled-Stats-Plugin/' . BLST_VERSION,
			'headers'    => array(
				'Accept' => 'application/vnd.github.v3+json',
			),
		);

		// Add authentication if token is available.
		$token = $this->settings['github_token'] ?? '';
		if ( ! empty( $token ) ) {
			$args['headers']['Authorization'] = 'Bearer ' . $token;
		}

		$response = wp_remote_get( $url, $args );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code        = wp_remote_retrieve_response_code( $response );
		$valid_codes = array( 200 );
		if ( $allow_202 ) {
			$valid_codes[] = 202;
		}

		if ( ! in_array( $code, $valid_codes, true ) ) {
			return new \WP_Error(
				'github_api_error',
				sprintf( 'GitHub API returned status %d', $code )
			);
		}

		return $response;
	}

	/**
	 * Get empty stats structure
	 *
	 * @return array
	 */
	private function get_empty_stats() {
		return array(
			'total_repos'             => 0,
			'total_stars'             => 0,
			'total_forks'             => 0,
			'total_open_issues'       => 0,
			'total_closed_issues'     => 0,
			'closed_issues_by_repo'   => array(),
			'total_contributors'      => 0,
			'top_contributors'        => array(),
			'total_release_downloads' => 0,
			'release_downloads'       => array(),
			'languages'               => array(),
			'top_repos'               => array(),
			'top_repos_by_forks'      => array(),
			'all_repos'               => array(),
			'fetched_at'              => null,
			'error'                   => true,
		);
	}

	/**
	 * Fetch top contributors across top repos with detailed stats
	 *
	 * Uses /stats/contributors endpoint to get additions/deletions.
	 * Limited to top 8 repos to avoid excessive API calls.
	 * Note: GitHub returns 202 while computing stats, so we retry once after a delay.
	 *
	 * @param array $repos List of repos to fetch contributors for.
	 * @return array Top 10 contributors with login, commits, additions, deletions, and avatar_url.
	 */
	private function fetch_top_contributors( $repos ) {
		$all_contributors = array();
		$repos_to_check   = array_slice( $repos, 0, 8 );

		foreach ( $repos_to_check as $repo ) {
			// Use stats/contributors endpoint for detailed data.
			$url      = self::API_BASE . '/repos/' . self::ORG_NAME . '/' . $repo['name'] . '/stats/contributors';
			$response = $this->make_request( $url, true ); // Allow 202.

			if ( is_wp_error( $response ) ) {
				continue;
			}

			// If 202, GitHub is computing stats - wait and retry once.
			$code = wp_remote_retrieve_response_code( $response );
			if ( 202 === $code ) {
				sleep( 2 ); // Wait 2 seconds for GitHub to compute.
				$response = $this->make_request( $url, true );
				if ( is_wp_error( $response ) ) {
					continue;
				}
			}

			$contributors = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( ! is_array( $contributors ) ) {
				continue;
			}

			foreach ( $contributors as $contrib ) {
				$login = $contrib['author']['login'] ?? '';
				if ( empty( $login ) ) {
					continue;
				}

				// Sum up additions, deletions, and commits from all weeks.
				$additions = 0;
				$deletions = 0;
				$commits   = 0;
				foreach ( $contrib['weeks'] ?? array() as $week ) {
					$additions += $week['a'] ?? 0;
					$deletions += $week['d'] ?? 0;
					$commits   += $week['c'] ?? 0;
				}

				if ( ! isset( $all_contributors[ $login ] ) ) {
					$all_contributors[ $login ] = array(
						'login'      => $login,
						'commits'    => 0,
						'additions'  => 0,
						'deletions'  => 0,
						'avatar_url' => $contrib['author']['avatar_url'] ?? '',
					);
				}
				$all_contributors[ $login ]['commits']   += $commits;
				$all_contributors[ $login ]['additions'] += $additions;
				$all_contributors[ $login ]['deletions'] += $deletions;
			}
		}

		// Sort by total lines changed (additions + deletions) descending.
		usort(
			$all_contributors,
			function ( $a, $b ) {
				$a_total = $a['additions'] + $a['deletions'];
				$b_total = $b['additions'] + $b['deletions'];
				return $b_total - $a_total;
			}
		);

		return array_slice( $all_contributors, 0, 10 );
	}

	/**
	 * Fetch closed issues count for repos using GitHub search API
	 *
	 * Limited to top 10 repos to avoid excessive API calls.
	 *
	 * @param array $repos List of repos to fetch closed issues for.
	 * @return array Associative array of repo name => closed count.
	 */
	private function fetch_closed_issues_count( $repos ) {
		$closed_counts  = array();
		$repos_to_check = array_slice( $repos, 0, 10 );

		foreach ( $repos_to_check as $repo ) {
			// Use search API to count closed issues.
			$url = self::API_BASE . '/search/issues';
			$url = add_query_arg(
				array(
					'q'        => 'repo:' . self::ORG_NAME . '/' . $repo['name'] . ' type:issue state:closed',
					'per_page' => 1,
				),
				$url
			);

			$response = $this->make_request( $url );

			if ( ! is_wp_error( $response ) ) {
				$data                           = json_decode( wp_remote_retrieve_body( $response ), true );
				$closed_counts[ $repo['name'] ] = $data['total_count'] ?? 0;
			}
		}

		return $closed_counts;
	}

	/**
	 * Fetch release downloads for repos
	 *
	 * Limited to top 10 repos to avoid excessive API calls.
	 *
	 * @param array $repos List of repos to fetch downloads for.
	 * @return array Array with 'total' count and 'per_repo' breakdown.
	 */
	private function fetch_release_downloads( $repos ) {
		$total    = 0;
		$per_repo = array();

		// Limit to top 10 repos to avoid rate limiting.
		$repos_to_check = array_slice( $repos, 0, 10 );

		foreach ( $repos_to_check as $repo ) {
			$url      = self::API_BASE . '/repos/' . self::ORG_NAME . '/' . $repo['name'] . '/releases';
			$response = $this->make_request( $url );

			if ( is_wp_error( $response ) ) {
				continue;
			}

			$releases = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( ! is_array( $releases ) ) {
				continue;
			}

			$repo_downloads = 0;
			foreach ( $releases as $release ) {
				if ( ! isset( $release['assets'] ) || ! is_array( $release['assets'] ) ) {
					continue;
				}

				foreach ( $release['assets'] as $asset ) {
					$repo_downloads += $asset['download_count'] ?? 0;
				}
			}

			if ( $repo_downloads > 0 ) {
				$per_repo[] = array(
					'name'      => $repo['name'],
					'downloads' => $repo_downloads,
				);
				$total     += $repo_downloads;
			}
		}

		// Sort by downloads descending.
		usort(
			$per_repo,
			function ( $a, $b ) {
				return $b['downloads'] - $a['downloads'];
			}
		);

		return array(
			'total'    => $total,
			'per_repo' => $per_repo,
		);
	}

	/**
	 * Get rate limit status
	 *
	 * @return array
	 */
	public function get_rate_limit() {
		$url      = self::API_BASE . '/rate_limit';
		$response = $this->make_request( $url );

		if ( is_wp_error( $response ) ) {
			return array(
				'limit'     => 0,
				'remaining' => 0,
				'reset'     => 0,
			);
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		$core = $data['resources']['core'] ?? array();

		return array(
			'limit'     => $core['limit'] ?? 0,
			'remaining' => $core['remaining'] ?? 0,
			'reset'     => $core['reset'] ?? 0,
		);
	}
}
