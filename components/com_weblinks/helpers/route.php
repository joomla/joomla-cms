<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_weblinks
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Component Helper
jimport('joomla.application.component.helper');
jimport('joomla.application.categories');

/**
 * Weblinks Component Route Helper
 *
 * @static
 * @package		Joomla.Site
 * @subpackage	com_weblinks
 * @since 1.5
 */
abstract class WeblinksHelperRoute
{
	protected static $lookup;

	/**
	 * @param	int	The route of the weblink
	 */
	public static function getWeblinkRoute($id, $catid)
	{
		$needles = array(
			'weblink'  => array((int) $id)
		);

		//Create the link
		$link = 'index.php?option=com_weblinks&view=weblink&id='. $id;
		if ($catid > 1) {
			$categories = JCategories::getInstance('Weblinks');
			$category = $categories->get($catid);

			if($category) {
				$needles['category'] = array_reverse($category->getPath());
				$needles['categories'] = $needles['category'];
				$link .= '&catid='.$catid;
			}
		}

		if ($item = self::_findItem($needles)) {
			$link .= '&Itemid='.$item;
		}

		return $link;
	}

	/**
	 * @param	int		$id		The id of the weblink.
	 * @param	string	$return	The return page variable.
	 */
	public static function getFormRoute($id, $return = null)
	{
		// Create the link.
		if ($id) {
			$link = 'index.php?option=com_weblinks&task=weblink.edit&w_id='. $id;
		}
		else {
			$link = 'index.php?option=com_weblinks&task=weblink.add&w_id=0';
		}

		if ($return) {
			$link .= '&return='.$return;
		}

		return $link;
	}

	public static function getCategoryRoute($catid)
	{
		if ($catid instanceof JCategoryNode) {
			$id = $catid->id;
			$category = $catid;
		}
		else {
			$id = (int) $catid;
			$category = JCategories::getInstance('Weblinks')->get($id);
		}

		if ($id < 1 || !($category instanceof JCategoryNode))
		{
			$link = '';
		}
		else {
			$needles = array();

			//Create the link
			$link = 'index.php?option=com_weblinks&view=category&id='.$id;
			
			$catids = array_reverse($category->getPath());
			$needles['category'] = $catids;
			$needles['categories'] = $catids;

			if ($item = self::_findItem($needles)) {
				$link .= '&Itemid='.$item;
			}
		}

		return $link;
	}

	protected static function _findItem($needles = null)
	{
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu('site');

		// Prepare the reverse lookup array.
		if (self::$lookup === null) {
			self::$lookup = array();

			$component	= JComponentHelper::getComponent('com_weblinks');
			$items		= $menus->getItems('component_id', $component->id);

			if ($items) {
				foreach ($items as $item)
				{
					if (isset($item->query) && isset($item->query['view'])) {
						$view = $item->query['view'];

						if (!isset(self::$lookup[$view])) {
							self::$lookup[$view] = array();
						}

						if (isset($item->query['id'])) {
							self::$lookup[$view][$item->query['id']] = $item->id;
						}
					}
				}
			}
		}

		if ($needles)
		{
			foreach ($needles as $view => $ids)
			{
				if (isset(self::$lookup[$view]))
				{
					foreach($ids as $id)
					{
						if (isset(self::$lookup[$view][(int)$id]))
						{
							return self::$lookup[$view][(int)$id];
						}
					}
				}
			}
		}

		$active = $menus->getActive();
		if ($active)
		{
			return $active->id;
		}

		return null;
	}
}
