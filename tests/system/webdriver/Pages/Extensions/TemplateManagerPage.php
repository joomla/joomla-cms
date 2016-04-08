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
 * Page class for the back-end component Template menu.
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.0
 */
class TemplateManagerPage extends AdminManagerPage
{
	/**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $waitForXpath = "//ul/li/a[@href='index.php?option=com_templates']";

	/**
	 * URL used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $url = 'administrator/index.php?option=com_templates';

	/**
	 * Array of filter id values for this page
	 *
	 * @var    array
	 * @since  3.0
	 */
	public $filters = array(
			'Select Template' => 'filter_template',
			'Select Location' => 'filter_client_id',
			);

	/**
	 * Array of toolbar id values for this page
	 *
	 * @var    array
	 * @since  3.0
	 */
	public $toolbar = array (
			'Make Default' => 'toolbar-star',
			'Edit' => 'toolbar-edit',
			'Duplicate' => 'toolbar-copy',
			'Delete' => 'toolbar-delete',
			'Options' => 'toolbar-options',
			'Help' => 'toolbar-help',
			'Save as Copy' => 'toolbar-save-copy',
			);

	/**
	 * Copy Style from the Template Manager Screen
	 * @param string   $name	  Template Name whose Copy is to be made
	 *
	 * @return  void
	 */
	public function copyStyle($name)
	{
		$this->searchFor($name);
		$row_number = $this->getRowNumber($name) - 1;
		$el = $this->driver->findElement(By::xPath(".//input[@id='cb" . $row_number . "']"));
		$el->click();
		$this->clickButton('toolbar-copy');
		$this->driver->waitForElementUntilIsPresent(By::xPath($this->waitForXpath));
	}

	/**
	 * function to delete style
	 *
	 * @param   string   $name  stores name of the style
	 *
	 * @return void
	 */
	public function deleteStyle($name)
	{
		$this->searchFor($name);
		$row_number = $this->getRowNumber($name) - 1;
		$el = $this->driver->findElement(By::xPath(".//input[@id='cb" . $row_number . "']"));
		$el->click();
		$this->clickButton('toolbar-delete');
		$this->driver->waitForElementUntilIsPresent(By::xPath($this->waitForXpath));
	}

	/**
	 * function to edit style
	 *
	 * @param   string    $name     stores name of the style
	 * @param   array     $fields   stores the value of the input fields
	 *
	 * @return void
	 *
	 */
	public function editStyle($name, $fields)
	{
		$this->searchFor($name);
		$this->clickItem($name);
		$templateEditPage = $this->test->getPageObject('TemplateEditPage');
		$templateEditPage->setFieldValues($fields);
		$templateEditPage->clickButton('toolbar-save');
		$this->test->getPageObject('TemplateManagerPage');
	}
}
