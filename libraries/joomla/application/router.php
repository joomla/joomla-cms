<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Application
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
 * Set the available masks for the routing mode
 */
define('JROUTER_MODE_RAW', 0);
define('JROUTER_MODE_SEF', 1);

/**
 * Class to create and parse routes
 *
 * @abstract
 * @author		Johan Janssens <johan.janssens@joomla.org>
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
	 * An route prefix
	 *
	 * @access protected
	 * @var string
	 */
	var $_prefix = null;
	
	/**
	 * Class constructor
	 *
	 * @access public
	 */
	function __construct($options = array())
	{
		if(array_key_exists('mode', $options)) {
			$this->_mode = $options['mode'];
		} else {
			$this->_mode = JROUTER_MODE_RAW;
		}
		
		if(array_key_exists('prefix', $options)) {
			$this->_prefix = $options['prefix'];
		} 
	}
	
	/**
	 * Returns a reference to the global JRouter object, only creating it if it
	 * doesn't already exist.
	 *
	 * This method must be invoked as:
	 * 		<pre>  $menu = &JRouter::getInstance();</pre>
	 *
	 * @access	public
	 * @param string  $client  The name of the client
	 * @param array   $options An associative array of options
	 * @return	JRouter	A router object.
	 * @since	1.5
	 */
	function &getInstance($client, $options = array())
	{
		//Load the router object
		$info =& JApplicationHelper::getClientInfo($client, true);
			
		$path = $info->path.DS.'includes'.DS.'router.php';
		if(file_exists($path)) 
		{
			require_once $path;
				
			// Create a JRouter object
			$classname = 'JRouter'.ucfirst($client);
			$instance = new $classname($options);
		} 
		else 
		{
			$error = new JException( E_ERROR, 500, 'Unable to load router: '.$classname);
			return $error;
		}
			
		return $instance;
	}

	/**
	 * Route a request
	 *
	 * @access public
	 * @since	1.5
	 */
	function parse($url)
	{
		return true;
	}

	/**
	 * Function to convert an internal URI to a route
	 *
	 * @param	string	$string	The internal URL
	 * @return	string	The absolute search engine friendly URL
	 * @since	1.5
	 */
	function build($url)
	{
		$url = str_replace('&amp;', '&', $url);

		return $url;
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
	 * @since	1.5
 	 */
	function setVar($key, $value, $create = true) {
		
		if(!$create && array_key_exists($key, $this->_vars)) {
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
	 * @since	1.5
 	 */
	function setVars($vars = array(), $merge = true) {
		
		if($merge) {
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
	 * @since	1.5
 	 */
	function getVar($key) 
	{
		$result = null;
		if(isset($this->_vars, $key)) {
			$result = $this->_vars[$key];
		}
		return $result;
	}
	
	/**
	 * Get the router variable array
	 *
	 * @access	public
	 * @return  array An associative array of router variables
	 * @since	1.5
 	 */
	function getVars() {
		return $this->_vars;
	}
	
	/**
	 * Create a uri based on a full or partial url string
	 *
	 * @access	protected
	 * @return  JURI  A JURI object
	 * @since	1.5
 	 */
	function &_createURI($url)
	{
		// Create full URL if we are only appending variables to it
		if(substr($url, 0, 1) == '&')
		{
			$vars = array();
			parse_str($url, $vars);

			$vars = array_merge($this->getVars(), $vars);
			
			foreach($vars as $key => $var) 
			{
				if(empty($var)) {
					unset($vars[$key]);
				}
			}
			
			$url = 'index.php?'.JURI::_buildQuery($vars);
		}
		
		// Decompose link into url component parts
		$uri = new JURI(JURI::base().$url);
		return $uri;
	}
	
	function _encodeSegments($segments)
	{
		$total = count($segments);
		for($i=0; $i<$total; $i++) {
			$segments[$i] = str_replace(':', '-', $segments[$i]);
		}

		return $segments;
	}

	function _decodeSegments($segments)
	{
		$total = count($segments);
		for($i=0; $i<$total; $i++)  {
			$segments[$i] = preg_replace('/-/', ':', $segments[$i], 1);
		}

		return $segments;
	}
}