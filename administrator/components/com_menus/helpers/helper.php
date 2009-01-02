<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Menus
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

/**
 * @package		Joomla
 * @subpackage	Menus
 */
class MenusHelper
{
	/**
	 * Get a list of the menu_types records
	 * @return array An array of records as objects
	 */
	function getMenuTypeList()
	{
		$db = &JFactory::getDBO();
		$query = 'SELECT a.*, SUM(b.home) AS home' .
				' FROM #__menu_types AS a' .
				' LEFT JOIN #__menu AS b ON b.menutype = a.menutype' .
				' GROUP BY a.id';
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
			$id = ' AND id != '.(int) $row->id;
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
				' WHERE menutype = '.$db->Quote($row->menutype) .
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
		$list = JHtml::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0 );

		// assemble menu items to the array
		$mitems 	= array();
		$mitems[] 	= JHtml::_('select.option',  '0', JText::_( 'Top' ) );

		foreach ( $list as $item ) {
			$mitems[] = JHtml::_('select.option',  $item->id, '&nbsp;&nbsp;&nbsp;'. $item->treename );
		}

		$output = JHtml::_(
			'select.genericlist',
			$mitems,
			'parent',
			array('list.attr' => 'class="inputbox" size="10"', 'list.select' => $row->parent)
		);

		return $output;
	}

	/**
	* build the select list for target window
	*/
	function Target( &$row )
	{
		$click[] = JHtml::_('select.option',  '0', JText::_( 'Parent Window With Browser Navigation' ) );
		$click[] = JHtml::_('select.option',  '1', JText::_( 'New Window With Browser Navigation' ) );
		$click[] = JHtml::_('select.option',  '2', JText::_( 'New Window Without Browser Navigation' ) );
		$target = JHtml::_(
			'select.genericlist',
			$click,
			'browserNav',
			array('list.attr' => 'class="inputbox" size="4"', 'list.select' => intval($row->browserNav))
		);

		return $target;
	}

	/**
	* build the select list for target window
	*/
	function Published( &$row )
	{
		$put[] = JHtml::_('select.option',  '0', JText::_( 'No' ));
		$put[] = JHtml::_('select.option',  '1', JText::_( 'Yes' ));

		// If not a new item, trash is not an option
		if ( !$row->id ) {
			$row->published = 1;
		}
		$published = JHtml::_('select.radiolist',  $put, 'published', '', 'value', 'text', $row->published );
		return $published;
	}
}