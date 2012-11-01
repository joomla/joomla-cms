<?php

// require_once 'AutoLoader.php';
require_once '../bootstrap.php';

use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;

class AlertTest extends PHPUnit_Framework_TestCase
{
	private $_driver = null;
	private $_testUrl = null;

	public function setUp()
	{
		$this->_testUrl = "http://localhost/joomla_development/cms-trunk/";

// 		$desiredCapabilities = new DesiredCapabilities("firefox");
		$desiredCapabilities = new DesiredCapabilities("chrome");

		$this->_driver = new WebDriver($desiredCapabilities);
	}

	public function tearDown()
	{
		if($this->_driver != null) { $this->_driver->quit(); }
	}

	public function testCreateMenuItem()
	{
		//get url
		$this->_driver->get($this->_testUrl . '/administrator');
		//access text input
		$webElement = $this->_driver->findElement(By::id("mod-login-username"));
		$webElement->clear();
		$webElement->sendKeys("admin");
		$webElement = $this->_driver->findElement(By::id("mod-login-password"));
		$webElement->clear();
		$webElement->sendKeys("password");
		//access button
		$this->_driver->findElement(By::partialLinkText("Log in"))->click();
		$d = $this->_driver;
		$el = $d->waitForElementUntilIsPresent(By::partialLinkText('Menu Manager'));

		$el->click();
		$el = $d->waitForElementUntilIsPresent(By::partialLinkText('Main Menu'));
		echo "found Main Menu";
		$el->click();
		$el = $d->waitForElementUntilIsPresent(By::partialLinkText('New'));
		$el->click();
		$el = $d->waitForElementUntilIsPresent(By::xPath("//input[contains(@onclick, 'iframe')]"));
		echo "found select button";
		$el->click();
		$el = $d->waitForElementUntilIsPresent(By::xPath("//div[@id='sbox-content']/iframe"));

		// switch to modal iframe
		$el = $d->switchTo()->getFrameByWebElement($el)->findElement(By::partialLinkText('Single Article'));
		$el->click();
		$el = $d->waitForElementUntilIsPresent(By::xPath("//input[@value='Single Article']"));

		$salt = mt_rand();
		$d->findElement(By::id('jform_title'))->sendKeys('Test Menu Title ' . $salt);
		$el = $d->findElement(By::partialLinkText('Select / Change'))->click();
		$el = $d->waitForElementUntilIsPresent(By::xPath("//div[@id='sbox-content']/iframe"));
		$el = $d->switchTo()->getFrameByWebElement($el)->findElement(By::partialLinkText('Australian Parks'));
		$el->click();

		$el = $d->waitForElementUntilIsNotPresent(By::xPath("//div[@id='sbox-content']/iframe"));

		$d->findElement(By::xPath("//a[contains(@onclick, 'item.save')]"))->click();


		// Clean up


	}
}