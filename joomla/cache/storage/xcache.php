<?php
/**
 * @version		$id:$
 * @package		Joomla.Framework
 * @subpackage	Cache
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * XCache cache storage handler
 *
 * @package		Joomla.Framework
 * @subpackage	Cache
 * @since		1.5
 */
class JCacheStorageXcache extends JCacheStorage
{
	/**
	* Constructor
	*
	* @param array $options optional parameters
	*/
	public function __construct($options = array())
	{
		parent::__construct($options);
	}

	/**
	 * Get cached data by id and group
	 *
	 * @param	string	$id			The cache data id
	 * @param	string	$group		The cache data group
	 * @param	boolean	$checkTime	True to verify cache time expiration threshold
	 * @return	mixed	Boolean false on failure or a cached data string
	 * @since	1.5
	 */
	public function get($id, $group, $checkTime)
	{
		$cache_id = $this->_getCacheId($id, $group);
		$cache_content = xcache_get($cache_id);
		if ($cache_content === null)
		{
			return false;
		}

		return $cache_content;
	}


	 /**
	 * Get all cached data
	 *
	 *  requires the php.ini setting xcache.admin.enable_auth = Off
	 *
	 * @return	array data
	 * @since	1.6
	 */
	public function getAll()
	{
		parent::getAll();

		$allinfo = xcache_list(XC_TYPE_VAR, 0);
		$keys = $allinfo['cache_list'];
        $secret = $this->_hash;

        $data = array();

		foreach ($keys as $key) {

			$namearr=explode('-',$key['name']);

			if ($namearr !== false && $namearr[0]==$secret &&  $namearr[1]=='cache') {

			$group = $namearr[2];

			if (!isset($data[$group])) {
			$item = new JCacheStorageHelper();
			} else {
			$item = $data[$group];
			}

			$item->updateSize($key['size']/1024,$group);

			$data[$group] = $item;

			}
		}


		return $data;
	}
	/**
	 * Store the data by id and group
	 *
	 * @param	string	$id		The cache data id
	 * @param	string	$group	The cache data group
	 * @param	string	$data	The data to store in cache
	 * @return	boolean	True on success, false otherwise
	 * @since	1.5
	 */
	public function store($id, $group, $data)
	{
		$cache_id = $this->_getCacheId($id, $group);
		$store = xcache_set($cache_id, $data, $this->_lifetime);
		return $store;
	}

	/**
	 * Remove a cached data entry by id and group
	 *
	 * @param	string	$id		The cache data id
	 * @param	string	$group	The cache data group
	 * @return	boolean	True on success, false otherwise
	 * @since	1.5
	 */
	public function remove($id, $group)
	{
		$cache_id = $this->_getCacheId($id, $group);

		if (!xcache_isset($cache_id)){
			return true;
		}

		return xcache_unset($cache_id);
	}

	/**
	 * Clean cache for a group given a mode.
	 *
	 * requires the php.ini setting xcache.admin.enable_auth = Off
	 *
	 * group mode		: cleans all cache in the group
	 * notgroup mode	: cleans all cache not in the group
	 *
	 * @param	string	$group	The cache data group
	 * @param	string	$mode	The mode for cleaning cache [group|notgroup]
	 * @return	boolean	True on success, false otherwise
	 * @since	1.5
	 */
	public function clean($group, $mode)
	{
		$allinfo = xcache_list(XC_TYPE_VAR, 0);
		$keys = $allinfo['cache_list'];

        $secret = $this->_hash;
        foreach ($keys as $key) {

        if (strpos($key['name'], $secret.'-cache-'.$group.'-')===0 xor $mode != 'group')
					xcache_unset($key['name']);
        }
		return true;
	}

	/**
	 * Garbage collect expired cache data
	 *
	 * @return boolean  True on success, false otherwise.
	 * * @since	1.6
	 */
	public function gc()
	{
		// dummy, xcache has builtin garbage collector, turn it on in php.ini by changing default xcache.gc_interval setting from 0 to 3600 (=1 hour)

		/**
		$now = time();

		$cachecount = xcache_count(XC_TYPE_VAR);

			for ($i = 0; $i < $cachecount; $i ++) {

				$allinfo  = xcache_list(XC_TYPE_VAR, $i);
				$keys = $allinfo ['cache_list'];

				foreach($keys as $key) {

					if(strstr($key['name'], $this->_hash)) {
						if(($key['ctime'] + $this->_lifetime ) < $this->_now) xcache_unset($key['name']);
					}
				}
			}

		 */

		return true;
	}

	/**
	 * Test to see if the cache storage is available.
	 *
	 * @return boolean  True on success, false otherwise.
	 */
	public static function test()
	{
		return (extension_loaded('xcache'));
	}
}
