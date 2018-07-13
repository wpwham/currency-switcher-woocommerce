<?php
/**
 * Currency Switcher Currency Reports
 *
 * The Currency Switcher Currency Reports class.
 *
 * @version  2.0.0
 * @since    1.0.0
 * @author   Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Alg_Currency_Switcher_Currency_Reports' ) ) :

class Alg_Currency_Switcher_Currency_Reports {

	/**
	 * Constructor.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @todo    reports with currency conversions
	 */
	function __construct() {
		add_filter( 'woocommerce_reports_get_order_report_data_args', array( $this, 'filter_reports'),                     PHP_INT_MAX, 1 );
		add_filter( 'woocommerce_currency',                           array( $this, 'change_currency_code_reports'),       PHP_INT_MAX, 1 );
		add_action( 'admin_bar_menu',                                 array( $this, 'add_reports_currency_to_admin_bar' ), PHP_INT_MAX );
	}

	/**
	 * add_reports_currency_to_admin_bar.
	 *
	 * @version  2.0.0
	 * @since    1.0.0
	 */
	function add_reports_currency_to_admin_bar( $wp_admin_bar ) {

		if ( isset( $_GET['page'] ) && 'wc-reports' === $_GET['page'] ) {

			$default_currency = get_option( 'woocommerce_currency' );
			$the_current_code = isset( $_GET['currency'] ) ? $_GET['currency'] : $default_currency;
			$parent = 'reports_currency_select';
			$args = array(
				'parent' => false,
				'id'     => $parent,
				'title'  => __( 'Reports currency:', 'currency-switcher-woocommerce' ) . ' ' . $the_current_code,
				'href'   => false,
				'meta'   => array( 'title' => __( 'Show reports only in', 'currency-switcher-woocommerce' ) . ' ' . $the_current_code, ),
			);
			$wp_admin_bar->add_node( $args );

			$currency_symbols = array();
			$currency_symbols[ $the_current_code ] = $the_current_code;
			foreach ( alg_get_enabled_currencies() as $currency ) {
				$currency_symbols[ $currency ] = $currency;
			}
			sort( $currency_symbols );
//			$currency_symbols['merge'] = 'merge';

			foreach ( $currency_symbols as $code ) {
				$args = array(
					'parent' => $parent,
					'id'     => $parent . '_' . $code,
					'title'  => $code,
					'href'   => add_query_arg( 'currency', $code ),
					'meta'   => array( 'title' => __( 'Show reports only in', 'currency-switcher-woocommerce' ) . ' ' . $code, ),
				);
				$wp_admin_bar->add_node( $args );
			}
		}
	}

	/**
	 * change_currency_code_reports.
	 *
	 * @version  1.0.0
	 * @since    1.0.0
	 */
	function change_currency_code_reports( $currency ) {
		if ( isset( $_GET['page'] ) && 'wc-reports' === $_GET['page'] ) {
			if ( isset( $_GET['currency'] ) ) {
				return ( 'merge' === $_GET['currency'] ) ? '' : $_GET['currency'];
			}
		}
		return $currency;
	}

	/**
	 * filter_reports.
	 *
	 * @version  2.0.0
	 * @since    1.0.0
	 */
	function filter_reports( $args ) {
		if ( isset( $_GET['currency'] ) && 'merge' === $_GET['currency'] ) {
			return $args;
		}
		$args['where_meta'] = array(
			array(
				'meta_key'   => '_order_currency',
				'meta_value' => isset( $_GET['currency'] ) ? $_GET['currency'] : get_option( 'woocommerce_currency' ),
				'operator'   => '=',
			),
		);
		return $args;
	}
}

endif;

return new Alg_Currency_Switcher_Currency_Reports();
