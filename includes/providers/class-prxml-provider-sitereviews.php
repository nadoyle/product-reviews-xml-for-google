<?php
if (!defined('ABSPATH')) exit;

/**
 * Site Reviews provider class
 */
class PRXML_Provider_SiteReviews extends PRXML_Provider {
    /**
     * Get reviews from Site Reviews plugin
     *
     * @return array Array of review data
     */
    public function get_reviews() {
        // Generous Safety Limit: 2500 reviews
        $limit = apply_filters('prxml_review_limit', 2500);
        $posts_per_page = ($limit === 0) ? -1 : $limit;

        $args = [
            'post_type' => 'site-review',
            'post_status' => 'publish',
            'posts_per_page' => $posts_per_page,
            'meta_query' => [['key' => '_assigned_to', 'compare' => 'EXISTS']], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
        ];

        $reviews = get_posts($args);
        $data = [];

        foreach ($reviews as $r) {
            $product_id = get_post_meta($r->ID, '_assigned_to', true);
            $product_data = $this->get_product_identifiers($product_id);
            if (!$product_data) {
                continue;
            }
            
            $rating = get_post_meta($r->ID, '_rating', true);
            
            $data[] = [
                'id' => $r->ID,
                'content' => $r->post_content,
                'rating' => (int) $rating,
                'reviewer_name' => get_post_meta($r->ID, '_author', true) ?: 'Anonymous',
                'is_verified' => false,
                'timestamp' => get_the_date('Y-m-d\TH:i:s\Z', $r->ID),
                'url' => get_permalink($r->ID),
                'product_name' => $product_data['name'],
                'product_url' => $product_data['url'],
                'identifiers' => [
                    'sku' => $product_data['sku'],
                    'brand' => $product_data['brand'],
                    'gtin' => $product_data['gtin'],
                    'mpn' => $product_data['mpn'],
                ],
            ];
        }
        
        return $data;
    }
}
