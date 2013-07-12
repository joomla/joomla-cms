<?php

require_once 'JoomlaWebdriverTestCase.php';

use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;

/**
 * This class tests the User Manager: Add / Edit User Screen
 * @author Mark
 *
 */
class UserManager0001Test extends JoomlaWebdriverTestCase
{
	/**
	 *
	 * @var UserManagerPage
	 */
	protected $userManagerPage = null; // Global configuration page

	public function setUp()
	{
		parent::setUp();
		$cpPage = $this->doAdminLogin();
		$this->userManagerPage = $cpPage->clickMenu('User Manager', 'UserManagerPage');
	}

	public function tearDown()
	{
		$this->doAdminLogout();
		parent::tearDown();
	}

	/**
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
	 * @test
	 */
	public function addUser_WithGivenFields_UserAdded()
	{
		$salt = rand();
		$userName = 'User' . $salt;
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
		$this->userManagerPage->delete($userName);
		$this->assertFalse($this->userManagerPage->getRowNumber($userName), 'Test user should not be present');
	}

	/**
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
		$this->userManagerPage->delete($userName);
	}

	/**
	 * @test
	 */
	public function changeUserState_ChangeEnabledUsingToolbar_EnabledChanged()
	{
		$this->userManagerPage->addUser('Test User');
		$state = $this->userManagerPage->getState('Test User');
		$this->assertEquals('published', $state, 'Initial state should be published');
		$this->userManagerPage->changeUserState('Test User', 'unpublished');
		$state = $this->userManagerPage->getState('Test User');
		$this->assertEquals('unpublished', $state, 'State should be unpublished');
		$this->userManagerPage->delete('Test User');
	}

}