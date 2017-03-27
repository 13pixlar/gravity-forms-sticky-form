<?php
/*
  Plugin Name: Gravity Forms Sticky Form
  Plugin URI: https://github.com/13pixlar/gravity-forms-sticky-form
  Description: This is a <a href="http://www.gravityforms.com/" target="_blank">Gravity Form</a> plugin that enables forms to be "sticky". A sticky form stays populated with the users submitted data retrieved from the actual entry.
  Author: Adam Rehal
  Version: 1.0.5
  Author URI: http://13pixlar.se
  Orginal Plugin by: asthait & unclhos
 */


// Definne global variable $valid and set to true. We need to do this to prevent the form values from disapearing in there is a validation error
global $valid;
$valid = [];

// Check if the form is valid, and if it is set the variable $valid to true, if not, set to false
add_filter( 'gform_validation', 'test_valid' );

function test_valid($form) {
    global $valid;
    if($form['is_valid']){
        $valid[$form['form']['id']] = 1;
    }else{
        $valid[$form['form']['id']] = 0;
    }
    return $form;
}


// Lets atempt to pre populate the form
add_filter("gform_pre_render", "sticky_pre_populate_the_form");

function isSerialized($str) {
    return ($str == serialize(false) || @unserialize($str) !== false);
}

function sticky_pre_populate_the_form($form) {
    if ($form['isSticky']) {
        $current_page = GFFormDisplay::get_current_page($form["id"]);
        if ($current_page == 1) {

            global $valid;

            // Get the stored entry ID 
            $entry_id = sticky_getEntryOptionKeyForGF($form);

            // If the form has been submited, is valid and we are not in the preview area
            if(!isset($valid[$form['form']['id']]) || ($valid[$form['form']['id']] && strpos($_SERVER['REQUEST_URI'],'preview') == false)) {

                // We have a previously saved entry
                if (get_option($entry_id)) {

                    // Get the entry 
                    $form_fields = RGFormsModel::get_lead(get_option($entry_id));

                    // If an entry is found we need prepare if for insertion into the form
                    if($form_fields  && $form_fields["status"] != "trash") {

                        // Create new correctly formated keys and get rid of the old ones
                        foreach ($form_fields as $key => &$value) {
                            // If the key is numeric we need to change it from [X.X] to [input_X_X]
                            if (is_numeric($key)) {

                                $new_key = str_replace(".", "_", "input_$key");

                                $form_fields[$new_key] = $form_fields[$key];
                                unset($form_fields[$key]);                            
                                
                    if(isSerialized($value))
                {
                    $dump=unserialize($value);
                    foreach($dump as $k => &$v)
                    {
                        if(is_array($v))
                        {
                            unset($form_fields[$new_key]);
                            foreach($v as $k2 => &$v2)
                            {
                                $a[]=$v2;
                            }
                            $form_fields[$new_key]=$a;
                        }
                    }
                    unset($a);
                }

                                // If we have an upload field
                                if(strpos($value, "uploads/")) {
                                    $upload = $value;
                                }                               
                            }
                        }
                        
                        // Add is_submit_id field
                        $form_id = $form['id'];
                        $form_fields["is_submit_$form_id"] = "1";

                        $_POST = array_merge($form_fields,$_POST);
                    // If no entry is found; unset the stored entry ID
                    }else {
                        update_option($entry_id, "");
                    }
                }
            }
        }
    }

    // Replace {upload} with reference to uploaded file
    if (isset($upload)) {
        foreach ($form["fields"] as &$field) {
            foreach ($field as $key => &$value) {
                if($key == "content") {
                    $value = str_replace("{upload}", $upload, $value);              
                }
            }
        }     
    }

    return $form;
}


// If we dont have a saved entry for this form, we need to save the ID of the one that have been created
// If we do have a saved value however, we need to  update the old entry and delete the new entry
add_action("gform_post_submission", "sticky_set_post_content", 10, 2);
function sticky_set_post_content($entry, $form) {

    if ($form['isSticky']) {
        
        if (is_user_logged_in()) {


            $saved_option_key = sticky_getEntryOptionKeyForGF($form);
            $saved_option_value = get_option($saved_option_key);

            // Save the entry ID in wp_options if this is the first time the form is submitted by this user.
            // If we dont have a saved value, we save the entry ID
            if(!$saved_option_value) {
                update_option($saved_option_key, $entry['id']);
            
            //If we have a saved value, update and delete
            }else{
                
                // ...as long as the form doesnt allow multiple entries...
                if(!$form['isEnableMulipleEntry']) {

                    // We dont want to loose our starred and read status when we update
                    $original_entry = RGFormsModel::get_lead($saved_option_value);
                    $entry["is_starred"] = $original_entry["is_starred"];
                    
                    // ...unless the user wants us to
                    if(!isset($form['isMarkUnread'])) {
                        $entry["is_read"] = $original_entry["is_read"];
                    }

                    $success = GFAPI::update_entry($entry, $saved_option_value);
                    $success = GFAPI::delete_entry($entry["id"]);

                // ...and if it does, dont delete or update but save the new entry ID instead so that the sticky form is populated with the latest saved entry.
                }else{
                   update_option($saved_option_key, $entry['id']); 
                }
            }
        }
    }
}


// To save a reference to th entry we need the option key to be unique to both user and form
function sticky_getEntryOptionKeyForGF($form) {

    global $current_user;
    get_currentuserinfo();

    // Lets make the option key unique
    $saved_option_key = $current_user->user_login . '_GF_sticky_' . $form['id'] . '_entry';
    
    return $saved_option_key;
}

// Add Sticky checkbox to the form settings
add_filter("gform_form_settings", "sticky_settings", 50, 2);

function sticky_settings($form_settings, $form) {

    $tr_sticky = '
            <tr>
                <td colspan="2"><h4 class="gf_settings_subgroup_title">Sticky Form</h4></td>
            </tr>
            <tr>
                <th>Sticky Form</th>
            <td>
            <input type="checkbox" id="form_sticky_value" onclick="SetFormStickyness();" />
            <label for="form_sticky_value">
                Make this form Sticky              
            </label>
            </td>
        </tr>
        <tr>
            <tr>
                <th>Multi entry</th>
            <td>
            <input type="checkbox" id="form_enable_multiple_entry" onclick="SetFormMultipleEntry();" /> 
            <label for="form_enable_multiple_entry">              
                Enable multiple entries from same user while form is sticky
            </label>
            </td>
        </tr>
        <tr>
            <tr>
                <th>Mark unread</th>
            <td>
            <input type="checkbox" id="form_mark_unread" onclick="SetFormMarkUnread();" /> 
            <label for="form_mark_unread">              
                Mark entry as unread when updated
            </label>
            </td>
        </tr>';
        
        $form_settings["Form Options"]['sticky'] = $tr_sticky;
        
        return $form_settings;
}

// Action to inject supporting script to the form editor page
add_action("gform_advanced_settings", "sticky_editor_script");
function sticky_editor_script() {
    ?>
    <script type='text/javascript'>
                
        function SetFormStickyness(){
            form.isSticky = jQuery("#form_sticky_value").is(":checked");
        }
        function SetFormMultipleEntry(){
            form.isEnableMulipleEntry = jQuery("#form_enable_multiple_entry").is(":checked");
        }
        function SetFormMarkUnread(){
            form.isMarkUnread = jQuery("#form_mark_unread").is(":checked");
        }
                
        jQuery("#form_sticky_value").attr("checked", form.isSticky);       
        jQuery("#form_enable_multiple_entry").attr("checked", form.isEnableMulipleEntry);
        jQuery("#form_mark_unread").attr("checked", form.isMarkUnread);
        
    </script>
    <?php
}
