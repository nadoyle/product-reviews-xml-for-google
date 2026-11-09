=== Product Reviews XML for Google Merchant Center ===
Contributors: tetherties
Tags: woocommerce, google merchant center, product reviews, xml feed, google shopping
Requires at least: 5.0
Tested up to: 7.1
Stable tag: 2.1.0
Requires PHP: 7.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://tetherties.com

Generate a compliant XML Feed of your WooCommerce product reviews for Google Merchant Center. Display stars on Google Shopping ads.

== Description ==

This plugin generates a strictly formatted XML feed of your product reviews that meets Google Merchant Center requirements. By submitting this feed to Google, you can enable "Product Ratings" (stars) on your Google Shopping PLAs (Product Listing Ads).

**Key Features:**
*   **Universal Support:** Works with native WooCommerce reviews, Customer Reviews for WooCommerce (CusRev), YITH Advanced Reviews, and Site Reviews.
*   **Strict Schema:** Generates the specific nested XML structure Google requires (GTINs, MPNs, Namespaces).
*   **Smart Caching:** Caches the XML feed to reduce server load. Default refresh is weekly (recommended by Google).
*   **Product Identifiers:** Maps your GTIN, MPN, and Brand fields to ensure reviews match your products in Google. If you have not set a GTIN meta key, the plugin reads WooCommerce's built-in GTIN, UPC, EAN or ISBN field (WooCommerce 9.2 and later).

**Performance & Limits:**
By default, this plugin includes your 2,500 most recent reviews. This safety cap ensures that generating the XML file does not crash your server due to memory exhaustion.
*   *Developers:* You can increase or remove this limit using the `prxml_review_limit` filter.

**Who makes this:**
TetherTies builds cable tethers that keep adapters and dongles attached to the cable they belong to. We wrote this plugin to put review stars on our own Google Shopping listings, then released it for free. If you run conference rooms or AV spaces, our [cable tether buying guide](https://tetherties.com/tetherties-cable-tethers-buying-guide/) covers the options. Support questions about the plugin belong in the support forum here.

== Installation ==

1.  Upload the plugin files to the `/wp-content/plugins/product-reviews-xml-for-google` directory.
2.  Activate the plugin through the 'Plugins' screen in WordPress.
3.  Go to **Settings > GMC Reviews XML** to configure.
4.  Copy the generated Feed URL.
5.  Log in to Google Merchant Center > Analytics > Reviews > Product Reviews > Feeds.
6.  Add a new feed using "Scheduled Fetch" and paste your URL.

== Frequently Asked Questions ==

= Which review plugins are supported? =
We natively support:
1. WooCommerce Standard Reviews
2. Customer Reviews for WooCommerce (CusRev)
3. YITH WooCommerce Advanced Reviews
4. Site Reviews
5. Judge.me / Yotpo (Only if "Sync to WooCommerce" is enabled in their settings).

= Where do I enter a GTIN for my products? =
On WooCommerce 9.2 and later, edit a product and open the Inventory tab. The "GTIN, UPC, EAN, or ISBN" field is read automatically. On older versions, or if you store GTINs in a custom field, enter that meta key in the plugin settings.

= Why does Merchant Center say my reviews have a missing or invalid product ID? =
Google matches each review to a product in your product feed using a GTIN, or a brand plus an MPN. A review that carries neither cannot be matched, and the whole feed shows errors. Check three things:

1. Open your feed URL and look inside one review's `<product_ids>` block. You should see `<gtins>` or `<mpns>`, plus `<brands>`.
2. Compare those values with the same product in your Merchant Center product feed (Products, then the product). The GTIN or the MPN has to be identical.
3. Set the GTIN Key and MPN Key in the plugin settings to the fields that hold those values in your store.

= My products have variations. What does the plugin send? =
Google's product feed usually lists each variation as its own product, with its own ID, SKU and GTIN, while customers review the parent product. From version 2.1.0 the plugin sends the ID, SKU, MPN and GTIN of every variation inside the review for the parent product, so the review matches whichever variation Google lists. The feed is longer as a result; a product with 22 variations adds about 22 entries per list. To turn this off, return false from the `prxml_include_variations` filter.

= What should I enter for GTIN Key and MPN Key? =
GTIN Key is the name of the custom field that holds the GTIN. WooCommerce 9.2 and later has a built-in GTIN field that the plugin reads when this key is empty. Many GTIN plugins store the value under their own key, and variations often use a different key from simple products. To find yours, take one GTIN from your Merchant Center product feed and search the `wp_postmeta` table for it: `SELECT post_id, meta_key FROM wp_postmeta WHERE meta_value = 'YOUR-GTIN';`. The `meta_key` in the result is the value to enter. One example is `hwp_var_gtin`, which one GTIN plugin uses on variations.

MPN Key works the same way. If your product feed uses the SKU as the MPN, enter `_sku`. Variations with no value under that key fall back to their own SKU.

Brand Key is the name of a product attribute or custom field. If it is empty or the field has no value, the plugin sends your site name as the brand.

= I changed a setting but the feed looks the same. =
The plugin stores the generated feed and refreshes it weekly or daily, depending on your setting. Changing the provider, GTIN Key, MPN Key or Brand Key clears that stored copy. If you changed anything else, such as reviews, product data or the plugin files, click "Clear Feed Cache Now" on the settings page. Hosts and CDNs can also keep their own copy of the page: on WP Engine, clear all caches in the User Portal, and on Cloudflare purge the feed URL. Add a throwaway query string such as `?x=1` to the feed URL to bypass your browser's copy while testing. Then use "Fetch now" on the feed in Merchant Center.

= Merchant Center rejects some reviews for containing a URL. =
Google refuses reviews whose text contains a web address, including bare domains such as example.com. Edit the review in WooCommerce under Products, Reviews, remove the address, and clear the feed cache. Merchant Center's "View examples" link on the error lists the affected reviews.

= Some reviews are for products that are not in my Merchant Center product feed. =
A review can only show stars on a product Google already has. Reviews on products that are not in your product feed, such as a sample package or a custom order page, will keep showing errors. They do not stop the other reviews from being used.

= My feed is showing a 404 error? =
Go to the plugin settings and click "Save Changes". This will flush your permalinks and fix the URL.

== Screenshots ==

1. The Settings panel where you configure identifiers and get your URL.

== Changelog ==

= 2.1.0 =
*   Added a troubleshooting section to the FAQ and help text under the identifier fields on the settings page.
*   Added a fallback to WooCommerce's built-in GTIN, UPC, EAN or ISBN field (WooCommerce 9.2 and later) when the configured GTIN meta key is empty, so more reviews match products in Merchant Center.
*   Reviews on variable products now list the ID, SKU, MPN and GTIN of every variation, so they match the variation-level rows in a Merchant Center product feed. A filter, `prxml_include_variations`, turns this off.
*   Tested up to WordPress 7.1.
*   Added a GTIN question to the FAQ.

= 2.0.9 =
*   Tested and confirmed compatible with WordPress 7.0.
*   Updated "Tested up to" to 7.0.

= 2.0.8 =
*   Fixed sanitization to use sanitize_title_with_dashes() as recommended by WordPress.org review.
*   Improved DONOTCACHEPAGE constant scoping to only affect feed rendering.
*   Added preg_quote() for regex security in rewrite rules.
*   Added WooCommerce dependency check with admin notice.
*   Fixed all escaping to use esc_html_e() instead of _e().
*   Changed prefix from 'grf' to 'prxml' (5 characters) to meet WordPress requirements.
*   Moved JavaScript to external file with proper wp_enqueue_script().
*   Added comprehensive error handling and PHPDoc blocks.

= 2.0.6 =
*   Increased safety limit to 2,500 reviews (Generous Free Tier).

= 2.0.5 =
*   Updated Author description.

= 2.0.4 =
*   Added "Copy URL" button.
*   Added connection instructions.

= 2.0.3 =
*   Updated default feed slug.
*   Updated default refresh rate to Weekly.

= 2.0.0 =
*   Full security hardening.
*   Added proper uninstaller.
*   Trademark-safe slug update.

= 1.0.0 =
*   Initial release.

== Upgrade Notice ==

= 2.1.0 =
Reads WooCommerce's built-in GTIN field when no GTIN meta key is set, and lists every variation's identifiers on reviews of variable products. No settings changes needed. The feed cache clears when you save settings, or use Clear Cache after updating.

= 2.0.9 =
Confirmed compatible with WordPress 7.0.

= 2.0.6 =
Increased default review limit to 2,500 for better free tier support.

= 2.0.7 =
* Security hardening: Added strict escaping and sanitization.
* Updated code structure with unique prefixes to prevent conflicts.
* Moved inline JavaScript to external assets file.
