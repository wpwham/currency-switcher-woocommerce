<?php
/*
Plugin Name: Currency Switcher for WooCommerce
Plugin URI: https://wpwham.com/products/currency-switcher-for-woocommerce/
Description: Currency Switcher for WooCommerce.
Version: 2.15.1
Author: WP Wham
Author URI: https://wpwham.com
Text Domain: currency-switcher-woocommerce
Domain Path: /langs
WC tested up to: 6.6
Copyright: © 2018-2022 WP Wham. All rights reserved.
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Check if WooCommerce is active
$plugin = 'woocommerce/woocommerce.php';
if (
	! in_array( $plugin, apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) ) ) &&
	! ( is_multisite() && array_key_exists( $plugin, get_site_option( 'active_sitewide_plugins', array() ) ) )
) return;

if ( 'currency-switcher-woocommerce.php' === basename( __FILE__ ) ) {
	// Check if Pro is active, if so then return
	$plugin = 'currency-switcher-woocommerce-pro/currency-switcher-woocommerce-pro.php';
	if (
		in_array( $plugin, apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) ) ) ||
		( is_multisite() && array_key_exists( $plugin, get_site_option( 'active_sitewide_plugins', array() ) ) )
	) return;
}

if ( ! defined( 'WPWHAM_CURRENCY_SWITCHER_VERSION' ) ) {
	define( 'WPWHAM_CURRENCY_SWITCHER_VERSION', '2.15.1' );
}



// Constants
require_once( 'includes/alg-constants.php' );

if ( ! class_exists( 'Alg_WC_Currency_Switcher' ) ) :

/**
 * Main Alg_WC_Currency_Switcher Class
 *
 * @class   Alg_WC_Currency_Switcher
 * @version 2.15.1
 * @since   1.0.0
 */
final class Alg_WC_Currency_Switcher {

	/**
	 * Currency Switcher plugin version.
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public $version = '2.15.1';

	/**
	 * @var   Alg_WC_Currency_Switcher The single instance of the class
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Main Alg_WC_Currency_Switcher Instance
	 *
	 * Ensures only one instance of Alg_WC_Currency_Switcher is loaded or can be loaded.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @static
	 * @return Alg_WC_Currency_Switcher - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Alg_WC_Currency_Switcher Constructor.
	 *
	 * @version 2.15.0
	 * @since   1.0.0
	 * @access  public
	 * @todo    (maybe) AJAX in admin "Currencies" settings section
	 * @todo    unschedule crons on plugin deactivate
	 * @todo    check for caching issues
	 * @todo    (maybe) add all currencies (so no other additional plugin is required)
	 */
	function __construct() {

		// Set up localisation
		load_plugin_textdomain( 'currency-switcher-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );		

		// Include required files
		$this->includes();

		// Add compatibility with third party plugins
		$compatibility = new Alg_Switcher_Third_Party_Compatibility();
		$compatibility->init();

		// Settings & Scripts
		if ( is_admin() ) {
			add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_woocommerce_settings_tab' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );
			add_action( 'woocommerce_system_status_report', array( $this, 'add_settings_to_status_report' ) );
		}
		
		// WooCommerce scheduled tasks
		add_action( 'wc_after_products_ending_sales', array( $this, 'cleanup_ended_sales_prices' ) );
		
	}	

	/**
	 * Show action links on the plugin screen
	 *
	 * @version 2.15.0
	 * @since   1.0.0
	 * @param   mixed $links
	 * @return  array
	 */
	function action_links( $links ) {
		$settings_link   = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=alg_wc_currency_switcher' ) . '">' . __( 'Settings', 'woocommerce' )   . '</a>';
		$unlock_all_link = '<a target="_blank" href="' . esc_url( 'https://wpwham.com/products/currency-switcher-for-woocommerce/?utm_source=plugins_page&utm_campaign=free&utm_medium=currency_switcher' ) . '">' .
			__( 'Unlock all', 'currency-switcher-woocommerce' ) . '</a>';
		$custom_links    = ( PHP_INT_MAX === apply_filters( 'alg_wc_currency_switcher_plugin_option', 2 ) ) ?
			array( $settings_link ) : array( $settings_link, $unlock_all_link );
		return array_merge( $custom_links, $links );
	}
	
	/**
	 * @since   2.15.0
	 */
	public function cleanup_ended_sales_prices( $product_ids ) {
		if ( ! apply_filters( 'wpwham_currency_switcher_cleanup_ended_sales_prices', true ) ) {
			return;
		}
		$currencies = alg_get_enabled_currencies( false );
		foreach ( $product_ids as $product_id ) {
			foreach ( $currencies as $currency ) {
				update_post_meta( $product_id, '_alg_currency_switcher_per_product_sale_price_' . $currency, '' );
			}
		}
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 *
	 * @version 2.15.1
	 * @since   1.0.0
	 * @todo    (maybe) import/export all settings
	 */
	private function includes() {

		// Functions
		require_once( 'includes/functions/alg-switcher-selector-functions.php' );
		require_once( 'includes/functions/alg-switcher-functions.php' );
		require_once( 'includes/functions/alg-switcher-exchange-rates-functions.php' );
		require_once( 'includes/functions/alg-switcher-country-functions.php' );
		require_once( 'includes/functions/alg-switcher-locale-functions.php' );

		// Compatibility
		require_once( 'includes/class-alg-switcher-third-party-compatibility.php' );

		// Settings
		require_once( 'includes/admin/settings/class-alg-wc-currency-switcher-settings-section.php' );
		$this->settings = array();
		$this->settings['general']            = require_once( 'includes/admin/settings/class-alg-wc-currency-switcher-settings-general.php' );
		$this->settings['currencies']         = require_once( 'includes/admin/settings/class-alg-wc-currency-switcher-settings-currencies.php' );
		$this->settings['exchange_rates']     = require_once( 'includes/admin/settings/class-alg-wc-currency-switcher-settings-exchange-rates.php' );
		$this->settings['currency_countries'] = require_once( 'includes/admin/settings/class-alg-wc-currency-switcher-settings-currency-countries.php' );
		$this->settings['currency_locales']   = require_once( 'includes/admin/settings/class-alg-wc-currency-switcher-settings-currency-locales.php' );
		$this->settings['price_formats']      = require_once( 'includes/admin/settings/class-alg-wc-currency-switcher-settings-price-formats.php' );
		$this->settings['flags']              = require_once( 'includes/admin/settings/class-alg-wc-currency-switcher-settings-flags.php' );
		$this->settings['advanced']           = require_once( 'includes/admin/settings/class-alg-wc-currency-switcher-settings-advanced.php' );
		if ( is_admin() && get_option( 'alg_currency_switcher_version', '' ) !== $this->version ) {
			foreach ( $this->settings as $section ) {
				foreach ( $section->get_settings() as $value ) {
					if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
						$autoload = isset( $value['autoload'] ) ? (bool) $value['autoload'] : true;
						add_option( $value['id'], $value['default'], '', $autoload );
					}
				}
			}
			update_option( 'alg_currency_switcher_version', $this->version );
		}

		// Per product Settings
		if ( 'yes' === get_option( 'alg_currency_switcher_per_product_enabled', 'yes' ) ) {
			require_once( 'includes/admin/class-alg-wc-currency-switcher-per-product.php' );
		}

		// Coupons Settings
		if ( 'yes' === get_option( 'alg_currency_switcher_fixed_coupons_base_currency_enabled', 'no' ) ) {
			require_once( 'includes/admin/class-alg-wc-currency-switcher-coupons.php' );
		}

		// Crons & Reports
		if ( 'yes' === get_option( 'alg_wc_currency_switcher_enabled', 'yes' ) ) {
			if ( 'manual' != get_option( 'alg_currency_switcher_exchange_rate_update', 'manual' ) ) {
				require_once( 'includes/class-alg-exchange-rates-crons.php' );
			}
			require_once( 'includes/admin/class-alg-currency-reports.php' );
		}

		// Widget
		require_once( 'includes/class-alg-widget-currency-switcher.php' );

		// Core
		$this->core = require_once( 'includes/class-alg-wc-currency-switcher.php' );
	}

	/**
	 * add settings to WC status report
	 *
	 * @version 2.12.2
	 * @since   2.12.2
	 * @author  WP Wham
	 */
	public static function add_settings_to_status_report() {
		#region add_settings_to_status_report
		$protected_settings      = array( 'wpwham_currency_switcher_license', 'wpw_cs_fcc_api_key' );
		$settings_general        = Alg_WC_Currency_Switcher_Settings_General::get_general_settings( array() );
		$settings_currencies     = Alg_WC_Currency_Switcher_Settings_Currencies::get_currencies_settings( array() );
		$settings_exchange_rates = Alg_WC_Currency_Switcher_Settings_Exchange_Rates::get_exchange_rates_settings( array() );
		$settings_countries      = Alg_WC_Currency_Switcher_Settings_Currency_Countries::get_currency_countries_settings( array() );
		$settings_languages      = Alg_WC_Currency_Switcher_Settings_Currency_Locales::get_currency_locales_settings( array() );
		$settings_price_formats  = Alg_WC_Currency_Switcher_Settings_Price_Formats::get_price_formats_settings( array() );
		$settings_flags          = Alg_WC_Currency_Switcher_Settings_Flags::get_flags_settings( array() );
		$settings_advanced       = Alg_WC_Currency_Switcher_Settings_Advanced::get_advanced_settings( array() );
		$settings                = array_merge(
			$settings_general, $settings_currencies, $settings_exchange_rates,
			$settings_countries, $settings_languages, $settings_price_formats,
			$settings_flags, $settings_advanced
		);
		?>
		<table class="wc_status_table widefat" cellspacing="0">
			<thead>
				<tr>
					<th colspan="3" data-export-label="Currency Switcher Settings"><h2><?php esc_html_e( 'Currency Switcher Settings', 'currency-switcher-woocommerce' ); ?></h2></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $settings as $setting ): ?>
				<?php 
				if ( in_array( $setting['type'], array( 'title', 'sectionend' ) ) ) { 
					continue;
				}
				if ( isset( $setting['title'] ) ) {
					$title = $setting['title'];
				} elseif ( isset( $setting['desc'] ) ) {
					$title = $setting['desc'];
				} else {
					$title = $setting['id'];
				}
				$value = get_option( $setting['id'] ); 
				if ( in_array( $setting['id'], $protected_settings ) ) {
					$value = $value > '' ? '(set)' : 'not set';
				}
				?>
				<tr>
					<td data-export-label="<?php echo esc_attr( $title ); ?>"><?php esc_html_e( $title, 'currency-switcher-woocommerce' ); ?>:</td>
					<td class="help">&nbsp;</td>
					<td><?php echo is_array( $value ) ? print_r( $value, true ) : $value; ?></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
		#endregion add_settings_to_status_report
	}

	/**
	 * Add Currency Switcher Plugin settings tab to WooCommerce settings.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function add_woocommerce_settings_tab( $settings ) {
		$settings[] = include( 'includes/admin/settings/class-wc-settings-currency-switcher.php' );
		return $settings;
	}

	/**
	 * Get the plugin url.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @return  string
	 */
	function plugin_url() {
		return untrailingslashit( plugin_dir_url( __FILE__ ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @return  string
	 */
	function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

}

endif;

if ( ! function_exists( 'alg_wc_currency_switcher_plugin' ) ) {
	/**
	 * Returns the main instance of Alg_WC_Currency_Switcher to prevent the need to use globals.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @return  Alg_WC_Currency_Switcher
	 */
	function alg_wc_currency_switcher_plugin() {
		return Alg_WC_Currency_Switcher::instance();
	}
}

/*
 * Load the plugin.
 * Note: ( plugins_loaded, 2 ) here makes us load immediately after polylang loads, but it's filterable if need-be.
 */
add_action(
	apply_filters( 'wpw_cs_loading_hook', 'plugins_loaded' ),
	'alg_wc_currency_switcher_plugin',
	apply_filters( 'wpw_cs_loading_priority', 2 )
);
