<?php
/**
 * Plugin Name: Google Reviews Feed (Universal)
 * Description: Generates a Google Merchant Center XML feed for WooCommerce reviews. Supports standard Woo reviews, YITH, Judge.me, Yotpo, and more.
 * Version: 2.0.0
 * Author: TetherTies, LLC
 */

if (!defined('ABSPATH')) exit;

define('GRF_PATH', plugin_dir_path(__FILE__));
define('GRF_VERSION', '2.0.0');

// Load Core Classes
require_once GRF_PATH . 'includes/class-grf-admin.php';
require_once GRF_PATH . 'includes/class-grf-feed.php';
require_once GRF_PATH . 'includes/abstract-grf-provider.php';

// Load Providers
require_once GRF_PATH . 'includes/providers/class-grf-provider-woo.php'; // Standard + YITH + ReviewX + CusRev
require_once GRF_PATH . 'includes/providers/class-grf-provider-wpcreviews.php'; // WP Customer Reviews
require_once GRF_PATH . 'includes/providers/class-grf-provider-sitereviews.php'; // Site Reviews

function grf_init() {
    new GRF_Admin();
    new GRF_Feed();
}
add_action('plugins_loaded', 'grf_init');

// Activation: Set defaults
register_activation_hook(__FILE__, 'grf_activation');
function grf_activation() {
    if (!get_option('grf_feed_slug')) update_option('grf_feed_slug', 'google-reviews-xml');
    if (!get_option('grf_provider')) update_option('grf_provider', 'woo'); // Default to Standard
    grf_init();
    flush_rewrite_rules();
}

register_deactivation_hook(__FILE__, 'grf_deactivation');
function grf_deactivation() {
    flush_rewrite_rules();
}