<?php
/**
 * Cache Manager Test
 *
 * @package BMLT_Enabled_Stats
 */

namespace BLST\Tests;

use Brain\Monkey\Functions;

/**
 * Test the Cache Manager class.
 */
class CacheManagerTest extends TestCase {

	/**
	 * Test get returns false when not cached.
	 */
	public function test_get_returns_false_when_not_cached() {
		
		Functions\expect( 'get_transient' )
			->once()
			->with( 'blst_github_repos' )
			->andReturn( false );

		$cache_manager = new \BLST\Cache_Manager( array() );
		$result        = $cache_manager->get( 'blst_github_repos' );

		$this->assertFalse( $result );
	}

	/**
	 * Test get returns cached data.
	 */
	public function test_get_returns_cached_data() {
		
		$cached_data = array( 'test' => 'data' );

		Functions\expect( 'get_transient' )
			->once()
			->with( 'blst_github_repos' )
			->andReturn( $cached_data );

		$cache_manager = new \BLST\Cache_Manager( array() );
		$result        = $cache_manager->get( 'blst_github_repos' );

		$this->assertEquals( $cached_data, $result );
	}

	/**
	 * Test set stores data with transient.
	 */
	public function test_set_stores_data_with_transient() {
		
		$data = array( 'test' => 'data' );

		Functions\expect( 'set_transient' )
			->once()
			->with( 'blst_github_repos', $data, BLST_DEFAULT_CACHE_DURATION )
			->andReturn( true );

		$cache_manager = new \BLST\Cache_Manager( array() );
		$result        = $cache_manager->set( 'blst_github_repos', $data );

		$this->assertTrue( $result );
	}

	/**
	 * Test set uses custom expiration when provided.
	 */
	public function test_set_uses_custom_expiration() {
		
		$data       = array( 'test' => 'data' );
		$expiration = 7200;

		Functions\expect( 'set_transient' )
			->once()
			->with( 'blst_github_repos', $data, $expiration )
			->andReturn( true );

		$cache_manager = new \BLST\Cache_Manager( array() );
		$result        = $cache_manager->set( 'blst_github_repos', $data, $expiration );

		$this->assertTrue( $result );
	}

	/**
	 * Test delete removes transient.
	 */
	public function test_delete_removes_transient() {
		
		Functions\expect( 'delete_transient' )
			->once()
			->with( 'blst_github_repos' )
			->andReturn( true );

		$cache_manager = new \BLST\Cache_Manager( array() );
		$result        = $cache_manager->delete( 'blst_github_repos' );

		$this->assertTrue( $result );
	}

	/**
	 * Test clear_all removes all transients.
	 */
	public function test_clear_all_removes_all_transients() {
		
		Functions\expect( 'delete_transient' )
			->once()
			->with( 'blst_github_org' )
			->andReturn( true );

		Functions\expect( 'delete_transient' )
			->once()
			->with( 'blst_github_repos' )
			->andReturn( true );

		Functions\expect( 'delete_transient' )
			->once()
			->with( 'blst_wporg_plugins' )
			->andReturn( true );

		Functions\expect( 'delete_transient' )
			->once()
			->with( 'blst_bmlt_aggregator' )
			->andReturn( true );

		$cache_manager = new \BLST\Cache_Manager( array() );
		$cache_manager->clear_all();

		// If we got here without exception, the test passes.
		$this->assertTrue( true );
	}

	/**
	 * Test get_cache_status returns array.
	 */
	public function test_get_cache_status_returns_array() {
		
		// Mock get_option for timeout checks.
		Functions\expect( 'get_option' )
			->times( 4 )
			->andReturn( time() + 3600 );

		// Mock get_transient for data checks.
		Functions\expect( 'get_transient' )
			->times( 4 )
			->andReturn( array( 'test' => 'data' ) );

		$cache_manager = new \BLST\Cache_Manager( array() );
		$status        = $cache_manager->get_cache_status();

		$this->assertIsArray( $status );
		$this->assertArrayHasKey( 'blst_github_org', $status );
		$this->assertArrayHasKey( 'blst_github_repos', $status );
		$this->assertArrayHasKey( 'blst_wporg_plugins', $status );
		$this->assertArrayHasKey( 'blst_bmlt_aggregator', $status );
	}

	/**
	 * Test get_cache_status returns correct structure.
	 */
	public function test_get_cache_status_returns_correct_structure() {
		
		$future_time = time() + 3600;

		Functions\expect( 'get_option' )
			->times( 4 )
			->andReturn( $future_time );

		Functions\expect( 'get_transient' )
			->times( 4 )
			->andReturn( array( 'test' => 'data' ) );

		$cache_manager = new \BLST\Cache_Manager( array() );
		$status        = $cache_manager->get_cache_status();

		$this->assertArrayHasKey( 'exists', $status['blst_github_repos'] );
		$this->assertArrayHasKey( 'expires', $status['blst_github_repos'] );
		$this->assertArrayHasKey( 'expires_in', $status['blst_github_repos'] );
		$this->assertTrue( $status['blst_github_repos']['exists'] );
	}

	/**
	 * Test needs_refresh returns true when not cached.
	 */
	public function test_needs_refresh_returns_true_when_not_cached() {
		
		Functions\expect( 'get_transient' )
			->once()
			->with( 'blst_github_repos' )
			->andReturn( false );

		$cache_manager = new \BLST\Cache_Manager( array() );
		$result        = $cache_manager->needs_refresh( 'blst_github_repos' );

		$this->assertTrue( $result );
	}

	/**
	 * Test needs_refresh returns false when cached.
	 */
	public function test_needs_refresh_returns_false_when_cached() {
		
		Functions\expect( 'get_transient' )
			->once()
			->with( 'blst_github_repos' )
			->andReturn( array( 'test' => 'data' ) );

		$cache_manager = new \BLST\Cache_Manager( array() );
		$result        = $cache_manager->needs_refresh( 'blst_github_repos' );

		$this->assertFalse( $result );
	}

	/**
	 * Test cache duration uses settings when available.
	 */
	public function test_cache_duration_uses_settings() {
		
		$custom_duration = 7200;
		$settings        = array( 'cache_duration' => $custom_duration );
		$data            = array( 'test' => 'data' );

		Functions\expect( 'set_transient' )
			->once()
			->with( 'blst_github_repos', $data, $custom_duration )
			->andReturn( true );

		$cache_manager = new \BLST\Cache_Manager( $settings );
		$cache_manager->set( 'blst_github_repos', $data );

		// Assertion is implicit - if set_transient is called with wrong duration, test fails.
		$this->assertTrue( true );
	}

	/**
	 * Test BMLT cache uses different duration.
	 */
	public function test_bmlt_cache_uses_different_duration() {
		
		$bmlt_duration = 6 * HOUR_IN_SECONDS;
		$settings      = array( 'bmlt_cache_duration' => $bmlt_duration );
		$data          = array( 'test' => 'data' );

		Functions\expect( 'set_transient' )
			->once()
			->with( 'blst_bmlt_aggregator', $data, $bmlt_duration )
			->andReturn( true );

		$cache_manager = new \BLST\Cache_Manager( $settings );
		$cache_manager->set( 'blst_bmlt_aggregator', $data );

		$this->assertTrue( true );
	}

	/**
	 * Test BMLT cache uses default 12 hour duration.
	 */
	public function test_bmlt_cache_uses_default_twelve_hour_duration() {
		
		$default_bmlt_duration = 12 * HOUR_IN_SECONDS;
		$data                  = array( 'test' => 'data' );

		Functions\expect( 'set_transient' )
			->once()
			->with( 'blst_bmlt_aggregator', $data, $default_bmlt_duration )
			->andReturn( true );

		$cache_manager = new \BLST\Cache_Manager( array() );
		$cache_manager->set( 'blst_bmlt_aggregator', $data );

		$this->assertTrue( true );
	}
}
