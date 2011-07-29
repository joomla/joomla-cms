<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Session
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

/**
 * Database session storage handler for PHP
 *
 * @package     Joomla.Platform
 * @subpackage  Session
 * @see         http://www.php.net/manual/en/function.session-set-save-handler.php
 * @since       11.1
 */
class JSessionStorageDatabase extends JSessionStorage
{

	protected $_data = null;

	/**
	 * Open the SessionHandler backend.
	 *
	 * @param   string   The path to the session object.
	 * @param   string   The name of the session.
	 * @return  boolean  True on success, false otherwise.
	 * @since   11.1
	 */
	public function open($save_path, $session_name)
	{
		return true;
	}

	/**
	 * Close the SessionHandler backend.
	 *
	 * @return  boolean  True on success, false otherwise.
	 * @since   11.1
	 */
	public function close()
	{
		return true;
	}

	/**
	 * Read the data for a particular session identifier from the
	 * SessionHandler backend.
	 *
	 * @param   string   The session identifier.
	 * @return  string   The session data.
	 * @since   11.1
	 */
	public function read($id)
	{
		// Get the database connection object and verify its connected.
		$db = JFactory::getDbo();
		if (!$db->connected())
		{
			return false;
		}

		// Get the session data from the database table.
		$query = $db->getQuery(true);
		$query->select($query->qn('data'))
			->from($query->qn('#__session'));
		$query->where($query->qn('session_id') . ' = ' . $query->q($id));
		$db->setQuery($query);
		return (string) $db->loadResult();
	}

	/**
	 * Write session data to the SessionHandler backend.
	 *
	 * @param   string   The session identifier.
	 * @param   string   The session data.
	 *
	 * @return  boolean  True on success, false otherwise.
	 * @since   11.1
	 */
	public function write($id, $data)
	{
		// Get the database connection object and verify its connected.
		$db = JFactory::getDbo();
		if (!$db->connected())
		{
			return false;
		}

		// Try to update the session data in the database table.
		$query = $db->getQuery(true);
		$db->setQuery(
			'UPDATE ' . $query->qn('#__session') . ' SET ' . $query->qn('data') . ' = ' . $query->q($data) . ',' . '	  ' . $query->qn('time') . ' = ' .
				 (int) time() . ' WHERE ' . $query->qn('session_id') . ' = ' . $query->q($id));
		if (!$db->query())
		{
			return false;
		}

		if ($db->getAffectedRows())
		{
			return true;
		}
		else
		{
			// If the session does not exist, we need to insert the session.
			$db->setQuery(
				'INSERT INTO ' . $query->qn('#__session') . ' (' . $query->qn('session_id') . ', ' . $query->qn('data') . ', ' . $query->qn('time') .
					 ')' . ' VALUES (' . $query->q($id) . ', ' . $query->q($data) . ', ' . (int) time() . ')');
			return (boolean) $db->query();
		}
	}

	/**
	 * Destroy the data for a particular session identifier in the
	 * SessionHandler backend.
	 *
	 * @param   string   The session identifier.
	 *
	 * @return  boolean  True on success, false otherwise.
	 * @since   11.1
	 */
	public function destroy($id)
	{
		// Get the database connection object and verify its connected.
		$db = JFactory::getDbo();
		if (!$db->connected())
		{
			return false;
		}

		// Remove a session from the database.
		$query = $db->getQuery(true);
		$db->setQuery('DELETE FROM ' . $query->qn('#__session') . ' WHERE ' . $query->qn('session_id') . ' = ' . $query->q($id));
		return (boolean) $db->query();
	}

	/**
	 * Garbage collect stale sessions from the SessionHandler backend.
	 *
	 * @param   integer  The maximum age of a session.
	 * @return  boolean  True on success, false otherwise.
	 * @since   11.1
	 */
	function gc($lifetime = 1440)
	{
		// Get the database connection object and verify its connected.
		$db = JFactory::getDbo();
		if (!$db->connected())
		{
			return false;
		}

		// Determine the timestamp threshold with which to purge old sessions.
		$past = time() - $lifetime;

		// Remove expired sessions from the database.
		$query = $db->getQuery(true);
		$db->setQuery('DELETE FROM ' . $query->qn('#__session') . ' WHERE ' . $query->qn('time') . ' < ' . (int) $past);
		return (boolean) $db->query();
	}
}
