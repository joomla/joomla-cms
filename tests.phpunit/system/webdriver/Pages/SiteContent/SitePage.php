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
 * Page class for front end page
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.2
 */
abstract class SitePage
{
	/**
	 *
	 * @var Webdriver  The driver object for invoking driver methods.
	 */
	protected $driver = null;

	/**
	 *
	 * @var SeleniumConfig  The configuration object.
	 */
	protected $cfg = null;

	/**
	 *
	 * @var string This is the element that we wait for when we load a new page. It should specify something unique about this page.
	 */
	protected $waitForXpath;

	/**
	 *
	 * @var JoomlaWebdriverTestCase  The test object for invoking test methods.
	 */
	protected $test = null;

	/**
	 * @var array $toolbar  Associative array as label => id for the toolbar buttons
	 */
	public $toolbar = array();

	/**
	 * @var string  This is the URL for this page. We check this when a new page class is loaded.
	 */
	protected $url = null;

	/**
	 *
	 * @var  array of top menu text that is visible in all frontend pages
	 */
	public $visibleMenuText = array ('Home','Sample Sites','Joomla.org');

	/**
	 * constructor function
	 *
	 * @param   Webdriver                 $driver  Driver for this test.
	 * @param   JoomlaWebdriverTestClass  $test    Test class object (needed to create page class objects)
	 * @param   string                    $url     Optional URL to load when object is created. Only use for initial page load.
	 */
	public function __construct(Webdriver $driver, $test, $url = null)
	{
		$this->driver = $driver;
		/* @var $test JoomlaWebdriverTestCase */
		$this->test = $test;
		$cfg = new SeleniumConfig;

		// Save current configuration
		$this->cfg = $cfg;

		if ($url)
		{
			$this->driver->get($url);
		}

		$element = $driver->waitForElementUntilIsPresent(By::xPath($this->waitForXpath), 5);

		if (isset($this->url))
		{
			$test->assertContains($this->url, $driver->getCurrentPageUrl(), 'URL for page does not match expected value.');
		}
	}

	/**
	 * @return String
	 */
	public function __toString()
	{
		return $this->driver->getCurrentPageUrl();
	}

	/**
	 * Checks for notices on a page.
	 *
	 * @return  bool  true if notices or warnings present on page
	 */
	public function checkForNotices()
	{
		$haystack = strip_tags($this->driver->pageSource());

		return (bool) (stripos($haystack, "( ! ) Notice") || stripos($haystack, "( ! ) Warning"));
	}
}
