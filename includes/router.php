<?php
/**
* @version		$Id: router.php 8180 2007-07-23 05:52:29Z eddieajau $
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

jimport( 'joomla.application.router' );

/**
 * Class to create and parse routes for the site application
 *
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package 	Joomla
 * @since		1.5
 */
class JRouterSite extends JRouter
{
	/**
	 * Class constructor
	 *
	 * @access public
	 */
	function __construct($options = array()) {
		parent::__construct($options);
	}

	/**
	 * Route a request
	 *
	 * @access public
	 */
	function parse($uri)
	{
		if(is_string($uri)) {
			$uri = JURI::getInstance($uri);
		}

		// Set Local Vars passed in via the URL
		$vars = $uri->getQuery(true);
		$this->_vars = array_merge($this->_vars, $vars);

		// Get the base and full URLs
		$full = $uri->toString( array('scheme', 'host', 'port', 'path'));
		$base = $uri->base();

		$url = urldecode(str_replace($base, '', $full));
		$url = preg_replace('/index[\d]?.php/', '', $url);
		$url = trim($url , '/');


		$menu =& JMenu::getInstance(true);

		/*
		 * Handle empty URL : mysite/ or mysite/index.php
		 */
		if(empty($url) && !$uri->getQuery())
		{
			$item = $menu->getDefault();

			// Set the active menu item
			$menu->setActive($item->id);

			//Set the information in the request
			JRequest::set($item->query, 'get', false );

			//Set the itemid in the request
			JRequest::setVar('Itemid',  $item->id);

			return true;
		}

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
				$vars = $this->_parseApplicationRoute($url);

				// Set the active menu item
				if(isset($vars['Itemid'])) {
					$menu->setActive($vars['Itemid']);
				}

				// Handle pagination
				$limitstart = JRequest::getVar('start', null, 'get', 'int');
				$vars['limitstart'] = $limitstart;

				//Set the variables
				$this->_vars = array_merge($this->_vars, $vars);
			}

			if(!empty($url))
			{
				// Parse component route
				$vars = $this->_parseComponentRoute($url);

				//Set the variables
				$this->_vars = array_merge($this->_vars, $vars);
			}
			else
			{
				//Set active menu item
				if($item =& $menu->getActive())
				{
					$vars = $item->query;

					//Set the variables
					$this->_vars = array_merge($this->_vars, $vars);
				}
			}

			//Set the information in the request
			JRequest::set($this->_vars, 'get', true );

			return true;
		}

		/*
		 * Handle unrouted URL : mysite/index.php?option=x&var=y&Itemid=z
		 */
		if(($itemid = (int) $uri->getVar('Itemid')))
		{
			//Make sure the itemid exists
			if(!$menu->getItem($itemid)) {
				return false;
			}

			// Set the active menu item
			$item =& $menu->setActive($itemid);

			//Set the variables
			$vars = JRequest::get('get');

			// Removed any appended variables
			/* Do we need this - tcp -> Yes we do but it's broken ! I'll fix
			foreach($vars as $key => $value)
			{
				$this->_vars[$key] = $value;
				if($key === 'Itemid') {
					break;
				}
			}
			*/

			//We only received an Itemid, set the information from the itemid in the
			// request
			if(count($vars) == 1) {
				JRequest::set($item->query, 'get', false );
			}

			//Set the route information in the request
			JRequest::set($vars, 'get', true );

			return true;
		}

		$default = $menu->getDefault();
		$itemid = $default->id;

		// Set the active menu item
		$menu->setActive($itemid);

		//Set the itemid in the request
		JRequest::setVar('Itemid', $itemid);

		return true;
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

		$vars = array();

		if(substr($url, 0, 9) == 'component')
		{
			$segments = explode('/', $url);
			$url = str_replace('component/'.$segments[1], '', $url);;

			//Get the option
			$option = 'com_'.$segments[1];
		}
		else
		{
			//Need to reverse the array (highest sublevels first)
			$items = array_reverse($menu->getMenu());

			foreach ($items as $item)
			{
				$lenght = strlen($item->route); //get the lenght of the route

				if($lenght > 0 && strpos($url.'/', $item->route.'/') === 0)
				{
					$url    = substr($url, $lenght);

					$itemid = $item->id;
					$option = $item->component;
					break;
				}
			}
		}

		$vars['option'] = $option;
		$vars['Itemid'] = $itemid;

		return $vars;
	}

	/**
	* Parse a component specific route
	*
	* @access protected
	*/
	function _parseComponentRoute($url)
	{
		$vars = array();

		$segments = explode('/', $url);
		array_shift($segments);

		// Handle component	route
		$component = preg_replace('/[^A-Z0-9_\.-]/i', '', $this->_vars['option']);

		// Use the component routing handler if it exists
		$path = JPATH_BASE.DS.'components'.DS.$component.DS.'router.php';

		if (file_exists($path) && count($segments))
		{
			//decode the route segments
			$segments = $this->_decodeSegments($segments);

			require_once $path;
			$function =  substr($component, 4).'ParseRoute';
			$vars =  $function($segments);
		}

		return $vars;
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
		// Replace all &amp; with & - ensures cache integrity
		$url = str_replace('&amp;', '&', $url);

		// Create full URL if we are only appending variables to it
		if(substr($url, 0, 1) == '&')
		{
			$vars = array();
			parse_str($url, $vars);

			$vars = array_merge($this->_vars, $vars);
			$url = 'index.php?'.JURI::_buildQuery($vars);
		}

		// Can this URL be build
		if(preg_match('/^(([^:\/\?#]+):)/i', $url) || strcasecmp(substr($url, 0, 9), 'index.php')) {
			return $url;
		}

		// Decompose link into url component parts
		// We need to use a clone otherwise the next getInstance on the same URL will foul up
		$uri  = clone( JURI::getInstance(JURI::base().$url) );
		$menu =& JMenu::getInstance();

		/*
		 * Build unrouted URL
		 */
		if(!$this->_mode)
		{
			if($uri->getVar('Itemid') && count($uri->getQuery(true)) == 1)
			{
				// Get the active menu item
				$itemid = $uri->getVar('Itemid');
				$item = $menu->getItem($itemid);
				$uri->setQuery($item->query);
				$uri->setVar('Itemid', $itemid);
				$url = $uri->toString();
				return $url;
			}
		}

		// If the itemid isn't set in the URL use default
		if(!$itemid = $uri->getVar('Itemid'))
		{
			$default = $menu->getDefault();
			$uri->setVar('Itemid', JRequest::getInt('Itemid', $default->id));
		}

		$item = $menu->getItem($uri->getVar('Itemid'));

		// If the option isn't set in the URL use the itemid
		if(!$option = $uri->getVar('option')) {
			$uri->setVar('option', $item->component);
		}

		$url = 'index.php'.$uri->toString(array('query', 'fragment'));

		/*
		 * Build routed URL : mysite/route/index.php?var=x
		 */
		if ($this->_mode)
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
		}

		return $url;
	}

	/**
	* Build the application specific route
	*
	* @access protected
	*/
	function _buildApplicationRoute(&$query)
	{
		//Create default route
		$route = 'component/'.substr($query['option'], 4);

		//Create itemid specific route 
		$menu =& JMenu::getInstance();
		$item = $menu->getItem($query['Itemid']);
		
		if ($query['option'] == $item->component) {
			$route = $item->route;
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
}