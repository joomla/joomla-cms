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
 * Abstract Class that is the parent class for all back-end page classes
 * A page class is designed to encapsulate the page-specific attributes and behaviors.
 * For example, page-specific HTML markup and functionality.
 *
 */
abstract class AdminPage
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
	 * @var array $toolbar  Associative array as label => id for the toolbar buttons.
	 */
	public $toolbar = array();

	/**
	 * @var string  This is the URL for this page. We check this when a new page class is loaded.
	 */
	protected $url = null;

	/**
	 *
	 * @var  array of top menu text that is initially visible in all admin menus
	 */
	public $visibleMenuText = array ('System', 'Users', 'Menus', 'Content', 'Components', 'Extensions', 'Help');

	/**
	 *
	 * @var  array  All top menu links in admin screens. These are common in all admin screens.
	 */
	public  $allMenuLinks = array(
			'System' 				=> 'administrator/index.php#',
			'Control Panel' 		=> 'administrator/index.php',
			'Global Configuration'	=> 'administrator/index.php?option=com_config',
			'Global Checkin' 		=> 'administrator/index.php?option=com_checkin',
			'Clear Cache' 			=> 'administrator/index.php?option=com_cache',
			'Purge Expired Cache' 	=> 'administrator/index.php?option=com_cache&view=purge',
			'System Information'	=> 'administrator/index.php?option=com_admin&view=sysinfo',
			'Users'					=> 'administrator/index.php#',
			'User Manager'			=> 'administrator/index.php?option=com_users&view=users',
			'Add New User'			=> 'administrator/index.php?option=com_users&task=user.add',
			'Groups'				=> 'administrator/index.php?option=com_users&view=groups',
			'Add New Group'			=> 'administrator/index.php?option=com_users&task=group.add',
			'Access Levels'			=> 'administrator/index.php?option=com_users&view=levels',
			'Add New Access Level'	=> 'administrator/index.php?option=com_users&task=level.add',
			'User Notes'			=> 'administrator/index.php?option=com_users&view=notes',
			'Add User Note'			=> 'administrator/index.php?option=com_users&task=note.add',
			'User Notes Categories'	=> 'administrator/index.php?option=com_categories&view=categories&extension=com_users',
		'Add User Note Category'	=> 'administrator/index.php?option=com_categories&task=category.add&extension=com_users',
			'Mass Mail Users'		=> 'administrator/index.php?option=com_users&view=mail',
			'Menus'					=> 'administrator/index.php#',
			'Menu Manager'			=> 'administrator/index.php?option=com_menus&view=menus',
			'Add New Menu'			=> 'administrator/index.php?option=com_menus&view=menu&layout=edit',
			'User Menu'				=> 'administrator/index.php?option=com_menus&view=items&menutype=usermenu',
	'User Menu Add New Menu Item' 	=> 'administrator/index.php?option=com_menus&view=item&layout=edit&menutype=usermenu',
			'Top Menu'				=> 'administrator/index.php?option=com_menus&view=items&menutype=top',
	'Top Menu Add New Menu Item' 	=> 'administrator/index.php?option=com_menus&view=item&layout=edit&menutype=top',
			'About Joomla'			=> 'administrator/index.php?option=com_menus&view=items&menutype=aboutjoomla',
'About Joomla Add New Menu Item'	=> 'administrator/index.php?option=com_menus&view=item&layout=edit&menutype=aboutjoomla',
			'Austrlian Parks'		=> 'administrator/index.php?option=com_menus&view=items&menutype=parks',
'Austrlian Parks Add New Menu Item'	=> 'administrator/index.php?option=com_menus&view=item&layout=edit&menutype=parks',
			'Main Menu'				=> 'administrator/index.php?option=com_menus&view=items&menutype=mainmenu',
	'Main Menu Add New Menu Item'	=> 'administrator/index.php?option=com_menus&view=item&layout=edit&menutype=mainmenu',
			'Fruit Shop'			=> 'administrator/index.php?option=com_menus&view=items&menutype=fruitshop',
	'Fruit Shop Add New Menu Item'	=> 'administrator/index.php?option=com_menus&view=item&layout=edit&menutype=fruitshop',
			'Content'				=> 'administrator/index.php#',
			'Article Manager'		=> 'administrator/index.php?option=com_content',
			'Add New Article'		=> 'administrator/index.php?option=com_content&task=article.add',
			'Category Manager'		=> 'administrator/index.php?option=com_categories&extension=com_content',
			'Add New Category'		=> 'administrator/index.php?option=com_categories&task=category.add&extension=com_content',
			'Featured Articles'		=> 'administrator/index.php?option=com_content&view=featured',
			'Media Manager'			=> 'administrator/index.php?option=com_media',
			'Components'			=> 'administrator/index.php#',
			'Banners'				=> 'administrator/index.php?option=com_banners',
			'Banners Categories'	=> 'administrator/index.php?option=com_categories&extension=com_banners',
			'Banners Clients'		=> 'administrator/index.php?option=com_banners&view=clients',
			'Banners Tracks'		=> 'administrator/index.php?option=com_banners&view=tracks',
			'Contacts'				=> 'administrator/index.php?option=com_contact',
			'Contacts Categories'	=> 'administrator/index.php?option=com_categories&extension=com_contact',
			'Joomla! Update'		=> 'administrator/index.php?option=com_joomlaupdate',
			'Messaging'				=> 'administrator/index.php?option=com_messages',
			'New Private Message'	=> 'administrator/index.php?option=com_messages&task=message.add',
			'Newsfeeds'				=> 'administrator/index.php?option=com_newsfeeds',
			'Feeds'					=> 'administrator/index.php?option=com_newsfeeds',
			'Newsfeeds Categories'	=> 'administrator/index.php?option=com_categories&extension=com_newsfeeds',
	'Post-installation Messages'	=> 'administrator/index.php?option=com_postinstall',
			'Redirect'				=> 'administrator/index.php?option=com_redirect',
			'Search'				=> 'administrator/index.php?option=com_search',
			'Smart Search'			=> 'administrator/index.php?option=com_finder',
			'Extensions'			=> 'administrator/index.php#',
			'Extension Manager'		=> 'administrator/index.php?option=com_installer',
			'Module Manager'		=> 'administrator/index.php?option=com_modules',
			'Plugin Manager'		=> 'administrator/index.php?option=com_plugins',
			'Template Manager'		=> 'administrator/index.php?option=com_templates',
			'Language Manager'		=> 'administrator/index.php?option=com_languages',
			'Help'					=> 'administrator/index.php#',
			'Joomla Help'			=> 'administrator/index.php?option=com_admin&view=help',
		'Official Support Forum'	=> 'http://forum.joomla.org/',
			'Documentation Wiki'	=> 'https://docs.joomla.org/',
			'Useful Joomla Links'	=> 'administrator/index.php#',
			'Joomla Extensions'		=> 'http://extensions.joomla.org/',
			'Joomla Translations'	=> 'http://community.joomla.org/translations.html',
			'Joomla Resources'		=> 'http://resources.joomla.org/',
			'Community Portal'		=> 'http://community.joomla.org/',
			'Security Center'		=> 'http://developer.joomla.org/security.html',
			'Developer Resources'	=> 'http://developer.joomla.org/',
			'Joomla Shop'			=> 'http://shop.joomla.org/',
			'Tags'			=>	'administrator/index.php?option=com_tags',
	);


	/**
	 * This variable creates a JavaScript function called moveToElementByAttribute.
	 * This function is used to hover the mouse on an element so the tooltip for that element becomes visible.
	 * It takes 2 arguments in an array: attribute name, attribute value. For example, the following code
	 * will move the mouse to an element with the 'for' attribute equal to $id:
	 *   $this->driver->executeScript($this->moveToElementByAttribute, array('for', $id));
	 *
	 * @var string  JavaScript function
	 */
	public $moveToElementByAttribute = "
		var matchingElements = [];
		var allElements = document.getElementsByTagName('*');
		for (var i = 0; i < allElements.length; i++)
		{
			if (allElements[i].getAttribute(arguments[0]) && allElements[i].getAttribute(arguments[0]) == arguments[1])
			{
				// Element exists with attribute. Add to array.
				matchingElements.push(allElements[i]);
			}
		}
		matchingElements[0].fireEvent('mouseenter');";

	/**
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
			$test->assertContains($this->url, $driver->getCurrentPageUrl(), 'URL for page does not match expected value.');
		}
	}

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

	public function clickButton($idOrLabel)
	{
		$return = false;
		$idOrLabel = strtolower($idOrLabel);
		if (in_array($idOrLabel, $this->toolbar))
		{
			$this->driver->findElement(By::xPath("//div[@id='" . $idOrLabel . "']/button"))->click();
			$return = true;
		}
		else
		{
			foreach ($this->toolbar as $label => $id)
			{
				if (stripos($label, $idOrLabel) !== false)
				{
					$this->driver->findElement(By::xPath("//div[@id='" . $id . "']/button"))->click();
					return true;
				}
			}

		}

	}

	public function clickMenu($linkText, $pageType = 'GenericAdminPage')
	{
		if (array_key_exists($linkText, $this->allMenuLinks))
		{
			$this->driver->get($this->cfg->host . $this->cfg->path . $this->allMenuLinks[$linkText]);
			return $this->test->getPageObject($pageType);
		}
		else
		{
			$this->test->fail('No URL for $linkText = ' . $linkText);
		}
	}

	public function clickMenuByUrl($linkURL, $pageType = 'GenericAdminPage')
	{
		$this->driver->get($this->cfg->host . $this->cfg->path . 'administrator/index.php?option=' . $linkURL, 'GlobalConfigPage');
		return $this->test->getPageObject($pageType);
	}

	public function enterTextField($elementId, $text)
	{
		if ($el = $this->driver->findElement(By::id($elementId)))
		{
			$el->clear();
			$el->sendKeys($text);
			return true;
		}
		else
		{
			return false;
		}

	}

	public function getAlertMessage()
	{
		return $this->driver->findElement(By::id('system-message-container'))->getText();
	}

	/**
	 * Finds all menu links in the top menu of an admin screen
	 *
	 * @return  array of stdClass objects
	 */
	public function getAllMenuLinks()
	{
		$menuContainer = $this->driver->findElement(By::id('menu'));
		$menus = $menuContainer->findElements(By::tagName('a'));
		$return = array();
		foreach ($menus as $menu) {
			$menuObject = new stdClass;
			$menuObject->href = $menu->getAttribute('href');
			$menuObject->text = $menu->getText();
			$menuObject->id = $menu->getElementId();
			$return[] = $menuObject;
		}
		return $return;
	}

	public function getErrorMessage()
	{
		return $this->driver->findElement(By::xPath("//dd[@class='error message']"))->getText();
	}

	public function getSystemMessage()
	{
		return $this->driver->findElement(By::id("system-message"))->getText();
	}

	/**
	 * Gets array of visible links in the menu container
	 * This is normally the header menu for back-end manager screens
	 *
	 * @return  array of stdClass objects
	 */
	public function getVisibleMenuLinks()
	{
		$menuContainer = $this->driver->findElement(By::id('menu'));
		$menus = $menuContainer->findElements(By::tagName('a'));
		$return = array();
		foreach ($menus as $menu) {
			if ($text = $menu->getText())
			{
				$menuObject = new stdClass();
				$menuObject->text = $text;
				$menuObject->href = $menu->getAttribute('href');
				$menuObject->id = $menu->getElementId();
				$return[] = $menuObject;
			}
		}
		return $return;
	}

	public function saveAndClose($returnPage = 'GenericAdminPage')
	{
		$this->clickButton('toolbar-save');
		return $this->test->getPageObject($returnPage);
	}

}
