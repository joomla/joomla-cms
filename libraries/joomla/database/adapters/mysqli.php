<?php
/**
* @version $Id$
* @package JoomlaFramework
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
* MySQLi database driver
*
 * @package JoomlaFramework
 * @subpackage Database
*/
class JDatabaseMySQLi extends JDatabase
{
	/** @var string The null/zero date string */
	var $_nullDate		= '0000-00-00 00:00:00';
	/** @var string Quote for named objects */
	var $_nameQuote		= '`';

	/**
	* Database object constructor
	* @param string Database host
	* @param string Database user name
	* @param string Database user password
	* @param string Database name
	* @param string Common prefix for all tables
	*/
	function __construct( $host='localhost', $user, $pass, $db='', $table_prefix='') {
		// perform a number of fatality checks, then die gracefully
		if (!function_exists( 'mysqli_connect' )) {
			$this->_errorNum = 1;
			return;
		}
		if (!($this->_resource = @mysqli_connect( $host, $user, $pass ))) {
			$this->_errorNum = 2;
			return;
		}
		if ($db != '' && !mysqli_select_db($this->_resource, $db)) {
			$this->_errorNum = 3;
			return;
		}

		parent::__construct($host, $user, $pass, $db, $table_prefix);
	}

	/**
	 * Determines UTF support
	 * @return boolean True - UTF is supported
	 */
	function hasUTF() {
		return true;
	}

	/**
	 * Custom settings for UTF support
	 */
	function setUTF() {
		mysqli_query( $this->_resource, "SET NAMES 'utf8'" );
	}

	/**
	* Get a database escaped string
	* @return string
	*/
	function getEscaped( $text ) {
		return mysqli_real_escape_string( $this->_resource, $text );
	}

	/**
	* Execute the query
	* @return mixed A database resource if successful, FALSE if not.
	*/
	function query() {
		global $mosConfig_debug;
		if ($this->_debug) {
			$this->_ticker++;
	  		$this->_log[] = $this->_sql;
		}
		if ($this->_limit > 0 || $this->_offset > 0) {
			$this->_sql .= "\nLIMIT $this->_offset, $this->_limit";
		}
		$this->_errorNum = 0;
		$this->_errorMsg = '';
		$this->_cursor = mysqli_query( $this->_resource, $this->_sql );
		if (!$this->_cursor) {
			$this->_errorNum = mysqli_errno( $this->_resource );
			$this->_errorMsg = mysqli_error( $this->_resource ) . " SQL=$this->_sql";
			if ($this->_debug) {
				trigger_error( mysqli_error( $this->_resource ), E_USER_NOTICE );
				//echo "<pre>" . $this->_sql . "</pre>\n";
				if (function_exists( 'debug_backtrace' )) {
					foreach( debug_backtrace() as $back) {
						if (@$back['file']) {
							echo '<br />'.$back['file'].':'.$back['line'];
						}
					}
				}
			}
			return false;
		}
		return $this->_cursor;
	}

   /**
	* Execute a batch query
	* @return mixed A database resource if successful, FALSE if not.
	*/
	function query_batch( $abort_on_error=true, $p_transaction_safe = false) {
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
	function explain() {
		$temp = $this->_sql;
		$this->_sql = "EXPLAIN $this->_sql";
		$this->query();

		if (!($cur = $this->query())) {
			return null;
		}
		$first = true;

		$buf = "<table cellspacing=\"1\" cellpadding=\"2\" border=\"0\" bgcolor=\"#000000\" align=\"center\">";
		$buf .= $this->getQuery();
		while ($row = mysqli_fetch_assoc( $cur )) {
			if ($first) {
				$buf .= "<tr>";
				foreach ($row as $k=>$v) {
					$buf .= "<th bgcolor=\"#ffffff\">$k</th>";
				}
				$buf .= "</tr>";
				$first = false;
			}
			$buf .= "<tr>";
			foreach ($row as $k=>$v) {
				$buf .= "<td bgcolor=\"#ffffff\">$v</td>";
			}
			$buf .= "</tr>";
		}
		$buf .= "</table><br />&nbsp;";
		mysqli_free_result( $cur );

		$this->_sql = $temp;

		return "<div style=\"background-color:#FFFFCC\" align=\"left\">$buf</div>";
	}
	/**
	* @return int The number of rows returned from the most recent query.
	*/
	function getNumRows( $cur=null ) {
		return mysqli_num_rows( $cur ? $cur : $this->_cursor );
	}

	/**
	* This method loads the first field of the first row returned by the query.
	*
	* @return The value returned in the query or null if the query failed.
	*/
	function loadResult() {
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
	function loadResultArray($numinarray = 0) {
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
	* Load a assoc list of database rows
	* @param string The field name of a primary key
	* @return array If <var>key</var> is empty as sequential list of returned records.
	*/
	function loadAssocList( $key='' ) {
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
	* If an object is passed to this function, the returned row is bound to the existing elements of <var>object</var>.
	* If <var>object</var> has a value of null, then all of the returned query fields returned in the object.
	* @param string The SQL query
	* @param object The address of variable
	*/
	function loadObject( &$object ) {
		if ($object != null) {
			if (!($cur = $this->query())) {
				return false;
			}
			if ($array = mysqli_fetch_assoc( $cur )) {
				mysqli_free_result( $cur );
				mosBindArrayToObject( $array, $object, null, null, false );
				return true;
			} else {
				return false;
			}
		} else {
			if ($cur = $this->query()) {
				if ($object = mysqli_fetch_object( $cur )) {
					mysqli_free_result( $cur );
					return true;
				} else {
					$object = null;
					return false;
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
	function loadObjectList( $key='' ) {
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
	function loadRow() {
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
	function loadRowList( $key='' ) {
		if (!($cur = $this->query())) {
			return null;
		}
		$array = array();
		while ($row = mysqli_fetch_array( $cur )) {
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
		foreach (get_object_vars( $object ) as $k => $v) {
			if (is_array($v) or is_object($v) or $v === NULL) {
				continue;
			}
			if ($k[0] == '_') { // internal field
				continue;
			}
			$fields[] = $this->NameQuote( $k );;
			$values[] = $this->isQuoted( $v ) ? $this->Quote( $v ) : $v;
		}
		$this->setQuery( sprintf( $fmtsql, implode( ",", $fields ) ,  implode( ",", $values ) ) );
		($verbose) && print "$sql<br />\n";
		if (!$this->query()) {
			return false;
		}
		$id = mysqli_insert_id( $this->_resource );
		($verbose) && print "id=[$id]<br />\n";
		if ($keyName && $id) {
			$object->$keyName = $id;
		}
		return true;
	}

	/**
	 * Document::db_updateObject()
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
				$val = $this->isQuoted( $v ) ? $this->Quote( '' ) : 0;
			} else {
				$val = $this->isQuoted( $v ) ? $this->Quote( $v ) : $v;
			}
			$tmp[] = $this->NameQuote( $k ) . '=' . $val;
		}
		$this->setQuery( sprintf( $fmtsql, implode( ",", $tmp ) , $where ) );
		return $this->query();
	}

	function insertid() {
		return mysqli_insert_id( $this->_resource );
	}

	/**
	 * Assumes database collation in use by sampling one text field in one table
	 * @return string Collation in use
	 */
	function getCollation (){
		$this->setQuery( 'SHOW FULL COLUMNS FROM #__content' );
		$array = $this->loadAssocList();
		return $array['4']['Collation'];
	}

	function getVersion() {
		return mysqli_get_server_info( $this->_resource );
	}

	/**
	 * @return array A list of all the tables in the database
	 */
	function getTableList() {
		$this->setQuery( 'SHOW TABLES' );
		return $this->loadResultArray();
	}
	/**
	 * @param array A list of table names
	 * @return array A list the create SQL for the tables
	 */
	function getTableCreate( $tables ) {
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
	function getTableFields( $tables ) {
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
?>