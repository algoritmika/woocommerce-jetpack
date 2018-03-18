<?php
/**
 * Booster for WooCommerce - Functions - Shipping
 *
 * @version 3.4.6
 * @since   3.4.6
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'wcj_get_customer_shipping_matching_zone_id' ) ) {
	/**
	 * wcj_get_customer_shipping_matching_zone_id.
	 *
	 * @version 3.4.6
	 * @since   3.4.6
	 * @todo    (maybe) move to `wcj-functions-users.php`
	 * @todo    (maybe) add `wcj_get_customer_shipping_destination()` function
	 */
	function wcj_get_customer_shipping_matching_zone_id() {
		$package = false;
		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			if ( '' != ( $meta = get_user_meta( $current_user->ID, 'shipping_country', true ) ) ) {
				$package['destination']['country']  = $meta;
				$package['destination']['state']    = get_user_meta( $current_user->ID, 'shipping_state', true );
				$package['destination']['postcode'] = '';
			}
		}
		if ( false === $package ) {
			$package['destination'] = wc_get_customer_default_location();
			$package['destination']['postcode'] = '';
		}
		$data_store = WC_Data_Store::load( 'shipping-zone' );
		return $data_store->get_zone_id_from_package( $package );
	}
}

if ( ! function_exists( 'wcj_get_product_shipping_class_term_id' ) ) {
	/**
	 * wcj_get_product_shipping_class_term_id.
	 *
	 * @version 3.4.6
	 * @since   3.4.6
	 * @todo    (maybe) move to `wcj-functions-products.php`
	 */
	function wcj_get_product_shipping_class_term_id( $_product ) {
		$product_shipping_class = $_product->get_shipping_class();
		if ( '' != $product_shipping_class ) {
			foreach ( WC()->shipping->get_shipping_classes() as $shipping_class ) {
				if ( $product_shipping_class === $shipping_class->slug ) {
					return $shipping_class->term_id;
				}
			}
		}
		return 0;
	}
}

if ( ! function_exists( 'wcj_get_shipping_classes' ) ) {
	/**
	 * wcj_get_shipping_classes.
	 *
	 * @version 3.4.6
	 * @since   3.4.6
	 */
	function wcj_get_shipping_classes( $include_empty_shipping_class = true ) {
		$shipping_classes = WC()->shipping->get_shipping_classes();
		$shipping_classes_data = array();
		foreach ( $shipping_classes as $shipping_class ) {
			$shipping_classes_data[ $shipping_class->term_id ] = $shipping_class->name;
		}
		if ( $include_empty_shipping_class ) {
			$shipping_classes_data[0] = __( 'No shipping class', 'woocommerce' );
		}
		return $shipping_classes_data;
	}
}

if ( ! function_exists( 'wcj_get_shipping_methods' ) ) {
	/**
	 * wcj_get_shipping_methods.
	 *
	 * @version 3.4.6
	 * @since   3.4.6
	 */
	function wcj_get_shipping_methods() {
		$shipping_methods = array();
		foreach ( WC()->shipping()->load_shipping_methods() as $method ) {
			$shipping_methods[ $method->id ] = $method->get_method_title();
		}
		return $shipping_methods;
	}
}

if ( ! function_exists( 'wcj_get_shipping_zones' ) ) {
	/**
	 * wcj_get_shipping_zones.
	 *
	 * @version 3.4.6
	 * @since   3.4.6
	 */
	function wcj_get_shipping_zones( $include_empty_zone = true ) {
		$zones = WC_Shipping_Zones::get_zones();
		if ( $include_empty_zone ) {
			$zone                                                = new WC_Shipping_Zone( 0 );
			$zones[ $zone->get_id() ]                            = $zone->get_data();
			$zones[ $zone->get_id() ]['zone_id']                 = $zone->get_id();
			$zones[ $zone->get_id() ]['formatted_zone_location'] = $zone->get_formatted_location();
			$zones[ $zone->get_id() ]['shipping_methods']        = $zone->get_shipping_methods();
		}
		return $zones;
	}
}

if ( ! function_exists( 'wcj_get_shipping_methods_instances' ) ) {
	/**
	 * wcj_get_shipping_methods_instances.
	 *
	 * @version 3.4.6
	 * @since   3.4.6
	 */
	function wcj_get_shipping_methods_instances( $full_data = false ) {
		$shipping_methods = array();
		foreach ( wcj_get_shipping_zones() as $zone_id => $zone_data ) {
			foreach ( $zone_data['shipping_methods'] as $shipping_method ) {
				if ( $full_data ) {
					$shipping_methods[ $shipping_method->instance_id ] = array(
						'zone_id'                     => $zone_id,
						'zone_name'                   => $zone_data['zone_name'],
						'formatted_zone_location'     => $zone_data['formatted_zone_location'],
						'shipping_method_title'       => $shipping_method->title,
						'shipping_method_id'          => $shipping_method->id,
						'shipping_method_instance_id' => $shipping_method->instance_id,
					);
				} else {
					$shipping_methods[ $shipping_method->instance_id ] = $zone_data['zone_name'] . ': ' . $shipping_method->title;
				}
			}
		}
		return $shipping_methods;
	}
}

if ( ! function_exists( 'wcj_get_woocommerce_package_rates_module_filter_priority' ) ) {
	/**
	 * wcj_get_woocommerce_package_rates_module_filter_priority.
	 *
	 * @version 3.2.4
	 * @since   3.2.4
	 * @todo    add `shipping_by_order_amount` module
	 */
	function wcj_get_woocommerce_package_rates_module_filter_priority( $module_id ) {
		$modules_priorities = array(
			'shipping_options_hide_free_shipping'  => PHP_INT_MAX,
			'shipping_by_products'                 => PHP_INT_MAX - 100,
			'shipping_by_user_role'                => PHP_INT_MAX - 100,
		);
		return ( 0 != ( $priority = get_option( 'wcj_' . $module_id . '_filter_priority', 0 ) ) ?
			$priority :
			( isset( $modules_priorities[ $module_id ] ) ? $modules_priorities[ $module_id ] : PHP_INT_MAX )
		);
	}
}

if ( ! function_exists( 'wcj_get_left_to_free_shipping' ) ) {
	/*
	 * wcj_get_left_to_free_shipping.
	 *
	 * @version 3.4.0
	 * @since   2.4.4
	 * @return  string
	 */
	function wcj_get_left_to_free_shipping( $content, $multiply_by = 1 ) {
		if ( function_exists( 'WC' ) && ( WC()->shipping ) && ( $packages = WC()->shipping->get_packages() ) ) {
			foreach ( $packages as $i => $package ) {
				$available_shipping_methods = $package['rates'];
				foreach ( $available_shipping_methods as $available_shipping_method ) {
					$method_id = ( WCJ_IS_WC_VERSION_BELOW_3_2_0 ? $available_shipping_method->method_id : $available_shipping_method->get_method_id() );
					if ( 'free_shipping' === $method_id ) {
						return do_shortcode( get_option( 'wcj_shipping_left_to_free_info_content_reached', __( 'You have Free delivery', 'woocommerce-jetpack' ) ) );
					}
				}
			}
		}
		if ( '' == $content ) {
			$content = __( '%left_to_free% left to free shipping', 'woocommerce-jetpack' );
		}
		$min_free_shipping_amount = 0;
		if ( version_compare( WCJ_WC_VERSION, '2.6.0', '<' ) ) {
			$free_shipping = new WC_Shipping_Free_Shipping();
			if ( in_array( $free_shipping->requires, array( 'min_amount', 'either', 'both' ) ) ) {
				$min_free_shipping_amount = $free_shipping->min_amount;
			}
		} else {
			$legacy_free_shipping = new WC_Shipping_Legacy_Free_Shipping();
			if ( 'yes' === $legacy_free_shipping->enabled ) {
				if ( in_array( $legacy_free_shipping->requires, array( 'min_amount', 'either', 'both' ) ) ) {
					$min_free_shipping_amount = $legacy_free_shipping->min_amount;
				}
			}
			if ( 0 == $min_free_shipping_amount ) {
				if ( function_exists( 'WC' ) && ( $wc_shipping = WC()->shipping ) && ( $wc_cart = WC()->cart ) ) {
					if ( $wc_shipping->enabled ) {
						if ( $packages = $wc_cart->get_shipping_packages() ) {
							$shipping_methods = $wc_shipping->load_shipping_methods( $packages[0] );
							foreach ( $shipping_methods as $shipping_method ) {
								if ( 'yes' === $shipping_method->enabled && 0 != $shipping_method->instance_id ) {
									if ( 'WC_Shipping_Free_Shipping' === get_class( $shipping_method ) ) {
										if ( in_array( $shipping_method->requires, array( 'min_amount', 'either', 'both' ) ) ) {
											$min_free_shipping_amount = $shipping_method->min_amount;
											break;
										}
									}
								}
							}
						}
					}
				}
			}
		}
		if ( 0 != $min_free_shipping_amount ) {
			if ( isset( WC()->cart->cart_contents_total ) ) {
				$cart_taxes = ( WCJ_IS_WC_VERSION_BELOW_3_2_0 ? WC()->cart->taxes : WC()->cart->get_cart_contents_taxes() );
				$total = ( WC()->cart->prices_include_tax ) ? WC()->cart->cart_contents_total + array_sum( $cart_taxes ) : WC()->cart->cart_contents_total;
				if ( $total >= $min_free_shipping_amount ) {
					return do_shortcode( get_option( 'wcj_shipping_left_to_free_info_content_reached', __( 'You have Free delivery', 'woocommerce-jetpack' ) ) );
				} else {
					$content = str_replace( '%left_to_free%',             wc_price( ( $min_free_shipping_amount - $total ) * $multiply_by ), $content );
					$content = str_replace( '%free_shipping_min_amount%', wc_price( ( $min_free_shipping_amount )          * $multiply_by ), $content );
					return $content;
				}
			}
		}
	}
}
