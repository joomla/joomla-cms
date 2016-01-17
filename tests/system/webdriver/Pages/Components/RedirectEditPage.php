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
 * Page class for the back-end menu Redirect manager screen.
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.0
 */
class RedirectEditPage extends AdminEditPage
{
  /**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */  
	protected $waitForXpath =  "//form[@id='link-form']";
	
	/**
	 * URL used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $url = 'administrator/index.php?option=com_redirect&view=link&layout=edit';
	
	/**
	 * Array of tabs present on this page
	 *
	 * @var    array
	 * @since  3.0
	 */
	public $tabs = array('basic');
	
	/**
	 * Array of tab labels for this page
	 *
	 * @var    array
	 * @since  3.0
	 */
	public $tabLabels = array('New Link');
	
	/**
	 * Array of all the field Details of the Edit page, along with the ID and tab value they are present on
	 *
	 * @var array		 
	 * @since 3.0
	 */
	public $inputFields = array (
			array('label' => 'Source URL', 'id' => 'jform_old_url', 'type' => 'input', 'tab' => 'basic'),
			array('label' => 'Destination URL', 'id' => 'jform_new_url', 'type' => 'input', 'tab' => 'basic'),		
			array('label' => 'Status', 'id' => 'jform_published', 'type' => 'select', 'tab' => 'basic'),			
			array('label' => 'Comment', 'id' => 'jform_comment', 'type' => 'input', 'tab' => 'basic'),
			array('label' => 'ID', 'id' => 'jform_id', 'type' => 'input', 'tab' => 'basic'),
			array('label' => 'Created Date', 'id' => 'jform_created_date', 'type' => 'input', 'tab' => 'basic'),
			array('label' => 'Last Updated Date', 'id' => 'jform_modified_date', 'type' => 'input', 'tab' => 'basic'),
			
	);
	
	
}
