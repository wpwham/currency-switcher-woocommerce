<?php
/**
 * Currency Switcher - Currencies Section Settings
 *
 * @version 2.15.0
 * @since   1.0.0
 * @author  Tom Anbinder
 * @author  WP Wham
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_Currency_Switcher_Settings_Currencies' ) ) :

class Alg_WC_Currency_Switcher_Settings_Currencies extends Alg_WC_Currency_Switcher_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 2.0.0
	 * @since   1.0.0
	 */
	function __construct() {
		$this->id   = 'currencies';
		$this->desc = __( 'Currencies', 'currency-switcher-woocommerce' );
		parent::__construct();
		add_action( 'woocommerce_admin_field_alg_custom_number', array( $this, 'output_custom_number' ) );
		add_action( 'admin_init', array( $this, 'process_buttons' ) );
	}

	/**
	 * output_custom_number.
	 *
	 * @version 2.0.0
	 * @since   1.0.0
	 */
	function output_custom_number( $value ) {
		$type         = 'number';
		$option_value = get_option( $value['id'], $value['default'] );
		$tooltip_html = ( isset( $value['desc_tip'] ) && '' != $value['desc_tip'] ) ? '<span class="woocommerce-help-tip" data-tip="' . $value['desc_tip'] . '"></span>' : '';
		$description  = ' <span class="description">' . $value['desc'] . '</span>';
		$style        = 'background: #ba0000; border-color: #aa0000; text-shadow: 0 -1px 1px #990000,1px 0 1px #990000,0 1px 1px #990000,-1px 0 1px #990000; box-shadow: 0 1px 0 #990000;';
		$save_button  = ' <input name="save" class="button-primary" style="' . $style . '" type="submit" value="' . __( 'Save changes', 'woocommerce' ) . '">';
		$custom_attributes = array();
		if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) ) {
			foreach ( $value['custom_attributes'] as $attribute => $attribute_value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}
		?><tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
				<?php echo $tooltip_html; ?>
			</th>
			<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
				<input
					name="<?php echo esc_attr( $value['id'] ); ?>"
					id="<?php echo esc_attr( $value['id'] ); ?>"
					type="<?php echo esc_attr( $type ); ?>"
					style="<?php echo esc_attr( $value['css'] ); ?>"
					value="<?php echo esc_attr( $option_value ); ?>"
					class="<?php echo esc_attr( $value['class'] ); ?>"
					placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
					<?php echo implode( ' ', $custom_attributes ); ?>
					/><?php echo $save_button; ?><?php echo $description; ?>
			</td>
		</tr><?php
	}

	/**
	 * process_buttons.
	 *
	 * @version 2.0.0
	 * @since   2.0.0
	 * @todo    RMB is missing in get_woocommerce_currencies()
	 */
	function process_buttons( $settings ) {
		if ( isset( $_GET['alg_auto_generate_paypal_supported_currencies'] ) && check_admin_referer( 'alg_auto_generate_paypal_supported_currencies' ) ) {
			// Auto Generate PayPal Supported Currencies
			$paypal_supported_currencies = apply_filters( 'woocommerce_paypal_supported_currencies', array( 'AUD', 'BRL', 'CAD', 'MXN', 'NZD', 'HKD', 'SGD', 'USD', 'EUR', 'JPY', 'TRY', 'NOK', 'CZK', 'DKK', 'HUF', 'ILS', 'MYR', 'PHP', 'PLN', 'SEK', 'CHF', 'TWD', 'THB', 'GBP', /* 'RMB', */ 'RUB' ) );
			$currency_from  = get_option( 'woocommerce_currency' );
			if ( false !== ( $key = array_search( $currency_from, $paypal_supported_currencies ) ) ) {
				unset( $paypal_supported_currencies[ $key ] );
			}
			update_option( 'alg_currency_switcher_total_number', count( $paypal_supported_currencies ) );
			$i = 1;
			foreach ( $paypal_supported_currencies as $currency ) {
				update_option( 'alg_currency_switcher_currency_' . ( $i++ ), $currency );
			}
			wp_safe_redirect( remove_query_arg( 'alg_auto_generate_paypal_supported_currencies' ) );
			exit();
		}
	}

	/**
	 * get_currencies_settings.
	 *
	 * @version 2.15.0
	 * @since   1.0.0
	 * @todo    (maybe) add "generate all world currencies" - makes sense only for Pro version
	 */
	public static function get_currencies_settings( $settings ) {
		$currency_from  = get_option( 'woocommerce_currency' );
		$all_currencies = get_woocommerce_currencies();
		$all_currencies_modified = array();
		foreach ( $all_currencies as $currency_code => $currency_name ) {
			$all_currencies_modified[ $currency_code ] = '[' . $currency_code . '] ' . $currency_name;
		}
		$currency_from_full_name = $all_currencies_modified[ $currency_from ];
		update_option( 'alg_currency_switcher_currency_shop_default', $currency_from_full_name );
		$settings = array_merge( $settings, array(
			array(
				'title'    => __( 'Currencies', 'currency-switcher-woocommerce' ),
				'type'     => 'alg_title',
				'desc'     => sprintf( __( 'Select <strong>additional</strong> currencies here. Your shop\'s default currency [%s] will be added automatically.', 'currency-switcher-woocommerce' ), $currency_from ) . ' ' .
					sprintf( __( 'If some currencies are missing from the list, we suggest using <a target="_blank" href="%s">All Currencies for WooCommerce</a> plugin.', 'currency-switcher-woocommerce' ), 'https://wordpress.org/plugins/woocommerce-all-currencies/' ),
				'buttons'  => ( PHP_INT_MAX === apply_filters( 'alg_wc_currency_switcher_plugin_option', 2 ) ) ?
					array(
						array(
							'id'    => 'alg_auto_generate_paypal_supported_currencies',
							'link'  => add_query_arg( 'alg_auto_generate_paypal_supported_currencies', '1' ),
							'title' => __( 'Auto generate PayPal supported currencies', 'currency-switcher-woocommerce' )
						),
					) : array(),
				'id'       => 'alg_currency_switcher_currencies_options',
			),
			array(
				'title'    => __( 'Currency (shop\'s default)', 'currency-switcher-woocommerce' ),
				'id'       => 'alg_currency_switcher_currency_shop_default',
				'default'  => $currency_from_full_name,
				'type'     => 'text',
				'css'      => 'width:250px;',
				'custom_attributes' => array( 'readonly' => 'readonly' ),
//				'desc'     => '<p>' . sprintf( __( 'As set in <a href="%s">WooCommerce > Settings > General</a>.', 'currency-switcher-woocommerce' ),
//					admin_url( 'admin.php?page=wc-settings&tab=general' ) ) . '</p>',
			),
			array(
				'title'    => __( 'Total additional currencies', 'currency-switcher-woocommerce' ),
				'desc_tip' => __( 'Save changes after you update this number.', 'currency-switcher-woocommerce' ),
				'id'       => 'alg_currency_switcher_total_number',
				'default'  => 2,
				'type'     => 'alg_custom_number',
				'desc'     => ( PHP_INT_MAX === apply_filters( 'alg_wc_currency_switcher_plugin_option', 2 ) ) ?
					'' : sprintf(
						'<p>' . __( 'To add more than two additional currencies, you will need %s plugin.', 'currency-switcher-woocommerce' ) . '</p>',
						'<a target="_blank" href="' . esc_url( 'https://wpwham.com/products/currency-switcher-for-woocommerce/?utm_source=settings_currencies&utm_campaign=free&utm_medium=currency_switcher' ) . '">' .
							__( 'Currency Switcher for WooCommerce Pro', 'currency-switcher-woocommerce' ) . '</a>'
						),
				'custom_attributes' => ( PHP_INT_MAX === apply_filters( 'alg_wc_currency_switcher_plugin_option', 2 ) ) ?
					array( 'step' => '1', 'min' => '1' ) : array( 'step' => '1', 'min' => '1', 'max' => '2' ),
			),
		) );
		$total_number = min( get_option( 'alg_currency_switcher_total_number', 2 ), apply_filters( 'alg_wc_currency_switcher_plugin_option', 2 ) );
		for ( $i = 1; $i <= $total_number; $i++ ) {
			$settings = array_merge( $settings, array(
				array(
					'title'    => __( 'Additional currency', 'currency-switcher-woocommerce' ) . ' #' . $i,
					'desc'     => __( 'Enabled', 'currency-switcher-woocommerce' ),
					'id'       => 'alg_currency_switcher_currency_enabled_' . $i,
					'default'  => 'yes',
					'type'     => 'checkbox',
				),
				array(
					'id'       => 'alg_currency_switcher_currency_' . $i,
					'default'  => $currency_from,
					'type'     => 'select',
					'class'    => 'wc-enhanced-select',
					'options'  => $all_currencies_modified,
					'css'      => 'width:250px;',
				),
			) );
		}
		$settings = array_merge( $settings, array(
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_currency_switcher_currencies_options',
			),
		) );
		return $settings;
	}

}

endif;

return new Alg_WC_Currency_Switcher_Settings_Currencies();
