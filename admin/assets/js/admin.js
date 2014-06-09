(function ( $ ) {
	"use strict";

	$(function () {

		$('.wpfav_apikey-button').click( function (e) {

			e.preventDefault();
			$('.loading').fadeIn();

			var what = $(this).attr('data-what');

			$.ajax({
				url: wpfavs.ajax_url,
				data: { action: 'wpfav_' + what, nonce : wpfavs.nonce, api_key: $('#wpfav_' + what).val() },
				success: function( response ) {
					$('.loading').hide();
					$('#wpfav-response').html( response );
				},
				type: "POST",
			});

		});
		//Prevent form submission with enter key
		$("#wpfavs-form").keypress( function( e ){
		    if( e.keyCode == 13 ){
		        return false;
		    }
		});
		//If enter is pressed click button
		$('#wpfavs-form .regular-text').keyup( function( e ){
			if( e.keyCode == 13 ){
		       $(this).next('.wpfav_apikey-button').click();
		    }
		});

	});

}(jQuery));