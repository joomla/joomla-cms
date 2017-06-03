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
 * Acceptance Page object class to define Users Viewing Access Levels view page objects.
 *
 * @package  Page\Acceptance\Administrator
 *
 * @since    __DEPLOY_VERSION__
 */
class UserAclPage extends AdminPage
{
	/**
	 * Url to Users Viewing Access Levels listing page
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public static $url = 'administrator/index.php?option=com_users&view=levels';

	/**
	 * Locator for user's acl name field
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $seeName = ['xpath' => "//table[@id='levelList']//tr[1]/td[3]"];
}
