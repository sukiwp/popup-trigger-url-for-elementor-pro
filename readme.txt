=== Popup Trigger URL for Elementor Pro ===
Contributors: sukiwp, daviedr
Tags: 
Requires at least: 4.6
Tested up to: 5.2
Stable tag: 1.0.3
Requires PHP: 5.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Helps you to trigger Elementor Pro's popups (open, close, or toggle) from menus or any kind of link.

== Description ==

Currently you can trigger Elementor Pro's popups by click only on buttons or links inside Elementor. This plugin will help you to trigger your Elementor Pro's popup from anywhere even outside the Elementor's content.

**Please note:** This plugin are helpful when you want to trigger your popup from links outside the Elementor content, e.g. WordPress menu items, links in standard content, etc.

= How it works =

1. Go to `Templates > Popups` and click the `Show URLs` button of the popup you want to show.
2. Copy the URL of the trigger type you chose (`toggle` is the most common type).
3. Paste the URL on any link that you want to use to trigger the popup (e.g. WordPress menu items via `Custom Links`).
4. **IMPORTANT:** You are **required** to set the `Display Conditions` settings of your popup to pages where you want the popup to show. Otherwise, your popup won't show up.

== Frequently Asked Questions ==

= Can I use this plugin without Elementor Pro? =

No, the Popups feature is only available on Elementor Pro version. So you need to build your popups using Elementor Pro.

= Not working, my popup doesn't show =

Please make sure you have set the `Display Conditions` settings of your popup to pages where you want the popup to show. Otherwise, your popup won't show up.

= Does it work with any other popup plugin? =

No, this plugin only supports the Popups module by Elementor Pro, not other 3rd party plugins.

= Does it work with any theme? =

Absolutely! You can use any theme you like. But if you want a really ligthweight, fast, flexible, and fully compatible Elementor theme, you can try [Suki](https://sukiwp.com/).

== Screenshots ==

1. 3 types of trigger URLs of each popup: open, close, and toggle.
2. Use the trigger URL on a WordPress menu item.
3. Use the trigger URL on a link in standard content.

== Installation ==

1. Go to Plugins > Add New.
2. Search for "Popup Trigger URL for Elementor Pro".
3. Click Install button and then Activate the plugin right away.

== Changelog ==

= v1.0.3 =

* Fix JS error when elementor-frontend.js is merged by cache plugins.

= v1.0.2 =

* Add trigger URL to Close popup with "Don't show again" mode.
* Add very tiny javascript for fallback compatibility and to fix error on trigger URLs because Elementor 2.9 just updated their URL generation method.

= v1.0.1 =

* Add notice to remind users to set the "Display Conditions" settings of the popup they choose.

= v1.0.0 =

* Initial release