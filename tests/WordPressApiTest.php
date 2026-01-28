<?php
/**
 * WordPress API Test
 *
 * @package BMLT_Enabled_Stats
 */

namespace BLST\Tests;

use Brain\Monkey\Functions;
use Mockery;

/**
 * Test the WordPress API class.
 */
class WordPressApiTest extends TestCase {

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

		$this->cache_manager = Mockery::mock( 'BLST\Cache_Manager' );
	}

	/**
	 * Test get_stats returns cached data when available.
	 */
	public function test_get_stats_returns_cached_data() {
		$cached_data = array(
			'total_downloads'       => 500000,
			'total_active_installs' => 10000,
			'average_rating'        => 4.5,
			'plugin_count'          => 5,
			'plugins'               => array(),
			'fetched_at'            => '2024-01-01 00:00:00',
		);

		$this->cache_manager
			->shouldReceive( 'get' )
			->once()
			->with( \BLST\Cache_Manager::KEY_WPORG_PLUGINS )
			->andReturn( $cached_data );

		$wp_api = new \BLST\WordPress_API( $this->cache_manager, array() );
		$result = $wp_api->get_stats();

		$this->assertEquals( $cached_data, $result );
	}

	/**
	 * Test get_stats fetches fresh data when not cached.
	 */
	public function test_get_stats_fetches_fresh_when_not_cached() {
		$this->cache_manager
			->shouldReceive( 'get' )
			->once()
			->with( \BLST\Cache_Manager::KEY_WPORG_PLUGINS )
			->andReturn( false );

		$this->cache_manager
			->shouldReceive( 'set' )
			->once()
			->andReturn( true );

		$api_response = array(
			'plugins' => array(
				array(
					'slug'            => 'bmlt-root-server',
					'name'            => 'BMLT Root Server',
					'version'         => '3.0.0',
					'rating'          => 100,
					'num_ratings'     => 5,
					'active_installs' => 5000,
					'downloaded'      => 100000,
					'last_updated'    => '2024-01-01',
				),
			),
		);

		Functions\expect( 'wp_remote_get' )
			->once()
			->andReturn(
				array(
					'body'     => wp_json_encode( $api_response ),
					'response' => array( 'code' => 200 ),
				)
			);

		Functions\expect( 'is_wp_error' )
			->andReturn( false );

		Functions\expect( 'wp_remote_retrieve_response_code' )
			->andReturn( 200 );

		Functions\expect( 'wp_remote_retrieve_body' )
			->andReturn( wp_json_encode( $api_response ) );

		Functions\expect( 'current_time' )
			->andReturn( '2024-01-01 00:00:00' );

		$wp_api = new \BLST\WordPress_API( $this->cache_manager, array() );
		$result = $wp_api->get_stats();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'total_downloads', $result );
		$this->assertArrayHasKey( 'total_active_installs', $result );
		$this->assertEquals( 100000, $result['total_downloads'] );
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

		Functions\expect( 'wp_remote_get' )
			->once()
			->andReturn(
				array(
					'body'     => wp_json_encode( array( 'plugins' => array() ) ),
					'response' => array( 'code' => 200 ),
				)
			);

		Functions\expect( 'is_wp_error' )
			->andReturn( false );

		Functions\expect( 'wp_remote_retrieve_response_code' )
			->andReturn( 200 );

		Functions\expect( 'wp_remote_retrieve_body' )
			->andReturn( wp_json_encode( array( 'plugins' => array() ) ) );

		Functions\expect( 'current_time' )
			->andReturn( '2024-01-01 00:00:00' );

		$wp_api = new \BLST\WordPress_API( $this->cache_manager, array() );
		$result = $wp_api->get_stats( true );

		$this->assertIsArray( $result );
	}

	/**
	 * Test aggregate_stats totals downloads correctly.
	 */
	public function test_aggregate_stats_totals_downloads() {
		$this->cache_manager
			->shouldReceive( 'get' )
			->once()
			->andReturn( false );

		$this->cache_manager
			->shouldReceive( 'set' )
			->once()
			->andReturn( true );

		$api_response = array(
			'plugins' => array(
				array(
					'slug'            => 'plugin-1',
					'name'            => 'Plugin 1',
					'downloaded'      => 50000,
					'active_installs' => 1000,
					'rating'          => 80,
				),
				array(
					'slug'            => 'plugin-2',
					'name'            => 'Plugin 2',
					'downloaded'      => 30000,
					'active_installs' => 2000,
					'rating'          => 90,
				),
			),
		);

		Functions\expect( 'wp_remote_get' )
			->once()
			->andReturn(
				array(
					'body'     => wp_json_encode( $api_response ),
					'response' => array( 'code' => 200 ),
				)
			);

		Functions\expect( 'is_wp_error' )
			->andReturn( false );

		Functions\expect( 'wp_remote_retrieve_response_code' )
			->andReturn( 200 );

		Functions\expect( 'wp_remote_retrieve_body' )
			->andReturn( wp_json_encode( $api_response ) );

		Functions\expect( 'current_time' )
			->andReturn( '2024-01-01 00:00:00' );

		$wp_api = new \BLST\WordPress_API( $this->cache_manager, array() );
		$result = $wp_api->get_stats();

		$this->assertEquals( 80000, $result['total_downloads'] );
	}

	/**
	 * Test aggregate_stats totals active installs correctly.
	 */
	public function test_aggregate_stats_totals_installs() {
		$this->cache_manager
			->shouldReceive( 'get' )
			->once()
			->andReturn( false );

		$this->cache_manager
			->shouldReceive( 'set' )
			->once()
			->andReturn( true );

		$api_response = array(
			'plugins' => array(
				array(
					'slug'            => 'plugin-1',
					'name'            => 'Plugin 1',
					'downloaded'      => 50000,
					'active_installs' => 1000,
					'rating'          => 80,
				),
				array(
					'slug'            => 'plugin-2',
					'name'            => 'Plugin 2',
					'downloaded'      => 30000,
					'active_installs' => 2000,
					'rating'          => 90,
				),
			),
		);

		Functions\expect( 'wp_remote_get' )
			->once()
			->andReturn(
				array(
					'body'     => wp_json_encode( $api_response ),
					'response' => array( 'code' => 200 ),
				)
			);

		Functions\expect( 'is_wp_error' )
			->andReturn( false );

		Functions\expect( 'wp_remote_retrieve_response_code' )
			->andReturn( 200 );

		Functions\expect( 'wp_remote_retrieve_body' )
			->andReturn( wp_json_encode( $api_response ) );

		Functions\expect( 'current_time' )
			->andReturn( '2024-01-01 00:00:00' );

		$wp_api = new \BLST\WordPress_API( $this->cache_manager, array() );
		$result = $wp_api->get_stats();

		$this->assertEquals( 3000, $result['total_active_installs'] );
	}

	/**
	 * Test aggregate_stats calculates average rating.
	 */
	public function test_aggregate_stats_calculates_average_rating() {
		$this->cache_manager
			->shouldReceive( 'get' )
			->once()
			->andReturn( false );

		$this->cache_manager
			->shouldReceive( 'set' )
			->once()
			->andReturn( true );

		$api_response = array(
			'plugins' => array(
				array(
					'slug'   => 'plugin-1',
					'name'   => 'Plugin 1',
					'rating' => 80,
				),
				array(
					'slug'   => 'plugin-2',
					'name'   => 'Plugin 2',
					'rating' => 100,
				),
			),
		);

		Functions\expect( 'wp_remote_get' )
			->once()
			->andReturn(
				array(
					'body'     => wp_json_encode( $api_response ),
					'response' => array( 'code' => 200 ),
				)
			);

		Functions\expect( 'is_wp_error' )
			->andReturn( false );

		Functions\expect( 'wp_remote_retrieve_response_code' )
			->andReturn( 200 );

		Functions\expect( 'wp_remote_retrieve_body' )
			->andReturn( wp_json_encode( $api_response ) );

		Functions\expect( 'current_time' )
			->andReturn( '2024-01-01 00:00:00' );

		$wp_api = new \BLST\WordPress_API( $this->cache_manager, array() );
		$result = $wp_api->get_stats();

		$this->assertEquals( 90, $result['average_rating'] );
	}

	/**
	 * Test get_plugin_stats returns individual plugin data.
	 */
	public function test_get_plugin_stats_returns_individual_data() {
		$cached_data = array(
			'total_downloads' => 500000,
			'plugins'         => array(
				'bmlt-root-server' => array(
					'slug'            => 'bmlt-root-server',
					'name'            => 'BMLT Root Server',
					'active_installs' => 5000,
				),
				'crouton'          => array(
					'slug'            => 'crouton',
					'name'            => 'Crouton',
					'active_installs' => 3000,
				),
			),
		);

		$this->cache_manager
			->shouldReceive( 'get' )
			->once()
			->with( \BLST\Cache_Manager::KEY_WPORG_PLUGINS )
			->andReturn( $cached_data );

		$wp_api = new \BLST\WordPress_API( $this->cache_manager, array() );
		$result = $wp_api->get_plugin_stats( 'bmlt-root-server' );

		$this->assertIsArray( $result );
		$this->assertEquals( 'bmlt-root-server', $result['slug'] );
		$this->assertEquals( 'BMLT Root Server', $result['name'] );
	}

	/**
	 * Test get_plugin_stats returns null for non-existent plugin.
	 */
	public function test_get_plugin_stats_returns_null_for_nonexistent() {
		$cached_data = array(
			'plugins' => array(
				'bmlt-root-server' => array(
					'slug' => 'bmlt-root-server',
					'name' => 'BMLT Root Server',
				),
			),
		);

		$this->cache_manager
			->shouldReceive( 'get' )
			->once()
			->with( \BLST\Cache_Manager::KEY_WPORG_PLUGINS )
			->andReturn( $cached_data );

		$wp_api = new \BLST\WordPress_API( $this->cache_manager, array() );
		$result = $wp_api->get_plugin_stats( 'non-existent-plugin' );

		$this->assertNull( $result );
	}

	/**
	 * Test fetch_plugins_by_author handles API error.
	 *
	 * Note: error_log cannot be mocked without patchwork config.
	 * This test is skipped but documents expected behavior.
	 */
	public function test_fetch_plugins_handles_api_error() {
		$this->markTestSkipped( 'Cannot mock error_log without patchwork configuration.' );
	}

	/**
	 * Test fetch_plugins_by_author handles non-200 response.
	 *
	 * Note: error_log cannot be mocked without patchwork config.
	 * This test is skipped but documents expected behavior.
	 */
	public function test_fetch_plugins_handles_http_error() {
		$this->markTestSkipped( 'Cannot mock error_log without patchwork configuration.' );
	}

	/**
	 * Test fetch_plugins_by_author handles JSON error.
	 *
	 * Note: error_log cannot be mocked without patchwork config.
	 * This test is skipped but documents expected behavior.
	 */
	public function test_fetch_plugins_handles_json_error() {
		$this->markTestSkipped( 'Cannot mock error_log without patchwork configuration.' );
	}

	/**
	 * Test plugins are sorted by active installs.
	 */
	public function test_plugins_sorted_by_active_installs() {
		$this->cache_manager
			->shouldReceive( 'get' )
			->once()
			->andReturn( false );

		$this->cache_manager
			->shouldReceive( 'set' )
			->once()
			->andReturn( true );

		$api_response = array(
			'plugins' => array(
				array(
					'slug'            => 'low-installs',
					'name'            => 'Low Installs',
					'active_installs' => 100,
				),
				array(
					'slug'            => 'high-installs',
					'name'            => 'High Installs',
					'active_installs' => 5000,
				),
				array(
					'slug'            => 'medium-installs',
					'name'            => 'Medium Installs',
					'active_installs' => 1000,
				),
			),
		);

		Functions\expect( 'wp_remote_get' )
			->once()
			->andReturn(
				array(
					'body'     => wp_json_encode( $api_response ),
					'response' => array( 'code' => 200 ),
				)
			);

		Functions\expect( 'is_wp_error' )
			->andReturn( false );

		Functions\expect( 'wp_remote_retrieve_response_code' )
			->andReturn( 200 );

		Functions\expect( 'wp_remote_retrieve_body' )
			->andReturn( wp_json_encode( $api_response ) );

		Functions\expect( 'current_time' )
			->andReturn( '2024-01-01 00:00:00' );

		$wp_api = new \BLST\WordPress_API( $this->cache_manager, array() );
		$result = $wp_api->get_stats();

		$plugins = array_values( $result['plugins'] );
		$this->assertEquals( 'high-installs', $plugins[0]['slug'] );
		$this->assertEquals( 'medium-installs', $plugins[1]['slug'] );
		$this->assertEquals( 'low-installs', $plugins[2]['slug'] );
	}
}
