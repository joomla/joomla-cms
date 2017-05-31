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
 * APCu cache driver for the Joomla Framework.
 *
 * @since  __DEPLOY_VERSION__
 */
class Apcu extends AbstractCacheItemPool
{
	/**
	 * This will wipe out the entire cache's keys
	 *
	 * @return  boolean  True if the pool was successfully cleared. False if there was an error.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function clear()
	{
		return apcu_clear_cache();
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
		$success = false;
		$value = apcu_fetch($key, $success);
		$item = new Item($key);

		if ($success)
		{
			$item->set($value);
		}

		return $item;
	}

	/**
	 * Returns a traversable set of cache items.
	 *
	 * @param   string[]  $keys  An indexed array of keys of items to retrieve.
	 *
	 * @return  array  A traversable collection of Cache Items keyed by the cache keys of each item.
	 *                 A Cache item will be returned for each key, even if that key is not found.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getItems(array $keys = [])
	{
		$items   = [];
		$success = false;
		$values  = apcu_fetch($keys, $success);

		if ($success && is_array($values))
		{
			foreach ($keys as $key)
			{
				$items[$key] = new Item($key);

				if (isset($values[$key]))
				{
					$items[$key]->set($values[$key]);
				}
			}
		}

		return $items;
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
			return apcu_delete($key);
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

		return apcu_store($item->getKey(), $item->get(), $ttl);
	}

	/**
	 * Confirms if the cache contains specified cache item.
	 *
	 * @param   string  $key  The key for which to check existence.
	 *
	 * @return  boolean  True if item exists in the cache, false otherwise.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function hasItem($key)
	{
		return apcu_exists($key);
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
		$supported = extension_loaded('apcu') && ini_get('apc.enabled');

		// If on the CLI interface, the `apc.enable_cli` option must also be enabled
		if ($supported && php_sapi_name() === 'cli')
		{
			$supported = ini_get('apc.enable_cli');
		}

		return (bool) $supported;
	}
}
