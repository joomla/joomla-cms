<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_tags
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Tags Component Route Helper
 *
 * @static
 * @package     Joomla.Site
 * @subpackage  com_tags
 * @since       3.1
 */
class TagsHelperRoute extends JHelperRoute
{
	protected static $lookup;

	/**
	 * @paramn  integer   The route of the tag
	 *
	 * @since  3.1
	 */
	public static function getItemRoute($id)
	{
		$needles = array(
			'item'  => array((int) $id)
		);

		//Create the link
		$link = 'index.php?option=com_tags&view=tag&id='. $id;

		if ($item = self::_findItem($needles))
		{
			$link .= '&Itemid='.$item;
		}
		elseif ($item = self::_findItem())
		{
			$link .= '&Itemid='.$item;
		}

		return $link;
	}

	public function getRoute($id, $typealias = 'com_tags.tag', $link = '', $language = null, $catid = null)
	{
		$needles = array(
			'tag'  => array((int) $id)
		);
		if ($id < 1)
		{
			$link = '';
		}
		else
		{
			if (!empty($needles) && $item = self::_findItem($needles))
			{
				$link = 'index.php?Itemid=' . $item;
			}
			else
			{
				//Create the link
				$link = 'index.php?option=com_tags&view=tag&id=' . $id;
			}
		}

		return $link;
	}

	protected static function _findItem($needles = null)
	{
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu('site');
		$language	= isset($needles['language']) ? $needles['language'] : '*';

		// Prepare the reverse lookup array.
		if (self::$lookup === null)
		{
			self::$lookup = array();

			$component	= JComponentHelper::getComponent('com_tags');
			$items		= $menus->getItems('component_id', $component->id);

			if ($items) {
				foreach ($items as $item)
				{
					if (isset($item->query) && isset($item->query['view'])) {
						$view = $item->query['view'];

						if (!isset(self::$lookup[$view]))
						{
							self::$lookup[$view] = array();
						}

						if (isset($item->query['id[0]']))
						{
							// Here it will become a bit tricky
							// language != * can override existing entries
							// language == * cannot override existing entries
							if (!isset(self::$lookup[$language][$view][$item->query['id[0]']]) || $item->language != '*')
							{
								self::$lookup[$language][$view][$item->query['id[0]']] = $item->id;
							}

							self::$lookup[$view][$item->query['id[0]']] = $item->id;
						}
						if (isset($item->query["tag_list_language_filter"]))
						{
							$language = $item->query["tag_list_language_filter"];
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
						if (isset(self::$lookup[$view][(int) $id]))
						{
							return self::$lookup[$view][(int) $id];
						}
					}
				}
			}
		}
		else
		{
			$active = $menus->getActive();
			if ($active)
			{
				return $active->id;
			}
		}

		return null;
	}
}
