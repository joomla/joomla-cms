<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

 /* Weblinks Component Route Helper
 *
 * @package		Joomla.Site
 * @subpackage	com_weblinks
 * @since 1.6
 */

defined('_JEXEC') or die;

class WeblinksRoute
{
	/**
	 * @var	array	A cache of the menu items pertaining to com_weblinks
	 */
	protected static $lookup = null;

	/**
	 * @param	int $id			The id of the weblink.
	 * @param	int	$categoryId	An optional category id.
	 *
	 * @return	string	The routed link.
	 */
	public static function weblink($id, $categoryId = null)
	{
		$needles = array(
			'weblink'	=> (int) $id,
			'category' => (int) $categoryId
		);

		//Create the link
		$link = 'index.php?option=com_weblinks&view=weblink&id='. $id;

		if ($categoryId) {
			$link .= '&catid='.$categoryId;
		}

		if ($itemId = self::_findItemId($needles)) {
			$link .= '&Itemid='.$itemId;
		};

		return $link;
	}

	/**
	 * @param	int $id			The id of the weblink.
	 * @param	int	$categoryId	An optional category id.
	 *
	 * @return	string	The routed link.
	 */
	public static function category($catid, $parentId = null)
	{
		$needles = array(

			'category' => (int) $catid
		);

		//Create the link
		$link = 'index.php?option=com_weblinks&view=category&id='.$catid;

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

			$component	= &JComponentHelper::getComponent('com_weblinks');
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
 * Build the route for the com_weblinks component
 *
 * @param	array	An array of URL arguments
 *
 * @return	array	The URL arguments to use to assemble the subsequent URL.
 */
function WeblinksBuildRoute(&$query)
{
	$segments = array();

	// get a menu item based on Itemid or currently active
	$menu = &JSite::getMenu();

	if (empty($query['Itemid'])) {
		$menuItem = &$menu->getActive();
	}
	else {
		$menuItem = &$menu->getItem($query['Itemid']);
	}
	$mView	= (empty($menuItem->query['view'])) ? null : $menuItem->query['view'];
	$mCatid	= (empty($menuItem->query['catid'])) ? null : $menuItem->query['catid'];
	$mId	= (empty($menuItem->query['id'])) ? null : $menuItem->query['id'];

	if (isset($query['view']))
	{
		$view = $query['view'];
		if (empty($query['Itemid'])) {
			$segments[] = $query['view'];
		}
		unset($query['view']);
	};

	// are we dealing with an weblink that is attached to a menu item?
	if (($mView == 'weblink') and (isset($query['id'])) and ($mId == intval($query['id']))) {
		unset($query['view']);
		unset($query['catid']);
		unset($query['id']);
	}

	if (isset($view) and $view == 'category') {
		if ($mId != intval($query['id']) || $mView != $view) {
			$segments[] = $query['id'];
		}
		unset($query['id']);
	}

	if (isset($query['catid'])) {
		// if we are routing a weblink or category where the category id matches the menu catid, don't include the category segment
		if ((($view == 'weblink') and ($mView != 'category') and ($mView != 'weblink') and ($mCatid != intval($query['catid'])))) {
			$segments[] = $query['catid'];
		}
		unset($query['catid']);
	};

	if (isset($query['id']))
	{
		if (empty($query['Itemid'])) {
			$segments[] = $query['id'];
		}
		else
		{
			if (isset($menuItem->query['id']))
			{
				if ($query['id'] != $mId) {
					$segments[] = $query['id'];
				}
			}
			else {
				$segments[] = $query['id'];
			}
		}
		unset($query['id']);
	};

	if (isset($query['year']))
	{
		if (!empty($query['Itemid'])) {
			$segments[] = $query['year'];
			unset($query['year']);
		}
	};

	if (isset($query['month']))
	{
		if (!empty($query['Itemid'])) {
			$segments[] = $query['month'];
			unset($query['month']);
		}
	};

	if (isset($query['layout']))
	{
		if (!empty($query['Itemid']) && isset($menuItem->query['layout']))
		{
			if ($query['layout'] == $menuItem->query['layout']) {

				unset($query['layout']);
			}
		}
		else
		{
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
function WeblinksParseRoute($segments)
{
	$vars = array();

	//Get the active menu item.
	$menu = &JSite::getMenu();
	$item = &$menu->getActive();

	// Count route segments
	$count = count($segments);

	// Standard routing for weblinks.
	if (!isset($item))
	{
		$vars['view']	= $segments[0];
		$vars['id']		= $segments[$count - 1];
		return $vars;
	}

	// Handle View and Identifier.
	switch ($item->query['view'])
	{
		case 'categories':
			// From the categories view, we can only jump to a category.

			if ($count > 1)
			{
				if (intval($segments[0]) && intval($segments[$count-1]))
				{
					// 123-path/to/category/456-article
					$vars['id']		= $segments[$count-1];
					$vars['view']	= 'weblink';
				}
				else
				{
					// 123-path/to/category
					$vars['id']		= $segments[0];
					$vars['view']	= 'category';
				}
			}
			else
			{
				// 123-category
				$vars['id']		= $segments[0];
				$vars['view']	= 'category';
			}
			break;

		case 'category':
			$vars['id']		= $segments[$count-1];
			$vars['view']	= 'weblink';
			break;
		case 'weblink':
			$vars['id']		= $segments[$count-1];
			$vars['view']	= 'weblink';
			break;

	}
		return $vars;

}
