<?php
if (!defined('ABSPATH')) exit;

class GRF_Admin {
    public function __construct() {
        add_action('admin_menu', [$this, 'menu']);
        add_action('admin_init', [$this, 'register_settings']);
        
        // Auto-flush permalinks when the slug setting is updated
        add_action('update_option_grf_feed_slug', [$this, 'flush_rewrites_on_change'], 10, 2);
    }

    public function flush_rewrites_on_change($old_value, $new_value) {
        if ($old_value !== $new_value) {
            if (class_exists('GRF_Feed')) {
                $feed = new GRF_Feed();
                $feed->rewrite(); 
            }
            flush_rewrite_rules();
        }
    }

    public function menu() {
        add_options_page(
            'Google Merchant Center Reviews XML generator', // Page Title
            'GMC Reviews XML', // Menu Title (kept short for UI)
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
        $new_slug = sanitize_title($input);
        $new_slug = preg_replace('/[^a-z0-9\.\-\_]/i', '', $input);
        return $new_slug ?: 'google-reviews-xml';
    }

    public function settings_page() {
        $slug = get_option('grf_feed_slug', 'google-reviews-xml');
        $feed_url = site_url('/' . $slug);
        $fallback_url = site_url('/?grf_feed=1');
        ?>
        <div class="wrap">
            <h1>Google Merchant Center Reviews XML generator</h1>
            <form method="post" action="options.php">
                <?php settings_fields('grf_options'); ?>
                
                <!-- Status Card -->
                <div class="card" style="max-width: 800px; padding: 20px; margin-bottom: 20px; border-left: 4px solid #2271b1;">
                    <h3 style="margin-top:0;">✅ Your Live Feed URL</h3>
                    <p>Copy this URL and paste it into Google Merchant Center (Scheduled Fetch):</p>
                    <code><a href="<?php echo esc_url($feed_url); ?>" target="_blank" style="font-size: 1.2em; font-weight: bold;"><?php echo esc_url($feed_url); ?></a></code>
                    
                    <p class="description" style="margin-top: 10px;">
                        <small>If the link above gives a 404 error, try this <a href="<?php echo esc_url($fallback_url); ?>" target="_blank">Direct Link</a> instead.</small>
                    </p>
                </div>

                <table class="form-table">
                    
                    <!-- URL SLUG SETTINGS -->
                    <tr>
                        <th scope="row"><label for="grf_feed_slug">URL Format (Slug)</label></th>
                        <td>
                            <div style="display: flex; align-items: center;">
                                <span style="background: #f0f0f1; padding: 6px 10px; border: 1px solid #8c8f94; border-right: 0; border-radius: 4px 0 0 4px;">
                                    <?php echo site_url('/'); ?>
                                </span>
                                <input type="text" id="grf_feed_slug" name="grf_feed_slug" value="<?php echo esc_attr($slug); ?>" class="regular-text" style="border-radius: 0 4px 4px 0;" />
                            </div>
                            <p class="description">
                                <strong>Recommended formats:</strong><br>
                                <code>google-reviews.xml</code> (Best for Google)<br>
                                <code>feeds/reviews.xml</code><br>
                                <code>google-product-reviews</code>
                            </p>
                        </td>
                    </tr>

                    <!-- DATA SOURCE -->
                    <tr valign="top">
                        <th scope="row">Review Source</th>
                        <td>
                            <select name="grf_provider">
                                <option value="woo" <?php selected(get_option('grf_provider'), 'woo'); ?>>WooCommerce Standard (Native, CusRev, YITH, Judge.me Sync)</option>
                                <option value="wpcreviews" <?php selected(get_option('grf_provider'), 'wpcreviews'); ?>>WP Customer Reviews (Custom Table)</option>
                                <option value="sitereviews" <?php selected(get_option('grf_provider'), 'sitereviews'); ?>>Site Reviews (Post Type)</option>
                            </select>
                        </td>
                    </tr>

                    <!-- REFRESH RATE -->
                    <tr valign="top">
                        <th scope="row">Refresh Rate</th>
                        <td>
                            <select name="grf_refresh_rate">
                                <option value="3600" <?php selected(get_option('grf_refresh_rate'), 3600); ?>>Hourly</option>
                                <option value="43200" <?php selected(get_option('grf_refresh_rate'), 43200); ?>>Twice Daily</option>
                                <option value="86400" <?php selected(get_option('grf_refresh_rate'), 86400); ?>>Daily (Recommended)</option>
                            </select>
                            <p class="description">We cache the feed to reduce server load. Google only fetches once a day usually.</p>
                        </td>
                    </tr>

                    <!-- IDENTIFIERS -->
                    <tr><th colspan="2"><h3>Product Identifiers</h3></th></tr>
                    <tr valign="top"><th scope="row">GTIN Key</th><td><input type="text" name="grf_gtin_key" value="<?php echo esc_attr(get_option('grf_gtin_key', '_gtin')); ?>" class="regular-text" /></td></tr>
                    <tr valign="top"><th scope="row">MPN Key</th><td><input type="text" name="grf_mpn_key" value="<?php echo esc_attr(get_option('grf_mpn_key', '_mpn')); ?>" class="regular-text" /></td></tr>
                    <tr valign="top"><th scope="row">Brand Key</th><td><input type="text" name="grf_brand_key" value="<?php echo esc_attr(get_option('grf_brand_key', 'brand')); ?>" class="regular-text" /></td></tr>
                </table>
                
                <?php submit_button('Save Changes & Update URL'); ?>
            </form>
        </div>
        <?php
    }
}