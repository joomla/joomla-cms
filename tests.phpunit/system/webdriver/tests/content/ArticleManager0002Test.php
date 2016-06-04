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
class ArticleManager0002Test extends JoomlaWebdriverTestCase
{
	/**
	 * The page class being tested.
	 *
	 * @var     ArticleManagerPage
	 * @since   3.0
	 */
	protected $articleManagerPage = null;

	/**
	 * Login to back end and navigate to menu Tags.
	 *
	 * @since   3.0
	 *
	 * @return void
	 */
	public function setUp()
	{
		parent::setUp();
		$cpPage = $this->doAdminLogin();
		$this->articleManagerPage = $cpPage->clickMenu('Article Manager', 'ArticleManagerPage');
	}

	/**
	 * Logout and close test.
	 *
	 * @since   3.0
	 *
	 * @return void
	 */
	public function tearDown()
	{
		$this->doAdminLogout();
		parent::tearDown();
	}

	/**
	 * check the availability of filters
	 *
	 * @return void
	 *
	 * @test
	 */
	public function getFilters_GetListOfFilters_ShouldMatchExpected()
	{
		$actualIds = $this->articleManagerPage->getFilters();
		$expectedIds = array_values($this->articleManagerPage->filters);
		$this->assertEquals($expectedIds, $actualIds, 'Filter ids should match expected');
	}

	/**
	 * set values to the filters
	 *
	 * @return void
	 *
	 * @test
	 */
	public function setFilter_SetFilterValues_ShouldExecuteFilter()
	{
		$salt = rand();
		$articleName = 'ABC' . $salt;
		$this->articleManagerPage->addArticle($articleName);
		$message = $this->articleManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Article successfully saved') >= 0, 'Tag save should return success');
		$test = $this->articleManagerPage->setFilter('filter_published', 'Unpublished');
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName), 'Article should not show');
		$test = $this->articleManagerPage->setFilter('filter_published', 'Published');
		$this->assertEquals(1, $this->articleManagerPage->getRowNumber($articleName), 'Article should be in row 1');
		$this->articleManagerPage->trashAndDelete($articleName);
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName), 'Article should not be present');
	}

	/**
	 * creating two articles one published and one unpublished and the verifying its existence
	 *
	 * @return void
	 *
	 * @test
	 */
	public function setFilter_TestFilters_ShouldFilterTags()
	{
		$salt = rand();
		$articleName_1 = 'ABC_TEST_1' . $salt;
		$articleName_2 = 'ABC_TEST_2' . $salt;

		$this->articleManagerPage->addArticle($articleName_1);
		$message = $this->articleManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Article successfully saved') >= 0, 'Article save should return success');
		$state = $this->articleManagerPage->getState($articleName_1);
		$this->assertEquals('published', $state, 'Initial state should be published');

		$this->articleManagerPage->addArticle($articleName_2);
		$message = $this->articleManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Article successfully saved') >= 0, 'Article save should return success');
		$state = $this->articleManagerPage->getState($articleName_2);
		$this->assertEquals('published', $state, 'Initial state should be published');
		$this->articleManagerPage->changeArticleState($articleName_2, 'unpublished');

		$test = $this->articleManagerPage->setFilter('filter_published', 'Unpublished');
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName_1), 'Article should not show');
		$this->assertEquals(1, $this->articleManagerPage->getRowNumber($articleName_2), 'Article should be in row 1');

		$test = $this->articleManagerPage->setFilter('filter_published', 'Published');
		$this->assertFalse($this->articleManagerPage->getRowNumber($articleName_2), 'Article should not show');
		$this->assertEquals(1, $this->articleManagerPage->getRowNumber($articleName_1), 'Article should be in row 1');

		$this->articleManagerPage->setFilter('Select Status', 'Select Status');
		$this->articleManagerPage->trashAndDelete($articleName_1);
		$this->articleManagerPage->trashAndDelete($articleName_2);
	}

    /**
     * create an archived article and then verify its creation
     *
     * @return void
     *
     * @test
     */
    public function setFilter_TestFilters_ShouldFilterTags2()
	{
		$salt = rand();
	    $articleName_1 = 'ABC_TEST_1' . $salt;
	    $articleName_2 = 'ABC_TEST_2' . $salt;

	    $this->articleManagerPage->addArticle($articleName_1);
	    $message = $this->articleManagerPage->getAlertMessage();
	    $this->assertTrue(strpos($message, 'Article successfully saved') >= 0, 'Article save should return success');
	    $state = $this->articleManagerPage->getState($articleName_1);
	    $this->assertEquals('published', $state, 'Initial state should be published');
	    $this->articleManagerPage->addArticle($articleName_2);
	    $message = $this->articleManagerPage->getAlertMessage();
	    $this->assertTrue(strpos($message, 'Article successfully saved') >= 0, 'Article save should return success');
	    $state = $this->articleManagerPage->getState($articleName_2);
	    $this->assertEquals('published', $state, 'Initial state should be published');
	    $this->articleManagerPage->changeArticleState($articleName_2, 'Archived');
	    $this->articleManagerPage->setFilter('filter_published', 'Archived');
	    $this->assertFalse($this->articleManagerPage->getRowNumber($articleName_1), 'Article should not show');
	    $this->assertGreaterThanOrEqual(1, $this->articleManagerPage->getRowNumber($articleName_2), 'Test Article should be present');
	    $this->articleManagerPage->setFilter('filter_published', 'Published');
	    $this->assertFalse($this->articleManagerPage->getRowNumber($articleName_2), 'Article should not show');
	    $this->assertGreaterThanOrEqual(1, $this->articleManagerPage->getRowNumber($articleName_1), 'Test Article should be present');
	    $this->articleManagerPage->setFilter('Select Status', 'Select Status');
	    $this->articleManagerPage->trashAndDelete($articleName_1);
	    $this->articleManagerPage->trashAndDelete($articleName_2);
    }
}
