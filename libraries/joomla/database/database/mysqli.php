<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Database
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * MySQLi database driver
 *
 * @package		Joomla.Framework
 * @subpackage	Database
 * @since		1.0
 */
class JDatabaseMySQLi extends JDatabase
{
	/**
	 *  The database driver name
	 *
	 * @var string
	 */
	public $name = 'mysqli';

	/**
	 * The null/zero date string
	 *
	 * @var string
	 */
	protected $_nullDate = '0000-00-00 00:00:00';

	/**
	 * Quote for named objects
	 *
	 * @var string
	 */
	protected $_nameQuote = '`';

	/**
	 * Database object constructor
	 *
	 * @param	array	List of options used to configure the connection
	 * @since	1.5
	 * @see		JDatabase
	 */
	function __construct($options)
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
		$targetSlot = substr(strstr($host, ":"), 1);
		if (!empty($targetSlot))
		{
			// Get the port number or socket name
			if (is_numeric($targetSlot)) {
				$port	= $targetSlot;
			}
			else {
				$socket	= $targetSlot;
			}

			// Extract the host name only
			$host = substr($host, 0, strlen($host) - (strlen($targetSlot) + 1));
			// This will take care of the following notation: ":3306"
			if ($host == '') {
				$host = 'localhost';
			}
		}

		// perform a number of fatality checks, then return gracefully
		if (!function_exists('mysqli_connect'))
		{
			$this->_errorNum = 1;
			$this->_errorMsg = 'The MySQL adapter "mysqli" is not available.';
			return;
		}

		// connect to the server
		if (!($this->_connection = @mysqli_connect($host, $user, $password, NULL, $port, $socket)))
		{
			$this->_errorNum = 2;
			$this->_errorMsg = 'Could not connect to MySQL';
			return;
		}

		// finalize initialization
		parent::__construct($options);

		// select the database
		if ($select) {
			$this->select($database);
		}
	}

	/**
	 * Database object destructor
	 *
	 * @return	boolean
	 * @since	1.5
	 */
	public function __destruct()
	{
		$return = false;
		if (is_object($this->_connection)) {
			$return = mysqli_close($this->_connection);
		}
		return $return;
	}

	/**
	 * Test to see if the MySQLi connector is available
	 *
	 * @return	boolean	True on success, false otherwise.
	 */
	public static function test()
	{
		return (function_exists('mysqli_connect'));
	}

	/**
	 * Determines if the connection to the server is active.
	 *
	 * @return	boolean
	 * @since	1.5
	 */
	public function connected()
	{
		return $this->_connection->ping();
	}

	/**
	 * Select a database for use
	 *
	 * @param	string $database
	 * @return	boolean True if the database has been successfully selected
	 * @since	1.5
	 */
	public function select($database)
	{
		if (! $database)
		{
			return false;
		}

		if (!mysqli_select_db($this->_connection, $database))
		{
			$this->_errorNum = 3;
			$this->_errorMsg = 'Could not connect to database';
			return false;
		}

		return true;
	}

	/**
	 * Determines UTF support
	 *
	 * @return	boolean	True - UTF is supported
	 */
	public function hasUTF()
	{
		$verParts = explode('.', $this->getVersion());
		return ($verParts[0] == 5 || ($verParts[0] == 4 && $verParts[1] == 1 && (int)$verParts[2] >= 2));
	}

	/**
	 * Custom settings for UTF support
	 */
	public function setUTF()
	{
		mysqli_query($this->_connection, "SET NAMES 'utf8'");
	}

	/**
	 * Get a database escaped string
	 *
	 * @param	string	The string to be escaped
	 * @param	boolean	Optional parameter to provide extra escaping
	 * @return	string
	 */
	public function getEscaped($text, $extra = false)
	{
		$result = mysqli_real_escape_string($this->_connection, $text);
		if ($extra) {
			$result = addcslashes($result, '%_');
		}
		return $result;
	}
	/**
	 * Execute the query
	 *
	 * @return	mixed	A database resource if successful, FALSE if not.
	 */
	public function query()
	{
		if (!is_object($this->_connection)) {
			return false;
		}

		// Take a local copy so that we don't modify the original query and cause issues later
		$sql = $this->_sql;
		if ($this->_limit > 0 || $this->_offset > 0) {
			$sql .= ' LIMIT '.$this->_offset.', '.$this->_limit;
		}
		if ($this->_debug)
		{
			$this->_ticker++;
			$this->_log[] = $sql;
		}
		$this->_errorNum = 0;
		$this->_errorMsg = '';
		$this->_cursor = mysqli_query($this->_connection, $sql);

		if (!$this->_cursor)
		{
			$this->_errorNum = mysqli_errno($this->_connection);
			$this->_errorMsg = mysqli_error($this->_connection)." SQL=$sql";

			if ($this->_debug) {
				JError::raiseError(500, 'JDatabaseMySQL::query: '.$this->_errorNum.' - '.$this->_errorMsg);
			}
			return false;
		}
		return $this->_cursor;
	}

	/**
	 * Description
	 *
	 * @return	int	The number of affected rows in the previous operation
	 * @since	1.0.5
	 */
	public function getAffectedRows()
	{
		return mysqli_affected_rows($this->_connection);
	}

	/**
	 * Execute a batch query
	 *
	 * @return	mixed	A database resource if successful, FALSE if not.
	 */
	public function queryBatch($abort_on_error=true, $p_transaction_safe = false)
	{
		$this->_errorNum = 0;
		$this->_errorMsg = '';
		if ($p_transaction_safe)
		{
			$this->_sql = rtrim($this->_sql, "; \t\r\n\0");
			$si = $this->getVersion();
			preg_match_all("/(\d+)\.(\d+)\.(\d+)/i", $si, $m);
			if ($m[1] >= 4) {
				$this->_sql = 'START TRANSACTION;' . $this->_sql . '; COMMIT;';
			}
			else if ($m[2] >= 23 && $m[3] >= 19) {
				$this->_sql = 'BEGIN WORK;' . $this->_sql . '; COMMIT;';
			}
			else if ($m[2] >= 23 && $m[3] >= 17) {
				$this->_sql = 'BEGIN;' . $this->_sql . '; COMMIT;';
			}
		}
		$query_split = $this->splitSql($this->_sql);
		$error = 0;
		foreach ($query_split as $command_line)
		{
			$command_line = trim($command_line);
			if ($command_line != '')
			{
				$this->_cursor = mysqli_query($this->_connection, $command_line);
				if ($this->_debug)
				{
					$this->_ticker++;
					$this->_log[] = $command_line;
				}
				if (!$this->_cursor)
				{
					$error = 1;
					$this->_errorNum .= mysqli_errno($this->_connection) . ' ';
					$this->_errorMsg .= mysqli_error($this->_connection)." SQL=$command_line <br />";
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
	 * @return	string
	 */
	public function explain()
	{
		$temp = $this->_sql;
		$this->_sql = "EXPLAIN $this->_sql";

		if (!($cur = $this->query())) {
			return null;
		}
		$first = true;

		$buffer = '<table id="explain-sql">';
		$buffer .= '<thead><tr><td colspan="99">'.$this->getQuery().'</td></tr>';
		while ($row = mysqli_fetch_assoc($cur))
		{
			if ($first)
			{
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
		mysqli_free_result($cur);

		$this->_sql = $temp;

		return $buffer;
	}

	/**
	 * Description
	 *
	 * @return	int		The number of rows returned from the most recent query.
	 */
	public function getNumRows($cur=null)
	{
		return mysqli_num_rows($cur ? $cur : $this->_cursor);
	}

	/**
	 * This method loads the first field of the first row returned by the query.
	 *
	 * @return	mixed	The value returned in the query or null if the query failed.
	 */
	public function loadResult()
	{
		if (!($cur = $this->query())) {
			return null;
		}
		$ret = null;
		if ($row = mysqli_fetch_row($cur)) {
			$ret = $row[0];
		}
		mysqli_free_result($cur);
		return $ret;
	}

	/**
	 * Load an array of single field results into an array
	 */
	public function loadResultArray($numinarray = 0)
	{
		if (!($cur = $this->query())) {
			return null;
		}
		$array = array();
		while ($row = mysqli_fetch_row($cur)) {
			$array[] = $row[$numinarray];
		}
		mysqli_free_result($cur);
		return $array;
	}

	/**
	 * Fetch a result row as an associative array
	 *
	 * @return	array
	 */
	public function loadAssoc()
	{
		if (!($cur = $this->query())) {
			return null;
		}
		$ret = null;
		if ($array = mysqli_fetch_assoc($cur)) {
			$ret = $array;
		}
		mysqli_free_result($cur);
		return $ret;
	}

	/**
	 * Load a assoc list of database rows
	 *
	 * @param	string	The field name of a primary key
	 * @return	array	If <var>key</var> is empty as sequential list of returned records.
	 */
	public function loadAssocList($key='')
	{
		if (!($cur = $this->query())) {
			return null;
		}
		$array = array();
		while ($row = mysqli_fetch_assoc($cur))
		{
			if ($key) {
				$array[$row[$key]] = $row;
			}
			else {
				$array[] = $row;
			}
		}
		mysqli_free_result($cur);
		return $array;
	}

	/**
	 * This global function loads the first row of a query into an object
	 *
	 * @return	object
	 */
	public function loadObject()
	{
		if (!($cur = $this->query())) {
			return null;
		}
		$ret = null;
		if ($object = mysqli_fetch_object($cur)) {
			$ret = $object;
		}
		mysqli_free_result($cur);
		return $ret;
	}

	/**
	 * Load a list of database objects
	 *
	 * If <var>key</var> is not empty then the returned array is indexed by the value
	 * the database key.  Returns <var>null</var> if the query fails.
	 *
	 * @param	string	The field name of a primary key
	 * @return	array	If <var>key</var> is empty as sequential list of returned records.
	 */
	public function loadObjectList($key='')
	{
		if (!($cur = $this->query())) {
			return null;
		}
		$array = array();
		while ($row = mysqli_fetch_object($cur))
		{
			if ($key) {
				$array[$row->$key] = $row;
			}
			else {
				$array[] = $row;
			}
		}
		mysqli_free_result($cur);
		return $array;
	}

	/**
	 * Description
	 *
	 * @return The first row of the query.
	 */
	public function loadRow()
	{
		if (!($cur = $this->query())) {
			return null;
		}
		$ret = null;
		if ($row = mysqli_fetch_row($cur)) {
			$ret = $row;
		}
		mysqli_free_result($cur);
		return $ret;
	}

	/**
	 * Load a list of database rows (numeric column indexing)
	 *
	 * If <var>key</var> is not empty then the returned array is indexed by the value
	 * the database key.  Returns <var>null</var> if the query fails.
	 *
	 * @param	string	The field name of a primary key
	 * @return	array	If <var>key</var> is empty as sequential list of returned records.
	 */
	public function loadRowList($key=null)
	{
		if (!($cur = $this->query())) {
			return null;
		}
		$array = array();
		while ($row = mysqli_fetch_row($cur))
		{
			if ($key !== null) {
				$array[$row[$key]] = $row;
			}
			else {
				$array[] = $row;
			}
		}
		mysqli_free_result($cur);
		return $array;
	}

	/**
	 * Load the next row returned by the query.
	 *
	 * @return	mixed	The result of the query as an array, false if there are no more rows, or null on an error.
	 *
	 * @since	1.6.0
	 */
	public function loadNextRow()
	{
		static $cur;

		if (!($cur = $this->query())) {
			return $this->_errorNum ? null : false;
		}

		if ($row = mysqli_fetch_row($cur)) {
			return $row;
		}

		mysql_free_result($cur);
		$cur = null;

		return false;
	}

	/**
	 * Load the next row returned by the query.
	 *
	 * @return	mixed	The result of the query as an object, false if there are no more rows, or null on an error.
	 *
	 * @since	1.6.0
	 */
	public function loadNextObject()
	{
		static $cur;

		if (!($cur = $this->query())) {
			return $this->_errorNum ? null : false;
		}

		if ($row = mysqli_fetch_object($cur)) {
			return $row;
		}

		mysql_free_result($cur);
		$cur = null;

		return false;
	}

	/**
	 * Inserts a row into a table based on an objects properties
	 *
	 * @param	string	The name of the table
	 * @param	object	An object whose properties match table fields
	 * @param	string	The name of the primary key. If provided the object property is updated.
	 */
	public function insertObject($table, &$object, $keyName = NULL)
	{
		$fmtsql = 'INSERT INTO '.$this->nameQuote($table).' (%s) VALUES (%s) ';
		$fields = array();
		foreach (get_object_vars($object) as $k => $v)
		{
			if (is_array($v) or is_object($v) or $v === NULL) {
				continue;
			}
			if ($k[0] == '_') { // internal field
				continue;
			}
			$fields[] = $this->nameQuote($k);
			$values[] = $this->isQuoted($k) ? $this->Quote($v) : (int) $v;
		}
		$this->setQuery(sprintf($fmtsql, implode(",", $fields) ,  implode(",", $values)));
		if (!$this->query()) {
			return false;
		}
		$id = $this->insertid();
		if ($keyName && $id) {
			$object->$keyName = $id;
		}
		return true;
	}

	/**
	 * Description
	 *
	 * @param [type] $updateNulls
	 */
	public function updateObject($table, &$object, $keyName, $updateNulls=true)
	{
		$fmtsql = 'UPDATE '.$this->nameQuote($table).' SET %s WHERE %s';
		$tmp = array();
		foreach (get_object_vars($object) as $k => $v)
		{
			if (is_array($v) or is_object($v) or $k[0] == '_')
			{
				// internal or NA field
				continue;
			}
			if ($k == $keyName) { // PK not to be updated
				$where = $keyName . '=' . $this->Quote($v);
				continue;
			}
			if ($v === null)
			{
				if ($updateNulls) {
					$val = 'NULL';
				}
				else {
					continue;
				}
			}
			else {
				$val = $this->isQuoted($k) ? $this->Quote($v) : (int) $v;
			}
			$tmp[] = $this->nameQuote($k) . '=' . $val;
		}
		$this->setQuery(sprintf($fmtsql, implode(",", $tmp) , $where));
		return $this->query();
	}

	/**
	 * Description
	 */
	public function insertid()
	{
		return mysqli_insert_id($this->_connection);
	}

	/**
	 * Description
	 */
	public function getVersion()
	{
		return mysqli_get_server_info($this->_connection);
	}

	/**
	 * Assumes database collation in use by sampling one text field in one table
	 *
	 * @return	string	Collation in use
	 */
	public function getCollation ()
	{
		if ($this->hasUTF())
		{
			$this->setQuery('SHOW FULL COLUMNS FROM #__content');
			$array = $this->loadAssocList();
			return $array['4']['Collation'];
		}
		else {
			return "N/A (mySQL < 4.1.2)";
		}
	}

	/**
	 * Description
	 *
	 * @return	array	A list of all the tables in the database
	 */
	public function getTableList()
	{
		$this->setQuery('SHOW TABLES');
		return $this->loadResultArray();
	}

	/**
	 * Shows the CREATE TABLE statement that creates the given tables
	 *
	 * @param 	array|string 	A table name or a list of table names
	 * @return 	array A list the create SQL for the tables
	 */
	public function getTableCreate($tables)
	{
		settype($tables, 'array'); //force to array
		$result = array();

		foreach ($tables as $tblval)
		{
			$this->setQuery('SHOW CREATE table ' . $this->getEscaped($tblval));
			$rows = $this->loadRowList();
			foreach ($rows as $row) {
				$result[$tblval] = $row[1];
			}
		}

		return $result;
	}

	/**
	 * Retrieves information about the given tables
	 *
	 * @param 	array|string 	A table name or a list of table names
	 * @param	boolean			Only return field types, default true
	 * @return	array	An array of fields by table
	 */
	public function getTableFields($tables, $typeonly = true)
	{
		settype($tables, 'array'); //force to array
		$result = array();

		foreach ($tables as $tblval)
		{
			$this->setQuery('SHOW FIELDS FROM ' . $tblval);
			$fields = $this->loadObjectList();

			if ($typeonly)
			{
				foreach ($fields as $field) {
					$result[$tblval][$field->Field] = preg_replace("/[(0-9)]/",'', $field->Type);
				}
			}
			else
			{
				foreach ($fields as $field) {
					$result[$tblval][$field->Field] = $field;
				}
			}
		}

		return $result;
	}
}
