/**
 * Check for the Classic Editor
 *
 * @package unique-title-checker
 */

jQuery( document ).ready(
	function () {

			jQuery( '#title' ).blur(
				function () {

					var title = jQuery( this ).val();

					// Show no warning on empty titles.
					if ( '' === title ) {
						return;
					}

					var request_data = {
						action      : 'unique_title_check',
						ajax_nonce  : unique_title_checker.nonce,
						post__not_in: [ jQuery( '#post_ID' ).val() ],
						post_type   : jQuery( '#post_type' ).val(),
						post_title  : title
					};

					jQuery.ajax(
						{
							url     : ajaxurl,
							data    : request_data,
							dataType: 'json'
						}
					).done(
						function ( data ) {
							jQuery( '#unique-title-message' ).remove();

							if ( 'error' === data.status || ! unique_title_checker.only_unique_error ) {
								jQuery( '#post' ).before( '<div id="unique-title-message" class="' + data.status + '"><p>' + data.message + '</p></div>' );
							}
						}
					);

				}
			);

	}
);
