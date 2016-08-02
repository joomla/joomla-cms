<?php
/**
 * @package     Joomla.Test
 * @subpackage  Webdriver
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once 'JoomlaWebdriverTestCase.php';

use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;

/**
 * This class tests the  Control panel.
 *
 * @package     Joomla.Tests
 * @subpackage  Test
 *
 * @copyright   Copyright (c) 2005 - 2016 Open Source Matters, Inc.   All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       Joomla 3.3
 */

class UserManager0001Test extends JoomlaWebdriverTestCase
{
	/**
	 *
	 * @var UserManagerPage
	 */
	protected $userManagerPage = null; /* Global configuration page*/

	/**
	 * Login to back end and navigate to menu Language Manager.
	 *
	 * @return void
	 */
	public function setUp()
	{
		parent::setUp();
		$cpPage = $this->doAdminLogin();
		$this->userManagerPage = $cpPage->clickMenu('User Manager', 'UserManagerPage');
	}

	/**
	 * Logout and close test.
	 *
	 * @return void
	 *
	 * @since   3.0
	 */
	public function tearDown()
	{
		$this->doAdminLogout();
		parent::tearDown();
	}

	/**
	 * open edit screen
	 *
	 * @return void
	 *
	 * @test
	 */
	public function constructor_OpenEditScreen_UserEditOpened()
	{
		$this->userManagerPage->clickButton('new');
		$userEditPage = $this->getPageObject('UserEditPage');
		$userEditPage->clickButton('cancel');
		$this->userManagerPage = $this->getPageObject('UserManagerPage');
	}

	/**
	 * check the available tab IDs
	 *
	 * @return void
	 *
	 * @test
	 */
	public function getTabIds_ScreenDisplayed_EqualExpected()
	{
		$this->userManagerPage->clickButton('toolbar-new');
		$userEditPage = $this->getPageObject('UserEditPage');
		$textArray = $userEditPage->getTabIds();
		$this->assertEquals($userEditPage->tabs, $textArray, 'Tab labels should match expected values.');
		$userEditPage->clickButton('toolbar-cancel');
		$this->userManagerPage = $this->getPageObject('UserManagerPage');
	}

	/**
	 * Gets the actual input fields and checks them against the $inputFields property.
	 *
	 * @return void
	 *
	 * @test
	 */
	public function getAllInputFields_ScreenDisplayed_EqualExpected()
	{
		$this->userManagerPage->clickButton('toolbar-new');
		$userEditPage = $this->getPageObject('UserEditPage');

		$testElements = $userEditPage->getAllInputFields(array('details', 'settings'));
		$actualFields = $this->getActualFieldsFromElements($testElements);
		$this->assertEquals($userEditPage->inputFields, $actualFields);
		$userEditPage->clickButton('toolbar-cancel');
		$this->userManagerPage = $this->getPageObject('UserManagerPage');
	}

	/**
	 * add user with default values
	 *
	 * @return void
	 *
	 * @test
	 */
	public function addUser_WithFieldDefaults_UserAdded()
	{
		$this->assertFalse($this->userManagerPage->getRowNumber('Test User'), 'Test user should not be present');
		$this->userManagerPage->addUser();
		$message = $this->userManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'User successfully saved') >= 0, 'User save should return success');
		$this->assertEquals(2, $this->userManagerPage->getRowNumber('Test User'), 'Test user should be in row 2');
		$this->userManagerPage->delete('Test User');
		$this->assertFalse($this->userManagerPage->getRowNumber('Test User'), 'Test user should not be present');
	}

	/**
	 * add user with given values
	 *
	 * @return void
	 *
	 * @test
	 */
	public function addUser_WithGivenFields_UserAdded()
	{
		$salt = rand();
		$userName = 'Test User' . $salt;
		$login = 'user' . $salt;
		$password = 'password' . $salt;
		$email = 'myemail' . $salt . '@test.com';
		$groups = array('Public', 'Manager');
		$this->assertFalse($this->userManagerPage->getRowNumber($userName), 'Test user should not be present');
		$this->userManagerPage->addUser($userName, $login, $password, $email, $groups);
		$message = $this->userManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'User successfully saved') >= 0, 'User save should return success');
		$this->assertEquals(2, $this->userManagerPage->getRowNumber($userName), 'Test user should be in row 2');
		$actualGroups = $this->userManagerPage->getGroups($userName);
		$this->assertEquals($groups, $actualGroups, 'Specified groups should be set');
		$values = $this->userManagerPage->getFieldValues('UserEditPage', $userName, array('Login Name', 'Email'));
		$this->assertEquals(array($login, $email), $values, 'Actual login, email should match expected');
		$this->userManagerPage->searchFor();
		$this->userManagerPage->delete('Test User');
		$this->assertFalse($this->userManagerPage->getRowNumber($userName), 'Test user should not be present');
	}

	/**
	 * edit the values of the input fields
	 *
	 * @return void
	 *
	 * @test
	 */
	public function editUser_ChangeFields_FieldsChanged()
	{
		$salt = rand();
		$userName = 'User' . $salt;
		$login = 'user' . $salt;
		$password = 'password' . $salt;
		$email = 'myemail' . $salt . '@test.com';
		$groups = array('Manager', 'Registered');
		$this->assertFalse($this->userManagerPage->getRowNumber($userName), 'Test user should not be present');
		$this->userManagerPage->addUser($userName, $login, $password, $email, $groups, array('Time Zone' => 'Vancouver'));
		$newGroups = array('Administrator', 'Author', 'Guest');
		$this->userManagerPage->editUser($userName, array('Email' => 'newemail@test.com', 'Time Zone' => 'Toronto'), $newGroups);
		$rowText = $this->userManagerPage->getRowText($userName);
		$this->assertTrue(strpos($rowText, 'newemail@test.com') > 0, 'Row should contain new email');
		$actualGroups = $this->userManagerPage->getGroups($userName);
		sort($newGroups);
		sort($actualGroups);
		$this->assertEquals($newGroups, $actualGroups, 'New groups should be assigned');
		$values = $this->userManagerPage->getFieldValues('UserEditPage', $userName, array('Email', 'Time Zone'));
		$this->assertEquals(array('newemail@test.com', 'Toronto' ), $values, 'Actual values should match expected');
		$this->userManagerPage->searchFor();
		$this->userManagerPage->delete($userName);
		$this->assertFalse($this->userManagerPage->getRowNumber($userName) > 0, 'Test User should not be present');
	}

	/**
	 * change the state of the user
	 *
	 * @return void
	 *
	 * @test
	 */
	public function changeUserState_ChangeEnabledUsingToolbar_EnabledChanged()
	{
		$salt = rand();
		$userName = 'Test User ' . $salt;
		$this->userManagerPage->addUser($userName);
		$state = $this->userManagerPage->getState($userName);
		$this->assertEquals('published', $state, 'Initial state should be published');
		$this->userManagerPage->changeUserState($userName, 'unpublished');
		$state = $this->userManagerPage->getState($userName);
		$this->assertEquals('unpublished', $state, 'State should be unpublished');
		$this->userManagerPage->searchFor();
		$this->userManagerPage->delete($userName);
		$this->assertFalse($this->userManagerPage->getRowNumber($userName) > 0, 'Test User should not be present');
	}
}
