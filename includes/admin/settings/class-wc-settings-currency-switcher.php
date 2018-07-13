<?php
/**
 * Currency Switcher - Settings
 *
 * @version 2.4.0
 * @since   1.0.0
 * @author  Tom Anbinder
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_Settings_Currency_Switcher' ) ) :

class Alg_WC_Settings_Currency_Switcher extends WC_Settings_Page {

	/**
	 * Constructor.
	 *
	 * @version 2.0.0
	 * @since   1.0.0
	 */
	function __construct() {
		$this->id    = 'alg_wc_currency_switcher';
		$this->label = __( 'Currency Switcher', 'currency-switcher-woocommerce' );
		parent::__construct();
		add_action( 'woocommerce_admin_field_alg_title', array( $this, 'output_alg_title' ) );
	}

	/**
	 * output_alg_title.
	 *
	 * @version 2.0.0
	 * @since   2.0.0
	 */
	function output_alg_title( $value ) {
		if ( ! empty( $value['title'] ) ) {
			echo '<h2>' . esc_html( $value['title'] ) . '</h2>';
		}
		if ( ! empty( $value['desc'] ) ) {
			echo wpautop( wptexturize( wp_kses_post( $value['desc'] ) ) );
		}
		if ( ! empty( $value['buttons'] ) ) {
			$buttons = array();
			$button_style = 'background: #00ba00; border-color: #00aa00; text-shadow: 0 -1px 1px #009900,1px 0 1px #009900,0 1px 1px #009900,-1px 0 1px #009900; box-shadow: 0 1px 0 #009900;';
			$button_template = '<a class="button-primary" style="%s" href="%s" onclick="return confirm(\'' . __( 'Are you sure?', 'currency-switcher-woocommerce' ) . '\')">%s</a>';
			foreach ( $value['buttons'] as $button ) {
				$buttons[] = sprintf( $button_template, $button_style, wp_nonce_url( $button['link'], $button['id'] ), $button['title'] );
			}
			echo wpautop( implode( ' ', $buttons ) );
		}
		echo '<table class="form-table">'. "\n\n";
		if ( ! empty( $value['id'] ) ) {
			do_action( 'woocommerce_settings_' . sanitize_title( $value['id'] ) );
		}
	}

	/**
	 * get_settings.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function get_settings() {
		global $current_section;
		return apply_filters( 'woocommerce_get_settings_' . $this->id . '_' . $current_section, array() );
	}

	/**
	 * maybe_reset_settings.
	 *
	 * @version 2.4.0
	 * @since   2.4.0
	 */
	function maybe_reset_settings() {
		global $current_section;
		$the_id = ( '' == $current_section ) ? 'general' : $current_section;
		if ( 'yes' === get_option( $this->id . '_' . $the_id . '_reset', 'no' ) ) {
			foreach ( $this->get_settings() as $value ) {
				if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
					delete_option( $value['id'] );
					$autoload = isset( $value['autoload'] ) ? (bool) $value['autoload'] : true;
					add_option( $value['id'], $value['default'], '', $autoload );
				}
			}
		}
	}

	/**
	 * Save settings.
	 *
	 * @version 2.4.0
	 * @since   2.4.0
	 */
	function save() {
		parent::save();
		$this->maybe_reset_settings();
	}

}

endif;

return new Alg_WC_Settings_Currency_Switcher();
