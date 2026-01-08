<?php
/**
 * Fired when the plugin is deleted.
 *
 * @package ProductReviewsXMLForGoogle
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Updated to delete the new 'prxml_' options
delete_option('prxml_feed_slug');
delete_option('prxml_provider');
delete_option('prxml_refresh_rate');
delete_option('prxml_gtin_key');
delete_option('prxml_mpn_key');
delete_option('prxml_brand_key');

delete_transient('prxml_xml_feed_cache');