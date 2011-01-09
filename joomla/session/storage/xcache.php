<?php
/**
 * @version		$Id:apc.php 6961 2007-03-15 16:06:53Z tcp $
 * @package		Joomla.Framework
 * @subpackage	Session
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * XCache session storage handler
 *
 * @package		Joomla.Framework
 * @subpackage	Cache
 * @since		1.5
 */
class JSessionStorageXcache extends JSessionStorage
{
	/**
	* Constructor
	*
	* @access protected
	* @param array $options optional parameters
	*/
	function __construct($options = array())
	{
		if (!$this->test()) {
			return JError::raiseError(404, JText::_('JLIB_SESSION_XCACHE_EXTENSION_NOT_AVAILABLE'));
		}

		parent::__construct($options);
	}

	/**
	 * Open the SessionHandler backend.
	 *
	 * @access public
	 * @param string $save_path	The path to the session object.
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
	 * @access public
	 * @param string $id  The session identifier.
	 * @return string  The session data.
	 */
	function read($id)
	{
		$sess_id = 'sess_'.$id;

		//check if id exists
		if (!xcache_isset($sess_id)){
			return;
		}

		return (string)xcache_get($sess_id);
	}

	/**
	 * Write session data to the SessionHandler backend.
	 *
	 * @access public
	 * @param string $id			The session identifier.
	 * @param string $session_data  The session data.
	 * @return boolean  True on success, false otherwise.
	 */
	function write($id, $session_data)
	{
		$sess_id = 'sess_'.$id;
		return xcache_set($sess_id, $session_data, ini_get("session.gc_maxlifetime") );
	}

	/**
	 * Destroy the data for a particular session identifier in the
	 * SessionHandler backend.
	 *
	 * @access public
	 * @param string $id  The session identifier.
	 * @return boolean  True on success, false otherwise.
	 */
	function destroy($id)
	{
		$sess_id = 'sess_'.$id;

		if (!xcache_isset($sess_id)){
			return true;
		}

		return xcache_unset($sess_id);
	}

	/**
	 * Garbage collect stale sessions from the SessionHandler backend.
	 *
	 * @access public
	 * @param integer $maxlifetime  The maximum age of a session.
	 * @return boolean  True on success, false otherwise.
	 */
	function gc($maxlifetime = null)
	{
		return true;
	}

	/**
	 * Test to see if the SessionHandler is available.
	 *
	 * @static
	 * @access public
	 * @return boolean  True on success, false otherwise.
	 */
	static function test() {
		return (extension_loaded('xcache'));
	}
}
