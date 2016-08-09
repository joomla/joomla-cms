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
 * Acceptance Page object class to define Frontend Login page objects.
 *
 * @package  Page\Acceptance\Site
 *
 * @since    __DEPLOY_VERSION__
 */
class FrontendLogin extends FrontPage
{
	/**
	 * Link for user's profile page in frontend
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public static $profile = '/index.php?option=com_users&view=profile';

	/**
	 * Locator for username input field in frontend login module.
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $moduleUsername = ['id' => 'modlgn-username'];

	/**
	 * Locator for password input field in frontend login module.
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $modulePassword = ['id' => 'modlgn-passwd'];

	/**
	 * Locator for title field in frontend login module.
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $moduleTitle = ['xpath' => ".//*[@id='aside']/div[2]/h3"];
}
