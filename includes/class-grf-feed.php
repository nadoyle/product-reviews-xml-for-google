<?php
if (!defined('ABSPATH')) exit;

class GRF_Feed {
    public function __construct() {
        add_action('init', [$this, 'rewrite']);
        add_action('template_redirect', [$this, 'render_feed']);
        add_filter('query_vars', [$this, 'add_query_var']);
    }

    public function add_query_var($vars) {
        $vars[] = 'grf_feed';
        return $vars;
    }

    public function rewrite() {
        $slug = get_option('grf_feed_slug', 'google-reviews-xml');
        add_rewrite_rule('^' . $slug . '/?$', 'index.php?grf_feed=1', 'top');
    }

    public function render_feed() {
        if (get_query_var('grf_feed') != 1) return;

        // 1. Prevent Caching by Plugins/Browsers
        if (!defined('DONOTCACHEPAGE')) define('DONOTCACHEPAGE', true);
        if (!defined('DONOTMINIFY')) define('DONOTMINIFY', true);
        
        header('Content-Type: application/xml; charset=UTF-8');
        header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
        header('Pragma: no-cache'); // HTTP 1.0.
        header('Expires: 0'); // Proxies.

        // 2. Check Internal Cache (Transient)
        $cached_xml = get_transient('grf_xml_feed_cache');
        if ($cached_xml) {
            echo $cached_xml;
            exit;
        }

        // 3. Generate XML if cache missing
        $xml = $this->generate_xml();

        // 4. Save to Cache
        $refresh_rate = (int) get_option('grf_refresh_rate', 86400);
        set_transient('grf_xml_feed_cache', $xml, $refresh_rate);

        echo $xml;
        exit;
    }

    private function generate_xml() {
        $provider = new GRF_Provider_WP_Comments();
        $reviews = $provider->get_reviews();
        
        // Start Output Buffering to capture XML
        ob_start();
        
        echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        ?>
<feed xmlns:vc="http://www.w3.org/2007/XMLSchema-versioning"
 xmlns:p="http://base.google.com/ns/1.0"
 name="<?php echo esc_attr(get_bloginfo('name')); ?> Product Reviews">
    <version>2.3</version>
    <aggregator>
        <name><?php echo esc_html(get_bloginfo('name')); ?></name>
    </aggregator>
    <publisher>
        <name><?php echo esc_html(get_bloginfo('name')); ?></name>
        <favicon><?php echo esc_url(get_site_icon_url()); ?></favicon>
    </publisher>
    <reviews>
        <?php foreach ($reviews as $r): ?>
        <review>
            <review_id><?php echo esc_html($r['id']); ?></review_id>
            <reviewer>
                <name><?php echo esc_html($r['reviewer_name']); ?></name>
                <?php if($r['is_verified']): ?><reviewer_type>verified</reviewer_type><?php endif; ?>
            </reviewer>
            <review_timestamp><?php echo esc_html($r['timestamp']); ?></review_timestamp>
            <content><?php echo htmlspecialchars($r['content'], ENT_XML1, 'UTF-8'); ?></content>
            <review_url type="singleton"><?php echo esc_url($r['url']); ?></review_url>
            <ratings>
                <overall min="1" max="5"><?php echo esc_html($r['rating']); ?></overall>
            </ratings>
            <products>
                <product>
                    <product_ids>
                        <?php if (!empty($r['identifiers']['gtin'])): ?>
                        <gtins>
                            <gtin><?php echo esc_html($r['identifiers']['gtin']); ?></gtin>
                        </gtins>
                        <?php endif; ?>
                        
                        <?php if (!empty($r['identifiers']['mpn'])): ?>
                        <mpns>
                            <mpn><?php echo esc_html($r['identifiers']['mpn']); ?></mpn>
                        </mpns>
                        <?php endif; ?>
                        
                        <skus>
                            <sku><?php echo esc_html($r['identifiers']['sku']); ?></sku>
                        </skus>

                        <brands>
                            <brand><?php echo esc_html($r['identifiers']['brand']); ?></brand>
                        </brands>
                    </product_ids>
                    <product_name><?php echo htmlspecialchars($r['product_name'], ENT_XML1, 'UTF-8'); ?></product_name>
                    <product_url><?php echo esc_url($r['product_url']); ?></product_url>
                </product>
            </products>
        </review>
        <?php endforeach; ?>
    </reviews>
</feed>
        <?php
        return ob_get_clean();
    }
}