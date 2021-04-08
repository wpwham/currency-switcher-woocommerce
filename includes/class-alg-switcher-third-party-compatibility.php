<?php
/**
 * Currency Switcher Plugin - Third Party Compatibility
 *
 * Adds compatibility with other third party plugins, like Product Addons
 *
 * @version 2.14.0
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
		 * @version 2.8.9
		 * @since   2.8.9
		 */
		function init() {
			// Adds compatibility with WooCommerce Product Addons plugin
			//$this->handle_product_addons_plugin();

			// Adds compatibility with WooCommerce Price Filter widget
			$this->handle_price_filter();
		}

		/**
		 * Adds compatibility with WooCommerce Price Filter widget
		 * @version 2.14.0
		 * @since   2.8.9
		 */
		private function handle_price_filter() {
			// WooCommerce Price Filter Widget
			if ( get_option( 'wpw_currency_switcher_price_filter_widget_enabled', 'yes' ) === 'yes' ) {
				
				// @todo idea for the future:
				// add_action( 'woocommerce_product_query', array( $this, 'modify_default_price_filter_hook' ), PHP_INT_MAX );
				
				add_action( 'wp_footer', array( $this, 'add_compatibility_with_price_filter_widget' ) );
				add_action( 'wp_footer', array( $this, 'fix_price_filter_widget_currency_format' ) );
				add_filter( 'posts_clauses', array( $this, 'posts_clauses_price_filter_compatible' ), 11, 2 );
				add_filter( 'woocommerce_price_filter_widget_step', function ( $step ) {
					$step = 1;
					return $step;
				} );
			}
		}

		/**
         * Fixes price filter widget currency format
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
		 * Adds compatibility with WooCommerce Price Filter widget
		 * @version 2.14.0
		 * @since   2.8.9
		 */
		public function add_compatibility_with_price_filter_widget() {
			#region add_compatibility_with_price_filter_widget
			
			if ( ! is_active_widget( false, false, 'woocommerce_price_filter' ) ) {
				return;
			}
			
			$exchange_rate = alg_wc_cs_get_currency_exchange_rate( alg_get_current_currency_code() );
			if ( $exchange_rate == 1 ) {
				return;
			}
			
			?>
			<input type="hidden" id="alg_wc_cs_exchange_rate" value="<?php echo esc_html( $exchange_rate ) ?>"/>
			<script>
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
			</script>
			<?php
			#endregion add_compatibility_with_price_filter_widget
		}
		
		/**
		 * Makes Price Filter Widget apply currency conversion to filter
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
		 * Add compatibility with WooCommerce Product Addons plugin
		 * @version 2.8.9
		 * @since   2.8.9
		 */
		private function handle_product_addons_plugin(){
			add_filter( 'ppom_option_price', array( $this, 'product_addons_convert_option_price' ), 10, 4 );
			add_filter( 'ppom_cart_line_total', array( $this, 'product_addons_convert_price_back' ) );
			add_filter( 'ppom_cart_fixed_fee', array( $this, 'product_addons_convert_price_back' ) );
			add_filter( 'ppom_add_cart_item_data', array( $this, 'ppom_woocommerce_add_cart_item_data' ), 10, 2 );
			add_filter( 'ppom_product_price', array( $this, 'ppom_product_price' ) );
			add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'ppom_get_cart_item_from_session' ), 1 );
		}

		/**
		 * Adds compatibility with WooCommerce Product Addons plugin, converting values back from plugin, if session was updated
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
		 * Fixes product price on Product Addons plugin
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
		 * Adds currency meta to Product Addons plugin
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
		 * Adds compatibility with WooCommerce Product Addons plugin, converting values back from plugin
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
		 * Adds compatibility with WooCommerce Product Addons plugin, converting values from plugin
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
