<?php
/**
 * GitHub API Test
 *
 * @package BMLT_Enabled_Stats
 */

namespace BLST\Tests;

use Brain\Monkey\Functions;
use Mockery;

/**
 * Test the GitHub API class.
 */
class GitHubApiTest extends TestCase {

	/**
	 * Mock cache manager.
	 *
	 * @var \BLST\Cache_Manager|Mockery\MockInterface
	 */
	private $cache_manager;

	/**
	 * Set up test fixtures.
	 */
	protected function set_up() {
		parent::set_up();

		require_once BLST_PLUGIN_DIR . 'includes/class-cache-manager.php';
		require_once BLST_PLUGIN_DIR . 'includes/class-github-api.php';

		$this->cache_manager = Mockery::mock( 'BLST\Cache_Manager' );
	}

	/**
	 * Test get_stats returns cached data when available.
	 */
	public function test_get_stats_returns_cached_data() {
		$cached_data = array(
			'total_repos' => 50,
			'total_stars' => 150,
			'total_forks' => 75,
			'fetched_at'  => '2024-01-01 00:00:00',
		);

		$this->cache_manager
			->shouldReceive( 'get' )
			->once()
			->with( \BLST\Cache_Manager::KEY_GITHUB_REPOS )
			->andReturn( $cached_data );

		$github_api = new \BLST\GitHub_API( $this->cache_manager, array() );
		$result     = $github_api->get_stats();

		$this->assertEquals( $cached_data, $result );
	}

	/**
	 * Test get_stats fetches fresh data when not cached.
	 */
	public function test_get_stats_fetches_fresh_when_not_cached() {
		$this->cache_manager
			->shouldReceive( 'get' )
			->once()
			->with( \BLST\Cache_Manager::KEY_GITHUB_REPOS )
			->andReturn( false );

		$this->cache_manager
			->shouldReceive( 'set' )
			->once()
			->andReturn( true );

		// Mock the HTTP request for repos.
		$repos_response = array(
			'headers'  => array( 'link' => '' ),
			'body'     => wp_json_encode(
				array(
					array(
						'name'              => 'test-repo',
						'full_name'         => 'bmlt-enabled/test-repo',
						'stargazers_count'  => 10,
						'forks_count'       => 5,
						'open_issues_count' => 2,
						'language'          => 'PHP',
						'html_url'          => 'https://github.com/bmlt-enabled/test-repo',
						'updated_at'        => '2024-01-01T00:00:00Z',
						'created_at'        => '2023-01-01T00:00:00Z',
						'archived'          => false,
						'fork'              => false,
					),
				)
			),
			'response' => array( 'code' => 200 ),
		);

		Functions\expect( 'add_query_arg' )
			->andReturnUsing(
				function ( $args, $url ) {
					return $url . '?' . http_build_query( $args );
				}
			);

		Functions\expect( 'wp_remote_get' )
			->andReturn( $repos_response );

		Functions\expect( 'is_wp_error' )
			->andReturn( false );

		Functions\expect( 'wp_remote_retrieve_response_code' )
			->andReturn( 200 );

		Functions\expect( 'wp_remote_retrieve_body' )
			->andReturn( $repos_response['body'] );

		Functions\expect( 'wp_remote_retrieve_headers' )
			->andReturn( array( 'link' => '' ) );

		Functions\expect( 'current_time' )
			->andReturn( '2024-01-01 00:00:00' );

		$github_api = new \BLST\GitHub_API( $this->cache_manager, array() );
		$result     = $github_api->get_stats();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'total_repos', $result );
		$this->assertArrayHasKey( 'total_stars', $result );
	}

	/**
	 * Test get_stats force refresh bypasses cache.
	 */
	public function test_get_stats_force_refresh_bypasses_cache() {
		// Should NOT call get when force_refresh is true.
		$this->cache_manager
			->shouldNotReceive( 'get' );

		$this->cache_manager
			->shouldReceive( 'set' )
			->once()
			->andReturn( true );

		Functions\expect( 'add_query_arg' )
			->andReturnUsing(
				function ( $args, $url ) {
					return $url . '?' . http_build_query( $args );
				}
			);

		Functions\expect( 'wp_remote_get' )
			->andReturn(
				array(
					'body'     => '[]',
					'response' => array( 'code' => 200 ),
				)
			);

		Functions\expect( 'is_wp_error' )
			->andReturn( false );

		Functions\expect( 'wp_remote_retrieve_response_code' )
			->andReturn( 200 );

		Functions\expect( 'wp_remote_retrieve_body' )
			->andReturn( '[]' );

		Functions\expect( 'wp_remote_retrieve_headers' )
			->andReturn( array( 'link' => '' ) );

		Functions\expect( 'current_time' )
			->andReturn( '2024-01-01 00:00:00' );

		$github_api = new \BLST\GitHub_API( $this->cache_manager, array() );
		$result     = $github_api->get_stats( true );

		$this->assertIsArray( $result );
	}

	/**
	 * Test get_stats returns empty stats on API error.
	 */
	public function test_get_stats_returns_empty_stats_on_error() {
		$this->cache_manager
			->shouldReceive( 'get' )
			->once()
			->andReturn( false );

		Functions\expect( 'add_query_arg' )
			->andReturnUsing(
				function ( $args, $url ) {
					return $url . '?' . http_build_query( $args );
				}
			);

		// Create a mock WP_Error.
		$wp_error = Mockery::mock( 'WP_Error' );

		Functions\expect( 'wp_remote_get' )
			->andReturn( $wp_error );

		Functions\expect( 'is_wp_error' )
			->andReturn( true );

		$github_api = new \BLST\GitHub_API( $this->cache_manager, array() );
		$result     = $github_api->get_stats();

		$this->assertIsArray( $result );
		$this->assertEquals( 0, $result['total_repos'] );
		$this->assertEquals( 0, $result['total_stars'] );
		$this->assertTrue( $result['error'] );
	}

	/**
	 * Test get_rate_limit returns status.
	 */
	public function test_get_rate_limit_returns_status() {
		$rate_limit_response = array(
			'resources' => array(
				'core' => array(
					'limit'     => 5000,
					'remaining' => 4999,
					'reset'     => time() + 3600,
				),
			),
		);

		Functions\expect( 'wp_remote_get' )
			->once()
			->andReturn(
				array(
					'body'     => wp_json_encode( $rate_limit_response ),
					'response' => array( 'code' => 200 ),
				)
			);

		Functions\expect( 'is_wp_error' )
			->andReturn( false );

		Functions\expect( 'wp_remote_retrieve_response_code' )
			->andReturn( 200 );

		Functions\expect( 'wp_remote_retrieve_body' )
			->andReturn( wp_json_encode( $rate_limit_response ) );

		$github_api = new \BLST\GitHub_API( $this->cache_manager, array() );
		$result     = $github_api->get_rate_limit();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'limit', $result );
		$this->assertArrayHasKey( 'remaining', $result );
		$this->assertArrayHasKey( 'reset', $result );
		$this->assertEquals( 5000, $result['limit'] );
	}

	/**
	 * Test get_rate_limit returns zeros on error.
	 */
	public function test_get_rate_limit_returns_zeros_on_error() {
		$wp_error = Mockery::mock( 'WP_Error' );

		Functions\expect( 'wp_remote_get' )
			->once()
			->andReturn( $wp_error );

		Functions\expect( 'is_wp_error' )
			->andReturn( true );

		$github_api = new \BLST\GitHub_API( $this->cache_manager, array() );
		$result     = $github_api->get_rate_limit();

		$this->assertEquals( 0, $result['limit'] );
		$this->assertEquals( 0, $result['remaining'] );
		$this->assertEquals( 0, $result['reset'] );
	}

	/**
	 * Test make_request adds auth header when token is provided.
	 */
	public function test_make_request_adds_auth_header() {
		$this->cache_manager
			->shouldReceive( 'get' )
			->once()
			->andReturn( false );

		$this->cache_manager
			->shouldReceive( 'set' )
			->once()
			->andReturn( true );

		$token          = 'test_github_token';
		$auth_validated = false;

		Functions\expect( 'add_query_arg' )
			->andReturnUsing(
				function ( $args, $url ) {
					return $url . '?' . http_build_query( $args );
				}
			);

		// Allow multiple calls to wp_remote_get, validate auth header on any.
		Functions\expect( 'wp_remote_get' )
			->andReturnUsing(
				function ( $url, $args ) use ( $token, &$auth_validated ) {
					if ( isset( $args['headers']['Authorization'] )
						&& 'Bearer ' . $token === $args['headers']['Authorization'] ) {
						$auth_validated = true;
					}
					return array(
						'body'     => '[]',
						'response' => array( 'code' => 200 ),
					);
				}
			);

		Functions\expect( 'is_wp_error' )
			->andReturn( false );

		Functions\expect( 'wp_remote_retrieve_response_code' )
			->andReturn( 200 );

		Functions\expect( 'wp_remote_retrieve_body' )
			->andReturn( '[]' );

		Functions\expect( 'wp_remote_retrieve_headers' )
			->andReturn( array( 'link' => '' ) );

		Functions\expect( 'current_time' )
			->andReturn( '2024-01-01 00:00:00' );

		$github_api = new \BLST\GitHub_API( $this->cache_manager, array( 'github_token' => $token ) );
		$github_api->get_stats();

		$this->assertTrue( $auth_validated, 'Authorization header should be set with token' );
	}

	/**
	 * Test make_request handles non-200 response.
	 */
	public function test_make_request_handles_non_200_response() {
		$this->cache_manager
			->shouldReceive( 'get' )
			->once()
			->andReturn( false );

		// Note: When make_request returns WP_Error, get_stats returns empty stats.

		Functions\expect( 'add_query_arg' )
			->andReturnUsing(
				function ( $args, $url ) {
					return $url . '?' . http_build_query( $args );
				}
			);

		Functions\expect( 'wp_remote_get' )
			->andReturn(
				array(
					'body'     => 'Rate limit exceeded',
					'response' => array( 'code' => 403 ),
				)
			);

		// is_wp_error is called multiple times.
		// First call is for wp_remote_get response (array, not error).
		// Second call is for WP_Error returned by make_request.
		$call_count = 0;
		Functions\expect( 'is_wp_error' )
			->andReturnUsing(
				// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
				function ( $value ) use ( &$call_count ) {
					++$call_count;
					return $call_count > 1;
				}
			);

		Functions\expect( 'wp_remote_retrieve_response_code' )
			->andReturn( 403 );

		$github_api = new \BLST\GitHub_API( $this->cache_manager, array() );
		$result     = $github_api->get_stats();

		$this->assertIsArray( $result );
		$this->assertTrue( $result['error'] );
	}

	/**
	 * Test empty stats structure is correct.
	 */
	public function test_get_empty_stats_returns_correct_structure() {
		$this->cache_manager
			->shouldReceive( 'get' )
			->once()
			->andReturn( false );

		$wp_error = Mockery::mock( 'WP_Error' );

		Functions\expect( 'add_query_arg' )
			->andReturnUsing(
				function ( $args, $url ) {
					return $url . '?' . http_build_query( $args );
				}
			);

		Functions\expect( 'wp_remote_get' )
			->andReturn( $wp_error );

		Functions\expect( 'is_wp_error' )
			->andReturn( true );

		$github_api = new \BLST\GitHub_API( $this->cache_manager, array() );
		$result     = $github_api->get_stats();

		$expected_keys = array(
			'total_repos',
			'total_stars',
			'total_forks',
			'total_open_issues',
			'total_contributors',
			'total_release_downloads',
			'release_downloads',
			'languages',
			'top_repos',
			'top_repos_by_forks',
			'all_repos',
			'fetched_at',
			'error',
		);

		foreach ( $expected_keys as $key ) {
			$this->assertArrayHasKey( $key, $result );
		}
	}

	/**
	 * Test process_repo_data filters forks with low stars.
	 */
	public function test_process_repo_data_filters_forks_with_low_stars() {
		$this->cache_manager
			->shouldReceive( 'get' )
			->once()
			->andReturn( false );

		$this->cache_manager
			->shouldReceive( 'set' )
			->once()
			->andReturn( true );

		$repos = array(
			array(
				'name'              => 'original-repo',
				'full_name'         => 'bmlt-enabled/original-repo',
				'stargazers_count'  => 10,
				'forks_count'       => 5,
				'open_issues_count' => 2,
				'language'          => 'PHP',
				'html_url'          => 'https://github.com/bmlt-enabled/original-repo',
				'fork'              => false,
				'archived'          => false,
			),
			array(
				'name'              => 'fork-repo',
				'full_name'         => 'bmlt-enabled/fork-repo',
				'stargazers_count'  => 2, // Less than 5 stars.
				'forks_count'       => 1,
				'open_issues_count' => 0,
				'language'          => 'PHP',
				'html_url'          => 'https://github.com/bmlt-enabled/fork-repo',
				'fork'              => true, // This is a fork.
				'archived'          => false,
			),
		);

		Functions\expect( 'add_query_arg' )
			->andReturnUsing(
				function ( $args, $url ) {
					return $url . '?' . http_build_query( $args );
				}
			);

		Functions\expect( 'wp_remote_get' )
			->andReturn(
				array(
					'body'     => wp_json_encode( $repos ),
					'response' => array( 'code' => 200 ),
				)
			);

		Functions\expect( 'is_wp_error' )
			->andReturn( false );

		Functions\expect( 'wp_remote_retrieve_response_code' )
			->andReturn( 200 );

		Functions\expect( 'wp_remote_retrieve_body' )
			->andReturn( wp_json_encode( $repos ) );

		Functions\expect( 'wp_remote_retrieve_headers' )
			->andReturn( array( 'link' => '' ) );

		Functions\expect( 'current_time' )
			->andReturn( '2024-01-01 00:00:00' );

		$github_api = new \BLST\GitHub_API( $this->cache_manager, array() );
		$result     = $github_api->get_stats();

		// Fork with low stars should be filtered out.
		$this->assertEquals( 1, $result['total_repos'] );
		$this->assertEquals( 10, $result['total_stars'] );
	}

	/**
	 * Test process_repo_data sorts by stars descending.
	 */
	public function test_process_repo_data_sorts_by_stars() {
		$this->cache_manager
			->shouldReceive( 'get' )
			->once()
			->andReturn( false );

		$this->cache_manager
			->shouldReceive( 'set' )
			->once()
			->andReturn( true );

		$repos = array(
			array(
				'name'              => 'low-stars',
				'full_name'         => 'bmlt-enabled/low-stars',
				'stargazers_count'  => 5,
				'forks_count'       => 1,
				'open_issues_count' => 0,
				'language'          => 'PHP',
				'html_url'          => 'https://github.com/bmlt-enabled/low-stars',
				'fork'              => false,
				'archived'          => false,
			),
			array(
				'name'              => 'high-stars',
				'full_name'         => 'bmlt-enabled/high-stars',
				'stargazers_count'  => 100,
				'forks_count'       => 50,
				'open_issues_count' => 10,
				'language'          => 'JavaScript',
				'html_url'          => 'https://github.com/bmlt-enabled/high-stars',
				'fork'              => false,
				'archived'          => false,
			),
		);

		Functions\expect( 'add_query_arg' )
			->andReturnUsing(
				function ( $args, $url ) {
					return $url . '?' . http_build_query( $args );
				}
			);

		Functions\expect( 'wp_remote_get' )
			->andReturn(
				array(
					'body'     => wp_json_encode( $repos ),
					'response' => array( 'code' => 200 ),
				)
			);

		Functions\expect( 'is_wp_error' )
			->andReturn( false );

		Functions\expect( 'wp_remote_retrieve_response_code' )
			->andReturn( 200 );

		Functions\expect( 'wp_remote_retrieve_body' )
			->andReturn( wp_json_encode( $repos ) );

		Functions\expect( 'wp_remote_retrieve_headers' )
			->andReturn( array( 'link' => '' ) );

		Functions\expect( 'current_time' )
			->andReturn( '2024-01-01 00:00:00' );

		$github_api = new \BLST\GitHub_API( $this->cache_manager, array() );
		$result     = $github_api->get_stats();

		// High stars repo should be first.
		$this->assertEquals( 'high-stars', $result['top_repos'][0]['name'] );
	}
}
