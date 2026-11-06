<?php
if (!defined('ABSPATH')) exit;

class GRF_Provider_Woo extends GRF_Provider {
    public function get_reviews() {
        // Generous Safety Limit: 2500 reviews.
        // Prevents server timeouts on large sites.
        $limit = apply_filters('grf_review_limit', 2500);

        $comments = get_comments([
            'status' => 'approve', 'type' => 'review', 'post_type' => 'product',
            'number' => $limit, 'parent' => 0
        ]);

        $data = [];
        // ... (rest of the file is identical) ...