<?php

use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;
use SeleniumClient\WebElement;

/**
 * Class for the back-end control panel screen.
 *
 */
class UserManagerPage extends AdminManagerPage
{
	protected $waitForXpath =  "//ul/li/a[@href='index.php?option=com_users&view=users']";
	protected $url = 'administrator/index.php?option=com_users&view=users';

	public $filters = array(
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

	public function getGroups($userName)
	{
		$this->clickItem($userName);
		$userEditPage = $this->test->getPageObject('UserEditPage');
		$result = $userEditPage->getGroups();
		$userEditPage->clickButton('toolbar-save');
		$this->test->getPageObject('UserManagerPage');
		return $result;
	}

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