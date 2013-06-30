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
		$this->deleteConfigurationFile();
		$url = $this->cfg->host . $this->cfg->path . 'installation/';
		$installPage = $this->getPageObject('InstallationPage', true, $url);
		$installPage->install($this->cfg);
		$cpPage = $this->doAdminLogin();
		$gcPage = $cpPage->clickMenu('Global Configuration', 'GlobalConfigurationPage');
		$gcPage->setFieldValue('Cache', 'OFF');
		$gcPage->setFieldValue('Error Reporting', 'Development');
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