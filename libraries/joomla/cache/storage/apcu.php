<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * APCu cache storage handler
 *
 * @see    http://php.net/manual/en/ref.apcu.php
 * @since  3.5
 */
class JCacheStorageApcu extends JCacheStorage
{
	/**
	 * Get cached data from APCu by id and group
	 *
	 * @param   string   $id         The cache data id
	 * @param   string   $group      The cache data group
	 * @param   boolean  $checkTime  True to verify cache time expiration threshold
	 *
	 * @return  mixed    Boolean     False on failure or a cached data string
	 *
	 * @since   3.5
	 */
	public function get($id, $group, $checkTime = true)
	{
		$cache_id = $this->_getCacheId($id, $group);

		return apcu_fetch($cache_id);
	}

	/**
	 * Get all cached data
	 *
	 * @return  array
	 *
	 * @since   3.5
	 */
	public function getAll()
	{
		parent::getAll();

		$allinfo = apcu_cache_info();
		$keys    = $allinfo['cache_list'];
		$secret  = $this->_hash;

		$data = array();

		foreach ($keys as $key)
		{
			// The internal key name changed with APCu 4.0.7 from key to info
			$name = isset($key['info']) ? $key['info'] : $key['key'];
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
	 * Store the data to APCu by id and group
	 *
	 * @param   string  $id     The cache data id
	 * @param   string  $group  The cache data group
	 * @param   string  $data   The data to store in cache
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since   3.5
	 */
	public function store($id, $group, $data)
	{
		$cache_id = $this->_getCacheId($id, $group);

		return apcu_store($cache_id, $data, $this->_lifetime);
	}

	/**
	 * Remove a cached data entry by id and group
	 *
	 * @param   string  $id     The cache data id
	 * @param   string  $group  The cache data group
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since   3.5
	 */
	public function remove($id, $group)
	{
		$cache_id = $this->_getCacheId($id, $group);

		// The apcu_delete function returns false if the id does not exist
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
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since   3.5
	 */
	public function clean($group, $mode = null)
	{
		$allinfo = apcu_cache_info();
		$keys = $allinfo['cache_list'];
		$secret = $this->_hash;

		foreach ($keys as $key)
		{
			// The internal key name changed with APCu 4.0.7 from key to info
			$internalKey = isset($key['info']) ? $key['info'] : $key['key'];

			if (strpos($internalKey, $secret . '-cache-' . $group . '-') === 0 xor $mode != 'group')
			{
				apcu_delete($internalKey);
			}
		}

		return true;
	}

	/**
	 * Force garbage collect expired cache data as items are removed only on fetch!
	 *
	 * @return  boolean  True on success, false otherwise.
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
			// The internal key name changed with APCu 4.0.7 from key to info
			$internalKey = isset($key['info']) ? $key['info'] : $key['key'];

			if (strpos($internalKey, $secret . '-cache-'))
			{
				apcu_fetch($internalKey);
			}
		}

		return true;
	}

	/**
	 * Test to see if the cache storage is available.
	 *
	 * @return  boolean  True on success, false otherwise.
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
	 * Lock cached item - override parent as this is more efficient
	 *
	 * @param   string   $id        The cache data id
	 * @param   string   $group     The cache data group
	 * @param   integer  $locktime  Cached item max lock time
	 *
	 * @return  stdClass   Properties are lock and locklooped
	 *
	 * @since   3.5
	 */
	public function lock($id, $group, $locktime)
	{
		$returning = new stdClass;
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
	 * Unlock cached item - override parent for cacheid compatibility with lock
	 *
	 * @param   string  $id     The cache data id
	 * @param   string  $group  The cache data group
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   3.5
	 */
	public function unlock($id, $group = null)
	{
		$cache_id = $this->_getCacheId($id, $group) . '_lock';

		// The apcu_delete function returns false if the id does not exist
		if (apcu_exists($cache_id))
		{
			return apcu_delete($cache_id);
		}

		return true;
	}
}
