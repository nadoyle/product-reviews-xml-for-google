<?php
if (!defined('ABSPATH')) exit;

class PRXML_Admin {
    public function __construct() {
        add_action('admin_menu', [$this, 'menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']); 
        add_action('update_option_prxml_feed_slug', [$this, 'flush_rewrites_on_change'], 10, 2);
        add_action('update_option_prxml_provider', [$this, 'clear_cache_on_setting_change']);
        add_action('update_option_prxml_gtin_key', [$this, 'clear_cache_on_setting_change']);
        add_action('update_option_prxml_mpn_key', [$this, 'clear_cache_on_setting_change']);
        add_action('update_option_prxml_brand_key', [$this, 'clear_cache_on_setting_change']);
        add_action('admin_post_prxml_clear_cache', [$this, 'process_clear_cache']);
    }

    public function enqueue_scripts($hook) {
        if ($hook !== 'settings_page_prxml-settings') {
            return;
        }
        
        wp_enqueue_script(
            'prxml-admin-js',
            plugins_url('../assets/js/admin.js', dirname(__FILE__)), 
            [],
            PRXML_VERSION,
            true
        );

        wp_localize_script('prxml-admin-js', 'prxml_vars', [
            'copied_text' => __('Copied!', 'product-reviews-xml-for-google')
        ]);
    }

    public function flush_rewrites_on_change($old_value, $new_value) {
        if ($old_value !== $new_value) {
            if (class_exists('PRXML_Feed')) { 
                $feed = new PRXML_Feed(); 
                $feed->rewrite(); 
            }
            flush_rewrite_rules();
        }
    }

    public function clear_cache_on_setting_change() {
        delete_transient('prxml_xml_feed_cache');
    }

    public function menu() {
        add_options_page(
            esc_html__('Google Merchant Center Reviews XML generator', 'product-reviews-xml-for-google'), 
            esc_html__('GMC Reviews XML', 'product-reviews-xml-for-google'), 
            'manage_options', 
            'prxml-settings', 
            [$this, 'settings_page']
        );
    }

    public function register_settings() {
        register_setting('prxml_options', 'prxml_feed_slug', [
            'type' => 'string',
            'sanitize_callback' => [$this, 'sanitize_slug'],
            'default' => 'google-merchant-center-reviews-xml'
        ]);
        register_setting('prxml_options', 'prxml_provider', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'woo'
        ]);
        register_setting('prxml_options', 'prxml_refresh_rate', [
            'type' => 'integer',
            'sanitize_callback' => 'absint',
            'default' => 604800
        ]);
        register_setting('prxml_options', 'prxml_gtin_key', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '_gtin'
        ]);
        register_setting('prxml_options', 'prxml_mpn_key', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '_mpn'
        ]);
        register_setting('prxml_options', 'prxml_brand_key', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'brand'
        ]);
    }

    public function sanitize_slug($input) {
        // Use WordPress's built-in sanitization for slugs
        $sanitized = sanitize_title_with_dashes($input);
        // Return default if empty after sanitization
        return $sanitized ?: 'google-merchant-center-reviews-xml';
    }

    public function process_clear_cache() {
        check_admin_referer('prxml_clear_cache_action', 'prxml_nonce_field');
        
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission.', 'product-reviews-xml-for-google'));
        }
        
        delete_transient('prxml_xml_feed_cache');
        
        wp_safe_redirect(add_query_arg(['page' => 'prxml-settings', 'cache_cleared' => '1'], admin_url('options-general.php')));
        exit;
    }

    public function settings_page() {
        $slug = get_option('prxml_feed_slug', 'google-merchant-center-reviews-xml');
        $feed_url = site_url('/' . $slug);
        $current_refresh = get_option('prxml_refresh_rate', 604800);
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Google Merchant Center Reviews XML generator', 'product-reviews-xml-for-google'); ?></h1>
            
            <?php 
            // We ignore nonce verification here because we are just checking a GET flag to show a success message.
            if (isset($_GET['cache_cleared'])): // phpcs:ignore WordPress.Security.NonceVerification.Recommended 
            ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php esc_html_e('Feed cache successfully cleared.', 'product-reviews-xml-for-google'); ?></p>
                </div>
            <?php endif; ?>

            <form method="post" action="options.php">
                <?php settings_fields('prxml_options'); ?>
                
                <div class="card" style="max-width: 800px; padding: 20px; margin-bottom: 20px; border-left: 4px solid #ff9900; background: #fffdf8;">
                    <h3 style="margin-top:0;"><?php esc_html_e('Brought to you by TetherTies', 'product-reviews-xml-for-google'); ?></h3>
                    <p><?php esc_html_e('This free plugin is maintained by TetherTies, the #1 security solution for adapters and dongles.', 'product-reviews-xml-for-google'); ?></p>
                    <p><a href="https://tetherties.com/?utm_source=plugin&utm_medium=admin_banner" target="_blank" rel="noopener noreferrer" class="button button-secondary"><?php esc_html_e('Visit TetherTies.com', 'product-reviews-xml-for-google'); ?></a></p>
                </div>

                <div class="card" style="max-width: 800px; padding: 20px; margin-bottom: 20px; border-left: 4px solid #2271b1;">
                    <h3 style="margin-top:0;"><?php esc_html_e('Your Live Feed URL', 'product-reviews-xml-for-google'); ?></h3>
                    <div style="display: flex; gap: 10px; align-items: center; background: #f0f0f1; padding: 10px; border-radius: 5px; border: 1px solid #c3c4c7;">
                        <code id="prxml_url_text" style="flex-grow: 1; font-size: 1.2em; background: transparent;"><?php echo esc_url($feed_url); ?></code>
                        <button type="button" id="prxml_copy_btn" class="button button-small"><?php esc_html_e('Copy URL', 'product-reviews-xml-for-google'); ?></button>
                    </div>
                    <p style="margin-top: 10px;">
                        <a href="<?php echo esc_url($feed_url); ?>" target="_blank" class="button button-secondary"><?php esc_html_e('Test Feed', 'product-reviews-xml-for-google'); ?></a>
                    </p>
                </div>

                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><label for="prxml_feed_slug"><?php esc_html_e('URL Slug', 'product-reviews-xml-for-google'); ?></label></th>
                        <td><input type="text" name="prxml_feed_slug" value="<?php echo esc_attr($slug); ?>" class="regular-text" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Review Source', 'product-reviews-xml-for-google'); ?></th>
                        <td>
                            <select name="prxml_provider" style="max-width: 100%;">
                                <option value="woo" <?php selected(get_option('prxml_provider'), 'woo'); ?>><?php esc_html_e('Universal WooCommerce (CusRev, YITH, Judge.me, Yotpo)', 'product-reviews-xml-for-google'); ?></option>
                                <option value="sitereviews" <?php selected(get_option('prxml_provider'), 'sitereviews'); ?>><?php esc_html_e('Site Reviews Plugin', 'product-reviews-xml-for-google'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Refresh Rate', 'product-reviews-xml-for-google'); ?></th>
                        <td>
                            <select name="prxml_refresh_rate">
                                <option value="604800" <?php selected($current_refresh, 604800); ?>><?php esc_html_e('Weekly (Recommended)', 'product-reviews-xml-for-google'); ?></option>
                                <option value="86400" <?php selected($current_refresh, 86400); ?>><?php esc_html_e('Daily', 'product-reviews-xml-for-google'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th colspan="2"><h3><?php esc_html_e('Product Identifiers', 'product-reviews-xml-for-google'); ?></h3></th>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('GTIN Key', 'product-reviews-xml-for-google'); ?></th>
                        <td><input type="text" name="prxml_gtin_key" value="<?php echo esc_attr(get_option('prxml_gtin_key', '_gtin')); ?>" class="regular-text" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('MPN Key', 'product-reviews-xml-for-google'); ?></th>
                        <td><input type="text" name="prxml_mpn_key" value="<?php echo esc_attr(get_option('prxml_mpn_key', '_mpn')); ?>" class="regular-text" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Brand Key', 'product-reviews-xml-for-google'); ?></th>
                        <td><input type="text" name="prxml_brand_key" value="<?php echo esc_attr(get_option('prxml_brand_key', 'brand')); ?>" class="regular-text" /></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
            
            <hr>
            <h3><?php esc_html_e('Manual Actions', 'product-reviews-xml-for-google'); ?></h3>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="prxml_clear_cache">
                <?php wp_nonce_field('prxml_clear_cache_action', 'prxml_nonce_field'); ?>
                <button type="submit" class="button button-secondary"><?php esc_html_e('Clear Feed Cache Now', 'product-reviews-xml-for-google'); ?></button>
            </form>
        </div>
        <?php
    }
}
