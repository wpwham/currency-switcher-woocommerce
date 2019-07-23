<?php
/**
 * Currency Switcher Selector Functions
 *
 * @version 2.8.4
 * @since   2.0.0
 * @author  Tom Anbinder
 */

if ( ! function_exists( 'alg_format_currency_switcher' ) ) {
	/**
	 * alg_format_currency_switcher.
	 *
	 * @version 2.8.4
	 * @since   2.1.1
	 */
	function alg_format_currency_switcher( $currency_name, $currency_code, $check_is_product = true  ) {
		$product_price = '';
		if ( ! $check_is_product || ( $check_is_product && is_product() ) ) {
			$_product = wc_get_product();
			if ( $_product ) {
				$product_price = alg_get_product_price_html_by_currency( $_product, $currency_code );
			}
		}
		$replaced_values = array(
			'%currency_name%'   => $currency_name,
			'%currency_code%'   => $currency_code,
			'%currency_symbol%' => get_woocommerce_currency_symbol( $currency_code ),
			'%product_price%'   => $product_price,
		);
		return str_replace(
			array_keys( $replaced_values ),
			array_values( $replaced_values ),
			get_option( 'alg_currency_switcher_format', '%currency_name%' )
		);
	}
}

if ( ! function_exists( 'alg_get_currency_selector' ) ) {
	/**
	 * alg_get_currency_selector.
	 *
	 * @version 2.8.3
	 * @since   1.0.0
	 */
	function alg_get_currency_selector( $type = 'select' ) {
		$flags_enabled = ( 'yes' === apply_filters( 'alg_wc_currency_switcher_plugin_option', 'no', 'value_flags' ) );
		$html = '';
		$html .= '<form action="" method="post" id="alg_currency_selector">';
		if ( 'select' === $type ) {
			$html .= '<select name="alg_currency" id="alg_currency_select" class="alg_currency_select' . ( $flags_enabled ? ' alg-wselect' : '' ) . '" onchange="this.form.submit()">';
		}
		// Options
		$function_currencies = alg_get_enabled_currencies();
		$currencies          = get_woocommerce_currencies();
		$selected_currency   = alg_get_current_currency_code();
		foreach ( $function_currencies as $currency_code ) {
			if ( isset( $currencies[ $currency_code ] ) ) {
				if ( '' == $selected_currency ) {
					$selected_currency = $currency_code;
				}
				if ( 'select' === $type ) {
					$data_icon = '';
					if ( $flags_enabled ) {
						$country_code = alg_get_country_flag_code( $currency_code );
						$data_icon    = ' data-icon="' . alg_get_country_flag_image_url( $country_code ) . '"';
					}
					$html .= '<option' . $data_icon . ' id="alg_currency_' . $currency_code . '" value="' . $currency_code . '" ' . selected( $currency_code, $selected_currency, false ) . '>' .
						alg_format_currency_switcher( $currencies[ $currency_code ], $currency_code ) . '</option>';
				} elseif ( 'radio' === $type ) {
					$flag_img = '';
					if ( $flags_enabled ) {
						$country_code = alg_get_country_flag_code( $currency_code );
						$flag_img     = '<img style="display:inline;" src="' . alg_get_country_flag_image_url( $country_code ) . '"> ';
					}
					$html .= '<input type="radio" id="alg_currency_' . $currency_code . '" name="alg_currency" class="alg_currency_radio" value="' . $currency_code . '" ' .
						checked( $currency_code, $selected_currency, false ) . ' onclick="this.form.submit()"> ' .
						'<label for="alg_currency_' . $currency_code . '">' . $flag_img . alg_format_currency_switcher( $currencies[ $currency_code ], $currency_code ) . '</label>' . '<br>';
				}
			}
		}
		if ( 'select' === $type ) {
			$html .= '</select>';
		}
		$html .= '</form>';
		return str_replace( '%currency_switcher%', $html, get_option( 'alg_currency_switcher_wrapper', '%currency_switcher%' ) );
	}
}

if ( ! function_exists( 'alg_currency_select_drop_down_list' ) ) {
	/**
	 * alg_currency_select_drop_down_list.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function alg_currency_select_drop_down_list() {
		return alg_get_currency_selector( 'select' );
	}
}

if ( ! function_exists( 'alg_currency_select_radio_list' ) ) {
	/**
	 * alg_currency_select_radio_list.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function alg_currency_select_radio_list() {
		return alg_get_currency_selector( 'radio' );
	}
}

if ( ! function_exists( 'alg_currency_select_link_list' ) ) {
	/**
	 * alg_currency_select_link_list.
	 *
	 * @version 2.8.4
	 * @since   1.0.0
	 */
	function alg_currency_select_link_list( $atts = array() ) {
		$flags_enabled       = ( 'yes' === apply_filters( 'alg_wc_currency_switcher_plugin_option', 'no', 'value_flags' ) );
		$function_currencies = alg_get_enabled_currencies();
		$currencies          = get_woocommerce_currencies();
		$selected_currency   = alg_get_current_currency_code();
		$html                = '';
		$links               = array();
		$first_link          = '';
		foreach ( $function_currencies as $currency_code ) {
			if ( isset( $currencies[ $currency_code ] ) ) {
				$flag_img = '';
				if ( $flags_enabled ) {
					$country_code = alg_get_country_flag_code( $currency_code );
					$flag_img     = '<img style="display:inline;" src="' . alg_get_country_flag_image_url( $country_code ) . '"> ';
				}
				$the_text = $flag_img . alg_format_currency_switcher( $currencies[ $currency_code ], $currency_code, false );
				$the_link = ( isset( $atts['no_links'] ) && 'yes' === $atts['no_links'] ?
					$the_text : '<a id="alg_currency_' . $currency_code . '" href="' . add_query_arg( 'alg_currency', $currency_code ) . '">' . $the_text . '</a>' );
				if ( $currency_code != $selected_currency ) {
					$links[] = $the_link;
				} else {
					$first_link = $the_link;
				}
			}
		}
		if ( '' != $first_link ) {
			$links = array_merge( array( $first_link ), $links );
		}
		$html .= implode( get_option( 'alg_wc_currency_switcher_link_list_separator', '<br>' ), $links );
		$html = '<div id="alg_currency_selector">' . $html . '</div>';
		return str_replace( '%currency_switcher%', $html, get_option( 'alg_currency_switcher_wrapper', '%currency_switcher%' ) );
	}
}

if ( ! function_exists( 'alg_currency_select' ) ) {
	/**
	 * alg_currency_select.
	 *
	 * @version 2.8.4
	 * @since   1.0.0
	 */
	function alg_currency_select( $atts ) {
		if ( ! isset( $atts['type'] ) ) {
			$atts['type'] = 'drop_down';
		}
		switch ( $atts['type'] ) {
			case 'radio':
				return alg_currency_select_radio_list();
			case 'links':
				return alg_currency_select_link_list( $atts );
			default: // 'drop_down'
				return alg_currency_select_drop_down_list();
		}
	}
}

// Shortcodes
add_shortcode( 'woocommerce_currency_switcher',               'alg_currency_select' );
add_shortcode( 'woocommerce_currency_switcher_drop_down_box', 'alg_currency_select_drop_down_list' );
add_shortcode( 'woocommerce_currency_switcher_radio_list',    'alg_currency_select_radio_list' );
add_shortcode( 'woocommerce_currency_switcher_link_list',     'alg_currency_select_link_list' );
