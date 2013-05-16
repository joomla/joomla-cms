<?php
/**
 * @package     Joomla.Test
 * @subpackage  Webdriver
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once 'JoomlaWebdriverTestCase.php';

use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;

/**
 * This class tests the  Contact: Add / Edit  Screen.
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.0
 */
class ContactManager0001Test extends JoomlaWebdriverTestCase
{
	/**
	 * The page class being tested.
	 *
	 * @var     contactManagerPage
	 * @since   3.0
	 */
	protected $contactManagerPage = null;
	
	/**
	 * Login to back end and navigate to menu Contact.
	 *
	 * @since   3.0
	 */
	public function setUp()
	{
		parent::setUp();
		$cpPage = $this->doAdminLogin();
		$this->contactManagerPage = $cpPage->clickMenu('Contacts', 'contactManagerPage');
	}

	/**
	 * Logout and close test.
	 *
	 * @since   3.0
	 */
	public function tearDown()
	{
		$this->doAdminLogout();
		parent::tearDown();
	}
	
	/**
	 * @test
	 */
	public function getAllInputFields_ScreenDisplayed_EqualExpected()
	{
		$this->contactManagerPage->clickButton('toolbar-new');
		$contactEditPage = $this->getPageObject('ContactEditPage');
		$testElements = $contactEditPage->getAllInputFields(array('details', 'publishing', 'basic', 'params-jbasic', 'params-email', 'metadata'));
		$actualFields = array();
		foreach ($testElements as $el)
		{
			$el->labelText = (substr($el->labelText, -2) == ' *') ? substr($el->labelText, 0, -2) : $el->labelText;
			$actualFields[] = array('label' => $el->labelText, 'id' => $el->id, 'type' => $el->contact, 'tab' => $el->tab);
		}
		$this->assertEquals($contactEditPage->inputFields, $actualFields);
		$contactEditPage->clickButton('toolbar-cancel');
		$this->contactManagerPage = $this->getPageObject('ContactManagerPage');
	}
	
	/**
	 * @test
	 */
	public function constructor_OpenEditScreen_ContactEditOpened()
	{
		$this->contactManagerPage->clickButton('new');
		$contactEditPage = $this->getPageObject('ContactEditPage');
		$contactEditPage->clickButton('cancel');
		$this->contactManagerPage = $this->getPageObject('ContactManagerPage');
	}
	
	/**
	 * @test
	 */
	public function getContactIds_ScreenDisplayed_EqualExpected()
	{
		$this->contactManagerPage->clickButton('toolbar-new');
		$contactEditPage = $this->getPageObject('ContactEditPage');
		$textArray = $contactEditPage->getTabIds();
		$this->assertEquals($contactEditPage->tabs, $textArray, 'Contact labels should match expected values.');
		$contactEditPage->clickButton('toolbar-cancel');
		$this->contactManagerPage = $this->getPageObject('ContactManagerPage');
	}
	
	/**
	 * @test
	 */
	public function addContact_WithFieldDefaults_ContactAdded()
	{
		$salt = rand();
		$contactName = 'Contact' . $salt;
		$this->assertFalse($this->contactManagerPage->getRowNumber($contactName), 'Test Contact should not be present');
		$this->contactManagerPage->addContact($contactName);
		$message = $this->contactManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Contact successfully saved') >= 0, 'Contact save should return success');
		$this->assertEquals(1, $this->contactManagerPage->getRowNumber($contactName), 'Test Contact should be in row 2');
		$this->contactManagerPage->deleteItem($contactName);
		$this->assertFalse($this->contactManagerPage->getRowNumber($contactName), 'Test Contact should not be present');
	}

	/**
	 * @test
	 */
	public function editContact_ChangeFields_FieldsChanged()
	{
		$salt = rand();
		$contactName = 'Contact' . $salt;
		$this->assertFalse($this->contactManagerPage->getRowNumber($contactName), 'Test contact should not be present');
		$this->contactManagerPage->addContact($contactName);
		$this->contactManagerPage->editContact($contactName);
		$values = $this->contactManagerPage->getFieldValues('contactEditPage', $contactName);
		$this->contactManagerPage->deleteItem($contactName);
	}
	
	/**
	 * @test
	 */
	public function changeContactState_ChangeEnabledUsingToolbar_EnabledChanged()
	{
		$this->contactManagerPage->addContact('Test Contact');
		$state = $this->contactManagerPage->getState('Test Contact');
		$this->assertEquals('published', $state, 'Initial state should be published');
		$this->contactManagerPage->changeContactState('Test Contact', 'unpublished');
		$state = $this->contactManagerPage->getState('Test Contact');
		$this->assertEquals('unpublished', $state, 'State should be unpublished');
		$this->contactManagerPage->deleteItem('Test Contact');
	}	
}
