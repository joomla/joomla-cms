<?php
/**
 * @package     Joomla.Test
 * @subpackage  Webdriver
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once 'JoomlaWebdriverTestCase.php';

use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;

/**
 * @package     Joomla.Tests
 * @subpackage  Page
 *
 * @copyright   Copyright (c) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
class ConfigFrontEnd0001Test extends JoomlaWebdriverTestCase
{
	/**
	 * Login and start test.
	 *
	 * @since   3.2
	 */
	public function setUp()
	{
		parent::setUp();
		$this->doSiteLogin();
	}

	/**
	 * Logout and close test.
	 *
	 * @since   3.2
	 */
	public function tearDown()
	{
		$this->doSiteLogout();
		parent::tearDown();
	}

	/**
	 * @test
	 */
	public function testChangeSiteName()
	{
		$newSiteName = 'Joomla Testing';
		$homePageUrl = 'index.php';
		$cfg = new SeleniumConfig();
		$url = $cfg->host . $cfg->path . $homePageUrl . '?option=com_config&view=config&controller=config.display.config';
		$this->siteHomePage = $this->getPageObject('SiteConfigurationConfigPage', true, $url);
		$previousSiteName = $this->siteHomePage->getSiteName();
		$this->siteHomePage->changeSiteName($newSiteName);
		$this->assertEquals($newSiteName, $this->siteHomePage->getSiteName(), 'Site name has changed');
		if (!empty($previousSiteName))
		{
			$this->siteHomePage->changeSiteName($previousSiteName);
		}
		$this->assertEquals($previousSiteName, $this->siteHomePage->getSiteName(), 'Site name has changed');
	}
}
