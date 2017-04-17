<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 6/20/14 1:56 PM $
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Database\DatabaseDriverInterface;

defined('CBLIB') or die();

/**
 * CBSessionStorage Class implementation
 * CLASS implements a minimal database connection working with CB database when present
 * and with MySql directly if not.
 *
 * @deprecated 2.0 Use CBLib\Database\DatabaseDriverInterface
 * @see \CBLib\Database\DatabaseDriverInterface
 */
class CBSessionStorage
{
	/**
	 * Database interface
	 * @var CBSessionStorage|DatabaseDriverInterface
	 */
	public $_db;
	/**
	 * Table prefix
	 * @var string
	 */
	public $_table_prefix;
	/**
	 * Mysql Database connection resource
	 * @var resource
	 */
	public $_resource;
	/**
	 * Mysql Query resource
	 * @var resource
	 */
	public $_cursor;
	/**
	 * SQL query
	 * @var string
	 */
	public $_sql;

	/**
	 * Constructor
	 *
	 * @deprecated 2.0 Use CBLib\Database\DatabaseDriverInterface
	 */
	public function __construct( )
	{
	}

	/**
	 * Connects to database layer or to mysql database
	 *
	 * @return CBSessionStorage   or $_CB_database
	 */
	public function connect( )
	{
		if ( ! $this->_db ) {
			global $_CB_database;
			if ( $_CB_database ) {
				$this->_db					=	$_CB_database;
			} else {
				$absolute_path				=	preg_replace( '%(/[^/]+){5}$%', '', str_replace( '\\', '/', dirname( __FILE__ ) ) );
				$config						=	file_get_contents( $absolute_path . '/configuration.php' );
				$db_host					=	$this->_parseConfig( $config, 'host' );
				$db_user					=	$this->_parseConfig( $config, 'user' );
				$db_password				=	$this->_parseConfig( $config, 'password' );
				$db_db						=	$this->_parseConfig( $config, 'db' );
				/** @noinspection PhpDeprecationInspection */
				$this->_db					=	new self();
				$this->_db->_resource		=	mysql_connect( $db_host, $db_user, $db_password );
				if ( $this->_db->_resource === false ) {
					die( 'Session connect error!' );
				}
				if ( ! mysql_select_db( $db_db, $this->_db->_resource ) ) {
					die( 'Session database error!' );
				}
				$this->_db->_table_prefix	=	$this->_parseConfig( $config, 'dbprefix' );
			}
		}
		return $this->_db;
	}

	/**
	 * Parses a mambo/joomla/compatibles configuration file content
	 * @access private
	 *
	 * @param  string  $config   Content of config file
	 * @param  string  $varName  Name of variable to fetch
	 * @return string            Content of variable or NULL
	 */
	private function _parseConfig( $config, $varName )
	{
		$matches		=	null;
		preg_match( '/\$(?:mosConfig_)?' . $varName . '\s*=\s*\'([^\']*)\'/', $config, $matches );
		if ( isset($matches[1]) ) {
			return $matches[1];
		} else {
			return null;
		}
	}

	/**
	 * Sets the SQL query string for later execution.
	 *
	 * This function replaces a string identifier $prefix with the
	 * string held is the _table_prefix class variable.
	 *
	 * @param string $sql     The SQL query
	 * @param int    $offset  The offset to start selection
	 * @param int    $limit   The number of results to return
	 * @param string $prefix  The common table prefix search for replacement string
	 */
	public function setQuery( $sql, $offset = 0, $limit = 0, $prefix='#__' )
	{
		if ( $offset || $limit ) {
			$sql		.=	" LIMIT ";
			if ( $offset ) {
				$sql	.=	( (int) abs( $offset ) ) . ', ';
			}
			$sql		.=	( (int) abs( $limit ) );
		}
		$this->_sql		=	$this->replacePrefix( $sql, $prefix );
	}

	/**
	 * Replace $prefix with $this->_table_prefix (simplified method)
	 * @access private
	 *
	 * @param  string  $sql      SQL query
	 * @param  string  $prefix   Common table prefix
	 * @return string            Replaced prefix
	 */
	public function replacePrefix( $sql, $prefix = '#__' )
	{
		return str_replace( $prefix, $this->_table_prefix, $sql );
	}

	/**
	 * Execute the query
	 *
	 * @param  string            $sql  The query (optional, it will use the setQuery one otherwise)
	 * @return resource|boolean        A database resource if successful, FALSE if not.
	 */
	public function query( $sql = null )
	{
		if ( $sql ) {
			$this->setQuery( $sql );
		}
		$this->_cursor	=	mysql_query( $this->_sql, $this->_resource );
		return $this->_cursor;
	}

	/**
	 * Fetch a result row as an associative array
	 *
	 * @return array
	 */
	public function loadAssoc( )
	{
		if ( ! ( $cur = $this->query() ) ) {
			$result		=	null;
		} else {
			$result		=	mysql_fetch_assoc( $cur );
			if ( ! $result ) {
				$result	=	null;
			}
			mysql_free_result( $cur );
		}
		return $result;
	}

	/**
	 * Get a database escaped string
	 *
	 * @param  string  $text
	 * @return string
	 */
	public function getEscaped( $text )
	{
		return mysql_real_escape_string( $text, $this->_resource );
	}

	/**
	 * Get a quoted database escaped string
	 *
	 * @param  string  $text
	 * @return string
	 */
	public function Quote( $text )
	{
		return '\'' . $this->getEscaped( $text ) . '\'';
	}

	/**
	 * Quote an identifier name (field, table, etc)
	 *
	 * @param  string  $s  The name
	 * @return string      The quoted name
	 */
	public function NameQuote( $s )
	{
		return '`' . $s . '`';
	}
}
