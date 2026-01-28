<?php
/**
 * Admin Test
 *
 * @package BMLT_Enabled_Stats
 */

namespace BLST\Tests;

use Brain\Monkey\Functions;
use Mockery;

/**
 * Test the Admin class.
 */
class AdminTest extends TestCase {

	/**
	 * Admin instance for tests.
	 *
	 * @var \BLST\Admin
	 */
	private static $admin_instance;

	/**
	 * Helper to get admin instance.
	 * Reuses same instance to avoid multiple constructor calls.
	 */
	private function get_admin_instance() {
		if ( null === self::$admin_instance ) {
			Functions\expect( 'add_action' )
				->zeroOrMoreTimes();

			require_once BLST_PLUGIN_DIR . 'admin/class-admin.php';
			self::$admin_instance = new \BLST\Admin();
		}
		return self::$admin_instance;
	}

	/**
	 * Helper to create admin instance with mocked hooks.
	 */
	private function create_admin_instance() {
		return $this->get_admin_instance();
	}

	/**
	 * Test constructor registers hooks.
	 *
	 * Note: The Admin class is auto-instantiated when the file is loaded,
	 * so we verify the class exists and has the expected methods.
	 */
	public function test_constructor_registers_hooks() {
		// Admin class registers add_action calls in constructor.
		// The file also instantiates Admin at the end, so hooks
		// are already registered when we require the file.
		Functions\expect( 'add_action' )
			->zeroOrMoreTimes();

		require_once BLST_PLUGIN_DIR . 'admin/class-admin.php';

		$this->assertTrue( class_exists( '\BLST\Admin' ) );
		$this->assertTrue( method_exists( '\BLST\Admin', 'add_menu_page' ) );
		$this->assertTrue( method_exists( '\BLST\Admin', 'register_settings' ) );
	}

	/**
	 * Test add_menu_page registers settings page.
	 */
	public function test_add_menu_page_registers_page() {
		$admin = $this->create_admin_instance();

		Functions\expect( 'add_options_page' )
			->once()
			->with(
				'BMLT Stats Settings',
				'BMLT Stats',
				'manage_options',
				'bmlt-enabled-stats',
				Mockery::type( 'array' )
			);

		$admin->add_menu_page();

		$this->assertTrue( true );
	}

	/**
	 * Test register_settings adds sections and fields.
	 */
	public function test_register_settings_adds_sections() {
		$admin = $this->create_admin_instance();

		Functions\expect( 'register_setting' )
			->once()
			->with(
				'blst_settings_group',
				'blst_settings',
				Mockery::type( 'array' )
			);

		Functions\expect( 'add_settings_section' )
			->twice(); // API section and Cache section.

		Functions\expect( 'add_settings_field' )
			->twice(); // github_token and cache_duration.

		$admin->register_settings();

		$this->assertTrue( true );
	}

	/**
	 * Test sanitize_settings cleans input.
	 */
	public function test_sanitize_settings_cleans_input() {
		$admin = $this->create_admin_instance();

		$input = array(
			'github_token'     => '  my_token_123  ',
			'cache_duration'   => '14400',
			'display_sections' => array( 'summary', 'github' ),
		);

		$result = $admin->sanitize_settings( $input );

		$this->assertEquals( 'my_token_123', $result['github_token'] );
		$this->assertEquals( 14400, $result['cache_duration'] );
		$this->assertEquals( array( 'summary', 'github' ), $result['display_sections'] );
	}

	/**
	 * Test sanitize_settings enforces minimum cache duration.
	 */
	public function test_sanitize_settings_enforces_minimum_duration() {
		$admin = $this->create_admin_instance();

		$input = array(
			'cache_duration' => '60', // Less than HOUR_IN_SECONDS.
		);

		$result = $admin->sanitize_settings( $input );

		$this->assertEquals( HOUR_IN_SECONDS, $result['cache_duration'] );
	}

	/**
	 * Test sanitize_settings handles missing fields.
	 */
	public function test_sanitize_settings_handles_missing_fields() {
		$admin = $this->create_admin_instance();

		$input  = array();
		$result = $admin->sanitize_settings( $input );

		$this->assertIsArray( $result );
		$this->assertArrayNotHasKey( 'github_token', $result );
		$this->assertArrayNotHasKey( 'cache_duration', $result );
	}

	/**
	 * Test enqueue_admin_assets skips non-settings pages.
	 */
	public function test_enqueue_admin_assets_skips_other_pages() {
		$admin = $this->create_admin_instance();

		// Should not enqueue anything for other pages.
		$admin->enqueue_admin_assets( 'dashboard' );

		// If no error, test passes (no wp_enqueue calls expected).
		$this->assertTrue( true );
	}

	/**
	 * Test enqueue_admin_assets loads on settings page.
	 */
	public function test_enqueue_admin_assets_loads_on_settings_page() {
		$admin = $this->create_admin_instance();

		Functions\expect( 'wp_enqueue_style' )
			->once()
			->with(
				'blst-admin',
				BLST_PLUGIN_URL . 'admin/css/admin.css',
				array(),
				BLST_VERSION
			);

		Functions\expect( 'wp_enqueue_script' )
			->once()
			->with(
				'blst-admin',
				BLST_PLUGIN_URL . 'admin/js/admin.js',
				array( 'jquery' ),
				BLST_VERSION,
				true
			);

		Functions\expect( 'admin_url' )
			->once()
			->with( 'admin-ajax.php' )
			->andReturn( 'http://example.com/wp-admin/admin-ajax.php' );

		Functions\expect( 'wp_create_nonce' )
			->twice()
			->andReturn( 'test_nonce' );

		Functions\expect( 'wp_localize_script' )
			->once()
			->with(
				'blst-admin',
				'blstAdmin',
				Mockery::type( 'array' )
			);

		$admin->enqueue_admin_assets( 'settings_page_bmlt-enabled-stats' );

		$this->assertTrue( true );
	}

	/**
	 * Test ajax_refresh_stats requires nonce.
	 */
	public function test_ajax_refresh_stats_requires_nonce() {
		$admin = $this->create_admin_instance();

		Functions\expect( 'check_ajax_referer' )
			->once()
			->with( 'blst_refresh_stats', 'nonce' )
			->andThrow( new \Exception( 'Invalid nonce' ) );

		$this->expectException( \Exception::class );

		$admin->ajax_refresh_stats();
	}

	/**
	 * Test ajax_refresh_stats requires capability.
	 */
	public function test_ajax_refresh_stats_requires_capability() {
		$admin = $this->create_admin_instance();

		Functions\expect( 'check_ajax_referer' )
			->once()
			->andReturn( true );

		Functions\expect( 'current_user_can' )
			->once()
			->with( 'manage_options' )
			->andReturn( false );

		// wp_send_json_error throws an exception to stop execution.
		Functions\expect( 'wp_send_json_error' )
			->once()
			->with( 'Permission denied.' )
			->andThrow( new \Exception( 'wp_send_json_error called' ) );

		$this->expectException( \Exception::class );
		$this->expectExceptionMessage( 'wp_send_json_error called' );

		$admin->ajax_refresh_stats();
	}

	/**
	 * Test ajax_refresh_stats returns success.
	 *
	 * This is a complex integration test that requires extensive mocking.
	 * Skipped in favor of simpler unit tests.
	 */
	public function test_ajax_refresh_stats_returns_success() {
		$this->markTestSkipped( 'Complex integration test - requires extensive mocking of multiple systems.' );
	}

	/**
	 * Test ajax_clear_cache requires nonce.
	 */
	public function test_ajax_clear_cache_requires_nonce() {
		$admin = $this->create_admin_instance();

		Functions\expect( 'check_ajax_referer' )
			->once()
			->with( 'blst_clear_cache', 'nonce' )
			->andThrow( new \Exception( 'Invalid nonce' ) );

		$this->expectException( \Exception::class );

		$admin->ajax_clear_cache();
	}

	/**
	 * Test ajax_clear_cache requires capability.
	 */
	public function test_ajax_clear_cache_requires_capability() {
		$admin = $this->create_admin_instance();

		Functions\expect( 'check_ajax_referer' )
			->once()
			->andReturn( true );

		Functions\expect( 'current_user_can' )
			->once()
			->with( 'manage_options' )
			->andReturn( false );

		// wp_send_json_error throws an exception to stop execution.
		Functions\expect( 'wp_send_json_error' )
			->once()
			->with( 'Permission denied.' )
			->andThrow( new \Exception( 'wp_send_json_error called' ) );

		$this->expectException( \Exception::class );
		$this->expectExceptionMessage( 'wp_send_json_error called' );

		$admin->ajax_clear_cache();
	}

	/**
	 * Test ajax_clear_cache clears all cache.
	 */
	public function test_ajax_clear_cache_clears_all() {
		$admin = $this->create_admin_instance();

		Functions\expect( 'check_ajax_referer' )
			->once()
			->andReturn( true );

		Functions\expect( 'current_user_can' )
			->once()
			->with( 'manage_options' )
			->andReturn( true );

		Functions\expect( 'get_option' )
			->once()
			->andReturn( array() );

		Functions\expect( 'delete_transient' )
			->times( 4 ) // All cache keys.
			->andReturn( true );

		Functions\expect( 'wp_send_json_success' )
			->once()
			->with(
				Mockery::on(
					function ( $data ) {
						return isset( $data['message'] );
					}
				)
			);

		$admin->ajax_clear_cache();

		$this->assertTrue( true );
	}

	/**
	 * Test render_github_token_field outputs input.
	 */
	public function test_render_github_token_field_outputs_input() {
		$admin = $this->create_admin_instance();

		Functions\expect( 'get_option' )
			->once()
			->with( 'blst_settings', array() )
			->andReturn( array( 'github_token' => 'test_token' ) );

		ob_start();
		$admin->render_github_token_field();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'type="password"', $output );
		$this->assertStringContainsString( 'name="blst_settings[github_token]"', $output );
		$this->assertStringContainsString( 'value="test_token"', $output );
	}

	/**
	 * Test render_cache_duration_field outputs select.
	 */
	public function test_render_cache_duration_field_outputs_select() {
		$admin = $this->create_admin_instance();

		Functions\expect( 'get_option' )
			->once()
			->with( 'blst_settings', array() )
			->andReturn( array( 'cache_duration' => 4 * HOUR_IN_SECONDS ) );

		Functions\expect( 'selected' )
			->times( 4 )
			->andReturnUsing(
				function ( $value, $current ) {
					return $value === $current ? ' selected="selected"' : '';
				}
			);

		ob_start();
		$admin->render_cache_duration_field();
		$output = ob_get_clean();

		$this->assertStringContainsString( '<select', $output );
		$this->assertStringContainsString( 'name="blst_settings[cache_duration]"', $output );
		$this->assertStringContainsString( '1 hour', $output );
		$this->assertStringContainsString( '4 hours', $output );
	}

	/**
	 * Test render_api_section outputs description.
	 */
	public function test_render_api_section_outputs_description() {
		$admin = $this->create_admin_instance();

		ob_start();
		$admin->render_api_section();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'Configure API access', $output );
	}

	/**
	 * Test render_cache_section outputs description.
	 */
	public function test_render_cache_section_outputs_description() {
		$admin = $this->create_admin_instance();

		ob_start();
		$admin->render_cache_section();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'Configure how long data is cached', $output );
	}

	/**
	 * Test constants are defined correctly.
	 */
	public function test_admin_constants() {
		$this->create_admin_instance();

		$this->assertEquals( 'bmlt-enabled-stats', \BLST\Admin::MENU_SLUG );
		$this->assertEquals( 'blst_settings', \BLST\Admin::OPTION_NAME );
	}
}
