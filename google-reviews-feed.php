<?php
/**
 * Plugin Name: Google Merchant Center Reviews Feed
 * Description: Generates a Google Product Reviews XML feed from WooCommerce reviews with support for GTIN, MPN, and Brands.
 * Version: 1.1.0
 * Author: TetherTies, LLC
 */

if (!defined('ABSPATH')) exit;

define('GRF_PATH', plugin_dir_path(__FILE__));
define('GRF_VERSION', '1.0.0');

// Load Classes
require_once GRF_PATH . 'includes/class-grf-admin.php';
require_once GRF_PATH . 'includes/class-grf-feed.php';
require_once GRF_PATH . 'includes/providers/class-grf-provider-wp-comments.php';

// Initialize
function grf_init() {
    new GRF_Admin();
    new GRF_Feed();
}
add_action('plugins_loaded', 'grf_init');

// Activation Hook to Flush Rewrites
register_activation_hook(__FILE__, 'grf_activation');
function grf_activation() {
    // Set defaults if not present
    if (!get_option('grf_feed_slug')) update_option('grf_feed_slug', 'google-reviews-xml');
    if (!get_option('grf_refresh_rate')) update_option('grf_refresh_rate', 86400); // Daily
    
    // Trigger rewrite flush
    grf_init();
    flush_rewrite_rules();
}

// Deactivation Hook
register_deactivation_hook(__FILE__, 'grf_deactivation');
function grf_deactivation() {
    flush_rewrite_rules();
}