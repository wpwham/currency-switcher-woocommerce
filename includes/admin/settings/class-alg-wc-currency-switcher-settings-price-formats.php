<?php
/**
 * Currency Switcher - Price Formats Section Settings
 *
 * @version 2.12.2
 * @since   2.4.0
 * @author  Tom Anbinder
 * @author  WP Wham
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_Currency_Switcher_Settings_Price_Formats' ) ) :

class Alg_WC_Currency_Switcher_Settings_Price_Formats extends Alg_WC_Currency_Switcher_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 2.4.0
	 * @since   2.4.0
	 */
	function __construct() {
		$this->id   = 'price_formats';
		$this->desc = __( 'Price Formats', 'currency-switcher-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_price_formats_settings.
	 *
	 * @version 2.12.2
	 * @since   2.4.0
	 * @todo    spaces in separators
	 * @todo    WPML (check Booster)
	 */
	public static function get_price_formats_settings( $settings ) {
		$all_currencies = get_woocommerce_currencies();
		$settings = array_merge( $settings, array(
			array(
				'title'     => __( 'Price Formats', 'currency-switcher-woocommerce' ),
				'type'      => 'title',
				'id'        => 'alg_wc_currency_switcher_price_formats_options',
			),
			array(
				'title'     => __( 'Price Formats', 'currency-switcher-woocommerce' ),
				'type'      => 'checkbox',
				'desc'      => '<strong>' . __( 'Enable section', 'currency-switcher-woocommerce' ) . '</strong>',
				'id'        => 'alg_wc_currency_switcher_price_formats_enabled',
				'default'   => 'no',
			),
		) );
		$currency = get_option( 'woocommerce_currency' );
		$currency_symbol = get_woocommerce_currency_symbol( $currency );
		$settings = array_merge( $settings, array(
			array(
				'title'    => '#0' . ' [' . $currency . '] ' . $all_currencies[ $currency ],
				'desc'     => __( 'Currency position', 'woocommerce' ),
				'id'       => 'woocommerce_currency_pos',
				'default'  => 'left',
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'options'  => array(
					'left'        => __( 'Left', 'woocommerce' ) . ' (' . $currency_symbol . '99.99)',
					'right'       => __( 'Right', 'woocommerce' ) . ' (99.99' . $currency_symbol . ')',
					'left_space'  => __( 'Left with space', 'woocommerce' ) . ' (' . $currency_symbol . ' 99.99)',
					'right_space' => __( 'Right with space', 'woocommerce' ) . ' (99.99 ' . $currency_symbol . ')',
				),
			),
			array(
				'desc'     => __( 'Additional currency code position (optional)', 'currency-switcher-woocommerce'  ),
				'id'       => 'alg_wc_currency_switcher_price_formats_currency_code_pos_' . $currency,
				'default'  => 'none',
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'options'  => array(
					'none'        => __( 'Do not add currency code', 'currency-switcher-woocommerce' ),
					'left'        => __( 'Left', 'woocommerce' ),
					'right'       => __( 'Right', 'woocommerce' ),
					'left_space'  => __( 'Left with space', 'woocommerce' ),
					'right_space' => __( 'Right with space', 'woocommerce' ),
				),
			),
			array(
				'desc'     => __( 'Thousand separator', 'woocommerce' ),
				'id'       => 'woocommerce_price_thousand_sep',
				'default'  => ',',
				'type'     => 'text',
			),
			array(
				'desc'     => __( 'Decimal separator', 'woocommerce' ),
				'id'       => 'woocommerce_price_decimal_sep',
				'default'  => '.',
				'type'     => 'text',
			),
			array(
				'desc'     => __( 'Number of decimals', 'woocommerce' ),
				'id'       => 'woocommerce_price_num_decimals',
				'default'  => '2',
				'type'     => 'number',
				'custom_attributes' => array(
					'min'  => 0,
					'step' => 1,
				),
			),
			array(
				'desc'     => __( 'Currency Symbol', 'currency-switcher-woocommerce'  ),
				'id'       => 'alg_wc_currency_switcher_price_formats_currency_code_' . $currency, // mislabeled, should be `alg_wc_currency_switcher_price_formats_currency_symbol_`
				'default'  => $currency_symbol,
				'type'     => 'text',
			),
		) );
		foreach ( alg_get_enabled_currencies( false ) as $i => $currency ) {
			if ( '' != $currency ) {
				$currency_symbol = get_woocommerce_currency_symbol( $currency );
				$settings = array_merge( $settings, array(
					array(
						'title'    => '#' . ( $i + 1 ) . ' [' . $currency . '] ' . $all_currencies[ $currency ],
						'desc'     => __( 'Currency position', 'woocommerce'  ),
						'id'       => 'alg_wc_currency_switcher_price_formats_currency_position_' . $currency,
						'default'  => get_option( 'woocommerce_currency_pos' ),
						'type'     => 'select',
						'class'    => 'wc-enhanced-select',
						'options'  => array(
							'left'        => __( 'Left', 'woocommerce' ) . ' (' . $currency_symbol . '99.99)',
							'right'       => __( 'Right', 'woocommerce' ) . ' (99.99' . $currency_symbol . ')',
							'left_space'  => __( 'Left with space', 'woocommerce' ) . ' (' . $currency_symbol . ' 99.99)',
							'right_space' => __( 'Right with space', 'woocommerce' ) . ' (99.99 ' . $currency_symbol . ')'
						),
					),
					array(
						'desc'     => __( 'Additional currency code position (optional)', 'currency-switcher-woocommerce'  ),
						'id'       => 'alg_wc_currency_switcher_price_formats_currency_code_pos_' . $currency,
						'default'  => 'none',
						'type'     => 'select',
						'class'    => 'wc-enhanced-select',
						'options'  => array(
							'none'        => __( 'Do not add currency code', 'currency-switcher-woocommerce' ),
							'left'        => __( 'Left', 'woocommerce' ),
							'right'       => __( 'Right', 'woocommerce' ),
							'left_space'  => __( 'Left with space', 'woocommerce' ),
							'right_space' => __( 'Right with space', 'woocommerce' ),
						),
					),
					array(
						'desc'     => __( 'Thousand separator', 'woocommerce'  ),
						'id'       => 'alg_wc_currency_switcher_price_formats_thousand_separator_' . $currency,
						'default'  => wc_get_price_thousand_separator(),
						'type'     => 'text',
					),
					array(
						'desc'     => __( 'Decimal separator', 'woocommerce'  ),
						'id'       => 'alg_wc_currency_switcher_price_formats_decimal_separator_' . $currency,
						'default'  => wc_get_price_decimal_separator(),
						'type'     => 'text',
					),
					array(
						'desc'     => __( 'Number of decimals', 'woocommerce'  ),
						'id'       => 'alg_wc_currency_switcher_price_formats_number_of_decimals_' . $currency,
						'default'  => wc_get_price_decimals(),
						'type'     => 'number',
						'custom_attributes' => array( 'min'  => 0, 'step' => 1 ),
					),
					array(
						'desc'     => __( 'Currency Symbol', 'currency-switcher-woocommerce'  ),
						'id'       => 'alg_wc_currency_switcher_price_formats_currency_code_' . $currency, // mislabeled, should be `alg_wc_currency_switcher_price_formats_currency_symbol_`
						'default'  => get_woocommerce_currency_symbol( $currency ),
						'type'     => 'text',
					),
				) );
			}
		}
		$settings = array_merge( $settings, array(
			array(
				'type'      => 'sectionend',
				'id'        => 'alg_wc_currency_switcher_price_formats_options',
			),
		) );
		return $settings;
	}

}

endif;

return new Alg_WC_Currency_Switcher_Settings_Price_Formats();
