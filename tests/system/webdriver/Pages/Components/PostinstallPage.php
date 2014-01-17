<?php

use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;
use SeleniumClient\WebElement;

/**
 * Class for the back-end control panel screen.
 *
 */
class PostinstallPage extends AdminPage
{
	protected $waitForXpath =  "//h1[contains(text(), 'Post-installation Messages')]";

	/**
	 * Clears post-installation messages by navigating to that screen and back
	 *
	 * @return  null
	 */
	public function clearInstallMessages()
	{
		$this->driver->findElement(By::xPath("//a[contains(text(), 'Hide this message')]"))->click();
		$page = $this->test->getPageObject('PostinstallPage');
	}

}