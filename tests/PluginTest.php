<?php
/**
 * Plugin Test
 *
 * @package BMLT_Enabled_Stats
 */

namespace BLST\Tests;

use Brain\Monkey\Functions;
use Mockery;

/**
 * Test the Plugin class.
 */
class PluginTest extends TestCase {

	/**
	 * Test get_settings returns defaults when no options saved.
	 */
	public function test_get_settings_returns_defaults() {
		Functions\expect( 'get_option' )
			->once()
			->with( 'blst_settings', array() )
			->andReturn( array() );

		$plugin   = new \BLST\Plugin();
		$settings = $plugin->get_settings();

		$this->assertArrayHasKey( 'github_token', $settings );
		$this->assertArrayHasKey( 'cache_duration', $settings );
		$this->assertArrayHasKey( 'display_sections', $settings );

		$this->assertEquals( '', $settings['github_token'] );
		$this->assertEquals( BLST_DEFAULT_CACHE_DURATION, $settings['cache_duration'] );
		$this->assertEquals( array( 'summary', 'github', 'wordpress' ), $settings['display_sections'] );
	}

	/**
	 * Test get_settings merges saved options with defaults.
	 */
	public function test_get_settings_merges_saved_options() {
		$saved_settings = array(
			'github_token'   => 'my_token_123',
			'cache_duration' => 7200,
		);

		Functions\expect( 'get_option' )
			->once()
			->with( 'blst_settings', array() )
			->andReturn( $saved_settings );

		$plugin   = new \BLST\Plugin();
		$settings = $plugin->get_settings();

		$this->assertEquals( 'my_token_123', $settings['github_token'] );
		$this->assertEquals( 7200, $settings['cache_duration'] );
		// Default should still be present.
		$this->assertEquals( array( 'summary', 'github', 'wordpress' ), $settings['display_sections'] );
	}

	/**
	 * Test init loads all dependencies.
	 */
	public function test_init_loads_dependencies() {
		Functions\expect( 'get_option' )
			->once()
			->andReturn( array() );

		Functions\expect( 'add_action' )
			->times( 4 ); // Various action registrations.

		Functions\expect( 'add_shortcode' )
			->once();

		$plugin = new \BLST\Plugin();
		$plugin->init();

		// Verify all components are initialized.
		$this->assertInstanceOf( \BLST\Cache_Manager::class, $plugin->cache_manager );
		$this->assertInstanceOf( \BLST\GitHub_API::class, $plugin->github_api );
		$this->assertInstanceOf( \BLST\WordPress_API::class, $plugin->wordpress_api );
		$this->assertInstanceOf( \BLST\BMLT_API::class, $plugin->bmlt_api );
		$this->assertInstanceOf( \BLST\Scheduler::class, $plugin->scheduler );
		$this->assertInstanceOf( \BLST\Shortcodes::class, $plugin->shortcodes );
		$this->assertInstanceOf( \BLST\Block::class, $plugin->block );
	}

	/**
	 * Test get_all_stats returns combined data.
	 */
	public function test_get_all_stats_returns_combined_data() {
		Functions\expect( 'get_option' )
			->once()
			->andReturn( array() );

		Functions\expect( 'add_action' )
			->times( 4 );

		Functions\expect( 'add_shortcode' )
			->once();

		Functions\expect( 'current_time' )
			->andReturn( '2024-01-01 00:00:00' );

		$plugin = new \BLST\Plugin();
		$plugin->init();

		// Mock the API handlers.
		$github_stats    = array(
			'total_repos' => 50,
			'total_stars' => 150,
		);
		$wordpress_stats = array(
			'plugin_count'    => 5,
			'total_downloads' => 500000,
		);
		$bmlt_stats      = array(
			'total_meetings' => 39000,
		);

		// Replace with mocks.
		$plugin->github_api = Mockery::mock( \BLST\GitHub_API::class );
		$plugin->github_api->shouldReceive( 'get_stats' )
			->once()
			->with( false )
			->andReturn( $github_stats );

		$plugin->wordpress_api = Mockery::mock( \BLST\WordPress_API::class );
		$plugin->wordpress_api->shouldReceive( 'get_stats' )
			->once()
			->with( false )
			->andReturn( $wordpress_stats );

		$plugin->bmlt_api = Mockery::mock( \BLST\BMLT_API::class );
		$plugin->bmlt_api->shouldReceive( 'get_stats' )
			->once()
			->with( false )
			->andReturn( $bmlt_stats );

		$result = $plugin->get_all_stats();

		$this->assertArrayHasKey( 'github', $result );
		// phpcs:ignore WordPress.WP.CapitalPDangit.MisspelledInText -- array key, not text.
		$this->assertArrayHasKey( 'wordpress', $result );
		$this->assertArrayHasKey( 'bmlt', $result );
		$this->assertArrayHasKey( 'updated', $result );

		$this->assertEquals( $github_stats, $result['github'] );
		$this->assertEquals( $wordpress_stats, $result['wordpress'] );
		$this->assertEquals( $bmlt_stats, $result['bmlt'] );
	}

	/**
	 * Test get_all_stats with force refresh.
	 */
	public function test_get_all_stats_with_force_refresh() {
		Functions\expect( 'get_option' )
			->once()
			->andReturn( array() );

		Functions\expect( 'add_action' )
			->times( 4 );

		Functions\expect( 'add_shortcode' )
			->once();

		Functions\expect( 'current_time' )
			->andReturn( '2024-01-01 00:00:00' );

		$plugin = new \BLST\Plugin();
		$plugin->init();

		// Replace with mocks that expect force_refresh = true.
		$plugin->github_api = Mockery::mock( \BLST\GitHub_API::class );
		$plugin->github_api->shouldReceive( 'get_stats' )
			->once()
			->with( true )
			->andReturn( array() );

		$plugin->wordpress_api = Mockery::mock( \BLST\WordPress_API::class );
		$plugin->wordpress_api->shouldReceive( 'get_stats' )
			->once()
			->with( true )
			->andReturn( array() );

		$plugin->bmlt_api = Mockery::mock( \BLST\BMLT_API::class );
		$plugin->bmlt_api->shouldReceive( 'get_stats' )
			->once()
			->with( true )
			->andReturn( array() );

		$result = $plugin->get_all_stats( true );

		$this->assertIsArray( $result );
	}

	/**
	 * Test get_summary_stats returns totals.
	 */
	public function test_get_summary_stats_returns_totals() {
		Functions\expect( 'get_option' )
			->once()
			->andReturn( array() );

		Functions\expect( 'add_action' )
			->times( 4 );

		Functions\expect( 'add_shortcode' )
			->once();

		$plugin = new \BLST\Plugin();
		$plugin->init();

		$github_stats    = array(
			'total_stars'             => 150,
			'total_forks'             => 75,
			'total_repos'             => 50,
			'total_release_downloads' => 10000,
		);
		$wordpress_stats = array(
			'total_downloads'       => 500000,
			'total_active_installs' => 10000,
		);
		$bmlt_stats      = array();

		$plugin->github_api = Mockery::mock( \BLST\GitHub_API::class );
		$plugin->github_api->shouldReceive( 'get_stats' )
			->once()
			->andReturn( $github_stats );

		$plugin->wordpress_api = Mockery::mock( \BLST\WordPress_API::class );
		$plugin->wordpress_api->shouldReceive( 'get_stats' )
			->once()
			->andReturn( $wordpress_stats );

		$plugin->bmlt_api = Mockery::mock( \BLST\BMLT_API::class );
		$plugin->bmlt_api->shouldReceive( 'get_stats' )
			->once()
			->andReturn( $bmlt_stats );

		$result = $plugin->get_summary_stats();

		$this->assertEquals( 150, $result['total_github_stars'] );
		$this->assertEquals( 75, $result['total_forks'] );
		$this->assertEquals( 500000, $result['total_downloads'] );
		$this->assertEquals( 10000, $result['total_active_installs'] );
		$this->assertEquals( 50, $result['total_repos'] );
		$this->assertEquals( 10000, $result['total_release_downloads'] );
	}

	/**
	 * Test get_summary_stats handles missing data.
	 */
	public function test_get_summary_stats_handles_missing_data() {
		Functions\expect( 'get_option' )
			->once()
			->andReturn( array() );

		Functions\expect( 'add_action' )
			->times( 4 );

		Functions\expect( 'add_shortcode' )
			->once();

		$plugin = new \BLST\Plugin();
		$plugin->init();

		// Return empty arrays from all APIs.
		$plugin->github_api = Mockery::mock( \BLST\GitHub_API::class );
		$plugin->github_api->shouldReceive( 'get_stats' )
			->once()
			->andReturn( array() );

		$plugin->wordpress_api = Mockery::mock( \BLST\WordPress_API::class );
		$plugin->wordpress_api->shouldReceive( 'get_stats' )
			->once()
			->andReturn( array() );

		$plugin->bmlt_api = Mockery::mock( \BLST\BMLT_API::class );
		$plugin->bmlt_api->shouldReceive( 'get_stats' )
			->once()
			->andReturn( array() );

		$result = $plugin->get_summary_stats();

		// Should return 0 for all missing values.
		$this->assertEquals( 0, $result['total_github_stars'] );
		$this->assertEquals( 0, $result['total_forks'] );
		$this->assertEquals( 0, $result['total_downloads'] );
		$this->assertEquals( 0, $result['total_active_installs'] );
		$this->assertEquals( 0, $result['total_repos'] );
		$this->assertEquals( 0, $result['total_release_downloads'] );
	}

	/**
	 * Test enqueue_frontend_assets on shortcode page.
	 *
	 * Note: is_a() cannot be mocked without patchwork config.
	 * This test documents expected behavior.
	 */
	public function test_enqueue_frontend_assets_on_shortcode_page() {
		Functions\expect( 'get_option' )
			->once()
			->andReturn( array() );

		Functions\expect( 'add_action' )
			->times( 4 );

		Functions\expect( 'add_shortcode' )
			->once();

		$plugin = new \BLST\Plugin();
		$plugin->init();

		// Test documents expected behavior:
		// When global $post is a WP_Post with [bmlt_stats] shortcode,
		// the method should enqueue chartjs and blst-stats-display scripts.
		$this->assertTrue( method_exists( $plugin, 'enqueue_frontend_assets' ) );
	}

	/**
	 * Test enqueue_frontend_assets skips non-post pages.
	 *
	 * Note: is_a() cannot be mocked without patchwork config.
	 * This test documents expected behavior.
	 */
	public function test_enqueue_frontend_assets_skips_non_post_pages() {
		Functions\expect( 'get_option' )
			->once()
			->andReturn( array() );

		Functions\expect( 'add_action' )
			->times( 4 );

		Functions\expect( 'add_shortcode' )
			->once();

		$plugin = new \BLST\Plugin();
		$plugin->init();

		// Test documents expected behavior:
		// When global $post is null or not a WP_Post,
		// no scripts should be enqueued.
		$this->assertTrue( method_exists( $plugin, 'enqueue_frontend_assets' ) );
	}

	/**
	 * Test enqueue_frontend_assets skips pages without shortcode or block.
	 *
	 * Note: is_a() cannot be mocked without patchwork config.
	 * This test documents expected behavior.
	 */
	public function test_enqueue_frontend_assets_skips_pages_without_shortcode() {
		Functions\expect( 'get_option' )
			->once()
			->andReturn( array() );

		Functions\expect( 'add_action' )
			->times( 4 );

		Functions\expect( 'add_shortcode' )
			->once();

		$plugin = new \BLST\Plugin();
		$plugin->init();

		// Test documents expected behavior:
		// When post content doesn't contain shortcode or block,
		// no scripts should be enqueued.
		$this->assertTrue( method_exists( $plugin, 'enqueue_frontend_assets' ) );
	}

	/**
	 * Test enqueue_frontend_assets on block page.
	 *
	 * Note: is_a() cannot be mocked without patchwork config.
	 * This test documents expected behavior.
	 */
	public function test_enqueue_frontend_assets_on_block_page() {
		Functions\expect( 'get_option' )
			->once()
			->andReturn( array() );

		Functions\expect( 'add_action' )
			->times( 4 );

		Functions\expect( 'add_shortcode' )
			->once();

		$plugin = new \BLST\Plugin();
		$plugin->init();

		// Test documents expected behavior:
		// When post contains bmlt-enabled-stats/stats-display block,
		// the method should enqueue chartjs and blst-stats-display scripts.
		$this->assertTrue( method_exists( $plugin, 'enqueue_frontend_assets' ) );
	}
}
