<?php
/**
 * Currency Switcher Plugin - Third Party Compatibility
 *
 * Adds compatibility with other third party plugins, like Product Addons
 *
 * @version 2.15.0
 * @since   2.8.8
 * @author  Tom Anbinder
 * @author  WP Wham
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
		 * @version 2.15.0
		 * @since   2.8.9
		 */
		function init() {

			// Add compatibility with WooCommerce Price Filter widget
			if ( get_option( 'wpw_currency_switcher_price_filter_widget_enabled', 'yes' ) === 'yes' ) {
				add_action( 'wp_footer', array( $this, 'add_compatibility_with_price_filter_widget' ) );
				add_action( 'wp_footer', array( $this, 'fix_price_filter_widget_currency_format' ) );
				add_action( 'init', array( $this, 'fix_price_filter_widget_query_args' ) );
				add_filter( 'posts_clauses', array( $this, 'posts_clauses_price_filter_compatible' ), 11, 2 );
				// @todo just an idea for the future, maybe a better way to address per-product price overrides:
				// add_action( 'woocommerce_product_query', array( $this, 'modify_default_price_filter_hook' ), PHP_INT_MAX );
			}
			
			// Add compatibility for WooCommerce Product Add-ons plugin
			// https://woocommerce.com/products/product-add-ons/
			if ( apply_filters( 'wpwham_currency_switcher_compatibility_product_addons', true ) ) {
				add_filter( 'get_product_addons', array( $this, 'product_addons_convert_addon_prices' ) );
				add_filter( 'woocommerce_get_item_data', array( $this, 'product_addons_fix_addon_prices_for_display' ), 11, 2 );
			}
		
			// Add compatibility with PPOM for WooCommerce plugin
			// https://wordpress.org/plugins/woocommerce-product-addon/
			if ( apply_filters( 'wpwham_currency_switcher_compatibility_ppom', false ) ) {
				add_filter( 'ppom_option_price', array( $this, 'product_addons_convert_option_price' ), 10, 4 );
				add_filter( 'ppom_cart_line_total', array( $this, 'product_addons_convert_price_back' ) );
				add_filter( 'ppom_cart_fixed_fee', array( $this, 'product_addons_convert_price_back' ) );
				add_filter( 'ppom_add_cart_item_data', array( $this, 'ppom_woocommerce_add_cart_item_data' ), 10, 2 );
				add_filter( 'ppom_product_price', array( $this, 'ppom_product_price' ) );
				add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'ppom_get_cart_item_from_session' ), 1 );
			}
			
		}
		
		/**
		 * Adds compatibility with WooCommerce Price Filter widget.
		 *
		 * Outputs JS scripts necessary to update the price slider.
		 *
		 * @version 2.15.0
		 * @since   2.8.9
		 */
		public function add_compatibility_with_price_filter_widget() {
			#region add_compatibility_with_price_filter_widget
			
			if ( ! apply_filters( 'wpwham_currency_switcher_output_price_filter_js', true ) ) {
				return;
			}
			
			if ( ! is_active_widget( false, false, 'woocommerce_price_filter' ) ) {
				return;
			}
			
			$exchange_rate = alg_wc_cs_get_currency_exchange_rate( alg_get_current_currency_code() );
			
			$convert_current_min_max = true;
			if ( isset( $_REQUEST['min_price'] ) && isset( $_REQUEST['max_price'] ) && isset( $_GET['currency_code'] ) ) {
				// if we're here a price filter has already been applied,
				// so set this to avoid a double conversion.
				$convert_current_min_max = false;
			}
			
			?>
			<input type="hidden" id="alg_wc_cs_exchange_rate" value="<?php echo esc_html( $exchange_rate ) ?>"/>
			<script type="text/javascript">
				
				<?php if ( version_compare( get_option( 'woocommerce_version' ), '4.9.0', '>=' ) ): ?>
				
				(function( $ ){
					
					var exchangeRate = $( '#alg_wc_cs_exchange_rate' ).val();
					
					var originalMin  = $( '.price_slider_amount #min_price' ).data( 'min' ) || 0; // this is always in shop's default currency
					var originalMax  = $( '.price_slider_amount #max_price' ).data( 'max' ) || 0; // this is always in shop's default currency
					var originalStep = $( '.price_slider_amount' ).data( 'step' );
					var originalCurrentMin  = $( '.price_slider_amount #min_price' ).val(); // this might be already converted, check $convert_current_min_max
					var originalCurrentMax  = $( '.price_slider_amount #max_price' ).val(); // this might be already converted, check $convert_current_min_max
					
					var convertedMin = Math.floor( originalMin * exchangeRate );
					var convertedMax = Math.ceil( originalMax * exchangeRate );
					<?php if ( $convert_current_min_max ): ?>
					var convertedCurrentMin = Math.floor( originalCurrentMin * exchangeRate );
					var convertedCurrentMax = Math.ceil( originalCurrentMax * exchangeRate );
					<?php else: ?>
					var convertedCurrentMin = parseFloat( originalCurrentMin );
					var convertedCurrentMax = parseFloat( originalCurrentMax );
					<?php endif; ?>
					
					var convertedStep = originalStep; // WC default step is 10
					if ( convertedMax - convertedMin <= 1 ) {
						convertedStep = 0.00000001; // make it tiny steps for cryptocurrencies
					} else if ( convertedMax - convertedMin < 10 ) {
						convertedStep = 1; // make small steps if our range is less than 10
					}
					
					if ( convertedMin % convertedStep ) {
						convertedMin -= convertedMin % convertedStep; // round down to nearest step
					}
					if ( convertedMax % convertedStep ) {
						convertedMax += convertedStep - ( convertedMax % convertedStep ); // round up to nearest step
					}
					if ( convertedCurrentMin % convertedStep ) {
						convertedCurrentMin -= convertedCurrentMin % convertedStep; // round down to nearest step
					}
					if ( convertedCurrentMax % convertedStep ) {
						convertedCurrentMax += convertedStep - ( convertedCurrentMax % convertedStep ); // round up to nearest step
					}
					
					$( '.price_slider_amount #min_price' ).data( 'min', convertedMin );
					$( '.price_slider_amount #max_price' ).data( 'max', convertedMax );
					$( '.price_slider_amount' ).data( 'step', convertedStep );
					$( '.price_slider_amount #min_price' ).val( convertedCurrentMin );
					$( '.price_slider_amount #max_price' ).val( convertedCurrentMax );
					
					if ( ! $( '.price_slider_amount input[name="currency_code"]' ).length ) {
						$( '.price_slider_amount' ).append( '<input name="currency_code" type="hidden" value="" />' ); 
					}
					$( '.price_slider_amount input[name="currency_code"]' ).val( '<?php echo alg_get_current_currency_code(); ?>' );
					
					$( document.body ).trigger( 'init_price_filter' );
					
				}( jQuery ));
				
				<?php else: ?>
				
                var awccs_slider = {
                    slider: null,
                    convert_rate: 1,
                    original_min: 1,
                    original_max: 1,
                    original_values: [],
                    current_min: 1,
                    current_max: 1,
                    current_values: [],
					step: 1,

					init(slider, convert_rate, step) {
						this.step = step;
                        this.slider = slider;
                        this.convert_rate = convert_rate;
                        this.original_min = jQuery(this.slider).slider("option", "min");
                        this.original_max = jQuery(this.slider).slider("option", "max");
						if (this.original_min > jQuery(this.slider).parent().find('#min_price').val()) {
							jQuery(this.slider).parent().find('#min_price').attr('value', this.original_min);
						}
						if (this.original_max < jQuery(this.slider).parent().find('#max_price').val()) {
							jQuery(this.slider).parent().find('#max_price').attr('value', this.original_max);
						}
                        this.original_values = jQuery(this.slider).slider("option", "values");
                        this.current_min = this.original_min * this.convert_rate;
                        this.current_max = this.original_max * this.convert_rate;
						this.current_values[0] = jQuery(this.slider).parent().find('#min_price').val() * awccs_slider.convert_rate;
						this.current_values[1] = jQuery(this.slider).parent().find('#max_price').val() * awccs_slider.convert_rate;
                        this.update_slider();
                    },

                    /**
                     * @see price-slider.js, init_price_filter()
                     */
                    update_slider() {
                        jQuery(this.slider).slider("destroy");
                        var current_min_price = Math.floor(this.current_min);
                        var current_max_price = Math.ceil(this.current_max);

                        jQuery(this.slider).slider({
                            range: true,
                            animate: true,
                            min: current_min_price,
                            max: current_max_price,
							step: parseFloat(this.step),
                            values: awccs_slider.current_values,
                            create: function () {
                                jQuery(awccs_slider.slider).parent().find('.price_slider_amount #min_price').val(awccs_slider.current_values[0] / awccs_slider.convert_rate);
                                jQuery(awccs_slider.slider).parent().find('.price_slider_amount #max_price').val(awccs_slider.current_values[1] / awccs_slider.convert_rate);
                                jQuery(document.body).trigger('price_slider_create', [Math.floor(awccs_slider.current_values[0]), Math.ceil(awccs_slider.current_values[1])]);
                            },
                            slide: function (event, ui) {
                                jQuery(awccs_slider.slider).parent().find('.price_slider_amount #min_price').val(Math.floor(ui.values[0] / awccs_slider.convert_rate));
                                jQuery(awccs_slider.slider).parent().find('.price_slider_amount #max_price').val(Math.ceil(ui.values[1] / awccs_slider.convert_rate));
                                jQuery(document.body).trigger('price_slider_slide', [Math.floor(ui.values[0]), Math.ceil(ui.values[1])]);
                            },
                            change: function (event, ui) {
                                jQuery(document.body).trigger('price_slider_change', [Math.floor(ui.values[0]), Math.ceil(ui.values[1])]);
                            }
                        });
                    }
                };
                var awccs_pfc = {
                    price_filters: null,
                    rate: 1,
					step: 1,
                    init: function (price_filters) {
                        this.price_filters = price_filters;
                        this.rate = document.getElementById('alg_wc_cs_exchange_rate').value;
                        this.update_slider();
                    },
                    update_slider: function () {
                        [].forEach.call(awccs_pfc.price_filters, function (el) {
                            awccs_slider.init(el, awccs_pfc.rate);
                        });
                    }
                }
                document.addEventListener("DOMContentLoaded", function () {
                    var price_filters = document.querySelectorAll('.price_slider.ui-slider');
                    if (price_filters.length) {
                        awccs_pfc.init(price_filters);
                    }
                });
				
				<?php endif; ?>
				
			</script>
			<?php
			#endregion add_compatibility_with_price_filter_widget
		}
		
		/**
         * Fixes WooCommerce Price Filter widget's currency formatting.
         *
		 * @version 2.9.3
		 * @since   2.9.3
		 */
		public function fix_price_filter_widget_currency_format() {
			$price_args = apply_filters( 'wc_price_args', array(
				'ex_tax_label'       => false,
				'currency'           => '',
				'decimal_separator'  => wc_get_price_decimal_separator(),
				'thousand_separator' => wc_get_price_thousand_separator(),
				'decimals'           => wc_get_price_decimals(),
				'price_format'       => get_woocommerce_price_format(),
			) );
			$symbol     = apply_filters( 'woocommerce_currency_symbol', get_woocommerce_currency_symbol(), get_woocommerce_currency() );
			wp_localize_script(
				'wc-price-slider', 'woocommerce_price_slider_params', array(
					'currency_format_num_decimals' => $price_args['decimals'],
					'currency_format_symbol'       => $symbol,
					'currency_format_decimal_sep'  => esc_attr( $price_args['decimal_separator'] ),
					'currency_format_thousand_sep' => esc_attr( $price_args['thousand_separator'] ),
					'currency_format'              => esc_attr( str_replace( array( '%1$s', '%2$s' ), array( '%s', '%v' ), $price_args['price_format'] ) ),
				)
			);
		}
		
		/**
		 * Fixes query args set by WooCommerce Price Filter widget.
		 *
		 * These values will be picked up automatically by the JS price slider, so they must be correct at the start.
		 *
		 * @version 2.15.0
		 * @since   2.15.0
		 */
		public function fix_price_filter_widget_query_args() {
			if (
				isset( $_GET['min_price'] ) && isset( $_GET['max_price'] ) && isset( $_GET['currency_code'] )
				&& $_GET['currency_code'] !== alg_get_current_currency_code()
			) {
				$currency_from = alg_wc_cs_get_currency_exchange_rate( sanitize_text_field( $_GET['currency_code'] ) );
				$currency_to   = alg_wc_cs_get_currency_exchange_rate( alg_get_current_currency_code() );
				$exchange_rate = $currency_to / $currency_from;
				wp_safe_redirect(
					add_query_arg( 
						array(
							'min_price' => floatval( $_GET['min_price'] ) * $exchange_rate,
							'max_price' => floatval( $_GET['max_price'] ) * $exchange_rate,
							'currency_code' => alg_get_current_currency_code(),
						),
						remove_query_arg( array( 'min_price', 'max_price', 'currency_code' ) )
					)
				);
				exit;
			}
		}
		
		/**
		 * Ensure the widget filters products within the correct price range.
		 *
		 * Since product prices in the DB are always stored in the shop's default currency, we
		 * have to reverse the currency conversion here for searching purposes.
		 *
		 * @version 2.14.0
		 * @since   2.14.0
		 *
		 * @see WC_Query::price_filter_post_clauses()
		 *
		 * @param $args
		 * @param $query
		 *
		 * @return mixed
		 */
		function posts_clauses_price_filter_compatible( $args, $query ) {
			
			if (
				is_admin() ||
				! isset( $args['where'] ) ||
				! ( isset( $_GET['min_price'] ) || isset( $_GET['max_price'] ) ) ||
				get_option( 'woocommerce_currency' ) === alg_get_current_currency_code() ||
				alg_wc_cs_get_currency_exchange_rate( alg_get_current_currency_code() ) === 1
			) {
				return $args;
			}
			
			global $wpdb;
			$current_currency_code = alg_get_current_currency_code();
			$exchange_rate         = alg_wc_cs_get_currency_exchange_rate( $current_currency_code );

			$min_price = isset( $_GET['min_price'] ) ? floatval( wp_unslash( $_GET['min_price'] ) ) : 0;
			$max_price = isset( $_GET['max_price'] ) ? floatval( wp_unslash( $_GET['max_price'] ) ) : PHP_INT_MAX;
			
			$min_price = $min_price / $exchange_rate;
			$max_price = $max_price / $exchange_rate;
			
			/* old WC way: */
			$args['where']   = preg_replace(
				'/AND wc_product_meta_lookup.min_price >= [\d.]* AND wc_product_meta_lookup.max_price <= [\d.]*\s/i',
				"AND wc_product_meta_lookup.min_price >= $min_price AND wc_product_meta_lookup.max_price <= $max_price",
				$args['where']
			);
			/* new WC way (circa WooCommerce 5.1ish): */
			$args['where']   = preg_replace(
				'/AND NOT \([\d.]*<wc_product_meta_lookup.min_price OR [\d.]*>wc_product_meta_lookup.max_price \)\s/i',
				"AND NOT ($max_price<wc_product_meta_lookup.min_price OR $min_price>wc_product_meta_lookup.max_price)",
				$args['where']
			);
			
			return $args;
		}
		
		/**
		 * modify_default_price_filter_hook.
		 *
		 * not currently used.
		 *
		 * forces the query to use our price_filter_post_clauses (taken from WooCommerce core), and not
		 * any other 3rd plugin's stuff.  See $this->price_filter_post_clauses() for part 2 of this idea.
		 *
		 * @todo just an idea for the future
		 */
		function modify_default_price_filter_hook( $query ) {
			
			if ( ! isset( $_GET['min_price'] ) || ! isset( $_GET['max_price'] ) ) {
				return $query;
			}

			// Remove Price Filter Meta Query
			$meta_query = $query->get( 'meta_query' );
			$meta_query = empty( $meta_query ) ? array() : $meta_query;
			foreach ( $meta_query as $key => $value ) {
				if ( is_array( $value ) ) {
					if ( isset( $value['price_filter'] ) ) {
						unset( $meta_query[ $key ]['price_filter'] );
					}
				}
			}
			$query->set( 'meta_query', $meta_query );

			// Remove Price Filter Hooks
			wpw_cs_remove_class_filter( 'posts_clauses', 'WC_Query', 'price_filter_post_clauses' );

			// Remove Price Filter hooks from "Product Filter for WooCommerce" plugin
			if ( class_exists( 'XforWC_Product_Filters_Frontend' ) ) {
				remove_filter( 'posts_clauses', 'XforWC_Product_Filters_Frontend::price_filter_post_clauses', 10, 2 );
			}

			// Add Price Filter Hook
			add_filter( 'posts_clauses', array( $this, 'price_filter_post_clauses' ), 10, 2 );
		}

		/**
		 * price_filter_post_clauses.
		 *
		 * not currently used.
		 *
		 * @todo just an idea for the future. this might be a solution to taking per-product settings into consideration.
		 */
		function price_filter_post_clauses( $args, $wp_query ) {
			global $wpdb;

			if ( ! $wp_query->is_main_query() || ( ! isset( $_GET['max_price'] ) && ! isset( $_GET['min_price'] ) ) ) {
				return $args;
			}

			$current_min_price = isset( $_GET['min_price'] ) ? floatval( wp_unslash( $_GET['min_price'] ) ) : 0; // WPCS: input var ok, CSRF ok.
			$current_max_price = isset( $_GET['max_price'] ) ? floatval( wp_unslash( $_GET['max_price'] ) ) : PHP_INT_MAX; // WPCS: input var ok, CSRF ok.

			/**
			 * Adjust if the store taxes are not displayed how they are stored.
			 * Kicks in when prices excluding tax are displayed including tax.
			 */
			if ( wc_tax_enabled() && 'incl' === get_option( 'woocommerce_tax_display_shop' ) && ! wc_prices_include_tax() ) {
				$tax_class = apply_filters( 'woocommerce_price_filter_widget_tax_class', '' ); // Uses standard tax class.
				$tax_rates = WC_Tax::get_rates( $tax_class );

				if ( $tax_rates ) {
					$current_min_price -= WC_Tax::get_tax_total( WC_Tax::calc_inclusive_tax( $current_min_price, $tax_rates ) );
					$current_max_price -= WC_Tax::get_tax_total( WC_Tax::calc_inclusive_tax( $current_max_price, $tax_rates ) );
				}
			}

			// $args['join']   = $this->append_product_sorting_table_join( $args['join'] );
			$args['where'] .= $wpdb->prepare(
				' AND wc_product_meta_lookup.min_price >= %f AND wc_product_meta_lookup.max_price <= %f ',
				$current_min_price,
				$current_max_price
			);
			return $args;
			
			/*
			 * @todo just an idea for the future.
			 * the following would take per-product settings into account, but it does not yet consider individual variations.
			 * need to think about this some more.
			 */
			// $current_currency_code = alg_get_current_currency_code();
			// $exchange_rate         = alg_wc_cs_get_currency_exchange_rate( $current_currency_code );
			
			// $args['where'] .= $wpdb->prepare(
				// "
					// AND {$wpdb->posts}.ID IN (
						// SELECT ID
						// FROM ( 
							// SELECT p.ID as ID, 
							
							// IF( pm3.meta_value>0, pm3.meta_value,
								// IF( pm2.meta_value>0, pm2.meta_value, pm.meta_value/$exchange_rate )
							// )
							
							// AS wpw_price
							
							// FROM {$wpdb->posts} as p
							// LEFT JOIN {$wpdb->postmeta} as pm ON (pm.post_id=p.ID) AND (pm.meta_key = '_price')
							// LEFT JOIN {$wpdb->postmeta} as pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_alg_currency_switcher_per_product_regular_price_$current_currency_code'
							// LEFT JOIN {$wpdb->postmeta} as pm3 ON p.ID = pm3.post_id AND pm3.meta_key = '_alg_currency_switcher_per_product_sale_price_$current_currency_code'
							// WHERE p.post_type = 'product' AND p.post_status = 'publish'
						// ) wpw_prices
						// WHERE wpw_price <= %f AND wpw_price >= %f
						// GROUP BY ID
					// )
				// ",
				// $current_max_price,
				// $current_min_price
			// );
			
			// return $args;
		}
		
		/**
         * Adds compatibility with WooCommerce Product Add-ons plugin, converting addon prices
		 *
		 * @version 2.15.0
		 * @since   2.15.0
		 * @link https://woocommerce.com/products/product-add-ons/
		 */
		function product_addons_convert_addon_prices( $addons ) {
			
			if (
				isset( $_POST['add-to-cart'] )
				&& ! empty( $_POST['add-to-cart'] )
			) {
				// don't adjust when adding to cart... conversion will happen later.
				$backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
				foreach ( $backtrace as $call ) {
					if (
						$call['function'] === 'add_cart_item_data'
						&& $call['class'] === 'WC_Product_Addons_Cart'
					) {
						return $addons;
					}
				}
			}
			
			$current_currency_code = alg_get_current_currency_code();
			$default_currency      = get_option( 'woocommerce_currency' );
			
			foreach ( $addons as $addon_key => $addon ) {
				foreach ( $addon['options'] as $option_key => $option ) {
					if ( $option['price_type'] === 'percentage_based' ) {
						continue;
					}
					if (
						isset( $option['wpwham_price_curr'] )
						&& $option['wpwham_price_curr'] === $current_currency_code
					) {
						continue;
					}
					$addons[ $addon_key ]['options'][ $option_key ]['price'] = alg_convert_price( array(
						'price'         => $option['price'],
						'currency_from' => $default_currency,
						'currency'      => $current_currency_code,
						'format_price'  => 'no'
					) );
					$addons[ $addon_key ]['options'][ $option_key ]['wpwham_price_curr'] = $current_currency_code;
				}
			}
			
			return $addons;
		}
		
		/**
         * Adds compatibility with WooCommerce Product Add-ons plugin, fixing addon prices for display
		 *
		 * @version 2.15.0
		 * @since   2.15.0
		 * @link https://woocommerce.com/products/product-add-ons/
		 */
		function product_addons_fix_addon_prices_for_display( $other_data, $cart_item ) {
			
			if ( ! is_callable( array( 'WC_Product_Addons_Helper', 'get_product_addon_price_for_display' ) ) ) {
				return $other_data;
			}
			
			$current_currency_code = alg_get_current_currency_code();
			$default_currency      = get_option( 'woocommerce_currency' );
			
			if ( ! empty( $cart_item['addons'] ) ) {
				foreach ( $cart_item['addons'] as $addon ) {
					$price = isset( $cart_item['addons_price_before_calc'] ) ? $cart_item['addons_price_before_calc'] : $addon['price'];
					$original_name = $addon['name'];
					$replaced_name = $addon['name'];
					
					if ( $addon['price_type'] !== 'percentage_based' ) {
						$replaced_price = alg_convert_price( array(
							'price'         => $addon['price'],
							'currency_from' => $default_currency,
							'currency'      => $current_currency_code,
							'format_price'  => 'no'
						) );
					}

					if ( 0 == $addon['price'] ) {
						$original_name .= '';
						$replaced_name .= '';
					} elseif ( 'percentage_based' === $addon['price_type'] && 0 == $price ) {
						$original_name .= '';
						$replaced_name .= '';
					} elseif ( 'percentage_based' !== $addon['price_type'] && $addon['price'] && apply_filters( 'woocommerce_addons_add_price_to_name', '__return_true' ) ) {
						$original_name .= ' (' . wc_price( WC_Product_Addons_Helper::get_product_addon_price_for_display( $addon['price'], $cart_item['data'], true ) ) . ')';
						$replaced_name .= ' (' . wc_price( WC_Product_Addons_Helper::get_product_addon_price_for_display( $replaced_price, $cart_item['data'], true ) ) . ')';
					} else {
						$_product = wc_get_product( $cart_item['product_id'] );
						$_product->set_price( $price * ( $addon['price'] / 100 ) );
						$original_name .= ' (' . WC()->cart->get_product_price( $_product ) . ')';
						$replaced_name .= ' (' . WC()->cart->get_product_price( $_product ) . ')';
					}
					
					foreach ( $other_data as $key => $value ) {
						if ( $value['name'] === $original_name ) {
							$other_data[ $key ]['name'] = $replaced_name;
						}
					}
				}
			}

			return $other_data;
			
		}


		/**
		 * Adds compatibility with PPOM for WooCommerce plugin, converting values back from plugin, if session was updated
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
		 * Fixes product price on PPOM for WooCommerce plugin
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
		 * Adds currency meta to PPOM for WooCommerce plugin
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
		 * Adds compatibility with PPOM for WooCommerce plugin, converting values back from plugin
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
		 * Adds compatibility with PPOM for WooCommerce plugin, converting values from plugin
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
		
		/**
		 * Adds compatibility with WooCommerce Chained Products plugin
		 * @since   2.11.0
		 */
		public static function is_chained_product( $_product ) {
			global $woocommerce;
			
			if ( ! $_product || ! $woocommerce || ! $woocommerce->cart ) {
				return false;
			}
		
			$items = $woocommerce->cart->get_cart();
			
			$product_id = $_product->get_id();
			
			foreach( $items as $item => $values ) {
				
				$cart_product_id = $values['data']->get_id();
				
				if ( $product_id === $cart_product_id && ! empty( $values['chained_item_of'] ) ) {
					return true;
				}
			}
			
			return false;
		}

	}

endif;
