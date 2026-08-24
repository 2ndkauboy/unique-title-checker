<?php
/**
 * Bootstrap for the unit tests.
 *
 * The plugin is tested in isolation, WordPress itself is never loaded. All WordPress
 * functions are mocked with Brain Monkey, the few WordPress classes the plugin touches
 * are replaced by the stubs in `_stubs.php`.
 *
 * @package unique-title-checker
 */

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

require_once __DIR__ . '/_stubs.php';

/*
 * The plugin file registers a hook while being loaded, so Brain Monkey has to be active
 * while it is required.
 */
Brain\Monkey\setUp();
require_once dirname( __DIR__ ) . '/unique-title-checker.php';
Brain\Monkey\tearDown();
