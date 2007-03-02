<?php
/**
* @version		$Id: pathway.php 6472 2007-02-03 10:47:26Z pasamio $
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
	 * @param 	string 	$url 	Absolute or Relative URI to Joomla resource
	 * @param	int		$ssl	Secure state for the resolved URI
	 * 		 1: Make URI secure using global secure site URI
	 * 		 0: Leave URI in the same secure state as it was passed to the function
	 * 		-1: Make URI unsecure using the global unsecure site URI
	 * @return The translated humanly readible URL
	 */
	function _($url, $ssl = 0)
	{
		global $mainframe;

		// If we are in the administrator application return
		if($mainframe->isAdmin()) {
			return $url;
		}

		if(!strstr($url, '?')) {

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
	function & getInstance($options = array())
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
		//Create the URI object based on the passed in URL
		$uri = JURI::getInstance($url);
		
		/*
		 * Handle raw URL
		 */ 
		if($itemid = $uri->getVar('Itemid')) 
		{
			//Set active menu item
			$menu  =& JMenu::getInstance();
			$menu->setActive($itemid);
		
			$item = $menu->getActive();
				
			//Set request information
			JRequest::set($item->query, 'get', false);
			return;
		}

		/*
		 * Handle routed URL
		 */ 
		$menu =& JMenu::getInstance();

		// Get the base and full URLs
		$full = $uri->toString( array('scheme', 'host', 'port', 'path'));
		$base = $uri->base();

		$url = urldecode(trim(str_replace($base, '', $full), '/'));
		$url = str_replace('index.php/', '', $url);
		
		// Set document link
		$doc = & JFactory::getDocument();
		$doc->setLink($base);
		
		// Parse the route
		if (!empty($url)) 
		{
			// Parse application segment
			$this->_parseApplicationSegment($url);
							
			//Parse component segment
			$this->_parseComponentSegment($url);
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
		global $mainframe, $Itemid, $option;

		static $strings;

		if (!$strings) {
			$strings = array();
		}
		
		// Replace all &amp; with & - ensures cache integrity
		$string = str_replace('&amp;', '&', $value);

		if (!isset( $strings[$string] ))
		{
			// Decompose link into url component parts
			$uri  =& JURI::getInstance($string);
			$menu =& JMenu::getInstance();

			// If the itemid isn't set in the URL use default
			if(!$itemid = $uri->getVar('Itemid')) {
				$uri->setVar('Itemid', JRequest::getVar('Itemid', $menu->getDefault()));
			}
			
			// Get the active menu item
			$item = $menu->getItem($uri->getVar(('Itemid')));
			
			// If the option isn't set in the URL use the itemid
			if(!$option = $uri->getVar('option')) {
				$uri->setVar('option', $item->component);
			}

			// rewite URL
			if ($this->_mode && !eregi("^(([^:/?#]+):)", $string) && !strcasecmp(substr($string, 0, 9), 'index.php'))
			{
				$route = ''; //the route created

				$query = $uri->getQuery(true);

				//Built application segment
				$app_segment = $this->_buildApplicationSegment($query);
				
				//Build component segment
				$com_segment = $this->_buildComponentSegment($query);

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
				$url = $app_segment.$com_segment.$fragment.$query;

				//Prepend the base URI if we are not using mod_rewrite
				if ($this->_mode == 1) {
					$url = 'index.php/'.$url;
				}

				$strings[$string] = $url;

				return str_replace( '&', '&amp;', $url );
			}

			$strings[$string] = $uri->toString();
		}

		return str_replace( '&', '&amp;', $strings[$string] );
	}
	
	/**
	* Parse a application specific route
	*
	* @access protected
	*/
	function _parseApplicationSegment(&$url)
	{
		if(substr($url, 0, 9) == 'component') 
		{
			$segments = explode('/', $url);
			$url = ''; 
			
			JRequest::setVar('option', 'com_'.$segments[1]);
		}
		else
		{
			$menu  =& JMenu::getInstance();
		
			//Need to reverse the array (highest sublevels first)
			$items = array_reverse($menu->getMenu());

			$itemid = null;
			foreach ($items as $item)
			{
				if(strpos($url, $item->route) === 0)
				{
					$itemid = $item->id;
					$url    = str_replace($item->route, '', $url);
					break;
				}
			}
		
			//Set active menu item
			$menu->setActive($itemid);
			
			//Set request information
			JRequest::set($item->query, 'get', false);		
			JRequest::setVar('Itemid', $itemid);
		}
	}

	/**
	* Parse a component specific route
	*
	* @access protected
	*/
	function _parseComponentSegment($url)
	{
		$segments = explode('/', $url);
		array_shift($segments);
			
		$component = JRequest::getVar('option');
		
		// Use the component routing handler if it exists
		$path = JPATH_BASE.DS.'components'.DS.$component.DS.'router.php';

		if (file_exists($path) && count($segments))
		{
			$limitstart = JRequest::getVar('start', null, 'get');
			if(isset($limitstart)) {
				JRequest::setVar('limitstart', $limitstart);
			}
			
			require_once $path;
			$function =  substr($component, 4).'ParseRoute';
			$function($segments);
		}
	}
	
	/**
	* Build the application specific route
	*
	* @access protected
	*/
	function _buildApplicationSegment(&$query)
	{
		$route = '';
		
		$menu =& JMenu::getInstance();
		$item = $menu->getItem($query['Itemid']);
		
		if($query['option'] == $item->component) {
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
	function _buildComponentSegment(&$query)
	{
		$route = '';
		
		// Get the component
		$component = $query['option'];
		
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

			$route = implode('/', $parts);
			$route = ($route) ? '/'.$route : null;
		}

		return $route;
	}
}
?>