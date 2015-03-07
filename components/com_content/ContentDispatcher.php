<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use JoomlaCMS\Component\AbstractDispatcher;

/**
 * Component dispatcher for frontend com_content component
 */
class ContentDispatcher extends AbstractDispatcher
{
	/**
	 * {@inheritdoc}
	 */
	public function boot()
	{
		require_once JPATH_COMPONENT . '/helpers/route.php';
		require_once JPATH_COMPONENT . '/helpers/query.php';
	}
}
