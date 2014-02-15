<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * WINCACHE cache storage handler
 *
 * @package     Joomla.Platform
 * @subpackage  Cache
 * @see         http://php.net/manual/en/book.wincache.php
 * @since       11.1
 */
class JCacheStorageWincache extends JCacheStorage
{
	/**
	 * Constructor
	 *
	 * @param   array  $options  Optional parameters.
	 *
	 * @since   11.1
	 */
	public function __construct($options = array())
	{
		parent::__construct($options);
	}

	/**
	 * Get cached data from WINCACHE by id and group
	 *
	 * @param   string   $id         The cache data id
	 * @param   string   $group      The cache data group
	 * @param   boolean  $checkTime  True to verify cache time expiration threshold
	 *
	 * @return  mixed  Boolean false on failure or a cached data string
	 *
	 * @since   11.1
	 */
	public function get($id, $group, $checkTime = true)
	{
		$cache_id = $this->_getCacheId($id, $group);
		$cache_content = wincache_ucache_get($cache_id);
		return $cache_content;
	}

	/**
	 * Get all cached data
	 *
	 * @return  array    data
	 *
	 * @since   11.1
	 */
	public function getAll()
	{
		parent::getAll();

		$allinfo = wincache_ucache_info();
		$keys = $allinfo['cache_entries'];
		$secret = $this->_hash;
		$data = array();

		foreach ($keys as $key)
		{
			$name = $key['key_name'];
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
	 * Store the data to WINCACHE by id and group
	 *
	 * @param   string  $id     The cache data id
	 * @param   string  $group  The cache data group
	 * @param   string  $data   The data to store in cache
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since   11.1
	 */
	public function store($id, $group, $data)
	{
		$cache_id = $this->_getCacheId($id, $group);
		return wincache_ucache_set($cache_id, $data, $this->_lifetime);
	}

	/**
	 * Remove a cached data entry by id and group
	 *
	 * @param   string  $id     The cache data id
	 * @param   string  $group  The cache data group
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since   11.1
	 */
	public function remove($id, $group)
	{
		$cache_id = $this->_getCacheId($id, $group);
		return wincache_ucache_delete($cache_id);
	}

	/**
	 * Clean cache for a group given a mode.
	 *
	 * @param   string  $group  The cache data group
	 * @param   string  $mode   The mode for cleaning cache [group|notgroup]
	 * group mode    : cleans all cache in the group
	 * notgroup mode : cleans all cache not in the group
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since   11.1
	 */
	public function clean($group, $mode = null)
	{
		$allinfo = wincache_ucache_info();
		$keys = $allinfo['cache_entries'];
		$secret = $this->_hash;

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
	 * Force garbage collect expired cache data as items are removed only on get/add/delete/info etc
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   11.1
	 */
	public function gc()
	{
		$allinfo = wincache_ucache_info();
		$keys = $allinfo['cache_entries'];
		$secret = $this->_hash;

		foreach ($keys as $key)
		{
			if (strpos($key['key_name'], $secret . '-cache-'))
			{
				wincache_ucache_get($key['key_name']);
			}
		}
	}

	/**
	 * Test to see if the cache storage is available.
	 *
	 * @return boolean  True on success, false otherwise.
	 *
	 * @since   12.1
	 */
	public static function isSupported()
	{
		$test = extension_loaded('wincache') && function_exists('wincache_ucache_get') && !strcmp(ini_get('wincache.ucenabled'), '1');
		return $test;
	}
}
