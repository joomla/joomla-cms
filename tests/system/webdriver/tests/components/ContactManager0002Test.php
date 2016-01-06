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
 * This class tests the  Contact: Add / Edit  Screen.
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.2
 */
class ContactManager0002Test extends JoomlaWebdriverTestCase
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
	 * @return void
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
	 * @return void
	 *
	 * @since   3.2
	 */
	public function tearDown()
	{
		$this->doAdminLogout();
		parent::tearDown();
	}

	/**
	 * get list of filters and match it with expected IDs
	 *
	 * @return void
	 *
	 * @test
	 */
	public function getFilters_GetListOfFilters_ShouldMatchExpected()
	{
		$actualIds = $this->contactManagerPage->getFilters();
		$expectedIds = array_values($this->contactManagerPage->filters);
		$this->assertEquals($expectedIds, $actualIds, 'Filter ids should match expected');
	}

	/**
	 * checking the working of published and unpublished filters
	 *
	 * @return void
	 *
	 * @test
	 */
	public function setFilter_SetFilterValues_ShouldExecuteFilter()
	{
		$salt = rand();
		$contactName = 'Test Filter' . $salt;
		$this->contactManagerPage->addContact($contactName, false);
		$message = $this->contactManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Contact successfully saved') >= 0, 'Contact save should return success');
		$test = $this->contactManagerPage->setFilter('filter_published', 'Unpublished');
		$this->assertFalse($this->contactManagerPage->getRowNumber($contactName), 'Contact should not show');
		$test = $this->contactManagerPage->setFilter('filter_published', 'Published');
		$this->assertGreaterThanOrEqual(1, $this->contactManagerPage->getRowNumber($contactName), 'Contact should be present');
		$this->contactManagerPage->trashAndDelete($contactName);
		$this->assertFalse($this->contactManagerPage->getRowNumber($contactName), 'Contact should not be present');
	}

	/**
	 * creating two tags one published and one unpublished and the verifying its existence
	 *
	 * @return void
	 *
	 * @test
	 */
	public function setFilter_TestFilters_ShouldFilterContacts()
	{
		$contactName_1 = 'Test Filter 1';
		$contactName_2 = 'Test Filter 2';

		$this->contactManagerPage->addContact($contactName_1, false);
		$message = $this->contactManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Contact successfully saved') >= 0, 'Contact save should return success');
		$state = $this->contactManagerPage->getState($contactName_1);
		$this->assertEquals('published', $state, 'Initial state should be published');

		$this->contactManagerPage->addContact($contactName_2, false);
		$message = $this->contactManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Contact successfully saved') >= 0, 'Contact save should return success');
		$state = $this->contactManagerPage->getState($contactName_2);
		$this->assertEquals('published', $state, 'Initial state should be published');
		$this->contactManagerPage->changeContactState($contactName_2, 'unpublished');

		$test = $this->contactManagerPage->setFilter('filter_published', 'Unpublished');
		$this->assertFalse($this->contactManagerPage->getRowNumber($contactName_1), 'Contact should not show');
		$this->assertGreaterThanOrEqual(1, $this->contactManagerPage->getRowNumber($contactName_2), 'Contact should be present');

		$test = $this->contactManagerPage->setFilter('filter_published', 'Published');
		$this->assertFalse($this->contactManagerPage->getRowNumber($contactName_2), 'Contact should not show');
		$this->assertGreaterThanOrEqual(1, $this->contactManagerPage->getRowNumber($contactName_1), 'Contact should be present');

		$this->contactManagerPage->setFilter('Select Status', 'Select Status');
		$this->contactManagerPage->trashAndDelete($contactName_1);
		$this->contactManagerPage->trashAndDelete($contactName_2);
	}

	/**
	 * create archived contact and then verify its existence.
	 *
	 * @return void
	 *
	 * @test
	 */
	public function setFilter_TestFilters_ShouldFilterTags2()
	{
		$contactName_1 = 'Test Filter 1';
		$contactName_2 = 'Test Filter 2';

		$this->contactManagerPage->addContact($contactName_1, false);
		$message = $this->contactManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Contact successfully saved') >= 0, 'Contact save should return success');
		$state = $this->contactManagerPage->getState($contactName_1);
		$this->assertEquals('published', $state, 'Initial state should be published');
		$this->contactManagerPage->addContact($contactName_2, false);
		$message = $this->contactManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Contact successfully saved') >= 0, 'Contact save should return success');
		$state = $this->contactManagerPage->getState($contactName_2);
		$this->assertEquals('published', $state, 'Initial state should be published');
		$this->contactManagerPage->changeContactState($contactName_2, 'Archived');

		$this->contactManagerPage->setFilter('filter_published', 'Archived');
		$this->assertFalse($this->contactManagerPage->getRowNumber($contactName_1), 'Contact should not show');
		$this->assertGreaterThanOrEqual(1, $this->contactManagerPage->getRowNumber($contactName_2), 'Test Contact should be present');

		$this->contactManagerPage->setFilter('filter_published', 'Published');
		$this->assertFalse($this->contactManagerPage->getRowNumber($contactName_2), 'Contact should not show');
		$this->assertGreaterThanOrEqual(1, $this->contactManagerPage->getRowNumber($contactName_1), 'Test Contact should be present');
		$this->contactManagerPage->setFilter('Select Status', 'Select Status');
		$this->contactManagerPage->trashAndDelete($contactName_1);
		$this->contactManagerPage->trashAndDelete($contactName_2);
	}
}
