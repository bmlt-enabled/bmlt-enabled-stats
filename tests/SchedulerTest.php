<?php
/**
 * Scheduler Test
 *
 * @package BMLT_Enabled_Stats
 */

namespace BLST\Tests;

use Brain\Monkey\Functions;
use Mockery;

/**
 * Test the Scheduler class.
 */
class SchedulerTest extends TestCase {

	/**
	 * Mock plugin instance.
	 *
	 * @var \BLST\Plugin|Mockery\MockInterface
	 */
	private $plugin;

	/**
	 * Mock cache manager.
	 *
	 * @var \BLST\Cache_Manager|Mockery\MockInterface
	 */
	private $cache_manager;

	/**
	 * Mock GitHub API.
	 *
	 * @var \BLST\GitHub_API|Mockery\MockInterface
	 */
	private $github_api;

	/**
	 * Mock WordPress API.
	 *
	 * @var \BLST\WordPress_API|Mockery\MockInterface
	 */
	private $wordpress_api;

	/**
	 * Mock BMLT API.
	 *
	 * @var \BLST\BMLT_API|Mockery\MockInterface
	 */
	private $bmlt_api;

	/**
	 * Set up test fixtures.
	 */
	protected function set_up() {
		parent::set_up();

		// Create mock plugin with mock components.
		$this->plugin        = Mockery::mock( 'BLST\Plugin' );
		$this->cache_manager = Mockery::mock( 'BLST\Cache_Manager' );
		$this->github_api    = Mockery::mock( 'BLST\GitHub_API' );
		$this->wordpress_api = Mockery::mock( 'BLST\WordPress_API' );
		$this->bmlt_api      = Mockery::mock( 'BLST\BMLT_API' );

		// Set up public properties on plugin mock.
		$this->plugin->cache_manager = $this->cache_manager;
		$this->plugin->github_api    = $this->github_api;
		$this->plugin->wordpress_api = $this->wordpress_api;
		$this->plugin->bmlt_api      = $this->bmlt_api;
	}

	/**
	 * Test refresh_all_stats clears cache and fetches all APIs.
	 */
	public function test_refresh_all_stats_updates_all_apis() {
		$this->cache_manager
			->shouldReceive( 'clear_all' )
			->once();

		$this->github_api
			->shouldReceive( 'get_stats' )
			->once()
			->with( true )
			->andReturn( array() );

		$this->wordpress_api
			->shouldReceive( 'get_stats' )
			->once()
			->with( true )
			->andReturn( array() );

		$this->bmlt_api
			->shouldReceive( 'get_stats' )
			->once()
			->with( true )
			->andReturn( array() );

		Functions\expect( 'update_option' )
			->once()
			->with( 'blst_last_refresh', Mockery::type( 'string' ) )
			->andReturn( true );

		Functions\expect( 'current_time' )
			->once()
			->with( 'mysql' )
			->andReturn( '2024-01-01 00:00:00' );

		$scheduler = new \BLST\Scheduler( $this->plugin );
		$scheduler->refresh_all_stats();

		// If we got here without exception, the test passes.
		$this->assertTrue( true );
	}

	/**
	 * Test get_last_refresh returns timestamp.
	 */
	public function test_get_last_refresh_returns_timestamp() {
		$last_refresh = '2024-01-01 12:00:00';

		Functions\expect( 'get_option' )
			->once()
			->with( 'blst_last_refresh', false )
			->andReturn( $last_refresh );

		$scheduler = new \BLST\Scheduler( $this->plugin );
		$result    = $scheduler->get_last_refresh();

		$this->assertEquals( $last_refresh, $result );
	}

	/**
	 * Test get_last_refresh returns false when not set.
	 */
	public function test_get_last_refresh_returns_false_when_not_set() {
		Functions\expect( 'get_option' )
			->once()
			->with( 'blst_last_refresh', false )
			->andReturn( false );

		$scheduler = new \BLST\Scheduler( $this->plugin );
		$result    = $scheduler->get_last_refresh();

		$this->assertFalse( $result );
	}

	/**
	 * Test get_next_scheduled returns future timestamp.
	 */
	public function test_get_next_scheduled_returns_future_time() {
		$next_scheduled = time() + 14400; // 4 hours from now.

		Functions\expect( 'wp_next_scheduled' )
			->once()
			->with( \BLST\Scheduler::CRON_HOOK )
			->andReturn( $next_scheduled );

		$scheduler = new \BLST\Scheduler( $this->plugin );
		$result    = $scheduler->get_next_scheduled();

		$this->assertEquals( $next_scheduled, $result );
	}

	/**
	 * Test get_next_scheduled returns false when not scheduled.
	 */
	public function test_get_next_scheduled_returns_false_when_not_scheduled() {
		Functions\expect( 'wp_next_scheduled' )
			->once()
			->with( \BLST\Scheduler::CRON_HOOK )
			->andReturn( false );

		$scheduler = new \BLST\Scheduler( $this->plugin );
		$result    = $scheduler->get_next_scheduled();

		$this->assertFalse( $result );
	}

	/**
	 * Test trigger_manual_refresh returns true on success.
	 */
	public function test_trigger_manual_refresh_returns_true_on_success() {
		$this->cache_manager
			->shouldReceive( 'clear_all' )
			->once();

		$this->github_api
			->shouldReceive( 'get_stats' )
			->once()
			->with( true )
			->andReturn( array() );

		$this->wordpress_api
			->shouldReceive( 'get_stats' )
			->once()
			->with( true )
			->andReturn( array() );

		$this->bmlt_api
			->shouldReceive( 'get_stats' )
			->once()
			->with( true )
			->andReturn( array() );

		Functions\expect( 'update_option' )
			->once()
			->andReturn( true );

		Functions\expect( 'current_time' )
			->once()
			->andReturn( '2024-01-01 00:00:00' );

		$scheduler = new \BLST\Scheduler( $this->plugin );
		$result    = $scheduler->trigger_manual_refresh();

		$this->assertTrue( $result );
	}

	/**
	 * Test trigger_manual_refresh returns false on exception.
	 *
	 * Note: error_log cannot be mocked without patchwork config.
	 * This test is skipped but documents expected behavior.
	 */
	public function test_trigger_manual_refresh_returns_false_on_exception() {
		$this->markTestSkipped( 'Cannot mock error_log without patchwork configuration.' );
	}

	/**
	 * Test reschedule clears existing schedule and creates new one.
	 */
	public function test_reschedule_updates_cron() {
		$existing_timestamp = time() + 3600;

		Functions\expect( 'wp_next_scheduled' )
			->once()
			->with( \BLST\Scheduler::CRON_HOOK )
			->andReturn( $existing_timestamp );

		Functions\expect( 'wp_unschedule_event' )
			->once()
			->with( $existing_timestamp, \BLST\Scheduler::CRON_HOOK )
			->andReturn( true );

		Functions\expect( 'wp_schedule_event' )
			->once()
			->with( Mockery::type( 'int' ), 'four_hours', \BLST\Scheduler::CRON_HOOK )
			->andReturn( true );

		$scheduler = new \BLST\Scheduler( $this->plugin );
		$result    = $scheduler->reschedule();

		$this->assertTrue( $result );
	}

	/**
	 * Test reschedule creates new schedule when none exists.
	 */
	public function test_reschedule_creates_new_schedule_when_none_exists() {
		Functions\expect( 'wp_next_scheduled' )
			->once()
			->with( \BLST\Scheduler::CRON_HOOK )
			->andReturn( false );

		// wp_unschedule_event should NOT be called.

		Functions\expect( 'wp_schedule_event' )
			->once()
			->with( Mockery::type( 'int' ), 'four_hours', \BLST\Scheduler::CRON_HOOK )
			->andReturn( true );

		$scheduler = new \BLST\Scheduler( $this->plugin );
		$result    = $scheduler->reschedule();

		$this->assertTrue( $result );
	}

	/**
	 * Test is_scheduled returns true when scheduled.
	 */
	public function test_is_scheduled_returns_true_when_scheduled() {
		Functions\expect( 'wp_next_scheduled' )
			->once()
			->with( \BLST\Scheduler::CRON_HOOK )
			->andReturn( time() + 3600 );

		$scheduler = new \BLST\Scheduler( $this->plugin );
		$result    = $scheduler->is_scheduled();

		$this->assertTrue( $result );
	}

	/**
	 * Test is_scheduled returns false when not scheduled.
	 */
	public function test_is_scheduled_returns_false_when_not_scheduled() {
		Functions\expect( 'wp_next_scheduled' )
			->once()
			->with( \BLST\Scheduler::CRON_HOOK )
			->andReturn( false );

		$scheduler = new \BLST\Scheduler( $this->plugin );
		$result    = $scheduler->is_scheduled();

		$this->assertFalse( $result );
	}

	/**
	 * Test cron hook constant is correct.
	 */
	public function test_cron_hook_constant() {
		$this->assertEquals( 'blst_stats_refresh', \BLST\Scheduler::CRON_HOOK );
	}
}
