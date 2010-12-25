<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.categories');

/**
 * Build the route for the com_content component
 *
 * @param	array	An array of URL arguments
 * @return	array	The URL arguments to use to assemble the subsequent URL.
 * @since	1.5
 */
function ContentBuildRoute(&$query)
{
	$segments	= array();

	// get a menu item based on Itemid or currently active
	$app		= JFactory::getApplication();
	$menu		= $app->getMenu();
	$params		= JComponentHelper::getParams('com_content');
	$advanced	= $params->get('sef_advanced_link', 0);

	// we need a menu item.  Either the one specified in the query, or the current active one if none specified
	if (empty($query['Itemid'])) {
		$menuItem = $menu->getActive();
	}
	else {
		$menuItem = $menu->getItem($query['Itemid']);
	}

	$mView	= (empty($menuItem->query['view'])) ? null : $menuItem->query['view'];
	$mCatid	= (empty($menuItem->query['catid'])) ? null : $menuItem->query['catid'];
	$mId	= (empty($menuItem->query['id'])) ? null : $menuItem->query['id'];

	// we determine the view from the query.  If we don't have an Itemid in the query
	// then we put the view onto the URL
	if (isset($query['view'])) {
		$view = $query['view'];
		if (empty($query['Itemid'])) {
			$segments[] = $query['view'];
		}
		unset($query['view']);
	}


	// we determine the category id.  If we are dealing with an article, it is the category
	// that the article is in (if specified in the passed in query).  If we are dealing with
	// a category, it is the id of the category.  Otherwise we set it to false
	// so that we don't look for it in the loop below
	if (isset($view) && $view == 'article' && isset($query['catid'])) {
		$catid = $query['catid'];
	}
	elseif (isset($view) && $view != 'article' && isset($query['id'])) {
		$catid = $query['id'];
	}
	else {
		$catid = false;
	}

	// in the section below, we find the category that our menu item points to and we
	// build the URL off of it.
	if (isset($view) and ($view == 'category' or $view == 'article') and $catid) {
		$menuCatid = $mId;
		$categories = JCategories::getInstance('Content');
		$category = $categories->get($catid);
		
		if ($category) {
			$path = $category->getPath();

			// $foundMenuCategory keeps track of whether or not we have traversed the path to our
			// target category to the point where our menu item points to.  i.e. if our path has five elements
			// (we are nested that deep), and our menu item points to a category in the third level, we want to
			// place the last two levels onto the URL.  So we don't start adding categories until we've found the
			// menu category in the path
			$foundMenuCategory = false;

			$array = array();

			// we walk the path to the category
			foreach($path as $id)
			{
				// we always strip our ids.  At the end, we will add in the id
				// of our target category
				list($tmp, $current_id) = explode(':', $id, 2);

				if ($foundMenuCategory) {
					$array[] = $current_id;
				} else {
					// once we arrive at our menu item category we set foundMenuCategory to true.  Thus on the next item in
					// the path we will start adding it
					if ($tmp == $menuCatid) {
						$foundMenuCategory = true;
					}
				}
			}

			// if we don't have a menu item pointing to our category we put the category path onto the url
			if (!$foundMenuCategory) {
				foreach($path AS $id)
				{
					list($tmp, $current_id) = explode(':', $id, 2);
					$array[] = $current_id;
				}
			}

			// we add the category id back in if we've adding a category to the URL.  If we are pointing at an article
			// and it is in the category that the menu item points to, then we don't add the category to the URL, because
			// there is no appropriate category to add
			if (!$advanced && count($array)) {
				$array[0] = $catid.':'.$array[0];
			}

			$segments = array_merge($segments, $array);
		}

		// if we are pointing at an article and the menu doesn't point at an article, we add
		// the id and alias of the article to the URL.  We don't want to add the article info
		// if it is already there in the menu item.
		if ($view == 'article' && $mView != 'article') {
			if ($advanced) {
				list($tmp, $id) = explode(':', $query['id'], 2);
			}
			else {
				$id = $query['id'];
			}
			$segments[] = $id;
		}

		// we unset id and catid so they don't show up in the URL.
		unset($query['id']);
		unset($query['catid']);
	}

	if (isset($query['year'])) {
		if (!empty($query['Itemid'])) {
			$segments[] = $query['year'];
			unset($query['year']);
		}
	}

	if (isset($query['month'])) {
		if (!empty($query['Itemid'])) {
			$segments[] = $query['month'];
			unset($query['month']);
		}
	}

	// if the layout is specified and it is the same as the layout in the menu item, we
	// unset it so it doesn't go into the query string.
	if (isset($query['layout'])) {
		if (!empty($query['Itemid']) && isset($menuItem->query['layout'])) {
			if ($query['layout'] == $menuItem->query['layout']) {

				unset($query['layout']);
			}
		}
		else {
			if ($query['layout'] == 'default') {
				unset($query['layout']);
			}
		}
	}

	if (isset($query['id'])) {
		$segments[] = $query['id'];
		unset($query['id']);
	}

	return $segments;
}

/**
 * Parse the segments of a URL.
 *
 * @param	array	The segments of the URL to parse.
 *
 * @return	array	The URL attributes to be used by the application.
 * @since	1.5
 */
function ContentParseRoute($segments)
{
	$vars = array();

	//Get the active menu item.
	$app	= JFactory::getApplication();
	$menu	= $app->getMenu();
	$item	= $menu->getActive();
	$params = JComponentHelper::getParams('com_content');
	$advanced = $params->get('sef_advanced_link', 0);
	$db = JFactory::getDBO();

	// Count route segments
	$count = count($segments);

	// Standard routing for articles.  If we don't pick up an Itemid then we get the view from the segments
	// the first segment is the view and the last segment is the id of the article or category.
	if (!isset($item)) {
		$vars['view']	= $segments[0];
		$vars['id']		= $segments[$count - 1];

		return $vars;
	}

	// if there is only one segment, then it points to either an article or a category
	// we test it first to see if it is a category.  If the id and alias match a category
	// then we assume it is a category.  If they don't we assume it is an article
	if ($count == 1) {
		list($id, $alias) = explode(':', $segments[0], 2);

		// first we check if it is a category
		$category = JCategories::getInstance('Content')->get($id);

		if ($category && $category->alias == $alias) {
			$vars['view'] = 'category';
			$vars['id'] = $id;

			return $vars;
		} else {
			$query = 'SELECT alias, catid FROM #__content WHERE id = '.(int)$id;
			$db->setQuery($query);
			$article = $db->loadObject();

			if ($article) {
				if ($article->alias == $alias) {
					$vars['view'] = 'article';
					$vars['id'] = (int)$id;

					return $vars;
				}
			}
		}
	}

	// if there was more than one segment, then we can determine where the URL points to
	// because the first segment will have the target category id prepended to it.  If the
	// last segment has a number prepended, it is an article, otherwise, it is a category.
	if (!$advanced) {
		$cat_id = (int)$segments[0];

		$article_id = (int)$segments[$count - 1];

		if ($article_id > 0) {
			$vars['view'] = 'article';
			$vars['catid'] = $cat_id;
			$vars['id'] = $article_id;
		} else {
			$vars['view'] = 'category';
			$vars['id'] = $cat_id;
		}

		return $vars;
	}

	// we get the category id from the menu item and search from there
	$id = $item->query['id'];
	$category = JCategories::getInstance('Content')->get($id);

	if (!$category) {
		JError::raiseError(404, JText::_('COM_CONTENT_ERROR_PARENT_CATEGORY_NOT_FOUND'));
		return $vars;
	}

	$categories = $category->getChildren();
	$vars['catid'] = $id;
	$vars['id'] = $id;
	$found = 0;

	foreach($segments as $segment)
	{
		$segment = str_replace(':', '-',$segment);

		foreach($categories as $category)
		{
			if ($category->alias == $segment) {
				$vars['id'] = $category->id;
				$vars['catid'] = $category->id;
				$vars['view'] = 'category';
				$categories = $category->getChildren();
				$found = 1;
				break;
			}
		}

		if ($found == 0) {
			if ($advanced) {
				$db = JFactory::getDBO();
				$query = 'SELECT id FROM #__content WHERE catid = '.$vars['catid'].' AND alias = '.$db->Quote($segment);
				$db->setQuery($query);
				$cid = $db->loadResult();
			} else {
				$cid = $segment;
			}

			$vars['id'] = $cid;

			if ($item->query['view'] == 'archive' && $count != 1){
				$vars['year']	= $count >= 2 ? $segments[$count-2] : null;
				$vars['month'] = $segments[$count-1];
				$vars['view']	= 'archive';
			}
			else {
				$vars['view'] = 'article';
			}
		}

		$found = 0;
	}

	return $vars;
}
