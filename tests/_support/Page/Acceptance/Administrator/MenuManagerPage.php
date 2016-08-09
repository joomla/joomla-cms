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
	public static $url = 'administrator/index.php?option=com_menus&view=item&layout=edit&menutype=mainmenu';

	/**
	 * Locator for select article for menu item
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $selectArticle = ['class' => 'icon-file'];

	/**
	 * Locator to choose article title
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $chooseArticle = ['link' => 'Test_article'];

	/**
	 * Locator for article link for menu item
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $article = ['link' => 'Article'];
}
