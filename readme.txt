=== Plugin Name ===
Contributors:  samueljesse, uditvirwani
Tags: maps, baidu, baidu maps
Requires at least: 3.0.1
Tested up to: 3.6
Stable tag: 4.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Baidu Maps is a Wordpress plugin to easily integrate Baidu maps in to your site.
Easily add multiple locations with custom markers and data.

== Description ==

This plugin allows to integrate one or more Baidu Maps in any page using shortcodes.

Along with that, this plugin is able customize the map to contain several custom markers
with options to add/change  location coordinates, foreground/background colors, and even the marker icons!


Basic Usage with shortcodes :

To provide a map with the width and height of 500 * 400,
centered at latitude : 116.403, longitude : 39.900
with a zoom of '12'

[bmap width="500" height="400" lat="116.403" lng="39.900" zoom="12"]


Advanced Usage Baidu Maps post-type :

1. Enter your Baidu Developers API Key (if you have not already).
1. Select the "Baidu Maps" post type from the wordpess menu.
1. Click on "Add New".
1. Enter the map details (height, width, zoom and coordinates).
1. Upon publishing, add the new generated shortcode to the page.


Adding a Marker on the map (optional)

1. Click on "Add Marker" below to add a new marker to this map.
1. Enter the marker details (name, description, coordinates, background color, font color).
1. You may also change the default marker image with you own image, click on "Choose Image" to select an image.
1. Click on "Show Marker Details" if you wish to see the details visible at start.



== Installation ==

Follow the steps below for the plugin installation :


1. Upload `baidu-maps.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Create new maps in the "Baidu Maps" post-type
1. Or use the shortcode [bmap ]



== Frequently Asked Questions ==

= Why is the plugin showing an error "You have not entered your Baidu Developers API Key" =

To use Baidu Maps, you need to have a Baidu Developers API Key.
To obtain the API Key please visit : http://lbsyun.baidu.com/apiconsole/key?application=key



== Screenshots ==


== Changelog ==



== Upgrade Notice ==




`<?php code(); // goes in backticks ?>`