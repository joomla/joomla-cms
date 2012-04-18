<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * MySQLi database driver
 *
 * @package     Joomla.Platform
 * @subpackage  Database
 * @see         http://php.net/manual/en/book.mysqli.php
 * @since       12.1
 */
class JDatabaseDriverMysqli extends JDatabaseDriverMysql
{
	/**
	 * The name of the database driver.
	 *
	 * @var    string
	 * @since  12.1
	 */
	public $name = 'mysqli';

	/**
	 * Constructor.
	 *
	 * @param   array  $options  List of options used to configure the connection
	 *
	 * @since   12.1
	 */
	public function __construct($options)
	{
		// Get some basic values from the options.
		$options['host'] = (isset($options['host'])) ? $options['host'] : 'localhost';
		$options['user'] = (isset($options['user'])) ? $options['user'] : 'root';
		$options['password'] = (isset($options['password'])) ? $options['password'] : '';
		$options['database'] = (isset($options['database'])) ? $options['database'] : '';
		$options['select'] = (isset($options['select'])) ? (bool) $options['select'] : true;
		$options['port'] = null;
		$options['socket'] = null;

		// Finalize initialisation.
		parent::__construct($options);
	}

	/**
	 * Connects to the database if needed.
	 *
	 * @return  void  Returns void if the database connected successfully.
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function connect()
	{
		if ($this->connection)
		{
			return;
		}

		/*
		 * Unlike mysql_connect(), mysqli_connect() takes the port and socket as separate arguments. Therefore, we
		 * have to extract them from the host string.
		 */
		$tmp = substr(strstr($this->options['host'], ':'), 1);
		if (!empty($tmp))
		{
			// Get the port number or socket name
			if (is_numeric($tmp))
			{
				$this->options['port'] = $tmp;
			}
			else
			{
				$this->options['socket'] = $tmp;
			}

			// Extract the host name only
			$this->options['host'] = substr($this->options['host'], 0, strlen($this->options['host']) - (strlen($tmp) + 1));

			// This will take care of the following notation: ":3306"
			if ($this->options['host'] == '')
			{
				$this->options['host'] = 'localhost';
			}
		}

		// Make sure the MySQLi extension for PHP is installed and enabled.
		if (!function_exists('mysqli_connect'))
		{
			throw new RuntimeException('The MySQL adapter mysqli is not available');
		}

		$this->connection = @mysqli_connect(
			$this->options['host'], $this->options['user'], $this->options['password'], null, $this->options['port'], $this->options['socket']
		);

		// Attempt to connect to the server.
		if (!$this->connection)
		{
			throw new RuntimeException('Could not connect to MySQL.');
		}

		// Set sql_mode to non_strict mode
		mysqli_query($this->connection, "SET @@SESSION.sql_mode = '';");

		// If auto-select is enabled select the given database.
		if ($this->options['select'] && !empty($this->options['database']))
		{
			$this->select($this->options['database']);
		}

		// Set charactersets (needed for MySQL 4.1.2+).
		$this->setUTF();
	}

	/**
	 * Destructor.
	 *
	 * @since   12.1
	 */
	public function __destruct()
	{
		if (is_callable($this->connection, 'close'))
		{
			mysqli_close($this->connection);
		}
	}

	/**
	 * Method to escape a string for usage in an SQL statement.
	 *
	 * @param   string   $text   The string to be escaped.
	 * @param   boolean  $extra  Optional parameter to provide extra escaping.
	 *
	 * @return  string  The escaped string.
	 *
	 * @since   12.1
	 */
	public function escape($text, $extra = false)
	{
		$this->connect();

		$result = mysqli_real_escape_string($this->getConnection(), $text);

		if ($extra)
		{
			$result = addcslashes($result, '%_');
		}

		return $result;
	}

	/**
	 * Test to see if the MySQL connector is available.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   12.1
	 */
	public static function isSupported()
	{
		return (function_exists('mysqli_connect'));
	}

	/**
	 * Determines if the connection to the server is active.
	 *
	 * @return  boolean  True if connected to the database engine.
	 *
	 * @since   12.1
	 */
	public function connected()
	{
		if (is_object($this->connection))
		{
			return mysqli_ping($this->connection);
		}

		return false;
	}

	/**
	 * Get the number of affected rows for the previous executed SQL statement.
	 *
	 * @return  integer  The number of affected rows.
	 *
	 * @since   12.1
	 */
	public function getAffectedRows()
	{
		$this->connect();

		return mysqli_affected_rows($this->connection);
	}

	/**
	 * Get the number of returned rows for the previous executed SQL statement.
	 *
	 * @param   resource  $cursor  An optional database cursor resource to extract the row count from.
	 *
	 * @return  integer   The number of returned rows.
	 *
	 * @since   12.1
	 */
	public function getNumRows($cursor = null)
	{
		return mysqli_num_rows($cursor ? $cursor : $this->cursor);
	}

	/**
	 * Get the version of the database connector.
	 *
	 * @return  string  The database connector version.
	 *
	 * @since   12.1
	 */
	public function getVersion()
	{
		$this->connect();

		return mysqli_get_server_info($this->connection);
	}

	/**
	 * Method to get the auto-incremented value from the last INSERT statement.
	 *
	 * @return  integer  The value of the auto-increment field from the last inserted row.
	 *
	 * @since   12.1
	 */
	public function insertid()
	{
		$this->connect();

		return mysqli_insert_id($this->connection);
	}

	/**
	 * Execute the SQL statement.
	 *
	 * @return  mixed  A database cursor resource on success, boolean false on failure.
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function execute()
	{
		$this->connect();

		if (!is_object($this->connection))
		{
			JLog::add(JText::sprintf('JLIB_DATABASE_QUERY_FAILED', $this->errorNum, $this->errorMsg), JLog::ERROR, 'database');
			throw new RuntimeException($this->errorMsg, $this->errorNum);
		}

		// Take a local copy so that we don't modify the original query and cause issues later
		$sql = $this->replacePrefix((string) $this->sql);
		if ($this->limit > 0 || $this->offset > 0)
		{
			$sql .= ' LIMIT ' . $this->offset . ', ' . $this->limit;
		}

		// If debugging is enabled then let's log the query.
		if ($this->debug)
		{
			// Increment the query counter and add the query to the object queue.
			$this->count++;
			$this->log[] = $sql;

			JLog::add($sql, JLog::DEBUG, 'databasequery');
		}

		// Reset the error values.
		$this->errorNum = 0;
		$this->errorMsg = '';

		// Execute the query. Error suppression is used here to prevent warnings/notices that the connection has been lost.
		$this->cursor = @mysqli_query($this->connection, $sql);

		// If an error occurred handle it.
		if (!$this->cursor)
		{
			// Check if the server was disconnected.
			if (!$this->connected())
			{
				try
				{
					// Attempt to reconnect.
					$this->connection = null;
					$this->connect();
				}
				// If connect fails, ignore that exception and throw the normal exception.
				catch (RuntimeException $e)
				{
					$this->errorNum = (int) mysqli_errno($this->connection);
					$this->errorMsg = (string) mysqli_error($this->connection) . ' SQL=' . $sql;

					JLog::add(JText::sprintf('JLIB_DATABASE_QUERY_FAILED', $this->errorNum, $this->errorMsg), JLog::ERROR, 'databasequery');
					throw new RuntimeException($this->errorMsg, $this->errorNum);
				}

				// Since we were able to reconnect, run the query again.
				return $this->execute();
			}
			// The server was not disconnected.
			else
			{
				$this->errorNum = (int) mysqli_errno($this->connection);
				$this->errorMsg = (string) mysqli_error($this->connection) . ' SQL=' . $sql;

				JLog::add(JText::sprintf('JLIB_DATABASE_QUERY_FAILED', $this->errorNum, $this->errorMsg), JLog::ERROR, 'databasequery');
				throw new RuntimeException($this->errorMsg, $this->errorNum);
			}
		}

		return $this->cursor;
	}

	/**
	 * Select a database for use.
	 *
	 * @param   string  $database  The name of the database to select for use.
	 *
	 * @return  boolean  True if the database was successfully selected.
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function select($database)
	{
		$this->connect();

		if (!$database)
		{
			return false;
		}

		if (!mysqli_select_db($this->connection, $database))
		{
			throw new RuntimeException('Could not connect to database.');
		}

		return true;
	}

	/**
	 * Set the connection to use UTF-8 character encoding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   12.1
	 */
	public function setUTF()
	{
		$this->connect();

		mysqli_query($this->connection, "SET NAMES 'utf8'");
	}

	/**
	 * Method to fetch a row from the result set cursor as an array.
	 *
	 * @param   mixed  $cursor  The optional result set cursor from which to fetch the row.
	 *
	 * @return  mixed  Either the next row from the result set or false if there are no more rows.
	 *
	 * @since   12.1
	 */
	protected function fetchArray($cursor = null)
	{
		return mysqli_fetch_row($cursor ? $cursor : $this->cursor);
	}

	/**
	 * Method to fetch a row from the result set cursor as an associative array.
	 *
	 * @param   mixed  $cursor  The optional result set cursor from which to fetch the row.
	 *
	 * @return  mixed  Either the next row from the result set or false if there are no more rows.
	 *
	 * @since   12.1
	 */
	protected function fetchAssoc($cursor = null)
	{
		return mysqli_fetch_assoc($cursor ? $cursor : $this->cursor);
	}

	/**
	 * Method to fetch a row from the result set cursor as an object.
	 *
	 * @param   mixed   $cursor  The optional result set cursor from which to fetch the row.
	 * @param   string  $class   The class name to use for the returned row object.
	 *
	 * @return  mixed   Either the next row from the result set or false if there are no more rows.
	 *
	 * @since   12.1
	 */
	protected function fetchObject($cursor = null, $class = 'stdClass')
	{
		return mysqli_fetch_object($cursor ? $cursor : $this->cursor, $class);
	}

	/**
	 * Method to free up the memory used for the result set.
	 *
	 * @param   mixed  $cursor  The optional result set cursor from which to fetch the row.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function freeResult($cursor = null)
	{
		mysqli_free_result($cursor ? $cursor : $this->cursor);
	}
}
