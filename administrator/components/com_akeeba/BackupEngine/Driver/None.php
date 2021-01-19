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

use Akeeba\Engine\Driver\Query\Base as QueryBase;

/**
 * Dummy driver class for flat-file CMS
 */
class None extends Base
{
	public static $dbtech = 'none';
	/**
	 * The name of the database driver.
	 *
	 * @var    string
	 * @since  1.0
	 */
	public $name = 'none';

	public function __construct(array $options)
	{
		$this->driverType = 'none';

		parent::__construct($options);
	}

	/**
	 * Test to see if this db driver is available
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   1.0
	 */
	public static function isSupported()
	{
		return true;
	}

	public function open()
	{
		return $this;
	}

	/**
	 * Closes the database connection
	 */
	public function close()
	{
		return;
	}

	/**
	 * Determines if the connection to the server is active.
	 *
	 * @return  boolean  True if connected to the database engine.
	 */
	public function connected()
	{
		return true;
	}

	/**
	 * Drops a table from the database.
	 *
	 * @param   string   $table     The name of the database table to drop.
	 * @param   boolean  $ifExists  Optionally specify that the table must exist before it is dropped.
	 *
	 * @return  Base  Returns this object to support chaining.
	 */
	public function dropTable($table, $ifExists = true)
	{
		return $this;
	}

	/**
	 * Method to escape a string for usage in an SQL statement.
	 *
	 * @param   string   $text   The string to be escaped.
	 * @param   boolean  $extra  Optional parameter to provide extra escaping.
	 *
	 * @return  string   The escaped string.
	 */
	public function escape($text, $extra = false)
	{
		return '';
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
		return false;
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
		return;
	}

	/**
	 * Get the number of affected rows for the previous executed SQL statement.
	 *
	 * @return  integer  The number of affected rows.
	 */
	public function getAffectedRows()
	{
		return 0;
	}

	/**
	 * Method to get the database collation in use by sampling a text field of a table in the database.
	 *
	 * @return  mixed  The collation in use by the database or boolean false if not supported.
	 */
	public function getCollation()
	{
		return false;
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
		return 0;
	}

	/**
	 * Get the current query object or a new QueryBase object.
	 *
	 * @param   boolean  $new  False to return the current query object, True to return a new QueryBase object.
	 *
	 * @return  QueryBase  The current query object or a new object extending the QueryBase class.
	 */
	public function getQuery($new = false)
	{
		return $this->sql;
	}

	/**
	 * Retrieves field information about the given tables.
	 *
	 * @param   string   $table     The name of the database table.
	 * @param   boolean  $typeOnly  True (default) to only return field types.
	 *
	 * @return  array  An array of fields by table.
	 */
	public function getTableColumns($table, $typeOnly = true)
	{
		return [];
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
		return [];
	}

	/**
	 * Retrieves field information about the given tables.
	 *
	 * @param   mixed  $tables  A table name or a list of table names.
	 *
	 * @return  array  An array of keys for the table(s).
	 */
	public function getTableKeys($tables)
	{
		return [];
	}

	/**
	 * Method to get an array of all tables in the database.
	 *
	 * @return  array  An array of all the tables in the database.
	 */
	public function getTableList()
	{
		return [];
	}

	/**
	 * Returns an array with the names of tables, views, procedures, functions and triggers
	 * in the database. The table names are the keys of the tables, whereas the value is
	 * the type of each element: table, view, merge, temp, procedure, function or trigger.
	 * Note that merge are MRG_MYISAM tables and temp is non-permanent data table, usually
	 * set up as temporary, black hole or federated tables. These two types should never,
	 * ever, have their data dumped in the SQL dump file.
	 *
	 * @param   bool  $abstract  Return or normal names? Defaults to true (names)
	 *
	 * @return  array
	 */
	public function getTables($abstract = true)
	{
		return [];
	}

	/**
	 * Get the version of the database connector
	 *
	 * @return  string  The database connector version.
	 */
	public function getVersion()
	{
		return '0.0.0';
	}

	/**
	 * Method to get the auto-incremented value from the last INSERT statement.
	 *
	 * @return  integer  The value of the auto-increment field from the last inserted row.
	 */
	public function insertid()
	{
		return 0;
	}

	/**
	 * Locks a table in the database.
	 *
	 * @param   string  $tableName  The name of the table to unlock.
	 *
	 * @return  Base  Returns this object to support chaining.
	 */
	public function lockTable($tableName)
	{
		return $this;
	}

	/**
	 * Execute the SQL statement.
	 *
	 * @return  mixed  A database cursor resource on success, boolean false on failure.
	 */
	public function query()
	{
		return false;
	}

	/**
	 * Renames a table in the database.
	 *
	 * @param   string  $oldTable  The name of the table to be renamed
	 * @param   string  $newTable  The new name for the table.
	 * @param   string  $backup    Table prefix
	 * @param   string  $prefix    For the table - used to rename constraints in non-mysql databases
	 *
	 * @return  Base  Returns this object to support chaining.
	 */
	public function renameTable($oldTable, $newTable, $backup = null, $prefix = null)
	{
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
		return true;
	}

	/**
	 * Set the connection to use UTF-8 character encoding.
	 *
	 * @return  boolean  True on success.
	 */
	public function setUTF()
	{
		return true;
	}

	/**
	 * Method to commit a transaction.
	 *
	 * @return  void
	 */
	public function transactionCommit()
	{
		return;
	}

	/**
	 * Method to roll back a transaction.
	 *
	 * @return  void
	 */
	public function transactionRollback()
	{
		return;
	}

	/**
	 * Method to initialize a transaction.
	 *
	 * @return  void
	 */
	public function transactionStart()
	{
		return;
	}

	/**
	 * Unlocks tables in the database.
	 *
	 * @return  Base  Returns this object to support chaining.
	 */
	public function unlockTables()
	{
		return $this;
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
		return false;
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
		return false;
	}
}
