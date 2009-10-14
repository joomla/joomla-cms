<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Component Helper
jimport('joomla.application.component.helper');
jimport('joomla.application.categorytree');

/**
 * Content Component Route Helper
 *
 * @static
 * @package		Joomla
 * @subpackage	com_content
 * @since 1.5
 */
abstract class ContentHelperRoute
{
	/**
	 * @param	int	The route of the content item
	 */
	public static function getArticleRoute($id, $catid)
	{
		if ($catid)
		{
			jimport('joomla.application.categories');
			$categoryTree = JCategories::getInstance('com_content');
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
			'article'  => (int) $id,
			'category' => $catids
		);

		//Create the link
		$link = 'index.php?option=com_content&view=article&id='. $id;

		if (is_array($catids)) {
			$link .= '&catid='.array_pop($catids);
		}

		if ($item = ContentHelperRoute::_findItem($needles)) {
			$link .= '&Itemid='.$item->id;
		};

		return $link;
	}

	public static function getCategoryRoute($catid)
	{
		jimport('joomla.application.categories');
		$categoryTree = JCategories::getInstance('com_content');
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
		$link = 'index.php?option=com_content&view=category&id='.$category->slug;

		if ($item = ContentHelperRoute::_findItem($needles)) {
			if (isset($item->query['layout'])) {
				$link .= '&layout='.$item->query['layout'];
			}
			$link .= '&Itemid='.$item->id;
		};

		return $link;
	}

	protected static function _findItem($needles)
	{
		$component = &JComponentHelper::getComponent('com_content');
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
