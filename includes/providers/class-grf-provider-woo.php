<?php
if (!defined('ABSPATH')) exit;

class GRF_Provider_Woo extends GRF_Provider {
    public function get_reviews() {
        // Fetch standard comments that are reviews on products
        $comments = get_comments([
            'status'    => 'approve',
            'type'      => 'review',
            'post_type' => 'product',
            'number'    => 2000, // Safety limit
            'parent'    => 0
        ]);

        $data = [];

        foreach ($comments as $c) {
            // Get Product Data using the Helper from the Abstract Class
            $product_data = $this->get_product_identifiers($c->comment_post_ID);
            
            // Skip if product doesn't exist anymore
            if (!$product_data) continue;

            // Get Rating
            $rating = get_comment_meta($c->comment_ID, 'rating', true);
            if (!$rating) continue;

            $data[] = [
                'id'            => $c->comment_ID,
                'content'       => $c->comment_content,
                'rating'        => (int) $rating,
                'reviewer_name' => $c->comment_author,
                'is_verified'   => wc_review_is_from_verified_owner($c->comment_ID),
                'timestamp'     => date('Y-m-d\TH:i:s\Z', strtotime($c->comment_date_gmt)),
                'url'           => get_comment_link($c),
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