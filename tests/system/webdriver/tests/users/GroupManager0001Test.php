<?php

require_once 'JoomlaWebdriverTestCase.php';

/**
 * This class tests the  Manager: Add / Edit  Screen
 * @author Mark
 *
 */
class GroupManager0001Test extends JoomlaWebdriverTestCase
{
	/**
	 *
	 * @var GroupManagerPage
	 */
	protected $groupManagerPage = null; // Global configuration page

	public function setUp()
	{
		parent::setUp();
		$cpPage = $this->doAdminLogin();
		$this->groupManagerPage = $cpPage->clickMenu('Groups', 'GroupManagerPage');
	}

	public function tearDown()
	{
		$this->doAdminLogout();
		parent::tearDown();
	}

	/**
	 * @test
	 */
	public function constructor_OpenEditScreen_GroupEditOpened()
	{
		$this->groupManagerPage->clickButton('toolbar-new');
		$groupEditPage = $this->getPageObject('GroupEditPage');
		$groupEditPage->clickButton('toolbar-cancel');
		$this->groupManagerPage = $this->getPageObject('groupManagerPage');
	}

	/**
	 * @test
	 */
	public function getAllInputFields_ScreenDisplayed_EqualExpected()
	{
		$this->groupManagerPage->clickButton('toolbar-new');
		$groupEditPage = $this->getPageObject('GroupEditPage');

		$testElements = $groupEditPage->getAllInputFields();
		$actualFields = array();
		foreach ($testElements as $el)
		{
			$el->labelText = (substr($el->labelText, -2) == ' *') ? substr($el->labelText, 0, -2) : $el->labelText;
			$actualFields[] = array('label' => $el->labelText, 'id' => $el->id, 'type' => $el->tag, 'tab' => $el->tab);
		}
		$this->assertEquals($groupEditPage->inputFields, $actualFields);
		$groupEditPage->clickButton('toolbar-cancel');
		$this->groupManagerPage = $this->getPageObject('groupManagerPage');
	}

	/**
	 * @test
	 */
	public function add_WithFieldDefaults_Added()
	{
		$this->assertFalse($this->groupManagerPage->getRowNumber('Test '), 'Test group should not be present');
		$this->groupManagerPage->addGroup();
		$message = $this->groupManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Group successfully saved') >= 0, 'Group save should return success');
		$this->assertEquals(12, $this->groupManagerPage->getRowNumber('Test Group'), 'Test group should be in row 2');
		$this->groupManagerPage->deleteGroup('Test Group');
		$this->assertFalse($this->groupManagerPage->getRowNumber('Test Group'), 'Test group should not be present');
	}

	/**
	 * @test
	 */
	public function addGroup_WithGivenFields_GroupAdded()
	{
		$salt = rand();
		$groupName = 'Group' . $salt;
		$parent = 'Administrator';
		$this->assertFalse($this->groupManagerPage->getRowNumber($groupName), 'Test group should not be present');
		$this->groupManagerPage->addGroup($groupName, $parent);
		$message = $this->groupManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Group successfully saved') >= 0, 'Group save should return success');
		$this->assertTrue($this->groupManagerPage->getRowNumber($groupName) > 0, 'Test group should be on the page');
		$values = $this->groupManagerPage->getFieldValues('GroupEditPage', $groupName, array('Group Parent'));
		$this->assertStringEndsWith($parent, $values[0], 'Actual group parent should match expected');
		$this->groupManagerPage->deleteGroup($groupName);
		$this->assertFalse($this->groupManagerPage->getRowNumber($groupName), 'Test group should not be present');
	}

	/**
	 * @test
	 */
	public function editGroup_ChangeFields_FieldsChanged()
	{
		$salt = rand();
		$groupName = 'Group' . $salt;
		$parent = 'Author';
		$this->assertFalse($this->groupManagerPage->getRowNumber($groupName), 'Test group should not be present');
		$this->groupManagerPage->addGroup($groupName, $parent);
		$this->groupManagerPage->editGroup($groupName, array('Group Parent' => 'Publisher'));
		$rowText = $this->groupManagerPage->getRowText($groupName);
		$values = $this->groupManagerPage->getFieldValues('GroupEditPage', $groupName, array('Group Parent'));
		$this->assertStringEndsWith('Publisher', $values[0], 'Actual group parent should be Publisher');
		$this->groupManagerPage->deleteGroup($groupName);
	}
}
