<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Utility class working with menu select lists
 *
 * @since  1.5
 */
abstract class JHtmlMenu
{
	/**
	 * Cached array of the menus.
	 *
	 * @var    array
	 * @since  1.6
	 */
	protected static $menus = array();

	/**
	 * Cached array of the menus items.
	 *
	 * @var    array
	 * @since  1.6
	 */
	protected static $items = array();

	/**
	 * Get a list of the available menus.
	 *
	 * @param   int  $clientId  The client id
	 *
	 * @return  array
	 *
	 * @since   1.6
	 */
	public static function menus($clientId = 0)
	{
		$key = serialize($clientId);

		if (!isset(static::$menus[$key]))
		{
			$db = JFactory::getDbo();

			$query = $db->getQuery(true)
				->select($db->qn(array('id', 'menutype', 'title', 'client_id'), array('id', 'value', 'text', 'client_id')))
				->from($db->quoteName('#__menu_types'))
				->order('client_id, title');

			if (isset($clientId))
			{
				$query->where('client_id = ' . (int) $clientId);
			}

			static::$menus[$key] = $db->setQuery($query)->loadObjectList();
		}

		return static::$menus[$key];
	}

	/**
	 * Returns an array of menu items grouped by menu.
	 *
	 * @param   array  $config  An array of configuration options [published, checkacl, clientid].
	 *
	 * @return  array
	 *
	 * @since   1.6
	 */
	public static function menuItems($config = array())
	{
		$key = serialize($config);

		if (empty(static::$items[$key]))
		{
			$menus = static::menus();

			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('a.id AS value, a.title AS text, a.level, a.menutype, a.client_id')
				->from('#__menu AS a')
				->where('a.parent_id > 0');

			// Filter on the client id
			if (isset($config['clientid']))
			{
				$query->where('a.client_id = ' . (int) $config['clientid']);
			}

			// Filter on the published state
			if (isset($config['published']))
			{
				if (is_numeric($config['published']))
				{
					$query->where('a.published = ' . (int) $config['published']);
				}
				elseif ($config['published'] === '')
				{
					$query->where('a.published IN (0,1)');
				}
			}

			$query->order('a.lft');

			$db->setQuery($query);
			$items = $db->loadObjectList();

			// Collate menu items based on menutype
			$lookup = array();

			foreach ($items as &$item)
			{
				if (!isset($lookup[$item->menutype]))
				{
					$lookup[$item->menutype] = array();
				}

				$lookup[$item->menutype][] = &$item;

				$item->text = str_repeat('- ', $item->level) . $item->text;
			}

			static::$items[$key] = array();

			$user = JFactory::getUser();

			$aclcheck = !empty($config['checkacl']) ? (int) $config['checkacl'] : 0;

			foreach ($menus as &$menu)
			{
				if ($aclcheck)
				{
					$action = $aclcheck == $menu->id ? 'edit' : 'create';

					if (!$user->authorise('core.' . $action, 'com_menus.menu.' . $menu->id))
					{
						continue;
					}
				}

				// Start group:
				static::$items[$key][] = JHtml::_('select.optgroup', $menu->text);

				// Special "Add to this Menu" option:
				static::$items[$key][] = JHtml::_('select.option', $menu->value . '.1', JText::_('JLIB_HTML_ADD_TO_THIS_MENU'));

				// Menu items:
				if (isset($lookup[$menu->value]))
				{
					foreach ($lookup[$menu->value] as &$item)
					{
						static::$items[$key][] = JHtml::_('select.option', $menu->value . '.' . $item->value, $item->text);
					}
				}

				// Finish group:
				static::$items[$key][] = JHtml::_('select.optgroup', $menu->text);
			}
		}

		return static::$items[$key];
	}

	/**
	 * Displays an HTML select list of menu items.
	 *
	 * @param   string  $name      The name of the control.
	 * @param   string  $selected  The value of the selected option.
	 * @param   string  $attribs   Attributes for the control.
	 * @param   array   $config    An array of options for the control [id, published, checkacl, clientid].
	 *
	 * @return  string
	 *
	 * @since   1.6
	 */
	public static function menuItemList($name, $selected = null, $attribs = null, $config = array())
	{
		static $count;

		$options = static::menuItems($config);

		return JHtml::_(
			'select.genericlist', $options, $name,
			array(
				'id' => isset($config['id']) ? $config['id'] : 'assetgroups_' . (++$count),
				'list.attr' => (is_null($attribs) ? 'class="inputbox" size="1"' : $attribs),
				'list.select' => (int) $selected,
				'list.translate' => false,
			)
		);
	}

	/**
	 * Build the select list for Menu Ordering
	 *
	 * @param   object   &$row  The row object
	 * @param   integer  $id    The id for the row. Must exist to enable menu ordering
	 *
	 * @return  string
	 *
	 * @since   1.5
	 */
	public static function ordering(&$row, $id)
	{
		if ($id)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('ordering AS value, title AS text')
				->from($db->quoteName('#__menu'))
				->where($db->quoteName('menutype') . ' = ' . $db->quote($row->menutype))
				->where($db->quoteName('parent_id') . ' = ' . (int) $row->parent_id)
				->where($db->quoteName('published') . ' != -2')
				->order('ordering');
			$order = JHtml::_('list.genericordering', $query);
			$ordering = JHtml::_(
				'select.genericlist', $order, 'ordering',
				array('list.attr' => 'class="inputbox" size="1"', 'list.select' => (int) $row->ordering)
			);
		}
		else
		{
			$ordering = '<input type="hidden" name="ordering" value="' . $row->ordering . '" />' . JText::_('JGLOBAL_NEWITEMSLAST_DESC');
		}

		return $ordering;
	}

	/**
	 * Build the multiple select list for Menu Links/Pages
	 *
	 * @param   boolean  $all         True if all can be selected
	 * @param   boolean  $unassigned  True if unassigned can be selected
	 * @param   int      $clientId    The client id
	 *
	 * @return  string
	 *
	 * @since   1.5
	 */
	public static function linkOptions($all = false, $unassigned = false, $clientId = 0)
	{
		$db = JFactory::getDbo();

		// Get a list of the menu items
		$query = $db->getQuery(true)
			->select('m.id, m.parent_id, m.title, m.menutype, m.client_id')
			->from($db->quoteName('#__menu') . ' AS m')
			->where($db->quoteName('m.published') . ' = 1')
			->order('m.client_id, m.menutype, m.parent_id');

		if (isset($clientId))
		{
			$query->where('m.client_id = ' . (int) $clientId);
		}

		$db->setQuery($query);

		$mitems = $db->loadObjectList();

		if (!$mitems)
		{
			$mitems = array();
		}

		// Establish the hierarchy of the menu
		$children = array();

		// First pass - collect children
		foreach ($mitems as $v)
		{
			$pt            = $v->parent_id;
			$list          = @$children[$pt] ? $children[$pt] : array();
			$list[]        = $v;
			$children[$pt] = $list;
		}

		// Second pass - get an indent list of the items
		$list = static::treerecurse((int) $mitems[0]->parent_id, '', array(), $children, 9999, 0, 0);

		// Code that adds menu name to Display of Page(s)
		$mitems = array();

		if ($all | $unassigned)
		{
			$mitems[] = JHtml::_('select.option', '<OPTGROUP>', JText::_('JOPTION_MENUS'));

			if ($all)
			{
				$mitems[] = JHtml::_('select.option', 0, JText::_('JALL'));
			}

			if ($unassigned)
			{
				$mitems[] = JHtml::_('select.option', -1, JText::_('JOPTION_UNASSIGNED'));
			}

			$mitems[] = JHtml::_('select.option', '</OPTGROUP>');
		}

		$lastMenuType = null;
		$tmpMenuType = null;

		foreach ($list as $list_a)
		{
			if ($list_a->menutype != $lastMenuType)
			{
				if ($tmpMenuType)
				{
					$mitems[] = JHtml::_('select.option', '</OPTGROUP>');
				}

				$mitems[] = JHtml::_('select.option', '<OPTGROUP>', $list_a->menutype);
				$lastMenuType = $list_a->menutype;
				$tmpMenuType = $list_a->menutype;
			}

			$mitems[] = JHtml::_('select.option', $list_a->id, $list_a->title);
		}

		if ($lastMenuType !== null)
		{
			$mitems[] = JHtml::_('select.option', '</OPTGROUP>');
		}

		return $mitems;
	}

	/**
	 * Build the list representing the menu tree
	 *
	 * @param   integer  $id         Id of the menu item
	 * @param   string   $indent     The indentation string
	 * @param   array    $list       The list to process
	 * @param   array    &$children  The children of the current item
	 * @param   integer  $maxlevel   The maximum number of levels in the tree
	 * @param   integer  $level      The starting level
	 * @param   int      $type       Set the type of spacer to use. Use 1 for |_ or 0 for -
	 *
	 * @return  array
	 *
	 * @since   1.5
	 */
	public static function treerecurse($id, $indent, $list, &$children, $maxlevel = 9999, $level = 0, $type = 1)
	{
		if (@$children[$id] && $level <= $maxlevel)
		{
			foreach ($children[$id] as $v)
			{
				$id = $v->id;

				if ($type)
				{
					$pre = '<sup>|_</sup>&#160;';
					$spacer = '.&#160;&#160;&#160;&#160;&#160;&#160;';
				}
				else
				{
					$pre = '- ';
					$spacer = '&#160;&#160;';
				}

				if ($v->parent_id == 0)
				{
					$txt = $v->title;
				}
				else
				{
					$txt = $pre . $v->title;
				}

				$list[$id] = $v;
				$list[$id]->treename = $indent . $txt;
				$list[$id]->children = count(@$children[$id]);
				$list = static::treerecurse($id, $indent . $spacer, $list, $children, $maxlevel, $level + 1, $type);
			}
		}

		return $list;
	}
}
