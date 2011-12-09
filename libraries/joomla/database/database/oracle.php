<?php
/**
 * @version     $Id: oracle.php 14094 2010-01-08 18:24:23Z orware $
 * @package     Joomla.Framework
 * @subpackage  Database
 * @copyright   Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();
defined('JPATH_PLATFORM') or die;
/**
 * Oracle database driver
 *
 * @package		Joomla.Framework
 * @subpackage	Database
 * @since		12.1
 */
class JDatabaseOracle extends JDatabase
{
	/**
	 * The database driver name
	 *
	 * @var string
	 */
	public $name = 'oracle';

	/**
	 *  The null/zero date string
	 *
	 * @var string
	 */
	protected $_nullDate = '0000-00-00 00:00:00';

	/**
	 * Quote for named objects
	 *
	 * @var string
	 */
	protected $_nameQuote		= '"';

	/**
	 * The parsed query sql string
	 *
	 * This will actually be an Oracle statement identifier,
	 * not a normal string
	 *
	 * @var string
	 */
	protected $_prepared			= '';

	/**
	 * The variables to be bound by oci_bind_by_name
	 *
	 * @var array
	 */
	protected $_bounded            = '';

	/**
	 * The charset passed into the constructor to create
	 * the database connection.
	 *
	 * @var array
	 */
	protected $_charset            = '';

	/**
	 * The number of rows affected by the previous
	 * INSERT, UPDATE, REPLACE or DELETE query executed
	 * @var int
	 */
	protected $_affectedRows       = '';

	/**
	 * The number of rows returned by the previous
	 * SELECT query executed
	 * @var int
	 */
	protected $_numRows       = '';

	/**
	 * Is used to decide whether a result set
	 * should generate lowercase field names
	 *
	 * @var boolean
	 */
	protected $_tolower = true;

	/**
	 * Is used to decide whether a result set
	 * should return the LOB values or the LOB objects
	 */
	protected $_returnlobs = true;

	/**
	 * Is used to decide whether queries should
	 * be auto-committed or transactional
	 */
	protected $_commitMode = null;

	/**
	 * Database object constructor
	 *
	 * @access	public
	 * @param	array	List of options used to configure the connection
	 * @since	12.1
	 * @see		JDatabase
	 */
	public function __construct( $options )
	{
		$host		= array_key_exists('host', $options)	? $options['host']		: 'localhost';
		$user		= array_key_exists('user', $options)	? $options['user']		: '';
		$password	= array_key_exists('password',$options)	? $options['password']	: '';
		$database	= array_key_exists('database',$options)	? $options['database']	: '';
		$prefix		= array_key_exists('prefix', $options)	? $options['prefix']	: 'jos_';
		$select		= array_key_exists('select', $options)	? $options['select']	: true;
		$port       = array_key_exists('port', $options)    ? $options['port']      : '1521';
		$charset    = array_key_exists('charset', $options) ? $options['charset']   : 'AL32UTF8';
		$dateformat = array_key_exists('dateformat', $options) ? $options['dateformat'] : 'RRRR-MM-DD HH24:MI:SS';
		$timestampformat = array_key_exists('timestampformat', $options) ? $options['timestampformat'] : 'RRRR-MM-DD HH24:MI:SS';

		// perform a number of fatality checks, then return gracefully
		if (!$this->test()) {
			$this->_errorNum = 1;
			$this->_errorMsg = 'The Oracle adapter "oracle" is not available.';
			return;
		}

		// connect to the server
		if (!($this->_resource = @oci_connect($user, $password, "//$host:$port/$database", $charset))) {
			$this->_errorNum = 2;
			$this->_errorMsg = 'Could not connect to Oracle';
			return;
		}

		// Saves the charset used to connect for later retrieval
		$this->_charset = $charset;

		/**
		 * Sets the default Date and Timestamp Formats for the session
		 * If the next line isn't executed on construction
		 * then dates will be returned in the default
		 * Oracle Date Format of: DD-MON-RR and
		 * Oracle Timestamp Format of: DD-MON-RR HH.MI.SSXFF AM
		 */
		$this->setDateFormat($dateformat);
		$this->setTimestampFormat($timestampformat);

		// Sets the default COMMIT mode
		$this->setCommitMode(OCI_COMMIT_ON_SUCCESS);

		// finalize initialization
		parent::__construct($options);
	}

	/**
	 * Database object destructor
	 *
	 * @return boolean
	 * @since 12.1
	 */
	public function __destruct()
	{
		$return = false;
		if (is_resource($this->_resource)) {
			$return = oci_close($this->_resource);
		}
		return $return;
	}

	/**
	 * Test to see if the Oracle connector is available
	 *
	 * @static
	 * @access public
	 * @return boolean  True on success, false otherwise.
	 */
	public function test()
	{
		return (function_exists('oci_connect'));
	}

	/**
	 * Determines if the connection to the server is active.
	 *
	 * @access	public
	 * @return	boolean
	 * @since	12.1
	 */
	public function connected()
	{
		if(is_resource($this->_resource)) {
			//return mysql_ping($this->_resource);
			// TODO See if there is a more elegant way to achieve this with Oracle DB
			return true;
		}
		return false;
	}

	/**
	 * Determines UTF support. Oracle versions 9.2+ will
	 * return true
	 *
	 * @access	public
	 * @return boolean True - UTF is supported
	 */
	public function hasUTF()
	{
		$verParts = explode('.', $this->getVersion());

		if ($verParts[0] > 9 || ($verParts[0] == 9 && $verParts[1] == 2)) {
			if (strripos($this->_charset, 'utf8') !== false) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Custom settings for UTF support
	 *
	 * @access	public
	 */
	public function setUTF()
	{
		return $this->setCharset();
	}

	/**
	 * Get a database escaped string
	 *
	 * @param	string	The string to be escaped
	 * @param	boolean	Optional parameter to provide extra escaping
	 * @return	string
	 * @access	public
	 * @abstract    12.1
	 */
	// TODO Figure out how to do this for Oracle...does oci_bind_by_name do the same thing?
	public function getEscaped($text, $extra = false)
	{
		/*
		 $result = mysql_real_escape_string( $text, $this->_resource );
		 if ($extra) {
			$result = addcslashes( $result, '%_' );
			}
			return $result;
			*/
		return $text;
	}

	/**
	 * Execute the query
	 *
	 * @access	public
	 * @return mixed A database resource if successful, FALSE if not.
	 */
	public function query()
	{
		if (!is_resource($this->_resource)) {
			return false;
		}

		if ($this->_limit > 0 || $this->_offset > 0) {
			$this->_sql = "SELECT joomla2.*
            FROM (
                SELECT ROWNUM AS joomla_db_rownum, joomla1.*
                FROM (
                    " . $this->_sql . "
                ) joomla1
            ) joomla2
            WHERE joomla2.joomla_db_rownum BETWEEN " . ($this->_offset+1) . " AND " . ($this->_offset+$this->_limit);
			$this->setQuery($this->_sql);
			$this->bindVars();
		}
		if ($this->_debug) {
			$this->_ticker++;
			$this->_log[] = $this->_sql;
		}
		$this->_errorNum = 0;
		$this->_errorMsg = '';
		$this->_cursor = oci_execute($this->_prepared, $this->_commitMode);

		if (!$this->_cursor)
		{
			$error = oci_error($this->_prepared);
			$this->_errorNum = $error['code'];
			$this->_errorMsg = $error['message']." SQL=$this->_sql";

			if ($this->_debug) {
				JError::raiseError(500, 'JDatabaseOracle::query: '.$this->_errorNum.' - '.$this->_errorMsg);
			}
			return false;
		}
		//Updates the affectedRows variable with the number of rows returned by the query
		$this->_affectedRows = oci_num_rows($this->_prepared);
		return $this->_prepared;
	}

	/**
	 * Sets the SQL query string for later execution.
	 *
	 * This function replaces a string identifier <var>$prefix</var> with the
	 * string held is the <var>_table_prefix</var> class variable.
	 *
	 * @access public
	 * @param string The SQL query
	 * @param string The offset to start selection
	 * @param string The number of results to return
	 * @param string The common table prefix
	 */
	public function setQuery($sql, $offset = 0, $limit = 0, $prefix='#__')
	{
		$this->_sql		= $this->replacePrefix($sql, $prefix);
		$this->_prepared= oci_parse($this->_resource, $this->_sql);
		$this->_limit	= (int) $limit;
		$this->_offset	= (int) $offset;
	}

	/**
	 * Adds a variable array to the bounded associative array.
	 *
	 * This method adds a new value to the bounded associative array
	 * using the placeholder variable as the key.
	 *
	 * @access public
	 * @param string The Oracle placeholder in your SQL query
	 * @param string The PHP variable you want to bind the placeholder to
	 */
	public function setVar($placeholder, &$var, $maxlength=-1, $type=SQLT_CHR)
	{
		$this->_bounded[$placeholder] = array($var, (int)$maxlength, (int)$type);
	}

	/**
	 * Binds all variables in the bounded associative array
	 *
	 * This method uses oci_bind_by_name to bind all entries in the bounded associative array.
	 *
	 * @access public
	 * @return boolean
	 */
	public function bindVars()
	{
		if ($this->_bounded)
		{
			foreach($this->_bounded as $placeholder => $params)
			{
				$variable =& $params[0];
				$maxlength = $params[1];
				$type = $params[2];
				if(!oci_bind_by_name($this->_prepared, $placeholder, $variable, $maxlength, $type))
				{
					$error = oci_error($this->_prepared);
					$this->_errorNum = $error['code'];
					$this->_errorMsg = $error['message']." BINDVARS=$placeholder, $variable, $maxlength, $type";

					if ($this->_debug)
					{
						JError::raiseError(500, 'JDatabaseOracle::query: '.$this->_errorNum.' - '.$this->_errorMsg );
					}
					return false;
				}
			}
		}

		// Reset the bounded variable for subsequent queries
		$this->_bounded = '';
		return true;
	}

	public function defineVar($placeholder, &$variable, $type=SQLT_CHR)
	{
		if(!oci_define_by_name($this->_prepared, $placeholder, $variable, $type))
		{
			$error = oci_error($this->_prepared);
			$this->_errorNum = $error['code'];
			$this->_errorMsg = $error['message']." DEFINEVAR=$placeholder, $variable, $type";

			if ($this->_debug)
			{
				JError::raiseError(500, 'JDatabaseOracle::query: '.$this->_errorNum.' - '.$this->_errorMsg);
			}
			return false;
		}

		return true;
	}

	/**
	 * Sets the Oracle Date Format for the session
	 * Default date format for Oracle is = DD-MON-RR
	 * The default date format for this driver is:
	 * 'RRRR-MM-DD HH24:MI:SS' since it is the format
	 * that matches the MySQL one used within most Joomla
	 * tables.
	 *
	 * @param mixed $dateformat
	 */
	public function setDateFormat($dateformat = 'DD-MON-RR')
	{
		$this->setQuery("alter session set nls_date_format = '$dateformat'");
		if (!$this->query()) {
			return false;
		}
		return true;
	}

	/**
	 * Returns the current date format
	 * This method should be useful in the case that
	 * somebody actually wants to use a different
	 * date format and needs to check what the current
	 * one is to see if it needs to be changed.
	 *
	 */
	public function getDateFormat()
	{
		$this->setQuery("select value from nls_session_parameters where parameter = 'NLS_DATE_FORMAT'");
		return $this->loadResult();
	}

	/**
	 * Sets the Oracle Timestamp Format for the session
	 * Default date format for Oracle is = DD-MON-RR HH.MI.SSXFF AM
	 * The default date format for this driver is:
	 * 'RRRR-MM-DD HH24:MI:SS' since it is the format
	 * that matches the MySQL one used within most Joomla
	 * tables.
	 *
	 * @param mixed $timestampformat
	 */
	public function setTimestampFormat($timestampformat = 'DD-MON-RR HH.MI.SSXFF AM')
	{
		$this->setQuery("alter session set nls_timestamp_format = '$timestampformat'");
		if (!$this->query()) {
			return false;
		}
		return true;
	}

	/**
	 * Returns the current Timestamp Format
	 * This method should be useful in the case that
	 * somebody actually wants to use a different
	 * timestamp format and needs to check what the current
	 * one is to see if it needs to be changed.
	 *
	 */
	public function getTimestampFormat()
	{

		$this->setQuery("select value from nls_session_parameters where parameter = 'NLS_TIMESTAMP_FORMAT'");
		return $this->loadResult();
	}

	/**
	 * Sets the Oracle Charset for the session.
	 * As far as I've read, the character set cannot
	 * be changed in the middle of a session.
	 *
	 * Please refer to:
	 * http://forums.oracle.com/forums/thread.jspa?messageID=3259228
	 *
	 * @param mixed $dateformat
	 */
	public function setCharset($charset = 'AL32UTF8')
	{
		return false;
	}

	/**
	 * Returns the current character set
	 * This method should be useful in the case that
	 * somebody actually wants to use a different
	 * character set and needs to check what the current
	 * one is to see if it needs to be changed.
	 *
	 */
	public function getCharset()
	{
		return $this->_charset;
	}

	/**
	 * Returns the current character set
	 * This method should be useful in the case that
	 * somebody actually wants to use a different
	 * character set and needs to check what the current
	 * one is to see if it needs to be changed.
	 *
	 */
	public function getDatabaseCharset()
	{
		$this->setQuery("select value from nls_database_parameters where parameter = 'NLS_CHARACTERSET'");
		return $this->loadResult();
	}

	/**
	 * Creates a new descriptor object for use in setVar, setDefine
	 * above.
	 *
	 * @param mixed $type
	 * @return OCI-Lob
	 */
	public function createDescriptor($type)
	{
		if ($type == OCI_D_FILE || $type == OCI_D_LOB || $type == OCI_D_ROWID)
		{
			return oci_new_descriptor($this->_resource, $type);
		}
		return false;
	}

	/**
	 * Get the active query
	 *
	 * @access public
	 * @return string The current value of the internal SQL variable
	 */
	public function getPreparedQuery()
	{
		return $this->_prepared;
	}

	/**
	 * Get the bounded associative array
	 *
	 * @access public
	 * @return string The current value of the internal SQL variable
	 */
	public function getBindVars()
	{
		return $this->_bounded;
	}

	/**
	 * Gets the number of affected rows from
	 * the previous INSERT, UPDATE, DELETE, etc.
	 * operation.
	 *
	 * @access	public
	 * @return int The number of affected rows in the previous operation
	 * @since 12.1
	 */
	public function getAffectedRows()
	{
		return $this->_affectedRows;
	}

	/**
	 * Execute a batch query. For Oracle support
	 * has not been added for batch queries that
	 * also require parameters to be bound.
	 *
	 * @access	public
	 * @return  boolean TRUE if successful, FALSE if not.
	 */
	public function queryBatch($abort_on_error = true, $p_transaction_safe = false)
	{
		$this->_errorNum = 0;
		$this->_errorMsg = '';
		if ($p_transaction_safe) {
			$this->_sql = rtrim($this->_sql, '; \t\r\n\0');
			$si = $this->getVersion();
			preg_match_all("/(\d+)\.(\d+)\.(\d+)/i", $si, $m);
			if ($m[1] >= 4) {
				$this->_sql = 'START TRANSACTION;' . $this->_sql . '; COMMIT;';
			} else if ($m[2] >= 23 && $m[3] >= 19) {
				$this->_sql = 'BEGIN WORK;' . $this->_sql . '; COMMIT;';
			} else if ($m[2] >= 23 && $m[3] >= 17) {
				$this->_sql = 'BEGIN;' . $this->_sql . '; COMMIT;';
			}
		}
		$query_split = $this->splitSql($this->_sql);
		$error = 0;
		foreach ($query_split as $command_line) {
			$command_line = trim($command_line);
			if ($command_line != '') {
				$this->setQuery($command_line);
				$this->query();
				if (!$this->_cursor) {
					$error = 1;
					$this->_errorNum .= oci_error($this->_resource) . ' ';
					$this->_errorMsg .= " SQL=$command_line <br />";
					if ($abort_on_error) {
						return $this->_cursor;
					}
				}
			}
		}
		return $error ? false : true;
	}

	/**
	 * Diagnostic function.
	 * Checks USER_TABLES first to see if the
	 * user already has a table named PLAN_TABLES
	 * created. If not, it is created and then
	 * the EXPLAIN query is run and the results
	 * retrieved from PLAN_TABLE and then deleted.
	 *
	 * @access	public
	 * @return	string
	 */
	public function explain()
	{
		$temp = $this->_sql;

		$this->setQuery("SELECT TABLE_NAME
                         FROM USER_TABLES
                         WHERE USER_TABLES.TABLE_NAME = 'PLAN_TABLE'");

		// If result then that means the plan_table exists
		$result = $this->loadResult();

		if (!$result)
		{
			$this->setQuery('CREATE TABLE "PLAN_TABLE" (
                                          "STATEMENT_ID"  VARCHAR2(30),
                                          "TIMESTAMP"  DATE,
                                          "REMARKS"  VARCHAR2(80),
                                          "OPERATION"  VARCHAR2(30),
                                          "OPTIONS"  VARCHAR2(30),
                                          "OBJECT_NODE"  VARCHAR2(128),
                                          "OBJECT_OWNER"  VARCHAR2(30),
                                          "OBJECT_NAME"  VARCHAR2(30),
                                          "OBJECT_INSTANCE"  NUMBER(22),
                                          "OBJECT_TYPE"  VARCHAR2(30),
                                          "OPTIMIZER"  VARCHAR2(255),
                                          "SEARCH_COLUMNS"  NUMBER(22),
                                          "ID"  NUMBER(22),
                                          "PARENT_ID"  NUMBER(22),
                                          "POSITION"  NUMBER(22),
                                          "COST"  NUMBER(22),
                                          "CARDINALITY"  NUMBER(22),
                                          "BYTES"  NUMBER(22),
                                          "OTHER_TAG"  VARCHAR2(255),
                                          "OTHER"  LONG)'
                                          );
                                          if (!($cur = $this->query())) {
                                          	return null;
                                          }
		}


		$this->_sql = "EXPLAIN PLAN FOR $temp";
		$this->setQuery($this->_sql);

		// This will add the results of the EXPLAIN PLAN
		// into the PLAN_TABLE
		if (!($cur = $this->query())) {
			return null;
		}

		$first = true;

		$buffer = '<table id="explain-sql">';
		$buffer .= '<thead><tr><td colspan="99">'.$this->getQuery().'</td></tr>';

		// SELECT rows that were just added to the PLAN_TABLE
		$this->setQuery("SELECT * FROM PLAN_TABLE");
		if (!($cur = $this->query())) {
			return null;
		}

		while ($row = oci_fetch_assoc($cur)) {
			if ($first) {
				$buffer .= '<tr>';
				foreach ($row as $k=>$v) {
					if ($k == 'STATEMENT_ID' || $k == 'REMARKS' || $k == 'OTHER_TAG' || $k == 'OTHER') {
						continue;
					}
					$buffer .= '<th>'.$k.'</th>';
				}
				$buffer .= '</tr>';
				$first = false;
			}
			$buffer .= '</thead><tbody><tr>';
			foreach ($row as $k=>$v) {
				if ($k == 'STATEMENT_ID' || $k == 'REMARKS' || $k == 'OTHER_TAG' || $k == 'OTHER') {
					continue;
				}
				$buffer .= '<td>'.$v.'</td>';
			}
			$buffer .= '</tr>';
		}
		$buffer .= '</tbody></table>';

		$this->setQuery("DELETE PLAN_TABLE");

		if (!($cur = $this->query())) {
			return null;
		}
		oci_free_statement($cur);

		$this->_sql = $temp;
		$this->setQuery($this->_sql);

		return $buffer;
	}

	/**
	 * Description
	 *
	 * @access	public
	 * @return int The number of rows returned from the most recent query.
	 */
	// TODO Check validity of this method, I don't feel it is the correct way to do it
	public function getNumRows($cur = null)
	{
		return $this->_numRows;
	}

	/**
	 * This method loads the first field of the first row returned by the query.
	 *
	 * @access	public
	 * @return The value returned in the query or null if the query failed.
	 */
	public function loadResult()
	{
		if (!($cur = $this->query())) {
			return null;
		}

		$mode = $this->getMode(true);

		$ret = null;
		if ($row = oci_fetch_array($cur, $mode)) {
			$ret = $row[0];
		}
		//Updates the affectedRows variable with the number of rows returned by the query
		$this->_numRows = oci_num_rows($this->_prepared);
		oci_free_statement($cur);
		return $ret;
	}

	/**
	 * Load an array of single field results into an array
	 *
	 * @access	public
	 */
	public function loadResultArray($numinarray = 0)
	{
		if (!($cur = $this->query())) {
			return null;
		}

		$mode = $this->getMode(true);

		$array = array();
		while ($row = oci_fetch_array($cur, $mode)) {
			$array[] = $row[$numinarray];
		}
		//Updates the affectedRows variable with the number of rows returned by the query
		$this->_numRows = oci_num_rows($this->_prepared);
		oci_free_statement($cur);
		return $array;
	}

	/**
	 * Fetch a result row as an associative array
	 *
	 * @access    public
	 * @return array
	 */
	public function loadAssoc()
	{
		$tolower = $this->_tolower;
		if (!($cur = $this->query())) {
			return null;
		}

		$mode = $this->getMode();

		$ret = null;
		if ($array = oci_fetch_array($cur, $mode)) {
			if ($tolower) {
				$array = array_change_key_case($array, CASE_LOWER);
			}

			$ret = $array;
		}
		//Updates the affectedRows variable with the number of rows returned by the query
		$this->_numRows = oci_num_rows($this->_prepared);
		oci_free_statement($cur);
		return $ret;
	}

	/**
	 * Load a assoc list of database rows
	 *
	 * @access    public
	 * @param string The field name of a primary key
	 * @return array If <var>key</var> is empty as sequential list of returned records.
	 */
	public function loadAssocList($key = '')
	{
		$tolower = $this->_tolower;
		if (!($cur = $this->query())) {
			return null;
		}

		$mode = $this->getMode();

		$array = array();
		while ($row = oci_fetch_array($cur, $mode)) {
			if ($tolower) {
				$row = array_change_key_case($row, CASE_LOWER);
			}

			if ($key) {
				$array[$row[$key]] = $row;
			} else {
				$array[] = $row;
			}
		}
		//Updates the affectedRows variable with the number of rows returned by the query
		$this->_numRows = oci_num_rows($this->_prepared);
		oci_free_statement($cur);
		return $array;
	}

	/**
	 * This global function loads the first row of a query into an object
	 *
	 * @param    string    The name of the class to return (stdClass by default).
	 * @param array The parameters to pass to the constructor function of the new object.
	 * @access    public
	 * @return     object
	 */
	public function loadObject($className = 'stdClass', $params = null)
	{
		$row = $this->loadAssoc();
		if (is_null($row)) {
			return $row;
		} else {
			if ($className === 'stdClass') {
				return (object) $row;
			} else {
				if (is_null($params)) {
					return new $className($row);
				} else {
					return new $className($row, $params);
				}

			}
		}
	}

	/**
	 * Load a list of database objects
	 *
	 * If <var>key</var> is not empty then the returned array is indexed by the value
	 * the database key.  Returns <var>null</var> if the query fails.
	 *
	 * @access    public
	 * @param string The field name of a primary key
	 * @param    string    The name of the class to return (stdClass by default).
	 * @param array The parameters to pass to the constructor function of the new object.
	 * @return array If <var>key</var> is empty as sequential list of returned records.
	 */
	public function loadObjectList($key = '', $className = 'stdClass', $params = null)
	{
		$list = $this->loadAssocList($key);
		if (is_null($list)) {
			return $list;
		}
		foreach($list as $k => $row) {
			if ($className === 'stdClass') {
				$list[$k] = (object) $row;
			} else {
				if (is_null($params)) {
					$list[$k] = new $className($row);
				} else {
					$list[$k] = new $className($row, $params);
				}
			}
		}
		return $list;
	}

	/**
	 * Description
	 *
	 * @access	public
	 * @return The first row of the query.
	 */
	public function loadRow()
	{
		if (!($cur = $this->query())) {
			return null;
		}

		$mode = $this->getMode(true);

		$ret = null;
		if ($row = oci_fetch_array($cur, $mode)) {
			$ret = $row;
		}
		//Updates the affectedRows variable with the number of rows returned by the query
		$this->_numRows = oci_num_rows($this->_prepared);
		oci_free_statement($cur);
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
	public function loadRowList($key=null)
	{
		if (!($cur = $this->query())) {
			return null;
		}

		$mode = $this->getMode(true);

		$array = array();
		while ($row = oci_fetch_array($cur, $mode)) {
			if ($key !== null) {
				$array[$row[$key]] = $row;
			} else {
				$array[] = $row;
			}
		}
		//Updates the affectedRows variable with the number of rows returned by the query
		$this->_numRows = oci_num_rows($this->_prepared);
		oci_free_statement($cur);
		return $array;
	}

	/**
	 * Load the next row returned by the query.
	 *
	 * @return    mixed    The result of the query as an array, false if there are no more rows, or null on an error.
	 *
	 * @since    12.1
	 */
	public function loadNextRow()
	{
		static $cur;
		 
		if (is_null($cur)) {
			if (!($cur = $this->query())) {
				return null;
			}
		}

		$mode = $this->getMode(true);

		if ($row = oci_fetch_array($cur, $mode)) {
			return $row;
		}
		//Updates the affectedRows variable with the number of rows returned by the query
		$this->_numRows = oci_num_rows($this->_prepared);
		oci_free_statement($cur);
		$cur = null;

		return false;
	}

	/**
	 * Load the next row returned by the query.
	 *
	 * @return    mixed    The result of the query as an array, false if there are no more rows, or null on an error.
	 *
	 * @since    12.1
	 */
	public function loadNextAssoc()
	{
		static $cur;
		 
		if (is_null($cur)) {
			if (!($cur = $this->query())) {
				return null;
			}
		}

		$mode = $this->getMode();
		$tolower = $this->_tolower;

		if ($array = oci_fetch_array($cur, $mode)) {
			if ($tolower) {
				$array = array_change_key_case($array, CASE_LOWER);
			}
			return $array;
		}
		//Updates the affectedRows variable with the number of rows returned by the query
		$this->_numRows = oci_num_rows($this->_prepared);
		oci_free_statement($cur);
		$cur = null;

		return false;
	}

	/**
	 * Load the next row returned by the query.
	 *
	 * @return    mixed    The result of the query as an object, false if there are no more rows, or null on an error.
	 *
	 * @since    12.1
	 */
	public function loadNextObject($className = 'stdClass', $params = null)
	{
		$row = $this->loadNextAssoc();
		if (is_null($row) || $row === false) {
			return $row;
		} else {
			if ($className === 'stdClass') {
				return (object) $row;
			} else {
				if (is_null($params)) {
					return new $className($row);
				} else {
					return new $className($row, $params);
				}

			}
		}
	}

	/**
	 * Inserts a row into a table based on an objects properties
	 *
	 * @access	public
	 * @param	string	The name of the table
	 * @param	object	An object whose properties match table fields
	 * @param	string	The name of the primary key. If provided the object property is updated.
	 */
	public function insertObject( $table, &$object, $keyName = NULL )
	{
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

			$fields[] = $k;

			if ( $k == $keyName ) {
				$values[] = $this->nextinsertid($table);
			} else {
				$values[] = $this->Quote($v);
			}
		}
		// Next two lines for debugging generated SQL statement
		$this->setQuery( sprintf( $fmtsql, implode( ",", $fields ) ,  implode( ",", $values ) ) );
		if (!$this->query()) {
			return false;
		}
		return true;
	}

	/**
	 * Updates a row in a table based on an objects properties.
	 *
	 * @param mixed $table
	 * @param mixed $object
	 * @param mixed $keyName
	 * @param mixed $updateNulls
	 */
	public function updateObject( $table, &$object, $keyName, $updateNulls=true )
	{
		$fmtsql = "UPDATE $table SET %s WHERE %s";
		$tmp = array();
		foreach (get_object_vars( $object ) as $k => $v)
		{
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
				$val = $this->isQuoted( $k ) ? $this->Quote( $v ) : (int) $v;
			}
			$tmp[] = $k . '=' . $val;
		}
		$this->setQuery( sprintf( $fmtsql, implode( ",", $tmp ) , $where ) );
		if (!$this->query()) {
			return false;
		}
		return true;
	}

	/**
	 * Returns the latest sequence value for
	 * a table
	 *
	 * @param mixed $tableName
	 * @param mixed $primaryKey
	 * @return string
	 */
	public function insertid($tableName = null, $primaryKey = null)
	{
		if ($tableName !== null) {
			$sequenceName = $tableName;
			if ($primaryKey) {
				$sequenceName .= "_$primaryKey";
			}
			$sequenceName .= '_SEQ';
			return $this->lastSequenceId($sequenceName);
		}
		// No support for IDENTITY columns; return null
		return null;
	}

	/**
	 * Return the most recent value from the specified sequence in the database.
	 * This is supported only on RDBMS brands that support sequences
	 * (e.g. Oracle, PostgreSQL, DB2).  Other RDBMS brands return null.
	 *
	 * @param string $sequenceName
	 * @return string
	 */
	public function lastSequenceId($sequenceName)
	{
		$this->_sql = 'SELECT '.$sequenceName.'.CURRVAL FROM dual';
		$this->setQuery($this->_sql);
		$value = $this->loadResult();
		return $value;
	}

	/**
	 * Returns the next sequence value for
	 * a table
	 *
	 * @param mixed $tableName
	 * @param mixed $primaryKey
	 * @return string
	 */
	public function nextInsertId($tableName = null, $primaryKey = null)
	{
		if ($tableName !== null) {
			$sequenceName = $tableName;
			if ($primaryKey) {
				$sequenceName .= "_$primaryKey";
			}
			$sequenceName .= '_SEQ';
			return $this->nextSequenceId($sequenceName);
		}
		// No support for IDENTITY columns; return null
		return null;
	}

	/**
	 * Generate a new value from the specified sequence in the database, and return it.
	 * This is supported only on RDBMS brands that support sequences
	 * (e.g. Oracle, PostgreSQL, DB2).  Other RDBMS brands return null.
	 *
	 * @param string $sequenceName
	 * @return string
	 */
	public function nextSequenceId($sequenceName)
	{
		$this->_sql = 'SELECT '.$sequenceName.'.NEXTVAL FROM dual';
		$this->setQuery($this->_sql);
		$value = $this->loadResult();
		return $value;
	}

	/**
	 * Returns the Oracle version number
	 *
	 * @access public
	 */
	public function getVersion()
	{
		$this->setQuery("select value from nls_database_parameters where parameter = 'NLS_RDBMS_VERSION'");
		return $this->loadResult();
	}

	/**
	 * Assumes database collation in use by the value
	 * of the NLS_CHARACTERSET parameter
	 *
	 * @access	public
	 * @return string Collation in use
	 */
	public function getCollation()
	{
		return $this->getCharset();
	}

	/**
	 * Gets list of all table_names
	 * for current user
	 *
	 * @access	public
	 * @return array A list of all the tables in the database
	 */
	// TODO Check is this is valid for Oracle DB
	// Visit this link for later review http://forums.devshed.com/oracle-development-96/show-tables-in-oracle-135613.html
	public function getTableList()
	{
		$this->_sql = 'SELECT table_name FROM all_tables';
		$this->setQuery($this->_sql);
		return $this->loadResultArray();
	}

	/**
	 * Shows the CREATE TABLE statement that creates the given tables
	 *
	 * @access	public
	 * @param 	array|string 	A table name or a list of table names
	 * @return 	array A list the create SQL for the tables
	 */
	public function getTableCreate( $tables )
	{
		settype($tables, 'array'); //force to array
		$result = array();

		foreach ($tables as $tblval) {
			$this->setQuery( "select dbms_metadata.get_ddl('TABLE', '".$tblval."') from dual");
			$statement = $this->loadResult();
			$result[$tblval] = $statement;
		}

		return $result;
	}

	/**
	 * Retrieves information about the given tables
	 *
	 * @access	public
	 * @param 	array|string 	A table name or a list of table names
	 * @param	boolean			Only return field types, default true
	 * @return	array An array of fields by table
	 */
	public function getTableFields($tables, $typeonly = true)
	{
		settype($tables, 'array'); //force to array
		$result = array();

		foreach ($tables as $tblval)
		{
			$tblval = strtoupper($tblval);
			$this->setQuery( "SELECT *
                              FROM ALL_TAB_COLUMNS
                              WHERE table_name = '".$tblval."'");
			$fields = $this->loadObjectList('', false);

			if($typeonly)
			{
				foreach ($fields as $field) {
					$result[$tblval][$field->COLUMN_NAME] = preg_replace("/[(0-9)]/",'', $field->DATA_TYPE );
				}
			}
			else
			{
				foreach ($fields as $field) {
					$result[$tblval][$field->COLUMN_NAME] = $field;
				}
			}
		}

		return $result;
	}

	/**
	 * Sets the $_tolower variable to true
	 * so that field names will be created
	 * using lowercase values.
	 *
	 * @return void
	 */
	public function toLower()
	{
		$this->_tolower = true;
	}

	/**
	 * Sets the $_tolower variable to false
	 * so that field names will be created
	 * using uppercase values.
	 *
	 * @return void
	 */
	public function toUpper()
	{
		$this->_tolower = false;
	}

	/**
	 * Sets the $_returnlobs variable to true
	 * so that LOB object values will be
	 * returned rather than an OCI-Lob Object.
	 *
	 * @return void
	 */
	public function returnLobValues()
	{
		$this->_returnlobs = true;
	}

	/**
	 * Sets the $_returnlobs variable to false
	 * so that OCI-Lob Objects will be returned.
	 *
	 * @return void
	 */
	public function returnLobObjects()
	{
		$this->_returnlobs = false;
	}

	/**
	 * Depending on the value for _returnlobs,
	 * this method returns the proper constant
	 * combinations to be passed to the oci* functions
	 *
	 * @return int
	 */
	public function getMode($numeric = false)
	{
		if ($numeric === false) {
			if ($this->_returnlobs) {
				$mode = OCI_ASSOC+OCI_RETURN_NULLS+OCI_RETURN_LOBS;
			}
			else {
				$mode = OCI_ASSOC+OCI_RETURN_NULLS;
			}
		} else {
			if ($this->_returnlobs) {
				$mode = OCI_NUM+OCI_RETURN_NULLS+OCI_RETURN_LOBS;
			}
			else {
				$mode = OCI_NUM+OCI_RETURN_NULLS;
			}
		}

		return $mode;
	}

	/**
	 * Gets the commit mode that will be used for queries
	 *
	 * @return int
	 */
	public function getCommitMode()
	{
		return $this->_commitMode;
	}

	/**
	 * Sets the commit mode to use for queries
	 *
	 * @return void
	 */
	public function setCommitMode($commit_mode)
	{
		$this->_commitMode = $commit_mode;
	}
}