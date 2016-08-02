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
 * @since  Joomla 3.0
 */
class UserManagerPage extends AdminManagerPage
{
	protected $waitForXpath = "//ul/li/a[@href='index.php?option=com_users&view=users']";

	protected $url = 'administrator/index.php?option=com_users&view=users';

	public $filters = array(
			'Sort Table By:' => 'list_fullordering',
			'20' => 'list_limit',
			'State' => 'filter_state',
			'Active' => 'filter_active',
			'Group' => 'filter_group_id',
			'Registration Date' => 'filter_range',
			);

	public $toolbar = array (
			'New' => 'toolbar-new',
			'Edit' => 'toolbar-edit',
			'Activate' => 'toolbar-publish',
			'Block' => 'toolbar-unpublish',
			'Unblock' => 'toolbar-unblock',
			'Delete' => 'toolbar-delete',
			'Options' => 'toolbar-options',
			'Help' => 'toolbar-help',
			);

	public $submenu = array (
			'option=com_users&view=users',
			'option=com_users&view=groups',
			'option=com_users&view=levels',
			'option=com_users&view=notes',
			'option=com_categories&extension=com_users'
			);

	/**
	 * function to add user
	 *
	 * @param   string  $name         title of the user
	 * @param   string  $login        stores login ID
	 * @param   string  $password     Stores Password
	 * @param   string  $email        Stores Email ID
	 * @param   array   $groupNames   Store name of the group
	 * @param   null    $otherFields  stores value of other fields
	 *
	 * @return void
	 */
	public function addUser($name='Test User', $login='test', $password='password', $email='abc@test.com', $groupNames = array(), $otherFields = null)
	{
		$this->clickButton('toolbar-new');
		$userEditPage = $this->test->getPageObject('UserEditPage');
		$userEditPage->setFieldValues(array('Name' => $name, 'Login Name' => $login, 'Password' => $password, 'Confirm Password' => $password, 'Email' => $email ));

		if (is_array($otherFields))
		{
			$userEditPage->setFieldValues($otherFields);
		}

		$userEditPage->setGroups($groupNames);
		$userEditPage->clickButton('toolbar-save');
		$this->test->getPageObject('UserManagerPage');
	}

	/**
	 * function to change the state of the user
	 *
	 * @param   string  $name   Title of the user
	 * @param   string  $state  State of the user
	 *
	 * @return void
	 */
	public function changeUserState($name, $state = 'published')
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
	 * function to edit user
	 *
	 * @param   string  $name        Title of the user
	 * @param   array   $fields      input fields of the user
	 * @param   array   $groupNames  array of names of groups
	 *
	 * @return void
	 */
	public function editUser($name, $fields, $groupNames = array())
	{
		$this->clickItem($name);
		$userEditPage = $this->test->getPageObject('UserEditPage');
		$userEditPage->setFieldValues($fields);
		$userEditPage->setGroups($groupNames);
		$userEditPage->clickButton('toolbar-save');
		$this->test->getPageObject('UserManagerPage');
		$this->searchFor();
	}

	/**
	 * function to get groups
	 *
	 * @param   string  $userName  Title of the user
	 *
	 * @return mixed
	 */
	public function getGroups($userName)
	{
		$this->clickItem($userName);
		$userEditPage = $this->test->getPageObject('UserEditPage');
		$result = $userEditPage->getGroups();
		$userEditPage->clickButton('toolbar-save');
		$this->test->getPageObject('UserManagerPage');

		return $result;
	}

	/**
	 * function to get the state of the user
	 *
	 * @param   string  $name  Title of the user
	 *
	 * @return bool|string
	 */
	public function getState($name)
	{
		$result = false;
		$row = $this->getRowNumber($name);
		$text = $this->driver->findElement(By::xPath("//tbody/tr[" . $row . "]/td[4]/a"))->getAttribute(@onclick);

		if (strpos($text, 'users.unblock') > 0)
		{
			$result = 'unpublished';
		}

		if (strpos($text, 'users.block') > 0)
		{
			$result = 'published';
		}

		return $result;
	}
}
