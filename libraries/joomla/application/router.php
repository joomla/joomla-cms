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
	 * Class constructor
	 *
	 * @access public
	 */
	function __construct() {
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
	function & getInstance()
	{
		static $instance;

		if (!is_object($instance)) {
			$instance = new JRouter();
		}

		return $instance;
	}

   /**
	* Route a request
	*
	* @access public
	*/
	function parseRoute($uri)
	{
		$menu =& JMenu::getInstance();

		// Check entry point
		$path = $uri->toString();
		if (!(preg_match( '#index\d?\.php#', $path) || (strpos($path, 'feed.php') == false))) {
			return;
		}

		// Get the base and full URLs
		$full = $uri->toString( array('scheme', 'host', 'port', 'path'));
		$base = JURI::base();

		$url = urldecode(trim(str_replace($base, '', $full), '/'));
		$url = str_replace('index.php/', '', $url);

		if (!$itemid = JRequest::getVar('Itemid'))
		{
			// Set document link
			$doc = & JFactory::getDocument();
			$doc->setLink($base);

			if (!empty($url))
			{
				//Need to reverse the array (highest sublevels first)
				$items = array_reverse($menu->getMenu());

				foreach ($items as $item)
				{
					if(strpos($url, $item->route) === 0) {
						$itemid = $item->id;
						$url    = str_replace($item->route, '', $url);
						break;
					}
				}
			}
		}

		// tcp added, temp fix	
		$item = $itemid ? $menu->getItem($itemid) : $menu->getDefault();
		$menu->setActive($item->id);

		JRequest::set($item->query, 'get', false);	
		JRequest::setVar('Itemid', ($item) ? $item->id : null, 'get');
		
		//Parse component route
		$this->parseComponentRoute($item->component, $url);
	}

	/**
 	 * Function to convert an internal URI to a route
 	 *
 	 * @param	string	$string	The internal URL
 	 * @return	string	The absolute search engine friendly URL
 	 * @since	1.5
 	 */
	function buildRoute($value)
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
			// Initialize some variables
			$config	= & JFactory::getConfig();

			// Get config variables
			$mode    = $config->getValue('config.sef_rewrite');
			$rewrite = $config->getValue('config.sef');

			// Home index.php
			if ($string == 'index.php') {
				$string = '';
			}

			// Decompose link into url component parts
			$uri  =& JURI::getInstance($string);
			$menu =& JMenu::getInstance();

			// If the itemid isn't set in the URL use default
			if(!$itemid = $uri->getVar('Itemid'))
			{
				if($itemid = JRequest::getVar('Itemid')) {
					$uri->setVar('Itemid', $itemid);
				}
			}

			// rewite URL
			if ($itemid && $rewrite && !eregi("^(([^:/?#]+):)", $string) && !strcasecmp(substr($string, 0, 9), 'index.php'))
			{
				$route = ''; //the route created

				// get the menu item for the itemid
				$item = $menu->getItem($itemid);

				$uri->delVar('option'); //don't need the option anymore
				$uri->delVar('Itemid'); //don't need the itemid anymore
				$query = $uri->getQuery(true);
				
				//Build component route
				$route = $this->buildComponentRoute($item->component, $query);
				
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
				$url = $item->route.$route.$fragment.$query;

				//Prepend the base URI if we are not using mod_rewrite
				if (!$mode) {
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
	* Parse a component specific route
	*
	* @access public
	*/
	function parseComponentRoute($component, $route)
	{
		// Use the component routing handler if it exists
		$path = JPATH_BASE.DS.'components'.DS.$component.DS.'route.php';

		$routeArray = explode('/', $route);
		array_shift($routeArray);

		if (file_exists($path) && count($routeArray))
		{
			// Handle Pagination
			$nArray = count($routeArray);
			$last = @$routeArray[$nArray-1];
			if ($last == 'all')
			{
				array_pop( $routeArray );
				JRequest::setVar('limit', 0, 'get');
				JRequest::setVar('limitstart', 0, 'get');
			}
			elseif (strpos( $last, 'page' ) === 0)
			{
				array_pop( $routeArray );
				$pts		= explode( ':', $last );
				$limit		= @$pts[1];
				$limitstart	= (max( 1, intval( str_replace( 'page', '', $pts[0] ) ) ) - 1)  * $limit;
				JRequest::setVar('limit', $limit, 'get');
				JRequest::setVar('limitstart', $limitstart, 'get');
			}
			
			require_once $path;
			$function =  substr($component, 4).'ParseRoute';
			$function($routeArray);
		}
	}
	
	/**
	* Build a component specific route
	*
	* @access public
	*/
	function buildComponentRoute($component, &$query)
	{
		$route = '';
		
		// Use the component routing handler if it exists
		$path = JPATH_BASE.DS.'components'.DS.$component.DS.'route.php';
		
		// Use the custom request handler if it exists
		if (file_exists($path))
		{
			require_once $path;
			$function	= substr($component, 4).'BuildRoute';
			$parts		= $function($query);
			
			if (isset( $query['limit'] ))
			{
				// Do all pages if limit = 0
				if ($query['limit'] == 0) {
					$parts[] = 'all';
				} else {
					$limit		= (int) $query['limit'];
					$limitstart	= (int) @$query['limitstart'];
					$page		= floor( $limitstart / $limit ) + 1;
					$parts[]	= 'page'.$page.':'.$limit;
				}
				
				unset($query['limit']);
				unset($query['limitstart']);
			}

			$route = implode('/', $parts);
			$route = ($route) ? '/'.$route : null;
		}
		
		return $route;
	}
}
?>