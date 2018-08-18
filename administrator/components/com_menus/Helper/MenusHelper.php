<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Menus\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Menu\MenuHelper;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Menus component helper.
 *
 * @since  1.6
 */
class MenusHelper
{
	/**
	 * Defines the valid request variables for the reverse lookup.
	 */
	protected static $_filter = array('option', 'view', 'layout');

	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public static function addSubmenu($vName)
	{
		\JHtmlSidebar::addEntry(
			\JText::_('COM_MENUS_SUBMENU_MENUS'),
			'index.php?option=com_menus&view=menus',
			$vName == 'menus'
		);
		\JHtmlSidebar::addEntry(
			\JText::_('COM_MENUS_SUBMENU_ITEMS'),
			'index.php?option=com_menus&view=items',
			$vName == 'items'
		);
	}

	/**
	 * Gets a standard form of a link for lookups.
	 *
	 * @param   mixed  $request  A link string or array of request variables.
	 *
	 * @return  mixed  A link in standard option-view-layout form, or false if the supplied response is invalid.
	 *
	 * @since   1.6
	 */
	public static function getLinkKey($request)
	{
		if (empty($request))
		{
			return false;
		}

		// Check if the link is in the form of index.php?...
		if (is_string($request))
		{
			$args = array();

			if (strpos($request, 'index.php') === 0)
			{
				parse_str(parse_url(htmlspecialchars_decode($request), PHP_URL_QUERY), $args);
			}
			else
			{
				parse_str($request, $args);
			}

			$request = $args;
		}

		// Only take the option, view and layout parts.
		foreach ($request as $name => $value)
		{
			if ((!in_array($name, self::$_filter)) && (!($name == 'task' && !array_key_exists('view', $request))))
			{
				// Remove the variables we want to ignore.
				unset($request[$name]);
			}
		}

		ksort($request);

		return 'index.php?' . http_build_query($request, '', '&');
	}

	/**
	 * Get the menu list for create a menu module
	 *
	 * @param   int  $clientId  Optional client id - viz 0 = site, 1 = administrator, can be NULL for all
	 *
	 * @return  array  The menu array list
	 *
	 * @since    1.6
	 */
	public static function getMenuTypes($clientId = 0)
	{
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('a.menutype')
			->from('#__menu_types AS a');

		if (isset($clientId))
		{
			$query->where('a.client_id = ' . (int) $clientId);
		}

		$db->setQuery($query);

		return $db->loadColumn();
	}

	/**
	 * Get a list of menu links for one or all menus.
	 *
	 * @param   string   $menuType   An option menu to filter the list on, otherwise all menu with given client id links
	 *                               are returned as a grouped array.
	 * @param   integer  $parentId   An optional parent ID to pivot results around.
	 * @param   integer  $mode       An optional mode. If parent ID is set and mode=2, the parent and children are excluded from the list.
	 * @param   array    $published  An optional array of states
	 * @param   array    $languages  Optional array of specify which languages we want to filter
	 * @param   int      $clientId   Optional client id - viz 0 = site, 1 = administrator, can be NULL for all (used only if menutype not givein)
	 *
	 * @return  array
	 *
	 * @since   1.6
	 */
	public static function getMenuLinks($menuType = null, $parentId = 0, $mode = 0, $published = array(), $languages = array(), $clientId = 0)
	{
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('DISTINCT(a.id) AS value,
					  a.title AS text,
					  a.alias,
					  a.level,
					  a.menutype,
					  a.client_id,
					  a.type,
					  a.published,
					  a.template_style_id,
					  a.checked_out,
					  a.language,
					  a.lft'
			)
			->from('#__menu AS a');

		$query->select('e.name as componentname, e.element')
			->join('left', '#__extensions e ON e.extension_id = a.component_id');

		if (Multilanguage::isEnabled())
		{
			$query->select('l.title AS language_title, l.image AS language_image, l.sef AS language_sef')
				->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = a.language');
		}

		// Filter by the type if given, this is more specific than client id
		if ($menuType)
		{
			$query->where('(a.menutype = ' . $db->quote($menuType) . ' OR a.parent_id = 0)');
		}
		elseif (isset($clientId))
		{
			$query->where('a.client_id = ' . (int) $clientId);
		}

		// Prevent the parent and children from showing if requested.
		if ($parentId && $mode == 2)
		{
			$query->join('LEFT', '#__menu AS p ON p.id = ' . (int) $parentId)
				->where('(a.lft <= p.lft OR a.rgt >= p.rgt)');
		}

		if (!empty($languages))
		{
			if (is_array($languages))
			{
				$languages = '(' . implode(',', array_map(array($db, 'quote'), $languages)) . ')';
			}

			$query->where('a.language IN ' . $languages);
		}

		if (!empty($published))
		{
			if (is_array($published))
			{
				$published = '(' . implode(',', $published) . ')';
			}

			$query->where('a.published IN ' . $published);
		}

		$query->where('a.published != -2');
		$query->order('a.lft ASC');

		// Get the options.
		$db->setQuery($query);

		try
		{
			$links = $db->loadObjectList();
		}
		catch (\RuntimeException $e)
		{
			\JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		if (empty($menuType))
		{
			// If the menutype is empty, group the items by menutype.
			$query->clear()
				->select('*')
				->from('#__menu_types')
				->where('menutype <> ' . $db->quote(''))
				->order('title, menutype');

			if (isset($clientId))
			{
				$query->where('client_id = ' . (int) $clientId);
			}

			$db->setQuery($query);

			try
			{
				$menuTypes = $db->loadObjectList();
			}
			catch (\RuntimeException $e)
			{
				\JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');

				return false;
			}

			// Create a reverse lookup and aggregate the links.
			$rlu = array();

			foreach ($menuTypes as &$type)
			{
				$rlu[$type->menutype] = & $type;
				$type->links = array();
			}

			// Loop through the list of menu links.
			foreach ($links as &$link)
			{
				if (isset($rlu[$link->menutype]))
				{
					$rlu[$link->menutype]->links[] = & $link;

					// Cleanup garbage.
					unset($link->menutype);
				}
			}

			return $menuTypes;
		}
		else
		{
			return $links;
		}
	}

	/**
	 * Get the associations
	 *
	 * @param   integer  $pk  Menu item id
	 *
	 * @return  array
	 *
	 * @since   3.0
	 */
	public static function getAssociations($pk)
	{
		$langAssociations = Associations::getAssociations('com_menus', '#__menu', 'com_menus.item', $pk, 'id', '', '');
		$associations     = array();

		foreach ($langAssociations as $langAssociation)
		{
			$associations[$langAssociation->language] = $langAssociation->id;
		}

		return $associations;
	}

	/**
	 * Load the menu items from database for the given menutype
	 *
	 * @param   string   $menutype     The selected menu type
	 * @param   boolean  $enabledOnly  Whether to load only enabled/published menu items.
	 * @param   int[]    $exclude      The menu items to exclude from the list
	 *
	 * @return  array
	 *
	 * @since   3.8.0
	 */
	public static function getMenuItems($menutype, $enabledOnly = false, $exclude = array())
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		// Prepare the query.
		$query->select('m.*')
			->from('#__menu AS m')
			->where('m.menutype = ' . $db->q($menutype))
			->where('m.client_id = 1')
			->where('m.id > 1');

		if ($enabledOnly)
		{
			$query->where('m.published = 1');
		}

		// Filter on the enabled states.
		$query->select('e.element')
			->join('LEFT', '#__extensions AS e ON m.component_id = e.extension_id')
			->where('(e.enabled = 1 OR e.enabled IS NULL)');

		if (count($exclude))
		{
			$exId = array_filter($exclude, 'is_numeric');
			$exEl = array_filter($exclude, 'is_string');

			if ($exId)
			{
				$query->where('m.id NOT IN (' . implode(', ', array_map('intval', $exId)) . ')');
				$query->where('m.parent_id NOT IN (' . implode(', ', array_map('intval', $exId)) . ')');
			}

			if ($exEl)
			{
				$query->where('e.element NOT IN (' . implode(', ', $db->quote($exEl)) . ')');
			}
		}

		// Order by lft.
		$query->order('m.lft');

		$db->setQuery($query);

		try
		{
			$menuItems = $db->loadObjectList();

			foreach ($menuItems as &$menuitem)
			{
				$menuitem->params = new Registry($menuitem->params);
			}
		}
		catch (\RuntimeException $e)
		{
			$menuItems = array();

			Factory::getApplication()->enqueueMessage(\JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
		}

		return $menuItems;
	}

	/**
	 * Method to install a preset menu into database and link them to the given menutype
	 *
	 * @param   string  $preset    The preset name
	 * @param   string  $menutype  The target menutype
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   3.8.0
	 */
	public static function installPreset($preset, $menutype)
	{
		$items = MenuHelper::loadPreset($preset, false);

		if (count($items) == 0)
		{
			throw new \Exception(\JText::_('COM_MENUS_PRESET_LOAD_FAILED'));
		}

		static::installPresetItems($items, $menutype, 1);
	}

	/**
	 * Method to install a preset menu item into database and link it to the given menutype
	 *
	 * @param   \stdClass[]  &$items    The single menuitem instance with a list of its descendants
	 * @param   string       $menutype  The target menutype
	 * @param   int          $parent    The parent id or object
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   3.8.0
	 */
	protected static function installPresetItems(&$items, $menutype, $parent = 1)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		static $components = array();

		if (!$components)
		{
			$query->select('extension_id, element')->from('#__extensions')->where('type = ' . $db->q('component'));
			$components = $db->setQuery($query)->loadObjectList();
			$components = ArrayHelper::getColumn((array) $components, 'element', 'extension_id');
		}

		Factory::getApplication()->triggerEvent('onPreprocessMenuItems', array('com_menus.administrator.import', &$items, null, true));

		foreach ($items as &$item)
		{
			/** @var  \JTableMenu  $table */
			$table = Table::getInstance('Menu');

			$item->alias = $menutype . '-' . $item->title;

			if ($item->type == 'separator')
			{
				// Do not reuse a separator
				$item->title = $item->title ?: '-';
				$item->alias = microtime(true);
			}
			elseif ($item->type == 'heading' || $item->type == 'container')
			{
				// Try to match an existing record to have minimum collision for a heading
				$keys  = array(
					'menutype'  => $menutype,
					'type'      => $item->type,
					'title'     => $item->title,
					'parent_id' => $parent,
					'client_id' => 1,
				);
				$table->load($keys);
			}
			elseif ($item->type == 'url' || $item->type == 'component')
			{
				if (substr($item->link, 0, 8) === 'special:')
				{
					$special = substr($item->link, 8);

					if ($special === 'language-forum')
					{
						$item->link = 'index.php?option=com_admin&amp;view=help&amp;layout=langforum';
					}
					elseif ($special === 'custom-forum')
					{
						$item->link = '';
					}
				}

				// Try to match an existing record to have minimum collision for a link
				$keys  = array(
					'menutype'  => $menutype,
					'type'      => $item->type,
					'link'      => $item->link,
					'parent_id' => $parent,
					'client_id' => 1,
				);
				$table->load($keys);
			}

			// Translate "hideitems" param value from "element" into "menu-item-id"
			if ($item->type == 'container' && count($hideitems = (array) $item->params->get('hideitems')))
			{
				foreach ($hideitems as &$hel)
				{
					if (!is_numeric($hel))
					{
						$hel = array_search($hel, $components);
					}
				}

				$query->clear()->select('id')->from('#__menu')->where('component_id IN (' . implode(', ', $hideitems) . ')');
				$hideitems = $db->setQuery($query)->loadColumn();

				$item->params->set('hideitems', $hideitems);
			}

			$record = array(
				'menutype'     => $menutype,
				'title'        => $item->title,
				'alias'        => $item->alias,
				'type'         => $item->type,
				'link'         => $item->link,
				'browserNav'   => $item->browserNav ? 1 : 0,
				'img'          => $item->class,
				'access'       => $item->access,
				'component_id' => array_search($item->element, $components),
				'parent_id'    => $parent,
				'client_id'    => 1,
				'published'    => 1,
				'language'     => '*',
				'home'         => 0,
				'params'       => (string) $item->params,
			);

			if (!$table->bind($record))
			{
				throw new \Exception('Bind failed: ' . $table->getError());
			}

			$table->setLocation($parent, 'last-child');

			if (!$table->check())
			{
				throw new \Exception('Check failed: ' . $table->getError());
			}

			if (!$table->store())
			{
				throw new \Exception('Saved failed: ' . $table->getError());
			}

			$item->id = $table->get('id');

			if (!empty($item->submenu))
			{
				static::installPresetItems($item->submenu, $menutype, $item->id);
			}
		}
	}
}
