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
 * Page class for the back-end component plugin menu.
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.0
 */
class PluginManagerPage extends AdminManagerPage
{
	/**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $waitForXpath = "//ul/li/a[@href='index.php?option=com_plugins']";

	/**
	 * URL used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $url = 'administrator/index.php?option=com_plugins';

	/**
	 * Array of filter id values for this page
	 *
	 * @var    array
	 * @since  3.0
	 */
	public $filters = array(
			'Select Status' => 'filter_enabled',
			'Select Type' => 'filter_folder',
			'Select Access' => 'filter_access',
			);

	/**
	 * Array of toolbar id values for this page
	 *
	 * @var    array
	 * @since  3.0
	 */
	public $toolbar = array (
			'Edit' => 'toolbar-edit',
			'Enable' => 'toolbar-publish',
			'Disable' => 'toolbar-unpublish',
			'Check In' => 'toolbar-checkin',
			'Options' => 'toolbar-options',
			'Help' => 'toolbar-help',
			);

	/**
	 * Get state  of a Plugin in the Plug-in Manager: Plugin Items screen.
	 *
	 * @param   string   $name	  Plugin Name
	 * 
	 * @return  State of the Plugin //Enabled or Disabled which is equvalent to publish and unpublish at backend
	 */
	public function getState($name)
	{
		$result = false;
		$row = $this->getRowNumber($name);
		$text = $this->driver->findElement(By::xPath("//tbody/tr[" . $row . "]/td[3]/a"))->getAttribute(@onclick);

		if (strpos($text, 'plugins.unpublish') > 0)
		{
			$result = 'published';
		}

		if (strpos($text, 'plugins.publish') > 0)
		{
			$result = 'unpublished';
		}

		return $result;
	}

	/**
	 * Change state of a Plugin  item in the Plugin Manager: Plugin Manager Items screen.
	 *
	 * @param string   $name	   Name of the Plugin
	 * @param string   $state      State of the Plugin
	 *
	 * @return  void
	 */
	public function changePluginState($name, $state = 'published')
	{
		$this->searchFor($name);
		$this->checkAll();

		if (strtolower($state) == 'published')
		{
			$this->clickButton('toolbar-publish');
			$this->driver->waitForElementUntilIsPresent(By::xPath($this->waitForXpath));
		}
		elseif (strtolower($state) == 'unpublished')
		{
			$this->clickButton('toolbar-unpublish');
			$this->driver->waitForElementUntilIsPresent(By::xPath($this->waitForXpath));
		}

		$this->searchFor();
	}

	/**
	 * Get Access of a Plugin  item in the Plugin Manager: Plugin Manager Items screen.
	 *
	 * @param string   $name	   Name of the Plugin
	 * 
	 * @return  PluginAccessLevel
	 */
	public function getPluginAccess($name)
	{
		$row = $this->getRowNumber($name);
		$text = $this->driver->findElement(By::xPath("//tbody/tr[" . $row . "]/td[7]"))->gettext();

		return $text;
	}

	/**
	 * Get Type of a Plugin  item in the Plugin Manager: Plugin Manager Items screen.
	 *
	 * @param string   $name	   Name of the Plugin
	 * 
	 * @return  Plugin type
	 */
	public function getPluginType($name)
	{
		$row = $this->getRowNumber($name);
		$text = $this->driver->findElement(By::xPath("//tbody/tr[" . $row . "]/td[5]"))->gettext();

		return $text;
	}

	/**
	 * Edit a Plugin  item in the Plugin Manager: Plugin Manager Edit Screen.
	 *
	 * @param string   $name	   Name of the Plugin
	 * @param string   $fields	   Input Fields that are to be changed in the form of a array 
	 * 
	 * @return  void
	 */
	public function editPlugin($name,$fields)
	{
		$this->clickItem($name);
		$pluginEditPage = $this->test->getPageObject('PluginEditPage');
		$pluginEditPage->setFieldValues($fields);
		$pluginEditPage->clickButton('toolbar-save');
		$this->test->getPageObject('pluginManagerPage');
		$this->searchFor();
	}
}
