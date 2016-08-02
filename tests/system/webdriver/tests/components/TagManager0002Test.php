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
class TagManager0002Test extends JoomlaWebdriverTestCase
{

	/**
	 * The page class being tested.
	 *
	 * @var     TagManagerPage
	 * @since   3.0
	 */
	protected $tagManagerPage = null;

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
		$this->tagManagerPage = $cpPage->clickMenu('Tags', 'TagManagerPage');
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
		$actualIds = $this->tagManagerPage->getFilters();
		$expectedIds = array_values($this->tagManagerPage->filters);
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
		$tagName = 'Test Filter' . $salt;
		$this->tagManagerPage->addTag($tagName);
		$message = $this->tagManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Tag successfully saved') >= 0, 'Tag save should return success');
		$test = $this->tagManagerPage->setFilter('filter_published', 'Unpublished');
		$this->assertFalse($this->tagManagerPage->getRowNumber($tagName), 'Tag should not show');
		$test = $this->tagManagerPage->setFilter('filter_published', 'Published');
		$this->assertEquals(1, $this->tagManagerPage->getRowNumber($tagName), 'Tag should be in row 1');
		$this->tagManagerPage->trashAndDelete($tagName);
		$this->assertFalse($this->tagManagerPage->getRowNumber($tagName), 'Tag should not be present');
	}

	/**
	 * creating two tags one published and one unpublished and the verifying its existence
	 *
	 * @return void
	 *
	 * @test
	 */
	public function setFilter_TestFilters_ShouldFilterTags()
	{
		$tagName_1 = 'Test Filter 1';
		$tagName_2 = 'Test Filter 2';

		$this->tagManagerPage->addTag($tagName_1);
		$message = $this->tagManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Tag successfully saved') >= 0, 'Tag save should return success');
		$state = $this->tagManagerPage->getState($tagName_1);
		$this->assertEquals('published', $state, 'Initial state should be published');
		$this->tagManagerPage->addTag($tagName_2);
		$message = $this->tagManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Tag successfully saved') >= 0, 'Tag save should return success');
		$state = $this->tagManagerPage->getState($tagName_2);
		$this->assertEquals('published', $state, 'Initial state should be published');
		$this->tagManagerPage->changeTagState($tagName_2, 'unpublished');

		$test = $this->tagManagerPage->setFilter('filter_published', 'Unpublished');
		$this->assertFalse($this->tagManagerPage->getRowNumber($tagName_1), 'Tag should not show');
		$this->assertEquals(1, $this->tagManagerPage->getRowNumber($tagName_2), 'Tag should be in row 1');

		$test = $this->tagManagerPage->setFilter('filter_published', 'Published');
		$this->assertFalse($this->tagManagerPage->getRowNumber($tagName_2), 'Tag should not show');
		$this->assertEquals(1, $this->tagManagerPage->getRowNumber($tagName_1), 'Tag should be in row 1');

		$this->tagManagerPage->setFilter('Select Status', 'Select Status');
		$this->tagManagerPage->trashAndDelete($tagName_1);
		$this->tagManagerPage->trashAndDelete($tagName_2);
	}

	/**
	 * creating two tags one published and one archived and the verifying its existence
	 *
	 * @return void
	 *
	 * @test
	 */
	public function setFilter_TestFilters_ShouldFilterTags2()
	{
		$tagName_1 = 'Test Filter 1';
		$tagName_2 = 'Test Filter 2';

		$this->tagManagerPage->addTag($tagName_1);
		$message = $this->tagManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Tag successfully saved') >= 0, 'Tag save should return success');
		$state = $this->tagManagerPage->getState($tagName_1);
		$this->assertEquals('published', $state, 'Initial state should be published');
		$this->tagManagerPage->addTag($tagName_2);
		$message = $this->tagManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Tag successfully saved') >= 0, 'Tag save should return success');
		$state = $this->tagManagerPage->getState($tagName_2);
		$this->assertEquals('published', $state, 'Initial state should be published');
		$this->tagManagerPage->changeTagState($tagName_2, 'Archived');

		$this->tagManagerPage->setFilter('filter_published', 'Archived');
		$this->assertFalse($this->tagManagerPage->getRowNumber($tagName_1), 'Tag should not show');
		$this->assertGreaterThanOrEqual(1, $this->tagManagerPage->getRowNumber($tagName_2), 'Test test tag should be present');

		$this->tagManagerPage->setFilter('filter_published', 'Published');
		$this->assertFalse($this->tagManagerPage->getRowNumber($tagName_2), 'Tag should not show');
		$this->assertGreaterThanOrEqual(1, $this->tagManagerPage->getRowNumber($tagName_1), 'Test test tag should be present');
		$this->tagManagerPage->setFilter('Select Status', 'Select Status');
		$this->tagManagerPage->trashAndDelete($tagName_1);
		$this->tagManagerPage->trashAndDelete($tagName_2);
	}
}
