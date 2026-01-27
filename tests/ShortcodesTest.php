<?php
/**
 * Shortcodes Test
 *
 * @package BMLT_Enabled_Stats
 */

namespace BLST\Tests;

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
}
