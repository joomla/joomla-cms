<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * MySQL database driver
 *
 * @link        https://dev.mysql.com/doc/
 * @since       3.0.0
 * @deprecated  4.0  Use MySQLi or PDO MySQL instead
 */
class JDatabaseDriverMysql extends JDatabaseDriverMysqli
{
	/**
	 * The name of the database driver.
	 *
	 * @var    string
	 * @since  3.0.0
	 */
	public $name = 'mysql';

	/**
	 * Constructor.
	 *
	 * @param   array  $options  Array of database options with keys: host, user, password, database, select.
	 *
	 * @since   3.0.0
	 */
	public function __construct($options)
	{
		// PHP's `mysql` extension is not present in PHP 7, block instantiation in this environment
		if (PHP_MAJOR_VERSION >= 7)
		{
			throw new JDatabaseExceptionUnsupported(
				'This driver is unsupported in PHP 7, please use the MySQLi or PDO MySQL driver instead.'
			);
		}

		// Get some basic values from the options.
		$options['host'] = (isset($options['host'])) ? $options['host'] : 'localhost';
		$options['user'] = (isset($options['user'])) ? $options['user'] : '';
		$options['password'] = (isset($options['password'])) ? $options['password'] : '';
		$options['database'] = (isset($options['database'])) ? $options['database'] : '';
		$options['select'] = (isset($options['select'])) ? (bool) $options['select'] : true;

		// Finalize initialisation.
		parent::__construct($options);
	}

	/**
	 * Connects to the database if needed.
	 *
	 * @return  void  Returns void if the database connected successfully.
	 *
	 * @since   3.0.0
	 * @throws  RuntimeException
	 */
	public function connect()
	{
		if ($this->connection)
		{
			return;
		}

		// Make sure the MySQL extension for PHP is installed and enabled.
		if (!self::isSupported())
		{
			throw new JDatabaseExceptionUnsupported('Make sure the MySQL extension for PHP is installed and enabled.');
		}

		// Attempt to connect to the server.
		if (!($this->connection = @ mysql_connect($this->options['host'], $this->options['user'], $this->options['password'], true)))
		{
			throw new JDatabaseExceptionConnecting('Could not connect to MySQL server.');
		}

		// Set sql_mode to non_strict mode
		mysql_query("SET @@SESSION.sql_mode = '';", $this->connection);

		// If auto-select is enabled select the given database.
		if ($this->options['select'] && !empty($this->options['database']))
		{
			$this->select($this->options['database']);
		}

		// Pre-populate the UTF-8 Multibyte compatibility flag based on server version
		$this->utf8mb4 = $this->serverClaimsUtf8mb4Support();

		// Set the character set (needed for MySQL 4.1.2+).
		$this->utf = $this->setUtf();

		// Disable query cache and turn profiling ON in debug mode.
		if ($this->debug)
		{
			mysql_query('SET query_cache_type = 0;', $this->connection);

			if ($this->hasProfiling())
			{
				mysql_query('SET profiling_history_size = 100, profiling = 1;', $this->connection);
			}
		}
	}

	/**
	 * Disconnects the database.
	 *
	 * @return  void
	 *
	 * @since   3.0.0
	 */
	public function disconnect()
	{
		// Close the connection.
		if (is_resource($this->connection))
		{
			foreach ($this->disconnectHandlers as $h)
			{
				call_user_func_array($h, array( &$this));
			}

			mysql_close($this->connection);
		}

		$this->connection = null;
	}

	/**
	 * Method to escape a string for usage in an SQL statement.
	 *
	 * @param   string   $text   The string to be escaped.
	 * @param   boolean  $extra  Optional parameter to provide extra escaping.
	 *
	 * @return  string  The escaped string.
	 *
	 * @since   3.0.0
	 */
	public function escape($text, $extra = false)
	{
		if (is_int($text))
		{
			return $text;
		}

		if (is_float($text))
		{
			// Force the dot as a decimal point.
			return str_replace(',', '.', $text);
		}

		$this->connect();

		$result = mysql_real_escape_string($text, $this->getConnection());

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
	 * @since   3.0.0
	 */
	public static function isSupported()
	{
		return PHP_MAJOR_VERSION < 7 && function_exists('mysql_connect');
	}

	/**
	 * Determines if the connection to the server is active.
	 *
	 * @return  boolean  True if connected to the database engine.
	 *
	 * @since   3.0.0
	 */
	public function connected()
	{
		if (is_resource($this->connection))
		{
			return @mysql_ping($this->connection);
		}

		return false;
	}

	/**
	 * Get the number of affected rows by the last INSERT, UPDATE, REPLACE or DELETE for the previous executed SQL statement.
	 *
	 * @return  integer  The number of affected rows.
	 *
	 * @since   3.0.0
	 */
	public function getAffectedRows()
	{
		$this->connect();

		return mysql_affected_rows($this->connection);
	}

	/**
	 * Get the number of returned rows for the previous executed SQL statement.
	 * This command is only valid for statements like SELECT or SHOW that return an actual result set.
	 * To retrieve the number of rows affected by an INSERT, UPDATE, REPLACE or DELETE query, use getAffectedRows().
	 *
	 * @param   resource  $cursor  An optional database cursor resource to extract the row count from.
	 *
	 * @return  integer   The number of returned rows.
	 *
	 * @since   3.0.0
	 */
	public function getNumRows($cursor = null)
	{
		$this->connect();

		return mysql_num_rows($cursor ? $cursor : $this->cursor);
	}

	/**
	 * Get the version of the database connector.
	 *
	 * @return  string  The database connector version.
	 *
	 * @since   3.0.0
	 */
	public function getVersion()
	{
		$this->connect();

		return mysql_get_server_info($this->connection);
	}

	/**
	 * Method to get the auto-incremented value from the last INSERT statement.
	 *
	 * @return  integer  The value of the auto-increment field from the last inserted row.
	 *
	 * @since   3.0.0
	 */
	public function insertid()
	{
		$this->connect();

		return mysql_insert_id($this->connection);
	}

	/**
	 * Execute the SQL statement.
	 *
	 * @return  mixed  A database cursor resource on success, boolean false on failure.
	 *
	 * @since   3.0.0
	 * @throws  RuntimeException
	 */
	public function execute()
	{
		$this->connect();

		// Take a local copy so that we don't modify the original query and cause issues later
		$query = $this->replacePrefix((string) $this->sql);

		if (!($this->sql instanceof JDatabaseQuery) && ($this->limit > 0 || $this->offset > 0))
		{
			$query .= ' LIMIT ' . $this->offset . ', ' . $this->limit;
		}

		if (!is_resource($this->connection))
		{
			JLog::add(JText::sprintf('JLIB_DATABASE_QUERY_FAILED', $this->errorNum, $this->errorMsg), JLog::ERROR, 'database');
			throw new JDatabaseExceptionExecuting($query, $this->errorMsg, $this->errorNum);
		}

		// Increment the query counter.
		$this->count++;

		// Reset the error values.
		$this->errorNum = 0;
		$this->errorMsg = '';

		// If debugging is enabled then let's log the query.
		if ($this->debug)
		{
			// Add the query to the object queue.
			$this->log[] = $query;

			JLog::add($query, JLog::DEBUG, 'databasequery');

			$this->timings[] = microtime(true);
		}

		// Execute the query. Error suppression is used here to prevent warnings/notices that the connection has been lost.
		$this->cursor = @mysql_query($query, $this->connection);

		if ($this->debug)
		{
			$this->timings[] = microtime(true);

			if (defined('DEBUG_BACKTRACE_IGNORE_ARGS'))
			{
				$this->callStacks[] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
			}
			else
			{
				$this->callStacks[] = debug_backtrace();
			}
		}

		// If an error occurred handle it.
		if (!$this->cursor)
		{
			// Get the error number and message before we execute any more queries.
			$this->errorNum = $this->getErrorNumber();
			$this->errorMsg = $this->getErrorMessage();

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
					// Get the error number and message.
					$this->errorNum = $this->getErrorNumber();
					$this->errorMsg = $this->getErrorMessage();

					// Throw the normal query exception.
					JLog::add(JText::sprintf('JLIB_DATABASE_QUERY_FAILED', $this->errorNum, $this->errorMsg), JLog::ERROR, 'database-error');

					throw new JDatabaseExceptionExecuting($query, $this->errorMsg, $this->errorNum, $e);
				}

				// Since we were able to reconnect, run the query again.
				return $this->execute();
			}
			// The server was not disconnected.
			else
			{
				// Throw the normal query exception.
				JLog::add(JText::sprintf('JLIB_DATABASE_QUERY_FAILED', $this->errorNum, $this->errorMsg), JLog::ERROR, 'database-error');

				throw new JDatabaseExceptionExecuting($query, $this->errorMsg, $this->errorNum);
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
	 * @since   3.0.0
	 * @throws  RuntimeException
	 */
	public function select($database)
	{
		$this->connect();

		if (!$database)
		{
			return false;
		}

		if (!mysql_select_db($database, $this->connection))
		{
			throw new JDatabaseExceptionConnecting('Could not connect to MySQL database.');
		}

		return true;
	}

	/**
	 * Set the connection to use UTF-8 character encoding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.0.0
	 */
	public function setUtf()
	{
		// If UTF is not supported return false immediately
		if (!$this->utf)
		{
			return false;
		}

		// Make sure we're connected to the server
		$this->connect();

		// Which charset should I use, plain utf8 or multibyte utf8mb4?
		$charset = $this->utf8mb4 ? 'utf8mb4' : 'utf8';

		$result = @mysql_set_charset($charset, $this->connection);

		/**
		 * If I could not set the utf8mb4 charset then the server doesn't support utf8mb4 despite claiming otherwise.
		 * This happens on old MySQL server versions (less than 5.5.3) using the mysqlnd PHP driver. Since mysqlnd
		 * masks the server version and reports only its own we can not be sure if the server actually does support
		 * UTF-8 Multibyte (i.e. it's MySQL 5.5.3 or later). Since the utf8mb4 charset is undefined in this case we
		 * catch the error and determine that utf8mb4 is not supported!
		 */
		if (!$result && $this->utf8mb4)
		{
			$this->utf8mb4 = false;
			$result = @mysql_set_charset('utf8', $this->connection);
		}

		return $result;
	}

	/**
	 * Method to fetch a row from the result set cursor as an array.
	 *
	 * @param   mixed  $cursor  The optional result set cursor from which to fetch the row.
	 *
	 * @return  mixed  Either the next row from the result set or false if there are no more rows.
	 *
	 * @since   3.0.0
	 */
	protected function fetchArray($cursor = null)
	{
		return mysql_fetch_row($cursor ? $cursor : $this->cursor);
	}

	/**
	 * Method to fetch a row from the result set cursor as an associative array.
	 *
	 * @param   mixed  $cursor  The optional result set cursor from which to fetch the row.
	 *
	 * @return  mixed  Either the next row from the result set or false if there are no more rows.
	 *
	 * @since   3.0.0
	 */
	protected function fetchAssoc($cursor = null)
	{
		return mysql_fetch_assoc($cursor ? $cursor : $this->cursor);
	}

	/**
	 * Method to fetch a row from the result set cursor as an object.
	 *
	 * @param   mixed   $cursor  The optional result set cursor from which to fetch the row.
	 * @param   string  $class   The class name to use for the returned row object.
	 *
	 * @return  mixed   Either the next row from the result set or false if there are no more rows.
	 *
	 * @since   3.0.0
	 */
	protected function fetchObject($cursor = null, $class = 'stdClass')
	{
		return mysql_fetch_object($cursor ? $cursor : $this->cursor, $class);
	}

	/**
	 * Method to free up the memory used for the result set.
	 *
	 * @param   mixed  $cursor  The optional result set cursor from which to fetch the row.
	 *
	 * @return  void
	 *
	 * @since   3.0.0
	 */
	protected function freeResult($cursor = null)
	{
		mysql_free_result($cursor ? $cursor : $this->cursor);
	}

	/**
	 * Internal function to check if profiling is available
	 *
	 * @return  boolean
	 *
	 * @since   3.1.3
	 */
	private function hasProfiling()
	{
		try
		{
			$res = mysql_query("SHOW VARIABLES LIKE 'have_profiling'", $this->connection);
			$row = mysql_fetch_assoc($res);

			return isset($row);
		}
		catch (Exception $e)
		{
			return false;
		}
	}

	/**
	 * Does the database server claim to have support for UTF-8 Multibyte (utf8mb4) collation?
	 *
	 * libmysql supports utf8mb4 since 5.5.3 (same version as the MySQL server). mysqlnd supports utf8mb4 since 5.0.9.
	 *
	 * @return  boolean
	 *
	 * @since   CMS 3.5.0
	 */
	private function serverClaimsUtf8mb4Support()
	{
		$client_version = mysql_get_client_info();
		$server_version = $this->getVersion();

		if (version_compare($server_version, '5.5.3', '<'))
		{
			return false;
		}
		else
		{
			if (strpos($client_version, 'mysqlnd') !== false)
			{
				$client_version = preg_replace('/^\D+([\d.]+).*/', '$1', $client_version);

				return version_compare($client_version, '5.0.9', '>=');
			}
			else
			{
				return version_compare($client_version, '5.5.3', '>=');
			}
		}
	}

	/**
	 * Return the actual SQL Error number
	 *
	 * @return  integer  The SQL Error number
	 *
	 * @since   3.4.6
	 */
	protected function getErrorNumber()
	{
		return (int) mysql_errno($this->connection);
	}

	/**
	 * Return the actual SQL Error message
	 *
	 * @return  string  The SQL Error message
	 *
	 * @since   3.4.6
	 */
	protected function getErrorMessage()
	{
		$errorMessage = (string) mysql_error($this->connection);

		// Replace the Databaseprefix with `#__` if we are not in Debug
		if (!$this->debug)
		{
			$errorMessage = str_replace($this->tablePrefix, '#__', $errorMessage);
		}

		return $errorMessage;
	}
}
