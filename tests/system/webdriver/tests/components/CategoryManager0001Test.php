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
 * This class tests the  Contact: Add / Edit  Screen.
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.2
 */
class CategoryManager0001Test extends JoomlaWebdriverTestCase
{
	/**
	 * The page class being tested.
	 *
	 * @var     contactManagerPage
	 * @since   3.2
	 */
	protected $categoryManagerPage = null;

	/**
	 * Login to back end and navigate to menu Contacts.
	 *
	 * @return void
	 *
	 * @since   3.2
	 */
	public function setUp()
	{
		parent::setUp();
		$cpPage = $this->doAdminLogin();
		$this->categoryManagerPage = $cpPage->clickMenuByUrl('com_categories&extension=com_contact', 'CategoryManagerPage');
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
	 * check tag edit page
	 *
	 * @return void
	 *
	 * @test
	 */
	public function constructor_OpenEditScreen_CategoryEditOpened()
	{
		$this->categoryManagerPage->clickButton('new');
		$categoryEditPage = $this->getPageObject('CategoryEditPage');
		$categoryEditPage->clickButton('cancel');
		$this->categoryManagerPage = $this->getPageObject('CategoryManagerPage');
	}
}
