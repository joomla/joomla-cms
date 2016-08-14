<?php
/**
 * @package     Joomla.Test
 * @subpackage  Webdriver
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;
use SeleniumClient\WebElement;

/**
 * Page class for the back-end Messaging Edit page.
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.2
 */
class MessagingEditPage extends AdminEditPage
{
	/**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $waitForXpath =  "//form[@id='message-form']";

	/**
	 * URL used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $url = 'administrator/index.php?option=com_messages&view=message&layout=edit';

	/**
	 * Array of tabs present on this page
	 *
	 * @var    array
	 * @since  3.2
	 */
	public $tabs = array('');

	/**
	 * Array of all the field Details of the Edit page, along with the ID and tab value they are present on
	 *
	 * @var array
	 * @since 3.2
	 */
	public $inputFields = array (
		array('label' => 'Recipient', 'id' => 'jform_user_id_to', 'type' => 'input', 'tab' => ''),
		array('label' => 'Subject', 'id' => 'jform_subject', 'type' => 'input', 'tab' => ''),
		array('label' => 'Description', 'id' => 'jform_message_ifr', 'type' => 'textarea', 'tab' => ''),
		);

}

