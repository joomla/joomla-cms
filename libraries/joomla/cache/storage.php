<?php
/**
 * @version		$Id:storage.php 6961 2007-03-15 16:06:53Z tcp $
 * @package		Joomla.Framework
 * @subpackage	Cache
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('JPATH_BASE') or die;

/**
 * Abstract cache storage handler
 *
 * @package		Joomla.Framework
 * @subpackage	Cache
 * @since		1.5
 */
abstract class JCacheStorage extends JObject
{
	/**
	 * Constructor.
	 *
	 * @param	array	Optional parameters (application|language|locking|lifetime|now).
	 */
	public function __construct($options = array())
	{
		$this->_application	= (isset($options['application'])) ? $options['application'] : null;
		$this->_language	= (isset($options['language'])) ? $options['language'] : 'en-GB';
		$this->_locking		= (isset($options['locking'])) ? $options['locking'] : true;
		$this->_lifetime	= (isset($options['lifetime'])) ? $options['lifetime'] : null;
		$this->_now			= (isset($options['now'])) ? $options['now'] : time();

		// Set time threshold value.  If the lifetime is not set, default to 60 (0 is BAD)
		// _threshold is now available ONLY as a legacy (it's deprecated).  It's no longer used in the core.
		if (empty($this->_lifetime)) {
			$this->_threshold = $this->_now - 60;
			$this->_lifetime = 60;
		} else {
			$this->_threshold = $this->_now - $this->_lifetime;
		}
	}

	/**
	 * Returns a cache storage hanlder object, only creating it
	 * if it doesn't already exist.
	 *
	 * @param	string	The cache storage handler to instantiate.
	 * @return	object	A JCacheStorageHandler object.
	 * @since	1.5
	 */
	public static function getInstance($handler = 'file', $options = array())
	{
		static $now = null;
		if (is_null($now)) {
			$now = time();
		}
		$options['now'] = $now;
		//We can't cache this since options may change...
		$handler = strtolower(preg_replace('/[^A-Z0-9_\.-]/i', '', $handler));
		$class = 'JCacheStorage'.ucfirst($handler);

		if (!class_exists($class)) {
			$path = dirname(__FILE__).DS.'storage'.DS.$handler.'.php';
			if (file_exists($path)) {
				require_once $path;
			} else {
				return JError::raiseWarning(500, 'Unable to load Cache Storage: '.$handler);
			}
		}

		return new $class($options);
	}

	/**
	 * Get cached data by id and group.
	 *
	 * @param	string	The cache data id.
	 * @param	string	The cache data group.
	 * @param	boolean	True to verify cache time expiration threshold.
	 * @return	mixed	Boolean false on failure or a cached data string.
	 * @since	1.5
	 */
	public function get($id, $group, $checkTime)
	{
		// Note, cannot abstract this method because of JObject (then why bother deriving from JObject??).
		jexit('Derived class must provide its own implementation of JCacheStorage::get.');
	}

	/**
	 * Store the data to cache by id and group.
	 *
	 * @param	string	The cache data id.
	 * @param	string	The cache data group.
	 * @param	string	The data to store in cache.
	 * @return	boolean	True on success, false otherwise.
	 * @since	1.5
	 */
	public function store($id, $group, $data)
	{
		// Note, cannot abstract this method because of JObject (then why bother deriving from JObject??).
		jexit('Derived class must provide its own implementation of JCacheStorage::set.');
	}

	/**
	 * Remove a cached data entry by id and group.
	 *
	 * @param	string	The cache data id.
	 * @param	string	The cache data group.
	 * @return	boolean	True on success, false otherwise.
	 * @since	1.5
	 */
	public abstract function remove($id, $group);

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
	public abstract function clean($group, $mode);

	/**
	 * Garbage collect expired cache data
	 *
	 * @return	boolean  True on success, false otherwise.
	 */
	public abstract function gc();

	/**
	 * Test to see if the storage handler is available.
	 *
	 * @retur	boolean	True on success, false otherwise.
	 */
	public static abstract function test();
}
