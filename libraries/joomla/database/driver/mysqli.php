<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * MySQLi database driver
 *
 * @link   https://www.php.net/manual/en/book.mysqli.php
 * @since  3.0.0
 */
class JDatabaseDriverMysqli extends JDatabaseDriver
{
	/**
	 * The name of the database driver.
	 *
	 * @var    string
	 * @since  3.0.0
	 */
	public $name = 'mysqli';

	/**
	 * The type of the database server family supported by this driver.
	 *
	 * @var    string
	 * @since  CMS 3.5.0
	 */
	public $serverType = 'mysql';

	/**
	 * @var    mysqli  The database connection resource.
	 * @since  1.7.0
	 */
	protected $connection;

	/**
	 * The character(s) used to quote SQL statement names such as table names or field names,
	 * etc. The child classes should define this as necessary.  If a single character string the
	 * same character is used for both sides of the quoted name, else the first character will be
	 * used for the opening quote and the second for the closing quote.
	 *
	 * @var    string
	 * @since  3.0.1
	 */
	protected $nameQuote = '`';

	/**
	 * The null or zero representation of a timestamp for the database driver.  This should be
	 * defined in child classes to hold the appropriate value for the engine.
	 *
	 * @var    string
	 * @since  3.0.1
	 */
	protected $nullDate = '0000-00-00 00:00:00';

	/**
	 * @var    string  The minimum supported database version.
	 * @since  3.0.1
	 */
	protected static $dbMinimum = '5.0.4';

	/**
	 * Constructor.
	 *
	 * @param   array  $options  List of options used to configure the connection
	 *
	 * @since   3.0.0
	 */
	public function __construct($options)
	{
		// Get some basic values from the options.
		$options['host']     = (isset($options['host'])) ? $options['host'] : 'localhost';
		$options['user']     = (isset($options['user'])) ? $options['user'] : '';
		$options['password'] = (isset($options['password'])) ? $options['password'] : '';
		$options['database'] = (isset($options['database'])) ? $options['database'] : '';
		$options['select']   = (isset($options['select'])) ? (bool) $options['select'] : true;
		$options['port']     = (isset($options['port'])) ? (int) $options['port'] : null;
		$options['socket']   = (isset($options['socket'])) ? $options['socket'] : null;

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

		/*
		 * Unlike mysql_connect(), mysqli_connect() takes the port and socket as separate arguments. Therefore, we
		 * have to extract them from the host string.
		 */
		$port = isset($this->options['port']) ? $this->options['port'] : 3306;
		$regex = '/^(?P<host>((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?))(:(?P<port>.+))?$/';

		if (preg_match($regex, $this->options['host'], $matches))
		{
			// It's an IPv4 address with or without port
			$this->options['host'] = $matches['host'];

			if (!empty($matches['port']))
			{
				$port = $matches['port'];
			}
		}
		elseif (preg_match('/^(?P<host>\[.*\])(:(?P<port>.+))?$/', $this->options['host'], $matches))
		{
			// We assume square-bracketed IPv6 address with or without port, e.g. [fe80:102::2%eth1]:3306
			$this->options['host'] = $matches['host'];

			if (!empty($matches['port']))
			{
				$port = $matches['port'];
			}
		}
		elseif (preg_match('/^(?P<host>(\w+:\/{2,3})?[a-z0-9\.\-]+)(:(?P<port>[^:]+))?$/i', $this->options['host'], $matches))
		{
			// Named host (e.g example.com or localhost) with or without port
			$this->options['host'] = $matches['host'];

			if (!empty($matches['port']))
			{
				$port = $matches['port'];
			}
		}
		elseif (preg_match('/^:(?P<port>[^:]+)$/', $this->options['host'], $matches))
		{
			// Empty host, just port, e.g. ':3306'
			$this->options['host'] = 'localhost';
			$port = $matches['port'];
		}
		// ... else we assume normal (naked) IPv6 address, so host and port stay as they are or default

		// Get the port number or socket name
		if (is_numeric($port))
		{
			$this->options['port'] = (int) $port;
		}
		else
		{
			$this->options['socket'] = $port;
		}

		// Make sure the MySQLi extension for PHP is installed and enabled.
		if (!self::isSupported())
		{
			throw new JDatabaseExceptionUnsupported('The MySQLi extension for PHP is not installed or enabled.');
		}

		$this->connection = @mysqli_connect(
			$this->options['host'], $this->options['user'], $this->options['password'], null, $this->options['port'], $this->options['socket']
		);

		// Attempt to connect to the server.
		if (!$this->connection)
		{
			throw new JDatabaseExceptionConnecting('Could not connect to MySQL server.');
		}

		// Set sql_mode to non_strict mode
		mysqli_query($this->connection, "SET @@SESSION.sql_mode = '';");

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
			if ($this->hasQueryCacheEnabled())
			{
				mysqli_query($this->connection, 'SET query_cache_type = 0;');
			}

			if ($this->hasProfiling())
			{
				mysqli_query($this->connection, 'SET profiling_history_size = 100, profiling = 1;');
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
		if ($this->connection instanceof mysqli && $this->connection->stat() !== false)
		{
			foreach ($this->disconnectHandlers as $h)
			{
				call_user_func_array($h, array( &$this));
			}

			mysqli_close($this->connection);
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
	 * @since   3.0.0
	 */
	public static function isSupported()
	{
		return function_exists('mysqli_connect');
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
		if (is_object($this->connection))
		{
			return mysqli_ping($this->connection);
		}

		return false;
	}

	/**
	 * Drops a table from the database.
	 *
	 * @param   string   $tableName  The name of the database table to drop.
	 * @param   boolean  $ifExists   Optionally specify that the table must exist before it is dropped.
	 *
	 * @return  JDatabaseDriverMysqli  Returns this object to support chaining.
	 *
	 * @since   3.0.1
	 * @throws  RuntimeException
	 */
	public function dropTable($tableName, $ifExists = true)
	{
		$this->connect();

		$query = $this->getQuery(true);

		$this->setQuery('DROP TABLE ' . ($ifExists ? 'IF EXISTS ' : '') . $query->quoteName($tableName));

		$this->execute();

		return $this;
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

		return mysqli_affected_rows($this->connection);
	}

	/**
	 * Method to get the database collation.
	 *
	 * @return  mixed  The collation in use by the database (string) or boolean false if not supported.
	 *
	 * @since   3.0.1
	 * @throws  RuntimeException
	 */
	public function getCollation()
	{
		$this->connect();

		// Attempt to get the database collation by accessing the server system variable.
		$this->setQuery('SHOW VARIABLES LIKE "collation_database"');
		$result = $this->loadObject();

		if (property_exists($result, 'Value'))
		{
			return $result->Value;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to get the database connection collation, as reported by the driver. If the connector doesn't support
	 * reporting this value please return an empty string.
	 *
	 * @return  string
	 */
	public function getConnectionCollation()
	{
		$this->connect();

		// Attempt to get the database collation by accessing the server system variable.
		$this->setQuery('SHOW VARIABLES LIKE "collation_connection"');
		$result = $this->loadObject();

		if (property_exists($result, 'Value'))
		{
			return $result->Value;
		}
		else
		{
			return false;
		}
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
		return mysqli_num_rows($cursor ? $cursor : $this->cursor);
	}

	/**
	 * Shows the table CREATE statement that creates the given tables.
	 *
	 * @param   mixed  $tables  A table name or a list of table names.
	 *
	 * @return  array  A list of the create SQL for the tables.
	 *
	 * @since   3.0.0
	 * @throws  RuntimeException
	 */
	public function getTableCreate($tables)
	{
		$this->connect();

		$result = array();

		// Sanitize input to an array and iterate over the list.
		settype($tables, 'array');

		foreach ($tables as $table)
		{
			// Set the query to get the table CREATE statement.
			$this->setQuery('SHOW CREATE table ' . $this->quoteName($this->escape($table)));
			$row = $this->loadRow();

			// Populate the result array based on the create statements.
			$result[$table] = $row[1];
		}

		return $result;
	}

	/**
	 * Retrieves field information about a given table.
	 *
	 * @param   string   $table     The name of the database table.
	 * @param   boolean  $typeOnly  True to only return field types.
	 *
	 * @return  array  An array of fields for the database table.
	 *
	 * @since   3.0.1
	 * @throws  RuntimeException
	 */
	public function getTableColumns($table, $typeOnly = true)
	{
		$this->connect();

		$result = array();

		// Set the query to get the table fields statement.
		$this->setQuery('SHOW FULL COLUMNS FROM ' . $this->quoteName($this->escape($table)));
		$fields = $this->loadObjectList();

		// If we only want the type as the value add just that to the list.
		if ($typeOnly)
		{
			foreach ($fields as $field)
			{
				$result[$field->Field] = preg_replace('/[(0-9)]/', '', $field->Type);
			}
		}
		// If we want the whole field data object add that to the list.
		else
		{
			foreach ($fields as $field)
			{
				$result[$field->Field] = $field;
			}
		}

		return $result;
	}

	/**
	 * Get the details list of keys for a table.
	 *
	 * @param   string  $table  The name of the table.
	 *
	 * @return  array  An array of the column specification for the table.
	 *
	 * @since   3.0.1
	 * @throws  RuntimeException
	 */
	public function getTableKeys($table)
	{
		$this->connect();

		// Get the details columns information.
		$this->setQuery('SHOW KEYS FROM ' . $this->quoteName($table));
		$keys = $this->loadObjectList();

		return $keys;
	}

	/**
	 * Method to get an array of all tables in the database.
	 *
	 * @return  array  An array of all the tables in the database.
	 *
	 * @since   3.0.1
	 * @throws  RuntimeException
	 */
	public function getTableList()
	{
		$this->connect();

		// Set the query to get the tables statement.
		$this->setQuery('SHOW TABLES');
		$tables = $this->loadColumn();

		return $tables;
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

		return mysqli_get_server_info($this->connection);
	}

	/**
	 * Method to get the auto-incremented value from the last INSERT statement.
	 *
	 * @return  mixed  The value of the auto-increment field from the last inserted row.
	 *                 If the value is greater than maximal int value, it will return a string.
	 *
	 * @since   3.0.0
	 */
	public function insertid()
	{
		$this->connect();

		return mysqli_insert_id($this->connection);
	}

	/**
	 * Locks a table in the database.
	 *
	 * @param   string  $table  The name of the table to unlock.
	 *
	 * @return  JDatabaseDriverMysqli  Returns this object to support chaining.
	 *
	 * @since   3.0.1
	 * @throws  RuntimeException
	 */
	public function lockTable($table)
	{
		$this->setQuery('LOCK TABLES ' . $this->quoteName($table) . ' WRITE')->execute();

		return $this;
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

		if (!is_object($this->connection))
		{
			JLog::add(JText::sprintf('JLIB_DATABASE_QUERY_FAILED', $this->errorNum, $this->errorMsg), JLog::ERROR, 'database');
			throw new JDatabaseExceptionExecuting($query, $this->errorMsg, $this->errorNum);
		}

		// Increment the query counter.
		$this->count++;

		// Reset the error values.
		$this->errorNum = 0;
		$this->errorMsg = '';
		$memoryBefore   = null;

		// If debugging is enabled then let's log the query.
		if ($this->debug)
		{
			// Add the query to the object queue.
			$this->log[] = $query;

			JLog::add($query, JLog::DEBUG, 'databasequery');

			$this->timings[] = microtime(true);

			if (is_object($this->cursor))
			{
				// Avoid warning if result already freed by third-party library
				@$this->freeResult();
			}

			$memoryBefore = memory_get_usage();
		}

		// Execute the query. Error suppression is used here to prevent warnings/notices that the connection has been lost.
		$this->cursor = @mysqli_query($this->connection, $query);

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

			$this->callStacks[count($this->callStacks) - 1][0]['memory'] = array(
				$memoryBefore,
				memory_get_usage(),
				is_object($this->cursor) ? $this->getNumRows() : null,
			);
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

					JLog::add(JText::sprintf('JLIB_DATABASE_QUERY_FAILED', $this->errorNum, $this->errorMsg), JLog::ERROR, 'database-error');

					throw new JDatabaseExceptionExecuting($query, $this->errorMsg, $this->errorNum, $e);
				}

				// Since we were able to reconnect, run the query again.
				return $this->execute();
			}
			// The server was not disconnected.
			else
			{
				JLog::add(JText::sprintf('JLIB_DATABASE_QUERY_FAILED', $this->errorNum, $this->errorMsg), JLog::ERROR, 'database-error');

				throw new JDatabaseExceptionExecuting($query, $this->errorMsg, $this->errorNum);
			}
		}

		return $this->cursor;
	}

	/**
	 * Renames a table in the database.
	 *
	 * @param   string  $oldTable  The name of the table to be renamed
	 * @param   string  $newTable  The new name for the table.
	 * @param   string  $backup    Not used by MySQL.
	 * @param   string  $prefix    Not used by MySQL.
	 *
	 * @return  JDatabaseDriverMysqli  Returns this object to support chaining.
	 *
	 * @since   3.0.1
	 * @throws  RuntimeException
	 */
	public function renameTable($oldTable, $newTable, $backup = null, $prefix = null)
	{
		$this->setQuery('RENAME TABLE ' . $oldTable . ' TO ' . $newTable)->execute();

		return $this;
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

		if (!mysqli_select_db($this->connection, $database))
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

		$result = @$this->connection->set_charset($charset);

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
			$result = @$this->connection->set_charset('utf8');
		}

		return $result;
	}

	/**
	 * Method to commit a transaction.
	 *
	 * @param   boolean  $toSavepoint  If true, commit to the last savepoint.
	 *
	 * @return  void
	 *
	 * @since   3.0.1
	 * @throws  RuntimeException
	 */
	public function transactionCommit($toSavepoint = false)
	{
		$this->connect();

		if (!$toSavepoint || $this->transactionDepth <= 1)
		{
			if ($this->setQuery('COMMIT')->execute())
			{
				$this->transactionDepth = 0;
			}

			return;
		}

		$this->transactionDepth--;
	}

	/**
	 * Method to roll back a transaction.
	 *
	 * @param   boolean  $toSavepoint  If true, rollback to the last savepoint.
	 *
	 * @return  void
	 *
	 * @since   3.0.1
	 * @throws  RuntimeException
	 */
	public function transactionRollback($toSavepoint = false)
	{
		$this->connect();

		if (!$toSavepoint || $this->transactionDepth <= 1)
		{
			if ($this->setQuery('ROLLBACK')->execute())
			{
				$this->transactionDepth = 0;
			}

			return;
		}

		$savepoint = 'SP_' . ($this->transactionDepth - 1);
		$this->setQuery('ROLLBACK TO SAVEPOINT ' . $this->quoteName($savepoint));

		if ($this->execute())
		{
			$this->transactionDepth--;
		}
	}

	/**
	 * Method to initialize a transaction.
	 *
	 * @param   boolean  $asSavepoint  If true and a transaction is already active, a savepoint will be created.
	 *
	 * @return  void
	 *
	 * @since   3.0.1
	 * @throws  RuntimeException
	 */
	public function transactionStart($asSavepoint = false)
	{
		$this->connect();

		if (!$asSavepoint || !$this->transactionDepth)
		{
			if ($this->setQuery('START TRANSACTION')->execute())
			{
				$this->transactionDepth = 1;
			}

			return;
		}

		$savepoint = 'SP_' . $this->transactionDepth;
		$this->setQuery('SAVEPOINT ' . $this->quoteName($savepoint));

		if ($this->execute())
		{
			$this->transactionDepth++;
		}
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
		return mysqli_fetch_row($cursor ? $cursor : $this->cursor);
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
	 * @since   3.0.0
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
	 * @since   3.0.0
	 */
	protected function freeResult($cursor = null)
	{
		mysqli_free_result($cursor ? $cursor : $this->cursor);

		if ((! $cursor) || ($cursor === $this->cursor))
		{
			$this->cursor = null;
		}
	}

	/**
	 * Unlocks tables in the database.
	 *
	 * @return  JDatabaseDriverMysqli  Returns this object to support chaining.
	 *
	 * @since   3.0.0
	 * @throws  RuntimeException
	 */
	public function unlockTables()
	{
		$this->setQuery('UNLOCK TABLES')->execute();

		return $this;
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
			$res = mysqli_query($this->connection, "SHOW VARIABLES LIKE 'have_profiling'");
			$row = mysqli_fetch_assoc($res);

			return isset($row);
		}
		catch (Exception $e)
		{
			return false;
		}
	}

	/**
	 * Internal function to check if query cache is enabled.
	 *
	 * @return  boolean
	 *
	 * @since   3.9.25
	 */
	private function hasQueryCacheEnabled()
	{
		try
		{
			$res = mysqli_query($this->connection, "SHOW VARIABLES LIKE 'query_cache_type'");
			$row = mysqli_fetch_assoc($res);

			return isset($row['Value']) && $row['Value'] === 'ON';
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
		$client_version = mysqli_get_client_info();
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
		return (int) mysqli_errno($this->connection);
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
		$errorMessage = (string) mysqli_error($this->connection);

		// Replace the Databaseprefix with `#__` if we are not in Debug
		if (!$this->debug)
		{
			$errorMessage = str_replace($this->tablePrefix, '#__', $errorMessage);
		}

		return $errorMessage;
	}
}
