<?php
/**
 * @package     Joomla.Test
 * @subpackage  AcceptanceTester.Page
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Page\Acceptance\Site;

/**
 * Acceptance Page object class to define Frontend page objects.
 *
 * @package  Page\Acceptance\Site
 *
 * @since    __DEPLOY_VERSION__
 */
class FrontPage extends \AcceptanceTester
{
	/**
	 * Link to the frontend
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public static $url = '/';

	/**
	 * Locator for alert message in frontend.
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $alertMessage = ['class' => 'alert-message'];

	/**
	 * Locator for login greeting for the user.
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $loginGreeting = ['class' => 'login-greeting'];
}
