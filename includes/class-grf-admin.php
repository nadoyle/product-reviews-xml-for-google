<?php
if (!defined('ABSPATH')) exit;

class GRF_Admin {
    public function __construct() {
        add_action('admin_menu', [$this, 'menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('update_option_grf_feed_slug', [$this, 'flush_rewrites_on_change'], 10, 2);
        add_action('admin_post_grf_clear_cache', [$this, 'process_clear_cache']);
    }

    public function flush_rewrites_on_change($old_value, $new_value) {
        if ($old_value !== $new_value) {
            if (class_exists('GRF_Feed')) { $feed = new GRF_Feed(); $feed->rewrite(); }
            flush_rewrite_rules();
        }
    }

    public function menu() {
        add_options_page(
            __('Google Merchant Center Reviews XML generator', 'product-reviews-xml-for-google'), 
            __('GMC Reviews XML', 'product-reviews-xml-for-google'), 
            'manage_options', 
            'grf-settings', 
            [$this, 'settings_page']
        );
    }

    public function register_settings() {
        register_setting('grf_options', 'grf_feed_slug', ['sanitize_callback' => [$this, 'sanitize_slug']]);
        register_setting('grf_options', 'grf_provider');
        register_setting('grf_options', 'grf_refresh_rate');
        register_setting('grf_options', 'grf_gtin_key', ['sanitize_callback' => 'sanitize_text_field']);
        register_setting('grf_options', 'grf_mpn_key', ['sanitize_callback' => 'sanitize_text_field']);
        register_setting('grf_options', 'grf_brand_key', ['sanitize_callback' => 'sanitize_text_field']);
    }

    public function sanitize_slug($input) {
        $new_slug = preg_replace('/[^a-z0-9\.\-\_]/i', '', $input);
        return $new_slug ?: 'google-merchant-center-reviews-xml';
    }

    /**
     * SECURITY: Process the clear cache button with Nonce check
     */
    public function process_clear_cache() {
        check_admin_referer('grf_clear_cache_action', 'grf_nonce_field');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to do this.', 'product-reviews-xml-for-google'));
        }

        delete_transient('grf_xml_feed_cache');
        
        wp_redirect(add_query_arg(['page' => 'grf-settings', 'cache_cleared' => '1'], admin_url('options-general.php')));
        exit;
    }

    public function settings_page() {
        $slug = get_option('grf_feed_slug', 'google-merchant-center-reviews-xml');
        $feed_url = site_url('/' . $slug);
        ?>
        <div class="wrap">
            <h1><?php _e('Google Merchant Center Reviews XML generator', 'product-reviews-xml-for-google'); ?></h1>
            
            <?php if (isset($_GET['cache_cleared'])): ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php _e('Feed cache successfully cleared.', 'product-reviews-xml-for-google'); ?></p>
                </div>
            <?php endif; ?>

            <form method="post" action="options.php">
                <?php settings_fields('grf_options'); ?>
                
                <!-- SPONSOR CARD -->
                <div class="card" style="max-width: 800px; padding: 20px; margin-bottom: 20px; border-left: 4px solid #ff9900; background: #fffdf8;">
                    <h3 style="margin-top:0;"><?php _e('🔌 Brought to you by TetherTies', 'product-reviews-xml-for-google'); ?></h3>
                    <p><?php _e('This plugin is free, maintained by the team at <strong>TetherTies</strong>.', 'product-reviews-xml-for-google'); ?></p>
                    <p><?php _e('We make the original cable tethers for Mac adapters, dongles, and remote controls. Never lose a dongle again.', 'product-reviews-xml-for-google'); ?></p>
                    <p><a href="https://tetherties.com/?utm_source=plugin&utm_medium=admin_banner" target="_blank" class="button button-secondary"><?php _e('Visit TetherTies.com', 'product-reviews-xml-for-google'); ?></a></p>
                </div>

                <div class="card" style="max-width: 800px; padding: 20px; margin-bottom: 20px; border-left: 4px solid #2271b1;">
                    <h3 style="margin-top:0;"><?php _e('✅ Your Live Feed URL', 'product-reviews-xml-for-google'); ?></h3>
                    <code><a href="<?php echo esc_url($feed_url); ?>" target="_blank" style="font-size: 1.2em; font-weight: bold;"><?php echo esc_url($feed_url); ?></a></code>
                </div>

                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><label for="grf_feed_slug"><?php _e('URL Slug', 'product-reviews-xml-for-google'); ?></label></th>
                        <td><input type="text" name="grf_feed_slug" value="<?php echo esc_attr($slug); ?>" class="regular-text" /></td>
                    </tr>
                    <tr valign="top"><th scope="row"><?php _e('Review Source', 'product-reviews-xml-for-google'); ?></th>
                        <td><select name="grf_provider">
                            <option value="woo" <?php selected(get_option('grf_provider'), 'woo'); ?>><?php _e('WooCommerce Standard', 'product-reviews-xml-for-google'); ?></option>
                            <option value="wpcreviews" <?php selected(get_option('grf_provider'), 'wpcreviews'); ?>><?php _e('WP Customer Reviews', 'product-reviews-xml-for-google'); ?></option>
                            <option value="sitereviews" <?php selected(get_option('grf_provider'), 'sitereviews'); ?>><?php _e('Site Reviews', 'product-reviews-xml-for-google'); ?></option>
                        </select></td></tr>
                    <tr valign="top"><th scope="row"><?php _e('Refresh Rate', 'product-reviews-xml-for-google'); ?></th>
                        <td><select name="grf_refresh_rate">
                            <option value="604800" <?php selected(get_option('grf_refresh_rate'), 604800); ?>><?php _e('Weekly', 'product-reviews-xml-for-google'); ?></option>
                            <option value="86400" <?php selected(get_option('grf_refresh_rate'), 86400); ?>><?php _e('Daily', 'product-reviews-xml-for-google'); ?></option>
                            <option value="3600" <?php selected(get_option('grf_refresh_rate'), 3600); ?>><?php _e('Hourly', 'product-reviews-xml-for-google'); ?></option>
                        </select></td></tr>
                    <tr><th colspan="2"><h3><?php _e('Product Identifiers', 'product-reviews-xml-for-google'); ?></h3></th></tr>
                    <tr valign="top"><th scope="row"><?php _e('GTIN Key', 'product-reviews-xml-for-google'); ?></th><td><input type="text" name="grf_gtin_key" value="<?php echo esc_attr(get_option('grf_gtin_key', '_gtin')); ?>" class="regular-text" /></td></tr>
                    <tr valign="top"><th scope="row"><?php _e('MPN Key', 'product-reviews-xml-for-google'); ?></th><td><input type="text" name="grf_mpn_key" value="<?php echo esc_attr(get_option('grf_mpn_key', '_mpn')); ?>" class="regular-text" /></td></tr>
                    <tr valign="top"><th scope="row"><?php _e('Brand Key', 'product-reviews-xml-for-google'); ?></th><td><input type="text" name="grf_brand_key" value="<?php echo esc_attr(get_option('grf_brand_key', 'brand')); ?>" class="regular-text" /></td></tr>
                </table>
                <?php submit_button(); ?>
            </form>

            <hr>
            <h3><?php _e('Manual Actions', 'product-reviews-xml-for-google'); ?></h3>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="grf_clear_cache">
                <?php wp_nonce_field('grf_clear_cache_action', 'grf_nonce_field'); ?>
                <button type="submit" class="button button-secondary"><?php _e('Clear Feed Cache Now', 'product-reviews-xml-for-google'); ?></button>
            </form>
        </div>
        <?php
    }
}