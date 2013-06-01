<?php

require_once 'JoomlaWebdriverTestCase.php';

/**
 * This class tests the  Manager: Add / Edit  Screen
 * @author Mark
 *
 */
class LevelManager0001Test extends JoomlaWebdriverTestCase
{
	/**
	 *
	 * @var LevelManagerPage
	 */
	protected $levelManagerPage = null; // Global configuration page

	public function setUp()
	{
		parent::setUp();
		$cpPage = $this->doAdminLogin();
		$this->levelManagerPage = $cpPage->clickMenu('Access Levels', 'LevelManagerPage');
	}

	public function tearDown()
	{
		$this->doAdminLogout();
		parent::tearDown();
	}

	/**
	 * @test
	 */
	public function constructor_OpenEditScreen_LevelEditOpened()
	{
		$this->levelManagerPage->clickButton('toolbar-new');
		$levelEditPage = $this->getPageObject('LevelEditPage');
		$levelEditPage->clickButton('toolbar-cancel');
		$this->levelManagerPage = $this->getPageObject('LevelManagerPage');
	}

	/**
	 * @test
	 */
	public function getAllInputFields_ScreenDisplayed_EqualExpected()
	{
		$this->levelManagerPage->clickButton('toolbar-new');
		$levelEditPage = $this->getPageObject('LevelEditPage');

		$testElements = $levelEditPage->getAllInputFields();
		$actualFields = array();
		foreach ($testElements as $el)
		{
			$el->labelText = (substr($el->labelText, -2) == ' *') ? substr($el->labelText, 0, -2) : $el->labelText;
			$actualFields[] = array('label' => $el->labelText, 'id' => $el->id, 'type' => $el->tag, 'tab' => $el->tab);
		}
		$this->assertEquals($levelEditPage->inputFields, $actualFields);
		$levelEditPage->clickButton('toolbar-cancel');
		$this->levelManagerPage = $this->getPageObject('LevelManagerPage');
	}

	/**
	 * @test
	 */
	public function add_WithFieldDefaults_Added()
	{
		$this->assertFalse($this->levelManagerPage->getRowNumber('Test Level'), 'Test level should not be present');
		$this->levelManagerPage->addLevel();
		$message = $this->levelManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Level successfully saved') >= 0, 'Level save should return success');
		$this->assertEquals(6, $this->levelManagerPage->getRowNumber('Test Level'), 'Test level should be in row 6');
		$this->levelManagerPage->deleteLevel('Test Level');
		$this->assertFalse($this->levelManagerPage->getRowNumber('Test Level'), 'Test level should not be present');
	}

	/**
	 * @test
	 */
	public function addLevel_WithGivenFields_LevelAdded()
	{
		$salt = rand();
		$levelName = 'Level' . $salt;
		$groups = array('Registered', 'Manager');
		$this->assertFalse($this->levelManagerPage->getRowNumber($levelName), 'Test level should not be present');
		$this->levelManagerPage->addLevel($levelName, $groups);
		$message = $this->levelManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Level successfully saved') >= 0, 'Level save should return success');
		$this->assertTrue($this->levelManagerPage->getRowNumber($levelName) > 0, 'Test level should be on the page');
		$actualGroups = $this->levelManagerPage->getGroups($levelName);
		sort($groups);
		sort($actualGroups);
		$this->assertEquals($groups, $actualGroups, 'Assigned groups should be as expected');
		$this->levelManagerPage->deleteLevel($levelName);
		$this->assertFalse($this->levelManagerPage->getRowNumber($levelName), 'Test level should not be present');
	}

	/**
	 * @test
	 */
	public function editLevel_ChangeFields_FieldsChanged()
	{
		$salt = rand();
		$levelName = 'Level' . $salt;
		$groups = array('Customer', 'Administrator', 'Author');
		$this->assertFalse($this->levelManagerPage->getRowNumber($levelName), 'Test level should not be present');
		$this->levelManagerPage->addLevel($levelName, $groups);
		$newGroups = array('Manager', 'Publisher');
		$this->levelManagerPage->editLevel($levelName, $newGroups);
		$actualGroups = $this->levelManagerPage->getGroups($levelName);
		sort($actualGroups);
		sort($newGroups);
		$this->assertEquals($newGroups, $actualGroups, 'New groups should be assigned to level');
		$this->levelManagerPage->deleteLevel($levelName);
	}
}
