<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * APC cache storage handler
 *
 * @package		Joomla.Platform
 * @subpackage	Cache
 * @since		11.1
 */
class JCacheStorageApc extends JCacheStorage
{
	/**
	 * Get cached data from APC by id and group
	 *
	 * @param	string	$id			The cache data id
	 * @param	string	$group		The cache data group
	 * @param	boolean	$checkTime	True to verify cache time expiration threshold
	 * @return	mixed	Boolean false on failure or a cached data string
	 * @since	11.1
	 */
	public function get($id, $group, $checkTime = true)
	{
		$cache_id = $this->_getCacheId($id, $group);
		return apc_fetch($cache_id);
	}

	/**
	 * Get all cached data
	 *
	 * @return	array data
	 * @since	11.1
	 */
	public function getAll()
	{
		parent::getAll();

		$allinfo 	= apc_cache_info('user');
		$keys 		= $allinfo['cache_list'];
		$secret 	= $this->_hash;

		$data = array();

		foreach ($keys as $key) {

			$name 		= $key['info'];
			$namearr 	= explode('-', $name);

			if ($namearr !== false && $namearr[0] == $secret &&  $namearr[1] == 'cache') {
				$group = $namearr[2];

				if (!isset($data[$group])) {
					$item = new JCacheStorageHelper($group);
				} else {
					$item = $data[$group];
				}

				$item->updateSize($key['mem_size']/1024);

				$data[$group] = $item;
			}
		}

		return $data;
	}

	/**
	 * Store the data to APC by id and group
	 *
	 * @param	string	$id		The cache data id
	 * @param	string	$group	The cache data group
	 * @param	string	$data	The data to store in cache
	 * @return	boolean	True on success, false otherwise
	 * @since	11.1
	 */
	public function store($id, $group, $data)
	{
		$cache_id = $this->_getCacheId($id, $group);
		return apc_store($cache_id, $data, $this->_lifetime);
	}

	/**
	 * Remove a cached data entry by id and group
	 *
	 * @param	string	$id		The cache data id
	 * @param	string	$group	The cache data group
	 * @return	boolean	True on success, false otherwise
	 * @since	11.1
	 */
	public function remove($id, $group)
	{
		$cache_id = $this->_getCacheId($id, $group);
		return apc_delete($cache_id);
	}

	/**
	 * Clean cache for a group given a mode.
	 *
	 * group mode		: cleans all cache in the group
	 * notgroup mode	: cleans all cache not in the group
	 *
	 * @param	string	$group	The cache data group
	 * @param	string	$mode	The mode for cleaning cache [group|notgroup]
	 * @return	boolean	True on success, false otherwise
	 * @since	11.1
	 */
	public function clean($group, $mode = null)
	{
		$allinfo 	= apc_cache_info('user');
		$keys 		= $allinfo['cache_list'];
		$secret 	= $this->_hash;

		foreach ($keys as $key) {

			if (strpos($key['info'], $secret.'-cache-'.$group.'-') === 0 xor $mode != 'group') {
				apc_delete($key['info']);
			}
		}
		return true;
	}

	/**
	 * Force garbage collect expired cache data as items are removed only on fetch!
	 *
	 * @return boolean  True on success, false otherwise.
	 * @since	11.1
	 */
	public function gc()
	{
		$lifetime 	= $this->_lifetime;
		$allinfo 	= apc_cache_info('user');
		$keys 		= $allinfo['cache_list'];
		$secret 	= $this->_hash;

		foreach ($keys as $key) {
			if (strpos($key['info'], $secret.'-cache-')) {
				apc_fetch($key['info']);
			}
		}
	}

	/**
	 * Test to see if the cache storage is available.
	 *
	 * @return boolean  True on success, false otherwise.
	 */
	public static function test()
	{
		return extension_loaded('apc');
	}

	/**
	 * Lock cached item - override parent as this is more efficient
	 *
	 * @param	string	$id		The cache data id
	 * @param	string	$group	The cache data group
	 * @param	integer	$locktime Cached item max lock time
	 * @return	boolean	True on success, false otherwise.
	 * @since	11.1
	 */
	public function lock($id,$group,$locktime)
	{
		$returning = new stdClass();
		$returning->locklooped = false;

		$looptime = $locktime * 10;

		$cache_id = $this->_getCacheId($id, $group).'_lock';

		$data_lock = apc_add( $cache_id, 1, $locktime );

		if ( $data_lock === FALSE ) {

			$lock_counter = 0;

			// loop until you find that the lock has been released.  that implies that data get from other thread has finished
			while ( $data_lock === FALSE ) {

				if ( $lock_counter > $looptime ) {
					$returning->locked 		= false;
					$returning->locklooped 	= true;
					break;
				}

				usleep(100);
				$data_lock = apc_add( $cache_id, 1, $locktime );
				$lock_counter++;
			}

		}
		$returning->locked = $data_lock;

		return $returning;
	}

	/**
	 * Unlock cached item - override parent for cacheid compatibility with lock
	 *
	 * @param	string	$id		The cache data id
	 * @param	string	$group	The cache data group
	 * @param	integer	$locktime Cached item max lock time
	 * @return	boolean	True on success, false otherwise.
	 * @since	11.1
	 */
	public function unlock($id,$group=null)
	{
		$unlock = false;

		$cache_id = $this->_getCacheId($id, $group).'_lock';

		$unlock = apc_delete($cache_id);
		return $unlock;
	}
}