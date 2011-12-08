<?php
/**
 * @version		$Id: postgresql.php 13547 2009-11-20 14:18:01Z louis $
 * @package		Joomla.Framework
 * @subpackage	Database
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;
defined('JPATH_PLATFORM') or die;
/**
 * PostgreSQL database driver
 *
 * @package		Joomla.Framework
 * @subpackage	Database
 * @since		12.1
 */
class JDatabasePostgreSQL extends JDatabase
{
	/**
	 * The database driver name
	 *
	 * @var string
	 */
	var $name			= 'postgresql';

	/**
	 *  The null/zero date string
	 *
	 * @var string
	 */
	var $_nullDate		= 'epoch';

	/**
	 * Quote for named objects
	 *
	 * @var string
	 */
	var $_nameQuote		= '"';

	/**
	 * Operator used for concatenation
	 *
	 * @var string
	 */
	var $_concat_operator	= '||';

	/**
	 * ID returned by last insert statement
	 *
	 * @var integer
	 */
	var $_insert_id		= 0;


	/**
	 * Database object constructor
	 *
	 * @access	public
	 * @param	array	List of options used to configure the connection
	 * @since	12.1
	 * @see		JDatabase
	 */
	function __construct( $options )
	{
		$host		= array_key_exists('host', $options)	? $options['host']		: 'localhost';
		$user		= array_key_exists('user', $options)	? $options['user']		: '';
		$password	= array_key_exists('password',$options)	? $options['password']	: '';
		$database	= array_key_exists('database',$options)	? $options['database']	: '';
		$prefix		= array_key_exists('prefix', $options)	? $options['prefix']	: 'jos_';

		// perform a number of fatality checks, then return gracefully
		if (!function_exists( 'pg_connect' )) {
			$this->_errorNum = 1;
			$this->_errorMsg = 'The PostgreSQL adapter "pg" is not available.';
			return;
		}

		// connect to the server
		if (!($this->_resource = @pg_connect( "host=$host user=$user password=$password" ))) {
			$this->_errorNum = 2;
			$this->_errorMsg = 'Could not connect to PostgreSQL';
			return;
		}

		// finalize initialization
		parent::__construct($options);
	}

	/**
	 * Database object destructor
	 *
	 * @return boolean
	 * @since 12.1
	 */
	function __destruct()
	{
		$return = false;
		if (is_resource($this->_resource)) {
			$return = pg_close($this->_resource);
		}
		return $return;
	}

	/**
	 * Test to see if the PostgreSQL connector is available
	 *
	 * @static
	 * @access public
	 * @return boolean  True on success, false otherwise.
	 */
	function test()
	{
		return (function_exists( 'pg_connect' ));
	}

	/**
	 * Determines if the connection to the server is active.
	 *
	 * @access	public
	 * @return	boolean
	 * @since	12.1
	 */
	function connected()
	{
		if(is_resource($this->_resource)) {
			return pg_ping($this->_resource);
		}
		return false;
	}

	/**
	 * Determines UTF support
	 *
	 * @access	public
	 * @return boolean True - UTF is supported
	 */
	function hasUTF()
	{
		return true;
	}

	/**
	 * Custom settings for UTF support
	 *
	 * @access	public
	 */
	function setUTF()
	{
		pg_set_client_encoding( $this->_resource, 'UTF8' );
	}

	/**
	 * Get a database escaped string
	 *
	 * @param	string	The string to be escaped
	 * @param	boolean	Optional parameter to provide extra escaping
	 * @return	string
	 * @access	public
	 * @abstract
	 */
	function getEscaped( $text, $extra = false )
	{
		$result = pg_escape_string( $this->_resource, $text );
		if ($extra) {
			$result = addcslashes( $result, '%_' );
		}

		return $result;
	}

	/**
	 * Generate SQL command for getting string position
	 *
	 * @access public
	 * @param string The string being sought
	 * @param string The string/column being searched
	 * @return string The resulting SQL
	 */
	function stringPositionSQL($substring, $string)
	{
		$sql = "POSITION($substring, $string)";

		return $sql;
	}

	/**
	 * Generate SQL command for returning random value
	 *
	 * @access public
	 * @return string The resulting SQL
	 */
	function stringRandomSQL()
	{
		return "RANDOM()";
	}

	/**
	 * Create database
	 *
	 * @access public
	 * @param string The database name
	 * @param bool Whether or not to create with UTF support (only here for function signature compatibility)
	 * @return string Database creation string
	 */
	function createDatabase($DBname, $DButfSupport)
	{
		$sql = "CREATE DATABASE ".$this->nameQuote($DBname)." ENCODING UTF8";

		$this->setQuery($sql);
		$this->query();
		$result = $this->getErrorNum();

		if ($result != 0) {
			return false;
		}

		return true;
	}

	/**
	 * Rename a database table
	 *
	 * @param string The old table name
	 * @param string The new table name
	 */
	function renameTable($oldTable, $newTable)
	{
		$query = "ALTER TABLE ".$oldTable." RENAME TO ".$newTable;
		$db->setQuery($query);
		$db->query();

		$result = $db->getErrorNum();

		if ($result != 0) {
			return false;
		}

		return true;
	}

	/**
	 * Execute the query
	 *
	 * @access	public
	 * @return mixed A database resource if successful, FALSE if not.
	 */
	function query()
	{
		if (!is_resource($this->_resource)) {
			return false;
		}

		// Take a local copy so that we don't modify the original query and cause issues later
		$sql = $this->_sql;
		if ($this->_limit > 0 || $this->_offset > 0) {
			$sql .= ' LIMIT '.$this->_limit.' OFFSET '.$this->_offset;
		}
		if ($this->_debug) {
			$this->_ticker++;
			$this->_log[] = $sql;
		}
		$this->_errorNum = 0;
		$this->_errorMsg = '';

		$this->_cursor = pg_query( $this->_resource, $sql );

		if (!$this->_cursor) {
			$this->_errorNum = pg_result_error_field( $this->_cursor, PGSQL_DIAG_SQLSTATE ) . ' ';
			$this->_errorMsg = pg_result_error_field( $this->_cursor, PGSQL_DIAG_MESSAGE_PRIMARY )." SQL=$sql <br />";
			if ($this->_debug) {
				JError::raiseError(500, 'JDatabasePostgreSQL::query: '.$this->_errorNum.' - '.$this->_errorMsg );
			}
			return false;
		}
		return $this->_cursor;
	}

	/**
	 * Description
	 *
	 * @access	public
	 * @return int The number of affected rows in the previous operation
	 * @since 12.1
	 */
	function getAffectedRows()
	{
		return pg_affected_rows( $this->_resource );
	}

	/**
	 * Execute a batch query
	 *
	 * @access	public
	 * @return mixed A database resource if successful, FALSE if not.
	 */
	function queryBatch( $abort_on_error=true, $p_transaction_safe = false)
	{
		$this->_errorNum = 0;
		$this->_errorMsg = '';
		if ($p_transaction_safe) {
			$this->_sql = rtrim($this->_sql, "; \t\r\n\0");
			$this->_sql = 'START TRANSACTION;' . $this->_sql . '; COMMIT;';
		}
		$query_split = $this->splitSql($this->_sql);
		$error = 0;
		foreach ($query_split as $command_line) {
			$command_line = trim( $command_line );
			if ($command_line != '') {
				$this->_cursor = pg_query( $this->_resource, $command_line );
				if ($this->_debug) {
					$this->_ticker++;
					$this->_log[] = $command_line;
				}
				if (!$this->_cursor) {
					$error = 1;
					$this->_errorNum .= pg_result_error_field( $this->_cursor, PGSQL_DIAG_SQLSTATE ) . ' ';
					$this->_errorMsg .= pg_result_error_field( $this->_cursor, PGSQL_DIAG_MESSAGE_PRIMARY ).
										" SQL=$command_line <br />";
					if ($abort_on_error) {
						return $this->_cursor;
					}
				}
			}
		}
		return $error ? false : true;
	}

	/**
	 * Diagnostic function
	 *
	 * @access	public
	 * @return	string
	 */
	function explain()
	{
		$temp = $this->_sql;
		$this->_sql = "EXPLAIN $this->_sql";

		if (!($cur = $this->query())) {
			return null;
		}
		$first = true;

		$buffer = '<table id="explain-sql">';
		$buffer .= '<thead><tr><td colspan="99">'.$this->getQuery().'</td></tr>';
		while ($row = pg_fetch_assoc( $cur )) {
			if ($first) {
				$buffer .= '<tr>';
				foreach ($row as $k=>$v) {
					$buffer .= '<th>'.$k.'</th>';
				}
				$buffer .= '</tr>';
				$first = false;
			}
			$buffer .= '</thead><tbody><tr>';
			foreach ($row as $k=>$v) {
				$buffer .= '<td>'.$v.'</td>';
			}
			$buffer .= '</tr>';
		}
		$buffer .= '</tbody></table>';
		pg_free_result( $cur );

		$this->_sql = $temp;

		return $buffer;
	}

	/**
	 * Description
	 *
	 * @access	public
	 * @return int The number of rows returned from the most recent query.
	 */
	function getNumRows( $cur=null )
	{
		return pg_num_rows( $cur ? $cur : $this->_cursor );
	}

	/**
	 * This method loads the first field of the first row returned by the query.
	 *
	 * @access	public
	 * @return The value returned in the query or null if the query failed.
	 */
	function loadResult()
	{
		if (!($cur = $this->query())) {
			return null;
		}
		$ret = null;
		if ($row = pg_fetch_row( $cur )) {
			$ret = $row[0];
		}
		pg_free_result( $cur );
		return $ret;
	}

	/**
	 * Load an array of single field results into an array
	 *
	 * @access	public
	 */
	function loadResultArray($numinarray = 0)
	{
		if (!($cur = $this->query())) {
			return null;
		}
		$array = array();
		while ($row = pg_fetch_row( $cur )) {
			$array[] = $row[$numinarray];
		}
		pg_free_result( $cur );
		return $array;
	}

	/**
	 * Fetch a result row as an associative array
	 *
	 * @access	public
	 * @return array
	 */
	function loadAssoc()
	{
		if (!($cur = $this->query())) {
			return null;
		}
		$ret = null;
		if ($array = pg_fetch_assoc( $cur )) {
			$ret = $array;
		}
		pg_free_result( $cur );
		return $ret;
	}

	/**
	 * Load a assoc list of database rows
	 *
	 * @access	public
	 * @param string The field name of a primary key
	 * @return array If <var>key</var> is empty as sequential list of returned records.
	 */
	function loadAssocList( $key='' )
	{
		if (!($cur = $this->query())) {
			return null;
		}
		$array = array();
		while ($row = pg_fetch_assoc( $cur )) {
			if ($key) {
				$array[$row[$key]] = $row;
			} else {
				$array[] = $row;
			}
		}
		pg_free_result( $cur );
		return $array;
	}

	/**
	 * This global function loads the first row of a query into an object
	 *
	 * @access	public
	 * @return 	object
	 */
	function loadObject( )
	{
		if (!($cur = $this->query())) {
			return null;
		}
		$ret = null;
		if ($object = pg_fetch_object( $cur )) {
			$ret = $object;
		}
		pg_free_result( $cur );
		return $ret;
	}

	/**
	 * Load a list of database objects
	 *
	 * If <var>key</var> is not empty then the returned array is indexed by the value
	 * the database key.  Returns <var>null</var> if the query fails.
	 *
	 * @access	public
	 * @param string The field name of a primary key
	 * @return array If <var>key</var> is empty as sequential list of returned records.
	 */
	function loadObjectList( $key='' )
	{
		if (!($cur = $this->query())) {
			return null;
		}
		$array = array();
		while ($row = pg_fetch_object( $cur )) {
			if ($key) {
				$array[$row->$key] = $row;
			} else {
				$array[] = $row;
			}
		}
		pg_free_result( $cur );
		return $array;
	}

	/**
	 * Description
	 *
	 * @access	public
	 * @return The first row of the query.
	 */
	function loadRow()
	{
		if (!($cur = $this->query())) {
			return null;
		}
		$ret = null;
		if ($row = pg_fetch_row( $cur )) {
			$ret = $row;
		}
		pg_free_result( $cur );
		return $ret;
	}

	/**
	 * Load a list of database rows (numeric column indexing)
	 *
	 * @access public
	 * @param string The field name of a primary key
	 * @return array If <var>key</var> is empty as sequential list of returned records.
	 * If <var>key</var> is not empty then the returned array is indexed by the value
	 * the database key.  Returns <var>null</var> if the query fails.
	 */
	function loadRowList( $key=null )
	{
		if (!($cur = $this->query())) {
			return null;
		}
		$array = array();
		while ($row = pg_fetch_row( $cur )) {
			if ($key !== null) {
				$array[$row[$key]] = $row;
			} else {
				$array[] = $row;
			}
		}
		pg_free_result( $cur );
		return $array;
	}

	/**
	 * Inserts a row into a table based on an objects properties
	 *
	 * @access	public
	 * @param	string	The name of the table
	 * @param	object	An object whose properties match table fields
	 * @param	string	The name of the primary key. If provided the object property is updated.
	 */
	function insertObject( $table, &$object, $keyName = NULL )
	{
		$fmtsql = 'INSERT INTO '.$table.' ( %s ) VALUES ( %s ) ';
		$verParts = explode( '.', $this->getVersion() );

		$fields = array();
		foreach (get_object_vars( $object ) as $k => $v) {
			if (is_array($v) or is_object($v) or $v === NULL) {
				continue;
			}
			if ($k[0] == '_') { // internal field
				continue;
			}

			$fields[] = $this->nameQuote( $k );
			$values[] = $this->isQuoted( $k ) ? $this->Quote( $v ) : (int) $v;
		}

		if ( !in_array($this->nameQuote($keyName), $fields) ) {
			if ( $verParts[0] > 8 || ($verParts[0] == 8 && $verParts[1] >= 2) ) {
				$fmtsql .= "RETURNING $keyName AS ".$this->nameQuote('id').";";
			} else {
				$fmtsql .= ";
                                	SELECT $keyName AS \"id\" FROM $table;";
			}
		}
		$this->setQuery( sprintf( $fmtsql, implode( ",", $fields ) ,  implode( ",", $values ) ) );

		$result = $this->query();

		if (!$result) {
			return false;
		}

		if ( $results[0][0]['id'] ) {
			$this->_insert_id = $results[0][0]['id'];
		}

		if ($keyName && $id) {
			$object->$keyName = $this->_insert_id;
		}
		return true;
	}

	/**
	 * Description
	 *
	 * @access public
	 * @param [type] $updateNulls
	 */
	function updateObject( $table, &$object, $keyName, $updateNulls=true )
	{
		$fmtsql = 'UPDATE '.$table.' SET %s WHERE %s';
		$tmp = array();
		foreach (get_object_vars( $object ) as $k => $v) {
			if( is_array($v) or is_object($v) or $k[0] == '_' ) { // internal or NA field
				continue;
			}
			if( $k == $keyName ) { // PK not to be updated
				$where = $keyName . '=' . $this->Quote( $v );
				continue;
			}
			if ($v === null) {
				if ($updateNulls) {
					$val = 'NULL';
				} else {
					continue;
				}
			} else {
				$val = $this->isQuoted( $k ) ? $this->Quote( $v ) : (int) $v;
			}
			$tmp[] = $this->nameQuote( $k ) . '=' . $val;
		}
		$this->setQuery( sprintf( $fmtsql, implode( ",", $tmp ) , $where ) );
		return $this->query();
	}

	/**
	 * Description
	 *
	 * @access public
	 */
	function insertid()
	{
		return $this->_insert_id;
	}

	/**
	 * Description
	 *
	 * @access public
	 */
	function getVersion()
	{
		$version = pg_version( $this->_resource );
		return $version['server'];
	}

	/**
	 * Assumes database collation in use by sampling one text field in one table
	 *
	 * @access	public
	 * @return string Collation in use
	 */
	function getCollation ()
	{
		if ( $this->hasUTF() ) {
			$cur = $this->query( 'SHOW LC_COLLATE;' );
			$coll = pg_fetch_row( $cur, 0 );
			return $coll['lc_ctype'];
		} else {
			return "N/A";
		}
	}

	/**
	 * Description
	 *
	 * @access	public
	 * @return array A list of all the tables in the database
	 */
	function getTableList()
	{
		$this->setQuery( "select tablename from pg_tables where schemaname='public';" );
		return $this->loadResultArray();
	}
	/**
	 * Selects the database, but redundant for PostgreSQL
	 *
	 * @return bool Always true
	 */
	function select($database=null)
	{
		return true;
	}

	/**
	 * Retrieves information about the given tables
	 *
	 * @access	public
	 * @param 	array|string 	A table name or a list of table names
	 * @param	boolean			Only return field types, default true
	 * @return	array An array of fields by table
	 */
	function getTableFields( $tables, $typeonly = true )
	{
		settype($tables, 'array'); //force to array
		$result = array();

		foreach ($tables as $tblval) {
			$this->setQuery( 'SELECT column_name FROM information_schema.columns WHERE table_name = '.$tblval.';' );
			$fields = $this->loadObjectList();

			if ($typeonly) {
				foreach ($fields as $field) {
					$result[$tblval][$field->Field] = preg_replace("/[(0-9)]/",'', $field->Type );
				}
			} else {
				foreach ($fields as $field) {
					$result[$tblval][$field->Field] = $field;
				}
			}
		}

		return $result;
	}
}
