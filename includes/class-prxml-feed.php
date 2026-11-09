<?php
if (!defined('ABSPATH')) exit;

/**
 * Feed generation and rendering class
 */
class PRXML_Feed {
    /**
     * Constructor - register hooks
     */
    public function __construct() {
        add_action('init', [$this, 'rewrite']);
        add_action('template_redirect', [$this, 'render_feed']);
        add_filter('query_vars', [$this, 'add_query_var']);
    }

    /**
     * Add custom query var for feed detection
     *
     * @param array $vars Existing query vars
     * @return array Modified query vars
     */
    public function add_query_var($vars) { 
        $vars[] = 'prxml_feed'; 
        return $vars; 
    }

    /**
     * Register rewrite rule for feed URL
     */
    public function rewrite() {
        $slug = get_option('prxml_feed_slug', 'google-merchant-center-reviews-xml');
        // Escape slug for use in regex to prevent regex injection
        $escaped_slug = preg_quote($slug, '/');
        add_rewrite_rule('^' . $escaped_slug . '/?$', 'index.php?prxml_feed=1', 'top');
    }

    /**
     * Render the XML feed
     */
    public function render_feed() {
        // Only execute if this is specifically our feed request
        if (get_query_var('prxml_feed') != 1) {
            return;
        }

        // Only set DONOTCACHEPAGE when actually rendering our feed
        // This prevents affecting other pages/plugins
        if (!defined('DONOTCACHEPAGE')) {
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- DONOTCACHEPAGE is a standard constant used by caching plugins
            define('DONOTCACHEPAGE', true);
        }
        
        $charset = get_bloginfo('charset');
        header('Content-Type: application/xml; charset=' . $charset);
        
        $cached = get_transient('prxml_xml_feed_cache');
        if ($cached) { 
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- XML content is pre-escaped during generation
            echo $cached; 
            exit; 
        }

        $xml = $this->generate_xml();
        
        if ($xml) {
            set_transient('prxml_xml_feed_cache', $xml, (int)get_option('prxml_refresh_rate', 604800));
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- XML content is pre-escaped during generation
            echo $xml;
        } else {
            echo '<?xml version="1.0" encoding="' . esc_attr($charset) . '"?><e>Failed to generate feed</e>';
        }
        exit;
    }

    /**
     * Generate XML feed content
     *
     * @return string|false XML string or false on error
     */
    private function generate_xml() {
        try {
            $provider_option = get_option('prxml_provider', 'woo');
            
            switch ($provider_option) {
                case 'sitereviews': 
                    $provider = new PRXML_Provider_SiteReviews(); 
                    break;
                default: 
                    $provider = new PRXML_Provider_Woo(); 
                    break;
            }

            $reviews = $provider->get_reviews();
            
            if (!is_array($reviews)) {
                return false;
            }
            

            
            ob_start();
            $charset = get_bloginfo('charset');
            echo '<?xml version="1.0" encoding="' . esc_attr($charset) . '"?>' . PHP_EOL;
            ?>
<feed xmlns:vc="http://www.w3.org/2007/XMLSchema-versioning" xmlns:p="http://base.google.com/ns/1.0" name="<?php echo esc_attr(get_bloginfo('name')); ?> Product Reviews">
    <version>2.3</version>
    <aggregator><n>WooCommerce</n></aggregator>
    <publisher><n><?php echo esc_html(get_bloginfo('name')); ?></n></publisher>
    <reviews>
        <?php foreach ($reviews as $r): ?>
        <review>
            <review_id><?php echo esc_html($r['id']); ?></review_id>
            <reviewer><n><?php echo esc_html($r['reviewer_name']); ?></n></reviewer>
            <review_timestamp><?php echo esc_html($r['timestamp']); ?></review_timestamp>
            <content><?php echo esc_html($r['content']); ?></content>
            <review_url type="singleton"><?php echo esc_url($r['url']); ?></review_url>
            <ratings><overall min="1" max="5"><?php echo esc_html($r['rating']); ?></overall></ratings>
            <products>
                <product>
                    <product_ids>
                        <?php foreach (['gtins' => 'gtin', 'mpns' => 'mpn', 'skus' => 'sku'] as $list => $tag): ?>
                        <?php if (!empty($r['identifiers'][$list])): ?><<?php echo $tag; ?>s><?php foreach ($r['identifiers'][$list] as $value): ?><<?php echo $tag; ?>><?php echo esc_html($value); ?></<?php echo $tag; ?>><?php endforeach; ?></<?php echo $tag; ?>s><?php endif; ?>
                        <?php endforeach; ?>
                        <brands><brand><?php echo esc_html($r['identifiers']['brand']); ?></brand></brands>
                    </product_ids>
                    <product_name><?php echo esc_html($r['product_name']); ?></product_name>
                    <product_url><?php echo esc_url($r['product_url']); ?></product_url>
                </product>
            </products>
        </review>
        <?php endforeach; ?>
    </reviews>
</feed>
            <?php
            return ob_get_clean();
        } catch (Exception $e) {
            return false;
        }
    }
}
