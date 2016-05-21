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
class CategoryManager0003Test extends JoomlaWebdriverTestCase
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
	 * check all the input fields
	 *
	 * @return void
	 *
	 * @test
	 */
	public function getAllInputFields_ScreenDisplayed_EqualExpected()
	{
		$this->categoryManagerPage->clickButton('toolbar-new');
		$categoryEditPage = $this->getPageObject('CategoryEditPage');

		/* Option to print actual element array*/
		/* @var $categoryEditPage CategoryEditPage */
// 	 	$categoryEditPage->printFieldArray($categoryEditPage->getAllInputFields($categoryEditPage->tabs));

		$testElements = $categoryEditPage->getAllInputFields($categoryEditPage->tabs);
		$actualFields = $this->getActualFieldsFromElements($testElements);
		$this->assertEquals($categoryEditPage->inputFields, $actualFields);
		$categoryEditPage->clickButton('toolbar-cancel');
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');
	}

	/**
	 * open edit screen
	 *
	 * @return void
	 *
	 * @test
	 */
	public function constructor_OpenEditScreen_CategoryEditOpened()
	{
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');
		$this->categoryManagerPage->clickButton('new');
		$categoryEditPage = $this->getPageObject('CategoryEditPage');
		$categoryEditPage->clickButton('cancel');
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');
	}

	/**
	 * add category with default input fields
	 *
	 * @return void
	 *
	 * @test
	 */
	public function addCategory_WithFieldDefaults_CategoryAdded()
	{
		$salt = rand();
		$categoryName = 'ABC' . $salt;
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName), 'Test Category should not be present');
		$this->categoryManagerPage->addCategory($categoryName);
		$message = $this->categoryManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Category successfully saved') >= 0, 'Category save should return success');
		$this->categoryManagerPage->trashAndDelete($categoryName);
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName), 'Test Category should not be present');
	}

	/**
	 * add category with given fields
	 *
	 * @return void
	 *
	 * @test
	 */
	public function addCategory_WithGivenFields_CategoryAdded()
	{
		$salt = rand();
		$categoryName = 'ABC' . $salt;
		$expected_alias = 'abc-alias' . $salt;
		$desc = $categoryName . ' Description';
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName), 'Test Category should not be present');
		$this->categoryManagerPage->addCategory($categoryName, $desc, array('Alias' => $expected_alias));
		$message = $this->categoryManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Category successfully saved') >= 0, 'Category save should return success');
		$values = $this->categoryManagerPage->getFieldValues('CategoryEditPage', $categoryName, array('Title', 'Alias'));
		$this->assertEquals(array($categoryName, $expected_alias), $values, 'Actual name, alias should match expected');
		$this->categoryManagerPage->trashAndDelete($categoryName);
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName), 'Test category should not be present');
	}

	/**
	 * edit the value of the fields
	 *
	 * @return void
	 *
	 * @test
	 */
	public function editCategory_ChangeFields_FieldsChanged()
	{
		$salt = rand();
		$categoryName = 'ABC' . $salt;
		$alias = 'abc-alias' . $salt;
		$desc = 'ABC_Description';
		$expected_desc = '<p>ABC_Description</p>';
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName), 'Test Category should not be present');
		$this->categoryManagerPage->addCategory($categoryName);
		$this->categoryManagerPage->editCategory($categoryName, array('Alias' => $alias, 'Description' => $desc));
		$values = $this->categoryManagerPage->getFieldValues('CategoryEditPage', $categoryName, array('Alias', 'Description'));
		$this->assertEquals(array($alias, $expected_desc), $values, 'Actual values should match expected');
		$this->categoryManagerPage->trashAndDelete($categoryName);
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName), 'Test category should not be present');
	}

	/**
	 * change the tate of the category
	 *
	 * @return void
	 *
	 * @test
	 */
	public function changeCategoryState_ChangeEnabledUsingToolbar_EnabledChanged()
	{
		$salt = rand();
		$categoryName = 'Test Category ' . $salt;
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');
		$this->categoryManagerPage->addCategory($categoryName);
		$state = $this->categoryManagerPage->getState($categoryName);
		$this->assertEquals('published', $state, 'Initial state should be published');
		$this->categoryManagerPage->changeCategoryState($categoryName, 'unpublished');
		$state = $this->categoryManagerPage->getState($categoryName);
		$this->assertEquals('unpublished', $state, 'State should be unpublished');
		$this->categoryManagerPage->trashAndDelete($categoryName);
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName), 'Test category should not be present');
	}
}
