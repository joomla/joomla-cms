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
 * this class tests if the color of the template is changed
 *
 * @package     Joomla.Tests
 * @subpackage  Page
 *
 * @copyright   Copyright (c) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       Joomla 3.3
 */

class TemplateFrontEnd0001Test extends JoomlaWebdriverTestCase
{
	private $previousTemplateColor;

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
		$url = $cfg->host . $cfg->path . $homePageUrl . '?option=com_config&view=templates&controller=config.display.templates';

		$this->siteHomePage = $this->getPageObject('SiteConfigurationTemplatePage', true, $url);

		$this->previousTemplateColor = $this->siteHomePage->getTemplateColor();
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
		$this->siteHomePage->changeTemplateColor($this->previousTemplateColor);

		$this->doSiteLogout();

		parent::tearDown();
	}

	/**
	 * test to change the color of the template
	 *
	 * @return void
	 *
	 * @test
	 */
	public function testChangeTemplateColor()
	{
		$this->siteHomePage->changeTemplateColor('#000000');

		$this->assertEquals('#000000', $this->siteHomePage->getTemplateColor(), 'Template Color has not changed');
	}
}
