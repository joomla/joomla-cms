<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

// Component Helper
jimport('joomla.application.component.helper');
jimport('joomla.application.categorytree');

/**
 * Contact Component Route Helper
 *
 * @static
 * @package		Joomla
 * @subpackage	com_contact
 * @since 1.6
 */

class ContactHelperRoute
{
	function getContactRoute($id, $catid) {
		$needles = array(
			'category' => (int) $catid,
			'categories' => null
		);

		//Find the itemid
		$itemid = ContactHelperRoute::_findItem($needles);
		$itemid = $itemid ? '&Itemid='.$itemid : '';

		//Create the link
		$link = 'index.php?option=com_contact&view=contact&id='. $id . '&catid='.$catid . $itemid;

		return $link;
	}

	function _findItem($needles)
	{
		static $items;

		if (!$items)
		{
			$component = &JComponentHelper::getComponent('com_contact');
			$menu = &JSite::getMenu();
			$items = $menu->getItems('component_id', $component->id);
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

			if (isset($match)) {
				break;
			}
		}

		return $match;
	}
}
?>
