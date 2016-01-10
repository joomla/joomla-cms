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
 * Page class for the back-end menu Plugin manager screen.
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.0
 */
class PluginEditPage extends AdminEditPage
{
	/**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $waitForXpath = "//form[@id='style-form']";

	/**
	 * URL used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $url = 'administrator/index.php?option=com_plugins&view=plugin&layout=edit';

	/**
	 * Array of tabs present on this page
	 *
	 * @var    array
	 * @since  3.0
	 */
	public $tabs = array('general');

	/**
	 * Array of tab labels for this page
	 *
	 * @var    array
	 * @since  3.0
	 */
	public $tabLabels = array('Details','Basic Options');

	// Basic Options screen would be present in some plugins and in some plugins the screen would not be present

	/**
	 * Array of all the field Details of the Edit page, along with the ID and tab value they are present on
	 *
	 * @var array
	 * @since 3.0
	 */
	public $inputFields = array (
			array('label' => 'Status', 'id' => 'jform_enabled', 'type' => 'fieldset', 'tab' => 'general'),
			array('label' => 'Access', 'id' => 'jform_access', 'type' => 'select', 'tab' => 'general'),
			array('label' => 'Ordering', 'id' => 'jformordering', 'type' => 'select', 'tab' => 'general'),
			array('label' => 'Check category deletion', 'id' => 'jform_params_check_categories', 'type' => 'fieldset', 'tab' => 'general'),
			array('label' => 'Email on new site article', 'id' => 'jform_params_email_new_fe', 'type' => 'fieldset', 'tab' => 'general'),
			);
}
