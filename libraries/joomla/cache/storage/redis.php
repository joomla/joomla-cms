<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Redis cache storage handler for PECL
 *
 * @since  3.4
 */
class JCacheStorageRedis extends JCacheStorage
{
	/**
	 * Get cached data from redis by id and group
	 *
	 * @param   string   $id         The cache data id
	 * @param   string   $group      The cache data group
	 * @param   boolean  $checkTime  True to verify cache time expiration threshold
	 *
	 * @return  mixed  Boolean false on failure or a cached data string
	 *
	 * @since   3.4
	 */
	public function get($id, $group, $checkTime = true)
	{
		$ds       = JFactory::getRedis('cache');
		$cache_id = $this->_getCacheId($id, $group);
		$back     = $ds->get($cache_id);

		return $back;
	}

	/**
	 * Store the data to Redis by id and group
	 *
	 * @param   string  $id     The cache data id
	 * @param   string  $group  The cache data group
	 * @param   string  $data   The data to store in cache
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since   3.4
	 */
	public function store($id, $group, $data)
	{
		$ds = JFactory::getRedis('cache');

		$cache_id = $this->_getCacheId($id, $group);
		$config   = JFactory::getConfig();
		$lifetime = (int) $config->get('cachetime', 15);

		// Lifetime to seconds
		$ds->setex($cache_id, $lifetime * 60, $data);

		return true;
	}

	/**
	 * Remove a cached data entry by id and group
	 *
	 * @param   string  $id     The cache data id
	 * @param   string  $group  The cache data group
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since   3.4
	 */
	public function remove($id, $group)
	{
		$ds       = JFactory::getRedis('cache');
		$cache_id = $this->_getCacheId($id, $group);

		return $ds->delete($cache_id);
	}

	/**
	 * Clean cache for a group given a mode.
	 *
	 * @param   string  $group  The cache data group
	 * @param   string  $mode   The mode for cleaning cache [group|notgroup]
	 *                          group mode : cleans all cache in the group
	 *                          notgroup mode : cleans all cache not in the group
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since   3.4
	 */
	public function clean($group, $mode = null)
	{
		$ds      = JFactory::getRedis('cache');
		$allKeys = $ds->keys('*');

		if ($allKeys === false)
		{
			$allKeys = array();
		}

		$secret = $this->_hash;

		foreach ($allKeys as $key)
		{
			if (strpos($key, $secret . '-cache-' . $group . '-') === 0 && $mode == 'group')
			{
				$ds->delete($key);
			}

			if (strpos($key, $secret . '-cache-' . $group . '-') !== 0 && $mode != 'group')
			{
				$ds->delete($key);
			}
		}

		return true;
	}

	/**
	 * Test to see if the cache storage is available.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   3.4
	 */
	public static function isSupported()
	{
		return (extension_loaded('redis') && class_exists('Redis'));
	}
}
