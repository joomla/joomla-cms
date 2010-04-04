<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Cache
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('JPATH_BASE') or die;

/**
 * APC cache storage handler
 *
 * @package		Joomla.Framework
 * @subpackage	Cache
 * @since		1.5
 */
class JCacheStorageApc extends JCacheStorage
{
	/**
	 * Constructor
	 *
	 * @param	array	optional parameters
	 */
	public function __construct($options = array())
	{
		parent::__construct($options);

		$config			= &JFactory::getConfig();
		$this->_hash	= $config->get('secret');
	}

	/**
	 * Get cached data from APC by id and group
	 *
	 * @param	string	The cache data id.
	 * @param	string	The cache data group.
	 * @param	boolean	True to verify cache time expiration threshold.
	 * @return	mixed	Boolean false on failure or a cached data string.
	 * @since	1.5
	 */
	public function get($id, $group, $checkTime)
	{
		$cache_id = $this->_getCacheId($id, $group);
		$this->_setExpire($cache_id);
		return apc_fetch($cache_id);
	}

	/**
	 * Store the data to APC by id and group
	 *
	 * @param	string	The cache data id.
	 * @param	string	The cache data group.
	 * @param	string	The data to store in cache.
	 * @return	boolean	True on success, false otherwise.
	 * @since	1.5
	 */
	public function store($id, $group, $data)
	{
		$cache_id = $this->_getCacheId($id, $group);
		apc_store($cache_id.'_expire', time());
		return apc_store($cache_id, $data, $this->_lifetime);
	}

	/**
	 * Remove a cached data entry by id and group.
	 *
	 * @param	string	The cache data id.
	 * @param	string	The cache data group.
	 * @return	boolean	True on success, false otherwise.
	 * @since	1.5
	 */
	public function remove($id, $group)
	{
		$cache_id = $this->_getCacheId($id, $group);
		apc_delete($cache_id.'_expire');
		return apc_delete($cache_id);
	}

	/**
	 * Clean cache for a group given a mode.
	 *
	 * group mode		: cleans all cache in the group
	 * notgroup mode	: cleans all cache not in the group
	 *
	 * @param	string	The cache data group.
	 * @param	string	The mode for cleaning cache [group|notgroup].
	 * @return	boolean	True on success, false otherwise.
	 * @since	1.5
	 */
	public function clean($group, $mode)
	{
		return true;
	}

	/**
	 * Test to see if the cache storage is available.
	 *
	 * @return	boolean  True on success, false otherwise.
	 */
	public static function test()
	{
		return extension_loaded('apc');
	}

	/**
	 * Set expire time on each call since memcache sets it on cache creation.
	 *
	 * @param	string  Cache key to expire.
	 * @param	integer Lifetime of the data in seconds.
	 */
	protected function _setExpire($key)
	{
		$lifetime	= $this->_lifetime;
		$expire		= apc_fetch($key.'_expire');

		// set prune period
		if ($expire + $lifetime < time()) {
			apc_delete($key);
			apc_delete($key.'_expire');
		} else {
			apc_store($key.'_expire',  time());
		}
	}

	/**
	 * Get a cache_id string from an id/group pair
	 *
	 * @param	string	The cache data id
	 * @param	string	The cache data group
	 * @return	string	The cache_id string
	 * @since	1.5
	 */
	protected function _getCacheId($id, $group)
	{
		$name	= md5($this->_application.'-'.$id.'-'.$this->_hash.'-'.$this->_language);
		return 'cache_'.$group.'-'.$name;
	}
}
