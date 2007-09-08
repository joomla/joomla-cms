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

		$link = 'index.php?option=com_content';

		if(isset($item))
		{
			if($item->link_parts['view'] == 'article') {
				$link .=  '&Itemid='. $item->id;
			}

			if($item->link_parts['view'] == 'category') {
				$link .= '&view=article&catid='.$catid.'&id='. $id .'&Itemid='. $item->id;
			}

			if($item->link_parts['view'] == 'section') {
				$link .= '&view=article&catid='.$catid.'&id='. $id . '&Itemid='. $item->id;
			}
		} else {
			$link .= '&view=article&catid='.$catid.'&id='. $id;
		}

		return JRoute::_( $link );
	}

	function getSectionRoute($sectionid)
	{
		$item = ContentHelperRoute::_getSectionMenuInfo((int)$sectionid);

		$link = 'index.php?option=com_content&view=category&id='.$sectionid;

		if(isset($item))
		{
			$link .= '&layout='.$item->link_parts['layout'].'&Itemid='. $item->id;
		}

		return JRoute::_( $link );
	}

	function getCategoryRoute($catid, $sectionid)
	{
		$item = ContentHelperRoute::_getCategoryMenuInfo((int)$catid, (int)$sectionid);

		$link = 'index.php?option=com_content';

		if(isset($item))
		{
			if($item->link_parts['view'] == 'category') {
				$link .= '&view=category&id='.$catid.'&layout='.$item->link_parts['layout'].'&Itemid='. $item->id;
			}

			if($item->link_parts['view'] == 'section') {
				$link .= '&view=category&id='.$catid.'&layout='.$item->link_parts['layout'].'&Itemid='. $item->id;
			}
		} else {
			$link .= '&view=category&id='.$catid;
		}

		return JRoute::_( $link );
	}

	/**
	 * @param	int	The menu information based on the article identifiers
	 */
	function _getArticleMenuInfo($id, $catid = 0, $sectionid = 0)
	{
		$component	=& JComponentHelper::getComponent('com_content');

		$menus		=& JSite::getMenu();
		$items		= $menus->getItems('componentid', $component->id);

		$n = count( $items );
		if (!$n) {
			return null;
		}

		for ($i = 0; $i < $n; $i++)
		{
			$item = &$items[$i];
			$url = str_replace('index.php?', '', $item->link);
			$url = str_replace('&amp;', '&', $url);
			$parts = null;
			parse_str($url, $parts);

			if(!isset($parts['id'])) {
				continue;
			}

			// set the link parts
			$item->link_parts = $parts;

			// Do we have a content item linked to the menu with this id?
			if (($item->published) && ($item->link_parts['id'] == $id) && ($item->link_parts['view'] == 'article')) {
				return $item;
			}

			// Check to see if it is in a published category
			if (($item->published) && ($item->link_parts['id'] == $catid) && $item->link_parts['view'] == 'category') {
				return $item;
			}

			// Check to see if it is in a published section
			if (($item->published) && ($item->link_parts['id'] == $sectionid) && $item->link_parts['view'] == 'section') {
				return $item;
			}
		}

		return null;
	}

	/**
	 * @param	int	The menu information based on the category identifiers
	 */
	function _getCategoryMenuInfo($catid, $sectionid = 0)
	{
		$component	=& JComponentHelper::getComponent('com_content');

		$menus		=& JSite::getMenu();
		$items		= $menus->getItems('componentid', $component->id);

		$n = count( $items );
		if (!$n) {
			return null;
		}

		for ($i = 0; $i < $n; $i++)
		{
			$item = &$items[$i];
			$url = str_replace('index.php?', '', $item->link);
			$url = str_replace('&amp;', '&', $url);
			$parts = null;
			parse_str($url, $parts);

			if(!isset($parts['id'])) {
				continue;
			}

			// set the link parts
			$item->link_parts = $parts;

			// Check to see if it is in a published category
			if (($item->published) && ($item->link_parts['id'] == $catid) && $item->link_parts['view'] == 'category') {
				return $item;
			}

			// Check to see if it is in a published section
			if (($item->published) && ($item->link_parts['id'] == $sectionid) && $item->link_parts['view'] == 'section') {
				return $item;
			}
		}

		return null;
	}

	/**
	 * @param	int	The menu information based on the category identifiers
	 */
	function _getSectionMenuInfo($sectionid)
	{
		$component	=& JComponentHelper::getComponent('com_content');

		$menus		=& JSite::getMenu();
		$items		= $menus->getItems('componentid', $component->id);

		$n = count( $items );
		if (!$n) {
			return null;
		}

		for ($i = 0; $i < $n; $i++)
		{
			$item = &$items[$i];
			$url = str_replace('index.php?', '', $item->link);
			$url = str_replace('&amp;', '&', $url);
			$parts = null;
			parse_str($url, $parts);

			if(!isset($parts['id'])) {
				continue;
			}

			// set the link parts
			$item->link_parts = $parts;

			// Check to see if it is in a published section
			if (($item->published) && ($item->link_parts['id'] == $sectionid) && $item->link_parts['view'] == 'section') {
				return $item;
			}
		}

		return null;
	}

}
?>
