<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Cache\Storage;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Cache\CacheStorage;

/**
 * APCu cache storage handler
 *
 * @link   https://secure.php.net/manual/en/ref.apcu.php
 * @since  3.5
 */
class ApcuStorage extends CacheStorage
{
	/**
	 * Check if the cache contains data stored by ID and group
	 *
	 * @param   string  $id     The cache data ID
	 * @param   string  $group  The cache data group
	 *
	 * @return  boolean
	 *
	 * @since   3.7.0
	 */
	public function contains($id, $group)
	{
		return apcu_exists($this->_getCacheId($id, $group));
	}

	/**
	 * Get cached data by ID and group
	 *
	 * @param   string   $id         The cache data ID
	 * @param   string   $group      The cache data group
	 * @param   boolean  $checkTime  True to verify cache time expiration threshold
	 *
	 * @return  mixed  Boolean false on failure or a cached data object
	 *
	 * @since   3.5
	 */
	public function get($id, $group, $checkTime = true)
	{
		return apcu_fetch($this->_getCacheId($id, $group));
	}

	/**
	 * Get all cached data
	 *
	 * @return  mixed  Boolean false on failure or a cached data object
	 *
	 * @since   3.5
	 */
	public function getAll()
	{
		$allinfo = apcu_cache_info();
		$keys    = $allinfo['cache_list'];
		$secret  = $this->_hash;

		$data = array();

		foreach ($keys as $key)
		{
			if (isset($key['info']))
			{
				// The internal key name changed with APCu 4.0.7 from key to info
				$name = $key['info'];
			}
			elseif (isset($key['entry_name']))
			{
				// Some APCu modules changed the internal key name from key to entry_name
				$name = $key['entry_name'];
			}
			else
			{
				// A fall back for the old internal key name
				$name = $key['key'];
			}

			$namearr = explode('-', $name);

			if ($namearr !== false && $namearr[0] == $secret && $namearr[1] == 'cache')
			{
				$group = $namearr[2];

				if (!isset($data[$group]))
				{
					$item = new CacheStorageHelper($group);
				}
				else
				{
					$item = $data[$group];
				}

				$item->updateSize($key['mem_size']);

				$data[$group] = $item;
			}
		}

		return $data;
	}

	/**
	 * Store the data to cache by ID and group
	 *
	 * @param   string  $id     The cache data ID
	 * @param   string  $group  The cache data group
	 * @param   string  $data   The data to store in cache
	 *
	 * @return  boolean
	 *
	 * @since   3.5
	 */
	public function store($id, $group, $data)
	{
		return apcu_store($this->_getCacheId($id, $group), $data, $this->_lifetime);
	}

	/**
	 * Remove a cached data entry by ID and group
	 *
	 * @param   string  $id     The cache data ID
	 * @param   string  $group  The cache data group
	 *
	 * @return  boolean
	 *
	 * @since   3.5
	 */
	public function remove($id, $group)
	{
		$cache_id = $this->_getCacheId($id, $group);

		// The apcu_delete function returns false if the ID does not exist
		if (apcu_exists($cache_id))
		{
			return apcu_delete($cache_id);
		}

		return true;
	}

	/**
	 * Clean cache for a group given a mode.
	 *
	 * group mode    : cleans all cache in the group
	 * notgroup mode : cleans all cache not in the group
	 *
	 * @param   string  $group  The cache data group
	 * @param   string  $mode   The mode for cleaning cache [group|notgroup]
	 *
	 * @return  boolean
	 *
	 * @since   3.5
	 */
	public function clean($group, $mode = null)
	{
		$allinfo = apcu_cache_info();
		$keys    = $allinfo['cache_list'];
		$secret  = $this->_hash;

		foreach ($keys as $key)
		{
			if (isset($key['info']))
			{
				// The internal key name changed with APCu 4.0.7 from key to info
				$internalKey = $key['info'];
			}
			elseif (isset($key['entry_name']))
			{
				// Some APCu modules changed the internal key name from key to entry_name
				$internalKey = $key['entry_name'];
			}
			else
			{
				// A fall back for the old internal key name
				$internalKey = $key['key'];
			}

			if (strpos($internalKey, $secret . '-cache-' . $group . '-') === 0 xor $mode != 'group')
			{
				apcu_delete($internalKey);
			}
		}

		return true;
	}

	/**
	 * Garbage collect expired cache data
	 *
	 * @return  boolean
	 *
	 * @since   3.5
	 */
	public function gc()
	{
		$allinfo = apcu_cache_info();
		$keys    = $allinfo['cache_list'];
		$secret  = $this->_hash;

		foreach ($keys as $key)
		{
			if (isset($key['info']))
			{
				// The internal key name changed with APCu 4.0.7 from key to info
				$internalKey = $key['info'];
			}
			elseif (isset($key['entry_name']))
			{
				// Some APCu modules changed the internal key name from key to entry_name
				$internalKey = $key['entry_name'];
			}
			else
			{
				// A fall back for the old internal key name
				$internalKey = $key['key'];
			}

			if (strpos($internalKey, $secret . '-cache-'))
			{
				apcu_fetch($internalKey);
			}
		}

		return true;
	}

	/**
	 * Test to see if the storage handler is available.
	 *
	 * @return  boolean
	 *
	 * @since   3.5
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

	/**
	 * Lock cached item
	 *
	 * @param   string   $id        The cache data ID
	 * @param   string   $group     The cache data group
	 * @param   integer  $locktime  Cached item max lock time
	 *
	 * @return  mixed  Boolean false if locking failed or an object containing properties lock and locklooped
	 *
	 * @since   3.5
	 */
	public function lock($id, $group, $locktime)
	{
		$returning = new \stdClass;
		$returning->locklooped = false;

		$looptime = $locktime * 10;

		$cache_id = $this->_getCacheId($id, $group) . '_lock';

		$data_lock = apcu_add($cache_id, 1, $locktime);

		if ($data_lock === false)
		{
			$lock_counter = 0;

			// Loop until you find that the lock has been released.
			// That implies that data get from other thread has finished
			while ($data_lock === false)
			{
				if ($lock_counter > $looptime)
				{
					$returning->locked = false;
					$returning->locklooped = true;
					break;
				}

				usleep(100);
				$data_lock = apcu_add($cache_id, 1, $locktime);
				$lock_counter++;
			}
		}

		$returning->locked = $data_lock;

		return $returning;
	}

	/**
	 * Unlock cached item
	 *
	 * @param   string  $id     The cache data ID
	 * @param   string  $group  The cache data group
	 *
	 * @return  boolean
	 *
	 * @since   3.5
	 */
	public function unlock($id, $group = null)
	{
		$cache_id = $this->_getCacheId($id, $group) . '_lock';

		// The apcu_delete function returns false if the ID does not exist
		if (apcu_exists($cache_id))
		{
			return apcu_delete($cache_id);
		}

		return true;
	}
}
