<?php
/**
 * Currency Switcher - Coupons Settings
 *
 * @version 2.8.0
 * @since   2.8.0
 * @author  Tom Anbinder
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_Currency_Switcher_Coupons_Settings' ) ) :

class Alg_WC_Currency_Switcher_Coupons_Settings {

	/**
	 * Constructor.
	 *
	 * @version 2.8.0
	 * @since   2.8.0
	 */
	function __construct() {
		add_action( 'add_meta_boxes',        array( $this, 'add_shop_coupon_base_currency_meta_box' ) );
		add_action( 'save_post_shop_coupon', array( $this, 'save_shop_coupon_base_currency_meta_box' ), PHP_INT_MAX, 2 );
	}

	/**
	 * save_shop_coupon_base_currency_meta_box.
	 *
	 * @version 2.8.0
	 * @since   2.8.0
	 */
	function save_shop_coupon_base_currency_meta_box( $post_id, $post ) {
		// Check that we are saving with current metabox displayed
		if ( ! isset( $_POST[ 'alg_wc_currency_switcher_coupon_base_currency_save_post' ] ) ) {
			return;
		}
		// Save options
		update_post_meta( $post_id, '_' . 'alg_wc_currency_switcher_coupon_base_currency', $_POST['alg_wc_currency_switcher_coupon_base_currency'] );
	}

	/**
	 * add_shop_coupon_base_currency_meta_box.
	 *
	 * @version 2.8.0
	 * @since   2.8.0
	 */
	function add_shop_coupon_base_currency_meta_box() {
		$post_id = get_the_ID();
		$_coupon = new WC_Coupon( $post_id );
		// Check if it's a "fixed ... discount" coupon
		if ( in_array( $_coupon->get_discount_type(), array( 'fixed_cart', 'fixed_product' ) ) ) {
			add_meta_box(
				'alg-wc-currency-switcher-coupon-base-currency',
				__( 'Coupon currency', 'currency-switcher-woocommerce' ),
				array( $this, 'create_shop_coupon_base_currency_meta_box' ),
				'shop_coupon',
				'side',
				'default'
			);
		}
	}

	/**
	 * create_shop_coupon_base_currency_meta_box.
	 *
	 * @version 2.8.0
	 * @since   2.8.0
	 */
	function create_shop_coupon_base_currency_meta_box() {
		$post_id = get_the_ID();
		$value   = get_post_meta( $post_id, '_' . 'alg_wc_currency_switcher_coupon_base_currency', true );
		$html = '';
		$html .= '<select style="width:100%;" name="alg_wc_currency_switcher_coupon_base_currency">';
		$html .= '<option value="default"' . selected( 'default', $value, false ) . '>' . __( 'Default (Shop Base)', 'currency-switcher-woocommerce' ) . '</option>';
		foreach ( alg_get_enabled_currencies( false ) as $currency ) {
			$html .= '<option value="' . $currency . '"' . selected( $currency, $value, false ) . '>' . $currency . '</option>';
		}
		$html .= '</select>';
		$html .= '<input type="hidden" name="alg_wc_currency_switcher_coupon_base_currency_save_post" value="1">';
		echo $html;
	}

}

endif;

return new Alg_WC_Currency_Switcher_Coupons_Settings();
