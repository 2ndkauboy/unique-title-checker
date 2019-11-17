/**
 * Check for the Block Editor
 *
 * @package unique-title-checker
 */

wp.domReady(
	function () {

			jQuery( '#post-title-0' ).blur(
				function () {

					var title = jQuery( this ).val();

					// Show no warning on empty titles.
					if ('' === title) {
						return;
					}

					var request_data = {
						action: 'unique_title_check',
						ajax_nonce: unique_title_checker.nonce,
						post__not_in: [jQuery( '#post_ID' ).val()],
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

							if ('error' === data.status || ! unique_title_checker.only_unique_error) {
								(function (wp) {
									// Overwrite status from 'updated' to 'success' in block editor.
									status = 'error' === data.status ? 'error' : 'success';
									wp.data.dispatch( 'core/notices' ).createNotice(
										status, // Can be one of: success, info, warning, error.
										data.message, // Text string to display.
										{
											id: 'unique-title-message',
											isDismissible: true, // Whether the user can dismiss the notice.
										}
									);
								})( window.wp );
							}
						}
					);

				}
			);

	}
);
