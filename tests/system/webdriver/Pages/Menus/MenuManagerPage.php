<?php
/**
 * @package     Joomla.Tests
 * @subpackage  Page
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;
use SeleniumClient\WebElement;

/**
 * Class for the back-end control panel screen.
 *
 * @since  joomla 3.0
 */
class MenuManagerPage extends AdminManagerPage
{
	protected $waitForXpath = "//ul/li/a[@href='index.php?option=com_menus&view=menus']";

	protected $url = 'administrator/index.php?option=com_menus&view=menus';

	public $filters = array();

	public $toolbar = array (
			'New' => 'toolbar-new',
			'Edit' => 'toolbar-edit',
			'Delete' => 'toolbar-delete',
			'Rebuild' => 'toolbar-refresh',
			'Options' => 'toolbar-options',
			'Help' => 'toolbar-help',
	);

	public $submenu = array (
			'option=com_menus&view=items',
	);


	/**
	 * function to add a menu
	 *
	 * @param   string  $title        stores value of title
	 * @param   string  $type         stores value of menu type
	 * @param   string  $description  stores value of description
	 *
	 * @return AdminPage
	 */
	public function addMenu($title='Test Menu', $type='testMenu', $description='This is a test menu.')
	{
		$this->clickButton('toolbar-new');
		$menuEditPage = $this->test->getPageObject('MenuEditPage');
		$menuEditPage->setFieldValues(array('Title' => $title, 'Menu type' => $type, 'Description' => $description));
		$menuEditPage->clickButton('toolbar-save');

		return $this->test->getPageObject('MenuManagerPage');
	}

	/**
	 * function to delete menu
	 *
	 * @param   string  $title  stores value of title
	 *
	 * @return void
	 */
	public function deleteMenu($title)
	{
		if ($this->getRowText($title))
		{
			$this->checkBox($title);
			$this->clickButton('toolbar-delete');
			$this->driver->acceptAlert();
			$this->driver->waitForElementUntilIsPresent(By::xPath($this->waitForXpath));
		}
	}

	/**
	 * function to edit page
	 *
	 * @param   string $title   stores value of title
	 * @param   array  $fields  stores value of input fields
	 *
	 * @return AdminPage
	 */
	public function editMenu($title, $fields)
	{
		$this->checkBox($title);
		$this->clickButton('Edit');

		/* @var $menuEditPage MenuEditPage*/

		$menuEditPage = $this->test->getPageObject('MenuEditPage');
		$menuEditPage->setFieldValues($fields);
		$menuEditPage->clickButton('toolbar-save');

		return $this->test->getPageObject('MenuManagerPage');
	}

	/**
	 * function to check box
	 *
	 * @param   string  $title  stores value of title
	 *
	 * @return void
	 */
	public function checkBox($title)
	{
		$this->driver->findElement(By::xPath("//td[contains(., '" . $title . "')]/../td/input"))->click();
	}

	/**
	 * Returns an array of field values from an edit screen.
	 *
	 * @param string  $itemName    Name of item (user name, article title, and so on)
	 * @param array   $fieldNames  Array of field labels to get values of.
	 *
	 * @return string
	 */
	public function getFieldValues($className, $itemName, $fieldNames)
	{
		$this->checkBox($itemName);
		$this->clickButton('Edit');
		$this->editItem = $this->test->getPageObject($className);
		$result = array();

		if (is_array($fieldNames))
		{
			foreach ($fieldNames as $name)
			{
				$result[] = $this->editItem->getFieldValue($name);
			}
		}

		$this->editItem->saveAndClose();

		return $result;
	}
}
