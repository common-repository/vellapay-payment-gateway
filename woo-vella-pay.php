<?php

/**
 * Plugin Name: VellaPay Payment Gateway
 * Plugin URI: https://wordpress.org/plugins/vellapay-payment-gateway
 * Description: Receive payment on your store with VellaPay
 * Version: 1.0.5
 * Author: Vella
 * Author URI: https://vella.finance/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * WC requires at least: 5.2 or higher
 * WC tested up to: 6.0
 * Text Domain: woo-vella-pay
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
	exit;
}

define('WC_VELLA_PAY_MAIN_FILE', __FILE__);
define('WC_VELLA_PAY_URL', untrailingslashit(plugins_url('/', __FILE__)));

define('WC_VELLA_PAY_VERSION', '1.0.2');

/**
 * Initialize Vella payment gateway.
 */
function vch_wc_vella_init()
{

	if (!class_exists('WC_Payment_Gateway')) {
		add_action('admin_notices', 'vch_wc_vella_wc_missing_notice');
		return;
	}

	add_action('admin_notices', 'vch_wc_vella_testmode_notice');

	require_once dirname(__FILE__) . '/includes/class-wc-gateway-vella.php';

	add_filter('woocommerce_payment_gateways', 'vch_wc_add_vella_gateway', 99);

	add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'vch_woo_vella_plugin_action_links');
}
add_action('plugins_loaded', 'vch_wc_vella_init', 99);

/**
 * Add Settings link to the plugin entry in the plugins menu.
 *
 * @param array $links Plugin action links.
 *
 * @return array
 **/
function vch_woo_vella_plugin_action_links($links)
{

	$settings_link = array(
		'settings' => '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=vella') . '" title="' . __('View VellaPay Settings', 'woo-vella-pay') . '">' . __('Settings', 'woo-vella-pay') . '</a>',
	);

	return array_merge($settings_link, $links);
}

/**
 * Add Vella Gateway to WooCommerce.
 *
 * @param array $methods WooCommerce payment gateways methods.
 *
 * @return array
 */
function vch_wc_add_vella_gateway($methods)
{

	$methods[] = 'WC_Gateway_Vella';
	return $methods;
}

/**
 * Display a notice if WooCommerce is not installed
 */
function vch_wc_vella_wc_missing_notice()
{
	echo '<div class="error"><p><strong>' . sprintf(__('Vella requires WooCommerce to be installed and active. Click %s to install WooCommerce.', 'woo-vella-pay'), '<a href="' . admin_url('plugin-install.php?tab=plugin-information&plugin=woocommerce&TB_iframe=true&width=772&height=539') . '" class="thickbox open-plugin-details-modal">here</a>') . '</strong></p></div>';
}

/**
 * Display the test mode notice.
 **/
function vch_wc_vella_testmode_notice()
{

	if (!current_user_can('manage_options')) {
		return;
	}

	$vella_settings = get_option('woocommerce_vella_settings');
	$test_mode         = isset($vella_settings['testmode']) ? $vella_settings['testmode'] : '';

	if ('yes' === $test_mode) {
		/* translators: 1. Vella settings page URL link. */
		echo '<div class="error"><p>' . sprintf(__('Vella test mode is still enabled, Click <strong><a href="%s">here</a></strong> to disable it when you want to start accepting live payment on your site.', 'woo-vella-pay'), esc_url(admin_url('admin.php?page=wc-settings&tab=checkout&section=vella'))) . '</p></div>';
	}
}
