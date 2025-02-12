/**
 * Check for the Classic Editor
 *
 * @package unique-title-checker
 */

document.addEventListener( "DOMContentLoaded", function () {
	document.getElementById( "title" ).addEventListener( "blur", function () {
		var title = this.value;

		// Show no warning on empty titles.
		if (title === "") {
			return;
		}

		var requestData = new URLSearchParams( {
			action: "unique_title_check",
			ajax_nonce: unique_title_checker.nonce,
			post_id: document.getElementById( "post_ID" ).value,
			post_type: document.getElementById( "post_type" ).value,
			post_title: title
		} );

		fetch( ajaxurl + "?" + requestData.toString() )
			.then( response => response.json() )
			.then( data => {
				var messageElement = document.getElementById( "unique-title-message" );
				if (messageElement) {
					messageElement.remove();
				}

				if (data.status === "error" || !unique_title_checker.only_unique_error) {
					document.getElementById( "post" ).insertAdjacentHTML(
						"beforebegin",
						`<div id="unique-title-message" class="${ data.status }">
							<p>${ data.message }</p>
						</div>`
					);
				}
			} )
			.catch( error => console.error( "Error fetching unique title check:", error ) );
	} );
} );
