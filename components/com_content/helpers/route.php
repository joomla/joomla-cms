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
		$items = ContentHelperRoute::_getComponentMenuItems('com_content');

		$parts = array((int) $sectionid => 'section', (int) $catid => 'category', (int) $id => 'article');

		$item = ContentHelperRoute::_checkMenuItems($items, $parts);

		$link = 'index.php?option=com_content&view=article&catid='.$catid.'&id='. $id;

		if(isset($item))
		{
			$link .= '&Itemid='. $item->id;
		}
		return JRoute::_( $link );
	}

	function getSectionRoute($sectionid)
	{
		$items = ContentHelperRoute::_getComponentMenuItems('com_content');

		$parts = array((int) $sectionid => 'section');

		$item = ContentHelperRoute::_checkMenuItems($items, $parts);

		$link = 'index.php?option=com_content&view=section&id='.$sectionid;

		if(isset($item))
		{
			if(isset($item->link_parts['layout']))
			{
				$link .= '&layout='.$item->link_parts['layout'];
			}
			$link .= '&Itemid='. $item->id;
		}

		return JRoute::_( $link );
	}

	function getCategoryRoute($catid, $sectionid)
	{
		$items = ContentHelperRoute::_getComponentMenuItems('com_content');

		$parts = array((int) $sectionid => 'section', (int) $catid => 'category');

		$item = ContentHelperRoute::_checkMenuItems($items, $parts);

		$link = 'index.php?option=com_content&view=category&id='.$catid;

		if(isset($item))
		{
			if(isset($item->link_parts['layout']))
			{
				$link .= '&layout='.$item->link_parts['layout'];
			}
			$link .= '&Itemid='. $item->id;
		}
		return JRoute::_( $link );
	}

	function _checkMenuItems(& $items, $parts)
	{
		foreach($parts as $id => $part)
		{
			if(isset($items[$part]))
			{
				foreach($items[$part] as $item)
				{
					if (($item->published) && (@$item->link_parts['id'] == $id)) {
						return $item;
					}
				}
			}
		}
			
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
