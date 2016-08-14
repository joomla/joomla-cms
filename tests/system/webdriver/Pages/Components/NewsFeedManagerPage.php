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
 * Page class for the back-end component newsfeed menu.
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.0
 */
class NewsFeedManagerPage extends AdminManagerPage
{
  /**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $waitForXpath =  "//ul/li/a[@href='index.php?option=com_newsfeeds']";

	/**
	 * URL used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $url = 'administrator/index.php?option=com_newsfeeds';

	/**
	 * Array of filter id values for this page
	 *
	 * @var    array
	 * @since  3.0
	 */
	public $filters = array(
			'Select Status' => 'filter_published',
			'Select Category' => 'filter_category_id',
			'Select Access' => 'filter_access',
			'Select Language' => 'filter_language',
			'Select Tags' => 'filter_tag'
			);

	/**
	 * Array of toolbar id values for this page
	 *
	 * @var    array
	 * @since  3.0
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
	 * Add a new NewsFeed item in the  News Feed Manager: Component screen.
	 *
	 * @param string    $name           Test Feed Name
	 *
	 * @param string    $link			Test URL for the News Feed
	 *
	 * @param string 	$category 		Test Feed Category
	 *
	 * @param string 	$description	Test Feed description
	 *
	 * @param string 	$caption		Test Feed Image Caption
	 *
	 * @param string 	$alt			Test Feed Image Alt
	 *
	 * @return  NewsFeedManagerPage
	 */
	public function addFeed($name='Test Tag', $link='administrator/index.php/dummysrc', $category= 'Sample Data-Newsfeeds', $description='Sample', $caption='',$alt='')
	{
		$new_name = $name;
		$this->clickButton('toolbar-new');
		$newsFeedEditPage = $this->test->getPageObject('NewsFeedEditPage');
		$newsFeedEditPage->setFieldValues(array('Title' => $name, 'Link'=> $link, 'Category'=>$category, 'Description'=>$description, 'Caption'=>$caption, 'Alt text'=>$alt));
		$newsFeedEditPage->clickButton('toolbar-save');
		$this->test->getPageObject('NewsFeedManagerPage');
	}

	/**
	 * Edit a News Feed item in the News Feed Manager: Newsfeed Items screen.
	 *
	 * @param string   $name	   Newsfeed Title field
	 * @param array    $fields     associative array of fields in the form label => value.
	 *
	 * @return  void
	 */
	public function editFeed($name, $fields)
	{
		$this->clickItem($name);
		$newsFeedEditPage = $this->test->getPageObject('NewsFeedEditPage');
		$newsFeedEditPage->setFieldValues($fields);
		$newsFeedEditPage->clickButton('toolbar-save');
		$this->test->getPageObject('NewsFeedManagerPage');
		$this->searchFor();
	}

	/**
	 * Get state  of a News Feed in the News Feed Manager: News Feed Items screen.
	 *
	 * @param string   $name	   News Feed Title field
	 *
	 * @return  State of the NewsFeed //Published or Unpublished
	 */
	public function getState($name)
	{
		$result = false;
		$row = $this->getRowNumber($name);
		$text = $this->driver->findElement(By::xPath("//tbody/tr[" . $row . "]/td[3]//a"))->getAttribute(@onclick);
		if (strpos($text, 'newsfeeds.unpublish') > 0)
		{
			$result = 'published';
		}
		if (strpos($text, 'newsfeeds.publish') > 0)
		{
			$result = 'unpublished';
		}
		return $result;
	}

	/**
	 * Change state of a News Feed item in the News Feed Manager: News Feed Items screen.
	 *
	 * @param string   $name	   News Feed Title field
	 * @param string   $state      State of the Feed
	 *
	 * @return  void
	 */
	public function changeFeedState($name, $state = 'published')
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
