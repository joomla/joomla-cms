<?php

use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;
use SeleniumClient\WebElement;

/**
 * @package     Joomla.Test
 * @subpackage  Webdriver
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Page class for the back-end menu items manager screen.
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.2
 */
class ContactEditPage extends AdminEditPage
{
	/**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $waitForXpath =  "//form[@id='contact-form']";

	/**
	 * URL used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $url = 'administrator/index.php?option=com_contact&view=contact&layout=edit';

	/**
	 * Array of tabs present on this page
	 *
	 * @var    array
	 * @since  3.2
	 */
	public $tabs = array('details', 'publishing', 'basic', 'params-jbasic', 'params-email', 'metadata');

	/**
	 * Array of tab labels for this page
	 *
	 * @var    array
	 * @since  3.2
	 */
	public $tabLabels = array('Edit Contact', 'Publishing Options', 'Contact Details', 'Display Options', 'Contact form', 'Metadata Options');

	/**
	 * Array of all the field Details of the Edit page, along with the ID and tab value they are present on
	 *
	 * @var array
	 * @since 3.2
	 */
	public $inputFields = array (
			array('label' => 'Name', 'id' => 'jform_name', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Alias', 'id' => 'jform_alias', 'type' => 'input', 'tab' => 'details'),
            array('label' => 'Linked User', 'id' => 'jform_user_id_name', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Category', 'id' => 'jform_catid', 'type' => 'select', 'tab' => 'details'),
			// Ordering only shows on edit, not on a new contact.
			//array('label' => 'Ordering', 'id' => 'jformordering', 'type' => 'select', 'tab' => 'details'),
			array('label' => 'ID', 'id' => 'jform_id', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Miscellaneous Information', 'id' => 'jform_misc', 'type' => 'textarea', 'tab' => 'details'),
			array('label' => 'Created by', 'id' => 'jform_created_by_name', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Created By Alias', 'id' => 'jform_created_by_alias', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Created date', 'id' => 'jform_created', 'type'=>'input', 'tab'=>'publishing'),
			array('label' => 'Start Publishing', 'id' => 'jform_publish_up', 'type'=>'input', 'tab'=>'publishing'),
			array('label' => 'Finish Publishing', 'id' => 'jform_publish_down', 'type'=>'input', 'tab'=>'publishing'),
			array('label' => 'Image', 'id' => 'jform_image', 'type'=>'input', 'tab'=>'basic'),
			array('label' => 'Position', 'id' => 'jform_con_position', 'type'=>'input', 'tab'=>'basic'),
			array('label' => 'Email', 'id' => 'jform_email_to', 'type'=>'input', 'tab'=>'basic'),
			array('label' => 'Address', 'id' => 'jform_address', 'type'=>'textarea', 'tab'=>'basic'),
			array('label' => 'City or Suburb', 'id' => 'jform_suburb', 'type'=>'input', 'tab'=>'basic'),
			array('label' => 'State or Province', 'id' => 'jform_state', 'type'=>'input', 'tab'=>'basic'),
			array('label' => 'Postal / ZIP Code', 'id' => 'jform_postcode', 'type'=>'input', 'tab'=>'basic'),
			array('label' => 'Country', 'id' => 'jform_country', 'type'=>'input', 'tab'=>'basic'),
			array('label' => 'Telephone', 'id' => 'jform_telephone', 'type'=>'input', 'tab'=>'basic'),
			array('label' => 'Mobile', 'id' => 'jform_mobile', 'type'=>'input', 'tab'=>'basic'),
			array('label' => 'Fax', 'id' => 'jform_fax', 'type'=>'input', 'tab'=>'basic'),
			array('label' => 'Website', 'id' => 'jform_webpage', 'type'=>'input', 'tab'=>'basic'),
			array('label' => 'First Sort Field', 'id' => 'jform_sortname1', 'type'=>'input', 'tab'=>'basic'),
			array('label' => 'Second Sort Field', 'id' => 'jform_sortname2', 'type'=>'input', 'tab'=>'basic'),
			array('label' => 'Third Sort Field', 'id' => 'jform_sortname3', 'type'=>'input', 'tab'=>'basic'),
			array('label' => 'Show Category', 'id' => 'jform_params_show_contact_category', 'type'=>'select', 'tab'=>'params-jbasic'),
			array('label' => 'Show Contact List', 'id' => 'jform_params_show_contact_list', 'type'=>'fieldset', 'tab'=>'params-jbasic'),
			array('label' => 'Display format', 'id' => 'jform_params_presentation_style', 'type'=>'select', 'tab'=>'params-jbasic'),
			array('label' => 'Show Tags', 'id' => 'jform_params_show_tags', 'type'=>'select', 'tab'=>'params-jbasic'),
			array('label' => 'Name', 'id' => 'jform_params_show_name', 'type'=>'select', 'tab'=>'params-jbasic'),
			array('label' => 'Contact\'s Position', 'id' => 'jform_params_show_position', 'type'=>'select', 'tab'=>'params-jbasic'),
			array('label' => 'Email', 'id' => 'jform_params_show_email', 'type'=>'select', 'tab'=>'params-jbasic'),
			array('label' => 'Street Address', 'id' => 'jform_params_show_street_address', 'type'=>'select', 'tab'=>'params-jbasic'),
			array('label' => 'City or Suburb', 'id' => 'jform_params_show_suburb', 'type'=>'select', 'tab'=>'params-jbasic'),
			array('label' => 'State or County', 'id' => 'jform_params_show_state', 'type'=>'select', 'tab'=>'params-jbasic'),
			array('label' => 'Postal Code', 'id' => 'jform_params_show_postcode', 'type'=>'select', 'tab'=>'params-jbasic'),
			array('label' => 'Country', 'id' => 'jform_params_show_country', 'type'=>'select', 'tab'=>'params-jbasic'),
			array('label' => 'Telephone', 'id' => 'jform_params_show_telephone', 'type'=>'select', 'tab'=>'params-jbasic'),
			array('label' => 'Mobile phone', 'id' => 'jform_params_show_mobile', 'type'=>'select', 'tab'=>'params-jbasic'),
			array('label' => 'Fax', 'id' => 'jform_params_show_fax', 'type'=>'select', 'tab'=>'params-jbasic'),
			array('label' => 'Webpage', 'id' => 'jform_params_show_webpage', 'type'=>'select', 'tab'=>'params-jbasic'),
			array('label' => 'Misc. Information', 'id' => 'jform_params_show_misc', 'type'=>'select', 'tab'=>'params-jbasic'),
			array('label' => 'Image', 'id' => 'jform_params_show_image', 'type'=>'select', 'tab'=>'params-jbasic'),
			array('label' => 'vCard', 'id' => 'jform_params_allow_vcard', 'type'=>'select', 'tab'=>'params-jbasic'),
			array('label' => 'Show User Articles', 'id' => 'jform_params_show_articles', 'type'=>'select', 'tab'=>'params-jbasic'),
			array('label' => 'Show Profile', 'id' => 'jform_params_show_profile', 'type'=>'select', 'tab'=>'params-jbasic'),
			array('label' => 'Show Links', 'id' => 'jform_params_show_links', 'type'=>'select', 'tab'=>'params-jbasic'),
			array('label' => 'Link A Label', 'id' => 'jform_params_linka_name', 'type'=>'input', 'tab'=>'params-jbasic'),
			array('label' => 'Link A URL', 'id' => 'jform_params_linka', 'type'=>'input', 'tab'=>'params-jbasic'),
			array('label' => 'Link B Label', 'id' => 'jform_params_linkb_name', 'type'=>'input', 'tab'=>'params-jbasic'),
			array('label' => 'Link B URL', 'id' => 'jform_params_linkb', 'type'=>'input', 'tab'=>'params-jbasic'),
			array('label' => 'Link C Label', 'id' => 'jform_params_linkc_name', 'type'=>'input', 'tab'=>'params-jbasic'),
			array('label' => 'Link C URL', 'id' => 'jform_params_linkc', 'type'=>'input', 'tab'=>'params-jbasic'),
			array('label' => 'Link D Label', 'id' => 'jform_params_linkd_name', 'type'=>'input', 'tab'=>'params-jbasic'),
			array('label' => 'Link D URL', 'id' => 'jform_params_linkd', 'type'=>'input', 'tab'=>'params-jbasic'),
			array('label' => 'Link E Label', 'id' => 'jform_params_linke_name', 'type'=>'input', 'tab'=>'params-jbasic'),
			array('label' => 'Link E URL', 'id' => 'jform_params_linke', 'type'=>'input', 'tab'=>'params-jbasic'),
			array('label' => 'Alternative Layout', 'id' => 'jform_params_contact_layout', 'type'=>'select', 'tab'=>'params-jbasic'),
			array('label' => 'Show Contact Form', 'id' => 'jform_params_show_email_form', 'type'=>'select', 'tab'=>'params-email'),
			array('label' => 'Send Copy to Submitter', 'id' => 'jform_params_show_email_copy', 'type'=>'select', 'tab'=>'params-email'),
			array('label' => 'Banned Email', 'id' => 'jform_params_banned_email', 'type'=>'textarea', 'tab'=>'params-email'),
			array('label' => 'Banned Subject', 'id' => 'jform_params_banned_subject', 'type'=>'textarea', 'tab'=>'params-email'),
			array('label' => 'Banned Text', 'id' => 'jform_params_banned_text', 'type'=>'textarea', 'tab'=>'params-email'),
			array('label' => 'Session Check', 'id' => 'jform_params_validate_session', 'type'=>'select', 'tab'=>'params-email'),
			array('label' => 'Custom Reply', 'id' => 'jform_params_custom_reply', 'type'=>'select', 'tab'=>'params-email'),
			array('label' => 'Contact Redirect', 'id' => 'jform_params_redirect', 'type'=>'input', 'tab'=>'params-email'),
			array('label' => 'Meta Description', 'id' => 'jform_metadesc', 'type'=>'textarea', 'tab'=>'metadata'),
			array('label' => 'Meta Keywords', 'id' => 'jform_metakey', 'type'=>'textarea', 'tab'=>'metadata'),
			array('label' => 'Robots', 'id' => 'jform_metadata_robots', 'type'=>'select', 'tab'=>'metadata'),
			array('label' => 'Rights', 'id' => 'jform_metadata_rights', 'type'=>'input', 'tab'=>'metadata'),
		);

}

