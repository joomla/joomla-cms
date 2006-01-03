<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport( 'joomla.common.base.object' );

/**
 * Database connector class
 *
 * @package Joomla.Framework
 * @subpackage Database
 * @abstract
 * @since 1.0
 */
class JDatabase extends JObject
{
	/** @var string Internal variable to hold the query sql */
	var $_sql			= '';
	/** @var int Internal variable to hold the database error number */
	var $_errorNum		= 0;
	/** @var string Internal variable to hold the database error message */
	var $_errorMsg		= '';
	/** @var string Internal variable to hold the prefix used on all database tables */
	var $_table_prefix	= '';
	/** @var Internal variable to hold the connector resource */
	var $_resource		= '';
	/** @var Internal variable to hold the last query cursor */
	var $_cursor		= null;
	/** @var boolean Debug option */
	var $_debug			= 0;
	/** @var int The limit for the query */
	var $_limit			= 0;
	/** @var int The for offset for the limit */
	var $_offset		= 0;
	/** @var int A counter for the number of queries performed by the object instance */
	var $_ticker		= 0;
	/** @var array A log of queries */
	var $_log			= null;
	/** @var string The null/zero date string */
	var $_nullDate		= null;
	/** @var string Quote for named objects */
	var $_nameQuote		= null;
	/**
	 * @var boolean UTF-8 support
	 * @since    1.1
	 */
	var $_utf			= 0;
	/**
	 * @var array The fields that are to be quote
	 * @since    1.1
	 */
	var $_quoted	= null;
	/**
	 * @var bool Legacy compatibility
	 * @since    1.1
	 */
	var $_hasQuoted	= null;

	/**
	* Database object constructor
	* @param string Database host
	* @param string Database user name
	* @param string Database user password
	* @param string Database name
	* @param string Common prefix for all tables
	*/
	function __construct( $host='localhost', $user, $pass, $db='', $table_prefix='')
	{
		// Determine utf-8 support
		$this->_utf = $this->hasUTF();

		//Set charactersets (needed for MySQL 4.1.2+)
		if ($this->_utf){
			$this->setUTF();
		}

		$this->_table_prefix = $table_prefix;
		$this->_ticker   = 0;
		$this->_errorNum = 0;
		$this->_log = array();
		$this->_quoted = array();
		$this->_hasQuoted = false;
	}

	/**
	 * Determines UTF support
     * @abstract
     * @return boolean
     * @since 1.1
	 */
	function hasUTF() {
		return false;
	}

	/**
	 * Custom settings for UTF support
     *
     * @abstract
     * @since 1.1
	 */
	function setUTF() {
	}

	/**
	 * Returns a reference to the global Database object, only creating it
	 * if it doesn't already exist.
	 *
	 * @param string  Database driver
	 * @param string Database host
	 * @param string Database user name
	 * @param string Database user password
	 * @param string Database name
	 * @param string Common prefix for all tables
	 * @return database A database object
	 * @since 1.1
	*/
	function &getInstance( $driver='MySQL', $host='localhost', $user, $pass, $db='', $table_prefix='' ) {
		static $instances;

		if (!isset( $instances )) {
			$instances = array();
		}

		$signature = serialize(array($driver, $host, $user, $pass, $db, $table_prefix));

		if (empty($instances[$signature])) {
			jimport('joomla.database.adapters.'.$driver);
			$adapter = 'JDatabase'.$driver;
			$instances[$signature] = new $adapter($host, $user, $pass, $db, $table_prefix);
		}

		return $instances[$signature];
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
	 * Adds a field or array of field names to the list that are to be quoted
	 * @param mixed Field name or array of names
	 * @since 1.1
	 */
	function addQuoted( $quoted ) {
		if (is_string( $quoted )) {
			$this->_quoted[] = $quoted;
		} else {
			$this->_quoted = array_merge( $this->_quoted, (array)$quoted );
		}
		$this->_hasQuoted = true;
	}
	/**
	 * Checks if field name needs to be quoted
	 * @param string The field name
	 * @return bool
	 */
	function isQuoted( $fieldName ) {
		if ($this->_hasQuoted) {
			return in_array( $fieldName, $this->_quoted );
		} else {
			return true;
		}
	}

	/**
	 * @param int
	 */
	function debug( $level ) {
		$this->_debug = intval( $level );
	}

	/**
	* @return boolean True if the database version supports utf storage
	* 				  False if backward compatibility is being used
	* @since 1.1
	*/
	function getUtfSupport() {
		return $this->_utf;
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
     * @abstract
	 * @return string
	 */
	function getEscaped( $text ) {
		return;
	}
	/**
	 * Quote an identifier name (field, table, etc)
	 * @param string The name
	 * @return string The quoted name
	 */
	function NameQuote( $s ) {
		$q = $this->_nameQuote;
		if (strlen( $q ) == 1) {
			return $q . $s . $q;
		} else {
			return $q{0} . $s . $q{1};
		}
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
		return $this->_nullDate;
	}
	/**
	 * Sets the SQL query string for later execution.
	 * This function replaces a string identifier <var>$prefix</var> with the
	 * string held is the <var>_table_prefix</var> class variable.
	 *
	 * @param string The SQL query
	 * @param string The offset to start selection
	 * @param string The number of results to return
	 * @param string The common table prefix
	 */
	function setQuery( $sql, $offset = 0, $limit = 0, $prefix='#__' ) {
		$this->_sql = $this->replacePrefix( $sql, $prefix );
		$this->_limit = intval( $limit );
		$this->_offset = intval( $offset );
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

		$n = JString::strlen( $sql );

		$startPos = 0;
		$literal = '';
		while ($startPos < $n) {
			$ip = JString::strpos($sql, $prefix, $startPos);
			if ($ip === false) {
				break;
			}

			$j = JString::strpos( $sql, "'", $startPos );
			$k = JString::strpos( $sql, '"', $startPos );
			if (($k !== FALSE) && (($k < $j) || ($j === FALSE))) {
				$quoteChar	= '"';
				$j			= $k;
			} else {
				$quoteChar	= "'";
			}

			if ($j === false) {
				$j = $n;
			}

			$literal .= str_replace( $prefix, $this->_table_prefix, JString::substr( $sql, $startPos, $j - $startPos ) );
			$startPos = $j;

			$j = $startPos + 1;

			if ($j >= $n) {
				break;
			}

			// quote comes first, find end of quote
			while (TRUE) {
				$k = JString::strpos( $sql, $quoteChar, $j );
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
			$literal .= JString::substr( $sql, $startPos, $k - $startPos + 1 );
			$startPos = $k+1;
		}
		if ($startPos < $n) {
			$literal .= JString::substr( $sql, $startPos, $n - $startPos );
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
	 * @abstract
	 * @return mixed A database resource if successful, FALSE if not.
	 */
	function query() {
		return;
	}

   /**
	* Execute a batch query
    * @abstract
	* @return mixed A database resource if successful, FALSE if not.
	*/
	function query_batch( $abort_on_error=true, $p_transaction_safe = false) {
		return false;
	}

	/**
	 * Diagnostic function
     * @abstract
	 */
	function explain() {
		return;
	}

	/**
	 * @param object Database resource
	 * @return int The number of rows returned from the most recent query.
	 * @abstract
     */
	function getNumRows( $cur=null ) {
		return;
	}

	/**
	 * This method loads the first field of the first row returned by the query.
     * @abstract
	 * @return The value returned in the query or null if the query failed.
	 */
	function loadResult() {
		return;
	}

	/**
	 * Load an array of single field results into an array
	 *
     * @abstract
	 */
	function loadResultArray($numinarray = 0) {
		return;
	}

	/**
	 * Load a assoc list of database rows
     * @abstract
	 * @param string The field name of a primary key
	 * @return array If key is empty as sequential list of returned records.
	 */
	function loadAssocList( $key='' ) {
		return;
	}
	/**
	 * This global function loads the first row of a query into an object
	 * If an object is passed to this function, the returned row is bound to the
	 * existing elements of <var>object</var>. If <var>object</var> has a value
	 * of null, then all of the returned query fields returned in the object.
     * @abstract
	 * @param object The address of variable
	 */
	function loadObject( &$object ) {
		return;
	}

	/**
	* Load a list of database objects
    *
    * @abstract
	* @param string The field name of a primary key
	* @return array If <var>key</var> is empty as sequential list of returned records.

    * If <var>key</var> is not empty then the returned array is indexed by the value
	* the database key.  Returns <var>null</var> if the query fails.
	*/
	function loadObjectList( $key='' ) {
		return;
	}
	/**
	* @abstract
    * @return The first row of the query.
	*/
	function loadRow() {
		return;
	}
	/**
	* Load a list of database rows (numeric column indexing)
	*
    * @abstract
    * @param string The field name of a primary key
	* @return array If <var>key</var> is empty as sequential list of returned records.
	* If <var>key</var> is not empty then the returned array is indexed by the value
	* the database key.  Returns <var>null</var> if the query fails.
	*/
	function loadRowList( $key='' ) {
		return;
	}
	/**
	 * @abstract
     * @param string The table name
	 * @param object
	 * @param string
	 * @param boolean
	 */
	function insertObject( $table, &$object, $keyName = NULL, $verbose=false ) {
		return;
	}

	/**
     * @abstract
	 * @param string
	 * @param object
	 * @param string
	 * @param boolean
	 */
	function updateObject( $table, &$object, $keyName, $updateNulls=true ) {
		return;
	}

	/**
	 * @param boolean If TRUE, displays the last SQL statement sent to the
	 * database
	 * @return string A standised error message
	 */
	function stderr( $showSQL = false ) {
		return "DB function failed with error number $this->_errorNum"
		."<br /><font color=\"red\">$this->_errorMsg</font>"
		.($showSQL ? "<br />SQL = <pre>$this->_sql</pre>" : '');
	}

	/**
     * @abstract
     * @return mixed
	 */
    function insertid() {
		return;
	}
	/**
	 * @abstract
     * @return string Collation in use
	 */
	function getCollation() {
		return;
	}

    /**
     * @abstract
	 */
	function getVersion() {
		return 'Not available for this connector';
	}

	/**
	 * @abstract
     * @return array A list of all the tables in the database
	 */
	function getTableList() {
		return;
	}
	/**
	 * @abstract
     * @param array A list of table names
	 * @return array A list the create SQL for the tables
	 */
	function getTableCreate( $tables ) {
		return;
	}
	/**
	 * @abstract
     * @param array A list of table names
	 * @return array An array of fields by table
	 */
	function getTableFields( $tables ) {
		return;
	}

	// ----
	// ADODB Compatibility Functions
	// ----

	/**
	* Get a quoted database escaped string
	* @return string
	*/
	function Quote( $text ) {
		return '\'' . $this->getEscaped( $text ) . '\'';
	}
	/**
	 * ADODB compatability function
	 * @param string SQL
	 * @since 1.1
	 */
	function GetCol( $query ) {
		$this->setQuery( $query );
		return $this->loadResultArray();
	}
	/**
	 * ADODB compatability function
	 * @param string SQL
	 * @return object
	 * @since 1.1
	 */
	function Execute( $query ) {
		$query = trim( $query );
		$this->setQuery( $query );
		if (eregi( '^select', $query )) {
			$result = $this->loadRowList();
			return new JSimpleRecordSet( $result );
		} else {
			$result = $this->query();
			if ($result === false) {
				return false;
			} else {
				return new JSimpleRecordSet( array() );
			}
		}
	}
	/**
	 * ADODB compatability function
	 * @since 1.1
	 */
	function SelectLimit( $query, $count, $offset=0 ) {
		$this->setQuery( $query, $offset, $count );
		$result = $this->loadRowList();
		return new JSimpleRecordSet( $result );
	}
	/**
	 * ADODB compatability function
	 * @since 1.1
	 */
	function PageExecute( $sql, $nrows, $page, $inputarr=false, $secs2cache=0 ) {
		$this->setQuery( $sql, $page*$nrows, $nrows );
		$result = $this->loadRowList();
		return new JSimpleRecordSet( $result );
	}
	/**
	 * ADODB compatability function
	 * @param string SQL
	 * @return array
	 * @since 1.1
	 */
	function GetRow( $query ) {
		$this->setQuery( $query );
		$result = $this->loadRowList();
		return $result[0];
	}
	/**
	 * ADODB compatability function
	 * @param string SQL
	 * @return mixed
	 * @since 1.1
	 */
	function GetOne( $query ) {
		$this->setQuery( $query );
		$result = $this->loadResult();
		return $result;
	}
	/**
	 * ADODB compatability function
	 * @since 1.1
	 */
	function BeginTrans() {
	}
	/**
	 * ADODB compatability function
	 * @since 1.1
	 */
	function RollbackTrans() {
	}
	/**
	 * ADODB compatability function
	 * @since 1.1
	 */
	function CommitTrans() {
	}
	/**
	 * ADODB compatability function
	 * @since 1.1
	 */
	function ErrorMsg() {
		return $this->getErrorMsg();
	}
	/**
	 * ADODB compatability function
	 * @since 1.1
	 */
	function ErrorNo() {
		return $this->getErrorNum();
	}
	/**
	 * ADODB compatability function
	 * @since 1.1
	 */
	function GenID( $foo1=null, $foo2=null ) {
		return '0';
	}
}

?>