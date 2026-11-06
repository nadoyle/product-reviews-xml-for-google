<?php
/**
 * Fired when the plugin is deleted.
 *
 * @package ProductReviewsXMLForGoogle
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete Options
delete_option('grf_feed_slug');
delete_option('grf_provider');
delete_option('grf_refresh_rate');
delete_option('grf_gtin_key');
delete_option('grf_mpn_key');
delete_option('grf_brand_key');

// Delete Transients (Cache)
delete_transient('grf_xml_feed_cache');