/**
 * Currency Switcher - Exchange Rates
 *
 * @version 2.2.0
 * @since   1.0.0
 * @author  Tom Anbinder
 */

(function( $ ) {

	$(document).ready(function() {

		$(".alg_grab_exchage_rate_button").click(function(){
			var id = $( this ).attr( 'id' );
			var input_id = '#'+this.getAttribute('exchange_rates_field_id');
			var data = {
				'action': 'alg_wc_cs_get_exchange_rate',
				'alg_currency_from': this.getAttribute('currency_from'),
				'alg_currency_to': this.getAttribute('currency_to')
			};
			$( this ).after( '<div class="spinner" style="visibility: visible; float: left;"></div>' );
			$.ajax({
				type: "POST",
				url: ajax_object.ajax_url,
				data: data,
				success: function(response) {
					$(input_id).val(parseFloat(response));
					$( '#' + id ).siblings( '.spinner' ).remove();
				},
			});
			return false;
		});
		
		var toggleFreeCurrencyConverterApi = function() {
			var show = false;
			$( '.alg-currency-switcher-exchange-rate-server' ).each( function(){
				if ( $( this ).val() === 'free_cur_api' ) {
					show = true;
					return false;
				}
			});
			if ( show ) {
				$( '#wpw_cs_fcc_api_key' ).attr( 'required', true ).closest( 'tr' ).show();
			} else {
				$( '#wpw_cs_fcc_api_key' ).removeAttr( 'required' ).closest( 'tr' ).hide();
			}
		}
		$( '.alg-currency-switcher-exchange-rate-server' ).on( 'change', toggleFreeCurrencyConverterApi );
		toggleFreeCurrencyConverterApi();
		
	});

})( jQuery );
