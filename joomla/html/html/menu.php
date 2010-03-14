<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage		HTML
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * Utility class working with menu select lists
 *
 * @static
 * @package		Joomla.Framework
 * @subpackage	HTML
 * @since		1.5
 */
abstract class JHtmlMenu
{
	/**
	 * @var	array	Cached array of the menus.
	 */
	protected static $menus = null;

	/**
	 * @var	array	Cached array of the menus items.
	 */
	protected static $items = null;

	/**
	 * Get a list of the available menus.
	 *
	 * @return	string
	 * @since	1.6
	 */
	public static function menus()
	{
		if (empty(self::$menus))
		{
			$db = &JFactory::getDbo();
			$db->setQuery(
				'SELECT menutype As value, title As text' .
				' FROM #__menu_types' .
				' ORDER BY title'
			);
			self::$menus = $db->loadObjectList();
		}

		return self::$menus;
	}

	/**
	 * Returns an array of menu items groups by menu.
	 *
	 * @param	array	An array of configuration options.
	 *
	 * @return	array
	 */
	public static function menuitems($config = array())
	{
		if (empty(self::$items))
		{
			$db = JFactory::getDbo();
			$db->setQuery(
				'SELECT menutype As value, title As text' .
				' FROM #__menu_types' .
				' ORDER BY title'
			);
			$menus = $db->loadObjectList();

			$query	= $db->getQuery(true);
			$query->select('a.id AS value, a.title As text, a.level, a.menutype');
			$query->from('#__menu AS a');
			$query->where('a.parent_id > 0');
			$query->where('a.type <> '.$db->quote('url'));

			// Filter on the published state
			if (isset($config['published'])) {
				$query->where('a.published = '.(int) $config['published']);
			}

			$query->order('a.lft');

			$db->setQuery($query);
			$items = $db->loadObjectList();

			// Collate menu items based on menutype
			$lookup = array();
			foreach ($items as &$item) {
				if (!isset($lookup[$item->menutype])) {
					$lookup[$item->menutype] = array();
				}
				$lookup[$item->menutype][] = &$item;

				$item->text = str_repeat('- ',$item->level).$item->text;
			}
			self::$items = array();

			foreach ($menus as &$menu) {
				self::$items[] = JHtml::_('select.optgroup',	$menu->text);
				self::$items[] = JHtml::_('select.option', $menu->value.'.0', JText::_('COM_MENUS_ADD_TO_THIS_MENU'));

				if (isset($lookup[$menu->value])) {
					foreach ($lookup[$menu->value] as &$item) {
						self::$items[] = JHtml::_('select.option', $menu->value.'.'.$item->value, $item->text);
					}
				}
			}
		}

		return self::$items;
	}

	/**
	 * Displays an HTML select list of menu items.
	 *
	 * @param	string	The name of the control.
	 * @param	string	The value of the selected option.
	 * @param	string	Attributes for the control.
	 * @param	array	An array of options for the control.
	 *
	 * @return	string
	 */
	public static function menuitemlist($name, $selected = null, $attribs = null, $config = array())
	{
		static $count;

		$options = self::menuitems($config);

		return JHtml::_(
			'select.genericlist',
			$options,
			$name,
			array(
				'id' =>				isset($config['id']) ? $config['id'] : 'assetgroups_'.++$count,
				'list.attr' =>		(is_null($attribs) ? 'class="inputbox" size="1"' : $attribs),
				'list.select' =>	(int) $selected,
				'list.translate' => false
			)
		);
	}


	/**
	 * Build the select list for Menu Ordering
	 */
	public static function ordering(&$row, $id)
	{
		$db = &JFactory::getDbo();

		if ($id)
		{
			$query = 'SELECT ordering AS value, title AS text'
			. ' FROM #__menu'
			. ' WHERE menutype = '.$db->Quote($row->menutype)
			. ' AND parent_id = '.(int) $row->parent_id
			. ' AND published != -2'
			. ' ORDER BY ordering';
			$order = JHtml::_('list.genericordering',  $query);
			$ordering = JHtml::_(
				'select.genericlist',
				$order,
				'ordering',
				array('list.attr' => 'class="inputbox" size="1"', 'list.select' => intval($row->ordering))
			);
		}
		else
		{
			$ordering = '<input type="hidden" name="ordering" value="'. $row->ordering .'" />'. JText::_('JCOMMON_NEWITEMSLAST_DESC');
		}
		return $ordering;
	}

	/**
	 * Build the multiple select list for Menu Links/Pages
	 */
	public static function linkoptions($all=false, $unassigned=false)
	{
		$db = &JFactory::getDbo();

		// get a list of the menu items
		$query = 'SELECT m.id, m.parent_id, m.title, m.menutype'
		. ' FROM #__menu AS m'
		. ' WHERE m.published = 1'
		. ' ORDER BY m.menutype, m.parent_id, m.ordering'
		;
		$db->setQuery($query);

		$mitems = $db->loadObjectList();

		// Check for a database error.
		if ($db->getErrorNum()) {
			JError::raiseNotice(500, $db->getErrorMsg());
		}

		if (!$mitems) {
			$mitems = array();
		}

		$mitems_temp = $mitems;

		// establish the hierarchy of the menu
		$children = array();
		// first pass - collect children
		foreach ($mitems as $v)
		{
			$id = $v->id;
			$pt = $v->parent_id;
			$list = @$children[$pt] ? $children[$pt] : array();
			array_push($list, $v);
			$children[$pt] = $list;
		}
		// second pass - get an indent list of the items
		$list = JHtmlMenu::TreeRecurse(intval($mitems[0]->parent_id), '', array(), $children, 9999, 0, 0);

		// Code that adds menu name to Display of Page(s)
		$mitems_spacer	= $mitems_temp[0]->menutype;

		$mitems = array();
		if ($all | $unassigned) {
			$mitems[] = JHtml::_('select.option',  '<OPTGROUP>', JText::_('COM_MENUS_OPTION_MENU'));

			if ($all) {
				$mitems[] = JHtml::_('select.option',  0, JText::_('JOPTION_ALL'));
			}
			if ($unassigned) {
				$mitems[] = JHtml::_('select.option',  -1, JText::_('JOPTION_UNASSIGNED'));
			}

			$mitems[] = JHtml::_('select.option',  '</OPTGROUP>');
		}

		$lastMenuType	= null;
		$tmpMenuType	= null;
		foreach ($list as $list_a)
		{
			if ($list_a->menutype != $lastMenuType)
			{
				if ($tmpMenuType) {
					$mitems[] = JHtml::_('select.option',  '</OPTGROUP>');
				}
				$mitems[] = JHtml::_('select.option',  '<OPTGROUP>', $list_a->menutype);
				$lastMenuType = $list_a->menutype;
				$tmpMenuType  = $list_a->menutype;
			}

			$mitems[] = JHtml::_('select.option',  $list_a->id, $list_a->treename);
		}
		if ($lastMenuType !== null) {
			$mitems[] = JHtml::_('select.option',  '</OPTGROUP>');
		}

		return $mitems;
	}

	public static function treerecurse($id, $indent, $list, &$children, $maxlevel=9999, $level=0, $type=1)
	{
		if (@$children[$id] && $level <= $maxlevel)
		{
			foreach ($children[$id] as $v)
			{
				$id = $v->id;

				if ($type) {
					$pre	= '<sup>|_</sup>&nbsp;';
					$spacer = '.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				} else {
					$pre	= '- ';
					$spacer = '&nbsp;&nbsp;';
				}

				if ($v->parent_id == 0) {
					$txt	= $v->title;
				} else {
					$txt	= $pre . $v->title;
				}
				$pt = $v->parent_id;
				$list[$id] = $v;
				$list[$id]->treename = "$indent$txt";
				$list[$id]->children = count(@$children[$id]);
				$list = JHtmlMenu::TreeRecurse($id, $indent . $spacer, $list, $children, $maxlevel, $level+1, $type);
			}
		}
		return $list;
	}
}