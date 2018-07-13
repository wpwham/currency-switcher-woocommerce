<?php
/**
 * Currency Switcher - Section Settings
 *
 * @version 2.8.7
 * @since   1.0.0
 * @author  Tom Anbinder
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_Currency_Switcher_Settings_Section' ) ) :

class Alg_WC_Currency_Switcher_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function __construct() {
		add_filter( 'woocommerce_get_sections_alg_wc_currency_switcher',              array( $this, 'settings_section' ) );
		add_filter( 'woocommerce_get_settings_alg_wc_currency_switcher_' . $this->id, array( $this, 'get_settings' ), PHP_INT_MAX );
		add_action( 'init',                                                           array( $this, 'add_settings_hooks' ) );
	}

	/**
	 * get_settings.
	 *
	 * @version 2.8.7
	 * @since   1.0.0
	 */
	function get_settings() {
		$the_id = ( '' == $this->id ) ? 'general' : $this->id;
		return array_merge( apply_filters( 'alg_currency_switcher_' . $the_id . '_settings', array() ), array(
			array(
				'title'     => __( 'Reset Settings', 'currency-switcher-woocommerce' ),
				'type'      => 'title',
				'id'        => 'alg_wc_currency_switcher' . '_' . $the_id . '_reset_options',
			),
			array(
				'title'     => __( 'Reset section settings', 'currency-switcher-woocommerce' ),
				'desc'      => '<strong>' . __( 'Reset', 'currency-switcher-woocommerce' ) . '</strong>',
				'id'        => 'alg_wc_currency_switcher' . '_' . $the_id . '_reset',
				'default'   => 'no',
				'type'      => 'checkbox',
			),
			array(
				'type'      => 'sectionend',
				'id'        => 'alg_wc_currency_switcher' . '_' . $the_id . '_reset_options',
			),
		) );
	}

	/**
	 * add_settings_hooks.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function add_settings_hooks() {
		$the_id = ( '' == $this->id ) ? 'general' : $this->id;
		add_filter( 'alg_currency_switcher_' . $the_id . '_settings', array( $this, 'get_' . $the_id . '_settings' ) );
	}

	/**
	 * settings_section.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function settings_section( $sections ) {
		$sections[ $this->id ] = $this->desc;
		return $sections;
	}

}

endif;
