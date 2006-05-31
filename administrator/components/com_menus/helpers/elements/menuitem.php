<?php
/**
* @version $Id: menu.php 3689 2006-05-27 04:54:39Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Renders a menu item element
 *
 * @author 		Louis Landry <louis.landry@joomla.org>
 * @package 	Menus
 * @subpackage 	Parameter
 * @since		1.5
 */

class JElement_MenuItem extends JElement
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
			$where = "\n WHERE menutype = '$menuType'";
		} else {
			$where = "\n WHERE 1";
		}

		$query = "SELECT id, name" .
				"\n FROM #__menu" .
				$where;

		$db->setQuery($query);
		$menuItems = $db->loadObjectList();

		$numItems = count($menuItems);
		for ($i=0;$i<$numItems;$i++) {
			$options[] = mosHTML::makeOption($menuItems[$i]->id, $menuItems[$i]->name);
		}
		array_unshift($options, mosHTML::makeOption('', '- '.JText::_('Select Item').' -'));

		return mosHTML::selectList($options, ''.$control_name.'['.$name.']', 'class="inputbox"', 'value', 'text', $value, $control_name.$name);
	}
}
?>