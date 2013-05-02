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
	 * @param string   $title          Test Tag Name
	 * 
	 * 
	 * @return  TagManagerPage
	 */
	public function addTag($name='Test Tag')
	{
		$new_name = $name . rand(1,100);
		$login = "testing";
		//echo $new_name; 
		$this->clickButton('toolbar-new');
		$tagEditPage = $this->test->getPageObject('TagEditPage');
		$tagEditPage->setFieldValues(array('Title' => $new_name));
		$tagEditPage->clickButton('toolbar-save');
		$this->test->getPageObject('TagManagerPage');
	}
	
	/**
	 * Edit a Tag item in the Tag Manager: Tag Items screen.
	 *
	 * @param string   $name	   Tag Title field
	 * @param array    $fields         associative array of fields in the form label => value.
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

	
}
