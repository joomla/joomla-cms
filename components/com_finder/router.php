<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Routing class from com_finder
 *
 * @since  3.3
 */
class FinderRouter extends JComponentRouterBase
{
	/**
	 * Build the route for the com_finder component
	 *
	 * @param   array  &$query  An array of URL arguments
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @since   3.3
	 */
	public function build(&$query)
	{
		$segments = array();

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
			$item = $this->menu->getItem($query['Itemid']);

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

		$total = count($segments);

		for ($i = 0; $i < $total; $i++)
		{
			$segments[$i] = str_replace(':', '-', $segments[$i]);
		}

		return $segments;
	}

	/**
	 * Parse the segments of a URL.
	 *
	 * @param   array  &$segments  The segments of the URL to parse.
	 *
	 * @return  array  The URL attributes to be used by the application.
	 *
	 * @since   3.3
	 */
	public function parse(&$segments)
	{
		$total = count($segments);
		$vars = array();

		for ($i = 0; $i < $total; $i++)
		{
			$segments[$i] = preg_replace('/-/', ':', $segments[$i], 1);
		}

		// Check if the view segment is set and it equals search or advanced.
		if (@$segments[0] === 'search' || @$segments[0] === 'advanced')
		{
			$vars['view'] = $segments[0];
		}

		return $vars;
	}
}

/**
 * Finder router functions
 *
 * These functions are proxys for the new router interface
 * for old SEF extensions.
 *
 * @param   array  &$query  An array of URL arguments
 *
 * @return  array  The URL arguments to use to assemble the subsequent URL.
 *
 * @deprecated  4.0  Use Class based routers instead
 */
function FinderBuildRoute(&$query)
{
	$router = new FinderRouter;

	return $router->build($query);
}

/**
 * Finder router functions
 *
 * These functions are proxys for the new router interface
 * for old SEF extensions.
 *
 * @param   array  $segments  The segments of the URL to parse.
 *
 * @return  array  The URL attributes to be used by the application.
 *
 * @deprecated  4.0  Use Class based routers instead
 */
function FinderParseRoute($segments)
{
	$router = new FinderRouter;

	return $router->parse($segments);
}
