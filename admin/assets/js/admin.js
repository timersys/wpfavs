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

	});

}(jQuery));