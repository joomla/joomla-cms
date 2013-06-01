<?php

require_once 'JoomlaWebdriverTestCase.php';

use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;

class AlertTest extends JoomlaWebdriverTestCase
{

	/**
	 * @test
	 */
	public function createMenuItem()
	{
		$cpPage = $this->doAdminLogin();

		$page = $cpPage->clickMenu('Main Menu');
		$page->clickButton('New');
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