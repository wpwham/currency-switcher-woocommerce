<?php
/**
 * Currency Switcher - Currency Locales Section Settings
 *
 * @version 2.12.2
 * @since   2.5.0
 * @author  Tom Anbinder
 * @author  WP Wham
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_Currency_Switcher_Settings_Currency_Locales' ) ) :

class Alg_WC_Currency_Switcher_Settings_Currency_Locales extends Alg_WC_Currency_Switcher_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 2.5.0
	 * @since   2.5.0
	 */
	function __construct() {
		$this->id   = 'currency_locales';
		$this->desc = __( 'Languages', 'currency-switcher-woocommerce' );
		parent::__construct();
		add_action( 'admin_init', array( $this, 'process_buttons' ) );
	}

	/**
	 * process_buttons.
	 *
	 * @version 2.5.0
	 * @since   2.5.0
	 */
	function process_buttons( $settings ) {
		if ( isset( $_GET['alg_auto_assign_locales_to_currencies'] ) && check_admin_referer( 'alg_auto_assign_locales_to_currencies' ) ) {
			// Auto Assign Locales
			$currency_locales = alg_wc_cc_get_currency_locales();
			foreach ( alg_get_enabled_currencies( false ) as $currency ) {
				if ( '' != $currency ) {
					update_option( 'alg_wc_currency_switcher_currency_locales_' . $currency, $currency_locales[ $currency ] );
				}
			}
			wp_safe_redirect( remove_query_arg( 'alg_auto_assign_locales_to_currencies' ) );
			exit();
		} elseif ( isset( $_GET['alg_reset_currencies_locales'] ) && check_admin_referer( 'alg_reset_currencies_locales' ) ) {
			// Reset Locales
			foreach ( alg_get_enabled_currencies( false ) as $currency ) {
				if ( '' != $currency ) {
					update_option( 'alg_wc_currency_switcher_currency_locales_' . $currency, array() );
				}
			}
			wp_safe_redirect( remove_query_arg( 'alg_reset_currencies_locales' ) );
			exit();
		}
	}

	/**
	 * get_currency_locales_settings.
	 *
	 * @version 2.12.2
	 * @since   2.5.0
	 * @todo    add more info (WPML, Polylang etc.)
	 */
	public static function get_currency_locales_settings( $settings ) {
		$all_currencies = get_woocommerce_currencies();
		$settings = array_merge( $settings, array(
			array(
				'title'     => __( 'Currency Languages (Locales)', 'currency-switcher-woocommerce' ),
				'type'      => 'alg_title',
				'desc'      => __( 'In this section you can set currencies to be automatically assigned depending on user\'s language.', 'currency-switcher-woocommerce' ) . '<br>' . '<br>' .
					__( 'Your shop\'s default currency will be assigned to all not selected locales.', 'currency-switcher-woocommerce' ),
				'id'        => 'alg_wc_currency_switcher_currency_locales_options',
				'buttons'  => array(
					array(
						'id'    => 'alg_auto_assign_locales_to_currencies',
						'link'  => add_query_arg( 'alg_auto_assign_locales_to_currencies', '1' ),
						'title' => __( 'Auto assign locales', 'currency-switcher-woocommerce' )
					),
					array(
						'id'    => 'alg_reset_currencies_locales',
						'link'  => add_query_arg( 'alg_reset_currencies_locales', '1' ),
						'title' => __( 'Reset locales', 'currency-switcher-woocommerce' )
					),
				),
			),
			array(
				'title'     => __( 'Currency Languages (Locales)', 'currency-switcher-woocommerce' ),
				'type'      => 'checkbox',
				'desc'      => '<strong>' . __( 'Enable section', 'currency-switcher-woocommerce' ) . '</strong>',
				'id'        => 'alg_wc_currency_switcher_currency_locales_enabled',
				'default'   => 'no',
			),
			array(
				'title'     => __( 'Enter locales as comma separated text', 'currency-switcher-woocommerce' ),
				'type'      => 'checkbox',
				'desc'      => __( 'Enable', 'currency-switcher-woocommerce' ),
				'desc_tip'  => __( 'To see different input fields, save changes after you set this option.', 'currency-switcher-woocommerce' ),
				'id'        => 'alg_wc_currency_switcher_currency_locales_as_text_enabled',
				'default'   => 'no',
			),
			array(
				'title'     => __( 'Always use locale to assign currency', 'currency-switcher-woocommerce' ),
				'type'      => 'checkbox',
				'desc'      => __( 'Enable', 'currency-switcher-woocommerce' ),
				'desc_tip'  => __( 'If disabled - currency will be assigned by locale only once (on first visit), then standard session value will be used.', 'currency-switcher-woocommerce' ),
				'id'        => 'alg_wc_currency_switcher_currency_locales_use_always_enabled',
				'default'   => 'yes',
			),
		) );
		$as_text = ( 'yes' === get_option( 'alg_wc_currency_switcher_currency_locales_as_text_enabled', 'no' ) );
		foreach ( alg_get_enabled_currencies( false ) as $i => $currency ) {
			if ( '' != $currency ) {
				$option_id = 'alg_wc_currency_switcher_currency_locales_' . $currency;
				alg_maybe_update_option_value_type( $option_id, $as_text );
				$settings = array_merge( $settings, array(
					array(
						'title'   => '#' . ( $i + 1 ) . ' [' . $currency . '] ' . $all_currencies[ $currency ],
						'id'      => $option_id,
						'default' => '',
						'type'    => ( $as_text ? 'text'    : 'multiselect' ),
						'options' => ( $as_text ? ''        : alg_wc_cc_get_all_locales() ),
						'class'   => ( $as_text ? 'widefat' : 'chosen_select' ),
					),
				) );
			}
		}
		$settings = array_merge( $settings, array(
			array(
				'type'      => 'sectionend',
				'id'        => 'alg_wc_currency_switcher_currency_locales_options',
			),
		) );
		return $settings;
	}

}

endif;

return new Alg_WC_Currency_Switcher_Settings_Currency_Locales();
