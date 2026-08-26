<?php
/**
 * Unit tests for the `Unique_Title_Checker` class.
 *
 * @package unique-title-checker
 */

namespace UniqueTitleChecker\Tests\Unit;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Mockery;
use Unique_Title_Checker;
use WP_Query;
use Yoast\WPTestUtils\BrainMonkey\TestCase;

/**
 * Class UniqueTitleCheckerTest.
 */
final class UniqueTitleCheckerTest extends TestCase {

	/**
	 * The instance under test.
	 *
	 * @var Unique_Title_Checker
	 */
	private $plugin;

	/**
	 * Set up the stubs for the translation and escaping functions and a fresh instance.
	 *
	 * @return void
	 */
	protected function set_up() {
		parent::set_up();

		$this->stubTranslationFunctions();
		$this->stubEscapeFunctions();

		WP_Query::reset();

		$this->plugin = new Unique_Title_Checker();
	}

	/**
	 * Reset the recorded state of the `WP_Query` stub.
	 *
	 * @return void
	 */
	protected function tear_down() {
		WP_Query::reset();

		parent::tear_down();
	}

	/**
	 * The singleton always returns the very same instance.
	 *
	 * @return void
	 */
	public function test_get_instance_returns_the_same_instance() {
		$this->assertSame(
			Unique_Title_Checker::get_instance(),
			Unique_Title_Checker::get_instance()
		);
	}

	/**
	 * The setup registers all hooks and stores the paths and the nonce.
	 *
	 * @return void
	 */
	public function test_plugin_setup_registers_the_hooks() {
		Functions\expect( 'plugins_url' )->once()->andReturn( 'https://example.org/plugin/' );
		Functions\expect( 'plugin_dir_path' )->once()->andReturn( '/srv/plugin/' );
		Functions\expect( 'wp_create_nonce' )
			->once()
			->with( 'unique_title_check_nonce' )
			->andReturn( 'the-nonce' );

		Actions\expectAdded( 'admin_enqueue_scripts' )->once();
		Actions\expectAdded( 'wp_ajax_unique_title_check' )->once();
		Actions\expectAdded( 'admin_notices' )->once();

		$this->plugin->plugin_setup();

		$this->assertSame( 'https://example.org/plugin/', $this->plugin->plugin_url );
		$this->assertSame( '/srv/plugin/', $this->plugin->plugin_path );
		$this->assertSame( 'the-nonce', $this->plugin->ajax_nonce );
	}

	/**
	 * No script is enqueued on admin pages other than the post edit screens.
	 *
	 * @return void
	 */
	public function test_enqueue_scripts_skips_other_admin_pages() {
		$calls = array();
		Functions\when( 'wp_enqueue_script' )->alias(
			static function ( ...$args ) use ( &$calls ) {
				$calls[] = $args;
			}
		);

		Functions\expect( 'get_current_screen' )->never();

		$this->plugin->enqueue_scripts( 'edit.php' );

		$this->assertSame( array(), $calls );
	}

	/**
	 * The block editor screen gets the block editor script and its dependencies.
	 *
	 * @return void
	 */
	public function test_enqueue_scripts_uses_the_block_editor_script() {
		$calls = $this->enqueue_scripts_on_screen(
			true,
			'unique-title-checker-block-editor',
			array( 'wp-data', 'wp-notices' ),
			'unit-test-block-editor'
		);

		$this->assertSame( array( 'wp-data', 'wp-notices' ), $calls[0][2] );
	}

	/**
	 * The classic editor screen gets the classic editor script and its dependencies.
	 *
	 * @return void
	 */
	public function test_enqueue_scripts_uses_the_classic_editor_script() {
		$calls = $this->enqueue_scripts_on_screen(
			false,
			'unique-title-checker',
			array(),
			'unit-test-classic'
		);

		$this->assertSame( array(), $calls[0][2] );
	}

	/**
	 * Nothing is enqueued when the build is missing, e.g. in a plain checkout of the sources.
	 *
	 * @return void
	 */
	public function test_enqueue_scripts_skips_a_missing_build() {
		$screen = Mockery::mock( 'WP_Screen' );
		$screen->shouldReceive( 'is_block_editor' )->once()->andReturn( false );

		Functions\expect( 'get_current_screen' )->once()->andReturn( $screen );
		Functions\expect( 'plugin_dir_path' )->once()->andReturn( '/does/not/exist/' );

		Functions\expect( 'wp_enqueue_script' )->never();
		Functions\expect( 'wp_localize_script' )->never();

		$this->plugin->enqueue_scripts( 'post.php' );
	}

	/**
	 * A title without any other post using it is reported as unique.
	 *
	 * @return void
	 */
	public function test_check_uniqueness_reports_a_unique_title() {
		WP_Query::$next_post_count = 0;

		$response = $this->check_uniqueness();

		$this->assertSame(
			array(
				'message' => 'The chosen title is unique.',
				'status'  => 'updated',
			),
			$response
		);
	}

	/**
	 * A single other post with the same title is reported in singular.
	 *
	 * @return void
	 */
	public function test_check_uniqueness_reports_a_single_duplicate() {
		WP_Query::$next_post_count = 1;

		$response = $this->check_uniqueness();

		$this->assertSame(
			array(
				'message' => 'There is one Post with the same title!',
				'status'  => 'error',
			),
			$response
		);
	}

	/**
	 * Several other posts with the same title are reported in plural.
	 *
	 * @return void
	 */
	public function test_check_uniqueness_reports_multiple_duplicates() {
		WP_Query::$next_post_count = 3;

		$response = $this->check_uniqueness();

		$this->assertSame(
			array(
				'message' => 'There are 3 other Posts with the same title!',
				'status'  => 'error',
			),
			$response
		);
	}

	/**
	 * An unknown post type falls back to the generic labels.
	 *
	 * @return void
	 */
	public function test_check_uniqueness_falls_back_to_generic_labels() {
		WP_Query::$next_post_count = 1;

		$response = $this->check_uniqueness( null );

		$this->assertSame( 'There is one post with the same title!', $response['message'] );
	}

	/**
	 * The post to be checked is excluded from the query.
	 *
	 * @return void
	 */
	public function test_check_uniqueness_excludes_the_current_post() {
		$this->check_uniqueness();

		$this->assertSame( array( 42 ), WP_Query::$last_args['post__not_in'] );
		$this->assertArrayNotHasKey( 'post_id', WP_Query::$last_args );
	}

	/**
	 * The `WHERE` clause is extended by an exact match on the post title.
	 *
	 * @return void
	 */
	public function test_post_title_where_matches_the_post_title_exactly() {
		global $wpdb;

		$wpdb = Mockery::mock( 'wpdb' ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		// Overwrite the escaping stub of the base test case with a real escaping.
		Functions\when( 'esc_sql' )->alias( 'addslashes' );

		$this->plugin->post_title = "O'Brien";

		$this->assertSame(
			" AND 1=1 AND wp_posts.post_title = 'O\\'Brien'",
			$this->plugin->post_title_where( ' AND 1=1' )
		);
	}

	/**
	 * The initial notice is only rendered while editing an existing post.
	 *
	 * @return void
	 */
	public function test_uniqueness_admin_notice_skips_other_admin_pages() {
		$this->set_up_edited_post( 'edit.php', 'Duplicate Title' );

		$this->expectOutputString( '' );

		$this->plugin->uniqueness_admin_notice();

		$this->assertNull( WP_Query::$last_args );
	}

	/**
	 * A post without a title is never checked.
	 *
	 * @return void
	 */
	public function test_uniqueness_admin_notice_skips_an_empty_title() {
		$this->set_up_edited_post( 'post.php', '' );

		$this->expectOutputString( '' );

		$this->plugin->uniqueness_admin_notice();

		$this->assertNull( WP_Query::$last_args );
	}

	/**
	 * A unique title does not produce an initial notice.
	 *
	 * @return void
	 */
	public function test_uniqueness_admin_notice_stays_silent_for_a_unique_title() {
		$this->set_up_edited_post( 'post.php', 'Unique Title' );
		$this->expect_post_type_object();

		WP_Query::$next_post_count = 0;

		$this->expectOutputString( '' );

		$this->plugin->uniqueness_admin_notice();
	}

	/**
	 * A duplicate title produces an initial error notice.
	 *
	 * @return void
	 */
	public function test_uniqueness_admin_notice_warns_about_a_duplicate_title() {
		$this->set_up_edited_post( 'post.php', 'Duplicate Title' );
		$this->expect_post_type_object();

		WP_Query::$next_post_count = 1;

		$this->expectOutputString(
			'<div id="unique-title-message" class="error"><p>There is one Post with the same title!</p></div>'
		);

		$this->plugin->uniqueness_admin_notice();

		$this->assertSame( array( 7 ), WP_Query::$last_args['post__not_in'] );
	}

	/**
	 * Enqueue the scripts for a post edit screen and return the `wp_enqueue_script()` calls.
	 *
	 * `plugin_dir_path()` is pointed at `tests/Unit/fixtures/`, which contains a `build/`
	 * directory standing in for the real build output, so `enqueue_scripts()` reads the
	 * dependencies and the version from a real `*.asset.php` file just like it would in
	 * production.
	 *
	 * @param bool     $is_block_editor  Whether the screen uses the block editor.
	 * @param string   $script           The name of the script, without the `.js` extension.
	 * @param string[] $expected_deps    The dependencies declared in the fixture asset file.
	 * @param string   $expected_version The version declared in the fixture asset file.
	 *
	 * @return array[] The arguments of all `wp_enqueue_script()` calls.
	 */
	private function enqueue_scripts_on_screen( $is_block_editor, $script, $expected_deps, $expected_version ) {
		$screen = Mockery::mock( 'WP_Screen' );
		$screen->shouldReceive( 'is_block_editor' )->once()->andReturn( $is_block_editor );

		$this->plugin->ajax_nonce = 'the-nonce';

		Functions\expect( 'get_current_screen' )->once()->andReturn( $screen );
		Functions\expect( 'plugin_dir_path' )->once()->andReturn( __DIR__ . '/fixtures/' );
		Functions\expect( 'plugins_url' )
			->once()
			->with( 'build/' . $script . '.js', Mockery::type( 'string' ) )
			->andReturn( 'https://example.org/plugin/build/' . $script . '.js' );

		Filters\expectApplied( 'unique_title_checker_only_unique_error' )
			->once()
			->with( false )
			->andReturn( false );

		$calls = array();
		Functions\when( 'wp_enqueue_script' )->alias(
			static function ( ...$args ) use ( &$calls ) {
				$calls[] = $args;
			}
		);

		Functions\expect( 'wp_localize_script' )
			->once()
			->with(
				'unique-title-checker',
				'unique_title_checker',
				array(
					'nonce'             => 'the-nonce',
					'only_unique_error' => false,
				)
			);

		$this->plugin->enqueue_scripts( 'post.php' );

		$this->assertCount( 1, $calls );
		$this->assertSame( 'unique-title-checker', $calls[0][0] );
		$this->assertSame( 'https://example.org/plugin/build/' . $script . '.js', $calls[0][1] );
		$this->assertSame( $expected_deps, $calls[0][2] );
		$this->assertSame( $expected_version, $calls[0][3] );
		$this->assertTrue( $calls[0][4] );

		return $calls;
	}

	/**
	 * Run a uniqueness check for a post with the ID 42.
	 *
	 * @param string|null $post_type The post type to look up, `null` for an unknown one.
	 *
	 * @return array The response of the check.
	 */
	private function check_uniqueness( $post_type = 'post' ) {
		$this->expect_post_type_object( $post_type );

		Filters\expectAdded( 'posts_where' )->once();
		Filters\expectRemoved( 'posts_where' )->once();

		return $this->plugin->check_uniqueness(
			array(
				'post_id'    => 42,
				'post_type'  => 'post',
				'post_title' => 'Duplicate Title',
			)
		);
	}

	/**
	 * Let `get_post_type_object()` return the labels of the post type, or nothing.
	 *
	 * @param string|null $post_type The post type to look up, `null` for an unknown one.
	 *
	 * @return void
	 */
	private function expect_post_type_object( $post_type = 'post' ) {
		Functions\expect( 'get_post_type_object' )
			->once()
			->with( 'post' )
			->andReturn(
				null === $post_type
					? null
					: (object) array(
						'labels' => (object) array(
							'singular_name' => 'Post',
							'name'          => 'Posts',
						),
					)
			);
	}

	/**
	 * Set up the globals of an admin page showing a post.
	 *
	 * @param string $pagenow    The current admin page.
	 * @param string $post_title The title of the edited post.
	 *
	 * @return void
	 */
	private function set_up_edited_post( $pagenow, $post_title ) {
		// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
		$GLOBALS['pagenow'] = $pagenow;
		$GLOBALS['post']    = (object) array(
			'ID'         => 7,
			'post_title' => $post_title,
			'post_type'  => 'post',
		);
		// phpcs:enable WordPress.WP.GlobalVariablesOverride.Prohibited
	}
}
