<?php
/**
 * Integration tests for the `Unique_Title_Checker` class.
 *
 * @package unique-title-checker
 */

use Yoast\WPTestUtils\WPIntegration\TestCase;

/**
 * Class UniqueTitleCheckerTest.
 */
class UniqueTitleCheckerTest extends TestCase {

	/**
	 * The instance of the plugin.
	 *
	 * @var Unique_Title_Checker
	 */
	private $plugin;

	/**
	 * Set up the plugin instance and a clean script queue.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		$this->plugin = Unique_Title_Checker::get_instance();

		$GLOBALS['wp_scripts'] = null;
	}

	/**
	 * A title that no other post uses is reported as unique.
	 *
	 * @return void
	 */
	public function test_check_uniqueness_reports_a_unique_title() {
		$post_id = self::factory()->post->create( array( 'post_title' => 'Unique Title' ) );

		$response = $this->check_uniqueness( $post_id, 'Unique Title' );

		$this->assertSame( 'updated', $response['status'] );
		$this->assertSame( 'The chosen title is unique.', $response['message'] );
	}

	/**
	 * A single other post with the same title is reported in singular.
	 *
	 * @return void
	 */
	public function test_check_uniqueness_reports_a_single_duplicate() {
		$post_id = self::factory()->post->create( array( 'post_title' => 'Duplicate Title' ) );
		self::factory()->post->create( array( 'post_title' => 'Duplicate Title' ) );

		$response = $this->check_uniqueness( $post_id, 'Duplicate Title' );

		$this->assertSame( 'error', $response['status'] );
		$this->assertSame( 'There is one Post with the same title!', $response['message'] );
	}

	/**
	 * Several other posts with the same title are reported in plural.
	 *
	 * @return void
	 */
	public function test_check_uniqueness_reports_multiple_duplicates() {
		$post_id = self::factory()->post->create( array( 'post_title' => 'Duplicate Title' ) );
		self::factory()->post->create_many( 2, array( 'post_title' => 'Duplicate Title' ) );

		$response = $this->check_uniqueness( $post_id, 'Duplicate Title' );

		$this->assertSame( 'error', $response['status'] );
		$this->assertSame( 'There are 2 other Posts with the same title!', $response['message'] );
	}

	/**
	 * Only an exactly matching title counts as a duplicate.
	 *
	 * @return void
	 */
	public function test_check_uniqueness_only_matches_the_exact_title() {
		$post_id = self::factory()->post->create( array( 'post_title' => 'Similar Title' ) );
		self::factory()->post->create( array( 'post_title' => 'Similar Title Too' ) );

		$response = $this->check_uniqueness( $post_id, 'Similar Title' );

		$this->assertSame( 'updated', $response['status'] );
	}

	/**
	 * A post of another post type with the same title is not a duplicate.
	 *
	 * @return void
	 */
	public function test_check_uniqueness_is_limited_to_the_post_type() {
		$post_id = self::factory()->post->create( array( 'post_title' => 'Shared Title' ) );
		self::factory()->post->create(
			array(
				'post_title' => 'Shared Title',
				'post_type'  => 'page',
			)
		);

		$response = $this->check_uniqueness( $post_id, 'Shared Title' );

		$this->assertSame( 'updated', $response['status'] );
	}

	/**
	 * The labels of the post type are used in the message.
	 *
	 * @return void
	 */
	public function test_check_uniqueness_uses_the_labels_of_the_post_type() {
		$page_id = self::factory()->post->create(
			array(
				'post_title' => 'Duplicate Page',
				'post_type'  => 'page',
			)
		);
		self::factory()->post->create(
			array(
				'post_title' => 'Duplicate Page',
				'post_type'  => 'page',
			)
		);

		$response = $this->check_uniqueness( $page_id, 'Duplicate Page', 'page' );

		$this->assertSame( 'There is one Page with the same title!', $response['message'] );
	}

	/**
	 * The query arguments can be changed with a filter.
	 *
	 * @return void
	 */
	public function test_check_uniqueness_arguments_can_be_filtered() {
		$post_id = self::factory()->post->create( array( 'post_title' => 'Duplicate Title' ) );
		$other   = self::factory()->post->create( array( 'post_title' => 'Duplicate Title' ) );

		// Exclude the other post as well, so no duplicate is left.
		add_filter(
			'unique_title_checker_arguments',
			static function ( $args ) use ( $other ) {
				$args['post__not_in'][] = $other;

				return $args;
			}
		);

		$response = $this->check_uniqueness( $post_id, 'Duplicate Title' );

		$this->assertSame( 'updated', $response['status'] );
	}

	/**
	 * A duplicate title is reported with an admin notice while editing the post.
	 *
	 * @return void
	 */
	public function test_uniqueness_admin_notice_warns_about_a_duplicate_title() {
		$post_id = self::factory()->post->create( array( 'post_title' => 'Duplicate Title' ) );
		self::factory()->post->create( array( 'post_title' => 'Duplicate Title' ) );

		$notice = $this->get_admin_notice( $post_id );

		$this->assertStringContainsString( 'id="unique-title-message"', $notice );
		$this->assertStringContainsString( 'class="error"', $notice );
		$this->assertStringContainsString( 'There is one Post with the same title!', $notice );
	}

	/**
	 * A unique title does not produce an admin notice.
	 *
	 * @return void
	 */
	public function test_uniqueness_admin_notice_stays_silent_for_a_unique_title() {
		$post_id = self::factory()->post->create( array( 'post_title' => 'Unique Title' ) );

		$this->assertSame( '', $this->get_admin_notice( $post_id ) );
	}

	/**
	 * The notice is only shown while editing an existing post.
	 *
	 * @return void
	 */
	public function test_uniqueness_admin_notice_skips_other_admin_pages() {
		$post_id = self::factory()->post->create( array( 'post_title' => 'Duplicate Title' ) );
		self::factory()->post->create( array( 'post_title' => 'Duplicate Title' ) );

		$this->assertSame( '', $this->get_admin_notice( $post_id, 'edit.php' ) );
	}

	/**
	 * The classic editor screen gets the classic editor script.
	 *
	 * @return void
	 */
	public function test_enqueue_scripts_uses_the_classic_editor_script() {
		set_current_screen( 'post.php' );
		get_current_screen()->is_block_editor( false );

		$this->plugin->enqueue_scripts( 'post.php' );

		$this->assertTrue( wp_script_is( 'unique_title_checker', 'enqueued' ) );
		$this->assertStringContainsString(
			'js/unique-title-checker.js',
			wp_scripts()->registered['unique_title_checker']->src
		);
		$this->assertSame(
			array( 'jquery' ),
			wp_scripts()->registered['unique_title_checker']->deps
		);
	}

	/**
	 * The block editor screen gets the block editor script.
	 *
	 * @return void
	 */
	public function test_enqueue_scripts_uses_the_block_editor_script() {
		set_current_screen( 'post.php' );
		get_current_screen()->is_block_editor( true );

		$this->plugin->enqueue_scripts( 'post.php' );

		$this->assertStringContainsString(
			'js/unique-title-checker-block-editor.js',
			wp_scripts()->registered['unique_title_checker']->src
		);
		$this->assertSame(
			array( 'jquery', 'wp-data', 'wp-notices' ),
			wp_scripts()->registered['unique_title_checker']->deps
		);
	}

	/**
	 * The nonce and the settings are passed to the script.
	 *
	 * @return void
	 */
	public function test_enqueue_scripts_localizes_the_settings() {
		set_current_screen( 'post.php' );

		$this->plugin->enqueue_scripts( 'post.php' );

		$data = wp_scripts()->get_data( 'unique_title_checker', 'data' );

		$this->assertStringContainsString( '"nonce"', $data );

		// `wp_localize_script()` turns the boolean `false` into an empty string.
		$this->assertStringContainsString( '"only_unique_error":""', $data );
	}

	/**
	 * No script is enqueued on admin pages other than the post edit screens.
	 *
	 * @return void
	 */
	public function test_enqueue_scripts_skips_other_admin_pages() {
		set_current_screen( 'edit.php' );

		$this->plugin->enqueue_scripts( 'edit.php' );

		$this->assertFalse( wp_script_is( 'unique_title_checker', 'enqueued' ) );
	}

	/**
	 * Run a uniqueness check for a post.
	 *
	 * @param int    $post_id    The ID of the post that is edited.
	 * @param string $post_title The title to check.
	 * @param string $post_type  The post type to check.
	 *
	 * @return array The response of the check.
	 */
	private function check_uniqueness( $post_id, $post_title, $post_type = 'post' ) {
		return $this->plugin->check_uniqueness(
			array(
				'post_id'    => $post_id,
				'post_type'  => $post_type,
				'post_title' => $post_title,
			)
		);
	}

	/**
	 * Render the initial admin notice for a post.
	 *
	 * @param int    $post_id The ID of the post that is edited.
	 * @param string $pagenow The admin page the notice is rendered on.
	 *
	 * @return string The rendered notice.
	 */
	private function get_admin_notice( $post_id, $pagenow = 'post.php' ) {
		$GLOBALS['post']    = get_post( $post_id );
		$GLOBALS['pagenow'] = $pagenow;

		ob_start();
		$this->plugin->uniqueness_admin_notice();

		return ob_get_clean();
	}
}
