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
class GenericAdminPage extends AdminPage
{
	protected $waitForXpath =  "//button[contains(@onclick, 'option=com_help&keyref=Help')]";

}