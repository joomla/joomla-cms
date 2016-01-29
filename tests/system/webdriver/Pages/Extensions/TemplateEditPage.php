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
 * Page class for the back-end menu Template manager screen.
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.0
 */
class TemplateEditPage extends AdminEditPage
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
	protected $url = 'administrator/index.php?option=com_templates&view=style&layout=edit';

	/**
	 * Array of tabs present on this page, it depends on the Style that we chose sometimes it is just Details,
	 * some times Both options and Details
	 *
	 * @var    array
	 * @since  3.0
	 */
	public $tabs = array('details');

	/**
	 * Array of tab labels for this page
	 *
	 * @var    array
	 * @since  3.0
	 */
	public $tabLabels = array('Details','Options');

	// Options screen would be present in some plugins and in some plugins the screen would not be present

	/**
	 * Array of all the field Details of the Edit page, along with the ID and tab value they are present on
	 *
	 * @var array
	 * @since 3.0
	 */
	public $inputFields = array (
			array('label' => 'Style Name', 'id' => 'jform_title', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Default', 'id' => 'jform_home', 'type' => 'fieldset', 'tab' => 'details'),
			array('lable' => 'Show Site Name', 'id' => 'jform_params_showSiteName', 'type' => 'fieldset', 'tab' => 'options'),
			array('lable' => 'Select Colour', 'id' => 'jform_params_colourChoice', 'type' => 'select', 'tab' => 'options'),
			array('lable' => 'Bold Text', 'id' => 'jform_params_boldText', 'type' => 'fieldset', 'tab' => 'options'),
			);
}
