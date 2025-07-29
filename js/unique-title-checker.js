/**
 * Check for the Classic Editor
 *
 * @package unique-title-checker
 */

document.addEventListener( 'DOMContentLoaded', function () {
	document.getElementById( 'title' ).addEventListener( 'blur', function () {
		const title = this.value;

		// Show no warning on empty titles.
		if (title === '') {
			return;
		}

		const postId = document.querySelector( '#post_ID' )?.value;
		const postType = document.querySelector( '#post_type' )?.value;

		// Build the request payload
		const requestData = new URLSearchParams( {
			action: 'unique_title_check',
			ajax_nonce: unique_title_checker.nonce,
			post_id: postId || '',
			post_type: postType || '',
			post_title: title
		} );

		// Perform the AJAX request
		fetch( ajaxurl + '?' + requestData.toString() )
			.then( ( response ) => response.json() )
			.then( ( data ) => {
				const messageElement = document.getElementById( 'unique-title-message' );
				if (messageElement) {
					messageElement.remove();
				}

				if (data.status === 'error' || !unique_title_checker.only_unique_error) {
					document.getElementById( 'post' ).insertAdjacentHTML(
						'beforebegin',
						`<div id="unique-title-message" class="${ data.status }">
							<p>${ data.message }</p>
						</div>`
					);
				}
			} )
			.catch( ( error ) => {
				console.error( 'Error fetching unique title check:', error );
			} );
	} );
} );
