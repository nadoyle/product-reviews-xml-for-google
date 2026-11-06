<?php
/**
 * Plugin Name: Product Reviews XML for Google Merchant Center
 * Description: Generates a Google Merchant Center XML feed for WooCommerce reviews. Supports standard Woo reviews, YITH, Judge.me, Yotpo, and more.
 * Version: 2.0.6
 * Author: TetherTies, LLC
 * Author URI: https://tetherties.com
 * Text Domain: product-reviews-xml-for-google
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) exit;

define('GRF_PATH', plugin_dir_path(__FILE__));
define('GRF_VERSION', '2.0.6');

// Load Core Classes
require_once GRF_PATH . 'includes/class-grf-feed.php';
require_once GRF_PATH . 'includes/abstract-grf-provider.php';

// Load Providers
require_once GRF_PATH . 'includes/providers/class-grf-provider-woo.php'; 
require_once GRF_PATH . 'includes/providers/class-grf-provider-wpcreviews.php'; 
require_once GRF_PATH . 'includes/providers/class-grf-provider-sitereviews.php';

function grf_init() {
    load_plugin_textdomain('product-reviews-xml-for-google', false, dirname(plugin_basename(__FILE__)) . '/languages');
    
    if (is_admin()) {
        require_once GRF_PATH . 'includes/class-grf-admin.php';
        new GRF_Admin();
    }
    new GRF_Feed();
}
add_action('plugins_loaded', 'grf_init');

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'grf_add_plugin_page_settings_link');
function grf_add_plugin_page_settings_link($links) {
    $settings_link = '<a href="options-general.php?page=grf-settings">' . __('Settings', 'product-reviews-xml-for-google') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}

register_activation_hook(__FILE__, 'grf_activation');
function grf_activation() {
    if (!get_option('grf_feed_slug')) update_option('grf_feed_slug', 'google-merchant-center-reviews-xml');
    if (!get_option('grf_provider')) update_option('grf_provider', 'woo');
    if (!get_option('grf_refresh_rate')) update_option('grf_refresh_rate', 604800);
    
    grf_init();
    flush_rewrite_rules();
}

register_deactivation_hook(__FILE__, 'grf_deactivation');
function grf_deactivation() {
    flush_rewrite_rules();
}