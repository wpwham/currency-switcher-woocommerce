<?php
/**
 * Currency Switcher Currency Reports
 *
 * The Currency Switcher Currency Reports class.
 *
 * @version 2.15.1
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 * @author  WP Wham.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Alg_Currency_Switcher_Currency_Reports' ) ) :

class Alg_Currency_Switcher_Currency_Reports {

	/**
	 * Constructor.
	 *
	 * @version 2.15.1
	 * @since   1.0.0
	 */
	public function __construct() {
		
		// reports
		if ( is_admin() ) {
			add_filter(
				'woocommerce_reports_get_order_report_query',
				array( $this, 'filter_reports'),
				PHP_INT_MAX,
				1
			);
			add_filter(
				'woocommerce_currency',
				array( $this, 'change_currency_code_reports'),
				PHP_INT_MAX,
				1
			);
		}
		if ( isset( $_GET['page'] ) && $_GET['page'] === 'wc-reports' ) {
			add_action(
				'admin_bar_menu',
				array( $this, 'add_reports_currency_to_admin_bar' ),
				PHP_INT_MAX
			);
		}
		
		// analytics
		$analytics_pages = array(
			'orders',
			'revenue',
			'products',
			'categories',
			'coupons',
			'taxes',
		);
		foreach ( $analytics_pages as $analytics_page ) {
			add_filter(
				"woocommerce_analytics_{$analytics_page}_query_args",
				array( $this, 'apply_currency_arg' ),
				PHP_INT_MAX,
				1
			);
			add_filter(
				"woocommerce_analytics_{$analytics_page}_stats_query_args",
				array( $this, 'apply_currency_arg' ),
				PHP_INT_MAX,
				1
			);
		}
		add_filter(
			'woocommerce_analytics_clauses_join',
			array( $this, 'filter_clauses_join' ),
			PHP_INT_MAX,
			2
		);
		add_filter(
			'woocommerce_analytics_clauses_where',
			array( $this, 'filter_clauses_where' ),
			PHP_INT_MAX,
			2
		);
		add_filter(
			'woocommerce_analytics_clauses_select',
			array( $this, 'filter_clauses_select' ),
			PHP_INT_MAX,
			2
		);
		if (
			isset( $_GET['page'] ) && $_GET['page'] === 'wc-admin' &&
			isset( $_GET['path'] ) && strpos( $_GET['path'], '/analytics/' ) === 0
		) {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_script' ) );
		}
	}
	
	/**
	 * apply_currency_arg.
	 *
	 * @version 2.13.0
	 * @since   2.13.0
	 */
	public function apply_currency_arg( $args ) {
		
		$args['currency'] = $this->get_current_currency();
		
		return $args;
	}
	
	/**
	 * add_reports_currency_to_admin_bar.
	 *
	 * @version  2.13.0
	 * @since    1.0.0
	 */
	public function add_reports_currency_to_admin_bar( $wp_admin_bar ) {
		
		$the_current_code = $this->get_current_currency();
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
			// $currency_symbols['merge'] = 'merge';
		
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
	
	/**
	 * change_currency_code_reports.
	 *
	 * @version  1.0.0
	 * @since    1.0.0
	 */
	public function change_currency_code_reports( $currency ) {
		if ( isset( $_GET['page'] ) && 'wc-reports' === $_GET['page'] ) {
			if ( isset( $_GET['currency'] ) ) {
				return ( 'merge' === $_GET['currency'] ) ? '' : $_GET['currency'];
			}
		}
		return $currency;
	}
	
	/**
	 * enqueue_script.
	 *
	 * @version 2.13.0
	 * @since   2.13.0
	 */
	public function enqueue_script() {
		
		$the_current_code = $this->get_current_currency();
		$currency_codes = array();
		$currency_codes[ $the_current_code ] = $the_current_code;
		foreach ( alg_get_enabled_currencies() as $currency ) {
			$currency_codes[ $currency ] = $currency;
		}
		$currencies = array();
		foreach ( $currency_codes as $currency_code ) {
			$currencies[] = array(
				'label' => $currency_code,
				'value' => $currency_code,
			);
		}
		
		wp_enqueue_script(
			'wpw-currency-switcher-analytics',
			plugin_dir_url( __FILE__ ) . 'js/analytics.min.js',
			array( 'jquery' ),
			alg_wc_currency_switcher_plugin()->version,
			true
		);
		wp_localize_script( 'wpw-currency-switcher-analytics', 'wpw_currency_switcher', array(
			'i18n'       => array(
				'Currency'         => __( 'Currency', 'woocommerce' ),
				'reports_currency' => __( 'Reports currency:', 'currency-switcher-woocommerce' ),
				'show_reports_in'  => __( 'Show reports only in', 'currency-switcher-woocommerce' ),
			),
			'currencies' => $currencies,
		) );
	}
	
	/**
	 * Add currency to the JOIN clause.
	 *
	 * @param string[] $clauses The array of clauses.
	 * @param string   $context Unused.
	 *
	 * @return array
	 * 
	 * @version 2.13.0
	 * @since   2.13.0
	 */
	public function filter_clauses_join( $clauses, $context ) {
		global $wpdb;
		
		$clauses[] = "JOIN {$wpdb->postmeta} currency_postmeta ON {$wpdb->prefix}wc_order_stats.order_id = currency_postmeta.post_id";
		
		return $clauses;
	}
	
	/**
	 * Add currency to the WHERE clause.
	 *
	 * @param string[] $clauses The array of clauses.
	 * @param string   $context Unused.
	 *
	 * @return array
	 * 
	 * @version 2.13.0
	 * @since   2.13.0
	 */
	public function filter_clauses_where( $clauses, $context ) {
		
		$currency = $this->get_current_currency();
		
		$clauses[] = "AND currency_postmeta.meta_key = '_order_currency' AND currency_postmeta.meta_value = '{$currency}'";
		
		return $clauses;
	}
	
	/**
	 * Add currency to the SELECT clause.
	 *
	 * @param string[] $clauses The array of clauses.
	 * @param string   $context Unused.
	 *
	 * @return array
	 * 
	 * @version 2.13.0
	 * @since   2.13.0
	 */
	public function filter_clauses_select( $clauses, $context ) {
		
		$clauses[] = ', currency_postmeta.meta_value AS currency';
		
		return $clauses;
	}
	
	/**
	 * filter_reports.
	 *
	 * @version 2.15.1
	 * @since   1.0.0
	 */
	public function filter_reports( $query ) {
		global $wpdb;
		
		if ( isset( $_GET['currency'] ) && 'merge' === $_GET['currency'] ) {
			return $query;
		}
		
		$report_currency = isset( $_GET['currency'] ) ? sanitize_text_field( $_GET['currency'] ) : get_option( 'woocommerce_currency' );
		
		if ( ! isset( $query['join'] ) ) {
			$query['join'] = '';
		}
		$query['join'] .= " INNER JOIN {$wpdb->postmeta} AS wpw_order_currency ON posts.ID = wpw_order_currency.post_id";
		
		if ( ! isset( $query['where'] ) ) {
			$query['where'] = '';
		}
		$query['where'] .= " AND ( wpw_order_currency.meta_key = '_order_currency' AND wpw_order_currency.meta_value = '$report_currency' )";
		
		return $query;
	}
	
	/**
	 * Return the current currency from $_GET or return store default.
	 *
	 * @return string
	 * 
	 * @version 2.13.0
	 * @since   2.13.0
	 */
	protected function get_current_currency() {
		
		$default_currency = get_option( 'woocommerce_currency' );
		
		if ( ! empty( $_GET['currency'] ) ) {
			$currency = sanitize_text_field( wp_unslash( $_GET['currency'] ) );
		} else {
			$currency = $default_currency;
		}
		
		return $currency;
	}
}

endif;

return new Alg_Currency_Switcher_Currency_Reports();
