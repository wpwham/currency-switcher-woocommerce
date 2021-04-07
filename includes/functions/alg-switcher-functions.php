<?php
/**
 * Currency Switcher Functions
 *
 * @since   1.0.0
 * @todo    ~~change prefix from `agl_` to `alg_wc_cs_` for all functions~~
 * @todo    change prefix to 'wpw_cs_'
 */

if ( ! function_exists( 'alg_wc_cs_session_maybe_start' ) ) {
	/**
	 * alg_wc_cs_session_maybe_start.
	 *
	 * @version 2.8.1
	 * @since   2.7.0
	 */
	function alg_wc_cs_session_maybe_start() {
		global $wpw_cs_local_session_started;
		$wpw_cs_local_session_started = false;
		switch ( ALG_WC_CS_SESSION_TYPE ) {
			case 'wc':
				if ( function_exists( 'WC' ) && WC()->session && ! WC()->session->has_session() ) {
					WC()->session->set_customer_session_cookie( true );
				}
				break;
			default: // 'standard'
				if ( ! session_id() && ! headers_sent() ) {
					if ( '' != ( $session_save_path = get_option( 'alg_wc_currency_switcher_session_save_path', '' ) ) ) {
						session_save_path( $session_save_path );
					}
					session_start();
					$wpw_cs_local_session_started = true;
				}
				break;
		}
	}
}

if ( ! function_exists( 'wpw_cs_session_maybe_stop' ) ) {
	/**
	 * wpw_cs_session_maybe_stop.
	 *
	 * @version 2.12.3
	 * @since   2.12.3
	 */
	function wpw_cs_session_maybe_stop() {
		global $wpw_cs_local_session_started;
		if ( ALG_WC_CS_SESSION_TYPE === 'standard' && $wpw_cs_local_session_started ) {
			session_write_close();
		}
	}
}

if ( ! function_exists( 'alg_wc_cs_session_set' ) ) {
	/**
	 * alg_wc_cs_session_set.
	 *
	 * @version 2.8.1
	 * @since   2.7.0
	 */
	function alg_wc_cs_session_set( $key, $value ) {
		switch ( ALG_WC_CS_SESSION_TYPE ) {
			case 'wc':
				if ( function_exists( 'WC' ) && WC()->session ) {
					WC()->session->set( $key, $value );
				}
				break;
			default: // 'standard'
				$_SESSION[ $key ] = $value;
				break;
		}
	}
}

if ( ! function_exists( 'alg_wc_cs_session_get' ) ) {
	/**
	 * alg_wc_cs_session_get.
	 *
	 * @version 2.8.1
	 * @since   2.7.0
	 */
	function alg_wc_cs_session_get( $key, $default = null ) {
		switch ( ALG_WC_CS_SESSION_TYPE ) {
			case 'wc':
				return ( function_exists( 'WC' ) && WC()->session ? WC()->session->get( $key, $default ) : $default );
			default: // 'standard'
				return ( isset( $_SESSION[ $key ] ) ? $_SESSION[ $key ] : $default );
		}
	}
}

if ( ! function_exists( 'alg_wc_currency_switcher_current_currency_code' ) ) {
	/**
	 * alg_wc_currency_switcher_current_currency_code.
	 *
	 * @version 2.6.0
	 * @since   2.6.0
	 */
	function alg_wc_currency_switcher_current_currency_code() {
		return get_woocommerce_currency();
	}
}
add_shortcode( 'woocommerce_currency_switcher_current_currency_code', 'alg_wc_currency_switcher_current_currency_code' );

if ( ! function_exists( 'alg_wc_currency_switcher_current_currency_symbol' ) ) {
	/**
	 * alg_wc_currency_switcher_current_currency_symbol.
	 *
	 * @version 2.6.0
	 * @since   2.6.0
	 */
	function alg_wc_currency_switcher_current_currency_symbol() {
		return get_woocommerce_currency_symbol();
	}
}
add_shortcode( 'woocommerce_currency_switcher_current_currency_symbol', 'alg_wc_currency_switcher_current_currency_symbol' );

if ( ! function_exists( 'alg_maybe_update_option_value_type' ) ) {
	/**
	 * alg_maybe_update_option_value_type.
	 *
	 * @version 2.5.0
	 * @since   2.5.0
	 */
	function alg_maybe_update_option_value_type( $option_id, $as_text ) {
		$option_value = get_option( $option_id, '' );
		if ( $as_text && is_array( $option_value ) ) {
			update_option( $option_id, implode( ',', $option_value ) );
		} elseif ( ! $as_text && ! is_array( $option_value ) ) {
			update_option( $option_id, array_map( 'trim', explode( ',', $option_value ) ) );
		}
	}
}

if ( ! function_exists( 'alg_get_enabled_currencies' ) ) {
	/**
	 * alg_get_enabled_currencies.
	 *
	 * @version 2.0.0
	 * @since   1.0.0
	 */
	function alg_get_enabled_currencies( $with_default = true ) {
		$additional_currencies = array();
		$default_currency = get_option( 'woocommerce_currency' );
		if ( $with_default ) {
			$additional_currencies[] = $default_currency;
		}
		$total_number = min( get_option( 'alg_currency_switcher_total_number', 2 ), apply_filters( 'alg_wc_currency_switcher_plugin_option', 2 ) );
		for ( $i = 1; $i <= $total_number; $i++ ) {
			if ( 'yes' === get_option( 'alg_currency_switcher_currency_enabled_' . $i, 'yes' ) ) {
				$additional_currencies[] = get_option( 'alg_currency_switcher_currency_' . $i, $default_currency );
			}
		}
		return array_unique( $additional_currencies );
	}
}

if ( ! function_exists( 'alg_get_currency_by_country' ) ) {
	/**
	 * alg_get_currency_by_country.
	 *
	 * @version 2.8.6
	 * @since   2.8.6
	 */
	function alg_get_currency_by_country( $country ) {
		foreach ( alg_get_enabled_currencies( false ) as $currency_to ) {
			if ( '' != $currency_to ) {
				$countries = get_option( 'alg_currency_switcher_currency_countries_' . $currency_to, '' );
				if ( ! empty( $countries ) ) {
					if ( ! is_array( $countries ) ) {
						$countries = array_map( 'trim', explode( ',', $countries ) );
					}
					if ( in_array( $country, $countries ) ) {
						return $currency_to;
					}
				}
			}
		}
		return false;
	}
}

if ( ! function_exists( 'alg_get_customer_override_country' ) ) {
	/**
	 * alg_get_customer_override_country.
	 *
	 * @version 2.8.6
	 * @since   2.8.6
	 */
	function alg_get_customer_override_country() {
		if ( 'disabled' === ( $override = get_option( 'alg_wc_currency_switcher_currency_countries_override', 'disabled' ) ) ) {
			return false;
		}
		if (
			( 'all_site_billing' === $override || 'all_site_shipping' === $override || ( ( 'checkout_billing' === $override || 'checkout_shipping' === $override ) && is_checkout() ) ) &&
			isset( WC()->customer )
		) {
			return ( 'checkout_billing' === $override || 'all_site_billing' === $override ? WC()->customer->get_billing_country() : WC()->customer->get_shipping_country() );
		} else {
			return false;
		}
	}
}

if ( ! function_exists( 'alg_get_current_currency_code' ) ) {
	/**
	 * alg_get_current_currency_code.
	 *
	 * @version 2.8.6
	 * @since   2.0.0
	 */
	function alg_get_current_currency_code( $default_currency = '' ) {
		$is_currency_countries_enabled = ( 'yes' === get_option( 'alg_wc_currency_switcher_currency_countries_enabled', 'no' ) );
		if ( $is_currency_countries_enabled && ( $customer_country = alg_get_customer_override_country() ) ) {
			if ( $currency = alg_get_currency_by_country( $customer_country ) ) {
				return $currency;
			}
		} elseif ( null !== ( $session_value = alg_wc_cs_session_get( 'alg_currency' ) ) ) {
			return $session_value;
		} elseif ( $is_currency_countries_enabled ) {
			if ( null != ( $customer_country = alg_get_customer_country_by_ip() ) ) {
				if ( $currency = alg_get_currency_by_country( $customer_country ) ) {
					alg_wc_cs_session_set( 'alg_currency', $currency );
					return $currency;
				}
			}
		}
		if ( '' == $default_currency ) {
			$default_currency = get_option( 'woocommerce_currency' );
		}
		alg_wc_cs_session_set( 'alg_currency', $default_currency );
		return $default_currency;
	}
}

if ( ! function_exists( 'alg_get_customer_country_by_ip' ) ) {
	/**
	 * alg_get_customer_country_by_ip.
	 *
	 * @version 2.0.0
	 * @since   2.0.0
	 */
	function alg_get_customer_country_by_ip() {
		if ( class_exists( 'WC_Geolocation' ) ) {
			// Get the country by IP
			$location = WC_Geolocation::geolocate_ip();
			// Base fallback
			if ( empty( $location['country'] ) ) {
				$location = wc_format_country_state_string( apply_filters( 'woocommerce_customer_default_location', get_option( 'woocommerce_default_country' ) ) );
			}
			return ( isset( $location['country'] ) ) ? $location['country'] : null;
		} else {
			return null;
		}
	}
}

if ( ! function_exists( 'alg_get_product_price_by_currency_per_product' ) ) {
	/**
	 * alg_get_product_price_by_currency_per_product.
	 *
	 * @since   2.4.3
	 */
	function alg_get_product_price_by_currency_per_product( $price, $currency_code, $_product, $direct_call ) {

		if ( ! $_product ) {
			return false;
		}

		$enabled = get_option( 'alg_currency_switcher_per_product_enabled' , 'yes' );
		if ( $enabled !== 'yes' ) {
			return false;
		}

		if ( $currency_code === get_option( 'woocommerce_currency' ) ) {
			return false;
		}

		// if ( 'yes' !== get_post_meta( $_main_product_id, '_' . 'alg_currency_switcher_per_product_settings_enabled', true ) ) {
			// return false;
		// }

		$_product_id = ( ALG_IS_WC_VERSION_BELOW_3 ?
			( isset( $_product->variation_id ) ? $_product->variation_id : $_product->id ) :
			$_product->get_id()
		);

		$regular_price_per_product = get_post_meta( $_product_id, '_' . 'alg_currency_switcher_per_product_regular_price_' . $currency_code, true );
		if ( ! $regular_price_per_product > '' ) {
			return false;
		}

		$_current_filter = current_filter();

		if ( in_array( $_current_filter,
			array( 'woocommerce_get_price_including_tax', 'woocommerce_get_price_excluding_tax' )
		) ) {
			return alg_get_product_display_price( $_product );
		}

		// determine sale price status
		$is_onsale = false;
		$sale_price_per_product = get_post_meta( $_product_id, '_' . 'alg_currency_switcher_per_product_sale_price_' . $currency_code, true );
		if ( $sale_price_per_product > '' ) {
			$now = time();
			$sale_price_dates_from = get_post_meta( $_product_id, '_sale_price_dates_from', true );
			$sale_price_dates_to = get_post_meta( $_product_id, '_sale_price_dates_to', true );
			if (
				// has sale price, no scheduled from or to dates
				(
					! $sale_price_dates_from &&
					! $sale_price_dates_to
				) ||
				// has sale price, and now is within scheduled "from" and "to" dates
				(
					$sale_price_dates_from && 
					$sale_price_dates_to &&
					$now > $sale_price_dates_from &&
					$now < $sale_price_dates_to
				) ||
				// has sale price, now is after the scheduled "from" date, and no "to" date is set
				(
					$sale_price_dates_from &&
					! $sale_price_dates_to &&
					$now > $sale_price_dates_from
				) ||
				// has sale price, no "from" date is set, and now is before the scheduled "to" date
				(
					! $sale_price_dates_from &&
					$sale_price_dates_to &&
					$now < $sale_price_dates_to
				)
			) {
				$is_onsale = true;
			}
		}

		if ( $direct_call || in_array( $_current_filter,
			array( ALG_PRODUCT_GET_PRICE_FILTER, 'woocommerce_variation_prices_price', 'woocommerce_product_variation_get_price' )
		) ) {
			return $is_onsale ? $sale_price_per_product : $regular_price_per_product;
		}

		if ( in_array( $_current_filter,
			array( ALG_PRODUCT_GET_REGULAR_PRICE_FILTER, 'woocommerce_variation_prices_regular_price', 'woocommerce_product_variation_get_regular_price' )
		) ) {
			return $regular_price_per_product;
		}

		if ( in_array( $_current_filter,
			array( ALG_PRODUCT_GET_SALE_PRICE_FILTER, 'woocommerce_variation_prices_sale_price', 'woocommerce_product_variation_get_sale_price' )
		) ) {
			return $is_onsale ? $sale_price_per_product : $price;
		}
		
		return false;
	}
}



if ( ! function_exists( 'alg_wc_cs_round_and_pretty' ) ) {
	/**
	 * alg_wc_cs_round_and_pretty.
	 *
	 * @version 2.9.3
	 * @since   2.8.5
	 */
	function alg_wc_cs_round_and_pretty( $price, $currency_code ) {
		$shop_precision_original = absint( get_option( 'woocommerce_price_num_decimals', 2 ) );
		$rounding_options = apply_filters( 'alg_wc_currency_switcher_correction', array(
			'shop_precision'     => $shop_precision_original,
			'rounding_precision' => get_option( 'alg_currency_switcher_rounding_precision', $shop_precision_original ),
			'rounding'           => get_option( 'alg_currency_switcher_rounding', 'no_round' ),
			'pretty_price'       => get_option( 'alg_currency_switcher_make_pretty_price', 'no' )
		), $currency_code );

		// Rounding
		$shop_precision     = $rounding_options['shop_precision'];
		$rounding_precision = $rounding_options['rounding_precision'];
		switch ( $rounding_options['rounding'] ) {
			case 'round':
				$price = round( $price, $rounding_precision );
				break;
			case 'round_up':
				$price = ceil( $price );
				break;
			case 'round_down':
				$price = floor( $price );
				break;
		}
		// Pretty Price
		if ( 'yes' === $rounding_options['pretty_price'] && $price >= 0.5 ) {
			if ( 'yes' === get_option( 'alg_wc_currency_switcher_price_formats_enabled', 'no' ) ) {
				$shop_precision = get_option( 'alg_wc_currency_switcher_price_formats_number_of_decimals_' . $currency_code, $shop_precision );
			}
			if ( $shop_precision > 0 ) {
				$price = round( $price ) - ( 1 / pow( 10, $shop_precision ) );
			}
		}
		return $price;
	}
}

if ( ! function_exists( 'alg_get_product_price_by_currency_global' ) ) {
	/**
	 * alg_get_product_price_by_currency_global.
	 *
	 * @version 2.8.5
	 * @since   2.4.3
	 */
	function alg_get_product_price_by_currency_global( $price, $currency_code ) {
		return alg_wc_cs_round_and_pretty( $price * alg_wc_cs_get_currency_exchange_rate( $currency_code ), $currency_code );
	}
}

if ( ! function_exists( 'alg_get_product_price_by_currency' ) ) {
	/**
	 * alg_get_product_price_by_currency.
	 *
	 * @version 2.6.0
	 * @since   2.2.4
	 */
	function alg_get_product_price_by_currency( $price, $currency_code, $_product = null, $direct_call = false ) {

		$do_save_prices_in_array = ( 'save_in_array' === get_option( 'alg_wc_currency_switcher_price_conversion_method', 'simple' ) );

		if ( $do_save_prices_in_array && null != $_product ) {
			$_product_id     = ( ALG_IS_WC_VERSION_BELOW_3 ? ( isset( $_product->variation_id ) ? $_product->variation_id : $_product->id ) : $_product->get_id() );
			$_current_filter = ( $direct_call ? 'direct_call' : current_filter() );
			if ( isset( alg_wc_currency_switcher_plugin()->saved_product_prices[ $_product_id ][ $_current_filter ][ $currency_code ] ) ) {
				return alg_wc_currency_switcher_plugin()->saved_product_prices[ $_product_id ][ $_current_filter ][ $currency_code ];
			}
		}

		// Check if empty price
		if ( '' === $price ) {
			if ( $do_save_prices_in_array && null != $_product ) {
				alg_wc_currency_switcher_plugin()->saved_product_prices[ $_product_id ][ $_current_filter ][ $currency_code ] = $price;
			}
			return $price;
		}

		// Check if shop's default currency
		if ( $currency_code == get_option( 'woocommerce_currency' ) && 'no' === get_option( 'alg_currency_switcher_default_currency_enabled', 'no' ) ) {
			if ( $do_save_prices_in_array && null != $_product ) {
				alg_wc_currency_switcher_plugin()->saved_product_prices[ $_product_id ][ $_current_filter ][ $currency_code ] = $price;
			}
			return $price;
		}

		// Per product
		if ( false !== ( $price_per_product = alg_get_product_price_by_currency_per_product( $price, $currency_code, $_product, $direct_call ) ) ) {
			if ( $do_save_prices_in_array && null != $_product ) {
				alg_wc_currency_switcher_plugin()->saved_product_prices[ $_product_id ][ $_current_filter ][ $currency_code ] = $price_per_product;
			}
			return $price_per_product;
		}

		// Global
		$price_global = alg_get_product_price_by_currency_global( $price, $currency_code );
		if ( $do_save_prices_in_array && null != $_product ) {
			alg_wc_currency_switcher_plugin()->saved_product_prices[ $_product_id ][ $_current_filter ][ $currency_code ] = $price_global;
		}
		return $price_global;

	}
}

if ( ! function_exists( 'alg_currency_switcher_product_price_filters' ) ) {
	/**
	 * alg_currency_switcher_product_price_filters.
	 *
	 * @version 2.8.0
	 * @since   2.3.0
	 * @todo    (maybe) Grouped products - check for `$price_filters_to_remove`
	 */
	function alg_currency_switcher_product_price_filters( $_object, $action = 'add_filter' ) {
		// Additional Price Filters
		$additional_price_filters = get_option( 'alg_currency_switcher_additional_price_filters', '' );
		if ( ! empty( $additional_price_filters ) ) {
			$additional_price_filters = array_map( 'trim', explode( PHP_EOL, $additional_price_filters ) );
			foreach ( $additional_price_filters as $additional_price_filter ) {
				$action( $additional_price_filter, array( $_object, 'change_price_by_currency' ), PHP_INT_MAX, 2 );
			}
		}
		// Price Filters to Remove
		$price_filters_to_remove = get_option( 'alg_currency_switcher_price_filters_to_remove', '' );
		if ( ! empty( $price_filters_to_remove ) ) {
			$price_filters_to_remove = array_map( 'trim', explode( PHP_EOL, $price_filters_to_remove ) );
		} else {
			$price_filters_to_remove = array();
		}
		// Standard Price Filters
		$standard_price_filters = array(
			// Prices
			ALG_PRODUCT_GET_PRICE_FILTER,
			ALG_PRODUCT_GET_SALE_PRICE_FILTER,
			ALG_PRODUCT_GET_REGULAR_PRICE_FILTER,
			// Variations
			'woocommerce_variation_prices_price',
			'woocommerce_variation_prices_regular_price',
			'woocommerce_variation_prices_sale_price'
		);
		if ( ! ALG_IS_WC_VERSION_BELOW_3 ) {
			// Variations
			$standard_price_filters = array_merge( $standard_price_filters, array(
				'woocommerce_product_variation_get_price',
				'woocommerce_product_variation_get_regular_price',
				'woocommerce_product_variation_get_sale_price',
			) );
		}
		foreach ( $standard_price_filters as $standard_price_filter ) {
			if ( ! in_array( $standard_price_filter, $price_filters_to_remove ) ) {
				$action( $standard_price_filter, array( $_object, 'change_price_by_currency' ), PHP_INT_MAX, 2 );
			}
		}
		// Grouped products
		$action( 'woocommerce_get_price_including_tax', array( $_object, 'change_price_by_currency_grouped' ), PHP_INT_MAX, 3 );
		$action( 'woocommerce_get_price_excluding_tax', array( $_object, 'change_price_by_currency_grouped' ), PHP_INT_MAX, 3 );
	}
}

if ( ! function_exists( 'alg_get_product_display_price' ) ) {
	/**
	 * alg_get_product_display_price.
	 *
	 * @version 2.3.0
	 * @since   2.3.0
	 */
	function alg_get_product_display_price( $_product, $price = '', $qty = 1 ) {
		return ( ALG_IS_WC_VERSION_BELOW_3 ) ? $_product->get_display_price( $price, $qty ) : wc_get_price_to_display( $_product, array( 'price' => $price, 'qty' => $qty ) );
	}
}

if ( ! function_exists( 'alg_get_product_price_html_by_currency' ) ) {
	/**
	 * alg_get_product_price_html_by_currency.
	 *
	 * @version 2.3.0
	 * @since   2.2.4
	 */
	function alg_get_product_price_html_by_currency( $_product, $currency_code ) {
		$price_html = '';
		alg_currency_switcher_product_price_filters( alg_wc_currency_switcher_plugin()->core, 'remove_filter' );
		if ( $_product->is_type( 'variable' ) || $_product->is_type( 'grouped' ) ) {
			$child_prices = array();
			foreach ( $_product->get_children() as $child_id ) {
				$child = wc_get_product( $child_id );
				if ( '' !== $child->get_price() ) {
					$child_prices[] = alg_get_product_price_by_currency( $child->get_price(), $currency_code, $child, true );
				}
			}
			if ( ! empty( $child_prices ) ) {
				$price_min = min( $child_prices );
				$price_max = max( $child_prices );
				$price_min = alg_get_product_display_price( $_product, $price_min, 1 );
				$price_max = alg_get_product_display_price( $_product, $price_max, 1 );
				if ( $price_min == $price_max ) {
					$price_html = wc_price( $price_min, array( 'currency' => $currency_code ) );
				} else {
					$price_html = wc_price( $price_min, array( 'currency' => $currency_code ) ) . '-' . wc_price( $price_max, array( 'currency' => $currency_code ) );
				}
			}
		} else {
			$price = $_product->get_price();
			$price = alg_get_product_price_by_currency( $price, $currency_code, $_product, true );
			$price = alg_get_product_display_price( $_product, $price, 1 );
			$price_html = wc_price( $price, array( 'currency' => $currency_code ) );
		}
		alg_currency_switcher_product_price_filters( alg_wc_currency_switcher_plugin()->core, 'add_filter' );
		return $price_html;
	}
}

if ( ! function_exists( 'alg_currencies_product_price_table' ) ) {
	/**
	 * alg_currencies_product_price_table.
	 *
	 * @version 2.2.4
	 * @since   2.2.4
	 */
	function alg_currencies_product_price_table( $atts ) {
		if ( ! is_product() ) {
			return '';
		}
		$_product = wc_get_product();
		if ( ! $_product ) {
			return '';
		}
		$function_currencies = alg_get_enabled_currencies();
		$table_data = array();
		foreach ( $function_currencies as $currency_code ) {
			$table_data[] = array(
				$currency_code,
				alg_get_product_price_html_by_currency( $_product, $currency_code ),
			);
		}
		return alg_get_table_html( $table_data, array( 'table_heading_type' => 'vertical' ) );
	}
}
add_shortcode( 'woocommerce_currency_switcher_product_price_table', 'alg_currencies_product_price_table' );

if ( ! function_exists( 'alg_get_table_html' ) ) {
	/**
	 * alg_get_table_html.
	 *
	 * @version 2.2.4
	 * @since   2.2.4
	 */
	function alg_get_table_html( $data, $args = array() ) {
		$defaults = array(
			'table_class'        => '',
			'table_style'        => '',
			'row_styles'         => '',
			'table_heading_type' => 'horizontal',
			'columns_classes'    => array(),
			'columns_styles'     => array(),
		);
		$args = array_merge( $defaults, $args );
		extract( $args );
		$table_class = ( '' == $table_class ) ? '' : ' class="' . $table_class . '"';
		$table_style = ( '' == $table_style ) ? '' : ' style="' . $table_style . '"';
		$row_styles  = ( '' == $row_styles )  ? '' : ' style="' . $row_styles  . '"';
		$html = '';
		$html .= '<table' . $table_class . $table_style . '>';
		$html .= '<tbody>';
		foreach( $data as $row_number => $row ) {
			$html .= '<tr' . $row_styles . '>';
			foreach( $row as $column_number => $value ) {
				$th_or_td = ( ( 0 === $row_number && 'horizontal' === $table_heading_type ) || ( 0 === $column_number && 'vertical' === $table_heading_type ) ) ? 'th' : 'td';
				$column_class = ( ! empty( $columns_classes ) && isset( $columns_classes[ $column_number ] ) ) ? ' class="' . $columns_classes[ $column_number ] . '"' : '';
				$column_style = ( ! empty( $columns_styles ) && isset( $columns_styles[ $column_number ] ) ) ? ' style="' . $columns_styles[ $column_number ] . '"' : '';
				$html .= '<' . $th_or_td . $column_class . $column_style . '>';
				$html .= $value;
				$html .= '</' . $th_or_td . '>';
			}
			$html .= '</tr>';
		}
		$html .= '</tbody>';
		$html .= '</table>';
		return $html;
	}
}

if ( ! function_exists( 'alg_convert_price' ) ) {
	/**
	 * alg_convert_price.
	 *
	 * @version 2.12.2
	 * @since   2.4.1
	 */
	function alg_convert_price( $atts ) {
		if ( ! isset( $atts['price'] ) ) {
			return '';
		}
		if ( '%cart_total%' === $atts['price'] ) {
			if ( function_exists( 'WC' ) && isset( WC()->cart ) && 0 != ( $cart_total = WC()->cart->get_cart_contents_total() ) ) {
				$atts['price']         = $cart_total;
				$atts['currency_from'] = alg_get_current_currency_code();
			} else {
				return '';
			}
		}
		if ( isset( $atts['currency_from'] ) ) {
			$rate = alg_wc_cs_get_currency_exchange_rate( $atts['currency_from'] );
			if ( 0 != $rate ) {
				$rate = 1 / $rate;
			}
			$atts['price'] = floatval( $atts['price'] ) * $rate;
		}
		if ( ! isset( $atts['currency'] ) ) {
			$atts['currency'] = alg_get_current_currency_code();
		}
		$converted_price = alg_get_product_price_by_currency( $atts['price'], $atts['currency'] );
		if ( ! isset( $atts['format_price'] ) ) {
			$atts['format_price'] = 'yes';
		}
		return ( 'yes' === $atts['format_price'] || true === $atts['format_price'] ? wc_price( $converted_price, array( 'currency' => $atts['currency'] ) ) : $converted_price );
	}
}
add_shortcode( 'woocommerce_currency_switcher_convert_price', 'alg_convert_price' );

if ( ! function_exists( 'wpw_cs_remove_class_filter' ) ) {
	/**
	 * Remove filter added with a callback to a class without access.
	 *
	 * @see https://gist.github.com/tripflex/c6518efc1753cf2392559866b4bd1a53
	 *
	 * @version 2.14.0
	 * @since   2.14.0
	 *
	 * @param $tag
	 * @param string $class_name
	 * @param string $method_name
	 * @param int $priority
	 *
	 * @return bool
	 */
	function wpw_cs_remove_class_filter( $tag, $class_name = '', $method_name = '', $priority = 10 ) {
		global $wp_filter;
		$is_hook_removed = false;
		if ( ! empty( $wp_filter[ $tag ]->callbacks[ $priority ] ) ) {
			$methods     = wp_list_pluck( $wp_filter[ $tag ]->callbacks[ $priority ], 'function' );
			$found_hooks = ! empty( $methods ) ? wp_list_filter( $methods, array( 1 => $method_name ) ) : array();
			foreach ( $found_hooks as $hook_key => $hook ) {
				if ( ! empty( $hook[0] ) && is_object( $hook[0] ) && get_class( $hook[0] ) === $class_name ) {
					$wp_filter[ $tag ]->remove_filter( $tag, $hook, $priority );
					$is_hook_removed = true;
				}
			}
		}
		return $is_hook_removed;
	}
}

if ( ! function_exists( 'wpw_cs_remove_class_action' ) ) {
	/**
	 * Remove action added with a callback to a class without access.
	 *
	 * @see https://gist.github.com/tripflex/c6518efc1753cf2392559866b4bd1a53
	 *
	 * @version 2.14.0
	 * @since   2.14.0
	 *
	 * @param $tag
	 * @param string $class_name
	 * @param string $method_name
	 * @param int $priority
	 */
	function wpw_cs_remove_class_action( $tag, $class_name = '', $method_name = '', $priority = 10 ) {
		wpw_cs_remove_class_filter( $tag, $class_name, $method_name, $priority );
	}
}
