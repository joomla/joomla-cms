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
 * @since       3.2
 */
class ContactManager0001Test extends JoomlaWebdriverTestCase
{
	/**
	 * The page class being tested.
	 *
	 * @var     contactManagerPage
	 * @since   3.2
	 */
	protected $contactManagerPage = null;

	/**
	 * Login to back end and navigate to menu Contacts.
	 *
	 * @since   3.2
	 */
	public function setUp()
	{
		parent::setUp();
		$cpPage = $this->doAdminLogin();
		$this->contactManagerPage = $cpPage->clickMenu('Contacts', 'ContactManagerPage');
	}

	/**
	 * Logout and close test.
	 *
	 * @since   3.2
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
			$actualFields[] = array('label' => $el->labelText, 'id' => $el->id, 'type' => $el->tag, 'tab' => $el->tab);
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
	public function getTabIds_ScreenDisplayed_EqualExpected()
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
		$this->contactManagerPage->addContact($contactName, false);
		$message = $this->contactManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Contact successfully saved') >= 0, 'Contact save should return success');
		$this->assertEquals(5, $this->contactManagerPage->getRowNumber($contactName), 'Test Contact should be in row 5');
		$this->contactManagerPage->deleteItem($contactName);
		$this->assertFalse($this->contactManagerPage->getRowNumber($contactName), 'Test Contact should not be present');
	}

	/**
	 * @test
	 */
	public function addContact_WithGivenFields_ContactAdded()
	{
		$salt = rand();
		$contactName = 'Contact' . $salt;
		$address='10 Downing Street';
		$city='London';
		$country='England';

		$this->assertFalse($this->contactManagerPage->getRowNumber($contactName), 'Test contact should not be present');
		$this->contactManagerPage->addContact($contactName, array('Country' => $country, 'Address' => $address, 'City or Suburb' => $city));
		$message = $this->contactManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Contact successfully saved') >= 0, 'Contact save should return success');
		$this->assertEquals(5, $this->contactManagerPage->getRowNumber($contactName), 'Test test contact should be in row 5');
		$values = $this->contactManagerPage->getFieldValues('ContactEditPage', $contactName, array('Name', 'Address', 'City or Suburb', 'Country'));
		$this->assertEquals(array($contactName,$address,$city,$country), $values, 'Actual name, address, city and country should match expected');
		$this->contactManagerPage->deleteItem($contactName);
		$this->assertFalse($this->contactManagerPage->getRowNumber($contactName), 'Test contact should not be present');
	}

	/**
	 * @test
	 */
	public function editContact_ChangeFields_FieldsChanged()
	{
		$salt = rand();
		$contactName = 'Contact' . $salt;
		$this->assertFalse($this->contactManagerPage->getRowNumber($contactName), 'Test contact should not be present');
		$this->contactManagerPage->addContact($contactName, false);
		$this->contactManagerPage->editContact($contactName, array('Country' => 'England', 'Address' => '10 Downing Street', 'City or Suburb' => 'London'));
		$values = $this->contactManagerPage->getFieldValues('ContactEditPage', $contactName, array('Country', 'Address', 'City or Suburb'));
		$this->contactManagerPage->deleteItem($contactName);
	}

	/**
	 * @test
	 */
	public function changeContactState_ChangeEnabledUsingToolbar_EnabledChanged()
	{
		$salt = rand();
		$contactName = 'Contact' . $salt;
		$this->contactManagerPage->addContact($contactName, false);
		$state = $this->contactManagerPage->getState($contactName);
		$this->assertEquals('published', $state, 'Initial state should be published');
		$this->contactManagerPage->changeContactState($contactName, 'unpublished');
		$state = $this->contactManagerPage->getState($contactName);
		$this->assertEquals('unpublished', $state, 'State should be unpublished');
		$this->contactManagerPage->deleteItem($contactName);
	}
}
