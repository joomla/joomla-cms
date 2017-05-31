<?php
/**
 * Part of the Joomla Framework Cache Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cache\Adapter;

use Joomla\Cache\AbstractCacheItemPool;
use Joomla\Cache\Item\HasExpirationDateInterface;
use Joomla\Cache\Item\Item;
use Psr\Cache\CacheItemInterface;

/**
 * XCache cache driver for the Joomla Framework.
 *
 * @since  1.0
 */
class XCache extends AbstractCacheItemPool
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
		$item = new Item($key);

		if ($this->hasItem($key))
		{
			$item->set(xcache_get($key));
		}

		return $item;
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
		if ($this->hasItem($key))
		{
			return xcache_unset($key);
		}

		// If the item doesn't exist, no error
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
		// If we are able to find out when the item expires - find out. Else bail.
		if ($item instanceof HasExpirationDateInterface)
		{
			$ttl = $this->convertItemExpiryToSeconds($item);
		}
		else
		{
			$ttl = 0;
		}

		return xcache_set($item->getKey(), $item->get(), $ttl);
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
		return xcache_isset($key);
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
		// XCache is not supported on CLI
		return extension_loaded('xcache') && php_sapi_name() != 'cli';
	}
}
