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
 * This class tests the  Weblinks: Add / Edit  Screen.
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.2
 */
class WeblinkManager0002Test extends JoomlaWebdriverTestCase
{
	/**
	 * The page class being tested.
	 *
	 * @var     weblinkManagerPage
	 * @since   3.2
	 */
	protected $weblinkManagerPage = null;

	/**
	 * Login to back end and navigate to menu Weblinks.
	 *
	 * @return void
	 *
	 * @since   3.2
	 */
	public function setUp()
	{
		parent::setUp();
		$cpPage = $this->doAdminLogin();
		$this->weblinkManagerPage = $cpPage->clickMenu('Weblinks', 'WeblinkManagerPage');
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
	 * 
	 */
	public function getFilters_GetListOfFilters_ShouldMatchExpected()
	{
		$actualIds = $this->weblinkManagerPage->getFilters();
		$expectedIds = array_values($this->weblinkManagerPage->filters);
		$this->assertEquals($expectedIds, $actualIds, 'Filter ids should match expected');
	}

	/**
	 * checking the working of published and unpublished filters
	 *
	 * @return void
	 *
	 * 
	 */
	public function setFilter_SetFilterValues_ShouldExecuteFilter()
	{
		$salt = rand();
		$weblinkName = 'Test Filter' . $salt;
		$url = 'www.example.com';
		$this->weblinkManagerPage->addWeblink($weblinkName, $url, false);
		$message = $this->weblinkManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Weblink successfully saved') >= 0, 'Weblink save should return success');
		$test = $this->weblinkManagerPage->setFilter('filter_state', 'Unpublished');
		$this->assertFalse($this->weblinkManagerPage->getRowNumber($weblinkName), 'Weblink should not show');
		$test = $this->weblinkManagerPage->setFilter('filter_state', 'Published');
		$this->assertEquals(10, $this->weblinkManagerPage->getRowNumber($weblinkName), 'Weblink should be in row 10');
		$this->weblinkManagerPage->trashAndDelete($weblinkName);
		$this->assertFalse($this->weblinkManagerPage->getRowNumber($weblinkName), 'Weblink should not be present');
	}

	/**
	 * creating two tags one published and one unpublished and the verifying its existence
	 *
	 * @return void
	 *
	 * 
	 */
	public function setFilter_TestFilters_ShouldFilterWeblinks()
	{
		$weblinkName_1 = 'Test Filter 1';
		$weblinkName_2 = 'Test Filter 2';
		$url = 'www.example.com';

		$this->weblinkManagerPage->addWeblink($weblinkName_1, $url, false);
		$message = $this->weblinkManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Weblink successfully saved') >= 0, 'Weblink save should return success');
		$state = $this->weblinkManagerPage->getState($weblinkName_1);
		$this->assertEquals('published', $state, 'Initial state should be published');

		$this->weblinkManagerPage->addWeblink($weblinkName_2, $url, false);
		$message = $this->weblinkManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Weblink successfully saved') >= 0, 'Weblink save should return success');
		$state = $this->weblinkManagerPage->getState($weblinkName_2);
		$this->assertEquals('published', $state, 'Initial state should be published');
		$this->weblinkManagerPage->changeWeblinkState($weblinkName_2, 'unpublished');

		$test = $this->weblinkManagerPage->setFilter('filter_state', 'Unpublished');
		$this->assertFalse($this->weblinkManagerPage->getRowNumber($weblinkName_1), 'Weblink should not show');
		$this->assertEquals(1, $this->weblinkManagerPage->getRowNumber($weblinkName_2), 'Weblink should be in row 1');

		$test = $this->weblinkManagerPage->setFilter('filter_state', 'Published');
		$this->assertFalse($this->weblinkManagerPage->getRowNumber($weblinkName_2), 'Weblink should not show');
		$this->assertEquals(10, $this->weblinkManagerPage->getRowNumber($weblinkName_1), 'Weblink should be in row 10');

		$this->weblinkManagerPage->setFilter('Select Status', 'Select Status');
		$this->weblinkManagerPage->trashAndDelete($weblinkName_1);
		$this->weblinkManagerPage->trashAndDelete($weblinkName_2);
	}
}
