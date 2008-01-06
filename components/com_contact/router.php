<?php
/**
 * @version		$Id: router.php 7380 2007-05-06 21:26:03Z eddieajau $
 * @package		Joomla
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

function ContactBuildRoute(&$query)
{
	static $items;

	$segments	= array();
	$itemid		= null;

	// Break up the contact id into numeric and alias values.
	if (isset($query['id']) && strpos($query['id'], ':')) {
		list($query['id'], $query['alias']) = explode(':', $query['id'], 2);
	}

	// Break up the category id into numeric and alias values.
	if (isset($query['catid']) && strpos($query['catid'], ':')) {
		list($query['catid'], $query['alias']) = explode(':', $query['catid'], 2);
	}

	// Get the menu items for this component.
	if (!$items) {
		$component	= &JComponentHelper::getComponent('com_contact');
		$menu		= &JSite::getMenu();
		$items		= $menu->getItems('componentid', $component->id);
	}

	// Search for an appropriate menu item.
	if (is_array($items))
	{
		// If only the option and itemid are specified in the query, return that item.
		if (!isset($query['view']) && !isset($query['id']) && !isset($query['catid']) && isset($query['Itemid'])) {
			$itemid = (int) $query['Itemid'];
		}

		// Search for a specific link based on the critera given.
		if (!$itemid)
		{
			foreach ($items as $item)
			{
				// Check if this menu item links to this view.
				if (isset($item->query['view']) && $item->query['view'] == 'contact'
					&& isset($query['view']) && $query['view'] != 'category'
					&& isset($item->query['id']) && $item->query['id'] == $query['id'])
				{
					$itemid	= $item->id;
				}
				elseif (isset($item->query['view']) && $item->query['view'] == 'category'
						&& isset($query['view']) && $query['view'] != 'contact'
						&& isset($item->query['catid']) && $item->query['catid'] == $query['catid'])
				{
					$itemid	= $item->id;
				}
			}
		}

		// If no specific link has been found, search for a general one.
		if (!$itemid)
		{
			foreach ($items as $item)
			{
				if (isset($query['view']) && $query['view'] == 'contact' && isset($item->query['view']) && $item->query['view'] == 'contact')
				{
					// Check for an undealt with contact id.
					if (isset($query['id']))
					{
						// This menu item links to the contact view but we need to append the contact id to it.
						$itemid		= $item->id;
						$segments[]	= isset($query['alias']) ? $query['id'].':'.$query['alias'] : $query['id'];
						break;
					}
				}
				elseif (isset($query['view']) && $query['view'] == 'category' && isset($item->query['view']) && $item->query['view'] == 'category')
				{
					// Check for an undealt with category id.
					if (isset($query['catid']))
					{
						// This menu item links to the category view but we need to append the category id to it.
						$itemid		= $item->id;
						$segments[]	= isset($query['alias']) ? $query['catid'].':'.$query['alias'] : $query['catid'];
						break;
					}
				}
			}
		}
	}

	// Check if the router found an appropriate itemid.
	if (!$itemid)
	{
		// Check if a id was specified.
		if (isset($query['id']))
		{
			if (isset($query['alias'])) {
				$query['id'] .= ':'.$query['alias'];
			}

			// Push the id onto the stack.
			$segments[] = $query['id'];

			unset($query['view']);
			unset($query['id']);
			unset($query['alias']);
		}
		elseif (isset($query['catid']))
		{
			if (isset($query['alias'])) {
				$query['catid'] .= ':'.$query['alias'];
			}

			// Push the catid onto the stack.
			$segments[]	= 'category';
			$segments[] = $query['catid'];

			unset($query['view']);
			unset($query['catid']);
			unset($query['alias']);
		}
	}
	else
	{
		$query['Itemid'] = $itemid;

		// Remove the unnecessary URL segments.
		unset($query['view']);
		unset($query['id']);
		unset($query['catid']);
		unset($query['alias']);
	}

	return $segments;
}

function ContactParseRoute($segments)
{
	$vars	= array();

	// Get the active menu item.
	$menu	= &JSite::getMenu();
	$item	= &$menu->getActive();

	// Check if we have a valid menu item.
	if (is_object($item))
	{
		// Proceed through the possible variations trying to match the most specific one.
		if (isset($item->query['view']) && $item->query['view'] == 'contact' && isset($segments[0]))
		{
			// Break up the contact id into numeric and alias values.
			if (isset($segments[0]) && strpos($segments[0], ':')) {
				list($id, $alias) = explode(':', $segments[0], 2);
			}

			// Contact view.
			$vars['view']	= 'contact';
			$vars['id']		= $id;
		}
		elseif (isset($item->query['view']) && $item->query['view'] == 'category' && isset($segments[0]))
		{
			// Break up the category id into numeric and alias values.
			if (isset($segments[0]) && strpos($segments[0], ':')) {
				list($catid, $alias) = explode(':', $segments[0], 2);
			}

			// Category view.
			$vars['view']	= 'category';
			$vars['catid']	= $catid;
		}
	}
	else
	{
		// Count route segments
		$count = count($segments);

		// Check if there are any route segments to handle.
		if ($count)
		{
			if ($segments[0] === 'category')
			{
				// We are viewing a category.
				$vars['view']	= 'category';
				$vars['catid']	= $segments[$count-1];
			}
			else
			{
				// We are viewing a contact.
				$vars['view']	= 'contact';
				$vars['id']		= $segments[$count-1];
			}
		}
	}

	return $vars;
}