<?php

require_once 'JoomlaWebdriverTestCase.php';

use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;

class GlobalConfiguration0001Test extends JoomlaWebdriverTestCase
{
	/**
	 *
	 * @var GlobalConfigurationPage
	 */
	protected $gcPage = null; // Global configuration page

	public function setUp()
	{
		parent::setUp();
		$cpPage = $this->doAdminLogin();
		$this->gcPage = $cpPage->clickMenu('Global Configuration', 'GlobalConfigurationPage');
	}

	public function tearDown()
	{
		$this->gcPage->saveAndClose('ControlPanelPage');
		$this->doAdminLogout();
		parent::tearDown();
	}

	/**
	 * @test
	 */
	public function getTabIds_ScreenLoaded_TabIdsShouldEqualExpected()
	{
		$textArray = $this->gcPage->getTabIds();
		$this->assertEquals($this->gcPage->tabs, $textArray, 'Tab labels should match expected values.');
	}

	/**
	 * @test
	 * Gets the actual input fields from the Control Panel page and checks them against the $inputFields property.
	 */
	public function getAllInputFields_ScreenLoaded_InputFieldsShouldMatchExpected()
	{
		$gc = $this->gcPage;
		$testElements = $gc->getAllInputFields(array('page-site', 'page-system', 'page-server', 'page-permissions'));
		$actualFields = $this->getActualFieldsFromElements($testElements);
		$this->assertEquals($actualFields, $gc->inputFields);
	}

}