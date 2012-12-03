<?php

require_once 'JoomlaWebdriverTestCase.php';

use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;

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
	 * @test
	 */
	public function add_WithFieldDefaults_Added()
	{
		$this->assertFalse($this->userNotesManagerPage->getRowNumber('Test User Group'), 'Test Use Notes should not be present');
		$this->userNotesManagerPage->addUserNotes();
		$message = $this->userNotesManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'UserNotes successfully saved') >= 0, 'User Notes save should return success');
		$this->assertTrue($this->userNotesManagerPage->getRowNumber('Test User Notes') > 0, 'Test User Notes should be in list');
		$this->userNotesManagerPage->deleteUserNotes('Test User Notes');
		$this->assertFalse($this->userNotesManagerPage->getRowNumber('Test User Notes'), 'Test Use Notes should not be present');
	}

	/**
	 * TODO: Finish this test
	 * @xtest
	 */
	public function addUserNotes_WithGivenFields_UserNotesAdded()
	{
		$salt = rand();
		$userNotesName = 'UserNotes' . $salt;
		$category = 'Uncategorised';
		$note = 'This is a test note.';
		$this->assertFalse($this->userNotesManagerPage->getRowNumber($userNotesName), 'Test User Notes should not be present');
		$this->userNotesManagerPage->addUserNotes($userNotesName, array('Category' => $category, 'Note' => $note));
		$message = $this->userNotesManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'UserNotes successfully saved') >= 0, 'User Notes save should return success');
		$this->assertTrue($this->userNotesManagerPage->getRowNumber($userNotesName) > 0, 'Test User Notes should be on the page');
		$values = $this->userNotesManagerPage->getFieldValues('UserNotesEditPage', $userNotesName, array('Category', 'Note'));
		$this->assertStringEndsWith($parent, $values[0], 'Actual userNotes parent should match expected');
		$this->userNotesManagerPage->deleteUserNotes($userNotesName);
		$this->assertFalse($this->userNotesManagerPage->getRowNumber($userNotesName), 'Test userNotes should not be present');
	}

	/**
	 * TODO: Finish this test
	 * @xtest
	 */
	public function editUserNotes_ChangeFields_FieldsChanged()
	{
		$salt = rand();
		$userNotesName = 'UserNotes' . $salt;
		$parent = 'Author';
		$this->assertFalse($this->userNotesManagerPage->getRowNumber($userNotesName), 'Test userNotes should not be present');
		$this->userNotesManagerPage->addUserNotes($userNotesName, $parent);
		$this->userNotesManagerPage->editUserNotes($userNotesName, array('UserNotes Parent' => 'Publisher'));
		$rowText = $this->userNotesManagerPage->getRowText($userNotesName);
		$values = $this->userNotesManagerPage->getFieldValues('UserNotesEditPage', $userNotesName, array('UserNotes Parent'));
		$this->assertStringEndsWith('Publisher', $values[0], 'Actual userNotes parent should be Publisher');
		$this->userNotesManagerPage->deleteUserNotes($userNotesName);
	}
}