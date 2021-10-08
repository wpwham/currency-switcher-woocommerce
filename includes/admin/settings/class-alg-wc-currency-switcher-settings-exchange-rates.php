<?php
/**
 * Currency Switcher - Exchange Rates Section Settings
 *
 * @version 2.15.0
 * @since   1.0.0
 * @author  Tom Anbinder
 * @author  WP Wham
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_Currency_Switcher_Settings_Exchange_Rates' ) ) :

class Alg_WC_Currency_Switcher_Settings_Exchange_Rates extends Alg_WC_Currency_Switcher_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 2.2.0
	 * @since   1.0.0
	 */
	function __construct() {
		$this->id   = 'exchange_rates';
		$this->desc = __( 'Exchange Rates', 'currency-switcher-woocommerce' );
		parent::__construct();
		add_action( 'woocommerce_admin_field_alg_exchange_rate',       array( $this, 'output_settings_button' ) );
		add_action( 'admin_enqueue_scripts',                           array( $this, 'enqueue_script' ) );
		add_action( 'wp_ajax_'        . 'alg_wc_cs_get_exchange_rate', array( $this, 'get_exchange_rate_ajax' ) );
		add_action( 'wp_ajax_nopriv_' . 'alg_wc_cs_get_exchange_rate', array( $this, 'get_exchange_rate_ajax' ) );
		add_action( 'admin_init',                                      array( $this, 'process_buttons' ) );
	}

	/**
	 * get_exchange_rate_ajax.
	 *
	 * @version 2.14.0
	 * @since   2.2.0
	 */
	function get_exchange_rate_ajax() {
		$currency_from   = sanitize_text_field( $_POST['alg_currency_from'] );
		$currency_to     = sanitize_text_field( $_POST['alg_currency_to'] );
		$server_override = $_POST['wpw_currency_server'] ? sanitize_text_field( $_POST['wpw_currency_server'] ) : false;
		echo alg_wc_cs_get_exchange_rate( $currency_from, $currency_to, $server_override );
		die();
	}

	/**
	 * enqueue_script.
	 *
	 * @version 2.15.0
	 * @since   1.0.0
	 */
	function enqueue_script() {
		global $pagenow;
		
		// check we are on the settings page
		if (
			$pagenow === 'admin.php'
			&& isset( $_REQUEST['tab'] ) && $_REQUEST['tab'] === 'alg_wc_currency_switcher'
			&& isset( $_REQUEST['section'] ) && $_REQUEST['section'] === 'exchange_rates'
		) {
			wp_enqueue_script( 'alg-exchange-rates', plugin_dir_url( __FILE__ ) . 'js/alg_exchange_rates.js', array( 'jquery' ), alg_wc_currency_switcher_plugin()->version, true );
			wp_localize_script( 'alg-exchange-rates', 'ajax_object', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
			) );
		}
	}

	/**
	 * output_settings_button.
	 *
	 * @version 2.0.0
	 * @since   1.0.0
	 */
	function output_settings_button( $value ) {

		$value['type'] = 'number';

		$option_value = get_option( $value['id'], $value['default'] );

		// Custom attribute handling
		$custom_attributes = array();
		if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) ) {
			foreach ( $value['custom_attributes'] as $attribute => $attribute_value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}
		$custom_attributes_button = array();
		if ( ! empty( $value['custom_attributes_button'] ) && is_array( $value['custom_attributes_button'] ) ) {
			foreach ( $value['custom_attributes_button'] as $attribute => $attribute_value ) {
				$custom_attributes_button[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}
		$tip = '';
		$description = '';
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
				<?php echo $tip; ?>
			</th>
			<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
				<input
					name="<?php echo esc_attr( $value['id'] ); ?>"
					id="<?php echo esc_attr( $value['id'] ); ?>"
					type="<?php echo esc_attr( $value['type'] ); ?>"
					style="<?php echo esc_attr( $value['css'] ); ?>"
					value="<?php echo esc_attr( $option_value ); ?>"
					class="<?php echo esc_attr( $value['class'] ); ?>"
					<?php echo implode( ' ', $custom_attributes ); ?>
					/>
				<input
					name="<?php echo esc_attr( $value['id'] . '_button' ); ?>"
					id="<?php echo esc_attr( $value['id'] . '_button' ); ?>"
					type="button"
					value="<?php echo esc_attr( $value['value'] ); ?>"
					title="<?php echo esc_attr( $value['value_title'] ); ?>"
					class="alg_grab_exchage_rate_button button-primary"
					style="background: #FFA500; border-color: #FF9500; text-shadow: 0 -1px 1px #FF8500,1px 0 1px #FF8500,0 1px 1px #FF8500,-1px 0 1px #FF8500; box-shadow: 0 1px 0 #FF8500;"
					<?php echo implode( ' ', $custom_attributes_button ); ?>
					/>
			</td>
		</tr>
		<?php
	}

	/**
	 * process_buttons.
	 *
	 * @version 2.0.0
	 * @since   2.0.0
	 */
	function process_buttons( $settings ) {
		if ( isset( $_GET['alg_update_all_exchange_rates'] ) && check_admin_referer( 'alg_update_all_exchange_rates' ) ) {
			// Update All Exchange Rates Now
			alg_wc_cs_update_the_exchange_rates();
			wp_safe_redirect( remove_query_arg( 'alg_update_all_exchange_rates' ) );
			exit();
		} elseif ( isset( $_GET['alg_reset_all_exchange_rates'] ) && check_admin_referer( 'alg_reset_all_exchange_rates' ) ) {
			// Reset All Rates
			$currency_from = get_option( 'woocommerce_currency' );
			foreach ( alg_get_enabled_currencies( false ) as $currency ) {
				update_option( 'alg_currency_switcher_exchange_rate_' . $currency_from . '_' . $currency, 1 );
			}
			wp_safe_redirect( remove_query_arg( 'alg_reset_all_exchange_rates' ) );
			exit();
		}
	}

	/**
	 * get_exchange_rates_settings.
	 *
	 * @version 2.14.0
	 * @since   1.0.0
	 * @todo    show custom offset input field only if "Custom Offset" is selected as "type"
	 * @todo    (maybe) optional additional "fixed" offset
	 */
	public static function get_exchange_rates_settings( $settings ) {
		$all_currencies = get_woocommerce_currencies();
		$currency_from = get_option( 'woocommerce_currency' );
		$desc = '';
		if ( 'manual' != get_option( 'alg_currency_switcher_exchange_rate_update', 'manual' ) ) {
			if ( '' != get_option( 'alg_currency_switcher_exchange_rate_cron_time', '' ) ) {
				$scheduled_time_diff = get_option( 'alg_currency_switcher_exchange_rate_cron_time', '' ) - time();
				if ( $scheduled_time_diff > 60 ) {
					$desc = sprintf( __( '%s till next update.', 'currency-switcher-woocommerce' ), human_time_diff( 0, $scheduled_time_diff ) );
				} elseif ( $scheduled_time_diff > 0 ) {
					$desc = sprintf( __( '%s seconds till next update.', 'currency-switcher-woocommerce' ), $scheduled_time_diff );
				}
			}
		}
		$settings = array_merge( $settings, array(
			array(
				'title'     => __( 'Exchange Rates', 'currency-switcher-woocommerce' ),
				'type'      => 'alg_title',
				'desc'      => $desc,
				'id'        => 'alg_wc_currency_switcher_exchange_rates_options',
				'buttons'  => array(
					array(
						'id'    => 'alg_update_all_exchange_rates',
						'link'  => add_query_arg( 'alg_update_all_exchange_rates', '1' ),
						'title' => __( 'Update all exchange rates now', 'currency-switcher-woocommerce' )
					),
					array(
						'id'    => 'alg_reset_all_exchange_rates',
						'link'  => add_query_arg( 'alg_reset_all_exchange_rates', '1' ),
						'title' => __( 'Reset all rates', 'currency-switcher-woocommerce' )
					),
				),
			),
			array(
				'title'     => __( 'Exchange rates updates', 'currency-switcher-woocommerce' ),
				'id'        => 'alg_currency_switcher_exchange_rate_update',
				'default'   => 'manual',
				'type'      => 'select',
				'class'     => 'wc-enhanced-select',
				'options'   => array(
					'manual'     => __( 'Enter Rates Manually', 'currency-switcher-woocommerce' ),
					'minutely'   => __( 'Update Automatically Every Minute', 'currency-switcher-woocommerce' ),
					'hourly'     => __( 'Update Automatically Hourly', 'currency-switcher-woocommerce' ),
					'twicedaily' => __( 'Update Automatically Twice Daily', 'currency-switcher-woocommerce' ),
					'daily'      => __( 'Update Automatically Daily', 'currency-switcher-woocommerce' ),
					'weekly'     => __( 'Update Automatically Weekly', 'currency-switcher-woocommerce' ),
				),
			),
			array(
				'title'     => __( 'Exchange rates server', 'currency-switcher-woocommerce' ),
				'id'        => 'alg_currency_switcher_exchange_rate_server',
				'default'   => 'ecb',
				'type'      => 'select',
				'class'     => 'alg-currency-switcher-exchange-rate-server wc-enhanced-select',
				'options'   => alg_wc_cs_get_exchange_rates_servers(),
			),
			array(
				'title'    => __( 'Free Currency Converter API Key', 'currency-switcher-woocommerce' ),
				'desc'     => sprintf(
					__( 'Free Currency Converter now requires an API key.  Get your key at %s', 'currency-switcher-woocommerce' ),
					'<a target="_blank" href="https://free.currencyconverterapi.com/free-api-key">https://free.currencyconverterapi.com/free-api-key</a>'
				),
				'type'     => 'text',
				'id'       => 'wpw_cs_fcc_api_key',
			),
			array(
				'title'    => __( 'CoinMarketCap API Key', 'currency-switcher-woocommerce' ),
				'desc'     => sprintf(
					__( 'CoinMarketCap now requires an API key.  Get your key at %s', 'currency-switcher-woocommerce' ),
					'<a target="_blank" href="https://coinmarketcap.com/api/pricing/">https://coinmarketcap.com/api/pricing/</a>'
				),
				'type'     => 'text',
				'id'       => 'wpw_currency_switcher_coinmarketcap_api_key',
			),
			array(
				'title'     => __( 'Exchange rates offset', 'currency-switcher-woocommerce' ),
				'desc'      => __( 'percent', 'currency-switcher-woocommerce' ),
				'id'        => 'alg_currency_switcher_exchange_rate_offset',
				'default'   => 0,
				'type'      => 'number',
				'custom_attributes' => array( 'step' => '0.000001' ),
			),
		) );
		foreach ( alg_get_enabled_currencies( false ) as $i => $currency_to ) {
			if ( $currency_from != $currency_to ) {
				$settings = array_merge( $settings, array(
					array(
						'title'                    => '#' . ( $i + 1 ) . ' ' . $currency_from . '/' . $currency_to . ' (' . $all_currencies[ $currency_to ] . ')',
						'id'                       => 'alg_currency_switcher_exchange_rate_' . $currency_from . '_' . $currency_to,
						'default'                  => 1,
						'type'                     => 'alg_exchange_rate',
						'custom_attributes'        => array( 'step' => 'any', 'min'  => '0' ),
						'custom_attributes_button' => array( 'currency_from' => $currency_from, 'currency_to' => $currency_to, 'exchange_rates_field_id' => 'alg_currency_switcher_exchange_rate_' . $currency_from . '_' . $currency_to ),
						'css'                      => 'width:100px;',
						'value'                    => $currency_from . '/' . $currency_to,
						'value_title'              => sprintf( __( 'Grab %s rate', 'currency-switcher-woocommerce' ), $currency_from . '/' . $currency_to ),
					),
					array(
						'desc_tip'  => __( 'Exchange Rates Server', 'currency-switcher-woocommerce' ),
						'id'        => 'alg_currency_switcher_exchange_rate_server_' . $currency_from . '_' . $currency_to,
						'default'   => 'default',
						'type'      => 'select',
						'class'     => 'alg-currency-switcher-exchange-rate-server wc-enhanced-select',
						'options'   => array_replace(
							array(
								'default' => __( 'Default server', 'currency-switcher-woocommerce' ) .
									' (' . alg_wc_cs_get_exchange_rates_server_title( get_option( 'alg_currency_switcher_exchange_rate_server', 'ecb' ) ) . ')',
							),
							alg_wc_cs_get_exchange_rates_servers()
						),
					),
					array(
						'desc_tip'  => __( 'Exchange Rates Offset', 'currency-switcher-woocommerce' ),
						'id'        => 'alg_currency_switcher_exchange_rate_offset_type_' . $currency_from . '_' . $currency_to,
						'default'   => 'default',
						'type'      => 'select',
						'class'     => 'wc-enhanced-select',
						'options'   => array(
							'default' => __( 'Default offset', 'currency-switcher-woocommerce' ) .
								' (' . get_option( 'alg_currency_switcher_exchange_rate_offset', 0 ) . '%)',
							'custom'  => __( 'Custom offset', 'currency-switcher-woocommerce' ) .
								' (' . get_option( 'alg_currency_switcher_exchange_rate_offset_' . $currency_from . '_' . $currency_to, 0 ) . '%)',
						),
					),
					array(
						'desc_tip'  => __( 'Custom Offset', 'currency-switcher-woocommerce' ),
						'desc'      => __( 'percent', 'currency-switcher-woocommerce' ),
						'id'        => 'alg_currency_switcher_exchange_rate_offset_' . $currency_from . '_' . $currency_to,
						'default'   => 0,
						'type'      => 'number',
						'custom_attributes' => array( 'step' => '0.000001' ),
					),
				) );
			}
		}
		$settings = array_merge( $settings, array(
			array(
				'type'      => 'sectionend',
				'id'        => 'alg_wc_currency_switcher_exchange_rates_options',
			),
		) );
		return $settings;
	}

}

endif;

return new Alg_WC_Currency_Switcher_Settings_Exchange_Rates();