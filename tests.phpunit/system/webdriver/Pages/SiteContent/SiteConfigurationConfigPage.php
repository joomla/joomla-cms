<?php
/**
 * @package     Joomla.Tests
 * @subpackage  Page
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

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
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Home Page Class
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.2
 */
class SiteConfigurationConfigPage extends SitePage
{
	/**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $waitForXpath = "//form[@id='application-form']";

	/**
	 * URL used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $url = 'controller=config.display.config';

	/**
	 * Function which changes the sitename saving the changes
	 *
	 * @param   string  $siteName  stores the name of the site
	 *
	 * @return void
	 */
	public function changeSiteName($siteName)
	{
		$d = $this->driver;

		$d->findElement(By::xPath("//input[@id='jform_sitename']"))->clear();
		$d->findElement(By::xPath("//input[@id='jform_sitename']"))->sendKeys($siteName);
		$d->findElement(By::xPath("//button[@type='button'][@class='btn btn-primary']"))->click();
		$this->test->getPageObject('SiteConfigurationConfigPage');
	}

	/**
	 * Function which returns site name
	 *
	 * @return  string   site name
	 */
	public function getSiteName()
	{
		$d = $this->driver;

		return $d->findElement(By::xPath("//span[@class='site-title']"))->getText();

	}

	/**
	 * Function which changes the meta description saving the changes
	 *
	 * @param   string  $metaDescription  store the value of metadescription
	 *
	 * @return  null
	 */
	public function changeMetaDescription($metaDescription)
	{
		$d = $this->driver;

		$d->findElement(By::xPath("//textarea[@id='jform_MetaDesc']"))->clear();
		$d->findElement(By::xPath("//textarea[@id='jform_MetaDesc']"))->sendKeys($metaDescription);
		$d->findElement(By::xPath("//button[@type='button'][@class='btn btn-primary']"))->click();
		$this->test->getPageObject('SiteConfigurationConfigPage');

	}

	/**
	 * Function which returns the meta description
	 *
	 * @return  string   Meta description
	 */
	public function getMetaDescription()
	{
		$d = $this->driver;

		return $d->findElement(By::xPath("//textarea[@id='jform_MetaDesc']"))->getText();

	}
}
