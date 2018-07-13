/**
 * Currency Switcher - Exchange Rates
 *
 * @version 2.2.0
 * @since   1.0.0
 * @author  Tom Anbinder
 */

jQuery(document).ready(function() {
	jQuery(".alg_grab_exchage_rate_button").click(function(){
		var input_id = '#'+this.getAttribute('exchange_rates_field_id');
		var data = {
			'action': 'alg_wc_cs_get_exchange_rate',
			'alg_currency_from': this.getAttribute('currency_from'),
			'alg_currency_to': this.getAttribute('currency_to')
		};
		jQuery.ajax({
			type: "POST",
			url: ajax_object.ajax_url,
			data: data,
			success: function(response) {
				jQuery(input_id).val(parseFloat(response));
			},
		});
		return false;
	});
});