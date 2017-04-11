<?php
/**
 * WooCommerce Jetpack Exchange Rates Crons
 *
 * The WooCommerce Jetpack Exchange Rates Crons class.
 *
 * @version 2.7.0
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WCJ_Exchange_Rates_Crons' ) ) :

class WCJ_Exchange_Rates_Crons {

	/**
	 * Constructor.
	 *
	 * @version 2.7.0
	 */
	public function __construct() {
		$this->update_intervals  = array(
			'minutely'   => __( 'Update Every Minute', 'woocommerce-jetpack' ),
			'hourly'     => __( 'Update Hourly', 'woocommerce-jetpack' ),
			'twicedaily' => __( 'Update Twice Daily', 'woocommerce-jetpack' ),
			'daily'      => __( 'Update Daily', 'woocommerce-jetpack' ),
			'weekly'     => __( 'Update Weekly', 'woocommerce-jetpack' ),
		);
		add_action( 'init',                            array( $this, 'schedule_the_events' ) );
		add_action( 'admin_init',                      array( $this, 'schedule_the_events' ) );
		add_action( 'auto_update_exchange_rates_hook', array( $this, 'update_the_exchange_rates' ) );
		add_filter( 'cron_schedules',                  array( $this, 'cron_add_custom_intervals' ) );
	}

	/**
	 * On an early action hook, check if the hook is scheduled - if not, schedule it.
	 *
	 * @version 2.5.5
	 */
	function schedule_the_events() {
		$selected_interval = get_option( 'wcj_currency_exchange_rates_auto', 'daily' );
		foreach ( $this->update_intervals as $interval => $desc ) {
			$event_hook = 'auto_update_exchange_rates_hook';
			$event_timestamp = wp_next_scheduled( $event_hook, array( $interval ) );
			if ( $selected_interval === $interval ) {
				update_option( 'wcj_currency_exchange_rate_cron_time', $event_timestamp );
			}
			if ( ! $event_timestamp && $selected_interval === $interval ) {
				wp_schedule_event( time(), $selected_interval, $event_hook, array( $selected_interval ) );
			} elseif ( $event_timestamp && $selected_interval !== $interval ) {
				wp_unschedule_event( $event_timestamp, $event_hook, array( $interval ) );
			}
		}
	}

	/**
	 * get_currency_pair.
	 *
	 * @version 2.3.0
	 * @since   2.3.0
	 */
	function get_currency_pair( $currency_pairs, $currency_to, $option_name ) {

		foreach ( $currency_pairs as $k => $currency_pair ) {
			if ( $currency_pair['currency_to'] == $currency_to ) {
				$currency_pairs[ $k ]['option_name'][] = $option_name;
				return $currency_pairs;
			}
		}
		$currency_pairs[] = array(
			'currency_from' => get_option( 'woocommerce_currency' ),
			'currency_to'   => $currency_to,
			'option_name'   => array( $option_name, ),
		);
		return $currency_pairs;
	}

	/**
	 * On the scheduled action hook, run a function.
	 *
	 * @version 2.7.0
	 */
	function update_the_exchange_rates( $interval ) {

		$currency_pairs[] = array();

		if ( wcj_is_module_enabled( 'price_by_country' ) ) {
			// Currency Pairs - Preparation - Price by Country
			if ( 'manual' != apply_filters( 'booster_get_option', 'manual', get_option( 'wcj_price_by_country_auto_exchange_rates', 'manual' ) ) ) {
				for ( $i = 1; $i <= apply_filters( 'booster_get_option', 1, get_option( 'wcj_price_by_country_total_groups_number', 1 ) ); $i++ ) {
					$currency_to = get_option( 'wcj_price_by_country_exchange_rate_currency_group_' . $i );
					$currency_pairs = $this->get_currency_pair( $currency_pairs, $currency_to, 'wcj_price_by_country_exchange_rate_group_' . $i );
				}
			}
		}

		if ( wcj_is_module_enabled( 'multicurrency' ) ) {
			// Currency Pairs - Preparation - Multicurrency
			if ( 'manual' != apply_filters( 'booster_get_option', 'manual', get_option( 'wcj_multicurrency_exchange_rate_update_auto', 'manual' ) ) ) {
				for ( $i = 1; $i <= apply_filters( 'booster_get_option', 2, get_option( 'wcj_multicurrency_total_number', 2 ) ); $i++ ) {
					$currency_to = get_option( 'wcj_multicurrency_currency_' . $i );
					$currency_pairs = $this->get_currency_pair( $currency_pairs, $currency_to, 'wcj_multicurrency_exchange_rate_' . $i );
				}
			}
		}

		if ( wcj_is_module_enabled( 'multicurrency_base_price' ) ) {
			// Currency Pairs - Preparation - Multicurrency Product Base Price
			if ( 'manual' != apply_filters( 'booster_get_option', 'manual', get_option( 'wcj_multicurrency_base_price_exchange_rate_update', 'manual' ) ) ) {
				for ( $i = 1; $i <= apply_filters( 'booster_get_option', 1, get_option( 'wcj_multicurrency_base_price_total_number', 1 ) ); $i++ ) {
					$currency_to = get_option( 'wcj_multicurrency_base_price_currency_' . $i );
					$currency_pairs = $this->get_currency_pair( $currency_pairs, $currency_to, 'wcj_multicurrency_base_price_exchange_rate_' . $i );
				}
			}
		}

		if ( wcj_is_module_enabled( 'currency_per_product' ) ) {
			// Currency Pairs - Preparation - Currency per Product
			if ( 'manual' != apply_filters( 'booster_get_option', 'manual', get_option( 'wcj_currency_per_product_exchange_rate_update', 'manual' ) ) ) {
				for ( $i = 1; $i <= apply_filters( 'booster_get_option', 1, get_option( 'wcj_currency_per_product_total_number', 1 ) ); $i++ ) {
					$currency_to = get_option( 'wcj_currency_per_product_currency_' . $i );
					$currency_pairs = $this->get_currency_pair( $currency_pairs, $currency_to, 'wcj_currency_per_product_exchange_rate_' . $i );
				}
			}
		}

		if ( wcj_is_module_enabled( 'payment_gateways_currency' ) ) {
			// Currency Pairs - Preparation - Gateway Currency
			if ( 'manual' != apply_filters( 'booster_get_option', 'manual', get_option( 'wcj_gateways_currency_exchange_rate_update_auto', 'manual' ) ) ) {
				global $woocommerce;
				$available_gateways = $woocommerce->payment_gateways->payment_gateways();
				foreach ( $available_gateways as $key => $gateway ) {
					$currency_to = get_option( 'wcj_gateways_currency_' . $key );
					if ( 'no_changes' != $currency_to ) {
						$currency_pairs = $this->get_currency_pair( $currency_pairs, $currency_to, 'wcj_gateways_currency_exchange_rate_' . $key );
					}
				}
			}
		}

		// Currency Pairs - Final
		$rate_offset_percent = get_option( 'wcj_currency_exchange_rates_offset_percent', 0 );
		if ( 0 != $rate_offset_percent ) {
			$rate_offset_percent = 1 + ( $rate_offset_percent / 100 );
		}
		$rate_offset_fixed = get_option( 'wcj_currency_exchange_rates_offset_fixed', 0 );
		foreach ( $currency_pairs as $currency_pair ) {
			$currency_from = $currency_pair['currency_from'];
			$currency_to   = $currency_pair['currency_to'];
			$the_rate = alg_get_exchange_rate( $currency_from, $currency_to );
			if ( 0 != $the_rate ) {
				if ( 0 != $rate_offset_percent ) {
					$the_rate = round( $the_rate * $rate_offset_percent, 6 );
				}
				if ( 0 != $rate_offset_fixed ) {
					$the_rate = $the_rate + $rate_offset_fixed;
				}
				if ( $currency_from != $currency_to ) {
					foreach ( $currency_pair['option_name'] as $option_name ) {
						update_option( $option_name, $the_rate );
					}
					$field_id = 'wcj_currency_exchange_rates_' . sanitize_title( $currency_from . $currency_to );
					update_option( $field_id, $the_rate );
					$result_message = __( 'Cron job: exchange rates successfully updated', 'woocommerce-jetpack' );
				} else {
					$result_message = __( 'Cron job: exchange rates not updated, as currency_from == currency_to', 'woocommerce-jetpack' );
				}
			} else {
				$result_message = __( 'Cron job: exchange rates update failed', 'woocommerce-jetpack' );
			}
			/* if ( 'yes' === get_option( 'wcj_currency_exchange_logging_enabled', 'no' ) ) {
				wcj_log( $result_message . ': ' . $currency_from . $currency_to . ': ' . $the_rate . ': ' . 'update_the_exchange_rates: ' . $interval );
			} */
		}

		if ( wcj_is_module_enabled( 'price_by_country' ) ) {
			if ( 'yes' === get_option( 'wcj_price_by_country_price_filter_widget_support_enabled', 'no' ) ) {
				wcj_update_products_price_by_country();
			}
		}
	}

	/**
	 * cron_add_custom_intervals.
	 */
	function cron_add_custom_intervals( $schedules ) {
		$schedules['weekly'] = array(
			'interval' => 604800,
			'display' => __( 'Once Weekly', 'woocommerce-jetpack' )
		);
		$schedules['minutely'] = array(
			'interval' => 60,
			'display' => __( 'Once a Minute', 'woocommerce-jetpack' )
		);
		return $schedules;
	}
}

endif;

return new WCJ_Exchange_Rates_Crons();
