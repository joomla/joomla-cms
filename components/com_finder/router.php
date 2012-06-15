<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

// Register dependent classes.

/**
 * Method to build a SEF route.
 *
 * @param   array  &$query  An array of route variables.
 *
 * @return  array  An array of route segments.
 *
 * @since   2.5
 */
function FinderBuildRoute(&$query)
{
	static $menu;
	$segments = array();

	// Load the menu if necessary.
	if (!$menu)
	{
		$menu = JFactory::getApplication('site')->getMenu();
	}

	/*
	 * First, handle menu item routes first. When the menu system builds a
	 * route, it only provides the option and the menu item id. We don't have
	 * to do anything to these routes.
	 */
	if (count($query) === 2 && isset($query['Itemid']) && isset($query['option']))
	{
		return $segments;
	}

	/*
	 * Next, handle a route with a supplied menu item id. All system generated
	 * routes should fall into this group. We can assume that the menu item id
	 * is the best possible match for the query but we need to go through and
	 * see which variables we can eliminate from the route query string because
	 * they are present in the menu item route already.
	 */
	if (!empty($query['Itemid']))
	{
		// Get the menu item.
		$item = $menu->getItem($query['Itemid']);

		// Check if the view matches.
		if ($item && @$item->query['view'] === @$query['view'])
		{
			unset($query['view']);
		}

		// Check if the search query filter matches.
		if ($item && @$item->query['f'] === @$query['f'])
		{
			unset($query['f']);
		}

		// Check if the search query string matches.
		if ($item && @$item->query['q'] === @$query['q'])
		{
			unset($query['q']);
		}

		return $segments;
	}

	/*
	 * Lastly, handle a route with no menu item id. Fortunately, we only need
	 * to deal with the view as the other route variables are supposed to stay
	 * in the query string.
	 */
	if (isset($query['view']))
	{
		// Add the view to the segments.
		$segments[] = $query['view'];
		unset($query['view']);
	}

	return $segments;
}

/**
 * Method to parse a SEF route.
 *
 * @param   array  $segments  An array of route segments.
 *
 * @return  array  An array of route variables.
 *
 * @since   2.5
 */
function FinderParseRoute($segments)
{
	$vars = array();

	// Check if the view segment is set and it equals search or advanced.
	if (@$segments[0] === 'search' || @$segments[0] === 'advanced')
	{
		$vars['view'] = $segments[0];
	}

	return $vars;
}
