/**
 * Check for the Block Editor
 *
 * @package unique-title-checker
 */

const closeListener = wp.data.subscribe( function () {
	let iframedEditor = document.querySelector( '.editor-canvas__iframe' );
	let postTitleInput = document.querySelector( '.wp-block-post-title' );
	if (!iframedEditor && !postTitleInput) {
		// The editor is not ready.
		return;
	}

	if (iframedEditor) {
		// Overwrite the `post_title` input selector if inside the iframe.
		postTitleInput = iframedEditor.contentWindow.document.querySelector( '.wp-block-post-title' );
	}

	if (!postTitleInput) {
		// The `post_title` input is still not ready.
		return;
	}

	// Close the listener as soon as we know the editor is ready to avoid an infinite loop.
	closeListener();

	postTitleInput.addEventListener( 'blur', () => {
		const title = postTitleInput.innerText;

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
			post__not_in: postId ? [ postId ] : [],
			post_type: postType || '',
			post_title: title
		} );

		// Perform the AJAX request
		fetch( ajaxurl + '?' + requestData.toString() )
			.then( ( response ) => response.json() )
			.then( ( data ) => {
				wp.data.dispatch( 'core/notices' ).removeNotice( 'unique-title-message' );

				if (data.status === 'error' || !unique_title_checker.only_unique_error) {
					const status = data.status === 'error' ? 'error' : 'success';

					wp.data.dispatch( 'core/notices' ).createNotice(
						status, // Can be one of: success, info, warning, error.
						data.message, // Text string to display.
						{
							id: 'unique-title-message',
							isDismissible: true, // Whether the user can dismiss the notice.
						}
					);
				}
			} )
			.catch( ( error ) => {
				console.error( 'Error fetching unique title check:', error );
			} );
	} );
} );
