/**
 * Check for the Block Editor
 *
 * @package unique-title-checker
 */

var closeListener = wp.data.subscribe( function () {
	let iframedEditor =  document.querySelector('.editor-canvas__iframe');
	let postTitleInput = document.querySelector( '.wp-block-post-title' );
	if ( !iframedEditor && !postTitleInput ) {
		// Editor not ready.
		return;
	}

	if (iframedEditor) {
		// Overwrite the post title input selector if inside the iframe.
		postTitleInput = iframedEditor.contentWindow.document.querySelector('.wp-block-post-title');
	}

	if ( !postTitleInput ) {
		// Post title input is not ready.
		return;
	}

	// Close the listener as soon as we know we are ready to avoid an infinite loop.
	closeListener();

	jQuery( postTitleInput ).blur(
		function () {
			var title = this.innerText;

			// Show no warning on empty titles.
			if ( '' === title ) {
				return;
			}

			var request_data = {
				action: 'unique_title_check',
				ajax_nonce: unique_title_checker.nonce,
				post__not_in: [ jQuery( '#post_ID' ).val() ],
				post_type: jQuery( '#post_type' ).val(),
				post_title: title
			};

			jQuery.ajax(
				{
					url: ajaxurl,
					data: request_data,
					dataType: 'json'
				}
			).done(
				function ( data ) {
					wp.data.dispatch( 'core/notices' ).removeNotice( 'unique-title-message' );

					if ( 'error' === data.status || !unique_title_checker.only_unique_error ) {
						( function ( wp ) {
							// Overwrite status from 'updated' to 'success' in block editor.
							var status = 'error' === data.status ? 'error' : 'success';
							wp.data.dispatch( 'core/notices' ).createNotice(
								status, // Can be one of: success, info, warning, error.
								data.message, // Text string to display.
								{
									id: 'unique-title-message',
									isDismissible: true, // Whether the user can dismiss the notice.
								}
							);
						} )( window.wp );
					}
				}
			);
		}
	);
} );
