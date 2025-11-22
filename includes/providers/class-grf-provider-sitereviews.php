<?php
if (!defined('ABSPATH')) exit;

class GRF_Provider_SiteReviews extends GRF_Provider {
    public function get_reviews() {
        // Site Reviews uses a Custom Post Type 'site-review'
        $args = [
            'post_type'      => 'site-review',
            'post_status'    => 'publish',
            'posts_per_page' => 2000,
            'meta_query'     => [
                [
                    'key'     => '_assigned_to', // Links review to Product ID
                    'compare' => 'EXISTS'
                ]
            ]
        ];

        $reviews = get_posts($args);
        $data = [];

        foreach ($reviews as $r) {
            $product_id = get_post_meta($r->ID, '_assigned_to', true);
            $product_data = $this->get_product_identifiers($product_id);

            if (!$product_data) continue;

            $rating = get_post_meta($r->ID, '_rating', true);

            $data[] = [
                'id'            => $r->ID,
                'content'       => $r->post_content,
                'rating'        => (int) $rating,
                'reviewer_name' => get_post_meta($r->ID, '_author', true) ?: 'Anonymous',
                'is_verified'   => false, // Site Reviews doesn't always track verification easily
                'timestamp'     => get_the_date('Y-m-d\TH:i:s\Z', $r->ID),
                'url'           => get_permalink($r->ID), // Review URL
                'product_name'  => $product_data['name'],
                'product_url'   => $product_data['url'],
                'identifiers'   => [
                    'sku'   => $product_data['sku'],
                    'brand' => $product_data['brand'],
                    'gtin'  => $product_data['gtin'],
                    'mpn'   => $product_data['mpn']
                ]
            ];
        }
        return $data;
    }
}