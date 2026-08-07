=== Lumina Instagram Feed ===
Contributors: lumina
Tags: instagram, social media, feed, gallery, instagram feed
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A fully customizable Instagram feed with hourly API caching and a powerful design engine for unique layouts.

== Description ==

Lumina Instagram Feed lets you connect your Instagram Business or Creator account and display a beautiful, performance-optimized feed anywhere on your WordPress site.

**Features:**

* Instagram Graph API integration
* Hourly automatic cache refresh via WP-Cron
* Manual cache refresh from admin
* Configurable post count (1–50)
* Toggle display fields: image, caption, date, account, links
* Six layout modes: Grid, Masonry, Carousel, Showcase, Bento Box, Mosaic
* Full design engine: colors, typography, shadows, hover effects, animations
* Custom CSS with feed selector placeholder
* Shortcode and Gutenberg block
* Live preview in Design Studio

== Installation ==

1. Upload the `lumina-instagram-feed` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu
3. Go to **Lumina Instagram → Settings**
4. Enter the **License Key** provided by your agency
5. Click **Test Connection**
6. Customize your design in **Design Studio**
7. Embed with `[lumina_instagram]` or the Gutenberg block

== Agency setup ==

Your agency hosts the Lumina hub and manages Instagram API access. Client sites only need a license key — no Instagram token or User ID on the WordPress site.

== Shortcode ==

`[lumina_instagram]`

Override defaults:

`[lumina_instagram count="8" layout="bento" show_caption="0" show_date="1"]`

== Frequently Asked Questions ==

= How often does the feed update? =

By default, feeds are cached for 1 hour. WP-Cron refreshes the cache hourly. You can also refresh manually from the admin.

= Can I use a personal Instagram account? =

The Instagram Graph API requires a Business or Creator account linked to a Facebook Page.

== Development ==

Run the unit test suite:

```
composer install
composer test
```

Tests cover settings partial-save behavior, curated selection persistence, library merge imports, and legacy selection migration.

== Changelog ==

= 1.0.0 =
* Initial release
