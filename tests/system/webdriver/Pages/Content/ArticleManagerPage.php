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
 * Page class for the back-end component tags menu.
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.0
 */
class ArticleManagerPage extends AdminManagerPage
{
  /**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $waitForXpath =  "//ul/li/a[@href='index.php?option=com_content']";
	
	/**
	 * URL used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $url = 'administrator/index.php?option=com_content';
	
	/**
	 * Array of filter id values for this page
	 *
	 * @var    array
	 * @since  3.0
	 */
	public $filters = array(
			'Select Status' => 'filter_published',
			'Select Category' => 'filter_category_id',
			'Select Max Levels' => 'filter_level',
			'Select Access' => 'filter_access',
			'Select Author' => 'filter_author_id',
			'Select Language' => 'filter_language',
			'Select Tag' => 'filter_tag'
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
			'Featured' => 'toolbar-featured',
			'Archive' => 'toolbar-archive',
			'Check In' => 'toolbar-checkin',
			'Trash' => 'toolbar-trash',
			'Empty Trash' => 'toolbar-delete',
			'Batch' => 'toolbar-batch',
			'Options' => 'toolbar-options',
			'Help' => 'toolbar-help',
			);
			
	/**
	 * Add a new Article item in the Article Manager: Article Manager Screen.
	 *
	 * @param string   $name          Test Article Title 
	 * 
	 * @param string   $category 	  Test Article Category
	 * 
	 * @return  ArticleManagerPage
	 */
	public function addArticle($name='Testing Articles', $category='Sample Data-Articles')
	{
		$new_name = $name;
		$this->clickButton('toolbar-new');
		$articleEditPage = $this->test->getPageObject('ArticleEditPage');
		$articleEditPage->setFieldValues(array('Title' => $name, 'Category' => $category));
		$articleEditPage->clickButton('toolbar-save');
		$this->test->getPageObject('ArticleManagerPage');
	}
	
	/**
	 * Edit a Article item in the Article Manager: Article Manager Screen.
	 *
	 * @param string   $name	   Title field
	 * @param array    $fields     associative array of fields in the form label => value.
	 *
	 * @return  void
	 */
	public function editArticle($name, $fields)
	{
		$this->clickItem($name);
		$articleEditPage = $this->test->getPageObject('ArticleEditPage');
		$articleEditPage->setFieldValues($fields);
		$articleEditPage->clickButton('toolbar-save');
		$this->test->getPageObject('ArticleManagerPage');
		$this->searchFor();
	}
	
	/**
	 * Get state  of a Article in Article Manager Screen: Article Manager.
	 *
	 * @param string   $name	   Article Title field
	 * 
	 * @return  State of the Article //Published or Unpublished
	 */
	public function getState($name)
	{
		$result = false;
		$row = $this->getRowNumber($name);
		$text = $this->driver->findElement(By::xPath("//tbody/tr[" . $row . "]/td[3]/div/a[1]"))->getAttribute(@onclick);
		if (strpos($text, 'articles.unpublish') > 0)
		{
			$result = 'published';
		}
		if (strpos($text, 'articles.publish') > 0)
		{
			$result = 'unpublished';
		}
		return $result;
	}
	
	/**
	 * Change state of a Article Item in Article Manager Screen
	 *
	 * @param string   $name	   Article Title field
	 * @param string   $state      State of the Article
	 *
	 * @return  void
	 */	
	public function changeArticleState($name, $state = 'published')
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
		$this->searchFor();
	}
	
}
