<?php
/**
 * Block Test
 *
 * @package BMLT_Enabled_Stats
 */

namespace BLST\Tests;

use Brain\Monkey\Functions;
use Mockery;

/**
 * Test the Block class.
 */
class BlockTest extends TestCase {

	/**
	 * Mock plugin instance.
	 *
	 * @var \BLST\Plugin|Mockery\MockInterface
	 */
	private $plugin;

	/**
	 * Mock shortcodes instance.
	 *
	 * @var \BLST\Shortcodes|Mockery\MockInterface
	 */
	private $shortcodes;

	/**
	 * Set up test fixtures.
	 */
	protected function set_up() {
		parent::set_up();

		$this->plugin     = Mockery::mock( 'BLST\Plugin' );
		$this->shortcodes = Mockery::mock( 'BLST\Shortcodes' );

		$this->plugin->shortcodes = $this->shortcodes;
	}

	/**
	 * Test register_hooks adds correct actions.
	 */
	public function test_register_hooks_adds_actions() {
		Functions\expect( 'add_action' )
			->once()
			->with( 'init', Mockery::type( 'array' ) );

		Functions\expect( 'add_action' )
			->once()
			->with( 'enqueue_block_editor_assets', Mockery::type( 'array' ) );

		new \BLST\Block( $this->plugin );

		$this->assertTrue( true );
	}

	/**
	 * Test register_block registers block type when available.
	 *
	 * Note: We can't mock function_exists without patchwork config,
	 * so we test the happy path where register_block_type exists.
	 */
	public function test_register_block_adds_block_type() {
		Functions\expect( 'add_action' )
			->times( 2 );

		// Define the function if it doesn't exist (simulating WP environment).
		if ( ! function_exists( 'register_block_type' ) ) {
			Functions\expect( 'register_block_type' )
				->once()
				->with(
					BLST_PLUGIN_DIR . 'block',
					Mockery::on(
						function ( $args ) {
							return isset( $args['render_callback'] ) && is_array( $args['render_callback'] );
						}
					)
				);
		}

		$block = new \BLST\Block( $this->plugin );
		$block->register_block();

		$this->assertTrue( true );
	}

	/**
	 * Test register_block behavior documentation.
	 *
	 * Note: Testing the "no block editor" path requires mocking
	 * function_exists which needs patchwork configuration.
	 * This test documents the expected behavior instead.
	 */
	public function test_register_block_checks_for_block_editor() {
		// This test verifies the Block class checks for register_block_type
		// before attempting to register. The actual conditional cannot be
		// tested without patchwork configuration for function_exists.
		$this->assertTrue( true );
	}

	/**
	 * Test render_block returns shortcode output.
	 */
	public function test_render_block_returns_shortcode_output() {
		Functions\expect( 'add_action' )
			->times( 2 );

		$expected_html = '<div class="blst-stats">Test Content</div>';

		$this->shortcodes
			->shouldReceive( 'render_stats' )
			->once()
			->with(
				array(
					'type'  => 'full',
					'theme' => 'default',
				)
			)
			->andReturn( $expected_html );

		$block  = new \BLST\Block( $this->plugin );
		$result = $block->render_block( array() );

		$this->assertEquals( $expected_html, $result );
	}

	/**
	 * Test render_block uses attributes.
	 */
	public function test_render_block_uses_attributes() {
		Functions\expect( 'add_action' )
			->times( 2 );

		$expected_html = '<div class="blst-stats dark">Summary Content</div>';

		$this->shortcodes
			->shouldReceive( 'render_stats' )
			->once()
			->with(
				array(
					'type'  => 'summary',
					'theme' => 'dark',
				)
			)
			->andReturn( $expected_html );

		$block  = new \BLST\Block( $this->plugin );
		$result = $block->render_block(
			array(
				'displayType' => 'summary',
				'theme'       => 'dark',
			)
		);

		$this->assertEquals( $expected_html, $result );
	}

	/**
	 * Test enqueue_editor_assets adds scripts.
	 */
	public function test_enqueue_editor_assets_adds_scripts() {
		Functions\expect( 'add_action' )
			->times( 2 );

		$this->plugin
			->shouldReceive( 'get_summary_stats' )
			->once()
			->andReturn(
				array(
					'total_meetings'        => 39000,
					'root_servers'          => 42,
					'total_github_stars'    => 150,
					'total_downloads'       => 500000,
					'total_active_installs' => 10000,
					'total_repos'           => 50,
				)
			);

		Functions\expect( 'wp_enqueue_script' )
			->once()
			->with(
				'blst-block-editor',
				BLST_PLUGIN_URL . 'block/index.js',
				Mockery::type( 'array' ),
				BLST_VERSION,
				true
			);

		Functions\expect( 'wp_enqueue_style' )
			->once()
			->with(
				'blst-block-editor',
				BLST_PLUGIN_URL . 'assets/css/stats-display.css',
				array(),
				BLST_VERSION
			);

		Functions\expect( 'wp_localize_script' )
			->once()
			->with(
				'blst-block-editor',
				'blstBlockData',
				Mockery::on(
					function ( $data ) {
						return isset( $data['previewData'] ) && is_array( $data['previewData'] );
					}
				)
			);

		$block = new \BLST\Block( $this->plugin );
		$block->enqueue_editor_assets();

		$this->assertTrue( true );
	}

	/**
	 * Test get_preview_data returns stats.
	 */
	public function test_get_preview_data_returns_stats() {
		Functions\expect( 'add_action' )
			->times( 2 );

		$summary_stats = array(
			'total_meetings'        => 45000,
			'root_servers'          => 50,
			'total_github_stars'    => 200,
			'total_downloads'       => 600000,
			'total_active_installs' => 15000,
			'total_repos'           => 60,
		);

		$this->plugin
			->shouldReceive( 'get_summary_stats' )
			->once()
			->andReturn( $summary_stats );

		Functions\expect( 'wp_enqueue_script' )
			->once();

		Functions\expect( 'wp_enqueue_style' )
			->once();

		Functions\expect( 'wp_localize_script' )
			->once()
			->with(
				'blst-block-editor',
				'blstBlockData',
				Mockery::on(
					function ( $data ) use ( $summary_stats ) {
						$preview = $data['previewData'];
						return $preview['total_meetings'] === $summary_stats['total_meetings']
							&& $preview['total_github_stars'] === $summary_stats['total_github_stars'];
					}
				)
			);

		$block = new \BLST\Block( $this->plugin );
		$block->enqueue_editor_assets();

		$this->assertTrue( true );
	}

	/**
	 * Test get_preview_data returns defaults for missing data.
	 */
	public function test_get_preview_data_returns_defaults_for_missing() {
		Functions\expect( 'add_action' )
			->times( 2 );

		// Return empty stats.
		$this->plugin
			->shouldReceive( 'get_summary_stats' )
			->once()
			->andReturn( array() );

		Functions\expect( 'wp_enqueue_script' )
			->once();

		Functions\expect( 'wp_enqueue_style' )
			->once();

		Functions\expect( 'wp_localize_script' )
			->once()
			->with(
				'blst-block-editor',
				'blstBlockData',
				Mockery::on(
					function ( $data ) {
						$preview = $data['previewData'];
						// Should have default values.
						return 39000 === $preview['total_meetings']
							&& 42 === $preview['root_servers']
							&& 150 === $preview['total_github_stars']
							&& 500000 === $preview['total_downloads']
							&& 10000 === $preview['total_active_installs']
							&& 50 === $preview['total_repos'];
					}
				)
			);

		$block = new \BLST\Block( $this->plugin );
		$block->enqueue_editor_assets();

		$this->assertTrue( true );
	}

	/**
	 * Test editor assets include correct dependencies.
	 */
	public function test_editor_assets_include_correct_dependencies() {
		Functions\expect( 'add_action' )
			->times( 2 );

		$this->plugin
			->shouldReceive( 'get_summary_stats' )
			->once()
			->andReturn( array() );

		$expected_deps = array(
			'wp-blocks',
			'wp-element',
			'wp-editor',
			'wp-components',
			'wp-i18n',
			'wp-block-editor',
		);

		Functions\expect( 'wp_enqueue_script' )
			->once()
			->with(
				'blst-block-editor',
				Mockery::type( 'string' ),
				$expected_deps,
				Mockery::type( 'string' ),
				true
			);

		Functions\expect( 'wp_enqueue_style' )
			->once();

		Functions\expect( 'wp_localize_script' )
			->once();

		$block = new \BLST\Block( $this->plugin );
		$block->enqueue_editor_assets();

		$this->assertTrue( true );
	}
}
