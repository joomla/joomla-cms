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
 * Acceptance Page object class to define administrator page objects.
 *
 * @package  Page\Acceptance\Administrator
 *
 * @since    __DEPLOY_VERSION__
 */
class AdminPage extends \AcceptanceTester
{
	/**
	 * The element id which contains system messages.
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $systemMessageContainer = ['id' => 'system-message-container'];

	/**
	 * The element id which contains page title in administrator header.
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $pageTitle = ['class' => 'page-title'];
}
