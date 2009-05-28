<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Application
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * JMenu class
 *
 * @package		Joomla.Framework
 * @subpackage	Application
 * @since		1.5
 */
class JMenuSite extends JMenu
{
	/**
	 * Loads the entire menu table into memory
	 *
	 * @access public
	 * @return array
	 */
	function load()
	{
		$cache = &JFactory::getCache('_system', 'output');

		if (!$data = $cache->get('menu_items'))
		{
			// Initialize some variables
			$db		= & JFactory::getDbo();

			$sql	= 'SELECT m.*, c.`option` as component' .
					' FROM #__menu AS m' .
					' LEFT JOIN #__components AS c ON m.componentid = c.id'.
					' WHERE m.published = 1'.
					' ORDER BY m.sublevel, m.parent, m.ordering';
			$db->setQuery($sql);

			if (!($menus = $db->loadObjectList('id'))) {
				JError::raiseWarning('SOME_ERROR_CODE', "Error loading Menus: ".$db->getErrorMsg());
				return false;
			}

			foreach($menus as $key => $menu)
			{
				//Get parent information
				$parent_route = '';
				$parent_tree  = array();
				if (($parent = $menus[$key]->parent) && (isset($menus[$parent])) &&
					(is_object($menus[$parent])) && (isset($menus[$parent]->route)) && isset($menus[$parent]->tree)) {
					$parent_route = $menus[$parent]->route.'/';
					$parent_tree  = $menus[$parent]->tree;
				}

				//Create tree
				array_push($parent_tree, $menus[$key]->id);
				$menus[$key]->tree   = $parent_tree;

				//Create route
				$route = $parent_route.$menus[$key]->alias;
				$menus[$key]->route  = $route;

				//Create the query array
				$url = str_replace('index.php?', '', $menus[$key]->link);
				if (strpos($url, '&amp;') !== false)
				{
				   $url = str_replace('&amp;','&',$url);
				}

				parse_str($url, $menus[$key]->query);
			}

			$cache->store(serialize($menus), 'menu_items');

			$this->_items = $menus;
		}
		else {
			$this->_items = unserialize($data);
		}
	}
}
