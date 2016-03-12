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
 * Class for the back-end control panel screen.
 *
 * @since  joomla 3.0
 */
class MenuItemEditPage extends AdminEditPage
{
	protected $waitForXpath = "//form[@id='item-form']";

	protected $url = 'administrator/index.php?option=com_menus&view=item&layout=edit';

	/**
	 * Array of tabs present on this page
	 *
	 * @var    array
	 * @since  3.2
	 */
	public $tabs = array('details', 'attrib-menu-options', 'attrib-page-options', 'attrib-metadata', 'modules','attrib-basic');

	/**
	 * Array of tab labels for this page
	 *
	 * @var    array
	 * @since  3.2
	 */
	public $tabLabels = array('Details', 'Advanced Options', 'Module Assignment');

	/**
	 * Array of groups for this page. A group is a collapsable slider inside a tab.
	 * The format of this array is <tab id> => <group label>. Note that each menu item type has its own options and its own groups.
	 * These are the common ones for almost all core menu item types.
	 *
	 * @var    array
	 * @since  3.2
	 */
	public $groups = array(
		'options' => array('Link Type', 'Page Display', 'Metadata'),
			);

	/**
	 * Associative array of expected input fields for the Menu Manager: Add / Edit Menu
	 * @var array
	 */
	public $inputFields = array (
			array('label' => 'Menu Title', 'id' => 'jform_title', 'type' => 'input', 'tab' => 'header'),
			array('label' => 'Alias', 'id' => 'jform_alias', 'type' => 'input', 'tab' => 'header'),
			array('label' => 'Menu Item Type', 'id' => 'jform_type', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Link', 'id' => 'jform_link', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Target Window', 'id' => 'jform_browserNav', 'type' => 'select', 'tab' => 'details'),
			array('label' => 'Template Style', 'id' => 'jform_template_style_id', 'type' => 'select', 'tab' => 'details'),
			array('label' => 'Menu Location', 'id' => 'jform_menutype', 'type' => 'select', 'tab' => 'details'),
			array('label' => 'Parent Item', 'id' => 'jform_parent_id', 'type' => 'select', 'tab' => 'details'),
			array('label' => 'Status', 'id' => 'jform_published', 'type' => 'select', 'tab' => 'details'),
			array('label' => 'Default Page', 'id' => 'jform_home', 'type' => 'fieldset', 'tab' => 'details'),
			array('label' => 'Access', 'id' => 'jform_access', 'type' => 'select', 'tab' => 'details'),
			array('label' => 'Language', 'id' => 'jform_language', 'type' => 'select', 'tab' => 'details'),
			array('label' => 'Note', 'id' => 'jform_note', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Link Title Attribute', 'id' => 'jform_params_menu_anchor_title', 'type' => 'input', 'tab' => 'attrib-menu-options'),
			array('label' => 'Link CSS Style', 'id' => 'jform_params_menu_anchor_css', 'type' => 'input', 'tab' => 'attrib-menu-options'),
			array('label' => 'Link Image', 'id' => 'jform_params_menu_image', 'type' => 'input', 'tab' => 'attrib-menu-options'),
			array('label' => 'Add Menu Title', 'id' => 'jform_params_menu_text', 'type' => 'fieldset', 'tab' => 'attrib-menu-options'),
			array('label' => 'Browser Page Title', 'id' => 'jform_params_page_title', 'type' => 'input', 'tab' => 'attrib-page-options'),
			array('label' => 'Show Page Heading', 'id' => 'jform_params_show_page_heading', 'type' => 'fieldset', 'tab' => 'attrib-page-options'),
			array('label' => 'Page Heading', 'id' => 'jform_params_page_heading', 'type' => 'input', 'tab' => 'attrib-page-options'),
			array('label' => 'Page Class', 'id' => 'jform_params_pageclass_sfx', 'type' => 'input', 'tab' => 'attrib-page-options'),
			array('label' => 'Meta Description', 'id' => 'jform_params_menu_meta_description', 'type' => 'textarea', 'tab' => 'attrib-metadata'),
			array('label' => 'Meta Keywords', 'id' => 'jform_params_menu_meta_keywords', 'type' => 'textarea', 'tab' => 'attrib-metadata'),
			array('label' => 'Robots', 'id' => 'jform_params_robots', 'type' => 'select', 'tab' => 'attrib-metadata'),
			array('label' => 'Secure', 'id' => 'jform_params_secure', 'type' => 'select', 'tab' => 'attrib-metadata'),
			array('label' => 'Hide Unassigned Modules', 'id' => 'showmods', 'type' => 'input', 'tab' => 'modules'),
			array('label' => 'Show Title', 'id ' => 'jform_params_show_title', 'type' => 'fieldset', 'tab' => 'attrib-basic' ),
			array('label' => 'Linked Titles','id' => 'jform_params_link_titles','type' => 'fieldset','tab' => 'attrib-basic' ),
			array('label' => 'Show Intro Text','id' => 'jform_params_show_intro','type' => 'fieldset','tab' => 'attrib-basic' ),
			array('label' => 'Position of Article Info', 'id' => 'jform_params_info_block_position', 'type' => 'fieldset', 'tab' => 'attrib-basic'),
			array('label' => 'Show Category', 'id' => 'jform_params_show_category', 'type' => 'fieldset', 'tab' => 'attrib-basic'),
			array('label' => 'Link Category', 'id' => 'jform_params_link_category', 'type' => 'fieldset', 'tab' => 'attrib-basic'),
			array('label' => 'Show Parent', 'id' => 'jform_params_show_parent', 'type' => 'fieldset', 'tab' => 'attrib-basic'),
			array('label' => 'Link Parent', 'id' => 'jform_params_link_parent_category', 'type' => 'fieldset', 'tab' => 'attrib-basic'),
			array('label' => 'Show Author', 'id' => 'jform_params_show_author', 'type' => 'fieldset', 'tab' => 'attrib-basic'),
			array('label' => 'Link Author', 'id' => 'jform_params_link_author', 'type' => 'fieldset', 'tab' => 'attrib-basic'),
			array('label' => 'Show Create Date', 'id' => 'jform_params_show_create', 'type' => 'fieldset', 'tab' => 'attrib-basic'),
			array('label' => 'Show Modify Date', 'id' => 'jform_params_show_modify_date', 'type' => 'fieldset', 'tab' => 'attrib-basic'),
			array('label' => 'Show Publish Date', 'id' => 'jform_params_show_publish_date', 'type' => 'fieldset', 'tab' => 'attrib-basic'),
			array('label' => 'Show Navigation', 'id' => 'jform_params_show_item_navigation', 'type' => 'fieldset', 'tab' => 'attrib-basic'),
			array('label' => 'Show Voting', 'id' => 'jform_params_show_vote', 'type' => 'fieldset', 'tab' => 'attrib-basic'),
			array('label' => 'Show Tags', 'id' => 'jform_params_show_tags', 'type' => 'fieldset', 'tab' => 'attrib-basic'),
			array('label' => 'Show Icons', 'id' => 'jform_params_show_icons', 'type' => 'fieldset', 'tab' => 'attrib-basic'),
			array('label' => 'Show Print Icon', 'id' => 'jform_params_show_print_icon', 'type' => 'fieldset','tab' => 'attrib-basic'),
			array('label' => 'Show Email Icon', 'id' => 'jform_params_show_email_icon','type' => 'fieldset','tab' => 'attrib-basic'),
			array('label' => 'Show Hits', 'id' => 'jform_params_show_hits', 'type' => 'fieldset', 'tab' => 'attrib-basic'),
			array('label' => 'Show Unauthorised Links', 'id' => 'jform_params_show_noauth', 'type' => 'fieldset', 'tab' => 'attrib-basic'),
			array('label' => 'Positioning of the Links', 'id' => 'jform_params_urls_position', 'type' => 'fieldset', 'tab' => 'attrib-basic'),
			);

			public $menuItemTypes = array(
					array('group' => 'Articles', 'type' => 'Archived Articles ' ),
					array('group' => 'Articles', 'type' => 'Category Blog ' ),
					array('group' => 'Articles', 'type' => 'Category List ' ),
					array('group' => 'Articles', 'type' => 'Create Article ' ),
					array('group' => 'Articles', 'type' => 'Featured Articles ' ),
					array('group' => 'Articles', 'type' => 'List All Categories ' ),
					array('group' => 'Articles', 'type' => 'Single Article ' ),
					array('group' => 'Configuration Manager', 'type' => 'Display Site Configuration Options ' ),
					array('group' => 'Configuration Manager', 'type' => 'Display Template Options ' ),
					array('group' => 'Contacts', 'type' => 'Featured Contacts ' ),
					array('group' => 'Contacts', 'type' => 'List All Contact Categories ' ),
					array('group' => 'Contacts', 'type' => 'List Contacts in a Category ' ),
					array('group' => 'Contacts', 'type' => 'Single Contact ' ),
					array('group' => 'News Feeds', 'type' => 'List All News Feed Categories ' ),
					array('group' => 'News Feeds', 'type' => 'List News Feeds in a Category ' ),
					array('group' => 'News Feeds', 'type' => 'Single News Feed ' ),
					array('group' => 'Search', 'type' => 'Search Form or Search Results ' ),
					array('group' => 'Smart Search', 'type' => 'Search ' ),
					array('group' => 'System Links', 'type' => 'External URL ' ),
					array('group' => 'System Links', 'type' => 'Menu Heading ' ),
					array('group' => 'System Links', 'type' => 'Menu Item Alias ' ),
					array('group' => 'System Links', 'type' => 'Text Separator ' ),
					array('group' => 'Tags', 'type' => 'Compact list of tagged items ' ),
					array('group' => 'Tags', 'type' => 'List of all tags ' ),
					array('group' => 'Tags', 'type' => 'Tagged Items ' ),
					array('group' => 'Users Manager', 'type' => 'Edit User Profile ' ),
					array('group' => 'Users Manager', 'type' => 'Login Form ' ),
					array('group' => 'Users Manager', 'type' => 'Password Reset ' ),
					array('group' => 'Users Manager', 'type' => 'Registration Form ' ),
					array('group' => 'Users Manager', 'type' => 'User Profile ' ),
					array('group' => 'Users Manager', 'type' => 'Username Reminder Request ' ),
					array('group' => 'Wrapper', 'type' => 'Iframe Wrapper ' ),
			);

	/**
	 * function to get field value
	 *
	 * @param   string  $label   stores label
	 *
	 * @return bool|String
	 */
	public function getFieldValue($label)
	{
		$result = false;

		if (strtolower($label) === 'menu item type')
		{
			$result = $this->getMenuItemType($label);
		}
		elseif (in_array(strtolower($label), array('article', 'contact', 'newsfeed', 'weblink')))
		{
			$result = $this->getRequestVariable($label);
		}
		elseif (strtolower($label) == 'category')
		{
			$result = parent::getSelectValues(array('tab' => 'Details', 'id' => 'jform_request_id'));
		}
		else
		{
			$result = parent::getFieldValue($label);
		}

		return $result;
	}

	/**
	 * function to get the group name
	 *
	 * @param   string  $value   stores value
	 *
	 * @return bool
	 */
	protected function getGroupName($value)
	{
		foreach ($this->menuItemTypes as $array)
		{
			if (strpos($array['type'], $value) !== false)
				return $array['group'];
		}

		return false;
	}

	/**
	 * function to get menu type
	 *
	 * @return String
	 */
	public function getMenuItemType()
	{
		return $this->driver->findElement(By::xPath("//label[@id='jform_type-lbl']/../..//input"))->getAttribute('value');
	}

	/**
	 * function to get all menu types
	 *
	 * @return array
	 */
	public function getMenuItemTypes()
	{
		$result = array();
		$d = $this->driver;
		$d->findElement(By::xPath("//a[contains(@onclick, 'option=com_menus&view=menutypes')]"))->click();
		$el = $d->waitForElementUntilIsPresent(By::xPath("//iframe[contains(@src, 'option=com_menus&view=menutypes')]"));
		$el = $d->switchTo()->getFrameByWebElement($el);
		$groups = $d->findElements(By::className('accordion-group'));

		foreach ($groups as $group)
		{
			$toggle = $group->findElement(By::className('accordion-toggle'));
			$toggleName = $toggle->getText();
			$toggle->click();
			$d->waitForElementUntilIsPresent(By::xPath("//div[contains(@class, 'accordion-body in')]/div/ul/li/a"));
			$menuTypes = $el->findElements(By::xPath("//div[contains(@class, 'accordion-body in')]/div/ul/li/a"));

			foreach ($menuTypes as $menuType)
			{
				$allText = $menuType->getText();
				$subTextLength = strlen($menuType->findElement(By::tagName('small'))->getText());
				$menuTypeText = substr($allText, 0, (strlen($allText) - $subTextLength));
				$result[] = array ('group' => $toggleName, 'type' => $menuTypeText);
			}
		}

		return $result;
	}

	/**
	 * function to get request variable
	 *
	 * @return String
	 */
	public function getRequestVariable()
	{
		return $this->driver->findElement(By::id('jform_request_id_name'))->getAttribute('value');
	}

	/**
	 * function to set value
	 *
	 * @param   string  $label   stores value of label
	 * @param   string  $value   stores value
	 *
	 * @return $this|void
	 */
	public function setFieldValue($label, $value)
	{
		if (strtolower($label) === 'menu item type')
		{
			$this->setMenuItemType($value);
		}
		elseif (in_array(strtolower($label), array('article', 'contact', 'newsfeed', 'weblink')))
		{
			$this->setRequestVariable($value);
		}
		elseif (in_array(strtolower($label), array('category')))
		{
			parent::setSelectValues(array('tab' => 'Details', 'id' => 'jform_request_id', 'value' => $value));
		}
		else
		{
			parent::setFieldValue($label, $value);
		}

		return $this;
	}

	/**
	 * function to set menu type
	 *
	 * @param   string   $value  stores value
	 *
	 * @return $this
	 */
	public function setMenuItemType($value)
	{
		$group = $this->getGroupName($value);
		$d = $this->driver;
		$d->findElement(By::xPath("//input[@id='jform_title']"))->click();
		$d->findElement(By::xPath("//a[contains(@onclick, 'option=com_menus&view=menutypes')]"))->click();
		$el = $d->waitForElementUntilIsPresent(By::xPath("//iframe[contains(@src, 'option=com_menus&view=menutypes')]"));
		$el = $d->switchTo()->getFrameByWebElement($el);
		$d->waitForElementUntilIsPresent(By::xPath("//a[contains(@class, 'accordion-toggle')][contains(., '" . $group . "')]"), 10);
		$el->findElement(By::xPath("//a[contains(@class, 'accordion-toggle')][contains(., '" . $group . "')]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//div[contains(@class, 'accordion-body in')]/div/ul/li/a"));
		$el->findElement(By::xPath("//div[contains(@class, 'accordion-body in')]//a[contains(text(), '" . $value . "')]"))->click();
		$d->waitForElementUntilIsNotPresent(By::xPath("//iframe[contains(@src, 'option=com_menus&view=menutypes')]"));
		$d->waitForElementUntilIsPresent(By::id('jform_title'));
		$d->switchTo()->getDefaultFrame();

		return $this;
	}

	/**
	 * function to set request variable
	 *
	 * @param   string   $value stores  value
	 *
	 * @return void
	 */
	public function setRequestVariable($value)
	{
		$this->selectTab('Details');
		$d = $this->driver;
		$d->findElement(By::xPath("//a[contains(@class, 'modal btn')][contains(@rel, 'iframe')]"))->click();
		$frameElement = $d->waitForElementUntilIsPresent(By::xPath("//iframe[contains(@src, 'layout=modal')]"));
		$d->switchTo()->getFrameByWebElement($frameElement);
		$filter = $d->waitForElementUntilIsPresent(By::id('filter_search'));
		$filter->clear();
		$filter->sendKeys($value);
		$d->findElement(By::xPath("//button[@data-original-title = 'Search']"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//button[@data-original-title = 'Search']"));
		$d->findElement(By::xPath("//a[contains(text(), '" . $value . "')]"))->click();
		$d->waitForElementUntilIsNotPresent(By::xPath("//iframe[contains(@src, 'layout=modal')]"));
		$d->switchTo()->getDefaultFrame();
	}
}
