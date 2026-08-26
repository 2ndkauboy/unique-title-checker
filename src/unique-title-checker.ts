/**
 * Check the uniqueness of the title in the Classic Editor.
 */

import { checkTitle, readInputValue, shouldShowResponse } from './check-title';
import type { UniqueTitleCheckResponse } from './types';

const MESSAGE_ID = 'unique-title-message';

/**
 * Replace the notice above the edit form with the result of the check.
 *
 * @param response The response of the uniqueness check.
 */
function renderNotice( response: UniqueTitleCheckResponse ): void {
	document.getElementById( MESSAGE_ID )?.remove();

	if ( ! shouldShowResponse( response ) ) {
		return;
	}

	/*
	 * The message is inserted as HTML, as the server has escaped it already and entities
	 * like `&amp;` have to be rendered rather than shown verbatim.
	 */
	document.getElementById( 'post' )?.insertAdjacentHTML(
		'beforebegin',
		`<div id="${ MESSAGE_ID }" class="${ response.status }">
			<p>${ response.message }</p>
		</div>`
	);
}

/**
 * Check the title as soon as the title field loses the focus.
 *
 * @param titleField The title field of the edit form.
 */
async function checkTitleField(
	titleField: HTMLInputElement
): Promise< void > {
	// Show no warning on empty titles.
	if ( titleField.value === '' ) {
		return;
	}

	const response = await checkTitle( {
		excludeParameter: 'post_id',
		postId: readInputValue( '#post_ID' ),
		postType: readInputValue( '#post_type' ),
		postTitle: titleField.value,
	} );

	renderNotice( response );
}

document.addEventListener( 'DOMContentLoaded', () => {
	const titleField = document.querySelector< HTMLInputElement >( '#title' );

	titleField?.addEventListener( 'blur', () => {
		checkTitleField( titleField ).catch( ( error ) => {
			// eslint-disable-next-line no-console
			console.error( 'Error fetching unique title check:', error );
		} );
	} );
} );
