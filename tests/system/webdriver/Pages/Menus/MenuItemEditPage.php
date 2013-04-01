<?php

use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;
use SeleniumClient\WebElement;

/**
 * Class for the back-end control panel screen.
 *
 */
class MenuItemEditPage extends AdminEditPage
{
	protected $waitForXpath = "//form[@id='item-form']";
	protected $url = 'administrator/index.php?option=com_menus&view=item&layout=edit';

	/**
	 * Associative array of expected input fields for the Menu Manager: Add / Edit Menu
	 * @var array
	 */
	public $inputFields = array (
		array('label' => 'Menu Title', 'id' => 'jform_title', 'type' => 'input', 'tab' => 'details'),
		array('label' => 'Alias', 'id' => 'jform_alias', 'type' => 'input', 'tab' => 'details'),
		array('label' => 'Status', 'id' => 'jform_published', 'type' => 'fieldset', 'tab' => 'details'),
		array('label' => 'Link', 'id' => 'jform_link', 'type' => 'input', 'tab' => 'details'),
		array('label' => 'Menu Location', 'id' => 'jform_menutype', 'type' => 'select', 'tab' => 'details'),
		array('label' => 'Parent Item', 'id' => 'jform_parent_id', 'type' => 'select', 'tab' => 'details'),
		array('label' => 'Access', 'id' => 'jform_access', 'type' => 'select', 'tab' => 'details'),
		array('label' => 'Default Page', 'id' => 'jform_home', 'type' => 'fieldset', 'tab' => 'details'),
		array('label' => 'Target Window', 'id' => 'jform_browserNav', 'type' => 'select', 'tab' => 'details'),
		array('label' => 'Template Style', 'id' => 'jform_template_style_id', 'type' => 'select', 'tab' => 'details'),
		array('label' => 'Language', 'id' => 'jform_language', 'type' => 'select', 'tab' => 'details'),
		array('label' => 'Note', 'id' => 'jform_note', 'type' => 'input', 'tab' => 'details'),
		array('label' => 'ID', 'id' => 'jform_id', 'type' => 'input', 'tab' => 'details'),
		array('label' => 'Link Title Attribute', 'id' => 'jform_params_menu_anchor_title', 'type' => 'input', 'tab' => 'options'),
		array('label' => 'Link CSS Style', 'id' => 'jform_params_menu_anchor_css', 'type' => 'input', 'tab' => 'options'),
		array('label' => 'Link Image', 'id' => 'jform_params_menu_image', 'type' => 'input', 'tab' => 'options'),
		array('label' => 'Add Menu Title', 'id' => 'jform_params_menu_text', 'type' => 'fieldset', 'tab' => 'options'),
		array('label' => 'Browser Page Title', 'id' => 'jform_params_page_title', 'type' => 'input', 'tab' => 'options'),
		array('label' => 'Show Page Heading', 'id' => 'jform_params_show_page_heading', 'type' => 'fieldset', 'tab' => 'options'),
		array('label' => 'Page Heading', 'id' => 'jform_params_page_heading', 'type' => 'input', 'tab' => 'options'),
		array('label' => 'Page Class', 'id' => 'jform_params_pageclass_sfx', 'type' => 'input', 'tab' => 'options'),
		array('label' => 'Meta Description', 'id' => 'jform_params_menu_meta_description', 'type' => 'textarea', 'tab' => 'options'),
		array('label' => 'Meta Keywords', 'id' => 'jform_params_menu_meta_keywords', 'type' => 'textarea', 'tab' => 'options'),
		array('label' => 'Robots', 'id' => 'jform_params_robots', 'type' => 'select', 'tab' => 'options'),
		array('label' => 'Secure', 'id' => 'jform_params_secure', 'type' => 'select', 'tab' => 'options'),
		array('label' => 'Hide Unassigned Modules', 'id' => 'showmods', 'type' => 'input', 'tab' => 'modules'),
			);

	public $menuItemTypes = array(
		array('group' => 'Contacts', 'type' => 'List All Contact Categories '),
		array('group' => 'Contacts', 'type' => 'List Contacts in a Category '),
		array('group' => 'Contacts', 'type' => 'Single Contact '),
		array('group' => 'Contacts', 'type' => 'Featured Contacts '),
		array('group' => 'Articles', 'type' => 'Archived Articles '),
		array('group' => 'Articles', 'type' => 'Single Article '),
		array('group' => 'Articles', 'type' => 'List All Categories '),
		array('group' => 'Articles', 'type' => 'Category Blog '),
		array('group' => 'Articles', 'type' => 'Category List '),
		array('group' => 'Articles', 'type' => 'Featured Articles '),
		array('group' => 'Articles', 'type' => 'Create Article '),
		array('group' => 'Smart Search', 'type' => 'Search '),
		array('group' => 'Newsfeeds', 'type' => 'List All News Feed Categories '),
		array('group' => 'Newsfeeds', 'type' => 'List News Feeds in a Category '),
		array('group' => 'Newsfeeds', 'type' => 'Single News Feed '),
		array('group' => 'Search', 'type' => 'Search Form or Search Results '),
		array('group' => 'Tags', 'type' => 'Tagged Items '),
		array('group' => 'Tags', 'type' => 'Compact list of tagged items '),
		array('group' => 'Tags', 'type' => 'List of all tags '),
		array('group' => 'Users Manager', 'type' => 'Login Form '),
		array('group' => 'Users Manager', 'type' => 'User Profile '),
		array('group' => 'Users Manager', 'type' => 'Edit User Profile '),
		array('group' => 'Users Manager', 'type' => 'Registration Form '),
		array('group' => 'Users Manager', 'type' => 'Username Reminder Request '),
		array('group' => 'Users Manager', 'type' => 'Password Reset '),
		array('group' => 'Weblinks', 'type' => 'List All Web Link Categories '),
		array('group' => 'Weblinks', 'type' => 'List Web Links in a Category '),
		array('group' => 'Weblinks', 'type' => 'Submit a Web Link '),
		array('group' => 'Wrapper', 'type' => 'Iframe Wrapper '),
		array('group' => 'System Links', 'type' => 'External URL '),
		array('group' => 'System Links', 'type' => 'Menu Item Alias '),
		array('group' => 'System Links', 'type' => 'Text Separator '),
		array('group' => 'System Links', 'type' => 'Menu Heading ' ),
		);


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

	protected function getGroupName($value)
	{
		foreach ($this->menuItemTypes as $array)
		{
			if (strpos($array['type'], $value) !== false)
				return $array['group'];
		}
		return false;
	}

	public function getMenuItemType()
	{
		return $this->driver->findElement(By::xPath("//label[@id='jform_type-lbl']/../..//input"))->getAttribute('value');
	}

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

	public function getRequestVariable()
	{
		return $this->driver->findElement(By::id('jform_request_id_name'))->getAttribute('value');
	}

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

	public function setMenuItemType($value)
	{
		$group = $this->getGroupName($value);
		$d = $this->driver;
		$d->findElement(By::xPath("//a[contains(@onclick, 'option=com_menus&view=menutypes')]"))->click();
		$el = $d->waitForElementUntilIsPresent(By::xPath("//iframe[contains(@src, 'option=com_menus&view=menutypes')]"));
		$el = $d->switchTo()->getFrameByWebElement($el);
		$el->findElement(By::xPath("//a[contains(@class, 'accordion-toggle')][contains(., '" . $group . "')]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//div[contains(@class, 'accordion-body in')]/div/ul/li/a"));
		$el->findElement(By::xPath("//div[contains(@class, 'accordion-body in')]//a[contains(text(), '" . $value . "')]"))->click();
		$d->waitForElementUntilIsNotPresent(By::xPath("//iframe[contains(@src, 'option=com_menus&view=menutypes')]"));
		$d->waitForElementUntilIsPresent(By::id('jform_title'));
		$d->switchTo()->getDefaultFrame();
		return $this;
	}

	public function setRequestVariable($value)
	{
		$this->selectTab('Details');
		$d = $this->driver;
		$d->findElement(By::xPath("//a[@class = 'modal btn'][contains(@rel, 'iframe')]"))->click();
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