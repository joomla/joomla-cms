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
 * Class for the back-end control panel screen.
 *
 */
class PostinstallPage extends AdminPage
{
	protected $waitForXpath =  "//h1[contains(., 'Post-installation Messages')]";

	/**
	 * Clears post-installation messages by navigating to that screen and back
	 *
	 * @return  null
	 */
	public function clearInstallMessages()
	{
		$clearButtons = $this->driver->findElements(By::xPath("//a[contains(text(), 'Hide this message')]"));
		while (count($clearButtons) > 0)
		{
			$clearButtons[0]->click();
			$page = $this->test->getPageObject('PostinstallPage');
			$clearButtons = $this->driver->findElements(By::xPath("//a[contains(text(), 'Hide this message')]"));
		}

	}

}