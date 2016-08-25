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
 * Acceptance Page object class to define category manager page objects.
 *
 * @package  Page\Acceptance\Administrator
 *
 * @since    __DEPLOY_VERSION__
 */
class CategoryManagerPage extends AdminPage
{
	/**
	 * Link to the article category listing url.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public static $url = '/administrator/index.php?option=com_categories&view=categories&extension=com_content';

	/**
	 * Locator for category name field
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $seeName = ['xpath' => "//table[@id='categoryList']//tr[1]//td[4]"];

	/**
	 * Locator for category unpublished icon
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $seeUnpublished = ['xpath' => "//table[@id='categoryList']//*//span[@class='icon-unpublish']"];

	/**
	 * Locator for category access level field
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $seeAccessLevel = ['xpath' => "//table[@id='categoryList']//tr[1]//td[9]"];

	/**
	 * Locator for category language field
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $seeLanguage = ['xpath' => "//table[@id='categoryList']//tr[1]//td[10]"];

	/**
	 * Locator for invalid category alert
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $invalidTitle = ['class' => 'alert-error'];
}
