<?php
/**
 * Shortcodes Test
 *
 * @package BMLT_Enabled_Stats
 */

namespace BLST\Tests;

use Brain\Monkey\Functions;
use Mockery;

/**
 * Test the Shortcodes utility methods.
 */
class ShortcodesTest extends TestCase {

	/**
	 * Test format_number with values under 1000.
	 */
	public function test_format_number_under_thousand() {
		require_once BLST_PLUGIN_DIR . 'includes/class-shortcodes.php';

		$result = \BLST\Shortcodes::format_number( 500 );
		$this->assertEquals( '500', $result );

		$result = \BLST\Shortcodes::format_number( 999 );
		$this->assertEquals( '999', $result );

		$result = \BLST\Shortcodes::format_number( 0 );
		$this->assertEquals( '0', $result );
	}

	/**
	 * Test format_number with thousands.
	 */
	public function test_format_number_thousands() {
		require_once BLST_PLUGIN_DIR . 'includes/class-shortcodes.php';

		$result = \BLST\Shortcodes::format_number( 1000 );
		$this->assertEquals( '1K', $result );

		$result = \BLST\Shortcodes::format_number( 1500 );
		$this->assertEquals( '1.5K', $result );

		$result = \BLST\Shortcodes::format_number( 50000 );
		$this->assertEquals( '50K', $result );

		$result = \BLST\Shortcodes::format_number( 999999 );
		$this->assertEquals( '1000K', $result );
	}

	/**
	 * Test format_number with millions.
	 */
	public function test_format_number_millions() {
		require_once BLST_PLUGIN_DIR . 'includes/class-shortcodes.php';

		$result = \BLST\Shortcodes::format_number( 1000000 );
		$this->assertEquals( '1M', $result );

		$result = \BLST\Shortcodes::format_number( 1500000 );
		$this->assertEquals( '1.5M', $result );

		$result = \BLST\Shortcodes::format_number( 10000000 );
		$this->assertEquals( '10M', $result );
	}

	/**
	 * Test get_language_color returns correct colors.
	 */
	public function test_get_language_color() {
		require_once BLST_PLUGIN_DIR . 'includes/class-shortcodes.php';

		$this->assertEquals( '#4F5D95', \BLST\Shortcodes::get_language_color( 'PHP' ) );
		$this->assertEquals( '#f1e05a', \BLST\Shortcodes::get_language_color( 'JavaScript' ) );
		$this->assertEquals( '#6e7681', \BLST\Shortcodes::get_language_color( 'Unknown' ) );
	}

	/**
	 * Test get_language_color returns all known languages.
	 */
	public function test_get_language_color_all_languages() {
		require_once BLST_PLUGIN_DIR . 'includes/class-shortcodes.php';

		$this->assertEquals( '#2b7489', \BLST\Shortcodes::get_language_color( 'TypeScript' ) );
		$this->assertEquals( '#e34c26', \BLST\Shortcodes::get_language_color( 'HTML' ) );
		$this->assertEquals( '#563d7c', \BLST\Shortcodes::get_language_color( 'CSS' ) );
		$this->assertEquals( '#F05138', \BLST\Shortcodes::get_language_color( 'Swift' ) );
		$this->assertEquals( '#A97BFF', \BLST\Shortcodes::get_language_color( 'Kotlin' ) );
		$this->assertEquals( '#b07219', \BLST\Shortcodes::get_language_color( 'Java' ) );
		$this->assertEquals( '#3572A5', \BLST\Shortcodes::get_language_color( 'Python' ) );
		$this->assertEquals( '#701516', \BLST\Shortcodes::get_language_color( 'Ruby' ) );
		$this->assertEquals( '#89e051', \BLST\Shortcodes::get_language_color( 'Shell' ) );
		$this->assertEquals( '#384d54', \BLST\Shortcodes::get_language_color( 'Dockerfile' ) );
	}

	/**
	 * Test format_date returns Unknown for empty date.
	 */
	public function test_format_date_returns_unknown_for_empty() {
		require_once BLST_PLUGIN_DIR . 'includes/class-shortcodes.php';

		$result = \BLST\Shortcodes::format_date( '' );
		$this->assertEquals( 'Unknown', $result );

		$result = \BLST\Shortcodes::format_date( null );
		$this->assertEquals( 'Unknown', $result );
	}

	/**
	 * Test format_date returns original date for invalid format.
	 */
	public function test_format_date_returns_original_for_invalid() {
		require_once BLST_PLUGIN_DIR . 'includes/class-shortcodes.php';

		$invalid_date = 'not a date';
		$result       = \BLST\Shortcodes::format_date( $invalid_date );
		$this->assertEquals( $invalid_date, $result );
	}

	/**
	 * Test format_date returns relative time for recent dates.
	 */
	public function test_format_date_returns_relative_time() {
		require_once BLST_PLUGIN_DIR . 'includes/class-shortcodes.php';

		// A date 5 days ago.
		$recent_date = gmdate( 'Y-m-d H:i:s', time() - ( 5 * DAY_IN_SECONDS ) );

		Functions\expect( 'human_time_diff' )
			->once()
			->andReturn( '5 days' );

		$result = \BLST\Shortcodes::format_date( $recent_date );
		$this->assertEquals( '5 days ago', $result );
	}

	/**
	 * Test format_date returns formatted date for older dates.
	 */
	public function test_format_date_returns_formatted_date() {
		require_once BLST_PLUGIN_DIR . 'includes/class-shortcodes.php';

		// A date 60 days ago.
		$old_date = gmdate( 'Y-m-d H:i:s', time() - ( 60 * DAY_IN_SECONDS ) );

		Functions\expect( 'get_option' )
			->once()
			->with( 'date_format' )
			->andReturn( 'F j, Y' );

		Functions\expect( 'date_i18n' )
			->once()
			->andReturn( 'November 28, 2023' );

		$result = \BLST\Shortcodes::format_date( $old_date );
		$this->assertEquals( 'November 28, 2023', $result );
	}

	/**
	 * Test render_stars returns correct HTML for full stars.
	 */
	public function test_render_stars_returns_full_stars() {
		require_once BLST_PLUGIN_DIR . 'includes/class-shortcodes.php';

		// 100% rating = 5 full stars.
		$result = \BLST\Shortcodes::render_stars( 100 );

		$this->assertStringContainsString( 'blst-stars', $result );
		$this->assertStringContainsString( 'star full', $result );
		$this->assertStringContainsString( '(5.0)', $result );
		$this->assertEquals( 5, substr_count( $result, 'star full' ) );
	}

	/**
	 * Test render_stars returns correct HTML for partial stars.
	 */
	public function test_render_stars_returns_partial_stars() {
		require_once BLST_PLUGIN_DIR . 'includes/class-shortcodes.php';

		// 70% rating = 3.5 stars (3 full, 1 half, 1 empty).
		$result = \BLST\Shortcodes::render_stars( 70 );

		$this->assertStringContainsString( 'star full', $result );
		$this->assertStringContainsString( 'star half', $result );
		$this->assertStringContainsString( 'star empty', $result );
		$this->assertStringContainsString( '(3.5)', $result );
	}

	/**
	 * Test render_stars returns correct HTML for no stars.
	 */
	public function test_render_stars_returns_empty_stars() {
		require_once BLST_PLUGIN_DIR . 'includes/class-shortcodes.php';

		// 0% rating = 0 stars (all empty).
		$result = \BLST\Shortcodes::render_stars( 0 );

		$this->assertStringContainsString( 'star empty', $result );
		$this->assertStringContainsString( '(0.0)', $result );
		$this->assertEquals( 5, substr_count( $result, 'star empty' ) );
	}

	/**
	 * Test render_stats returns HTML with template.
	 */
	public function test_render_stats_returns_html() {
		require_once BLST_PLUGIN_DIR . 'includes/class-shortcodes.php';

		$plugin = Mockery::mock( 'BLST\Plugin' );
		$plugin->shouldReceive( 'get_all_stats' )
			->once()
			->andReturn(
				array(
					'github'    => array( 'total_repos' => 50 ),
					'wordpress' => array( 'plugin_count' => 5 ),
					'bmlt'      => array(),
				)
			);

		Functions\expect( 'shortcode_atts' )
			->once()
			->andReturnUsing(
				function ( $defaults, $atts ) {
					return array_merge( $defaults, $atts ?? array() );
				}
			);

		Functions\expect( 'locate_template' )
			->once()
			->andReturn( '' );

		Functions\expect( 'add_shortcode' )
			->once();

		$shortcodes = new \BLST\Shortcodes( $plugin );
		$result     = $shortcodes->render_stats( array() );

		// Should return HTML (template content).
		$this->assertIsString( $result );
	}

	/**
	 * Test render_stats with non-existent template type falls back to full.
	 */
	public function test_render_stats_with_nonexistent_type_uses_fallback() {
		require_once BLST_PLUGIN_DIR . 'includes/class-shortcodes.php';

		$plugin = Mockery::mock( 'BLST\Plugin' );
		$plugin->shouldReceive( 'get_all_stats' )
			->once()
			->andReturn( array() );

		Functions\expect( 'shortcode_atts' )
			->once()
			->andReturn(
				array(
					'type'  => 'nonexistent', // Falls back to full template.
					'theme' => 'default',
				)
			);

		Functions\expect( 'locate_template' )
			->once()
			->andReturn( '' );

		Functions\expect( 'add_shortcode' )
			->once();

		$shortcodes = new \BLST\Shortcodes( $plugin );
		$result     = $shortcodes->render_stats( array( 'type' => 'nonexistent' ) );

		// Non-existent type falls back to full template which exists.
		$this->assertIsString( $result );
	}

	/**
	 * Test render_stats uses summary type correctly.
	 */
	public function test_render_stats_with_summary_type() {
		require_once BLST_PLUGIN_DIR . 'includes/class-shortcodes.php';

		$plugin = Mockery::mock( 'BLST\Plugin' );
		$plugin->shouldReceive( 'get_summary_stats' )
			->once()
			->andReturn(
				array(
					'total_github_stars' => 150,
					'total_downloads'    => 500000,
				)
			);

		Functions\expect( 'shortcode_atts' )
			->once()
			->andReturn(
				array(
					'type'  => 'summary',
					'theme' => 'default',
				)
			);

		Functions\expect( 'locate_template' )
			->once()
			->andReturn( '' );

		Functions\expect( 'add_shortcode' )
			->once();

		$shortcodes = new \BLST\Shortcodes( $plugin );
		$result     = $shortcodes->render_stats( array( 'type' => 'summary' ) );

		$this->assertIsString( $result );
	}

	/**
	 * Test render_stats uses github type correctly.
	 */
	public function test_render_stats_with_github_type() {
		require_once BLST_PLUGIN_DIR . 'includes/class-shortcodes.php';

		$github_api = Mockery::mock( 'BLST\GitHub_API' );
		$github_api->shouldReceive( 'get_stats' )
			->once()
			->andReturn(
				array(
					'total_repos' => 50,
					'total_stars' => 150,
				)
			);

		$plugin             = Mockery::mock( 'BLST\Plugin' );
		$plugin->github_api = $github_api;

		Functions\expect( 'shortcode_atts' )
			->once()
			->andReturn(
				array(
					'type'  => 'github',
					'theme' => 'default',
				)
			);

		Functions\expect( 'locate_template' )
			->once()
			->andReturn( '' );

		Functions\expect( 'add_shortcode' )
			->once();

		$shortcodes = new \BLST\Shortcodes( $plugin );
		$result     = $shortcodes->render_stats( array( 'type' => 'github' ) );

		$this->assertIsString( $result );
	}

	/**
	 * Test render_stats with theme attribute.
	 */
	public function test_render_stats_with_theme_attribute() {
		require_once BLST_PLUGIN_DIR . 'includes/class-shortcodes.php';

		$plugin = Mockery::mock( 'BLST\Plugin' );
		$plugin->shouldReceive( 'get_all_stats' )
			->once()
			->andReturn( array() );

		Functions\expect( 'shortcode_atts' )
			->once()
			->andReturn(
				array(
					'type'  => 'full',
					'theme' => 'dark',
				)
			);

		Functions\expect( 'locate_template' )
			->once()
			->andReturn( '' );

		Functions\expect( 'add_shortcode' )
			->once();

		// sanitize_html_class is stubbed in TestCase.

		$shortcodes = new \BLST\Shortcodes( $plugin );
		$result     = $shortcodes->render_stats( array( 'theme' => 'dark' ) );

		$this->assertIsString( $result );
	}

	/**
	 * Test theme template override.
	 */
	public function test_render_stats_uses_theme_template_override() {
		require_once BLST_PLUGIN_DIR . 'includes/class-shortcodes.php';

		$plugin = Mockery::mock( 'BLST\Plugin' );
		$plugin->shouldReceive( 'get_all_stats' )
			->once()
			->andReturn( array() );

		Functions\expect( 'shortcode_atts' )
			->once()
			->andReturn(
				array(
					'type'  => 'full',
					'theme' => 'default',
				)
			);

		// Simulate theme override existing.
		$theme_template = '/path/to/theme/bmlt-enabled-stats/stats-full.php';
		Functions\expect( 'locate_template' )
			->once()
			->with( 'bmlt-enabled-stats/stats-full.php' )
			->andReturn( $theme_template );

		Functions\expect( 'add_shortcode' )
			->once();

		$shortcodes = new \BLST\Shortcodes( $plugin );

		// The result will be comment since file doesn't actually exist in test.
		$result = $shortcodes->render_stats( array() );

		// We just verify the method runs without error.
		$this->assertIsString( $result );
	}
}
