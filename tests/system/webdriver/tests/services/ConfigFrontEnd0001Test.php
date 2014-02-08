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


class ConfigFrontEnd0001Test extends JoomlaWebdriverTestCase
{

	private $previousSiteName;
	
	
	/**
	 * Login and start test.
	 *
	 * @since   3.2
	 */
	public function setUp()
	{

		$cfg = new SeleniumConfig();
		parent::setUp();

		$homePageUrl = 'index.php';
		$this->driver->get($cfg->host.$cfg->path . $homePageUrl);
		$this->doSiteLogin();
		$url = $cfg->host.$cfg->path . $homePageUrl . '?option=com_config&view=config&controller=config.display.config';

		$this->siteHomePage = $this->getPageObject('SiteConfigurationConfigPage', true, $url);

		$this->previousSiteName = $this->siteHomePage->getSiteName();
	}

	/**
	 * Logout and close test.
	 *
	 * @since   3.2
	 */
	public function tearDown()
	{
		if(!empty($this->previousSiteName))
		{
			$this->siteHomePage->changeSiteName($this->previousSiteName);
		}

		$this->doSiteLogout();
		parent::tearDown();
	}


	/**
	 * @test
	 */
	public function testChangeSiteName()
	{

		$this->siteHomePage->changeSiteName('JoomlaTestSiteTest');

		$this->assertEquals('JoomlaTestSiteTest', $this->siteHomePage->getSiteName(), 'Site name has not changed');		

	}

}