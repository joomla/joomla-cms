<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Session table
 *
 * @package     Joomla.Platform
 * @subpackage  Table
 * @since       11.1
 */
class JTableSession extends JTable
{
	/**
	 * Constructor
	 *
	 * @param  database  $db  A database connector object
	 *
	 * @since  11.1
	 */
	function __construct(&$db)
	{
		parent::__construct('#__session', 'session_id', $db);

		$this->guest	= 1;
		$this->username = '';
	}

	/**
	 * Insert a session
	 *
	 * @param   string   $sessionId  The session id
	 * @param   integer  $clientId   The id of the client application
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	function insert($sessionId, $clientId)
	{
		$this->session_id	= $sessionId;
		$this->client_id	= $clientId;

		$this->time = time();
		$ret = $this->_db->insertObject($this->_tbl, $this, 'session_id');

		if (!$ret) {
			$this->setError(JText::sprintf('JLIB_DATABASE_ERROR_STORE_FAILED', strtolower(get_class($this)), $this->_db->stderr()));
			return false;
		} else {
			return true;
		}
	}
	/**
	 * Updates the session
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on successs.
	 *
	 * @since   11.1
	 */
	function update($updateNulls = false)
	{
		$this->time = time();
		$ret = $this->_db->updateObject($this->_tbl, $this, 'session_id', $updateNulls);

		if (!$ret) {
			$this->setError(JText::sprintf('JLIB_DATABASE_ERROR_STORE_FAILED', strtolower(get_class($this)), $this->_db->stderr()));
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Destroys the pesisting session
	 *
	 * @param  integer  $userId     Identifier of the user for this session.
	 * @param  integer  $clientIds  Array of client ids for which session(s)
	 *                              will be destroyed
	 *
	 * @return  boolean  True on successs.
	 *
	 * @since   11.1
	 */
	function destroy($userId, $clientIds = array())
	{
		$clientIds = implode(',', $clientIds);

		$query = 'DELETE FROM #__session'
			. ' WHERE userid = '. $this->_db->Quote($userId)
			. ' AND client_id IN ('.$clientIds.')'
			;
		$this->_db->setQuery($query);

		if (!$this->_db->query()) {
			$this->setError($this->_db->stderr());
			return false;
		}

		return true;
	}

	/**
	 * Purge old sessions
	 *
	 * @param   integer  $maxLifetime  Session age in seconds
	 *
	 * @return  mixed    Resource on success, null on fail
	 *
	 * @since   11.1
	*/
	function purge($maxLifetime = 1440)
	{
		$past = time() - $maxLifetime;
		$query = 'DELETE FROM '. $this->_tbl .' WHERE (time < \''. (int) $past .'\')'; // Index on 'VARCHAR'
		$this->_db->setQuery($query);

		return $this->_db->query();
	}

	/**
	 * Find out if a user has a one or more active sessions
	 *
	 * @param   integer  $userid  The identifier of the user
	 *
	 * @return  boolean  True if a session for this user exists
	 *
	 * @since   11.1
	 */
	function exists($userid)
	{
		$query = 'SELECT COUNT(userid) FROM #__session'
			. ' WHERE userid = '. $this->_db->Quote($userid);
		$this->_db->setQuery($query);

		if (!$result = $this->_db->loadResult()) {
			$this->setError($this->_db->stderr());
			return false;
		}

		return (boolean) $result;
	}

	/**
	 * Overloaded delete method
	 *
	 * We must override it because of the non-integer primary key
	 *
	 * @return  mixed  True if successful otherwise an error message
	 *
	 * @since   11.1
	 */
	function delete($oid=null)
	{
		//if (!$this->canDelete($msg))
		//{
		//	return $msg;
		//}

		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = $oid;
		}

		$query = 'DELETE FROM '.$this->_db->quoteName($this->_tbl).
				' WHERE '.$this->_tbl_key.' = '. $this->_db->Quote($this->$k);
		$this->_db->setQuery($query);

		if ($this->_db->query())
		{
			return true;
		}
		else
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
	}
}