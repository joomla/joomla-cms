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
 * Content Component Helper Content
 *
 * @static
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class ContentHelper
{
	/**
	 * @param	int	The route of the content item
	 */
	function getArticleRoute($id, $catid = 0, $sectionid = 0)
	{
		$item = ContentHelper::_getArticleMenuInfo((int)$id, (int)$catid, (int)$sectionid);

		$link = 'index.php?option=com_content&view=article';
		if(@$item->link_parts['view'] == 'article') {
			$link .=  '&Itemid='. $item->id;
		}

		if(@$item->link_parts['view'] == 'category') {
			$link .= '&catid='.$catid.'&id='. $id . '&Itemid='. $item->id;
		}

		if(@$item->link_parts['view'] == 'section') {
			$link .= '&catid='.$catid.'&id='. $id . '&Itemid='. $item->id;
		}

		return JRoute::_( $link );
	}

	/**
	 * @param	int	The menu information based on the article identifiers
	 */
	function _getArticleMenuInfo($id, $catid = 0, $sectionid = 0)
	{
		$component	=& JComponentHelper::getInfo('com_content');

		$menus		=& JMenu::getInstance();
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
			if (($item->published) && ($item->link_parts['id'] == $id) && ($item->link_parts['view'] = 'article')) {
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
}
?>
