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
 * this class tests if site name can be changed from from front end
 *
 * @package     Joomla.Tests
 * @subpackage  Page
 *
 * @copyright   Copyright (c) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       Joomla 3.3
 */
class ConfigFrontEnd0001Test extends JoomlaWebdriverTestCase
{
	/**
	 * Login and start test.
	 *
	 * @return void
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
	 * @return void
	 *
	 * @since   3.2
	 */
	public function tearDown()
	{
		$this->doSiteLogout();
		parent::tearDown();
	}

	/**
	 * it checks if the name of the site can be changed from front end
	 *
	 * @return void
	 *
	 * @test
	 */
	public function testChangeSiteName()
	{
		$newSiteName = 'Joomla Testing';
		$homePageUrl = 'index.php';
		$cfg = new SeleniumConfig;
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
