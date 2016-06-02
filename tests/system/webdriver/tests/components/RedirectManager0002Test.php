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
 * This class tests the  Redirect: Add / Edit  Screen.
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.0
 */
class RedirectManager0002Test extends JoomlaWebdriverTestCase
{
	/**
	 * The page class being tested.
	 *
	 * @var     RedirectManagerPage
	 * @since   3.0
	 */
	protected $redirectManagerPage = null;

	/**
	 * Login to back end and navigate to menu Redirect.
	 *
	 * @return void
	 *
	 * @since   3.0
	 */
	public function setUp()
	{
		parent::setUp();
		$cpPage = $this->doAdminLogin();
		$this->redirectManagerPage = $cpPage->clickMenu('Redirect', 'RedirectManagerPage');
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
	 * get list of filters and match it with expected IDs
	 *
	 * @return void
	 *
	 * @test
	 */
	public function getFilters_GetListOfFilters_ShouldMatchExpected()
	{
		$actualIds = $this->redirectManagerPage->getFilters();
		$expectedIds = array_values($this->redirectManagerPage->filters);
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
		$srcName = 'administrator/index.php/dummysrc' . $salt;
		$this->redirectManagerPage->addRedirect($srcName);
		$message = $this->redirectManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Link successfully saved') >= 0, 'Redirect save should return success');
		$test = $this->redirectManagerPage->setFilter('filter_state', 'Disabled');
		$this->assertFalse($this->redirectManagerPage->getRowNumber($srcName), 'Redirect should not show');
		$test = $this->redirectManagerPage->setFilter('filter_state', 'Enabled');
		$this->assertGreaterThanOrEqual(1, $this->redirectManagerPage->getRowNumber($srcName), 'Redirect should be in row 1');
		$this->redirectManagerPage->trashAndDelete($srcName);
		$this->assertFalse($this->redirectManagerPage->getRowNumber($srcName), 'Redirect should not be present');
	}

	/**
	 * creating two tags one published and one unpublished and the verifying its existence
	 *
	 * @return void
	 *
	 * @test
	 */
	public function setFilter_TestFilters_ShouldFilterRedirect()
	{
		$salt = rand();
		$srcName_1 = 'administrator/index.php/dummysrc1' . $salt;
		$srcName_2 = 'administrator/index.php/dummysrc2' . $salt;

		$this->redirectManagerPage->addRedirect($srcName_1);
		$message = $this->redirectManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Link successfully saved') >= 0, 'Redirect save should return success');
		$state = $this->redirectManagerPage->getState($srcName_1);
		$this->assertEquals('published', $state, 'Initial state should be published');

		$this->redirectManagerPage->addRedirect($srcName_2);
		$message = $this->redirectManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Link successfully saved') >= 0, 'Redirect save should return success');
		$state = $this->redirectManagerPage->getState($srcName_2);
		$this->assertEquals('published', $state, 'Initial state should be published');
		$this->redirectManagerPage->changeRedirectState($srcName_2, 'unpublished');

		$test = $this->redirectManagerPage->setFilter('filter_state', 'Disabled');
		$this->assertFalse($this->redirectManagerPage->getRowNumber($srcName_1), 'Redirect should not show');
		$this->assertGreaterThanOrEqual(1, $this->redirectManagerPage->getRowNumber($srcName_2), 'Redirect should be in row 1');

		$test = $this->redirectManagerPage->setFilter('filter_state', 'Enabled');
		$this->assertFalse($this->redirectManagerPage->getRowNumber($srcName_2), 'Redirect should not show');
		$this->assertGreaterThanOrEqual(1, $this->redirectManagerPage->getRowNumber($srcName_1), 'Redirect should be in row 1');

		$this->redirectManagerPage->setFilter('Select Status', 'Select Status');
		$this->redirectManagerPage->trashAndDelete($srcName_1);
		$this->redirectManagerPage->trashAndDelete($srcName_2);
	}

	/**
	 * create archived redirects and then verify its existence.
	 *
	 * @return void
	 *
	 * @test
	 */

	public function setFilter_TestFilters_ShouldFilterTags2()
	{
		$salt = rand();
		$srcName_1 = 'administrator/index.php/dummysrc1' . $salt;
		$srcName_2 = 'administrator/index.php/dummysrc2' . $salt;

		$this->redirectManagerPage->addRedirect($srcName_1);
		$message = $this->redirectManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Redirect successfully saved') >= 0, 'Redirect save should return success');
		$state = $this->redirectManagerPage->getState($srcName_1);
		$this->assertEquals('published', $state, 'Initial state should be published');
		$this->redirectManagerPage->addRedirect($srcName_2);
		$message = $this->redirectManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Redirect successfully saved') >= 0, 'Redirect save should return success');
		$state = $this->redirectManagerPage->getState($srcName_1);
		$this->assertEquals('published', $state, 'Initial state should be published');
		$this->redirectManagerPage->changeRedirectState($srcName_2, 'Archived');

		$this->redirectManagerPage->setFilter('filter_state', 'Archived');
		$this->assertFalse($this->redirectManagerPage->getRowNumber($srcName_1), 'Redirect should not show');
		$this->assertGreaterThanOrEqual(1, $this->redirectManagerPage->getRowNumber($srcName_2), 'Test Redirect should be present');

		$this->redirectManagerPage->setFilter('filter_state', 'Enabled');
		$this->assertFalse($this->redirectManagerPage->getRowNumber($srcName_2), 'Redirect should not show');
		$this->assertGreaterThanOrEqual(1, $this->redirectManagerPage->getRowNumber($srcName_1), 'Test Redirect should be present');
		$this->redirectManagerPage->setFilter('Select Status', 'Select Status');
		$this->redirectManagerPage->trashAndDelete($srcName_1);
		$this->redirectManagerPage->trashAndDelete($srcName_2);
	}
}
