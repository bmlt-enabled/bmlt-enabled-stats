<?php
/**
 * Base Test Case with Brain Monkey
 *
 * @package BMLT_Enabled_Stats
 */

namespace BLST\Tests;

use Yoast\PHPUnitPolyfills\TestCases\TestCase as PolyfillTestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;

/**
 * Base test class with Brain Monkey setup for WordPress function mocking.
 */
abstract class TestCase extends PolyfillTestCase {

	/**
	 * Set up test fixtures.
	 */
	protected function set_up() {
		parent::set_up();
		Monkey\setUp();

		// Define common WordPress function stubs.
		$this->define_wp_function_stubs();
	}

	/**
	 * Tear down test fixtures.
	 */
	protected function tear_down() {
		Monkey\tearDown();
		parent::tear_down();
	}

	/**
	 * Define common WordPress function stubs.
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	protected function define_wp_function_stubs() {
		// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter
		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped

		// Translation functions - $domain param required for API compatibility.
		Functions\stubs(
			array(
				'__'         => function ( $text, $domain = 'default' ) {
					return $text;
				},
				'_e'         => function ( $text, $domain = 'default' ) {
					echo $text;
				},
				'esc_html__' => function ( $text, $domain = 'default' ) {
					return $text;
				},
				'esc_html_e' => function ( $text, $domain = 'default' ) {
					echo $text;
				},
				'esc_attr__' => function ( $text, $domain = 'default' ) {
					return $text;
				},
				'esc_attr_e' => function ( $text, $domain = 'default' ) {
					echo $text;
				},
			)
		);

		// phpcs:enable Generic.CodeAnalysis.UnusedFunctionParameter
		// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped

		// Escaping functions.
		Functions\stubs(
			array(
				'esc_html'     => function ( $text ) {
					return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
				},
				'esc_attr'     => function ( $text ) {
					return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
				},
				'esc_url'      => function ( $url ) {
					return filter_var( $url, FILTER_SANITIZE_URL );
				},
				'wp_kses_post' => function ( $text ) {
					return $text;
				},
			)
		);

		// Other common functions.
		// phpcs:disable WordPress.WP.AlternativeFunctions
		Functions\stubs(
			array(
				'wp_json_encode'      => function ( $data ) {
					return json_encode( $data );
				},
				'wp_parse_args'       => function ( $args, $defaults = array() ) {
					if ( is_object( $args ) ) {
						$args = get_object_vars( $args );
					}
					return array_merge( $defaults, $args );
				},
				'sanitize_text_field' => function ( $str ) {
					return trim( strip_tags( $str ) );
				},
			)
		);
		// phpcs:enable WordPress.WP.AlternativeFunctions
	}
}
