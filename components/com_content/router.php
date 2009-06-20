<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

jimport('joomla.application.categories');

/**
 * Content Component Route Helper
 *
 * @package		Joomla.Site
 * @subpackage	com_content
 * @since 1.6
 */
class ContentRoute
{
	/**
	 * @var	array	A cache of the menu items pertaining to com_content
	 */
	protected static $lookup = null;

	/**
	 * @param	int $id			The id of the article.
	 * @param	int	$categoryId	An optional category id.
	 *
	 * @return	string	The routed link.
	 */
	public static function article($id, $categoryId = null)
	{
		$needles = array(
			'article'  => (int) $id,
			'category' => (int) $categoryId
		);

		//Create the link
		$link = 'index.php?option=com_content&view=article&id='. $id;

		if ($categoryId) {
			$link .= '&catid='.$categoryId;
		}

		if ($itemId = self::_findItemId($needles)) {
			$link .= '&Itemid='.$itemId;
		};

		return $link;
	}

	/**
	 * @param	int $id			The id of the article.
	 * @param	int	$categoryId	An optional category id.
	 *
	 * @return	string	The routed link.
	 */
	public static function category($catid, $sectionid)
	{
		$needles = array(
			'category' => (int) $catid,
			'section'  => (int) $sectionid
		);

		//Create the link
		$link = 'index.php?option=com_content&view=category&id='.$catid;

		if ($itemId = self::_findItemId($needles)) {
			// TODO: The following should work automatically??
			//if (isset($item->query['layout'])) {
			//	$link .= '&layout='.$item->query['layout'];
			//}
			$link .= '&Itemid='.$itemId;
		};

		return $link;
	}

	protected static function _findItemId($needles)
	{
		// Prepare the reverse lookup array.
		if (self::$lookup === null)
		{
			self::$lookup = array();

			$component	= &JComponentHelper::getComponent('com_content');
			$menus		= &JApplication::getMenu('site', array());
			$items		= $menus->getItems('component_id', $component->id);

			foreach ($items as &$item)
			{
				if (isset($item->query) && isset($item->query['view']))
				{
					$view = $item->query['view'];
					if (!isset(self::$lookup[$view])) {
						self::$lookup[$view] = array();
					}
					if (isset($item->query['id'])) {
						self::$lookup[$view][$item->query['id']] = $item->id;
					}
				}
			}
		}

		$match = null;

		foreach ($needles as $view => $id)
		{
			if (isset(self::$lookup[$view]))
			{
				if (isset(self::$lookup[$view][$id])) {
					return self::$lookup[$view][$id];
				}
			}
		}

		return null;
	}
}

/**
 * Build the route for the com_content component
 *
 * @param	array	An array of URL arguments
 *
 * @return	array	The URL arguments to use to assemble the subsequent URL.
 */
function ContentBuildRoute(&$query)
{
	$segments = array();

	// get a menu item based on Itemid or currently active
	$menu = &JSite::getMenu();
	if (empty($query['Itemid'])) {
		$menuItem = &$menu->getActive();
	} else {
		$menuItem = &$menu->getItem($query['Itemid']);
	}

	$mView	= (empty($menuItem->query['view'])) ? null : $menuItem->query['view'];
	if (isset($menuItem->query['catid']))
	{
		$mCatid	= (empty($menuItem->query['catid'])) ? null : $menuItem->query['catid'];
	} else {
		$mCatid	= (empty($menuItem->query['id'])) ? null : $menuItem->query['id'];
	}
	$mId	= (empty($menuItem->query['id'])) ? null : $menuItem->query['id'];
	if (isset($query['view']))
	{
		$view = $query['view'];
		if (empty($query['Itemid'])) {
			$segments[] = $query['view'];
		}
		unset($query['view']);
	}
	if (isset($view))
	{
		if ($view == 'category')
		{
			$catid = (int) $query['id'];
		} elseif ($view == 'article') {
			$catid = (int) $query['catid'];
		}
	}

	if (isset($catid) && $catid > 0)
	{
		$categoryTree = JCategories::getInstance('com_content');
		$category = $categoryTree->get($catid);
	}

	// are we dealing with an article that is attached to a menu item?
	if ((($mView == 'article' && isset($view) && $view == 'article')|| ($mView == 'category' && isset($view) && $view == 'category')) and (isset($query['id'])) and ($mId == intval($query['id']))) {
		unset($query['view']);
		unset($query['catid']);
		unset($query['id']);
	}

	if (isset($category) && isset($view)
		&& $view == 'category' && $mView == $view
		&& (int) $mCatid != $category->id) {
		$path = array();
		while((int)$category->id != (int)$mCatid)
		{
			$path[] = $category->slug;
			$category = $category->getParent();
		}
		$path = array_reverse($path);
		$segments = array_merge($segments, $path);
		unset($query['id']);
	}

	if (isset($view) && $view == 'article' && isset($query['id'])) {
		if (empty($query['Itemid'])) {
			$segments[] = $query['id'];
		} else {
			if (isset($category))
			{
				$path = array();
				while((int)$category->id != (int)$mCatid)
				{
					$path[] = $category->slug;
					$category = $category->getParent();
				}
				$path = array_reverse($path);
				$segments = array_merge($segments, $path);
			}
			$segments[] = $query['id'];
			unset($query['catid']);
		}
		unset($query['id']);
	};

	if (isset($query['year'])) {

		if (!empty($query['Itemid'])) {
			$segments[] = $query['year'];
			unset($query['year']);
		}
	};

	if (isset($query['month'])) {

		if (!empty($query['Itemid'])) {
			$segments[] = $query['month'];
			unset($query['month']);
		}
	};

	if (isset($query['layout']))
	{
		if (!empty($query['Itemid']) && isset($menuItem->query['layout'])) {
			if ($query['layout'] == $menuItem->query['layout']) {

				unset($query['layout']);
			}
		} else {
			if ($query['layout'] == 'default') {
				unset($query['layout']);
			}
		}
	};

	return $segments;
}

/**
 * Parse the segments of a URL.
 *
 * @param	array	The segments of the URL to parse.
 *
 * @return	array	The URL attributes to be used by the application.
 */
function ContentParseRoute($segments)
{
	$vars = array();

	//Get the active menu item
	$menu = &JSite::getMenu();
	$item = &$menu->getActive();

	if ($item->query['view'] == 'category')
	{
		$categoryTree = JCategories::getInstance('com_content');
		$category = $categoryTree->get((int) $item->query['id']);
	}
	// Count route segments
	$count = count($segments);

	//Standard routing for articles
	if (!isset($item))
	{
		$vars['view']  = $segments[0];
		$vars['id']    = $segments[$count - 1];
		return $vars;
	}

	//Handle View and Identifier
	switch($item->query['view'])
	{
		case 'category':
			$categories = $category->getChildren();
			$found = 0;
			foreach($segments as $segment)
			{
				foreach($categories as $category)
				{
					if ($category->slug == $segment)
					{
						$vars['id'] = $segment;
						$vars['view'] = 'category';
						$categories = $category->getChildren();
						$found = 1;
						break;
					}
				}
				if ($found == 0)
				{
					$vars['id'] = $segment;
					$vars['view'] = 'article';
					break;
				}
				$found = 0;
			}
			break;

		case 'frontpage':
			$vars['id']   = $segments[$count-1];
			$vars['view'] = 'article';
			break;

		case 'article':
			$vars['id']	  = $segments[$count-1];
			$vars['view'] = 'article';
			break;

		case 'archive':
			if ($count != 1)
			{
				$vars['year']  = $count >= 2 ? $segments[$count-2] : null;
				$vars['month'] = $segments[$count-1];
				$vars['view']  = 'archive';
			} else {
				$vars['id']	  = $segments[$count-1];
				$vars['view'] = 'article';
			}
			break;
	}

	return $vars;
}
