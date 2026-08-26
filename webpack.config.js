/**
 * Webpack configuration, extending the one of `@wordpress/scripts` by the entry points.
 *
 * @package unique-title-checker
 */

/**
 * External dependencies
 */
const path = require( 'path' );

/**
 * WordPress dependencies
 */
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
	...defaultConfig,
	entry: {
		'unique-title-checker': path.resolve( __dirname, 'src/unique-title-checker.ts' ),
		'unique-title-checker-block-editor': path.resolve(
			__dirname,
			'src/unique-title-checker-block-editor.ts'
		),
	},
};
