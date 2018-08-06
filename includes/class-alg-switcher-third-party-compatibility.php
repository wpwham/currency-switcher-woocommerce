<?php
/**
 * Currency Switcher Plugin - Third Party Compatibility
 *
 * Adds compatibility with other third party plugins, like Product Addons
 *
 * @version 2.8.8
 * @since   2.8.8
 * @author  Tom Anbinder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Alg_Switcher_Third_Party_Compatibility' ) ) :

	class Alg_Switcher_Third_Party_Compatibility {

		public $updated_session = false;

		/**
		 * Constructor
		 *
		 * @version 2.8.8
		 * @since   2.8.8
		 */
		function __construct() {

		}

		/**
		 * Initializes
		 *
		 * @version 2.8.8
		 * @since   2.8.8
		 */
		function init() {
			// Add compatibility with WooCommerce Product Addons plugin
			add_filter( 'ppom_option_price', array( $this, 'product_addons_convert_option_price' ), 10, 4 );
			add_filter( 'ppom_cart_line_total', array( $this, 'product_addons_convert_price_back' ) );
			add_filter( 'ppom_cart_fixed_fee', array( $this, 'product_addons_convert_price_back' ) );
			add_filter( 'ppom_add_cart_item_data', array( $this, 'ppom_woocommerce_add_cart_item_data' ), 10, 2 );
			add_filter( 'ppom_product_price', array( $this, 'ppom_product_price' ) );
			add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'ppom_get_cart_item_from_session' ), 1 );
		}

		/**
		 * Adds compatibility with WooCommerce Product Addons plugin, converting values back from plugin, if session was updated
		 *
		 * @version 2.8.8
		 * @since   2.8.8
		 * @link https://wordpress.org/plugins/woocommerce-product-addon/
		 */
		public function ppom_product_price( $price ) {
			if ( $this->updated_session ) {
				$price = $this->product_addons_convert_price_back( $price );
			}

			return $price;
		}

		/**
		 * Fixes product price on Product Addons plugin
		 *
		 * @version 2.8.8
		 * @since   2.8.8
		 * @link https://wordpress.org/plugins/woocommerce-product-addon/
		 */
		public function ppom_get_cart_item_from_session( $cart_item ) {
			if (
				! isset( $cart_item['ppom'] ) ||
				empty( $cart_item['ppom']['as_currency'] ) ||
				$cart_item['ppom']['as_currency'] == alg_get_current_currency_code()

			) {
				return $cart_item;
			}

			$option_prices    = json_decode( stripslashes( $cart_item['ppom']['ppom_option_price'] ), true );
			$additional_price = 0;
			$wc_product       = $cart_item['data'];
			foreach ( $option_prices as $key => $price ) {
				$additional_price = $option_prices[ $key ]['price'];
				$price            = alg_convert_price( array(
					'price'         => $option_prices[ $key ]['price'],
					'currency_from' => $cart_item['ppom']['as_currency'],
					'currency'      => alg_get_current_currency_code(),
					'format_price'  => 'no'
				) );

				$option_prices[ $key ]['price'] = $price;
			}
			$cart_item['ppom']['ppom_option_price'] = json_encode( $option_prices );
			$cart_item['ppom']['as_currency']       = alg_get_current_currency_code();
			$final_price                            = $cart_item['data']->get_price() - $additional_price + $price;
			$this->updated_session                  = true;
			$wc_product->set_price( $final_price );

			return $cart_item;
		}

		/**
		 * Adds currency meta to Product Addons plugin
		 *
		 * @version 2.8.8
		 * @since   2.8.8
		 * @link https://wordpress.org/plugins/woocommerce-product-addon/
		 */
		public function ppom_woocommerce_add_cart_item_data( $ppom, $post ) {
			if (
				'yes' !== apply_filters( 'alg_wc_currency_switcher_plugin_option', 'no', 'premium_version' ) ||
				! function_exists( 'PPOM' )
			) {
				return $ppom;
			}

			$ppom['as_currency'] = alg_get_current_currency_code();

			return $ppom;
		}

		/**
		 * Adds compatibility with WooCommerce Product Addons plugin, converting values back from plugin
		 *
		 * @version 2.8.8
		 * @since   2.8.8
		 * @link https://wordpress.org/plugins/woocommerce-product-addon/
		 */
		public function product_addons_convert_price_back( $price ) {
			if (
				'yes' !== apply_filters( 'alg_wc_currency_switcher_plugin_option', 'no', 'premium_version' ) ||
				! function_exists( 'PPOM' )
			) {
				return $price;
			}

			if ( alg_get_current_currency_code() != get_option( 'woocommerce_currency' ) ) {
				$current_currency_code = alg_get_current_currency_code();
				$default_currency      = get_option( 'woocommerce_currency' );
				$price                 = alg_convert_price( array(
					'price'         => $price,
					'currency_from' => $current_currency_code,
					'currency'      => $default_currency,
					'format_price'  => 'no'
				) );
			}

			return $price;
		}

		/**
		 * Adds compatibility with WooCommerce Product Addons plugin, converting values from plugin
		 *
		 * @version 2.8.8
		 * @since   2.8.8
		 * @link https://wordpress.org/plugins/woocommerce-product-addon/
		 */
		public function product_addons_convert_option_price( $price ) {
			if (
				'yes' !== apply_filters( 'alg_wc_currency_switcher_plugin_option', 'no', 'premium_version' ) ||
				! function_exists( 'PPOM' )
			) {
				return $price;
			}

			if ( alg_get_current_currency_code() != get_option( 'woocommerce_currency' ) ) {
				$price = alg_convert_price( array(
					'price'        => $price,
					'format_price' => 'no'
				) );
			}

			return $price;
		}

	}

endif;