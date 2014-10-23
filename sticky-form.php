<?php
/*
  Plugin Name: Gravity Forms Sticky Form
  Plugin URI: https://github.com/13pixlar/gravity-forms-sticky-form
  Description: This is a <a href="http://www.gravityforms.com/" target="_blank">Gravity Form</a> plugin that enables forms to be "sticky". A sticky form stays populated with the users submitted data retrieved from the actual entry.
  Author: Adam Rehal
  Version: 1.0
  Author URI: http://13pixlar.se
  Orginal Plugin by: asthait & unclhos
 */


// Definne global variable $valid and set to true 
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

add_filter("gform_pre_render", "sticky_pre_populate_the_form");

function sticky_pre_populate_the_form($form) {
    if ($form['isSticky']) {
		$current_page = GFFormDisplay::get_current_page($form["id"]);
		if ($current_page == 1) {

            // Get the entry ID 
			$entry_id = sticky_getEntryOptionKeyForGF($form);

            global $valid;

            // If the form has been submited, is valid and we are not in the preview area
            if($valid && strpos($_SERVER['REQUEST_URI'],'preview') == false) {

                if (get_option($entry_id)) {

                    // Get the entry 
    				$form_fields = RGFormsModel::get_lead(get_option($entry_id));

                    // If an antry is found we need to modify it
                    if($form_fields) {

                        // Helper function to remove non needed items
                        function array_change_key(&$array, $old_key, $new_key) {
                            $array[$new_key] = $array[$old_key];
                            unset($array[$old_key]);
                            return;
                        }

                        // Remove non needed items and format the keys correctly
                        foreach ($form_fields as $key => $value) {
                            if (is_numeric($key)) {
                                array_change_key($form_fields, $key, str_replace(".", "_", "input_$key"));
                                
                                // If the field is an upload
                                if(strpos($value, "uploads/")) {
                                    $upload = $value;
                                }
                            } else {
                                unset($form_fields[$key]);
                            }
                        }
                        
                        // Add is_submit_id field
                        $form_id = $form['id'];
                        $form_fields["is_submit_$form_id"] = "1";
                        $_POST = $form_fields;
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

add_action("gform_post_submission", "sticky_set_post_content", 10, 2);

function sticky_set_post_content($entry, $form) {

    if ($form['isSticky']) {
        
        //Update form data in wp_options table
        if (is_user_logged_in()) {

            $entry_id = sticky_getEntryOptionKeyForGF($form);
            if (get_option($entry_id)) {
                
                //Delete old entry from GF tables
                if (!$form['isEnableMulipleEntry']) {
                   RGFormsModel::delete_lead(get_option($entry_id));
                }
            }
            update_option($entry_id, $entry['id']);
        }
    }
}

function sticky_getEntryOptionKeyForGF($form) {

    global $current_user;
    get_currentuserinfo();

    // We need to make the option key unique
    $option_key = $current_user->user_login . '_GF_sticky_' . $form['id'] . '_entry';
    
    return $option_key;
}

// Add Sticky checkbox to the form settings
add_filter("gform_form_settings", "sticky_settings", 50, 2);

function sticky_settings($form_settings, $form) {

	$tr_sticky = '
        	<tr>
            	<th>Sticky</th>
        	<td>
            <input type="checkbox" id="form_sticky_value" onclick="SetFormStickyness();" />
            <label for="form_sticky_value">
                Make form Sticky              
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
