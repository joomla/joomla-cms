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
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
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
	public $tabs = array('details', 'misc', 'publishing', 'attrib-display', 'attrib-email');

	/**
	 * Array of all the field Details of the Edit page, along with the ID and tab value they are present on
	 *
	 * @var array
	 * @since 3.2
	 */
	public $inputFields = array (
						array('label' => 'Name', 'id' => 'jform_name', 'type' => 'input', 'tab' => 'header'),
			array('label' => 'Alias', 'id' => 'jform_alias', 'type' => 'input', 'tab' => 'header'),
			array('label' => 'Linked User', 'id' => 'jform_user_id', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Image', 'id' => 'jform_image', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Position', 'id' => 'jform_con_position', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Email', 'id' => 'jform_email_to', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Address', 'id' => 'jform_address', 'type' => 'textarea', 'tab' => 'details'),
			array('label' => 'City or Suburb', 'id' => 'jform_suburb', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'State or Province', 'id' => 'jform_state', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Postal/ZIP Code', 'id' => 'jform_postcode', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Country', 'id' => 'jform_country', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Telephone', 'id' => 'jform_telephone', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Mobile', 'id' => 'jform_mobile', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Fax', 'id' => 'jform_fax', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Website', 'id' => 'jform_webpage', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'First Sort Field', 'id' => 'jform_sortname1', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Second Sort Field', 'id' => 'jform_sortname2', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Third Sort Field', 'id' => 'jform_sortname3', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Status', 'id' => 'jform_published', 'type' => 'select', 'tab' => 'details'),
			array('label' => 'Category', 'id' => 'jform_catid', 'type' => 'select', 'tab' => 'details'),
			array('label' => 'Featured', 'id' => 'jform_featured', 'type' => 'fieldset', 'tab' => 'details'),
			array('label' => 'Access', 'id' => 'jform_access', 'type' => 'select', 'tab' => 'details'),
			array('label' => 'Language', 'id' => 'jform_language', 'type' => 'select', 'tab' => 'details'),
			array('label' => 'Tags', 'id' => 'jform_tags', 'type' => 'select', 'tab' => 'details'),
			array('label' => 'Version Note', 'id' => 'jform_version_note', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Miscellaneous Information', 'id' => 'jform_misc', 'type' => 'textarea', 'tab' => 'misc'),
			array('label' => 'Start Publishing', 'id' => 'jform_publish_up', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Finish Publishing', 'id' => 'jform_publish_down', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Created Date', 'id' => 'jform_created', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Created By', 'id' => 'jform_created_by', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Created By Alias', 'id' => 'jform_created_by_alias', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Modified Date', 'id' => 'jform_modified', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Modified By', 'id' => 'jform_modified_by', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Revision', 'id' => 'jform_version', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Hits', 'id' => 'jform_hits', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'ID', 'id' => 'jform_id', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Meta Description', 'id' => 'jform_metadesc', 'type' => 'textarea', 'tab' => 'publishing'),
			array('label' => 'Meta Keywords', 'id' => 'jform_metakey', 'type' => 'textarea', 'tab' => 'publishing'),
			array('label' => 'Robots', 'id' => 'jform_metadata_robots', 'type' => 'select', 'tab' => 'publishing'),
			array('label' => 'Rights', 'id' => 'jform_metadata_rights', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Show Category', 'id' => 'jform_params_show_contact_category', 'type' => 'select', 'tab' => 'attrib-display'),
			array('label' => 'Show Contact List', 'id' => 'jform_params_show_contact_list', 'type' => 'select', 'tab' => 'attrib-display'),
			array('label' => 'Display Format', 'id' => 'jform_params_presentation_style', 'type' => 'select', 'tab' => 'attrib-display'),
			array('label' => 'Show Tags', 'id' => 'jform_params_show_tags', 'type' => 'select', 'tab' => 'attrib-display'),
			array('label' => 'Name', 'id' => 'jform_params_show_name', 'type' => 'select', 'tab' => 'attrib-display'),
			array('label' => 'Contact\'s Position', 'id' => 'jform_params_show_position', 'type' => 'select', 'tab' => 'attrib-display'),
			array('label' => 'Email', 'id' => 'jform_params_show_email', 'type' => 'select', 'tab' => 'attrib-display'),
			array('label' => 'Street Address', 'id' => 'jform_params_show_street_address', 'type' => 'select', 'tab' => 'attrib-display'),
			array('label' => 'City or Suburb', 'id' => 'jform_params_show_suburb', 'type' => 'select', 'tab' => 'attrib-display'),
			array('label' => 'State or County', 'id' => 'jform_params_show_state', 'type' => 'select', 'tab' => 'attrib-display'),
			array('label' => 'Postal Code', 'id' => 'jform_params_show_postcode', 'type' => 'select', 'tab' => 'attrib-display'),
			array('label' => 'Country', 'id' => 'jform_params_show_country', 'type' => 'select', 'tab' => 'attrib-display'),
			array('label' => 'Telephone', 'id' => 'jform_params_show_telephone', 'type' => 'select', 'tab' => 'attrib-display'),
			array('label' => 'Mobile Phone', 'id' => 'jform_params_show_mobile', 'type' => 'select', 'tab' => 'attrib-display'),
			array('label' => 'Fax', 'id' => 'jform_params_show_fax', 'type' => 'select', 'tab' => 'attrib-display'),
			array('label' => 'Webpage', 'id' => 'jform_params_show_webpage', 'type' => 'select', 'tab' => 'attrib-display'),
			array('label' => 'Misc. Information', 'id' => 'jform_params_show_misc', 'type' => 'select', 'tab' => 'attrib-display'),
			array('label' => 'Image', 'id' => 'jform_params_show_image', 'type' => 'select', 'tab' => 'attrib-display'),
			array('label' => 'vCard', 'id' => 'jform_params_allow_vcard', 'type' => 'select', 'tab' => 'attrib-display'),
			array('label' => 'Show User Articles', 'id' => 'jform_params_show_articles', 'type' => 'select', 'tab' => 'attrib-display'),
			array('label' => '# Articles to List', 'id' => 'jform_params_articles_display_num', 'type' => 'select', 'tab' => 'attrib-display'),
			array('label' => 'Show Profile', 'id' => 'jform_params_show_profile', 'type' => 'select', 'tab' => 'attrib-display'),
			array('label' => 'Show Links', 'id' => 'jform_params_show_links', 'type' => 'select', 'tab' => 'attrib-display'),
			array('label' => 'Link A Label', 'id' => 'jform_params_linka_name', 'type' => 'input', 'tab' => 'attrib-display'),
			array('label' => 'Link A URL', 'id' => 'jform_params_linka', 'type' => 'input', 'tab' => 'attrib-display'),
			array('label' => 'Link B Label', 'id' => 'jform_params_linkb_name', 'type' => 'input', 'tab' => 'attrib-display'),
			array('label' => 'Link B URL', 'id' => 'jform_params_linkb', 'type' => 'input', 'tab' => 'attrib-display'),
			array('label' => 'Link C Label', 'id' => 'jform_params_linkc_name', 'type' => 'input', 'tab' => 'attrib-display'),
			array('label' => 'Link C URL', 'id' => 'jform_params_linkc', 'type' => 'input', 'tab' => 'attrib-display'),
			array('label' => 'Link D Label', 'id' => 'jform_params_linkd_name', 'type' => 'input', 'tab' => 'attrib-display'),
			array('label' => 'Link D URL', 'id' => 'jform_params_linkd', 'type' => 'input', 'tab' => 'attrib-display'),
			array('label' => 'Link E Label', 'id' => 'jform_params_linke_name', 'type' => 'input', 'tab' => 'attrib-display'),
			array('label' => 'Link E URL', 'id' => 'jform_params_linke', 'type' => 'input', 'tab' => 'attrib-display'),
			array('label' => 'Alternative Layout', 'id' => 'jform_params_contact_layout', 'type' => 'select', 'tab' => 'attrib-display'),
			array('label' => 'Show Contact Form', 'id' => 'jform_params_show_email_form', 'type' => 'select', 'tab' => 'attrib-email'),
			array('label' => 'Send Copy to Submitter', 'id' => 'jform_params_show_email_copy', 'type' => 'select', 'tab' => 'attrib-email'),
			array('label' => 'Banned Email', 'id' => 'jform_params_banned_email', 'type' => 'textarea', 'tab' => 'attrib-email'),
			array('label' => 'Banned Subject', 'id' => 'jform_params_banned_subject', 'type' => 'textarea', 'tab' => 'attrib-email'),
			array('label' => 'Banned Text', 'id' => 'jform_params_banned_text', 'type' => 'textarea', 'tab' => 'attrib-email'),
			array('label' => 'Session Check', 'id' => 'jform_params_validate_session', 'type' => 'select', 'tab' => 'attrib-email'),
			array('label' => 'Custom Reply', 'id' => 'jform_params_custom_reply', 'type' => 'select', 'tab' => 'attrib-email'),
			array('label' => 'Contact Redirect', 'id' => 'jform_params_redirect', 'type' => 'input', 'tab' => 'attrib-email'),
			);

}

