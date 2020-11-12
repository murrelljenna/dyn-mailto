Contributors: murrelljenna
Plugin Name: Mailto Templates
Tags: mailto, link, template, contact, email, dynamic
Requires at least: 5.5
Tested up to: 7.2
Requires PHP: 7.2.5
Stable tag: 1.0
Version: 1.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Enables you to create email links that load differently depending on who visits the page, where they visit the page from, where on the site they see the link, and more.

== Description ==

The dyn-mailto Wordpress plugin enables you to create mailto links that load differently depending on who visits the page, where they visit the page from, where on the site they see the link, and more. You create a mailto link by writing a template that, when it comes time to render to someone visiting your website, has the appropriate fields substituted where ever you've designated.

This plugin allows you to write dynamic mailto links that can access information about:
    * The Wordpress user account (if logged in)
    * The Wordpress site itself
    * The Wordpress post that the link is viewed on
    * The user's IP address and geographic location, including country, region and city

In addition to including relevant data in the link, dynamic mailto links can incorporate useful logical constructs, including if statements, loops and randomizing fields.

This plugin makes use of the [FreeGeoIP API](https://freegeoip.app/) to retrieve visitor location for use by your mailto templates. This API will not be called unless you make use of location fields inside a mailto template, and the API will only be called when that specific template is rendered. No other 3rd party APIs are used.
