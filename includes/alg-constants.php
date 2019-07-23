<?php
/**
 * WooCommerce Currency Switcher - Constants
 *
 * @version 2.8.0
 * @since   2.3.0
 * @author  Tom Anbinder
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'ALG_WC_VERSION' ) ) {
	/**
	 * WooCommerce version.
	 *
	 * @version 2.3.0
	 * @since   2.3.0
	 */
	define( 'ALG_WC_VERSION', get_option( 'woocommerce_version', null ) );
}

if ( ! defined( 'ALG_IS_WC_VERSION_BELOW_3' ) ) {
	/**
	 * WooCommerce version - is below version 3.0.0.
	 *
	 * @version 2.3.0
	 * @since   2.3.0
	 */
	define( 'ALG_IS_WC_VERSION_BELOW_3', version_compare( ALG_WC_VERSION, '3.0.0', '<' ) );
}

if ( ! defined( 'ALG_IS_WC_VERSION_AT_LEAST_3_2' ) ) {
	/**
	 * WooCommerce version - is at least version 3.2.0.
	 *
	 * @version 2.8.0
	 * @since   2.8.0
	 */
	define( 'ALG_IS_WC_VERSION_AT_LEAST_3_2', version_compare( ALG_WC_VERSION, '3.2.0', '>=' ) );
}

if ( ! defined( 'ALG_PRODUCT_GET_PRICE_FILTER' ) ) {
	/**
	 * WooCommerce price filters - price.
	 *
	 * @version 2.3.0
	 * @since   2.3.0
	 */
	define( 'ALG_PRODUCT_GET_PRICE_FILTER', ( ALG_IS_WC_VERSION_BELOW_3 ? 'woocommerce_get_price' : 'woocommerce_product_get_price' ) );
}

if ( ! defined( 'ALG_PRODUCT_GET_SALE_PRICE_FILTER' ) ) {
	/**
	 * WooCommerce price filters - sale price.
	 *
	 * @version 2.3.0
	 * @since   2.3.0
	 */
	define( 'ALG_PRODUCT_GET_SALE_PRICE_FILTER', ( ALG_IS_WC_VERSION_BELOW_3 ? 'woocommerce_get_sale_price' : 'woocommerce_product_get_sale_price' ) );
}

if ( ! defined( 'ALG_PRODUCT_GET_REGULAR_PRICE_FILTER' ) ) {
	/**
	 * WooCommerce price filters - regular price.
	 *
	 * @version 2.3.0
	 * @since   2.3.0
	 */
	define( 'ALG_PRODUCT_GET_REGULAR_PRICE_FILTER', ( ALG_IS_WC_VERSION_BELOW_3 ? 'woocommerce_get_regular_price' : 'woocommerce_product_get_regular_price' ) );
}

if ( ! defined( 'ALG_WC_CS_SESSION_TYPE' ) ) {
	/**
	 * Session type.
	 *
	 * @version 2.7.0
	 * @since   2.7.0
	 */
//	define( 'ALG_WC_CS_SESSION_TYPE', get_option( 'alg_wc_currency_switcher_session_type', 'standard' ) );
	define( 'ALG_WC_CS_SESSION_TYPE', 'standard' );
}

if ( ! defined( 'ALG_WC_CS_EXCHANGE_RATES_PRECISION' ) ) {
	/**
	 * Session type.
	 *
	 * @version 2.8.0
	 * @since   2.8.0
	 */
	define( 'ALG_WC_CS_EXCHANGE_RATES_PRECISION', 12 );
}

if ( ! defined( 'ALG_WC_CS_EXCHANGE_RATES_STEP' ) ) {
	/**
	 * Session type.
	 *
	 * @version 2.8.0
	 * @since   2.8.0
	 */
	define( 'ALG_WC_CS_EXCHANGE_RATES_STEP', ( 1 / pow( 10, ALG_WC_CS_EXCHANGE_RATES_PRECISION ) ) );
}
