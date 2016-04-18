<?php

require_once 'JoomlaWebdriverTestCase.php';

use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;

class WikihelpTest extends JoomlaWebdriverTestCase
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
		$this->gcPage = $cpPage->clickMenuByUrl('com_config', 'GlobalConfigurationPage');
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
	public function toWikiText_ScreenLoaded_HelpTextShouldPrint()
	{
		echo $this->gcPage->toWikiHelp();
// 		echo implode("", $this->gcPage->toWikiHelpFilters('1'));
		$this->driver->setScreenShotsDirectory($this->cfg->baseURI . "/tests/system/tmp");
		foreach ($this->gcPage->tabs as $tab)
		{
			$this->gcPage->selectTab($tab);
			$this->helpScreenshot('test-' . $tab . '.png', $this->cfg->baseURI . "/tests/system/tmp");
		}

	}
}
