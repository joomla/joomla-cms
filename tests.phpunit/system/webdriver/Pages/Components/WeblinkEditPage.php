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
class WeblinkEditPage extends AdminEditPage
{
	/**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $waitForXpath =  "//form[@id='weblink-form']";

	/**
	 * URL used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $url = 'administrator/index.php?option=com_weblinks&view=weblink&layout=edit';

	/**
	 * Array of tabs present on this page
	 *
	 * @var    array
	 * @since  3.2
	 */
	public $tabs = array('details', 'images', 'publishing', 'attrib-jbasic');

	/**
	 * Array of all the field Details of the Edit page, along with the ID and tab value they are present on
	 *
	 * @var array
	 * @since 3.2
	 */
	public $inputFields = array (
		array('label' => 'Title', 'id' => 'jform_title', 'type' => 'input', 'tab' => 'header'),
		array('label' => 'Alias', 'id' => 'jform_alias', 'type' => 'input', 'tab' => 'header'),
		array('label' => 'URL', 'id' => 'jform_url', 'type' => 'input', 'tab' => 'details'),
		array('label' => 'Description', 'id' => 'jform_description', 'type' => 'textarea', 'tab' => 'details'),
		array('label' => 'Category', 'id' => 'jform_catid', 'type' => 'select', 'tab' => 'details'),
		array('label' => 'Tags', 'id' => 'jform_tags', 'type' => 'select', 'tab' => 'details'),
		array('label' => 'Status', 'id' => 'jform_state', 'type' => 'select', 'tab' => 'details'),
		array('label' => 'Access', 'id' => 'jform_access', 'type' => 'select', 'tab' => 'details'),
		array('label' => 'Language', 'id' => 'jform_language', 'type' => 'select', 'tab' => 'details'),
		array('label' => 'Version Note', 'id' => 'jform_version_note', 'type' => 'input', 'tab' => 'details'),
		array('label' => 'First image', 'id' => 'jform_images_image_first', 'type' => 'input', 'tab' => 'images'),
		array('label' => 'Image Float', 'id' => 'jform_images_float_first', 'type' => 'select', 'tab' => 'images'),
		array('label' => 'Alt text', 'id' => 'jform_images_image_first_alt', 'type' => 'input', 'tab' => 'images'),
		array('label' => 'Caption', 'id' => 'jform_images_image_first_caption', 'type' => 'input', 'tab' => 'images'),
		array('label' => 'Second image', 'id' => 'jform_images_image_second', 'type' => 'input', 'tab' => 'images'),
		array('label' => 'Image Float', 'id' => 'jform_images_float_second', 'type' => 'select', 'tab' => 'images'),
		array('label' => 'Alt text', 'id' => 'jform_images_image_second_alt', 'type' => 'input', 'tab' => 'images'),
		array('label' => 'Caption', 'id' => 'jform_images_image_second_caption', 'type' => 'input', 'tab' => 'images'),
		array('label' => 'Start Publishing', 'id' => 'jform_publish_up', 'type' => 'input', 'tab' => 'publishing'),
		array('label' => 'Finish Publishing', 'id' => 'jform_publish_down', 'type' => 'input', 'tab' => 'publishing'),
		array('label' => 'Created Date', 'id' => 'jform_created', 'type' => 'input', 'tab' => 'publishing'),
		array('label' => 'Created by', 'id' => 'jform_created_by', 'type' => 'input', 'tab' => 'publishing'),
		array('label' => 'Author\'s Alias', 'id' => 'jform_created_by_alias', 'type' => 'input', 'tab' => 'publishing'),
		array('label' => 'Modified Date', 'id' => 'jform_modified', 'type' => 'input', 'tab' => 'publishing'),
		array('label' => 'Modified by', 'id' => 'jform_modified_by', 'type' => 'input', 'tab' => 'publishing'),
		array('label' => 'Revision', 'id' => 'jform_version', 'type' => 'input', 'tab' => 'publishing'),
		array('label' => 'Hits', 'id' => 'jform_hits', 'type' => 'input', 'tab' => 'publishing'),
		array('label' => 'ID', 'id' => 'jform_id', 'type' => 'input', 'tab' => 'publishing'),
		array('label' => 'Meta Description', 'id' => 'jform_metadesc', 'type' => 'textarea', 'tab' => 'publishing'),
		array('label' => 'Meta Keywords', 'id' => 'jform_metakey', 'type' => 'textarea', 'tab' => 'publishing'),
		array('label' => 'External Reference', 'id' => 'jform_xreference', 'type' => 'input', 'tab' => 'publishing'),
		array('label' => 'Robots', 'id' => 'jform_metadata_robots', 'type' => 'select', 'tab' => 'publishing'),
		array('label' => 'Content Rights', 'id' => 'jform_metadata_rights', 'type' => 'input', 'tab' => 'publishing'),
		array('label' => 'Target', 'id' => 'jform_params_target', 'type' => 'select', 'tab' => 'attrib-jbasic'),
		array('label' => 'Width', 'id' => 'jform_params_width', 'type' => 'input', 'tab' => 'attrib-jbasic'),
		array('label' => 'Height', 'id' => 'jform_params_height', 'type' => 'input', 'tab' => 'attrib-jbasic'),
		array('label' => 'Count Clicks', 'id' => 'jform_params_count_clicks', 'type' => 'select', 'tab' => 'attrib-jbasic'),			);

}

