<?php
/**
 * Currency Switcher Functions - Exchange Rates
 *
 * @version 2.15.0
 * @since   2.8.0
 * @author  Tom Anbinder
 * @author  WP Wham
 *
 * @todo show an admin notice if Free Currency Coverter API key is invalid.
 * @todo show an admin notice if libxml is missing (impacts the XML-based APIs: ECB, BoE, TCMB)
 */

if ( ! function_exists( 'alg_wc_cs_get_exchange_rates_servers' ) ) {
	/*
	 * alg_wc_cs_get_exchange_rates_servers.
	 *
	 * @version 2.12.0
	 * @since   2.8.0
	 *
	 * @author  Algoritmika Ltd.
	 * @author  David Grant
	 */
	function alg_wc_cs_get_exchange_rates_servers() {
		return array(
			'ecb'             => __( 'European Central Bank (ECB) [recommended]', 'currency-switcher-woocommerce' ),
			'free_cur_api'    => __( 'The Free Currency Converter API', 'currency-switcher-woocommerce' ),
			'boe'             => __( 'Bank of England', 'currency-switcher-woocommerce' ),
			'georgia'         => __( 'National Bank of Georgia', 'currency-switcher-woocommerce' ),
			'tcmb'            => __( 'Türkiye Cumhuriyet Merkez Bankası (TCMB)', 'currency-switcher-woocommerce' ),
			'coinbase'        => __( 'Coinbase', 'currency-switcher-woocommerce' ),
			'coinmarketcap'   => __( 'CoinMarketCap (for Cryptocurrencies)', 'currency-switcher-woocommerce' ),
		);
	}
}

if ( ! function_exists( 'alg_wc_cs_get_exchange_rate_georgia' ) ) {
	/*
	 * alg_wc_cs_get_exchange_rate_georgia.
	 *
	 * @version 2.14.0
	 * @since   2.9.0
	 */
	function alg_wc_cs_get_exchange_rate_georgia( $currency_from, $currency_to ) {
		if ( ! class_exists( "SoapClient" ) ) {
			return 0;
		}
		$client = new SoapClient( 'https://services.nbg.gov.ge/Rates/Service.asmx?wsdl' );

		$currencies = "{$currency_from},{$currency_to}";
		$result     = $client->GetCurrentRates( array( 'Currencies' => $currencies ) );

		if ( is_array( $result->GetCurrentRatesResult->CurrencyRate ) ) {
			foreach ( $result->GetCurrentRatesResult->CurrencyRate as $currency ) {
				if ( $currency->Code === $currency_from ) {
					$rate_from = $currency->Rate / $currency->Quantity;
				}
				if ( $currency->Code === $currency_to ) {
					$rate_to = $currency->Rate / $currency->Quantity;
				}
			}
		} else {
			// if its not an array then NBG does not have the currency pair
			$rate_from = 0;
			$rate_to   = 0;
		}
		if ( ! empty( $rate_from ) && ! empty( $rate_to ) ) {
			$final_rate = round( $rate_from / $rate_to, ALG_WC_CS_EXCHANGE_RATES_PRECISION );
		} else {
			$final_rate = 0;
		}

		return $final_rate;
	}
}

if ( ! function_exists( 'alg_wc_cs_get_exchange_rate_free_currency_api' ) ) {
	/**
	 * Converts using the Free Currency Converter api
	 * @link https://free.currencyconverterapi.com/
	 *
	 * @version 2.12.0
	 * @since   2.9.1
	 *
	 * @author  Algoritmika Ltd.
	 * @author  WP Wham
	 */
	function alg_wc_cs_get_exchange_rate_free_currency_api( $currency_from, $currency_to ) {
		$pair    = $currency_from . '_' . $currency_to;
		$url     = 'https://free.currconv.com/api/v7/convert?q=' . $pair . '&compact=y';
		$api_key = get_option( 'wpw_cs_fcc_api_key' );
		if ( ! empty( $api_key ) ) {
			$url = add_query_arg( array(
				'apiKey' => $api_key
			), $url );
		}
		$response = alg_wc_cs_get_currency_exchange_rates_url_response( $url );
		if ( $response ) {
			return ( ! empty( $response->{$pair}->val ) ? $response->{$pair}->val : false );
		}
		return false;
	}
}

if ( ! function_exists( 'alg_wc_cs_get_exchange_rate_ecb' ) ) {
	/*
	 * alg_wc_cs_get_exchange_rate_ecb.
	 *
	 * @version 2.14.0
	 * @since   2.2.0
	 *
	 * @author  Algoritmika Ltd.
	 * @author  WP Wham
	 */
	function alg_wc_cs_get_exchange_rate_ecb( $currency_from, $currency_to ) {
		$final_rate = 0;
		$url = 'http://www.ecb.int/stats/eurofxref/eurofxref-daily.xml';
		if ( function_exists( 'simplexml_load_file' ) ) {
			$xml = simplexml_load_file( $url );
			if ( ! $xml ) {
				$xml = simplexml_load_string( alg_wc_cs_get_currency_exchange_rates_url_response( $url, array(), false ) );
			}
			if ( isset( $xml->Cube->Cube->Cube ) ) {
				if ( 'EUR' === $currency_from ) {
					$EUR_currency_from_rate = 1;
				}
				if ( 'EUR' === $currency_to ) {
					$EUR_currency_to_rate = 1;
				}
				foreach ( $xml->Cube->Cube->Cube as $currency_rate ) {
					$currency_rate = $currency_rate->attributes();
					if ( ! isset( $EUR_currency_from_rate ) && $currency_from == $currency_rate->currency ) {
						$EUR_currency_from_rate = (float) $currency_rate->rate;
					}
					if ( ! isset( $EUR_currency_to_rate ) && $currency_to == $currency_rate->currency ) {
						$EUR_currency_to_rate = (float) $currency_rate->rate;
					}
				}
				if ( isset( $EUR_currency_from_rate ) && isset( $EUR_currency_to_rate ) && 0 != $EUR_currency_from_rate ) {
					$final_rate = round( $EUR_currency_to_rate / $EUR_currency_from_rate, ALG_WC_CS_EXCHANGE_RATES_PRECISION );
				} else {
					$final_rate = 0;
				}
			}
		}
		return $final_rate;
	}
}

if ( ! function_exists( 'alg_wc_cs_get_exchange_rates_server_title' ) ) {
	/*
	 * alg_wc_cs_get_exchange_rates_server_title.
	 *
	 * @version 2.8.0
	 * @since   2.8.0
	 */
	function alg_wc_cs_get_exchange_rates_server_title( $server_id ) {
		$servers = alg_wc_cs_get_exchange_rates_servers();
		return ( isset( $servers[ $server_id ] ) ? $servers[ $server_id ] : $server_id );
	}
}

if ( ! function_exists( 'alg_wc_cs_get_exchange_rate' ) ) {
	/*
	 * alg_wc_cs_get_exchange_rate.
	 *
	 * @version 2.15.0
	 * @since   2.0.0
	 * @return  float rate on success, else 0
	 */
	function alg_wc_cs_get_exchange_rate( $currency_from, $currency_to, $server_override = false ) {
		
		$return = 0;
		
		if ( $server_override ) {
			$server = $server_override;
		} else {
			$server = get_option( 'alg_currency_switcher_exchange_rate_server_' . $currency_from . '_' . $currency_to, 'default' );
		}
		if ( $server === 'default' ) {
			$server = get_option( 'alg_currency_switcher_exchange_rate_server', 'ecb' );
		}
		
		switch ( $server ) {
			case 'coinmarketcap':
				$return = alg_wc_cs_get_exchange_rate_coinmarketcap( $currency_from, $currency_to );
				break;
			case 'coinbase':
				$return = alg_wc_cs_get_exchange_rate_coinbase( $currency_from, $currency_to );
				break;
			case 'tcmb':
				$return = wpw_cs_tcmb_get_exchange_rate( $currency_from, $currency_to );
				break;
			case 'georgia':
				$return = alg_wc_cs_get_exchange_rate_georgia( $currency_from, $currency_to );
				break;
			case 'boe':
				$return = wpw_cs_boe_get_exchange_rate( $currency_from, $currency_to );
				break;
			case 'free_cur_api':
				$return = alg_wc_cs_get_exchange_rate_free_currency_api( $currency_from, $currency_to );
				break;
			default: // 'ecb'
				$return = alg_wc_cs_get_exchange_rate_ecb( $currency_from, $currency_to );
		}
		$return = number_format( floatval( $return ), 8, '.', '' ); // make sure its in a format we can handle
		$return = apply_filters( 'alg_wc_cs_get_exchange_rate', $return, $server, $currency_from, $currency_to );
		if ( 'default' === get_option( 'alg_currency_switcher_exchange_rate_offset_type_' . $currency_from . '_' . $currency_to, 'default' ) ) {
			$offset = get_option( 'alg_currency_switcher_exchange_rate_offset', 0 );
		} else {
			$offset = get_option( 'alg_currency_switcher_exchange_rate_offset_' . $currency_from . '_' . $currency_to, 0 );
		}
		return ( 0 != $offset ? ( $offset / 100 * $return + $return ) : $return );
	}
}

if ( ! function_exists( 'alg_wc_cs_get_exchange_rate_coinmarketcap' ) ) {
	/*
	 * alg_wc_cs_get_exchange_rate_coinmarketcap.
	 *
	 * @version 2.14.0
	 * @since   2.8.0
	 */
	function alg_wc_cs_get_exchange_rate_coinmarketcap( $currency_from, $currency_to, $try_reverse = true ) {
		$return = 0;
		$api_key  = get_option( 'wpw_currency_switcher_coinmarketcap_api_key' );
		$response = alg_wc_cs_get_currency_exchange_rates_url_response(
			"https://pro-api.coinmarketcap.com/v1/cryptocurrency/quotes/latest?symbol=$currency_to&convert=$currency_from",
			array(
				'Accepts' => 'application/json',
				'X-CMC_PRO_API_KEY' => $api_key,
			)
		);
		if ( isset( $response->data->{$currency_to}->quote->{$currency_from}->price ) ) {
			$return = $response->data->{$currency_to}->quote->{$currency_from}->price;
			$return = round( ( 1 / $return ), ALG_WC_CS_EXCHANGE_RATES_PRECISION );
		}
		if ( $return === 0 && $try_reverse ) {
			$return = alg_wc_cs_get_exchange_rate_coinmarketcap( $currency_to, $currency_from, false );
		}
		return $return;
	}
}

if ( ! function_exists( 'alg_wc_cs_get_currency_exchange_rates_url_response' ) ) {
	/*
	 * alg_wc_cs_get_currency_exchange_rates_url_response.
	 *
	 * @version 2.14.0
	 * @since   2.8.0
	 */
	function alg_wc_cs_get_currency_exchange_rates_url_response( $url, $headers = array(), $do_json_decode = true ) {
		$response = apply_filters( 'wpw_cs_http_request', false, $url, $headers );
		if ( ! $response ) {
			$response = wp_remote_get(
				$url, 
				array(
					'sslverify' => false,
					'timeout'   => 10,
					'headers'   => $headers,
				)
			);
			if ( ! is_wp_error( $response ) ) {
				$response = $response['body'];
			} else {
				$response = false;
			}
		}
		if ( $response !== false && $do_json_decode ) {
			$response = json_decode( $response );
		}
		return $response;
	}
}

if ( ! function_exists( 'alg_wc_cs_get_exchange_rate_coinbase' ) ) {
	/*
	 * alg_wc_cs_get_exchange_rate_coinbase.
	 *
	 * @version 2.8.0
	 * @since   2.8.0
	 */
	function alg_wc_cs_get_exchange_rate_coinbase( $currency_from, $currency_to ) {
		$response = alg_wc_cs_get_currency_exchange_rates_url_response( "https://api.coinbase.com/v2/exchange-rates?currency=$currency_from" );
		return ( isset( $response->data->rates->{$currency_to} ) ? $response->data->rates->{$currency_to} : 0 );
	}
}

if ( ! function_exists( 'alg_wc_cs_update_the_exchange_rates' ) ) {
	/**
	 * alg_wc_cs_update_the_exchange_rates.
	 *
	 * @version 2.0.0
	 * @since   2.0.0
	 * @todo    add price filter widget and sorting by price support
	 */
	function alg_wc_cs_update_the_exchange_rates() {
		$currency_from = get_option( 'woocommerce_currency' );
		foreach ( alg_get_enabled_currencies() as $currency ) {
			if ( $currency_from != $currency ) {
				$the_rate = alg_wc_cs_get_exchange_rate( $currency_from, $currency );
				if ( 0 != $the_rate ) {
					update_option( 'alg_currency_switcher_exchange_rate_' . $currency_from . '_' . $currency, $the_rate );
				}
			}
		}
		/*
		if ( 'yes' === get_option( 'alg_price_by_country_price_filter_widget_support_enabled', 'no' ) ) {
			alg_update_products_price_by_country();
		}
		*/
	}
}

if ( ! function_exists( 'alg_wc_cs_get_currency_exchange_rate' ) ) {
	/**
	 * alg_wc_cs_get_currency_exchange_rate.
	 *
	 * @version 2.2.4
	 * @since   2.2.4
	 */
	function alg_wc_cs_get_currency_exchange_rate( $currency_code ) {
		$currency_from = get_option( 'woocommerce_currency' );
		return ( $currency_from == $currency_code ) ? 1 : get_option( 'alg_currency_switcher_exchange_rate_' . $currency_from . '_' . $currency_code, 1 );
	}
}

if ( ! function_exists( 'wpw_cs_boe_get_exchange_rate_gbp' ) ) {
	/*
	 * wpw_cs_boe_get_exchange_rate_gbp.
	 *
	 * @version 2.14.0
	 * @since   2.12.0
	 */
	function wpw_cs_boe_get_exchange_rate_gbp( $currency_to ) {
		if ( 'GBP' == $currency_to ) {
			return 1;
		}
		$final_rate = false;
		$currency_codes = array(
			'AUD' => 'EC3', // Australian Dollar
			'CAD' => 'ECL', // Canadian Dollar
			'CNY' => 'INB', // Chinese Yuan
			'CZK' => 'DS7', // Czech Koruna
			'DKK' => 'ECH', // Danish Krone
			'EUR' => 'C8J', // Euro
			'HKD' => 'ECN', // Hong Kong Dollar
			'HUF' => '5LA', // Hungarian Forint
			'INR' => 'INE', // Indian Rupee
			'ILS' => 'IN7', // Israeli Shekel
			'JPY' => 'C8N', // Japanese Yen
			'MYR' => 'IN8', // Malaysian ringgit
			'NZD' => 'ECO', // New Zealand Dollar
			'NOK' => 'EC6', // Norwegian Krone
			'PLN' => '5OW', // Polish Zloty
			'RUB' => 'IN9', // Russian Ruble
			'SAR' => 'ECZ', // Saudi Riyal
			'SGD' => 'ECQ', // Singapore Dollar
			'ZAR' => 'ECE', // South African Rand
			'KRW' => 'INC', // South Korean Won
			'SEK' => 'ECC', // Swedish Krona
			'CHF' => 'ECU', // Swiss Franc
			'TWD' => 'ECD', // Taiwan Dollar
			'THB' => 'INA', // Thai Baht
			'TRY' => 'IND', // Turkish Lira
			'USD' => 'C8P', // US Dollar
		);
		if ( isset( $currency_codes[ $currency_to ] ) && function_exists( 'simplexml_load_file' ) ) {
			for ( $i = 1; $i <= 7; $i++ ) {
				$date         = time() - $i*24*60*60;
				$date_from_d  = date( 'd', $date );
				$date_from_m  = date( 'M', $date );
				$date_from_y  = date( 'Y', $date );
				$date_to_d    = date( 'd', $date );
				$date_to_m    = date( 'M', $date );
				$date_to_y    = date( 'Y', $date );
				$date_url     = '&FD=' . $date_from_d . '&FM=' . $date_from_m . '&FY=' . $date_from_y . '&TD=' . $date_to_d . '&TM=' . $date_to_m . '&TY=' . $date_to_y;
				$url          = 'http://www.bankofengland.co.uk/boeapps/iadb/fromshowcolumns.asp?Travel=NIxRSxSUx&FromSeries=1&ToSeries=50&DAT=RNG' . $date_url .
					'&VFD=Y&xml.x=23&xml.y=18&CSVF=TT&C=' . $currency_codes[ $currency_to ] . '&Filter=N';
				$xml = simplexml_load_file( $url );
				if ( ! $xml ) {
					$xml = simplexml_load_string( alg_wc_cs_get_currency_exchange_rates_url_response( $url, array(), false ) );
				}
				$json_string  = json_encode( $xml );
				$result_array = json_decode( $json_string, true );
				if ( isset( $result_array['Cube']['Cube'] ) ) {
					$last_element_index = count( $result_array['Cube']['Cube'] ) - 1;
					if ( isset( $result_array['Cube']['Cube'][ $last_element_index ]['@attributes']['OBS_VALUE'] ) ) {
						return $result_array['Cube']['Cube'][ $last_element_index ]['@attributes']['OBS_VALUE'];
					}
				}
			}
		}
		return $final_rate;
	}
}

if ( ! function_exists( 'wpw_cs_boe_get_exchange_rate' ) ) {
	/*
	 * wpw_cs_boe_get_exchange_rate.
	 *
	 * @version 2.12.0
	 * @since   2.12.0
	 */
	function wpw_cs_boe_get_exchange_rate( $currency_from, $currency_to ) {
		if (
			false != ( $gbp_currency_from = wpw_cs_boe_get_exchange_rate_gbp( $currency_from ) ) &&
			false != ( $gbp_currency_to   = wpw_cs_boe_get_exchange_rate_gbp( $currency_to ) )
		) {
			return round( $gbp_currency_to / $gbp_currency_from, 6 );
		}
		return false;
	}
}

if ( ! function_exists( 'wpw_cs_tcmb_get_exchange_rate_TRY' ) ) {
	/*
	 * wpw_cs_tcmb_get_exchange_rate_TRY.
	 *
	 * @version 2.14.0
	 * @since   2.12.0
	 */
	function wpw_cs_tcmb_get_exchange_rate_TRY( $currency_from ) {
		if ( 'TRY' === $currency_from ) {
			return 1;
		}
		$url = 'http://www.tcmb.gov.tr/kurlar/today.xml';
		$xml = simplexml_load_file( $url );
		if ( ! $xml ) {
			$xml = simplexml_load_string( alg_wc_cs_get_currency_exchange_rates_url_response( $url, array(), false ) );
		}
		if ( isset( $xml->Currency ) ) {
			foreach ( $xml->Currency as $the_rate ) {
				$attributes = $the_rate->attributes();
				if ( isset( $attributes['CurrencyCode'] ) ) {
					$currency_code = (string) $attributes['CurrencyCode'];
					if ( $currency_code === $currency_from  ) {
						// Possible values: ForexSelling, ForexBuying, BanknoteSelling, BanknoteBuying. Not used: CrossRateUSD, CrossRateOther.
						if ( '' != ( $property_to_check = apply_filters( 'wpw_cs_tcmb_property_to_check', '' ) ) ) {
							if ( isset( $the_rate->{$property_to_check} ) ) {
								$rate = (float) $the_rate->{$property_to_check};
							} else {
								continue;
							}
						} else {
							if ( isset( $the_rate->ForexSelling ) ) {
								$rate = (float) $the_rate->ForexSelling;
							} elseif ( isset( $the_rate->ForexBuying ) ) {
								$rate = (float) $the_rate->ForexBuying;
							} elseif ( isset( $the_rate->BanknoteSelling ) ) {
								$rate = (float) $the_rate->BanknoteSelling;
							} elseif ( isset( $the_rate->BanknoteBuying ) ) {
								$rate = (float) $the_rate->BanknoteBuying;
							} else {
								continue;
							}
						}
						$unit = ( isset( $the_rate->Unit ) ) ? (float) $the_rate->Unit : 1;
						return ( $rate / $unit );
					}
				}
			}
		}
		return false;
	}
}

if ( ! function_exists( 'wpw_cs_tcmb_get_exchange_rate' ) ) {
	/*
	 * wpw_cs_tcmb_get_exchange_rate.
	 *
	 * @version 2.12.0
	 * @since   2.12.0
	 */
	function wpw_cs_tcmb_get_exchange_rate( $currency_from, $currency_to ) {
		$currency_from_TRY = wpw_cs_tcmb_get_exchange_rate_TRY( strtoupper( $currency_from ) );
		if ( false == $currency_from_TRY  ) {
			return false;
		}
		$currency_to_TRY = wpw_cs_tcmb_get_exchange_rate_TRY( strtoupper( $currency_to )  );
		if ( false == $currency_to_TRY ) {
			return false;
		}
		if ( 1 == $currency_to_TRY ) {
			return round( $currency_from_TRY, 6 );
		}
		return round( ( $currency_from_TRY / $currency_to_TRY ), 6 );
	}
}
