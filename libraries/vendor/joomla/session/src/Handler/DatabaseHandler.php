<?php
/**
 * Part of the Joomla Framework Session Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Handler;

use Joomla\Database\DatabaseInterface;
use Joomla\Session\HandlerInterface;

/**
 * Database session storage handler
 *
 * @since  __DEPLOY_VERSION__
 */
class DatabaseHandler implements HandlerInterface
{
	/**
	 * Database connector
	 *
	 * @var    DatabaseInterface
	 * @since  __DEPLOY_VERSION__
	 */
	private $db;

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
	 * Constructor
	 *
	 * @param   DatabaseInterface  $db  Database connector
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(DatabaseInterface $db)
	{
		$this->db = $db;
	}

	/**
	 * Close the session
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function close()
	{
		if ($this->gcCalled)
		{
			$query = $this->db->getQuery(true)
				->delete($this->db->quoteName('#__session'))
				->where($this->db->quoteName('time') . ' < ' . $this->db->quote((int) $this->gcLifetime));

			// Remove expired sessions from the database.
			$this->db->setQuery($query)->execute();

			$this->gcCalled   = false;
			$this->gcLifetime = null;
		}

		$this->db->disconnect();

		return true;
	}

	/**
	 * Creates the session database table
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \RuntimeException
	 * @throws  \UnexpectedValueException
	 */
	public function createDatabaseTable()
	{
		switch ($this->db->name)
		{
			case 'mysql':
			case 'mysqli':
				$filename = 'mysql.sql';

				break;

			case 'postgresql':
				$filename = 'pgsql.sql';

				break;

			case 'sqlsrv':
			case 'sqlazure':
				$filename = 'sqlsrv.sql';

				break;

			case 'sqlite':
				$filename = 'sqlite.sql';

				break;

			default:
				throw new \UnexpectedValueException(sprintf('The %s database driver is not supported.', $this->db->name));
		}

		$path = dirname(dirname(__DIR__)) . '/meta/sql/' . $filename;

		if (!is_readable($path))
		{
			throw new \RuntimeException(sprintf('Database schema could not be read from %s.  Please ensure the file exists and is readable.', $path));
		}

		$queries = $this->db->splitSql(file_get_contents($path));

		foreach ($queries as $query)
		{
			$query = trim($query);

			if ($query !== '')
			{
				try
				{
					$this->db->setQuery($query)->execute();
				}
				catch (\RuntimeException $exception)
				{
					throw new \RuntimeException('Failed to create the session table.', 0, $exception);
				}
			}
		}

		return true;
	}

	/**
	 * Destroy a session
	 *
	 * @param   integer  $session_id  The session ID being destroyed
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function destroy($session_id)
	{
		try
		{
			$query = $this->db->getQuery(true)
				->delete($this->db->quoteName('#__session'))
				->where($this->db->quoteName('session_id') . ' = ' . $this->db->quote($session_id));

			// Remove a session from the database.
			$this->db->setQuery($query)->execute();

			return true;
		}
		catch (\Exception $e)
		{
			return false;
		}
	}

	/**
	 * Cleanup old sessions
	 *
	 * @param   integer  $maxlifetime  Sessions that have not updated for the last maxlifetime seconds will be removed
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function gc($maxlifetime)
	{
		// We'll delay garbage collection until the session is closed to prevent potential issues mid-cycle
		$this->gcLifetime = time() - $maxlifetime;
		$this->gcCalled   = true;

		return true;
	}

	/**
	 * Test to see if the HandlerInterface is available
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function isSupported()
	{
		return interface_exists(DatabaseInterface::class);
	}

	/**
	 * Initialize session
	 *
	 * @param   string  $save_path   The path where to store/retrieve the session
	 * @param   string  $session_id  The session id
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function open($save_path, $session_id)
	{
		$this->db->connect();

		return true;
	}

	/**
	 * Read session data
	 *
	 * @param   string  $session_id  The session id to read data for
	 *
	 * @return  string  The session data
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function read($session_id)
	{
		try
		{
			// Get the session data from the database table.
			$query = $this->db->getQuery(true)
				->select($this->db->quoteName('data'))
				->from($this->db->quoteName('#__session'))
				->where($this->db->quoteName('session_id') . ' = ' . $this->db->quote($session_id));

			$this->db->setQuery($query);

			return (string) $this->db->loadResult();
		}
		catch (\Exception $e)
		{
			return '';
		}
	}

	/**
	 * Write session data
	 *
	 * @param   string  $session_id    The session id
	 * @param   string  $session_data  The encoded session data
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function write($session_id, $session_data)
	{
		try
		{
			// Figure out if a row exists for the session ID
			$query = $this->db->getQuery(true)
				->select($this->db->quoteName('session_id'))
				->from($this->db->quoteName('#__session'))
				->where($this->db->quoteName('session_id') . ' = ' . $this->db->quote($session_id));

			$idExists = $this->db->setQuery($query)->loadResult();

			$query = $this->db->getQuery(true);

			if ($idExists)
			{
				$query->update($this->db->quoteName('#__session'))
					->set($this->db->quoteName('data') . ' = ' . $this->db->quote($session_data))
					->set($this->db->quoteName('time') . ' = ' . $this->db->quote((int) time()))
					->where($this->db->quoteName('session_id') . ' = ' . $this->db->quote($session_id));
			}
			else
			{
				$query->insert($this->db->quoteName('#__session'))
					->columns(array($this->db->quoteName('data'), $this->db->quoteName('time'), $this->db->quoteName('session_id')))
					->values(implode(', ', array($this->db->quote($session_data), (int) time(), $this->db->quote($session_id))));
			}

			// Try to insert the session data in the database table.
			$this->db->setQuery($query)->execute();

			return true;
		}
		catch (\Exception $e)
		{
			return false;
		}
	}
}
