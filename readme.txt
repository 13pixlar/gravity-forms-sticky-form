=== Gravity Forms Sticky Form ===
Contributors: asthait, unclhos
Tags: gravity, form, data, field, persistence, sticky, add-on, addon, plugin, plug-in, extension, pre, populate
Requires at least: 2.9.2
Tested up to: 4.0
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin makes your Gravity Forms sticky.

== Description ==

This is a WordPress plugin for <a href="http://www.gravityforms.com/" target="_blank">Gravity Forms</a> that enables forms to be "sticky". A sticky form stays populated with the users submitted data. The data is retrieved from the actual entry. This makes the same entries editable from both back- and front end.

The stycky form is persistent so that when the user returns, all previous data is pre populated with his/hers previous submission.

Gravity Form persistens, or stickyness, is usefull if you have a form that acts as a user profile, company profile or in other similar situations where the data needs to be persistant every time a user visits that form. 

This plugin is based on <h href="https://wordpress.org/plugins/gravity-forms-data-persistence-add-on-reloaded/">Gravity Forms Data Persistence Add-On Reloaded</a>.

**Note:** <a href="http://www.gravityforms.com/" target="_blank">Gravity Forms</a> is required for this plugin.

This plugin is Open Source and pull requests can be made on the <a href="#">Gitgub project page</a>

== Installation ==

<h4>Installation</h4>

1. Upload extracted folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Choose the required sticky settings on the individual form settings page.

== Frequently Asked Questions ==

= Does this work with file upload? =

Yes, to some extent. The plugin supports one file upload per form. To output the link to the file use {upload} in a HTML field. Future versions of this plugin will support multiple files.

= How is this plugin diffrent from similar plugins ?=

Sticky Form stores the data in an actual Gravity Forms entry. The advantage is that the entry can be edited on the back end and the new data will be used to populate the form on the front end. 

== Changelog ==

= 1.0 =
* Initial release