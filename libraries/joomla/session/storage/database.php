<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Session
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Database session storage handler for PHP
 *
 * @see    https://secure.php.net/manual/en/function.session-set-save-handler.php
 * @since  11.1
 */
class JSessionStorageDatabase extends JSessionStorage
{
	/**
	 * Flag whether gc() has been called
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	private $gcCalled = false;

	/**
	 * Lifetime for garbage collection
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	private $gcLifetime;

	/**
	 * Close the session
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function close()
	{
		$db = JFactory::getDbo();

		if ($this->gcCalled)
		{
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__session'))
				->where($db->quoteName('time') . ' < ' . $db->quote((int) $this->gcLifetime));

			// Remove expired sessions from the database.
			try
			{
				$db->setQuery($query)->execute();
			}
			catch (JDatabaseExceptionExecuting $e)
			{
				// Executing garbage collection should not cause closing the session to fatally error out, so we can safely ignore this Exception
			}

			$this->gcCalled   = false;
			$this->gcLifetime = null;
		}

		$db->disconnect();

		return true;
	}

	/**
	 * Initialize session
	 *
	 * @param   string  $save_path  The path where to store/retrieve the session
	 * @param   string  $id         The session id
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function open($save_path, $id)
	{
		JFactory::getDbo()->connect();

		return true;
	}

	/**
	 * Read the data for a particular session identifier from the SessionHandler backend.
	 *
	 * @param   string  $id  The session identifier.
	 *
	 * @return  string  The session data.
	 *
	 * @since   11.1
	 */
	public function read($id)
	{
		// Get the database connection object and verify its connected.
		$db = JFactory::getDbo();

		try
		{
			// Get the session data from the database table.
			$query = $db->getQuery(true)
				->select($db->quoteName('data'))
				->from($db->quoteName('#__session'))
				->where($db->quoteName('session_id') . ' = ' . $db->quote($id));

			$db->setQuery($query);

			$result = (string) $db->loadResult();

			$result = str_replace('\0\0\0', chr(0) . '*' . chr(0), $result);

			return $result;
		}
		catch (RuntimeException $e)
		{
			return '';
		}
	}

	/**
	 * Write session data to the SessionHandler backend.
	 *
	 * @param   string  $id    The session identifier.
	 * @param   string  $data  The session data.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   11.1
	 */
	public function write($id, $data)
	{
		// Get the database connection object and verify its connected.
		$db = JFactory::getDbo();

		$data = str_replace(chr(0) . '*' . chr(0), '\0\0\0', $data);

		try
		{
			// Figure out if a row exists for the session ID
			$query = $db->getQuery(true)
				->select($db->quoteName('session_id'))
				->from($db->quoteName('#__session'))
				->where($db->quoteName('session_id') . ' = ' . $db->quote($id));

			$idExists = $db->setQuery($query)->loadResult();

			$query = $db->getQuery(true);

			if ($idExists)
			{
				$query->update($db->quoteName('#__session'))
					->set($db->quoteName('data') . ' = ' . $db->quote($data))
					->set($db->quoteName('time') . ' = ' . $db->quote((int) time()))
					->where($db->quoteName('session_id') . ' = ' . $db->quote($id));
			}
			else
			{
				$query->insert($db->quoteName('#__session'))
					->columns(array($db->quoteName('data'), $db->quoteName('time'), $db->quoteName('session_id')))
					->values(implode(', ', array($db->quote($data), (int) time(), $db->quote($id))));
			}

			// Try to insert the session data in the database table.
			$db->setQuery($query)->execute();

			return true;
		}
		catch (RuntimeException $e)
		{
			return false;
		}
	}

	/**
	 * Destroy the data for a particular session identifier in the SessionHandler backend.
	 *
	 * @param   string  $id  The session identifier.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   11.1
	 */
	public function destroy($id)
	{
		// Get the database connection object and verify its connected.
		$db = JFactory::getDbo();

		try
		{
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__session'))
				->where($db->quoteName('session_id') . ' = ' . $db->quote($id));

			// Remove a session from the database.
			$db->setQuery($query)->execute();

			return true;
		}
		catch (RuntimeException $e)
		{
			return false;
		}
	}

	/**
	 * Garbage collect stale sessions from the SessionHandler backend.
	 *
	 * @param   integer  $lifetime  The maximum age of a session.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   11.1
	 */
	public function gc($lifetime = 1440)
	{
		// We'll delay garbage collection until the session is closed to prevent potential issues mid-cycle
		$this->gcLifetime = time() - $lifetime;
		$this->gcCalled   = true;

		return true;
	}
}
