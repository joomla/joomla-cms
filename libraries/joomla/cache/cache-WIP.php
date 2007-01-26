<?php
/**
 * @version		$Id: cache.php 6138 2007-01-02 03:44:18Z eddiea $
 * @package		Joomla.Framework
 * @subpackage	Cache
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

/**
 * Joomla! Cache base object
 *
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla.Framework
 * @subpackage	Cache
 * @since		1.5
 */
class JCache extends JObject
{
	/**
	 * Storage Handler
	 * @access	private
	 * @var		object
	 */
	var $_handler;

	/**
	 * Cache Options
	 * @access	private
	 * @var		array
	 */
	var $_options;

	/**
	 * Constructor
	 *
	 * @access	protected
	 * @param	array	$options	options
	 */
	function _construct($options)
	{
		$this->_options =& $options;
	}

	/**
	 * Returns a reference to a cache adapter object, only creating it
	 * if it doesn't already exist.
	 *
	 * @static
	 * @param	string	$type	The cache object type to instantiate
	 * @return	object	A JCache object
	 * @since	1.5
	 */
	function &getInstance($type = 'output', $options = array())
	{
		static $instances;

		if (!isset ($instances)) {
			$instances = array();
		}

		$type = strtolower($type);
		if (!is_object($instances[$type]))
		{
			jimport('joomla.cache.adapter.'.$type);
			$class = 'JCache'.ucfirst($type);
			$instances[$type] = new $class($options);
		}
		return $instances[$type];
	}

	/**
	 * Get cached data by id and group
	 *
	 * @abstract
	 * @access	public
	 * @param	string	$id		The cache data id
	 * @param	string	$group	The cache data group
	 * @return	mixed	Boolean false on failure or a cached data string
	 * @since	1.5
	 */
	function get($id, $group)
	{
		// Get the storage handler
		$handler =& $this->_getStorageHandler();
		if (!JError::isError($handler)) {
			return $handler->get($id, $group, (isset($this->_options['checkTime']))? $this->_options['checkTime'] : true);
		}
		return false;
	}

	/**
	 * Store the cached data by id and group
	 *
	 * @access	public
	 * @param	string	$id		The cache data id
	 * @param	string	$group	The cache data group
	 * @param	mixed	$data	The data to store
	 * @return	boolean	True if cache stored
	 * @since	1.5
	 */
	function store($id, $group, $data)
	{
		// Get the storage handler and store the cached data
		$handler =& $this->_getStorageHandler();
		if (!JError::isError($handler)) {
			return $handler->store($id, $group, $data);
		}
		return false;
	}

	/**
	 * Remove a cached data entry by id and group
	 *
	 * @abstract
	 * @access	public
	 * @param	string	$id		The cache data id
	 * @param	string	$group	The cache data group
	 * @return	boolean	True on success, false otherwise
	 * @since	1.5
	 */
	function remove($id, $group)
	{
		// Get the storage handler
		$handler =& $this->_getStorageHandler();
		if (!JError::isError($handler)) {
			return $handler->remove($id, $group);
		}
		return false;
	}

	/**
	 * Clean cache for a group given a mode.
	 *
	 * group mode		: cleans all cache in the group
	 * notgroup mode	: cleans all cache not in the group
	 *
	 * @access	public
	 * @param	string	$group	The cache data group
	 * @param	string	$mode	The mode for cleaning cache [group|notgroup]
	 * @return	boolean	True on success, false otherwise
	 * @since	1.5
	 */
	function clean($group, $mode='group')
	{
		// Get the storage handler
		$handler =& $this->_getStorageHandler();
		if (!JError::isError($handler)) {
			return $handler->clean($group, $mode);
		}
		return false;
	}

	/**
	 * Garbage collect expired cache data
	 *
	 * @access public
	 * @return boolean  True on success, false otherwise.
	 */
	function gc()
	{
		// Get the storage handler
		$handler =& $this->_getStorageHandler();
		if (!JError::isError($handler)) {
			return $handler->gc();
		}
		return false;
	}

	function &_getStorageHandler()
	{
		if (is_a($this->_handler, 'JCacheStorage')) {
			return $this->_handler;
		}

		$config =& JFactory::getConfig();
		$handler = $config->getValue('config.cache_handler', 'file');
		jimport('joomla.cache.storage');
		$this->_handler =& JCacheStorage::getInstance($handler, $this->_options);
		return $this->_handler;
	}
}
?>