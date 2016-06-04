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
 * This class tests the  Tags: Add / Edit  Screen.
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.0
 */
class NewsFeedManager0001Test extends JoomlaWebdriverTestCase
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
	 * @return void
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
	 * check all input fields
	 *
	 * @return void
	 *
	 * @test
	 */
	public function getAllInputFields_ScreenDisplayed_EqualExpected()
	{
		$this->newsFeedManagerPage->clickButton('toolbar-new');
		$newsFeedEditPage = $this->getPageObject('NewsFeedEditPage');
		/* Option to print actual element array */
		/* @var $newsFeedEditPage NewsFeedEditPage */
// 	 	$newsFeedEditPage->printFieldArray($newsFeedEditPage->getAllInputFields($newsFeedEditPage->tabs));

		$testElements = $newsFeedEditPage->getAllInputFields($newsFeedEditPage->tabs);
		$actualFields = $this->getActualFieldsFromElements($testElements);
		$this->assertEquals($newsFeedEditPage->inputFields, $actualFields);
		$newsFeedEditPage->clickButton('toolbar-cancel');
		$this->newsFeedManagerPage = $this->getPageObject('NewsFeedManagerPage');
	}

	/**
	 * check Newsfeed edit page
	 *
	 * @return void
	 *
	 * @test
	 */
	public function constructor_OpenEditScreen_FeedEditOpened()
	{
		$this->newsFeedManagerPage->clickButton('new');
		$newsFeedEditPage = $this->getPageObject('NewsFeedEditPage');
		$newsFeedEditPage->clickButton('cancel');
		$this->newsFeedManagerPage = $this->getPageObject('NewsFeedManagerPage');
	}

	/**
	 * check tab IDs
	 *
	 * @return void
	 *
	 * @test
	 */
	public function getTabIds_ScreenDisplayed_EqualExpected()
	{
		$this->newsFeedManagerPage->clickButton('toolbar-new');
		$newsFeedEditPage = $this->getPageObject('NewsFeedEditPage');
		$textArray = $newsFeedEditPage->getTabIds();
		$this->assertEquals($newsFeedEditPage->tabs, $textArray, 'Tab labels should match expected values.');
		$newsFeedEditPage->clickButton('toolbar-cancel');
		$this->newsFeedManagerPage = $this->getPageObject('NewsFeedManagerPage');
	}

	/**
	 * add feed with default values
	 *
	 * @return void
	 *
	 * @test
	 */
	public function addFeed_WithFieldDefaults_FeedAdded()
	{
		$salt = rand();
		$feedName = 'Test_Feed' . $salt;
		$this->assertFalse($this->newsFeedManagerPage->getRowNumber($feedName), 'Test Feed should not be present');
		$this->newsFeedManagerPage->addFeed($feedName);
		$message = $this->newsFeedManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'News feed successfully saved') >= 0, 'Feed save should return success');
		$this->assertEquals(5, $this->newsFeedManagerPage->getRowNumber($feedName), 'Test feed should be in row 5');
		$this->newsFeedManagerPage->trashAndDelete($feedName);
		$this->assertFalse($this->newsFeedManagerPage->getRowNumber($feedName), 'Test feed should not be present');
	}

	/**
	 * add feed with given values
	 *
	 * @return void
	 *
	 * @test
	 */
	public function addFeed_WithGivenFields_FeedAdded()
	{
		$salt = rand();
		$feedName = 'Test_Feed' . $salt;
		$link = 'administrator/index.php/dummysrc' . $salt;
		/*other than the default value */
		$category = 'Uncategorised';
		$description = 'Sample Test Feed';
		$caption = 'Sample Caption';
		$alt = 'Sample Alt Test';

		$this->assertFalse($this->newsFeedManagerPage->getRowNumber($feedName), 'Test feed should not be present');
		$this->newsFeedManagerPage->addFeed($feedName, $link, $category, $description, $caption, $alt);
		$message = $this->newsFeedManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'News feed successfully saved') >= 0, 'Feed save should return success');
		$this->assertEquals(5, $this->newsFeedManagerPage->getRowNumber($feedName), 'Test feed should be in row 5');
		$values = $this->newsFeedManagerPage->getFieldValues('NewsFeedEditPage', $feedName, array('Title', 'Caption'));
		$this->assertEquals(array($feedName,$caption), $values, 'Actual name, caption should match expected');
		$this->newsFeedManagerPage->trashAndDelete($feedName);
		$this->assertFalse($this->newsFeedManagerPage->getRowNumber($feedName), 'Test feed should not be present');
	}

	/**
	 * edit feed and change the input feed values
	 *
	 * @return void
	 *
	 * @test
	 */
	public function editFeed_ChangeFields_FieldsChanged()
	{
		$salt = rand();
		$feedName = 'Test_Feed' . $salt;
		$link = 'administrator/index.php/dummysrc' . $salt;
		/* other than the default value */
		$category = 'Uncategorised';
		$description = 'Sample Test Feed';
		$caption = 'Sample Caption';
		$alt = 'Sample Alt Test';

		$this->assertFalse($this->newsFeedManagerPage->getRowNumber($feedName), 'Test feed should not be present');
		$this->newsFeedManagerPage->addFeed($feedName, $link, $category, $description, $caption, $alt);
		$this->newsFeedManagerPage->editFeed($feedName, array('Caption' => 'NewSample Caption', 'Alt text' => 'New Alt Text'));
		$values = $this->newsFeedManagerPage->getFieldValues('NewsFeedEditPage', $feedName, array('Caption', 'Alt text'));
		$this->assertEquals(array('NewSample Caption','New Alt Text'), $values, 'Actual values should match expected');
		$this->newsFeedManagerPage->trashAndDelete($feedName);
	}

	/**
	 * change the state of the feed
	 *
	 * @return void
	 *
	 * @test
	 */
	public function changeFeedState_ChangeEnabledUsingToolbar_EnabledChanged()
	{
		$this->newsFeedManagerPage->addFeed('Test Feed');
		$state = $this->newsFeedManagerPage->getState('Test Feed');
		$this->assertEquals('published', $state, 'Initial state should be published');
		$this->newsFeedManagerPage->changeFeedState('Test Feed', 'unpublished');
		$state = $this->newsFeedManagerPage->getState('Test Feed');
		$this->assertEquals('unpublished', $state, 'State should be unpublished');
		$this->newsFeedManagerPage->trashAndDelete('Test Feed');
	}
}
