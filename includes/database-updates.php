<?php
/* ==============================================================================
 * DATABASE UPDATES
 * ==============================================================================
 *
 * History:
 * 2024-08-29 -- DB v2, for Currency Switcher versions < 2.15.2
 */
function wpwham_currency_switcher_db_update() {
	global $wpdb;
	
	$db_version = get_option( 'wpwham_currency_switcher_dbversion' );
	
	if ( $db_version && $db_version >= WPWHAM_CURRENCY_SWITCHER_DBVERSION ) {
		// all good
		return;
	}
	
	// upgrade from v1 to v2
	if ( ! $db_version || $db_version < 2 ) {
		$alg_version = get_option( 'alg_currency_switcher_version' );
		if ( $alg_version && $alg_version > 0 ) {
			update_option( 'wpwham_currency_switcher_version', 'legacy' );
		} else {
			update_option( 'wpwham_currency_switcher_version', WPWHAM_CURRENCY_SWITCHER_VERSION );
			update_option( 'alg_currency_switcher_placement', array( 'single_page_after_price_select' ) );
			update_option( 'alg_wc_currency_switcher_flags_enabled', 'yes' );
		}
	}
	
	// Done
	update_option( 'wpwham_currency_switcher_dbversion', WPWHAM_CURRENCY_SWITCHER_DBVERSION );
	
}
wpwham_currency_switcher_db_update();
