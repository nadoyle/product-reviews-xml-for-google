<?php
if (!defined('ABSPATH')) exit;

class GRF_Admin {
    public function __construct() {
        add_action('admin_menu', [$this, 'menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('update_option_grf_feed_slug', [$this, 'flush_rewrites_on_change'], 10, 2);
    }

    public function flush_rewrites_on_change($old_value, $new_value) {
        if ($old_value !== $new_value) {
            if (class_exists('GRF_Feed')) { $feed = new GRF_Feed(); $feed->rewrite(); }
            flush_rewrite_rules();
        }
    }

    public function menu() {
        add_options_page(
            'Google Merchant Center Reviews XML generator', 
            'GMC Reviews XML', 
            'manage_options', 
            'grf-settings', 
            [$this, 'settings_page']
        );
    }

    public function register_settings() {
        register_setting('grf_options', 'grf_feed_slug', ['sanitize_callback' => [$this, 'sanitize_slug']]);
        register_setting('grf_options', 'grf_provider');
        register_setting('grf_options', 'grf_refresh_rate');
        register_setting('grf_options', 'grf_gtin_key');
        register_setting('grf_options', 'grf_mpn_key');
        register_setting('grf_options', 'grf_brand_key');
    }

    public function sanitize_slug($input) {
        $new_slug = preg_replace('/[^a-z0-9\.\-\_]/i', '', $input);
        return $new_slug ?: 'google-reviews-xml';
    }

    public function settings_page() {
        $slug = get_option('grf_feed_slug', 'google-reviews-xml');
        $feed_url = site_url('/' . $slug);
        ?>
        <div class="wrap">
            <h1>Google Merchant Center Reviews XML generator</h1>
            <form method="post" action="options.php">
                <?php settings_fields('grf_options'); ?>
                
                <!-- SPONSOR CARD -->
                <div class="card" style="max-width: 800px; padding: 20px; margin-bottom: 20px; border-left: 4px solid #ff9900; background: #fffdf8;">
                    <h3 style="margin-top:0;">🔌 Brought to you by TetherTies</h3>
                    <p>This plugin is free, maintained by the team at <strong>TetherTies</strong>.</p>
                    <p>We make the original cable tethers for Mac adapters, dongles, and remote controls. Never lose a dongle again.</p>
                    <p><a href="https://tetherties.com/?utm_source=plugin&utm_medium=admin_banner" target="_blank" class="button button-secondary">Visit TetherTies.com</a></p>
                </div>

                <div class="card" style="max-width: 800px; padding: 20px; margin-bottom: 20px; border-left: 4px solid #2271b1;">
                    <h3 style="margin-top:0;">✅ Your Live Feed URL</h3>
                    <p>Copy this URL into Google Merchant Center (Scheduled Fetch):</p>
                    <code><a href="<?php echo esc_url($feed_url); ?>" target="_blank" style="font-size: 1.2em; font-weight: bold;"><?php echo esc_url($feed_url); ?></a></code>
                </div>

                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><label for="grf_feed_slug">URL Slug</label></th>
                        <td><input type="text" name="grf_feed_slug" value="<?php echo esc_attr($slug); ?>" class="regular-text" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Review Source</th>
                        <td>
                            <select name="grf_provider">
                                <option value="woo" <?php selected(get_option('grf_provider'), 'woo'); ?>>WooCommerce Standard (Native, CusRev, YITH)</option>
                                <option value="wpcreviews" <?php selected(get_option('grf_provider'), 'wpcreviews'); ?>>WP Customer Reviews</option>
                                <option value="sitereviews" <?php selected(get_option('grf_provider'), 'sitereviews'); ?>>Site Reviews</option>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Refresh Rate</th>
                        <td>
                            <select name="grf_refresh_rate">
                                <option value="3600" <?php selected(get_option('grf_refresh_rate'), 3600); ?>>Hourly</option>
                                <option value="86400" <?php selected(get_option('grf_refresh_rate'), 86400); ?>>Daily (Recommended)</option>
                            </select>
                        </td>
                    </tr>
                    <tr><th colspan="2"><h3>Product Identifiers</h3></th></tr>
                    <tr valign="top"><th scope="row">GTIN Key</th><td><input type="text" name="grf_gtin_key" value="<?php echo esc_attr(get_option('grf_gtin_key', '_gtin')); ?>" class="regular-text" /></td></tr>
                    <tr valign="top"><th scope="row">MPN Key</th><td><input type="text" name="grf_mpn_key" value="<?php echo esc_attr(get_option('grf_mpn_key', '_mpn')); ?>" class="regular-text" /></td></tr>
                    <tr valign="top"><th scope="row">Brand Key</th><td><input type="text" name="grf_brand_key" value="<?php echo esc_attr(get_option('grf_brand_key', 'brand')); ?>" class="regular-text" /></td></tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}