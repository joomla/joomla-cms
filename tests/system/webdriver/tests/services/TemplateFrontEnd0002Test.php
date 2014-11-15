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


class TemplateFrontEnd0002Test extends JoomlaWebdriverTestCase
{

	private $previousBackgroundColor;

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
		$url = $cfg->host.$cfg->path . $homePageUrl . '?option=com_config&view=templates&controller=config.display.templates';

		$this->siteHomePage = $this->getPageObject('SiteConfigurationTemplatePage', true, $url);

		$this->previousBackgroundColor = $this->siteHomePage->getBackgroundColor();

	}
	
	/**
	 * Logout and close test.
	 *
	 * @since   3.2
	 */
	public function tearDown()
	{

		$this->siteHomePage->changeBackgroundColor($this->previousBackgroundColor);

		$this->doSiteLogout();

		parent::tearDown();
	}


	/**
	 * @test
	 */
	public function testChangeBackgroundColor()
	{

		$this->siteHomePage->changeBackgroundColor('#000000');

		$this->assertEquals('#000000', $this->siteHomePage->getBackgroundColor(), 'Template Color has not changed');			

	}
}