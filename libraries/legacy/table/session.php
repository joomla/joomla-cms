<?php
/**
 * @package     Joomla.Legacy
 * @subpackage  Table
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * Session table
 *
 * @since       1.5
 * @deprecated  3.2  Use SQL queries to interact with the session table.
 */
class JTableSession extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  $db  Database driver object.
	 *
	 * @since   1.5
	 * @deprecated  3.2  Use SQL queries to interact with the session table.
	 */
	public function __construct(JDatabaseDriver $db)
	{
		JLog::add('JTableSession is deprecated. Use SQL queries directly to interact with the session table.', JLog::WARNING, 'deprecated');
		parent::__construct('#__session', 'session_id', $db);

		$this->guest = 1;
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
	 * @since   1.5
	 * @deprecated  3.2  Use SQL queries to interact with the session table.
	 */
	public function insert($sessionId, $clientId)
	{
		$this->session_id = $sessionId;
		$this->client_id = $clientId;

		$this->time = time();
		$ret = $this->_db->insertObject($this->_tbl, $this, 'session_id');

		if (!$ret)
		{
			$this->setError(JText::sprintf('JLIB_DATABASE_ERROR_STORE_FAILED', strtolower(get_class($this)), $this->_db->stderr()));

			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Updates the session
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.5
	 * @deprecated  3.2  Use SQL queries to interact with the session table.
	 */
	public function update($updateNulls = false)
	{
		$this->time = time();
		$ret = $this->_db->updateObject($this->_tbl, $this, 'session_id', $updateNulls);

		if (!$ret)
		{
			$this->setError(JText::sprintf('JLIB_DATABASE_ERROR_STORE_FAILED', strtolower(get_class($this)), $this->_db->stderr()));

			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Destroys the pre-existing session
	 *
	 * @param   integer  $userId     Identifier of the user for this session.
	 * @param   array    $clientIds  Array of client ids for which session(s) will be destroyed
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.5
	 * @deprecated  3.2  Use SQL queries to interact with the session table.
	 */
	public function destroy($userId, $clientIds = array())
	{
		$clientIds = implode(',', $clientIds);

		$query = $this->_db->getQuery(true)
			->delete($this->_db->quoteName($this->_tbl))
			->where($this->_db->quoteName('userid') . ' = ' . $this->_db->quote($userId))
			->where($this->_db->quoteName('client_id') . ' IN (' . $clientIds . ')');
		$this->_db->setQuery($query);

		if (!$this->_db->execute())
		{
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
	 * @return  mixed  Resource on success, null on fail
	 *
	 * @since   1.5
	 * @deprecated  3.2  Use SQL queries to interact with the session table.
	 */
	public function purge($maxLifetime = 1440)
	{
		$past = time() - $maxLifetime;
		$query = $this->_db->getQuery(true)
			->delete($this->_db->quoteName($this->_tbl))
			->where($this->_db->quoteName('time') . ' < ' . (int) $past);
		$this->_db->setQuery($query);

		return $this->_db->execute();
	}

	/**
	 * Find out if a user has one or more active sessions
	 *
	 * @param   integer  $userid  The identifier of the user
	 *
	 * @return  boolean  True if a session for this user exists
	 *
	 * @since   1.5
	 * @deprecated  3.2  Use SQL queries to interact with the session table.
	 */
	public function exists($userid)
	{
		$query = $this->_db->getQuery(true)
			->select('COUNT(userid)')
			->from($this->_db->quoteName($this->_tbl))
			->where($this->_db->quoteName('userid') . ' = ' . $this->_db->quote($userid));
		$this->_db->setQuery($query);

		if (!$result = $this->_db->loadResult())
		{
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
	 * @param   integer  $oid  The object id (optional).
	 *
	 * @return  mixed  True if successful otherwise an error message
	 *
	 * @since   1.5
	 * @deprecated  3.2  Use SQL queries to interact with the session table.
	 */
	public function delete($oid = null)
	{
		$k = $this->_tbl_key;

		if ($oid)
		{
			$this->$k = $oid;
		}

		$query = $this->_db->getQuery(true)
			->delete($this->_db->quoteName($this->_tbl))
			->where($this->_db->quoteName($this->_tbl_key) . ' = ' . $this->_db->quote($this->$k));
		$this->_db->setQuery($query);

		$this->_db->execute();

		return true;
	}
}
