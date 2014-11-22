=== Gravity Forms Sticky Form ===
Contributors: fried_eggz
Tags: gravity, form, data, field, persistence, sticky, add-on, addon, plugin, plug-in, extension, pre, populate
Requires at least: 2.9.2
Tested up to: 4.0
Stable tag: 1.0.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A plugin that makes your Gravity Forms stick!

== Description ==

#### Sticky Form
Sticky Form is a WordPress plugin for <a href="http://www.gravityforms.com/" target="_blank">Gravity Forms</a> that enables forms to be "sticky". A sticky form stays populated with the users submitted data. The data is retrieved from the actual entry. This makes the same entries editable from both back- and front end.

The sticky form is persistent so that when the user returns, all previous data is pre populated with his/hers previous submission.

**Note:** There is a bug in earlier versions the Gravity Forms API that prevents Sticky Form from working correctly. Please update Gravity Forms. For more information, please see the <a href="https://wordpress.org/plugins/gravity-forms-sticky-form/faq/">FAQ section</a>.

#### Persistent Gravity Forms
Gravity Forms persistens, or stickyness, is usefull if you have a form that acts as a user profile, company profile or in other similar situations where the data needs to be persistant every time a user visits that form. 

#### Save entry ####
Sticky Form uses a new Gravity Forms hook to save the submission to the same entry rather than creating a new entry and deleting the old one. This makes read and starred status stick!

#### Developers ####	
There is a fully documented version of the plugin on the <a href="https://github.com/13pixlar/gravity-forms-sticky-form">Github project page</a>. This plugin is Open Source and pull requests are welcome.

This plugin is based on <h href="https://wordpress.org/plugins/gravity-forms-data-persistence-add-on-reloaded/">Gravity Forms Data Persistence Add-On Reloaded</a>.

**Note:** <a href="http://www.gravityforms.com/" target="_blank">Gravity Forms</a> is required for this plugin.

== Installation ==

<h4>Installation</h4>

1. Upload extracted folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Choose the required sticky settings on the individual form settings page.

== Frequently Asked Questions ==

= Does this work with file upload? =

Yes, to some extent. The plugin supports one file upload per form. To output the link to the file use {upload} in a HTML field. Future versions of this plugin will support multiple files.

= How is this plugin diffrent from similar plugins? =

Sticky Form stores the data in an actual Gravity Forms entry. The advantage is that the entry can be edited on the back end and the new data will be used to populate the form on the front end. 

Also, Sticky Form does not just delete the old entry and save a new one, thus keeping its read and starred status.

= Some fields do not get updated =

There was a bug in the Gravity Forms api that prevented fields from getting saved in the entry. The bug was fixed in the latest version of Gravity Forms. Make sure you use an <a href="http://www.gravityhelp.com/downloads/">updated version</a>. If you are not able to update Gravity Forms you can easily apply the patch manually to `plugins/gravityforms/includes/api.php`

On line `510`, remove 
`
if (empty($entry_id))
    $entry_id = $entry["id"];
`
and replace with
`
if (empty($entry_id)) {
    $entry_id = $entry["id"];
}else{
    $entry["id"] = $entry_id;
}
`

== Changelog ==

= 1.0.4 =
* Improvment: Use the Gravity Forms API to update form
* New option: Choose if the entry should be marked as unread upon save

= 1.0.3 =
* Fixed: Fixed a bug where new forms wouldn't get saved

= 1.0.2 =
* Update: Save as same entry instead of creating a new one (entry retains its read and starred status)

= 1.0.1 =
* Fixed: Do not pre-populate if the entry is in trash

= 1.0 =
* Initial release