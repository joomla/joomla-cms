<?php

use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;
use SeleniumClient\WebElement;

/**
 * Class for the back-end login screen
 *
 */
class AdminLoginPage extends AdminPage
{
	protected $waitForXpath =  "//input[@id='mod-login-username']";
	protected $url = 'administrator/index.php';

	public function loginValidUser($userName, $password)
	{
		$this->executeLogin($userName, $password);
		return $this->test->getPageObject('ControlPanelPage');
	}

	private function executeLogin($userName, $password)
	{
		$webElement = $this->driver->findElement(By::id("mod-login-username"));
		$webElement->clear();
		$webElement->sendKeys($this->cfg->username);
		$webElement = $this->driver->findElement(By::id("mod-login-password"));
		$webElement->clear();
		$webElement->sendKeys($this->cfg->password);
		//access button
		$this->driver->findElement(By::xPath("//button[contains(., 'Log in')]"))->click();
	}

}