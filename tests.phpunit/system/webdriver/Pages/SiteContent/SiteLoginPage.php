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
 * Page class for front end login page
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.2
 */
class SiteLoginPage extends SitePage
{
	/**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $waitForXpath = "//button[@class='btn btn-primary']";

	/**
	 * URL used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $url = '/index.php/login';

	/**
	 * Function to click on logout button 
	 *
	 * @return  void
	 */
	 public function SiteLogoutUser()
	 {
		 $d = $this->driver;
		 $d->findElement(By::xPath("//button[contains(text(), 'Log out')]"))->click();
		 $d->clearCurrentCookies();
	 }
	
	/**
	 * Function to enter Username Password
	 * 
	 * @param   string  $username  Username of the user
	 * @param   string  $password  Password of the user
	 *
	 * @return  void
	 */
	 public function SiteLoginUser($username, $password)
	 {
		$d = $this->driver;
		$d->findElement(By::xPath("//input[@id='username']"))->sendKeys($username);
		$d->findElement(By::xPath("//input[@id='password']"))->sendKeys($password);
		$d->findElement(By::xPath("//button[contains(text(), 'Log in')]"))->click();
	 }
}
