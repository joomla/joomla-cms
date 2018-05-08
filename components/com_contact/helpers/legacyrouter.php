<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Legacy routing rules class from com_contact
 *
 * @since       3.6
 * @deprecated  4.0
 */
class ContactRouterRulesLegacy implements JComponentRouterRulesInterface
{
	/**
	 * Constructor for this legacy router
	 *
	 * @param   JComponentRouterAdvanced  $router  The router this rule belongs to
	 *
	 * @since       3.6
	 * @deprecated  4.0
	 */
	public function __construct($router)
	{
		$this->router = $router;
	}

	/**
	 * Preprocess the route for the com_contact component
	 *
	 * @param   array  &$query  An array of URL arguments
	 *
	 * @return  void
	 *
	 * @since       3.6
	 * @deprecated  4.0
	 */
	public function preprocess(&$query)
	{
	}

	/**
	 * Build the route for the com_contact component
	 *
	 * @param   array  &$query     An array of URL arguments
	 * @param   array  &$segments  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @return  void
	 *
	 * @since       3.6
	 * @deprecated  4.0
	 */
	public function build(&$query, &$segments)
	{
		// Get a menu item based on Itemid or currently active
		$params = JComponentHelper::getParams('com_contact');
		$advanced = $params->get('sef_advanced_link', 0);

		if (empty($query['Itemid']))
		{
			$menuItem = $this->router->menu->getActive();
		}
		else
		{
			$menuItem = $this->router->menu->getItem($query['Itemid']);
		}

		$mView = empty($menuItem->query['view']) ? null : $menuItem->query['view'];
		$mId = empty($menuItem->query['id']) ? null : $menuItem->query['id'];

		if (isset($query['view']))
		{
			$view = $query['view'];

			if (empty($query['Itemid']) || empty($menuItem) || $menuItem->component != 'com_contact')
			{
				$segments[] = $query['view'];
			}

			unset($query['view']);
		}

		// Are we dealing with a contact that is attached to a menu item?
		if (isset($view) && ($mView == $view) && isset($query['id']) && ($mId == (int) $query['id']))
		{
			unset($query['view'], $query['catid'], $query['id']);

			return;
		}

		if (isset($view) && ($view == 'category' || $view == 'contact'))
		{
			if ($mId != (int) $query['id'] || $mView != $view)
			{
				if ($view == 'contact' && isset($query['catid']))
				{
					$catid = $query['catid'];
				}
				elseif (isset($query['id']))
				{
					$catid = $query['id'];
				}

				$menuCatid = $mId;
				$categories = JCategories::getInstance('Contact');
				$category = $categories->get($catid);

				if ($category)
				{
					// TODO Throw error that the category either not exists or is unpublished
					$path = array_reverse($category->getPath());

					$array = array();

					foreach ($path as $id)
					{
						if ((int) $id == (int) $menuCatid)
						{
							break;
						}

						if ($advanced)
						{
							list($tmp, $id) = explode(':', $id, 2);
						}

						$array[] = $id;
					}

					$segments = array_merge($segments, array_reverse($array));
				}

				if ($view == 'contact')
				{
					if ($advanced)
					{
						list($tmp, $id) = explode(':', $query['id'], 2);
					}
					else
					{
						$id = $query['id'];
					}

					$segments[] = $id;
				}
			}

			unset($query['id'], $query['catid']);
		}

		if (isset($query['layout']))
		{
			if (!empty($query['Itemid']) && isset($menuItem->query['layout']))
			{
				if ($query['layout'] == $menuItem->query['layout'])
				{
					unset($query['layout']);
				}
			}
			else
			{
				if ($query['layout'] == 'default')
				{
					unset($query['layout']);
				}
			}
		}

		$total = count($segments);

		for ($i = 0; $i < $total; $i++)
		{
			$segments[$i] = str_replace(':', '-', $segments[$i]);
		}
	}

	/**
	 * Parse the segments of a URL.
	 *
	 * @param   array  &$segments  The segments of the URL to parse.
	 * @param   array  &$vars      The URL attributes to be used by the application.
	 *
	 * @return  void
	 *
	 * @since       3.6
	 * @deprecated  4.0
	 */
	public function parse(&$segments, &$vars)
	{
		$total = count($segments);

		for ($i = 0; $i < $total; $i++)
		{
			$segments[$i] = preg_replace('/-/', ':', $segments[$i], 1);
		}

		// Get the active menu item.
		$item = $this->router->menu->getActive();
		$params = JComponentHelper::getParams('com_contact');
		$advanced = $params->get('sef_advanced_link', 0);

		// Count route segments
		$count = count($segments);

		// Standard routing for newsfeeds.
		if (!isset($item))
		{
			$vars['view'] = $segments[0];
			$vars['id'] = $segments[$count - 1];

			return;
		}

		// From the categories view, we can only jump to a category.
		$id = (isset($item->query['id']) && $item->query['id'] > 1) ? $item->query['id'] : 'root';

		$contactCategory = JCategories::getInstance('Contact')->get($id);

		$categories = $contactCategory ? $contactCategory->getChildren() : array();
		$vars['catid'] = $id;
		$vars['id'] = $id;
		$found = 0;

		foreach ($segments as $segment)
		{
			$segment = $advanced ? str_replace(':', '-', $segment) : $segment;

			foreach ($categories as $category)
			{
				if ($category->slug == $segment || $category->alias == $segment)
				{
					$vars['id'] = $category->id;
					$vars['catid'] = $category->id;
					$vars['view'] = 'category';
					$categories = $category->getChildren();
					$found = 1;
					break;
				}
			}

			if ($found == 0)
			{
				if ($advanced)
				{
					$db = JFactory::getDbo();
					$query = $db->getQuery(true)
						->select($db->quoteName('id'))
						->from('#__contact_details')
						->where($db->quoteName('catid') . ' = ' . (int) $vars['catid'])
						->where($db->quoteName('alias') . ' = ' . $db->quote($segment));
					$db->setQuery($query);
					$nid = $db->loadResult();
				}
				else
				{
					$nid = $segment;
				}

				$vars['id'] = $nid;
				$vars['view'] = 'contact';
			}

			$found = 0;
		}
	}
}
