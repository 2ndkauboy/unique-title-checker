<?php
/**
 * Bootstrap for the integration tests.
 *
 * The tests run against a real WordPress installation inside the `wp-env` environment.
 * `wp-env` ships the PHPUnit test files of the installed WordPress version and points the
 * environment variable `WP_TESTS_DIR` to them, which is what `yoast/wp-test-utils` picks
 * up here.
 *
 * @package unique-title-checker
 */

use Yoast\WPTestUtils\WPIntegration;

$unique_title_checker_autoload = dirname( __DIR__ ) . '/vendor/autoload.php';

if ( ! file_exists( $unique_title_checker_autoload ) ) {
	echo 'The Composer dependencies are missing. Run `npm run composer install` first.' . PHP_EOL;
	exit( 1 );
}

require_once $unique_title_checker_autoload;
require_once dirname( __DIR__ ) . '/vendor/yoast/wp-test-utils/src/WPIntegration/bootstrap-functions.php';

$unique_title_checker_tests_dir = WPIntegration\get_path_to_wp_test_dir();

if ( false === $unique_title_checker_tests_dir ) {
	echo 'The WordPress test suite could not be found. Run the tests with `npm run test:integration`.' . PHP_EOL;
	exit( 1 );
}

require_once $unique_title_checker_tests_dir . 'includes/functions.php';

/*
 * Load the plugin as a mu-plugin, so it is active for every test without going through
 * the activation hooks.
 */
tests_add_filter(
	'muplugins_loaded',
	static function () {
		require dirname( __DIR__ ) . '/unique-title-checker.php';
	}
);

WPIntegration\bootstrap_it();
