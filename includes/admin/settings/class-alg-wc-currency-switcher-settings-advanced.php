<?php
/**
 * Currency Switcher - Advanced Section Settings
 *
 * @version 2.12.2
 * @since   2.8.3
 * @author  Tom Anbinder
 * @author  WP Wham
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_Currency_Switcher_Settings_Advanced' ) ) :

class Alg_WC_Currency_Switcher_Settings_Advanced extends Alg_WC_Currency_Switcher_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 2.8.3
	 * @since   2.8.3
	 */
	function __construct() {
		$this->id   = 'advanced';
		$this->desc = __( 'Advanced', 'currency-switcher-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_advanced_settings.
	 *
	 * @version 2.12.2
	 * @since   2.8.3
	 * @todo    "Session Type" - finish (now problem is that `WC()` is called too early, so probably all sessions related code must be moved to e.g. `init` hook)
	 * @todo    "Price Conversion Method" - maybe set `save_in_array` as default value
	 * @todo    (maybe) "Session Save Path" - a) `unclean_text`; b) reload after change;
	 * @todo    (maybe) re-enable "Show Flags in Admin Settings Section" option
	 */
	public static function get_advanced_settings( $settings ) {
		$settings = array_merge( $settings, array(
			array(
				'title'     => __( 'Advanced Options', 'currency-switcher-woocommerce' ),
				'type'      => 'title',
				'id'        => 'alg_wc_currency_switcher_advanced_options',
			),
			array(
				'title'    => __( 'Price conversion method', 'currency-switcher-woocommerce' ),
				'desc_tip' => __( 'This may help if you are experiencing compatibility issues with other plugins.', 'currency-switcher-woocommerce' ),
				'id'       => 'alg_wc_currency_switcher_price_conversion_method',
				'default'  => 'simple',
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'options'  => array(
					'simple'        => __( 'Simple', 'currency-switcher-woocommerce' ),
					'save_in_array' => __( 'Save prices in array', 'currency-switcher-woocommerce' ),
				),
			),
			array(
				'title'    => __( 'Fix mini cart', 'currency-switcher-woocommerce' ),
				'desc'     => __( 'Enable', 'currency-switcher-woocommerce' ),
				'desc_tip' => __( 'Enable this option if you have issues with currencies in mini cart. It will recalculate cart totals on each page load.', 'currency-switcher-woocommerce' ),
				'id'       => 'alg_currency_switcher_fix_mini_cart',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			/* array(
				'title'    => __( 'Show flags in admin settings section', 'currency-switcher-woocommerce' ),
				'desc'     => __( 'Show', 'currency-switcher-woocommerce' ),
				'id'       => 'alg_currency_switcher_show_flags_in_admin_settings_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
			), */
			array(
				'title'    => __( 'Disable on URI', 'currency-switcher-woocommerce' ),
				'desc'     => __( 'List URIs where you want switcher functionality to be disabled. One per line. Leave blank if not sure.', 'currency-switcher-woocommerce' ),
				'desc_tip' => __( 'Enter only the part AFTER your domain. For example if the URL is https://example.com/product/widget/, the URI to enter here is /product/widget/', 'currency-switcher-woocommerce' ),
				'id'       => 'alg_currency_switcher_disable_uri',
				'default'  => '',
				'type'     => 'textarea',
				'css'      => 'width:100%;min-height:100px;',
			),
			array(
				'title'    => __( 'Additional price filters', 'currency-switcher-woocommerce' ),
				'desc'     => __( 'List additional price filters to apply price conversion by currency. One per line. Leave blank if not sure.', 'currency-switcher-woocommerce' ),
				'id'       => 'alg_currency_switcher_additional_price_filters',
				'default'  => '',
				'type'     => 'textarea',
				'css'      => 'width:100%;min-height:100px;',
			),
			array(
				'title'    => __( 'Price filters to remove', 'currency-switcher-woocommerce' ),
				'desc'     => __( 'List price filters to remove. One per line. Leave blank if not sure.', 'currency-switcher-woocommerce' ),
				'id'       => 'alg_currency_switcher_price_filters_to_remove',
				'default'  => '',
				'type'     => 'textarea',
				'css'      => 'width:100%;min-height:100px;',
			),
			array(
				'title'    => __( 'Session save path', 'currency-switcher-woocommerce' ),
				'desc'     => '<br>' . __( 'Leave blank to use standard session save path.', 'currency-switcher-woocommerce' ) .
					( function_exists( 'session_save_path' ) && '' != session_save_path() ?
						' ' . sprintf( __( 'Currently: %s.', 'currency-switcher-woocommerce' ), '<code>' . session_save_path() . '</code>' ) : '' ),
				'id'       => 'alg_wc_currency_switcher_session_save_path',
				'default'  => '',
				'type'     => 'text',
				'css'      => 'width:100%;',
			),
			/* array(
				'title'    => __( 'Session type', 'currency-switcher-woocommerce' ),
				'id'       => 'alg_wc_currency_switcher_session_type',
				'default'  => 'standard',
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'options'  => array(
					'standard' => __( 'Standard PHP sessions', 'currency-switcher-woocommerce' ),
					'wc'       => __( 'WC sessions', 'currency-switcher-woocommerce' ),
				),
			), */
			array(
				'type'      => 'sectionend',
				'id'        => 'alg_wc_currency_switcher_advanced_options',
			),
		) );
		return $settings;
	}

}

endif;

return new Alg_WC_Currency_Switcher_Settings_Advanced();
