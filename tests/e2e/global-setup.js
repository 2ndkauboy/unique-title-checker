/**
 * Global setup for the end-to-end tests.
 *
 * @package unique-title-checker
 */

/**
 * WordPress dependencies
 */
const baseGlobalSetup = require( '@wordpress/scripts/config/playwright/global-setup' );

/**
 * Internal dependencies
 */
const { runWpCli } = require( './wp-cli' );

/**
 * @param {import('@playwright/test').FullConfig} config The Playwright configuration.
 *
 * @return {Promise<void>}
 */
module.exports = async function globalSetup( config ) {
	await baseGlobalSetup( config );

	/*
	 * Both plugins are activated by `wp-env` on start, but the integration test suite
	 * reinstalls WordPress in the very same database, which resets the active plugins.
	 * Activating them here keeps the E2E setup independent of that.
	 *
	 * The two Classic Editor options make both editors available, so every post can be
	 * opened in either of them. They are not registered for the REST API, so they have
	 * to be set with WP-CLI.
	 */
	runWpCli( [
		'( wp plugin is-active unique-title-checker || wp plugin activate unique-title-checker )',
		'( wp plugin is-active classic-editor || wp plugin activate classic-editor )',
		"wp option update classic-editor-replace 'block'",
		"wp option update classic-editor-allow-users 'allow'",
	] );
};
