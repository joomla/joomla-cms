<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * APC cache storage handler
 *
 * @see    https://secure.php.net/manual/en/book.apc.php
 * @since  11.1
 */
class JCacheStorageApc extends JCacheStorage
{
	/**
	 * Check if the cache contains data stored by ID and group
	 *
	 * @param   string  $id     The cache data ID
	 * @param   string  $group  The cache data group
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function contains($id, $group)
	{
		return apc_exists($this->_getCacheId($id, $group));
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
	 * @since   11.1
	 */
	public function get($id, $group, $checkTime = true)
	{
		return apc_fetch($this->_getCacheId($id, $group));
	}

	/**
	 * Get all cached data
	 *
	 * @return  mixed  Boolean false on failure or a cached data object
	 *
	 * @since   11.1
	 */
	public function getAll()
	{
		$allinfo = apc_cache_info('user');
		$keys    = $allinfo['cache_list'];
		$secret  = $this->_hash;

		$data = array();

		foreach ($keys as $key)
		{
			if (isset($key['info']))
			{
				// If APCu is being used for this adapter, the internal key name changed with APCu 4.0.7 from key to info
				$name = $key['info'];
			}
			elseif (isset($key['entry_name']))
			{
				// Some APC modules changed the internal key name from key to entry_name, HHVM is one such case
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
					$item = new JCacheStorageHelper($group);
				}
				else
				{
					$item = $data[$group];
				}

				$item->updateSize($key['mem_size'] / 1024);

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
	 * @since   11.1
	 */
	public function store($id, $group, $data)
	{
		return apc_store($this->_getCacheId($id, $group), $data, $this->_lifetime);
	}

	/**
	 * Remove a cached data entry by ID and group
	 *
	 * @param   string  $id     The cache data ID
	 * @param   string  $group  The cache data group
	 *
	 * @return  boolean
	 *
	 * @since   11.1
	 */
	public function remove($id, $group)
	{
		return apc_delete($this->_getCacheId($id, $group));
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
	 * @since   11.1
	 */
	public function clean($group, $mode = null)
	{
		$allinfo = apc_cache_info('user');
		$keys    = $allinfo['cache_list'];
		$secret  = $this->_hash;

		foreach ($keys as $key)
		{
			if (isset($key['info']))
			{
				// If APCu is being used for this adapter, the internal key name changed with APCu 4.0.7 from key to info
				$internalKey = $key['info'];
			}
			elseif (isset($key['entry_name']))
			{
				// Some APC modules changed the internal key name from key to entry_name, HHVM is one such case
				$internalKey = $key['entry_name'];
			}
			else
			{
				// A fall back for the old internal key name
				$internalKey = $key['key'];
			}

			if (strpos($internalKey, $secret . '-cache-' . $group . '-') === 0 xor $mode != 'group')
			{
				apc_delete($internalKey);
			}
		}

		return true;
	}

	/**
	 * Garbage collect expired cache data
	 *
	 * @return  boolean
	 *
	 * @since   11.1
	 */
	public function gc()
	{
		$allinfo = apc_cache_info('user');
		$keys    = $allinfo['cache_list'];
		$secret  = $this->_hash;

		foreach ($keys as $key)
		{
			if (isset($key['info']))
			{
				// If APCu is being used for this adapter, the internal key name changed with APCu 4.0.7 from key to info
				$internalKey = $key['info'];
			}
			elseif (isset($key['entry_name']))
			{
				// Some APC modules changed the internal key name from key to entry_name, HHVM is one such case
				$internalKey = $key['entry_name'];
			}
			else
			{
				// A fall back for the old internal key name
				$internalKey = $key['key'];
			}

			if (strpos($internalKey, $secret . '-cache-'))
			{
				apc_fetch($internalKey);
			}
		}
	}

	/**
	 * Test to see if the storage handler is available.
	 *
	 * @return  boolean
	 *
	 * @since   12.1
	 */
	public static function isSupported()
	{
		$supported = extension_loaded('apc') && ini_get('apc.enabled');

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
	 * @since   11.1
	 */
	public function lock($id, $group, $locktime)
	{
		$returning             = new stdClass;
		$returning->locklooped = false;

		$looptime = $locktime * 10;

		$cache_id = $this->_getCacheId($id, $group) . '_lock';

		$data_lock = apc_add($cache_id, 1, $locktime);

		if ($data_lock === false)
		{
			$lock_counter = 0;

			// Loop until you find that the lock has been released. That implies that data get from other thread has finished
			while ($data_lock === false)
			{
				if ($lock_counter > $looptime)
				{
					$returning->locked     = false;
					$returning->locklooped = true;
					break;
				}

				usleep(100);
				$data_lock = apc_add($cache_id, 1, $locktime);
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
	 * @since   11.1
	 */
	public function unlock($id, $group = null)
	{
		return apc_delete($this->_getCacheId($id, $group) . '_lock');
	}
}
