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

= My feed is showing a 404 error? =
Go to the plugin settings and click "Save Changes". This will flush your permalinks and fix the URL.

== Screenshots ==

1. The Settings panel where you configure identifiers and get your URL.

== Changelog ==

= 2.1.0 =
*   Added a fallback to WooCommerce's built-in GTIN, UPC, EAN or ISBN field (WooCommerce 9.2 and later) when the configured GTIN meta key is empty, so more reviews match products in Merchant Center.
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
Reads WooCommerce's built-in GTIN field when no GTIN meta key is set. No settings changes needed.

= 2.0.9 =
Confirmed compatible with WordPress 7.0.

= 2.0.6 =
Increased default review limit to 2,500 for better free tier support.

= 2.0.7 =
* Security hardening: Added strict escaping and sanitization.
* Updated code structure with unique prefixes to prevent conflicts.
* Moved inline JavaScript to external assets file.
