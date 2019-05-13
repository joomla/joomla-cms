<?php
/**
 * @package     Joomla.Test
 * @subpackage  AcceptanceTester.Page
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Page\Acceptance\Administrator;

/**
 * Acceptance Page object class for media manager file page.
 *
 * @package  Page\Acceptance\Administrator
 *
 * @since    __DEPLOY_VERSION__
 */
class MediaFilePage extends AdminFormPage
{
	/**
	 * Url to media manager file page.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public static $url = "administrator/index.php?option=com_media&view=file";
}
