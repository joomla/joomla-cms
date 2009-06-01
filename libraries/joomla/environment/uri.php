<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Environment
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * JURI Class
 *
 * This class serves two purposes.  First to parse a URI and provide a common interface
 * for the Joomla Framework to access and manipulate a URI.  Second to attain the URI of
 * the current executing script from the server regardless of server.
 *
 * @package		Joomla.Framework
 * @subpackage	Environment
 * @since		1.5
 */
class JURI extends JObject
{
	/**
	 * Original URI
	 *
	 * @var		string
	 */
	var $_uri = null;

	/**
	 * Protocol
	 *
	 * @var		string
	 */
	var $_scheme = null;

	/**
	 * Host
	 *
	 * @var		string
	 */
	var $_host = null;

	/**
	 * Port
	 *
	 * @var		integer
	 */
	var $_port = null;

	/**
	 * Username
	 *
	 * @var		string
	 */
	var $_user = null;

	/**
	 * Password
	 *
	 * @var		string
	 */
	var $_pass = null;

	/**
	 * Path
	 *
	 * @var		string
	 */
	var $_path = null;

	/**
	 * Query
	 *
	 * @var		string
	 */
	var $_query = null;

	/**
	 * Anchor
	 *
	 * @var		string
	 */
	var $_fragment = null;

	/**
	 * Query variable hash
	 *
	 * @var		array
	 */
	var $_vars = array ();

	/**
	 * Constructor.
	 * You can pass a URI string to the constructor to initialize a specific URI.
	 *
	 * @param	string $uri The optional URI string
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
	 * 		<pre>  $uri = &JURI::getInstance([$uri]);</pre>
	 *
	 * @static
	 * @param	string $uri The URI to parse.  [optional: if null uses script URI]
	 * @return	JURI  The URI object.
	 * @since	1.5
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

				// Now we need to clean what we got since we can't trust the server var
				$theURI = urldecode($theURI);
				$theURI = str_replace('"', '&quot;',$theURI);
				$theURI = str_replace('<', '&lt;',$theURI);
				$theURI = str_replace('>', '&gt;',$theURI);
				$theURI = preg_replace('/eval\((.*)\)/', '', $theURI);
				$theURI = preg_replace('/[\\\"\\\'][\\s]*javascript:(.*)[\\\"\\\']/', '""', $theURI);
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
	 * @static
	 * @param	boolean $pathonly If false, prepend the scheme, host and port information. Default is false.
	 * @return	string	The base URI string
	 * @since	1.5
	 */
	function base($pathonly = false)
	{
		static $base;

		// Get the base request path
		if (!isset($base))
		{
			$config = &JFactory::getConfig();
			$live_site = $config->getValue('config.live_site');
			if (trim($live_site) != '') {
				$uri = &JURI::getInstance($live_site);
				$base['prefix'] = $uri->toString(array('scheme', 'host', 'port'));
				$base['path'] = rtrim($uri->toString(array('path')), '/\\');
				if (JPATH_BASE == JPATH_ADMINISTRATOR) {
					$base['path'] .= '/administrator';
				}
			} else {
				$uri	         = &JURI::getInstance();
				$base['prefix'] = $uri->toString(array('scheme', 'host', 'port'));

				if (strpos(php_sapi_name(), 'cgi') !== false && !empty($_SERVER['REQUEST_URI'])) {
					//Apache CGI
					$base['path'] =  rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
				} else {
					//Others
					$base['path'] =  rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
				}
			}
		}

		return $pathonly === false ? $base['prefix'].$base['path'].'/' : $base['path'];
	}

	/**
	 * Returns the root URI for the request.
	 *
	 * @access	public
	 * @static
	 * @param	boolean $pathonly If false, prepend the scheme, host and port information. Default is false.
	 * @return	string	The root URI string
	 * @since	1.5
	 */
	function root($pathonly = false, $path = null)
	{
		static $root;

		// Get the scheme
		if (!isset($root))
		{
			$uri	        = &JURI::getInstance(JURI::base());
			$root['prefix'] = $uri->toString(array('scheme', 'host', 'port'));
			$root['path']   = rtrim($uri->toString(array('path')), '/\\');
		}

		// Get the scheme
		if (isset($path)) {
			$root['path']    = $path;
		}

		return $pathonly === false ? $root['prefix'].$root['path'].'/' : $root['path'];
	}

	/**
	 * Returns the URL for the request, minus the query
	 *
	 * @access	public
	 * @return	string
	 * @since	1.5
	 */
	function current()
	{
		static $current;

		// Get the current URL
		if (!isset($current))
		{
			$uri	 = & JURI::getInstance();
			$current = $uri->toString(array('scheme', 'host', 'port', 'path'));
		}

		return $current;
	}

	/**
	 * Parse a given URI and populate the class fields
	 *
	 * @access	public
	 * @param	string $uri The URI string to parse
	 * @return	boolean True on success
	 * @since	1.5
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
		if ($_parts = parse_url($uri)) {
			$retval = true;
		}

		//We need to replace &amp; with & for parse_str to work right...
		if (isset ($_parts['query']) && strpos($_parts['query'], '&amp;')) {
			$_parts['query'] = str_replace('&amp;', '&', $_parts['query']);
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

		if (isset ($_parts['query'])) parse_str($_parts['query'], $this->_vars);
		return $retval;
	}

	/**
	 * Returns full uri string
	 *
	 * @access	public
	 * @param	array $parts An array specifying the parts to render
	 * @return	string The rendered URI string
	 * @since	1.5
	 */
	function toString($parts = array('scheme', 'user', 'pass', 'host', 'port', 'path', 'query', 'fragment'))
	{
		$query = $this->getQuery(); //make sure the query is created

		$uri = '';
		$uri .= in_array('scheme', $parts)  ? (!empty($this->_scheme) ? $this->_scheme.'://' : '') : '';
		$uri .= in_array('user', $parts)	? $this->_user : '';
		$uri .= in_array('pass', $parts)	? (!empty ($this->_pass) ? ':' : '') .$this->_pass. (!empty ($this->_user) ? '@' : '') : '';
		$uri .= in_array('host', $parts)	? $this->_host : '';
		$uri .= in_array('port', $parts)	? (!empty ($this->_port) ? ':' : '').$this->_port : '';
		$uri .= in_array('path', $parts)	? $this->_path : '';
		$uri .= in_array('query', $parts)	? (!empty ($query) ? '?'.$query : '') : '';
		$uri .= in_array('fragment', $parts)? (!empty ($this->_fragment) ? '#'.$this->_fragment : '') : '';

		return $uri;
	}

	/**
	 * Adds a query variable and value, replacing the value if it
	 * already exists and returning the old value.
	 *
	 * @access	public
	 * @param	string $name Name of the query variable to set
	 * @param	string $value Value of the query variable
	 * @return	string Previous value for the query variable
	 * @since	1.5
	 */
	function setVar($name, $value)
	{
		$tmp = @$this->_vars[$name];
		$this->_vars[$name] = $value;

		//empty the query
		$this->_query = null;

		return $tmp;
	}

	/**
	 * Returns a query variable by name
	 *
	 * @access	public
	 * @param	string $name Name of the query variable to get
	 * @return	array Query variables
	 * @since	1.5
	 */
	function getVar($name = null, $default=null)
	{
		if (isset($this->_vars[$name])) {
			return $this->_vars[$name];
		}
		return $default;
	}

	/**
	 * Removes an item from the query string variables if it exists
	 *
	 * @access	public
	 * @param	string $name Name of variable to remove
	 * @since	1.5
	 */
	function delVar($name)
	{
		if (in_array($name, array_keys($this->_vars)))
		{
			unset ($this->_vars[$name]);

			//empty the query
			$this->_query = null;
		}
	}

	/**
	 * Sets the query to a supplied string in format:
	 * 		foo=bar&x=y
	 *
	 * @access	public
	 * @param	mixed (array|string) $query The query string
	 * @since	1.5
	 */
	function setQuery($query)
	{
		if (!is_array($query)) {
			if (strpos($query, '&amp;') !== false)
			{
			   $query = str_replace('&amp;','&',$query);
			}
			parse_str($query, $this->_vars);
		}

		if (is_array($query)) {
			$this->_vars = $query;
		}

		//empty the query
		$this->_query = null;
	}

	/**
	 * Returns flat query string
	 *
	 * @access	public
	 * @return	string Query string
	 * @since	1.5
	 */
	function getQuery($toArray = false)
	{
		if ($toArray) {
			return $this->_vars;
		}

		//If the query is empty build it first
		if (is_null($this->_query)) {
			$this->_query = $this->buildQuery($this->_vars);
		}

		return $this->_query;
	}

	/**
	 * Build a query from a array (reverse of the PHP parse_str())
	 *
	 * @access	public
	 * @return	string The resulting query string
	 * @since	1.5
	 * @see	parse_str()
	 */
	function buildQuery ($params, $akey = null)
	{
		if (!is_array($params) || count($params) == 0) {
			return false;
		}

		$out = array();

		//reset in case we are looping
		if (!isset($akey) && !count($out))  {
			unset($out);
			$out = array();
		}

		foreach ($params as $key => $val)
		{
			if (is_array($val)) {
				$out[] = JURI::buildQuery($val,$key);
				continue;
			}

			$thekey = (!$akey) ? $key : $akey.'['.$key.']';
			$out[] = $thekey."=".urlencode($val);
		}

		return implode("&",$out);
	}

	/**
	 * Get URI scheme (protocol)
	 * 		ie. http, https, ftp, etc...
	 *
	 * @access	public
	 * @return	string The URI scheme
	 * @since	1.5
	 */
	function getScheme() {
		return $this->_scheme;
	}

	/**
	 * Set URI scheme (protocol)
	 * 		ie. http, https, ftp, etc...
	 *
	 * @access	public
	 * @param	string $scheme The URI scheme
	 * @since	1.5
	 */
	function setScheme($scheme) {
		$this->_scheme = $scheme;
	}

	/**
	 * Get URI username
	 * 		returns the username, or null if no username was specified
	 *
	 * @access	public
	 * @return	string The URI username
	 * @since	1.5
	 */
	function getUser() {
		return $this->_user;
	}

	/**
	 * Set URI username
	 *
	 * @access	public
	 * @param	string $user The URI username
	 * @since	1.5
	 */
	function setUser($user) {
		$this->_user = $user;
	}

	/**
	 * Get URI password
	 * 		returns the password, or null if no password was specified
	 *
	 * @access	public
	 * @return	string The URI password
	 * @since	1.5
	 */
	function getPass() {
		return $this->_pass;
	}

	/**
	 * Set URI password
	 *
	 * @access	public
	 * @param	string $pass The URI password
	 * @since	1.5
	 */
	function setPass($pass) {
		$this->_pass = $pass;
	}

	/**
	 * Get URI host
	 * 		returns the hostname/ip, or null if no hostname/ip was specified
	 *
	 * @access	public
	 * @return	string The URI host
	 * @since	1.5
	 */
	function getHost() {
		return $this->_host;
	}

	/**
	 * Set URI host
	 *
	 * @access	public
	 * @param	string $host The URI host
	 * @since	1.5
	 */
	function setHost($host) {
		$this->_host = $host;
	}

	/**
	 * Get URI port
	 * 		returns the port number, or null if no port was specified
	 *
	 * @access	public
	 * @return	int The URI port number
	 */
	function getPort() {
		return (isset ($this->_port)) ? $this->_port : null;
	}

	/**
	 * Set URI port
	 *
	 * @access	public
	 * @param	int $port The URI port number
	 * @since	1.5
	 */
	function setPort($port) {
		$this->_port = $port;
	}

	/**
	 * Gets the URI path string
	 *
	 * @access	public
	 * @return	string The URI path string
	 * @since	1.5
	 */
	function getPath() {
		return $this->_path;
	}

	/**
	 * Set the URI path string
	 *
	 * @access	public
	 * @param	string $path The URI path string
	 * @since	1.5
	 */
	function setPath($path) {
		$this->_path = $this->_cleanPath($path);
	}

	/**
	 * Get the URI archor string
	 * 		everything after the "#"
	 *
	 * @access	public
	 * @return	string The URI anchor string
	 * @since	1.5
	 */
	function getFragment() {
		return $this->_fragment;
	}

	/**
	 * Set the URI anchor string
	 * 		everything after the "#"
	 *
	 * @access	public
	 * @param	string $anchor The URI anchor string
	 * @since	1.5
	 */
	function setFragment($anchor) {
		$this->_fragment = $anchor;
	}

	/**
	 * Checks whether the current URI is using HTTPS
	 *
	 * @access	public
	 * @return	boolean True if using SSL via HTTPS
	 * @since	1.5
	 */
	function isSSL() {
		return $this->getScheme() == 'https' ? true : false;
	}

	/**
	 * Checks if the supplied URL is internal
	 *
	 * @access	public
	 * @param 	string $url The URL to check
	 * @return	boolean True if Internal
	 * @since	1.5
	 */
	function isInternal($url) {
		$uri = &JURI::getInstance($url);
		$base = $uri->toString(array('scheme', 'host', 'port', 'path'));
		$host = $uri->toString(array('scheme', 'host', 'port'));
		if (stripos($base, JURI::base()) !== 0 && !empty($host)) {
			return false;
		}
		return true;
	}

	/**
	 * Resolves //, ../ and ./ from a path and returns
	 * the result. Eg:
	 *
	 * /foo/bar/../boo.php	=> /foo/boo.php
	 * /foo/bar/../../boo.php => /boo.php
	 * /foo/bar/.././/boo.php => /foo/boo.php
	 *
	 * @access	private
	 * @param	string $uri The URI path to clean
	 * @return	string Cleaned and resolved URI path
	 * @since	1.5
	 */
	function _cleanPath($path)
	{
		$path = explode('/', preg_replace('#(/+)#', '/', $path));

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
}