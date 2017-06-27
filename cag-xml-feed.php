<?php

/**
 *
 * @link              https://www.superdream.co.uk/
 * @since             0.0.1
 * @package           cag_xml_feed
 *
 * @wordpress-plugin
 * Plugin Name:       CAG XML FEED
 * Plugin URI:        https://github.com/wearesuperdream
 * Description:       A plugin that generates an XML feed from the gravity forms plugin
 * Version:           0.0.1
 * Author:            Superdream
 * Author URI:        https://www.superdream.co.uk/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cag-xml-feed
 * Domain Path:       /languages
 */


class cag_xml_feed {

	/*
	* ADMIN FUNCTIONS - Create and save metabox information
	*/
	
	function __construct(){

		//Create Admin settings section/page
		add_action('gform_field_standard_settings', array($this, 'my_standard_settings'), 10, 2);
		// save the custom field value with the associated Gravity Forms field
		add_action('gform_editor_js', array($this, 'editor_script'), 11, 2);

		add_filter('gform_tooltips', array($this, 'add_xml_tooltips'), 12, 2 );
	}



// add_filter('gform_notification', 'CRM_create_XML_email', 10, 3);
function CRM_create_XML_email($notification, $form, $entry) {

  if($notification["name"] == "CRM Integration"):
  
    $cagXML = array();

    //Make sure it doesn't render the notification as HTML
    $notification['message_format'] = "text";

    if ($form['fields']):
      foreach($form['fields'] as $item):

        // Set the ID
        $entryID = $item['id'];

        // Check that parameter title exists
        if (!empty($item['cagxmlField'])):
          $cagXML[] = array(
            'id' => $entryID,
            'param_title' => $item['cagxmlField'],
            'value' => $entry[$entryID]
          );
        endif;

      endforeach;
    endif;
    
    $prefix = 'FCAS';
    $formTitle = $prefix . ' - ' . $form['title'];

    if ($cagXML):
      $notification['message'] = '<Forms><formName>' . $formTitle . '</formName><parameters>';
      
      foreach ($cagXML as $param):
        $notification['message'] .= '<param title="' . $param['param_title'] . '" type="string">' . $param['value'] . '</param>';
      endforeach;

      $notification['message'] .= '</parameters></Forms>';
    endif;
  
  endif;

  return $notification;
}


function my_standard_settings($position, $form_id) {

  //create settings on position 25 (right after Field Label)
  if (50 !== $position):
    return;
  endif;
  ?>

    <li class="xml_parameter_setting field_setting">
      <label for="field_admin_label">
        <?php esc_html_e('CRM parameter title', 'gravityforms'); ?>
        <?php gform_tooltip('form_field_xml_value'); ?>
      </label>

      <input type="text" id="xml_title_field" onchange="SetFieldProperty('cagxmlField', this.value);">

    </li>
  <?php
}

function editor_script(){
  ?>
  <script type='text/javascript'>
    //adding setting to fields of type "text"

    jQuery.map(fieldSettings, function (el, i) {
      fieldSettings[i] += ', .xml_parameter_setting';
    });

    jQuery(document).on('gform_load_field_settings', function(ev, field) {
      jQuery('#xml_title_field').val(field.cagxmlField || '');
    });
  </script>
  <?php
}


function add_xml_tooltips( $tooltips ) {
   $tooltips['form_field_xml_value'] = "<h6>CRM parameter title</h6>Add in a parameter title for this fields XML. If this field is left blank, it won't be passed to the CRM";
   return $tooltips;
}




} //END CLASS



//Initiate class
$cag_xml_feed = new cag_xml_feed();
