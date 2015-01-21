<?php
/**
 * @package     Joomla.Test
 * @subpackage  Webdriver
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
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
class ArticleManager0001Test extends JoomlaWebdriverTestCase
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
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$this->articleManagerPage->clickButton('toolbar-new');
		$articleEditPage = $this->getPageObject('ArticleEditPage');

		// Option to print actual element array
		/* @var $articleEditPage ArticleEditPage */
// 	 	$articleEditPage->printFieldArray($articleEditPage->getAllInputFields($articleEditPage->tabs));

		$testElements = $articleEditPage->getAllInputFields($articleEditPage->tabs);
		$actualFields = array();
		foreach ($testElements as $el)
		{
			$el->labelText = (substr($el->labelText, -2) == ' *') ? substr($el->labelText, 0, -2) : $el->labelText;
			$actualFields[] = array('label' => $el->labelText, 'id' => $el->id, 'type' => $el->tag, 'tab' => $el->tab);
		}
		$this->assertEquals($articleEditPage->inputFields, $actualFields);
		$articleEditPage->clickButton('toolbar-cancel');
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
	}


	/**
	 * @test
	 */
	public function constructor_OpenEditScreen_ArticleEditOpened()
	{
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
		$this->articleManagerPage->clickButton('new');
		$articleEditPage = $this->getPageObject('ArticleEditPage');
		$articleEditPage->clickButton('cancel');
		$this->articleManagerPage = $this->getPageObject('ArticleManagerPage');
	}

}
