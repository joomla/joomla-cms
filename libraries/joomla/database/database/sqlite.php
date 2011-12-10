<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;




/*
 * This is a

FIRST SCRATCH                      !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! (!)
*/




JLoader::register('JDatabaseQuerySQLite', __DIR__ . '/sqlitequery.php');
// JLoader::register('JDatabaseExporterMySQL', dirname(__FILE__) . '/mysqlexporter.php');
// JLoader::register('JDatabaseImporterMySQL', dirname(__FILE__) . '/mysqlimporter.php');

/**
 * SQLite database driver
 *
 * @package     Joomla.Platform
 * @subpackage  Database
 * @see         http://www.sqlite.org
 * @see         http://www.php.net/manual/en/book.pdo.php
 * @since       ¿
 */
class JDatabaseSQLite extends JDatabase
{
	/**
	 * The name of the database driver.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $name = 'sqlite';

	/**
	 * @var    PDO  The database connection resource.
	 * @since  11.1
	 */
	protected $connection;

	/**
	 * @var    PDOStatement  The database connection cursor from the last query.
	 * @since  11.1
	 */
	protected $cursor;

	/**
	 * The character(s) used to quote SQL statement names such as table names or field names,
	 * etc. The child classes should define this as necessary.  If a single character string the
	 * same character is used for both sides of the quoted name, else the first character will be
	 * used for the opening quote and the second for the closing quote.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $nameQuote = ' ';

	/**
	 * The null or zero representation of a timestamp for the database driver.  This should be
	 * defined in child classes to hold the appropriate value for the engine.
	 *
	 * @var    string
	 * @since  ¿
	 */
	protected $nullDate = '0000-00-00 00:00:00';

	/**
	 * Constructor.
	 *
	 * @param   array  $options  Array of database options with keys: host, user, password, database, select.
	 *
	 * @since   ¿
	 */
	protected function __construct($options)
	{
		// Get some basic values from the options.
		// 		$options['host'] = (isset($options['host'])) ? $options['host'] : 'localhost';
		// 		$options['user'] = (isset($options['user'])) ? $options['user'] : 'root';
		// 		$options['password'] = (isset($options['password'])) ? $options['password'] : '';
		$options['database'] = (isset($options['database'])) ? $options['database'] : '';
		// 		$options['select'] = (isset($options['select'])) ? (bool) $options['select'] : true;

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
		// 		if (!($this->connection = @ mysql_connect($options['host'], $options['user'], $options['password'], true)))
		// 		$this->connection = sqlite_open($options['database'], $this->errorMsg);
		$this->connection = new PDO('sqlite:'.$path);

		if( ! $this->connection)
		{
			throw new JDatabaseException(sprintf('Unable to connect to the SQLite database in %s', $path));
		}

		// Set the error reporting attribute
		$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		// 		$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);


		// Finalize initialisation
		parent::__construct($options);
	}

	public function showTables($dbName)
	{
		;
	}
	public function renameTable($oldTable, $prefix = null, $backup = null, $newTable)
	{
		;
	}

	public function lock($table)
	{
		;
	}

	public function unlock()
	{
		;
	}


	/**
	 * Destructor.
	 *
	 * @since   ¿
	 */
	public function __destruct()
	{
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
	 * @since   ¿
	 */
	public function escape($text, $extra = false)
	{
		// @TODO !!?
		return str_replace("'", "''", $text);

		return str_replace("\"","\"\"",$text);
		return $text;
		return $this->connection->quote($text);//, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		return $sth->queryString;

		// 		$result = mysql_real_escape_string($text, $this->getConnection());

		// 		if ($extra)
		// 		{
		// 			$result = addcslashes($result, '%_');
		// 		}

		// 		return $result;
		}

		/**
		 * Test to see if the MySQL connector is available.
		 *
		 * @return  boolean  True on success, false otherwise.
		 *
		 * @since   ¿
		 */
		public static function test()
		{
			return true;//(function_exists('mysql_connect'));
		}

		public function isQuoted($field)
		{
			return false;
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
		 * Drops a table from the database.
		 *
		 * @param   string   $tableName  The name of the database table to drop.
		 * @param   boolean  $ifExists   Optionally specify that the table must exist before it is dropped.
		 *
		 * @return  JDatabaseMySQL  Returns this object to support chaining.
		 *
		 * @since   ¿
		 */
		public function dropTable($tableName, $ifExists = true)
		{
			$query = $this->getQuery(true);

			$this->setQuery('DROP TABLE ' . ($ifExists ? 'IF EXISTS ' : '') . $query->quoteName($tableName));

			$this->query();

			return $this;
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
		 * Method to get the database collation in use by sampling a text field of a table in the database.
		 *
		 * @return  mixed  The collation in use by the database (string) or boolean false if not supported.
		 *
		 * @since   ¿
		 * @throws  JDatabaseException
		 */
		public function getCollation()
		{
			return '¿';
			// 		$this->setQuery('SHOW FULL COLUMNS FROM #__users');
			// 		$array = $this->loadAssocList();
			// 		return $array['2']['Collation'];
		}

		/**
		 * Gets an exporter class object.
		 *
		 * @return  JDatabaseExporterMySQL  An exporter object.
		 *
		 * @since   ¿
		 * @throws  JDatabaseException
		 */
		public function getExporter()
		{
			// Make sure we have an exporter class for this driver.
			if (!class_exists('JDatabaseExporterMySQL'))
			{
				throw new JDatabaseException(JText::_('JLIB_DATABASE_ERROR_MISSING_EXPORTER'));
			}

			$o = new JDatabaseExporterMySQL;
			$o->setDbo($this);

			return $o;
		}

		/**
		 * Gets an importer class object.
		 *
		 * @return  JDatabaseImporterMySQL  An importer object.
		 *
		 * @since   ¿
		 * @throws  JDatabaseException
		 */
		public function getImporter()
		{
			// Make sure we have an importer class for this driver.
			if (!class_exists('JDatabaseImporterMySQL'))
			{
				throw new JDatabaseException(JText::_('JLIB_DATABASE_ERROR_MISSING_IMPORTER'));
			}

			$o = new JDatabaseImporterMySQL;
			$o->setDbo($this);

			return $o;
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
		public function getNumRows($cursor = null)
		{
			return 0;
			throw new Exception('NOT IMPLEMENTED: '.__METHOD__);
			//return mysql_num_rows($cursor ? $cursor : $this->cursor);
		}

		/**
		 * Get the current or query, or new JDatabaseQuery object.
		 *
		 * @param   boolean  $new  False to return the last query set, True to return a new JDatabaseQuery object.
		 *
		 * @return  mixed  The current value of the internal SQL variable or a new JDatabaseQuery object.
		 *
		 * @since   ¿
		 * @throws  JDatabaseException
		 */
		public function getQuery($new = false)
		{
			if ($new)
			{
				// Make sure we have a query class for this driver.
				if (!class_exists('JDatabaseQuerySQLite'))
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
		 * Shows the table CREATE statement that creates the given tables.
		 *
		 * @param   mixed  $tables  A table name or a list of table names.
		 *
		 * @return  array  A list of the create SQL for the tables.
		 *
		 * @since   ¿
		 * @throws  JDatabaseException
		 */
		public function getTableCreate($tables)
		{
			// Initialise variables.
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
		 * @since   ¿
		 * @throws  JDatabaseException
		 */
		public function getTableColumns($table, $typeOnly = true)
		{
			$result = array();

			// Set the query to get the table fields statement.
			$this->setQuery('PRAGMA table_info('.$table.');');
			//$this->setQuery('SHOW FULL COLUMNS FROM ' . $this->quoteName($this->escape($table)));

			$fields = $this->loadObjectList();

			// If we only want the type as the value add just that to the list.
			if ($typeOnly)
			{
				foreach ($fields as $field)
				{
					$result[$field->name] = preg_replace("/[(0-9)]/", '', $field->type);
				}
			}
			// If we want the whole field data object add that to the list.
			else
			{
				foreach ($fields as $field)
				{
					$field->Default = $field->dflt_value;
					$result[$field->name] = $field;
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
		 * @since   ¿
		 * @throws  JDatabaseException
		 */
		public function getTableKeys($table)
		{
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
		 * @since   ¿
		 * @throws  JDatabaseException
		 */
		public function getTableList()
		{
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
		 * @since   ¿
		 */
		public function getVersion()
		{
			throw new Exception('NOT IMPLEMENTED: '.__METHOD__);

			return mysql_get_server_info($this->connection);
		}

		/**
		 * Determines if the database engine supports UTF-8 character encoding.
		 *
		 * @return  boolean  True if supported.
		 *
		 * @since   ¿
		 * @deprecated 12.1
		 */
		public function hasUTF()
		{
			// 		JLog::add('JDatabaseMySQL::hasUTF() is deprecated.', JLog::WARNING, 'deprecated');
			return true;
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
			//		return mysql_insert_id($this->connection);
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
				// 			JLog::add(JText::sprintf('JLIB_DATABASE_QUERY_FAILED', $this->errorNum, $this->errorMsg), JLog::ERROR, 'database');
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

				// 			JLog::add($sql, JLog::DEBUG, 'databasequery');
			}

			// Reset the error values.
			$this->errorNum = 0;
			$this->errorMsg = '';

			// Execute the query.
			// 		$this->cursor = mysql_query($sql, $this->connection);
			// 		$sss = $this->connection->prepare((string)$sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
			// 		$sql = $this->connection->quote($sql);
			$this->cursor = $this->connection->query($sql);//ss->queryString);

			// If an error occurred handle it.
			if (!$this->cursor)
			{
				$this->errorNum = (int) $this->connection->errorCode();
				$info = $this->connection->errorInfo();
				$this->errorMsg = $info[0].' ('.$info[1].') '.$info[2].' SQL = '.$sql;

				// 			JLog::add(JText::sprintf('JLIB_DATABASE_QUERY_FAILED', $this->errorNum, $this->errorMsg), JLog::ERROR, 'databasequery');
				throw new JDatabaseException(__METHOD__.' - '.$this->errorMsg, $this->errorNum);
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
		 * @since   ¿
		 * @throws  JDatabaseException
		 */
		public function select($database)
		{
			throw new Exception('NOT IMPLEMENTED: '.__METHOD__);

			if (!$database)
			{
				return false;
			}

			if (!mysql_select_db($database, $this->connection))
			{
				// Legacy error handling switch based on the JError::$legacy switch.
				// @deprecated  12.1
				if (JError::$legacy)
				{
					$this->errorNum = 3;
					$this->errorMsg = JText::_('JLIB_DATABASE_ERROR_DATABASE_CONNECT');
					return false;
				}
				else
				{
					throw new JDatabaseException(JText::_('JLIB_DATABASE_ERROR_DATABASE_CONNECT'));
				}
			}

			return true;
		}

		/**
		 * Set the connection to use UTF-8 character encoding.
		 *
		 * @return  boolean  True on success.
		 *
		 * @since   ¿
		 */
		public function setUTF()
		{
			//return mysql_query("SET NAMES 'utf8'", $this->connection);
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
			//		return mysql_fetch_assoc($cursor ? $cursor : $this->cursor);
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
			// 		mysql_free_result($cursor ? $cursor : $this->cursor);
		}

		/**
		 * Diagnostic method to return explain information for a query.
		 *
		 * @return      string  The explain output.
		 *
		 * @since       ¿
		 * @deprecated  12.1
		 */
		public function explain()
		{
			// Deprecation warning.
			JLog::add('JDatabaseMySQL::explain() is deprecated.', JLog::WARNING, 'deprecated');

			// Backup the current query so we can reset it later.
			$backup = $this->sql;

			// Prepend the current query with EXPLAIN so we get the diagnostic data.
			$this->sql = 'EXPLAIN ' . $this->sql;

			// Execute the query and get the result set cursor.
			if (!($cursor = $this->query()))
			{
				return null;
			}

			// Build the HTML table.
			$first = true;
			$buffer = '<table id="explain-sql">';
			$buffer .= '<thead><tr><td colspan="99">' . $this->getQuery() . '</td></tr>';
			while ($row = $this->fetchAssoc($cursor))
			{
				if ($first)
				{
					$buffer .= '<tr>';
					foreach ($row as $k => $v)
					{
						$buffer .= '<th>' . $k . '</th>';
					}
					$buffer .= '</tr></thead><tbody>';
					$first = false;
				}
				$buffer .= '<tr>';
				foreach ($row as $k => $v)
				{
					$buffer .= '<td>' . $v . '</td>';
				}
				$buffer .= '</tr>';
			}
			$buffer .= '</tbody></table>';

			// Restore the original query to its state before we ran the explain.
			$this->sql = $backup;

			// Free up system resources and return.
			$this->freeResult($cursor);

			return $buffer;
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
		 * @since   ¿
		 */
		public function queryBatch($abortOnError = true, $transactionSafe = false)
		{
			throw new Exception('NOT IMPLEMENTED: '.__METHOD__);

			// Deprecation warning.
			JLog::add('JDatabaseMySQL::queryBatch() is deprecated.', JLog::WARNING, 'deprecated');

			$sql = $this->replacePrefix((string) $this->sql);
			$this->errorNum = 0;
			$this->errorMsg = '';

			// If the batch is meant to be transaction safe then we need to wrap it in a transaction.
			if ($transactionSafe)
			{
				$sql = 'START TRANSACTION;' . rtrim($sql, "; \t\r\n\0") . '; COMMIT;';
			}
			$queries = $this->splitSql($sql);
			$error = 0;
			foreach ($queries as $query)
			{
				$query = trim($query);
				if ($query != '')
				{
					$this->cursor = mysql_query($query, $this->connection);
					if ($this->debug)
					{
						$this->count++;
						$this->log[] = $query;
					}
					if (!$this->cursor)
					{
						$error = 1;
						$this->errorNum .= mysql_errno($this->connection) . ' ';
						$this->errorMsg .= mysql_error($this->connection) . " SQL=$query <br />";
						if ($abortOnError)
						{
							return $this->cursor;
						}
					}
				}
			}
			return $error ? false : true;
		}
	}
