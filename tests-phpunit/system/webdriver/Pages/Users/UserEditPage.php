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
class UserEditPage extends AdminEditPage
{
	protected $waitForXpath = "//form[@id='user-form']";

	protected $url = 'administrator/index.php?option=com_users&view=user&layout=edit';

	/**
	 * Array of tabs
	 *
	 * @var array expected id values for tab div elements
	 */
	public $tabs = array('details', 'groups', 'settings');

	public $tabLabels = array('Account Details', 'Assigned User Groups', 'Basic Settings');

	/**
	 * Associative array of expected input fields for the Account Details and Basic Settings tabs
	 * Assigned User Groups tab is omitted because that depends on the groups set up in the sample data
	 * @var unknown_type
	 */
	public $inputFields = array (
			array('label' => 'Name', 'id' => 'jform_name', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Login Name', 'id' => 'jform_username', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Password', 'id' => 'jform_password', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Confirm Password', 'id' => 'jform_password2', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Email', 'id' => 'jform_email', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Registration Date', 'id' => 'jform_registerDate', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Last Visit Date', 'id' => 'jform_lastvisitDate', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Last Reset Date', 'id' => 'jform_lastResetTime', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Password Reset Count', 'id' => 'jform_resetCount', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Receive System Emails', 'id' => 'jform_sendEmail', 'type' => 'fieldset', 'tab' => 'details'),
			array('label' => 'Block this User', 'id' => 'jform_block', 'type' => 'fieldset', 'tab' => 'details'),
			array('label' => 'Require Password Reset', 'id' => 'jform_requireReset', 'type' => 'fieldset', 'tab' => 'details'),
			array('label' => 'ID', 'id' => 'jform_id', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Backend Template Style', 'id' => 'jform_params_admin_style', 'type' => 'select', 'tab' => 'settings'),
			array('label' => 'Backend Language', 'id' => 'jform_params_admin_language', 'type' => 'select', 'tab' => 'settings'),
			array('label' => 'Frontend Language', 'id' => 'jform_params_language', 'type' => 'select', 'tab' => 'settings'),
			array('label' => 'Editor', 'id' => 'jform_params_editor', 'type' => 'select', 'tab' => 'settings'),
			array('label' => 'Help Site', 'id' => 'jform_params_helpsite', 'type' => 'select', 'tab' => 'settings'),
			array('label' => 'Time Zone', 'id' => 'jform_params_timezone', 'type' => 'select', 'tab' => 'settings'),
	);

	/**
	 * function to get the value of the groups
	 *
	 * @return array
	 */
	public function getGroups()
	{
		$result = array();
		$this->selectTab('Groups');
		$elements = $this->driver->findElements(By::xPath("//div[@id='groups']//input[@checked='checked']/../../label"));

		foreach ($elements as $el)
		{
			$result[] = str_replace(array('|','—'), '', $el->getText());
		}

		return $result;
	}

	/**
	 * function to set the value of the groups
	 *
	 * @param   array  $groupNames  title of the group
	 *
	 * @return void
	 */
	public function setGroups(array $groupNames)
	{
		if (count($groupNames) == 0)
		{
			return;
		}

		$this->selectTab('Groups');

		// Uncheck any checked boxes

		$elements = $this->driver->findElements(By::xPath("//div[@id='groups']//input[@checked='checked']"));

		foreach ($elements as $el)
		{
			$el->click();
		}

		foreach ($groupNames as $name)
		{
			$this->driver->findElement(By::xPath("//div[@id='groups']//label[contains(., '$name')]"))->click();
		}
	}
}
