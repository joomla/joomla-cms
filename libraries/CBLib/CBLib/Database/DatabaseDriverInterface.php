<?php
/**
 * CBLib, Community Builder Library(TM)
 *
 * @version       $Id: 4/27/14 2:26 AM $
 * @package       ${NAMESPACE}
 * @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
 * @license       http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

namespace CBLib\Database;

/**
 * CBLib\Database\Driver\CmsDatabaseDriver Class implementation
 *
 */
interface DatabaseDriverInterface
{
	/**
	 * This method loads the first field of the first row returned by the query.
	 *
	 * @return string|null  The value returned in the query or null if the query failed.
	 *
	 * @throws  \RuntimeException
	 */
	public function loadResult();

	/**
	 * Load an array of single field results into an array
	 *
	 * @param   int $offset The row offset to use to build the result array
	 * @return  array         The array with the result (empty in case of error)
	 *
	 * @throws  \RuntimeException
	 */
	public function loadResultArray( $offset = 0 );

	/**
	 * @return  \stdClass  The first row of the query.
	 *
	 * @throws  \RuntimeException
	 */
	public function loadRow();

	/**
	 * Load a list of database rows (numeric column indexing)
	 * If <var>key</var> is not empty then the returned array is indexed by the value
	 * the database key.  Returns <var>null</var> if the query fails.
	 *
	 * @param  string $key The field name of a primary key
	 * @return array         If <var>key</var> is empty as sequential list of returned records.
	 *
	 * @throws  \RuntimeException
	 */
	public function loadRowList( $key = null );

	/**
	 * Fetch a result row as an associative array
	 *
	 * @return array
	 *
	 * @throws  \RuntimeException
	 */
	public function loadAssoc();

	/**
	 * Load a associative array of associative database rows or column values.
	 *
	 * @param  string  $key     The name of a field on which to key the result array
	 * @param  string  $column  [optional] column name. If not null: Instead of the whole row, only this column value will be in the result array
	 * @return array            If $key is null: Sequential array of returned records/values, Otherwise: Keyed array
	 *
	 * @throws  \RuntimeException
	 */
	public function loadAssocList( $key = null, $column = null );

	/**
	 * This global function loads the first row of a query into an object
	 *
	 * If an object is passed to this function, the returned row is bound to the existing elements of <var>object</var>.
	 * If <var>object</var> has a value of null, then all of the returned query fields returned in the object.
	 *
	 * @param  object|\stdClass $object
	 * @return boolean          Success
	 *
	 * @throws  \RuntimeException
	 */
	public function loadObject( &$object );

	/**
	 * Load a list of database objects
	 * If $key is not empty then the returned array is indexed by the value
	 * the database key.  Returns NULL if the query fails.
	 *
	 * @param  string|array $key             The field name of a primary key, if array contains keys for sub-arrays: e.g. array( 'a', 'b' ) will store into $array[$row->a][$row->b]
	 * @param  string|null  $className       The name of the class to instantiate, set the properties of and return. If not specified, a stdClass object is returned
	 * @param  array|null   $ctor_params     An optional array of parameters to pass to the constructor for class_name objects
	 * @param  boolean      $lowerCaseIndex  default: FALSE: keep case, TRUE: lowercase array indexes (only valid if $key is string and not array)
	 * @return array                          If $key is empty as sequential list of returned records.
	 *
	 * @throws  \RuntimeException
	 */
	public function loadObjectList( $key = null, $className = null, $ctor_params = null, $lowerCaseIndex = false );

	/**
	 * Get the database driver SQL statement log.
	 *
	 * @return  array  SQL statements executed by the database driver.
	 */
	public function getLog();

	/**
	 * Returns the status of all tables, with the prefix changed if needed.
	 *
	 * @param  string  $tableName  Name of table (SQL LIKE pattern), null: all tables
	 * @param  string  $prefix     Prefix to change back
	 * @return array               A list of all the table statuses in the database
	 *
	 * @throws  \RuntimeException
	 */
	public function getTableStatus( $tableName = null, $prefix = '#__' );

	/**
	 * Method to initialize a transaction.
	 *
	 * @param   boolean  $asSavepoint  If true and a transaction is already active, a savepoint will be created.
	 * @return  self                   Returns this object to support chaining.
	 *
	 * @throws  \RuntimeException
	 */
	public function transactionStart( $asSavepoint = false );

	/**
	 * Method to commit a transaction.
	 *
	 * @param   boolean  $toSavepoint  If true, commit to the last savepoint.
	 * @return  self                   Returns this object to support chaining.
	 *
	 * @throws  \RuntimeException
	 */
	public function transactionCommit( $toSavepoint = false );

	/**
	 * Method to roll back a transaction.
	 *
	 * @param   boolean  $toSavepoint  If true, rollback to the last savepoint.
	 * @return  self                   Returns this object to support chaining.
	 *
	 * @throws  \RuntimeException
	 */
	public function transactionRollback( $toSavepoint = false );

	/**
	 * Was setting error message
	 *
	 * @deprecated 2.0 (no effect)
	 *
	 * @param  string  $errorMsg  The error message for the most recent query
	 */
	public function setErrorMsg( $errorMsg );

	/**
	 * Gets error message
	 *
	 * @return  string  The error message for the most recent query
	 */
	public function getErrorMsg();

	/**
	 * Returns a PHP date() function compliant date format for the database driver.
	 *
	 * @param  string  $dateTime   'datetime', 'date', 'time'
	 * @return string  The format  string.
	 */
	public function getDateFormat( $dateTime = 'datetime' );

	/**
	 * Returns the zero date/time
	 *
	 * @param  string  $dateTime  'datetime', 'date', 'time'
	 * @return string             Unquoted null/zero date string
	 */
	public function getNullDate( $dateTime = 'datetime' );

	/**
	 * Returns the database-formatted (not quoted) date/time in UTC timezone format
	 *
	 * @param  int     $time      NULL: Now of script start time
	 * @param  string  $dateTime  'datetime', 'date', 'time'
	 * @return string             Unquoted date string
	 */
	public function getUtcDateTime( $time = null, $dateTime = 'datetime' );

	/**
	 * Gets the fields as in DESCRIBE of MySQL
	 *
	 * @param  array|string $tables    A (list of) table names
	 * @param  boolean      $onlyType  TRUE: only type without size, FALSE: full DESCRIBE MySql
	 * @return array                   EITHER: array( tablename => array( fieldname => fieldtype ) ) or of => fieldDESCRIBE
	 *
	 * @throws  \RuntimeException
	 */
	public function getTableFields( $tables, $onlyType = true );

	/**
	 * Replace $prefix with $this->getPrefix() in $sql
	 *
	 * @param  string $sql    SQL query
	 * @param  string $prefix Common table prefix
	 * @return string
	 */
	public function replacePrefix( $sql, $prefix = '#__' );

	/**
	 * @param  array $tables A list of valid (and safe!) table names
	 * @return array           A list the create SQL for the tables
	 *
	 * @throws  \RuntimeException
	 */
	public function getTableCreate( $tables );

	/**
	 * @return int The number of affected rows in the previous operation
	 */
	public function getAffectedRows();

	/**
	 * Locks a table in the database.
	 *
	 * @param   string $tableName The name of the table to unlock.
	 *
	 * @return  self                Returns this object to support chaining.
	 *
	 * @throws  \RuntimeException
	 */
	public function lockTable( $tableName );

	/**
	 * Unlocks tables in the database.
	 *
	 * @return  self  Returns this object to support chaining.
	 *
	 * @throws  \RuntimeException
	 */
	public function unlockTables();

	/**
	 * @return string The current value of the internal SQL vairable
	 */
	public function getQuery();

	/**
	 * Gets error number
	 *
	 * @return int The error number for the most recent query
	 */
	public function getErrorNum();

	/**
	 * Checks if database's collation is case-INsensitive
	 * WARNING: individual table's fields might have a different collation
	 *
	 * @return boolean  TRUE if case INsensitive
	 *
	 * @throws  \RuntimeException
	 */
	public function isDbCollationCaseInsensitive();

	/**
	 * Method to truncate a table.
	 *
	 * @param   string $tableName The table to truncate
	 * @return  self                Returns this object to support chaining.
	 *
	 * @throws  \RuntimeException
	 */
	public function truncateTable( $tableName );

	/**
	 * Returns the version of MySQL
	 *
	 * @return string
	 */
	public function getVersion();

	/**
	 * Get the total number of SQL statements executed by the database driver.
	 *
	 * @return  integer
	 */
	public function getCount();

	/**
	 * Returns a list of tables, with the prefix changed if needed.
	 *
	 * @param  string $tableName Name of table (SQL LIKE pattern), null: all tables
	 * @param  string $prefix    Prefix to change back
	 * @return array               A list of all the tables in the database
	 *
	 * @throws  \RuntimeException
	 */
	public function getTableList( $tableName = null, $prefix = '#__' );

	/**
	 * Returns the number of rows returned from the most recent query.
	 *
	 * @param  \mysqli_result|\resource $cursor
	 * @return int
	 */
	public function getNumRows( $cursor = null );

	/**
	 * Get tables prefix (so that '#__' can be replaced by this
	 *
	 * @since 1.7
	 *
	 * @return string  Database table prefix.
	 *
	 */
	public function getPrefix();

	/**
	 * Returns the insert_id() from Mysql
	 *
	 * @return int
	 */
	public function insertid();

	/**
	 * Get a database escaped string. For LIKE statemends: $db->Quote( $db->getEscaped( $text, true ) . '%', false )
	 *
	 * @param  string  $text
	 * @param  boolean $escapeForLike : escape also % and _ wildcards for LIKE statements with % or _ in search strings  (since CB 1.2.3)
	 * @return string
	 */
	public function getEscaped( $text, $escapeForLike = false );

	/**
	 * Get a quoted database escaped string (or array of strings)
	 *
	 * @param  string|array $text
	 * @param  boolean      $escape
	 * @return string
	 */
	public function Quote( $text, $escape = true );

	/**
	 * Quote an identifier name (field, table, etc)
	 *
	 * @param  string|array $name The name (supports arrays and .-notations)
	 * @param  string|array $as   The AS query part (supports arrays too)
	 * @return string               The quoted name
	 */
	public function NameQuote( $name, $as = null );

	/**
	 * Sanitizes an array of (int) as REFERENCE
	 *
	 * @param  array   $array  Array to sanitize out
	 * @return string          ' ( 1, 2, 3 ) '
	 */
	public function safeArrayOfIntegers( $array );

	/**
	 * Sanitizes an array of (int) as REFERENCE
	 *
	 * @param  array   $array  Array to sanitize out
	 * @return string          ' ( "a", "b", "c" ) '
	 */
	public function safeArrayOfStrings( $array );

	/**
	 * Sets the SQL query string for later execution.
	 *
	 * This function replaces a string identifier $prefix with the
	 * string held is the $this->getPrefix() class variable.
	 *
	 * @param  string $sql    The SQL query (casted to (string) )
	 * @param  int    $offset The offset to start selection
	 * @param  int    $limit  The number of results to return
	 * @return self            For chaining
	 */
	public function setQuery( $sql, $offset = 0, $limit = 0 );

	/**
	 * Compares MySQL version with version_compare( MySQLversion, $minimumVersionCompare, '>=' )
	 *
	 * @param  string $minimumVersionCompare Version to compare to
	 * @return int                             Result of version_compare( $version, $minimumVersionCompare, '>=' )
	 */
	public function versionCompare( $minimumVersionCompare );

	/**
	 * Sets debug level
	 *
	 * @param  int $level New level
	 * @return int          Previous level
	 */
	public function debug( $level );

	/**
	 * Returns the formatted standard error message of SQL
	 *
	 * @deprecated 2.0
	 *
	 * @param  boolean $showSQL If TRUE, displays the last SQL statement sent to the database
	 * @return string  A standised error message
	 */
	public function stderr( $showSQL = false );

	/**
	 * Renames a table in the database.
	 *
	 * @param   string $oldTable The name of the table to be renamed
	 * @param   string $newTable The new name for the table.
	 * @param   string $backup   Non-MySQL: Table prefix
	 * @param   string $prefix   Non-MySQL: For the table - used to rename constraints in non-mysql databases
	 *
	 * @return  self               Returns this object to support chaining.
	 *
	 * @throws  \RuntimeException
	 */
	public function renameTable( $oldTable, $newTable, $backup = null, $prefix = null );

	/**
	 * Drops a table from the database.
	 *
	 * @param   string  $tableName The name of the database table to drop.
	 * @param   boolean $ifExists  Optionally specify that the table must exist before it is dropped.
	 * @return  self                 Returns this object to support chaining.
	 *
	 * @throws  \RuntimeException
	 */
	public function dropTable( $tableName, $ifExists = true );

	/**
	 * Renames a column of table in the database.
	 *
	 * @param   string  $table      The table of the field to rename
	 * @param   string  $oldColumn  The name of the field to be renamed
	 * @param   string  $newColumn  The new name for the field.
	 *
	 * @return  self               Returns this object to support chaining.
	 *
	 * @throws  \RuntimeException
	 */
	public function renameColumn( $table, $oldColumn, $newColumn );

	/**
	 * Drops a column of table in the database.
	 *
	 * @param   string   $table      The table of the field to rename
	 * @param   string   $column     The name of the field to be dropped
	 * @param   boolean  $ifExists   Optionally specify that the column must exist before it is dropped.
	 *
	 * @return  self               Returns this object to support chaining.
	 *
	 * @throws  \RuntimeException
	 */
	public function dropColumn( $table, $column, $ifExists = true );

	/**
	 * Insert an object into database
	 *
	 * @param  string $table   This is expected to be a valid (and safe!) table name
	 * @param  object &$object A reference to an object whose public properties match the table fields.
	 * @param  string $keyName The name of the primary key. If provided the object property is updated.
	 * @return boolean           TRUE if insert succeeded, FALSE when error
	 *
	 * @throws  \RuntimeException
	 */
	public function insertObject( $table, &$object, $keyName = null );

	/**
	 * Updates an object into a database
	 *
	 * @param  string              $table This is expected to be a valid (and safe!) table name
	 * @param  object              $object
	 * @param  string|array|object $keysNames
	 * @param  boolean             $updateNulls
	 * @return mixed                               A database resource if successful, FALSE if not.
	 *
	 * @throws  \RuntimeException
	 */
	public function updateObject( $table, &$object, $keysNames, $updateNulls = true );

	/**
	 * Execute the query
	 *
	 * @param  string $sql The query (optional, it will use the setQuery one otherwise)
	 * @return \mysqli_result|\resource|boolean        A database resource if successful, FALSE if not.
	 *
	 * @throws  \RuntimeException
	 */
	public function query( $sql = null );

	/**
	 * Was setting error number
	 *
	 * @deprecated 2.0 (no effect)
	 *
	 * @param int $errorNum The error number for the most recent query
	 */
	public function setErrorNum( $errorNum );

	/**
	 * Gets the index of the table
	 *
	 * @param  string $table param array|string $tables A (list of) table names
	 * @param  string $prefix
	 * @return array            Indexes
	 *
	 * @throws  \RuntimeException
	 */
	public function getTableIndex( $table, $prefix = '#__' );
}