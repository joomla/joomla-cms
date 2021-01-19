<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Driver;

defined('AKEEBAENGINE') || die();

use Akeeba\Engine\Driver\Query\Mysql as QueryMysql;
use Akeeba\Engine\Factory;
use Exception;
use RuntimeException;

/**
 * MySQL classic driver for Akeeba Engine
 *
 * Based on Joomla! Platform 11.2
 */
class Mysql extends Base
{

	/**
	 * The name of the database driver.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $name = 'mysql';

	/**
	 * Hostname
	 *
	 * @var   string
	 */
	protected $host;

	/**
	 * Username
	 *
	 * @var   string
	 */
	protected $user;

	/**
	 * Password
	 *
	 * @var   string
	 */
	protected $password;

	/**
	 * Should I select a database?
	 *
	 * @var   bool
	 */
	protected $selectDatabase;

	/**
	 * The character(s) used to quote SQL statement names such as table names or field names,
	 * etc. The child classes should define this as necessary.  If a single character string the
	 * same character is used for both sides of the quoted name, else the first character will be
	 * used for the opening quote and the second for the closing quote.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $nameQuote = '`';

	/**
	 * The null or zero representation of a timestamp for the database driver.  This should be
	 * defined in child classes to hold the appropriate value for the engine.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $nullDate = '0000-00-00 00:00:00';

	/**
	 * Database object constructor
	 *
	 * @param   array  $options  List of options used to configure the connection
	 */
	public function __construct($options)
	{
		$this->driverType = 'mysql';

		// Init
		$this->nameQuote = '`';

		$host     = array_key_exists('host', $options) ? $options['host'] : 'localhost';
		$port     = array_key_exists('port', $options) ? $options['port'] : '';
		$user     = array_key_exists('user', $options) ? $options['user'] : '';
		$password = array_key_exists('password', $options) ? $options['password'] : '';
		$database = array_key_exists('database', $options) ? $options['database'] : '';
		$prefix   = array_key_exists('prefix', $options) ? $options['prefix'] : '';
		$select   = array_key_exists('select', $options) ? $options['select'] : true;

		if (!empty($port))
		{
			$host .= ':' . $port;
		}

		// finalize initialization
		parent::__construct($options);

		// Avoid overwriting connection info if they're already set
		if (is_null($this->host))
		{
			$this->host = $host;
		}

		if (is_null($this->user))
		{
			$this->user = $user;
		}

		if (is_null($this->password))
		{
			$this->password = $password;
		}

		if (is_null($this->_database))
		{
			$this->_database = $database;
		}

		if (is_null($this->selectDatabase))
		{
			$this->selectDatabase = $select;
		}

		// Open the connection
		if (!is_resource($this->connection) || is_null($this->connection))
		{
			$this->open();
		}
	}

	/**
	 * Test to see if the MySQL connector is available.
	 *
	 * @return  boolean  True on success, false otherwise.
	 */
	public static function test()
	{
		return (function_exists('mysql_connect'));
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
		return (function_exists('mysql_connect'));
	}

	public function open()
	{
		if ($this->connected())
		{
			return;
		}
		else
		{
			$this->close();
		}

		// perform a number of fatality checks, then return gracefully
		if (!function_exists('mysql_connect'))
		{
			$this->errorNum = 1;
			$this->errorMsg = 'The MySQL adapter "mysql" is not available.';

			return;
		}

		if (!($this->connection = @mysql_connect($this->host, $this->user, $this->password, true)))
		{
			$this->errorNum = 2;
			$this->errorMsg = 'Could not connect to MySQL';

			return;
		}

		// Set sql_mode to non_strict mode
		mysql_query("SET @@SESSION.sql_mode = '';", $this->connection);

		// If auto-select is enabled select the given database.
		if ($this->selectDatabase && !empty($this->_database))
		{
			if (!$this->select($this->_database))
			{
				$this->errorNum = 3;
				$this->errorMsg = "Cannot select database {$this->_database}";

				return;
			}
		}

		$this->setUTF();
	}

	public function close()
	{
		$return = false;
		if (is_resource($this->cursor))
		{
			mysql_free_result($this->cursor);
		}
		if (is_resource($this->connection) || (!is_null($this->connection) && !is_bool($this->connection)))
		{
			$return = mysql_close($this->connection);
		}
		$this->connection = null;

		return $return;
	}

	/**
	 * Method to escape a string for usage in an SQL statement.
	 *
	 * @param   string   $text   The string to be escaped.
	 * @param   boolean  $extra  Optional parameter to provide extra escaping.
	 *
	 * @return  string  The escaped string.
	 */
	public function escape($text, $extra = false)
	{
		$result = @mysql_real_escape_string($text, $this->getConnection());

		if ($result === false)
		{
			// Attempt to reconnect.
			try
			{
				$this->connection = null;
				$this->open();

				$result = @mysql_real_escape_string($text, $this->getConnection());
			}
			catch (RuntimeException $e)
			{
				$result = $this->unsafe_escape($text);
			}
		}

		if ($extra)
		{
			$result = addcslashes($result, '%_');
		}

		return $result;
	}

	/**
	 * Determines if the connection to the server is active.
	 *
	 * @return  boolean  True if connected to the database engine.
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
	 * Drops a table from the database.
	 *
	 * @param   string   $tableName  The name of the database table to drop.
	 * @param   boolean  $ifExists   Optionally specify that the table must exist before it is dropped.
	 *
	 * @return  Mysql  Returns this object to support chaining.
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
	 */
	public function getAffectedRows()
	{
		return mysql_affected_rows($this->connection);
	}

	/**
	 * Method to get the database collation in use by sampling a text field of a table in the database.
	 *
	 * @return  mixed  The collation in use by the database (string) or boolean false if not supported.
	 */
	public function getCollation()
	{
		$this->setQuery('SHOW FULL COLUMNS FROM #__ak_stats');
		$array = $this->loadAssocList();

		return $array['2']['Collation'];
	}

	/**
	 * Get the number of returned rows for the previous executed SQL statement.
	 *
	 * @param   resource  $cursor  An optional database cursor resource to extract the row count from.
	 *
	 * @return  integer   The number of returned rows.
	 */
	public function getNumRows($cursor = null)
	{
		return mysql_num_rows($cursor ?: $this->cursor);
	}

	/**
	 * Get the current or query, or new JDatabaseQuery object.
	 *
	 * @param   boolean  $new  False to return the last query set, True to return a new JDatabaseQuery object.
	 *
	 * @return  mixed  The current value of the internal SQL variable or a new JDatabaseQuery object.
	 */
	public function getQuery($new = false)
	{
		if ($new)
		{
			return new QueryMysql($this);
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
	 */
	public function getTableCreate($tables)
	{
		// Initialise variables.
		$result = [];

		// Sanitize input to an array and iterate over the list.
		$tables = (array) $tables;
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
	 */
	public function getTableColumns($table, $typeOnly = true)
	{
		$result = [];

		// Set the query to get the table fields statement.
		$this->setQuery('SHOW FULL COLUMNS FROM ' . $this->quoteName($this->escape($table)));
		$fields = $this->loadObjectList();

		// If we only want the type as the value add just that to the list.
		if ($typeOnly)
		{
			foreach ($fields as $field)
			{
				$result[$field->Field] = preg_replace("/[(0-9)]/", '', $field->Type);
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
	 */
	public function getVersion()
	{
		return mysql_get_server_info($this->connection);
	}

	/**
	 * Determines if the database engine supports UTF-8 character encoding.
	 *
	 * @return  boolean  True if supported.
	 */
	public function hasUTF()
	{
		$verParts = explode('.', $this->getVersion());

		return ($verParts[0] == 5 || ($verParts[0] == 4 && $verParts[1] == 1 && (int) $verParts[2] >= 2));
	}

	/**
	 * Method to get the auto-incremented value from the last INSERT statement.
	 *
	 * @return  integer  The value of the auto-increment field from the last inserted row.
	 */
	public function insertid()
	{
		return mysql_insert_id($this->connection);
	}

	/**
	 * Locks a table in the database.
	 *
	 * @param   string  $table  The name of the table to unlock.
	 *
	 * @return  Mysql  Returns this object to support chaining.
	 */
	public function lockTable($table)
	{
		$this->setQuery('LOCK TABLES ' . $this->quoteName($table) . ' WRITE')->query();

		return $this;
	}

	/**
	 * Execute the SQL statement.
	 *
	 * @return  mixed  A database cursor resource on success, boolean false on failure.
	 */
	public function query()
	{
		static $isReconnecting = false;

		if (!is_resource($this->connection))
		{
			throw new RuntimeException($this->errorMsg, $this->errorNum);
		}

		// Take a local copy so that we don't modify the original query and cause issues later
		$query = $this->replacePrefix((string) $this->sql);
		if ($this->limit > 0 || $this->offset > 0)
		{
			$query .= ' LIMIT ' . $this->offset . ', ' . $this->limit;
		}

		// Increment the query counter.
		$this->count++;

		// If debugging is enabled then let's log the query.
		if ($this->debug)
		{
			// Add the query to the object queue.
			$this->log[] = $query;
		}

		// Reset the error values.
		$this->errorNum = 0;
		$this->errorMsg = '';

		// Execute the query. Error suppression is used here to prevent warnings/notices that the connection has been lost.
		$this->cursor = @mysql_query($query, $this->connection);

		// If an error occurred handle it.
		if (!$this->cursor)
		{
			// Check if the server was disconnected.
			if (!$this->connected() && !$isReconnecting)
			{
				$isReconnecting = true;

				try
				{
					// Attempt to reconnect.
					$this->connection = null;
					$this->open();
				}
					// If connect fails, ignore that exception and throw the normal exception.
				catch (RuntimeException $e)
				{
					// Get the error number and message.
					$this->errorNum = (int) mysql_errno($this->connection);
					$this->errorMsg = (string) mysql_error($this->connection) . ' SQL=' . $query;

					// Throw the normal query exception.
					throw new RuntimeException($this->errorMsg, $this->errorNum);
				}

				// Since we were able to reconnect, run the query again.
				$result         = $this->query();
				$isReconnecting = false;

				return $result;
			}
			// The server was not disconnected.
			else
			{
				// Get the error number and message.
				$this->errorNum = (int) mysql_errno($this->connection);
				$this->errorMsg = (string) mysql_error($this->connection) . ' SQL=' . $query;

				// Throw the normal query exception.
				if ($this->errorNum != 0)
				{
					throw new RuntimeException($this->errorMsg, $this->errorNum);
				}
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
	 * @return  Mysql  Returns this object to support chaining.
	 */
	public function renameTable($oldTable, $newTable, $backup = null, $prefix = null)
	{
		$this->setQuery('RENAME TABLE ' . $oldTable . ' TO ' . $newTable)->query();

		return $this;
	}

	/**
	 * Select a database for use.
	 *
	 * @param   string  $database  The name of the database to select for use.
	 *
	 * @return  boolean  True if the database was successfully selected.
	 */
	public function select($database)
	{
		if (!$database)
		{
			return false;
		}

		if (!mysql_select_db($database, $this->connection))
		{
			throw new RuntimeException('Could not connect to database');
		}

		return true;
	}

	/**
	 * Set the connection to use UTF-8 character encoding.
	 *
	 * @return  boolean  True on success.
	 */
	public function setUTF()
	{
		$result = false;

		if ($this->supportsUtf8mb4())
		{
			$result = @mysql_set_charset('utf8mb4', $this->connection);
		}

		if (!$result)
		{
			$result = @mysql_set_charset('utf8', $this->connection);
		}

		return $result;
	}

	/**
	 * Method to commit a transaction.
	 *
	 * @return  void
	 */
	public function transactionCommit()
	{
		$this->setQuery('COMMIT');
		$this->execute();
	}

	/**
	 * Method to roll back a transaction.
	 *
	 * @return  void
	 */
	public function transactionRollback()
	{
		$this->setQuery('ROLLBACK');
		$this->execute();
	}

	/**
	 * Method to initialize a transaction.
	 *
	 * @return  void
	 */
	public function transactionStart()
	{
		$this->setQuery('START TRANSACTION');
		$this->execute();
	}

	/**
	 * Method to fetch a row from the result set cursor as an associative array.
	 *
	 * @param   mixed  $cursor  The optional result set cursor from which to fetch the row.
	 *
	 * @return  mixed  Either the next row from the result set or false if there are no more rows.
	 */
	public function fetchAssoc($cursor = null)
	{
		return mysql_fetch_assoc($cursor ?: $this->cursor);
	}

	/**
	 * Method to free up the memory used for the result set.
	 *
	 * @param   mixed  $cursor  The optional result set cursor from which to fetch the row.
	 *
	 * @return  void
	 */
	public function freeResult($cursor = null)
	{
		mysql_free_result($cursor ?: $this->cursor);
	}

	/**
	 * Unlocks tables in the database.
	 *
	 * @return  Mysql  Returns this object to support chaining.
	 *
	 * @throws  Exception
	 * @since   11.4
	 */
	public function unlockTables()
	{
		$this->setQuery('UNLOCK TABLES')->execute();

		return $this;
	}

	/**
	 * Returns an array with the names of tables, views, procedures, functions and triggers
	 * in the database. The table names are the keys of the tables, whereas the value is
	 * the type of each element: table, view, merge, temp, procedure, function or trigger.
	 * Note that merge are MRG_MYISAM tables and temp is non-permanent data table, usually
	 * set up as temporary, black hole or federated tables. These two types should never,
	 * ever, have their data dumped in the SQL dump file.
	 *
	 * @param   bool  $abstract  Return abstract or normal names? Defaults to true (abstract names)
	 *
	 * @return array
	 */
	public function getTables($abstract = true)
	{
		static $tables = [];

		if (!empty($tables))
		{
			return $tables;
		}

		$sql = "SHOW TABLES";
		$this->setQuery($sql);
		$all_tables = $this->loadColumn();

		if (!empty($all_tables))
		{
			// Start by adding tables and views to the list
			foreach ($all_tables as $table_name)
			{
				if ($abstract)
				{
					$table_name = $this->getAbstract($table_name);
				}
				$tables[$table_name] = 'table';
			}

			// Loop all metadatas
			foreach ($all_tables as $table_metadata)
			{
				$table_name     = $table_metadata;
				$table_abstract = $this->getAbstract($table_metadata);
				$type           = 'table';

				if ($abstract)
				{
					$table_metadata = $table_abstract;
				}

				$create = $this->get_create($table_abstract, $table_name, $type);
				// Scan for the table engine.
				$engine = null; // So that we detect VIEWs correctly

				if ($type == 'table')
				{
					$engine      = 'MyISAM'; // So that even with MySQL 4 hosts we don't screw this up
					$engine_keys = ['ENGINE=', 'TYPE='];
					foreach ($engine_keys as $engine_key)
					{
						$start_pos = strrpos($create, $engine_key);
						if ($start_pos !== false)
						{
							// Advance the start position just after the position of the ENGINE keyword
							$start_pos += strlen($engine_key);
							// Try to locate the space after the engine type
							$end_pos = stripos($create, ' ', $start_pos);
							if ($end_pos === false)
							{
								// Uh... maybe it ends with ENGINE=EngineType;
								$end_pos = stripos($create, ';');
							}
							if ($end_pos !== '')
							{
								// Grab the string
								$engine = substr($create, $start_pos, $end_pos - $start_pos);
							}
						}
					}
					$engine = strtoupper($engine);
				}

				switch ($engine)
				{
					// Views -- FIX: They are detected based on their CREATE STATEMENT
					case null:
						$tables[$table_metadata] = 'view';
						break;

					// Merge tables
					case 'MRG_MYISAM':
						$tables[$table_metadata] = 'merge';
						break;

					// Tables whose data we do not back up (memory, federated and can-have-no-data tables)
					case 'MEMORY':
					case 'EXAMPLE':
					case 'BLACKHOLE':
					case 'FEDERATED':
						$tables[$table_metadata] = 'temp';
						break;

					// Normal tables
					default:
						break;
				} // switch
			} // foreach
		} // if !empty

		// If we have MySQL > 5.0 add the list of stored procedures, stored functions
		// and triggers
		$registry        = Factory::getConfiguration();
		$enable_entities = $registry->get('engine.dump.native.advanced_entitites', true);
		if ($enable_entities)
		{
			// 1. Stored procedures
			$sql = "SHOW PROCEDURE STATUS WHERE " . $this->quoteName('Db') . "=" . $this->quote($this->_database);
			$this->setQuery($sql);

			try
			{
				$all_entries = $this->loadAssocList();
			}
			catch (Exception $e)
			{
				$all_entries = [];
			}

			if (is_array($all_entries) || $all_entries instanceof \Countable ? count($all_entries) : 0)
			{
				foreach ($all_entries as $entry)
				{
					$table_name = $entry['Name'];
					if ($abstract)
					{
						$table_name = $this->getAbstract($table_name);
					}
					$tables[$table_name] = 'procedure';
				}
			}

			// 2. Stored functions
			$sql = "SHOW FUNCTION STATUS WHERE " . $this->quoteName('Db') . "=" . $this->quote($this->_database);
			$this->setQuery($sql);

			try
			{
				$all_entries = $this->loadColumn(1);
			}
			catch (Exception $e)
			{
				$all_entries = [];
			}

			// If we have filters, make sure the tables pass the filtering
			if (is_array($all_entries))
			{
				if (count($all_entries))
				{
					foreach ($all_entries as $table_name)
					{
						if ($abstract)
						{
							$table_name = $this->getAbstract($table_name);
						}
						$tables[$table_name] = 'function';
					}
				}
			}

			// 3. Triggers
			$sql = "SHOW TRIGGERS";
			$this->setQuery($sql);

			try
			{
				$all_entries = $this->loadColumn();
			}
			catch (Exception $e)
			{
				$all_entries = [];
			}

			// If we have filters, make sure the tables pass the filtering
			if (is_array($all_entries))
			{
				if (count($all_entries))
				{
					foreach ($all_entries as $table_name)
					{
						if ($abstract)
						{
							$table_name = $this->getAbstract($table_name);
						}
						$tables[$table_name] = 'trigger';
					}
				}
			}

		}

		return $tables;
	}

	/**
	 * Method to fetch a row from the result set cursor as an array.
	 *
	 * @param   mixed  $cursor  The optional result set cursor from which to fetch the row.
	 *
	 * @return  mixed  Either the next row from the result set or false if there are no more rows.
	 */
	protected function fetchArray($cursor = null)
	{
		return mysql_fetch_row($cursor ?: $this->cursor);
	}

	/**
	 * Method to fetch a row from the result set cursor as an object.
	 *
	 * @param   mixed   $cursor  The optional result set cursor from which to fetch the row.
	 * @param   string  $class   The class name to use for the returned row object.
	 *
	 * @return  mixed   Either the next row from the result set or false if there are no more rows.
	 */
	protected function fetchObject($cursor = null, $class = 'stdClass')
	{
		return mysql_fetch_object($cursor ?: $this->cursor, $class);
	}

	/**
	 * Gets the CREATE TABLE command for a given table/view
	 *
	 * @param   string  $table_abstract  The abstracted name of the entity
	 * @param   string  $table_name      The name of the table
	 * @param   string  $type            The type of the entity to scan. If it's found to differ, the correct type is returned.
	 *
	 * @return string The CREATE command, w/out newlines
	 */
	protected function get_create($table_abstract, $table_name, &$type)
	{
		$sql = "SHOW CREATE TABLE `$table_abstract`";
		$this->setQuery($sql);
		$temp      = $this->loadRowList();
		$table_sql = $temp[0][1];
		unset($temp);

		// Smart table type detection
		if (in_array($type, ['table', 'merge', 'view']))
		{
			// Check for CREATE VIEW
			$pattern = '/^CREATE(.*) VIEW (.*)/i';
			$result  = preg_match($pattern, $table_sql);
			if ($result === 1)
			{
				// This is a view.
				$type = 'view';
			}
			else
			{
				// This is a table.
				$type = 'table';
			}

			// Is it a VIEW but we don't have SHOW VIEW privileges?
			if (empty($table_sql))
			{
				$type = 'view';
			}
		}

		$table_sql = str_replace($table_name, $table_abstract, $table_sql);

		// Replace newlines with spaces
		$table_sql = str_replace("\n", " ", $table_sql) . ";\n";
		$table_sql = str_replace("\r", " ", $table_sql);
		$table_sql = str_replace("\t", " ", $table_sql);

		// Post-process CREATE VIEW
		if ($type == 'view')
		{
			$pos_view = strpos($table_sql, ' VIEW ');

			if ($pos_view > 7)
			{
				// Only post process if there are view properties between the CREATE and VIEW keywords
				$propstring = substr($table_sql, 7, $pos_view - 7); // Properties string
				// Fetch the ALGORITHM={UNDEFINED | MERGE | TEMPTABLE} keyword
				$algostring = '';
				$algo_start = strpos($propstring, 'ALGORITHM=');
				if ($algo_start !== false)
				{
					$algo_end   = strpos($propstring, ' ', $algo_start);
					$algostring = substr($propstring, $algo_start, $algo_end - $algo_start + 1);
				}
				// Create our modified create statement
				$table_sql = 'CREATE OR REPLACE ' . $algostring . substr($table_sql, $pos_view);
			}
		}

		return $table_sql;
	}

	/**
	 * Does this database server support UTF-8 four byte (utf8mb4) collation?
	 *
	 * libmysql supports utf8mb4 since 5.5.3 (same version as the MySQL server). mysqlnd supports utf8mb4 since 5.0.9.
	 *
	 * This method's code is based on WordPress' wpdb::has_cap() method
	 *
	 * @return  bool
	 */
	protected function supportsUtf8mb4()
	{
		$client_version = mysql_get_client_info();

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

	protected function unsafe_escape($string)
	{
		if (function_exists('mb_ereg_replace')) {
			return mb_ereg_replace('[\x00\x0A\x0D\x1A\x22\x27\x5C]', '\\\0', $string);
		}

		return preg_replace('~[\x00\x0A\x0D\x1A\x22\x27\x5C]~u', '\\\$0', $string);
	}
}
