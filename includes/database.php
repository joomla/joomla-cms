<?php
/**
* @version $Id: database.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Database
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

define( 'ADODB_DIR', dirname( __FILE__ ) . '/adodb' );
require_once( ADODB_DIR.'/adodb.inc.php' );

// perform a number of fatality checks, then die gracefully
/* ADODB define (v4.2) */
if (!defined( '_ADODB_LAYER' )) {
	$mosSystemError = 1;
	$basePath = dirname( __FILE__ );
	include $basePath . '/../configuration.php';
	include $basePath . '/../offline.php';
	exit();
}

/**
 * Database connector class
 * @subpackage Database
 * @package Joomla
 */
class database {
	/** @var string Internal variable to hold the query sql */
	var $_sql='';
	/** @var int Internal variable to hold the database error number */
	var $_errorNum=0;
	/** @var string Internal variable to hold the database error message */
	var $_errorMsg='';
	/** @var string Internal variable to hold the prefix used on all database tables */
	var $_table_prefix='';
	/** @var Internal variable to hold the connector resource */
	var $_resource='';
	/** @var Internal variable to hold the last query cursor */
	var $_cursor=null;
	/** @var boolean Debug option */
	var $_debug=0;
	/** @var int The limit for the query */
	var $_limit=0;
	/** @var int The for offset for the limit */
	var $_offset=0;
	/** @var int A counter for the number of queries performed by the object instance */
	var $_ticker=0;
	/** @var array A log of queries */
	var $_log=null;
	/** @var string The null/zero date string */
	var $_nullDate=null;

	/**
	 * Database object constructor
	 * @param string Database host
	 * @param string Database user name
	 * @param string Database user password
	 * @param string Database name
	 * @param string Common prefix for all tables
	 * @param string DB driver name
	 */
	function database( $host='localhost', $user, $pass, $db, $table_prefix='mos_', $driver='', $nullDate=null ) {
		if ($driver == '') {
			$driver = 'mysql';
		}
		$this->_resource =& ADONewConnection( $driver );

		// check database exists
		if (!$this->_resource->PConnect( $host, $user, $pass, $db )) {
			// doesn't exist, check hostname/username/password
			$this->_resource =& ADONewConnection( $driver );
			if ($this->_resource->PConnect( $host, $user, $pass )) {
				$this->_errorNum = 3;
				return;
			} else {
				$this->_errorNum = 2;
				return;
			}
		}
		$this->_table_prefix = $table_prefix;
		$this->_ticker = 0;
		$this->_errorNum = 0;
		$this->_log = array();
		$this->_nullDate = @$GLOBALS['mosConfig_zero_date'];
	}
	/**
	 * @param int
	 */
	function debug( $level ) {
		//$this->_resource->debug = intval( $level );
		$this->_debug = intval( $level );
	}
	/**
	 * @return int The error number for the most recent query
	 */
	function getErrorNum() {
		return $this->_errorNum;
	}
	/**
	 * @return string The error message for the most recent query
	 */
	function getErrorMsg() {
		return str_replace( array( "\n", "'" ), array( '\n', "\'" ), $this->_errorMsg );
	}
	/**
	 * Get a database escaped string
	 * @return string
	 */
	function getEscaped( $text ) {
		return trim( $this->_resource->qstr($text), '\'' );
	}
	/**
	 * @return string The database prefix
	 */
	function getPrefix() {
		return $this->_table_prefix;
	}
	/**
	 * @return string Quoted null/zero date string
	 */
	function getNullDate() {
		return $this->Quote( $this->_nullDate );
	}
	/**
	 * Get a quoted database escaped string
	 * @return string
	 */
	function Quote( $text ) {
		return $this->_resource->qstr($text);
	}

	/**
	 * Quote an identifier name (field, table, etc)
	 * @param string The name
	 * @return string The quoted name
	 */
	function NameQuote( $s ) {
		$q = $this->_resource->nameQuote;
		if (strlen( $q ) == 1) {
			return $q . $s . $q;
		} else {
			return $q{0} . $s . $q{1};
		}
	}

	/**
	 * Sets the SQL query string for later execution.
	*
	 * This function replaces a string identifier <var>$prefix</var> with the
	 * string held is the <var>_table_prefix</var> class variable.
	*
	 * @param string The SQL query
	 * @param string The common table prefix
	 */
	function setQuery( $sql, $offset = 0, $limit = 0, $prefix='#__' ) {
		$this->_sql = $this->replacePrefix($sql, $prefix);
		$this->_limit = $limit;
		$this->_offset = $offset;
	}

	/**
	 * This function replaces a string identifier <var>$prefix</var> with the
	 * string held is the <var>_table_prefix</var> class variable.
	 *
	 * @param string The SQL query
	 * @param string The common table prefix
	 * @author thede, David McKinnis
	 */
	function replacePrefix( $sql, $prefix='#__' ) {
		$sql = trim( $sql );

		$escaped = false;
		$quoteChar = '';

		$n = strlen( $sql );

		$startPos = 0;
		$literal = '';
		while ($startPos < $n) {
			$ip = strpos($sql, $prefix, $startPos);
			if ($ip === false) {
				break;
			}

			$j = strpos( $sql, "'", $startPos );
			$k = strpos( $sql, '"', $startPos );
			if (($k !== FALSE) && (($k < $j) || ($j === FALSE))) {
				$quoteChar	= '"';
				$j			= $k;
			} else {
				$quoteChar	= "'";
			}

			if ($j === false) {
				$j = $n;
			}

			$literal .= str_replace( $prefix, $this->_table_prefix, substr( $sql, $startPos, $j - $startPos ) );
			$startPos = $j;

			$j = $startPos + 1;

			if ($j >= $n) {
				break;
			}

			// quote comes first, find end of quote
			while (TRUE) {
				$k = strpos( $sql, $quoteChar, $j );
				$escaped = false;
				if ($k === false) {
					break;
				}
				$l = $k - 1;
				while ($l >= 0 && $sql{$l} == '\\') {
					$l--;
					$escaped = !$escaped;
				}
				if ($escaped) {
					$j	= $k+1;
					continue;
				}
				break;
			}
			if ($k === FALSE) {
				// error in the query - no end quote; ignore it
				break;
			}
			$literal .= substr( $sql, $startPos, $k - $startPos + 1 );
			$startPos = $k+1;
		}
		if ($startPos < $n) {
			$literal .= substr( $sql, $startPos, $n - $startPos );
		}
		return $literal;
	}

	/**
	 * @return string The current value of the internal SQL vairable
	 */
	function getQuery() {
		return "<pre>" . htmlspecialchars( $this->_sql ) . "</pre>";
	}
	/**
	 * Execute the query
	 * @param array Parameters for ADODB Execute method
	 * @return mixed A database resource if successful, FALSE if not.
	 */
	function query( $params = array() ) {
		$this->_errorNum = 0;
		$this->_errorMsg = '';
		$this->_ticker++;
		if ($this->_debug) {
	  		$this->_log[] = $this->_sql;
		}
		if ($this->_limit > 0 || $this->_offset > 0) {
			$this->_cursor = $this->_resource->SelectLimit( $this->_sql, $this->_limit, $this->_offset );
		} else {
			$this->_cursor = $this->_resource->Execute( $this->_sql, $params );
		}
		if (!$this->_cursor) {
			$this->_errors();
			return false;
		}
		return $this->_cursor;
	}

	/**
	 * Sets the internal error markers
	 * @protected
	 */
	function _errors() {
		$this->_errorNum = $this->_resource->ErrorNo();
		$this->_errorMsg = $this->_resource->ErrorMsg();
		if ($this->_debug && $this->_errorNum > 0) {
			trigger_error( $this->_resource->ErrorMsg(), E_USER_NOTICE );
			//echo "<pre>" . $this->_sql . "</pre>\n";
			mosBackTrace();
		}
	}

	/**
	 * @return int The number of rows returned from the most recent query.
	 */
	function getNumRows( $cur=null ) {
		return ( $cur ? $cur->RecordCount() : $this->_cursor->RecordCount());
	}

	/**
	 * This method loads the first field of the first row returned by the query.
	*
	 * @return The value returned in the query or null if the query failed.
	 */
	function loadResult() {
		if (($ret = $this->_resource->GetOne( $this->_sql )) === false) {
			$this->_errors();
			return null;
		}
		return $ret;
	}

	/**
	 * Load a column of results from a query
	 * @return array
	 */
	function loadResultArray() {
		$ret = $this->_resource->GetCol( $this->_sql );
		$this->_errors();
		return $ret;
	}
	/**
	 * Load a assoc list of database rows
	 * @param string The field name of a primary key
	 * @return array If <var>key</var> is empty as sequential list of returned records.
	 */
	function loadAssocList( $key='' ) {
		$temp = $this->_resource->setFetchMode( ADODB_FETCH_ASSOC );

		if (!($cur = $this->query())) {
			$this->_resource->setFetchMode( $temp );
			return null;
		}

		$array = array();
		while ($row = $cur->FetchRow(false)) {
			if ($key) {
				$array[$row[$key]] = $row;
			} else {
				$array[] = $row;
			}
		}
		$this->_resource->setFetchMode( $temp );
		return $array;
	}
	/**
	 * This global function loads the first row of a query into an object
	*
	 * If an object is passed to this function, the returned row is bound to the existing elements of <var>object</var>.
	 * If <var>object</var> has a value of null, then all of the returned query fields returned in the object.
	 * @param string The SQL query
	 * @param object The address of variable
	 */
	function loadObject( &$object ) {
		if (1) { // old way
			if ($object != null) {
	 			if (!($cur = $this->query())) {
	 				return false;
	 			}
				if ($array = $cur->FetchRow()) {
	 				mosBindArrayToObject( $array, $object );
	 				return true;
	 			} else {
					return false;
				}
	 		} else {
	 			if ($cur = $this->query()) {
					if ($object = $cur->FetchNextObject(false) ) {
	 					return true;
	 				} else {
	 					$object = null;
						return false;
					}
				} else {
					return false;
				}
			}
		} else { // new way
			if ($rs = $this->query()) {
				if ($object != null) {
					if ($array = $rs->FetchRow()) {
		 				mosBindArrayToObject( $array, $object );
		 				return true;
		 			} else {
						return false;
					}
				} else {
					$temp = $this->_resource->setFetchMode( ADODB_FETCH_OBJECT );
					$obj = $rs->FetchRow();
					$this->_resource->setFetchMode( $temp );
				}
			} else {
 				return false;
 			}
		}
	}
	/**
	 * Load a list of database objects
	 * @param string The field name of a primary key
	 * @return array If <var>key</var> is empty as sequential list of returned records.
	 * If <var>key</var> is not empty then the returned array is indexed by the value
	 * the database key.  Returns <var>null</var> if the query fails.
	 */
	function loadObjectList( $index=null ) {
		if (1) { // old way
			if (!($cur = $this->query())) {
				return false;
			}
			$list = array();
			while ($obj = $cur->FetchNextObject( false )) {
				if ($index) {
					$list[$obj->$index] = clone( $obj );
				} else {
					$list[] = clone( $obj );
				}
			}
			return $list;
		} else { // new way
			$list = array();
			$temp = $this->_resource->setFetchMode( ADODB_FETCH_OBJECT );
			if ($rs = $this->query()) {
				while ($obj = $rs->FetchRow()) {
					if ($index) {
						$list[$obj->$index] = clone( $obj );
					} else {
						$list[] = clone( $obj );
					}
				}
				$rs->Close();
			}
			$this->_resource->setFetchMode( $temp );

			return $list;
		}
	}
	/**
	 * @return The first row of the query.
	 */
	function loadRow() {
		$temp = $this->_resource->setFetchMode( ADODB_FETCH_NUM );
		if (!($cur = $this->query())) {
			$this->_resource->setFetchMode( $temp );
			return null;
		}
		$ret = null;
		if ($row = $cur->FetchRow()) {
			$ret = $row;
		}
		$this->_resource->setFetchMode( $temp );
		return $ret;
	}
	/**
	 * Load a list of database rows (numeric column indexing)
	 * @param string The field name of a primary key
	 * @return array If <var>key</var> is empty as sequential list of returned records.
	 * If <var>key</var> is not empty then the returned array is indexed by the value
	 * the database key.  Returns <var>null</var> if the query fails.
	 */
	function loadRowList( $key='' ) {
		$temp = $this->_resource->setFetchMode( ADODB_FETCH_NUM );
		if (!($cur = $this->query())) {
			$this->_resource->setFetchMode( $temp );
			return null;
		}

		$array = array();
		while ($row = $cur->FetchRow(false)) {
			if ($key) {
				$array[$row[$key]] = $row;
			} else {
				$array[] = $row;
			}
		}
		$this->_resource->setFetchMode( $temp );
		return $array;
	}
	/**
	 * Document::db_insertObject()
	*
	 * { Description }
	*
	 * @param [type] $keyName
	 * @param [type] $verbose
	 */
	function insertObject( $table, &$object, $keyName = NULL, $verbose=false ) {
		$fmtsql = "INSERT INTO $table ( %s ) VALUES ( %s ) ";
		$fields = array();
		$values = array();
		foreach (get_object_vars( $object ) as $k => $v) {
			if (is_array($v) or is_object($v) or $v === NULL) {
				continue;
			}
			if ($k[0] == '_') { // internal field
				continue;
			}
			$fields[] = $this->NameQuote( $k );
			$values[] = $this->Quote( $v );
		}
		$this->setQuery( sprintf( $fmtsql, implode( ",", $fields ) ,  implode( ",", $values ) ) );
		($verbose) && print $this->getQuery() . "<br />\n";
		if (!$this->query()) {
			return false;
		}
		$id = $this->insertid();
		($verbose) && print "id=[$id]<br />\n";
		if ($keyName && $id) {
			$object->$keyName = $id;
		}
		return true;
	}

	/**
	 * Document::db_updateObject()
	*
	 * { Description }
	*
	 * @param [type] $updateNulls
	 */
	function updateObject( $table, &$object, $keyName, $updateNulls=true ) {
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
			if ($v === NULL && !$updateNulls) {
				continue;
			}
			if( $v == '' ) {
				$val = "''";
			} else {
				$val = $this->Quote( $v );
			}
			$tmp[] = $this->NameQuote( $k ) . '=' . $val;
		}
		$this->setQuery( sprintf( $fmtsql, implode( ",", $tmp ) , $where ) );
		return $this->query();
	}

	/**
	 * @param boolean If TRUE, displays the last SQL statement sent to the database
	 * @return string A standised error message
	 */
	function stderr( $showSQL = false ) {
		return "DB function failed with error number $this->_errorNum"
		."<br /><font color=\"red\">$this->_errorMsg</font>"
		.($showSQL ? "<br />SQL = <pre>$this->_sql</pre>" : '');
	}

	function insertid() {
		return $this->_resource->Insert_ID();
	}

	function getVersion() {
		//die( get_class( $this ) . "::getVersion is not defined for this connector." );
		return @mysql_get_server_info();
	}

	/**
	 * @return array A list of all the tables in the database
	 */
	function getTableList() {
		return $this->_resource->MetaTables( 'TABLES' );;
	}
	/**
	 * @param array A list of table names
	 * @return array A list the create SQL for the tables
	 */
	function getTableCreate( $tables ) {
		$result = array();

		foreach ($tables as $tblval) {
			$this->setQuery( 'SHOW CREATE table ' . $this->getEscaped( $tblval ) );
			$this->query();
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
	function getTableFields( $tables ) {
		$result = array();

		foreach ($tables as $tblval) {
			$this->setQuery( 'SHOW FIELDS FROM ' . $tblval );
			$this->query();
			$fields = $this->loadObjectList();
			foreach ($fields as $field) {
				$result[$tblval][$field->Field] = preg_replace("/[(0-9)]/",'', $field->Type );
			}
		}

		return $result;
	}

	/**
	 * Updates the AXMLS schema
	 * @param string The name of the xml file
	 * @return boolean True if successful
	 */
	function schemaUpdate( $file ) {
		mosFS::load( 'includes/adodb/adodb-xmlschema.inc.php' );

		$schema = new adoSchema( $this->_resource );
		$schema->SetPrefix( $this->getPrefix() );

		// Build the SQL array
		$schema->ParseSchema( $file );

		// Execute the SQL on the database
		// ADODB's xmlschema is being lame, continue on error.
		$schema->ContinueOnError( true );
		$result = $schema->ExecuteSchema();

		return $result;
	}
}

/**
* mosDBTable Abstract Class.
* @abstract
* @package Joomla
* @subpackage Database
*
* Parent classes to all database derived objects.  Customisation will generally
* not involve tampering with this object.
* @package Joomla
* @author Andrew Eddie <eddieajau@users.sourceforge.net
*/
class mosDBTable {
	/** @var string Name of the table in the db schema relating to child class */
	var $_tbl = '';
	/** @var string Name of the primary key field in the table */
	var $_tbl_key = '';
	/** @var string Error message */
	var $_error = '';
	/** @var mosDatabase Database connector */
	var $_db = null;

	/**
	 * Object constructor to set table and key field
	*
	 * Can be overloaded/supplemented by the child class
	 * @param string $table name of the table in the db schema relating to child class
	 * @param string $key name of the primary key field in the table
	 */
	function mosDBTable( $table, $key, &$db ) {
		$this->_tbl = $table;
		$this->_tbl_key = $key;
		$this->_db =& $db;
	}
	/**
	 * Filters public properties
	 * @access protected
	 * @param array List of fields to ignore
	 */
	function filter( $ignoreList=null ) {
		$ignore = is_array( $ignoreList );

		$iFilter = new InputFilter();
		foreach ($this->getPublicProperties() as $k) {
			if ($ignore && in_array( $k, $ignoreList ) ) {
				continue;
			}
			$this->$k = $iFilter->process( $this->$k );
		}
	}
	/**
	 *	@return string Returns the error message
	 */
	function getError() {
		return $this->_error;
	}
	/**
	 * Gets the value of the class variable
	 * @param string The name of the class variable
	 * @return mixed The value of the class var (or null if no var of that name exists)
	 */
	function get( $_property ) {
		if(isset( $this->$_property )) {
			return $this->$_property;
		} else {
			return null;
		}
	}
	/**
	 * Returns an array of public properties
	 * @return array
	 */
	function getPublicProperties() {
		static $cache = null;
		if (is_null( $cache )) {
			$cache = array();
			foreach (get_class_vars( get_class( $this ) ) as $key=>$val) {
				if (substr( $key, 0, 1 ) != '_') {
					$cache[] = $key;
				}
			}
		}
		return $cache;
	}
	/**
	 * Set the value of the class variable
	 * @param string The name of the class variable
	 * @param mixed The value to assign to the variable
	 */
	function set( $_property, $_value ) {
		$this->$_property = $_value;
	}

	/**
	 * binds a named array/hash to this object
	 *
	 * can be overloaded/supplemented by the child class
	 * @param array $hash named array
	 * @param string Space separated list of fields to ignore
	 * @param string A prefix on the field names
	 * @return null|string	null is operation was satisfactory, otherwise returns an error
	 */
	function bind( $array, $ignore=null, $prefix=null ) {
		if (!is_array( $array )) {
			$this->_error = strtolower(get_class( $this ))."::bind failed.";
			return false;
		} else {
			return mosBindArrayToObject( $array, $this, $ignore, $prefix );
		}
	}

	/**
	 * Loads the record information from the database
	 * @param int $oid optional argument, if not specifed then the value of current key is used
	 * @return any result from the database operation
	 */
	function load( $oid=null ) {
		$k = $this->_tbl_key;
		if ($oid !== null) {
			$this->$k = $oid;
		}
		$oid = $this->$k;
		if ($oid === null) {
			return false;
		}
		$query = "SELECT *"
		. "\n FROM $this->_tbl"
		. "\n WHERE $this->_tbl_key = '$oid'";
		$this->_db->setQuery( $query );
		return $this->_db->loadObject( $this );
	}

	/**
	 * generic check method
	*
	 * can be overloaded/supplemented by the child class
	 * @return boolean True if the object is ok
	 */
	function check() {
		return true;
	}

	/**
	 * Inserts a new row if id is zero or updates an existing row in the database table
	*
	 * Can be overloaded/supplemented by the child class
	 * @param boolean If false, null object variables are not updated
	 * @return null|string null if successful otherwise returns and error message
	 */
	function store( $updateNulls=false ) {
		$k = $this->_tbl_key;
		global $migrate;
		if( $this->$k && !$migrate) {
			$ret = $this->_db->updateObject( $this->_tbl, $this, $this->_tbl_key, $updateNulls );
		} else {
			$ret = $this->_db->insertObject( $this->_tbl, $this, $this->_tbl_key );
		}
		if( !$ret ) {
			$this->_error = strtolower(get_class( $this ))."::store failed <br />" . $this->_db->getErrorMsg();
			return false;
		} else {
			return true;
		}
	}

	/**
	 */
	function move( $dirn, $where='' ) {
		$k = $this->_tbl_key;

		$sql = "SELECT $this->_tbl_key, ordering"
		. "\n FROM $this->_tbl";

		if ( $dirn < 0 ) {
			$sql .= "\n WHERE ordering < $this->ordering";
			$sql .= ( $where ? "\n AND $where" : '' );
			$sql .= "\n ORDER BY ordering DESC";
		} else if ( $dirn > 0 ) {
			$sql .= "\n WHERE ordering > $this->ordering";
			$sql .= ( $where ? "\n AND $where" : '' );
			$sql .= "\n ORDER BY ordering";
		} else {
			$sql .= "\n WHERE ordering = $this->ordering";
			$sql .= ( $where ? "\n AND $where" : '' );
			$sql .= "\n ORDER BY ordering";
		}

		$this->_db->setQuery( $sql, 0, 1 );

		$row = null;
		if ($this->_db->loadObject( $row )) {
			$query = "UPDATE $this->_tbl"
			. "\n SET ordering = '$row->ordering'"
			. "\n WHERE $this->_tbl_key = '".$this->$k."'"
			;
			$this->_db->setQuery( $query );

			if (!$this->_db->query()) {
				$err = $this->_db->getErrorMsg();
				die( $err );
			}

			$query = "UPDATE $this->_tbl"
			. "\n SET ordering = '$this->ordering'"
			. "\n WHERE $this->_tbl_key = '".$row->$k."'"
			;
			$this->_db->setQuery( $query );

			if (!$this->_db->query()) {
				$err = $this->_db->getErrorMsg();
				die( $err );
			}

			$this->ordering = $row->ordering;
		} else {
			$query = "UPDATE $this->_tbl"
			. "\n SET ordering = '$this->ordering'"
			. "\n WHERE $this->_tbl_key = '".$this->$k."'"
			;
			$this->_db->setQuery( $query );

			if (!$this->_db->query()) {
				$err = $this->_db->getErrorMsg();
				die( $err );
			}
		}
	}

	function _arrayMoveElement( &$v, $from, $to ) {
		$cut = array_splice( $v, $from, 1 );
		array_splice( $v, $to, 0, $cut );
	}
	/**
	 * Compacts the ordering sequence of the selected records
	 * @param string Additional where query to limit ordering to a particular subset of records
	 */
	function updateOrder( $where='' ) {
		$k = $this->_tbl_key;

		if (!array_key_exists( 'ordering', get_class_vars( strtolower(get_class( $this )) ) )) {
			$this->_error = "WARNING: ".strtolower(get_class( $this ))." does not support ordering.";
			return false;
		}

		if ( $this->_tbl == '#__content_frontpage' ) {
			$order2 = ', content_id DESC';
		} else {
			$order2 = '';
		}

		$query = "SELECT $this->_tbl_key, ordering"
		. "\n FROM $this->_tbl"
		. ( $where ? "\n WHERE $where" : '' )
		. "\n ORDER BY ordering". $order2
		;
		$this->_db->setQuery( $query );
		if (!($orders = $this->_db->loadObjectList())) {
			$this->_error = $this->_db->getErrorMsg();
			return false;
		}
		// first pass, compact the ordering numbers
		for ($i=0, $n=count( $orders ); $i < $n; $i++) {
			if ($orders[$i]->ordering >= 0) {
				$orders[$i]->ordering = $i+1;
			}
		}

		$shift = 0;
		$n=count( $orders );
		for ($i=0; $i < $n; $i++) {
			if ($orders[$i]->$k == $this->$k) {
				// place 'this' record in the desired location
				$orders[$i]->ordering = min( $this->ordering, $n );
				$shift = 1;
			} else if ($orders[$i]->ordering >= $this->ordering && $this->ordering > 0) {
				$orders[$i]->ordering++;
			}
		}
		// compact once more until I can find a better algorithm
		for ($i=0, $n=count( $orders ); $i < $n; $i++) {
			if ($orders[$i]->ordering >= 0) {
				$orders[$i]->ordering = $i+1;
				$query = "UPDATE $this->_tbl"
				. "\n SET ordering = '".$orders[$i]->ordering."'"
				. "\n WHERE $k = '".$orders[$i]->$k."'"
				;
				$this->_db->setQuery( $query );
				$this->_db->query();
			}
		}

		// if we didn't reorder the current record, make it last
		if ($shift == 0) {
			$order = $n+1;

			$query = "UPDATE $this->_tbl"
			. "\n SET ordering = '$order'"
			. "\n WHERE $k = '".$this->$k."'"
			;
			$this->_db->setQuery( $query );
			$this->_db->query();
		}
		return true;
	}
	/**
	 * Generic check for whether dependancies exist for this object in the db schema
	 *
	 * can be overloaded/supplemented by the child class
	 * @param string $msg Error message returned
	 * @param int Optional key index
	 * @param array Optional array to compiles standard joins: format [label=>'Label',name=>'table name',idfield=>'field',joinfield=>'field']
	 * @return true|false
	 */
	function canDelete( $oid=null, $joins=null ) {
		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = intval( $oid );
		}
		if (is_array( $joins )) {
			$select = "$k";
			$join = "";
			foreach( $joins as $table ) {
				$select .= ",\n COUNT( DISTINCT {$table['idfield']} ) AS {$table['idfield']}";
				$join .= "\n LEFT JOIN {$table['name']} ON {$table['joinfield']} = $k";
			}
			$query = "SELECT $select"
			. "\n FROM $this->_tbl"
			. "\n $join"
			. "\n WHERE $k = ". $this->$k .""
			. "\n GROUP BY $k";
			$this->_db->setQuery( $query );

			if ($obj = $this->_db->loadObject()) {
				$this->_error = $this->_db->getErrorMsg();
				return false;
			}
			$msg = array();
			foreach( $joins as $table ) {
				$k = $table['idfield'];
				if ($obj->$k) {
					$msg[] = $AppUI->_( $table['label'] );
				}
			}

			if (count( $msg )) {
				$this->_error = "noDeleteRecord" . ": " . implode( ', ', $msg );
				return false;
			} else {
				return true;
			}
		}

		return true;
	}

	/**
	 * Default delete method
	 *
	 * can be overloaded/supplemented by the child class
	 * @return true if successful otherwise returns and error message
	 */
	function delete( $oid=null ) {
		//if (!$this->canDelete( $msg )) {
		//	return $msg;
		//}

		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = intval( $oid );
		}

		$query = "DELETE FROM $this->_tbl"
		. "\n WHERE $this->_tbl_key = '".$this->$k."'";
		$this->_db->setQuery( $query );

		if ($this->_db->query()) {
			return true;
		} else {
			$this->_error = $this->_db->getErrorMsg();
			return false;
		}
	}

	function checkout( $who, $oid=null ) {
		if (!array_key_exists( 'checked_out', get_class_vars( strtolower(get_class( $this )) ) )) {
			$this->_error = "WARNING: ".strtolower(get_class( $this ))." does not support checkouts.";
			return false;
		}
		$k = $this->_tbl_key;
		if ($oid !== null) {
			$this->$k = $oid;
		}

		$time = date( "Y-m-d H:i:s" );
		if (intval( $who )) {
			// new way of storing editor, by id
			$query = "UPDATE $this->_tbl"
			. "\n SET checked_out = '$who', checked_out_time = '$time'"
			. "\n WHERE $this->_tbl_key = '".$this->$k."'"
			;
			$this->_db->setQuery( $query );
		} else {
			// old way of storing editor, by name
			$query = "UPDATE $this->_tbl"
			. "\n SET checked_out = '1', checked_out_time = '$time', editor = '".$who."' "
			. "\n WHERE $this->_tbl_key = '".$this->$k."'"
			;
			$this->_db->setQuery( $query );
		}
		return $this->_db->query();
	}

	function checkin( $oid=null ) {
		global $mosConfig_zero_date;

		if (!array_key_exists( 'checked_out', get_class_vars( strtolower(get_class( $this )) ) )) {
			$this->_error = "WARNING: ".strtolower(get_class( $this ))." does not support checkin.";
			return false;
		}
		$k = $this->_tbl_key;
		if ($oid !== null) {
			$this->$k = $oid;
		}
		$time = date( "H:i:s" );
		$query = "UPDATE $this->_tbl"
		. "\n SET checked_out = '0', checked_out_time = '$mosConfig_zero_date'"
		. "\n WHERE $this->_tbl_key = '".$this->$k."'"
		;
		$this->_db->setQuery( $query );
		return $this->_db->query();
	}

	function hit( $oid=null ) {
		global $mosConfig_enable_log_items;

		$k = $this->_tbl_key;
		if ($oid !== null) {
			$this->$k = intval( $oid );
		}
		$query = "UPDATE $this->_tbl SET hits = ( hits + 1 )"
		. "\n WHERE $this->_tbl_key = '$this->id'"
		;
		$this->_db->setQuery( $query );
		$this->_db->query();

		if ( @$mosConfig_enable_log_items ) {
			$now = date( "Y-m-d" );
			$query = "SELECT hits"
			. "\n FROM #__core_log_items"
			. "\n WHERE time_stamp = '$now'"
			. "\n AND item_table = '$this->_tbl'"
			. "\n AND item_id = '".$this->$k."'"
			;
			$this->_db->setQuery( $query );
			$hits = intval( $this->_db->loadResult() );
			if ( $hits ) {
				$query = "UPDATE #__core_log_items SET hits = ( hits + 1 )"
				. "\n WHERE time_stamp = '$now'"
				. "\n AND item_table = '$this->_tbl'"
				. "\n AND item_id = '".$this->$k."'"
				;
				$this->_db->setQuery( $query );
				$this->_db->query();
			} else {
				$query = "INSERT INTO #__core_log_items VALUES"
				. "\n ('$now','$this->_tbl','".$this->$k."','1')"
				;
				$this->_db->setQuery( $query );
				$this->_db->query();
			}
		}
	}

	/**
	 * Tests if item is checked out
	 * @param boolean
	 */
	function isCheckedOut() {
		global $my;
		return ($this->checked_out && $this->checked_out <> $my->id);
	}

	/**
	 * Generic save function
	 * @param array Source array for binding to class vars
	 * @param string Filter for the order updating
	 * @returns TRUE if completely successful, FALSE if partially or not succesful.
	 */
	function save( $source, $order_filter ) {
		if (!$this->bind( $_POST )) {
			return false;
		}
		if (!$this->check()) {
			return false;
		}
		if (!$this->store()) {
			return false;
		}
		if (!$this->checkin()) {
			return false;
		}
		$filter_value = $this->$order_filter;
		$this->updateOrder( $order_filter ? "`$order_filter`='$filter_value'" : "" );
		$this->_error = '';
		return true;
	}

	/**
	 * Generic Publish/Unpublish function
	 * @param array An array of id numbers
	 * @param integer 0 if unpublishing, 1 if publishing
	 * @param integer The id of the user performnig the operation
	 */
	function publish_array( $cid=null, $publish=1, $myid=0 ) {
		if (!is_array( $cid ) || count( $cid ) < 1) {
			$this->_error = "No items selected.";
			return false;
		}

		$cids = implode( ',', $cid );

		$query = "UPDATE $this->_tbl SET published='$publish'"
		. "\n WHERE $this->_tbl_key IN ( $cids )"
		. "\n AND ( checked_out = 0 OR ( checked_out = '$myid' ) )";
		$this->_db->setQuery( $query );
		if (!$this->_db->query()) {
			$this->_error = $this->_db->getErrorMsg();
			return false;
		}

		if (count( $cid ) == 1) {
			$this->checkin( $cid[0] );
		}
		$this->_error = '';
		return true;
	}

	/**
	 * Presents a string represntation
	 * @param boolean Map foreign keys to text values
	 */
	function toString() {
		$txt = '';
		foreach (get_object_vars( $this ) as $k => $v) {
			if (is_array($v) or is_object($v) or $v === NULL) {
				continue;
			}
			if ($k[0] == '_') { // internal field
				continue;
			}
			$txt .= "$k = $v\n";
		}

		return $txt;
	}

	/**
	 * Export item list to xml
	 * @param boolean Map foreign keys to text values
	 */
	function toXML( $mapKeysToText=false ) {
		$xml = '<record table="' . $this->_tbl . '"';
		if ($mapKeysToText) {
			$xml .= ' mapkeystotext="true"';
		}
		$xml .= '>';
		foreach (get_object_vars( $this ) as $k => $v) {
			if (is_array($v) or is_object($v) or $v === NULL) {
				continue;
			}
			if ($k[0] == '_') { // internal field
				continue;
			}
			$xml .= '<' . $k . '><![CDATA[' . $v . ']]></' . $k . '>';
		}
		$xml .= '</record>';

		return $xml;
	}
}
?>