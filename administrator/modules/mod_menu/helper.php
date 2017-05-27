<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Helper for mod_menu
 *
 * @since  1.5
 */
abstract class ModMenuHelper
{
	/**
	 * Get a list of the available menus.
	 *
	 * @return  array  An array of the available menus (from the menu types table).
	 *
	 * @since   1.6
	 */
	public static function getMenus()
	{
		$db     = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('a.*, SUM(b.home) AS home')
			->from('#__menu_types AS a')
			->join('LEFT', '#__menu AS b ON b.menutype = a.menutype AND b.home != 0')
			->select('b.language')
			->join('LEFT', '#__languages AS l ON l.lang_code = language')
			->select('l.image')
			->select('l.sef')
			->select('l.title_native')
			->where('(b.client_id = 0 OR b.client_id IS NULL)');

		// Sqlsrv change
		$query->group('a.id, a.menutype, a.description, a.title, b.menutype,b.language,l.image,l.sef,l.title_native');

		$db->setQuery($query);

		try
		{
			$result = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			$result = array();
			JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
		}

		return $result;
	}

	/**
	 * Get a list of the authorised, non-special components to display in the components menu.
	 *
	 * @param   boolean  $authCheck    An optional switch to turn off the auth check (to support custom layouts 'grey out' behaviour).
	 * @param   boolean  $enabledOnly  Whether to load only enabled/published menu items.
	 * @param   int[]    $exclude      The menu items to exclude from the list
	 *
	 * @return  array  A nest array of component objects and submenus
	 *
	 * @since   1.6
	 */
	public static function getComponents($authCheck = true, $enabledOnly = false, $exclude = array())
	{
		$lang   = JFactory::getLanguage();
		$user   = JFactory::getUser();
		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true);
		$result = array();

		// Prepare the query.
		$query->select('m.id, m.title, m.alias, m.link, m.parent_id, m.img, e.element, m.menutype')
			->from('#__menu AS m')
			->where('m.menutype = ' . $db->q('main'))
			->where('m.client_id = 1')
			->where('m.id > 1');

		if ($enabledOnly)
		{
			$query->where('m.published = 1');
		}

		if (count($exclude))
		{
			$query->where('m.id NOT IN (' . implode(', ', array_map('intval', $exclude)) . ')');
			$query->where('m.parent_id NOT IN (' . implode(', ', array_map('intval', $exclude)) . ')');
		}

		// Filter on the enabled states.
		$query->join('INNER', '#__extensions AS e ON m.component_id = e.extension_id')
			->where('e.enabled = 1');

		// Order by lft.
		$query->order('m.lft');

		$db->setQuery($query);

		// Component list
		try
		{
			$components = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			$components = array();
			JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
		}

		// Parse the list of extensions.
		foreach ($components as &$component)
		{
			// Trim the menu link.
			$component->link = trim($component->link);

			if ($component->parent_id == 1)
			{
				// Only add this top level if it is authorised and enabled.
				if ($authCheck == false || ($authCheck && $user->authorise('core.manage', $component->element)))
				{
					// Root level.
					$result[$component->id] = $component;

					if (!isset($result[$component->id]->submenu))
					{
						$result[$component->id]->submenu = array();
					}

					// If the root menu link is empty, add it in.
					if (empty($component->link))
					{
						$component->link = 'index.php?option=' . $component->element;
					}

					if (!empty($component->element))
					{
						// Load the core file then
						// Load extension-local file.
						$lang->load($component->element . '.sys', JPATH_BASE, null, false, true)
						|| $lang->load($component->element . '.sys', JPATH_ADMINISTRATOR . '/components/' . $component->element, null, false, true);
					}

					$component->text = JText::_(strtoupper($component->title));
				}
			}
			// Sub-menu level.
			// Add the submenu link if it is defined.
			elseif (isset($result[$component->parent_id]) && isset($result[$component->parent_id]->submenu) && !empty($component->link))
			{
				$component->text = JText::_(strtoupper($component->title));

				$result[$component->parent_id]->submenu[] = &$component;
			}
		}

		return ArrayHelper::sortObjects($result, 'text', 1, false, true);
	}

	/**
	 * Load the menu items from database for the given menutype
	 *
	 * @param   string  $menutype  The selected menu type
	 *
	 * @return  array
	 *
	 * @since   3.7.0
	 */
	public static function getMenuItems($menutype)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Prepare the query.
		$query->select('m.*')
			->from('#__menu AS m')
			->where('m.menutype = ' . $db->q($menutype))
			->where('m.client_id = 1')
			->where('m.published = 1')
			->where('m.id > 1');

		// Filter on the enabled states.
		$query->select('e.element')
			->join('LEFT', '#__extensions AS e ON m.component_id = e.extension_id')
			->where('(e.enabled = 1 OR e.enabled IS NULL)');

		// Order by lft.
		$query->order('m.lft');

		$db->setQuery($query);

		// Component list
		try
		{
			$menuItems = $db->loadObjectList();

			foreach ($menuItems as &$menuitem)
			{
				$menuitem->params = new Registry($menuitem->params);
			}
		}
		catch (RuntimeException $e)
		{
			$menuItems = array();
			JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
		}

		return $menuItems;
	}

	/**
	 * Parse the list of extensions.
	 *
	 * @param   array  $menuItems  List of loaded components
	 * @param   bool   $authCheck  An optional switch to turn off the auth check (to support custom layouts 'grey out' behaviour).
	 *
	 * @return  array
	 *
	 * @since   3.7.0
	 */
	public static function parseItems($menuItems, $authCheck = true)
	{
		$result = array();
		$user   = JFactory::getUser();
		$lang   = JFactory::getLanguage();
		$levels = $user->getAuthorisedViewLevels();

		// Process each item
		foreach ($menuItems as $i => &$menuitem)
		{
			/*
			 * Exclude item with menu item option set to exclude from menu modules
			 * Exclude item if the component is not authorised
			 * Exclude item if menu item set access level is not met
			 */
			if (($menuitem->params->get('menu_show', 1) == 0)
				|| ($menuitem->element && $authCheck && !$user->authorise('core.manage', $menuitem->element))
				|| ($menuitem->access && !in_array($menuitem->access, $levels)))
			{
				continue;
			}

			// Evaluate link url
			switch ($menuitem->type)
			{
				case 'url':
				case 'component':
					$menuitem->link = trim($menuitem->link);
					break;
				case 'separator':
				case 'heading':
				case 'container':
					$menuitem->link = '#';
					break;
				case 'alias':
					$aliasTo        = $menuitem->params->get('aliasoptions');
					$menuitem->link = static::getLink($aliasTo);
					break;
				default:
			}

			if ($menuitem->link == '')
			{
				continue;
			}

			// Translate Menu item label, if needed
			if (!empty($menuitem->element))
			{
				$lang->load($menuitem->element . '.sys', JPATH_BASE, null, false, true)
				|| $lang->load($menuitem->element . '.sys', JPATH_ADMINISTRATOR . '/components/' . $menuitem->element, null, false, true);
			}

			$menuitem->text    = $lang->hasKey($menuitem->title) ? JText::_($menuitem->title) : $menuitem->title;
			$menuitem->submenu = array();

			$result[$menuitem->parent_id][$menuitem->id] = $menuitem;
		}

		// Do an early exit if there are no top level menu items.
		if (!isset($result[1]))
		{
			return array();
		}

		// Put the items under respective parent menu items.
		foreach ($result as $parentId => &$mItems)
		{
			foreach ($mItems as &$mItem)
			{
				if (isset($result[$mItem->id]))
				{
					static::cleanup($result[$mItem->id]);

					$mItem->submenu = &$result[$mItem->id];
				}
			}
		}

		// Return only top level items
		return $result[1];
	}

	/**
	 * Method to get a link to the aliased menu item
	 *
	 * @param   int  $menuId  The record id of the referencing menu item
	 *
	 * @return  string
	 *
	 * @since   3.7.0
	 */
	protected static function getLink($menuId)
	{
		$table = JTable::getInstance('Menu');
		$table->load($menuId);

		// Look for an alias-to-alias
		if ($table->type == 'alias')
		{
			$params  = new Registry($table->params);
			$aliasTo = $params->get('aliasoptions');

			return static::getLink($aliasTo);
		}

		return $table->link;
	}

	/**
	 * Method to cleanup the menu items for repeated, leading or trailing separators in a given menu level
	 *
	 * @param   array  &$items  The list of menu items in the selected level
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	protected static function cleanup(&$items)
	{
		$b = true;

		foreach ($items as $k => &$item)
		{
			if ($item->type == 'separator')
			{
				if ($b)
				{
					$item = false;
				}

				$b = true;
			}
			else
			{
				$b = false;
			}
		}

		if ($b)
		{
			$item = false;
		}

		$items = array_filter($items);
	}
}
