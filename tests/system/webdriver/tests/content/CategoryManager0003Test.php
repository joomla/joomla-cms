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
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');
		$this->categoryManagerPage->clickButton('toolbar-new');
		$categoryEditPage = $this->getPageObject('CategoryEditPage');
		$testElements = $categoryEditPage->getAllInputFields(array('general', 'publishing', 'options', 'metadata','rules'));
		$actualFields = array();
		foreach ($testElements as $el)
		{
			$el->labelText = (substr($el->labelText, -2) == ' *') ? substr($el->labelText, 0, -2) : $el->labelText;
			$actualFields[] = array('label' => $el->labelText, 'id' => $el->id, 'type' => $el->tag, 'tab' => $el->tab);
		}
		$this->assertEquals($categoryEditPage->inputFields, $actualFields);
		$categoryEditPage->clickButton('toolbar-cancel');
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');
	}


	/**
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
	 * @test
	 */
	public function addCategory_WithGivenFields_CategoryAdded()
	{
		$salt = rand();
		$categoryName = 'ABC' . $salt;
		$alias = 'ABC_Alias'. $salt;
		$expected_alias='abc-alias'.$salt;
		$desc = 'ABC_Desc';
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName), 'Test Category should not be present');
		$this->categoryManagerPage->addCategory($categoryName,$alias,$desc);
		$message = $this->categoryManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Category successfully saved') >= 0, 'Category save should return success');
		$values = $this->categoryManagerPage->getFieldValues('CategoryEditPage', $categoryName, array('Title', 'Alias'));
		$this->assertEquals(array($categoryName,$expected_alias), $values, 'Actual name, alias should match expected');
		$this->categoryManagerPage->trashAndDelete($categoryName);
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName), 'Test category should not be present');
	}

	/**
	 * @test
	*/
	public function editCategory_ChangeFields_FieldsChanged()
	{
		$salt = rand();
		$categoryName = 'ABC' . $salt;
		$alias = 'ABC_Alias'. $salt;
		$expected_alias='abc-alias'.$salt;
		$desc = 'ABC_Description';
		$expected_desc='<p>ABC_Description</p>';
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');
		$this->assertFalse($this->categoryManagerPage->getRowNumber($categoryName), 'Test Category should not be present');
		$this->categoryManagerPage->addCategory($categoryName);
		$this->categoryManagerPage->editCategory($categoryName, array('Alias' => $alias, 'Description' => $desc));
		$values = $this->categoryManagerPage->getFieldValues('CategoryEditPage', $categoryName, array('Alias', 'Description'));
		$this->assertEquals(array($expected_alias, $expected_desc), $values, 'Actual values should match expected');
		$this->categoryManagerPage->trashAndDelete($categoryName);
	}

	/**
	 * @test
	 */
	public function changeCategoryState_ChangeEnabledUsingToolbar_EnabledChanged()
	{
		$salt = rand();
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');
		$this->categoryManagerPage->addCategory('ABC_Test', $salt);
		$state = $this->categoryManagerPage->getState('ABC_Test');
		$this->assertEquals('published', $state, 'Initial state should be published');
		$this->categoryManagerPage->changeCategoryState('ABC_Test', 'unpublished');
		$state = $this->categoryManagerPage->getState('ABC_Test');
		$this->assertEquals('unpublished', $state, 'State should be unpublished');
		$this->categoryManagerPage->trashAndDelete('ABC_Test');
	}


}
