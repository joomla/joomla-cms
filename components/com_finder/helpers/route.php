<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Finder route helper class.
 *
 * @since  2.5
 */
class FinderHelperRoute
{
	/**
	 * Method to get the route for a search page.
	 *
	 * @param   integer  $f  The search filter id. [optional]
	 * @param   string   $q  The search query string. [optional]
	 *
	 * @return  string  The search route.
	 *
	 * @since   2.5
	 */
	public static function getSearchRoute($f = null, $q = null)
	{
		// Get the menu item id.
		$query = array('view' => 'search', 'q' => $q, 'f' => $f);
		$item = self::getItemid($query);

		// Get the base route.
		$uri = clone(JUri::getInstance('index.php?option=com_finder&view=search'));

		// Add the pre-defined search filter if present.
		if ($f !== null)
		{
			$uri->setVar('f', $f);
		}

		// Add the search query string if present.
		if ($q !== null)
		{
			$uri->setVar('q', $q);
		}

		// Add the menu item id if present.
		if ($item !== null)
		{
			$uri->setVar('Itemid', $item);
		}

		return $uri->toString(array('path', 'query'));
	}

	/**
	 * Method to get the route for an advanced search page.
	 *
	 * @param   integer  $f  The search filter id. [optional]
	 * @param   string   $q  The search query string. [optional]
	 *
	 * @return  string  The advanced search route.
	 *
	 * @since   2.5
	 */
	public static function getAdvancedRoute($f = null, $q = null)
	{
		// Get the menu item id.
		$query = array('view' => 'advanced', 'q' => $q, 'f' => $f);
		$item = self::getItemid($query);

		// Get the base route.
		$uri = clone(JUri::getInstance('index.php?option=com_finder&view=advanced'));

		// Add the pre-defined search filter if present.
		if ($q !== null)
		{
			$uri->setVar('f', $f);
		}

		// Add the search query string if present.
		if ($q !== null)
		{
			$uri->setVar('q', $q);
		}

		// Add the menu item id if present.
		if ($item !== null)
		{
			$uri->setVar('Itemid', $item);
		}

		return $uri->toString(array('path', 'query'));
	}

	/**
	 * Method to get the most appropriate menu item for the route based on the
	 * supplied query needles.
	 *
	 * @param   array  $query  An array of URL parameters.
	 *
	 * @return  mixed  An integer on success, null otherwise.
	 *
	 * @since   2.5
	 */
	public static function getItemid($query)
	{
		static $items, $active;

		// Get the menu items for com_finder.
		if (!$items || !$active)
		{
			$app = JFactory::getApplication('site');
			$com = JComponentHelper::getComponent('com_finder');
			$menu = $app->getMenu();
			$active = $menu->getActive();
			$items = $menu->getItems('component_id', $com->id);
			$items = is_array($items) ? $items : array();
		}

		// Try to match the active view and filter.
		if ($active && @$active->query['view'] == @$query['view'] && @$active->query['f'] == @$query['f'])
		{
			return $active->id;
		}

		// Try to match the view, query, and filter.
		foreach ($items as $item)
		{
			if (@$item->query['view'] == @$query['view'] && @$item->query['q'] == @$query['q'] && @$item->query['f'] == @$query['f'])
			{
				return $item->id;
			}
		}

		// Try to match the view and filter.
		foreach ($items as $item)
		{
			if (@$item->query['view'] == @$query['view'] && @$item->query['f'] == @$query['f'])
			{
				return $item->id;
			}
		}

		// Try to match the view.
		foreach ($items as $item)
		{
			if (@$item->query['view'] == @$query['view'])
			{
				return $item->id;
			}
		}

		return null;
	}
}
