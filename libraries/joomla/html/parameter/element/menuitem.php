<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Parameter
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Renders a menu item element
 *
 * @author 		Louis Landry <louis.landry@joomla.org>
 * @package 	Joomla.Framework
 * @subpackage		Parameter
 * @since		1.5
 */

class JElementMenuItem extends JElement
{
   /**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'MenuItem';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$db =& JFactory::getDBO();

		$menuType = $this->_parent->get('menu_type');
		if (!empty($menuType)) {
			$where = ' WHERE menutype = "'.$menuType."'";
		} else {
			$where = ' WHERE 1';
		}

		// load the list of menu types
		// TODO: move query to model
		$query = 'SELECT menutype, title' .
				' FROM #__menu_types' .
				' ORDER BY title';
		$db->setQuery( $query );
		$menuTypes = $db->loadObjectList();

		if ($state = $node->attributes('state')) {
			$where .= ' AND published = "'.$state.'"';
		}

		// load the list of menu items
		// TODO: move query to model
		$query = 'SELECT id, parent, name, menutype' .
				' FROM #__menu' .
				$where .
				' ORDER BY menutype, parent, ordering'
				;

		$db->setQuery($query);
		$menuItems = $db->loadObjectList();

		// establish the hierarchy of the menu
		// TODO: use node model
		$children = array();

		if ($menuItems) {
			// first pass - collect children
			foreach ($menuItems as $v) {
				$pt 	= $v->parent;
				$list 	= @$children[$pt] ? $children[$pt] : array();
				array_push( $list, $v );
				$children[$pt] = $list;
			}
		}

		// second pass - get an indent list of the items
		$list = mosTreeRecurse( 0, '', array(), $children, 9999, 0, 0 );

		// assemble into menutype groups
		$n = count( $list );
		$groupedList = array();
		foreach ($list as $k => $v)
		{
			$groupedList[$v->menutype][] = &$list[$k];
		}

		// assemble menu items to the array
		$options 	= array();
		$options[]	= JHTML::_('select.option', '', '- '.JText::_('Select Item').' -');

		foreach ($menuTypes as $type)
		{
			if ($menuType == '')
			{
				$options[]	= JHTML::_('select.option',  '0', '&nbsp;' );
				$options[]	= JHTML::_('select.option',  $type->menutype, $type->title . ' - ' . JText::_( 'Top' ) );
			}
			if (isset( $groupedList[$type->menutype] ))
			{
				$n = count( $groupedList[$type->menutype] );
				for ($i = 0; $i < $n; $i++)
				{
					$item = &$groupedList[$type->menutype][$i];
					$options[] = JHTML::_('select.option',  $item->id, '&nbsp;&nbsp;&nbsp;' .$item->treename );

				}
			}
		}

		return JHTML::_('select.genericlist',  $options, ''.$control_name.'['.$name.']', 'class="inputbox"', 'value', 'text', $value, $control_name.$name);
	}
}