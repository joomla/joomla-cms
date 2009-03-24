<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
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
abstract class ContentHelperRoute
{
	/**
	 * @param	int	The route of the content item
	 */
	public static function getArticleRoute($id, $catids = 0)
	{
		$needles = array(
			'article'  => (int) $id,
			'category' => $catids
		);

		//Create the link
		$link = 'index.php?option=com_content&view=article&id='. $id;

		if(is_array($catids)) {
			$path = '&path='.implode('/', $catids);
			$link .= '&catid='.array_pop($catids).$path;
		}

		if($item = ContentHelperRoute::_findItem($needles)) {
			$link .= '&Itemid='.$item->id;
		};

		return $link;
	}

	public static function getCategoryRoute($catids)
	{
		$needles = array(
			'category' => $catids
		);
		
		//Create the link
		$link = 'index.php?option=com_content&view=category&id='.array_pop($catids).'&path='.implode('/', $catids);

		if($item = ContentHelperRoute::_findItem($needles)) {
			if(isset($item->query['layout'])) {
				$link .= '&layout='.$item->query['layout'];
			}
			$link .= '&Itemid='.$item->id;
		};

		return $link;
	}

	protected static function _findItem($needles)
	{
		$component =& JComponentHelper::getComponent('com_content');
		$app = JFactory::getApplication();
		$menus	= & $app->getMenu();
		$items	= $menus->getItems('componentid', $component->id);

		$match = null;

		foreach($needles as $needle => $id)
		{
			if(is_array($id))
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

			if(isset($match)) {
				break;
			}
		}

		return $match;
	}
}
?>
