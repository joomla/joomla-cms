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
 * WinCache cache storage handler
 *
 * @see    https://secure.php.net/manual/en/book.wincache.php
 * @since  11.1
 */
class JCacheStorageWincache extends JCacheStorage
{
	/**
	 * Flag to indicate whether storage support raw, not serialized data.
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected static $supportRawData = true;

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
		return wincache_ucache_exists($this->_getCacheId($id, $group));
	}

	/**
	 * Get cached data by ID and group
	 *
	 * @param   string   $id         The cache data ID
	 * @param   string   $group      The cache data group
	 * @param   boolean  $checkTime  True to verify cache time expiration threshold
	 * @param   boolean  $rawData    If true then method returns unserialized data
	 *
	 * @return  mixed  Boolean false on failure or a cached data object
	 *
	 * @since   11.1
	 */
	public function get($id, $group, $checkTime = true, $rawData = false)
	{
		$data = wincache_ucache_get($this->_getCacheId($id, $group));

		if ($rawData)
		{
			return $data !== false ? unserialize($data) : false;
		}

		return $data;
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
		$allinfo = wincache_ucache_info();
		$keys    = $allinfo['ucache_entries'];
		$secret  = $this->_hash;
		$data    = array();

		foreach ($keys as $key)
		{
			$name    = $key['key_name'];
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

				if (isset($key['value_size']))
				{
					$item->updateSize($key['value_size'] / 1024);
				}
				else
				{
					// Dummy, WINCACHE version is too low.
					$item->updateSize(1);
				}

				$data[$group] = $item;
			}
		}

		return $data;
	}

	/**
	 * Store the data to cache by ID and group
	 *
	 * @param   string   $id       The cache data ID
	 * @param   string   $group    The cache data group
	 * @param   string   $data     The data to store in cache
	 * @param   boolean  $rawData  If true then method treats $data as unserialized
	 *
	 * @return  boolean
	 *
	 * @since   11.1
	 */
	public function store($id, $group, $data, $rawData = false)
	{
		if ($rawData)
		{
			$data = serialize($data);
		}

		return wincache_ucache_set($this->_getCacheId($id, $group), $data, $this->_lifetime);
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
		return wincache_ucache_delete($this->_getCacheId($id, $group));
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
		$allinfo = wincache_ucache_info();
		$keys    = $allinfo['ucache_entries'];
		$secret  = $this->_hash;

		foreach ($keys as $key)
		{
			if (strpos($key['key_name'], $secret . '-cache-' . $group . '-') === 0 xor $mode != 'group')
			{
				wincache_ucache_delete($key['key_name']);
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
		$allinfo = wincache_ucache_info();
		$keys    = $allinfo['ucache_entries'];
		$secret  = $this->_hash;

		foreach ($keys as $key)
		{
			if (strpos($key['key_name'], $secret . '-cache-'))
			{
				wincache_ucache_get($key['key_name']);
			}
		}

		return true;
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
		return extension_loaded('wincache') && function_exists('wincache_ucache_get') && !strcmp(ini_get('wincache.ucenabled'), '1');
	}
}
