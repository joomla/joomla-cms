<?php

use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;
use SeleniumClient\WebElement;

/**
 * @package     Joomla.Test
 * @subpackage  Webdriver
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Home Page Class
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.2
 */
class SiteConfigurationTemplatePage extends SitePage
{
	/**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $waitForXpath =  "//form[@id='templates-form']";

	/**
	 * URL used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $url = 'controller=config.display.templates';

	/**
	 * Function which changes the template color saving the changes
	 *
	 * @var string template Color
	 *
	 * @return  null
	 */
	public function changeTemplateColor($templateColor)
	{

		$d = $this->driver;

		$d->findElement(By::xPath("//input[@id='params_templateColor']"))->clear();
		$d->findElement(By::xPath("//input[@id='params_templateColor']"))->sendKeys($templateColor);
		$d->findElement(By::xPath("//button[@type='button'][@class='btn btn-primary']"))->click();

	}

	/**
	 * Function which returns Template Color
	 *
	 *
	 * @return  string   template color
	 */
	public function getTemplateColor()
	{
		$d = $this->driver;

		return $d->findElement(By::xPath("//input[@id='params_templateColor']"))->getAttribute("value");

	}

	/**
	 * Function which changes the Background Color saving the changes
	 *
	 * @var string background color
	 *
	 * @return  null
	 */
	public function changeBackgroundColor($backgroundColor)
	{

		$d = $this->driver;

		$d->findElement(By::xPath("//input[@id='params_templateBackgroundColor']"))->clear();
		$d->findElement(By::xPath("//input[@id='params_templateBackgroundColor']"))->sendKeys($backgroundColor);
		$d->findElement(By::xPath("//button[@type='button'][@class='btn btn-primary']"))->click();
		$this->test->getPageObject('SiteConfigurationTemplatePage');
	}

	/**
	 * Function which returns the Background Color
	 *
	 * @return  string   background color
	 */
	public function getBackgroundColor()
	{
		$d = $this->driver;

		return $d->findElement(By::xPath("//input[@id='params_templateBackgroundColor']"))->getAttribute("value");

	}
}
