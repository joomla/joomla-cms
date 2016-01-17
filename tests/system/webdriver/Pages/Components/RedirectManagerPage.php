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
class RedirectManagerPage extends AdminManagerPage
{
  /**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $waitForXpath =  "//ul/li/a[@href='index.php?option=com_redirect']";
	
	/**
	 * URL used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $url = '/administrator/index.php?option=com_redirect';
	
	/**
	 * Array of filter id values for this page
	 *
	 * @var    array
	 * @since  3.0
	 */
	public $filters = array(
			'Select Status' => 'filter_state',
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
			'Enable' => 'toolbar-publish',
			'Disable' => 'toolbar-unpublish',
			'Archive' => 'toolbar-archive',
			'Trash' => 'toolbar-trash',
			'Options' => 'toolbar-options',
			'Help' => 'toolbar-help',
			'Empty Trash' => 'toolbar-delete',			
			);
			
	/**
	 * Add a new Redirect item in the  Redirect Manager: Component screen.
	 *
	 * @param string   $srcLink          Test Source Link
	 * 
	 * @param string   $desLink 		 Test Destination Link
	 * 
	 * @param string   $status			 Status for the Redirect
	 * 
	 * @param string	$comment		 Comments on the Redirection
	 * 
	 * @return  RedirectManagerPage
	 */
	public function addRedirect($srcLink='administrator/index.php/dummysrc', $desLink='administrator/index.php/dummydest', $status='Enabled', $comments='')
	{
		$this->clickButton('toolbar-new');
		$redirectEditPage = $this->test->getPageObject('RedirectEditPage');
		$redirectEditPage->setFieldValues(array('Source URL' => $srcLink, 'Destination URL' => $desLink, 'Status' => $status, 'Comment' => $comments));
		$redirectEditPage->clickButton('toolbar-save');
		$this->test->getPageObject('RedirectManagerPage');
	}
	
	/**
	 * Edit a  Redirect item in the Redirect Manager: Redirect Items screen.
	 *
	 * @param string   $src	   	   Link Src Field
	 * @param array    $fields     associative array of fields in the form label => value.
	 *
	 * @return  void
	 */
	public function editRedirect($src, $fields)
	{
		$this->clickItem($src);
		$redirectEditPage = $this->test->getPageObject('RedirectEditPage');
		$redirectEditPage->setFieldValues($fields);
		$redirectEditPage->clickButton('toolbar-save');
		$this->test->getPageObject('RedirectManagerPage');
		$this->searchFor();
	}
	
	/**
	 * Get state  of a Redirect in the Redirect Manager: Redirect Items screen.
	 *
	 * @param string   $src	   Redirect Src field
	 * 
	 * @return  State of the Redirect Link //Enabled or Disabled which is equvalent to publish and unpublish at backend
	 */
	public function getState($src)
	{
		$result = false;
		$row = $this->getRowNumber($src);
		$text = $this->driver->findElement(By::xPath("//tbody/tr[" . $row . "]/td[2]/a"))->getAttribute(@onclick);
		if (strpos($text, 'links.unpublish') > 0)
		{
			$result = 'published';
		}
		if (strpos($text, 'links.publish') > 0)
		{
			$result = 'unpublished';
		}
		return $result;
	}
	
	/**
	 * Change state of a Redirect link item in the Redirect Manager: Redirect Items screen.
	 *
	 * @param string   $src	   	   Redirect link SRC field
	 * @param string   $state      State of the Link
	 *
	 * @return  void
	 */	
	public function changeRedirectState($src, $state = 'published')
	{
		$this->searchFor($src);
		$rowNumber = $this->getRowNumber($src) - 1;
		$this->driver->findElement(By::xPath("//input[@id='cb" . $rowNumber ."']"))->click();
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
