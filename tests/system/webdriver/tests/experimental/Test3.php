<?php

// require_once 'AutoLoader.php';

require_once '../bootstrap.php';

use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;
use SeleniumClient\WebElement;

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

	public function testTinyTyping()
	{
		$d = $this->_driver;
		//get url
		$d->get($this->_testUrl . '/administrator');
		//access text input
		$webElement = $d->findElement(By::id("mod-login-username"));
		$webElement->clear();
		$webElement->sendKeys("admin");
		$webElement = $d->findElement(By::id("mod-login-password"));
		$webElement->clear();
		$webElement->sendKeys("password");
		//access button
		$d->findElement(By::partialLinkText("Log in"))->click();
		$d->waitForElementUntilIsPresent(By::partialLinkText('Add New Article'))->click();

		$salt = mt_rand();
		$d->waitForElementUntilIsPresent(By::id('jform_title'))->sendKeys('Article Title ' . $salt);
		$d->switchTo()->getFrameByName('jform_articletext_ifr');
		$d->findElement(By::id('tinymce'))->sendKeys('This is some article text.');
		$d->switchTo()->getDefaultFrame();
		$d->findElement(By::xPath("//a[contains(@onclick, 'article.save')]"))->click();

		$el = $d->waitForElementUntilIsPresent(By::partialLinkText('Article Title ' . $salt));

		$this->assertInstanceOf('SeleniumClient\WebElement', $el);
		$el->click();
		$d->waitForElementUntilIsPresent(By::id('jform_title'));

		$select = new SelectElement($d->findElement(By::id("jform_state")));
		$select->selectByValue("2");

		$select = new SelectElement($d->findElement(By::id("jform_featured")));
		$select->selectByValue("1");


		sleep(5);
		// Clean up


	}
}