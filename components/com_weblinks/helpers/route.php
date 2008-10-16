<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Weblinks
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
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
 * Weblinks Component Route Helper
 *
 * @static
 * @package		Joomla
 * @subpackage	Weblinks
 * @since 1.5
 */
class WeblinksHelperRoute
{
	function getWeblinkRoute($id, $catid) {
		$needles = array(
			'category' => (int) $catid,
			'categories' => null
		);

		//Find the itemid
		$itemid = WeblinksHelperRoute::_findItem($needles);
		$itemid = $itemid ? '&Itemid='.$itemid : '';

		//Create the link
		$link = 'index.php?option=com_weblinks&view=weblink&id='. $id . '&catid='.$catid . $itemid;

		return $link;
	}

	function _findItem($needles)
	{
		static $items;

		if (!$items)
		{
			$component =& JComponentHelper::getComponent('com_weblinks');
			$menu = &JSite::getMenu();
			$items = $menu->getItems('componentid', $component->id);
		}

		if (!is_array($items)) {
			return null;
		}

		$match = null;
		foreach($needles as $needle => $id)
		{
			foreach($items as $item)
			{
				if ((@$item->query['view'] == $needle) && (@$item->query['id'] == $id)) {
					$match = $item->id;
					break;
				}
			}

			if(isset($match)) {
				break;
			}
		}

		return $match;
	}
}
?>
