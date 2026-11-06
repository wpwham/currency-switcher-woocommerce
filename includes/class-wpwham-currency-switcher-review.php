<?php
/**
 * Currency Switcher for WooCommerce - Review Suggestion
 *
 * @version x.x.x
 * @since   x.x.x
 * @author  WP Wham
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WPWham_Currency_Switcher_Review' ) ) :

class WPWham_Currency_Switcher_Review {

	/**
	 * Constructor.
	 *
	 * @version x.x.x
	 * @since   x.x.x
	 */
	function __construct() {
		add_action( 'admin_init', array( $this, 'review_suggestion' ) );
	}

	/**
	 * Review suggestion notice in admin.
	 *
	 * @version x.x.x
	 * @since   x.x.x
	 */
	function review_suggestion() {
		if ( wp_doing_ajax() ) {
			return;
		}
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

        /* Show this on Currency Switcher settings page only?
		if ( ! ( isset( $_GET['page'] ) && $_GET['page'] === 'wc-settings' && isset( $_GET['tab'] ) && $_GET['tab'] === 'alg_wc_currency_switcher' ) ) {
			return;
		}
        */

		$dismissed = get_option( 'wpwham_currency_switcher_review_dismissed' );
		if ( $dismissed === 'permanently' || ( is_numeric( $dismissed ) && $dismissed > time() ) ) {
			return;
		}

		if ( isset( $_GET['wpwham_currency_switcher_dismiss_review'] ) && isset( $_GET['_wpnonce'] ) ) {
			if ( wp_verify_nonce( $_GET['_wpnonce'], 'wpwham_currency_switcher_dismiss' ) ) {
				if ( isset( $_GET['later'] ) ) {
					update_option( 'wpwham_currency_switcher_review_dismissed', time() + ( DAY_IN_SECONDS * 30 ) );
				} else {
					update_option( 'wpwham_currency_switcher_review_dismissed', 'permanently' );
				}
				wp_redirect( remove_query_arg( array( 'wpwham_currency_switcher_dismiss_review', '_wpnonce', 'later' ) ) );
				exit;
			}
		}

		$installed = get_option( 'wpwham_currency_switcher_installed_time' );
		if ( ! $installed ) {
			update_option( 'wpwham_currency_switcher_installed_time', time() );
			return;
		}

		if ( ( $installed + ( DAY_IN_SECONDS * 7 ) ) > time() ) {
			return;
		}

		if ( ! $this->has_minimum_usage() ) {
			return;
		}

		$review_url = 'https://wordpress.org/support/plugin/currency-switcher-woocommerce/reviews/?rate=5#new-post';
		$dismiss_url = wp_nonce_url( add_query_arg( 'wpwham_currency_switcher_dismiss_review', '1' ), 'wpwham_currency_switcher_dismiss' );
		$later_url = add_query_arg( 'later', '1', $dismiss_url );
		?>
		<div class="notice notice-info woocommerce-message">
			<a class="woocommerce-message-close notice-dismiss" href="<?php echo esc_url( $later_url ); ?>"><?php esc_html_e( 'Dismiss', 'currency-switcher-woocommerce' ); ?></a>
			<p><?php esc_html_e( 'Finding Currency Switcher useful? We\'d appreciate a 5-star review!', 'currency-switcher-woocommerce' ); ?></p>
			<p><a href="<?php echo esc_url( $review_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Sure, you deserve it', 'currency-switcher-woocommerce' ); ?></a></p>
			<p><a href="<?php echo esc_url( $later_url ); ?>"><?php esc_html_e( 'Maybe later', 'currency-switcher-woocommerce' ); ?></a></p>
			<p><a href="<?php echo esc_url( $dismiss_url ); ?>"><?php esc_html_e( 'I already did!', 'currency-switcher-woocommerce' ); ?></a></p>
		</div>
		<?php
	}

	/**
	 * Check if an additional currency is enabled and it is not the shop default.
	 *
	 * @version x.x.x
	 * @since   x.x.x
	 */
	function has_minimum_usage() {
		$cached = get_transient( 'wpwham_currency_switcher_has_usage' );
		if ( $cached !== false ) {
			return $cached === 'yes';
		}

		$shop_currency = get_option( 'woocommerce_currency' );
		$has_usage     = false;
		foreach ( alg_get_enabled_currencies( false ) as $currency ) {
			if ( $currency !== $shop_currency ) {
				$has_usage = true;
				break;
			}
		}

		set_transient( 'wpwham_currency_switcher_has_usage', $has_usage ? 'yes' : 'no', DAY_IN_SECONDS );
		return $has_usage;
	}
}

endif;

return new WPWham_Currency_Switcher_Review();
