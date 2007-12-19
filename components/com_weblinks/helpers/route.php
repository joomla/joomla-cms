<?php
/**
 * @version		$Id: route.php 9019 2007-09-26 00:40:35Z jinx $
 * @package		Joomla
 * @subpackage	Weblinks
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
		
		//Create the link
		$link = 'index.php?option=com_weblinks&view=weblink&id='. $id . '&catid='.$catid;
		$link .= '&Itemid=' . WeblinksHelperRoute::_findItem($needles);
		
		return $link;		
	}

	function _findItem($needles)
	{
		$component =& JComponentHelper::getComponent('com_weblinks');

		$menus	= &JApplication::getMenu('site', array());
		$items	= $menus->getItems('componentid', $component->id);
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
