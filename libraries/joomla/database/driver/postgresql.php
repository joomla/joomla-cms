<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * PostgreSQL database driver
 *
 * @package     Joomla.Platform
 * @subpackage  Database
 * @since       12.1
 */
class JDatabaseDriverPostgresql extends JDatabaseDriver
{
	/**
	 * The database driver name
	 *
	 * @var string
	 */
	public $name = 'postgresql';

	/**
	 * Quote for named objects
	 *
	 * @var string
	 */
	protected $nameQuote = '"';

	/**
	 *  The null/zero date string
	 *
	 * @var string
	 */
	protected $nullDate = '1970-01-01 00:00:00';

	/**
	 * @var    string  The minimum supported database version.
	 * @since  12.1
	 */
	protected static $dbMinimum = '8.3.18';

	/**
	 * Operator used for concatenation
	 *
	 * @var string
	 */
	protected $concat_operator = '||';

	/**
	 * JDatabaseDriverPostgresqlQuery object returned by getQuery
	 *
	 * @var JDatabaseDriverPostgresqlQuery
	 */
	protected $queryObject = null;

	/**
	 * Database object constructor
	 *
	 * @param   array  $options  List of options used to configure the connection
	 *
	 * @since	12.1
	 */
	public function __construct( $options )
	{
		$options['host'] = (isset($options['host'])) ? $options['host'] : 'localhost';
		$options['user'] = (isset($options['user'])) ? $options['user'] : '';
		$options['password'] = (isset($options['password'])) ? $options['password'] : '';
		$options['database'] = (isset($options['database'])) ? $options['database'] : '';

		// Finalize initialization
		parent::__construct($options);
	}

	/**
	 * Database object destructor
	 *
	 * @since 12.1
	 */
	public function __destruct()
	{
		$this->disconnect();
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

		// Make sure the postgresql extension for PHP is installed and enabled.
		if (!function_exists('pg_connect'))
		{
			throw new RuntimeException('PHP extension pg_connect is not available.');
		}

		// Build the DSN for the connection.
		$dsn = "host={$this->options['host']} dbname={$this->options['database']} user={$this->options['user']} password={$this->options['password']}";

		// Attempt to connect to the server.
		if (!($this->connection = @pg_connect($dsn)))
		{
			throw new RuntimeException('Error connecting to PGSQL database.');
		}

		pg_set_error_verbosity($this->connection, PGSQL_ERRORS_DEFAULT);
		pg_query('SET standard_conforming_strings=off');
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
		// Close the connection.
		if (is_resource($this->connection))
		{
			foreach ($this->disconnectHandlers as $h)
			{
				call_user_func_array($h, array( &$this));
			}

			pg_close($this->connection);
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
	 * @since   12.1
	 */
	public function escape($text, $extra = false)
	{
		$this->connect();

		$result = pg_escape_string($this->connection, $text);

		if ($extra)
		{
			$result = addcslashes($result, '%_');
		}

		return $result;
	}

	/**
	 * Test to see if the PostgreSQL connector is available
	 *
	 * @return boolean  True on success, false otherwise.
	 */
	public static function test()
	{
		return (function_exists('pg_connect'));
	}

	/**
	 * Determines if the connection to the server is active.
	 *
	 * @return	boolean
	 *
	 * @since	12.1
	 */
	public function connected()
	{
		$this->connect();

		if (is_resource($this->connection))
		{
			return pg_ping($this->connection);
		}

		return false;
	}

	/**
	 * Drops a table from the database.
	 *
	 * @param   string   $tableName  The name of the database table to drop.
	 * @param   boolean  $ifExists   Optionally specify that the table must exist before it is dropped.
	 *
	 * @return  boolean	true
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function dropTable($tableName, $ifExists = true)
	{
		$this->connect();

		$this->setQuery('DROP TABLE ' . ($ifExists ? 'IF EXISTS ' : '') . $this->quoteName($tableName));
		$this->execute();

		return true;
	}

	/**
	 * Get the number of affected rows for the previous executed SQL statement.
	 *
	 * @return int The number of affected rows in the previous operation
	 *
	 * @since 12.1
	 */
	public function getAffectedRows()
	{
		$this->connect();

		return pg_affected_rows($this->cursor);
	}

	/**
	 * Method to get the database collation in use by sampling a text field of a table in the database.
	 *
	 * @return  mixed  The collation in use by the database or boolean false if not supported.
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function getCollation()
	{
		$this->connect();

		$this->setQuery('SHOW LC_COLLATE');
		$array = $this->loadAssocList();

		return $array[0]['lc_collate'];
	}

	/**
	 * Get the number of returned rows for the previous executed SQL statement.
	 *
	 * @param   resource  $cur  An optional database cursor resource to extract the row count from.
	 *
	 * @return  integer   The number of returned rows.
	 *
	 * @since   12.1
	 */
	public function getNumRows( $cur = null )
	{
		$this->connect();

		return pg_num_rows((int) $cur ? $cur : $this->cursor);
	}

	/**
	 * Get the current or query, or new JDatabaseQuery object.
	 *
	 * @param   boolean  $new    False to return the last query set, True to return a new JDatabaseQuery object.
	 * @param   boolean  $asObj  False to return last query as string, true to get JDatabaseQueryPostgresql object.
	 *
	 * @return  JDatabaseQuery  The current query object or a new object extending the JDatabaseQuery class.
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function getQuery($new = false, $asObj = false)
	{
		if ($new)
		{
			// Make sure we have a query class for this driver.
			if (!class_exists('JDatabaseQueryPostgresql'))
			{
				throw new RuntimeException('JDatabaseQueryPostgresql Class not found.');
			}

			$this->queryObject = new JDatabaseQueryPostgresql($this);

			return $this->queryObject;
		}
		else
		{
			if ($asObj)
			{
				return $this->queryObject;
			}
			else
			{
				return $this->sql;
			}
		}
	}

	/**
	 * Shows the table CREATE statement that creates the given tables.
	 *
	 * This is unsuported by PostgreSQL.
	 *
	 * @param   mixed  $tables  A table name or a list of table names.
	 *
	 * @return  char  An empty char because this function is not supported by PostgreSQL.
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function getTableCreate($tables)
	{
		return '';
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

		$result = array();

		$tableSub = $this->replacePrefix($table);

		$this->setQuery('
				SELECT a.attname AS "column_name",
					pg_catalog.format_type(a.atttypid, a.atttypmod) as "type",
					CASE WHEN a.attnotnull IS TRUE
						THEN \'NO\'
						ELSE \'YES\'
					END AS "null",
					CASE WHEN pg_catalog.pg_get_expr(adef.adbin, adef.adrelid, true) IS NOT NULL
						THEN pg_catalog.pg_get_expr(adef.adbin, adef.adrelid, true)
					END as "Default",
					CASE WHEN pg_catalog.col_description(a.attrelid, a.attnum) IS NULL
					THEN \'\'
					ELSE pg_catalog.col_description(a.attrelid, a.attnum)
					END  AS "comments"
				FROM pg_catalog.pg_attribute a
				LEFT JOIN pg_catalog.pg_attrdef adef ON a.attrelid=adef.adrelid AND a.attnum=adef.adnum
				LEFT JOIN pg_catalog.pg_type t ON a.atttypid=t.oid
				WHERE a.attrelid =
					(SELECT oid FROM pg_catalog.pg_class WHERE relname=' . $this->quote($tableSub) . '
						AND relnamespace = (SELECT oid FROM pg_catalog.pg_namespace WHERE
						nspname = \'public\')
					)
				AND a.attnum > 0 AND NOT a.attisdropped
				ORDER BY a.attnum'
		);

		$fields = $this->loadObjectList();

		if ($typeOnly)
		{
			foreach ($fields as $field)
			{
				$result[$field->column_name] = preg_replace("/[(0-9)]/", '', $field->type);
			}
		}
		else
		{
			foreach ($fields as $field)
			{
				$result[$field->column_name] = $field;
			}
		}

		/* Change Postgresql's NULL::* type with PHP's null one */
		foreach ($fields as $field)
		{
			if (preg_match("/^NULL::*/", $field->Default))
			{
				$field->Default = null;
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
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function getTableKeys($table)
	{
		$this->connect();

		// To check if table exists and prevent SQL injection
		$tableList = $this->getTableList();

		if ( in_array($table, $tableList) )
		{
			// Get the details columns information.
			$this->setQuery('
					SELECT indexname AS "idxName", indisprimary AS "isPrimary", indisunique  AS "isUnique",
						CASE WHEN indisprimary = true THEN
							( SELECT \'ALTER TABLE \' || tablename || \' ADD \' || pg_catalog.pg_get_constraintdef(const.oid, true)
								FROM pg_constraint AS const WHERE const.conname= pgClassFirst.relname )
						ELSE pg_catalog.pg_get_indexdef(indexrelid, 0, true)
						END AS "Query"
					FROM pg_indexes
					LEFT JOIN pg_class AS pgClassFirst ON indexname=pgClassFirst.relname
					LEFT JOIN pg_index AS pgIndex ON pgClassFirst.oid=pgIndex.indexrelid
					WHERE tablename=' . $this->quote($table) . ' ORDER BY indkey'
			);
			$keys = $this->loadObjectList();

			return $keys;
		}
		return false;
	}

	/**
	 * Method to get an array of all tables in the database.
	 *
	 * @return  array  An array of all the tables in the database.
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function getTableList()
	{
		$this->connect();

		$query = $this->getQuery(true)
			->select('table_name')
			->from('information_schema.tables')
			->where('table_type=' . $this->quote('BASE TABLE'))
			->where(
				'table_schema NOT IN (' . $this->quote('pg_catalog') . ', ' . $this->quote('information_schema') . ')'
			)
			->order('table_name ASC');

		$this->setQuery($query);
		$tables = $this->loadColumn();

		return $tables;
	}

	/**
	 * Get the details list of sequences for a table.
	 *
	 * @param   string  $table  The name of the table.
	 *
	 * @return  array  An array of sequences specification for the table.
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function getTableSequences($table)
	{
		$this->connect();

		// To check if table exists and prevent SQL injection
		$tableList = $this->getTableList();

		if ( in_array($table, $tableList) )
		{
			$name = array('s.relname', 'n.nspname', 't.relname', 'a.attname', 'info.data_type',
							'info.minimum_value', 'info.maximum_value', 'info.increment', 'info.cycle_option');
			$as = array('sequence', 'schema', 'table', 'column', 'data_type',
							'minimum_value', 'maximum_value', 'increment', 'cycle_option');

			if (version_compare($this->getVersion(), '9.1.0') >= 0)
			{
				$name[] .= 'info.start_value';
				$as[] .= 'start_value';
			}

			// Get the details columns information.
			$query = $this->getQuery(true)
				->select($this->quoteName($name, $as))
				->from('pg_class AS s')
				->join('LEFT', "pg_depend d ON d.objid=s.oid AND d.classid='pg_class'::regclass AND d.refclassid='pg_class'::regclass")
				->join('LEFT', 'pg_class t ON t.oid=d.refobjid')
				->join('LEFT', 'pg_namespace n ON n.oid=t.relnamespace')
				->join('LEFT', 'pg_attribute a ON a.attrelid=t.oid AND a.attnum=d.refobjsubid')
				->join('LEFT', 'information_schema.sequences AS info ON info.sequence_name=s.relname')
				->where("s.relkind='S' AND d.deptype='a' AND t.relname=" . $this->quote($table));
			$this->setQuery($query);
			$seq = $this->loadObjectList();

			return $seq;
		}
		return false;
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
		$version = pg_version($this->connection);

		return $version['server'];
	}

	/**
	 * Method to get the auto-incremented value from the last INSERT statement.
	 * To be called after the INSERT statement, it's MANDATORY to have a sequence on
	 * every primary key table.
	 *
	 * To get the auto incremented value it's possible to call this function after
	 * INSERT INTO query, or use INSERT INTO with RETURNING clause.
	 *
	 * @example with insertid() call:
	 *		$query = $this->getQuery(true)
	 *			->insert('jos_dbtest')
	 *			->columns('title,start_date,description')
	 *			->values("'testTitle2nd','1971-01-01','testDescription2nd'");
	 *		$this->setQuery($query);
	 *		$this->execute();
	 *		$id = $this->insertid();
	 *
	 * @example with RETURNING clause:
	 *		$query = $this->getQuery(true)
	 *			->insert('jos_dbtest')
	 *			->columns('title,start_date,description')
	 *			->values("'testTitle2nd','1971-01-01','testDescription2nd'")
	 *			->returning('id');
	 *		$this->setQuery($query);
	 *		$id = $this->loadResult();
	 *
	 * @return  integer  The value of the auto-increment field from the last inserted row.
	 *
	 * @since   12.1
	 */
	public function insertid()
	{
		$this->connect();
		$insertQuery = $this->getQuery(false, true);
		$table = $insertQuery->__get('insert')->getElements();

		/* find sequence column name */
		$colNameQuery = $this->getQuery(true);
		$colNameQuery->select('column_default')
						->from('information_schema.columns')
						->where(
								"table_name=" . $this->quote(
									$this->replacePrefix(str_replace('"', '', $table[0]))
								), 'AND'
						)
						->where("column_default LIKE '%nextval%'");

		$this->setQuery($colNameQuery);
		$colName = $this->loadRow();
		$changedColName = str_replace('nextval', 'currval', $colName);

		$insertidQuery = $this->getQuery(true);
		$insertidQuery->select($changedColName);
		$this->setQuery($insertidQuery);
		$insertVal = $this->loadRow();

		return $insertVal[0];
	}

	/**
	 * Locks a table in the database.
	 *
	 * @param   string  $tableName  The name of the table to unlock.
	 *
	 * @return  JDatabaseDriverPostgresql  Returns this object to support chaining.
	 *
	 * @since   11.4
	 * @throws  RuntimeException
	 */
	public function lockTable($tableName)
	{
		$this->transactionStart();
		$this->setQuery('LOCK TABLE ' . $this->quoteName($tableName) . ' IN ACCESS EXCLUSIVE MODE')->execute();

		return $this;
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

		if (!is_resource($this->connection))
		{
			JLog::add(JText::sprintf('JLIB_DATABASE_QUERY_FAILED', $this->errorNum, $this->errorMsg), JLog::ERROR, 'database');
			throw new RuntimeException($this->errorMsg, $this->errorNum);
		}

		// Take a local copy so that we don't modify the original query and cause issues later
		$query = $this->replacePrefix((string) $this->sql);

		if ($this->limit > 0 || $this->offset > 0)
		{
			$query .= ' LIMIT ' . $this->limit . ' OFFSET ' . $this->offset;
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
		$this->cursor = @pg_query($this->connection, $query);

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
					$this->errorNum = (int) pg_result_error_field($this->cursor, PGSQL_DIAG_SQLSTATE) . ' ';
					$this->errorMsg = JText::_('JLIB_DATABASE_QUERY_FAILED') . "\n" . pg_last_error($this->connection) . "\nSQL=" . $query;

					// Throw the normal query exception.
					JLog::add(JText::sprintf('JLIB_DATABASE_QUERY_FAILED', $this->errorNum, $this->errorMsg), JLog::ERROR, 'databasequery');
					throw new RuntimeException($this->errorMsg);
				}

				// Since we were able to reconnect, run the query again.
				return $this->execute();
			}
			// The server was not disconnected.
			else
			{
				// Get the error number and message.
				$this->errorNum = (int) pg_result_error_field($this->cursor, PGSQL_DIAG_SQLSTATE) . ' ';
				$this->errorMsg = JText::_('JLIB_DATABASE_QUERY_FAILED') . "\n" . pg_last_error($this->connection) . "\nSQL=" . $query;

				// Throw the normal query exception.
				JLog::add(JText::sprintf('JLIB_DATABASE_QUERY_FAILED', $this->errorNum, $this->errorMsg), JLog::ERROR, 'databasequery');
				throw new RuntimeException($this->errorMsg);
			}
		}

		return $this->cursor;
	}

	/**
	 * Renames a table in the database.
	 *
	 * @param   string  $oldTable  The name of the table to be renamed
	 * @param   string  $newTable  The new name for the table.
	 * @param   string  $backup    Not used by PostgreSQL.
	 * @param   string  $prefix    Not used by PostgreSQL.
	 *
	 * @return  JDatabaseDriverPostgresql  Returns this object to support chaining.
	 *
	 * @since   11.4
	 * @throws  RuntimeException
	 */
	public function renameTable($oldTable, $newTable, $backup = null, $prefix = null)
	{
		$this->connect();

		// To check if table exists and prevent SQL injection
		$tableList = $this->getTableList();

		// Origin Table does not exist
		if ( !in_array($oldTable, $tableList) )
		{
			// Origin Table not found
			throw new RuntimeException('Table not found in Postgresql database.');
		}
		else
		{
			/* Rename indexes */
			$this->setQuery(
							'SELECT relname
								FROM pg_class
								WHERE oid IN (
									SELECT indexrelid
									FROM pg_index, pg_class
									WHERE pg_class.relname=' . $this->quote($oldTable, true) . '
									AND pg_class.oid=pg_index.indrelid );'
			);

			$oldIndexes = $this->loadColumn();

			foreach ($oldIndexes as $oldIndex)
			{
				$changedIdxName = str_replace($oldTable, $newTable, $oldIndex);
				$this->setQuery('ALTER INDEX ' . $this->escape($oldIndex) . ' RENAME TO ' . $this->escape($changedIdxName));
				$this->execute();
			}

			/* Rename sequence */
			$this->setQuery(
							'SELECT relname
								FROM pg_class
								WHERE relkind = \'S\'
								AND relnamespace IN (
									SELECT oid
									FROM pg_namespace
									WHERE nspname NOT LIKE \'pg_%\'
									AND nspname != \'information_schema\'
								)
								AND relname LIKE \'%' . $oldTable . '%\' ;'
			);

			$oldSequences = $this->loadColumn();

			foreach ($oldSequences as $oldSequence)
			{
				$changedSequenceName = str_replace($oldTable, $newTable, $oldSequence);
				$this->setQuery('ALTER SEQUENCE ' . $this->escape($oldSequence) . ' RENAME TO ' . $this->escape($changedSequenceName));
				$this->execute();
			}

			/* Rename table */
			$this->setQuery('ALTER TABLE ' . $this->escape($oldTable) . ' RENAME TO ' . $this->escape($newTable));
			$this->execute();
		}

		return true;
	}

	/**
	 * Selects the database, but redundant for PostgreSQL
	 *
	 * @param   string  $database  Database name to select.
	 *
	 * @return  boolean  Always true
	 */
	public function select($database)
	{
		return true;
	}

	/**
	 * Custom settings for UTF support
	 *
	 * @return  int  Zero on success, -1 on failure
	 *
	 * @since   12.1
	 */
	public function setUTF()
	{
		$this->connect();

		return pg_set_client_encoding($this->connection, 'UTF8');
	}

	/**
	 * This function return a field value as a prepared string to be used in a SQL statement.
	 *
	 * @param   array   $columns      Array of table's column returned by ::getTableColumns.
	 * @param   string  $field_name   The table field's name.
	 * @param   string  $field_value  The variable value to quote and return.
	 *
	 * @return  string  The quoted string.
	 *
	 * @since   11.3
	 */
	public function sqlValue($columns, $field_name, $field_value)
	{
		switch ($columns[$field_name])
		{
			case 'boolean':
				$val = 'NULL';

				if ($field_value == 't')
				{
					$val = 'TRUE';
				}
				elseif ($field_value == 'f')
				{
					$val = 'FALSE';
				}
				break;

			case 'bigint':
			case 'bigserial':
			case 'integer':
			case 'money':
			case 'numeric':
			case 'real':
			case 'smallint':
			case 'serial':
			case 'numeric,':
				$val = strlen($field_value) == 0 ? 'NULL' : $field_value;
				break;

			case 'date':
			case 'timestamp without time zone':
				if (empty($field_value))
				{
					$field_value = $this->getNullDate();
				}
				$val = $this->quote($field_value);
				break;

			default:
				$val = $this->quote($field_value);
				break;
		}

		return $val;
	}

	/**
	 * Method to commit a transaction.
	 *
	 * @param   boolean  $toSavepoint  If true, commit to the last savepoint.
	 *
	 * @return  void
	 *
	 * @since   12.1
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
	 * @since   12.1
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
			$this->setQuery('RELEASE SAVEPOINT ' . $this->quoteName($savepoint))->execute();
		}
	}

	/**
	 * Method to initialize a transaction.
	 *
	 * @param   boolean  $asSavepoint  If true and a transaction is already active, a savepoint will be created.
	 *
	 * @return  void
	 *
	 * @since   12.1
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
	 * @since   12.1
	 */
	protected function fetchArray($cursor = null)
	{
		return pg_fetch_row($cursor ? $cursor : $this->cursor);
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
		return pg_fetch_assoc($cursor ? $cursor : $this->cursor);
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
		return pg_fetch_object(is_null($cursor) ? $this->cursor : $cursor, null, $class);
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
		pg_free_result($cursor ? $cursor : $this->cursor);
	}

	/**
	 * Inserts a row into a table based on an object's properties.
	 *
	 * @param   string  $table    The name of the database table to insert into.
	 * @param   object  &$object  A reference to an object whose public properties match the table fields.
	 * @param   string  $key      The name of the primary key. If provided the object property is updated.
	 *
	 * @return  boolean    True on success.
	 *
	 * @since   11.1
	 * @throws  RuntimeException
	 */
	public function insertObject($table, &$object, $key = null)
	{
		$columns = $this->getTableColumns($table);

		$fields = array();
		$values = array();

		// Iterate over the object variables to build the query fields and values.
		foreach (get_object_vars($object) as $k => $v)
		{
			// Only process non-null scalars.
			if (is_array($v) or is_object($v) or $v === null)
			{
				continue;
			}

			// Ignore any internal fields.
			if ($k[0] == '_')
			{
				continue;
			}

			// Prepare and sanitize the fields and values for the database query.
			$fields[] = $this->quoteName($k);
			$values[] = $this->sqlValue($columns, $k, $v);
		}

		// Create the base insert statement.
		$query = $this->getQuery(true)
			->insert($this->quoteName($table))
			->columns($fields)
			->values(implode(',', $values));

		$retVal = false;

		if ($key)
		{
			$query->returning($key);

			// Set the query and execute the insert.
			$this->setQuery($query);

			$id = $this->loadResult();
			if ($id)
			{
				$object->$key = $id;
				$retVal = true;
			}
		}
		else
		{
			// Set the query and execute the insert.
			$this->setQuery($query);

			if ($this->execute())
			{
				$retVal = true;
			}
		}

		return $retVal;
	}

	/**
	 * Test to see if the PostgreSQL connector is available.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   12.1
	 */
	public static function isSupported()
	{
		return (function_exists('pg_connect'));
	}

	/**
	 * Returns an array containing database's table list.
	 *
	 * @return	array	The database's table list.
	 */
	public function showTables()
	{
		$this->connect();

		$query = $this->getQuery(true)
			->select('table_name')
			->from('information_schema.tables')
			->where('table_type=' . $this->quote('BASE TABLE'))
			->where(
				'table_schema NOT IN (' . $this->quote('pg_catalog') . ', ' . $this->quote('information_schema') . ' )'
			);

		$this->setQuery($query);
		$tableList = $this->loadColumn();

		return $tableList;
	}

	/**
	 * Get the substring position inside a string
	 *
	 * @param   string  $substring  The string being sought
	 * @param   string  $string     The string/column being searched
	 *
	 * @return int   The position of $substring in $string
	 */
	public function getStringPositionSQL( $substring, $string )
	{
		$this->connect();

		$query = "SELECT POSITION( $substring IN $string )";
		$this->setQuery($query);
		$position = $this->loadRow();

		return $position['position'];
	}

	/**
	 * Generate a random value
	 *
	 * @return float The random generated number
	 */
	public function getRandom()
	{
		$this->connect();

		$this->setQuery('SELECT RANDOM()');
		$random = $this->loadAssoc();

		return $random['random'];
	}

	/**
	 * Get the query string to alter the database character set.
	 *
	 * @param   string  $dbName  The database name
	 *
	 * @return  string  The query that alter the database query string
	 *
	 * @since   12.1
	 */
	public function getAlterDbCharacterSet( $dbName )
	{
		$query = 'ALTER DATABASE ' . $this->quoteName($dbName) . ' SET CLIENT_ENCODING TO ' . $this->quote('UTF8');

		return $query;
	}

	/**
	 * Get the query string to create new Database in correct PostgreSQL syntax.
	 *
	 * @param   object   $options  object coming from "initialise" function to pass user
	 * 									and database name to database driver.
	 * @param   boolean  $utf      True if the database supports the UTF-8 character set,
	 * 									not used in PostgreSQL "CREATE DATABASE" query.
	 *
	 * @return  string	The query that creates database, owned by $options['user']
	 *
	 * @since   12.1
	 */
	public function getCreateDbQuery($options, $utf)
	{
		$query = 'CREATE DATABASE ' . $this->quoteName($options->db_name) . ' OWNER ' . $this->quoteName($options->db_user);

		if ($utf)
		{
			$query .= ' ENCODING ' . $this->quote('UTF-8');
		}

		return $query;
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
	 * @since   12.1
	 */
	public function replacePrefix($query, $prefix = '#__')
	{
		$query = trim($query);

		if (strpos($query, '\''))
		{
			// Sequence name quoted with ' ' but need to be replaced
			if (strpos($query, 'currval'))
			{
				$query = explode('currval', $query);

				for ($nIndex = 1; $nIndex < count($query); $nIndex = $nIndex + 2)
				{
					$query[$nIndex] = str_replace($prefix, $this->tablePrefix, $query[$nIndex]);
				}
				$query = implode('currval', $query);
			}

			// Sequence name quoted with ' ' but need to be replaced
			if (strpos($query, 'nextval'))
			{
				$query = explode('nextval', $query);

				for ($nIndex = 1; $nIndex < count($query); $nIndex = $nIndex + 2)
				{
					$query[$nIndex] = str_replace($prefix, $this->tablePrefix, $query[$nIndex]);
				}
				$query = implode('nextval', $query);
			}

			// Sequence name quoted with ' ' but need to be replaced
			if (strpos($query, 'setval'))
			{
				$query = explode('setval', $query);

				for ($nIndex = 1; $nIndex < count($query); $nIndex = $nIndex + 2)
				{
					$query[$nIndex] = str_replace($prefix, $this->tablePrefix, $query[$nIndex]);
				}
				$query = implode('setval', $query);
			}

			$explodedQuery = explode('\'', $query);

			for ($nIndex = 0; $nIndex < count($explodedQuery); $nIndex = $nIndex + 2)
			{
				if (strpos($explodedQuery[$nIndex], $prefix))
				{
					$explodedQuery[$nIndex] = str_replace($prefix, $this->tablePrefix, $explodedQuery[$nIndex]);
				}
			}

			$replacedQuery = implode('\'', $explodedQuery);
		}
		else
		{
			$replacedQuery = str_replace($prefix, $this->tablePrefix, $query);
		}

		return $replacedQuery;
	}

	/**
	 * Method to release a savepoint.
	 *
	 * @param   string  $savepointName  Savepoint's name to release
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function releaseTransactionSavepoint( $savepointName )
	{
		$this->connect();
		$this->setQuery('RELEASE SAVEPOINT ' . $this->quoteName($this->escape($savepointName)));
		$this->execute();
	}

	/**
	 * Method to create a savepoint.
	 *
	 * @param   string  $savepointName  Savepoint's name to create
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function transactionSavepoint( $savepointName )
	{
		$this->connect();
		$this->setQuery('SAVEPOINT ' . $this->quoteName($this->escape($savepointName)));
		$this->execute();
	}

	/**
	 * Unlocks tables in the database, this command does not exist in PostgreSQL,
	 * it is automatically done on commit or rollback.
	 *
	 * @return  JDatabaseDriverPostgresql  Returns this object to support chaining.
	 *
	 * @since   11.4
	 * @throws  RuntimeException
	 */
	public function unlockTables()
	{
		$this->transactionCommit();
		return $this;
	}

	/**
	 * Updates a row in a table based on an object's properties.
	 *
	 * @param   string   $table    The name of the database table to update.
	 * @param   object   &$object  A reference to an object whose public properties match the table fields.
	 * @param   string   $key      The name of the primary key.
	 * @param   boolean  $nulls    True to update null fields or false to ignore them.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 * @throws  RuntimeException
	 */
	public function updateObject($table, &$object, $key, $nulls = false)
	{
		$columns = $this->getTableColumns($table);
		$fields = array();
		$where = '';

		// Create the base update statement.
		$query = $this->getQuery(true)
			->update($table);
		$stmt = '%s WHERE %s';

		// Iterate over the object variables to build the query fields/value pairs.
		foreach (get_object_vars($object) as $k => $v)
		{
			// Only process scalars that are not internal fields.
			if (is_array($v) or is_object($v) or $k[0] == '_')
			{
				continue;
			}

			// Set the primary key to the WHERE clause instead of a field to update.
			if ($k == $key)
			{
				$key_val = $this->sqlValue($columns, $k, $v);
				$where = $this->quoteName($k) . '=' . $key_val;
				continue;
			}

			// Prepare and sanitize the fields and values for the database query.
			if ($v === null)
			{
				// If the value is null and we want to update nulls then set it.
				if ($nulls)
				{
					$val = 'NULL';
				}
				// If the value is null and we do not want to update nulls then ignore this field.
				else
				{
					continue;
				}
			}
			// The field is not null so we prep it for update.
			else
			{
				$val = $this->sqlValue($columns, $k, $v);
			}

			// Add the field to be updated.
			$fields[] = $this->quoteName($k) . '=' . $val;
		}

		// We don't have any fields to update.
		if (empty($fields))
		{
			return true;
		}

		// Set the query and execute the update.
		$query->set(sprintf($stmt, implode(",", $fields), $where));
		$this->setQuery($query);

		return $this->execute();
	}
}
