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
 * @see http://www.sqlite.org/pragma.html
 * @see http://www.sqlite.org/docs.html
 *
 * @since       ¿
 */
class JDatabaseSqlite extends JDatabase implements  Serializable
{
	/**
	 * The name of the database driver.
	 *
	 * @var    string
	 * @since  ¿
	 */
	public $name = 'sqlite';

	/**
	 * @var PDO The database connection resource.
	 * @since ¿
	 */
	protected $connection;

	/**
	 * @var PDOStatement The database connection cursor from the last query.
	 * @since ¿
	 */
	protected $cursor;

	/**
	 * The character(s) used to quote SQL statement names such as table names or field names,
	 * etc. The child classes should define this as necessary. If a single character string the
	 * same character is used for both sides of the quoted name, else the first character will be
	 * used for the opening quote and the second for the closing quote.
	 *
	 * @var string
	 * @since ¿
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
	 * @deprecated
	 */
	public function hasUTF(){}

	/**
	 * @deprecated
	 */
	public function queryBatch($abortOnError = true, $transactionSafe = false){}

	/**
	 * @deprecated
	 */
	public function explain(){}

	public function serialize()
	{
		// Finder wants to clone us...
		return serialize(array());//$this->validator, $this->arguments, $this->code, $this->message));
	}

	public function unserialize($serialized)
	{
		//list($this->validator, $this->arguments, $this->code, $this->message) = unserialize($serialized);
	}

	/**
	 * Constructor.
	 *
	 * @param   array  $options  List of options used to configure the connection
	 *
	 * @since   ¿
	 */
	public function __construct($options)
	{
		// The name of the database file
		$options['database'] = (isset($options['database'])) ? $options['database'] : '';

		// The "host" is the path to the database - localhost means JPATH_ROOT/db
		$options['host'] = (isset($options['host'])) ? $options['host'] : 'localhost';

		$path =('localhost' == $options['host'])
			? JPATH_ROOT.'/db/'.$options['database']
			: $options['host'].'/'.$options['database'];

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

		// Attempt to connect to the database.
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
	 * @since   ¿
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
	 * @since   ¿
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
	 * @since   ¿
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
	 * @since   ¿
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
	 * @since   ¿
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
	 * @since   ¿
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
	 * @since   ¿
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
	 * @since   ¿
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
	 * @since   ¿
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
	 * @since   ¿
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
	 * @since   ¿
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
	 * Method to get the auto-incremented value from the last INSERT statement.
	 *
	 * @return  integer  The value of the auto-increment field from the last inserted row.
	 *
	 * @since   ¿
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
	 * @since   ¿
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
	 * @since   ¿
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
	 * @since   ¿
	 * @throws  JDatabaseException
	 */
	public function renameTable($oldTable, $newTable, $backup = null, $prefix = null){
	}

	/**
	 * Method to truncate a table.
	 *
	 * @param   string  $table  The table to truncate
	 *
	 * @return  void
	 *
	 * @since   ¿
	 * @throws  JDatabaseException
	 */
	public function truncateTable($table)
	{
		$this->setQuery('DELETE FROM ' . $this->quoteName($table))->query();

		return $this;
	}


	/**
	 * Method to commit a transaction.
	 *
	 * @return  void
	 *
	 * @since   ¿
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
	 * @since   ¿
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
	 * @since   ¿
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
	 * @since   ¿
	 * @throws  JDatabaseException
	 */
	public function unlockTables(){
	}

	/**
	 * Method to get the database collation in use by sampling a text field of a table in the database.
	 *
	 * @return  mixed  The collation in use by the database or boolean false if not supported.
	 *
	 * @since   ¿
	 */
	public function getCollation()
	{
		$this->connect();

		return $this->setQuery('pragma encoding')->loadResult();
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
	 * @since   ¿
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
	 * @since   ¿
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
	 * @since   ¿
	 * @throws  RuntimeException
	 */
	public function getTableKeys($table)
	{
		$this->connect();

		$keys = array();

		$query = $this->getQuery(true)->setQuery('pragma table_info( ' . $table . ')');

		$rows = $this->setQuery($query)->loadObjectList();

		foreach ($rows as $column)
		{
			if ($column->pk == 1)
			{
				$keys[$column->name] = $column;
			}
		}

		return $keys;
	}

	/**
	 * Method to get an array of all tables in the database (schema).
	 *
	 * @return  array   An array of all the tables in the database.
	 *
	 * @since   ¿
	 * @throws  RuntimeException
	 */
	public function getTableList()
	{
		$this->connect();

		$query = $this->getQuery(true)
			->from('sqlite_master')
			->select('name')
			->where('type = :type')
			->bind(':type', 'table')
			->order('name');

		return $this->setQuery($query)->loadColumn();
	}

	/**
	 * Get the version of the database connector.
	 *
	 * @return  string  The database connector version.
	 *
	 * @since   ¿
	 */
	public function getVersion()
	{
		$this->connect();

		return $this->setQuery('SELECT sqlite_version()')->loadResult();
	}

	/**
	 * Select a database for use.
	 *
	 * @param   string  $database  The name of the database to select for use.
	 *
	 * @return  boolean  True if the database was successfully selected.
	 *
	 * @since   ¿
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
	 * @since   ¿
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
	 * @since   ¿
	 */
	public static function test()
	{
		return in_array('sqlite', PDO::getAvailableDrivers());
	}

	public function integrityCheck()
	{
		$result = array();

		$rows = $this->setQuery('pragma integrity_check;')->loadObjectList();

		foreach ($rows as $column)
		{
			$result[] = $column->integrity_check;
		}

		return implode("\n", $result);
	}

}//class
