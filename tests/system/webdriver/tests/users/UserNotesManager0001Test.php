<?php

require_once 'JoomlaWebdriverTestCase.php';

/**
 * This class tests the  Manager: Add / Edit  Screen
 * @author Mark
 *
 */
class UserNotesManager0001Test extends JoomlaWebdriverTestCase
{
	/**
	 *
	 * @var UserNotesManagerPage
	 */
	protected $userNotesManagerPage = null; // Global configuration page

	public function setUp()
	{
		parent::setUp();
		$cpPage = $this->doAdminLogin();
		$this->userNotesManagerPage = $cpPage->clickMenu('User Notes', 'UserNotesManagerPage');
	}

	public function tearDown()
	{
		$this->doAdminLogout();
		parent::tearDown();
	}

	/**
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
	 * @test
	 */
	public function getAllInputFields_ScreenDisplayed_EqualExpected()
	{
		$this->userNotesManagerPage->clickButton('toolbar-new');
		$userNotesEditPage = $this->getPageObject('UserNotesEditPage');
		$testElements = $userNotesEditPage->getAllInputFields();
		$actualFields = array();
		foreach ($testElements as $el)
		{
			$el->labelText = (substr($el->labelText, -2) == ' *') ? substr($el->labelText, 0, -2) : $el->labelText;
			$actualFields[] = array('label' => $el->labelText, 'id' => $el->id, 'type' => $el->tag, 'tab' => $el->tab);
		}
		$this->assertEquals($userNotesEditPage->inputFields, $actualFields);
		$userNotesEditPage->clickButton('toolbar-cancel');
		$this->userNotesManagerPage = $this->getPageObject('UserNotesManagerPage');
	}

	/**
	 * TODO: Add User Notes Category to methods
	 * @test
	 */
	public function add_WithFieldDefaults_Added()
	{
		$this->assertFalse($this->userNotesManagerPage->getRowNumber('Test User Group'), 'Test Use Notes should not be present');
		$this->userNotesManagerPage->addUserNotes();
		$message = $this->userNotesManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'UserNotes successfully saved') >= 0, 'User Notes save should return success');
		$this->assertTrue($this->userNotesManagerPage->getRowNumber('Test User Notes') > 0, 'Test User Notes should be in list');
		$this->userNotesManagerPage->deleteItem('Test User Notes');
		$this->assertFalse($this->userNotesManagerPage->getRowNumber('Test User Notes'), 'Test Use Notes should not be present');
	}

	/**
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

		$this->userNotesManagerPage->addUserNotes($userNotesName, 'Super User', array('Category' => $category, 'Status' => $status, 'Review time' => $reviewTime, 'Note' => $note));
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
		$this->userNotesManagerPage->deleteItem($userNotesName);
		$this->assertFalse($this->userNotesManagerPage->getRowNumber($userNotesName), 'Test userNotes should not be present');
	}

	/**
	 * TODO: Finish this test
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
		$this->userNotesManagerPage->addUserNotes($userNotesName, 'Super User', array('Category' => $category, 'Status' => $status, 'Review time' => $reviewTime, 'Note' => $note));

		/* @var $userManagerPage UserManagerPage */
		$userManagerPage = $this->userNotesManagerPage->clickMenu('User Manager', 'UserManagerPage');
		$userName = 'Test User ' . $salt;
		$userManagerPage->addUser($userName);
		$this->userNotesManagerPage = $userManagerPage->clickMenu('User Notes', 'UserNotesManagerPage');

		$newNotesName = 'NewUserNotes' . $salt;
		$newUserName = $userName;
		$newStatus = 'Unpublished';
		$newReviewTime = '2012-12-30';
		$newNote = 'This is a modified note';
		$this->userNotesManagerPage->editUserNotes($userNotesName, array('Subject' => $newNotesName, 'ID' => $newUserName, 'Status' => $newStatus, 'Review time' => $newReviewTime, 'Note' => $newNote));

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
		$actualReviewTime = $userEditPage->getFieldValue('Review time');
		$this->assertEquals($newStatus, $actualStatus, 'Status should be set to new value');
		$this->assertEquals($newUserName, $actualId, 'User name should be set to new value');
		$this->assertContains($newNote, $actualNote, 'Note should be set to new value');
		$this->assertContains($newReviewTime, $actualReviewTime, 'Review time should be set to new value');

		$userEditPage->clickButton('Close');
		$this->userNotesManagerPage = $this->getPageObject('UserNotesManagerPage');
		$this->userNotesManagerPage->deleteItem($userNotesName);
		$this->assertFalse($this->userNotesManagerPage->getRowNumber($userNotesName), 'Test userNotes should not be present');

		$userManagerPage = $this->userNotesManagerPage->clickMenu('User Manager', 'UserManagerPage');
		$userManagerPage->deleteUser($newUserName);
	}
}
