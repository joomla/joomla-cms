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
 * Acceptance Page object class to media manager page objects.
 *
 * @package  Page\Acceptance\Administrator
 *
 * @since    __DEPLOY_VERSION__
 */
class MediaManagerPage extends AdminPage
{
	/**
	 * Url to media manager listing page.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public static $url = "administrator/index.php?option=com_media&path=local-0:/";

	/**
	 * Page title of the media manager listing page.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public static $pageTitleText = 'Media';

	/**
	 * Powered by Image
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public static $poweredByImage = '//div[contains(@class, \'media-browser-item-info\') and normalize-space(text()) = \'powered_by.png\']';

	/**
	 * Info button
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public static $buttonInfo = ['class' => 'media-toolbar-info'];
}
