<?php
/**
 * @package     Joomla.Test
 * @subpackage  AcceptanceTester.Page
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Page\Acceptance\Administrator;

/**
 * Acceptance Page object class to define menu manager page objects.
 *
 * @package  Page\Acceptance\Administrator
 *
 * @since    __DEPLOY_VERSION__
 */
class MenuManagerPage extends AdminPage
{
	/**
	 * Link to the article category listing url.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public static $url = 'administrator/index.php?option=com_menus&view=items&menutype=mainmenu';

	/**
	 * Locator for menu item name field
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $seeName = ['xpath' => "//table[@id='itemList']//tr[1]//td[4]"];

	/**
	 * Locator for select article for menu item
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $selectArticle = ['class' => 'icon-file'];

	/**
	 * Locator to choose article title
	 * Must be initialized using MenuManagerPage::setChooseArticle($text) method.
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $chooseArticle = [];

	/**
	 * Locator for article link for menu item
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $article = ['link' => 'Article'];

	/**
	 * This method is to set page object to choose an article dynamically.
	 *
	 * @param   string  $text  Text of the link to be choosen.
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public static function setChooseArticle($text)
	{
		self::$chooseArticle = ['link' => $text];
	}

	/**
	 * Prepare menu item creation by choosing menu and adding title.
	 *
	 * @param   string  $title  The Menu Item title
	 * @param   string  $menu   The menu in which menu item will be created.
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function prepareMenuItemCreate($title, $menu = 'Main Menu')
	{
		$I = $this;

		$I->amOnPage('administrator/index.php?option=com_menus&view=menus');
		$I->waitForText('Menus', '60', ['css' => 'H1']);
		$I->checkForPhpNoticesOrWarnings();

		$I->click(['link' => $menu]);
		$I->waitForText('Menus: Items', '60', ['css' => 'H1']);
		$I->checkForPhpNoticesOrWarnings();

		$I->click("New");
		$I->waitForText('Menus: New Item', '60', ['css' => 'h1']);
		$I->checkForPhpNoticesOrWarnings();
		$I->fillField(self::$title, $title);
	}

	/**
	 * Creates a menu item with the Joomla menu manager, only working for menu items without additional required fields
	 *
	 * @param   string  $menuCategory  The category of the menu type (for example Weblinks)
	 * @param   string  $menuItem      The menu item type / link text (for example List all Web Link Categories)
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function selectMenuItemType($menuCategory, $menuItem)
	{
		$I = $this;

		$I->comment("Open the menu types iframe");
		$I->click(['link' => "Select"]);
		$I->waitForElement(['id' => 'menuTypeModal'], '60');
		$I->wait(1);
		$I->switchToIFrame("Menu Item Type");

		$I->comment("Open the menu category: $menuCategory");

		// Open the category
		$I->wait(1);
		$I->waitForElement(['link' => $menuCategory], '60');
		$I->click(['link' => $menuCategory]);

		$I->comment("Choose the menu item type: $menuItem");
		$I->wait(1);
		$I->waitForElement(['xpath' => "//a[contains(text()[normalize-space()], '$menuItem')]"], '60');
		$I->click(['xpath' => "//div[@id='collapseTypes']//a[contains(text()[normalize-space()], '$menuItem')]"]);
		$I->comment('I switch back to the main window');
	}
}
