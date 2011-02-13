<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.Platform
 * @subpackage  Cache
 */

defined('JPATH_PLATFORM') or die;

/**
 * Memcache cache storage handler
 *
 * @package		Joomla.Platform
 * @subpackage	Cache
 * @since		11.1
 */
class JCacheStorageMemcache extends JCacheStorage
{
	/**
	 * @since	11.1
	 */
	private static $_db = null;

	/**
	 * @since	11.1
	 */
	private $_persistent = false;

	/**
	 * @since	11.1
	 */
	private $_compress = 0;

	/**
	 * Constructor
	 *
	 * @param	array	$options optional parameters
	 * @since	11.1
	 */
	public function __construct($options = array())
	{
		parent::__construct($options);
		if (self::$_db === null) {
			$this->getConnection();
		}
	}

	/**
	 * return memcache connection object
	 *
	 * @return	object	memcache connection object
	 * @since	11.1
	 */
	private function getConnection()
	{
		if ((extension_loaded('memcache') && class_exists('Memcache')) != true ) {
			return false;
		}

		$config = JFactory::getConfig();
		$this->_persistent	= $config->get('memcache_persist', true);
		$this->_compress = $config->get('memcache_compress', false) == false ? 0 : MEMCACHE_COMPRESSED;

		// This will be an array of loveliness
		// @todo: multiple servers
		//$servers	= (isset($params['servers'])) ? $params['servers'] : array();
		$server=array();
		$server['host'] = $config->get('memcache_server_host', 'localhost');
		$server['port'] = $config->get('memcache_server_port', 11211);
		// Create the memcache connection
		self::$_db = new Memcache;
		self::$_db->addServer($server['host'], $server['port'], $this->_persistent);

		$memcachetest = @self::$_db->connect($server['host'], $server['port']);
		if ($memcachetest == false) {
			return JError::raiseError(404, "Could not connect to memcache server");
		}

		// memcahed has no list keys, we do our own accounting, initalise key index
		if (self::$_db->get($this->_hash.'-index') === false) {
			$empty = array();
			self::$_db->set($this->_hash.'-index', $empty , $this->_compress, 0);
		}

		return;
	}

	/**
	 * Get cached data from memcache by id and group
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
		$back = self::$_db->get($cache_id);
		return $back;
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

		$keys = self::$_db->get($this->_hash.'-index');
		$secret = $this->_hash;

		$data = array();

		if (!empty($keys)){
			foreach ($keys as $key) {
				if (empty($key)) {
					continue;
				}
				$namearr=explode('-',$key->name);

				if ($namearr !== false && $namearr[0]==$secret &&  $namearr[1]=='cache') {

					$group = $namearr[2];

					if (!isset($data[$group])) {
						$item = new JCacheStorageHelper($group);
					} else {
						$item = $data[$group];
					}

					$item->updateSize($key->size/1024);

					$data[$group] = $item;
				}
			}
		}

		return $data;
	}

	/**
	 * Store the data to memcache by id and group
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

		if (!$this->lockindex()) {
			return false;
		}

		$index = self::$_db->get($this->_hash.'-index');
		if ($index === false) {
			$index = array();
		}

		$tmparr = new stdClass;
		$tmparr->name = $cache_id;
		$tmparr->size = strlen($data);
		$index[] = $tmparr;
		self::$_db->replace($this->_hash.'-index', $index , 0, 0);
		$this->unlockindex();

		// prevent double writes, write only if it doesn't exist else replace
		if (!self::$_db->replace($cache_id, $data, $this->_compress, $this->_lifetime)) {
			self::$_db->set($cache_id, $data, $this->_compress, $this->_lifetime);
		}

		return true;
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

		if (!$this->lockindex()) {
			return false;
		}

		$index = self::$_db->get($this->_hash.'-index');
		if ($index === false) {
			$index = array();
		}

		foreach ($index as $key => $value) {
			if ($value->name == $cache_id) unset ($index[$key]);
			break;
		}
		self::$_db->replace($this->_hash.'-index', $index, 0, 0);
		$this->unlockindex();

		return self::$_db->delete($cache_id);
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
		if (!$this->lockindex()) {
			return false;
		}

		$index = self::$_db->get($this->_hash.'-index');
		if ($index === false) {
			$index = array();
		}

		$secret = $this->_hash;
		foreach ($index as $key=>$value) {

			if (strpos($value->name, $secret.'-cache-'.$group.'-')===0 xor $mode != 'group') {
				self::$_db->delete($value->name,0);
				unset ($index[$key]);
			}
		}
		self::$_db->replace($this->_hash.'-index', $index , 0, 0);
		$this->unlockindex();
		return true;
	}

	/**
	 * Test to see if the cache storage is available.
	 *
	 * @return	boolean	True on success, false otherwise.
	 */
	public static function test()
	{
		if ((extension_loaded('memcache') && class_exists('Memcache')) != true ) {
			return false;
		}

		$config = JFactory::getConfig();
		$host = $config->get('memcache_server_host', 'localhost');
		$port = $config->get('memcache_server_port', 11211);

		$memcache = new Memcache;
		$memcachetest = @$memcache->connect($host, $port);

		 if (!$memcachetest) {
		 	return false;
		 } else {
		 	return true;
		 }
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

		$cache_id = $this->_getCacheId($id, $group);

		if (!$this->lockindex()) {
			return false;
		}

		$index = self::$_db->get($this->_hash.'-index');
		if ($index === false) {
			$index = array();
		}

		$tmparr = new stdClass;
		$tmparr->name = $cache_id;
		$tmparr->size = 1;
		$index[] = $tmparr;
		self::$_db->replace($this->_hash.'-index', $index , 0, 0);
		$this->unlockindex();

		$data_lock = self::$_db->add($cache_id.'_lock', 1, false, $locktime);

		if ($data_lock === FALSE) {

			$lock_counter = 0;

			// loop until you find that the lock has been released.  that implies that data get from other thread has finished
			while ($data_lock === FALSE) {

				if ($lock_counter > $looptime) {
						$returning->locked 		= false;
						$returning->locklooped 	= true;
					break;
				}

				usleep(100);
				$data_lock = self::$_db->add($cache_id.'_lock', 1, false, $locktime);
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
	 * @since	11.1
	 * @return boolean  True on success, false otherwise.
	 */
	public function unlock($id,$group=null)
	{
		$unlock = false;

		$cache_id = $this->_getCacheId($id, $group).'_lock';

		if (!$this->lockindex()) return false;

		$index = self::$_db->get($this->_hash.'-index');
		if ($index === false) {$index = array();}

		foreach ($index as $key=>$value){
		if ($value->name == $cache_id) unset ($index[$key]);
		break;
		}
		self::$_db->replace($this->_hash.'-index', $index, 0, 0);
		$this->unlockindex();

		return self::$_db->delete($cache_id);
	}


	/**
	 * Lock cache index
	 *
	 * @since	11.1
	 * @return boolean  True on success, false otherwise.
	 */
	private function lockindex()
	{
		$looptime 	= 300;
		$data_lock 	= self::$_db->add($this->_hash.'-index_lock', 1, false, 30);

		if ($data_lock === FALSE) {

			$lock_counter = 0;

			// loop until you find that the lock has been released.  that implies that data get from other thread has finished
			while ( $data_lock === FALSE ) {

				if ( $lock_counter > $looptime ) {
					return false;
					break;
				}

				usleep(100);
				$data_lock = self::$_db->add($this->_hash.'-index_lock', 1, false, 30);
				$lock_counter++;
			}
		}

		return true;
	}

	/**
	 * Unlock cache index
	 *
	 * @return	boolean	True on success, false otherwise.
	 * @since	11.1
	 */
	private function unlockindex()
	{
		return self::$_db->delete($this->_hash.'-index_lock');
	}
}