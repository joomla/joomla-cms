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
 * @since       3.0
 */
class TagEditPage extends AdminEditPage
{
	/**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $waitForXpath =  "//form[@id='item-form']";

	/**
	 * URL used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $url = 'administrator/index.php?option=com_tags&view=tag&layout=edit';

	/**
	 * Array of tabs present on this page
	 *
	 * @var    array
	 * @since  3.0
	 */
	public $tabs = array('general', 'publishing', 'metadata');

	/**
	 * Array of tab labels for this page
	 *
	 * @var    array
	 * @since  3.0
	 */
	 public $tabLabels = array('Tag Details', 'Publishing Options', 'Metadata Options');

	/**
	 * Array of all the field Details of the Edit page, along with the ID and tab value they are present on
	 *
	 * @var array
	 * @since 3.0
	 */
	public $inputFields = array (
			array('label' => 'Title', 'id' => 'jform_title', 'type' => 'input', 'tab' => 'general'),
			array('label' => 'Status', 'id' => 'jform_published', 'type' => 'select', 'tab' => 'general'),
			array('label' => 'Access', 'id' => 'jform_access', 'type' => 'select', 'tab' => 'general'),
			array('label' => 'Language', 'id' => 'jform_language', 'type' => 'select', 'tab' => 'general'),
			array('label' => 'Float', 'id' => 'jform_images_float_fulltext', 'type' => 'select', 'tab' => 'general'),
			array('label' => 'Alt', 'id' => 'jform_images_image_fulltext_alt', 'type' => 'input', 'tab' => 'general'),
			array('label' => 'Caption', 'id' => 'jform_images_image_fulltext_caption', 'type' => 'input', 'tab' => 'general'),
	);

}

