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
 * This class tests the  Global Configuration page.
 *
 * @package     Joomla.Tests
 * @subpackage  Test
 *
 * @copyright   Copyright (c) 2005 - 2016 Open Source Matters, Inc.   All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       Joomla 3.3
 */
class GlobalConfiguration0001Test extends JoomlaWebdriverTestCase
{
	/**
	 *
	 * @var GlobalConfigurationPage
	 */
	protected $gcPage = null; // Global configuration page

	/**
	 * Login to back end
	 *
	 * @return void
	 *
	 * @since   3.0
	 */
	public function setUp()
	{
		parent::setUp();
		$cpPage = $this->doAdminLogin();
		$this->gcPage = $cpPage->clickMenu('Global Configuration', 'GlobalConfigurationPage');
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
		$this->gcPage->saveAndClose('ControlPanelPage');
		$this->doAdminLogout();
		parent::tearDown();
	}

	/**
	 * checks the tab Ids
	 *
	 * @return void
	 *
	 * @test
	 */
	public function getTabIds_ScreenLoaded_TabIdsShouldEqualExpected()
	{
		$textArray = $this->gcPage->getTabIds();
		$this->assertEquals($this->gcPage->tabs, $textArray, 'Tab labels should match expected values.');
	}

	/**
	 * Gets the actual input fields from the Control Panel page and checks them against the $inputFields property.
	 *
	 * @return void
	 *
	 * @test
	 */
	public function getAllInputFields_ScreenLoaded_InputFieldsShouldMatchExpected()
	{
		$gc = $this->gcPage;

// 	 	$gc->printFieldArray($gc->getAllInputFields($gc->tabs));

		$testElements = $gc->getAllInputFields(array('page-site', 'page-system', 'page-server', 'page-permissions'));
		$actualFields = $this->getActualFieldsFromElements($testElements);
		$this->assertEquals($actualFields, $gc->inputFields);
	}
}
