=== Universal Post & RSS Loop ===
Contributors: Universal Loop Team
Tags: rss feed, post grid, rss loop, wpbakery, gutenberg
Requires at least: 5.8
Tested up to: 6.6
Stable tag: 2.0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Unified post grid/list display for WordPress Posts and External RSS Feeds using the exact same card design.

== Description ==

Universal Post & RSS Loop allows WordPress creators to present local WordPress Posts and external RSS Feeds using the exact same card/loop design. Switching between a local post loop and an external RSS feed is seamless and requires zero design rebuilding.

= Key Features =
* **Unified Design System**: One template design renders both native WordPress Posts and RSS Feeds cleanly.
* **No Database Import**: External RSS Feeds are displayed dynamically without cluttering your WordPress database with dummy posts. Direct clicks lead directly to original external source URLs.
* **Gutenberg & WPBakery Ready**: Custom Gutenberg Block and WPBakery Page Builder element included out of the box. No Elementor dependency.
* **Shortcode Support**: Use `[universal_post_rss_loop]` anywhere.
* **Multiple RSS Feeds**: Combine and merge multiple RSS/Atom feeds into a single sorted, deduplicated list.
* **Advanced Deduplication**: Avoid duplicate articles using URL, GUID, or Normalized Title matching algorithms.
* **Built-in RSS Tester**: Troubleshoot connection issues, HTTP status (200, 403, 404), XML syntax, and image extraction right inside WP Admin.
* **Transient Caching Engine**: High-performance caching with clear-cache actions in WP Admin.
* **Theme Overrides**: Override layout and item templates by placing files in `wp-content/themes/YOUR-THEME/universal-post-rss-loop/item.php`.

== Installation ==

1. Upload the `universal-post-rss-loop` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Add the "Universal Post & RSS Loop" Block, WPBakery Element, or Shortcode to your pages.

== Shortcode Example ==

`[universal_post_rss_loop source="rss" feeds="https://example.com/feed/" limit="6" columns="3"]`
