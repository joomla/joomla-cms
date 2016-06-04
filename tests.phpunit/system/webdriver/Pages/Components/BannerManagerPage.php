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
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Page class for the back-end component banner menu.
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.2
 */
class BannerManagerPage extends AdminManagerPage
{
	/**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $waitForXpath =  "//ul/li/a[@href='index.php?option=com_banners']";

	/**
	 * URL used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $url = 'administrator/index.php?option=com_banners';

	/**
	 * Array of filter id values for this page
	 *
	 * @var array
	 * @since 3.2
	 */
	public $filters = array(
		'Sort Table By:' => 'list_fullordering',
		'20' => 'list_limit',
		'Select Status' => 'filter_state',
		'Select Client' => 'filter_client_id',
		'Select Category' => 'filter_category_id',
		'Select Language' => 'filter_language'
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
			'Help' => 'toolbar-help',
			);

	/**
	 * Add a new Banner item in the Banner Manager: Component screen.
	 *
	 * @param string   $name          Test Banner Name
	 * @param array    $fields		  associative array of fields in the form label => value.
	 *
	 * @return  BannerManagerPage
	 */
	public function addBanner($name='Test Banner', $fields = null)
	{
		$this->clickButton('toolbar-new');
		$bannerEditPage = $this->test->getPageObject('BannerEditPage');
		$bannerEditPage->setFieldValues(array('Name' => $name));
		if ($fields)
		{
			$bannerEditPage->setFieldValues($fields);
		}
		$bannerEditPage->clickButton('toolbar-save');
		$this->test->getPageObject('BannerManagerPage');
	}

	/**
	 * Edit a Banner item in the Banner Manager: Banner Items screen.
	 *
	 * @param string   $name	   Banner Title field
	 * @param array    $fields     associative array of fields in the form label => value.
	 *
	 * @return  void
	 */
	public function editBanner($name, $fields)
	{
		$this->clickItem($name);
		$bannerEditPage = $this->test->getPageObject('BannerEditPage');
		$bannerEditPage->setFieldValues($fields);
		$bannerEditPage->clickButton('toolbar-save');
		$this->test->getPageObject('BannerManagerPage');
		$this->searchFor();
	}

	/**
	 * Get state  of a Banner item in the Banner Manager: Banner Items screen.
	 *
	 * @param string   $name	   Banner Title field
	 *
	 * @return  State of the Banner //Published or Unpublished
	 */
	public function getState($name)
	{
		$result = false;
		$row = $this->getRowNumber($name);
		$text = $this->driver->findElement(By::xPath("//tbody/tr[" . $row . "]/td[3]//a"))->getAttribute(@onclick);
		if (strpos($text, 'banners.unpublish') > 0)
		{
			$result = 'published';
		}
		if (strpos($text, 'banners.publish') > 0)
		{
			$result = 'unpublished';
		}
		return $result;
	}

	/**
	 * Change state of a Banner item in the Banner Manager: Banner Items screen.
	 *
	 * @param string   $name	   Banner Title field
	 * @param string   $state      State of the Banner
	 *
	 * @return  void
	 */
	public function changeBannerState($name, $state = 'published')
	{
		$this->searchFor($name);
		$this->checkAll();
		if (strtolower($state) == 'published')
		{
			$this->clickButton('toolbar-publish');
			$this->driver->waitForElementUntilIsPresent(By::xPath($this->waitForXpath));
		}
		elseif (strtolower($state) == 'unpublished')
		{
			$this->clickButton('toolbar-unpublish');
			$this->driver->waitForElementUntilIsPresent(By::xPath($this->waitForXpath));
		}
		elseif (strtolower($state) == 'archived')
		{
			$this->clickButton('toolbar-archive');
			$this->driver->waitForElementUntilIsPresent(By::xPath($this->waitForXpath));
		}
		$this->searchFor();
	}
}
