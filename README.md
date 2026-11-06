![Product Reviews XML for Google Merchant Center](.wordpress-org/banner-772x250.jpg)

# Product Reviews XML for Google Merchant Center

A WordPress plugin that turns WooCommerce product reviews into the XML feed Google Merchant Center needs to show star ratings on Shopping ads.

Live on WordPress.org: [wordpress.org/plugins/product-reviews-xml-for-google](https://wordpress.org/plugins/product-reviews-xml-for-google/)

## Why it exists

I built it for [TetherTies](https://tetherties.com), my cable-security hardware business, to get review stars onto our Google Shopping listings. Then I took it through WordPress.org's plugin review and published it free.

## What it does

- Reads reviews from native WooCommerce, Customer Reviews for WooCommerce, YITH Advanced Reviews, and Site Reviews, each through its own provider class.
- Writes Google's product reviews schema with GTIN, MPN, and brand on every review, so Google can match ratings to products.
- Serves the feed at a pretty URL through a rewrite rule and caches it, refreshing weekly by default.
- Caps output at 2,500 recent reviews so a big store can't exhaust memory. Developers can raise it with the `prxml_review_limit` filter.

## Layout

```
product-reviews-xml-for-google.php   bootstrap, dependency check
includes/class-prxml-feed.php        rewrite rule, caching, XML output
includes/class-prxml-admin.php       settings page
includes/abstract-prxml-provider.php shared product-identifier logic
includes/providers/                  one adapter per review plugin
uninstall.php                        removes options and cached feed
```

## History

Each commit up to 2.0.9 is one release. The 2.0.8 commit holds the round of fixes from the WordPress.org plugin review: the `grf` prefix became `prxml`, inline JavaScript moved to an enqueued file, and sanitization and escaping were tightened. The commit after 2.0.9 adds a fallback to WooCommerce's native GTIN field and hasn't shipped yet.

## License

GPLv2 or later, as WordPress.org requires.
