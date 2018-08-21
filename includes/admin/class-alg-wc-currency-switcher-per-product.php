<?php
/**
 * Currency Switcher - Per Product Settings
 *
 * @version 2.3.0
 * @since   1.0.0
 * @author  Tom Anbinder
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_Currency_Switcher_Per_Product_Settings' ) ) :

class Alg_WC_Currency_Switcher_Per_Product_Settings {

	/**
	 * Constructor.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function __construct() {
		$this->id = 'alg_currency_switcher';
		add_action( 'add_meta_boxes',    array( $this, 'add_meta_box' ) );
		add_action( 'save_post_product', array( $this, 'save_meta_box' ), PHP_INT_MAX, 2 );
	}

	/**
	 * get_meta_box_options.
	 *
	 * @version 2.3.0
	 * @since   1.0.0
	 */
	function get_meta_box_options() {
		$main_product_id = get_the_ID();
		$_product = wc_get_product( $main_product_id );
		$products = array();
		if ( $_product->is_type( 'variable' ) ) {
			$available_variations = $_product->get_available_variations();
			foreach ( $available_variations as $variation ) {
				$variation_product = wc_get_product( $variation['variation_id'] );
				$products[ $variation['variation_id'] ] = ( ALG_IS_WC_VERSION_BELOW_3 ?
					$variation_product->get_formatted_variation_attributes( true ) :
					wc_get_formatted_variation( $variation_product, true, true )
				);
			}
		} else {
			$products[ $main_product_id ] = '';
		}
		$currencies = array();
		$currency_from = get_option( 'woocommerce_currency' );
		foreach ( $products as $product_id => $desc ) {
			foreach ( alg_get_enabled_currencies( false ) as $currency ) {
				if ( $currency != $currency_from ) {
					$currencies = array_merge( $currencies, array(
						array(
							'name'       => 'alg_currency_switcher_per_product_regular_price_' . $currency . '_' . $product_id,
							'default'    => '',
							'type'       => 'price',
							'title'      => '[' . $currency . '] ' . __( 'Regular Price', 'currency-switcher-woocommerce' ),
							'desc'       => $desc,
							'product_id' => $product_id,
							'meta_name'  => '_' . 'alg_currency_switcher_per_product_regular_price_' . $currency,
						),
						array(
							'name'       => 'alg_currency_switcher_per_product_sale_price_' . $currency . '_' . $product_id,
							'default'    => '',
							'type'       => 'price',
							'title'      => '[' . $currency . '] ' . __( 'Sale Price', 'currency-switcher-woocommerce' ),
							'desc'       => $desc,
							'product_id' => $product_id,
							'meta_name'  => '_' . 'alg_currency_switcher_per_product_sale_price_' . $currency,
						),
					) );
				}
			}
		}
		return $currencies;
	}

	/**
	 * save_meta_box.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function save_meta_box( $post_id, $post ) {
		// Check that we are saving with current metabox displayed.
		if ( ! isset( $_POST[ 'alg_currency_switcher_' . $this->id . '_save_post' ] ) ) return;
		// Save options
		foreach ( $this->get_meta_box_options() as $option ) {
			if ( 'title' === $option['type'] ) {
				continue;
			}
			$is_enabled = ( isset( $option['enabled'] ) && 'no' === $option['enabled'] ) ? false : true;
			if ( $is_enabled ) {
				$option_value  = ( isset( $_POST[ $option['name'] ] ) ) ? $_POST[ $option['name'] ] : $option['default'];
				$the_post_id   = ( isset( $option['product_id'] )     ) ? $option['product_id']     : $post_id;
				$the_meta_name = ( isset( $option['meta_name'] ) )      ? $option['meta_name']      : '_' . $option['name'];
				update_post_meta( $the_post_id, $the_meta_name, $option_value );
			}
		}
	}

	/**
	 * add_meta_box.
	 *
	 * @version 2.3.0
	 * @since   1.0.0
	 */
	function add_meta_box() {
		$screen   = ( isset( $this->meta_box_screen ) )   ? $this->meta_box_screen   : 'product';
		$context  = ( isset( $this->meta_box_context ) )  ? $this->meta_box_context  : 'normal';
		$priority = ( isset( $this->meta_box_priority ) ) ? $this->meta_box_priority : 'high';
		add_meta_box(
			'alg_currency_switcher_' . $this->id,
			__( 'Currency Switcher', 'currency-switcher-woocommerce' ),
			array( $this, 'create_meta_box' ),
			$screen,
			$context,
			$priority
		);
	}

	/**
	 * create_meta_box.
	 *
	 * @version 2.3.0
	 * @since   1.0.0
	 */
	function create_meta_box() {
		$current_post_id = get_the_ID();
		$html = '';
		$html .= '<table class="widefat striped">';
		foreach ( $this->get_meta_box_options() as $option ) {
			$is_enabled = ( isset( $option['enabled'] ) && 'no' === $option['enabled'] ) ? false : true;
			if ( $is_enabled ) {
				if ( 'title' === $option['type'] ) {
					$html .= '<tr>';
					$html .= '<th cospan="2" style="text-align:left;">' . $option['title'] . '</th>';
					$html .= '</tr>';
				} else {
					$custom_attributes = '';
					$the_post_id   = ( isset( $option['product_id'] ) ) ? $option['product_id'] : $current_post_id;
					$the_meta_name = ( isset( $option['meta_name'] ) )  ? $option['meta_name']  : '_' . $option['name'];
					if ( get_post_meta( $the_post_id, $the_meta_name ) ) {
						$option_value = get_post_meta( $the_post_id, $the_meta_name, true );
					} else {
						$option_value = ( isset( $option['default'] ) ) ? $option['default'] : '';
					}
					$input_ending = '';
					if ( 'select' === $option['type'] ) {
						if ( isset( $option['multiple'] ) ) {
							$custom_attributes = ' multiple';
							$option_name       = $option['name'] . '[]';
						} else {
							$option_name       = $option['name'];
						}
						$options = '';
						foreach ( $option['options'] as $select_option_key => $select_option_value ) {
							$selected = '';
							if ( is_array( $option_value ) ) {
								foreach ( $option_value as $single_option_value ) {
									$selected .= selected( $single_option_value, $select_option_key, false );
								}
							} else {
								$selected = selected( $option_value, $select_option_key, false );
							}
							$options .= '<option value="' . $select_option_key . '" ' . $selected . '>' . $select_option_value . '</option>';
						}
					} else {
						$input_ending = ' id="' . $option['name'] . '" name="' . $option['name'] . '" value="' . $option_value . '">';
					}
					switch ( $option['type'] ) {
						case 'price':
							$field_html = '<input class="short wc_input_price" type="number" step="0.0001"' . $input_ending;
							break;
						case 'date':
							$field_html = '<input class="input-text" display="date" type="text"' . $input_ending;
							break;
						case 'textarea':
							$field_html = '<textarea style="min-width:300px;"' . ' id="' . $option['name'] . '" name="' . $option['name'] . '">' . $option_value . '</textarea>';
							break;
						case 'select':
							$field_html = '<select' . $custom_attributes . ' id="' . $option['name'] . '" name="' . $option_name . '">' . $options . '</select>';
							break;
						default:
							$field_html = '<input class="short" type="' . $option['type'] . '"' . $input_ending;
							break;
					}
					$html .= '<tr>';
					$html .= '<th style="text-align:left;">' . $option['title'] . '</th>';
					if ( isset( $option['desc'] ) && '' != $option['desc'] ) {
						$html .= '<td style="font-style:italic;">' . $option['desc'] . '</td>';
					}
					$html .= '<td>' . $field_html . '</td>';
					$html .= '</tr>';
				}
			}
		}
		$html .= '</table>';
		$html .= '<input type="hidden" name="alg_currency_switcher_' . $this->id . '_save_post" value="alg_currency_switcher_' . $this->id . '_save_post">';
		echo $html;
	}

}

endif;

return new Alg_WC_Currency_Switcher_Per_Product_Settings();
