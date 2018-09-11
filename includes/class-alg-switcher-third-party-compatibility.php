<?php
/**
 * Currency Switcher Plugin - Third Party Compatibility
 *
 * Adds compatibility with other third party plugins, like Product Addons
 *
 * @version 2.9.3
 * @since   2.8.8
 * @author  Tom Anbinder
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
		 * @version 2.9.3
		 * @since   2.8.9
		 */
		private function handle_price_filter() {
			add_action( 'wp_footer', array( $this, 'add_compatibility_with_price_filter_widget' ) );
			add_action( 'wp_footer', array( $this, 'fix_price_filter_widget_currency_format' ) );
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
		 * @version 2.9.3
		 * @since   2.8.9
		 */
		public function add_compatibility_with_price_filter_widget() {
			if ( ! is_active_widget( false, false, 'woocommerce_price_filter' ) ) {
				return;
			}
			?>
			<?php
				$exchange_rate = alg_wc_cs_get_currency_exchange_rate( alg_get_current_currency_code() );
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

                    init(slider, convert_rate) {
                        this.slider = slider;
                        this.convert_rate = convert_rate;
                        this.original_min = jQuery(this.slider).slider("option", "min");
                        this.original_max = jQuery(this.slider).slider("option", "max");
                        this.original_values = jQuery(this.slider).slider("option", "values");
                        this.current_min = this.original_min * this.convert_rate;
                        this.current_max = this.original_max * this.convert_rate;
                        this.current_values = this.original_values.map(function (elem) {
                            return elem * awccs_slider.convert_rate;
                        });
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
                            values: awccs_slider.current_values,
                            create: function () {
                                jQuery(awccs_slider.slider).parent().find('.price_slider_amount #min_price').val(awccs_slider.current_values[0] / awccs_slider.convert_rate);
                                jQuery(awccs_slider.slider).parent().find('.price_slider_amount #max_price').val(awccs_slider.current_values[1] / awccs_slider.convert_rate);
                                jQuery(document.body).trigger('price_slider_create', [awccs_slider.current_values[0], awccs_slider.current_values[1]]);
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

	}

endif;