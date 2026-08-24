/**
 * End-to-end tests for the title uniqueness notices in both editors.
 *
 * @package unique-title-checker
 */

/**
 * WordPress dependencies
 */
const { test, expect } = require( '@wordpress/e2e-test-utils-playwright' );

const MESSAGE_UNIQUE = 'The chosen title is unique.';
const MESSAGE_DUPLICATE = 'There is one Post with the same title!';

/**
 * The two editors under test, each one described by the way it is opened from the post
 * list and by the locators for its title field, its content area and its notices.
 */
const editors = {
	'Block Editor': {
		// The accessible name of the row action added by the Classic Editor plugin.
		rowAction: 'in the block editor',

		async prepare( { editor } ) {
			await editor.setPreferences( 'core/edit-post', {
				welcomeGuide: false,
				fullscreenMode: false,
			} );
		},

		title: ( { editor } ) => editor.canvas.locator( '.wp-block-post-title' ),

		content: ( { editor } ) =>
			editor.canvas.locator( '.block-editor-block-list__layout' ).first(),

		notice: ( { page }, message ) =>
			page.locator( '.components-notice' ).filter( { hasText: message } ),
	},

	'Classic Editor': {
		// The accessible name of the row action added by the Classic Editor plugin.
		rowAction: 'in the classic editor',

		async prepare( { page } ) {
			// Wait for TinyMCE to have set up the content iframe.
			await expect( page.locator( '#content_ifr' ) ).toBeVisible();
		},

		title: ( { page } ) => page.locator( '#title' ),

		content: ( { page } ) => page.frameLocator( '#content_ifr' ).locator( 'body#tinymce' ),

		notice: ( { page }, message ) =>
			page.locator( '#unique-title-message' ).filter( { hasText: message } ),
	},
};

/**
 * The posts the tests are run against, created once for the whole suite.
 *
 * @type {{ unique: Object, duplicate: Object }}
 */
const posts = {};

test.beforeAll( async ( { requestUtils } ) => {
	// Start from a clean slate, so the titles below are really the only ones.
	await requestUtils.deleteAllPosts();

	posts.unique = await requestUtils.createPost( {
		title: 'Unique Title',
		status: 'draft',
	} );

	// The post under test is created first, the post making its title a duplicate second.
	posts.duplicate = await requestUtils.createPost( {
		title: 'Duplicate Title',
		status: 'draft',
	} );

	await requestUtils.createPost( {
		title: 'Duplicate Title',
		status: 'draft',
	} );
} );

test.afterAll( async ( { requestUtils } ) => {
	await requestUtils.deleteAllPosts();
} );

/**
 * Open a post from the post list, using the row action of the given editor.
 *
 * @param {Object} utils  The `admin` and `page` fixtures.
 * @param {Object} editor The editor description.
 * @param {number} postId The ID of the post to open.
 *
 * @return {Promise<void>}
 */
async function openPostFromList( { admin, page }, editor, postId ) {
	await admin.visitAdminPage( 'edit.php' );

	const row = page.locator( `#post-${ postId }` );

	// The row actions are only revealed on hover.
	await row.hover();
	await row.getByRole( 'link', { name: editor.rowAction } ).click();
	await page.waitForLoadState();
}

for ( const [ name, editor ] of Object.entries( editors ) ) {
	test.describe( `Unique Title Checker (${ name })`, () => {
		test( 'shows a notice for a unique title', async ( {
			admin,
			editor: editorUtils,
			page,
		} ) => {
			const utils = { admin, editor: editorUtils, page };

			await openPostFromList( utils, editor, posts.unique.id );
			await editor.prepare( utils );

			await editor.title( utils ).click();
			await editor.content( utils ).click();

			await expect( editor.notice( utils, MESSAGE_UNIQUE ) ).toBeVisible();
		} );

		test( 'warns about a duplicate title until it is made unique', async ( {
			admin,
			editor: editorUtils,
			page,
		} ) => {
			const utils = { admin, editor: editorUtils, page };

			await openPostFromList( utils, editor, posts.duplicate.id );
			await editor.prepare( utils );

			await editor.title( utils ).click();
			await editor.content( utils ).click();

			await expect( editor.notice( utils, MESSAGE_DUPLICATE ) ).toBeVisible();

			// Make the title unique again by appending an exclamation mark.
			await editor.title( utils ).click();
			await page.keyboard.press( 'End' );
			await page.keyboard.type( '!' );
			await editor.content( utils ).click();

			await expect( editor.notice( utils, MESSAGE_UNIQUE ) ).toBeVisible();
			await expect( editor.notice( utils, MESSAGE_DUPLICATE ) ).toBeHidden();
		} );
	} );
}
