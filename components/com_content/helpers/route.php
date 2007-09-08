<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Component Helper
jimport('joomla.application.component.helper');

/**
 * Content Component Route Helper
 *
 * @static
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class ContentHelperRoute
{
	/**
	 * @param	int	The route of the content item
	 */
	function getArticleRoute($id, $catid = 0, $sectionid = 0)
	{
		$item = ContentHelperRoute::_getArticleMenuInfo((int)$id, (int)$catid, (int)$sectionid);

		$link = 'index.php?option=com_content&view=article&catid='.$catid.'&id='. $id;

		if(isset($item))
		{
			$link .= '&Itemid='. $item->id;
		}
		return JRoute::_( $link );
	}

	function getSectionRoute($sectionid)
	{
		$item = ContentHelperRoute::_getSectionMenuInfo((int)$sectionid);

		$link = 'index.php?option=com_content&view=section&id='.$sectionid;

		if(isset($item))
		{
			$link .= '&layout='.$item->link_parts['layout'].'&Itemid='. $item->id;
		}

		return JRoute::_( $link );
	}

	function getCategoryRoute($catid, $sectionid)
	{
		$item = ContentHelperRoute::_getCategoryMenuInfo((int)$catid, (int)$sectionid);

		$link = 'index.php?option=com_content&view=category&id='.$catid;

		if(isset($item))
		{
			$link .= '&layout='.$item->link_parts['layout'].'&Itemid='. $item->id;
		}
		return JRoute::_( $link );
	}

	/**
	 * @param	int	The menu information based on the article identifiers
	 */
	function _getArticleMenuInfo($id, $catid = 0, $sectionid = 0)
	{
		$items = ContentHelperRoute::_getComponentMenuItems('com_content');

		if(isset($items['section']))
		{
			foreach($items['section'] as $item)
			{
				if (($item->published) && ($item->link_parts['id'] == $sectionid)) {
					return $item;
				}
			}
		}

		if(isset($items['category']))
		{
			foreach($items['category'] as $item)
			{
				if (($item->published) && ($item->link_parts['id'] == $catid)) {
					return $item;
				}
			}
		}

		if(isset($items['article']))
		{
			foreach($items['article'] as $item)
			{
				if (($item->published) && ($item->link_parts['id'] == $id)) {
					return $item;
				}
			}
		}
		return null;
	}

	/**
	 * @param	int	The menu information based on the category identifiers
	 */
	function _getCategoryMenuInfo($catid, $sectionid = 0)
	{
		$items = ContentHelperRoute::_getComponentMenuItems('com_content');

		if(isset($items['section']))
		{
			foreach($items['section'] as $item)
			{
				if (($item->published) && ($item->link_parts['id'] == $sectionid)) {
					return $item;
				}
			}
		}

		if(isset($items['category']))
		{
			foreach($items['category'] as $item)
			{
				if (($item->published) && ($item->link_parts['id'] == $catid)) {
					return $item;
				}
			}
		}
		return null;
	}

	/**
	 * @param	int	The menu information based on the category identifiers
	 */
	function _getSectionMenuInfo($sectionid)
	{
		$items = ContentHelperRoute::_getComponentMenuItems('com_content');

		if(isset($items['section']))
		{
			foreach($items['section'] as $item)
			{
				if (($item->published) && ($item->link_parts['id'] == $sectionid) && $item->link_parts['view'] == 'section') {
					return $item;
				}
			}
		}
		return null;
	}

	function & _getComponentMenuItems($component)
	{
		static $items;

		if(!$items)
		{
			$comp	=& JComponentHelper::getComponent($component);

			$menus		=& JSite::getMenu();
			$menuitems		= $menus->getItems('componentid', $comp->id);

			foreach($menuitems as $menuitem)
			{
				$url = str_replace('index.php?', '', $menuitem->link);
				$url = str_replace('&amp;', '&', $url);
				$parts = null;
				parse_str($url, $parts);
				$menuitem->link_parts = $parts;

				$items[$menuitem->link_parts['view']][] = $menuitem;
			} 
		}
		return $items;
	}
}
?>
