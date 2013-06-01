<?php

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
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Page class for the back-end component contact menu.
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.2
 */
class CategoryManagerPage extends AdminManagerPage
{
	/**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $waitForXpath =  "//h1[contains(., 'Category Manager:')]";

	/**
	 * URL used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $url = 'option=com_categories&extension=';

	/**
	 * Array of filter id values for this page
	 *
	 * @var    array
	 * @since  3.2
	 */
	public $filters = array(
			'Select Status' => 'filter_published',
			'Select Category' => 'filter_category_id',
			'Select Access' => 'filter_access',
			'Select Language' => 'filter_language',
			'Select Tags' => 'filter_tag',
			);

	/**
	 * Array of toolbar id values for this page
	 *
	 * @var    array
	 * @since  3.2
	 */
	public $toolbar = array (
			'New' => 'toolbar-new',
			'Edit' => 'toolbar-edit',
			'Publish' => 'toolbar-publish',
			'Unpublish' => 'toolbar-unpublish',
			'Archive' => 'toolbar-archive',
			'Check In' => 'toolbar-check-in',
			'Trash' => 'toolbar-trash',
			'Empty Trash' => 'toolbar-delete',
			'Batch' => 'toolbar-batch',
			'Options' => 'toolbar-options',
			'Rebuid' => 'toolbar-refresh',
			'Help' => 'toolbar-help',
			);

	/**
	 * Override parent constructor so we can deal with the view=categories in the URL.
	 *
	 * @param  Webdriver                 $driver    Driver for this test.
	 * @param  JoomlaWebdriverTestClass  $test      Test class object (needed to create page class objects)
	 * @param  string                    $url       Optional URL to load when object is created. Only use for initial page load.
	 */
	public function __construct(Webdriver $driver, $test, $url = null)
	{
		$this->driver = $driver;
		/* @var $test JoomlaWebdriverTestCase */
		$this->test = $test;
		$cfg = new SeleniumConfig();
		$this->cfg = $cfg; // save current configuration
		if ($url)
		{
			$this->driver->get($url);
		}
		$element = $driver->waitForElementUntilIsPresent(By::xPath($this->waitForXpath), 5);
		if (isset($this->url))
		{
			$actualUrl = $driver->getCurrentPageUrl();
			// Strip out view=categories if it is present
			$actualUrl = str_replace('&view=categories', '', $actualUrl);
			$test->assertContains($this->url, $actualUrl, 'URL for page does not match expected value.');
		}
	}

}
