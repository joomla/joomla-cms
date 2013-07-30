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
 * This class tests the  News Feeds: Add / Edit  Screen.
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.0
 */
class NewsFeedManager0002Test extends JoomlaWebdriverTestCase
{
  /**
	 * The page class being tested.
	 *
	 * @var     NewsFeedManagerPage
	 * @since   3.0
	 */
	protected $newsFeedManagerPage = null;
	
	/**
	 * Login to back end and navigate to menu NewsFeed.
	 *
	 * @since   3.0
	 */
	public function setUp()
	{
		parent::setUp();
		$cpPage = $this->doAdminLogin();
		$this->newsFeedManagerPage = $cpPage->clickMenu('Newsfeeds', 'NewsFeedManagerPage');
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
	public function getFilters_GetListOfFilters_ShouldMatchExpected()
	{
		$actualIds = $this->newsFeedManagerPage->getFilters();
		$expectedIds = array_values($this->newsFeedManagerPage->filters);
		$this->assertEquals($expectedIds, $actualIds, 'Filter ids should match expected');
	}
	
	/**
	 * @test
	 */
	public function setFilter_SetFilterValues_ShouldExecuteFilter()
	{
		$salt = rand();
		$feedName = 'Test Filter' . $salt;
		$this->newsFeedManagerPage->addFeed($feedName);
		$message = $this->newsFeedManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'News feed successfully saved') >= 0, 'News Feed save should return success');
		$test = $this->newsFeedManagerPage->setFilter('filter_published', 'Unpublished');
		$this->assertFalse($this->newsFeedManagerPage->getRowNumber($feedName), 'Feed should not show');
		$test = $this->newsFeedManagerPage->setFilter('filter_published', 'Published');
		$this->assertEquals(5, $this->newsFeedManagerPage->getRowNumber($feedName), 'Feed should be in row 5');
		$this->newsFeedManagerPage->trashAndDelete($feedName);
		$this->assertFalse($this->newsFeedManagerPage->getRowNumber($feedName), 'Feed should not be present');
	}
	
	/**
	 * @test
	 */
	public function setFilter_TestFilters_ShouldFilterTags()
	{
		$feedName_1 = 'Test Filter 1';
		$feedName_2 = 'Test Filter 2';
		
		$this->newsFeedManagerPage->addFeed($feedName_1);
		$message = $this->newsFeedManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'News feed successfully saved') >= 0, 'NewsFeed save should return success');
		$state = $this->newsFeedManagerPage->getState($feedName_1);
		$this->assertEquals('published', $state, 'Initial state should be published');
		
	
		$this->newsFeedManagerPage->addFeed($feedName_2);
		$message = $this->newsFeedManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'News feed successfully saved') >= 0, 'NewsFeed save should return success');
		$state = $this->newsFeedManagerPage->getState($feedName_2);
		$this->assertEquals('published', $state, 'Initial state should be published');
		$this->newsFeedManagerPage->changeFeedState($feedName_2, 'Unpublished');
		
		$test = $this->newsFeedManagerPage->setFilter('filter_published', 'Unpublished');
		$this->assertFalse($this->newsFeedManagerPage->getRowNumber($feedName_1), 'NewsFeed should not show');
		$this->assertEquals(1, $this->newsFeedManagerPage->getRowNumber($feedName_2), 'NewsFeed should be in row 1');
		
		$test = $this->newsFeedManagerPage->setFilter('filter_published', 'Published');
		$this->assertFalse($this->newsFeedManagerPage->getRowNumber($feedName_2), 'NewsFeed should not show');
		$this->assertEquals(5, $this->newsFeedManagerPage->getRowNumber($feedName_1), 'NewsFeed should be in row 5');
		
		$this->newsFeedManagerPage->setFilter('Select Status', 'Select Status');
		$this->newsFeedManagerPage->trashAndDelete($feedName_1);
		$this->newsFeedManagerPage->trashAndDelete($feedName_2);
	}
	
}
