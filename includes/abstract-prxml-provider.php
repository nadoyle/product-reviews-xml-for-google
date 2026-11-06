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
        
        // Get GTIN and MPN from meta
        if ($this->gtin_key) {
            $gtin = get_post_meta($product_id, $this->gtin_key, true);
            $data['gtin'] = $gtin ?: '';
        }
        if ($this->mpn_key) {
            $mpn = get_post_meta($product_id, $this->mpn_key, true);
            $data['mpn'] = $mpn ?: '';
        }

        return $data;
    }
}
