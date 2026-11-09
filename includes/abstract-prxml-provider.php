<?php
if (!defined('ABSPATH')) exit;

/**
 * Abstract provider class for review sources
 */
abstract class PRXML_Provider {
    /**
     * @var string GTIN meta key
     */
    protected $gtin_key;
    
    /**
     * @var string MPN meta key
     */
    protected $mpn_key;
    
    /**
     * @var string Brand attribute/meta key
     */
    protected $brand_key;

    /**
     * Constructor - load settings
     */
    public function __construct() {
        $this->gtin_key  = get_option('prxml_gtin_key', '_gtin');
        $this->mpn_key   = get_option('prxml_mpn_key', '_mpn');
        $this->brand_key = get_option('prxml_brand_key', 'brand');
    }

    /**
     * Get reviews from the provider
     *
     * @return array Array of review data
     */
    abstract public function get_reviews();

    /**
     * Get product identifiers for XML feed
     *
     * @param int $product_id WooCommerce product ID
     * @return array|false Product data array or false on failure
     */
    protected function get_product_identifiers($product_id) {
        static $memo = [];
        if (isset($memo[$product_id])) {
            return $memo[$product_id];
        }
        $memo[$product_id] = $this->build_product_identifiers($product_id);
        return $memo[$product_id];
    }

    /**
     * Build the identifier set for one product (memoized by the caller)
     *
     * @param int $product_id WooCommerce product ID
     * @return array|false
     */
    private function build_product_identifiers($product_id) {
        $product = wc_get_product($product_id);
        
        // Strict type checking for WC_Product
        if (!$product || !is_a($product, 'WC_Product')) {
            return false;
        }

        $data = [
            'name' => $product->get_name(), 
            'url' => $product->get_permalink(), 
            'sku' => $product->get_sku() ?: '',
            'brand' => get_bloginfo('name'), 
            'gtin' => '', 
            'mpn' => ''
        ];

        // Get brand from attribute or meta
        if ($this->brand_key) {
            $brand = $product->get_attribute($this->brand_key);
            if (!$brand) {
                $brand = get_post_meta($product_id, $this->brand_key, true);
            }
            if ($brand) {
                $data['brand'] = $brand;
            }
        }
        
        // Get GTIN: use the configured meta key if it holds a value,
        // otherwise fall back to WooCommerce's native field. WC 9.2+
        // stores GTIN/UPC/EAN/ISBN in the _global_unique_id meta key and
        // exposes it via get_global_unique_id().
        $gtin = '';
        if ($this->gtin_key) {
            $gtin = get_post_meta($product_id, $this->gtin_key, true);
        }
        if (empty($gtin) && method_exists($product, 'get_global_unique_id')) {
            $gtin = $product->get_global_unique_id();
        }
        $data['gtin'] = $gtin ?: '';

        // Get MPN from meta (WooCommerce has no native MPN field)
        if ($this->mpn_key) {
            $mpn = get_post_meta($product_id, $this->mpn_key, true);
            $data['mpn'] = $mpn ?: '';
        }

        // Merge in identifiers from variations so reviews on a variable
        // product match the variation-level rows in a Merchant Center
        // product feed.
        $lists = [
            'gtins' => $data['gtin'] !== '' ? [$data['gtin']] : [],
            'mpns'  => $data['mpn'] !== '' ? [$data['mpn']] : [],
            'skus'  => $data['sku'] !== '' ? [$data['sku']] : [],
        ];

        if ($product->is_type('variable') && apply_filters('prxml_include_variations', true, $product)) {
            foreach ($product->get_children() as $variation_id) {
                $variation = wc_get_product($variation_id);
                if (!$variation || !is_a($variation, 'WC_Product')) {
                    continue;
                }

                $lists['skus'][] = (string) $variation_id;
                if ($variation->get_sku()) {
                    $lists['skus'][] = $variation->get_sku();
                }

                $v_gtin = '';
                if ($this->gtin_key) {
                    $v_gtin = get_post_meta($variation_id, $this->gtin_key, true);
                }
                if (empty($v_gtin) && method_exists($variation, 'get_global_unique_id')) {
                    $v_gtin = $variation->get_global_unique_id();
                }
                if ($v_gtin) {
                    $lists['gtins'][] = $v_gtin;
                }

                $v_mpn = $this->mpn_key ? get_post_meta($variation_id, $this->mpn_key, true) : '';
                if (!$v_mpn && $variation->get_sku()) {
                    $v_mpn = $variation->get_sku();
                }
                if ($v_mpn) {
                    $lists['mpns'][] = $v_mpn;
                }
            }
        }

        foreach ($lists as $key => $values) {
            $data[$key] = array_values(array_unique(array_map('strval', $values)));
        }

        return $data;
    }
}
