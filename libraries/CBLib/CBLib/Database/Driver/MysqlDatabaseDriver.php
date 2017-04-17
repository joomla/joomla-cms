<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 4/24/14 5:33 PM $
* @package CBLib\Database\Engine
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\Database\Driver;

use CBLib\Database\DatabaseDriverInterface;

defined('CBLIB') or die();

/**
 * CBLib\Database\Driver\Mysql Class implementation
 * 
 */
class MysqlDatabaseDriver implements DatabaseDriverInterface
{
	/**  Host CMS database class
	 * @var \mysqli
	 */
	protected $connection;

	/**
	 * Holds database prefix replacer (replacing the $prefix (default '#__')).
	 * @var string
	 */
	protected $_table_prefix		= '';

	/**
	 * @var    boolean  The database driver debugging state.
	 */
	protected $debug = false;

	/**
	 * @var         integer  The database error number
	 */
	protected $errorNum = 0;

	/**
	 * @var         string  The database error message
	 */
	protected $errorMsg;

	/**
	 * The number of SQL statements executed by the database driver
	 * @var    int
	 */
	protected $count			=	0;

	/**
	 * The log of executed SQL statements by the database driver
	 * @var    array
	 */
	protected $log				=	array();

	/**
	 * Name-quoting string
	 * @var string
	 */
	protected $nameQuote		=	'`';

	/**
	 * Database date format
	 * @var string
	 */
	protected $dateFormat		=	array( 'datetime' => 'Y-m-d H:i:s', 'date' => 'Y-m-d', 'time' => 'H:i:s' );

	/**
	 * Database null date notation
	 * @var string
	 */
	protected $nullDateTime		=	array( 'datetime' => '0000-00-00 00:00:00', 'date' => '0000-00-00', 'time' => '00:00:00' );

	/**
	 * The current SQL statement to execute
	 * @var string
	 */
	protected $sql;

	/**
	 * The affected row limit for the current SQL statement
	 * @var    int
	 */
	protected $limit			=	0;

	/**
	 * The row offset to apply for the current SQL statement
	 * @var    integer
	 */
	protected $offset			=	0;

	/**
	 * The database connection cursor from the last query
	 * @var    resource
	 */
	protected $cursor;

	/**
	 * The depth of the current transaction
	 * @var    int
	 */
	protected $transactionDepth	 =	0;

	/**
	 * Database object constructor
	 *
	 * @param  \mysqli|\resource  $connection
	 * @param  string             $prefix
	 */
	public function __construct( $connection, $prefix ) {
		$this->connection				=	$connection;
		$this->_table_prefix			=	$prefix;
	}

	/**
	 * Sets debug level
	 *
	 * @param  int  $level  New level
	 * @return int          Previous level
	 */
	public function debug( $level )
	{
		$previous = $this->debug;
		$this->debug = (bool) $level;

		return $previous;
	}

	/**
	 * Gets error number
	 *
	 * @deprecated 2.0 (use exceptions instead)
	 *
	 * @return int The error number for the most recent query
	 */
	public function getErrorNum( ) {
		return $this->errorNum;
	}

	/**
	 * Gets error message
	 *
	 * @deprecated 2.0 (use exceptions instead)
	 *
	 * @return string The error message for the most recent query
	 */
	public function getErrorMsg( ) {
		return stripslashes( $this->errorMsg );
	}

	/**
	 * Was setting error number
	 * @deprecated 2.0 (no effect)
	 *
	 * @param int      $errorNum  The error number for the most recent query
	 */
	public function setErrorNum( $errorNum) {
		$this->errorNum	=	$errorNum;
	}

	/**
	 * Was setting error message
	 * @deprecated 2.0 (no effect)
	 *
	 * @param  string  $errorMsg  The error message for the most recent query
	 */
	public function setErrorMsg( $errorMsg ) {
		$this->errorMsg	=	$errorMsg;
	}

	/**
	 * Get a database escaped string. For LIKE statemends: $db->Quote( $db->getEscaped( $text, true ) . '%', false )
	 *
	 * @param  string  $text
	 * @param  boolean $escapeForLike : escape also % and _ wildcards for LIKE statements with % or _ in search strings  (since CB 1.2.3)
	 * @return string
	 */
	public function getEscaped( $text, $escapeForLike = false ) {
		if ( $this->connection instanceof \mysqli ) {
			$result		=	mysqli_real_escape_string( $this->connection, $text );
		} else {
			$result		=	mysql_real_escape_string( $text, $this->connection );
		}

		if ( $escapeForLike )
		{
			$result = addcslashes( $result, '%_' );
		}

		return $result;
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
			foreach ( $text as $k => $v )
			{
				$text[$k] = $this->quote( $v, $escape );
			}

			return $text;
		}

		return '\'' . ( $escape ? $this->getEscaped( $text ) : $text ) . '\'';
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
		if (is_string($name))
		{
			$quotedName = $this->quoteNameStr(explode('.', $name));

			$quotedAs = '';
			if (!is_null($as))
			{
				settype($as, 'array');
				$quotedAs .= ' AS ' . $this->quoteNameStr($as);
			}

			return $quotedName . $quotedAs;
		}
		else
		{
			$fin = array();

			if (is_null($as))
			{
				foreach ($name as $str)
				{
					$fin[] = $this->NameQuote($str);
				}
			}
			elseif (is_array($name) && (count($name) == count($as)))
			{
				for ($i = 0; $i < count($name); $i++)
				{
					$fin[] = $this->NameQuote($name[$i], $as[$i]);
				}
			}

			return $fin;
		}
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
	 * Quote strings coming from quoteName call.
	 *
	 * @param   array  $strArr  Array of strings coming from quoteName dot-explosion.
	 *
	 * @return  string  Dot-imploded string of quoted parts.
	 *
	 * @since 11.3
	 */
	protected function quoteNameStr($strArr)
	{
		$parts = array();
		$q = $this->nameQuote;

		foreach ($strArr as $part)
		{
			if (is_null($part))
			{
				continue;
			}

			if (strlen($q) == 1)
			{
				$parts[] = $q . $part . $q;
			}
			else
			{
				$parts[] = $q{0} . $part . $q{1};
			}
		}

		return implode('.', $parts);
	}

	/**
	 * Returns a PHP date() function compliant date format for the database driver.
	 *
	 * @param  string  $dateTime  'datetime', 'date', 'time'
	 * @return string  The format string.
	 */
	public function getDateFormat( $dateTime = 'datetime' )
	{
		return $this->dateFormat[$dateTime];
	}

	/**
	 * Returns the zero date/time
	 *
	 * @param  string  $dateTime  'datetime', 'date', 'time'
	 * @return string  Unquoted null/zero date string
	 */
	public function getNullDate( $dateTime = 'datetime' ) {
		return $this->nullDateTime[$dateTime];
	}

	/**
	 * Returns the database-formatted (not quoted) date/time in UTC timezone format
	 *
	 * @param  int     $time      NULL: Now of script start time ($_SERVER['REQUEST_TIME'] or time())
	 * @param  string  $dateTime  'datetime', 'date', 'time'
	 * @return string             Unquoted SQL date string
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
		$this->sql		=	$sql;
		$this->limit	=	(int) max(0, abs( $limit ) );
		$this->offset	=	(int) max(0, abs( $offset ) );

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
		// Preg Pattern is: find any non-quoted (which is not including single or double quotes) string being the prefix in $sql possibly followed by a double or single quoted one:
		// not including quotes:
		//		positive lookahead:			(?<=
		//		not including " or ':			[^"\']+
		//									)(
		// including exactly the prefix to replace:		preg_quote( $prefix, '/' )
		// 								)(
		// Followed by a double-quoted:		"(?:[^\\"]|\\.)*"
		// Or:								|
		// single-quoted:					\'(?:[^\\\']|\\.)*\'
		// 								)
		// possibly:						?
		////// $pattern				=	'/(?:(?<=[^"\'])|^)(' . preg_quote( $prefix, '/' ) . ')("(?:[^\\\\"]|\.)*"|\'(?:[^\\\\\']|\.)*\')?/';
		////// return preg_replace( $pattern, $this->getPrefix() . '\\2', $sql );

		// Initialize variables.
		$startPos = 0;
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

			$j = strpos($sql, "'", $startPos);
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

			$literal .= str_replace($prefix, $this->_table_prefix, substr($sql, $startPos, $j - $startPos));
			$startPos = $j;

			$j = $startPos + 1;

			if ($j >= $n)
			{
				break;
			}

			// quote comes first, find end of quote
			while (true)
			{
				$k = strpos($sql, $quoteChar, $j);
				$escaped = false;
				if ($k === false)
				{
					break;
				}
				$l = $k - 1;
				while ($l >= 0 && $sql{$l} == '\\')
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
				// error in the query - no end quote; ignore it
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
	 * @return string The current value of the internal SQL vairable
	 */
	public function getQuery( ) {
		return $this->sql;
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
		return $this->execute();
	}

	/**
	 * Executes the query
	 *
	 * @return \mysqli_result|\resource|boolean        A database resource if successful, FALSE if not.
	 *
	 * @throws  \RuntimeException
	 */
	protected function execute( )
	{
		$query					=	$this->replacePrefix( (string) $this->sql );

		if ( $this->limit > 0 || $this->offset > 0 )
		{
			$query				.=	' LIMIT ' . $this->offset . ', ' . $this->limit;
		}

		// Increment the query counter.
		$this->count++;

		// Reset the error values.
		$this->errorNum			=	0;
		$this->errorMsg			=	'';

		if ( $this->debug ) {
			$this->log[]		=	$query;
		}

		// Execute the query. Error suppression is used here to prevent warnings/notices that the connection has been lost.
		if ( $this->connection instanceof \mysqli ) {
			$this->cursor		=	@mysqli_query( $this->connection, $query );

			if (!$this->cursor)
			{
				$this->errorNum	=	(int) mysqli_errno( $this->connection );
				$this->errorMsg	=	(string) mysqli_error( $this->connection ) . ' SQL=' . $query;
				throw new \RuntimeException($this->errorMsg, $this->errorNum);
			}
		} else {
			$this->cursor		=	@mysql_query( $query, $this->connection );

			if (!$this->cursor)
			{
				$this->errorNum	=	(int) mysql_errno( $this->connection );
				$this->errorMsg	=	(string) mysql_error( $this->connection ) . ' SQL=' . $query;
				throw new \RuntimeException($this->errorMsg, $this->errorNum);
			}
		}

		return $this->cursor;
	}

	/**
	 * @return int The number of affected rows in the previous operation
	 */
	public function getAffectedRows( ) {
		if ( $this->connection instanceof \mysqli ) {
			return mysqli_affected_rows( $this->connection );
		} else {
			return mysql_affected_rows( $this->connection );
		}
	}

	/**
	 * Returns the number of rows returned from the most recent query.
	 *
	 * @param  \mysqli_result|\resource  $cursor
	 * @return int
	 */
	public function getNumRows( $cursor = null ) {
		if ( $this->connection instanceof \mysqli ) {
			return mysqli_num_rows( $cursor ? $cursor : $this->cursor );
		} else {
			return mysql_num_rows( $cursor ? $cursor : $this->cursor );
		}
	}

	/**
	 * This method loads the first field of the first row returned by the query.
	 *
	 * @return string|null  The value returned in the query or null if the query failed.
	 *
	 * @throws  \RuntimeException
	 */
	public function loadResult( ) {
		$ret	=	null;

		// Execute the query and get the result set cursor.
		if ( ! ( $cursor = $this->execute() ) ) {
			return null;
		}

		// Get the first row from the result set as an array.
		if ($row	=	$this->fetchArray( $cursor ) ) {
			$ret	=	$row[0];
		}

		// Free up system resources and return.
		$this->freeResult( $cursor );

		return $ret;
	}

	/**
	 * Internal function to replace a null result by an empty array
	 *
	 * @param  array|null  $resultArray  The array that may be a null on some systems
	 * @return array
	 */
	public function & _nullToArray( &$resultArray ) {
		if ( $resultArray === null ) {
			$resultArray	=	array();
		}
		return $resultArray;
	}

	/**
	 * Load an array of single field results into an array
	 * @param   int  $offset  The row offset to use to build the result array
	 * @return  array         The array with the result (empty in case of error)
	 *
	 * @throws  \RuntimeException
	 */
	public function loadResultArray( $offset = 0 ) {
		$array = array();

		// Execute the query and get the result set cursor.
		if ( ! ( $cursor	=	$this->execute() ) ) {
			return array();
		}

		// Get all of the rows from the result set as arrays.
		while ( $row = $this->fetchArray( $cursor ) ) {
			$array[]		=	$row[$offset];
		}

		// Free up system resources and return.
		$this->freeResult( $cursor );

		return $array;
	}

	/**
	 * Fetch a result row as an associative array
	 *
	 * @return array
	 *
	 * @throws  \RuntimeException
	 */
	public function loadAssoc( ) {
		if ( ! ( $cursor = $this->execute() ) ) {
			$result	=	null;
		} else {
			$result		=	$this->fetchAssoc( $cursor );
			if ( ! $result ) {
				$result	=	null;
			}
			$this->freeResult( $cursor );
		}

		return $result;
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
		if ( ! ( $cursor = $this->execute() ) ) {
			return null;
		}
		$array						=	array();
		while ( is_array( $row = $this->fetchAssoc( $cursor ) ) ) {
			$value					=	( $column ? ( isset( $row[$column] ) ? $row[$column] : $row ) : $row );

			if ( $key ) {
				$array[$row[$key]]	=	$value;		//  $row->key is not an object, but an array
			} else {
				$array[]			=	$value;
			}
		}
		$this->freeResult( $cursor );

		return $array;
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
		$cursor			=	$this->execute();

		if ( ! $cursor ) {
			return false;
		}

		if ( $object != null ) {
			$array			=	$this->fetchAssoc( $cursor );
			$this->freeResult( $cursor );

			if ( is_array( $array ) ) {

				foreach ( get_object_vars( $object ) as $k => $v) {
					if( substr( $k, 0, 1 ) != '_' ) {
						if ( array_key_exists( $k, $array ) ) {
							$object->$k		=	$array[$k];
						}
					}
				}
				return true;
			}
		} else {
			$object		=	$this->fetchObject( $cursor );
			$this->freeResult( $cursor );

			if ( is_object( $object ) ) {
				return true;
			} else {
				$object = null;
			}
		}

		return false;
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
		if ( ! ( $cursor = $this->execute() ) ) {
			return null;
		}
		$array													=	array();
		if ( ! $key ) {
			while ( is_object( $row = $this->fetchObject( $cursor, $className, $ctor_params ) ) ) {
				$array[]										=	$row;
			}
		} elseif ( is_array( $key ) ) {
			if ( count( $key ) == 2 ) {
				list( $ka, $kb )								=	$key;
				while ( is_object( $row = $this->fetchObject( $cursor, $className, $ctor_params ) ) ) {
					$array[$row->$ka][$row->$kb]				=	$row;
				}
			} elseif ( count( $key ) == 3 ) {
				list( $ka, $kb, $kc )							=	$key;
				while ( is_object( $row = $this->fetchObject( $cursor, $className, $ctor_params ) ) ) {
					$array[$row->$ka][$row->$kb][$row->$kc]		=	$row;
				}
			}
		} elseif ( $lowerCaseIndex ) {
			while ( is_object( $row = $this->fetchObject( $cursor, $className, $ctor_params ) ) ) {
				$array[strtolower($row->$key)]					=	$row;
			}
		} else {
			while ( is_object( $row = $this->fetchObject( $cursor, $className, $ctor_params ) ) ) {
				$array[$row->$key]								=	$row;
			}
		}
		$this->freeResult( $cursor );
		return $array;
	}

	/**
	 * Gets the first row of the result set from the database query as an array
	 *
	 * @return  array|null  The first row of the query or NULL if query failed
	 *
	 * @throws  \RuntimeException
	 */
	public function loadRow( ) {
		$ret		=	null;

		if ( !( $cursor = $this->execute() ) ) {
			return null;
		}

		// Get the first row from the result set as an array.
		if ( $row = $this->fetchArray( $cursor ) ) {
			$ret	=	$row;
		}

		$this->freeResult( $cursor );

		return $ret;
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
		$array		=	array();

		if ( ! ( $cursor = $this->execute() ) ) {
			return array();
		}

		// Get all of the rows from the result set as arrays.
		while ( $row = $this->fetchArray( $cursor ) ) {
			if ( $key !== null ) {
				$array[$row[$key]]	=	$row;
			} else {
				$array[]			=	$row;
			}
		}

		$this->freeResult($cursor);

		return $array;
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
			$fields[] = $this->nameQuote($k);
			$values[] = $this->quote($v);
		}

		$statement = 'INSERT INTO ' . $this->nameQuote($table) . ' (%s) VALUES (%s)';

		$this->setQuery(sprintf($statement, implode(',', $fields), implode(',', $values)));

		if ( ! $this->execute() ) {
			return false;
		}

		// Update the primary key if it exists.
		$id = $this->insertid();

		if ( $keyName && $id && is_string( $keyName ) ) {
			$object->$keyName	=	$id;
		}

		return true;
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
		// return $this->_db->updateObject( $table, $object, $keyName, $updateNulls );
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
		if ( $this->errorNum != 0 ) {
			/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
			return \CBLib\Language\CBTxt::T( 'DATABASEDRIVER_FUNCTION_FAILED_WITH_ERROR_NUMBER_ERRNUM_ERRMSG', 'Database function failed with error number [ERRNUM]', array( '[ERRNUM]' => $this->errorNum ) )
			. '<br /><span style="color: red;">'
			. $this->errorMsg
			. '</span>'
			. ( $showSQL ? "<br />SQL = <pre>$this->sql</pre>" : '');
		}
		else
		{
			/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
			return \CBLib\Language\CBTxt::T( 'DATABASEDRIVER_DB_FUNCTION_REPORTED_NO_ERROR', 'Database function reports no errors' );
		}
	}

	/**
	 * Returns the insert_id() from Mysql
	 *
	 * @return int
	 */
	public function insertid( ) {
		if ( $this->connection instanceof \mysqli ) {
			return mysqli_insert_id( $this->connection );
		} else {
			return mysql_insert_id( $this->connection );
		}
	}

	/**
	 * Returns the version of MySQL
	 *
	 * @return string
	 */
	public function getVersion( ) {
		if ( $this->connection instanceof \mysqli ) {
			return mysqli_get_server_info( $this->connection );
		} else {
			return mysql_get_server_info( $this->connection );
		}
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
		return $this->count;
	}

	/**
	 * Get the database driver SQL statement log.
	 *
	 * @return  array  SQL statements executed by the database driver.
	 */
	public function getLog()
	{
		return $this->log;
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
	public function renameTable( $oldTable, $newTable, /** @noinspection PhpUnusedParameterInspection */ $backup = null, /** @noinspection PhpUnusedParameterInspection */ $prefix = null )
	{
		$this->query( 'RENAME TABLE ' . $this->nameQuote( $oldTable ) . ' TO ' . $this->nameQuote( $newTable ) );

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
		$this->query( 'DROP TABLE ' . ( $ifExists ? 'IF EXISTS ' : '') . $this->nameQuote( $tableName ) );

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
	public function lockTable( $tableName )
	{
		$this->query( 'LOCK TABLES ' . $this->nameQuote( $tableName ) . ' WRITE' );

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
		if ( ! $toSavepoint || $this->transactionDepth <= 1 )
		{
			if ( $this->query( 'COMMIT' ) ) {
				$this->transactionDepth		=	0;
			}

			return $this;
		}

		$this->transactionDepth--;

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

		return $this;
	}

	/**
	 * Method to fetch a row from the result set cursor as an array.
	 *
	 * @param   \mysqli_result|\resource  $cursor  The optional result set cursor from which to fetch the row.
	 * @return  array|boolean|null                 Either the next row from the result set or false if there are no more rows.
	 */
	protected function fetchArray( $cursor = null )
	{
		$cursor		=	$cursor ? $cursor : $this->cursor;

		if ( is_object( $cursor ) && ( $cursor instanceof \mysqli_result ) ) {
			return mysqli_fetch_row( $cursor );
		} else {
			return mysql_fetch_row( $cursor );
		}
	}

	/**
	 * Method to fetch a row from the result set cursor as an associative array.
	 *
	 * @param   \mysqli_result|\resource  $cursor  The optional result set cursor from which to fetch the row.
	 * @return  array|boolean|null                 Either the next row from the result set or false if there are no more rows.
	 */
	protected function fetchAssoc( $cursor = null )
	{
		$cursor		=	$cursor ? $cursor : $this->cursor;

		if ( is_object( $cursor ) && ( $cursor instanceof \mysqli_result ) ) {
			return mysqli_fetch_assoc( $cursor );
		} else {
			return mysql_fetch_assoc( $cursor );
		}
	}

	/**
	 * Method to fetch a row from the result set cursor as an object.
	 *
	 * @param   \mysqli_result|\resource   $cursor       The optional result set cursor from which to fetch the row.
	 * @param   string                     $className    The class name to use for the returned row object.
	 * @param   array                      $ctor_params  The parameters for class creation
	 * @return  array|boolean|null                       Either the next row from the result set or false if there are no more rows.
	 */
	protected function fetchObject( $cursor = null, $className = null, $ctor_params = null )
	{
		$cursor		=	$cursor ? $cursor : $this->cursor;

		if ( is_object( $cursor ) && ( $cursor instanceof \mysqli_result ) ) {
			if ( $className === null ) {
				return mysqli_fetch_object( $cursor );
			} else {
				return mysqli_fetch_object( $cursor, $className, $ctor_params );
			}
		} else {
			// MySql:
			if ( $className === null ) {
				return mysql_fetch_object( $cursor );
			} else {
				return mysql_fetch_object( $cursor, $className, $ctor_params );
			}
		}

	}

	/**
	 * Method to free up the memory used for the result set.
	 *
	 * @param   \mysqli_result|\resource  $cursor  The optional result set cursor from which to fetch the row.
	 * @return  void
	 */
	protected function freeResult( $cursor = null )
	{
		$cur		=	$cursor ? $cursor : $this->cursor;

		if ( is_object( $cur ) && ( $cur instanceof \mysqli_result ) ) {
			mysqli_free_result( $cur );
		} else {
			mysql_free_result( $cur );
		}

		if ( ( ! $cursor ) || ( $cursor === $this->cursor ) ) {
			$this->cursor = null;
		}
	}
}
