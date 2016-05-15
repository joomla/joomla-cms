<?php

require_once 'JoomlaWebdriverTestCase.php';

use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;

/**
 * This class tests the Module Manager: Add / Edit Module Screen
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.0
 */
class ModuleManager0001Test extends JoomlaWebdriverTestCase
{
	/**
	 *
	 * @var ModuleManagerPage
	 */
	protected $moduleManagerPage = null;

	/**
	 * Login to back end and navigate to menu Tags.
	 *
	 * @return void
	 *
	 * @since   3.0
	 */
	public function setUp()
	{
		parent::setUp();
		$cpPage = $this->doAdminLogin();
		/* @var $cpPage ControlPanelPage */
		$this->moduleManagerPage = $cpPage->clickMenu('Module Manager', 'ModuleManagerPage');
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
	 * check tag edit page
	 *
	 * @return void
	 *
	 * @test
	 */
	public function constructor_OpenEditScreen_ModuleEditOpened()
	{
		$this->moduleManagerPage->clickButton('new');
		$this->driver->waitForElementUntilIsPresent(By::xPath("//a[contains(., 'Articles - Categories')]"))->click();
		/* @var $moduleEditPage ModuleEditPage */
		$moduleEditPage = $this->getPageObject('ModuleEditPage');
		$moduleEditPage->clickButton('cancel');
		$this->moduleManagerPage = $this->getPageObject('ModuleManagerPage');
	}

	/**
	 * check tab Ids
	 *
	 * @return void
	 *
	 * @test
	 */
	public function getTabIds_ScreenDisplayed_EqualExpected()
	{
		$this->moduleManagerPage->clickButton('toolbar-new');
		$this->driver->waitForElementUntilIsPresent(By::xPath("//a[contains(., 'Articles - Categories')]"))->click();
		$moduleEditPage = $this->getPageObject('ModuleEditPage');
		$textArray = $moduleEditPage->getTabIds();
		$this->assertEquals($moduleEditPage->tabs, $textArray, 'Tab labels should match expected values.');
		$moduleEditPage->clickButton('toolbar-cancel');
		$this->moduleManagerPage = $this->getPageObject('ModuleManagerPage');
	}

	/**
	 * check available module types
	 *
	 * @return void
	 *
	 * @test
	 */
	public function getModuleTypes_GetsTypes_EqualsExpected()
	{
		$actualModuleTypes = $this->moduleManagerPage->getModuleTypes();
		$this->assertEquals($this->moduleManagerPage->moduleTypes, $actualModuleTypes, 'Module types should equal expected');
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
		$this->moduleManagerPage->clickButton('toolbar-new');
		$this->driver->waitForElementUntilIsPresent(By::xPath("//a[contains(., 'Articles - Categories')]"))->click();
		$moduleEditPage = $this->getPageObject('ModuleEditPage');

		$testElements = $moduleEditPage->getAllInputFields($moduleEditPage->tabs);
		$actualFields = $this->getActualFieldsFromElements($testElements);
		$this->assertLessThanOrEqual($moduleEditPage->inputFields, $actualFields);
		$moduleEditPage->clickButton('toolbar-cancel');
		$this->moduleManagerPage = $this->getPageObject('ModuleManagerPage');
	}

	/**
	 * add module with default values
	 *
	 * @return void
	 *
	 * @test
	 */
	public function addModule_WithFieldDefaults_ModuleAdded()
	{
		$this->assertFalse($this->moduleManagerPage->getRowNumber('Test Module'), 'Test module should not be present');
		$this->moduleManagerPage->addModule();
		$message = $this->moduleManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Module successfully saved') >= 0, 'Module save should return success');
		$this->moduleManagerPage->searchFor('Test Module');
		$this->assertTrue($this->moduleManagerPage->getRowNumber('Test Module') > 0, 'Test module should be in row 2');
		$this->moduleManagerPage->trashAndDelete('Test Module');
		$this->assertFalse($this->moduleManagerPage->getRowNumber('Test Module'), 'Test module should not be present');
	}

	/**
	 * add module with given values
	 *
	 * @return void
	 *
	 * @test
	 */
	public function addModule_WithGivenFields_ModuleAdded()
	{
		$salt = rand();
		$title = 'Module' . $salt;
		$client = 'Administrator';
		$type = 'Custom HTML';
		$position = 'mynewposition';
		$suffix = 'mysuffix';
		$otherFields = array('Position' => $position, 'Module Class Suffix' => $suffix);
		$this->moduleManagerPage->setFilter('filter_client_id', $client)->searchFor($title);
		$this->assertFalse($this->moduleManagerPage->getRowNumber($title), 'Test module should not be present');
		$this->moduleManagerPage->addModule($title, $client, $type, $otherFields);
		$message = $this->moduleManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Module successfully saved') >= 0, 'Module save should return success');
		$this->moduleManagerPage->searchFor($title);
		$this->assertTrue($this->moduleManagerPage->getRowNumber($title) > 0, 'Test module should be present');

		$values = $this->moduleManagerPage->getModuleFieldValues($title, $client, array('Position', 'Module Class Suffix'));
		$this->assertEquals(array($position, $suffix), $values, 'Actual position and suffix should match expected');
		$this->moduleManagerPage->trashAndDelete($title);
		$this->moduleManagerPage->searchFor($title);
		$this->assertFalse($this->moduleManagerPage->getRowNumber($title), 'Test module should not be present');
		$this->moduleManagerPage->searchFor();
	}

	/**
	 * edit the value of the input fields in the module
	 *
	 * @return void
	 *
	 * @test
	 */
	public function editModule_ChangeFields_FieldsChanged()
	{
		$salt = rand();
		$title = 'Module' . $salt;
		$client = 'Administrator';
		$type = 'Custom HTML';
		$position = 'myposition';
		$suffix = 'mysuffix';
		$note = 'My old note.';
		$otherFields = array('Position' => $position, 'Module Class Suffix' => $suffix, 'Note' => $note);
		$this->moduleManagerPage->setFilter('filter_client_id', $client)->searchFor($title);
		$this->assertFalse($this->moduleManagerPage->getRowNumber($title), 'Test module should not be present');
		$this->moduleManagerPage->addModule($title, $client, $type, $otherFields);
		$message = $this->moduleManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Module successfully saved') >= 0, 'Module save should return success');
		$this->moduleManagerPage->searchFor($title);
		$this->assertTrue($this->moduleManagerPage->getRowNumber($title) > 0, 'Test module should be present');

		$values = $this->moduleManagerPage->getModuleFieldValues($title, $client, array('Position', 'Module Class Suffix'));
		$this->assertEquals(array($position, $suffix), $values, 'Actual position and suffix should match expected');

		$newTitle = 'New Module Title' . $salt;
		$newPosition = 'mynewposition';
		$newSuffix = 'mynewsuffix';
		$newNote = 'my new note';
		$this->moduleManagerPage->editModule($title, array('Title' => $newTitle, 'Position' => $newPosition, 'Module Class Suffix' => $newSuffix, 'Note' => $newNote));

		$values = $this->moduleManagerPage->getModuleFieldValues($newTitle, $client, array('Title', 'Position', 'Module Class Suffix', 'Note'));
		$this->assertEquals(array($newTitle, $newPosition, $newSuffix, $newNote), $values, 'Actual values should match expected');
		$this->moduleManagerPage->trashAndDelete($newTitle);
	}

	/**
	 * change the state of the module and verufy
	 *
	 * @return void
	 *
	 * @test
	 */
	public function changeModuleState_ChangeEnabledUsingToolbar_EnabledChanged()
	{
		$this->moduleManagerPage->addModule('Test Module');
		$state = $this->moduleManagerPage->getState('Test Module');
		$this->assertEquals('published', $state, 'Initial state should be published');
		$this->moduleManagerPage->changeModuleState('Test Module', 'unpublished');
		$state = $this->moduleManagerPage->getState('Test Module');
		$this->assertEquals('unpublished', $state, 'State should be unpublished');
		$this->moduleManagerPage->trashAndDelete('Test Module');
	}
}
