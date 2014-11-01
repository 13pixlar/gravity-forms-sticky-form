<?php
/*
  Plugin Name: Gravity Forms Sticky Form
  Plugin URI: https://github.com/13pixlar/gravity-forms-sticky-form
  Description: This is a <a href="http://www.gravityforms.com/" target="_blank">Gravity Form</a> plugin that enables forms to be "sticky". A sticky form stays populated with the users submitted data retrieved from the actual entry.
  Author: Adam Rehal
  Version: 1.0.3
  Author URI: http://13pixlar.se
  Orginal Plugin by: asthait & unclhos
 */


// Definne global variable $valid and set to true. We need to do this to prevent the form values from disapearing in there is a validation error
global $valid;
$valid = 1;

// Check if the form is valid, and if it is set the variable $valid to true, if not, set to false
add_filter( 'gform_validation', 'test_valid' );

function test_valid($form) {
    global $valid;
    if($form['is_valid']){
        $valid = 1;
    }else{
        $valid = 0;
    }
    return $form;
}


// Lets atempt to pre populate the form
add_filter("gform_pre_render", "sticky_pre_populate_the_form");

function sticky_pre_populate_the_form($form) {
    if ($form['isSticky']) {
        $current_page = GFFormDisplay::get_current_page($form["id"]);
        if ($current_page == 1) {

            global $valid;

            // Get the stored entry ID 
            $entry_id = sticky_getEntryOptionKeyForGF($form);

            // If the form has been submited, is valid and we are not in the preview area
            if($valid && strpos($_SERVER['REQUEST_URI'],'preview') == false) {

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
                                
                                // If we have an upload field
                                if(strpos($value, "uploads/")) {
                                    $upload = $value;
                                }                               
                            }
                        }
                        
                        // Add is_submit_id field
                        $form_id = $form['id'];
                        $form_fields["is_submit_$form_id"] = "1";

                        $_POST = $form_fields;

                    // If no entry is found; unset the stored entry ID
                    }else {
                        update_option($entry_id, "");
                    }
                }
            }
        }
    }

    // Replace {upload} with reference to uploaded file
    if($upload) {
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

// Now we need to save the entry rather then creating a new one
add_filter( 'gform_entry_id_pre_save_lead', 'save_with_same_id', 10, 2 );
function save_with_same_id( $entry_id, $form ) {
    
    $saved_option_value = get_option(sticky_getEntryOptionKeyForGF($form));

    // But only if we allready have a saved entry
    if ($form['isSticky'] && !$form['isEnableMulipleEntry'] && $saved_option_value) {
        
        $update_entry_id = $saved_option_value;
        return $update_entry_id ? $update_entry_id : $entry_id;
    }
}

// If we dont have a saved entry for this form, we need to save the ID of the one that have been created
add_action("gform_post_submission", "sticky_set_post_content", 10, 2);
function sticky_set_post_content($entry, $form) {

    if ($form['isSticky']) {
        
        // Save the entry ID in wp_options if this is the first time the form is submitted by this user.
        if (is_user_logged_in()) {

            $saved_option_key = sticky_getEntryOptionKeyForGF($form);
            $saved_option_value = get_option($saved_option_key);
            
            // If we dont have a saved value, we save the entry ID
            if(!$saved_option_value) {
                update_option($saved_option_key, $entry['id']);
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
                <th>Sticky Form</th>
            <td>
            <input type="checkbox" id="form_sticky_value" onclick="SetFormStickyness();" />
            <label for="form_sticky_value">
                Make this form Sticky              
            </label>
            </td>
        </tr>
        <tr>
            <th></th>
            <td>
            <input type="checkbox" id="form_enable_multiple_entry" onclick="SetFormMultipleEntry();" /> 
            <label for="form_enable_multiple_entry">              
                Enable multi entry from same user while form is sticky
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
                
        jQuery("#form_sticky_value").attr("checked", form.isSticky);       
        jQuery("#form_enable_multiple_entry").attr("checked", form.isEnableMulipleEntry);
        
    </script>
    <?php
}
