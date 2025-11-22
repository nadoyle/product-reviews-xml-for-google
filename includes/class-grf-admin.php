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
        register_setting('grf_options', 'grf_refresh_rate'); // In seconds
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
                
                <div class="card" style="max-width: 800px; padding: 20px; margin-bottom: 20px;">
                    <h3>Your Feed URL</h3>
                    <p>Submit this URL to Google Merchant Center using the "Scheduled Fetch" method.</p>
                    <code><a href="<?php echo esc_url($feed_url); ?>" target="_blank"><?php echo esc_url($feed_url); ?></a></code>
                    <p><em>Note: If you change the Feed Slug below, click "Save Changes" and then wait 1 minute for permalinks to flush.</em></p>
                </div>

                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Feed URL Slug</th>
                        <td>
                            <input type="text" name="grf_feed_slug" value="<?php echo esc_attr($slug); ?>" class="regular-text" />
                            <p class="description">The URL endpoint for the feed (e.g., 'google-reviews-xml').</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Cache Duration (Refresh Rate)</th>
                        <td>
                            <select name="grf_refresh_rate">
                                <option value="3600" <?php selected(get_option('grf_refresh_rate'), 3600); ?>>Hourly</option>
                                <option value="43200" <?php selected(get_option('grf_refresh_rate'), 43200); ?>>Twice Daily</option>
                                <option value="86400" <?php selected(get_option('grf_refresh_rate'), 86400); ?>>Daily (Recommended)</option>
                            </select>
                            <p class="description">How often should the XML be regenerated? (Prevents server overload)</p>
                        </td>
                    </tr>
                    
                    <tr><th colspan="2"><h3>Product Identifiers (Meta Keys)</h3></th></tr>
                    <tr><td colspan="2"><p class="description">Enter the custom field names (meta keys) where you store these values. Leave blank if unused.</p></td></tr>

                    <tr valign="top">
                        <th scope="row">GTIN Meta Key</th>
                        <td>
                            <input type="text" name="grf_gtin_key" value="<?php echo esc_attr(get_option('grf_gtin_key', '_gtin')); ?>" class="regular-text" />
                            <p class="description">Default: <code>_gtin</code> or <code>_global_unique_id</code></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">MPN Meta Key</th>
                        <td>
                            <input type="text" name="grf_mpn_key" value="<?php echo esc_attr(get_option('grf_mpn_key', '_mpn')); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Brand Meta Key</th>
                        <td>
                            <input type="text" name="grf_brand_key" value="<?php echo esc_attr(get_option('grf_brand_key', 'brand')); ?>" class="regular-text" />
                            <p class="description">If blank, site name is used as fallback.</p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
            
            <hr>
            <h3>Manual Actions</h3>
            <form method="post" action="">
                <input type="hidden" name="grf_clear_cache" value="1">
                <button type="submit" class="button button-secondary">Clear Feed Cache Now</button>
                <?php 
                if (isset($_POST['grf_clear_cache'])) {
                    delete_transient('grf_xml_feed_cache');
                    echo '<div class="updated inline"><p>Cache cleared.</p></div>';
                }
                ?>
            </form>
        </div>
        <?php
    }
}