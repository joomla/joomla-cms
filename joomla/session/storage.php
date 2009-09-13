<?php
/**
 * @version		$Id:sessionstorage.php 6961 2007-03-15 16:06:53Z tcp $
 * @package		Joomla.Framework
 * @subpackage	Session
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

/**
* Custom session storage handler for PHP
*
* @abstract
 * @package		Joomla.Framework
 * @subpackage	Session
 * @since		1.5
* @see http://www.php.net/manual/en/function.session-set-save-handler.php
 */
class JSessionStorage extends JObject
{
	/**
	* Constructor
	*
	* @access protected
	* @param array $options optional parameters
	*/
	function __construct($options = array())
	{
		$this->register($options);
	}

	/**
	 * Returns a reference to a session storage handler object, only creating it
	 * if it doesn't already exist.
	 *
	 * @param name 	$name The session store to instantiate
	 * @return database A JSessionStorage object
	 * @since 1.5
	 */
	public static function &getInstance($name = 'none', $options = array())
	{
		static $instances;

		if (!isset ($instances)) {
			$instances = array ();
		}

		$name = strtolower(JFilterInput::clean($name, 'word'));
		if (empty ($instances[$name]))
		{
			$class = 'JSessionStorage'.ucfirst($name);
			if (!class_exists($class))
			{
				$path = dirname(__FILE__).DS.'storage'.DS.$name.'.php';
				if (file_exists($path)) {
					require_once($path);
				} else {
					// No call to JError::raiseError here, as it tries to close the non-existing session
					jexit('Unable to load session storage class: '.$name);
				}
			}

			$instances[$name] = new $class($options);
		}

		return $instances[$name];
	}

	/**
	* Register the functions of this class with PHP's session handler
	*
	* @access public
	* @param array $options optional parameters
	*/
	function register($options = array())
	{
		// use this object as the session handler
		session_set_save_handler(
			array($this, 'open'),
			array($this, 'close'),
			array($this, 'read'),
			array($this, 'write'),
			array($this, 'destroy'),
			array($this, 'gc')
		);
	}

	/**
	 * Open the SessionHandler backend.
	 *
	 * @abstract
	 * @access public
	 * @param string $save_path     The path to the session object.
	 * @param string $session_name  The name of the session.
	 * @return boolean  True on success, false otherwise.
	 */
	function open($save_path, $session_name)
	{
		return true;
	}

	/**
	 * Close the SessionHandler backend.
	 *
	 * @abstract
	 * @access public
	 * @return boolean  True on success, false otherwise.
	 */
	function close()
	{
		return true;
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
	function read($id)
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
	function write($id, $session_data)
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
	function destroy($id)
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
	function gc($maxlifetime)
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
