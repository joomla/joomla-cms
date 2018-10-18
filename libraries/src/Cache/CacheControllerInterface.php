<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Cache;

defined('_JEXEC') or die;

/**
 * The cache controller interface.
 *
 * @since  __DEPLOY_VERSION__
 */
interface CacheControllerInterface
{
	/**
	 * Get stored cached data by ID and group.
	 *
	 * @param   string  $id     The cache data ID
	 * @param   string  $group  The cache data group
	 *
	 * @return  mixed  Boolean false on no result, cached object otherwise
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function get($id, $group = null);

	/**
	 * Store data to cache by ID and group.
	 *
	 * @param   mixed    $data        The data to store
	 * @param   string   $id          The cache data ID
	 * @param   string   $group       The cache data group
	 * @param   boolean  $wrkarounds  True to use wrkarounds
	 *
	 * @return  boolean  True if cache stored
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function store($data, $id, $group = null, $wrkarounds = true);
}
