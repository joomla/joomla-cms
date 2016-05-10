<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * SQLite database driver
 *
 * @see    http://php.net/pdo
 * @since  12.1
 */
class JDatabaseDriverSqlite extends JDatabaseDriverPdo
{
	/**
	 * The name of the database driver.
	 *
	 * @var    string
	 * @since  12.1
	 */
	public $name = 'sqlite';

	/**
	 * The type of the database server family supported by this driver.
	 *
	 * @var    string
	 * @since  CMS 3.5.0
	 */
	public $serverType = 'sqlite';

	/**
	 * The character(s) used to quote SQL statement names such as table names or field names,
	 * etc. The child classes should define this as necessary.  If a single character string the
	 * same character is used for both sides of the quoted name, else the first character will be
	 * used for the opening quote and the second for the closing quote.
	 *
	 * @var    string
	 * @since  12.1
	 */
	protected $nameQuote = '`';

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
	 * Disconnects the database.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function disconnect()
	{
		$this->freeResult();
		unset($this->connection);
	}

	/**
	 * Drops a table from the database.
	 *
	 * @param   string   $tableName  The name of the database table to drop.
	 * @param   boolean  $ifExists   Optionally specify that the table must exist before it is dropped.
	 *
	 * @return  JDatabaseDriverSqlite  Returns this object to support chaining.
	 *
	 * @since   12.1
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
	 * Method to escape a string for usage in an SQLite statement.
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

		return SQLite3::escapeString($text);
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
	 * Shows the table CREATE statement that creates the given tables.
	 *
	 * Note: Doesn't appear to have support in SQLite
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
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function getTableColumns($table, $typeOnly = true)
	{
		$this->connect();

		$columns = array();
		$query = $this->getQuery(true);

		$fieldCasing = $this->getOption(PDO::ATTR_CASE);

		$this->setOption(PDO::ATTR_CASE, PDO::CASE_UPPER);

		$table = strtoupper($table);

		$query->setQuery('pragma table_info(' . $table . ')');

		$this->setQuery($query);
		$fields = $this->loadObjectList();

		if ($typeOnly)
		{
			foreach ($fields as $field)
			{
				$columns[$field->NAME] = $field->TYPE;
			}
		}
		else
		{
			foreach ($fields as $field)
			{
				// Do some dirty translation to MySQL output.
				// TODO: Come up with and implement a standard across databases.
				$columns[$field->NAME] = (object) array(
					'Field' => $field->NAME,
					'Type' => $field->TYPE,
					'Null' => ($field->NOTNULL == '1' ? 'NO' : 'YES'),
					'Default' => $field->DFLT_VALUE,
					'Key' => ($field->PK != '0' ? 'PRI' : '')
				);
			}
		}

		$this->setOption(PDO::ATTR_CASE, $fieldCasing);

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

		$keys = array();
		$query = $this->getQuery(true);

		$fieldCasing = $this->getOption(PDO::ATTR_CASE);

		$this->setOption(PDO::ATTR_CASE, PDO::CASE_UPPER);

		$table = strtoupper($table);
		$query->setQuery('pragma table_info( ' . $table . ')');

		// $query->bind(':tableName', $table);

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
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function getTableList()
	{
		$this->connect();

		$type = 'table';

		$query = $this->getQuery(true)
			->select('name')
			->from('sqlite_master')
			->where('type = :type')
			->bind(':type', $type)
			->order('name');

		$this->setQuery($query);

		$tables = $this->loadColumn();

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
	 * @since   12.1
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
	 * @since   12.1
	 */
	public function setUtf()
	{
		$this->connect();

		return false;
	}

	/**
	 * Locks a table in the database.
	 *
	 * @param   string  $table  The name of the table to unlock.
	 *
	 * @return  JDatabaseDriverSqlite  Returns this object to support chaining.
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function lockTable($table)
	{
		return $this;
	}

	/**
	 * Renames a table in the database.
	 *
	 * @param   string  $oldTable  The name of the table to be renamed
	 * @param   string  $newTable  The new name for the table.
	 * @param   string  $backup    Not used by Sqlite.
	 * @param   string  $prefix    Not used by Sqlite.
	 *
	 * @return  JDatabaseDriverSqlite  Returns this object to support chaining.
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function renameTable($oldTable, $newTable, $backup = null, $prefix = null)
	{
		$this->setQuery('ALTER TABLE ' . $oldTable . ' RENAME TO ' . $newTable)->execute();

		return $this;
	}

	/**
	 * Unlocks tables in the database.
	 *
	 * @return  JDatabaseDriverSqlite  Returns this object to support chaining.
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function unlockTables()
	{
		return $this;
	}

	/**
	 * Test to see if the PDO ODBC connector is available.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   12.1
	 */
	public static function isSupported()
	{
		return class_exists('PDO') && in_array('sqlite', PDO::getAvailableDrivers());
	}

	/**
	 * Method to commit a transaction.
	 *
	 * @param   boolean  $toSavepoint  If true, commit to the last savepoint.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function transactionCommit($toSavepoint = false)
	{
		$this->connect();

		if (!$toSavepoint || $this->transactionDepth <= 1)
		{
			parent::transactionCommit($toSavepoint);
		}
		else
		{
			$this->transactionDepth--;
		}
	}

	/**
	 * Method to roll back a transaction.
	 *
	 * @param   boolean  $toSavepoint  If true, rollback to the last savepoint.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function transactionRollback($toSavepoint = false)
	{
		$this->connect();

		if (!$toSavepoint || $this->transactionDepth <= 1)
		{
			parent::transactionRollback($toSavepoint);
		}
		else
		{
			$savepoint = 'SP_' . ($this->transactionDepth - 1);
			$this->setQuery('ROLLBACK TO ' . $this->quoteName($savepoint));

			if ($this->execute())
			{
				$this->transactionDepth--;
			}
		}
	}

	/**
	 * Method to initialize a transaction.
	 *
	 * @param   boolean  $asSavepoint  If true and a transaction is already active, a savepoint will be created.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function transactionStart($asSavepoint = false)
	{
		$this->connect();

		if (!$asSavepoint || !$this->transactionDepth)
		{
			parent::transactionStart($asSavepoint);
		}

		$savepoint = 'SP_' . $this->transactionDepth;
		$this->setQuery('SAVEPOINT ' . $this->quoteName($savepoint));

		if ($this->execute())
		{
			$this->transactionDepth++;
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
	 * Return the query string to create new Database.
	 * Each database driver, other than MySQL, need to override this member to return correct string.
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
		return 'CREATE DATABASE ' . $this->quoteName($options->db_name);
	}
}
