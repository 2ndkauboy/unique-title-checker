/**
 * Check the uniqueness of the title in the Block Editor.
 */

import { dispatch, subscribe } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';

import { checkTitle, readInputValue, shouldShowResponse } from './check-title';

const MESSAGE_ID = 'unique-title-message';

/**
 * Find the title field of the editor.
 *
 * Depending on the theme and the WordPress version, the editor renders its canvas inside
 * an iframe. If it does, the title field lives in the document of that iframe.
 *
 * @return The title field, or `null` while the editor is not ready yet.
 */
function getPostTitleField(): HTMLElement | null {
	const canvas = document.querySelector< HTMLIFrameElement >(
		'iframe[name="editor-canvas"],.editor-canvas__iframe'
	);

	const canvasDocument = canvas?.contentWindow?.document ?? document;

	return canvasDocument.querySelector< HTMLElement >(
		'.wp-block-post-title'
	);
}

/**
 * Check the title as soon as the title field loses the focus.
 *
 * @param titleField The title field of the editor.
 */
async function checkTitleField( titleField: HTMLElement ): Promise< void > {
	const title = titleField.innerText;

	// Show no warning on empty titles.
	if ( title === '' ) {
		return;
	}

	const response = await checkTitle( {
		excludeParameter: 'post__not_in',
		postId: readInputValue( '#post_ID' ),
		postType: readInputValue( '#post_type' ),
		postTitle: title,
	} );

	const notices = dispatch( noticesStore );

	await notices.removeNotice( MESSAGE_ID );

	if ( ! shouldShowResponse( response ) ) {
		return;
	}

	await notices.createNotice(
		response.status === 'error' ? 'error' : 'success',
		response.message,
		{
			id: MESSAGE_ID,
			isDismissible: true,
		}
	);
}

let unsubscribe: ( () => void ) | null = null;
let listening = false;

/**
 * Wait for the editor to be ready and then watch the title field.
 */
function watchPostTitleField(): void {
	if ( listening ) {
		return;
	}

	const titleField = getPostTitleField();

	if ( ! titleField ) {
		// The editor is not ready yet.
		return;
	}

	// Stop listening as soon as the editor is ready, to avoid an infinite loop.
	listening = true;
	unsubscribe?.();

	titleField.addEventListener( 'blur', () => {
		checkTitleField( titleField ).catch( ( error ) => {
			// eslint-disable-next-line no-console
			console.error( 'Error fetching unique title check:', error );
		} );
	} );
}

unsubscribe = subscribe( watchPostTitleField );
