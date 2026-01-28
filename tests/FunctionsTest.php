<?php
/**
 * Functions Test
 *
 * @package BMLT_Enabled_Stats
 */

namespace BLST\Tests;

use Brain\Monkey\Functions;

/**
 * Test the helper functions.
 */
class FunctionsTest extends TestCase {

	/**
	 * Set up test fixtures.
	 */
	protected function set_up() {
		parent::set_up();

		// Reset global plugin instance.
		unset( $GLOBALS['blst_plugin'] );
	}

	/**
	 * Tear down test fixtures.
	 */
	protected function tear_down() {
		// Clean up global.
		unset( $GLOBALS['blst_plugin'] );
		parent::tear_down();
	}

	/**
	 * Test blst_get_plugin_instance returns singleton.
	 */
	public function test_blst_get_plugin_instance_returns_singleton() {
		Functions\expect( 'get_option' )
			->once()
			->andReturn( array() );

		Functions\expect( 'add_action' )
			->times( 4 );

		Functions\expect( 'add_shortcode' )
			->once();

		$instance1 = \BLST\blst_get_plugin_instance();
		$instance2 = \BLST\blst_get_plugin_instance();

		$this->assertSame( $instance1, $instance2 );
	}

	/**
	 * Test blst_get_plugin_instance initializes plugin.
	 */
	public function test_blst_get_plugin_instance_initializes_plugin() {
		Functions\expect( 'get_option' )
			->once()
			->andReturn( array() );

		Functions\expect( 'add_action' )
			->times( 4 );

		Functions\expect( 'add_shortcode' )
			->once();

		$instance = \BLST\blst_get_plugin_instance();

		$this->assertInstanceOf( \BLST\Plugin::class, $instance );
		// Verify init was called by checking components exist.
		$this->assertInstanceOf( \BLST\Cache_Manager::class, $instance->cache_manager );
		$this->assertInstanceOf( \BLST\GitHub_API::class, $instance->github_api );
	}

	/**
	 * Test blst_get_plugin_instance returns existing global.
	 */
	public function test_blst_get_plugin_instance_returns_existing_global() {
		// Set global manually.
		$mock_plugin             = new \stdClass();
		$mock_plugin->test_value = 'test';
		$GLOBALS['blst_plugin']  = $mock_plugin;

		$instance = \BLST\blst_get_plugin_instance();

		$this->assertSame( $mock_plugin, $instance );
		$this->assertEquals( 'test', $instance->test_value );
	}
}
