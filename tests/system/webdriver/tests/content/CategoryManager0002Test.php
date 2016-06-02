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
 * This class tests the  Category: Add / Edit  Screen.
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.0
 */
class CategoryManager0002Test extends JoomlaWebdriverTestCase
{
	/**
	 * The page class being tested.
	 *
	 * @var     CategoryManagerPage
	 * @since   3.0
	 */
	protected $categoryManagerPage = null;

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
		$this->categoryManagerPage = $cpPage->clickMenu('Category Manager', 'CategoryManagerPage');
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
	 * check availability of filters
	 *
	 * @return void
	 *
	 * @test
	 */
	public function getFilters_GetListOfFilters_ShouldMatchExpected()
	{
		$actualIds = $this->categoryManagerPage->getFilters();
		$expectedIds = array_values($this->categoryManagerPage->filters);
		$this->assertEquals($expectedIds, $actualIds, 'Filter ids should match expected');
	}

	/**
	 * check value of filters
	 *
	 * @return void
	 *
	 * @test
	 */
	public function setFilter_SetFilterValues_ShouldExecuteFilter()
	{
		$salt = rand();
		$categoryName = 'ABC' . $salt;
		$this->categoryManagerPage->addCategory($categoryName);
		$message = $this->categoryManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Category successfully saved') >= 0, 'Category save should return success');
		$test = $this->categoryManagerPage->setFilter('filter_published', 'Unpublished');
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName), 'Category should not show');
		$test = $this->categoryManagerPage->setFilter('filter_published', 'Published');
		$this->categoryManagerPage->trashAndDelete($categoryName);
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName), 'category should not be present');
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
		$categoryName_1 = 'ABC_TEST_1' . $salt;
		$categoryName_2 = 'ABC_TEST_2' . $salt;

		$this->categoryManagerPage->addCategory($categoryName_1);
		$message = $this->categoryManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Category successfully saved') >= 0, 'Category save should return success');
		$state = $this->categoryManagerPage->getState($categoryName_1);
		$this->assertEquals('published', $state, 'Initial state should be published');


		$this->categoryManagerPage->addCategory($categoryName_2);
		$message = $this->categoryManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Category successfully saved') >= 0, 'Category save should return success');
		$state = $this->categoryManagerPage->getState($categoryName_2);
		$this->assertEquals('published', $state, 'Initial state should be published');
		$this->categoryManagerPage->changeCategoryState($categoryName_2, 'unpublished');

		$test = $this->categoryManagerPage->setFilter('filter_published', 'Unpublished');
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName_1), 'Category should not show');
		$this->categoryManagerPage->searchFor($categoryName_2);
		$this->assertEquals(1, $this->categoryManagerPage->getRowNumber($categoryName_2), 'Category should be in row 1');

		$test = $this->categoryManagerPage->setFilter('filter_published', 'Published');
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName_2), 'Category should not show');
		$this->categoryManagerPage->searchFor($categoryName_1);
		$this->assertEquals(1, $this->categoryManagerPage->getRowNumber($categoryName_1), 'Category should be in row 1');

		$this->categoryManagerPage->setFilter('Select Status', 'Select Status');
		$this->categoryManagerPage->trashAndDelete('ABC_TEST');
	}

    /**
     * create an archived category and then verify its creation
     *
     * @return void
     * 
     * @test
     */
    public function setFilter_TestFilters_ShouldFilterTags2()
    {
        $salt = rand();
        $categoryName_1 = 'ABC_TEST_1' . $salt;
        $categoryName_2 = 'ABC_TEST_2' . $salt;

        $this->categoryManagerPage->addCategory($categoryName_1);
        $message = $this->categoryManagerPage->getAlertMessage();
        $this->assertTrue(strpos($message, 'Category successfully saved') >= 0, 'Category save should return success');
        $state = $this->categoryManagerPage->getState($categoryName_1);
        $this->assertEquals('published', $state, 'Initial state should be published');
        $this->categoryManagerPage->addCategory($categoryName_2);
        $message = $this->categoryManagerPage->getAlertMessage();
        $this->assertTrue(strpos($message, 'Category successfully saved') >= 0, 'Category save should return success');
        $state = $this->categoryManagerPage->getState($categoryName_2);
        $this->assertEquals('published', $state, 'Initial state should be published');
        $this->categoryManagerPage->changeCategoryState($categoryName_2, 'Archived');

        $this->categoryManagerPage->setFilter('filter_published', 'Archived');
        $this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName_1), 'Category should not show');
        $this->assertGreaterThanOrEqual(1, $this->categoryManagerPage->getRowNumber($categoryName_2), 'Test Category should be present');;

        $this->categoryManagerPage->setFilter('filter_published', 'Published');
        $this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName_2), 'Category should not show');
        $this->categoryManagerPage->searchFor($categoryName_1);
        $this->assertGreaterThanOrEqual(1, $this->categoryManagerPage->getRowNumber($categoryName_1), 'Test Category should be present');
        $this->categoryManagerPage->setFilter('Select Status', 'Select Status');
        $this->categoryManagerPage->trashAndDelete($categoryName_1);
        $this->categoryManagerPage->trashAndDelete($categoryName_2);
    }
}
