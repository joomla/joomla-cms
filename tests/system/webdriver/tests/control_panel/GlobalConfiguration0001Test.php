<?php

require_once 'JoomlaWebdriverTestCase.php';

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
		$actualFields = array();
		foreach ($testElements as $el)
		{
			$el->labelText = (substr($el->labelText, -2) == ' *') ? substr($el->labelText, 0, -2) : $el->labelText;
			$actualFields[] = array('label' => $el->labelText, 'id' => $el->id, 'type' => $el->tag, 'tab' => $el->tab);
		}
		$this->assertEquals($actualFields, $gc->inputFields);
	}

}
