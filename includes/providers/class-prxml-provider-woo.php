<?php
if (!defined('ABSPATH')) exit;

/**
 * WooCommerce reviews provider class
 */
class PRXML_Provider_Woo extends PRXML_Provider {
    /**
     * Get reviews from WooCommerce
     *
     * @return array Array of review data
     */
    public function get_reviews() {
        // Generous Safety Limit: 2500 reviews
        // Prevents server timeouts on large sites
        $limit = apply_filters('prxml_review_limit', 2500);

        $comments = get_comments([
            'status' => 'approve',
            'type' => 'review',
            'post_type' => 'product',
            'number' => $limit,
            'parent' => 0,
        ]);

        $data = [];
        
        foreach ($comments as $c) {
            $product_data = $this->get_product_identifiers($c->comment_post_ID);
            if (!$product_data) {
                continue;
            }

            $rating = get_comment_meta($c->comment_ID, 'rating', true);
            if (!$rating) {
                continue;
            }

            $data[] = [
                'id' => $c->comment_ID,
                'content' => $c->comment_content,
                'rating' => (int) $rating,
                'reviewer_name' => $c->comment_author,
                'is_verified' => wc_review_is_from_verified_owner($c->comment_ID),
                'timestamp' => gmdate('Y-m-d\TH:i:s\Z', strtotime($c->comment_date_gmt)),
                'url' => get_comment_link($c),
                'product_name' => $product_data['name'],
                'product_url' => $product_data['url'],
                'identifiers' => [
                    'sku' => $product_data['sku'],
                    'brand' => $product_data['brand'],
                    'gtin' => $product_data['gtin'],
                    'mpn' => $product_data['mpn'],
                    'gtins' => $product_data['gtins'],
                    'mpns' => $product_data['mpns'],
                    'skus' => $product_data['skus'],
                ],
            ];
        }
        
        return $data;
    }
}
