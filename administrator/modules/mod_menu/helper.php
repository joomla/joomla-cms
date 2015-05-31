<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

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
		$query	= $db->getQuery(true)
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

		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * Get a list of the authorised, non-special components to display in the components menu.
	 *
	 * @param   boolean  $authCheck	  An optional switch to turn off the auth check (to support custom layouts 'grey out' behaviour).
	 *
	 * @return  array  A nest array of component objects and submenus
	 *
	 * @since   1.6
	 */
	public static function getComponents($authCheck = true)
	{
		$lang   = JFactory::getLanguage();
		$user   = JFactory::getUser();
		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true);
		$result = array();

		// Prepare the query.
		$query->select('m.id, m.title, m.alias, m.link, m.parent_id, m.img, e.element')
			->from('#__menu AS m');

		// Filter on the enabled states.
		$query->join('LEFT', '#__extensions AS e ON m.component_id = e.extension_id')
			->where('m.client_id = 1')
			->where('e.enabled = 1')
			->where('m.id > 1');

		// Order by lft.
		$query->order('m.lft');

		$db->setQuery($query);

		// Component list
		$components = $db->loadObjectList();

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
					||	$lang->load($component->element . '.sys', JPATH_ADMINISTRATOR . '/components/' . $component->element, null, false, true);
					}

					$component->text = $lang->hasKey($component->title) ? JText::_($component->title) : $component->alias;
				}
			}
			else
			{
				// Sub-menu level.
				if (isset($result[$component->parent_id]))
				{
					// Add the submenu link if it is defined.
					if (isset($result[$component->parent_id]->submenu) && !empty($component->link))
					{
						$component->text                          = $lang->hasKey($component->title) ? JText::_($component->title) : $component->alias;
						$result[$component->parent_id]->submenu[] = &$component;
					}
				}
			}
		}

		$result = JArrayHelper::sortObjects($result, 'text', 1, false, true);

		return $result;
	}
}
