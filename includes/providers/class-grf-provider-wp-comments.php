<?php
if (!defined('ABSPATH')) exit;

class GRF_Provider_WP_Comments {
    
    public function get_reviews() {
        // Get Settings
        $gtin_key  = get_option('grf_gtin_key', '_gtin');
        $mpn_key   = get_option('grf_mpn_key', '_mpn');
        $brand_key = get_option('grf_brand_key', 'brand');

        // Fetch WooCommerce Reviews
        $comments = get_comments([
            'status'    => 'approve',
            'type'      => 'review',
            'post_type' => 'product', // Ensure we only get product reviews
            'number'    => 2000, // Limit to prevent timeouts on huge sites
            'parent'    => 0 // Only top level reviews
        ]);

        $data = [];

        foreach ($comments as $c) {
            $product = wc_get_product($c->comment_post_ID);
            
            // Skip if product is deleted or hidden
            if (!$product) continue; 

            $rating = get_comment_meta($c->comment_ID, 'rating', true);
            if (!$rating) continue; // Skip unrated comments

            // Identifier Logic
            $identifiers = [
                'sku'   => $product->get_sku(),
                'brand' => get_bloginfo('name'), // Default fallback
                'gtin'  => '',
                'mpn'   => ''
            ];

            // 1. Try to fetch Brand
            if ($brand_key) {
                // Check if it's an attribute
                $brand_attr = $product->get_attribute($brand_key);
                if ($brand_attr) {
                    $identifiers['brand'] = $brand_attr;
                } else {
                    // Check if it's a custom field
                    $brand_meta = get_post_meta($product->get_id(), $brand_key, true);
                    if ($brand_meta) $identifiers['brand'] = $brand_meta;
                }
            }

            // 2. Try to fetch GTIN
            if ($gtin_key) {
                $identifiers['gtin'] = get_post_meta($product->get_id(), $gtin_key, true);
            }

            // 3. Try to fetch MPN
            if ($mpn_key) {
                $identifiers['mpn'] = get_post_meta($product->get_id(), $mpn_key, true);
            }

            $data[] = [
                'id'            => $c->comment_ID,
                'content'       => $c->comment_content,
                'rating'        => (int) $rating,
                'reviewer_name' => $c->comment_author,
                'is_verified'   => wc_review_is_from_verified_owner($c->comment_ID),
                'timestamp'     => date('Y-m-d\TH:i:s\Z', strtotime($c->comment_date_gmt)),
                'url'           => get_comment_link($c),
                'product_name'  => $product->get_name(),
                'product_url'   => $product->get_permalink(),
                'identifiers'   => $identifiers
            ];
        }

        return $data;
    }
}