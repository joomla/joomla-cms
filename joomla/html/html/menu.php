<?php
/**
 * @version		$Id: menu.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla.Framework
 * @subpackage		HTML
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

/**
 * Utility class working with menu select lists
 *
 * @static
 * @package 	Joomla.Framework
 * @subpackage	HTML
 * @since		1.5
 */
abstract class JHtmlMenu
{
	/**
	* Build the select list for Menu Ordering
	*/
	public static function ordering(&$row, $id)
	{
		$db = &JFactory::getDbo();

		if ($id)
		{
			$query = 'SELECT ordering AS value, name AS text'
			. ' FROM #__menu'
			. ' WHERE menutype = '.$db->Quote($row->menutype)
			. ' AND parent = '.(int) $row->parent
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
			$ordering = '<input type="hidden" name="ordering" value="'. $row->ordering .'" />'. JText::_('DESCNEWITEMSLAST');
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
		$query = 'SELECT m.id, m.parent, m.name, m.menutype'
		. ' FROM #__menu AS m'
		. ' WHERE m.published = 1'
		. ' ORDER BY m.menutype, m.parent, m.ordering'
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
			$pt = $v->parent;
			$list = @$children[$pt] ? $children[$pt] : array();
			array_push($list, $v);
			$children[$pt] = $list;
		}
		// second pass - get an indent list of the items
		$list = JHtmlMenu::TreeRecurse(intval($mitems[0]->parent), '', array(), $children, 9999, 0, 0);

		// Code that adds menu name to Display of Page(s)
		$mitems_spacer 	= $mitems_temp[0]->menutype;

		$mitems = array();
		if ($all | $unassigned) {
			$mitems[] = JHtml::_('select.option',  '<OPTGROUP>', JText::_('Menus'));

			if ($all) {
				$mitems[] = JHtml::_('select.option',  0, JText::_('All'));
			}
			if ($unassigned) {
				$mitems[] = JHtml::_('select.option',  -1, JText::_('Unassigned'));
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
					$pre 	= '<sup>|_</sup>&nbsp;';
					$spacer = '.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				} else {
					$pre 	= '- ';
					$spacer = '&nbsp;&nbsp;';
				}

				if ($v->parent == 0) {
					$txt 	= $v->name;
				} else {
					$txt 	= $pre . $v->name;
				}
				$pt = $v->parent;
				$list[$id] = $v;
				$list[$id]->treename = "$indent$txt";
				$list[$id]->children = count(@$children[$id]);
				$list = JHtmlMenu::TreeRecurse($id, $indent . $spacer, $list, $children, $maxlevel, $level+1, $type);
			}
		}
		return $list;
	}
}