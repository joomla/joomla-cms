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
 * Cache Model
 *
 * @package     Joomla.Administrator
 * @subpackage  com_cache
 * @since       3.2
 */
class CacheModelPurge extends CacheModelCache
{
	/**
	 * Garbage collect all cache files found
	 *
	 * @param  array  $array  The array of groups to clean
	 *
	 * @since  3.2
	 */
	public function purge()
	{
		$cache = JFactory::getCache('');
		return $cache->gc();
	}
}
