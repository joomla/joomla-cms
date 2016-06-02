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
 * This class tests the  Manager: Add / Edit  Screen
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.0
 */
class UserNotesManager0001Test extends JoomlaWebdriverTestCase
{
	/**
	 *
	 * @var UserNotesManagerPage
	 */
	protected $userNotesManagerPage = null; /* Global configuration page*/

	/**
	 * Login to back end and navigate to menu Language Manager.
	 *
	 * @return void
	 *
	 * @since   3.0
	 */
	public function setUp()
	{
		parent::setUp();
		$cpPage = $this->doAdminLogin();
		$this->userNotesManagerPage = $cpPage->clickMenu('User Notes', 'UserNotesManagerPage');
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
	 * check usernotes edit page
	 *
	 * @return void
	 *
	 * @test
	 */
	public function constructor_OpenEditScreen_UserNotesEditOpened()
	{
		$this->userNotesManagerPage->clickButton('toolbar-new');
		$userNotesEditPage = $this->getPageObject('UserNotesEditPage');
		$userNotesEditPage->clickButton('toolbar-cancel');
		$this->userNotesManagerPage = $this->getPageObject('UserNotesManagerPage');
	}

	/**
	 * check all input fields
	 *
	 * @return void
	 *
	 * @test
	 */
	public function getAllInputFields_ScreenDisplayed_EqualExpected()
	{
		$this->userNotesManagerPage->clickButton('toolbar-new');
		$userNotesEditPage = $this->getPageObject('UserNotesEditPage');
		$testElements = $userNotesEditPage->getAllInputFields();
		$actualFields = $this->getActualFieldsFromElements($testElements);

		/* Option to print actual element array
		 @var $userNotesEditPage UserNotesEditPage */
		/* 	$userNotesEditPage->printFieldArray($userNotesEditPage->getAllInputFields($$userNotesEditPage->tabs));*/


		$this->assertEquals($userNotesEditPage->inputFields, $actualFields);
		$userNotesEditPage->clickButton('toolbar-cancel');
		$this->userNotesManagerPage = $this->getPageObject('UserNotesManagerPage');
	}

	/**
	 * Add User Notes Category to methods
	 *
	 * @return void
	 *
	 * @test
	 */
	public function add_WithFieldDefaults_Added()
	{
		$this->assertFalse($this->userNotesManagerPage->getRowNumber('Test User Group'), 'Test Use Notes should not be present');
		$this->userNotesManagerPage->addUserNotes();
		$message = $this->userNotesManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'UserNotes successfully saved') >= 0, 'User Notes save should return success');
		$this->assertTrue($this->userNotesManagerPage->getRowNumber('Test User Notes') > 0, 'Test User Notes should be in list');
		$this->userNotesManagerPage->trashAndDelete('Test User Notes');
		$this->assertFalse($this->userNotesManagerPage->getRowNumber('Test User Notes'), 'Test Use Notes should not be present');
	}

	/**
	 * add usernotes with given values
	 *
	 * @return void
	 *
	 * @test
	 */
	public function addUserNotes_WithGivenFields_UserNotesAdded()
	{
		$salt = rand();
		$userNotesName = 'UserNotes' . $salt;
		$category = 'Uncategorised';
		$status = 'Unpublished';
		$reviewTime = '2012-12-31';
		$note = 'This is a test note.';
		$this->assertFalse($this->userNotesManagerPage->getRowNumber($userNotesName), 'Test User Notes should not be present');

		$this->userNotesManagerPage->addUserNotes($userNotesName, 'Super User', array('Category' => $category, 'Status' => $status, 'Review Date' => $reviewTime, 'Note' => $note));
		$message = $this->userNotesManagerPage->getAlertMessage();
		$this->assertTrue(strlen($message) > 0);
		$this->assertTrue(strpos($message, 'UserNotes successfully saved') >= 0, 'User Notes save should return success');
		$this->assertTrue($this->userNotesManagerPage->getRowNumber($userNotesName) > 0, 'Test User Notes should be on the page');

		/* @var $userEditPage UserEditPage */
		$this->userNotesManagerPage->clickItem($userNotesName);
		$userEditPage = $this->getPageObject('UserNotesEditPage');
		$actualStatus = $userEditPage->getFieldValue('Status');
		$actualId = $userEditPage->getFieldValue('ID');
		$actualNote = $userEditPage->getFieldValue('Note');
		$this->assertEquals($status, $actualStatus, 'Status should be set to given value');
		$this->assertEquals('Super User', $actualId, 'User name should be set to given value');
		$this->assertContains($note, $actualNote, 'Note should be set to given value');

		$userEditPage->clickButton('Close');
		$this->userNotesManagerPage = $this->getPageObject('UserNotesManagerPage');
		$this->userNotesManagerPage->trashAndDelete($userNotesName);
		$this->assertFalse($this->userNotesManagerPage->getRowNumber($userNotesName), 'Test userNotes should not be present');
	}

	/**
	 * edit the values of the input fields
	 *
	 * @return void
	 *
	 * @test
	 */
	public function editUserNotes_ChangeFields_FieldsChanged()
	{
		$salt = rand();
		$userNotesName = 'UserNotes' . $salt;
		$category = 'Uncategorised';
		$status = 'Published';
		$reviewTime = '2012-12-31';
		$note = 'This is a user note with custom fields.';
		$this->assertFalse($this->userNotesManagerPage->getRowNumber($userNotesName), 'Test userNotes should not be present');
		$this->userNotesManagerPage->addUserNotes($userNotesName, 'Super User', array('Category' => $category, 'Status' => $status, 'Review Date' => $reviewTime, 'Note' => $note));

		/* @var $userManagerPage UserManagerPage */
		$userManagerPage = $this->userNotesManagerPage->clickMenu('User Manager', 'UserManagerPage');
		$userName = 'Test User ' . $salt;
		$userNameLogin = 'test' .rand(9, 99);
		$userNamePassword = 'password1';
		$userNameEmail = $userNameLogin . '@test.com';
		$userManagerPage->addUser($userName, $userNameLogin, $userNamePassword, $userNameEmail);
		$this->userNotesManagerPage = $userManagerPage->clickMenu('User Notes', 'UserNotesManagerPage');

		$newNotesName = 'NewUserNotes' . $salt;
		$newUserName = $userName;
		$newStatus = 'Unpublished';
		$newReviewTime = '2012-12-30';
		$newNote = 'This is a modified note';
		$this->userNotesManagerPage->editUserNotes($userNotesName, array('Subject' => $newNotesName, 'ID' => $newUserName, 'Status' => $newStatus, 'Review Date' => $newReviewTime, 'Note' => $newNote));

		$message = $this->userNotesManagerPage->getAlertMessage();
		$this->assertTrue(strlen($message) > 0);
		$this->assertTrue(strpos($message, 'UserNotes successfully saved') >= 0, 'User Notes save should return success');
		$this->assertTrue($this->userNotesManagerPage->getRowNumber($newNotesName) > 0, 'Test User Notes should be on the page');

		/* @var $userEditPage UserEditPage */
		$this->userNotesManagerPage->clickItem($newNotesName);
		$userEditPage = $this->getPageObject('UserNotesEditPage');
		$actualStatus = $userEditPage->getFieldValue('Status');
		$actualId = $userEditPage->getFieldValue('ID');
		$actualNote = $userEditPage->getFieldValue('Note');
		$actualReviewTime = $userEditPage->getFieldValue('Review Date');
		$this->assertEquals($newStatus, $actualStatus, 'Status should be set to new value');
		$this->assertEquals($newUserName, $actualId, 'User name should be set to new value');
		$this->assertContains($newNote, $actualNote, 'Note should be set to new value');
		$this->assertContains($newReviewTime, $actualReviewTime, 'Review time should be set to new value');

		$userEditPage->clickButton('Close');
		$this->userNotesManagerPage = $this->getPageObject('UserNotesManagerPage');
		$this->userNotesManagerPage->trashAndDelete($userNotesName);
		$this->assertFalse($this->userNotesManagerPage->getRowNumber($userNotesName), 'Test userNotes should not be present');

		$userManagerPage = $this->userNotesManagerPage->clickMenu('User Manager', 'UserManagerPage');
		$userManagerPage->delete($newUserName);
	}

	/**
	 * check the filters
	 *
	 * @return void
	 *
	 * @test
	 */
	public function setFilter_TestOrdering_ShouldOrderNotes()
	{
		$salt = rand();
		$superUserNotesName = 'UserNotes B';
		$category = 'Uncategorised';
		$status = 'Published';
		$reviewTime = '2014-01-01';
		$note = 'This is a user note with custom fields.';
		$this->userNotesManagerPage->addUserNotes($superUserNotesName, 'Super User', array('Category' => $category, 'Status' => $status, 'Review Date' => $reviewTime, 'Note' => $note));

		/* @var $userManagerPage UserManagerPage */
		$userManagerPage = $this->userNotesManagerPage->clickMenu('User Manager', 'UserManagerPage');
		$userName1 = '1 Test User';
		$userName1Login = 'test1' .rand(9, 99);
		$userName1Password = 'password1';
		$userName1Email = $userName1Login . '@test.com';
		$userManagerPage->addUser($userName1, $userName1Login, $userName1Password, $userName1Email);
		$userName2 = 'Test User 2';
		$userName2Login = 'test2' .rand(9, 99);
		$userName2Password = 'password2';
		$userName2Email = $userName2Login . '@test.com';
		$userManagerPage->addUser($userName2, $userName2Login, $userName2Password, $userName2Email);

		/* @var $userEditPage UserEditPage */
		$this->userNotesManagerPage = $userManagerPage->clickMenu('User Notes', 'UserNotesManagerPage');
		$user1NotesName = 'UserNotes C';
		$user1Status = 'Unpublished';
		$user1ReviewTime = '2012-12-30';
		$user1Note = 'This is another user note with custom fields.';
		$this->userNotesManagerPage->addUserNotes($user1NotesName, $userName1, array('Category' => $category, 'Status' => $user1Status, 'Review Date' => $user1ReviewTime, 'Note' => $user1Note));

		$user2NotesName = 'UserNotes A';
		$user2Status = 'Published';
		$user2ReviewTime = '2012-12-31';
		$user2Note = 'This is another user note with custom fields.';
		$this->userNotesManagerPage->addUserNotes($user2NotesName, $userName2, array('Category' => $category, 'Status' => $user2Status, 'Review Date' => $user2ReviewTime, 'Note' => $user2Note));

		$orderings = array('User', 'Subject', 'Category', 'Status', 'Review Date', 'ID');
		$rows = array('1 Test User', 'Super User', 'Test User 2');
		$actualRowNumbers = $this->userNotesManagerPage->orderAndGetRowNumbers($orderings, $rows);

		$expectedRowNumbers = array(
				'User' => array('ascending' => array(1, 2, 3), 'descending' => array(3, 2, 1)),
				'Subject' => array('ascending' => array(3, 2, 1), 'descending' => array(1, 2, 3)),
				'Category' => array('ascending' => array(2, 1, 3), 'descending' => array(2, 1, 3)),
				'Status' => array('ascending' => array(1, 2, 3), 'descending' => array(3, 1, 2)),
				'Review Date' => array('ascending' => array(1, 3, 2), 'descending' => array(3, 1, 2)),
				'ID' => array('ascending' => array(2, 1, 3), 'descending' => array(2, 3, 1))
		);

		foreach ($actualRowNumbers as $ordering => $orderingRowNumbers)
		{
			foreach ($orderingRowNumbers as $order => $rowNumbers)
			{
				foreach ($rowNumbers as $key => $rowNumber)
				{
					$this->assertEquals(
							$expectedRowNumbers[$ordering][$order][$key],
							$rowNumber,
							'When the table is sorted by ' . strtolower($ordering) . ' in the ' . $order . ' order '
							. $rows[$key] . ' should be in row ' . $expectedRowNumbers[$ordering][$order][$key]
					);
				}
			}
		}

		$this->userNotesManagerPage->trashAndDelete('UserNotes');
		$userManagerPage = $this->userNotesManagerPage->clickMenu('User Manager', 'UserManagerPage');
		$userManagerPage->delete('Test User');
	}
}
