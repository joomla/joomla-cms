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
 * Page class for the back-end component contact menu.
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.2
 */
class ContactManagerPage extends AdminManagerPage
{
	/**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $waitForXpath =  "//ul/li/a[@href='index.php?option=com_contact']";

	/**
	 * URL used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $url = 'administrator/index.php?option=com_contact';

	/**
	 * Array of filter id values for this page
	 *
	 * @var    array
	 * @since  3.2
	 */
	public $filters = array(
			'Select Status' => 'filter_published',
			'Select Category' => 'filter_category_id',
			'Select Access' => 'filter_access',
			'Select Language' => 'filter_language',
			'Select Tags' => 'filter_tag',
			);

	/**
	 * Array of toolbar id values for this page
	 *
	 * @var    array
	 * @since  3.2
	 */
	public $toolbar = array (
			'New' => 'toolbar-new',
			'Edit' => 'toolbar-edit',
			'Publish' => 'toolbar-publish',
			'Unpublish' => 'toolbar-unpublish',
			'Archive' => 'toolbar-archive',
			'Check In' => 'toolbar-check-in',
			'Trash' => 'toolbar-trash',
			'Empty Trash' => 'toolbar-delete',
			'Batch' => 'toolbar-batch',
			'Options' => 'toolbar-options',
			'Help' => 'toolbar-help',
			);

	/**
	 * Add a new Contact item in the Contact Manager: Component screen.
	 *
	 * @param string   $name          Test Contact Name
	 * @param array    $fields     associative array of fields in the form label => value.
	 *
	 * @return  ContactManagerPage
	 */
	public function addContact($name='Test Contact', $fields)
	{
		$new_name = $name;
		$login = "testing";
		$this->clickButton('toolbar-new');
		$contactEditPage = $this->test->getPageObject('ContactEditPage');
		$contactEditPage->setFieldValues(array('Name' => $name));
		if ($fields)
		{
			$contactEditPage->setFieldValues($fields);
		}
		$contactEditPage->clickButton('toolbar-save');
		$this->test->getPageObject('ContactManagerPage');
	}

	/**
	 * Edit a Contact item in the Contact Manager: Contact Items screen.
	 *
	 * @param string   $name	   Contact Title field
	 * @param array    $fields     associative array of fields in the form label => value.
	 *
	 * @return  void
	 */
	public function editContact($name, $fields)
	{
		$this->clickItem($name);
		$contactEditPage = $this->test->getPageObject('ContactEditPage');
		$contactEditPage->setFieldValues($fields);
		$contactEditPage->clickButton('toolbar-save');
		$this->test->getPageObject('ContactManagerPage');
		$this->searchFor();
	}

	/**
	 * Get state  of a Contact item in the Contact Manager: Contact Items screen.
	 *
	 * @param   string   $name	   Contact Title field
	 *
	 * @return  State of the Contact Published or Unpublished
	 */
	public function getState($name)
	{
		$result = false;
		$row = $this->getRowNumber($name);
		$text = $this->driver->findElement(By::xPath("//tbody/tr[" . $row . "]/td[3]/a"))->getAttribute(@onclick);
		if (strpos($text, 'contacts.unpublish') > 0)
		{
			$result = 'published';
		}
		if (strpos($text, 'contacts.publish') > 0)
		{
			$result = 'unpublished';
		}
		return $result;
	}

	/**
	 * Change state of a Contact item in the Contact Manager: Contact Items screen.
	 *
	 * @param string   $name	   Contact Title field
	 * @param string   $state      State of the Contact
	 *
	 * @return  void
	 */
	public function changeContactState($name, $state = 'published')
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
}
