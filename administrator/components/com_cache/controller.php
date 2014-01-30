<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cache
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Cache Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_cache
 * @since       1.6
 * @deprecated  4.0
 */
class CacheController extends JControllerLegacy
{
	public function display()
	{
		include_once JPATH_ADMINISTRATOR . '/components/com_cache/cache/controller/display.php';
		$controller = new CacheControllerCacheDisplay;

		return $controller->execute();
	}

	public function delete()
	{
		include_once JPATH_ADMINISTRATOR . '/components/com_cache/cache/controller/cleanlist.php';
		$controller = new CacheControllerCacheCleanlist;

		return $controller->execute();
	}

	public function purge()
	{
		include_once JPATH_ADMINISTRATOR . '/components/com_cache/cache/controller/cleanlist.php';
		$controller = new CacheControllerCacheCleanlist;

		return $controller->execute();
	}
}
