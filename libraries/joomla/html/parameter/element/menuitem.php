<?php
/**
 * @package     Joomla.Platform
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Renders a menu item element
 *
 * @package		Joomla.Platform
 * @subpackage	Parameter
 * @since		11.1
 */

class JElementMenuItem extends JElement
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	protected $_name = 'MenuItem';

	public function fetchElement($name, $value, &$node, $control_name)
	{
		$db = JFactory::getDbo();

		$menuType = $this->_parent->get('menu_type');
		if (!empty($menuType)) {
			$where = ' WHERE menutype = '.$db->Quote($menuType);
		} else {
			$where = ' WHERE 1';
		}

		// Load the list of menu types
		// TODO: move query to model
		$query = 'SELECT menutype, title' .
				' FROM #__menu_types' .
				' ORDER BY title';
		$db->setQuery($query);
		$menuTypes = $db->loadObjectList();

		if ($state = $node->attributes('state')) {
			$where .= ' AND published = '.(int) $state;
		}

		// load the list of menu items
		// TODO: move query to model
		$query = 'SELECT id, parent_id, name, menutype, type' .
				' FROM #__menu' .
				$where .
				' ORDER BY menutype, parent_id, ordering'
				;

		$db->setQuery($query);
		$menuItems = $db->loadObjectList();

		// Establish the hierarchy of the menu
		// TODO: use node model
		$children = array();

		if ($menuItems)
		{
			// First pass - collect children
			foreach ($menuItems as $v)
			{
				$pt	= $v->parent_id;
				$list	= @$children[$pt] ? $children[$pt] : array();
				array_push($list, $v);
				$children[$pt] = $list;
			}
		}

		// Second pass - get an indent list of the items
		$list = JHtml::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0);

		// Assemble into menutype groups
		$n = count($list);
		$groupedList = array();
		foreach ($list as $k => $v) {
			$groupedList[$v->menutype][] = &$list[$k];
		}

		// Assemble menu items to the array
		$options	= array();
		$options[]	= JHtml::_('select.option', '', JText::_('JOPTION_SELECT_MENU_ITEM'));

		foreach ($menuTypes as $type)
		{
			if ($menuType == '')
			{
				$options[]	= JHtml::_('select.option',  '0', '&#160;', 'value', 'text', true);
				$options[]	= JHtml::_('select.option',  $type->menutype, $type->title . ' - ' . JText::_('JGLOBAL_TOP'), 'value', 'text', true);
			}
			if (isset($groupedList[$type->menutype]))
			{
				$n = count($groupedList[$type->menutype]);
				for ($i = 0; $i < $n; $i++)
				{
					$item = &$groupedList[$type->menutype][$i];

					// If menutype is changed but item is not saved yet, use the new type in the list
					if (JRequest::getString('option', '', 'get') == 'com_menus') {
						$currentItemArray = JRequest::getVar('cid', array(0), '', 'array');
						$currentItemId = (int) $currentItemArray[0];
						$currentItemType = JRequest::getString('type', $item->type, 'get');
						if ($currentItemId == $item->id && $currentItemType != $item->type) {
							$item->type = $currentItemType;
						}
					}

					$disable = strpos($node->attributes('disable'), $item->type) !== false ? true : false;
					$options[] = JHtml::_('select.option',  $item->id, '&#160;&#160;&#160;' .$item->treename, 'value', 'text', $disable);

				}
			}
		}

		return JHtml::_('select.genericlist', $options, $control_name.'['.$name.']',
			array(
				'id' => $control_name.$name,
				'list.attr' => 'class="inputbox"',
				'list.select' => $value
			)
		);
	}
}
