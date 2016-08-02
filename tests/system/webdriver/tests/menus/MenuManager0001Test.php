<?php

require_once 'JoomlaWebdriverTestCase.php';

use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;

/**
 * This class tests the  Manager: Add / Edit  Screen
 *
 * @package     Joomla.Tests
 * @subpackage  Page
 *
 * @copyright   Copyright (c) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       Joomla 3.3
 */
class MenuManager0001Test extends JoomlaWebdriverTestCase
{
	/**
	 *
	 * @var MenuManagerPage
	 */
	protected $menuManagerPage = null;

	/**
	 * Login to back end and navigate to menu item manager.
	 *
	 * @return void
	 *
	 * @since   3.0
	 */
	public function setUp()
	{
		parent::setUp();
		$cpPage = $this->doAdminLogin();
		$this->menuManagerPage = $cpPage->clickMenu('Menu Manager', 'MenuManagerPage');
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
	 * check menu edit page
	 *
	 * @return void
	 *
	 * @test
	 */
	public function constructor_OpenEditScreen_MenuEditOpened()
	{
		$this->menuManagerPage->clickButton('toolbar-new');

		/* @var $menuEditPage MenuEditPage */
		$menuEditPage = $this->getPageObject('MenuEditPage');
		/* $menuEditPage->printFieldArray($menuEditPage->getAllInputFields());*/
		$menuEditPage->clickButton('toolbar-cancel');
		$this->menuManagerPage = $this->getPageObject('menuManagerPage');
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
		$this->menuManagerPage->clickButton('toolbar-new');
		$MenuEditPage = $this->getPageObject('MenuEditPage');

		$testElements = $MenuEditPage->getAllInputFields();
		$actualFields = $this->getActualFieldsFromElements($testElements);
		$this->assertEquals($MenuEditPage->inputFields, $actualFields);
		$MenuEditPage->clickButton('toolbar-cancel');
		$this->menuManagerPage = $this->getPageObject('menuManagerPage');
	}

	/**
	 * add menu with default values
	 *
	 * @return void
	 *
	 * @test
	 */
	public function add_WithFieldDefaults_Added()
	{
		$this->assertFalse($this->menuManagerPage->getRowNumber('Test '), 'Test menu should not be present');
		$this->menuManagerPage->addMenu();
		$message = $this->menuManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Menu successfully saved') >= 0, 'Menu save should return success');
		$this->assertGreaterThanOrEqual(1, $this->menuManagerPage->getRowNumber('Test Menu'), 'Test menu should be present');
		$this->menuManagerPage->deleteMenu('Test Menu');
		$this->assertFalse($this->menuManagerPage->getRowNumber('Test Menu'), 'Test menu should not be present');
	}

	/**
	 * add menu with the given fields
	 *
	 * @return void
	 *
	 * @test
	 */
	public function addMenu_WithGivenFields_MenuAdded()
	{
		$salt = rand();
		$menuName = 'Menu' . $salt;
		$type = 'menu' . $salt;
		$description = 'test menu ' . $salt;
		$this->assertFalse($this->menuManagerPage->getRowNumber($menuName), 'Test menu should not be present');
		$this->menuManagerPage->addMenu($menuName, $type, $description);
		$message = $this->menuManagerPage->getAlertMessage();
		$this->assertContains('Menu successfully saved', $message, 'Menu save should return success', true);
		$this->assertTrue($this->menuManagerPage->getRowNumber($menuName) > 0, 'Test menu should be on the page');
		$actualValues = $this->menuManagerPage->getFieldValues('MenuEditPage', $menuName, array('Title', 'Menu type', 'Description'));
		$expectedValues = array ($menuName, $type, $description);
		$this->assertEquals($expectedValues, $actualValues, 'Actual values should match entered values');
		$this->menuManagerPage->deleteMenu($menuName);
		$this->assertFalse($this->menuManagerPage->getRowNumber($menuName), 'Test menu should not be present');
	}

	/**
	 * edit values of the input fields
	 *
	 * @return void
	 *
	 * @test
	 */
	public function editMenu_ChangeFields_FieldsChanged()
	{
		$salt = rand();
		$menuName = 'Menu' . $salt;
		$type = 'menu' . $salt;
		$description = 'test menu ' . $salt;
		$this->assertFalse($this->menuManagerPage->getRowNumber($menuName), 'Test menu should not be present');
		$this->menuManagerPage->addMenu($menuName, $type, $description);

		$newMenuName = 'New Menu' . $salt;
		$newType = 'newmenu' . $salt;
		$newDescription = 'new test menu' . $salt;
		$this->menuManagerPage->editMenu($menuName, array('Title' => $newMenuName, 'Menu type' => $newType, 'Description' => $newDescription));

		$actualValues = $this->menuManagerPage->getFieldValues('MenuEditPage', $newMenuName, array('Title', 'Menu type', 'Description'));
		$expectedValues = array ($newMenuName, $newType, $newDescription);
		$this->assertEquals($expectedValues, $actualValues, 'Actual values should match entered values');
		$this->menuManagerPage->deleteMenu($newMenuName);
		$this->assertFalse($this->menuManagerPage->getRowNumber($newMenuName), 'Test menu should not be present');
	}
}
