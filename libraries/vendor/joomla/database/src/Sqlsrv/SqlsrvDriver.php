<?php
/**
 * Part of the Joomla Framework Database Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Database\Sqlsrv;

use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseEvents;
use Joomla\Database\Event\ConnectionEvent;
use Joomla\Database\Exception\ConnectionFailureException;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Database\Exception\PrepareStatementFailureException;
use Joomla\Database\Exception\UnsupportedAdapterException;
use Joomla\Database\StatementInterface;

/**
 * SQL Server Database Driver
 *
 * @link   https://secure.php.net/manual/en/book.sqlsrv.php
 * @since  1.0
 */
class SqlsrvDriver extends DatabaseDriver
{
	/**
	 * The name of the database driver.
	 *
	 * @var    string
	 * @since  1.0
	 */
	public $name = 'sqlsrv';

	/**
	 * The character(s) used to quote SQL statement names such as table names or field names, etc.
	 *
	 * If a single character string the same character is used for both sides of the quoted name, else the first character will be used for the
	 * opening quote and the second for the closing quote.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $nameQuote;

	/**
	 * The null or zero representation of a timestamp for the database driver.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $nullDate = '1900-01-01 00:00:00';

	/**
	 * The minimum supported database version.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected static $dbMinimum = '11.0.2100.60';

	/**
	 * Test to see if the SQLSRV connector is available.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   1.0
	 */
	public static function isSupported()
	{
		return function_exists('sqlsrv_connect');
	}

	/**
	 * Constructor.
	 *
	 * @param   array  $options  List of options used to configure the connection
	 *
	 * @since   1.0
	 */
	public function __construct(array $options)
	{
		// Get some basic values from the options.
		$options['host']     = isset($options['host']) ? $options['host'] : 'localhost';
		$options['user']     = isset($options['user']) ? $options['user'] : '';
		$options['password'] = isset($options['password']) ? $options['password'] : '';
		$options['database'] = isset($options['database']) ? $options['database'] : '';
		$options['select']   = isset($options['select']) ? (bool) $options['select'] : true;

		// Finalize initialisation
		parent::__construct($options);
	}

	/**
	 * Destructor.
	 *
	 * @since   1.0
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
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function connect()
	{
		if ($this->connection)
		{
			return;
		}

		// Make sure the SQLSRV extension for PHP is installed and enabled.
		if (!static::isSupported())
		{
			throw new UnsupportedAdapterException('PHP extension sqlsrv_connect is not available.');
		}

		// Build the connection configuration array.
		$config = [
			'Database'             => $this->options['database'],
			'uid'                  => $this->options['user'],
			'pwd'                  => $this->options['password'],
			'CharacterSet'         => 'UTF-8',
			'ReturnDatesAsStrings' => true
		];

		// Attempt to connect to the server.
		if (!($this->connection = @ sqlsrv_connect($this->options['host'], $config)))
		{
			throw new ConnectionFailureException('Could not connect to SQL Server');
		}

		// Make sure that DB warnings are not returned as errors.
		sqlsrv_configure('WarningsReturnAsErrors', 0);

		// If auto-select is enabled select the given database.
		if ($this->options['select'] && !empty($this->options['database']))
		{
			$this->select($this->options['database']);
		}

		$this->dispatchEvent(new ConnectionEvent(DatabaseEvents::POST_CONNECT, $this));
	}

	/**
	 * Disconnects the database.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function disconnect()
	{
		// Close the connection.
		if (is_resource($this->connection))
		{
			sqlsrv_close($this->connection);
		}

		parent::disconnect();
	}

	/**
	 * Get table constraints
	 *
	 * @param   string  $tableName  The name of the database table.
	 *
	 * @return  array  Any constraints available for the table.
	 *
	 * @since   1.0
	 */
	protected function getTableConstraints($tableName)
	{
		$this->connect();

		return $this->setQuery('SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE TABLE_NAME = ' . $this->quote($tableName))
			->loadColumn();
	}

	/**
	 * Rename constraints.
	 *
	 * @param   array   $constraints  Array(strings) of table constraints
	 * @param   string  $prefix       A string
	 * @param   string  $backup       A string
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function renameConstraints($constraints = array(), $prefix = null, $backup = null)
	{
		$this->connect();

		foreach ($constraints as $constraint)
		{
			$this->setQuery('sp_rename ' . $constraint . ',' . str_replace($prefix, $backup, $constraint))
				->execute();
		}
	}

	/**
	 * Method to escape a string for usage in an SQL statement.
	 *
	 * The escaping for MSSQL isn't handled in the driver though that would be nice.  Because of this we need to handle the escaping ourselves.
	 *
	 * @param   string   $text   The string to be escaped.
	 * @param   boolean  $extra  Optional parameter to provide extra escaping.
	 *
	 * @return  string  The escaped string.
	 *
	 * @since   1.0
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

		$result = str_replace("'", "''", $text);

		// SQL Server does not accept NULL byte in query string
		$result = str_replace("\0", "' + CHAR(0) + N'", $result);

		// Fix for SQL Sever escape sequence, see https://support.microsoft.com/en-us/kb/164291
		$result = str_replace(
			array("\\\n",     "\\\r",     "\\\\\r\r\n"),
			array("\\\\\n\n", "\\\\\r\r", "\\\\\r\n\r\n"),
			$result
		);

		if ($extra)
		{
			// Escape special chars
			$result = str_replace(
				array('[',   '_',   '%'),
				array('[[]', '[_]', '[%]'),
				$result
			);
		}

		return $result;
	}

	/**
	 * Quotes and optionally escapes a string to database requirements for use in database queries.
	 *
	 * @param   mixed    $text    A string or an array of strings to quote.
	 * @param   boolean  $escape  True (default) to escape the string, false to leave it unchanged.
	 *
	 * @return  string  The quoted input string.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function quote($text, $escape = true)
	{
		if (is_array($text))
		{
			return parent::quote($text, $escape);
		}

		// To support unicode on MSSQL we have to add prefix N
		return 'N\'' . ($escape ? $this->escape($text) : $text) . '\'';
	}

	/**
	 * Determines if the connection to the server is active.
	 *
	 * @return  boolean  True if connected to the database engine.
	 *
	 * @since   1.0
	 */
	public function connected()
	{
		// TODO: Run a blank query here
		return true;
	}

	/**
	 * Drops a table from the database.
	 *
	 * @param   string   $tableName  The name of the database table to drop.
	 * @param   boolean  $ifExists   Optionally specify that the table must exist before it is dropped.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function dropTable($tableName, $ifExists = true)
	{
		$this->connect();

		if ($ifExists)
		{
			$this->setQuery(
				'IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = '
					. $this->quote($tableName) . ') DROP TABLE ' . $tableName
			);
		}
		else
		{
			$this->setQuery('DROP TABLE ' . $tableName);
		}

		$this->execute();

		return $this;
	}

	/**
	 * Method to get the database collation in use by sampling a text field of a table in the database.
	 *
	 * @return  mixed  The collation in use by the database or boolean false if not supported.
	 *
	 * @since   1.0
	 */
	public function getCollation()
	{
		// TODO: Not fake this
		return 'MSSQL UTF-8 (UCS2)';
	}

	/**
	 * Method to get the database connection collation in use by sampling a text field of a table in the database.
	 *
	 * @return  mixed  The collation in use by the database connection (string) or boolean false if not supported.
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \RuntimeException
	 */
	public function getConnectionCollation()
	{
		// TODO: Not fake this
		return 'MSSQL UTF-8 (UCS2)';
	}

	/**
	 * Retrieves field information about the given tables.
	 *
	 * @param   mixed    $table     A table name
	 * @param   boolean  $typeOnly  True to only return field types.
	 *
	 * @return  array  An array of fields.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function getTableColumns($table, $typeOnly = true)
	{
		$result = [];

		$table_temp = $this->replacePrefix((string) $table);

		// Set the query to get the table fields statement.
		$this->setQuery(
			'SELECT column_name as Field, data_type as Type, is_nullable as \'Null\', column_default as \'Default\'' .
			' FROM information_schema.columns WHERE table_name = ' . $this->quote($table_temp)
		);
		$fields = $this->loadObjectList();

		// If we only want the type as the value add just that to the list.
		if ($typeOnly)
		{
			foreach ($fields as $field)
			{
				$result[$field->Field] = preg_replace('/[(0-9)]/', '', $field->Type);
			}
		}
		else
		// If we want the whole field data object add that to the list.
		{
			foreach ($fields as $field)
			{
				$field->Default = preg_replace("/(^(\(\(|\('|\(N'|\()|(('\)|(?<!\()\)\)|\))$))/i", '', $field->Default);
				$result[$field->Field] = $field;
			}
		}

		return $result;
	}

	/**
	 * Shows the table CREATE statement that creates the given tables.
	 *
	 * This is unsupported by MSSQL.
	 *
	 * @param   mixed  $tables  A table name or a list of table names.
	 *
	 * @return  array  A list of the create SQL for the tables.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function getTableCreate($tables)
	{
		$this->connect();

		return '';
	}

	/**
	 * Get the details list of keys for a table.
	 *
	 * @param   string  $table  The name of the table.
	 *
	 * @return  array  An array of the column specification for the table.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function getTableKeys($table)
	{
		$this->connect();

		// TODO To implement.
		return [];
	}

	/**
	 * Method to get an array of all tables in the database.
	 *
	 * @return  array  An array of all the tables in the database.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function getTableList()
	{
		$this->connect();

		// Set the query to get the tables statement.
		return $this->setQuery('SELECT name FROM ' . $this->getDatabase() . '.sys.Tables WHERE type = \'U\';')->loadColumn();
	}

	/**
	 * Get the version of the database connector.
	 *
	 * @return  string  The database connector version.
	 *
	 * @since   1.0
	 */
	public function getVersion()
	{
		$this->connect();

		$version = sqlsrv_server_info($this->connection);

		return $version['SQLServerVersion'];
	}

	/**
	 * Inserts a row into a table based on an object's properties.
	 *
	 * @param   string  $table   The name of the database table to insert into.
	 * @param   object  $object  A reference to an object whose public properties match the table fields.
	 * @param   string  $key     The name of the primary key. If provided the object property is updated.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function insertObject($table, &$object, $key = null)
	{
		$fields       = [];
		$values       = [];
		$tableColumns = $this->getTableColumns($table);
		$statement    = 'INSERT INTO ' . $this->quoteName($table) . ' (%s) VALUES (%s)';

		foreach (get_object_vars($object) as $k => $v)
		{
			// Skip columns that don't exist in the table.
			if (!array_key_exists($k, $tableColumns))
			{
				continue;
			}

			// Only process non-null scalars.
			if (is_array($v) || is_object($v) || $v === null)
			{
				continue;
			}

			if (!$this->checkFieldExists($table, $k))
			{
				continue;
			}

			if ($k[0] === '_')
			{
				// Internal field
				continue;
			}

			if ($k === $key && $key == 0)
			{
				continue;
			}

			$fields[] = $this->quoteName($k);
			$values[] = $this->quote($v);
		}

		// Set the query and execute the insert.
		$this->setQuery(sprintf($statement, implode(',', $fields), implode(',', $values)))->execute();

		$id = $this->insertid();

		if ($key && $id)
		{
			$object->$key = $id;
		}

		return true;
	}

	/**
	 * Method to get the auto-incremented value from the last INSERT statement.
	 *
	 * @return  integer  The value of the auto-increment field from the last inserted row.
	 *
	 * @since   1.0
	 */
	public function insertid()
	{
		$this->connect();

		// TODO: SELECT IDENTITY
		$this->setQuery('SELECT @@IDENTITY');

		return (int) $this->loadResult();
	}

	/**
	 * Execute the SQL statement.
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function execute()
	{
		$this->connect();

		// Take a local copy so that we don't modify the original query and cause issues later
		$sql = $this->replacePrefix((string) $this->sql);

		// Increment the query counter.
		$this->count++;

		// If there is a monitor registered, let it know we are starting this query
		if ($this->monitor)
		{
			$this->monitor->startQuery($sql);
		}

		// Execute the query.
		$this->executed = false;

		// Bind the variables
		$bounded =& $this->sql->getBounded();

		foreach ($bounded as $key => $obj)
		{
			$this->statement->bindParam($key, $obj->value, $obj->dataType);
		}

		try
		{
			$this->executed = $this->statement->execute();

			// If there is a monitor registered, let it know we have finished this query
			if ($this->monitor)
			{
				$this->monitor->stopQuery();
			}

			return true;
		}
		catch (ExecutionFailureException $exception)
		{
			// If there is a monitor registered, let it know we have finished this query
			if ($this->monitor)
			{
				$this->monitor->stopQuery();
			}

			// Check if the server was disconnected.
			if (!$this->connected())
			{
				try
				{
					// Attempt to reconnect.
					$this->connection = null;
					$this->connect();
				}
				catch (ConnectionFailureException $e)
				{
					// If connect fails, ignore that exception and throw the normal exception.
					throw $exception;
				}

				// Since we were able to reconnect, run the query again.
				return $this->execute();
			}

			// Throw the normal query exception.
			throw $exception;
		}
	}

	/**
	 * This function replaces a string identifier <var>$prefix</var> with the string held is the <var>tablePrefix</var> class variable.
	 *
	 * @param   string  $sql     The SQL statement to prepare.
	 * @param   string  $prefix  The common table prefix.
	 *
	 * @return  string  The processed SQL statement.
	 *
	 * @since   1.0
	 */
	public function replacePrefix($sql, $prefix = '#__')
	{
		$escaped = false;
		$startPos = 0;
		$quoteChar = '';
		$literal = '';

		$sql = trim($sql);
		$n = strlen($sql);

		while ($startPos < $n)
		{
			$ip = strpos($sql, $prefix, $startPos);

			if ($ip === false)
			{
				break;
			}

			$j = strpos($sql, "N'", $startPos);
			$k = strpos($sql, '"', $startPos);

			if (($k !== false) && (($k < $j) || ($j === false)))
			{
				$quoteChar = '"';
				$j = $k;
			}
			else
			{
				$quoteChar = "'";
			}

			if ($j === false)
			{
				$j = $n;
			}

			$literal .= str_replace($prefix, $this->tablePrefix, substr($sql, $startPos, $j - $startPos));
			$startPos = $j;

			$j = $startPos + 1;

			if ($j >= $n)
			{
				break;
			}

			// Quote comes first, find end of quote
			while (true)
			{
				$k = strpos($sql, $quoteChar, $j);
				$escaped = false;

				if ($k === false)
				{
					break;
				}

				$l = $k - 1;

				while ($l >= 0 && $sql{$l} === '\\')
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

			$literal .= substr($sql, $startPos, $k - $startPos + 1);
			$startPos = $k + 1;
		}

		if ($startPos < $n)
		{
			$literal .= substr($sql, $startPos, $n - $startPos);
		}

		return $literal;
	}

	/**
	 * Select a database for use.
	 *
	 * @param   string  $database  The name of the database to select for use.
	 *
	 * @return  boolean  True if the database was successfully selected.
	 *
	 * @since   1.0
	 * @throws  ConnectionFailureException
	 */
	public function select($database)
	{
		$this->connect();

		if (!$database)
		{
			return false;
		}

		if (!sqlsrv_query($this->connection, 'USE ' . $database, null, ['scrollable' => SQLSRV_CURSOR_STATIC]))
		{
			throw new ConnectionFailureException('Could not connect to database');
		}

		return true;
	}

	/**
	 * Set the connection to use UTF-8 character encoding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.0
	 */
	public function setUtf()
	{
		return true;
	}

	/**
	 * Method to commit a transaction.
	 *
	 * @param   boolean  $toSavepoint  If true, commit to the last savepoint.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function transactionCommit($toSavepoint = false)
	{
		$this->connect();

		if (!$toSavepoint || $this->transactionDepth <= 1)
		{
			$this->setQuery('COMMIT TRANSACTION')->execute();

			$this->transactionDepth = 0;

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
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function transactionRollback($toSavepoint = false)
	{
		$this->connect();

		if (!$toSavepoint || $this->transactionDepth <= 1)
		{
			$this->setQuery('ROLLBACK TRANSACTION')->execute();

			$this->transactionDepth = 0;

			return;
		}

		$savepoint = 'SP_' . ($this->transactionDepth - 1);
		$this->setQuery('ROLLBACK TRANSACTION ' . $this->quoteName($savepoint))->execute();

		$this->transactionDepth--;
	}

	/**
	 * Method to initialize a transaction.
	 *
	 * @param   boolean  $asSavepoint  If true and a transaction is already active, a savepoint will be created.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function transactionStart($asSavepoint = false)
	{
		$this->connect();

		if (!$asSavepoint || !$this->transactionDepth)
		{
			$this->setQuery('BEGIN TRANSACTION')->execute();

			$this->transactionDepth = 1;

			return;
		}

		$savepoint = 'SP_' . $this->transactionDepth;
		$this->setQuery('BEGIN TRANSACTION ' . $this->quoteName($savepoint))->execute();

		$this->transactionDepth++;
	}

	/**
	 * Method to check and see if a field exists in a table.
	 *
	 * @param   string  $table  The table in which to verify the field.
	 * @param   string  $field  The field to verify.
	 *
	 * @return  boolean  True if the field exists in the table.
	 *
	 * @since   1.0
	 */
	protected function checkFieldExists($table, $field)
	{
		$this->connect();

		$table = $this->replacePrefix((string) $table);
		$sql = 'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS' . " WHERE TABLE_NAME = '$table' AND COLUMN_NAME = '$field'" .
			' ORDER BY ORDINAL_POSITION';
		$this->setQuery($sql);

		return (bool) $this->loadResult();
	}

	/**
	 * Prepares a SQL statement for execution
	 *
	 * @param   string  $query  The SQL query to be prepared.
	 *
	 * @return  StatementInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  PrepareStatementFailureException
	 */
	protected function prepareStatement(string $query): StatementInterface
	{
		return new SqlsrvStatement($this->connection, $query);
	}

	/**
	 * Renames a table in the database.
	 *
	 * @param   string  $oldTable  The name of the table to be renamed
	 * @param   string  $newTable  The new name for the table.
	 * @param   string  $backup    Table prefix
	 * @param   string  $prefix    For the table - used to rename constraints in non-mysql databases
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function renameTable($oldTable, $newTable, $backup = null, $prefix = null)
	{
		$constraints = [];

		if (!is_null($prefix) && !is_null($backup))
		{
			$constraints = $this->getTableConstraints($oldTable);
		}

		if (!empty($constraints))
		{
			$this->renameConstraints($constraints, $prefix, $backup);
		}

		$this->setQuery("sp_rename '" . $oldTable . "', '" . $newTable . "'");

		return $this->execute();
	}

	/**
	 * Locks a table in the database.
	 *
	 * @param   string  $tableName  The name of the table to lock.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function lockTable($tableName)
	{
		return $this;
	}

	/**
	 * Unlocks tables in the database.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function unlockTables()
	{
		return $this;
	}
}
