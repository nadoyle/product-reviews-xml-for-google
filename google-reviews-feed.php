<?php
/**
 * Plugin Name: Google Merchant Center Reviews XML generator
 * Description: Generates a Google Merchant Center XML feed for WooCommerce reviews. Supports standard Woo reviews, YITH, Judge.me, Yotpo, and more.
 * Version: 1.4.0
 * Author: TetherTies, LLC
 * Author URI: https://tetherties.com
 */

if (!defined('ABSPATH')) exit;

define('GRF_PATH', plugin_dir_path(__FILE__));
define('GRF_VERSION', '1.4.0');

// Load Core Classes
require_once GRF_PATH . 'includes/class-grf-admin.php';
require_once GRF_PATH . 'includes/class-grf-feed.php';
require_once GRF_PATH . 'includes/abstract-grf-provider.php';

// Load Providers
require_once GRF_PATH . 'includes/providers/class-grf-provider-woo.php'; 
require_once GRF_PATH . 'includes/providers/class-grf-provider-wpcreviews.php'; 
require_once GRF_PATH . 'includes/providers/class-grf-provider-sitereviews.php';

// Initialize Plugin
function grf_init() {
    new GRF_Admin();
    new GRF_Feed();
}
add_action('plugins_loaded', 'grf_init');

// Add "Settings" Link to Plugins Page
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'grf_add_plugin_page_settings_link');
function grf_add_plugin_page_settings_link($links) {
    $settings_link = '<a href="options-general.php?page=grf-settings">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}

// Activation Hook
register_activation_hook(__FILE__, 'grf_activation');
function grf_activation() {
    if (!get_option('grf_feed_slug')) update_option('grf_feed_slug', 'google-reviews-xml');
    if (!get_option('grf_provider')) update_option('grf_provider', 'woo');
    grf_init();
    flush_rewrite_rules();
}

// Deactivation Hook
register_deactivation_hook(__FILE__, 'grf_deactivation');
function grf_deactivation() {
    flush_rewrite_rules();
}