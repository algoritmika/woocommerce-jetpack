<?php
/**
 * Booster for WooCommerce - Core - Admin
 *
 * @version 3.2.5
 * @since   3.2.4
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WCJ_Admin' ) ) :

class WCJ_Admin {

	/**
	 * Constructor.
	 *
	 * @version 3.2.4
	 * @since   3.2.4
	 */
	function __construct() {
		if ( is_admin() ) {
			add_filter( 'booster_message',                                      'wcj_get_plus_message', 100, 3 );
			add_filter( 'woocommerce_get_settings_pages',                            array( $this, 'add_wcj_settings_tab' ), 1 );
			add_filter( 'plugin_action_links_' . plugin_basename( WCJ_PLUGIN_FILE ), array( $this, 'action_links' ) );
			add_action( 'admin_menu',                                                array( $this, 'booster_menu' ), 100 );
			add_filter( 'admin_footer_text',                                         array( $this, 'admin_footer_text' ), 2 );
			if ( 'woocommerce-jetpack.php' === basename( WCJ_PLUGIN_FILE ) ) {
				add_action( 'admin_notices',                                         array( $this, 'check_plus_version' ) );
			}
		}
	}

	/**
	 * check_plus_version.
	 *
	 * @version 3.2.5
	 * @since   2.5.9
	 * @todo    expand "Please upgrade ..." message (i.e. "... you can downgrade to Booster v3.2.4 or earlier from ...")
	 */
	function check_plus_version() {
		if ( ! is_admin() ) {
			return;
		}
		// Check if Plus is installed and activated
		$is_deprecated_plus_active = false;
		$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) );
		if ( is_multisite() ) {
			$active_plugins = array_merge( $active_plugins, array_keys( get_site_option( 'active_sitewide_plugins', array() ) ) );
		}
		foreach ( $active_plugins as $active_plugin ) {
			$active_plugin = explode( '/', $active_plugin );
			if ( isset( $active_plugin[1] ) ) {
				if ( 'booster-plus-for-woocommerce.php' === $active_plugin[1] ) {
					return;
				} elseif ( 'woocommerce-jetpack-plus.php' === $active_plugin[1] || 'woocommerce-booster-plus.php' === $active_plugin[1] ) {
					$is_deprecated_plus_active = true;
				}
			}
		}
		// Check Plus version
		if ( $is_deprecated_plus_active ) {
			$class   = 'notice notice-error';
			$message = sprintf(
				__( 'Please upgrade <strong>Booster Plus for WooCommerce</strong> plugin. Visit <a target="_blank" href="%s">your account page</a> on booster.io to download the latest Booster Plus version.', 'woocommerce-jetpack' ),
				'https://booster.io/my-account/?utm_source=plus_update'
			);
			echo '<div class="' . $class . '"><p>' . $message . '</p></div>';
		}
	}

	/**
	 * admin_footer_text
	 *
	 * @version 2.9.0
	 */
	function admin_footer_text( $footer_text ) {
		if ( isset( $_GET['page'] ) ) {
			if ( 'wcj-tools' === $_GET['page'] || ( 'wc-settings' === $_GET['page'] && isset( $_GET['tab'] ) && 'jetpack' === $_GET['tab'] ) ) {
				$rocket_icons = wcj_get_5_rocket_image();
				$rating_link = '<a href="https://wordpress.org/support/plugin/woocommerce-jetpack/reviews/?rate=5#new-post" target="_blank">' . $rocket_icons . '</a>';
				return sprintf(
					__( 'If you like <strong>Booster for WooCommerce</strong> please leave us a %s rating. Thank you, we couldn\'t have done it without you!', 'woocommerce-jetpack' ),
					$rating_link
				);
			}
		}
		return $footer_text;
	}

	/**
	 * Add menu item
	 *
	 * @version 2.9.0
	 */
	function booster_menu() {
		add_submenu_page(
			'woocommerce',
			__( 'Booster for WooCommerce', 'woocommerce-jetpack' ),
			__( 'Booster Settings', 'woocommerce-jetpack' ) ,
			'manage_woocommerce',
			'admin.php?page=wc-settings&tab=jetpack'
		);
	}

	/**
	 * Show action links on the plugin screen
	 *
	 * @version 3.2.4
	 * @param   mixed $links
	 * @return  array
	 */
	function action_links( $links ) {
		$custom_links = array(
			'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=jetpack' ) . '">' . __( 'Settings', 'woocommerce' ) . '</a>',
			'<a href="' . esc_url( 'https://booster.io/' )                      . '">' . __( 'Docs', 'woocommerce-jetpack' ) . '</a>',
		);
		if ( 1 === apply_filters( 'booster_option', 1, '' ) ) {
			$custom_links[] = '<a href="' . esc_url( 'https://booster.io/plus/' ) . '">' . __( 'Unlock all', 'woocommerce-jetpack' ) . '</a>';
		}
		return array_merge( $custom_links, $links );
	}

	/**
	 * Add Jetpack settings tab to WooCommerce settings.
	 *
	 * @version 3.2.4
	 */
	function add_wcj_settings_tab( $settings ) {
		$_settings = include( WCJ_PLUGIN_PATH . '/includes/admin/class-wc-settings-jetpack.php' );
		$_settings->add_module_statuses( WCJ()->module_statuses );
		$settings[] = $_settings;
		return $settings;
	}

}

endif;

return new WCJ_Admin();