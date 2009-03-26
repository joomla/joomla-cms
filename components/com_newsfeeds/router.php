<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/
jimport('joomla.application.categorytree');
function NewsfeedsBuildRoute(&$query)
{
	static $items;

	$segments	= array();
	$itemid		= 0;
	$menuitem	= 0;

	// Get the menu items for this component.
	if (!$items) {
		$component	= &JComponentHelper::getComponent('com_newsfeeds');
		$menu		= &JSite::getMenu();
		$items		= $menu->getItems('componentid', $component->id);
	}

	if(isset($query['view']))
	{
		if($query['view'] == 'category')
		{
			$catid = (int) $query['id'];
		} elseif ($query['view'] == 'newsfeed') {
			$catid = (int) $query['catid'];
		}
		$view = $query['view'];
	}
	
	if(isset($catid) && $catid > 0)
	{
		$categoryTree = JCategoryTree::getInstance('com_newsfeeds');
		$category = $categoryTree->get($catid);
	}
	
	if(isset($category) && count($items))
	{
		$path = array();
		while($category instanceof JCategoryNode)
		{
			foreach($items as $item)
			{
				if($item->query['view'] == 'newsfeed'
					&& $view == 'newsfeed'
					&& (int)$item->query['id'] == (int)$query['id'])
				{
					$itemid = $item->id;
					$menuitem = 1;
					break;
				}
			}
			foreach($items as $item)
			{
				if($item->query['view'] == 'category' 
					&& (int)$item->query['id'] == (int)$category->id)
				{
					$itemid = $item->id;
					break;
				}
			}
			if($itemid > 0)
			{
				break;
			} else {
				$path[] = $category->slug;
				$category = $category->getParent();
			}
		}
		if($itemid > 0)
		{
			$query['Itemid'] = $itemid;
		}
		$path = array_reverse($path);
		$segments = array_merge($segments, $path);
	}

	if(isset($view) && $view == 'newsfeed' && $itemid > 0)
	{
		if(!$menuitem)
		$segments[] = $query['id'];
	}

	if($itemid == 0 && isset($query['id']))
	{
		$segments[] = $query['id'];
	}
	
	// Remove the unnecessary URL segments.
	unset($query['view']);
	unset($query['id']);
	unset($query['catid']);

	return $segments;
}

function NewsfeedsParseRoute($segments)
{
	$vars	= array();

	// Get the active menu item.
	$menu	= &JSite::getMenu();
	$item	= &$menu->getActive();

	// Check if we have a valid menu item.
	if (is_object($item))
	{
		if($item->query['view'] == 'category')
		{
			$categorytree = JCategoryTree::getInstance('com_newsfeeds');
			$category = $categorytree->get($item->query['id']);
			foreach($segments as $segment)
			{
				$found = 0;
				foreach($category->getChildren() as $child)
				{
					if($segment == $child->slug)
					{
						$found = 1;
						$category = $child;
						break;
					}
				}
				if($found == 0)
				{
					$vars['id'] = $segment;
					$vars['catid'] = $category->slug;
					$vars['view'] = 'newsfeed';
				} else {
					$vars['id'] = $category->slug;
					$vars['view'] = 'category';
				}
			}
		}
	}
	else
	{
		// Count route segments
		$count = count($segments);

		// Check if there are any route segments to handle.
		if ($count)
		{
			if (count($segments[0]) == 2)
			{
				// We are viewing a newsfeed.
				$vars['view']	= 'newsfeed';
				$vars['id']		= $segments[$count-2];
				$vars['catid']	= $segments[$count-1];

			}
			else
			{
				// We are viewing a category.
				$vars['view']	= 'category';
				$vars['catid']	= $segments[$count-1];
			}
		}
	}

	return $vars;
}
?>
