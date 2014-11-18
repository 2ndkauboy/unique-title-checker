jQuery( document ).ready( function ( $ ) {

	$( '#title' ).blur( function () {

		var title = $( this ).val();

		// show no warning on empty titles
		if ( '' == title ) {
			return;
		}

		var request_data = {
			action: 'unique_title_check',
			ajax_nonce: unique_title_checker.nonce,
			post__not_in: [ $( '#post_ID' ).val() ],
			post_type: $( '#post_type' ).val(),
			post_title: title
		};

		$.ajax( {
			url: ajaxurl,
			data: request_data,
			dataType: 'json'
		} ).done( function ( data ) {
			$( '#unique-title-message' ).remove();
			$( '#post' ).before( '<div id="unique-title-message" class="' + data.status + '"><p>' + data.message + '</p></div>' );
		} );

	} );

} );