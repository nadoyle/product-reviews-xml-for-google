<?php
if (!defined('ABSPATH')) exit;

abstract class GRF_Provider {
    protected $gtin_key; protected $mpn_key; protected $brand_key;

    public function __construct() {
        $this->gtin_key  = get_option('grf_gtin_key', '_gtin');
        $this->mpn_key   = get_option('grf_mpn_key', '_mpn');
        $this->brand_key = get_option('grf_brand_key', 'brand');
    }

    abstract public function get_reviews();

    protected function get_product_identifiers($product_id) {
        $product = wc_get_product($product_id);
        if (!$product) return false;

        $data = [
            'name' => $product->get_name(), 'url' => $product->get_permalink(), 'sku' => $product->get_sku(),
            'brand' => get_bloginfo('name'), 'gtin' => '', 'mpn' => ''
        ];

        if ($this->brand_key) {
            $brand = $product->get_attribute($this->brand_key) ?: get_post_meta($product_id, $this->brand_key, true);
            if ($brand) $data['brand'] = $brand;
        }
        if ($this->gtin_key) $data['gtin'] = get_post_meta($product_id, $this->gtin_key, true);
        if ($this->mpn_key) $data['mpn'] = get_post_meta($product_id, $this->mpn_key, true);

        return $data;
    }
}