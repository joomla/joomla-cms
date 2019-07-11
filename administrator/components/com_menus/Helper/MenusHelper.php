<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Menus\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Menu\MenuItem;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Menus component helper.
 *
 * @since  1.6
 */
class MenusHelper extends ContentHelper
{
	/**
	 * Defines the valid request variables for the reverse lookup.
	 */
	protected static $_filter = array('option', 'view', 'layout');

	/**
	 * List of preset include paths
	 *
	 * @var  array
	 *
	 * @since   4.0.0
	 */
	protected static $presets = null;

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
		$db = Factory::getDbo();
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
		$db = Factory::getDbo();
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
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

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
				Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

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
	 * @return  MenuItem  A root node with the menu items as children
	 *
	 * @since   4.0.0
	 */
	public static function getMenuItems($menutype, $enabledOnly = false, $exclude = array())
	{
		$root = new MenuItem;
		$db    = Factory::getContainer()->get(DatabaseInterface::class);
		$query = $db->getQuery(true);

		// Prepare the query.
		$query->select('m.*')
			->from('#__menu AS m')
			->where('m.menutype = ' . $db->quote($menutype))
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
			$menuItems = $db->loadObjectList('id', '\Joomla\CMS\Menu\MenuItem');

			foreach ($menuItems as $menuitem)
			{
				$menuitem->params = new Registry($menuitem->params);

				// Resolve the alias item to get the original item
				if ($menuitem->type == 'alias')
				{
					static::resolveAlias($menuitem);
				}

				if ($menuitem->link = in_array($menuitem->type, array('separator', 'heading', 'container')) ? '#' : trim($menuitem->link))
				{
					$menuitem->submenu    = array();
					$menuitem->class      = $menuitem->img ?? '';
					$menuitem->scope      = $menuitem->scope ?? null;
					$menuitem->browserNav = $menuitem->browserNav ? '_blank' : '';
				}

				if ($menuitem->parent_id > 1)
				{
					if (isset($menuItems[$menuitem->parent_id]))
					{
						$menuItems[$menuitem->parent_id]->addChild($menuitem);
					}
				}
				else
				{
					$root->addChild($menuitem);
				}
			}
		}
		catch (\RuntimeException $e)
		{
			Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
		}

		return $root;
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
	 * @since   4.0.0
	 */
	public static function installPreset($preset, $menutype)
	{
		$root = static::loadPreset($preset, false);

		if (count($root->getChildren()) == 0)
		{
			throw new \Exception(Text::_('COM_MENUS_PRESET_LOAD_FAILED'));
		}

		static::installPresetItems($root, $menutype, 1);
	}

	/**
	 * Method to install a preset menu item into database and link it to the given menutype
	 *
	 * @param   MenuItem  $node      The parent node of the items to process
	 * @param   string    $menutype  The target menutype
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   4.0.0
	 */
	protected static function installPresetItems($node, $menutype)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$items = $node->getChildren();

		static $components = array();

		if (!$components)
		{
			$query->select('extension_id, element')->from('#__extensions')->where('type = ' . $db->quote('component'));
			$components = $db->setQuery($query)->loadObjectList();
			$components = ArrayHelper::getColumn((array) $components, 'element', 'extension_id');
		}

		Factory::getApplication()->triggerEvent('onPreprocessMenuItems', array('com_menus.administrator.import', &$items, null, true));

		foreach ($items as $item)
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
					'parent_id' => $item->getParent()->id,
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
					'parent_id' => $item->getParent()->id,
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
				'component_id' => array_search($item->element, $components) ?: 0,
				'parent_id'    => $item->getParent()->id,
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

			$table->setLocation($item->getParent()->id, 'last-child');

			if (!$table->check())
			{
				throw new \Exception('Check failed: ' . $table->getError());
			}

			if (!$table->store())
			{
				throw new \Exception('Saved failed: ' . $table->getError());
			}

			$item->id = $table->get('id');

			if ($item->hasChildren())
			{
				static::installPresetItems($item, $menutype);
			}
		}
	}

	/**
	 * Add a custom preset externally via plugin or any other means.
	 * WARNING: Presets with same name will replace previously added preset *except* Joomla's default preset (joomla)
	 *
	 * @param   string  $name     The unique identifier for the preset.
	 * @param   string  $title    The display label for the preset.
	 * @param   string  $path     The path to the preset file.
	 * @param   bool    $replace  Whether to replace the preset with the same name if any (except 'joomla').
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public static function addPreset($name, $title, $path, $replace = true)
	{
		if (static::$presets === null)
		{
			static::getPresets();
		}

		if ($name == 'joomla')
		{
			$replace = false;
		}

		if (($replace || !array_key_exists($name, static::$presets)) && is_file($path))
		{
			$preset = new \stdClass;

			$preset->name  = $name;
			$preset->title = $title;
			$preset->path  = $path;

			static::$presets[$name] = $preset;
		}
	}

	/**
	 * Get a list of available presets.
	 *
	 * @return  \stdClass[]
	 *
	 * @since   4.0.0
	 */
	public static function getPresets()
	{
		if (static::$presets === null)
		{
			// Important: 'null' will cause infinite recursion.
			static::$presets = array();

			static::addPreset('joomla', 'JLIB_MENUS_PRESET_JOOMLA', JPATH_ADMINISTRATOR . '/components/com_menus/presets/joomla.xml');
			static::addPreset('modern', 'JLIB_MENUS_PRESET_MODERN', JPATH_ADMINISTRATOR . '/components/com_menus/presets/modern.xml');

			// Load from template folder automatically
			$app = Factory::getApplication();
			$tpl = JPATH_THEMES . '/' . $app->getTemplate() . '/html/com_menus/presets';

			if (is_dir($tpl))
			{
				$files = Folder::files($tpl, '\.xml$');

				foreach ($files as $file)
				{
					$name  = substr($file, 0, -4);
					$title = str_replace('-', ' ', $name);

					static::addPreset(strtolower($name), ucwords($title), $tpl . '/' . $file);
				}
			}
		}

		return static::$presets;
	}

	/**
	 * Load the menu items from a preset file into a hierarchical list of objects
	 *
	 * @param   string    $name      The preset name
	 * @param   bool      $fallback  Fallback to default (joomla) preset if the specified one could not be loaded?
	 * @param   MenuItem  $parent    Root node of the menu
	 *
	 * @return  MenuItem
	 *
	 * @since   4.0.0
	 */
	public static function loadPreset($name, $fallback = true, $parent = null)
	{
		$presets = static::getPresets();

		if (!$parent)
		{
			$parent = new MenuItem;
		}

		if (isset($presets[$name]) && ($xml = simplexml_load_file($presets[$name]->path, null, LIBXML_NOCDATA)) && $xml instanceof \SimpleXMLElement)
		{
			static::loadXml($xml, $parent);
		}
		elseif ($fallback && isset($presets['joomla']))
		{
			if (($xml = simplexml_load_file($presets['joomla']->path, null, LIBXML_NOCDATA)) && $xml instanceof \SimpleXMLElement)
			{
				static::loadXml($xml, $parent);
			}
		}

		return $parent;
	}

	/**
	 * Method to resolve the menu item alias type menu item
	 *
	 * @param   \stdClass  &$item  The alias object
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public static function resolveAlias(&$item)
	{
		$obj = $item;

		while ($obj->type == 'alias')
		{
			$params  = new Registry($obj->params);
			$aliasTo = $params->get('aliasoptions');

			$db = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('a.id, a.link, a.type, e.element')
				->from('#__menu a')
				->where('a.id = ' . (int) $aliasTo)
				->join('left', '#__extensions e ON e.id = a.component_id = e.id');

			try
			{
				$obj = $db->setQuery($query)->loadObject();

				if (!$obj)
				{
					$item->link = '';

					return;
				}
			}
			catch (\Exception $e)
			{
				$item->link = '';

				return;
			}
		}

		$item->id      = $obj->id;
		$item->link    = $obj->link;
		$item->type    = $obj->type;
		$item->element = $obj->element;
	}

	/**
	 * Parse the flat list of menu items and prepare the hierarchy of them using parent-child relationship.
	 *
	 * @param   MenuItem  $item  Menu item to preprocess
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public static function preprocess($item)
	{
		// Resolve the alias item to get the original item
		if ($item->type == 'alias')
		{
			static::resolveAlias($item);
		}

		if ($item->link = in_array($item->type, array('separator', 'heading', 'container')) ? '#' : trim($item->link))
		{
			$item->submenu    = array();
			$item->class      = $item->img ?? '';
			$item->scope      = $item->scope ?? null;
			$item->browserNav = $item->browserNav ? '_blank' : '';
		}
	}

	/**
	 * Load a menu tree from an XML file
	 *
	 * @param   \SimpleXMLElement[]  $elements  The xml menuitem nodes
	 * @param   MenuItem             $parent    The menu hierarchy list to be populated
	 * @param   string[]             $replace   The substring replacements for iterator type items
	 *
	 * @return  void
	 *
	 * @since  4.0.0
	 */
	protected static function loadXml($elements, $parent, $replace = array())
	{
		foreach ($elements as $element)
		{
			if ($element->getName() != 'menuitem')
			{
				continue;
			}

			$select = (string) $element['sql_select'];
			$from   = (string) $element['sql_from'];

			/**
			 * Following is a repeatable group based on simple database query. This requires sql_* attributes (sql_select and sql_from are required)
			 * The values can be used like - "{sql:columnName}" in any attribute of repeated elements.
			 * The repeated elements are place inside this xml node but they will be populated in the same level in the rendered menu
			 */
			if ($select && $from)
			{
				$hidden = $element['hidden'] == 'true';
				$where  = (string) $element['sql_where'];
				$order  = (string) $element['sql_order'];
				$group  = (string) $element['sql_group'];
				$lJoin  = (string) $element['sql_leftjoin'];
				$iJoin  = (string) $element['sql_innerjoin'];

				$db    = Factory::getDbo();
				$query = $db->getQuery(true);
				$query->select($select)->from($from);

				if ($where)
				{
					$query->where($where);
				}

				if ($order)
				{
					$query->order($order);
				}

				if ($group)
				{
					$query->group($group);
				}

				if ($lJoin)
				{
					$query->leftJoin($lJoin);
				}

				if ($iJoin)
				{
					$query->innerJoin($iJoin);
				}

				$results = $db->setQuery($query)->loadObjectList();

				// Skip the entire group if no items to iterate over.
				if ($results)
				{
					// Show the repeatable group heading node only if not set as hidden.
					if (!$hidden)
					{
						$child = static::parseXmlNode($element, $replace);
						$parent->addChild($child);
					}

					// Iterate over the matching records, items goes in the same level (not $item->submenu) as this node.
					foreach ($results as $result)
					{
						static::loadXml($element->menuitem, $parent, $result);
					}
				}
			}
			else
			{
				$item = static::parseXmlNode($element, $replace);

				// Process the child nodes
				static::loadXml($element->menuitem, $item, $replace);

				$parent->addChild($item);
			}
		}
	}

	/**
	 * Create a menu item node from an xml element
	 *
	 * @param   \SimpleXMLElement  $node     A menuitem element from preset xml
	 * @param   string[]           $replace  The values to substitute in the title, link and element texts
	 *
	 * @return  \stdClass
	 *
	 * @since   4.0.0
	 */
	protected static function parseXmlNode($node, $replace = array())
	{
		$item = new MenuItem;

		$item->id         = null;
		$item->type       = (string) $node['type'];
		$item->title      = (string) $node['title'];
		$item->link       = (string) $node['link'];
		$item->target     = (string) $node['target'];
		$item->element    = (string) $node['element'];
		$item->class      = (string) $node['class'];
		$item->icon       = (string) $node['icon'];
		$item->browserNav = (string) $node['target'];
		$item->access     = (int) $node['access'];
		$item->scope      = (string) $node['scope'] ?: 'default';
		$item->setParams(new Registry(trim($node->params)));
		$item->getParams()->set('menu-permission', (string) $node['permission']);

		if ($item->type == 'separator' && trim($item->title, '- '))
		{
			$item->getParams()->set('text_separator', 1);
		}

		if ($item->type == 'heading' || $item->type == 'container')
		{
			$item->link = '#';
		}

		if ((string) $node['quicktask'])
		{
			$item->getParams()->set('menu-quicktask', (string) $node['quicktask']);
			$item->getParams()->set('menu-quicktask-title', (string) $node['quicktask-title']);
			$item->getParams()->set('menu-quicktask-icon', (string) $node['quicktask-icon']);
			$item->getParams()->set('menu-quicktask-permission', (string) $node['quicktask-permission']);
		}

		// Translate attributes for iterator values
		foreach ($replace as $var => $val)
		{
			$item->title   = str_replace("{sql:$var}", $val, $item->title);
			$item->element = str_replace("{sql:$var}", $val, $item->element);
			$item->link    = str_replace("{sql:$var}", $val, $item->link);
			$item->class   = str_replace("{sql:$var}", $val, $item->class);
			$item->icon    = str_replace("{sql:$var}", $val, $item->icon);
		}

		return $item;
	}
}
