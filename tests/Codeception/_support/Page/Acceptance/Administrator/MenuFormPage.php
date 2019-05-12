<?php
/**
 * @package     Joomla.Test
 * @subpackage  AcceptanceTester.Page
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Page\Acceptance\Administrator;

/**
 * Acceptance Page object for menu form pages.
 *
 * @package  Page\Acceptance\Administrator
 *
 * @since    __DEPLOY_VERSION__
 */
class MenuFormPage extends AdminFormPage
{
	/**
	 * Url to menu create page.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public static $url = "administrator/index.php?option=com_menus&view=menu&layout=edit";

	/**
	 * Page title for adding a menu
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public static $pageTitleText = 'Menus: Add';

	/**
	 * Title field
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $fieldTitle = ['id' => 'jform_title'];

	/**
	 * Type field
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $fieldMenuType = ['id' => 'jform_menutype'];

	/**
	 * Optional Description field
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $fieldMenuDescription = ['id' => 'jform_menudescription'];

}
