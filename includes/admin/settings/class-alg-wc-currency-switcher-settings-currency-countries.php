<?php
/**
 * Currency Switcher - Currency Countries Section Settings
 *
 * @version 2.12.2
 * @since   2.0.0
 * @author  Tom Anbinder
 * @author  WP Wham
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_Currency_Switcher_Settings_Currency_Countries' ) ) :

class Alg_WC_Currency_Switcher_Settings_Currency_Countries extends Alg_WC_Currency_Switcher_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 2.5.0
	 * @since   2.0.0
	 */
	function __construct() {
		$this->id   = 'currency_countries';
		$this->desc = __( 'Countries', 'currency-switcher-woocommerce' );
		parent::__construct();
		add_action( 'admin_init', array( $this, 'process_buttons' ) );
	}

	/**
	 * process_buttons.
	 *
	 * @version 2.0.0
	 * @since   2.0.0
	 */
	function process_buttons( $settings ) {
		if ( isset( $_GET['alg_auto_assign_countries_to_currencies'] ) && check_admin_referer( 'alg_auto_assign_countries_to_currencies' ) ) {
			// Auto Assign Countries
			foreach ( alg_get_enabled_currencies( false ) as $currency ) {
				if ( '' != $currency ) {
					$currency_countries = alg_get_currency_countries();
					update_option( 'alg_currency_switcher_currency_countries_' . $currency, $currency_countries[ $currency ] );
				}
			}
			wp_safe_redirect( remove_query_arg( 'alg_auto_assign_countries_to_currencies' ) );
			exit();
		} elseif ( isset( $_GET['alg_reset_currencies_countries'] ) && check_admin_referer( 'alg_reset_currencies_countries' ) ) {
			// Reset Countries
			foreach ( alg_get_enabled_currencies( false ) as $currency ) {
				if ( '' != $currency ) {
					update_option( 'alg_currency_switcher_currency_countries_' . $currency, array() );
				}
			}
			wp_safe_redirect( remove_query_arg( 'alg_reset_currencies_countries' ) );
			exit();
		}
	}

	/**
	 * get_currency_countries_settings.
	 *
	 * @version 2.12.2
	 * @since   2.0.0
	 * @todo    check if "geolocate" option in WooCommerce is really required, if so - fix the message
	 * @todo    (maybe) fix/expand description for "alg_wc_currency_switcher_currency_countries_options"
	 */
	public static function get_currency_countries_settings( $settings ) {
		$desc = '';
		if ( ! in_array( get_option( 'woocommerce_default_customer_address' ), array( 'geolocation_ajax', 'geolocation' ) ) ) {
			$desc = '<br>' . '<em>' . sprintf(
				__( 'Important: "Default Customer Location" is not set to "Geolocate" or "Geolocate (with page caching support)" in <a href="%s">WooCommerce > Settings > General</a>.', 'currency-switcher-woocommerce' ),
				admin_url( 'admin.php?page=wc-settings&tab=general' ) )
			. '</em>';
		}
		$all_currencies = get_woocommerce_currencies();
		$settings = array_merge( $settings, array(
			array(
				'title'     => __( 'Currency Countries (by IP)', 'currency-switcher-woocommerce' ),
				'type'      => 'alg_title',
				'desc'      => __( 'All not selected countries, will be assigned your shop\'s default currency.', 'currency-switcher-woocommerce' ) . $desc,
				'id'        => 'alg_wc_currency_switcher_currency_countries_options',
				'buttons'  => array(
					array(
						'id'    => 'alg_auto_assign_countries_to_currencies',
						'link'  => add_query_arg( 'alg_auto_assign_countries_to_currencies', '1' ),
						'title' => __( 'Auto assign countries', 'currency-switcher-woocommerce' )
					),
					array(
						'id'    => 'alg_reset_currencies_countries',
						'link'  => add_query_arg( 'alg_reset_currencies_countries', '1' ),
						'title' => __( 'Reset countries', 'currency-switcher-woocommerce' )
					),
				),
			),
			array(
				'title'     => __( 'Currency Countries (by IP)', 'currency-switcher-woocommerce' ),
				'type'      => 'checkbox',
				'desc'      => '<strong>' . __( 'Enable section', 'currency-switcher-woocommerce' ) . '</strong>',
				'id'        => 'alg_wc_currency_switcher_currency_countries_enabled',
				'default'   => 'no',
			),
			array(
				'title'     => __( 'Enter countries as comma separated text', 'currency-switcher-woocommerce' ),
				'type'      => 'checkbox',
				'desc'      => __( 'Enable', 'currency-switcher-woocommerce' ),
				'desc_tip'  => __( 'To see different input fields, save changes after you set this option.', 'currency-switcher-woocommerce' ),
				'id'        => 'alg_wc_currency_switcher_currency_countries_as_text_enabled',
				'default'   => 'no',
			),
			array(
				'title'     => __( 'Override country', 'currency-switcher-woocommerce' ),
				'type'      => 'select',
				'class'     => 'wc-enhanced-select',
				'id'        => 'alg_wc_currency_switcher_currency_countries_override',
				'default'   => 'disabled',
				'options'   => array(
					'disabled' => __( 'Override disabled', 'currency-switcher-woocommerce' ),
					'checkout_billing'  => __( 'Override with billing country on checkout page only', 'currency-switcher-woocommerce' ),
					'all_site_billing'  => __( 'Override with billing country on all site', 'currency-switcher-woocommerce' ),
					'checkout_shipping' => __( 'Override with shipping country on checkout page only', 'currency-switcher-woocommerce' ),
					'all_site_shipping' => __( 'Override with shipping country on all site', 'currency-switcher-woocommerce' ),
				),
			),
		) );
		$as_text = ( 'yes' === get_option( 'alg_wc_currency_switcher_currency_countries_as_text_enabled', 'no' ) );
		foreach ( alg_get_enabled_currencies( false ) as $i => $currency ) {
			if ( '' != $currency ) {
				$option_id = 'alg_currency_switcher_currency_countries_' . $currency;
				alg_maybe_update_option_value_type( $option_id, $as_text );
				$settings = array_merge( $settings, array(
					array(
						'title'   => '#' . ( $i + 1 ) . ' [' . $currency . '] ' . $all_currencies[ $currency ],
						'id'      => $option_id,
						'default' => '',
						'type'    => ( $as_text ? 'text'    : 'multiselect' ),
						'options' => ( $as_text ? ''        : alg_get_countries() ),
						'class'   => ( $as_text ? 'widefat' : 'chosen_select' ),
					),
				) );
			}
		}
		$settings = array_merge( $settings, array(
			array(
				'type'      => 'sectionend',
				'id'        => 'alg_wc_currency_switcher_currency_countries_options',
			),
		) );
		return $settings;
	}

}

endif;

return new Alg_WC_Currency_Switcher_Settings_Currency_Countries();
