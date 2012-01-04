<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JLoader::register('JDatabaseQuerySqlite', dirname(__FILE__) . '/sqlitequery.php');

/**
 * SQLite database driver
 *
 *          W I P !!!
 *
 * @package     Joomla.Platform
 * @subpackage  Database
 * @see         http://php.net/pdo
 * @since       11.4
 */
class JDatabaseSqlite extends JDatabase
{
	/**
	 * The name of the database driver.
	 *
	 * @var    string
	 * @since  11.4
	 */
	public $name = 'sqlite';

	/**
	 * @var PDO The database connection resource.
	 * @since 11.1
	 */
	protected $connection;

	/**
	 * @var PDOStatement The database connection cursor from the last query.
	 * @since 11.1
	 */
	protected $cursor;

	/**
	 * The character(s) used to quote SQL statement names such as table names or field names,
	 * etc. The child classes should define this as necessary. If a single character string the
	 * same character is used for both sides of the quoted name, else the first character will be
	 * used for the opening quote and the second for the closing quote.
	 *
	 * @var string
	 * @since 11.1
	 */
	protected $nameQuote = ' ';

	/**
	 * The null or zero representation of a timestamp for the database driver. This should be
	 * defined in child classes to hold the appropriate value for the engine.
	 *
	 * @var string
	 * @since ¿
	 */
	protected $nullDate = '0000-00-00 00:00:00';

	/**
	 * Constructor.
	 *
	 * @param   array  $options  List of options used to configure the connection
	 *
	 * @since   11.4
	 */
	public function __construct($options)
	{

		$options['database'] = (isset($options['database'])) ? $options['database'] : '';

		$path = JPATH_ROOT.'/db/'.$options['database'];

		if( ! file_exists($path))
		{
			if(isset($options['create_db']) && $options['create_db'])
			{
				if( ! JFolder::create(dirname($path)))
				{
					throw new Exception(sprintf('Unable to create the database in %s', $path));
				}
			}
			else
			{
				throw new JDatabaseException(sprintf('The SQLite database file has not been found in %s', $path));
			}
		}

		// Attempt to connect to the server.
		// if (!($this->connection = @ mysql_connect($options['host'], $options['user'], $options['password'], true)))
		// $this->connection = sqlite_open($options['database'], $this->errorMsg);
		$this->connection = new PDO('sqlite:'.$path);

		if( ! $this->connection)
		{
			throw new JDatabaseException(sprintf('Unable to connect to the SQLite database in %s', $path));
		}

		// Set the error reporting attribute
		$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		// $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

		// Finalize initialisation
		parent::__construct($options);
	}

	/**
	 * Destructor.
	 *
	 * @since   11.4
	 */
	public function __destruct()
	{
		$this->freeResult();
		$this->connection = null;
	}

	/**
	 * Drops a table from the database.
	 *
	 * @param   string   $tableName  The name of the database table to drop.
	 * @param   boolean  $ifExists   Optionally specify that the table must exist before it is dropped.
	 *
	 * @return  JDatabaseDriverSqlite  Returns this object to support chaining.
	 *
	 * @since   11.4
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
	 * Method to escape a string for usage in an SQL statement.
	 *
	 * @param   string   $text   The string to be escaped.
	 * @param   boolean  $extra  Optional parameter to provide extra escaping.
	 *
	 * @return  string   The escaped string.
	 *
	 * @since   11.1
	 */
	public function escape($text, $extra = false)
	{
		return str_replace("'", "''", $text);
	}

	/**
	 * Determines if the connection to the server is active.
	 *
	 * @return  boolean  True if connected to the database engine.
	 *
	 * @since   11.1
	 */
	public function connected()
	{
		return $this->connection;
	}

	/**
	 * Method to fetch a row from the result set cursor as an associative array.
	 *
	 * @param   mixed  $cursor  The optional result set cursor from which to fetch the row.
	 *
	 * @return  mixed  Either the next row from the result set or false if there are no more rows.
	 *
	 * @since   11.1
	 */
	protected function fetchAssoc($cursor = null)
	{
		return $this->cursor->fetch(PDO::FETCH_ASSOC);
	}

	/**
	 * Method to fetch a row from the result set cursor as an object.
	 *
	 * @param   mixed   $cursor  The optional result set cursor from which to fetch the row.
	 * @param   string  $class   The class name to use for the returned row object.
	 *
	 * @return  mixed   Either the next row from the result set or false if there are no more rows.
	 *
	 * @since   11.1
	 */
	protected function fetchObject($cursor = null, $class = 'stdClass')
	{
		return $this->cursor->fetchObject($class);
	}

	/**
	 * Method to fetch a row from the result set cursor as an array.
	 *
	 * @param   mixed  $cursor  The optional result set cursor from which to fetch the row.
	 *
	 * @return  mixed  Either the next row from the result set or false if there are no more rows.
	 *
	 * @since   11.1
	 */
	protected function fetchArray($cursor = null)
	{
		return $this->cursor->fetch();
	}

	/**
	 * Method to free up the memory used for the result set.
	 *
	 * @param   mixed  $cursor  The optional result set cursor from which to fetch the row.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	protected function freeResult($cursor = null)
	{
		$this->cursor = null;
	}

	/**
	 * Get the number of affected rows for the previous executed SQL statement.
	 *
	 * @return  integer  The number of affected rows.
	 *
	 * @since   11.1
	 */
	public function getAffectedRows()
	{
		return $this->cursor->rowCount();
	}

	/**
	 * Get the number of returned rows for the previous executed SQL statement.
	 *
	 * @param   resource  $cursor  An optional database cursor resource to extract the row count from.
	 *
	 * @return  integer   The number of returned rows.
	 *
	 * @since   11.1
	 */
	public function getNumRows($cursor = null){
	}

	/**
	 * Get the current query object or a new JDatabaseQuery object.
	 *
	 * @param   boolean  $new  False to return the current query object, True to return a new JDatabaseQuery object.
	 *
	 * @return  JDatabaseQuery  The current query object or a new object extending the JDatabaseQuery class.
	 *
	 * @since   11.1
	 * @throws  JDatabaseException
	 */
	public function getQuery($new = false)
	{
		if ($new)
		{
			// Make sure we have a query class for this driver.
			if (!class_exists('JDatabaseQuerySqlite'))
			{
				throw new JDatabaseException(JText::_('JLIB_DATABASE_ERROR_MISSING_QUERY'));
			}

			return new JDatabaseQuerySQLite($this);
		}
		else
		{
			return $this->sql;
		}

	}

	/**
	 * Determines if the database engine supports UTF-8 character encoding.
	 *
	 * @return  boolean  True if supported.
	 *
	 * @since   11.1
	 *
	 * @deprecated  12.1
	 */
	public function hasUTF()
	{
	}

	/**
	 * Method to get the auto-incremented value from the last INSERT statement.
	 *
	 * @return  integer  The value of the auto-increment field from the last inserted row.
	 *
	 * @since   11.1
	 */
	public function insertid()
	{
		return $this->connection->lastInsertId();
	}

	/**
	 * Locks a table in the database.
	 *
	 * @param   string  $tableName  The name of the table to unlock.
	 *
	 * @return  JDatabase  Returns this object to support chaining.
	 *
	 * @since   11.4
	 * @throws  JDatabaseException
	 */
	public function lockTable($tableName)
	{
	}

	/**
	 * Execute the SQL statement.
	 *
	 * @return  mixed  A database cursor resource on success, boolean false on failure.
	 *
	 * @since   11.1
	 * @throws  JDatabaseException
	 */
	public function query()
	{
		if ( ! $this->connection instanceof PDO)
		{
			// JLog::add(JText::sprintf('JLIB_DATABASE_QUERY_FAILED', $this->errorNum, $this->errorMsg), JLog::ERROR, 'database');
			throw new JDatabaseException(__METHOD__.' - '.$this->errorMsg, $this->errorNum);
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

		// Execute the query.
		// $this->cursor = mysql_query($sql, $this->connection);
		// $sss = $this->connection->prepare((string)$sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		// $sql = $this->connection->quote($sql);
		try
		{
			$this->cursor = $this->connection->query($sql);
		}
		catch(Exception $e)
		{
			$msg = $e->getMessage().' SQL = '.$sql;
			$code = $e->getCode();
			throw new JDatabaseException($msg);
		}

		// If an error occurred handle it.
		if (!$this->cursor)
		{
			$this->errorNum = (int) $this->connection->errorCode();
			$info = $this->connection->errorInfo();
			$this->errorMsg = $info[0].' ('.$info[1].') '.$info[2].' SQL = '.$sql;

			// JLog::add(JText::sprintf('JLIB_DATABASE_QUERY_FAILED', $this->errorNum, $this->errorMsg), JLog::ERROR, 'databasequery');
			throw new JDatabaseException(__METHOD__.' - '.$this->errorMsg, $this->errorNum);
		}

		return $this->cursor;
	}

	/**
	 * Renames a table in the database.
	 *
	 * @param   string  $oldTable  The name of the table to be renamed
	 * @param   string  $newTable  The new name for the table.
	 * @param   string  $backup    Table prefix
	 * @param   string  $prefix    For the table - used to rename constraints in non-mysql databases
	 *
	 * @return  JDatabase  Returns this object to support chaining.
	 *
	 * @since   11.4
	 * @throws  JDatabaseException
	 */
	public function renameTable($oldTable, $newTable, $backup = null, $prefix = null){
	}

	/**
	 * Method to commit a transaction.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 * @throws  JDatabaseException
	 */
	public function transactionCommit()
	{
		$this->setQuery('COMMIT');
		$this->query();

		return $this;
	}

	/**
	 * Method to roll back a transaction.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 * @throws  JDatabaseException
	 */
	public function transactionRollback()
	{
		$this->setQuery('ROLLBACK');
		$this->query();

		return $this;
	}

	/**
	 * Method to initialize a transaction.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 * @throws  JDatabaseException
	 */
	public function transactionStart()
	{
		$this->setQuery('START TRANSACTION');
		$this->query();

		return $this;
	}

	/**
	 * Unlocks tables in the database.
	 *
	 * @return  JDatabase  Returns this object to support chaining.
	 *
	 * @since   11.4
	 * @throws  JDatabaseException
	 */
	public function unlockTables(){
	}

	/**
	 * Diagnostic method to return explain information for a query.
	 *
	 * @return  string  The explain output.
	 *
	 * @deprecated  12.1
	 * @since   11.1
	 */
	public function explain(){
	}

	/**
	 * Execute a query batch.
	 *
	 * @param   boolean  $abortOnError     Abort on error.
	 * @param   boolean  $transactionSafe  Transaction safe queries.
	 *
	 * @return  mixed  A database resource if successful, false if not.
	 *
	 * @deprecated  12.1
	 * @since   11.1
	 */
	public function queryBatch($abortOnError = true, $transactionSafe = false){
	}

	/**
	 * Method to get the database collation in use by sampling a text field of a table in the database.
	 *
	 * @return  mixed  The collation in use by the database or boolean false if not supported.
	 *
	 * @since   11.4
	 */
	public function getCollation()
	{
		return $this->charset;
	}

	/**
	 * Shows the table CREATE statement that creates the given tables.
	 *
	 * Note: Doesn't appear to have support in SQLite
	 *
	 * @param   mixed  $tables  A table name or a list of table names.
	 *
	 * @return  array  A list of the create SQL for the tables.
	 *
	 * @since   11.4
	 * @throws  RuntimeException
	 */
	public function getTableCreate($tables)
	{
		$this->connect();

		// Sanitize input to an array and iterate over the list.
		settype($tables, 'array');

		return $tables;
	}

	/**
	 * Retrieves field information about a given table.
	 *
	 * @param   string   $table     The name of the database table.
	 * @param   boolean  $typeOnly  True to only return field types.
	 *
	 * @return  array  An array of fields for the database table.
	 *
	 * @since   11.4
	 * @throws  RuntimeException
	 */
	public function getTableColumns($table, $typeOnly = true)
	{
		$this->connect();

		$columns = array();

		$query = 'pragma table_info( ' . $table . ')';

		$this->setQuery($query);

		$fields = $this->loadObjectList();

		if ($typeOnly)
		{
			foreach ($fields as $field)
			{
				$columns[$table][$field->name] = $field->type;
			}
		}
		else
		{
			foreach ($fields as $field)
			{
				$field->Default = $field->dflt_value;
				$columns[$field->name] = $field;
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
	 * @since   11.4
	 * @throws  RuntimeException
	 */
	public function getTableKeys($table)
	{
		$this->connect();

		$keys = array();
		$query = $this->getQuery(true);

		$fieldCasing = $this->getOption(PDO::ATTR_CASE);

		$this->setOption(PDO::ATTR_CASE, PDO::CASE_UPPER);

		$table = strtoupper($table);
		$query->setQuery('pragma table_info( ' . $table . ')');

		$this->setQuery($query);
		$rows = $this->loadObjectList();

		foreach ($rows as $column)
		{
			if ($column->PK == 1)
			{
				$keys[$column->NAME] = $column;
			}
		}

		$this->setOption(PDO::ATTR_CASE, $fieldCasing);

		return $keys;
	}

	/**
	 * Method to get an array of all tables in the database (schema).
	 *
	 * @return  array   An array of all the tables in the database.
	 *
	 * @since   11.4
	 * @throws  RuntimeException
	 */
	public function getTableList()
	{
		$this->connect();

		$query = $this->getQuery(true);

		$tables = array();

		$query->select('name');
		$query->from('sqlite_master');
		$query->where('type = :type');
		$query->bind(':type', 'table');
		$query->order('name');

		$this->setQuery($query);

		$tables = $this->loadResultArray();

		return $tables;
	}

	/**
	 * Get the version of the database connector.
	 *
	 * @return  string  The database connector version.
	 *
	 * @since   11.4
	 */
	public function getVersion()
	{
		$this->connect();

		$this->setQuery("SELECT sqlite_version()");

		return $this->loadResult();
	}

	/**
	 * Select a database for use.
	 *
	 * @param   string  $database  The name of the database to select for use.
	 *
	 * @return  boolean  True if the database was successfully selected.
	 *
	 * @since   11.4
	 * @throws  RuntimeException
	 */
	public function select($database)
	{
		$this->connect();

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
	 * @since   11.4
	 */
	public function setUTF()
	{
		$this->connect();

		return false;
	}

	/**
	 * Test to see if the PDO ODBC connector is available.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   11.4
	 */
	public static function test()
	{
		return in_array('sqlite', PDO::getAvailableDrivers());
	}
}
