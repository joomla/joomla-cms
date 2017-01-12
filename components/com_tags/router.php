<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_tags
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Routing class from com_tags
 *
 * @since  3.3
 */
class TagsRouter extends JComponentRouterBase
{
	/**
	 * Build the route for the com_tags component
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

		// Get a menu item based on Itemid or currently active
		$params = JComponentHelper::getParams('com_tags');

		// We need a menu item.  Either the one specified in the query, or the current active one if none specified
		if (empty($query['Itemid']))
		{
			$menuItem = $this->menu->getActive();
		}
		else
		{
			$menuItem = $this->menu->getItem($query['Itemid']);
		}

		$mView = empty($menuItem->query['view']) ? null : $menuItem->query['view'];
		$mId   = empty($menuItem->query['id']) ? null : $menuItem->query['id'];

		if (is_array($mId))
		{
			$mId = ArrayHelper::toInteger($mId);
		}

		$view = '';

		if (isset($query['view']))
		{
			$view = $query['view'];

			if (empty($query['Itemid']))
			{
				$segments[] = $view;
			}

			unset($query['view']);
		}

		// Are we dealing with a tag that is attached to a menu item?
		if ($mView == $view && isset($query['id']) && $mId == $query['id'])
		{
			unset($query['id']);

			return $segments;
		}

		if ($view == 'tag')
		{
			$notActiveTag = is_array($mId) ? (count($mId) > 1 || $mId[0] != (int) $query['id']) : ($mId != (int) $query['id']);

			if ($notActiveTag || $mView != $view)
			{
				// ID in com_tags can be either an integer, a string or an array of IDs
				$id = is_array($query['id']) ? implode(',', $query['id']) : $query['id'];
				$segments[] = $id;
			}

			unset($query['id']);
		}

		if (isset($query['layout']))
		{
			if ((!empty($query['Itemid']) && isset($menuItem->query['layout'])
				&& $query['layout'] == $menuItem->query['layout'])
				|| $query['layout'] == 'default')
			{
				unset($query['layout']);
			}
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

		// Get the active menu item.
		$item = $this->menu->getActive();

		// Count route segments
		$count = count($segments);

		// Standard routing for tags.
		if (!isset($item))
		{
			$vars['view'] = $segments[0];
			$vars['id']   = $segments[$count - 1];

			return $vars;
		}

		// From the tags view, we can only jump to a tag.
		$id = (isset($item->query['id']) && $item->query['id'] > 1) ? $item->query['id'] : 'root';

		$vars['id'] = $segments[0];
		$vars['view'] = 'tag';

		return $vars;
	}
}

/**
 * Tags router functions. These functions are proxys for the new router interface or old SEF extensions.
 *
 * @param   array  &$query  An array of URL arguments.
 *
 * @return array
 *
 * @deprecated  4.0  Use Class based routers instead
 */
function tagsBuildRoute(&$query)
{
	$router = new TagsRouter;

	return $router->build($query);
}

/**
 * Parse the segments of a URL. These functions are proxys for the new router interface or old SEF extensions.
 *
 * @param   array  $segments  The segments of the URL to parse.
 *
 * @return  array  The URL attributes to be used by the application.
 *
 * @deprecated  4.0  Use Class based routers instead
 */
function tagsParseRoute($segments)
{
	$router = new TagsRouter;

	return $router->parse($segments);
}
