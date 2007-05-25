<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Menus
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights
 * reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

/**
 * @package		Joomla
 * @subpackage	Menus
 * @author Andrew Eddie
 */
class JMenuHelper
{
	/**
	 * Get a list of the menu_types records
	 * @return array An array of records as objects
	 */
	function getMenuTypeList()
	{
		$db = &JFactory::getDBO();
		$query = 'SELECT *' .
				' FROM #__menu_types';
		$db->setQuery( $query );
		return $db->loadObjectList();
	}

	/**
	 * Get a list of the menutypes
	 * @return array An array of menu type names
	 */
	function getMenuTypes()
	{
		$db = &JFactory::getDBO();
		$query = 'SELECT menutype' .
				' FROM #__menu_types';
		$db->setQuery( $query );
		return $db->loadResultArray();
	}

	/**
	 * Gets a list of components that can link to the menu
	 */
	function getComponentList()
	{
		$db = &JFactory::getDBO();
		$query = 'SELECT c.id, c.name, c.link, c.option' .
				' FROM #__components AS c' .
				' WHERE c.link <> "" AND parent = 0' .
				' ORDER BY c.name';
		$db->setQuery( $query );
		$result = $db->loadObjectList( );
		return $result;
	}

	/**
	 * Build the select list for parent menu item
	 */
	function Parent( &$row )
	{
		$db =& JFactory::getDBO();

		// If a not a new item, lets set the menu item id
		if ( $row->id ) {
			$id = ' AND id != '.$row->id;
		} else {
			$id = null;
		}

		// In case the parent was null
		if (!$row->parent) {
			$row->parent = 0;
		}

		// get a list of the menu items
		// excluding the current menu item and its child elements
		$query = 'SELECT m.*' .
				' FROM #__menu m' .
				' WHERE menutype = "'.$row->menutype.'"' .
				' AND published != -2' .
				$id .
				' ORDER BY parent, ordering';
		$db->setQuery( $query );
		$mitems = $db->loadObjectList();

		// establish the hierarchy of the menu
		$children = array();

		if ( $mitems )
		{
			// first pass - collect children
			foreach ( $mitems as $v )
			{
				$pt 	= $v->parent;
				$list 	= @$children[$pt] ? $children[$pt] : array();
				array_push( $list, $v );
				$children[$pt] = $list;
			}
		}

		// second pass - get an indent list of the items
		$list = JHTML::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0 );

		// assemble menu items to the array
		$mitems 	= array();
		$mitems[] 	= JHTML::_('select.option',  '0', JText::_( 'Top' ) );

		foreach ( $list as $item ) {
			$mitems[] = JHTML::_('select.option',  $item->id, '&nbsp;&nbsp;&nbsp;'. $item->treename );
		}

		$output = JHTML::_('select.genericlist',   $mitems, 'parent', 'class="inputbox" size="10"', 'value', 'text', $row->parent );

		return $output;
	}

	/**
	* build the select list for target window
	*/
	function Target( &$row )
	{
		$click[] = JHTML::_('select.option',  '0', JText::_( 'Parent Window With Browser Navigation' ) );
		$click[] = JHTML::_('select.option',  '1', JText::_( 'New Window With Browser Navigation' ) );
		$click[] = JHTML::_('select.option',  '2', JText::_( 'New Window Without Browser Navigation' ) );
		$target = JHTML::_('select.genericlist',   $click, 'browserNav', 'class="inputbox" size="4"', 'value', 'text', intval( $row->browserNav ) );

		return $target;
	}

	/**
	* build the select list for target window
	*/
	function Published( &$row )
	{
		$put[] = JHTML::_('select.option',  '0', JText::_( 'No' ));
		$put[] = JHTML::_('select.option',  '1', JText::_( 'Yes' ));

		// If not a new item, trash is not an option
		if ( $row->id ) {
			$put[] = JHTML::_('select.option',  '-1', JText::_( 'Trash' ));
		} else {
			$row->published = 1;
		}
		$published = JHTML::_('select.radiolist',  $put, 'published', '', 'value', 'text', $row->published );
		return $published;
	}
}
?>
