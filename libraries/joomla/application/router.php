<?php
/**
 * @version		$Id:router.php 8876 2007-09-13 22:54:03Z jinx $
 * @package		Joomla.Framework
 * @subpackage	Application
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * Set the available masks for the routing mode
 */
define('JROUTER_MODE_RAW', 0);
define('JROUTER_MODE_SEF', 1);

/**
 * Class to create and parse routes
 *
 * @abstract
 * @package 	Joomla.Framework
 * @subpackage	Application
 * @since		1.5
 */
class JRouter extends JObject
{
	/**
	 * The rewrite mode
	 *
	 * @access protected
	 * @var integer
	 */
	var $_mode = null;

	/**
	 * An array of variables
	 *
	 * @access protected
	 * @var array
	 */
	var $_vars = array();

	/**
	 * An array of rules
	 *
	 * @access protected
	 * @var array
	 */
	var $_rules = array(
		'build' => array(),
		'parse' => array()
	);

	/**
	 * Class constructor
	 *
	 * @access public
	 */
	function __construct($options = array())
	{
		if (array_key_exists('mode', $options)) {
			$this->_mode = $options['mode'];
		} else {
			$this->_mode = JROUTER_MODE_RAW;
		}
	}

	/**
	 * Returns the global JRouter object, only creating it if it
	 * doesn't already exist.
	 *
	 * @param string  $client  The name of the client
	 * @param array   $options An associative array of options
	 * @return	JRouter	A router object.
	 */
	public static function getInstance($client, $options = array())
	{
		static $instances;

		if (!isset($instances)) {
			$instances = array();
		}

		if (empty($instances[$client]))
		{
			//Load the router object
			$info = &JApplicationHelper::getClientInfo($client, true);

			$path = $info->path.DS.'includes'.DS.'router.php';
			if (file_exists($path))
			{
				require_once $path;

				// Create a JRouter object
				$classname = 'JRouter'.ucfirst($client);
				$instance = new $classname($options);
			}
			else
			{
				$error = JError::raiseError(500, 'Unable to load router: '.$client);
				return $error;
			}

			$instances[$client] = & $instance;
		}

		return $instances[$client];
	}

	/**
	 *  Function to convert a route to an internal URI
	 */
	public function parse(&$uri)
	{
		$vars = array();

		// Process the parsed variables based on custom defined rules
		$vars = $this->_processParseRules($uri);

		// Parse RAW URL
		if ($this->_mode == JROUTER_MODE_RAW) {
			$vars += $this->_parseRawRoute($uri);
		}

		// Parse SEF URL
		if ($this->_mode == JROUTER_MODE_SEF) {
			$vars += $vars + $this->_parseSefRoute($uri);
		}

	 	return  array_merge($this->getVars(), $vars);
	}

	/**
	 * Function to convert an internal URI to a route
	 *
	 * @param	string	$string	The internal URL
	 * @return	string	The absolute search engine friendly URL
	 */
	function build($url)
	{
		//Create the URI object
		$uri = &$this->_createURI($url);

		//Process the uri information based on custom defined rules
		$this->_processBuildRules($uri);

		// Build RAW URL
		if ($this->_mode == JROUTER_MODE_RAW) {
			$this->_buildRawRoute($uri);
		}

		// Build SEF URL : mysite/route/index.php?var=x
		if ($this->_mode == JROUTER_MODE_SEF) {
			$this->_buildSefRoute($uri);
		}

		return $uri;
	}

	/**
	 * Get the router mode
	 *
	 * @access public
	 */
	function getMode() {
		return $this->_mode;
	}

	/**
	 * Get the router mode
	 *
	 * @access public
	 */
	function setMode($mode) {
		$this->_mode = $mode;
	}

	/**
	 * Set a router variable, creating it if it doesn't exist
	 *
	 * @access	public
	 * @param	string  $key    The name of the variable
	 * @param	mixed   $value  The value of the variable
	 * @param	boolean $create If True, the variable will be created if it doesn't exist yet
 	 */
	function setVar($key, $value, $create = true) {

		if (!$create && array_key_exists($key, $this->_vars)) {
			$this->_vars[$key] = $value;
		} else {
			$this->_vars[$key] = $value;
		}
	}

	/**
	 * Set the router variable array
	 *
	 * @access	public
	 * @param	array   $vars   An associative array with variables
	 * @param	boolean $create If True, the array will be merged instead of overwritten
 	 */
	function setVars($vars = array(), $merge = true) {

		if ($merge) {
			$this->_vars = array_merge($this->_vars, $vars);
		} else {
			$this->_vars = $vars;
		}
	}

	/**
	 * Get a router variable
	 *
	 * @access	public
	 * @param	string $key   The name of the variable
	 * $return  mixed  Value of the variable
 	 */
	function getVar($key)
	{
		$result = null;
		if (isset($this->_vars[$key])) {
			$result = $this->_vars[$key];
		}
		return $result;
	}

	/**
	 * Get the router variable array
	 *
	 * @access	public
	 * @return  array An associative array of router variables
 	 */
	function getVars() {
		return $this->_vars;
	}

	/**
	 * Attach a build rule
	 *
	 * @access	public
	 * @param   callback $callback The function to be called.
 	 */
	function attachBuildRule($callback)
	{
		$this->_rules['build'][] = $callback;
	}

	/**
	 * Attach a parse rule
	 *
	 * @access	public
	 * @param   callback $callback The function to be called.
 	 */
	function attachParseRule($callback)
	{
		$this->_rules['parse'][] = $callback;
	}

	/**
	 * Function to convert a raw route to an internal URI
	 *
	 * @abstract
	 * @access protected
	 */
	function _parseRawRoute(&$uri)
	{
		return false;
	}

	/**
	 *  Function to convert a sef route to an internal URI
	 *
	 * @abstract
	 * @access protected
	 */
	function _parseSefRoute(&$uri)
	{
		return false;
	}

	/**
	 * Function to build a raw route
	 *
	 * @abstract
	 * @access protected
	 */
	function _buildRawRoute(&$uri)
	{

	}

	/**
	 * Function to build a sef route
	 *
	 * @abstract
	 * @access protected
	 */
	function _buildSefRoute(&$uri)
	{

	}

	/**
	 * Process the parsed router variables based on custom defined rules
	 *
	 * @abstract
	 * @access protected
	 */
	function _processParseRules(&$uri)
	{
		$vars = array();

		foreach($this->_rules['parse'] as $rule) {
			$vars = call_user_func_array($rule, array(&$this, &$uri));
		}

		return $vars;
	}

	/**
	 * Process the build uri query data based on custom defined rules
	 *
	 * @abstract
	 * @access protected
	 */
	function _processBuildRules(&$uri)
	{
		foreach($this->_rules['build'] as $rule) {
			call_user_func_array($rule, array(&$this, &$uri));
		}
	}

	/**
	 * Create a uri based on a full or partial url string
	 *
	 * @access	protected
	 * @return  JURI  A JURI object
 	 */
	function _createURI($url)
	{
		// Create full URL if we are only appending variables to it
		if (substr($url, 0, 1) == '&')
		{
			$vars = array();
			if (strpos($url, '&amp;') !== false)
			{
			   $url = str_replace('&amp;','&',$url);
			}

			parse_str($url, $vars);

			$vars = array_merge($this->getVars(), $vars);

			foreach($vars as $key => $var)
			{
				if ($var == "") {
					unset($vars[$key]);
				}
			}

			$url = 'index.php?'.JURI::buildQuery($vars);
		}

		// Decompose link into url component parts
		return new JURI($url);
	}

	/**
	 * Encode route segments
	 *
	 * @access	protected
	 * @param   array 	An array of route segments
	 * @return  array
 	 */
	function _encodeSegments($segments)
	{
		$total = count($segments);
		for ($i=0; $i<$total; $i++) {
			$segments[$i] = str_replace(':', '-', $segments[$i]);
		}

		return $segments;
	}

	/**
	 * Decode route segments
	 *
	 * @access	protected
	 * @param   array 	An array of route segments
	 * @return  array
 	 */
	function _decodeSegments($segments)
	{
		$total = count($segments);
		for ($i=0; $i<$total; $i++)  {
			$segments[$i] = preg_replace('/-/', ':', $segments[$i], 1);
		}

		return $segments;
	}
}
