<?php
/**
 * @version		$Id:mod_menu.php 2463 2006-02-18 06:05:38Z webImagery $
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Include dependancies.
jimport('joomla.database.query');

/**
 * @package		Joomla.Administrator
 * @subpackage	mod_menu
 */
abstract class ModMenuHelper
{
	/**
	 * Get a list of the available menus.
	 *
	 * @return	array	An array of the available menus (from the menu types table).
	 */
	public static function getMenus()
	{
		$db		= &JFactory::getDbo();
		$query	= new JQuery;

		$query->select('a.*, SUM(b.home) AS home');
		$query->from('#__menu_types AS a');
		$query->leftJoin('#__menu AS b ON b.menutype = a.menutype');
		$query->group('a.id');

		$db->setQuery($query);

		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * Get a list of the authorised, non-special components to display in the components menu.
	 *
	 * @param	array	An optional array of components to exclude from the list.
	 * @param	boolean	An optional switch to turn off the auth check (to support custom layouts 'grey out' behaviour).
	 *
	 * @return	array	A nest array of component objects and submenus
	 */
	public static function getComponents($exclude = array(), $authCheck = true)
	{
		// Initialise variables.
		$lang	= &JFactory::getLanguage();
		$user	= &JFactory::getUser();
		$db		= &JFactory::getDbo();
		$query	= new JQuery;
		$result	= array();
		$langs	= array();

		// SQL quote the excluded 'option' values.
		$exclude = array_map(array($db, 'quote'), $exclude);

		// Prepare the query.
		$query->select('c.id, c.parent, c.name, c.option, c.admin_menu_link, c.admin_menu_img');
		$query->from('#__components AS c');

		// Filter on the enabled states.
		$query->leftJoin('#__extensions e ON c.option = e.element');
		$query->where('((c.parent = 0 AND e.enabled = 1) OR c.parent > 0)');
		$query->where('((c.parent = 0 AND e.state > -1) OR c.parent > 0)');

		// Filter on the exclusions.
		if (is_array($exclude) && !empty($exclude)) {
			$query->where('c.option NOT IN ('.implode(',', $exclude).')');
		}

		// Order by parent (group top level first), then ordering, then name.
		$query->order('c.parent, c.ordering, c.name');

		$db->setQuery($query);
		$components	= $db->loadObjectList(); // component list
		// Parse the list of extensions.
		foreach ($components as &$component)
		{
			// Trim the menu link.
			$component->admin_menu_link = trim($component->admin_menu_link);

			if ($component->parent == 0)
			{
				// Only add this top level if it is authorised and enabled.
				if ($authCheck == false || ($authCheck && $user->authorize('core.manage', $component->option)))
				{
					// Root level.
					$result[$component->id] = $component;
					if (!isset($result[$component->id]->submenu)) {
						$result[$component->id]->submenu = array();
					}

					// If the root menu link is empty, add it in.
					if (empty($component->admin_menu_link)) {
						$component->admin_menu_link = 'index.php?option='.$component->option;
					}

					if (!empty($component->option)) {
						$langs[$component->option.'.menu'] = true;
					}
				}
			}
			else
			{
				// Sub-menu level.
				if (isset($result[$component->parent]))
				{
					// Add the submenu link if it is defined.
					if (isset($result[$component->parent]->submenu) && !empty($component->admin_menu_link)) {
						$result[$component->parent]->submenu[] = &$component;
					}
				}
			}

			// Check that index.php prefixes the link.
			if (strpos($component->admin_menu_link, 'index.php') === false && strpos($component->admin_menu_link, 'http') === false) {
				$component->admin_menu_link = 'index.php?'.$component->admin_menu_link;
			}
		}

		// Load additional language files.
		foreach (array_keys($langs) as $langName)
		{
			// Load the core file.
			$lang->load($langName);

			// Load extension-local file.
			$lang->load('menu', JPATH_ADMINISTRATOR.DS.'components'.DS.str_replace('.menu', '', $langName));
		}

		return $result;
	}
}
