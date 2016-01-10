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
 * This class tests the  Template Manager: Add / Edit  Screen.
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.0
 */
class TemplateManager0001Test extends JoomlaWebdriverTestCase
{
	/**
	 * The page class being tested.
	 *
	 * @var     templateManagerPage
	 * @since   3.0
	 */
	protected $templateManagerPage = null;

	/**
	 * Login to back end and navigate to menu .
	 *
	 * @return void
	 *
	 * @since   3.0
	 */
	public function setUp()
	{
		parent::setUp();
		$cpPage = $this->doAdminLogin();
		$this->pluginManagerPage = $cpPage->clickMenu('Template Manager', 'TemplateManagerPage');
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
	 * A test Template which we are going to select to open the edit pag
	 *
	 * @return void
	 *
	 * @test
	 */
	public function constructor_OpenEditScreen_TemplateEditOpened()
	{
		$test_template = 'Hathor - Default';
		$this->templateManagerPage = $this->getPageObject('TemplateManagerPage');
		$this->templateManagerPage->clickItem($test_template);
		$templateEditPage = $this->getPageObject('TemplateEditPage');
		$templateEditPage->clickButton('cancel');
		$this->templateManagerPage = $this->getPageObject('TemplateManagerPage');
	}

	/**
	 * open and check the edit page
	 *
	 * @return void
	 *
	 * @test
	 */
	public function getTabIds_ScreenDisplayed_EqualExpected()
	{
		$test_template = 'Hathor - Default';
		$this->templateManagerPage = $this->getPageObject('TemplateManagerPage');
		$this->templateManagerPage->clickItem($test_template);
		$templateEditPage = $this->getPageObject('TemplateEditPage');
		$textArray = $templateEditPage->getTabIds();
		$this->assertEquals($templateEditPage->tabs, $textArray, 'Tab labels should match expected values.');
		$templateEditPage->clickButton('toolbar-cancel');
		$this->templateManagerPage = $this->getPageObject('TemplateManagerPage');
	}

	/**
	 * select to create a duplicate template
	 *
	 * @return void
	 *
	 * @test
	 */
	public function copyStyle_MakeDuplicate()
	{
		$template_name = 'Hathor - Default (2)';
		$test_template = 'Hathor - Default';
		$this->templateManagerPage = $this->getPageObject('TemplateManagerPage');
		$this->templateManagerPage->copyStyle($test_template);
		$message = $this->templateManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Style successfully duplicated') >= 0, 'Style Copy should return success');
		$this->templateManagerPage->deleteStyle($template_name);
		$message = $this->templateManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Template style successfully deleted') >= 0, 'Style Delete should return success');
	}

	/**
	 * edit duplicate template
	 *
	 * @return void
	 *
	 * @test
	 */
	public function editStyle_EditDuplicate()
	{
		$template_name = 'Hathor - Default (2)';
		$template_new_name = 'Testing 1234';
		$test_template = 'Hathor - Default';
		$this->templateManagerPage = $this->getPageObject('TemplateManagerPage');
		$this->templateManagerPage->copyStyle($test_template);
		$message = $this->templateManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Style successfully duplicated') >= 0, 'Style Copy should return success');
		$this->templateManagerPage->editStyle($template_name, array('Style Name' => $template_new_name));
		$message = $this->templateManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Template style successfully saved') >= 0, 'Style Delete should return success');
		$this->templateManagerPage->deleteStyle($template_new_name);
		$message = $this->templateManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Template style successfully deleted') >= 0, 'Style Delete should return success');
	}
}
