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
 * Page class for the back-end menu Language manager screen.
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.0
 */
class LanguageEditPage extends AdminEditPage
{
	/**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $waitForXpath = "//form[@id='language-form']";

	/**
	 * URL used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $url = 'administrator/index.php?option=com_languages&view=language&layout=edit';

	/**
	 * Array of tabs present on this page
	 *
	 * @var    array
	 * @since  3.0
	 */
	public $tabs = array('details', 'metadata', 'site_name');

	/**
	 * Array of tab labels for this page
	 *
	 * @var    array
	 * @since  3.0
	 */
	public $tabLabels = array('Details','Metadata Options', 'Site Name');

	/**
	 * Array of all the field Details of the Edit page, along with the ID and tab value they are present on
	 *
	 * @var array
	 * @since 3.0
	 */
	public $inputFields = array (
			array('label' => 'Title', 'id' => 'jform_title', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Title Native', 'id' => 'jform_title_native', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Language Tag', 'id' => 'jform_lang_code', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'URL Language Code', 'id' => 'jform_sef', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Image Prefix', 'id' => 'jform_image', 'type' => 'select', 'tab' => 'details'),
			array('label' => 'Status', 'id' => 'jform_published', 'type' => 'select', 'tab' => 'details'),
			array('label' => 'Access', 'id' => 'jform_access', 'type' => 'select', 'tab' => 'details'),
			array('label' => 'Description', 'id' => 'jform_description', 'type' => 'textarea', 'tab' => 'details'),
			array('label' => 'ID', 'id' => 'jform_lang_id', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Meta Keywords', 'id' => 'jform_metakey', 'type' => 'textarea', 'tab' => 'metadata'),
			array('label' => 'Meta Description', 'id' => 'jform_metadesc', 'type' => 'textarea', 'tab' => 'metadata'),
			array('label' => 'Custom Site Name', 'id' => 'jform_sitename', 'type' => 'input', 'tab' => 'site_name'),
		);
}
