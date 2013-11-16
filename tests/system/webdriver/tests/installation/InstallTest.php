<?php

require_once 'JoomlaWebdriverTestCase.php';

use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;

class InstallTest extends JoomlaWebdriverTestCase
{

	/**
	 * @test
	 */
	public function install_NormalInstallFromConfig_ShouldInstall()
	{
		if ($this->cfg->doInstall == 'true') {
			$this->deleteConfigurationFile();
			$url = $this->cfg->host . $this->cfg->path . 'installation/';
			$installPage = $this->getPageObject('InstallationPage', true, $url);
			$installPage->install($this->cfg);
		}

		$cpPage = $this->doAdminLogin();
		if (isset($this->cfg->clearPostInstall) && $this->cfg->clearPostInstall)
		{
			$cpPage->clearInstallMessages();
		}

		$gcPage = $cpPage->clickMenu('Global Configuration', 'GlobalConfigurationPage');
		if (isset($this->cfg->cache))
		{
			$gcPage->setFieldValue('Cache', $this->cfg->cache);
		}
		if (isset($this->cfg->errorReporting))
		{
			$gcPage->setFieldValue('Error Reporting', $this->cfg->errorReporting);
		}
		if (isset($this->cfg->listLimit))
		{
			$gcPage->setFieldValue('Default List Limit', $this->cfg->listLimit);
		}
		$gcPage->saveAndClose('ControlPanelPage');
		$this->doAdminLogout();
	}

	protected function deleteConfigurationFile()
	{
		$configFile = $this->cfg->folder . $this->cfg->path . "configuration.php";
		if (file_exists($configFile)) {
			chmod($configFile, 0777);
			unlink($configFile);
		}
	}
}
