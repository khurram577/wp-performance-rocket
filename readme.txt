=== Performance Rocket ===
Contributors: khuram577
Tags: performance, speed optimization, image compression, minify, lazy load, page speed, optimization, caching
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.6.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==

**Performance Rocket** is a simple, lightweight, and effective WordPress speed optimization plugin designed to improve your website’s loading time without any negative impact on functionality.

It automatically optimizes your site by:
- Compressing all existing and newly uploaded images (balanced quality at 82%)
- Enabling lazy loading for images to improve page speed
- Minifying HTML output by removing unnecessary whitespace and comments
- Deferring non-critical JavaScript files

The plugin features a clean and simple admin interface where you can see your current website speed and run full optimization with a single click.

Performance Rocket focuses purely on real performance gains for both **mobile** and **desktop** devices while keeping the plugin lightweight and easy to use.

== Installation ==

= Installation from within WordPress =
1. Visit **Plugins > Add New**.
2. Search for **Performance Rocket**.
3. Install and activate the **Performance Rocket** plugin.

= Manual installation =
1. Upload the entire `performance-rocket` folder to the `/wp-content/plugins/` directory.
2. Visit **Plugins** in your WordPress admin.
3. Activate the **Performance Rocket** plugin.
4. Go to **SpeedOptix** in the admin menu to view current speed and optimize your website.

== Frequently Asked Questions ==

= Will this plugin slow down my website? =
No. Performance Rocket is designed to only improve speed. It has zero negative impact on your website’s functionality or user experience.

= Does it optimize existing images? =
Yes. Clicking "Optimize Website Now" will compress all images in your media library.

= How much faster will my website become? =
Results vary depending on your current images, theme, and plugins. Most users experience 30-60% faster load times. We recommend testing with Google PageSpeed Insights before and after using the plugin.

= Is it compatible with caching plugins? =
Yes, it works well alongside popular caching plugins such as WP Rocket, LiteSpeed Cache, and WP Super Cache.

= Where can I report issues or suggest improvements? =
Please report any issues on the plugin's GitHub repository (if available) or contact the author.

== Changelog ==

= 1.3.0 =
* Improved and simplified admin dashboard UI
* Fixed AJAX handler for reliable bulk image optimization
* Strengthened HTML minification for better performance
* More accurate "Before vs After" speed measurement
* Enhanced stability and error handling

= 1.2.0 =
* Initial public release with core optimization features
* Added bulk image compression
* Added HTML minification and JavaScript deferral
* Added lazy loading support
* Clean and simple admin interface

== Upgrade Notice ==

= 1.1.0 =
Recommended update. Includes important fixes for optimization reliability and a cleaner admin interface.
