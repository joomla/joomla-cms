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
 * this class tests if metadiscription can be changed from site
 *
 * @package     Joomla.Tests
 * @subpackage  Page
 *
 * @copyright   Copyright (c) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       Joomla 3.3
 */
class ConfigFrontEnd0002Test extends JoomlaWebdriverTestCase
{
	private $previousMetaDescription;

	/**
	 * Login and start test.
	 *
	 * @return void
	 *
	 * @since   3.2
	 */
	public function setUp()
	{
		$cfg = new SeleniumConfig;
		parent::setUp();

		$homePageUrl = 'index.php';
		$this->driver->get($cfg->host . $cfg->path . $homePageUrl);
		$this->doSiteLogin();
		$url = $cfg->host . $cfg->path . $homePageUrl . '?option=com_config&view=config&controller=config.display.config';

		$this->siteHomePage = $this->getPageObject('SiteConfigurationConfigPage', true, $url);

		$this->previousMetaDescription = $this->siteHomePage->getMetaDescription();
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
		$this->siteHomePage->changeMetaDescription($this->previousMetaDescription);

		$this->doSiteLogout();
		parent::tearDown();
	}

	/**
	 * test to change the meta description
	 *
	 * @return void
	 *
	 * @test
	 */
	public function testChangeMetaDescription()
	{
		$newMetaDescription = 'JoomlaTestMetaDescription' . rand(1,100);
		$this->siteHomePage->changeMetaDescription($newMetaDescription);

		$this->assertNotEquals($this->previousMetaDescription, $this->siteHomePage->getMetaDescription(), 'Site Meta Description has not changed');
	}
}
