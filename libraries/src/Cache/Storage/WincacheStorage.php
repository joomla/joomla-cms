<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Cache\Storage;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Cache\CacheStorage;

/**
 * WinCache cache storage handler
 *
 * @link   https://www.php.net/manual/en/book.wincache.php
 * @since  1.7.0
 */
class WincacheStorage extends CacheStorage
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
		return wincache_ucache_exists($this->_getCacheId($id, $group));
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
	 * @since   1.7.0
	 */
	public function get($id, $group, $checkTime = true)
	{
		return wincache_ucache_get($this->_getCacheId($id, $group));
	}

	/**
	 * Get all cached data
	 *
	 * @return  mixed  Boolean false on failure or a cached data object
	 *
	 * @since   1.7.0
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
					$item = new CacheStorageHelper($group);
				}
				else
				{
					$item = $data[$group];
				}

				if (isset($key['value_size']))
				{
					$item->updateSize($key['value_size']);
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
	 * @param   string  $id     The cache data ID
	 * @param   string  $group  The cache data group
	 * @param   string  $data   The data to store in cache
	 *
	 * @return  boolean
	 *
	 * @since   1.7.0
	 */
	public function store($id, $group, $data)
	{
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
	 * @since   1.7.0
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
	 * @since   1.7.0
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
	 * @since   1.7.0
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
	 * @since   3.0.0
	 */
	public static function isSupported()
	{
		return extension_loaded('wincache') && function_exists('wincache_ucache_get') && !strcmp(ini_get('wincache.ucenabled'), '1');
	}
}
