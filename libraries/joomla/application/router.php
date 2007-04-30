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
 * Route handling class
 *
 * @static
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package 	Joomla.Framework
 * @subpackage	Application
 * @since		1.5
 */
class JRoute
{
	/**
	 * Translates an internal Joomla URL to a humanly readible URL.
	 *
	 * @access public
	 * @param 	string 	 $url 	Absolute or Relative URI to Joomla resource
	 * @param 	boolean  $xhtml Replace & by &amp; for xml compilance
	 * @param	int		 $ssl	Secure state for the resolved URI
	 * 		 1: Make URI secure using global secure site URI
	 * 		 0: Leave URI in the same secure state as it was passed to the function
	 * 		-1: Make URI unsecure using the global unsecure site URI
	 * @return The translated humanly readible URL
	 */
	function _($url, $xhtml = true, $ssl = 0)
	{
		global $mainframe;

		// If we are in the administrator application return
		if($mainframe->isAdmin()) {
			return  str_replace( '&', '&amp;', str_replace('&amp;', '&', $url));
		}

		// Get the router
		$router =& $mainframe->getRouter();

		// Build route
		$url = $router->build($url);

		/*
		 * Get the secure/unsecure URLs.

		 * If the first 5 characters of the BASE are 'https', then we are on an ssl connection over
		 * https and need to set our secure URL to the current request URL, if not, and the scheme is
		 * 'http', then we need to do a quick string manipulation to switch schemes.
		 */

		$base = JURI::base(); //get base URL

		if ( substr( $base, 0, 5 ) == 'https' )
		{
			$secure 	= $base;
			$unsecure	= 'http'.substr( $base, 5 );
		}
		elseif ( substr( $base, 0, 4 ) == 'http' )
		{
			$secure		= 'https'.substr( $base, 4 );
			$unsecure	= $base;
		}

		// Ensure that proper secure URL is used if ssl flag set secure
		if ($ssl == 1) {
			$url = $secure.$url;
		}

		// Ensure that unsecure URL is used if ssl flag is set to unsecure
		if ($ssl == -1) {
			$url = $unsecure.$url;
		}

		if($xhtml) {
			$url = str_replace( '&', '&amp;', $url );
		}

		return $url;
	}
}

/**
 * Class to create and parse routes
 *
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
	var $_vars = null;

	/**
	 * Class constructor
	 *
	 * @access public
	 */
	function __construct($options = array())
	{
		if(isset($options['mode'])) {
			$this->_mode = $options['mode'];
		} else {
			$this->_mode = 0;
		}
		
		if(isset($options['vars'])) {
			$this->_vars = $options['vars'];
		} else {
			$this->_vars = array();
		}
	}

	/**
	 * Returns a reference to the global Router object, only creating it
	 * if it doesn't already exist.
	 *
	 * This method must be invoked as:
	 * 		<pre>  $router = &JRouter::getInstance();</pre>
	 *
	 * @access	public
	 * @return	JRouter	The Router object.
	 * @since	1.5
	 */
	function &getInstance($options = array())
	{
		static $instance;

		if (!is_object($instance)) {
			$instance = new JRouter($options);
		}

		return $instance;
	}

	/**
	 * Route a request
	 *
	 * @access public
	 */
	function parse($url)
	{
		$uri  =& JURI::getInstance($url);
		$menu =& JMenu::getInstance();
		
		// Get the base and full URLs
		$full = $uri->toString( array('scheme', 'host', 'port', 'path'));
		$base = $uri->base();
		
		$url = urldecode(str_replace($base, '', $full));
		$url = str_replace('index.php', '', $url);
		$url = trim($url , '/');
		
		/* 
		 * Handle empty URL : mysite/ or mysite/index.php 
		 */
		if(empty($url) && !$uri->getQuery()) 
		{
			JRequest::set($this->_vars, 'get', false );
			return;
		}
		
		$this->_vars = array();
		
		/*
		 * Handle routed URL : mysite/index.php/route?var=x
		 */
		if(!empty($url)&& !(int) $uri->getVar('Itemid'))
		{	
			// Set document link
			$doc = & JFactory::getDocument();
			$doc->setLink($base);

			if (!empty($url)) 
			{	
				// Parse application route
				$this->_parseApplicationRoute($url);
				
				// Set the active menu item
				$menu->setActive($this->_vars['itemid']);

				// Parsz component route
				$this->_parseComponentRoute($url);
			}
			
			//Set active menu item
			$item =&$menu->getActive();
		
			//Set the information in the request
			JRequest::set($item->query, 'get', true );
			JRequest::set($this->_vars, 'get', true );
		
			//Set the itemid in the request
			unset($this->_vars['itemid']);
			JRequest::setVar('Itemid', $item->id);
			
			return;
		}
		
		/*
		 * Handle unrouted URL : mysite/index.php?var=x&var=y
		 */
		if($itemid = (int) $uri->getVar('Itemid'))
		{	
			// Set the active menu item
			$item =& $menu->setActive($itemid);
			
			//Set the information in the request
			JRequest::set($item->query, 'get', false );
			return;
		}  
	}

	/**
	 * Function to convert an internal URI to a route
	 *
	 * @param	string	$string	The internal URL
	 * @return	string	The absolute search engine friendly URL
	 * @since	1.5
	 */
	function build($value)
	{
		static $strings;

		if (!$strings) {
			$strings = array();
		}

		// Replace all &amp; with & - ensures cache integrity
		$string = str_replace('&amp;', '&', $value);
		
		// Create full URL if we are only appending variables to it
		if(substr($string, 0, 1) == '&') 
		{
			$vars = array();
			parse_str($string, $vars);
			
			$vars = array_merge($this->_vars, $vars);
			$string = 'index.php?'.JURI::_buildQuery($vars);
		}
		
		if (!isset( $strings[$string] ))
		{	
			// Decompose link into url component parts
			$uri  =& JURI::getInstance(JURI::base().$string);
			$menu =& JMenu::getInstance();

			// If the itemid isn't set in the URL use default
			if(!$itemid = $uri->getVar('Itemid')) {
				$uri->setVar('Itemid', JRequest::getInt('Itemid', $menu->getDefault()));
			}

			// Get the active menu item
			$item = $menu->getItem($uri->getVar('Itemid'));

			// If the option isn't set in the URL use the itemid
			if(!$option = $uri->getVar('option')) {
				$uri->setVar('option', $item->component);
			}

			// rewite URL
			if ($this->_mode && !preg_match('/^(([^:\/\?#]+):)/i', $string) && !strcasecmp(substr($string, 0, 9), 'index.php'))
			{
				$route = ''; //the route created

				$query = $uri->getQuery(true);

				//Built application route
				$app_route = $this->_buildApplicationRoute($query);

				//Build component route
				$com_route = $this->_buildComponentRoute($query);

				//Set query again in the URI
				$uri->setQuery($query);

				//Check if link contained fragment identifiers (ex. #foo)
				$fragment = null;
				if ($fragment = $uri->getFragment())
				{
					// ensure fragment identifiers are compatible with HTML4
					if (preg_match('@^[A-Za-z][A-Za-z0-9:_.-]*$@', $fragment)) {
						$fragment = '#'.$fragment;
					}
				}

				//Check if the component has left any query information unhandled
				if($query = $uri->getQuery()) {
					$query = '?'.$query;
				}

				//Create the route
				$url = $app_route.$com_route.$fragment.$query;

				//Prepend the base URI if we are not using mod_rewrite
				if ($this->_mode == 1) {
					$url = 'index.php/'.$url;
				}

				$strings[$string] = $url;

				return $url;
			}

			$strings[$string] = $uri->toString(array('path', 'query', 'fragment'));
		}

		return $strings[$string];
	}

	/**
	* Parse a application specific route
	*
	* @access protected
	*/
	function _parseApplicationRoute(&$url)
	{
		$menu  =& JMenu::getInstance();
		
		$itemid = null;
		$option = null;
		
		if(substr($url, 0, 9) == 'component')
		{
			$segments = explode('/', $url);
			$url = str_replace('component/'.$segments[1], '', $url);;

			//Get the option
			$option = 'com_'.$segments[1];
			$item   = $menu->getDefault();
			$itemid = $item->id;
		}
		else
		{	
			//Need to reverse the array (highest sublevels first)
			$items = array_reverse($menu->getMenu());

			foreach ($items as $item)
			{
				if(strlen($item->route) > 0 && strpos($url, $item->route) === 0) 
				{
					$url    = str_replace($item->route, '', $url);
					break;
				}
			}

			//Get the option
			$itemid = $item->id;
			$option = $item->component;
		}
		
		$this->_vars['itemid'] = $itemid;
		$this->_vars['option'] = $option;
	}

	/**
	* Parse a component specific route
	*
	* @access protected
	*/
	function _parseComponentRoute($url)
	{
		$segments = explode('/', $url);
		array_shift($segments);

		// Handle pagination
		$limitstart = JRequest::getVar('start', null, 'get');
		if(isset($limitstart)) {
			JRequest::setVar('limitstart', $limitstart);
		}

		// Handle component	route
		$component = $this->_vars['option'];

		// Use the component routing handler if it exists
		$path = JPATH_BASE.DS.'components'.DS.$component.DS.'router.php';

		if (file_exists($path) && count($segments))
		{
			//decode the route segments
			$segments = $this->_decodeSegments($segments);

			require_once $path;
			$function =  substr($component, 4).'ParseRoute';
			$vars =  $function($segments);
				
			$this->_vars = array_merge($this->_vars, $vars);
		}
	}

	/**
	* Build the application specific route
	*
	* @access protected
	*/
	function _buildApplicationRoute(&$query)
	{
		$route = '';

		$menu =& JMenu::getInstance();
		$item = $menu->getItem($query['Itemid']);

		if ($query['option'] == $item->component) {
			$route = $item->route;
		} else {
			$route = 'component/'.substr($query['option'], 4);
		}

		return $route;
	}

	/**
	* Build the component specific route
	*
	* @access protected
	*/
	function _buildComponentRoute(&$query)
	{
		$route = '';

		// Get the component
		$component = preg_replace('/[^A-Z0-9_\.-]/i', '', $query['option']);

		// Unset unneeded query information
		unset($query['option']);
		unset($query['Itemid']);

		// Use the component routing handler if it exists
		$path = JPATH_BASE.DS.'components'.DS.$component.DS.'router.php';

		// Use the custom request handler if it exists
		if (file_exists($path))
		{
			require_once $path;
			$function	= substr($component, 4).'BuildRoute';
			$parts		= $function($query);

			if (isset( $query['limitstart'] ))
			{
				$query['start'] = (int) $query['limitstart'];
				unset($query['limitstart']);
			}

			//encode the route segments
			$parts = $this->_encodeSegments($parts);

			$route = implode('/', $parts);
			$route = ($route) ? '/'.$route : null;
		}

		return $route;
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