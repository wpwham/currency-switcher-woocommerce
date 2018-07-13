<?php
/**
 * Currency Switcher - Uninstall
 *
 * @version 2.8.0
 * @since   2.8.0
 * @author  Tom Anbinder
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

global $wpdb;

$plugin_meta = $wpdb->get_results( "SELECT * FROM $wpdb->postmeta WHERE meta_key LIKE '_alg_currency_switcher_per_product_%'" );
foreach( $plugin_meta as $meta ) {
	delete_post_meta( $meta->post_id, $meta->meta_key );
}

$plugin_options = $wpdb->get_results( "SELECT option_name FROM $wpdb->options WHERE option_name LIKE 'alg_wc_currency_switcher_%' OR option_name LIKE 'alg_currency_switcher_%'" );
foreach( $plugin_options as $option ) {
	delete_option( $option->option_name );
	delete_site_option( $option->option_name );
}
