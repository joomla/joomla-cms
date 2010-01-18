<?php
/**
 * @version		$Id:eaccelerator.php 6961 2007-03-15 16:06:53Z tcp $
 * @package		Joomla.Framework
 * @subpackage	Session
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

/**
* eAccelerator session storage handler for PHP
*
 * @package		Joomla.Framework
 * @subpackage	Session
 * @since		1.5
* @see http://www.php.net/manual/en/function.session-set-save-handler.php
 */
class JSessionStorageEaccelerator extends JSessionStorage
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
            return JError::raiseError(404, "THE_EACCELERATOR_EXTENSION_IS_NOT_AVAILABLE");
        }

		parent::__construct($options);
	}

	/**
	 * Open the SessionHandler backend.
	 *
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
		return (string) eaccelerator_get($sess_id);
	}

	/**
	 * Write session data to the SessionHandler backend.
	 *
	 * @access public
	 * @param string $id            The session identifier.
	 * @param string $session_data  The session data.
	 * @return boolean  True on success, false otherwise.
	 */
	function write($id, $session_data)
	{
		$sess_id = 'sess_'.$id;
		return eaccelerator_put($sess_id, $session_data, ini_get("session.gc_maxlifetime"));
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
		return eaccelerator_rm($sess_id);
	}

	/**
	 * Garbage collect stale sessions from the SessionHandler backend.
	 *
	 * @access public
	 * @param integer $maxlifetime  The maximum age of a session.
	 * @return boolean  True on success, false otherwise.
	 */
	function gc($maxlifetime)
	{
		eaccelerator_gc();
		return true;
	}

	/**
	 * Test to see if the SessionHandler is available.
	 *
	 * @static
	 * @access public
	 * @return boolean  True on success, false otherwise.
	 */
	function test() {
		return (extension_loaded('eaccelerator') && function_exists('eaccelerator_get'));
	}
}
