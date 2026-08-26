/**
 * The AJAX request checking the uniqueness of a title.
 */

import type { UniqueTitleCheckResponse } from './types';

/**
 * The values identifying the post whose title is checked.
 */
export interface PostToCheck {
	/** The name of the parameter excluding the edited post, either `post_id` or `post__not_in`. */
	excludeParameter: 'post_id' | 'post__not_in';
	postId: string;
	postType: string;
	postTitle: string;
}

/**
 * Read the value of a hidden input on the edit screen.
 *
 * @param selector The selector of the input.
 *
 * @return The value of the input, or an empty string if it is not there.
 */
export function readInputValue( selector: string ): string {
	return document.querySelector< HTMLInputElement >( selector )?.value ?? '';
}

/**
 * Ask the server whether the given title is unique.
 *
 * @param post The post whose title is checked.
 *
 * @return The message and the status to be shown to the editor.
 */
export async function checkTitle(
	post: PostToCheck
): Promise< UniqueTitleCheckResponse > {
	const requestData = new URLSearchParams( {
		action: 'unique_title_check',
		ajax_nonce: window.unique_title_checker.nonce,
		[ post.excludeParameter ]: post.postId,
		post_type: post.postType,
		post_title: post.postTitle,
	} );

	const response = await fetch(
		`${ window.ajaxurl }?${ requestData.toString() }`
	);

	return ( await response.json() ) as UniqueTitleCheckResponse;
}

/**
 * Whether a response has to be shown to the editor.
 *
 * Unless the `unique_title_checker_only_unique_error` filter is used, both the error and
 * the success message are shown.
 *
 * @param response The response of the uniqueness check.
 *
 * @return Whether the message has to be rendered.
 */
export function shouldShowResponse(
	response: UniqueTitleCheckResponse
): boolean {
	return (
		response.status === 'error' ||
		! window.unique_title_checker.only_unique_error
	);
}
