<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Mock of JCacheStorage Backend Class.  Used for testing of cache handlers.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Cache
 * @since       11.1
 */
class JCacheStorageMock extends JCacheStorage
{
	private $_storage = array();

	/**
	 * Constructor
	 *
	 * @param   array  $options  optional parameters
	 */
	public function __construct($options = array())
	{
		parent::__construct($options);

		$config = JFactory::getConfig();
		$this->_hash = $config->get('secret');
	}

	/**
	 * Get cached data by id and group
	 *
	 * @param   string   $id         The cache data id
	 * @param   string   $group      The cache data group
	 * @param   boolean  $checkTime  True to verify cache time expiration threshold
	 *
	 * @return  mixed  Boolean  false on failure or a cached data object
	 *
	 * @since   11.1
	 */
	public function get($id, $group, $checkTime = true)
	{
		$cache_id = $this->_getCacheId($id, $group);

		if (isset($this->_storage[$cache_id]))
		{
			return $this->_storage[$cache_id];
		}

		return false;
	}

	/**
	 * Store the data to cache by id and group
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

		return ($this->_storage[$cache_id] = $data);
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
		unset($this->_storage[$cache_id]);
	}

	/**
	 * Clean cache for a group given a mode.
	 *
	 * @param   string  $group  The cache data group
	 * @param   string  $mode   The mode for cleaning cache [group|notgroup]
	 *                          group mode     : cleans all cache in the group
	 *                          notgroup mode  : cleans all cache not in the group
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since   11.1
	 */
	public function clean($group, $mode = null)
	{
		return ($this->_storage = array());
	}

	/**
	 * Get a cache_id string from an id/group pair
	 *
	 * @param   string  $id     The cache data id
	 * @param   string  $group  The cache data group
	 *
	 * @return  string   The cache_id string
	 *
	 * @since   11.1
	 */
	protected function _getCacheId($id, $group)
	{
		$name = md5($this->_application . '-' . $id . '-' . $this->_hash . '-' . $this->_language);

		return 'cache_' . $group . '-' . $name;
	}
}
