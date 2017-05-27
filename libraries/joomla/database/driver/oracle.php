<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Oracle database driver
 *
 * @link   https://secure.php.net/pdo
 * @since  12.1
 */
class JDatabaseDriverOracle extends JDatabaseDriver
{
	/**
	 * The name of the database driver.
	 *
	 * @var    string
	 * @since  12.1
	 */
	public $name = 'oracle';

	/**
	 * The type of the database server family supported by this driver.
	 *
	 * @var    string
	 * @since  CMS 3.5.0
	 */
	public $serverType = 'oracle';

	/**
	 * The character(s) used to quote SQL statement names such as table names or field names,
	 * etc.  The child classes should define this as necessary.  If a single character string the
	 * same character is used for both sides of the quoted name, else the first character will be
	 * used for the opening quote and the second for the closing quote.
	 *
	 * @var    string
	 * @since  12.1
	 */
	protected $nameQuote = '"';

	/**
	 * Returns the current commit mode
	 *
	 * @var   int
	 * @since 12.1
	 */
	protected $commitMode = OCI_COMMIT_ON_SUCCESS;

	/**
	 * Returns the current dateformat
	 *
	 * @var   string
	 * @since 12.1
	 */
	protected $dateformat;

	/**
	 * Returns the current character set
	 *
	 * @var   string
	 * @since 12.1
	 */
	protected $charset;

	/**
    * Saves the number of rows value
    * before it gets reset by freeResult().
    *
    * @var int
    */
	protected $numRows = 0;

	/**
    * Is used to decide whether a result set
    * should generate lowercase field names
    *
    * @var boolean
    */
	protected $toLower = true;

	/**
    * Contains the query type of the
    * query about to be executed
    *
    * @var   string
    */
	protected $queryType = '';

	/**
    * Is used to decide whether a result set
    * should return the LOB values or the LOB objects
    */
	protected $returnLobs = true;

	/**
	 * @var    resource  The prepared statement.
	 * @since  12.1
	 */
	protected $prepared;

	/**
	 * Contains the current query execution status
	 *
	 * @var array
	 * @since 12.1
	 */
	protected $executed = false;

	/**
	 * Constructor.
	 *
	 * @param   array  $options  List of options used to configure the connection
	 *
	 * @since   12.1
	 */
	public function __construct($options)
	{
		$options['host']    = (isset($options['host'])) ? $options['host']   : 'localhost';
		$options['user']    = (isset($options['user'])) ? $options['user']   : '';
		$options['password']    = (isset($options['password'])) ? $options['password']   : '';
		$options['select']    = (isset($options['select'])) ? (bool) $options['select']   : true;
		$options['port']    = (isset($options['port'])) ? (int) $options['port']   : 1521;
		$options['charset']    = (isset($options['charset'])) ? $options['charset']   : 'AL32UTF8';
		$options['dateformat'] = (isset($options['dateformat'])) ? $options['dateformat'] : 'RRRR-MM-DD HH24:MI:SS';

		$this->charset = $options['charset'];
		$this->dateformat = $options['dateformat'];

		// Finalize initialisation
		parent::__construct($options);
	}

	/**
	 * Destructor.
	 *
	 * @since   12.1
	 */
	public function __destruct()
	{
		$this->freeResult();
		unset($this->connection);
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

		// Perform a number of fatality checks, then return gracefully
		if (!function_exists('oci_connect'))
		{
			throw new JDatabaseExceptionConnecting('The oci8 extension may not be available.', 1);
		}

		// Connect to the server
		$user = $this->options['user'];
		$password = $this->options['password'];
		$host = $this->options['host'];
		$port = $this->options['port'];
		$database = $this->options['database'];

		if (!($this->connection = @oci_connect($user, $password, "//$host:$port/$database")))
		{
			throw new JDatabaseExceptionConnecting('Could not connect to the Oracle database using the information provided', 2);
		}

		if (isset($this->options['schema']))
		{
			$this->setQuery('ALTER SESSION SET CURRENT_SCHEMA = ' . $this->quoteName($this->options['schema']))->execute();
		}

		$this->setDateFormat($this->dateformat);
	}

	/**
	 * Disconnects the database.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function disconnect()
	{
		foreach ($this->disconnectHandlers as $h)
		{
			call_user_func_array($h, array( &$this));
		}

		// Close the connection.
		if (is_resource($this->connection))
		{
			oci_close($this->connection);
			$this->connection = null;
		}
	}

	/**
	 * Copies a table with/without it's data in the database.
	 *
	 * @param   string   $fromTable  The name of the database table to copy from.
	 * @param   string   $toTable    The name of the database table to create.
	 * @param   boolean  $withData   Optionally include the data in the new table.
	 *
	 * @return  JDatabaseDriverOracle  Returns this object to support chaining.
	 *
	 * @since   12.1
	 */
	public function copyTable($fromTable, $toTable, $withData = false)
	{
		$this->connect();

		$fromTable = strtoupper($fromTable);
		$toTable = strtoupper($toTable);

		$query = $this->getQuery(true);

		// Works as a flag to include/exclude the data in the copied table:
		if ($withData)
		{
			$whereClause = ' where 11 = 11';
		}
		else
		{
			$whereClause = ' where 11 = 1';
		}

		$query->setQuery('CREATE TABLE ' . $this->quoteName($toTable) . ' as SELECT * FROM ' . $this->quoteName($fromTable) . $whereClause);

		$this->setQuery($query);

		try
		{
			$this->execute();
		}
		catch (JDatabaseExceptionExecuting $e)
		{
			/**
			* Code 955 is for when the table already exists
			* so we can safely ignore that code and catch any others.
			*/
			if ($e->getCode() !== 955)
			{
				throw $e;
			}
		}

		return $this;
	}

	/**
	 * Drops an entire database (Use with Caution!).
	 *
	 * Note: The IF EXISTS flag is unused in the Oracle driver.
	 *
	 * @param   string   $databaseName  The name of the database table to drop.
	 * @param   boolean  $ifExists      Optionally specify that the table must exist before it is dropped.
	 *
	 * @return  JDatabaseDriver  Returns this object to support chaining.
	 *
	 * @since   12.1
	 */
	public function dropDatabase($databaseName, $ifExists = true)
	{
		$this->connect();

		$databaseName = strtoupper($databaseName);

		$query = $this->getQuery(true)
			->setQuery('DROP USER ' . $this->quoteName($databaseName) . ' CASCADE');

		$this->setQuery($query);

		try
		{
			$this->execute();
		}
		catch (JDatabaseExceptionExecuting $e)
		{
			/**
			* Code 1918 is for when the database doesn't exist
			* so we can safely ignore that code and catch any others.
			*/
			if ($e->getCode() !== 1918)
			{
				throw $e;
			}
		}

		return $this;
	}

	/**
	 * Drops a table from the database.
	 *
	 * Note: The IF EXISTS flag is unused in the Oracle driver.
	 *
	 * @param   string   $tableName  The name of the database table to drop.
	 * @param   boolean  $ifExists   Optionally specify that the table must exist before it is dropped.
	 *
	 * @return  JDatabaseDriverOracle  Returns this object to support chaining.
	 *
	 * @since   12.1
	 */
	public function dropTable($tableName, $ifExists = true)
	{
		$this->connect();

		$tableName = strtoupper($tableName);

		$query = $this->getQuery(true)
			->setQuery('DROP TABLE ' . $this->quoteName($tableName));

		$this->setQuery($query);

		try
		{
			$this->execute();
		}
		catch (JDatabaseExceptionExecuting $e)
		{
			/**
			* Code 942 is for when the table doesn't exist
			* so we can safely ignore that code and catch any others.
			*/
			if ($e->getCode() !== 942)
			{
				throw $e;
			}
		}

		return $this;
	}

	/**
	 * Get the number of affected rows by the last INSERT, UPDATE, REPLACE or DELETE for the previous executed SQL statement.
	 *
	 * @return  integer  The number of affected rows.
	 *
	 * @since   12.1
	 */
	public function getAffectedRows()
	{
		return $this->numRows;
	}

	/**
	 * Method to get the database collation in use by sampling a text field of a table in the database.
	 *
	 * @return  mixed  The collation in use by the database or boolean false if not supported.
	 *
	 * @since   12.1
	 */
	public function getCollation()
	{
		return $this->charset;
	}

	/**
	 * Method to get the database connection collation, as reported by the driver. If the connector doesn't support
	 * reporting this value please return an empty string.
	 *
	 * @return  string
	 */
	public function getConnectionCollation()
	{
		return $this->charset;
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
	 * @since   12.1
	 */
	public function getNumRows($cursor = null)
	{
		return $this->numRows;
	}

	/**
	 * Returns the Query Type returned by
	 * oci_statement_type() in the setQuery() call.
	 *
	 * @return  string   The query type
	 *
	 * @since   12.1
	 */
	public function getQueryType()
	{
		return $this->queryType;
	}

	/**
	 * Get a query to run and verify the database is operational.
	 *
	 * @return  string  The query to check the health of the DB.
	 *
	 * @since   12.2
	 */
	public function getConnectedQuery()
	{
		return 'SELECT 1 FROM dual';
	}

	/**
	 * Returns the current date format
	 * This method should be useful in the case that
	 * somebody actually wants to use a different
	 * date format and needs to check what the current
	 * one is to see if it needs to be changed.
	 *
	 * @return string The current date format
	 *
	 * @since 12.1
	 */
	public function getDateFormat()
	{
		return $this->dateformat;
	}

	/**
	 * Get a new iterator on the current query.
	 *
	 * @param   string  $column  An option column to use as the iterator key.
	 * @param   string  $class   The class of object that is returned.
	 *
	 * @return  JDatabaseIterator  A new database iterator.
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function getIterator($column = null, $class = 'stdClass')
	{
		// Derive the class name from the driver.
		$iteratorClass = 'JDatabaseIterator' . ucfirst($this->name);

		// Make sure we have an iterator class for this driver.
		if (!class_exists($iteratorClass))
		{
			// If it doesn't exist we are at an impasse so throw an exception.
			throw new JDatabaseExceptionUnsupported(sprintf('class *%s* is not defined', $iteratorClass));
		}

		// Return a new iterator
		return new $iteratorClass($this->execute(), $column, $class, $this->toLower, $this->returnLobs);
	}

	/**
	 * Shows the table CREATE statement that creates the given tables.
	 *
	 * Note: You must have the correct privileges before this method
	 * will return usable results!
	 *
	 * @param   mixed  $tables  A table name or a list of table names.
	 *
	 * @return  array  A list of the create SQL for the tables.
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function getTableCreate($tables)
	{
		$this->connect();

		$result = array();
		$type = 'TABLE';
		$query = $this->getQuery(true)
			->select('dbms_metadata.get_ddl(:type, :tableName, :schema)')
			->from('dual')
			->bind(':type', $type);

		// Sanitize input to an array and iterate over the list.
		settype($tables, 'array');

		$defaultSchema = strtoupper($this->options['user']);
		foreach ($tables as $table)
		{
			$table = strtoupper($table);
			$parts = explode('.', $table);

			if (count($parts) === 1)
			{
				$query->bind(':tableName', $table);
				$query->bind(':schema', $defaultSchema);
			}
			elseif (count($parts) === 2)
			{
				$query->bind(':tableName', $parts[1]);
				$query->bind(':schema', $parts[0]);
			}

			$this->setQuery($query);
			$statement = (string) $this->loadResult();
			$result[$table] = $statement;
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
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function getTableColumns($table, $typeOnly = true)
	{
		$this->connect();

		$columns = array();
		$query = $this->getQuery(true);

		$query->select('*');
		$query->from('ALL_TAB_COLUMNS');
		$query->where('table_name = :tableName');

		$prefixedTable = strtoupper(str_replace('#__', $this->tablePrefix, $table));
		$query->bind(':tableName', $prefixedTable);
		$this->setQuery($query);
		$fields = $this->loadObjectList();

		if ($typeOnly)
		{
			foreach ($fields as $field)
			{
				if ($this->useLowercaseFieldNames())
				{
					$columns[strtolower($field->column_name)] = $field->data_type;
				}
				else
				{
					$columns[$field->COLUMN_NAME] = $field->DATA_TYPE;
				}
			}
		}
		else
		{
			foreach ($fields as $field)
			{
				if ($this->useLowercaseFieldNames())
				{
					$columns[strtolower($field->column_name)] = $field;
				}
				else
				{
					$columns[$field->COLUMN_NAME] = $field;
				}
			}
		}

		return $columns;
	}

	/**
	 * Get the details list of keys for a table.
	 *
	 * @param   string  $table  The name of the table.
	 *
	 * @return  array  An array of the column specification for the table.
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function getTableKeys($table)
	{
		$this->connect();

		$query = $this->getQuery(true);

		$table = strtoupper($table);
		$query->select('*')
			->from('ALL_CONSTRAINTS NATURAL JOIN ALL_CONS_COLUMNS')
			->where('table_name = :tableName')
			->bind(':tableName', $table);

		$this->setQuery($query);
		$keys = $this->loadObjectList();

		return $keys;
	}

	/**
	 * Method to get an array of all tables in the database (schema).
	 *
	 * @param   string   $databaseName         The database (schema) name
	 * @param   boolean  $includeDatabaseName  Whether to include the schema name in the results
	 *
	 * @return  array    An array of all the tables in the database.
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function getTableList($databaseName = null, $includeDatabaseName = false)
	{
		$this->connect();

		$query = $this->getQuery(true);

		if ($includeDatabaseName)
		{
			$query->select('owner, table_name');
		}
		else
		{
			$query->select('table_name');
		}

		$query->from('all_tables');

		if ($databaseName)
		{
			$databaseName = strtoupper($databaseName);
			$query->where('owner = :database')
				->bind(':database', $databaseName);
		}

		$query->order('table_name');

		$this->setQuery($query);

		if ($includeDatabaseName)
		{
			$tables = $this->loadAssocList();
		}
		else
		{
			$tables = $this->loadColumn();
		}

		return $tables;
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

		$this->setQuery("select value from nls_database_parameters where parameter = 'NLS_RDBMS_VERSION'");

		return $this->loadResult();
	}

	/**
	 * Method to get the auto-incremented value from the last INSERT statement.
	 *
	 * @return  mixed  The value of the auto-increment field from the last inserted row.
	 *                 If the value is greater than maximal int value, it will return a string.
	 *
	 * @since   12.1
	 */
	public function insertid()
	{
		// Not really supported on Oracle:
		return null;
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

		return true;
	}

	/**
	 * Sets the SQL statement string for later execution.
	 *
	 * @param   mixed    $query   The SQL statement to set either as a JDatabaseQuery object or a string.
	 * @param   integer  $offset  The affected row offset to set.
	 * @param   integer  $limit   The maximum affected rows to set.
	 *
	 * @return  JDatabaseDriver  This object to support method chaining.
	 *
	 * @since   12.1
	 */
	public function setQuery($query, $offset = null, $limit = null)
	{
		$this->connect();

		$this->freeResult();

		if (is_string($query))
		{
			// Allows taking advantage of bound variables in a direct query:
			$query = $this->getQuery(true)->setQuery($query);
		}

		if ($query instanceof JDatabaseQueryLimitable && !is_null($offset) && !is_null($limit))
		{
			$query = $query->processLimit($query, $limit, $offset);
		}

		// Create a stringified version of the query (with prefixes replaced):
		$sql = $this->replacePrefix((string) $query);

		// Use the stringified version in the prepare call:
		$this->prepared = oci_parse($this->connection, $sql);

		$this->queryType = oci_statement_type($this->prepared);

		// Store reference to the original JDatabaseQuery instance within the class.
		// This is important since binding variables depends on it within execute():
		parent::setQuery($query, $offset, $limit);

		return $this;
	}

	/**
	 * Sets the Oracle Commit Mode.
	 *
	 * Mainly needed when using the transaction
	 * methods within the driver.
	 *
	 * @param   int  $mode  Oracle Commit Mode
	 *
	 * @return boolean
	 *
	 * @since  12.1
	 */
	public function setCommitMode($mode = OCI_COMMIT_ON_SUCCESS)
	{
		$this->commitMode = $mode;
	}

	/**
	 * Sets the Oracle Date Format for the session
	 * Default date format for Oracle is = DD-MON-RR
	 * The default date format for this driver is:
	 * 'RRRR-MM-DD HH24:MI:SS' since it is the format
	 * that matches the MySQL one used within most Joomla
	 * tables.
	 *
	 * @param   string  $dateFormat  Oracle Date Format String
	 *
	 * @return boolean
	 *
	 * @since  12.1
	 */
	public function setDateFormat($dateFormat = 'DD-MON-RR')
	{
		$this->connect();

		$this->setQuery("ALTER SESSION SET NLS_DATE_FORMAT = '$dateFormat'");

		if (!$this->execute())
		{
			return false;
		}

		$this->setQuery("ALTER SESSION SET NLS_TIMESTAMP_FORMAT = '$dateFormat'");

		if (!$this->execute())
		{
			return false;
		}

		$this->dateformat = $dateFormat;

		return true;
	}

	/**
	 * Set the connection to use UTF-8 character encoding.
	 *
	 * Returns false automatically for the Oracle driver since
	 * you can only set the character set when the connection
	 * is created.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   12.1
	 */
	public function setUtf()
	{
		return false;
	}

	/**
	 * Method to escape a string for usage in an SQL statement.
	 *
	 * Oracle escaping reference:
	 * http://www.orafaq.com/wiki/SQL_FAQ#How_does_one_escape_special_characters_when_writing_SQL_queries.3F
	 *
	 * SQLite escaping notes:
	 * http://www.sqlite.org/faq.html#q14
	 *
	 * Method body is as implemented by the Zend Framework
	 *
	 * Note: Using query objects with bound variables is
	 * preferable to the below.
	 *
	 * @param   string   $text   The string to be escaped.
	 * @param   boolean  $extra  Unused optional parameter to provide extra escaping.
	 *
	 * @return  string  The escaped string.
	 *
	 * @since   12.1
	 */
	public function escape($text, $extra = false)
	{
		if (is_int($text) || is_float($text))
		{
			return $text;
		}

		$text = str_replace("'", "''", $text);

		return addcslashes($text, "\000\n\r\\\032");
	}

	/**
	 * Method to get an array of the result set rows from the database query where each row is an associative array
	 * of ['field_name' => 'row_value'].  The array of rows can optionally be keyed by a field name, but defaults to
	 * a sequential numeric array.
	 *
	 * NOTE: Chosing to key the result array by a non-unique field name can result in unwanted
	 * behavior and should be avoided.
	 *
	 * @param   string  $key     The name of a field on which to key the result array.
	 * @param   string  $column  An optional column name. Instead of the whole row, only this column value will be in
	 * the result array.
	 *
	 * @return  mixed   The return value or null if the query failed.
	 *
	 * @since   11.1
	 * @throws  RuntimeException
	 */
	public function loadAssocList($key = null, $column = null)
	{
		if (!empty($key))
		{
			if ($this->useLowercaseFieldNames())
			{
				$key = strtolower($key);
			}
			else
			{
				$key = strtoupper($key);
			}
		}

		if (!empty($column))
		{
			if ($this->useLowercaseFieldNames())
			{
				$column = strtolower($column);
			}
			else
			{
				$column = strtoupper($column);
			}
		}

		return parent::loadAssocList($key, $column);
	}

	/**
	 * Method to get an array of the result set rows from the database query where each row is an object.  The array
	 * of objects can optionally be keyed by a field name, but defaults to a sequential numeric array.
	 *
	 * NOTE: Choosing to key the result array by a non-unique field name can result in unwanted
	 * behavior and should be avoided.
	 *
	 * @param   string  $key    The name of a field on which to key the result array.
	 * @param   string  $class  The class name to use for the returned row objects.
	 *
	 * @return  mixed   The return value or null if the query failed.
	 *
	 * @since   11.1
	 * @throws  RuntimeException
	 */
	public function loadObjectList($key = '', $class = 'stdClass')
	{
		if (!empty($key))
		{
			if ($this->useLowercaseFieldNames())
			{
				$key = strtolower($key);
			}
			else
			{
				$key = strtoupper($key);
			}
		}

		return parent::loadObjectList($key, $class);
	}

	/**
	 * Locks a table in the database.
	 *
	 * @param   string  $table  The name of the table to unlock.
	 *
	 * @return  JDatabaseDriverOracle  Returns this object to support chaining.
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function lockTable($table)
	{
		$table = strtoupper($table);

		$this->setQuery('LOCK TABLE ' . $this->quoteName($table) . ' IN EXCLUSIVE MODE')->execute();

		return $this;
	}

	/**
	 * Execute the SQL statement.
	 *
	 * @return  mixed  A database cursor resource on success, boolean false on failure.
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 * @throws  Exception
	 */
	public function execute()
	{
		$this->connect();

		// Take a local copy so that we don't modify the original query and cause issues later
		$query = $this->replacePrefix((string) $this->sql);

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

		// Execute the query.
		$this->executed = false;

		if (is_resource($this->prepared))
		{
			// Bind the variables:
			if ($this->sql instanceof JDatabaseQueryPreparable)
			{
				$bounded = $this->sql->getBounded();

				foreach ($bounded as $key => $obj)
				{
					oci_bind_by_name($this->prepared, $key, $obj->value, $obj->length, $obj->dataType);
				}
			}

			$this->executed = @oci_execute($this->prepared, $this->commitMode);
		}

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
		if (!$this->executed)
		{
			// Get the error number and message before we execute any more queries.
			$errorNum = $this->getErrorNumber();
			$errorMsg = $this->getErrorMessage($query);

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
					$this->errorMsg = $this->getErrorMessage($query);

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
				// Get the error number and message from before we tried to reconnect.
				$this->errorNum = $errorNum;
				$this->errorMsg = $errorMsg;

				// Throw the normal query exception.
				JLog::add(JText::sprintf('JLIB_DATABASE_QUERY_FAILED', $this->errorNum, $this->errorMsg), JLog::ERROR, 'database-error');

				throw new JDatabaseExceptionExecuting($query, $this->errorMsg, $this->errorNum);
			}
		}

		$this->numRows = (int) oci_num_rows($this->prepared);

		return $this->prepared;
	}

	/**
	 * Renames a table in the database.
	 *
	 * @param   string  $oldTable  The name of the table to be renamed
	 * @param   string  $newTable  The new name for the table.
	 * @param   string  $backup    Not used by Oracle.
	 * @param   string  $prefix    Not used by Oracle.
	 *
	 * @return  JDatabaseDriverOracle  Returns this object to support chaining.
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function renameTable($oldTable, $newTable, $backup = null, $prefix = null)
	{
		$oldTable = strtoupper($oldTable);
		$newTable = strtoupper($newTable);

		$this->setQuery('RENAME ' . $this->quoteName($oldTable) . ' TO ' . $this->quoteName($newTable))->execute();

		return $this;
	}

	/**
	 * Unlocks tables in the database.
	 *
	 * @return  JDatabaseDriverOracle  Returns this object to support chaining.
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function unlockTables()
	{
		$this->setQuery('COMMIT')->execute();

		return $this;
	}

	/**
	 * Test to see if the oci8 functions are available.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   12.1
	 */
	public static function isSupported()
	{
		return function_exists('oci_connect');
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
		// Flag to prevent recursion into this function.
		static $checkingConnected = false;

		if ($checkingConnected)
		{
			// Reset this flag and throw an exception.
			$checkingConnected = true;
			die('Recursion trying to check if connected.');
		}

		// Backup the query state.
		$query = $this->sql;
		$limit = $this->limit;
		$offset = $this->offset;
		$prepared = $this->prepared;

		try
		{
			// Set the checking connection flag.
			$checkingConnected = true;

			// Run a simple query to check the connection.
			$this->setQuery($this->getConnectedQuery());
			$status = (bool) $this->loadResult();
		}
		// If we catch an exception here, we must not be connected.
		catch (Exception $e)
		{
			$status = false;
		}

		// Restore the query state.
		$this->sql = $query;
		$this->limit = $limit;
		$this->offset = $offset;
		$this->prepared = $prepared;
		$checkingConnected = false;

		return $status;
	}

	/**
	 * Create a new database using information from $options object, obtaining query string
	 * from protected member.
	 *
	 * For Oracle, it differs compared to MySQL. Instead of creating new databases within
	 * the overall MySQL RDBMS, in Oracle the RDBMS = Database. Within that Database Instance
	 * you can have multiple "schemas" which are equivalent to Oracle Users within the system.
	 * These schemas are basically the same as the different databases that can be created in
	 * MySQL. So here, the db_name provided will be used as the new Oracle USER and db_user
	 * will more or less be ignored. An additional parameter named db_password must be included
	 * in order for the new user to have a password set upon creation.
	 *
	 * @param   stdClass  $options  Object used to pass user and database name to database driver.
	 * 									This object must have "db_name" and "db_password" set for Oracle.
	 * @param   boolean   $utf      True if the database supports the UTF-8 character set.
	 *
	 * @return  JDatabaseDriver  Returns this object to support chaining.
	 *
	 * @since   12.2
	 * @throws  RuntimeException
	 */
	public function createDatabase($options, $utf = true)
	{
		if (is_null($options))
		{
			throw new RuntimeException('$options object must not be null.');
		}
		elseif (empty($options->db_name))
		{
			throw new RuntimeException('$options object must have db_name set.');
		}
		elseif (empty($options->db_password))
		{
			throw new RuntimeException('$options object must have db_password set.');
		}

		$options->db_user = $options->db_name;

		try
		{
			$this->setQuery($this->getCreateDatabaseQuery($options, $utf))->execute();

			$this->setQuery('GRANT create session TO ' . $this->quoteName($options->db_name))->execute();
			$this->setQuery('GRANT create table TO ' . $this->quoteName($options->db_name))->execute();
			$this->setQuery('GRANT create view TO ' . $this->quoteName($options->db_name))->execute();
			$this->setQuery('GRANT create any trigger TO ' . $this->quoteName($options->db_name))->execute();
			$this->setQuery('GRANT create any procedure TO ' . $this->quoteName($options->db_name))->execute();
			$this->setQuery('GRANT create sequence TO ' . $this->quoteName($options->db_name))->execute();
			$this->setQuery('GRANT create synonym TO ' . $this->quoteName($options->db_name))->execute();
		}
		catch (JDatabaseExceptionExecuting $e)
		{
			/**
			* Error 1920 gets thrown when the user already exists:
			*/
			if ($e->getCode() !== 1920)
			{
				throw $e;
			}
		}

		return $this;
	}

	/**
	 * This function replaces a string identifier <var>$prefix</var> with the string held is the
	 * <var>tablePrefix</var> class variable.
	 *
	 * @param   string  $query   The SQL statement to prepare.
	 * @param   string  $prefix  The common table prefix.
	 *
	 * @return  string  The processed SQL statement.
	 *
	 * @since   11.1
	 */
	public function replacePrefix($query, $prefix = '#__')
	{
		$startPos = 0;
		$quoteChar = "'";
		$literal = '';

		$query = trim($query);
		$n = strlen($query);

		while ($startPos < $n)
		{
			$ip = strpos($query, $prefix, $startPos);

			if ($ip === false)
			{
				break;
			}

			$j = strpos($query, "'", $startPos);

			if ($j === false)
			{
				$j = $n;
			}

			$literal .= str_replace($prefix, $this->tablePrefix, substr($query, $startPos, $j - $startPos));
			$startPos = $j;

			$j = $startPos + 1;

			if ($j >= $n)
			{
				break;
			}

			// Quote comes first, find end of quote
			while (true)
			{
				$k = strpos($query, $quoteChar, $j);
				$escaped = false;

				if ($k === false)
				{
					break;
				}

				$l = $k - 1;

				while ($l >= 0 && $query{$l} == '\\')
				{
					$l--;
					$escaped = !$escaped;
				}

				if ($escaped)
				{
					$j = $k + 1;
					continue;
				}

				break;
			}

			if ($k === false)
			{
				// Error in the query - no end quote; ignore it
				break;
			}

			$literal .= substr($query, $startPos, $k - $startPos + 1);
			$startPos = $k + 1;
		}

		if ($startPos < $n)
		{
			$literal .= substr($query, $startPos, $n - $startPos);
		}

		return $literal;
	}

	/**
	 * Method to commit a transaction.
	 *
	 * @param   boolean  $toSavepoint  If true, commit to the last savepoint.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 * @throws  RuntimeException
	 */
	public function transactionCommit($toSavepoint = false)
	{
		$this->connect();

		if (!$toSavepoint || $this->transactionDepth <= 1)
		{
			if (oci_commit($this->connection))
			{
				// Reset internal values:
				$this->transactionDepth = 0;
				$this->setCommitMode(OCI_COMMIT_ON_SUCCESS);
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
	 * @since   12.2
	 * @throws  RuntimeException
	 */
	public function transactionRollback($toSavepoint = false)
	{
		$this->connect();

		if (!$toSavepoint || $this->transactionDepth <= 1)
		{
			if (oci_rollback($this->connection))
			{
				// Reset internal values:
				$this->transactionDepth = 0;
				$this->setCommitMode(OCI_COMMIT_ON_SUCCESS);
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
	 * @since   12.2
	 * @throws  RuntimeException
	 */
	public function transactionStart($asSavepoint = false)
	{
		$this->connect();

		if (!$asSavepoint || !$this->transactionDepth)
		{
			$this->setCommitMode(OCI_NO_AUTO_COMMIT);
			$this->transactionDepth = 1;

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
	* Indicates whether to use lowercase
	* field names throughout the class or not.
	*
	* @return bool
	*/
	public function useLowercaseFieldNames()
	{
		return $this->toLower;
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
		$mode = $this->getMode(true);

		$row = oci_fetch_array($cursor ? $cursor : $this->prepared, $mode);

		// Update Number of Rows Value:
		$this->numRows = (int) oci_num_rows($cursor ? $cursor : $this->prepared);

		return $row;
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
		$mode = $this->getMode();

		$row = oci_fetch_array($cursor ? $cursor : $this->prepared, $mode);

		if ($row && $this->useLowercaseFieldNames())
		{
			$row = array_change_key_case($row);
		}

		// Update Number of Rows Value:
		$this->numRows = (int) oci_num_rows($cursor ? $cursor : $this->prepared);

		return $row;
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
		$row = $this->fetchAssoc($cursor);

		if ($row)
		{
			if ($class !== 'stdClass')
			{
				$row = new $class($row);
			}
			else
			{
				$row = (object) $row;
			}
		}

		return $row;
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
		$this->executed = false;

		if (is_resource($cursor))
		{
			oci_free_statement($cursor);
			$cursor = null;
		}

		if (is_resource($this->prepared))
		{
			oci_free_statement($this->prepared);
			$this->prepared = null;
		}
	}

	/**
	 * Get the query strings to alter the character set and collation of a table.
	 *
	 * @param   string  $tableName  The name of the table
	 *
	 * @return  string[]  The queries required to alter the table's character set and collation
	 *
	 * @since   CMS 3.5.0
	 */
	public function getAlterTableCharacterSet($tableName)
	{
		return array();
	}

	/**
    * Sets the $toLower variable to true
    * so that field names will be created
    * using lowercase values.
    *
    * @return void
    */
	public function toLower()
	{
		$this->toLower = true;
	}

	/**
	* Sets the $toLower variable to false
	* so that field names will be created
	* using uppercase values.
	*
	* @return void
	*/
	public function toUpper()
	{
		$this->toLower = false;
	}

	/**
	* Sets the $returnLobs variable to true
	* so that LOB object values will be
	* returned rather than an OCI-Lob Object.
	*
	* @return void
	*/
	public function returnLobValues()
	{
		$this->returnLobs = true;
	}

	/**
	* Sets the $returnLobs variable to false
	* so that OCI-Lob Objects will be returned.
	*
	* @return void
	*/
	public function returnLobObjects()
	{
		$this->returnLobs = false;
	}

	/**
	* Depending on the value for $returnLobs,
    * this method returns the proper constant
    * combinations to be passed to the oci* functions
    *
    * @param   bool  $numeric  Assoc or Numeric Mode
    *
	* @return int
	*/
	public function getMode($numeric = false)
	{
		if ($numeric === false)
		{
			if ($this->returnLobs)
			{
				$mode = OCI_ASSOC+OCI_RETURN_NULLS+OCI_RETURN_LOBS;
			}
			else
			{
				$mode = OCI_ASSOC+OCI_RETURN_NULLS;
			}
		}
		else
		{
			if ($this->returnLobs)
			{
				$mode = OCI_NUM+OCI_RETURN_NULLS+OCI_RETURN_LOBS;
			}
			else
			{
				$mode = OCI_NUM+OCI_RETURN_NULLS;
			}
		}

		return $mode;
	}

	/**
	 * Return the query string to create new User/Database in Oracle.
	 *
	 * For the Oracle drivers, db_user is ignored and db_name is the main field
	 * that is used. Simply set db_user to be the same as db_name when passing in
	 * the $options object.
	 *
	 * Optionally, you may also include the "db_default_tablespace" and "db_temporary_tablespace"
	 * attributes and those will be used when creating the user (these must already be created in
	 * the Oracle RDBMS before being used!). A quota for the permanent tablespace may also be optionally set
	 * using "db_default_tablespace_quota".
	 *
	 * @param   stdClass  $options  Object used to pass user and database name to database driver.
	 *                   This object must have "db_name" and "db_user" set.
	 * @param   boolean   $utf      True if the database supports the UTF-8 character set.
	 *
	 * @return  string  The query that creates database
	 *
	 * @since   12.2
	 */
	protected function getCreateDatabaseQuery($options, $utf)
	{
		$options->db_name = strtoupper($options->db_name);
		$options->db_user = $options->db_name;

		$defaultPermanentTablespaceQuery = "select PROPERTY_VALUE
											  from database_properties
											  where property_name = 'DEFAULT_PERMANENT_TABLESPACE'";

		$defaultTemporaryTablespaceQuery = "select PROPERTY_VALUE
											  from database_properties
											  where property_name = 'DEFAULT_TEMP_TABLESPACE'";

		$defaultPermanentTablespace = $this->setQuery($defaultPermanentTablespaceQuery)->loadResult();
		$defaultTemporaryTablespace = $this->setQuery($defaultTemporaryTablespaceQuery)->loadResult();

		// Set Tablespace Options with defaults if needed:
		$options->db_default_tablespace = (isset($options->db_default_tablespace)) ? $options->db_default_tablespace : $defaultPermanentTablespace;
		$options->db_temporary_tablespace = (isset($options->db_temporary_tablespace)) ? $options->db_temporary_tablespace : $defaultTemporaryTablespace;

		// Set Tablespace Quota Options with defaults if needed:
		$options->db_default_tablespace_quota = (isset($options->db_default_tablespace_quota)) ? $options->db_default_tablespace_quota : 'UNLIMITED';

		// Setup the clauses to be added into the query:
		$defaultTablespaceClause = ' DEFAULT TABLESPACE ' . $this->quoteName($options->db_default_tablespace);
		$temporaryTablespaceClause = ' TEMPORARY TABLESPACE ' . $this->quoteName($options->db_temporary_tablespace);
		$defaultTablespaceQuotaClause = ' QUOTA  ' . $options->db_default_tablespace_quota . ' ON ' . $this->quoteName($options->db_default_tablespace);

		return 'CREATE USER ' . $this->quoteName($options->db_name) .
					' IDENTIFIED BY ' . $this->quoteName($options->db_password) .
					$defaultTablespaceClause .
					$temporaryTablespaceClause .
					$defaultTablespaceQuotaClause;
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
		$error = oci_error($this->prepared);

		if ($error !== false)
		{
			return $error['code'];
		}

		return 0;
	}

	/**
	 * Return the actual SQL Error message
	 *
	 * @param   string  $query  The SQL Query that fails
	 *
	 * @return  string  The SQL Error message
	 *
	 * @since   3.4.6
	 */
	protected function getErrorMessage($query)
	{
		$error = oci_error($this->prepared);

		if ($error !== false)
		{
			// Replace the Databaseprefix with `#__` if we are not in Debug
			if (!$this->debug)
			{
				$errorMessage = str_replace($this->tablePrefix, '#__', $error['message']);
				$query        = str_replace($this->tablePrefix, '#__', $query);
			}

			return $error['message'] . ' SQL=' . $query;
		}

		return '';
	}
}
