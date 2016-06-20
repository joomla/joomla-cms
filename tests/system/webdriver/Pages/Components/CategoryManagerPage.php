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
 * Page class for the back-end component tags menu.
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.0
 */
class CategoryManagerPage extends AdminManagerPage
{
  /**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $waitForXpath =  "//ul/li/a[@href='index.php?option=com_categories&extension=com_content']";

	/**
	 * URL used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $url = 'option=com_categories&';

	/**
	 * Array of filter id values for this page
	 *
	 * @var    array
	 * @since  3.0
	 */
	public $filters = array(
			'Sort Table By:' => 'list_fullordering',
			'20' => 'list_limit',
			'Select Max Levels' => 'filter_level',
			'Select Status' => 'filter_published',
			'Select Access' => 'filter_access',
			'Select Language' => 'filter_language',
			'Select Tag' => 'filter_tag'
			);

	/**
	 * Array of toolbar id values for this page
	 *
	 * @var    array
	 * @since  3.0
	 */
	public $toolbar = array (
			'New' => 'toolbar-new',
			'Edit' => 'toolbar-edit',
			'Publish' => 'toolbar-publish',
			'Unpublish' => 'toolbar-unpublish',
			'Featured' => 'toolbar-featured',
			'Archive' => 'toolbar-archive',
			'Check In' => 'toolbar-checkin',
			'Trash' => 'toolbar-trash',
			'Empty Trash' => 'toolbar-delete',
			'Batch' => 'toolbar-batch',
			'Options' => 'toolbar-options',
			'Help' => 'toolbar-help',
			);

	/**
	 * Add a new Category item in the Category Manager: Category Manager Screen.
	 *
	 * @param string   $name          Test Category Title
	 *
	 * @param string   $desc		  Test Description of Category
	 *
	 * @param array    $fields        Optional associative array of fields to set
	 *
	 * @return  CategoryManagerPage
	 */
	public function addCategory($name='ABC Testing', $desc='System Test Category', $fields = array())
	{
		$new_name = $name;
		$this->clickButton('toolbar-new');
		$categoryEditPage = $this->test->getPageObject('CategoryEditPage');
		$categoryEditPage->setFieldValues(array('Title' => $name, 'Description'=>$desc));
		$categoryEditPage->setFieldValues($fields);
		$categoryEditPage->clickButton('toolbar-save');
		$this->test->getPageObject('CategoryManagerPage');
	}

	/**
	 * Edit a Category item in the Category Manager: Category Manager Screen.
	 *
	 * @param string   $name	   Title field
	 * @param array    $fields     associative array of fields in the form label => value.
	 *
	 * @return  void
	 */
	public function editCategory($name, $fields)
	{
		$this->clickItem($name);
		$categoryEditPage = $this->test->getPageObject('CategoryEditPage');
		$categoryEditPage->setFieldValues($fields);
		$categoryEditPage->clickButton('toolbar-save');
		$this->test->getPageObject('CategoryManagerPage');
		$this->searchFor();
	}

	/**
	 * Get state  of a Category in Category Manager: Category Manager Screen.
	 *
	 * @param string   $name	   Category Title field
	 *
	 * @return  State of the Category //Published or Unpublished
	 */
	public function getState($name)
	{
		$result = false;
		$this->searchFor($name);
		$row = $this->getRowNumber($name);
		$text = $this->driver->findElement(By::xPath("//tbody/tr[" . $row . "]/td[3]/a"))->getAttribute(@onclick);
		if (strpos($text, 'categories.unpublish') > 0)
		{
			$result = 'published';
		}
		if (strpos($text, 'categories.publish') > 0)
		{
			$result = 'unpublished';
		}
		return $result;
	}

	/**
	 * Change state of a Category in Category Manager: Category Manager Screen.
	 *
	 * @param string   $name	   Category Title field
	 * @param string   $state      State of the Category
	 *
	 * @return  void
	 */
	public function changeCategoryState($name, $state = 'published')
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
		elseif(strtolower($state) == 'archived')
		{
			$this->clickButton('toolbar-archive');
			$this->driver->waitForElementUntilIsPresent(By::xPath($this->waitForXpath));
		}
		$this->searchFor();
	}

}
