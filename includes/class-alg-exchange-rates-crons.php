<?php
/**
 * WooCommerce Currency Switcher Exchange Rates Crons
 *
 * The WooCommerce Currency Switcher Exchange Rates Crons class.
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Tom Anbinder
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Alg_Currency_Switcher_Exchange_Rates_Crons' ) ) :

class Alg_Currency_Switcher_Exchange_Rates_Crons {

	/**
	 * Constructor.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function __construct() {
		$this->update_intervals  = array(
			'minutely'   => __( 'Update Every Minute', 'currency-switcher-woocommerce' ),
			'hourly'     => __( 'Update Hourly', 'currency-switcher-woocommerce' ),
			'twicedaily' => __( 'Update Twice Daily', 'currency-switcher-woocommerce' ),
			'daily'      => __( 'Update Daily', 'currency-switcher-woocommerce' ),
			'weekly'     => __( 'Update Weekly', 'currency-switcher-woocommerce' ),
		);
		add_action( 'init',                           array( $this, 'schedule_the_events' ) );
		add_action( 'admin_init',                     array( $this, 'schedule_the_events' ) );
		add_action( 'alg_update_exchange_rates_hook', array( $this, 'update_the_exchange_rates' ) );
		add_filter( 'cron_schedules',                 array( $this, 'cron_add_custom_intervals' ) );
	}

	/**
	 * On an early action hook, check if the hook is scheduled - if not, schedule it.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function schedule_the_events() {
		$selected_interval = get_option( 'alg_currency_switcher_exchange_rate_update', 'manual' );
		foreach ( $this->update_intervals as $interval => $desc ) {
			$event_hook = 'alg_update_exchange_rates_hook';
			$event_timestamp = wp_next_scheduled( $event_hook, array( $interval ) );
			if ( $selected_interval === $interval ) {
				update_option( 'alg_currency_switcher_exchange_rate_cron_time', $event_timestamp );
			}
			if ( ! $event_timestamp && $selected_interval === $interval ) {
				wp_schedule_event( time(), $selected_interval, $event_hook, array( $selected_interval ) );
			} elseif ( $event_timestamp && $selected_interval !== $interval ) {
				wp_unschedule_event( $event_timestamp, $event_hook, array( $interval ) );
			}
		}
	}

	/**
	 * On the scheduled action hook, run a function.
	 *
	 * @version 2.0.0
	 * @since   1.0.0
	 */
	function update_the_exchange_rates( $interval ) {
		if ( 'yes' === get_option( 'alg_wc_currency_switcher_enabled', 'yes' ) ) {
			if ( 'manual' != get_option( 'alg_currency_switcher_exchange_rate_update', 'manual' ) ) {
				alg_wc_cs_update_the_exchange_rates();
			}
		}
	}

	/**
	 * cron_add_custom_intervals.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function cron_add_custom_intervals( $schedules ) {
		$schedules['weekly'] = array(
			'interval' => 604800,
			'display' => __( 'Once Weekly', 'currency-switcher-woocommerce' )
		);
		$schedules['minutely'] = array(
			'interval' => 60,
			'display' => __( 'Once a Minute', 'currency-switcher-woocommerce' )
		);
		return $schedules;
	}
}

endif;

return new Alg_Currency_Switcher_Exchange_Rates_Crons();
