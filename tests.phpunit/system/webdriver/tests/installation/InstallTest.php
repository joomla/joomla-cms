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
 * This class tests the  installation of joomla.
 *
 * @package     Joomla.Tests
 * @subpackage  Test
 *
 * @copyright   Copyright (c) 2005 - 2016 Open Source Matters, Inc.   All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       Joomla 3.3
 */
class InstallTest extends JoomlaWebdriverTestCase
{

	/**
	 * install normal installation configuration
	 *
	 * @return void
	 *
	 * @test
	 */
	public function install_NormalInstallFromConfig_ShouldInstall()
	{
		if ($this->cfg->doInstall == 'true')
		{
			$this->deleteConfigurationFile();
			$url = $this->cfg->host . $this->cfg->path . 'installation/';
			$installPage = $this->getPageObject('InstallationPage', true, $url);
			$installPage->install($this->cfg);
		}

		$cpPage = $this->doAdminLogin();
		$cpPage->clearInstallMessages();
		$gcPage = $cpPage->clickMenu('Global Configuration', 'GlobalConfigurationPage');
		$gcPage->setFieldValue('Cache', 'OFF');
		$gcPage->setFieldValue('Error Reporting', 'Development');
		$gcPage->saveAndClose('ControlPanelPage');
		$this->doAdminLogout();
	}

	/**
	 * it deletes the configuration file
	 *
	 * @return void
	 */
	protected function deleteConfigurationFile()
	{
		$configFile = $this->cfg->folder . $this->cfg->path . "configuration.php";

		if (file_exists($configFile))
		{
			chmod($configFile, 0777);
			unlink($configFile);
		}
	}
}
