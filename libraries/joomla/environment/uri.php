<?php
/**
 * @version $Id$
 * @package Joomla.Framework
 * @subpackage Environment
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

/**
 * JURI Class
 *
 * This class serves two purposes.  First to parse a URI and provide a common interface
 * for the Joomla Framework to access and manipulate a URI.  Second to attain the URI of
 * the current executing script from the server regardless of server.
 *
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla.Framework
 * @subpackage	Environment
 * @since		1.5
 */
class JURI extends JObject
{
	/**
	 * Original URI
	 *
	 * @var string
	 */
	var $_uri = null;

	/**
	 * Protocol
	 *
	 * @var string
	 */
	var $_scheme = null;

	/**
	 * Host
	 *
	 * @var string
	 */
	var $_host = null;

	/**
	 * Port
	 *
	 * @var integer
	 */
	var $_port = null;

	/**
	 * Username
	 *
	 * @var string
	 */
	var $_user = null;

	/**
	 * Password
	 *
	 * @var string
	 */
	var $_pass = null;

	/**
	 * Path
	 *
	 * @var string
	 */
	var $_path = null;

	/**
	 * Query
	 *
	 * @var string
	 */
	var $_query = null;

	/**
	 * Anchor
	 *
	 * @var string
	 */
	var $_fragment = null;

	/**
	 * Query variable hash
	 *
	 * @var array
	 */
	var $_vars = array ();

	/**
	 * Constructor.
	 * You can pass a URI string to the constructor to initialize a specific URI.
	 *
	 * @param string $uri The optional URI string
	 */
	function __construct($uri = null)
	{
		if ($uri !== null) {
			$this->parse($uri);
		}
	}

	/**
	 * Returns a reference to a global JURI object, only creating it
	 * if it doesn't already exist.
	 *
	 * This method must be invoked as:
	 * 		<pre>  $uri =& JURI::getInstance([$uri]);</pre>
	 *
	 * @static
	 * @param string $uri The URI to parse.  [optional: if null uses script URI]
	 * @return JURI  The URI object.
	 * @since 1.5
	 */
	function &getInstance($uri = 'SERVER')
	{
		static $instances = array();

		if (!isset ($instances[$uri]))
		{
			// Are we obtaining the URI from the server?
			if ($uri == 'SERVER')
			{
				// Determine if the request was over SSL (HTTPS)
				if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) {
					$https = 's://';
				} else {
					$https = '://';
				}

				/*
				 * Since we are assigning the URI from the server variables, we first need
				 * to determine if we are running on apache or IIS.  If PHP_SELF and REQUEST_URI
				 * are present, we will assume we are running on apache.
				 */
				if (!empty ($_SERVER['PHP_SELF']) && !empty ($_SERVER['REQUEST_URI'])) {

					/*
					 * To build the entire URI we need to prepend the protocol, and the http host
					 * to the URI string.
					 */
					$theURI = 'http' . $https . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

				/*
				 * Since we do not have REQUEST_URI to work with, we will assume we are
				 * running on IIS and will therefore need to work some magic with the SCRIPT_NAME and
				 * QUERY_STRING environment variables.
				 */
				}
				 else
				 {
					// IIS uses the SCRIPT_NAME variable instead of a REQUEST_URI variable... thanks, MS
					$theURI = 'http' . $https . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];

					// If the query string exists append it to the URI string
					if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
						$theURI .= '?' . $_SERVER['QUERY_STRING'];
					}
				}
			}
			else
			{
				// We were given a URI
				$theURI = $uri;
			}

			// Create the new JURI instance
			$instances[$uri] = new JURI($theURI);
		}
		return $instances[$uri];
	}

	/**
	 * Returns the base URI for the request.
	 *
	 * @access	public
	 * @return	string	The base URI string
	 * @since	1.5
	 */
	function base()
	{
		static $base;

		// Get the base request URL if not set
		if (!isset($base)) {
			$uri	=& JURI::getInstance();
			$base	= $uri->getScheme().'://';
			$base	.= $uri->getHost();
			if ($port = $uri->getPort()) {
				$base .= ":$port";
			}

			if (strpos(php_sapi_name(), 'cgi') !== false && !empty($_SERVER['REQUEST_URI'])) {
				//Apache CGI
				$base .=  rtrim(dirname($_SERVER['PHP_SELF']), '/\\').'/';
			} else {
				//Others
				$base .=  rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\').'/';
			}
		}
		return $base;
	}

	/**
	 * Method to resolve a URI internally if possible
	 *
	 * @access	public
	 * @param	string	$url	Absolute or Relative URI to Joomla resource
	 * @param	int		$ssl	Secure state for the resolved URI
	 * 		 1: Make URI secure using global secure site URI
	 * 		 0: Leave URI in the same secure state as it was passed to the function
	 * 		-1: Make URI unsecure using the global unsecure site URI
	 * @param	int		$sef	Search engine friendly state for the resolved URI
	 * 		 1: Make URI search engine friendly
	 *  	 0: Leave URI in the same sef state as it was passed to the function
	 * @return	string	Resolved URI
	 * @since 1.5
	 */
	function resolve( $url, $ssl=0, $sef=1 )
	{
		// Initialize variables
		$base = JURI::base();

		/*
		 * First we need to get the secure/unsecure URLs.  If the first 5
		 * characters of the BASE are 'https', then we are on an ssl connection over
		 * https and need to set our secure URL to the current request URL, if not,
		 * and the scheme is 'http', then we need to do a quick string manipulation
		 * to switch schemes.
		 */
		if ( substr( $base, 0, 5 ) == 'https' ) {
			$secure 	= $base;
			$unsecure	= 'http'.substr( $base, 5 );
		} elseif ( substr( $base, 0, 4 ) == 'http' ) {
			$secure		= 'https'.substr( $base, 4 );
			$unsecure	= $base;
		}

		// If $url is / the we set the base url as the given one
		if ($url == '/') {
			$url = $base;
		}

		/*
		 * If we want to SEF the url, and the SEF function exists... lets pass the
		 * url through it.
		 */
		if ( ( $sef == 1 ) && ( function_exists('sefRelToAbs' ) ) ) {
			$url = sefRelToAbs( $url );
		}

		// Were we fed a relative URL?
		if ( substr( $url,0,4 ) != 'http' ) {
			$url = $base . $url;
		}

		/*
		 * Ensure that proper secure site url is used if ssl flag set and url
		 * doesn't already include it
		 */
		if ($ssl == 1 && strstr($url, $unsecure)) {
			$url = str_replace( $unsecure, $secure , $url );
		}

		/*
		 * Ok, now if the SSL flag is set to always unsecure, and we are in SSL
		 * mode, lets change the link to use the unsecure URL
		 */
		if ($ssl == -1 && strstr($url, $secure)) {
			$url = str_replace( $secure, $unsecure , $url );
		}

		return $url;
	}

	/**
	 * Parse a given URI and populate the class fields
	 *
	 * @access public
	 * @param string $uri The URI string to parse
	 * @return boolean True on success
	 * @since 1.5
	 */
	function parse($uri)
	{
		//Initialize variables
		$retval = false;

		// Set the original URI to fall back on
		$this->_uri = $uri;

		/*
		 * Parse the URI and populate the object fields.  If URI is parsed properly,
		 * set method return value to true.
		 */
		if ($_parts = @parse_url($uri)) {
			$retval = true;
		}

		$this->_scheme = isset ($_parts['scheme']) ? $_parts['scheme'] : null;
		$this->_user = isset ($_parts['user']) ? $_parts['user'] : null;
		$this->_pass = isset ($_parts['pass']) ? $_parts['pass'] : null;
		$this->_host = isset ($_parts['host']) ? $_parts['host'] : null;
		$this->_port = isset ($_parts['port']) ? $_parts['port'] : null;
		$this->_path = isset ($_parts['path']) ? $_parts['path'] : null;
		$this->_query = isset ($_parts['query'])? $_parts['query'] : null;
		$this->_fragment = isset ($_parts['fragment']) ? $_parts['fragment'] : null;

		//parse the query
		if(isset ($_parts['query'])) parse_str($_parts['query'], $this->_vars);

		return $retval;
	}

	/**
	 * Returns full uri string
	 *
	 * @access public
	 * @param array $parts An array specifying the parts to render
	 * @return string The rendered URI string
	 * @since 1.5
	 */
	function toString($parts = array('scheme', 'user', 'pass', 'host', 'port', 'path', 'query', 'fragment'))
	{
		$uri = '';
		$uri .= in_array('scheme', $parts)  ? (!empty($this->_scheme) ? $this->_scheme.'://' : '') : '';
		$uri .= in_array('user', $parts)	? $this->_user : '';
		$uri .= in_array('pass', $parts)	? (!empty ($this->_pass) ? ':' : '') .$this->_pass. (!empty ($this->_user) ? '@' : '') : '';
		$uri .= in_array('host', $parts)	? $this->_host : '';
		$uri .= in_array('port', $parts)	? (!empty ($this->_port) ? ':' : '').$this->_port : '';
		$uri .= in_array('path', $parts)	? $this->_path : '';
		$uri .= in_array('query', $parts)	? (!empty ($this->_query) ? '?'.$this->_query : '') : '';
		$uri .= in_array('fragment', $parts)? (!empty ($this->_fragment) ? '#'.$this->_fragment : '') : '';

		return $uri;
	}

	/**
	 * Adds a query variable and value, replacing the value if it
	 * already exists and returning the old value.
	 *
	 * @access public
	 * @param string $name Name of the query variable to set
	 * @param string $value Value of the query variable
	 * @return string Previous value for the query variable
	 * @since 1.5
	 */
	function setVar($name, $value)
	{
		$tmp = @$this->_vars[$name];
		$this->_vars[$name] = is_array($value) ? array_map('urlencode', $value) : urlencode($value);
		return $tmp;
	}

	/**
	 * Returns a query variable by name
	 *
	 * @access public
	 * @param string $name Name of the query variable to get
	 * @return array Query variables
	 * @since 1.5
	 */
	function getVar($name = null, $default=null)
	{
		if(isset($this->_vars[$name])) {
			return $this->_vars[$name];
		}
		return $default;
	}

	/**
	 * Removes an item from the query string variables if it exists
	 *
	 * @access public
	 * @param string $name Name of variable to remove
	 * @since 1.5
	 */
	function delVar($name)
	{
		if (in_array($name, array_keys($this->_vars))) {
			unset ($this->_vars[$name]);
		}
	}

	/**
	 * Sets the query to a supplied string in format:
	 * 		foo=bar&x=y
	 *
	 * @access public
	 * @param mixed (array|string) $query The query string
	 * @since 1.5
	 */
	function setQuery($query)
	{
		if(!is_array($query)) {
			$this->_query = $query;
			parse_str($query, $this->_vars);
		}

		if(is_array($query)) {
			$this->_query = JURI::_buildQuery($query);
			$this->_vars = $query;
		}
	}

	/**
	 * Returns flat query string
	 *
	 * @access public
	 * @return string Query string
	 * @since 1.5
	 */
	function getQuery($toArray = false)
	{
		if($toArray) {
			return $this->_vars;
		}
		return $this->_query;
	}

	/**
	 * Get URI scheme (protocol)
	 * 		ie. http, https, ftp, etc...
	 *
	 * @access public
	 * @return string The URI scheme
	 * @since 1.5
	 */
	function getScheme() {
		return $this->_scheme;
	}

	/**
	 * Set URI scheme (protocol)
	 * 		ie. http, https, ftp, etc...
	 *
	 * @access public
	 * @param string $scheme The URI scheme
	 * @since 1.5
	 */
	function setScheme($scheme) {
		$this->_scheme = $scheme;
	}

	/**
	 * Get URI username
	 * 		returns the username, or null if no username was specified
	 *
	 * @access public
	 * @return string The URI username
	 * @since 1.5
	 */
	function getUser() {
		return $this->_user;
	}

	/**
	 * Set URI username
	 *
	 * @access public
	 * @param string $user The URI username
	 * @since 1.5
	 */
	function setUser($user) {
		$this->_user = $user;
	}

	/**
	 * Get URI password
	 * 		returns the password, or null if no password was specified
	 *
	 * @access public
	 * @return string The URI password
	 * @since 1.5
	 */
	function getPass() {
		return $this->_pass;
	}

	/**
	 * Set URI password
	 *
	 * @access public
	 * @param string $pass The URI password
	 * @since 1.5
	 */
	function setPass($pass) {
		$this->_pass = $pass;
	}

	/**
	 * Get URI host
	 * 		returns the hostname/ip, or null if no hostname/ip was specified
	 *
	 * @access public
	 * @return string The URI host
	 * @since 1.5
	 */
	function getHost() {
		return $this->_host;
	}

	/**
	 * Set URI host
	 *
	 * @access public
	 * @param string $host The URI host
	 * @since 1.5
	 */
	function setHost($host) {
		$this->_host = $host;
	}

	/**
	 * Get URI port
	 * 		returns the port number, or null if no port was specified
	 *
	 * @access public
	 * @return int The URI port number
	 */
	function getPort() {
		return (isset ($this->_port)) ? $this->_port : null;
	}

	/**
	 * Set URI port
	 *
	 * @access public
	 * @param int $port The URI port number
	 * @since 1.5
	 */
	function setPort($port) {
		$this->_port = $port;
	}

	/**
	 * Gets the URI path string
	 *
	 * @access public
	 * @return string The URI path string
	 * @since 1.5
	 */
	function getPath() {
		return $this->_path;
	}

	/**
	 * Set the URI path string
	 *
	 * @access public
	 * @param string $path The URI path string
	 * @since 1.5
	 */
	function setPath($path) {
		$this->_path = $this->_cleanPath($path);
	}

	/**
	 * Get the URI archor string
	 * 		everything after the "#"
	 *
	 * @access public
	 * @return string The URI anchor string
	 * @since 1.5
	 */
	function getFragment() {
		return $this->_fragment;
	}

	/**
	 * Set the URI anchor string
	 * 		everything after the "#"
	 *
	 * @access public
	 * @param string $anchor The URI anchor string
	 * @since 1.5
	 */
	function setFragment($anchor) {
		$this->_fragment = $anchor;
	}

	/**
	 * Checks whether the current URI is using HTTPS
	 *
	 * @access public
	 * @return boolean True if using SSL via HTTPS
	 * @since 1.5
	 */
	function isSSL() {
		return $this->getScheme() == 'https' ? true : false;
	}

	/**
	 * Resolves //, ../ and ./ from a path and returns
	 * the result. Eg:
	 *
	 * /foo/bar/../boo.php	=> /foo/boo.php
	 * /foo/bar/../../boo.php => /boo.php
	 * /foo/bar/.././/boo.php => /foo/boo.php
	 *
	 * @access private
	 * @param string $uri The URI path to clean
	 * @return string Cleaned and resolved URI path
	 * @since 1.5
	 */
	function _cleanPath($path)
	{
		$path = explode('/', str_replace('//', '/', $path));

		for ($i = 0; $i < count($path); $i ++) {
			if ($path[$i] == '.') {
				unset ($path[$i]);
				$path = array_values($path);
				$i --;

			}
			elseif ($path[$i] == '..' AND ($i > 1 OR ($i == 1 AND $path[0] != ''))) {
				unset ($path[$i]);
				unset ($path[$i -1]);
				$path = array_values($path);
				$i -= 2;

			}
			elseif ($path[$i] == '..' AND $i == 1 AND $path[0] == '') {
				unset ($path[$i]);
				$path = array_values($path);
				$i --;

			} else {
				continue;
			}
		}

		return implode('/', $path);
	}

	/**
	 * Build a query from a array (reverse of the PHP parse_str())
	 *
	 * @access private
	 * @return string The resulting query string
	 * @since 1.5
	 * @see parse_str()
	 */
	function _buildQuery ($params, $akey = null)
	{
		if ( !is_array($params) || count($params) == 0 ) {
			return false;
		}

		static $out = array();

		//reset in case we are looping
		if( !isset($akey) && !count($out) )  {
			unset($out);
			$out = array();
		}

		foreach ( $params as $key => $val )
		{
			if ( is_array($val) ) {
				JURI::_buildQuery($val,$key);
				continue;
			}

			$thekey = ( !$akey ) ? $key : $akey.'[]';
			$out[] = $thekey."=".$val;
		}

		return implode("&",$out);
	}
}
?>
