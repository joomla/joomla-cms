<?php
/**
 * Part of the Joomla Framework Cache Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cache\Adapter;

use Joomla\Cache\AbstractCacheItemPool;
use Joomla\Cache\Item\Item;
use Psr\Cache\CacheItemInterface;

/**
 * Runtime cache only driver for the Joomla Framework.
 *
 * @since  1.0
 */
class None extends AbstractCacheItemPool
{
	/**
	 * This will wipe out the entire cache's keys
	 *
	 * @return  boolean  True if the pool was successfully cleared. False if there was an error.
	 *
	 * @since   1.0
	 */
	public function clear()
	{
		return true;
	}

	/**
	 * Returns a Cache Item representing the specified key.
	 *
	 * @param   string  $key  The key for which to return the corresponding Cache Item.
	 *
	 * @return  CacheItemInterface  The corresponding Cache Item.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getItem($key)
	{
		return new Item($key);
	}

	/**
	 * Removes the item from the pool.
	 *
	 * @param   string  $key  The key to delete.
	 *
	 * @return  boolean  True if the item was successfully removed. False if there was an error.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function deleteItem($key)
	{
		return true;
	}

	/**
	 * Persists a cache item immediately.
	 *
	 * @param   CacheItemInterface  $item  The cache item to save.
	 *
	 * @return  boolean  True if the item was successfully persisted. False if there was an error.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function save(CacheItemInterface $item)
	{
		return true;
	}

	/**
	 * Confirms if the cache contains specified cache item.
	 *
	 * @param   string  $key  The key for which to check existence.
	 *
	 * @return  boolean  True if item exists in the cache, false otherwise.
	 *
	 * @since   1.0
	 */
	public function hasItem($key)
	{
		return false;
	}

	/**
	 * Fetches a value from the cache.
	 *
	 * @param   string  $key      The unique key of this item in the cache.
	 * @param   mixed   $default  Default value to return if the key does not exist.
	 *
	 * @return  mixed  The value of the item from the cache, or $default in case of cache miss.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function get($key, $default = null)
	{
		return $default;
	}

	/**
	 * Test to see if the CacheItemPoolInterface is available
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function isSupported()
	{
		return true;
	}
}
