<h1>Gravity Forms Sticky Form</h1>

Sticky Form is a Gravity Forms Add-on that enables forms to be "sticky". A sticky form stays populated with the users submitted data.

<h2>Description</h2>

#### Sticky Form
Sticky Form is a WordPress plugin for <a href="http://www.gravityforms.com/" target="_blank">Gravity Forms</a> that enables forms to be "sticky". A sticky form stays populated with the users submitted data. The data is retrieved from the actual entry. This makes the same entries editable from both back- and front end.

The stycky form is persistent so that when the user returns, all previous data is pre populated with his/hers previous submission.

#### Persistent Gravity Forms
Gravity Forms persistens, or stickyness, is usefull if you have a form that acts as a user profile, company profile or in other similar situations where the data needs to be persistant every time a user visits that form. 

#### Save entry
Sticky Form uses a new Gravity Forms hook to save the submission to the same entry rather than creating a new entry and deleting the old one. This makes read and starred status stick!

#### Developers
This is the fully documented version of the plugin. This plugin is Open Source and pull requests are welcome.

This plugin is based on <h href="https://wordpress.org/plugins/gravity-forms-data-persistence-add-on-reloaded/">Gravity Forms Data Persistence Add-On Reloaded</a>.

**Note:** <a href="http://www.gravityforms.com/" target="_blank">Gravity Forms</a> is required for this plugin.

<h3>Installation</h3>

1. Upload extracted folder to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Choose the required sticky settings on the individual form settings page.

<h3>Frequently Asked Questions</h3>

<h5>Does this work with file upload?</h5>

Yes, to some extent. The plugin supports one file upload per form. To output the link to the file use {upload} in a HTML field. Future versions of this plugin will support multiple files.

<h5>How is this plugin diffrent from similar plugins ?</h5>

Sticky Form stores the data in an actual Gravity Forms entry. The advantage is that the entry can be edited on the back end and the new data will be used to populate the form on the front end. 

Also, Sticky Form does not just delete the old entry and save a new one, thus keeping its read and starred status.


<h5>Some fields do not get updated</h5>

There is a bug in the Gravity Forms api that prevent fields from get saved in the entry. I have patched the bug and submitted the patch to Gravity Forms. In the 

meantime, use an <a href="https://downloads.wordpress.org/plugin/gravity-forms-sticky-form.1.0.1.zip">earlier version</a> of this plugin that uses a different way to save the entries, or apply the patch manually to 'plugins/gravityforms/includes/api.php'

On line '510', remove 
'if (empty($entry_id))
    $entry_id = $entry["id"];'
and replace with
'if (empty($entry_id)) {
    $entry_id = $entry["id"];
}else{
    $entry["id"] = $entry_id;
}'

<h3>Changelog</h3>

**1.0.4**
* Improvment: Use the Gravity Forms API to update form (Please RocketGenius, fix the bug in your code)
* New option: Choose if the entry should be marked as unread upon save

**1.0.3**
* Fixed: Fixed a bug where new forms wouldn't get saved

**1.0.2**
* Update: Save as same entry instead of creating a new one (entry retains its read and starred status)

**1.0.1**
* Fixed: Do not pre-populate if the entry is in trash

**1.0**
* Initial release
