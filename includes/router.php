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

	function _parseRawRoute(&$uri)
	{
		$vars   = array();
		
		$menu =& JSite::getMenu(true);
		
		//Handle an empty URL (special case)
		if(!$uri->getQuery())
		{
			$item = $menu->getDefault();
			
			//Set the information in the request
			$vars = $item->query;
			
			//Get the itemid
			$vars['Itemid'] = $item->id;
			
			// Set the active menu item
			$menu->setActive($vars['Itemid']);
			
			return $vars;
		} 
		
		//Get the variables from the request
		$this->setVars(JRequest::get('get'));
			
		//Get the itemid, if it hasn't been set force it to null
		$this->setVar('Itemid', JRequest::getInt('Itemid', null));
			
		//Only an Itemid ? Get the full information from the itemid
		if(count($this->getVars()) == 1) 
		{
			$item = $menu->getItem($this->getVar('Itemid'));
			$vars = $vars + $item->query;
		}
		
		// Set the active menu item
		$menu->setActive($this->getVar('Itemid'));
		
		return $vars;
	}
	
	function _parseSefRoute(&$uri)
	{
		$vars   = array();
		
		$menu =& JSite::getMenu(true);
		
		// Get the base and full URLs
		$full = $uri->toString( array('scheme', 'host', 'port', 'path'));
		$base = $uri->base();

		$url = str_replace(array($base, $this->_suffix), '', $full);
		$url = preg_replace('/index[\d]?.php/', '', $url);
		$url = trim($url , '/');
			
		//Handle an empty URL (special case)
		if(empty($url) && !$uri->getQuery())
		{
			$item = $menu->getDefault();
			
			//Set the information in the request
			$vars = $item->query;
			
			//Get the itemid
			$vars['Itemid'] = $item->id;

			// Set the active menu item
			$menu->setActive($vars['Itemid']);
			
			return $vars;
		} 
		
		//Get the variables from the request
		$vars = JRequest::get('get'); 
		
		/*
		 * Parse the application route
		 */
		
		if(substr($url, 0, 9) == 'component')
		{
			$segments = explode('/', $url);
			$url      = str_replace('component/'.$segments[1], '', $url);
			
			$vars['option'] = 'com_'.$segments[1];
			$vars['Itemid'] = null;
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

					$vars['Itemid'] = $item->id;
					$vars['option'] = $item->component;
					break;
				}
			}
		}

		// Set the active menu item
		if ( isset($vars['Itemid']) ) {
			$menu->setActive(  $vars['Itemid'] );
		}
			
		//Set the variables
		$this->setVars($vars);

		/*
		 * Parse the component route
		 */
		if(!empty($url))
		{
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
				
				$this->setVars($vars);
			}
		}
		else
		{
			//Set active menu item
			if($item =& $menu->getActive()) {
				$vars = $item->query;
			}
		}
		
		return $vars;
	}
	
	function _buildRawRoute(&$uri)
	{
		$route = 'index.php'; //the route created
		
		if($uri->getVar('Itemid') && count($uri->getQuery(true)) == 2)
		{
			$menu =& JSite::getMenu();
			
			// Get the active menu item
			$itemid = $uri->getVar('Itemid');
			$item   = $menu->getItem($itemid);
			
			$uri->setQuery($item->query);
			$uri->setVar('Itemid', $itemid);
		}
		
		return $route;
	}
	
	function _buildSefRoute(&$uri)
	{
		$route = ''; //the route created
		
		$menu =& JSite::getMenu();
		
		//Get the query data
		$query = $uri->getQuery(true);
		
		/*
		 * Built the application route
		 */
		$route = 'component/'.substr($query['option'], 4);

		if(isset($query['Itemid'])) 
		{
			$item = $menu->getItem($query['Itemid']);
		
			if ($query['option'] == $item->component) {
				$route = $item->route;
			} 
		}

		/*
		 * Built the component route
		 */ 
		$component = preg_replace('/[^A-Z0-9_\.-]/i', '', $query['option']);

		// Use the component routing handler if it exists
		$path = JPATH_BASE.DS.'components'.DS.$component.DS.'router.php';

		// Unset unneeded query information
		unset($query['option']);
		
		// Use the custom request handler if it exists
		if (file_exists($path) && !empty($query))
		{
			require_once $path;
			$function	= substr($component, 4).'BuildRoute';
			$parts		= $function($query);

			//encode the route segments
			$parts = $this->_encodeSegments($parts);
			
			$result  = implode('/', $parts);
			$route  .= ($result != "") ? '/'.$result : null;
		}
		
		// Unset unneeded query information
		unset($query['Itemid']);
		
		//Set query again in the URI
		$uri->setQuery($query);
		
		return $route;
	}
	
	function _processParseRules()
	{
		//Process rules
		if($start = JRequest::getVar('start', null, 'get', 'int'))
		{
			$this->setVar('limitstart', $start);
			unset($this->_vars['start']);
		}
	}
	
	function _processBuildRules(&$uri)
	{
		//Process rules
		if ($limitstart = $uri->getVar('limitstart'))
		{
			$uri->setVar('start', (int) $limitstart);
			$uri->delVar('limitstart');
		}
	}

	function &_createURI($url)
	{
		//Create the URI
		$uri =& parent::_createURI($url);
		
		// Set URI defaults
		$menu =& JSite::getMenu();
		
		if(!$itemid = $uri->getVar('Itemid'))
		{
			if($option = $uri->getVar('option'))
			{
				$item  = $menu->getItem($this->getVar('Itemid'));
				if(isset($item) && $item->component == $option) {
					$uri->setVar('Itemid', $item->id);	
				} 
			}
			else
			{
				$uri->setVar('option', $this->getVar('option'));
				if($itemid = $this->getVar('Itemid')) {
					$uri->setVar('Itemid', $itemid);
				}
			}
		}
		else
		{
			if(!$uri->getVar('option'))
			{
				$item  = $menu->getItem($itemid);
				$uri->setVar('option', $item->component);
			}
		}
			
		return $uri;
	}
}