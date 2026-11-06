<?php
/**
 * Plugin Name: Product Reviews XML for Google Merchant Center
 * Description: Generates a Google Merchant Center XML feed for WooCommerce reviews. Supports standard Woo reviews, YITH, Judge.me, Yotpo, and more.
 * Version: 2.0.8
 * Author: TetherTies, LLC
 * Author URI: https://tetherties.com
 * Text Domain: product-reviews-xml-for-google
 * Domain Path: /languages
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * Tested up to: 6.9
 * Requires Plugins: woocommerce
 */

if (!defined('ABSPATH')) exit;

define('PRXML_PATH', plugin_dir_path(__FILE__));
define('PRXML_VERSION', '2.0.8');

/**
 * Check if WooCommerce is active
 */
function prxml_check_woocommerce() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', 'prxml_woocommerce_missing_notice');
        return false;
    }
    return true;
}

/**
 * Display admin notice if WooCommerce is not active
 */
function prxml_woocommerce_missing_notice() {
    ?>
    <div class="notice notice-error">
        <p>
            <?php
            echo wp_kses_post(
                sprintf(
                    /* translators: %s: WooCommerce plugin name */
                    __('<strong>Product Reviews XML for Google Merchant Center</strong> requires %s to be installed and activated.', 'product-reviews-xml-for-google'),
                    '<a href="' . esc_url(admin_url('plugin-install.php?s=woocommerce&tab=search&type=term')) . '">WooCommerce</a>'
                )
            );
            ?>
        </p>
    </div>
    <?php
}

/**
 * Initialize plugin
 */
function prxml_init() {
    if (!prxml_check_woocommerce()) {
        return;
    }

    require_once PRXML_PATH . 'includes/class-prxml-feed.php';
    require_once PRXML_PATH . 'includes/abstract-prxml-provider.php';
    require_once PRXML_PATH . 'includes/providers/class-prxml-provider-woo.php'; 
    require_once PRXML_PATH . 'includes/providers/class-prxml-provider-wpcreviews.php'; 
    require_once PRXML_PATH . 'includes/providers/class-prxml-provider-sitereviews.php';

    if (is_admin()) {
        require_once PRXML_PATH . 'includes/class-prxml-admin.php';
        new PRXML_Admin();
    }
    new PRXML_Feed();
}
add_action('plugins_loaded', 'prxml_init');

/**
 * Add settings link on plugin page
 */
function prxml_add_plugin_page_settings_link($links) {
    $settings_link = '<a href="' . esc_url(admin_url('options-general.php?page=prxml-settings')) . '">' . esc_html__('Settings', 'product-reviews-xml-for-google') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'prxml_add_plugin_page_settings_link');

/**
 * Plugin activation
 */
function prxml_activation() {
    if (!get_option('prxml_feed_slug')) {
        update_option('prxml_feed_slug', 'google-merchant-center-reviews-xml');
    }
    if (!get_option('prxml_provider')) {
        update_option('prxml_provider', 'woo');
    }
    if (!get_option('prxml_refresh_rate')) {
        update_option('prxml_refresh_rate', 604800);
    }
    
    // Only flush rewrites if WooCommerce is active
    if (class_exists('WooCommerce')) {
        require_once PRXML_PATH . 'includes/class-prxml-feed.php';
        $feed = new PRXML_Feed();
        $feed->rewrite();
        flush_rewrite_rules();
    }
}
register_activation_hook(__FILE__, 'prxml_activation');

/**
 * Plugin deactivation
 */
function prxml_deactivation() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'prxml_deactivation');
