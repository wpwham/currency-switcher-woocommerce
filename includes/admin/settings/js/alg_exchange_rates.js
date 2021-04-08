/**
 * Currency Switcher - Exchange Rates
 *
 * @version 2.14.0
 * @since   1.0.0
 * @author  Tom Anbinder
 * @author  WP Wham
 */

(function( $ ) {

	$(document).ready(function() {

		$(".alg_grab_exchage_rate_button").click(function(){
			var id = $( this ).attr( 'id' );
			var input_id = '#'+this.getAttribute('exchange_rates_field_id');
			var currencyFrom = this.getAttribute('currency_from');
			var currencyTo   = this.getAttribute('currency_to');
			var data = {
				'action': 'alg_wc_cs_get_exchange_rate',
				'alg_currency_from': currencyFrom,
				'alg_currency_to': currencyTo,
				'wpw_currency_server': $( '#alg_currency_switcher_exchange_rate_server_' + currencyFrom + '_' + currencyTo ).val()
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
		
		var toggleCoinMarketCapApi = function() {
			var show = false;
			$( '.alg-currency-switcher-exchange-rate-server' ).each( function(){
				if ( $( this ).val() === 'coinmarketcap' ) {
					show = true;
					return false;
				}
			});
			if ( show ) {
				$( '#wpw_currency_switcher_coinmarketcap_api_key' ).attr( 'required', true ).closest( 'tr' ).show();
			} else {
				$( '#wpw_currency_switcher_coinmarketcap_api_key' ).removeAttr( 'required' ).closest( 'tr' ).hide();
			}
		}
		$( '.alg-currency-switcher-exchange-rate-server' ).on( 'change', toggleCoinMarketCapApi );
		toggleCoinMarketCapApi();
		
	});

})( jQuery );
