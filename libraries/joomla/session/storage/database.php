<?php
/**
 * @version		$Id:database.php 6961 2007-03-15 16:06:53Z tcp $
 * @package		Joomla.Framework
 * @subpackage	Session
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * Database session storage handler for PHP
 *
 * @package		Joomla.Framework
 * @subpackage	Session
 * @since		1.5
 * @see http://www.php.net/manual/en/function.session-set-save-handler.php
 */
class JSessionStorageDatabase extends JSessionStorage
{
	protected $_data = null;

	/**
	 * Open the SessionHandler backend.
	 *
	 * @param	string	The path to the session object.
	 * @param	string	The name of the session.
	 * @return	boolean	True on success, false otherwise.
	 */
	public function open($save_path, $session_name)
	{
		return true;
	}

	/**
	 * Close the SessionHandler backend.
	 *
	 * @return	boolean	True on success, false otherwise.
	 */
	public function close()
	{
		return true;
	}

 	/**
 	 * Read the data for a particular session identifier from the
 	 * SessionHandler backend.
 	 *
 	 * @param	string	The session identifier.
 	 * @return	string	The session data.
 	 */
	public function read($id)
	{
		// Get the database connection object and verify its connected.
		$db = &JFactory::getDbo();
		if (!$db->connected()) {
			return false;
		}

		$session = & JTable::getInstance('session');
		$session->load($id);
		return (string)$session->data;
	}

	/**
	 * Write session data to the SessionHandler backend.
	 *
	 * @param	string	The session identifier.
	 * @param	string	The session data.
	 * @return	boolean	True on success, false otherwise.
	 */
	public function write($id, $session_data)
	{
		// Get the database connection object and verify its connected.
		$db = &JFactory::getDbo();
		if (!$db->connected()) {
			return false;
		}

		$session = & JTable::getInstance('session');
		$session->load($id);
		$session->data = $session_data;
		$session->store();

		return true;
	}

	/**
	  * Destroy the data for a particular session identifier in the
	  * SessionHandler backend.
	  *
	  * @param string $id  The session identifier.
	  * @return boolean  True on success, false otherwise.
	  */
	public function destroy($id)
	{
		// Get the database connection object and verify its connected.
		$db = &JFactory::getDbo();
		if (!$db->connected()) {
			return false;
		}

		$session = & JTable::getInstance('session');
		$session->delete($id);
		return true;
	}

	/**
	 * Garbage collect stale sessions from the SessionHandler backend.
	 *
	 * @param	integer	The maximum age of a session.
	 * @return	boolean	True on success, false otherwise.
	 */
	public function gc($maxlifetime)
	{
		// Get the database connection object and verify its connected.
		$db = &JFactory::getDbo();
		if (!$db->connected()) {
			return false;
		}

		$session = & JTable::getInstance('session');
		$session->purge($maxlifetime);
		return true;
	}
}
