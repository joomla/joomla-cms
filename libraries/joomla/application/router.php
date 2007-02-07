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
	function parse($uri)
	{
		$menu =& JMenu::getInstance();

		$params = array();

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
				
				//MOVE somwhere else
				/*$path = JPATH_BASE.DS.'components'.DS.'com_'.$component.DS.$component.'.php';
				// Do a quick check to make sure component exists
				if (!file_exists($path)) {
					JError::raiseError(404, JText::_('Invalid Request'));
					exit (404);
				}*/
			}
		}
		
		$item = $menu->getItem($itemid);

		$uri =& JURI::getInstance(($item) ? $item->link : null);
		$query = $uri->getQuery(true);

		JRequest::set($query, 'get', false);
		JRequest::setVar('Itemid', ($item) ? $item->id : null, 'get');

		// Use the custom sef handler if it exists
		$path = ($item) ? JPATH_BASE.DS.'components'.DS.$item->component.DS.'route.php' : null;
		
		$urlArray = explode('/', $url);
		array_shift($urlArray);
		
		if (count($urlArray) && file_exists($path))
		{
			require_once $path;
			$function =  substr($item->component, 4).'ParseRoute';
			$function($urlArray, $params);
		}
	}
	
	/**
 	 * Function to convert an internal URI to a route
 	 *
 	 * @param	string	$string	The internal URL
 	 * @return	string	The absolute search engine friendly URL
 	 * @since	1.0
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
			// Initialize some variables
			$config	= & JFactory::getConfig();
			$params = array();

			// Get config variables
			$mode    = $config->getValue('config.sef_rewrite');
			$rewrite = $config->getValue('config.sef');

			// Home index.php
			if ($string == 'index.php') {
				$string = '';
			}

			// decompose link into url component parts
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

				// Build component name and sef handler path
				$path = JPATH_BASE.DS.'components'.DS.$item->component.DS.'route.php';

				$uri->delVar('option'); //don't need the option anymore
				$uri->delVar('Itemid'); //don't need the itemid anymore
				$query = $uri->getQuery(true);

				// Use the custom request handler if it exists
				if (file_exists($path))
				{
					require_once $path;
					$function	= substr($item->component, 4).'BuildRoute';
					$parts		= $function($query, $params);

					$route = implode('/', $parts);
					$route = ($route) ? '/'.$route : null;

					$uri->setQuery($query);
				}

				// get the query
				$query = $uri->getQuery();

				// check if link contained fragment identifiers (ex. #foo)
				$fragment = null;
				if ($fragment = $uri->getFragment()) 
				{
					// ensure fragment identifiers are compatible with HTML4
					if (preg_match('@^[A-Za-z][A-Za-z0-9:_.-]*$@', $fragment)) {
						$fragment = '#'.$fragment;
					}
				}

				if($query) {
					$query = '?'.$query;
				}
				
				$url = $item->route.$route.$fragment.$query;

				// Prepend the base URI if we are not using mod_rewrite
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
}
?>
