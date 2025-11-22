<?php
if (!defined('ABSPATH')) exit;

class GRF_Admin {
    public function __construct() {
        add_action('admin_menu', [$this, 'menu']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function menu() {
        add_options_page(
            'Google Reviews Feed',
            'Google Reviews Feed',
            'manage_options',
            'grf-settings',
            [$this, 'settings_page']
        );
    }

    public function register_settings() {
        register_setting('grf_options', 'grf_feed_slug');
        register_setting('grf_options', 'grf_provider');
        register_setting('grf_options', 'grf_refresh_rate');
        register_setting('grf_options', 'grf_gtin_key');
        register_setting('grf_options', 'grf_mpn_key');
        register_setting('grf_options', 'grf_brand_key');
    }

    public function settings_page() {
        $slug = get_option('grf_feed_slug', 'google-reviews-xml');
        $feed_url = site_url('/' . $slug);
        ?>
        <div class="wrap">
            <h1>Google Reviews Feed Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields('grf_options'); ?>
                
                <div class="notice notice-info inline">
                    <p><strong>Feed URL:</strong> <a href="<?php echo esc_url($feed_url); ?>" target="_blank"><?php echo esc_url($feed_url); ?></a></p>
                </div>

                <table class="form-table">
                    <!-- DATA SOURCE -->
                    <tr valign="top">
                        <th scope="row">Review Plugin Source</th>
                        <td>
                            <select name="grf_provider" style="min-width: 300px;">
                                <optgroup label="Standard Database (Most Common)">
                                    <option value="woo" <?php selected(get_option('grf_provider'), 'woo'); ?>>WooCommerce Standard (Default)</option>
                                    <option value="woo" <?php selected(get_option('grf_provider'), 'woo'); ?>>Customer Reviews for WooCommerce (CusRev)</option>
                                    <option value="woo" <?php selected(get_option('grf_provider'), 'woo'); ?>>YITH WooCommerce Advanced Reviews</option>
                                    <option value="woo" <?php selected(get_option('grf_provider'), 'woo'); ?>>ReviewX / Judge.me (Sync Enabled) / Yotpo (Sync Enabled)</option>
                                </optgroup>
                                <optgroup label="Custom Tables / Logic">
                                    <option value="wpcreviews" <?php selected(get_option('grf_provider'), 'wpcreviews'); ?>>WP Customer Reviews</option>
                                    <option value="sitereviews" <?php selected(get_option('grf_provider'), 'sitereviews'); ?>>Site Reviews</option>
                                </optgroup>
                            </select>
                            <p class="description">Select which plugin you use to collect reviews.</p>
                            <p class="description" style="color: #d63638;"><strong>Note for Judge.me / Yotpo Users:</strong> You must enable the "Sync to WooCommerce" setting inside your review plugin for this feed to work.</p>
                        </td>
                    </tr>

                    <!-- URL SLUG -->
                    <tr valign="top">
                        <th scope="row">Feed URL Slug</th>
                        <td>
                            <input type="text" name="grf_feed_slug" value="<?php echo esc_attr($slug); ?>" class="regular-text" />
                        </td>
                    </tr>

                    <!-- REFRESH RATE -->
                    <tr valign="top">
                        <th scope="row">Cache Refresh Rate</th>
                        <td>
                            <select name="grf_refresh_rate">
                                <option value="3600" <?php selected(get_option('grf_refresh_rate'), 3600); ?>>Hourly</option>
                                <option value="86400" <?php selected(get_option('grf_refresh_rate'), 86400); ?>>Daily (Recommended)</option>
                            </select>
                        </td>
                    </tr>

                    <!-- PRODUCT IDENTIFIERS -->
                    <tr><th colspan="2"><h3>Product Identifiers (Meta Keys)</h3></th></tr>
                    <tr valign="top"><th scope="row">GTIN Meta Key</th><td><input type="text" name="grf_gtin_key" value="<?php echo esc_attr(get_option('grf_gtin_key', '_gtin')); ?>" class="regular-text" /></td></tr>
                    <tr valign="top"><th scope="row">MPN Meta Key</th><td><input type="text" name="grf_mpn_key" value="<?php echo esc_attr(get_option('grf_mpn_key', '_mpn')); ?>" class="regular-text" /></td></tr>
                    <tr valign="top"><th scope="row">Brand Meta Key</th><td><input type="text" name="grf_brand_key" value="<?php echo esc_attr(get_option('grf_brand_key', 'brand')); ?>" class="regular-text" /></td></tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}