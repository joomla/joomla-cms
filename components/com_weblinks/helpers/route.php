<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	Weblinks
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Component Helper
jimport('joomla.application.component.helper');
jimport('joomla.application.categorytree');

/**
 * Weblinks Component Route Helper
 *
 * @static
 * @package		Joomla.Site
 * @subpackage	Weblinks
 * @since 1.5
 */
abstract class WeblinksHelperRoute
{
	/**
	 * @param	int	The route of the weblink item
	 */
	public static function getWeblinkRoute($id, $catid)
	{
		if ($catid)
		{
			jimport('joomla.application.categories');
			$categoryTree = JCategories::getInstance('com_weblinks');
			$category = $categoryTree->get($catid);
			$catids = array();
			$catids[] = $category->id;
			while($category->getParent() instanceof JCategoryNode)
			{
				$category = $category->getParent();
				$catids[] = $category->id;
			}
			$catids = array_reverse($catids);
		} else {
			$catids = array();
		}
		$needles = array(
			'weblink'  => (int) $id,
			'category' => $catids
		);

		//Create the link
		$link = 'index.php?option=com_weblinks&view=weblink&id='. $id;

		if (is_array($catids)) {
			$link .= '&catid='.array_pop($catids);
		}

		if ($item = WeblinksHelperRoute::_findItem($needles)) {
			$link .= '&Itemid='.$item->id;
		};

		return $link;
	}

	public static function getCategoryRoute($catid)
	{
		jimport('joomla.application.categories');
		$categoryTree = JCategories::getInstance('com_weblinks');
		$category = $categoryTree->get($catid);
		$catids = array();
		$catids[] = $category->id;
		while($category->getParent() instanceof JCategoryNode)
		{
			$category = $category->getParent();
			$catids[] = $category->id;
		}
		$catids = array_reverse($catids);
		$needles = array(
			'category' => $catids
		);
		$category = $categoryTree->get($catid);
		//Create the link
		$link = 'index.php?option=com_weblinks&view=category&id='.$category->slug;

		if ($item = WeblinksHelperRoute::_findItem($needles)) {
			if (isset($item->query['layout'])) {
				$link .= '&layout='.$item->query['layout'];
			}
			$link .= '&Itemid='.$item->id;
		};

		return $link;
	}

	protected static function _findItem($needles)
	{
		$component = &JComponentHelper::getComponent('com_weblinks');
		$app = JFactory::getApplication();
		$menus	= & $app->getMenu();
		$items	= $menus->getItems('component_id', $component->id);

		$match = null;
		foreach($needles as $needle => $id)
		{
			if (is_array($id))
			{
				foreach($id as $tempid)
				{
					foreach($items as $item)
					{
						if ((@$item->query['view'] == $needle) && (@$item->query['id'] == $tempid)) {
							$match = $item;
							break;
						}
					}

				}
			} else {
				foreach($items as $item)
				{
					if ((@$item->query['view'] == $needle) && (@$item->query['id'] == $id)) {
						$match = $item;
						break;
					}
				}
			}

			if (isset($match)) {
				break;
			}
		}

		return $match;
	}
}
?>
