<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 4/25/14 2:19 PM $
* @package CBLib\Database\Driver
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\Database\Driver;

use CBLib\Database\DatabaseDriverInterface;

defined('CBLIB') or die();

/**
 * CBLib\Database\Driver\CmsDatabaseDriver Class implementation
 * 
 */
class CmsDatabaseDriver implements DatabaseDriverInterface
{
	/**  Host CMS database class
	 * @var \JDatabase|\JDatabaseDriver
	 */
	protected $_db;

	/**
	 * Holds database prefix replacer (replacing the $prefix (default '#__')).
	 * @var string
	 */
	protected $_table_prefix		=	'';

	/**
	 * Holds the version of the CMS
	 * @var string
	 */
	protected $cmsRelease;

	/**
	 * The depth of the current transaction
	 * @var    int
	 */
	protected $transactionDepth	 =	0;

	/**
	 * Database object constructor
	 *
	 * @param  object|\JDatabase|\JDatabaseDriver  $cmsDatabase
	 * @param  string                              $prefix
	 * @param  string                              $cmsRelease
	 */
	public function __construct( $cmsDatabase, $prefix, $cmsRelease ) {
		$this->_db					=	$cmsDatabase;
		$this->_table_prefix		=	$prefix;
		$this->cmsRelease			=	$cmsRelease;

		if ( version_compare( $this->cmsRelease, '3.0', '<' ) && class_exists( '\JError' ) ) {
			// Make Joomla 2.5 use RunTimeExceptions
			\JError::$legacy		=	false;
		}
	}

	/**
	 * Sets debug level
	 *
	 * @param  int  $level  New level
	 * @return int          Previous level
	 */
	public function debug( $level ) {
		return $this->_db->setDebug( $level );
	}

	/**
	 * Gets error number
	 *
	 * @deprecated 2.0 (use exceptions instead)
	 *
	 * @return int The error number for the most recent query
	 */
	public function getErrorNum( ) {
		return $this->_db->getErrorNum();
	}

	/**
	 * Gets error message
	 *
	 * @deprecated 2.0 (use exceptions instead)
	 *
	 * @return string The error message for the most recent query
	 */
	public function getErrorMsg( ) {
		return stripslashes( $this->_db->getErrorMsg() );
	}

	/**
	 * Was setting error number
	 * @deprecated 2.0 (no effect)
	 *
	 * @param int      $errorNum  The error number for the most recent query
	 */
	public function setErrorNum( $errorNum) {
		// in J1.6, this is protected:
		//	$this->_db->_errorNum	=	$errorNum;
	}

	/**
	 * Was setting error message
	 * @deprecated 2.0 (no effect)
	 *
	 * @param  string  $errorMsg  The error message for the most recent query
	 */
	public function setErrorMsg( $errorMsg ) {
		// in J1.6, this is protected:
		//	$this->_db->_errorMsg	=	$errorMsg;
	}

	/**
	 * Get a database escaped string. For LIKE statemends: $db->Quote( $db->getEscaped( $text, true ) . '%', false )
	 *
	 * @param  string  $text
	 * @param  boolean $escapeForLike : escape also % and _ wildcards for LIKE statements with % or _ in search strings  (since CB 1.2.3)
	 * @return string
	 */
	public function getEscaped( $text, $escapeForLike = false ) {
		return $this->_db->escape( $text, $escapeForLike );
	}

	/**
	 * Get a quoted database escaped string (or array of strings)
	 *
	 * @param  string|array  $text
	 * @param  boolean       $escape
	 * @return string
	 */
	public function Quote( $text, $escape = true ) {
		if ( is_array( $text ) ) {
			// CMS 2.5 doesn't support arrays:
			foreach ( $text as $k => $v )
			{
				$text[$k] = $this->Quote( $v, $escape );
			}

			return $text;
		}

		return $this->_db->quote( $text, $escape );
	}

	/**
	 * Quote an identifier name (field, table, etc)
	 *
	 * @param  string|array  $name  The name (supports arrays and .-notations)
	 * @param  string|array  $as    The AS query part (supports arrays too)
	 * @return string               The quoted name
	 */
	public function NameQuote( $name, $as = null )
	{
		return $this->_db->quoteName( $name, $as );
	}

	/**
	 * Sanitizes an array of (int) as REFERENCE
	 *
	 * @param  array   $array  Array to sanitize out
	 * @return string          ' ( 1, 2, 3 ) '
	 */
	public function safeArrayOfIntegers( $array )
	{
		return ' ('
		. implode(
			', ',
			array_map(
				function ( $v )
				{
					return (int) $v;
				},
				$array
			)
		)
		. ') ';
	}

	/**
	 * Sanitizes an array of (int) as REFERENCE
	 *
	 * @param  array   $array  Array to sanitize out
	 * @return string          ' ( "a", "b", "c" ) '
	 */
	public function safeArrayOfStrings( $array )
	{
		$self	=	$this;		// PHP < 5.4 bug workaround

		return ' ('
		. implode(
			', ',
			array_map(
				function ( $v ) use ( $self )
				{
					return $self->Quote( $v );
				},
				$array
			)
		)
		. ') ';
	}

	/**
	 * Returns a PHP date() function compliant date format for the database driver.
	 *
	 * @param  string  $dateTime  'datetime', 'date', 'time'
	 * @return string             The format string.
	 */
	public function getDateFormat( $dateTime = 'datetime' )
	{
		return $this->formatDateOrTime( $this->_db->getDateFormat(), $dateTime );
	}

	/**
	 * Returns the zero date/time
	 *
	 * @param  string  $dateTime  'datetime', 'date', 'time'
	 * @return string             Unquoted null/zero date string
	 */
	public function getNullDate( $dateTime = 'datetime' ) {
		return $this->formatDateOrTime( $this->_db->getNullDate(), $dateTime );
	}

	/**
	 * Returns the database-formatted (not quoted) date/time in UTC timezone format
	 *
	 * @param  int     $time      NULL: Now of script start time
	 * @param  string  $dateTime  'datetime', 'date', 'time'
	 * @return string             Unquoted date string
	 */
	public function getUtcDateTime( $time = null, $dateTime = 'datetime' )
	{
		if ( ! $time ) {
			$time	=	isset( $_SERVER['REQUEST_TIME'] ) ? $_SERVER['REQUEST_TIME'] : time();
		}

		$date		=	new \DateTime( '@' . (string) $time, new \DateTimeZone( 'UTC' ) );

		return $date->format( $this->getDateFormat( $dateTime ) );

	}

	/**
	 * Returns the zero date/time
	 *
	 * @param  string  $date      Formated date+time
	 * @param  string  $dateTime  'datetime', 'date', 'time'
	 * @return string             Unquoted null/zero date string
	 */
	protected function formatDateOrTime( $date, $dateTime = 'datetime' )
	{
		if ( $dateTime == 'date' ) {
			if ( strlen( $date ) > 10 ) {
				return substr( $date, 0, 10 );
			}
		} elseif ( $dateTime == 'time' ) {
			if ( strlen( $date ) > 10 ) {
				return substr( $date, 11, 8 );
			}
		}

		return $date;
	}

	/**
	 * Sets the SQL query string for later execution.
	 *
	 * This function replaces a string identifier $prefix with the
	 * string held is the $this->getPrefix() class variable.
	 *
	 * @param  string $sql     The SQL query (casted to (string) )
	 * @param  int    $offset  The offset to start selection
	 * @param  int    $limit   The number of results to return
	 * @return self            For chaining
	 */
	public function setQuery( $sql, $offset = 0, $limit = 0 ) {
		$this->_db->setQuery( (string) $sql, (int) abs( $offset ), (int) abs( $limit ) );

		return $this;
	}

	/**
	 * Replace $prefix with $this->getPrefix() in $sql
	 *
	 * @param  string $sql    SQL query
	 * @param  string $prefix Common table prefix
	 * @return string
	 */
	public function replacePrefix( $sql, $prefix='#__' ) {
		return $this->_db->replacePrefix( $sql, $prefix );
	}

	/**
	 * @return string The current value of the internal SQL vairable
	 */
	public function getQuery( ) {
		return $this->_db->getQuery();
	}

	/**
	 * Execute the query
	 *
	 * @param  string                            $sql  The query (optional, it will use the setQuery one otherwise)
	 * @return \mysqli_result|\resource|boolean        A database resource if successful, FALSE if not.
	 *
	 * @throws  \RuntimeException
	 */
	public function query( $sql = null ) {
		if ( $sql !== null ) {
			$this->setQuery( $sql );
		}

		return $this->_db->execute();
	}

	/**
	 * @return int The number of affected rows in the previous operation
	 */
	public function getAffectedRows( ) {
		return $this->_db->getAffectedRows();
	}

	/**
	 * Returns the number of rows returned from the most recent query.
	 *
	 * @param  \mysqli_result|\resource  $cursor
	 * @return int
	 */
	public function getNumRows( $cursor = null ) {
		return $this->_db->getNumRows( $cursor );
	}

	/**
	 * This method loads the first field of the first row returned by the query.
	 *
	 * @return string|null  The value returned in the query or null if the query failed.
	 *
	 * @throws  \RuntimeException
	 */
	public function loadResult( ) {
		return $this->_db->loadResult();
	}

	/**
	 * Internal function to replace a null result by an empty array
	 *
	 * @param  array|null  $resultArray  The array that may be a null on some systems
	 * @return array
	 */
	protected function _nullToArray( $resultArray ) {
		if ( $resultArray === null ) {
			$resultArray	=	array();
		}
		return $resultArray;
	}

	/**
	 * Load an array of single field results into an array
	 *
	 * @param   int  $offset  The row offset to use to build the result array
	 * @return  array         The array with the result (empty in case of error)
	 *
	 * @throws  \RuntimeException
	 */
	public function loadResultArray( $offset = 0 ) {
		return $this->_nullToArray( $this->_db->loadColumn( $offset ) );
	}

	/**
	 * Fetch a result row as an associative array
	 *
	 * @return array
	 *
	 * @throws  \RuntimeException
	 */
	public function loadAssoc( ) {
		return $this->_nullToArray( $this->_db->loadAssoc( ) );
	}

	/**
	 * Load a associative array of associative database rows or column values.
	 *
	 * @param  string  $key     The name of a field on which to key the result array
	 * @param  string  $column  [optional] column name. If not null: Instead of the whole row, only this column value will be in the result array
	 * @return array            If $key is null: Sequential array of returned records/values, Otherwise: Keyed array
	 *
	 * @throws  \RuntimeException
	 */
	public function loadAssocList( $key = null, $column = null ) {
		return $this->_nullToArray( $this->_db->loadAssocList( $key, $column ) );
	}

	/**
	 * This global function loads the first row of a query into an object
	 *
	 * If an object is passed to this function, the returned row is bound to the existing elements of <var>object</var>.
	 * If <var>object</var> has a value of null, then all of the returned query fields returned in the object.
	 * @param  object|\stdClass  $object
	 * @return boolean          Success
	 *
	 * @throws  \RuntimeException
	 */
	public function loadObject( &$object ) {

		if ( $object === null ) {
			$object		=	$this->_db->loadObject();
			return is_object( $object );
		}

		$array			=	$this->_db->loadAssoc();

		if ( ! is_array( $array ) ) {
			return false;
		}

		foreach ( get_object_vars( $object ) as $k => $v) {
			if( substr( $k, 0, 1 ) != '_' ) {
				if ( array_key_exists( $k, $array ) ) {
					$object->$k		=	$array[$k];
				}
			}
		}
		return true;
	}

	/**
	 * Load a list of database objects
	 * If $key is not empty then the returned array is indexed by the value
	 * the database key.  Returns NULL if the query fails.
	 *
	 * @param  string|array  $key             The field name of a primary key, if array contains keys for sub-arrays: e.g. array( 'a', 'b' ) will store into $array[$row->a][$row->b]
	 * @param  string|null   $className       The name of the class to instantiate, set the properties of and return. If not specified, a stdClass object is returned
	 * @param  array|null    $ctor_params     An optional array of parameters to pass to the constructor for class_name objects
	 * @param  boolean       $lowerCaseIndex  default: FALSE: keep case, TRUE: lowercase array indexes (only valid if $key is string and not array)
	 * @return array                          If $key is empty as sequential list of returned records.
	 *
	 * @throws  \RuntimeException
	 */
	public function loadObjectList( $key = null, $className = null, $ctor_params = null, $lowerCaseIndex = false ) {
		// Unfortunately, the CMS 2.5.18 and 3.3.0 loadObjectList() does not support array $key, $ctor_params, nor $lowerCaseIndex

		$rows				=	$this->loadAssocList( $key );

		$array				=	array();

		$reflection			=	new \ReflectionClass( $className ? $className : 'stdClass' );

		foreach ( $rows as $rowArray ) {
			if ( empty( $ctor_params ) ) {
				$obj		=	$reflection->newInstance();
			} else {
				$obj		=	$reflection->newInstanceArgs( $ctor_params );
			}

			foreach ( $rowArray as $k => $v ) {
				$obj->$k	=	$v;
			}

			if ( ! $key ) {
				$array[]										=	$obj;
			}
			elseif ( is_array( $key ) ) {
				if ( count( $key ) == 2 ) {
					list( $ka, $kb )							=	$key;
					$array[$obj->$ka][$obj->$kb]				=	$obj;
				}
				elseif ( count( $key ) == 3 ) {
					list( $ka, $kb, $kc )						=	$key;
					$array[$obj->$ka][$obj->$kb][$obj->$kc]		=	$obj;
				}
			}
			elseif ( $lowerCaseIndex ) {
				$array[strtolower($obj->$key)]					=	$obj;
			} else {
				$array[$obj->$key]								=	$obj;
			}
		}

		unset( $rows, $rowArray );

		return $array;
	}

	/**
	 * @return  \stdClass  The first row of the query.
	 *
	 * @throws  \RuntimeException
	 */
	public function loadRow( ) {
		return $this->_db->loadRow();
	}

	/**
	 * Load a list of database rows (numeric column indexing)
	 * If <var>key</var> is not empty then the returned array is indexed by the value
	 * the database key.  Returns <var>null</var> if the query fails.
	 *
	 * @param  string  $key  The field name of a primary key
	 * @return array         If <var>key</var> is empty as sequential list of returned records.
	 *
	 * @throws  \RuntimeException
	 */
	public function loadRowList( $key = null ) {
		$resultArray	=	$this->_db->loadRowList( $key );
		return $this->_nullToArray( $resultArray );
	}

	/**
	 * Insert an object into database
	 *
	 * @param  string  $table    This is expected to be a valid (and safe!) table name
	 * @param  object  &$object  A reference to an object whose public properties match the table fields.
	 * @param  string  $keyName  The name of the primary key. If provided the object property is updated.
	 * @return boolean           TRUE if insert succeeded, FALSE when error
	 *
	 * @throws  \RuntimeException
	 */
	public function insertObject( $table, &$object, $keyName = null ) {
		return $this->_db->insertObject( $table, $object, $keyName );
	}

	/**
	 * Updates an object into a database
	 *
	 * @param  string                $table        This is expected to be a valid (and safe!) table name
	 * @param  object                $object
	 * @param  string|array|object   $keysNames
	 * @param  boolean               $updateNulls
	 * @return mixed                               A database resource if successful, FALSE if not.
	 *
	 * @throws  \RuntimeException
	 */
	public function updateObject( $table, &$object, $keysNames, $updateNulls = true ) {
		$keysNames	=	(array) $keysNames;

		$fields		=	array();
		$where		=	array();

		foreach ( get_object_vars( $object ) as $k => $v ) {
			if( is_array( $v ) or is_object( $v ) or $k[0] == '_' ) {
			 	// internal or NA field
				continue;
			}

			if( in_array( $k, $keysNames ) ) {
			 	// PK not to be updated (is_int case is missing in Joomla 3)
				$where[]	=	$this->NameQuote( $k ) . ' = ' . ( is_int( $v ) ? (int) $v : $this->Quote( $v ) );
				continue;
			}

			if( $v === NULL ) {
				if ( ! $updateNulls ) {
					continue;
				}
				$val	=	'NULL';
			} elseif( is_int( $v ) ) {
				// This case is missing in Joomla 3
				$val	=	(int) $v;
			} else {
				$val	=	$this->Quote( $v );
			}

			$fields[]	=	$this->NameQuote( $k ) . ' = ' . $val;
		}

		if ( empty( $fields ) ) {
			return true;
		}

		$formatedSql	=	'UPDATE ' . $this->NameQuote( $table ) . ' SET %s WHERE %s';
		$this->setQuery( sprintf( $formatedSql, implode( ', ', $fields ) , implode(' AND ', $where ) ) );

		return $this->query();
	}

	/**
	 * Returns the formatted standard error message of SQL
	 *
	 * @deprecated 2.0
	 *
	 * @param  boolean $showSQL  If TRUE, displays the last SQL statement sent to the database
	 * @return string  A standised error message
	 */
	public function stderr( $showSQL = false ) {
		return $this->_db->stderr( $showSQL );
	}

	/**
	 * Returns the insert_id() from Mysql
	 *
	 * @return int
	 */
	public function insertid( ) {
		return $this->_db->insertid();
	}

	/**
	 * Returns the version of MySQL
	 *
	 * @return string
	 */
	public function getVersion( ) {
		return $this->_db->getVersion();
	}

	/**
	 * Compares MySQL version with version_compare( MySQLversion, $minimumVersionCompare, '>=' )
	 *
	 * @param  string  $minimumVersionCompare  Version to compare to
	 * @return int                             Result of version_compare( $version, $minimumVersionCompare, '>=' )
	 */
	public function versionCompare( $minimumVersionCompare ) {
		static $version					=	null;
		if ( $version === null ) {
			$version					=	preg_replace( '/^([0-9\.]+).*/', '\\1', $this->getVersion() );
		}
		return version_compare( $version, $minimumVersionCompare, '>=' );
	}

	/**
	 * Get tables prefix (so that '#__' can be replaced by this
	 * @since 1.7
	 *
	 * @return string  Database table prefix.
	 *
	 */
	public function getPrefix() {
		return $this->_table_prefix;
	}

	/**
	 * Returns a list of tables, with the prefix changed if needed.
	 *
	 * @param  string  $tableName  Name of table (SQL LIKE pattern), null: all tables
	 * @param  string  $prefix     Prefix to change back
	 * @return array               A list of all the tables in the database
	 *
	 * @throws  \RuntimeException
	 */
	public function getTableList( $tableName = null, $prefix = '#__' ) {
		$this->setQuery( 'SHOW TABLES' . ( $tableName ? ' LIKE ' . $this->Quote( $this->replacePrefix( $tableName, $prefix ) ) : '' ) );
		$tables							=	$this->loadResultArray();
		if ( $prefix ) {
			foreach ( $tables as $k => $n ) {
				$tables[$k]				=	preg_replace( '/^(' . $this->getPrefix() . ')/', $prefix, $n );
			}
		}
		return $tables;
	}

	/**
	 * Returns the status of all tables, with the prefix changed if needed.
	 *
	 * @param  string  $tableName  Name of table (SQL LIKE pattern), null: all tables
	 * @param  string  $prefix     Prefix to change back
	 * @return array               A list of all the table statuses in the database
	 *
	 * @throws  \RuntimeException
	 */
	public function getTableStatus( $tableName = null, $prefix = '#__' ) {
		$this->setQuery( 'SHOW TABLE STATUS' . ( $tableName ? ' LIKE ' . $this->Quote( $this->replacePrefix( $tableName, $prefix ) ) : '' ) );
		$tables							=	$this->loadObjectList();
		if ( $prefix ) {
			foreach ( $tables as $k => $n ) {
				$tables[$k]->Name		=	preg_replace( '/^(' . $this->getPrefix() . ')/', $prefix, $n->Name );
			}
		}
		return $tables;
	}

	/**
	 * @param  array  $tables  A list of valid (and safe!) table names
	 * @return array           A list the create SQL for the tables
	 *
	 * @throws  \RuntimeException
	 */
	public function getTableCreate( $tables ) {
		$createQueries					=	array();
		foreach ( $tables as $tableName ) {
			$this->setQuery( 'SHOW CREATE table ' . $this->NameQuote( $tableName ) );
			$this->query();
			$createQueries[$tableName]	=	$this->loadResultArray( 1 );
		}
		return $createQueries;
	}

	/**
	 * Gets the fields as in DESCRIBE of MySQL
	 *
	 * @param  array|string  $tables    A (list of) table names
	 * @param  boolean       $onlyType  TRUE: only type without size, FALSE: full DESCRIBE MySql
	 * @return array                    EITHER: array( tablename => array( fieldname => fieldtype ) ) or of => fieldDESCRIBE
	 *
	 * @throws  \RuntimeException
	 */
	public function getTableFields( $tables, $onlyType = true ) {
		$result							=	array();
		$tables							=	(array) $tables;

		foreach ( $tables as $tbl ) {
			$this->setQuery( 'SHOW' . ( ( ! $onlyType ) && $this->versionCompare( '4.1' ) ? ' FULL' : '' ) . ' COLUMNS FROM ' . $this->NameQuote( $tbl ) );
			$result[$tbl]				=	$this->loadObjectList( 'Field' );
			if ( is_array( $result[$tbl] ) && $onlyType ) {
				foreach ( $result[$tbl] as $k => $fld ) {
					$result[$tbl][$k]	=	preg_replace( '/[(0-9)]/','', $fld->Type );
				}
			}
		}
		return $result;
	}

	/**
	 * Gets the index of the table
	 *
	 * @param  string  $table   param array|string $tables A (list of) table names
	 * @param  string  $prefix
	 * @return array            Indexes
	 *
	 * @throws  \RuntimeException
	 */
	public function getTableIndex( $table, $prefix = '#__' ) {
		$this->setQuery( 'SHOW INDEX FROM ' . $this->NameQuote( $table ) );
		$indexes						=	$this->loadObjectList();
		if ( $prefix ) {
			foreach ( $indexes as $k => $n ) {
				$indexes[$k]->Table		=	preg_replace( '/^(' . $this->getPrefix() . ')/', $prefix, $n->Table );
			}
		}
		return $indexes;
	}

	/**
	 * Checks if database's collation is case-INsensitive
	 * WARNING: individual table's fields might have a different collation
	 *
	 * @return boolean  TRUE if case INsensitive
	 *
	 * @throws  \RuntimeException
	 */
	public function isDbCollationCaseInsensitive( ) {
		static $result = null;

		if ( $result === null ) {
			$query = "SELECT IF('a'='A', 1, 0);";
			$this->setQuery( $query );
			$result		=	$this->loadResult();
		}
		return ( $result == 1 );
	}

	/**
	 * Get the total number of SQL statements executed by the database driver.
	 *
	 * @return  integer
	 */
	public function getCount()
	{
		return $this->_db->getCount();
	}

	/**
	 * Get the database driver SQL statement log.
	 *
	 * @return  array  SQL statements executed by the database driver.
	 */
	public function getLog()
	{
		return $this->_db->getLog();
	}

	/**
	 * Renames a table in the database.
	 *
	 * @param   string  $oldTable  The name of the table to be renamed
	 * @param   string  $newTable  The new name for the table.
	 * @param   string  $backup    Non-MySQL: Table prefix
	 * @param   string  $prefix    Non-MySQL: For the table - used to rename constraints in non-mysql databases
	 *
	 * @return  self               Returns this object to support chaining.
	 *
	 * @throws  \RuntimeException
	 */
	public function renameTable( $oldTable, $newTable, $backup = null, $prefix = null )
	{
		$this->_db->renameTable( $oldTable, $newTable, $backup, $prefix );

		return $this;
	}

	/**
	 * Drops a table from the database.
	 *
	 * @param   string   $tableName  The name of the database table to drop.
	 * @param   boolean  $ifExists   Optionally specify that the table must exist before it is dropped.
	 * @return  self                 Returns this object to support chaining.
	 *
	 * @throws  \RuntimeException
	 */
	public function dropTable( $tableName, $ifExists = true )
	{
		$this->_db->dropTable( $tableName, $ifExists );

		return $this;
	}

	/**
	 * Method to truncate a table.
	 *
	 * @param   string  $tableName  The table to truncate
	 * @return  self                Returns this object to support chaining.
	 *
	 * @throws  \RuntimeException
	 */
	public function truncateTable( $tableName )
	{
		$this->query( 'TRUNCATE TABLE ' . $this->nameQuote( $tableName ) );

		return $this;
	}

	/**
	 * Locks a table in the database.
	 *
	 * @param   string  $tableName  The name of the table to unlock.
	 *
	 * @return  self                Returns this object to support chaining.
	 *
	 * @throws  \RuntimeException
	 */
	public function lockTable($tableName)
	{
		$this->_db->lockTable( $tableName );

		return $this;
	}

	/**
	 * Unlocks tables in the database.
	 *
	 * @return  self  Returns this object to support chaining.
	 *
	 * @throws  \RuntimeException
	 */
	public function unlockTables()
	{
		$this->query('UNLOCK TABLES');

		return $this;
	}

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
	public function renameColumn( $table, $oldColumn, $newColumn )
	{
		$columns	=	$this->getTableFields( $table, false );

		if ( isset( $columns[$table][$oldColumn] ) ) {
			$this->query( 'ALTER TABLE ' . $this->nameQuote( $table ) . ' CHANGE ' . $this->nameQuote( $oldColumn ) . ' ' . $this->nameQuote( $newColumn ) . ' ' . $columns[$table][$oldColumn]->Type );
		}

		return $this;
	}

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
	public function dropColumn( $table, $column, $ifExists = true )
	{
		$drop			=	true;

		if ( $ifExists ) {
			$columns	=	$this->getTableFields( $table );

			if ( ! isset( $columns[$table][$column] ) ) {
				$drop	=	false;
			}
		}

		if ( $drop ) {
			$this->query( 'ALTER TABLE ' . $this->nameQuote( $table ) . ' DROP COLUMN ' . $this->nameQuote( $column ) );
		}

		return $this;
	}

	/**
	 * Method to initialize a transaction.
	 *
	 * @param   boolean  $asSavepoint  If true and a transaction is already active, a savepoint will be created.
	 * @return  self                   Returns this object to support chaining.
	 *
	 * @throws  \RuntimeException
	 */
	public function transactionStart( $asSavepoint = false )
	{
		if ( version_compare( $this->cmsRelease, '3.2', '>=' ) ) {
			$this->_db->transactionStart( $asSavepoint );
		} else {
			if ( ! $asSavepoint || ! $this->transactionDepth ) {
				if ($this->query( 'START TRANSACTION' ) ) {
					$this->transactionDepth		=	1;
				}

				return $this;
			}

			$savepoint		=	'SP_' . $this->transactionDepth;
			if ( $this->query( 'SAVEPOINT ' . $this->nameQuote( $savepoint ) ) ) {
				$this->transactionDepth++;
			}
		}

		return $this;
	}

	/**
	 * Method to commit a transaction.
	 *
	 * @param   boolean  $toSavepoint  If true, commit to the last savepoint.
	 * @return  self                   Returns this object to support chaining.
	 *
	 * @throws  \RuntimeException
	 */
	public function transactionCommit( $toSavepoint = false )
	{
		if ( version_compare( $this->cmsRelease, '3.2', '>=' ) ) {
			$this->_db->transactionCommit( $toSavepoint );
		} else {
			if ( ! $toSavepoint || $this->transactionDepth <= 1 )
			{
				$this->_db->transactionCommit();
				$this->transactionDepth		=	0;

				return $this;
			}

			$this->transactionDepth--;
		}

		return $this;
	}

	/**
	 * Method to roll back a transaction.
	 *
	 * @param   boolean  $toSavepoint  If true, rollback to the last savepoint.
	 * @return  self                   Returns this object to support chaining.
	 *
	 * @throws  \RuntimeException
	 */
	public function transactionRollback( $toSavepoint = false )
	{
		if ( version_compare( $this->cmsRelease, '3.2', '>=' ) ) {
			$this->_db->transactionRollback( $toSavepoint );
		} else {
			if ( ! $toSavepoint || $this->transactionDepth <= 1 ) {
				if ($this->query( 'ROLLBACK' ) ) {
					$this->transactionDepth		=	0;
				}

				return $this;
			}

			$savepoint		=	'SP_' . ( $this->transactionDepth - 1 );
			if ( $this->query( 'ROLLBACK TO SAVEPOINT ' . $this->nameQuote( $savepoint ) ) ) {
				$this->transactionDepth--;
			}
		}

		return $this;
	}
}
