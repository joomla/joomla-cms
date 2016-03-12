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
 * Page class for the back-end component tags menu.
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.0
 */
class TagManagerPage extends AdminManagerPage
{
	/**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $waitForXpath =  "//ul/li/a[@href='index.php?option=com_tags']";

	/**
	 * URL used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $url = 'administrator/index.php?option=com_tags';

	/**
	 * Array of filter id values for this page
	 *
	 * @var    array
	 * @since  3.0
	 */
	public $filters = array(
			'Select Status' => 'filter_published',
			'Select Access' => 'filter_access',
			'Select Language' => 'filter_language',
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
	 * Add a new Tag item in the Tag Manager: Component screen.
	 *
	 * @param string   $name          Test Tag Name
	 *
	 * @param string   $caption 	  Caption of the test Image
	 *
	 * @param string   $alt			  Alternative Caption for the Image
	 *
	 * @param string   $float		  Position of the Image of the tag
	 *
	 * @return  TagManagerPage
	 */
	public function addTag($name='Test Tag', $caption='sample', $alt='Sample_Alt', $float='Use Global')
	{
		$new_name = $name;
		$login = "testing";
		$this->clickButton('toolbar-new');
		$tagEditPage = $this->test->getPageObject('TagEditPage');
		$tagEditPage->setFieldValues(array('Title' => $name, 'Caption' => $caption, 'Alt'=>$alt,'Float'=>$float));
		$tagEditPage->clickButton('toolbar-save');
		$this->test->getPageObject('TagManagerPage');
	}

	/**
	 * Edit a Tag item in the Tag Manager: Tag Items screen.
	 *
	 * @param string   $name	   Tag Title field
	 * @param array    $fields     associative array of fields in the form label => value.
	 *
	 * @return  void
	 */
	public function editTag($name, $fields)
	{
		$this->clickItem($name);
		$tagEditPage = $this->test->getPageObject('TagEditPage');
		$tagEditPage->setFieldValues($fields);
		$tagEditPage->clickButton('toolbar-save');
		$this->test->getPageObject('TagManagerPage');
		$this->searchFor();
	}

	/**
	 * Get state  of a Tag item in the Tag Manager: Tag Items screen.
	 *
	 * @param string   $name	   Tag Title field
	 *
	 * @return  State of the Tag //Published or Unpublished
	 */
	public function getState($name)
	{
		$result = false;
		$row = $this->getRowNumber($name);
		$text = $this->driver->findElement(By::xPath("//tbody/tr[" . $row . "]/td[3]//a"))->getAttribute(@onclick);
		if (strpos($text, 'tags.unpublish') > 0)
		{
			$result = 'published';
		}
		if (strpos($text, 'tags.publish') > 0)
		{
			$result = 'unpublished';
		}
		return $result;
	}

	/**
	 * Change state of a Tag item in the Tag Manager: Tag Items screen.
	 *
	 * @param string   $name	   Tag Title field
	 * @param string   $state      State of the Tag
	 *
	 * @return  void
	 */
	public function changeTagState($name, $state = 'published')
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
