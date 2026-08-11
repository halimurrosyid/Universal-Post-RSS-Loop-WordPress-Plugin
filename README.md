# 🚀 Universal Post & RSS Loop (WordPress Plugin)

**Universal Post & RSS Loop** is a powerful, modern, and highly customizable WordPress plugin designed to render both **WordPress Posts** and **External RSS Feeds** using the exact same unified, beautiful card grid design.

- **Author**: Mujaddid Halimurrosyid
- **Author URI**: [https://ajidmujaddid.staff.telkomuniversity.ac.id/](https://ajidmujaddid.staff.telkomuniversity.ac.id/)
- **Version**: 2.0.0
- **License**: GPL-2.0+

---

## ✨ Features at a Glance

### 1. 🔄 Data Source Switch
- **WordPress Posts**: Auto-detects all public Post Types (`post`, `page`, `portfolio`, `product`, custom post types) and Categories.
- **External RSS Feeds**: Seamlessly fetches, parses, and formats external RSS feeds without saving them as posts into your database. Clicking external RSS articles opens the original source URL in a new tab.
- **Multiple Feeds Aggregator**: Merge multiple RSS feed URLs into a single unified stream.

### 2. 🎨 5 Modern Preset Card Designs
- **Classic Card**: Clean border, soft shadow, and structured content.
- **Modern Card**: Floating elevation shadow with blurred glass badge overlay on top of the image.
- **Minimalist**: Flat design with subtle bottom borders.
- **Overlay Hero**: Full dark gradient backdrop over a 100% height cover image with crisp white typography.
- **Glassmorphism**: Elegant frosted glass effect with backdrop blur.

### 3. ⚡ Interactive Features
- **🔍 Live Search Bar**: Instant real-time article filtering as visitors type.
- **🗂️ Category Filter Tabs**: Animated tab filter buttons above the grid.
- **🔄 Pagination Modes**: Choose between *Load More Button* or *Numeric Page Numbers (1, 2, 3...)*.
- **⏱️ Read Time Estimator**: Automatic `⏱️ X min read` metadata calculation.
- **💬 Social Share Buttons**: One-click sharing to WhatsApp, Twitter/X, Facebook, and LinkedIn.
- **⚙️ WP-Cron Pre-caching**: Hourly background pre-caching ensures 0ms instant page loads.

### 4. 🛠️ Page Builder & Shortcode Integration
- **Gutenberg Block**: Custom native block (`upr/universal-post-rss-loop`) with live server-side preview in the editor.
- **WPBakery Page Builder**: Fully integrated via `vc_map()` with organized tabs, dropdowns, and color pickers.
- **Shortcode**: Flexible shortcode `[universal_post_rss_loop]` for classic editor, widgets, or PHP templates.

---

## 💻 Shortcode Examples

### Basic Grid for WordPress Posts
```shortcode
[universal_post_rss_loop source="posts" limit="6" columns="3" card_style="modern"]
```

### Display External RSS Feed
```shortcode
[universal_post_rss_loop source="rss" feed_url="https://news.ycombinator.com/rss" limit="6" card_style="classic"]
```

### Full Interactive Grid with Live Search, Filter Tabs & Load More
```shortcode
[universal_post_rss_loop source="posts" card_style="overlay" show_search_bar="true" show_filter_tabs="true" show_read_time="true" show_social_share="true" pagination_type="load_more" items_per_page="6"]
```

---

## 📋 Complete Shortcode Parameters Reference

| Parameter | Options / Default | Description |
|---|---|---|
| `source` | `posts` \| `rss` (Default: `posts`) | Data source switch |
| `post_type` | `post`, `page`, etc. (Default: `post`) | Target post type |
| `category` | Category Slug | Filter posts by category slug |
| `feed_url` | RSS Feed URL | Single RSS feed source URL |
| `feeds` | Comma/Line separated URLs | Multiple RSS feed source URLs |
| `limit` | `1` to `50` (Default: `6`) | Total number of items |
| `card_style` | `classic`, `modern`, `minimal`, `overlay`, `glass` | Preset design card model |
| `layout` | `grid`, `list`, `horizontal`, `custom` | Display layout mode |
| `columns` | `1` to `6` (Default: `3`) | Grid columns count |
| `image_ratio` | `16:9`, `4:3`, `1:1`, `3:2`, `auto` | Image aspect ratio box |
| `image_hover_effect` | `zoom`, `brighten`, `none` | Image hover animation |
| `show_search_bar` | `true` \| `false` | Enable live search bar |
| `show_filter_tabs` | `true` \| `false` | Enable category filter tabs |
| `show_read_time` | `true` \| `false` | Display estimated read time |
| `show_social_share`| `true` \| `false` | Display social share icons |
| `pagination_type` | `none`, `load_more`, `numeric` | Pagination mode |
| `items_per_page` | `1` to `30` (Default: `6`) | Items visible per page slice |
| `card_bg` | Hex / RGBA Color | Custom card background color |
| `title_color` | Hex / RGBA Color | Custom title font color |
| `button_bg` | Hex / RGBA Color | Custom Read More button background |

---

## 🎨 Theme Override Support

You can easily override the HTML card markup in your WordPress theme!
Simply copy `templates/item.php` into your active theme directory:
`wp-content/themes/YOUR-THEME/universal-post-rss-loop/item.php`

---

## 🔄 Automatic WordPress Updates via GitHub

This plugin features a **native automatic update mechanism** directly integrated with GitHub Releases.

### How it works:
1. Every time a new release tag (e.g. `v2.0.1`) is published on GitHub, WordPress will automatically detect the new version on your `wp-admin/plugins.php` page.
2. An **"Update Available"** notice will appear in your WordPress admin plugin list.
3. Simply click **"Update Now"** to instantly update the plugin directly from GitHub without needing manual ZIP uploads!

---

## 👤 Author & Support

- **Author**: Mujaddid Halimurrosyid
- **Website**: [https://ajidmujaddid.staff.telkomuniversity.ac.id/](https://ajidmujaddid.staff.telkomuniversity.ac.id/)
- **GitHub Repository**: [https://github.com/halimurrosyid/Universal-Post-RSS-Loop-WordPress-Plugin](https://github.com/halimurrosyid/Universal-Post-RSS-Loop-WordPress-Plugin)
