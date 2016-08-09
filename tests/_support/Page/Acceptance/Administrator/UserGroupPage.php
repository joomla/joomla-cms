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
 * Acceptance Page object class to define Users Groups view page objects.
 *
 * @package  Page\Acceptance\Administrator
 *
 * @since    __DEPLOY_VERSION__
 */
class UserGroupPage extends AdminPage
{
	/**
	 * Url to Users Groups listing page
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public static $url = 'administrator/index.php?option=com_users&view=groups';
}
