<?php
/**
* @version		$Id:storage.php 6961 2007-03-15 16:06:53Z tcp $
* @package		Joomla.Framework
* @subpackage	Cache
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Abstract cache storage handler
 *
 * @abstract
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla.Framework
 * @subpackage	Cache
 * @since		1.5
 */
class JCacheStorage extends JObject
{
	/**
	* Constructor
	*
	* @access protected
	* @param array $options optional parameters
	*/
	function __construct( $options = array() )
	{
		$this->_application	= (isset($options['application'])) ? $options['application'] : null;
		$this->_language	= (isset($options['language'])) ? $options['language'] : 'en-GB';
		$this->_locking		= (isset($options['locking'])) ? $options['locking'] : true;
		$this->_lifetime	= (isset($options['lifetime'])) ? $options['lifetime'] : null;
		$this->_now			= time();

 		// Set time threshold value
        if (is_null($this->_lifetime)) {
            $this->_threshold = 0;
        } else {
            $this->_threshold = $this->_now - $this->_lifetime;
        }
	}

	/**
	 * Returns a reference to a cache storage hanlder object, only creating it
	 * if it doesn't already exist.
	 *
	 * @static
	 * @param	string	$handler	The cache storage handler to instantiate
	 * @return	object	A JCacheStorageHandler object
	 * @since	1.5
	 */
	function &getInstance($handler = 'file', $options = array())
	{
		static $instances;

		if (!isset ($instances)) {
			$instances = array ();
		}

		$handler = strtolower(preg_replace('/[^A-Z0-9_\.-]/i', '', $handler));
		if (!isset($instances[$handler]))
		{
			$path = JPATH_LIBRARIES.DS.'joomla'.DS.'cache'.DS.'storage'.DS.$handler.'.php';
			if (file_exists($path)) {
				require_once $path;
			} else {
				return JError::raiseWarning(500, 'Unable to load Cache Storage: '.$handler);
			}

			$class = 'JCacheStorage'.ucfirst($handler);
			if (class_exists($class)) {
				$instances[$handler] = new $class($options);
			} else {
				return JError::raiseWarning(500, 'Invalid Cache Type: '.$handler);
			}
		}
		return $instances[$handler];
	}

	/**
	 * Get cached data by id and group
	 *
	 * @abstract
	 * @access	public
	 * @param	string	$id			The cache data id
	 * @param	string	$group		The cache data group
	 * @param	boolean	$checkTime	True to verify cache time expiration threshold
	 * @return	mixed	Boolean false on failure or a cached data string
	 * @since	1.5
	 */
	function get($id, $group, $checkTime)
	{
		return;
	}

	/**
	 * Store the data to cache by id and group
	 *
	 * @abstract
	 * @access	public
	 * @param	string	$id		The cache data id
	 * @param	string	$group	The cache data group
	 * @param	string	$data	The data to store in cache
	 * @return	boolean	True on success, false otherwise
	 * @since	1.5
	 */
	function store($id, $group, $data)
	{
		return true;
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
		return true;
	}

	/**
	 * Clean cache for a group given a mode.
	 *
	 * group mode		: cleans all cache in the group
	 * notgroup mode	: cleans all cache not in the group
	 *
	 * @abstract
	 * @access	public
	 * @param	string	$group	The cache data group
	 * @param	string	$mode	The mode for cleaning cache [group|notgroup]
	 * @return	boolean	True on success, false otherwise
	 * @since	1.5
	 */
	function clean($group, $mode)
	{
		return true;
	}

	/**
	 * Garbage collect expired cache data
	 *
	 * @abstract
	 * @access public
	 * @return boolean  True on success, false otherwise.
	 */
	function gc()
	{
		return true;
	}

	/**
	 * Test to see if the storage handler is available.
	 *
	 * @abstract
	 * @static
	 * @access public
	 * @return boolean  True on success, false otherwise.
	 */
	function test()
	{
		return true;
	}
}