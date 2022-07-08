=== Currency Switcher for WooCommerce ===
Contributors: wpwham
Tags: currency switcher, multicurrency, multi currency, currency, switcher
Requires at least: 4.4
Tested up to: 6.0
Stable tag: 2.15.1
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Currency Switcher for WooCommerce.

== Description ==

Currency Switcher for WooCommerce.

= Features =
* Automatic currency exchange rates updates.
* Prices on per product basis.
* Currency by country (i.e. by IP).
* Currency by language (i.e. locale).
* Option to revert to original currency on checkout.
* Option to override currency by user selected billing or shipping country.
* Various currency switcher placement and format options.
* Option to add currency switcher as widget or as shortcodes.
* Option to additionally change order currency by admin.

= Currency Switcher Shortcodes =
* `[woocommerce_currency_switcher_drop_down_box]` - currency switcher in drop down box format.
* `[woocommerce_currency_switcher_radio_list]` - currency switcher in radio list format.
* `[woocommerce_currency_switcher_link_list]` - currency switcher in links list format.

= More Shortcodes =
* `[woocommerce_currency_switcher_product_price_table]` - product prices preview in all currencies.
* `[woocommerce_currency_switcher_convert_price]` - convert any price to another currency.
* `[woocommerce_currency_switcher_current_currency_symbol]` - show current currency symbol.
* `[woocommerce_currency_switcher_current_currency_code]` - show current currency code.

= Feedback =
* We are open to your suggestions and feedback. Thank you for using or trying out one of our plugins!
* Drop us a line at [https://wpwham.com](https://wpwham.com).

= More =
* Visit the [Currency Switcher for WooCommerce plugin page](https://wpwham.com/products/currency-switcher-for-woocommerce/).

== Installation ==

1. Upload the entire 'currency-switcher-woocommerce' folder to the '/wp-content/plugins/' directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Start by visiting plugin settings at WooCommerce > Settings > Currency Switcher.

== Screenshots ==

1. Currency Switcher for WooCommerce - Currencies.
2. Currency Switcher for WooCommerce - Exchange Rates.
3. Currency Switcher for WooCommerce - Currency Countries (by IP).
4. Currency Switcher for WooCommerce - General.
5. Currency Switcher for WooCommerce - General - Switcher Placement and Format.
6. Currency Switcher for WooCommerce - General - Exchange Rates Final Price Correction.
7. Currency Switcher for WooCommerce - General - Advanced Options.
8. Currency Switcher for WooCommerce - Price Formats.
9. Currency Switcher for WooCommerce - Languages.
10. Currency Switcher for WooCommerce - Shortcodes - Product Price Table.

== Frequently Asked Questions ==
= Issues regarding Paypal =

* If you are experiencing some sort of issue regarding paypal, like incorrect currency code displayed in notification emails or anything else, please try to disable the default paypal component bundled with WooCommerce and use [this plugin instead](https://www.angelleye.com/product/woocommerce-paypal-plugin/)

= How to get the converted price =
If you are trying to display the converted price on a custom template for example you can use our `alg_get_product_price_by_currency()` function.
e.g

`
add_action('woocommerce_single_product_summary', function(){
	if ( ! class_exists( 'Alg_WC_Currency_Switcher' ) ) {
		return;
	}
	global $product;
	$price = get_post_meta( get_the_ID(), '_regular_price', true);
	$converted_price = alg_get_product_price_by_currency( $price, alg_get_current_currency_code(), $product, true );
	$converted_price_formatted = wc_price( $converted_price );
	echo "<h1>{$converted_price}</h1>";
	echo "<h1>{$converted_price_formatted}</h1>";
});
`

= CoinMarketCap not working? =
Please make sure you are using at least one Cryptocurrency on your Currency settings.
You can install [All Currencies for WooCommerce](https://wordpress.org/plugins/woocommerce-all-currencies/) plugin to enable them

= National Bank of Georgia =
If you are trying the National Bank of Georgia server option with no success, make sure that your server has the SoapClient library installed and enabled

= How to override rounding and pretty price per currency? =
If you want for example to turn on the rounding for all products except for Bitcoin, supposing you’ve enabled rounding and pretty price on plugin’s settings:
`
add_filter( 'alg_wc_currency_switcher_correction', function ( $correction, $currency ) {
	if ( $currency == 'BTC' ) {
		$correction['rounding'] = 'no_round';
		$correction['pretty_price'] = 'no';
	}

	return $correction;
}, 10, 2 );
`

= How to force currency by URL =
If you want to set a currency just accessing an URL you can append the **alg_currency** variable with the currency code you want.
e.g
`
http://www.yoursite.com?alg_currency=USD
`

== Changelog ==

= 2.15.1 - 2022-07-07 =
* FIX: conflict with WP block widgets.
* FIX: various issues with WC reports filter.

= 2.15.0 - 2021-10-07 =
* NEW: added compatibility with "WooCommerce Product Add-ons" plugin.
* FIX: issue with WooCommerce Price Filter Widget min/max prices not applying exchange rate.
* FIX: clear out per-product sale prices from all currencies after sale ends.  (WooCommerce automatically deletes the sale price in the shop's default currency; we added a hook to do the same thing for any additional currencies.  If you don't want this and want to keep the old prices, use the new hook 'wpwham_currency_switcher_cleanup_ended_sales_prices').
* UPDATE: PHP 8 now officially supported.
* UPDATE: performance improvement -- load our admin assets only when needed.
* UPDATE: updated .pot file for translations.

= 2.14.0 - 2021-04-07 =
* NEW: added a setting "Apply Currency Conversion to Shipping Amount". (Previously, the shipping amount was always adjusted).
* NEW: added a setting "Apply Currency Conversion to WooCommerce Price Filter Widget". (Previously, the price filters were always adjusted).
* NEW: added filter 'wpw_currency_switcher_adjust_package_rate' so you can disable currency conversion on shipping programmatically, either for one shipping method or for all.
* FIX: added a fallback method to try and fetch exchange rates a different way if simplexml_load_file fails (e.g. simplexml_load_file will fail if the server configuration has allow_url_fopen=0).
* FIX: analytics currency filters updated to work with newer versions of Woo Admin.
* FIX: make WooCommerce Price Filter Widget apply currency conversion when filtering.
* FIX: updated CoinMarketCap to use latest API version. (NOTE: CoinMarketCap now requires an API Key -- if you are using this service, you must obtain a key and enter it in the settings under WooCommerce / Settings / Currency Switcher / Exchange Rates).
* FIX: various issues with National Bank of Georgia exchange rates: error when a certain currency pair is not available; results not always returned in the same order; exchange rates which are quoted in quantities other than 1.
* UPDATE: added support for custom headers in remote API calls (necessary now for CoinMarketCap).
* UPDATE: improved UI on exchange rate settings page: you can now test different servers' exchange rates without having to save your settings between each one.
* UPDATE: updated .pot file for translations.

= 2.13.0 - 2021-01-19 =
* NEW: Ability to filter WooCommerce Analytics by currency.
* FIX: add 'post_type==shop_order' check to function 'is_admin_order_page' (thanks to Pablo).
* FIX: include 'currency' argument in function 'price_format' (thanks to Pablo).
* FIX: issue on product edit page where variations tab shows the current currency code from the frontend, instead of the shop default.

= 2.12.4 - 2020-09-17 =
* UPDATE: bump tested versions

= 2.12.3 - 2020-08-20 =
* FIX: apply currency conversion to coupon min/max thresholds.
* FIX: shipping price doesn't adjust in cart if subtotal is zero.
* UPDATE: minor text change.
* UPDATE: updated .pot file for translations.

= 2.12.2 - 2020-08-07 =
* FIX: issue where changing back to default locale does not also switch back to default currency. (When using "Currency Languages (Locales)" feature).
* FIX: PHP notice.
* UPDATE: display our settings in WC status report.

= 2.12.1 - 2020-06-13 =
* FIX: possible conflict with Polylang (i.e. our plugin loading too early, before Polylang is ready)
* UPDATE: minor text change.
* UPDATE: updated .pot file for translations.

= 2.12.0 - 2020-05-27 =
* NEW: added new exchange rate servers: Bank of England, TCMP.
* UPDATE: updated Free Currency Converter API to v7, including new API Key requirement.  (Free Currency Converter now requires an API Key -- if you are using this service, you must obtain a key and enter it in the settings under WooCommerce / Settings / Currency Switcher / Exchange Rates).
* UPDATE: removed Google Finance API (service was discontinued).
* UPDATE: miscellaneous refactoring.
* UPDATE: updated .pot file for translations.

= 2.11.5 - 2019-12-17 =
* UPDATE: bump tested versions

= 2.11.4 - 2019-11-15 =
* UPDATE: bump tested versions

= 2.11.3 - 2019-11-04 =
* FIX: issue where scheduled sale prices ignore the schedule

= 2.11.2 - 2019-10-31 =
* FIX: issue where someone could force a currency that is not enabled in the settings

= 2.11.1 - 2019-09-29 =
* FIX: possible PHP error

= 2.11.0 - 2019-08-14 =
* NEW: Added compatibility with WooCommerce Chained Products plugin (https://woocommerce.com/products/chained-products/)

= 2.10.0 - 2019-07-23 =
* UPDATE: updated .pot file for translations

= 2.9.7 - 2018-11-06 =
* Fix cart currency when cart is empty

= 2.9.6 - 2018-10-30 =
* Add option to format price on admin order page

= 2.9.5 - 2018-10-19 =
* Improve CURL setup

= 2.9.4 - 2018-09-22 =
* Explain how to force currency by url on readme
* Replace currency input step from 'ALG_WC_CS_EXCHANGE_RATES_STEP' to 'any'

= 2.9.3 - 2018-09-11 =
* Fix price filter widget currency format
* Fix price filter rounding
* Add 'alg_wc_currency_switcher_correction' filter to override 'Final Price Correction options'
* Add FAQ question about override rounding with 'alg_wc_currency_switcher_correction' filter

= 2.9.2 - 2018-09-10 =
* Add CoinMarketCap exchange server
* Add CoinMarketCap question on FAQ

= 2.9.1 - 2018-09-10 =
* Add Free Currency Converter API exchange server
* Remove Yahoo exchange server
* Remove Fixer.io exchange server
* Remove CoinMarketCap exchange server

= 2.9.0 - 2018-08-21 =
* Add question on plugin's description about getting converted values
* Add 'alg_wc_cs_get_exchange_rate' filter allowing managing the exchange rate externally
* Add 'National Bank of Georgia' exchange server

= 2.8.9 - 2018-08-08 =
* Improve compatibility with WooCommerce Product Addons plugin
* Add compatibility with price filter widget

= 2.8.8 - 2018-07-19 =
* Add notification on plugin's description about possible paypal issues
* Add karzin as contributor
* Fix version number
* Prepare premium version for adding compatibility with WooCommerce Product Addons plugin 

= 2.8.7 - 2018-07-13 =
* Dev - Admin settings descriptions updated.

= 2.8.6 - 2018-06-14 =
* Fix - Exchange Rates - "Google" exchange rates server removed (fallback - default "European Central Bank (ECB)" server).
* Dev - Countries - "Override Country" options added.
* Dev - General - Order Options - "Order Currency" options added.
* Dev - `alg_convert_price()` function (and `[woocommerce_currency_switcher_convert_price]` shortcode) - Special `%cart_total%` case for `price` attribute added; `currency_from` attribute (empty by default) added.
* Dev - Plugin link updated from <a href="https://wpcodefactory.com">https://wpcodefactory.com</a> to <a href="https://wpfactory.com">https://wpfactory.com</a>.

= 2.8.5 - 2018-05-08 =
* Dev - General - "Apply Currency Conversion for Cart Fees" option added.
* Dev - General - Exchange Rates Final Price Correction Options - "Apply Rounding and Pretty Price to Shipping Rates" option added.

= 2.8.4 - 2018-04-20 =
* Dev - `[woocommerce_currency_switcher_link_list]` - `no_links` attribute added.
* Dev - `[woocommerce_currency_switcher_link_list]` - `%product_price%` - Checking for single product page disabled.

= 2.8.3 - 2018-03-07 =
* Dev - Advanced - Section added (some options moved from "General" section).
* Dev - Advanced - "Default customer location" option added.
* Dev - Advanced - "Show Flags in Admin Settings Section" option removed.
* Dev - Core - Variation price hash - Minor changes.
* Fix - Flags - Checking file to exist at URL - Removed.
* Dev - Flags - "World" and "N/A" flags added (e.g. for Bitcoin etc.).
* Dev - Flags - Some cryptocurrencies icons added.
* Dev - Flags - `alg_wc_currency_switcher_country_flag_image_url` filter added.
* Dev - Functions - Selector - Code refactoring (`alg_get_country_flag_code()` function added).

= 2.8.2 - 2018-02-21 =
* Dev - Exchange Rates - Server - "Google" server added.

= 2.8.1 - 2018-01-28 =
* Dev - Exchange Rates - "Offset" options added.
* Dev - Code refactoring.
* Fix - Session (WC) functions - Additional checks added.
* Dev - "WC tested up to" added to plugin header.

= 2.8.0 - 2017-12-26 =
* Dev - General - Advanced Options - WooCommerce v3.2 compatibility - Apply Currency Conversion for Fixed Amount Coupons.
* Dev - General - Advanced Options - Show Flags in Admin Settings Section - Defaults to `no` now.
* Dev - General - Advanced Options - "Add Base Currency for Fixed Amount Coupons" option added.
* Dev - General - Advanced Options - "Price Filters to Remove" option added.
* Dev - Exchange Rates - Secondary server option added.
* Dev - Exchange Rates - Precision set 12 decimals (was 6).
* Dev - Exchange Rates - Server - "CoinMarketCap" server added.
* Dev - Exchange Rates - Server - "Coinbase" server added.
* Dev - Functions - Exchange Rates - Code refactoring - `alg_get_currency_exchange_rates_url_response()` function added.
* Dev - Functions - Code refactoring - Exchange rates functions moved to a new `alg-switcher-exchange-rates-functions.php` file.
* Dev - `uninstall.php` added.

= 2.7.0 - 2017-11-12 =
* Dev - General - Advanced Options - "Session Save Path" option added.
* Dev - Exchange Rates - Server - Yahoo finance - URL updated.
* Dev - Exchange Rates - Server - "Fixer.io" server added.
* Dev - Exchange Rates - Server - Default value changed to "European Central Bank".

= 2.6.0 - 2017-10-16 =
* Dev - WooCommerce v3.2 compatibility - Admin settings - `select` settings type fixed.
* Dev - WooCommerce v3.2 compatibility - `change_shipping_price_by_currency()` - Taxes.
* Dev - `[woocommerce_currency_switcher_current_currency_symbol]` and `[woocommerce_currency_switcher_current_currency_code]` shortcodes added.
* Dev - General - Advanced Options - "Price Conversion Method" option added.
* Dev - "Global" flag image added.
* Dev - Settings sections array saved as main class property.

= 2.5.2 - 2017-09-05 =
* Dev - General - Switcher Placement and Format Options - "Link List Switcher - Separator" option added.

= 2.5.1 - 2017-09-03 =
* Fix - Price Formats - "Currency Code" option renamed to "Currency Symbol".
* Dev - Flags - Settings description updated.

= 2.5.0 - 2017-09-02 =
* Dev - `format_price` attribute (defaults to `yes`) added to `alg_convert_price()` function (and `[woocommerce_currency_switcher_convert_price]` shortcode).
* Dev - "Currency Languages (Locales)" section added.
* Dev - General - "Show Flags in Admin Settings Section" option added.
* Dev - General - Settings section restyled.
* Dev - Countries - "Enter Countries as Comma Separated Text" option added.
* Dev - Countries - Section renamed from "Currency Countries (by IP)".
* Dev - Price Formats - "Currency Code" options added.
* Dev - Minor code refactoring.

= 2.4.4 - 2017-08-02 =
* Dev - Flags added.

= 2.4.3 - 2017-07-30 =
* Fix - General - Pretty Price - If "Price Formats" section is enabled - now uses corresponding currency "Number of decimals" instead of shop's default.
* Dev - General - Advanced Options - "Apply Rounding and Pretty Price to Shop's Default Currency" option added.
* Dev - Price Format - Default shop currency added ("Additional currency code position (optional)" as new option; other options are copied from "WooCommerce > Settings > General").
* Dev - Functions - `alg_get_product_price_by_currency()` - Code refactoring.

= 2.4.2 - 2017-07-29 =
* Fix - Skipping price by currency calculation for shop default currency (this fixes the issue with original prices rounded, when rounding is enabled).
* Fix - Skipping price formatting for shop default currency.

= 2.4.1 - 2017-07-28 =
* Dev - `[woocommerce_currency_switcher_convert_price]` shortcode added.

= 2.4.0 - 2017-07-03 =
* Dev - "Price Formats" section added.
* Dev - "Reset settings" option added.
* Dev - Autoloading plugin options.
* Dev - Code cleanup.
* Dev - Plugin link updated from <a href="http://coder.fm">http://coder.fm</a> to <a href="https://wpcodefactory.com">https://wpcodefactory.com</a>.

= 2.3.1 - 2017-05-21 =
* Dev - "Apply Currency Conversion for Fixed Amount Coupons" option added.

= 2.3.0 - 2017-04-14 =
* Dev - WooCommerce v3.x.x compatibility - Price filters.
* Dev - WooCommerce v3.x.x compatibility - Product ID.
* Dev - WooCommerce v3.x.x compatibility - `alg_get_product_display_price()`.
* Dev - WooCommerce v3.x.x compatibility - `wc_get_formatted_variation()`.
* Fix - `alg_get_product_price_html_by_currency()` - variable and grouped products fixed.
* Dev - Functions - `alg_get_exchange_rate_yahoo()` - cURL prioritized over `allow_url_fopen` (`file_get_contents()`).
* Tweak - Per product settings metabox restyled.

= 2.2.4 - 2017-03-11 =
* Dev - General - "Switcher Wrapper" option added.
* Dev - General - Switcher Item Format - `%currency_symbol%` value added.
* Dev - General - Switcher Item Format - `%product_price%` value added.
* Dev - `[woocommerce_currency_switcher_product_price_table]` shortcode added.
* Dev - Code refactoring.

= 2.2.3 - 2017-03-06 =
* Dev - General - "Reposition Page after Currency Switch" option added.

= 2.2.2 - 2017-03-01 =
* Dev - Functions - `alg_get_exchange_rate_yahoo()` - cURL fallback added for Yahoo server rates (in case `allow_url_fopen` is disabled).
* Dev - Functions - `alg_get_exchange_rate_yahoo()` - Time limit (`set_time_limit()`) increased to 10 seconds.
* Dev - Language (POT) file updated.

= 2.2.1 - 2017-02-20 =
* Dev - Free shipping minimum order amount conversion by currency added.
* Dev - Language (POT) file updated.
* Tweak - General - Settings divided in sections.

= 2.2.0 - 2017-02-19 =
* Fix - Rounding and Precision added to variable hash.
* Dev - General - "Make Pretty Price" option added.
* Dev - Exchange Rates - "Exchange Rates Server" option added (and "European Central Bank" server added).
* Dev - JS "grab exchange rate" button changed to AJAX.
* Dev - Autoload set to `no` in `add_option`.
* Dev - Language (POT) file updated.
* Tweak - Link to "All Currencies for WooCommerce" plugin added.

= 2.1.1 - 2016-12-31 =
* Dev - Admin - General - "Advanced: Fix Mini Cart" option added.
* Dev - Admin - General - "Switcher Format" option added.
* Dev - Admin - General - "Advanced: Additional Price Filters" option added.
* Dev - Language (POT) file updated.
* Tweak - Tag added.

= 2.1.0 - 2016-12-14 =
* Dev - Admin - General - "Advanced: Disable on URI" option added.

= 2.0.0 - 2016-12-08 =
* Dev - Admin - Exchange Rates - "Reset All Rates" button added.
* Dev - Admin - Currencies - "Update All Exchange Rates Now" button added.
* Dev - Admin - Currencies - "Auto Generate PayPal Supported Currencies" button added.
* Dev - "Currency Countries (by IP)" section added.
* Fix - `load_plugin_textdomain` moved to constructor.
* Tweak - `get_woocommerce_currency()` replaced with `get_option( 'woocommerce_currency' )`.
* Tweak - Admin - Exchange Rates - Full currency name and number added.
* Tweak - Admin - Exchange Rates - "Grab rate" button restyled.
* Tweak - Admin - Currencies - "Currency (Shop's Default)" added.
* Tweak - Admin - Currencies - Code added to currency name in list.
* Tweak - Tooltip added to custom number admin settings.
* Tweak - Check for Pro rewritten.
* Tweak - Author added.
* Tweak - Major code refactoring.

= 1.0.1 - 2016-08-04 =
* Fix - `custom_number` replaced with `alg_custom_number` - this fixes the issue with "Total Currencies" field duplicating.
* Dev - Language (POT) file added.

= 1.0.0 - 2016-07-24 =
* Initial Release.
