<?php
/**
* @version		$Id: session.php 6157 2007-01-03 00:22:09Z Jinx $
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

/**
 * Abstract cache storage handler
 *
 * @abstract
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla.Framework
 * @subpackage	Cache
 * @since		1.5
 */
class JCacheStorageHandler extends JObject
{
	/**
	* Constructor
	*
	* @access protected
	* @param array $options optional parameters
	*/
	function __construct( $options = array() )
	{
		$this->_locking		= (isset($options['locking'])) ? $options['locking'] : true;
		$this->_lifetime	= (isset($options['lifetime'])) ? $options['lifetime'] : null;
		$this->_now			= time();

 		// Set time threshold value
        if (is_null($this->_lifetime)) {
            $this->_threshold = null;
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

		$handler = strtolower($handler);
		if (!is_object($instances[$handler]))
		{
			jimport('joomla.cache.storagehandler.'.$handler);
			$class = 'JCacheStorageHandler'.ucfirst($handler);
			$instances[$handler] = new $class($options);
		}
		return $instances[$handler];
	}

 	/**
 	 * Read the data for a particular session identifier from the
 	 * SessionHandler backend.
 	 *
 	 * @abstract
 	 * @access public
 	 * @param string $id  The session identifier.
 	 * @return string  The session data.
 	 */
	function get($id)
	{
		return;
	}

	/**
	 * Write session data to the SessionHandler backend.
	 *
	 * @abstract
	 * @access public
	 * @param string $id            The session identifier.
	 * @param string $session_data  The session data.
	 * @return boolean  True on success, false otherwise.
	 */
	function store($id, $session_data)
	{
		return true;
	}

	/**
	  * Destroy the data for a particular session identifier in the
	  * SessionHandler backend.
	  *
	  * @abstract
	  * @access public
	  * @param string $id  The session identifier.
	  * @return boolean  True on success, false otherwise.
	  */
	function remove($id)
	{
		return true;
	}

	/**
	 * Garbage collect stale sessions from the SessionHandler backend.
	 *
	 * @abstract
	 * @access public
	 * @param integer $maxlifetime  The maximum age of a session.
	 * @return boolean  True on success, false otherwise.
	 */
	function clean($maxlifetime)
	{
		return true;
	}

	/**
	 * Test to see if the SessionHandler is available.
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
?>