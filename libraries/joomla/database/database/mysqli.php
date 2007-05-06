<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Database
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * MySQLi database driver
 *
 * @package		Joomla.Framework
 * @subpackage	Database
 * @since		1.0
 */
class JDatabaseMySQLi extends JDatabase
{
	/** @var string The database driver name */
	var $name			= 'mysqli';
	/** @var string The null/zero date string */
	var $_nullDate		= '0000-00-00 00:00:00';
	/** @var string Quote for named objects */
	var $_nameQuote		= '`';

	/**
	* Database object constructor
	*
	* @access	public
	* @param	array	List of options used to configure the connection
	* @since	1.5
	* @see		JDatabase
	*/
	function __construct( $options )
	{
		$host		= array_key_exists('host', $options)	? $options['host']		: 'localhost';
		$user		= array_key_exists('user', $options)	? $options['user']		: '';
		$password	= array_key_exists('password',$options)	? $options['password']	: '';
		$database	= array_key_exists('database',$options)	? $options['database']	: '';
		$prefix		= array_key_exists('prefix', $options)	? $options['prefix']	: 'jos_';
		$select		= array_key_exists('select', $options)	? $options['select']	: true;

		// Unlike mysql_connect(), mysqli_connect() takes the port and socket
		// as separate arguments. Therefore, we have to extract them from the
		// host string.
		$port	= NULL;
		$socket	= NULL;
		$targetSlot = substr( strstr( $host, ":" ), 1 );
		if (!empty( $targetSlot )) {
			// Get the port number or socket name
			if (is_numeric( $targetSlot ))
				$port	= $targetSlot;
			else
				$socket	= $targetSlot;

			// Extract the host name only
			$host = substr( $host, 0, strlen( $host ) - (strlen( $targetSlot ) + 1) );
			// This will take care of the following notation: ":3306"
			if($host == '')
				$host = 'localhost';
		}

		// perform a number of fatality checks, then return gracefully
		if (!function_exists( 'mysqli_connect' )) {
			$this->_errorNum = 1;
			$this->_errorMsg = 'The MySQL adapter "mysqli" is not available.';
			return;
		}

		// connect to the server
		if (!($this->_resource = @mysqli_connect($host, $user, $password, NULL, $port, $socket))) {
			$this->_errorNum = 2;
			$this->_errorMsg = 'Could not connect to MySQL';
			return;
		}

		// finalize initialization
		parent::__construct( $options );

		// select the database
		if ( $select )
		{
			$this->select($database);
		}
	}

	/**
	 * Database object destructor
	 *
	 * @return boolean
	 * @since 1.5
	 */
	function __destruct()
	{
		// mysqli does not persist connections, so let's make sure it's closed
		return @mysqli_close($this->_resource);
	}

	/**
	 * Test to see if the MySQLi connector is available
	 *
	 * @static
	 * @access public
	 * @return boolean  True on success, false otherwise.
	 */
	function test()
	{
		return (function_exists( 'mysqli_connect' ));
	}

	function _parseHost( $host )
	{

	}

	/**
	 * Determines if the connection to the server is active.
	 *
	 * @access	public
	 * @return	boolean
	 * @since	1.5
	 */
	function connected()
	{
		return $this->_resource->ping();
	}

	/**
	 * Select a database for use
	 *
	 * @access	public
	 * @param	string $database
	 * @return	boolean True if the database has been successfully selected
	 * @since	1.5
	 */
	function select($database)
	{
		if ( ! $database )
		{
			return false;
		}

		if ( !mysqli_select_db($this->_resource, $database)) {
			$this->_errorNum = 3;
			$this->_errorMsg = 'Could not connect to database';
			return false;
		}

		// if running mysql 5, set sql-mode to mysql40 - thereby circumventing strict mode problems
		if ( strpos( $this->getVersion(), '5' ) === 0 ) {
			$this->setQuery( "SET sql_mode = 'MYSQL40'" );
			$this->query();
		}

		return true;
	}


	/**
	 * Determines UTF support
	 * @return boolean True - UTF is supported
	 */
	function hasUTF()
	{
		$verParts = explode( '.', $this->getVersion() );
		return ($verParts[0] == 5 || ($verParts[0] == 4 && $verParts[1] == 1 && (int)$verParts[2] >= 2));
	}

	/**
	 * Custom settings for UTF support
	 */
	function setUTF()
	{
		mysqli_query( $this->_resource, "SET NAMES 'utf8'" );
	}

	/**
	* Get a database escaped string
	* @return string
	*/
	function getEscaped( $text )
	{
		return mysqli_real_escape_string( $this->_resource, $text );
	}

	/**
	* Execute the query
	* @return mixed A database resource if successful, FALSE if not.
	*/
	function query()
	{
		if (!is_object($this->_resource)) {
			return false;
		}

		if ($this->_limit > 0 || $this->_offset > 0) {
			$this->_sql .= ' LIMIT '.$this->_offset.', '.$this->_limit;
		}
		if ($this->_debug) {
			$this->_ticker++;
			$this->_log[] = $this->_sql;
		}
		$this->_errorNum = 0;
		$this->_errorMsg = '';
		$this->_cursor = mysqli_query( $this->_resource, $this->_sql );

		if (!$this->_cursor)
		{
			$this->_errorNum = mysqli_errno( $this->_resource );
			$this->_errorMsg = mysqli_error( $this->_resource ) . " SQL=$this->_sql";

			if ($this->_debug) {
				JError::raiseError('joomla.database:'.$this->_errorNum, 'JDatabaseMySQLi::query: '.$this->_errorMsg );
			}
			return false;
		}
		return $this->_cursor;
	}

	/**
	 * @return int The number of affected rows in the previous operation
	 * @since 1.0.5
	 */
	function getAffectedRows()
	{
		return mysqli_affected_rows( $this->_resource );
	}

   /**
	* Execute a batch query
	* @return mixed A database resource if successful, FALSE if not.
	*/
	function queryBatch( $abort_on_error=true, $p_transaction_safe = false)
	{
		$this->_errorNum = 0;
		$this->_errorMsg = '';
		if ($p_transaction_safe) {
			$si = mysqli_get_server_info();
			preg_match_all( "/(\d+)\.(\d+)\.(\d+)/i", $si, $m );
			if ($m[1] >= 4) {
				$this->_sql = 'START TRANSACTION;' . $this->_sql . '; COMMIT;';
			} else if ($m[2] >= 23 && $m[3] >= 19) {
				$this->_sql = 'BEGIN WORK;' . $this->_sql . '; COMMIT;';
			} else if ($m[2] >= 23 && $m[3] >= 17) {
				$this->_sql = 'BEGIN;' . $this->_sql . '; COMMIT;';
			}
		}
		$query_split = preg_split ("/[;]+/", $this->_sql);
		$error = 0;
		foreach ($query_split as $command_line) {
			$command_line = trim( $command_line );
			if ($command_line != '') {
				$this->_cursor = mysqli_query( $command_line, $this->_resource );
				if (!$this->_cursor) {
					$error = 1;
					$this->_errorNum .= mysqli_errno( $this->_resource ) . ' ';
					$this->_errorMsg .= mysqli_error( $this->_resource )." SQL=$command_line <br />";
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
	*/
	function explain()
	{
		$temp = $this->_sql;
		$this->_sql = "EXPLAIN $this->_sql";
		$this->query();

		if (!($cur = $this->query())) {
			return null;
		}
		$first = true;

		$buffer = '<table id="explain-sql">';
		$buffer .= '<thead><tr><td colspan="99">'.$this->getQuery().'</td></tr>';
		while ($row = mysqli_fetch_assoc( $cur )) {
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
		mysqli_free_result( $cur );

		$this->_sql = $temp;

		return $buffer;
	}

	/**
	* @return int The number of rows returned from the most recent query.
	*/
	function getNumRows( $cur=null )
	{
		return mysqli_num_rows( $cur ? $cur : $this->_cursor );
	}

	/**
	* This method loads the first field of the first row returned by the query.
	*
	* @return The value returned in the query or null if the query failed.
	*/
	function loadResult()
	{
		if (!($cur = $this->query())) {
			return null;
		}
		$ret = null;
		if ($row = mysqli_fetch_row( $cur )) {
			$ret = $row[0];
		}
		mysqli_free_result( $cur );
		return $ret;
	}
	/**
	* Load an array of single field results into an array
	*/
	function loadResultArray($numinarray = 0)
	{
		if (!($cur = $this->query())) {
			return null;
		}
		$array = array();
		while ($row = mysqli_fetch_row( $cur )) {
			$array[] = $row[$numinarray];
		}
		mysqli_free_result( $cur );
		return $array;
	}

	/**
	* Fetch a result row as an associative array
	*
	* return array
	*/
	function loadAssoc()
	{
		if (!($cur = $this->query())) {
			return null;
		}
		$ret = null;
		if ($array = mysqli_fetch_assoc( $cur )) {
			$ret = $array;
		}
		mysqli_free_result( $cur );
		return $ret;
	}

	/**
	* Load a assoc list of database rows
	* @param string The field name of a primary key
	* @return array If <var>key</var> is empty as sequential list of returned records.
	*/
	function loadAssocList( $key='' )
	{
		if (!($cur = $this->query())) {
			return null;
		}
		$array = array();
		while ($row = mysqli_fetch_assoc( $cur )) {
			if ($key) {
				$array[$row[$key]] = $row;
			} else {
				$array[] = $row;
			}
		}
		mysqli_free_result( $cur );
		return $array;
	}
	/**
	* This global function loads the first row of a query into an object
	*
	* return object
	*/
	function loadObject( )
	{
		if (!($cur = $this->query())) {
			return null;
		}
		$ret = null;
		if ($object = mysqli_fetch_object( $cur )) {
			$ret = $object;
		}
		mysqli_free_result( $cur );
		return $ret;
	}
	/**
	* Load a list of database objects
	* @param string The field name of a primary key
	* @return array If <var>key</var> is empty as sequential list of returned records.
	* If <var>key</var> is not empty then the returned array is indexed by the value
	* the database key.  Returns <var>null</var> if the query fails.
	*/
	function loadObjectList( $key='' )
	{
		if (!($cur = $this->query())) {
			return null;
		}
		$array = array();
		while ($row = mysqli_fetch_object( $cur )) {
			if ($key) {
				$array[$row->$key] = $row;
			} else {
				$array[] = $row;
			}
		}
		mysqli_free_result( $cur );
		return $array;
	}
	/**
	* @return The first row of the query.
	*/
	function loadRow()
	{
		if (!($cur = $this->query())) {
			return null;
		}
		$ret = null;
		if ($row = mysqli_fetch_row( $cur )) {
			$ret = $row;
		}
		mysqli_free_result( $cur );
		return $ret;
	}
	/**
	* Load a list of database rows (numeric column indexing)
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
		while ($row = mysqli_fetch_row( $cur )) {
			if ($key !== null) {
				$array[$row[$key]] = $row;
			} else {
				$array[] = $row;
			}
		}
		mysqli_free_result( $cur );
		return $array;
	}
	/**
	 * Inserts a row into a table based on an objects properties
	 * @param	string	The name of the table
	 * @param	object	An object whose properties match table fields
	 * @param	string	The name of the primary key. If provided the object property is updated.
	 */
	function insertObject( $table, &$object, $keyName = NULL )
	{
		$fmtsql = "INSERT INTO $table ( %s ) VALUES ( %s ) ";
		$fields = array();
		foreach (get_object_vars( $object ) as $k => $v) {
			if (is_array($v) or is_object($v) or $v === NULL) {
				continue;
			}
			if ($k[0] == '_') { // internal field
				continue;
			}
			$fields[] = $this->nameQuote( $k );;
			$values[] = $this->isQuoted( $k ) ? $this->Quote( $v ) : $v;
		}
		$this->setQuery( sprintf( $fmtsql, implode( ",", $fields ) ,  implode( ",", $values ) ) );
		if (!$this->query()) {
			return false;
		}
		$id = mysqli_insert_id( $this->_resource );
		if ($keyName && $id) {
			$object->$keyName = $id;
		}
		return true;
	}

	/**
	 * Document::db_updateObject()
	 * @param [type] $updateNulls
	 */
	function updateObject( $table, &$object, $keyName, $updateNulls=true )
	{
		$fmtsql = "UPDATE $table SET %s WHERE %s";
		$tmp = array();
		foreach (get_object_vars( $object ) as $k => $v) {
			if( is_array($v) or is_object($v) or $k[0] == '_' ) { // internal or NA field
				continue;
			}
			if( $k == $keyName ) { // PK not to be updated
				$where = $keyName . '=' . $this->Quote( $v );
				continue;
			}
			if ($v === null)
			{
				if ($updateNulls) {
					$val = 'NULL';
				} else {
					continue;
				}
			} else {
				$val = $this->isQuoted( $k ) ? $this->Quote( $v ) : $v;
			}
			$tmp[] = $this->nameQuote( $k ) . '=' . $val;
		}
		$this->setQuery( sprintf( $fmtsql, implode( ",", $tmp ) , $where ) );
		return $this->query();
	}

	function insertid()
	{
		return mysqli_insert_id( $this->_resource );
	}

	/**
	 * Assumes database collation in use by sampling one text field in one table
	 * @return string Collation in use
	 */
	function getCollation ()
	{
		if ( $this->hasUTF() ) {
			$this->setQuery( 'SHOW FULL COLUMNS FROM #__content' );
			$array = $this->loadAssocList();
			return $array['4']['Collation'];
		} else {
			return "N/A (mySQL < 4.1.2)";
		}
	}

	function getVersion()
	{
		return mysqli_get_server_info( $this->_resource );
	}

	/**
	 * @return array A list of all the tables in the database
	 */
	function getTableList()
	{
		$this->setQuery( 'SHOW TABLES' );
		return $this->loadResultArray();
	}
	/**
	 * @param array A list of table names
	 * @return array A list the create SQL for the tables
	 */
	function getTableCreate( $tables )
	{
		$result = array();

		foreach ($tables as $tblval) {
			$this->setQuery( 'SHOW CREATE table ' . $this->getEscaped( $tblval ) );
			$rows = $this->loadRowList();
			foreach ($rows as $row) {
				$result[$tblval] = $row[1];
			}
		}

		return $result;
	}
	/**
	 * @param array A list of table names
	 * @return array An array of fields by table
	 */
	function getTableFields( $tables )
	{
		$result = array();

		foreach ($tables as $tblval) {
			$this->setQuery( 'SHOW FIELDS FROM ' . $tblval );
			$fields = $this->loadObjectList();
			foreach ($fields as $field) {
				$result[$tblval][$field->Field] = preg_replace("/[(0-9)]/",'', $field->Type );
			}
		}

		return $result;
	}
}
