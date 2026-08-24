<?php
/**
 * Stubs for the WordPress classes used by the plugin.
 *
 * @package unique-title-checker
 */

/**
 * Stub for `WP_Query`, recording the arguments and reporting a configurable post count.
 */
class WP_Query {

	/**
	 * The post count the next instance will report.
	 *
	 * @var int
	 */
	public static $next_post_count = 0;

	/**
	 * The arguments the last instance was created with.
	 *
	 * @var array|string|null
	 */
	public static $last_args = null;

	/**
	 * The number of posts found.
	 *
	 * @var int
	 */
	public $post_count;

	/**
	 * Constructor.
	 *
	 * @param array|string $args The query arguments.
	 */
	public function __construct( $args = array() ) {
		self::$last_args  = $args;
		$this->post_count = self::$next_post_count;
	}

	/**
	 * Reset the recorded state between tests.
	 *
	 * @return void
	 */
	public static function reset() {
		self::$next_post_count = 0;
		self::$last_args       = null;
	}
}

/**
 * Stub for `WP_Screen`, so it can be mocked with the `is_block_editor()` method.
 */
class WP_Screen {

	/**
	 * Whether the current screen uses the block editor.
	 *
	 * @return bool
	 */
	public function is_block_editor() {
		return false;
	}
}

/**
 * Stub for `wpdb`, only providing the table name used by the plugin.
 */
class wpdb { // phpcs:ignore PEAR.NamingConventions.ValidClassName.StartWithCapital

	/**
	 * The name of the posts table.
	 *
	 * @var string
	 */
	public $posts = 'wp_posts';
}
